<?php

namespace App\Domain\Cabinet\Models;

use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ClientCourse
 *
 * @package App\Models
 * @property-read int id
 * @property-read int client_id
 * @property-read int status_id
 * @property-read int node_id
 * @property-read string status
 * @property-read string name
 *
 * @property-read int|null $lessons_count
 * @property-read int|null $categories_count
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ClientCourseLesson[] $lessons
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseCategory[] $categories
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCourse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCourse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCourse query()
 *
 * @mixin \Eloquent
 */
class ClientCourse extends Model
{
    use HasFactory;
    use FieldAdapter;

    protected $appends = ['status'];
    public $table = 'API_CLIENT_COURSES';
    protected $primaryKey = 'id';
    protected $casts = [
        'client_id' => 'integer',
        'status_id' => 'integer',
        'node_id' => 'integer',
    ];

    // Accessors -------------------------------------------------------------------------------------------------------

    function getStatusAttribute(): string
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

    function getNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    // Relations -------------------------------------------------------------------------------------------------------

    function lessons(): HasMany
    {
        return $this->hasMany(ClientCourseLesson::class, 'course_id', 'id');
    }

    function categories(): HasMany
    {
        return $this->hasMany(CourseCategory::class, 'node_id', 'node_id');
    }
}
