<?php

use Illuminate\Support\Facades\Route;
use Athka\AuthKit\Http\Controllers\Api\AuthController;
use Athka\AuthKit\Http\Controllers\Api\PasswordController;

$prefix = trim((string) config('authkit.api.prefix', 'api/auth'), '/');
$as     = (string) config('authkit.api.as', 'authkit.api.');
$as     = $as === '' ? '' : rtrim($as, '.') . '.';

$mw     = (array) config('authkit.api.middleware', ['api']);
$authMw = (array) config('authkit.api.auth_middleware', ['auth:sanctum']);

Route::prefix($prefix)->as($as)->middleware($mw)->group(function () use ($authMw) {
    // Auth
    Route::post('/login',  [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware($authMw)->name('logout');
    Route::get('/me',      [AuthController::class, 'me'])->middleware($authMw)->name('me');

    // ✅ NEW: Bootstrap (Company + Employee + Roles/Permissions)
    Route::get('/bootstrap', [AuthController::class, 'bootstrap'])->middleware($authMw)->name('bootstrap');

    // Password
    Route::post('/change-password', [PasswordController::class, 'change'])->middleware($authMw)->name('password.change');
    Route::post('/forgot-password', [PasswordController::class, 'forgot'])->name('password.forgot');
    Route::post('/reset-password',  [PasswordController::class, 'reset'])->name('password.reset');
});

