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
        Schema::create('fabrica_disponibilidades', function (Blueprint $table) {
            $table->id()->comment('Identificador único da configuração de disponibilidade');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('restrict')->comment('Fábrica dona desta configuração (1:1)');
            $table->json('dias_semana')->comment('Dias da semana disponíveis [0=dom, 1=seg, ..., 6=sab]');
            $table->unsignedSmallInteger('duracao_coleta_min')->nullable()->comment('Duração da coleta em minutos (30, 60, 120). NULL = dia inteiro');
            $table->unsignedSmallInteger('antecedencia_minima_dias')->default(1)->comment('Dias mínimos de antecedência (0 = mesmo dia)');
            $table->unsignedSmallInteger('max_coletas_dia')->default(5)->comment('Quantidade máxima de coletas que a fábrica aceita por dia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabrica_disponibilidades');
    }
};
