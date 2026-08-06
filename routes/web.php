<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    app()->setLocale('fa');

    return view('welcome');
});
