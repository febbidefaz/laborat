<?php

use App\Http\Controllers\NotifSPController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NotifSPController::class, 'notifSP'])
    ->name('index');