<?php

use Illuminate\Support\Facades\Route;

// Serve the Vue SPA for all web routes
Route::get('/{any}', function () {
    // Return the 'spa' view which mounts your Vue app
    return view('spa');
})->where('any', '.*');
