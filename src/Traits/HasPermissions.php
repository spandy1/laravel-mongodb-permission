<?php

namespace Jimmy\Permissions\Traits;

use Illuminate\Support\Facades\DB;
use Jimmy\Permissions\Models\Permission;

trait HasPermissions
{
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('permission.collections.model_has_permissions'),
            'model_id',
            'permission_id'
        )
        ->where('model_type', get_class($this))
        ->where('guard_name', $this->getGuard());
    }

    public function givePermissionTo(...$permissions)
    {
        foreach (collect($permissions)->flatten() as $permission) {
            $permission = $this->getStoredPermission($permission);

            DB::connection($this->getConnectionName())
                ->collection(config('permission.collections.model_has_permissions'))
                ->updateOne(
                    [
                        'permission_id' => $permission->getKey(),
                        'model_type'    => get_class($this),
                        'model_id'      => $this->getKey(),
                        'guard_name'    => $permission->guard_name,
                    ],
                    ['$set' => [
                        'permission_id' => $permission->getKey(),
                        'model_type'    => get_class($this),
                        'model_id'      => $this->getKey(),
                        'guard_name'    => $permission->guard_name,
                    ]],
                    ['upsert' => true]
                );
        }

        $this->clearPermissionCache();

        return $this;
    }

    public function revokePermissionTo($permission)
    {
        $permission = $this->getStoredPermission($permission);

        DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.model_has_permissions'))
            ->deleteOne([
                'permission_id' => $permission->getKey(),
                'model_type'    => get_class($this),
                'model_id'      => $this->getKey(),
                'guard_name'    => $permission->guard_name,
            ]);

        $this->clearPermissionCache();

        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.model_has_permissions'))
            ->deleteMany([
                'model_type' => get_class($this),
                'model_id'   => $this->getKey(),
            ]);

        return $this->givePermissionTo(...$permissions);
    }

    public function hasPermissionTo($permission): bool
    {
        $cacheKey = 'permissions_for_user_' . $this->getKey();

        $all = cache()->remember(
            $cacheKey,
            config('permission.cache_ttl') * 60,
            function () {
                $conn = $this->getConnectionName();

                $direct = DB::connection($conn)
                    ->collection(config('permission.collections.model_has_permissions'))
                    ->where('model_type', get_class($this))
                    ->where('model_id',   $this->getKey())
                    ->pluck('permission_id');

                $viaRoles = collect(
                    DB::connection($conn)
                        ->collection(config('permission.collections.model_has_roles'))
                        ->where('model_type', get_class($this))
                        ->where('model_id',   $this->getKey())
                        ->pluck('role_id')
                )
                ->flatMap(fn($roleId) => DB::connection($conn)
                    ->collection(config('permission.collections.role_has_permissions'))
                    ->where('role_id', $roleId)
                    ->pluck('permission_id'));

                return collect($direct)->merge($viaRoles)->unique()->toArray();
            }
        );

        $perm = $this->getStoredPermission($permission);

        return in_array($perm->getKey(), $all, true);
    }

    protected function getStoredPermission($permission): Permission
    {
        if (is_string($permission)) {
            return Permission::where('name', $permission)
                ->where('guard_name', $this->getGuard())
                ->firstOrFail();
        }

        if ($permission instanceof Permission) {
            return $permission;
        }

        throw new \InvalidArgumentException("Invalid permission");
    }

    protected function getGuard(): string
    {
        return property_exists($this, 'guard_name')
            ? $this->guard_name
            : config('auth.defaults.guard');
    }

    public function getConnectionName(): string
    {
        return config('permission.connection') ?: config('database.default');
    }

    protected function clearPermissionCache()
    {
        cache()->forget('permissions_for_user_' . $this->getKey());
    }
}
