<?php

namespace App\Http\Controllers;

use App\Models\OfertaResiduo;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function impacto()
    {
        $totalKg = OfertaResiduo::where('status', 'concluido')->sum('quantidade_kg');
        $totalCacamba = OfertaResiduo::where('status', 'concluido')->sum('quantidade_cacamba');

        return response()->json([
            'total_reaproveitado_kg' => (float) $totalKg,
            'total_reaproveitado_cacamba' => (int) $totalCacamba,
        ]);
    }
}
