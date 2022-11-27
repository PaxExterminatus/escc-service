<?php

namespace App\Domain\Messages\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * App\Domain\Messages\Models\ElectronicMessage
 *
 * @property int|null emsg_stream
 * @property int emsg
 * @property int emsg_type
 * @property int|null emsg_type_sub
 * @property string emsg_date
 * @property string|null emsg_address
 * @property string|null emsg_body
 * @property int|null emsg_status
 * @property int|null updated_at
 * @method static Builder|ElectronicMessage newModelQuery()
 * @method static Builder|ElectronicMessage newQuery()
 * @method static Builder|ElectronicMessage query()
 * @method static Builder|ElectronicMessage whereEMSG($value)
 * @method static Builder|ElectronicMessage whereEMSGADDRESS($value)
 * @method static Builder|ElectronicMessage whereEMSGBODY($value)
 * @method static Builder|ElectronicMessage whereEMSGDATE($value)
 * @method static Builder|ElectronicMessage whereEMSGSTATUS($value)
 * @method static Builder|ElectronicMessage whereEMSGSTREAM($value)
 * @method static Builder|ElectronicMessage whereEMSGTYPE($value)
 * @method static Builder|ElectronicMessage whereEMSGTYPESUB($value)
 * @mixin Model
 * @mixin Eloquent
 */
class ElectronicMessage extends OracleEloquent
{
    protected $table = 'EMSG';
    protected $primaryKey = 'EMSG';
    protected $fillable = ['*'];

    const EMSG_STATUS_WAIT = 1; // Message waiting to be sent
    const EMSG_STATUS_TRY = 2; // The message was sent. The service provender received the message
    const EMSG_STATUS_OPERATOR = 3; // The message was sent by the operator in manual mode
}
