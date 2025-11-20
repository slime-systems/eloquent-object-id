<?php

namespace SlimeSystems\EloquentObjectId;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use SlimeSystems\ObjectId;
use SlimeSystems\ObjectId\Exception\Invalid;

class ObjectIdCast implements CastsAttributes, SerializesCastableAttributes
{
    public function get($model, string $key, mixed $value, array $attributes): ?ObjectId
    {
        try {
            return ObjectId::fromBinary($value);
        } catch (Invalid) {
            return null;
        }
    }

    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof ObjectId)
            return $value->toBinary();
        return null;
    }

    public function serialize($model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof ObjectId)
            return $value->toBinary();
        return $value;
    }
}
