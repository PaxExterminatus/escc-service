<?php

namespace App\Domain\Messages\Models;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Traits\FieldAdapter;
use Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @mixin Eloquent
 */
class DailyMessage extends Model
{
    use HasFactory;
    use FieldAdapter;

    static string $TYPE_SMS = 'SMS';
    static string $TYPE_EMAIL = 'EMAIL';

    protected $table = 'API_MESSAGES_SMS_DAILY';
    protected $primaryKey = 'ID';
    protected $fillable = ['id', 'type', 'encoding', 'address', 'body'];
}
