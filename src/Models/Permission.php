<?php
namespace Jimmy\Permissions\Models;

use MongoDB\Laravel\Eloquent\Model;
use Jimmy\Permissions\Traits\InteractsWithGuard;

class Permission extends Model
{
    use InteractsWithGuard;

    protected $fillable = ['name','guard_name'];
    public function getTable(): string
    { return config('permission.collections.permissions'); }
}
