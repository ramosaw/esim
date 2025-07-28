<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EsimController;

Route::get('/countries', [EsimController::class, 'getCountries']);
Route::get('/coverages/{code}', [EsimController::class, 'getCoverages']);
Route::post('/esim/create', [EsimController::class, 'createEsim']);
Route::post('/esim/confirm', [EsimController::class, 'confirmEsim']);
Route::get('/esim/details/{id}', [EsimController::class, 'getEsimDetails']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
