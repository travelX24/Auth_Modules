<?php

use Athka\AuthKit\Http\Controllers\ForgotPasswordController;
use Athka\AuthKit\Http\Controllers\LoginController;
use Athka\AuthKit\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

$prefix = config('authkit.routes.prefix', '');
$as = config('authkit.routes.as', 'authkit.');
$as = $as === '' ? '' : rtrim($as, '.').'.';

Route::middleware('web')->group(function () use ($prefix, $as) {
    // Login routes
    Route::get($prefix ?: '/login', [LoginController::class, 'show'])->name($as.'login');
    Route::post($prefix ?: '/login', [LoginController::class, 'store'])->name($as.'login.store');
    Route::post('/logout', [LoginController::class, 'logout'])->name($as.'logout');

    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name($as.'password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->middleware('throttle:5,1')->name($as.'password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name($as.'password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'update'])->middleware('throttle:10,1')->name($as.'password.update');
});
