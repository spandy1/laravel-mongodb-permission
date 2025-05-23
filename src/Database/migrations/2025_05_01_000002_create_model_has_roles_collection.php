<?php

use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModelHasRolesCollection extends Migration
{
    public function up()
    {
        $conn = config('permission.connection') ?: config('database.default');
        Schema::connection($conn)
            ->create(config('permission.collections.model_has_roles'), function (Blueprint $collection) {
                $collection->index(
                    [
                        'role_id'    => 1,
                        'model_type' => 1,
                        'model_id'   => 1,
                        'guard_name' => 1,
                    ],
                    options: ['unique' => true]
                );
            });
    }

    public function down()
    {
        $conn = config('permission.connection') ?: config('database.default');
        Schema::connection($conn)
            ->dropIfExists(config('permission.collections.model_has_roles'));
    }
}
