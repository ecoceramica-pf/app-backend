<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfertaResiduo;
use Illuminate\Http\Request;
use App\Http\Resources\OfertaResiduoResource;

class OfertaController extends Controller
{
    /**
     * Listar todas as ofertas (ativas e inativas)
     */
    public function index(Request $request)
    {
        $query = OfertaResiduo::withTrashed()->with(['user', 'material']);

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('material_id')) {
            $query->where('material_id', $request->query('material_id'));
        }

        // Filtro por ofertas moderadas (deletadas)
        if ($request->has('moderadas')) {
            if ($request->query('moderadas') == 'true') {
                $query->onlyTrashed();
            }
        }

        $ofertas = $query->orderBy('created_at', 'desc')->paginate(20);

        return $this->success([
            'data' => OfertaResiduoResource::collection($ofertas->items()),
            'current_page' => $ofertas->currentPage(),
            'last_page' => $ofertas->lastPage(),
            'total' => $ofertas->total()
        ]);
    }

    /**
     * Retornar detalhes de uma oferta
     */
    public function show($id)
    {
        $oferta = OfertaResiduo::withTrashed()
            ->with(['user', 'endereco', 'material', 'ofertaImagens'])
            ->find($id);

        if (!$oferta) {
            return $this->error('Oferta não encontrada', 404);
        }

        return $this->success(new OfertaResiduoResource($oferta));
    }

    /**
     * Excluir (Moderar) uma oferta
     */
    public function destroy($id)
    {
        $oferta = OfertaResiduo::withTrashed()->find($id);

        if (!$oferta) {
            return $this->error('Oferta não encontrada', 404);
        }

        if ($oferta->trashed()) {
            return $this->error('Esta oferta já foi excluída/moderada.', 400);
        }

        $oferta->delete();

        return $this->success(null, 'Oferta moderada (removida) com sucesso.');
    }

    /**
     * Restaurar uma oferta moderada
     */
    public function restore($id)
    {
        $oferta = OfertaResiduo::withTrashed()->find($id);

        if (!$oferta) {
            return $this->error('Oferta não encontrada', 404);
        }

        if (!$oferta->trashed()) {
            return $this->error('Esta oferta não está excluída.', 400);
        }

        $oferta->restore();

        return $this->success(null, 'Oferta restaurada com sucesso.');
    }
}
