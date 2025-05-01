<?php

namespace Jimmy\Permissions\Traits;

use Jimmy\Permissions\Models\Role;

trait HasRoles
{
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', '_id');
    }

    public function assignRole($role)
    {
        $role = $this->getStoredRole($role);
        $this->role_id = $role->getKey();
        $this->save();
        cache()->forget('permissions_for_user_'.$this->getKey());
        return $this;
    }

    public function removeRole()
    {
        $this->role_id = null;
        $this->save();
        cache()->forget('permissions_for_user_'.$this->getKey());
        return $this;
    }

    public function hasRole($role): bool
    {
        $role = $this->getStoredRole($role);
        return (string)$this->role_id === (string)$role->getKey();
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

        throw new \InvalidArgumentException('Invalid role');
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
}
