<?php

use App\Http\Controllers\LabController;
use App\Http\Controllers\LabPrintController;
use App\Http\Controllers\RawatInapController;
use Illuminate\Support\Facades\Route;

Route::post('/insert', [RawatInapController::class, 'insertLab'])
    ->name('insert');

Route::post('/delete', [RawatInapController::class, 'deleteLab'])
    ->name('delete');

Route::get('/sp-info', [LabController::class, 'getInfoSP'])
    ->name('sp.info'); 
    
Route::get('/sppa-info', [LabController::class, 'getInfoSPPA'])
    ->name('sppa.info');

Route::get('/laboratorium/master', [LabController::class, 'masterLab'])
    ->name('master');

Route::get('/laboratorium/harga', [LabController::class, 'hargaLab'])
    ->name('harga');

Route::post('/laboratorium/simpan', [LabController::class, 'simpanLab'])
    ->name('simpan');

Route::post('/laboratorium/update', [LabController::class, 'updateLab'])
    ->name('update');

Route::post('/laboratorium/simpan-header', [LabController::class, 'simpanHeaderLab'])
    ->name('simpan.header');

Route::get('/detail/{idLab}', [LabController::class, 'detailLab'])
    ->name('detail');

Route::get('/grup', [LabController::class, 'grupLab'])
    ->name('grup');
    
Route::post('/grup/generate', [LabController::class, 'generateGrupLab'])
    ->name('grup.generate');

Route::get('/grup-kelompok', [LabController::class, 'grupKelLab'])
    ->name('grup.kelompok');
   
Route::post('/grup-kelompok/generate', [LabController::class, 'generateGrupKelLab'])
    ->name('grup.kelompok.generate');

Route::get('/print-sp/{no}', [LabController::class, 'printSP'])
    ->name('print.sp');

Route::get('/print-sppa/{no}/{idLab}', [LabController::class, 'printSPPA'])
    ->name('print.sppa');

Route::post('/tarik-sp', [LabController::class, 'tarikSP'])
    ->name('tarik.sp');

Route::post('/sp/selesai', [LabController::class, 'selesaiSP'])
    ->name('sp.selesai');

Route::post('/sp/update-dokter', [LabController::class, 'updateDokterFromSP'])
    ->name('sp.update.dokter');

Route::get('/lis/list', [LabController::class, 'listLIS'])
    ->name('lis.list');
    
Route::post('/lis/tarik', [LabController::class, 'tarikLIS'])
    ->name('lis.tarik');

Route::post('/lab/lis-saba/insert', [LabController::class, 'insertLISSaba'])
    ->name('lis.saba.insert');

Route::get('/lab/lis-saba/cek', [LabController::class, 'cekLISSaba'])
    ->name('lis.saba.cek');

Route::post('/lab/lis-saba/update', [LabController::class, 'updateLISSaba'])
    ->name('lis.saba.update');

Route::post('/lab/lis-saba/tarik', [LabController::class, 'tarikLISSaba'])
    ->name('lis.saba.tarik');

Route::get('/analis', [LabController::class, 'analisLab'])
    ->name('analis');

Route::post('/foto/upload', [LabController::class, 'uploadFotoLab'])
    ->name('foto.upload');    
    
Route::get('/foto/{idLab}/{prepID}', [LabController::class, 'listFotoLab'])
    ->name('foto.list');    

Route::get('/foto/{idLab}/{prepID}/{name}', [LabController::class, 'showFotoLab'])
    ->name('foto.show');
       
Route::delete('/foto/delete', [LabController::class, 'hapusFotoLab'])
    ->name('foto.delete');

Route::post('/update-pxrs/{id}', [RawatInapController::class, 'updatePxRS'])
    ->name('update.pxrs');

Route::get('/print/{idLab}', [LabPrintController::class, 'print'])
    ->name('print');  

Route::get('/printkwitansi/{idLab}', [LabPrintController::class, 'printkwitansi'])
    ->name('printkwitansi');      

Route::get('/cari-pasien-id', [LabController::class, 'cariPasien'])->name('cari.pasien.id'); 
