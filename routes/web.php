<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/esim', function () {
    return view('esim');
})->name('esim.index');

Route::get('/payment/{esimId}', function ($esimId) {
    return view('payment', ['esimId' => $esimId]);
})->name('payment.show');

Route::get('/payment-success', function () {
    return view('payment_success');
})->name('payment.success');

Route::get('/', function () {
    return view('welcome');
});
