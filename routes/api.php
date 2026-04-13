<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnderecoController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\OfertaResiduoController;
use App\Http\Controllers\OfertaImagemController;
use App\Http\Controllers\ColetaController;
use App\Http\Controllers\DashboardController;

// Auth Público
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Materiais e Info Publica / Dashboard simples não exige auth segundo requisito geral ou pode ser publico dependendo da visão do frontend
Route::get('/materiais', [MaterialController::class, 'index']);
Route::get('/dashboard/impacto', [DashboardController::class, 'impacto']);

// UUID imagem upload - caso queira manter publico com token curto ou autenticado
Route::post('/ofertas/{uuid}/imagens', [OfertaImagemController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endereços
    Route::get('/enderecos', [EnderecoController::class, 'index']);
    Route::post('/enderecos', [EnderecoController::class, 'store']);

    // Ofertas
    Route::get('/ofertas', [OfertaResiduoController::class, 'index']);
    Route::post('/ofertas', [OfertaResiduoController::class, 'store']);
    Route::get('/ofertas/{id}', [OfertaResiduoController::class, 'show']);
    Route::get('/minhas-ofertas', [OfertaResiduoController::class, 'minhasOfertas']);

    // Coletas
    Route::get('/minhas-coletas', [ColetaController::class, 'minhasColetas']);
    Route::post('/ofertas/{id}/reservar', [ColetaController::class, 'reservar']);
    Route::post('/coletas/{id}/confirmar-fabrica', [ColetaController::class, 'confirmarFabrica']);
    Route::post('/coletas/{id}/confirmar-coletor', [ColetaController::class, 'confirmarColetor']);
});
