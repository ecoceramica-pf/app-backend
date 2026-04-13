<?php

namespace App\Enums;

enum TipoPerfil: string
{
    case Fabrica = 'fabrica';
    case Coletor = 'coletor';
    case Admin = 'admin';
}
