<?php

namespace App\Policies;

use App\Models\Coleta;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ColetaPolicy
{
    /**
     * Determine whether the user can cancel the model.
     */
    public function cancelar(User $user, Coleta $coleta): Response
    {
        if ($user->id !== $coleta->coletor_id) {
            return Response::deny('Acesso não autorizado.');
        }

        if ($coleta->data_conclusao) {
            return Response::deny('Não é possível cancelar uma coleta já concluída.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can confirm as coletor.
     */
    public function confirmarColetor(User $user, Coleta $coleta): Response
    {
        if ($user->id !== $coleta->coletor_id) {
            return Response::deny('Acesso não autorizado.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can confirm as fabrica (owner of the offer).
     */
    public function confirmarFabrica(User $user, Coleta $coleta): Response
    {
        if ($user->id !== $coleta->ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado. Apenas o criador da oferta pode confirmar.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can view the coleta.
     */
    public function view(User $user, Coleta $coleta): Response
    {
        if ($user->id === $coleta->coletor_id || $user->id === $coleta->ofertaResiduo->user_id) {
            return Response::allow();
        }

        return Response::deny('Acesso não autorizado.');
    }

    public function aprovar(User $user, Coleta $coleta): Response
    {
        if ($user->id !== $coleta->ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado. Apenas o criador da oferta pode aprovar.');
        }

        return Response::allow();
    }

    public function recusar(User $user, Coleta $coleta): Response
    {
        if ($user->id !== $coleta->ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado. Apenas o criador da oferta pode recusar.');
        }

        return Response::allow();
    }
}
