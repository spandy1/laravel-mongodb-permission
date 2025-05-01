<?php

use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleHasPermissionsCollection extends Migration
{
    public function up()
    {
        $conn = config('permission.connection') ?: config('database.default');
        Schema::connection($conn)
            ->create(config('permission.collections.role_has_permissions'), function (Blueprint $collection) {
                $collection->index(
                    ['permission_id','role_id','guard_name'],
                    ['unique' => true]
                );
            });
    }

    public function down()
    {
        $conn = config('permission.connection') ?: config('database.default');
        Schema::connection($conn)
            ->dropIfExists(config('permission.collections.role_has_permissions'));
    }
}
