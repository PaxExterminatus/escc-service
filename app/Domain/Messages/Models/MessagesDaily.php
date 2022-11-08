<?php

namespace App\Domain\Messages\Models;

use App\Traits\FieldAdapter;
use Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Domain\Messages\Models\MessagesDaily
 *
 * @property int id
 * @property string body
 * @property string address
 * @method static EloquentBuilder|MessagesDaily newModelQuery()
 * @method static EloquentBuilder|MessagesDaily newQuery()
 * @method static EloquentBuilder|MessagesDaily query()
 * @mixin Eloquent
 */
class MessagesDaily extends Model
{
    use HasFactory;
    use FieldAdapter;

    static string $TYPE_SMS = 'SMS';
    static string $TYPE_EMAIL = 'EMAIL';

    protected $table = 'API_MESSAGES_SMS_DAILY';
    protected $primaryKey = 'ID';
    protected $fillable = ['id', 'type', 'encoding', 'address', 'body'];
}
