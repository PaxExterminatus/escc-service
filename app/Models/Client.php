<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Ramsey\Collection\Collection;

class Client extends Model
{
    use HasFactory;

    public $table = 'API_CLIENT';
    protected $primaryKey = 'id';

    private function nameCase($value): string
    {
        return Str::of($value)->lower()->ucfirst();
    }

    public function getNameAttribute($value): string
    {
        return $this->nameCase($value);
    }

    public function getMiddleNameAttribute($value): string
    {
        return $this->nameCase($value);
    }

    public function getLastNameAttribute($value): string
    {
        return $this->nameCase($value);
    }

    /**
     * @return HasMany|Collection|ClientCourse[]
     */
    public function courses(): HasMany
    {
        return $this->hasMany(ClientCourse::class, 'client_id', 'id');
    }
}
