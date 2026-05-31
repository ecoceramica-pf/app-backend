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
        Schema::create('materiais', function (Blueprint $table) {
            $table->id()->comment('Identificador único do tipo de material');
            $table->string('nome', 100)->comment('Nome do material');
            $table->string('descricao', 120)->comment('Descrição detalhada do material');
            $table->boolean('ativo')->default(true)->comment('Indica se o material está ativo para uso');
            $table->boolean('cortante')->default(false)->comment('Sinaliza se o material oferece risco de corte');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiais');
    }
};
