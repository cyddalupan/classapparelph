<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-modal', function () {
    return view('inventory.create-test');
})->name('test.modal');
