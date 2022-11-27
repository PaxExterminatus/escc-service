<?php

namespace App\Domain\Messages\Queries;

use App\Base\Repositories\DatabaseQuery;
use App\Domain\Messages\Models\DailyMessageView;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class DailyMessagesQuery extends DatabaseQuery implements DailyMessagesRepository
{
    use DailyMessagesParamsTrait;

    protected function base(): EloquentBuilder|QueryBuilder
    {
        return DailyMessageView::query();
    }

    protected function apply(): static
    {
        $this->query->where('type', 'SMS');
        return $this;
    }
}
