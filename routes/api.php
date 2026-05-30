<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Bearer Token authentication (Sanctum)
|--------------------------------------------------------------------------
|
| POST   /api/login          → obtain a token
| POST   /api/logout         → revoke the current token
|
| GET    /api/projects        → paginated list of projects
| POST   /api/projects        → create a project
| GET    /api/projects/{id}   → detail + tasks
| PUT    /api/projects/{id}   → update
| DELETE /api/projects/{id}   → delete
|
| GET    /api/tasks           → paginated list of tasks
| POST   /api/tasks           → create a task
| GET    /api/tasks/{id}      → detail
| PUT    /api/tasks/{id}      → update
| DELETE /api/tasks/{id}      → delete
|
*/

// All API route names are prefixed with "api." to avoid collisions with the
// web route names (e.g. the shallow "tasks.update" route in routes/web.php).
Route::name('api.')->group(function () {

    // Public routes — no token required
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Protected routes — Bearer Token required
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::apiResource('projects', ProjectController::class);

        Route::apiResource('tasks', TaskController::class);
    });
});
