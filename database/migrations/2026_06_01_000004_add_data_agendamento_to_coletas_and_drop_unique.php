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
        Schema::table('coletas', function (Blueprint $table) {
            $table->dateTime('data_agendamento')->nullable()->after('data_reserva')->comment('Data e horário agendado pelo coletor para realizar a coleta');

            // Remover unique que impede re-reserva após cancelamento
            // Por limitação do MySQL (Error 1553), precisamos remover as FKs primeiro, dropar o índice, e depois recriar as FKs.
            $table->dropForeign(['coletor_id']);
            $table->dropForeign(['oferta_residuo_id']);
            $table->dropUnique(['coletor_id', 'oferta_residuo_id']);
            
            $table->foreign('coletor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('oferta_residuo_id')->references('id')->on('ofertas_residuos')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coletas', function (Blueprint $table) {
            $table->dropColumn('data_agendamento');
            
            $table->dropForeign(['coletor_id']);
            $table->dropForeign(['oferta_residuo_id']);
            
            $table->unique(['coletor_id', 'oferta_residuo_id']);
            
            $table->foreign('coletor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('oferta_residuo_id')->references('id')->on('ofertas_residuos')->onDelete('restrict');
        });
    }
};
