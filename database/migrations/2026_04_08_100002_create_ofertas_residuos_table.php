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
        Schema::create('ofertas_residuos', function (Blueprint $table) {
            $table->id()->comment('Identificador único da oferta');
            $table->uuid('uuid')->unique()->comment('Identificador universal único da oferta');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->comment('Fábrica que disponibilizou o resíduo');
            $table->foreignId('endereco_id')->constrained('enderecos')->onDelete('restrict')->comment('Localização de retirada da oferta');
            $table->foreignId('material_id')->constrained('materiais')->onDelete('restrict')->comment('Tipo do material ofertado');
            $table->decimal('quantidade_kg', 10, 2)->nullable()->comment('Quantidade ofertada em quilos');
            $table->integer('quantidade_cacamba')->nullable()->comment('Quantidade ofertada em caçambas');
            $table->dateTime('data_publicacao')->comment('Data de publicação da oferta no sistema');
            $table->enum('status', ['disponivel', 'em processo', 'concluido', 'cancelado'])->default('disponivel')->comment('Status atual do andamento da oferta');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ofertas_residuos');
    }
};
