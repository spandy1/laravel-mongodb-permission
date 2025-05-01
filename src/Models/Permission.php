<?php
namespace Jimmy\Permissions\Models;

use Jimmy\Permissions\Traits\InteractsWithGuard;
use MongoDB\Laravel\Eloquent\Model;

class Permission extends Model
{
    use InteractsWithGuard;
    protected $fillable = ['name','guard_name'];
    public function getTable(): string
    { return config('permission.collections.permissions'); }
}