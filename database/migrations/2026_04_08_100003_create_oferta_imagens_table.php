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
        Schema::create('oferta_imagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_residuo_id')->constrained('ofertas_residuos')->onDelete('cascade');
            $table->string('imagem', 500);
            $table->bigInteger('tamanho_arquivo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta_imagens');
    }
};
