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
        $cat = Cat::latest()->first();
        expect($cat->id)
            ->toBeInstanceOf(ObjectId::class)
            ->and($cat->id->toBinary())
            ->toEqual($originalId->toBinary());
    });

    test('it should be able to find by ObjectId', function () {
        $id = new ObjectId;
        Cat::create([
            'id' => $id,
            'name' => 'Chloe',
        ]);
        $cat = Cat::find(OI::val($id));
        expect($cat->name)->toBe('Chloe');
    });

    test('::val helper should support multiple formats of ObjectId', function () {
        $id1 = new ObjectId;
        $id2 = new ObjectId;
        Cat::create([
            'name' => 'Coco',
        ]);
        Cat::create([
            'id' => $id1,
            'name' => 'Smokey',
        ]);
        Cat::create([
            'id' => $id2,
            'name' => 'Tommy',
        ]);
        expect(Cat::count())->toBe(3);
        expect(Cat::whereIn('id', [OI::val($id1)])->count())->toBe(1);
        expect(Cat::whereIn('id', array_map(OI::val(...), [
            $id1->toBinary(),
            $id2->toString(),
            'Jasper',
        ]))->count())->toBe(2);
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
