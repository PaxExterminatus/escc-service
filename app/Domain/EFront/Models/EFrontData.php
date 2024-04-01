<?php

namespace App\Domain\EFront\Models;

use App\Domain\Messages\Models\DailyMessage;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Domain\EFront\Models\EFrontData
 *
 * @property int client_id
 * @property string data_date
 * @property string data_xml
 * @property string data_lob
 *
 * @method static EloquentBuilder|EFrontData newModelQuery()
 * @method static EloquentBuilder|EFrontData newQuery()
 * @method static EloquentBuilder|EFrontData query()
 * @mixin \Eloquent
 */
class EFrontData extends Model
{
    use HasFactory;

    protected $table = 'API_EFRONT_DATA';
    protected $primaryKey = 'CLIENT_ID';
    protected $fillable = ['client_id', 'data_date', 'data_xml', 'data_lob'];
}
