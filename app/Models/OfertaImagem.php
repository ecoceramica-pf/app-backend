<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfertaImagem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'oferta_imagens';

    protected $fillable = [
        'oferta_residuo_id',
        'imagem',
        'tamanho_arquivo'
    ];

    public function ofertaResiduo()
    {
        return $this->belongsTo(OfertaResiduo::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($imagem) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($imagem->imagem)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagem->imagem);
            }
        });
    }
}
