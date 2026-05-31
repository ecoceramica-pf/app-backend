<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MaterialController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OfertaController;
use App\Http\Controllers\Admin\ColetaController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Rotas administrativas protegidas pelos middlewares auth:sanctum + admin.
| Prefixo: /api/admin
|
*/

// Materiais
Route::post('/materiais', [MaterialController::class, 'store']);
Route::get('/materiais/{material}', [MaterialController::class, 'show']);
Route::put('/materiais/{material}', [MaterialController::class, 'update']);
Route::delete('/materiais/{material}', [MaterialController::class, 'destroy']);

// Usuários
Route::get('/usuarios', [UserController::class, 'index']);
Route::get('/usuarios/{id}', [UserController::class, 'show']);
Route::put('/usuarios/{id}/status', [UserController::class, 'updateStatus']);

// Ofertas
Route::get('/ofertas', [OfertaController::class, 'index']);
Route::get('/ofertas/{id}', [OfertaController::class, 'show']);
Route::delete('/ofertas/{id}', [OfertaController::class, 'destroy']);
Route::post('/ofertas/{id}/restore', [OfertaController::class, 'restore']);

// Coletas
Route::get('/coletas', [ColetaController::class, 'index']);
Route::get('/coletas/{id}', [ColetaController::class, 'show']);

// Dashboard
Route::get('/dashboard/metricas', [DashboardController::class, 'metricas']);
