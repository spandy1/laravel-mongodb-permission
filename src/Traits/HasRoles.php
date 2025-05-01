<?php
namespace Jimmy\Permissions\Traits;

use Jimmy\Permissions\Models\Role;

trait HasRoles
{
    use InteractsWithGuard;

    public function role() { return $this->belongsTo(Role::class, 'role_id', '_id'); }

    public function assignRole($role)
    {
        $role            = $this->resolveRole($role);
        $this->role_id   = $role->getKey();
        $this->save();
        cache()->forget($this->cacheKey());
        return $this;
    }

    public function removeRole()
    {
        $this->role_id = null; $this->save();
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
}
