<?php

namespace Jimmy\Permissions\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheResetCommand extends Command
{
    protected $signature   = 'rbac:cache-reset';
    protected $description = 'Clear all permission caches';

    public function handle()
    {
        Cache::flush();
        $this->info('Permission cache cleared.');
    }
}
