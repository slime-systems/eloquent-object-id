<?php

namespace SlimeSystems\EloquentObjectId;

use Closure;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use SlimeSystems\ObjectId;
use SlimeSystems\ObjectId\Exception\Invalid;

class ObjectIdCast implements CastsAttributes
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

    private static array $memoized = [];

    public static function setDefault(string $fieldName): Closure
    {
        return self::$memoized[$fieldName] ??= function ($model) use ($fieldName) {
            if (!is_null($model->$fieldName))
                return;
            $model->$fieldName = new ObjectId;
        };
    }
}
