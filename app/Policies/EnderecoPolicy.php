<?php

namespace App\Policies;

use App\Models\Endereco;
use App\Models\User;

class EnderecoPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Endereco $endereco): bool
    {
        return $user->id === $endereco->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Endereco $endereco): bool
    {
        return $user->id === $endereco->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Endereco $endereco): bool
    {
        return $user->id === $endereco->user_id;
    }
}
