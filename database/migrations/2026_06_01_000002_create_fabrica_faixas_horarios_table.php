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
        Schema::create('fabrica_faixas_horarios', function (Blueprint $table) {
            $table->id()->comment('Identificador único da faixa de horário');
            $table->foreignId('disponibilidade_id')->constrained('fabrica_disponibilidades')->onDelete('restrict')->comment('Configuração de disponibilidade à qual esta faixa pertence');
            $table->time('hora_inicio')->comment('Horário de início da faixa (ex: 08:00)');
            $table->time('hora_fim')->comment('Horário de fim da faixa (ex: 12:00)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabrica_faixas_horarios');
    }
};
