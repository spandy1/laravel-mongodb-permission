<?php
namespace Jimmy\Permissions\Casts;

use MongoDB\BSON\ObjectId;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class ObjectIdArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return array_map(
            fn($id) => $id instanceof ObjectId ? $id : new ObjectId($id),
            $value ?? []
        );
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return array_map(
            fn($id) => $id instanceof ObjectId ? $id : new ObjectId($id),
            $value ?? []
        );
    }
}
