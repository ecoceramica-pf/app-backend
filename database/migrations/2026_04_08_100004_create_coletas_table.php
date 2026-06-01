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
        Schema::create('coletas', function (Blueprint $table) {
            $table->id()->comment('Identificador único da coleta');
            $table->foreignId('oferta_residuo_id')->constrained('ofertas_residuos')->onDelete('restrict')->comment('Oferta de resíduo que está sendo coletada');
            $table->foreignId('coletor_id')->constrained('users')->onDelete('restrict')->comment('Usuário coletor responsável');
            $table->unique(['coletor_id', 'oferta_residuo_id']);
            $table->dateTime('data_reserva')->comment('Data e hora em que a reserva da coleta foi feita');
            $table->dateTime('data_conclusao')->nullable()->comment('Data e hora em que a coleta foi finalizada fisicamente');
            $table->dateTime('confirmacao_fabrica')->nullable()->comment('Data de confirmação de entrega do resíduo pela fábrica');
            $table->dateTime('confirmacao_coletor')->nullable()->comment('Data de confirmação do recolhimento pelo coletor');
            $table->enum('status', ['pendente', 'concluido', 'cancelado'])->default('pendente')->comment('Status atual da transação de coleta');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coletas');
    }
};
