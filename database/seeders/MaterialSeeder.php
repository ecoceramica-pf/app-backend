<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materiais = [
            [
                'nome' => 'Cerâmica',
                'descricao' => 'Restos e cacos de produtos cerâmicos em geral.',
                'ativo' => true,
                'cortante' => true,
            ],
            [
                'nome' => 'Gesso',
                'descricao' => 'Moldes descartados e restos de gesso da produção.',
                'ativo' => true,
                'cortante' => false,
            ],
            [
                'nome' => 'Barro',
                'descricao' => 'Sobras de argila e barro cru ou em processamento.',
                'ativo' => true,
                'cortante' => false,
            ],
            [
                'nome' => 'Vidro',
                'descricao' => 'Cacos de vidro provenientes de esmaltação ou outras etapas.',
                'ativo' => true,
                'cortante' => true,
            ]
        ];

        foreach ($materiais as $material) {
            Material::firstOrCreate(
                ['nome' => $material['nome']],
                $material
            );
        }
    }
}
