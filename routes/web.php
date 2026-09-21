<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('lab-auth.login');
});
Route::get('/dashboard', function () {
    return view('dashboard');
});



