<?php

use App\Http\Controllers\Api\LisController;
use App\Http\Controllers\Api\LisHasilController;
use Illuminate\Support\Facades\Route;

Route::post('/lis/registrasi',[LisController::class, 'registrasi']);

Route::post('/lis/registrasi/{idLab}', [LisController::class, 'registrasiSP']
    )->whereNumber('idLab');
// GET JSON dari Stored Procedure (tidak mengirim LIS)    
 Route::get('/lis/registrasi/{idLab}', [LisController::class, 'getRegistrasiSP']
    )->whereNumber('idLab');

// LIS -> SIMRS
Route::post('/lis/hasil', [LisHasilController::class, 'terimaHasil']
    )->middleware('lis.apikey');

Route::post('/lis/hasil/update', [LisHasilController::class, 'updateHasil']
    )->middleware('lis.apikey');

Route::post('/lis/batal-order', [LisController::class, 'batalOrder']
    )->middleware('lis.apikey');