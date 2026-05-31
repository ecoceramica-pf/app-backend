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
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id()->comment('Identificador único do endereço');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('ID do usuário proprietário do endereço');
            $table->string('logradouro', 150)->comment('Nome da rua, avenida, etc.');
            $table->string('numero', 20)->comment('Número do imóvel');
            $table->string('bairro', 80)->comment('Nome do bairro');
            $table->string('cidade', 80)->comment('Nome da cidade');
            // $table->geometry('localizacao', subtype: 'point', srid: 4326)->nullable()->comment('Coordenadas geográficas do endereço');
            $table->geometry('localizacao', subtype: 'point')->nullable()->comment('Coordenadas geográficas do endereço');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
