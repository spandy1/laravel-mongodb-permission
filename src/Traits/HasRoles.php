<?php

namespace Jimmy\Permissions\Traits;

use Illuminate\Support\Facades\DB;
use Jimmy\Permissions\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            config('permission.collections.model_has_roles'),
            'model_id',
            'role_id'
        )
        ->where('model_type', get_class($this))
        ->where('guard_name', $this->getGuard());
    }

    public function assignRole(...$roles)
    {
        foreach (collect($roles)->flatten() as $role) {
            $role = $this->getStoredRole($role);

            DB::connection($this->getConnectionName())
                ->collection(config('permission.collections.model_has_roles'))
                ->updateOne(
                    [
                        'role_id'    => $role->getKey(),
                        'model_type' => get_class($this),
                        'model_id'   => $this->getKey(),
                        'guard_name' => $role->guard_name,
                    ],
                    ['$set' => [
                        'role_id'    => $role->getKey(),
                        'model_type' => get_class($this),
                        'model_id'   => $this->getKey(),
                        'guard_name' => $role->guard_name,
                    ]],
                    ['upsert' => true]
                );
        }

        $this->clearPermissionCache();

        return $this;
    }

    public function removeRole($role)
    {
        $role = $this->getStoredRole($role);

        DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.model_has_roles'))
            ->deleteOne([
                'role_id'    => $role->getKey(),
                'model_type' => get_class($this),
                'model_id'   => $this->getKey(),
                'guard_name' => $role->guard_name,
            ]);

        $this->clearPermissionCache();

        return $this;
    }

    public function syncRoles(...$roles)
    {
        DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.model_has_roles'))
            ->deleteMany([
                'model_type' => get_class($this),
                'model_id'   => $this->getKey(),
            ]);

        return $this->assignRole(...$roles);
    }

    public function hasRole($role): bool
    {
        $role = $this->getStoredRole($role);

        return (bool) DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.model_has_roles'))
            ->where([
                'role_id'    => $role->getKey(),
                'model_type' => get_class($this),
                'model_id'   => $this->getKey(),
                'guard_name' => $role->guard_name,
            ])
            ->count();
    }

    public function hasAnyRole(...$roles): bool
    {
        foreach ($roles as $r) {
            if ($this->hasRole($r)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllRoles(...$roles): bool
    {
        foreach ($roles as $r) {
            if (! $this->hasRole($r)) {
                return false;
            }
        }
        return true;
    }

    protected function getStoredRole($role): Role
    {
        if (is_string($role)) {
            return Role::where('name', $role)
                       ->where('guard_name', $this->getGuard())
                       ->firstOrFail();
        }

        if ($role instanceof Role) {
            return $role;
        }

        throw new \InvalidArgumentException("Invalid role");
    }

    protected function getGuard(): string
    {
        return property_exists($this, 'guard_name')
            ? $this->guard_name
            : config('auth.defaults.guard');
    }

    protected function getConnectionName(): string
    {
        return config('permission.connection') ?: config('database.default');
    }

    protected function clearPermissionCache()
    {
        cache()->forget('permissions_for_user_' . $this->getKey());
    }
}
