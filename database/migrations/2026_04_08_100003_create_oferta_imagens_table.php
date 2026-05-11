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
            $table->id()->comment('Identificador único da imagem');
            $table->foreignId('oferta_residuo_id')->constrained('ofertas_residuos')->onDelete('cascade')->comment('Oferta a qual a imagem pertence');
            $table->string('imagem', 500)->comment('URL ou caminho do arquivo de imagem');
            $table->bigInteger('tamanho_arquivo')->comment('Tamanho da imagem em bytes');
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
