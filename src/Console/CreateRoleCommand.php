<?php

namespace Jimmy\Permissions\Console;

use Illuminate\Console\Command;
use Jimmy\Permissions\Models\Role;

class CreateRoleCommand extends Command
{
    protected $signature   = 'rbac:create-role {name}';
    protected $description = 'Create a new role';

    public function handle()
    {
        $role = Role::firstOrCreate([
            'name'       => $this->argument('name'),
            'guard_name' => config('auth.defaults.guard'),
        ]);

        $this->info("Role '{$role->name}' created.");
    }
}
