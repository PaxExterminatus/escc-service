<?php

namespace App\Base;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
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
        throw new HttpResponseException(
            $this->error([
                'errors' => $validator->errors()
            ]),
        );
    }

    protected function error(iterable $data = [], string $message = 'Failed validation', int $code = 400, string $redirect = null): JsonResponse
    {
        return response()->json([
                'status' => 'error',
                'message' => $message,
            ] + $data, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
