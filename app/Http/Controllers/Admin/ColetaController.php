<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coleta;
use Illuminate\Http\Request;
use App\Http\Resources\ColetaResource;

class ColetaController extends Controller
{
    /**
     * Listar todas as coletas (visão global para auditoria)
     */
    public function index(Request $request)
    {
        $query = Coleta::withTrashed()
            ->with(['coletor', 'ofertaResiduo.user', 'ofertaResiduo.material']);

        // Filtro por status da coleta
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $coletas = $query->orderBy('created_at', 'desc')->paginate(20);

        return $this->success([
            'data' => ColetaResource::collection($coletas->items()),
            'current_page' => $coletas->currentPage(),
            'last_page' => $coletas->lastPage(),
            'total' => $coletas->total()
        ]);
    }

    /**
     * Detalhar uma coleta específica
     */
    public function show($id)
    {
        $coleta = Coleta::withTrashed()
            ->with([
                'coletor',
                'ofertaResiduo.user',
                'ofertaResiduo.material',
                'ofertaResiduo.endereco',
                'ofertaResiduo.ofertaImagens'
            ])
            ->find($id);

        if (!$coleta) {
            return $this->error('Coleta não encontrada', 404);
        }

        return $this->success(new ColetaResource($coleta));
    }
}
