<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coleta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'oferta_residuo_id',
        'coletor_id',
        'status',
        'data_reserva',
        'data_agendamento',
        'data_conclusao',
        'confirmacao_fabrica',
        'confirmacao_coletor',
        'observacoes'
    ];

    protected $casts = [
        'data_reserva' => 'datetime',
        'data_agendamento' => 'datetime',
        'data_conclusao' => 'datetime',
        'confirmacao_fabrica' => 'datetime',
        'confirmacao_coletor' => 'datetime',
    ];

    public function ofertaResiduo()
    {
        return $this->belongsTo(OfertaResiduo::class);
    }

    public function coletor()
    {
        return $this->belongsTo(User::class, 'coletor_id');
    }
}
