<?php

use App\Http\Controllers\AuthLabController;
use App\Http\Controllers\UserLabController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthLabController::class, 'showLogin'])
    ->name('lab.login');

Route::post('/login', [AuthLabController::class, 'login'])
    ->name('lab.login.post');

Route::middleware('cekloginlab')->group(function () {

    Route::get('/', function () {
        return redirect('/rawat-inap');
    })->name('lab.home');

    Route::post('/logout', [AuthLabController::class, 'logout'])
        ->name('lab.logout');

    Route::post('/profile/password/update', [AuthLabController::class, 'updatePassword'])
        ->name('lab.profile.password.update');

    Route::prefix('user')->name('userlab.')->group(function () {
        Route::get('/', [UserLabController::class, 'index'])->name('index');
        Route::post('/store', [UserLabController::class, 'store'])->name('store');
        Route::put('/update/{id}', [UserLabController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [UserLabController::class, 'destroy'])->name('destroy');
    });
});