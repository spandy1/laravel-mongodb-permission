<?php
namespace Jimmy\Permissions\Traits;

use MongoDB\BSON\ObjectId;
use Illuminate\Support\Facades\Cache;
use Jimmy\Permissions\Models\{Role,Permission};

trait HasPermissions
{
    use InteractsWithGuard;

    private function roleIds(): array
    {
        if (property_exists($this, 'role_ids') && $this->role_ids) {
            return array_map(fn($id) => $id instanceof ObjectId ? $id : new ObjectId($id), $this->role_ids);
        }
        if (! empty($this->role_id)) {
            return [ $this->role_id instanceof ObjectId ? $this->role_id : new ObjectId($this->role_id) ];
        }
        return [];
    }

    public function hasPermissionTo(string|Permission $permission): bool
    {
        $perm     = $this->resolvePermission($permission);
        $cacheKey = $this->cacheKey();

        $allowedIds = Cache::remember(
            $cacheKey,
            config('permission.cache_ttl')*60,
            function () {
                $roleIds = $this->roleIds();
                if (!$roleIds) return [];
                return Role::whereIn('_id', $roleIds)
                    ->pluck('permission_ids')
                    ->flatten()
                    ->map('strval')
                    ->unique()
                    ->toArray();
            }
        );

        return in_array((string)$perm->getKey(), $allowedIds, true);
    }

    protected function resolvePermission(string|Permission $permission): Permission
    {
        if ($permission instanceof Permission) return $permission;

        return Permission::where('name',$permission)
            ->where('guard_name',$this->guardName())
            ->firstOrFail();
    }
}
