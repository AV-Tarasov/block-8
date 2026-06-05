<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::get('/ready', function () {

    try {

        DB::select('SELECT 1');

        Redis::ping();

        return response()->json([
            'status' => 'ready',
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'status' => 'not_ready',
            'error' => $e->getMessage(),
        ], 503);
    }
});

Route::get('/metrics', function () {

    return response()->json([
        'users_total' => User::count(),
        'projects_total' => Project::count(),
        'tasks_total' => Task::count(),
        'comments_total' => Comment::count(),
        'memory_usage_mb' => round(
            memory_get_usage(true) / 1024 / 1024,
            2
        ),
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource(
        'projects',
        ProjectController::class
    );

    Route::apiResource(
        'projects.tasks',
        TaskController::class
    )->scoped();

    Route::apiResource(
        'tasks.comments',
        CommentController::class
    );
});
