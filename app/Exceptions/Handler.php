<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler extends ExceptionHandler
{
    protected function convertValidationExceptionToResponse(ValidationException $e, $request): Response|JsonResponse|SymfonyResponse|RedirectResponse|null
    {
        if ($e->response) {
            return $e->response;
        }

        return response()->json($e->validator->errors()->getMessages(), 422);
    }

    /**
     * routes/api.php — чистый JSON API, а не HTML. По умолчанию Laravel решает
     * JSON-ответ вернуть или HTML-страницу ошибки по заголовку Accept запроса —
     * для браузерной навигации (или клиента без Accept: application/json) это
     * значит HTML "404 Not Found" вместо {"message": ...} даже для api/*-роутов.
     * Форсируем JSON по префиксу пути, не трогая routes/web.php (там SPA-фолбэк
     * на resources/views/spa.blade.php, ему HTML нужен).
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $e);
    }

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // ModelNotFoundException::getMessage() включает полный класс модели
        // ("No query results for model [App\...\Profile]") — служебная деталь
        // реализации, которая утекает во фронтенд-тост как есть. Отдаём наружу
        // только "No query results". Ловим уже после того, как базовый Handler
        // (prepareException) сам оборачивает ModelNotFoundException в
        // NotFoundHttpException — на исходный тип renderable() не сработал бы,
        // потому что эта конвертация происходит раньше проверки renderable-колбэков.
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') && $e->getPrevious() instanceof ModelNotFoundException) {
                return response()->json(['message' => 'No query results'], 404);
            }
        });
    }
}
