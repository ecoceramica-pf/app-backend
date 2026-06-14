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

    // Ofertas
    Route::get('/ofertas', [OfertaResiduoController::class, 'index']);
    Route::post('/ofertas', [OfertaResiduoController::class, 'store']);
    Route::post('/ofertas/{uuid}/imagens', [OfertaImagemController::class, 'store']);
    Route::delete('/ofertas/imagens/{imagem}', [OfertaImagemController::class, 'destroy']);
    Route::get('/ofertas/{oferta}', [OfertaResiduoController::class, 'show']);
    Route::put('/ofertas/{oferta}', [OfertaResiduoController::class, 'update']);
    Route::delete('/ofertas/{oferta}', [OfertaResiduoController::class, 'destroy']);
    Route::patch('/ofertas/{oferta}/status', [OfertaResiduoController::class, 'alterarStatus']);
    Route::get('/minhas-ofertas', [OfertaResiduoController::class, 'minhasOfertas']);

    // Coletas
    Route::get('/minhas-coletas', [ColetaController::class, 'minhasColetas']);
    Route::get('/coletas/{coleta}', [ColetaController::class, 'show']);
    Route::post('/ofertas/{oferta}/reservar', [ColetaController::class, 'reservar']);
    Route::post('/coletas/{coleta}/cancelar', [ColetaController::class, 'cancelar']);
    Route::post('/coletas/{coleta}/aprovar', [ColetaController::class, 'aprovar']);
    Route::post('/coletas/{coleta}/recusar', [ColetaController::class, 'recusar']);
    Route::post('/coletas/{coleta}/confirmar-fabrica', [ColetaController::class, 'confirmarFabrica']);
    Route::post('/coletas/{coleta}/confirmar-coletor', [ColetaController::class, 'confirmarColetor']);

    // Disponibilidade da Fábrica
    Route::get('/disponibilidade', [DisponibilidadeController::class, 'show']);
    Route::put('/disponibilidade', [DisponibilidadeController::class, 'upsert']);
    Route::get('/disponibilidade/faixas-horarios', [DisponibilidadeController::class, 'indexFaixas']);
    Route::post('/disponibilidade/faixas-horarios', [DisponibilidadeController::class, 'storeFaixa']);
    Route::delete('/disponibilidade/faixas-horarios/{faixa}', [DisponibilidadeController::class, 'destroyFaixa']);
    Route::get('/disponibilidade/bloqueios', [DisponibilidadeController::class, 'indexBloqueios']);
    Route::post('/disponibilidade/bloqueios', [DisponibilidadeController::class, 'storeBloqueio']);
    Route::delete('/disponibilidade/bloqueios/{bloqueio}', [DisponibilidadeController::class, 'destroyBloqueio']);

    // Slots disponíveis para agendamento (coletor)
    Route::get('/ofertas/{oferta}/slots-disponiveis', [DisponibilidadeController::class, 'slotsDisponiveis']);
});

