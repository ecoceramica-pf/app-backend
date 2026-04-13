<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiais';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
        'cortante'
    ];

    public function ofertasResiduos()
    {
        return $this->hasMany(OfertaResiduo::class);
    }
}
