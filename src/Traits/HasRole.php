<?php
namespace Jimmy\Permissions\Traits;

use MongoDB\BSON\ObjectId;
use Jimmy\Permissions\Models\Role;

trait HasRole
{
    use InteractsWithGuard;

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', '_id');
    }

    /* assign / overwrite the single role */
    public function assignRole(string|Role $role)
    {
        $role          = $this->resolveRole($role);
        $this->role_id = new ObjectId((string)$role->getKey());
        $this->save();
        cache()->forget($this->cacheKey());
        return $this;
    }

    public function removeRole()
    {
        $this->role_id = null;
        $this->save();
        cache()->forget($this->cacheKey());
        return $this;
    }

    public function hasRole(string|Role $role): bool
    {
        $role = $this->resolveRole($role);
        return (string)$this->role_id === (string)$role->getKey();
    }

    protected function resolveRole(string|Role $role): Role
    {
        if ($role instanceof Role) return $role;

        return Role::where('name', $role)
            ->where('guard_name', $this->guardName())
            ->firstOrFail();
    }
}
