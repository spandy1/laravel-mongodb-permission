<?php

use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesCollection extends Migration
{
    public function up()
    {
        $conn = config('permission.connection') ?: config('database.default');
        Schema::connection($conn)
            ->create(config('permission.collections.roles'), function (Blueprint $collection) {
                $collection->index('name');
                $collection->index('guard_name');
                $collection->timestamps();
            });
    }

    public function down()
    {
        $conn = config('permission.connection') ?: config('database.default');
        Schema::connection($conn)
            ->dropIfExists(config('permission.collections.roles'));
    }
}
