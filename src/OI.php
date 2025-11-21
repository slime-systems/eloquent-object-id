<?php

namespace SlimeSystems\EloquentObjectId;

use Closure;
use SlimeSystems\ObjectId;

class OI
{
    private static array $memoized = [];

    public static function setDefault(string $fieldName): Closure
    {
        return self::$memoized[$fieldName] ??= function ($model) use ($fieldName) {
            if (!is_null($model->$fieldName))
                return;
            $model->$fieldName = new ObjectId;
        };
    }

    public static function val(ObjectId $id): string
    {
        return $id->toBinary();
    }
}
