<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/preview-email', function () {
    $data = [
        'name' => 'Surya Intan Permana',
        'formasi' => 'Analis Dampak Lingkungan',
    ];

    return view('emails.test-blast', $data);
});
