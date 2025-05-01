<?php

namespace Jimmy\Permissions\Models;

use Illuminate\Support\Facades\DB;
use MongoDB\Laravel\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'guard_name'];

    /**
     * Use configured connection or default.
     */
    public function getConnectionName()
    {
        return config('permission.connection') ?: parent::getConnectionName();
    }

    public function getTable()
    {
        return config('permission.collections.roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            config('permission.collections.role_has_permissions'),
            'role_id',
            'permission_id'
        );
    }

    public function givePermissionTo($permission)
    {
        $perm = $this->getStoredPermission($permission);

        DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.role_has_permissions'))
            ->updateOne(
                [
                    'role_id'       => $this->getKey(),
                    'permission_id' => $perm->getKey(),
                    'guard_name'    => $perm->guard_name,
                ],
                ['$set' => [
                    'role_id'       => $this->getKey(),
                    'permission_id' => $perm->getKey(),
                    'guard_name'    => $perm->guard_name,
                ]],
                ['upsert' => true]
            );

        return $this;
    }

    public function revokePermissionTo($permission)
    {
        $perm = $this->getStoredPermission($permission);

        DB::connection($this->getConnectionName())
            ->collection(config('permission.collections.role_has_permissions'))
            ->deleteOne([
                'role_id'       => $this->getKey(),
                'permission_id' => $perm->getKey(),
                'guard_name'    => $perm->guard_name,
            ]);

        return $this;
    }

    protected function getStoredPermission($permission): Permission
    {
        if (is_string($permission)) {
            return Permission::where('name', $permission)
                ->where('guard_name', $this->guard_name)
                ->firstOrFail();
        }

        if ($permission instanceof Permission) {
            return $permission;
        }

        throw new \InvalidArgumentException("Invalid permission");
    }
}
