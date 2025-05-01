<?php

namespace Jimmy\Permissions\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'rbac:install';
    protected $description = 'Publish config & migrations';

    public function handle()
    {
        $this->info('Publishing configuration and migrations...');
        $this->call('vendor:publish', ['--tag' => 'permission-config']);
        $this->call('vendor:publish', ['--tag' => 'permission-migrations']);

        $this->info("Now run:\n  php artisan migrate");
    }
}
