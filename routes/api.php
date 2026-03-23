<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    ProjectController,
    TaskController,
    CommentController,
    NotificationController,
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Health check endpoint
Route::get('/health', fn() => response()->json([
    'status' => 'ok',
    'timestamp' => now(),
    'environment' => config('app.env'),
]))->name('health');

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register')
        ->middleware('throttle:5,1');
    
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:5,1');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Tasks
    Route::prefix('projects/{projectId}/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/{taskId}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('/{taskId}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/{taskId}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // Comments
        Route::prefix('{taskId}/comments')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('comments.index');
            Route::post('/', [CommentController::class, 'store'])->name('comments.store');
        });
    });

    // Comments
    Route::patch('/comments/{commentId}', [CommentController::class, 'update'])
        ->name('comments.update');
    Route::delete('/comments/{commentId}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::patch('/{notificationId}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
    });
});