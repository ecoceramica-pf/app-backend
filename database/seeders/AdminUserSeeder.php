<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\TipoPerfil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Str::random(12);

        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'nome' => 'Administrador',
                'password' => $password,
                'tipo_perfil' => TipoPerfil::Admin,
                'documento' => '00000000000',
                'telefone' => '00000000000',
            ]
        );

        $this->command->info('Usuário Administrador (Admin) configurado com sucesso!');
        $this->command->info('E-mail: admin@admin.com');
        $this->command->warn('Senha (guarde em um local seguro): ' . $password);
    }
}
