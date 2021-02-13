<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    public $table = 'API_CLIENT';
    protected $primaryKey = 'id';

    private function nameCase($value) {
        return Str::of($value)->lower()->ucfirst();
    }

    public function getNameAttribute($value) {
        return $this->nameCase($value);
    }

    public function getMiddleNameAttribute($value) {
        return $this->nameCase($value);
    }

    public function getLastNameAttribute($value) {
        return $this->nameCase($value);
    }
}
