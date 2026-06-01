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
        Schema::create('fabrica_bloqueios', function (Blueprint $table) {
            $table->id()->comment('Identificador único do bloqueio');
            $table->foreignId('disponibilidade_id')->constrained('fabrica_disponibilidades')->onDelete('restrict')->comment('Configuração de disponibilidade à qual este bloqueio pertence');
            $table->date('data_bloqueio')->comment('Data específica bloqueada (ex: feriado, férias)');
            $table->string('motivo', 150)->nullable()->comment('Motivo do bloqueio (ex: Feriado de Natal)');
            $table->timestamps();

            $table->unique(['disponibilidade_id', 'data_bloqueio'], 'bloqueio_data_unica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabrica_bloqueios');
    }
};
