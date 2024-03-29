<?php

namespace App\Models;

use App\Traits\FieldAdapter;
use Eloquent;
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
