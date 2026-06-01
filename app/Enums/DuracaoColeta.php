<?php

namespace App\Enums;

enum DuracaoColeta: int
{
    case TrintaMinutos = 30;
    case UmaHora = 60;
    case DuasHoras = 120;
    // O valor null representa "dia inteiro" e será tratado na lógica,
    // mas se precisar de uma representação aqui, não podemos usar null em um backed enum.
    // Portanto, o "dia inteiro" não precisa estar no enum se formos usar null na coluna do BD.
}
