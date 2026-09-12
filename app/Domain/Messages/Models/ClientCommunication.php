<?php

namespace App\Domain\Messages\Models;

use App\Casts\PhoneCast;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int client_id
 * @property string|null client_mphone
 * @property bool client_smsuse
 * @property string|null client_email
 * @property bool subscriber_email_status
 * @mixin \Eloquent
 */
class ClientCommunication extends Model
{
    protected $table = 'CLIENT_PROPERTY';
    protected $primaryKey = 'client_id';
    public $timestamps = false;
    protected $fillable = ['client_mphone', 'client_smsuse', 'client_email', 'subscriber_email_status'];

    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'client_smsuse' => 'boolean',
            'client_mphone' => PhoneCast::class,
            'subscriber_email_status' => 'boolean',
        ];
    }
}
