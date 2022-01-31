<?php

namespace App\Models;

use App\Traits\FieldAdapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CourseCategory
 * @package App\Models
 * @property-read int node_id Course node
 * @property-read string code Category code
 * @property-read string name Category name
 */
class CourseCategory extends Model
{
    use HasFactory;
    use FieldAdapter;

    public $table = 'API_COURSES_CATEGORY';
    protected $primaryKey = 'node_id';
    protected $casts = [
        'node_id' => 'integer',
    ];

    // Accessors -------------------------------------------------------------------------------------------------------

    public function getNameAttribute($value): string
    {
        return $this->adaptCase($value);
    }

    // Relations -------------------------------------------------------------------------------------------------------

    public function lessons(): HasMany
    {
        return $this->hasMany(ClientCourseLesson::class, 'course_id', 'id');
    }
}
