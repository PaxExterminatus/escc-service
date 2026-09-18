<?php

use App\Domain\App\Invoice\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')
    ->group(function () {
        Route::get('invoice/container/{id}', [InvoiceController::class, 'show']);
        Route::get('invoice/container/{id}/email-preview', [InvoiceController::class, 'emailPreview']);
        Route::post('invoice/container/{id}/email', [InvoiceController::class, 'sendEmail']);
        Route::get('invoice/range/{from}/{to}', [InvoiceController::class, 'range'])
            ->where(['from' => '\d{4}-\d{2}-\d{2}', 'to' => '\d{4}-\d{2}-\d{2}']);
        Route::get('invoice/range/{from}/{to}/print', [InvoiceController::class, 'rangePrint'])
            ->where(['from' => '\d{4}-\d{2}-\d{2}', 'to' => '\d{4}-\d{2}-\d{2}']);
    });
