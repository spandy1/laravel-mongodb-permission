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
        ->wherePivot('model_type', get_class($this))
        ->wherePivot('guard_name', $this->getGuard());
    }

    public function givePermissionTo(...$permissions)
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_permissions');

        foreach (collect($permissions)->flatten() as $permission) {
            $permission = $this->getStoredPermission($permission);

            $attributes = [
                'permission_id' => $permission->getKey(),
                'model_type'    => get_class($this),
                'model_id'      => $this->getKey(),
                'guard_name'    => $permission->guard_name,
            ];

            DB::connection($conn)
                ->table($table)
                ->updateOrInsert($attributes, []);
        }

        $this->clearPermissionCache();

        return $this;
    }

    public function revokePermissionTo($permission)
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_permissions');

        $permission = $this->getStoredPermission($permission);

        DB::connection($conn)
            ->table($table)
            ->where('permission_id', $permission->getKey())
            ->where('model_type',    get_class($this))
            ->where('model_id',      $this->getKey())
            ->where('guard_name',    $permission->guard_name)
            ->delete();

        $this->clearPermissionCache();

        return $this;
    }

    public function syncPermissions(...$permissions)
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_permissions');

        // remove all existing
        DB::connection($conn)
            ->table($table)
            ->where('model_type', get_class($this))
            ->where('model_id',   $this->getKey())
            ->delete();

        // reassign
        return $this->givePermissionTo(...$permissions);
    }

    public function hasPermissionTo($permission): bool
    {
        $cacheKey  = 'permissions_for_user_' . $this->getKey();
        $conn      = $this->getConnectionName();
        $modelType = get_class($this);

        $all = cache()->remember(
            $cacheKey,
            config('permission.cache_ttl') * 60,
            function () use ($conn, $modelType) {
                // direct perms
                $direct = DB::connection($conn)
                    ->table(config('permission.collections.model_has_permissions'))
                    ->where('model_type', $modelType)
                    ->where('model_id',   $this->getKey())
                    ->pluck('permission_id')
                    ->toArray();

                // via roles
                $roleIds = DB::connection($conn)
                    ->table(config('permission.collections.model_has_roles'))
                    ->where('model_type', $modelType)
                    ->where('model_id',   $this->getKey())
                    ->pluck('role_id')
                    ->toArray();

                $viaRoles = [];
                foreach ($roleIds as $roleId) {
                    $perms = DB::connection($conn)
                        ->table(config('permission.collections.role_has_permissions'))
                        ->where('role_id', $roleId)
                        ->pluck('permission_id')
                        ->toArray();
                    $viaRoles = array_merge($viaRoles, $perms);
                }

                return array_unique(array_merge($direct, $viaRoles));
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
