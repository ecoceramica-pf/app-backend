<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Listar usuários (com opção de filtros)
     */
    public function index(Request $request)
    {
        $query = User::withTrashed();

        // Filtro por tipo de perfil
        if ($request->has('tipo_perfil')) {
            $query->where('tipo_perfil', $request->query('tipo_perfil'));
        }

        // Filtro por status (ativo / inativo)
        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'inativo') {
                $query->onlyTrashed();
            } elseif ($status === 'ativo') {
                $query->whereNull('deleted_at');
            }
        }

        // Paginamos os resultados para não sobrecarregar
        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return $this->success([
            'data' => UserResource::collection($users->items()),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'total' => $users->total()
        ]);
    }

    /**
     * Retornar detalhes de um usuário
     */
    public function show($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return $this->error('Usuário não encontrado', 404);
        }

        return $this->success(new UserResource($user));
    }

    /**
     * Ativar ou Inativar (Banir) um usuário
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'ativo' => 'required|boolean'
        ]);

        $user = User::withTrashed()->find($id);

        if (!$user) {
            return $this->error('Usuário não encontrado', 404);
        }

        // Previne inativar a si mesmo ou outro admin se quiser adicionar camada de segurança
        if ($user->id === $request->user()->id) {
            return $this->error('Você não pode alterar o status da sua própria conta', 403);
        }

        if ($request->input('ativo')) {
            // Reativa a conta
            if ($user->trashed()) {
                $user->restore();
            }
        } else {
            // Inativa (bane) a conta
            if (!$user->trashed()) {
                $user->delete();
            }
        }

        // Busca o usuário atualizado (com withTrashed para garantir que seja retornado se inativado)
        $userAtualizado = User::withTrashed()->find($id);

        $msg = $request->input('ativo') ? 'Usuário ativado com sucesso.' : 'Usuário inativado com sucesso.';

        return $this->success(new UserResource($userAtualizado), $msg);
    }
}
