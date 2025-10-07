<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/invoice/preview/{invoice}', [InvoiceController::class, 'preview'])
    ->name('invoice.preview');


