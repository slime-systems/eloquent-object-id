<?php

namespace SlimeSystems\EloquentObjectId;

use Closure;
use SlimeSystems\ObjectId;
use SlimeSystems\ObjectId\Exception\Invalid;
use function is_string;

class OI
{
    private static array $memoized = [];

    public static function setDefault(string $fieldName): Closure
    {
        return self::$memoized[$fieldName] ??= function ($model) use ($fieldName) {
            if ($model->$fieldName !== null)
                return;
            $model->$fieldName = new ObjectId;
        };
    }

    public static function val($id): string
    {
        if ($id instanceof ObjectId)
            return $id->toBinary();
        if (!is_string($id))
            return $id;
        try {
            return ObjectId::fromString($id)->toBinary();
        } catch (Invalid) {
            return $id;
        }
    }
}
