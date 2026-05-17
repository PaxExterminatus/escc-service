<?php

namespace App\Domain\Cabinet\Models;

use App\Casts\NameCast;
use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 *
 * @property-read int|null $courses_count
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Course[] $courses
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 *
 * @mixin \Eloquent
 */
class Client extends Model
{
    use HasFactory, FieldAdapter;

    public $table = 'API_CLIENT';
    protected $primaryKey = 'id';
    protected $casts = [
        'total_deb' => 'float',
        'name' => NameCast::class,
        'middle_name' => NameCast::class,
        'last_name' => NameCast::class,
    ];

    // Accessors -------------------------------------------------------------------------------------------------------

    // Relations -------------------------------------------------------------------------------------------------------

    function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'client_id', 'id');
    }
}
