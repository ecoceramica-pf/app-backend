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
            $table->id();
            $table->foreignId('oferta_residuo_id')->unique()->constrained('ofertas_residuos')->onDelete('cascade');
            $table->foreignId('coletor_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('data_reserva');
            $table->dateTime('data_conclusao')->nullable();
            $table->dateTime('confirmacao_fabrica')->nullable();
            $table->dateTime('confirmacao_coletor')->nullable();
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
