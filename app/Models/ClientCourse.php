<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCourse extends Model
{
    use HasFactory;

    public $table = 'API_CLIENT_COURSES';
    protected $primaryKey = 'id';
    protected $casts = [
        'client_id' => 'integer',
        'status_id' => 'integer',
    ];
}
