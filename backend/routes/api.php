<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Resources\UserResource;


Route::get('/user', function (Request $request) {
    return new UserResource($request->user()->load('roles'));
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('applications', ApplicationController::class);
        Route::post('applications/{application}/submit',   [ApplicationController::class, 'submit']);
        Route::post('applications/{application}/documents',[DocumentController::class, 'store']);

        Route::middleware('role:penilai')->group(function () {
            Route::post('applications/{application}/review', [ReviewController::class, 'store']);
            Route::get('reviews/history',                    [ReviewController::class, 'history']);
        });

        Route::get('dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('export/applications', [ExportController::class, 'applications']);
    });
});
