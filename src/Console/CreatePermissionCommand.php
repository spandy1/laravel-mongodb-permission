<?php

namespace Jimmy\Permissions\Console;

use Illuminate\Console\Command;
use Jimmy\Permissions\Models\Permission;

class CreatePermissionCommand extends Command
{
    protected $signature   = 'rbac:create-permission {name}';
    protected $description = 'Create a new permission';

    public function handle()
    {
        $perm = Permission::firstOrCreate([
            'name'       => $this->argument('name'),
            'guard_name' => config('auth.defaults.guard'),
        ]);

        $this->info("Permission '{$perm->name}' created.");
    }
}
