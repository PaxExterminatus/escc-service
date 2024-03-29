<?php

namespace App\Base\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

abstract class DatabaseQuery
{
    protected EloquentBuilder|QueryBuilder $query;

    function __construct()
    {
        $this->query = $this->base();
    }

    static function make(): static
    {
        return new static;
    }

    abstract protected function base(): EloquentBuilder|QueryBuilder;

    protected function apply(): static
    {
        return $this;
    }

    function get(): iterable|Collection|EloquentCollection
    {
        return $this->apply()->query->get();
    }
}
