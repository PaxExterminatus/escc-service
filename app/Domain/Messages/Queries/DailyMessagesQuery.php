<?php

namespace App\Domain\Messages\Queries;

use App\Base\Queries\DatabaseQuery;
use App\Domain\Messages\Models\MessagesDaily;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class DailyMessagesQuery extends DatabaseQuery
{
    use DailyMessagesParamsTrait;

    protected function base(): EloquentBuilder|QueryBuilder
    {
        return MessagesDaily::query();
    }

    protected function apply(): static
    {
        $this->query->where('type', 'SMS');
        return $this;
    }
}
