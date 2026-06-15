<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use App\Models\Coleta;
use Illuminate\Http\Request;
use App\Enums\TipoPerfil;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function impacto()
    {
        $dados = \Illuminate\Support\Facades\Cache::remember('dashboard_impacto', 3600, function () {
            $totalKg = OfertaResiduo::where('status', 'concluido')->sum('quantidade_kg');
            $totalCacamba = OfertaResiduo::where('status', 'concluido')->sum('quantidade_cacamba');

            return [
                'total_reaproveitado_kg' => (float) $totalKg,
                'total_reaproveitado_cacamba' => (int) $totalCacamba,
            ];
        });

        return response()->json($dados);
    }

    public function meuImpacto(Request $request)
    {
        $user = $request->user();
        $isColetor = $user->tipo_perfil === TipoPerfil::Coletor;

        if ($isColetor) {
            $baseQuery = Coleta::query()
                ->where('coletas.coletor_id', $user->id)
                ->where('coletas.status', 'concluido')
                ->join('ofertas_residuos', 'coletas.oferta_residuo_id', '=', 'ofertas_residuos.id')
                ->join('materiais', 'ofertas_residuos.material_id', '=', 'materiais.id');

            $total_kg = (float) (clone $baseQuery)->sum('ofertas_residuos.quantidade_kg');
            $total_cacambas = (int) (clone $baseQuery)->sum('ofertas_residuos.quantidade_cacamba');
            $total_concluidas = Coleta::where('coletor_id', $user->id)->where('status', 'concluido')->count();

            $por_mes_raw = (clone $baseQuery)
                ->selectRaw('DATE_FORMAT(COALESCE(coletas.data_conclusao, coletas.updated_at), "%Y-%m") as mes, SUM(ofertas_residuos.quantidade_kg) as total')
                ->groupBy('mes')
                ->pluck('total', 'mes');

            $por_material_raw = (clone $baseQuery)
                ->selectRaw('materiais.nome as material, SUM(ofertas_residuos.quantidade_kg) as total')
                ->groupBy('materiais.nome')
                ->pluck('total', 'material');

        } else {
            $baseQuery = OfertaResiduo::query()
                ->where('ofertas_residuos.user_id', $user->id)
                ->where('ofertas_residuos.status', 'concluido')
                ->join('materiais', 'ofertas_residuos.material_id', '=', 'materiais.id');

            $total_kg = (float) (clone $baseQuery)->sum('ofertas_residuos.quantidade_kg');
            $total_cacambas = (int) (clone $baseQuery)->sum('ofertas_residuos.quantidade_cacamba');
            $total_concluidas = OfertaResiduo::where('user_id', $user->id)->where('status', 'concluido')->count();

            $por_mes_raw = (clone $baseQuery)
                ->selectRaw('DATE_FORMAT(ofertas_residuos.updated_at, "%Y-%m") as mes, SUM(ofertas_residuos.quantidade_kg) as total')
                ->groupBy('mes')
                ->pluck('total', 'mes');

            $por_material_raw = (clone $baseQuery)
                ->selectRaw('materiais.nome as material, SUM(ofertas_residuos.quantidade_kg) as total')
                ->groupBy('materiais.nome')
                ->pluck('total', 'material');
        }

        $meses = [];
        foreach ($por_mes_raw as $mes => $kg) {
            $meses[] = ['label' => $mes, 'kg' => (float) $kg];
        }
        usort($meses, fn($a, $b) => strcmp($a['label'], $b['label']));

        $materiais = [];
        foreach ($por_material_raw as $mat => $kg) {
            $materiais[] = ['label' => $mat, 'kg' => (float) $kg];
        }
        usort($materiais, fn($a, $b) => $b['kg'] <=> $a['kg']);

        return response()->json([
            'total_kg' => $total_kg,
            'total_cacambas' => $total_cacambas,
            'total_concluidas' => $total_concluidas,
            'historico_meses' => $meses,
            'historico_materiais' => $materiais,
        ]);
    }
}
