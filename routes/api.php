<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — authentification par Bearer Token (Sanctum)
|--------------------------------------------------------------------------
|
| POST   /api/login          → obtenir un token
| POST   /api/logout         → révoquer le token courant
|
| GET    /api/projects        → liste paginée des projets
| POST   /api/projects        → créer un projet
| GET    /api/projects/{id}   → détail + tâches
| PUT    /api/projects/{id}   → mettre à jour
| DELETE /api/projects/{id}   → supprimer
|
| GET    /api/tasks           → liste paginée des tâches
| POST   /api/tasks           → créer une tâche
| GET    /api/tasks/{id}      → détail
| PUT    /api/tasks/{id}      → mettre à jour
| DELETE /api/tasks/{id}      → supprimer
|
*/

// Routes publiques — pas de token requis
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées — Bearer Token requis
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('projects', ProjectController::class);

    Route::apiResource('tasks', TaskController::class);
});
