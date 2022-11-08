<?php

namespace App\Models;

use Eloquent;
use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Client
 *
 * @package App\Models
 * @property-read int id
 * @property-read string name
 * @property-read string middle_name
 * @property-read string last_name
 * @property-read float total_deb
 * @mixin Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ClientCourse[] $courses
 * @property-read int|null $courses_count
 * @property-read string $last_name
 * @property-read string $middle_name
 * @property-read string $name
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 */
class Client extends Model
{
    use HasFactory;
    use FieldAdapter;

    public $table = 'API_CLIENT';
    protected $primaryKey = 'id';
    protected $casts = [
        'total_deb' => 'float',
    ];

    // Accessors -------------------------------------------------------------------------------------------------------

    function getNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    function getMiddleNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    function getLastNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    // Relations -------------------------------------------------------------------------------------------------------

    function courses(): HasMany
    {
        return $this->hasMany(ClientCourse::class, 'client_id', 'id');
    }
}
