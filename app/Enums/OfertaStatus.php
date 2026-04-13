<?php

namespace App\Enums;

enum OfertaStatus: string
{
    case Disponivel = 'disponivel';
    case EmProcesso = 'em processo';
    case Concluido = 'concluido';
    case Cancelado = 'cancelado';
}
