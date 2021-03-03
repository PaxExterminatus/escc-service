<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    protected string $paramsClass;

    public function params() {
        return new $this->paramsClass($this->all());
    }
}
