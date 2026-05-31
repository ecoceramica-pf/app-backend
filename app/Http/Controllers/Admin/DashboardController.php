<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OfertaResiduo;
use App\Models\Coleta;

class DashboardController extends Controller
{
    /**
     * Métricas consolidadas para o painel administrativo
     */
    public function metricas()
    {
        return $this->success([
            'usuarios' => [
                'total' => User::count(),
                'fabricas' => User::where('tipo_perfil', 'fabrica')->count(),
                'coletores' => User::where('tipo_perfil', 'coletor')->count(),
                'admins' => User::where('tipo_perfil', 'admin')->count(),
                'inativos' => User::onlyTrashed()->count(),
            ],
            'ofertas' => [
                'total' => OfertaResiduo::count(),
                'disponiveis' => OfertaResiduo::where('status', 'disponivel')->count(),
                'em_processo' => OfertaResiduo::where('status', 'em processo')->count(),
                'concluidas' => OfertaResiduo::where('status', 'concluido')->count(),
                'moderadas' => OfertaResiduo::onlyTrashed()->count(),
            ],
            'coletas' => [
                'total' => Coleta::count(),
                'pendentes' => Coleta::where('status', 'pendente')->count(),
                'concluidas' => Coleta::where('status', 'concluido')->count(),
                'canceladas' => Coleta::where('status', 'cancelado')->count(),
            ],
            'impacto' => [
                'total_kg' => (float) OfertaResiduo::where('status', 'concluido')->sum('quantidade_kg'),
                'total_cacambas' => (int) OfertaResiduo::where('status', 'concluido')->sum('quantidade_cacamba'),
            ],
        ]);
    }
}
