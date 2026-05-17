<?php

namespace App\Domain\Messages\Models;

use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Domain\Messages\Models\MessagesDaily
 *
 * @property int id
 * @property string type
 * @property string address
 * @property string body
 * @method static EloquentBuilder|DailyMessage newModelQuery()
 * @method static EloquentBuilder|DailyMessage newQuery()
 * @method static EloquentBuilder|DailyMessage query()
 * @method static EloquentBuilder|DailyMessage whereType(string $type)
 * @mixin \Eloquent
 */
class DailyMessage extends Model
{
    use HasFactory;

    protected $table = 'API_MESSAGES_SMS_DAILY';
    protected $primaryKey = 'ID';
    protected $fillable = ['id', 'type', 'encoding', 'address', 'body'];
}
