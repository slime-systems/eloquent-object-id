<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use SlimeSystems\EloquentObjectId\ServiceProvider;
use Illuminate\Database\Capsule\Manager as DB;

abstract class DBTestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $db = new DB;
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $db->setAsGlobal();
        $db->bootEloquent();

        DB::schema()->create('cats', function ($table) {
            $table->objectId('id')->primary();
            $table->string('name');
        });
    }
}

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use SlimeSystems\EloquentObjectId\ObjectIdCast;
use SlimeSystems\EloquentObjectId\OI;

class Cat extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'id' => ObjectIdCast::class,
    ];

    protected static function booted()
    {
        static::creating(OI::setDefault('id'));
    }
}
