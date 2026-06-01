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
        Schema::table('enderecos', function (Blueprint $table) {
            $table->string('estado', 2)->nullable()->after('cidade')->comment('Sigla do estado (UF)');
            $table->string('cep', 10)->nullable()->after('estado')->comment('CEP do endereço');
            $table->string('complemento', 100)->nullable()->after('cep')->comment('Complemento do endereço');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enderecos', function (Blueprint $table) {
            $table->dropColumn(['estado', 'cep', 'complemento']);
        });
    }
};
