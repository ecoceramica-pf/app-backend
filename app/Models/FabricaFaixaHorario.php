<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricaFaixaHorario extends Model
{
    use HasFactory;

    protected $table = 'fabrica_faixas_horarios';

    protected $fillable = [
        'disponibilidade_id',
        'hora_inicio',
        'hora_fim',
    ];

    public function disponibilidade()
    {
        return $this->belongsTo(FabricaDisponibilidade::class, 'disponibilidade_id');
    }
}
