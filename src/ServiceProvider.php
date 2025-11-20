<?php

namespace SlimeSystems\EloquentObjectId;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        Blueprint::macro('objectId', function ($column) {
            /** @var Blueprint $this */
            return $this->binary($column, 12, fixed: true);
        });
    }
}
