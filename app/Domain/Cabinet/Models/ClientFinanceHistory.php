<?php

namespace App\Domain\Cabinet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientFinanceHistory
 *
 * @package App\Models
 * @property-read int id
 * @property-read int client_id
 * @property-read string operation_type
 * @property-read \Illuminate\Support\Carbon operation_date
 * @property-read float amount
 * @property-read string|null description
 * @property-read string|null course_name
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ClientFinanceHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientFinanceHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientFinanceHistory query()
 *
 * @mixin \Eloquent
 */
class ClientFinanceHistory extends Model
{
    use HasFactory;

    public $table = 'API_CLIENT_FINANCE_HISTORY';
    public $incrementing = false;

    protected $casts = [
        'client_id' => 'integer',
        'operation_date' => 'datetime',
        'amount' => 'float',
    ];
}
