<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE coletas MODIFY COLUMN status ENUM('pendente', 'agendado', 'concluido', 'cancelado', 'recusado') NOT NULL DEFAULT 'pendente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE coletas MODIFY COLUMN status ENUM('pendente', 'concluido', 'cancelado') NOT NULL DEFAULT 'pendente'");
    }
};
