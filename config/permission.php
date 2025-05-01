<?php

return [

    'connection' => env('DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Permission Collections
    |--------------------------------------------------------------------------
    */

    'collections' => [
        'roles'                 => 'roles',
        'permissions'           => 'permissions',
        'model_has_roles'       => 'model_has_roles',
        'model_has_permissions' => 'model_has_permissions',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        'role'       => Jimmy\Permissions\Models\Role::class,
        'permission' => Jimmy\Permissions\Models\Permission::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    */

    'guards' => ['web', 'api'],

    /*
    |--------------------------------------------------------------------------
    | Cache Time To Live (minutes)
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => 60,

];
