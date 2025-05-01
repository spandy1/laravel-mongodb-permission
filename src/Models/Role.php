<?php
namespace Jimmy\Permissions\Models;

use MongoDB\Laravel\Eloquent\Model;
use Jimmy\Permissions\Models\Permission;

class Role extends Model
{
    protected $fillable = ['name','guard_name','permission_ids'];
    protected $casts    = ['permission_ids'=>'array'];

    public function getConnectionName(): string
    {
        return config('permission.connection') ?: parent::getConnectionName();
    }

    public function getTable(): string
    {
        return config('permission.collections.roles');
    }

    public function givePermissionTo($permission)
    {
        $perm = $this->getStoredPermission($permission);
        $ids  = $this->permission_ids ?? [];
        $key  = (string)$perm->getKey();
        if (! in_array($key, $ids, true)) {
            $ids[] = $key;
            $this->permission_ids = $ids;
            $this->save();
        }
        return $this;
    }

    public function revokePermissionTo($permission)
    {
        $perm = $this->getStoredPermission($permission);
        $ids  = $this->permission_ids ?? [];
        $key  = (string)$perm->getKey();
        if (($i = array_search($key, $ids, true)) !== false) {
            unset($ids[$i]);
            $this->permission_ids = array_values($ids);
            $this->save();
        }
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
        throw new \InvalidArgumentException('Invalid permission');
    }
}
