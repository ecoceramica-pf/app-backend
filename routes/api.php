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
use App\Http\Controllers\DisponibilidadeController;
use App\Http\Controllers\NotificationController;

// Auth Público com Throttle
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset']);
});

// Materiais e Info Publica / Dashboard simples não exige auth segundo requisito geral ou pode ser publico dependendo da visão do frontend
Route::get('/materiais', [MaterialController::class, 'index']);
Route::get('/dashboard/impacto', [DashboardController::class, 'impacto']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard/meu-impacto', [DashboardController::class, 'meuImpacto']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Notificações
    Route::get('/notificacoes', [NotificationController::class, 'index']);
    Route::get('/notificacoes/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notificacoes/{id}/lida', [NotificationController::class, 'markAsRead']);
    Route::post('/notificacoes/ler-todas', [NotificationController::class, 'markAllAsRead']);

    // Endereços
    Route::get('/enderecos', [EnderecoController::class, 'index']);
    Route::post('/enderecos', [EnderecoController::class, 'store']);
    Route::get('/enderecos/{endereco}', [EnderecoController::class, 'show']);
    Route::put('/enderecos/{endereco}', [EnderecoController::class, 'update']);
    Route::delete('/enderecos/{endereco}', [EnderecoController::class, 'destroy']);

    // Ofertas gerais
    Route::get('/ofertas', [OfertaResiduoController::class, 'index']);
    Route::get('/ofertas/{oferta}', [OfertaResiduoController::class, 'show']);

    // Coletas gerais (que ambos os perfis podem interagir)
    Route::get('/coletas/{coleta}', [ColetaController::class, 'show']);
    Route::post('/coletas/{coleta}/cancelar', [ColetaController::class, 'cancelar']);

    // --- Rotas Exclusivas para Fábrica ---
    Route::middleware('role:fabrica')->group(function () {
        // Ofertas
        Route::post('/ofertas', [OfertaResiduoController::class, 'store']);
        Route::post('/ofertas/{uuid}/imagens', [OfertaImagemController::class, 'store']);
        Route::delete('/ofertas/imagens/{imagem}', [OfertaImagemController::class, 'destroy']);
        Route::put('/ofertas/{oferta}', [OfertaResiduoController::class, 'update']);
        Route::delete('/ofertas/{oferta}', [OfertaResiduoController::class, 'destroy']);
        Route::patch('/ofertas/{oferta}/status', [OfertaResiduoController::class, 'alterarStatus']);
        Route::get('/minhas-ofertas', [OfertaResiduoController::class, 'minhasOfertas']);

        // Coletas (ações da fábrica)
        Route::get('/coletas-fabrica', [ColetaController::class, 'coletasFabrica']);
        Route::post('/coletas/{coleta}/aprovar', [ColetaController::class, 'aprovar']);
        Route::post('/coletas/{coleta}/recusar', [ColetaController::class, 'recusar']);
        Route::post('/coletas/{coleta}/confirmar-fabrica', [ColetaController::class, 'confirmarFabrica']);

        // Disponibilidade da Fábrica
        Route::get('/disponibilidade', [DisponibilidadeController::class, 'show']);
        Route::put('/disponibilidade', [DisponibilidadeController::class, 'upsert']);
        Route::get('/disponibilidade/faixas-horarios', [DisponibilidadeController::class, 'indexFaixas']);
        Route::post('/disponibilidade/faixas-horarios', [DisponibilidadeController::class, 'storeFaixa']);
        Route::delete('/disponibilidade/faixas-horarios/{faixa}', [DisponibilidadeController::class, 'destroyFaixa']);
        Route::get('/disponibilidade/bloqueios', [DisponibilidadeController::class, 'indexBloqueios']);
        Route::post('/disponibilidade/bloqueios', [DisponibilidadeController::class, 'storeBloqueio']);
        Route::delete('/disponibilidade/bloqueios/{bloqueio}', [DisponibilidadeController::class, 'destroyBloqueio']);
    });

    // --- Rotas Exclusivas para Coletor ---
    Route::middleware('role:coletor')->group(function () {
        Route::get('/minhas-coletas', [ColetaController::class, 'minhasColetas']);
        Route::post('/ofertas/{oferta}/reservar', [ColetaController::class, 'reservar']);
        Route::post('/coletas/{coleta}/confirmar-coletor', [ColetaController::class, 'confirmarColetor']);
        Route::get('/ofertas/{oferta}/slots-disponiveis', [DisponibilidadeController::class, 'slotsDisponiveis']);
    });
});

