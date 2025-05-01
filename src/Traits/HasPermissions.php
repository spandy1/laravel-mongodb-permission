<?php
namespace Jimmy\Permissions\Traits;

use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Cache;
use Jimmy\Permissions\Models\Permission;

trait HasPermissions
{
    use InteractsWithGuard;

    public function getRolePermissions()
    {
        return $this->role
            ? Permission::whereIn('_id', $this->role->permission_ids ?? [])->get()
            : collect();
    }

    public function hasPermissionTo($permission): bool
    {
        $perm     = $this->resolvePermission($permission);
        $allowed  = Cache::remember(
            $this->cacheKey(),
            config('permission.cache_ttl')*60,
            fn() => $this->role->permission_ids ?? []
        );
        $allowedStrings = array_map('strval', $allowed);
        return in_array((string)$perm->getKey(), $allowedStrings, true);
    }

    protected function resolvePermission($permission): Permission
    {
        if ($permission instanceof Permission) return $permission;

        return Permission::where('name',$permission)
            ->where('guard_name',$this->guardName())
            ->firstOrFail();
    }
}