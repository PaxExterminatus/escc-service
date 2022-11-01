<?php

namespace App\Base\Queries;

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

    function query(): EloquentBuilder|QueryBuilder
    {
        return $this->apply()->query;
    }
}
