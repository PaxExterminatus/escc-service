<?php

namespace App\Models;

use Eloquent;
use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Client
 * @package App\Models
 *
 * @property-read int id
 * @property-read string name
 * @property-read string middle_name
 * @property-read string last_name
 *
 * @mixin Eloquent
 */
class Client extends Model
{
    use HasFactory;
    use FieldAdapter;

    public $table = 'API_CLIENT';
    protected $primaryKey = 'id';

    // Accessors ---------------------------------------------------------

    /**
     * Client name.
     * @param $value
     * @return string
     */
    public function getNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    /**
     * Client middle_name.
     * @param $value
     * @return string
     */
    public function getMiddleNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    /**
     * Client last_name.
     * @param $value
     * @return string
     */
    public function getLastNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    // Relations --------------------------------------------------------

    /**
     * Client courses.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(ClientCourse::class, 'client_id', 'id');
    }

    // -------------------------------------------------------------------
}
