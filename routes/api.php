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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endereços
    Route::get('/enderecos', [EnderecoController::class, 'index']);
    Route::post('/enderecos', [EnderecoController::class, 'store']);
    Route::get('/enderecos/{endereco}', [EnderecoController::class, 'show']);
    Route::put('/enderecos/{endereco}', [EnderecoController::class, 'update']);
    Route::delete('/enderecos/{endereco}', [EnderecoController::class, 'destroy']);

    // Ofertas
    Route::get('/ofertas', [OfertaResiduoController::class, 'index']);
    Route::post('/ofertas', [OfertaResiduoController::class, 'store']);
    Route::post('/ofertas/{uuid}/imagens', [OfertaImagemController::class, 'store']);
    Route::get('/ofertas/{oferta}', [OfertaResiduoController::class, 'show']);
    Route::put('/ofertas/{oferta}', [OfertaResiduoController::class, 'update']);
    Route::delete('/ofertas/{oferta}', [OfertaResiduoController::class, 'destroy']);
    Route::get('/minhas-ofertas', [OfertaResiduoController::class, 'minhasOfertas']);

    // Coletas
    Route::get('/minhas-coletas', [ColetaController::class, 'minhasColetas']);
    Route::get('/coletas/{coleta}', [ColetaController::class, 'show']);
    Route::post('/ofertas/{oferta}/reservar', [ColetaController::class, 'reservar']);
    Route::post('/coletas/{coleta}/cancelar', [ColetaController::class, 'cancelar']);
    Route::post('/coletas/{coleta}/confirmar-fabrica', [ColetaController::class, 'confirmarFabrica']);
    Route::post('/coletas/{coleta}/confirmar-coletor', [ColetaController::class, 'confirmarColetor']);
});
