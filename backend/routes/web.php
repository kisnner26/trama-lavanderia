<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Middleware\EnsureBranchAccess;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/acceso', [SessionController::class, 'create'])->name('login');
    Route::post('/acceso', [SessionController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});
Route::middleware('auth')->group(function () {
    Route::get('/', [WorkspaceController::class, 'home'])->name('home');
    Route::post('/salir', [SessionController::class, 'destroy'])->name('logout');
    Route::middleware(EnsureBranchAccess::class)->prefix('/sucursales/{branch}')->whereNumber('branch')->group(function () {
        Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');
    });
    Route::get('/sucursales/{branch}', [WorkspaceController::class, 'show'])
        ->whereNumber('branch')->middleware(EnsureBranchAccess::class)->name('workspace');
});
