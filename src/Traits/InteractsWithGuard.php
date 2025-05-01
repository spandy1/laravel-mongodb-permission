<?php
namespace Jimmy\Permissions\Traits;

trait InteractsWithGuard
{
    protected function guardName(): string
    {
        return property_exists($this, 'guard_name')
            ? $this->guard_name
            : config('auth.defaults.guard');
    }

    protected function cacheKey(): string
    {
        return 'permissions_for_user_'.$this->getKey();
    }

    public function getConnectionName(): string
    {
        return config('permission.connection') ?: config('database.default');
    }
}
