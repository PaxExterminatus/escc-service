<?php

namespace App\Domain\App\Profile\Models;

use App\Casts\NameCast;
use App\Casts\SexCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int client_id
 * @property int type_id
 * @property int status_id
 * @property int area_id
 * @property string client_code
 * @property Carbon client_date
 * @property string twins_code
 * @property string client_desc
 * @property string client_name
 * @property string client_middle_name
 * @property string client_last_name
 * @property Carbon client_birthday
 * @property string client_sex
 *
 * @mixin \Eloquent
 */
class Profile extends Model
{
    protected $table = 'CLIENT';
    protected $primaryKey = 'client_id';

    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'client_date' => 'datetime',
            'client_name' => NameCast::class,
            'client_middle_name' => NameCast::class,
            'client_last_name' => NameCast::class,
            'client_birthday' => 'date:Y-m-d',
            'client_sex' => SexCast::class,
        ];
    }

    // Accessors -------------------------------------------------------------------------------------------------------

    // Relations -------------------------------------------------------------------------------------------------------
}
