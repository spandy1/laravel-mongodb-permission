<?php

namespace Jimmy\Permissions\Traits;

use Illuminate\Support\Facades\Cache;
use Jimmy\Permissions\Models\Permission;

trait HasPermissions
{
    public function getRolePermissions()
    {
        return $this->role
            ? Permission::whereIn('_id', $this->role->permission_ids ?? [])->get()
            : collect();
    }

    public function hasPermissionTo($permission): bool
    {
        $perm      = $this->getStoredPermission($permission);
        $cacheKey  = 'permissions_for_user_'.$this->getKey();
        $allowed   = Cache::remember(
            $cacheKey,
            config('permission.cache_ttl') * 60,
            fn() => $this->role->permission_ids ?? []
        );

        return in_array((string)$perm->getKey(), $allowed, true);
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

        throw new \InvalidArgumentException('Invalid permission');
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
