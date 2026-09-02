<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/probe', function () {
    return response('ok');
});

Route::get('/perf-probe', function () {
    $start = microtime(true);

    return response()->json([
        'message' => 'ok',
        'server_time_ms' => round((microtime(true) - $start) * 1000, 2),
    ]);
});