<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\DuracaoColeta;

class FabricaDisponibilidade extends Model
{
    use HasFactory;

    protected $table = 'fabrica_disponibilidades';

    protected $fillable = [
        'user_id',
        'dias_semana',
        'duracao_coleta_min',
        'antecedencia_minima_dias',
        'max_coletas_dia',
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'duracao_coleta_min' => DuracaoColeta::class,
        'antecedencia_minima_dias' => 'integer',
        'max_coletas_dia' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faixasHorarios()
    {
        return $this->hasMany(FabricaFaixaHorario::class, 'disponibilidade_id');
    }

    public function bloqueios()
    {
        return $this->hasMany(FabricaBloqueio::class, 'disponibilidade_id');
    }

    /**
     * Verifica se a duração é "dia inteiro" (NULL).
     */
    public function isDiaInteiro(): bool
    {
        return is_null($this->duracao_coleta_min) || $this->faixasHorarios->isEmpty();
    }
}
