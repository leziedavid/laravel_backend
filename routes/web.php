<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route::view('/{any}', 'app')->where('any', '.*');
require __DIR__.'/api.php';
