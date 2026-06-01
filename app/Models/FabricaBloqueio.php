<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FabricaBloqueio extends Model
{
    use HasFactory;

    protected $table = 'fabrica_bloqueios';

    protected $fillable = [
        'disponibilidade_id',
        'data_bloqueio',
        'motivo',
    ];

    protected $casts = [
        'data_bloqueio' => 'date',
    ];

    public function disponibilidade()
    {
        return $this->belongsTo(FabricaDisponibilidade::class, 'disponibilidade_id');
    }
}
