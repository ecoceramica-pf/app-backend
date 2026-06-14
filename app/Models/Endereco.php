<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'complemento',
        'localizacao'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Intercepta a leitura da coluna 'localizacao' (que é um POINT binário no MySQL)
     * e converte para um array [longitude, latitude].
     */
    protected function localizacao(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function ($value) {
                if (empty($value)) return null;
                
                // Se por algum motivo já for um array (ex: durante atribuição em memória)
                if (is_array($value)) return $value;

                try {
                    // O formato interno do MySQL para POINT é: 
                    // 4 bytes SRID + 1 byte ByteOrder + 4 bytes Type + 8 bytes X + 8 bytes Y
                    $unpacked = unpack('VSRID/CByteOrder/VType/elongitude/elatitude', $value);
                    
                    if ($unpacked !== false && isset($unpacked['longitude']) && isset($unpacked['latitude'])) {
                        return [
                            $unpacked['longitude'],
                            $unpacked['latitude']
                        ];
                    }
                } catch (\Exception $e) {
                    // Ignora silenciosamente em caso de erro no unpack
                }

                return null;
            }
        );
    }

    public function ofertasResiduos()
    {
        return $this->hasMany(OfertaResiduo::class);
    }
}
