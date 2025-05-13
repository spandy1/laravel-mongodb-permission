<?php
namespace Jimmy\Permissions\Traits;

use MongoDB\BSON\ObjectId;
use Jimmy\Permissions\Models\Role;
use Jimmy\Permissions\Casts\ObjectIdArray;

trait HasRoles
{
    use InteractsWithGuard;

    /* ensure role_ids is cast to BSON array */
    public function initializeHasRoles(): void
    {
        $this->casts['role_ids'] = ObjectIdArray::class;
    }

    /* return a collection of Role models */
    public function roles()
    {
        return Role::whereIn('_id', $this->role_ids ?? [])->get();
    }

    public function assignRole(string|Role ...$roles)
    {
        foreach ($roles as $role) {
            $role = $this->resolveRole($role);
            $this->push('role_ids', new ObjectId($role->getKey()), true);
        }
        cache()->forget($this->cacheKey());
        return $this;
    }

    public function removeRole(string|Role ...$roles)
    {
        foreach ($roles as $role) {
            $role = $this->resolveRole($role);
            $this->pull('role_ids', new ObjectId($role->getKey()));
        }
        cache()->forget($this->cacheKey());
        return $this;
    }

    public function syncRoles(array $roles)
    {
        $this->role_ids = [];
        $this->save();
        return $this->assignRole(...$roles);
    }

    public function hasRole(string|Role $role): bool
    {
        $role = $this->resolveRole($role);
        return in_array((string)$role->getKey(), array_map('strval', $this->role_ids ?? []), true);
    }

    public function hasAnyRole(...$roles): bool
    {
        foreach ($roles as $r) if ($this->hasRole($r)) return true;
        return false;
    }

    public function hasAllRoles(...$roles): bool
    {
        foreach ($roles as $r) if (! $this->hasRole($r)) return false;
        return true;
    }

    protected function resolveRole(string|Role $role): Role
    {
        if ($role instanceof Role) return $role;

        return Role::where('name',$role)
            ->where('guard_name',$this->guardName())
            ->firstOrFail();
    }
}
