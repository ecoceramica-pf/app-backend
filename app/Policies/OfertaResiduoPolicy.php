<?php

namespace App\Policies;

use App\Models\OfertaResiduo;
use App\Models\User;
use App\Enums\OfertaStatus;
use Illuminate\Auth\Access\Response;

class OfertaResiduoPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OfertaResiduo $ofertaResiduo): Response
    {
        if ($user->id !== $ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado.');
        }

        if ($ofertaResiduo->status !== OfertaStatus::Disponivel) {
            return Response::deny('Não é possível alterar uma oferta que já está em processo ou concluída.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OfertaResiduo $ofertaResiduo): Response
    {
        if ($user->id !== $ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado.');
        }

        if ($ofertaResiduo->status !== OfertaStatus::Disponivel) {
            return Response::deny('Não é possível excluir uma oferta que já está em processo ou concluída.');
        }

        return Response::allow();
    }
}
