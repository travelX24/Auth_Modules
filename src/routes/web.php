<?php

use Illuminate\Support\Facades\Route;
use Athka\AuthKit\Http\Controllers\LoginController;
use Athka\AuthKit\Http\Controllers\ForgotPasswordController;
use Athka\AuthKit\Http\Controllers\ResetPasswordController;

$prefix = trim((string) config('authkit.routes.prefix', ''), '/');
$as     = config('authkit.routes.as', config('authkit.routes.name', 'authkit.'));

$route = Route::middleware('web')->name($as);

if ($prefix !== '') {
    $route->prefix($prefix);
}

$route->group(function () {

        Route::middleware('guest')->group(function () {
            Route::get('/login', [LoginController::class, 'show'])->name('login');
            Route::post('/login', [LoginController::class, 'store'])->name('login.store');

            Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
            Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');

            Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
            Route::post('/reset-password', [ResetPasswordController::class, 'update'])->name('password.update');
        });

        Route::middleware('auth')->group(function () {
            Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        });
    });
