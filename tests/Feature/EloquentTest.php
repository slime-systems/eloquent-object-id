<?php

namespace Tests;

use Carbon\Carbon;
use SlimeSystems\EloquentObjectId\OI;
use SlimeSystems\ObjectId;

describe('ObjectIdCast', function () {
    test('it correctly serialize and deserialize ObjectId to and from databases', function () {
        $originalId = new ObjectId;

        Cat::create([
            'id' => $originalId,
            'name' => 'Luna',
        ]);

        $model = Cat::latest()->first();

        expect($model->id)
            ->toBeInstanceOf(ObjectId::class)
            ->and($model->id->toBinary())
            ->toEqual($originalId->toBinary());
    });

    test('`setDefault` helper should works', function () {
        Cat::create([
            'name' => 'Peanut',
        ]);

        $model = Cat::latest()->first();

        expect($model->id)
            ->toBeInstanceOf(ObjectId::class);
    });

    test('it supports querying using comparison operators', function () {
        $ref = Carbon::now()->subDay();
        $id1 = ObjectId::fromTime($ref->addMinutes(5));
        $id2 = ObjectId::fromTime($ref->addMinutes(10));
        $id3 = ObjectId::fromTime($ref->addMinutes(15));

        foreach ([
            [$id1, 'Mittens'],
            [$id2, 'Bella'],
            [$id3, 'Shadow'],
        ] as [$id, $name]) {
            Cat::create(['id' => $id, 'name' => $name]);
        }

        $scope = Cat::where('id', '>', OI::val($id2));
        expect($scope->first()->name)->toBe('Shadow')->and($scope->count())->toBe(1);
        expect(Cat::where('id', '>=', OI::val($id2))->count())->toBe(2);
        expect(Cat::where('id', '<=', OI::val($id3))->count())->toBe(3);
    });
});
