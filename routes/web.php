<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoice/upload', [InvoiceController::class, 'upload']);
});

Route::post('/whatsapp/webhook', [WebhookController::class, 'handleIncoming']);
