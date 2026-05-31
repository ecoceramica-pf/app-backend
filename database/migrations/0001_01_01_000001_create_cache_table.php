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
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary()->comment('Chave de identificação do registro no cache');
            $table->mediumText('value')->comment('Valor serializado armazenado em cache');
            $table->bigInteger('expiration')->index()->comment('Timestamp indicando a validade do cache');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary()->comment('Chave de identificação do bloqueio');
            $table->string('owner')->comment('Identificador do proprietário do bloqueio');
            $table->bigInteger('expiration')->index()->comment('Timestamp indicando a validade do bloqueio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
