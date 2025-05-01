<?php
namespace Jimmy\Permissions\Traits;

use MongoDB\BSON\ObjectId;
use Jimmy\Permissions\Models\Role;

trait HasRoles
{
    use InteractsWithGuard;

    public function initializeHasRoles(): void
    {
        $this->casts['role_id'] = ObjectId::class;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', '_id');
    }

    public function assignRole($role)
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

    public function hasRole($role): bool
    {
        $role = $this->resolveRole($role);
        return (string)$this->role_id === (string)$role->getKey();
    }

    protected function resolveRole($role): Role
    {
        if ($role instanceof Role) return $role;

        return Role::where('name', $role)
            ->where('guard_name', $this->guardName())
            ->firstOrFail();
    }

    public function getRoleId()
    {
        return (string)$this->role_id;
    }

    public function setRoleId($role_id): void
    {
        $this->role_id = new ObjectId($role_id);
    }
}