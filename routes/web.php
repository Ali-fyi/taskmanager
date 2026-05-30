<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('workspaces', WorkspaceController::class);

    // POST   /workspaces/{workspace}/members         → invite a member
    // DELETE /workspaces/{workspace}/members/{user}  → remove a member
    Route::post('workspaces/{workspace}/members', [WorkspaceMemberController::class, 'store'])
        ->name('workspaces.members.store');
    Route::delete('workspaces/{workspace}/members/{user}', [WorkspaceMemberController::class, 'destroy'])
        ->name('workspaces.members.destroy');

    Route::resource('workspaces.projects', ProjectController::class)
        ->except(['index'])
        ->scoped();

    Route::resource('workspaces.projects.tasks', TaskController::class)
        ->except(['index'])
        ->shallow();

    Route::resource('workspaces.statuses', StatusController::class)
        ->except(['show'])
        ->scoped();

    // store : POST /tasks/{task}/comments
    // destroy (shallow) : DELETE /comments/{comment}
    Route::resource('tasks.comments', CommentController::class)
        ->only(['store', 'destroy'])
        ->shallow();
});

require __DIR__.'/auth.php';
