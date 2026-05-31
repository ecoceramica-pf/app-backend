<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment('Identificador único do usuário');
            $table->string('nome', 120)->comment('Nome completo do usuário ou razão social da fábrica');
            $table->string('email', 150)->unique()->comment('Endereço de e-mail do usuário para login e contato');
            $table->timestamp('email_verified_at')->nullable()->comment('Data e hora em que o e-mail foi verificado');
            $table->string('password')->comment('Senha criptografada do usuário'); // senha
            $table->enum('tipo_perfil', ['fabrica', 'coletor', 'admin'])->comment('Tipo de perfil do usuário no sistema');
            $table->string('documento', 20)->comment('CPF ou CNPJ do usuário');
            $table->string('telefone', 20)->comment('Número de telefone ou celular para contato');
            $table->rememberToken()->comment('Token para manter a sessão do usuário ativa');
            $table->timestamps();
            $table->softDeletes(); // excluido_em
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('E-mail do usuário solicitando a redefinição');
            $table->string('token')->comment('Token único gerado para a redefinição de senha');
            $table->timestamp('created_at')->nullable()->comment('Data e hora em que o token foi criado');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Identificador único da sessão');
            $table->foreignId('user_id')->nullable()->index()->comment('ID do usuário associado à sessão');
            $table->string('ip_address', 45)->nullable()->comment('Endereço IP de origem da sessão');
            $table->text('user_agent')->nullable()->comment('Informações do navegador/dispositivo (User-Agent)');
            $table->longText('payload')->comment('Dados da sessão serializados');
            $table->integer('last_activity')->index()->comment('Timestamp da última atividade na sessão');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
