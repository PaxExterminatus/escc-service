<?php

namespace App\Domain\Cabinet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ClientCourseLesson
 *
 * @package App\Models
 * @property-read int id
 * @property-read int client_id
 * @property-read int course_id
 * @property-read int node_id
 * @property-read string name
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCourseLesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCourseLesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientCourseLesson query()
 *
 * @mixin \Eloquent
 */
class ClientCourseLesson extends Model
{
    use HasFactory;

    public $table = 'API_CLIENT_COURSES_LESSONS';

    protected $casts = [
        'client_id' => 'integer',
        'course_id' => 'integer',
        'node_id' => 'integer',
    ];
}
