<?php

namespace Jimmy\Permissions\Models;

use MongoDB\Laravel\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'guard_name'];

    public function getConnectionName(): string
    {
        return config('permission.connection') ?: parent::getConnectionName();
    }

    public function getTable(): string
    {
        return config('permission.collections.permissions');
    }
}
