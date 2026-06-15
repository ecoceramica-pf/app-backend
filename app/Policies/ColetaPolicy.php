<?php

namespace App\Policies;

use App\Models\Coleta;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Enums\TipoPerfil;

class ColetaPolicy
{
    /**
     * Determine whether the user can cancel the model.
     */
    public function cancelar(User $user, Coleta $coleta): Response
    {
        if ($user->id !== $coleta->coletor_id && $user->id !== $coleta->ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado. Apenas o coletor ou a fábrica podem cancelar a coleta.');
        }

        if (in_array($coleta->status, ['cancelado', 'recusado', 'concluido'])) {
            return Response::deny('Não é possível cancelar uma coleta já finalizada (concluída, recusada ou cancelada).');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can confirm as coletor.
     */
    public function confirmarColetor(User $user, Coleta $coleta): Response
    {
        if ($user->tipo_perfil !== TipoPerfil::Coletor || $user->id !== $coleta->coletor_id) {
            return Response::deny('Acesso não autorizado.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can confirm as fabrica (owner of the offer).
     */
    public function confirmarFabrica(User $user, Coleta $coleta): Response
    {
        if ($user->tipo_perfil !== TipoPerfil::Fabrica || $user->id !== $coleta->ofertaResiduo->user_id) {
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
        if ($user->tipo_perfil !== TipoPerfil::Fabrica || $user->id !== $coleta->ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado. Apenas o criador da oferta pode aprovar.');
        }

        return Response::allow();
    }

    public function recusar(User $user, Coleta $coleta): Response
    {
        if ($user->tipo_perfil !== TipoPerfil::Fabrica || $user->id !== $coleta->ofertaResiduo->user_id) {
            return Response::deny('Acesso não autorizado. Apenas o criador da oferta pode recusar.');
        }

        return Response::allow();
    }
}
