<?php

namespace App\Models;

use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientCourse
 * @package App\Models
 * @property-read int id
 * @property-read int client_id
 * @property-read int status_id
 * @property-read string status
 * @property-read string name
 */
class ClientCourse extends Model
{
    use HasFactory;
    use FieldAdapter;

    protected $hidden = ['status_id'];
    protected $appends = ['status'];
    public $table = 'API_CLIENT_COURSES';
    protected $primaryKey = 'id';
    protected $casts = [
        'client_id' => 'integer',
        'status_id' => 'integer',
    ];

    // Accessors ---------------------------------------------------------

    /**
     * Course status name.
     * @return string
     */
    public function getStatusAttribute(): string
    {
        if ($this->status_id === 1) return 'active';
        if ($this->status_id === 2) return 'not active';
        if ($this->status_id === 3) return 'error';
        if ($this->status_id === 4) return 'finished';
        if ($this->status_id === 5) return 'outage';
        if ($this->status_id === 6) return 'finishing';
        if ($this->status_id === 7) return 'refusing';
        return 'unknown';
    }

    /**
     * Course name.
     * @param $value
     * @return string
     */
    public function getNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }
}
