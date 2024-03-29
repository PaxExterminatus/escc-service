<?php

namespace App\Base;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ApplicationProgrammingInterfaceRequest extends FormRequest
{
    public function all($keys = null): array
    {
        $all = parent::all($keys);
        foreach (Route::getCurrentRoute()->parameters() as $key => $value)
        {
            $all[$key] = $value;
        }
        return $all;
    }

    protected function failedValidation(Validator $validator)
    {
        $data = [
            'message' => 'Failed validation',
            'errors' => $validator->errors()
        ];

        $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        $response = response()->json($data, $status);

        throw new HttpResponseException($response);
    }
}
