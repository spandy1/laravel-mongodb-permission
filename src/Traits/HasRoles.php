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
        ->wherePivot('model_type', get_class($this))
        ->wherePivot('guard_name', $this->getGuard());
    }

    public function assignRole(...$roles)
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_roles');

        foreach (collect($roles)->flatten() as $role) {
            $role = $this->getStoredRole($role);

            $attributes = [
                'role_id'    => $role->getKey(),
                'model_type' => get_class($this),
                'model_id'   => $this->getKey(),
                'guard_name' => $role->guard_name,
            ];

            DB::connection($conn)
                ->table($table)
                ->updateOrInsert($attributes, []);
        }

        $this->clearPermissionCache();

        return $this;
    }

    public function removeRole($role)
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_roles');

        $role = $this->getStoredRole($role);

        DB::connection($conn)
            ->table($table)
            ->where('role_id',    $role->getKey())
            ->where('model_type', get_class($this))
            ->where('model_id',   $this->getKey())
            ->where('guard_name', $role->guard_name)
            ->delete();

        $this->clearPermissionCache();

        return $this;
    }

    public function syncRoles(...$roles)
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_roles');

        DB::connection($conn)
            ->table($table)
            ->where('model_type', get_class($this))
            ->where('model_id',   $this->getKey())
            ->delete();

        return $this->assignRole(...$roles);
    }

    public function hasRole($role): bool
    {
        $conn  = $this->getConnectionName();
        $table = config('permission.collections.model_has_roles');

        $role = $this->getStoredRole($role);

        return DB::connection($conn)
            ->table($table)
            ->where('role_id',    $role->getKey())
            ->where('model_type', get_class($this))
            ->where('model_id',   $this->getKey())
            ->where('guard_name', $role->guard_name)
            ->exists();
    }

    public function hasAnyRole(...$roles): bool
    {
        foreach (collect($roles)->flatten() as $r) {
            if ($this->hasRole($r)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllRoles(...$roles): bool
    {
        foreach (collect($roles)->flatten() as $r) {
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

    public function getConnectionName(): string
    {
        return config('permission.connection') ?: config('database.default');
    }

    protected function clearPermissionCache()
    {
        cache()->forget('permissions_for_user_' . $this->getKey());
    }
}
