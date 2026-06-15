<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\OfertaStatus;

class OfertaResiduo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ofertas_residuos';

    protected $fillable = [
        'uuid',
        'user_id',
        'endereco_id',
        'material_id',
        'quantidade_kg',
        'quantidade_cacamba',
        'observacoes',
        'data_publicacao',
        'status'
    ];

    protected $casts = [
        'data_publicacao' => 'datetime',
        'status' => OfertaStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function ofertaImagens()
    {
        return $this->hasMany(OfertaImagem::class);
    }

    public function coletas()
    {
        return $this->hasMany(Coleta::class);
    }

    public function coleta()
    {
        return $this->hasOne(Coleta::class)
            ->orderByRaw("CASE WHEN status IN ('pendente', 'agendado') THEN 0 ELSE 1 END")
            ->latest();
    }

    protected static function booted()
    {
        static::saved(function ($oferta) {
            if ($oferta->status === OfertaStatus::Concluido || $oferta->getOriginal('status') === OfertaStatus::Concluido) {
                \Illuminate\Support\Facades\Cache::forget('dashboard_impacto');
            }
        });

        static::deleted(function ($oferta) {
            if ($oferta->status === OfertaStatus::Concluido) {
                \Illuminate\Support\Facades\Cache::forget('dashboard_impacto');
            }
        });

        static::restored(function ($oferta) {
            if ($oferta->status === OfertaStatus::Concluido) {
                \Illuminate\Support\Facades\Cache::forget('dashboard_impacto');
            }
        });
    }
}
