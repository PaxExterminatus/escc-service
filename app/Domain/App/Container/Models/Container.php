<?php

namespace App\Domain\App\Container\Models;

use App\Domain\App\Container\Casts\ContainerStatusCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int container_id
 * @property int client_id
 * @property int status_id
 * @property string|null container_code
 * @property float container_cost
 * @property float container_weight
 * @property float post_val1
 * @property float post_val2
 * @property float post_val3
 * @property float post_val4
 * @property float post_val5
 * @property float post_fee
 * @property int post_schema
 * @property int post_pack
 * @property Carbon container_date
 * @property Carbon|null invoice_date
 * @property Carbon|null assemble_date
 * @property Carbon|null send_date
 * @property Carbon|null notice_date
 * @property int msg_id
 * @property int|null rec_tool
 * @property int|null user_id
 * @property int|null sub_id
 * @property int mode_throw
 * @property int mode_sale
 * @property int mode_event
 * @property int|null clone_id
 * @property float post_val6
 * @property float post_fee_client
 * @property int batch_id
 * @property Carbon|null unload_date
 *
 * @mixin \Eloquent
 */
class Container extends Model
{
    protected $table = 'CONTAINER';
    protected $primaryKey = 'container_id';

    protected function casts(): array
    {
        return [
            'container_id' => 'integer',
            'client_id' => 'integer',
            'status_id' => ContainerStatusCast::class,

        ];
    }
}
