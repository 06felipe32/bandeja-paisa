<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\RestaurantController::class, 'index'])->name('restaurante');
Route::match(['get','post','put'], '/rest/api/{path}', [\App\Http\Controllers\RestaurantController::class, 'handle'])->middleware('throttle:120,1');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'activo', 'auth.session'])->group(function (): void {
    Route::get('/inicio', DashboardController::class)->name('dashboard');
    Route::get('/mi-perfil', [ClienteController::class, 'profile'])->middleware('rol:cliente')->name('perfil');
    Route::middleware('rol:administrador,trabajador')->group(function (): void {
        // US-03 (editar/eliminar clientes) corresponde al Sprint 2.
        Route::resource('clientes', ClienteController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
        Route::get('/mesas/{mesa}', [MesaController::class, 'show'])->whereNumber('mesa')->name('mesas.show');
    });
    Route::middleware('rol:administrador')->group(function (): void {
        Route::get('/mesas/crear', [MesaController::class, 'create'])->name('mesas.create');
        Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
        Route::get('/mesas/{mesa}/editar', [MesaController::class, 'edit'])->name('mesas.edit');
        Route::put('/mesas/{mesa}', [MesaController::class, 'update'])->name('mesas.update');
        Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy'])->name('mesas.destroy');
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
    });
});
