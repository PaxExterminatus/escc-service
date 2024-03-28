<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\DatabaseQuery;
use App\Domain\Messages\Models\DailyMessageView;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;

class DailyMessagesSourceDatabase extends DatabaseQuery
{
    use DailyMessagesParamsTrait;

    protected function base(): EloquentBuilder|QueryBuilder
    {
        return DailyMessageView::query();
    }

    protected function apply(): static
    {
        if ($this->type)
            $this->query->where('type', Str::upper($this->type));

        return $this;
    }
}
