<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentUploadController;

Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);

// Admin routes
Route::middleware('auth:user-api')->group(function () {
    Route::get('/payment-uploads', [PaymentUploadController::class, 'index']);
    Route::get('/payment-uploads/{id}', [PaymentUploadController::class, 'show']);
    Route::post('/payment-uploads', [PaymentUploadController::class, 'upload']);

    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
});
