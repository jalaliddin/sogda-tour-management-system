<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['app' => 'Sogda Tour Automated System', 'version' => '1.0.0']);
});

Route::get('/logo', function () {
    $path = public_path('logo.jpeg');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, ['Cache-Control' => 'public, max-age=86400']);
});
