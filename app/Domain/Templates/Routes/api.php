<?php

use App\Domain\Templates\Controllers\TagController;
use App\Domain\Templates\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('tags', [TagController::class, 'index']);
        Route::get('containers/{id}/tags', [TagController::class, 'forContainer']);

        /** Самостоятельные шаблоны сообщений (SMS/Email) */
        Route::get('templates', [TemplateController::class, 'index']);
        Route::post('templates', [TemplateController::class, 'store']);
        Route::post('templates/email-preview', [TemplateController::class, 'emailPreview']);

        /** Библиотека обёрток письма (шапка+футер), см. Template::wrappers() */
        Route::get('templates/wrappers', [TemplateController::class, 'wrappers']);

        /** Шаблоны, привязанные к операциям (счёт-документ/письмо) */
        Route::get('templates/operations', [TemplateController::class, 'operations']);
        Route::post('templates/operations', [TemplateController::class, 'assignOperation']);
        Route::get('templates/operations/{operationId}/file', [TemplateController::class, 'operationFile'])
            ->where('operationId', '\d+');

        /** Действия над конкретным шаблоном — ниже буквенных путей, чтобы {id} их не перехватывал */
        Route::put('templates/{id}', [TemplateController::class, 'update'])->whereNumber('id');
        Route::delete('templates/{id}', [TemplateController::class, 'destroy'])->whereNumber('id');
        Route::get('templates/{id}/render', [TemplateController::class, 'render'])->whereNumber('id');
        Route::get('templates/{id}/preview', [TemplateController::class, 'preview'])->whereNumber('id');
    });
