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
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('endereco_id')->constrained('enderecos')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materiais')->onDelete('cascade');
            $table->decimal('quantidade_kg', 10, 2)->nullable();
            $table->integer('quantidade_cacamba')->nullable();
            $table->dateTime('data_publicacao');
            $table->enum('status', ['disponivel', 'em processo', 'concluido', 'cancelado'])->default('disponivel');
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
