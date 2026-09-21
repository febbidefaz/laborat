<?php

use App\Http\Controllers\SPController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SPController::class, 'index']) ->name('index');
Route::get('/data', [SPController::class, 'data']) ->name('data');
Route::get('/{no}', [SPController::class, 'detail']) ->name('detail');
Route::post('/update-ket-cancel', [SPController::class, 'updateKetCancel'])
    ->name('update.ketcancel');