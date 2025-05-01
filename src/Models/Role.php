<?php
namespace Jimmy\Permissions\Models;

use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;
use Jimmy\Permissions\Traits\InteractsWithGuard;
use Jimmy\Permissions\Models\Permission;
use Jimmy\Permissions\Casts\ObjectIdArray;

class Role extends Model
{
    use InteractsWithGuard;

    protected $fillable = ['name', 'guard_name', 'permission_ids'];

    protected $casts = [
        'permission_ids' => ObjectIdArray::class,
    ];

    public function getTable(): string
    {
        return config('permission.collections.roles');
    }

    /* -------------------------------------------------------------------
     |  public API
     |-------------------------------------------------------------------- */

    public function givePermissionTo(string|Permission ...$permissions)
    {
        $this->addPermissions($permissions);
        return $this;
    }

    public function revokePermissionTo(string|Permission ...$permissions)
    {
        $this->removePermissions($permissions);
        return $this;
    }

    public function syncPermissions(array $permissions)
    {
        $this->permission_ids = [];
        $this->save();

        $this->addPermissions($permissions);
        return $this;
    }

    /* -------------------------------------------------------------------
     |  Internal helpers
     |-------------------------------------------------------------------- */

    /** @param  array<string|Permission> $permissions */
    private function addPermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            $perm = $this->resolvePermission($permission);
            $this->push(
                'permission_ids',
                new ObjectId($perm->getKey()),
                true
            );
        }
    }

    /** @param  array<string|Permission> $permissions */
    private function removePermissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            $perm = $this->resolvePermission($permission);
            $this->pull('permission_ids', new ObjectId($perm->getKey()));
        }
    }

    private function resolvePermission(string|Permission $permission): Permission
    {
        if ($permission instanceof Permission) {
            return $permission;
        }

        return Permission::where('name', $permission)
            ->where('guard_name', $this->guard_name)
            ->firstOrFail();
    }
}
