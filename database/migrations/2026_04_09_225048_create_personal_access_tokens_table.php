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
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id()->comment('Identificador único do token');
            $table->morphs('tokenable');
            $table->text('name')->comment('Nome de identificação do token');
            $table->string('token', 64)->unique()->comment('Hash do token de acesso pessoal');
            $table->text('abilities')->nullable()->comment('Permissões concedidas ao token');
            $table->timestamp('last_used_at')->nullable()->comment('Última vez que o token foi utilizado');
            $table->timestamp('expires_at')->nullable()->index()->comment('Data limite de validade do token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
