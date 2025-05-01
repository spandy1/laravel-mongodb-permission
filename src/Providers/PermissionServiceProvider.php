<?php

namespace Jimmy\Permissions\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Jimmy\Permissions\Console\{
    CreateRoleCommand,
    CreatePermissionCommand,
    CacheResetCommand,
    InstallCommand
};
use Jimmy\Permissions\Http\Middleware\{
    RoleMiddleware,
    PermissionMiddleware
};

class PermissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/permission.php',
            'permission'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/permission.php' => config_path('permission.php'),
        ], 'permission-config');

        $this->publishes([
            __DIR__.'/../Database/migrations/' => database_path('migrations'),
        ], 'permission-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateRoleCommand::class,
                CreatePermissionCommand::class,
                CacheResetCommand::class,
                InstallCommand::class,
            ]);
        }

        $router = $this->app['router'];
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('permission', PermissionMiddleware::class);

        Blade::directive('role', fn($expr) => "<?php if(auth()->check() && auth()->user()->hasRole{$expr}): ?>");
        Blade::directive('endrole', fn()      => "<?php endif; ?>");

        Blade::directive('hasanyrole', fn($expr) => "<?php if(auth()->check() && auth()->user()->hasAnyRole{$expr}): ?>");
        Blade::directive('endhasanyrole', fn()   => "<?php endif; ?>");

        Blade::directive('permission', fn($expr) => "<?php if(auth()->check() && auth()->user()->hasPermissionTo{$expr}): ?>");
        Blade::directive('endpermission', fn()    => "<?php endif; ?>");
    }
}
