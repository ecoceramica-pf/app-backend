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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id()->comment('Identificador único do job');
            $table->string('queue')->index()->comment('Nome da fila que processará o job');
            $table->longText('payload')->comment('Dados necessários para execução do job');
            $table->unsignedTinyInteger('attempts')->comment('Contador de tentativas de execução');
            $table->unsignedInteger('reserved_at')->nullable()->comment('Data e hora que o job foi reservado para processamento');
            $table->unsignedInteger('available_at')->comment('Data e hora que o job estará pronto para processamento');
            $table->unsignedInteger('created_at')->comment('Data e hora de criação do job');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Identificador único do lote de jobs');
            $table->string('name')->comment('Nome atribuído ao lote');
            $table->integer('total_jobs')->comment('Número total de jobs no lote');
            $table->integer('pending_jobs')->comment('Número de jobs aguardando processamento');
            $table->integer('failed_jobs')->comment('Número de jobs que falharam');
            $table->longText('failed_job_ids')->comment('IDs dos jobs que falharam no lote');
            $table->mediumText('options')->nullable()->comment('Opções de configuração do lote');
            $table->integer('cancelled_at')->nullable()->comment('Data e hora do cancelamento do lote');
            $table->integer('created_at')->comment('Data e hora de criação do lote');
            $table->integer('finished_at')->nullable()->comment('Data e hora de conclusão do lote');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id()->comment('Identificador único da falha');
            $table->string('uuid')->unique()->comment('UUID referenciando a falha');
            $table->text('connection')->comment('Conexão utilizada no momento da falha');
            $table->text('queue')->comment('Fila na qual ocorreu a falha');
            $table->longText('payload')->comment('Dados do job que ocasionou a falha');
            $table->longText('exception')->comment('Mensagem/stacktrace da exceção lançada');
            $table->timestamp('failed_at')->useCurrent()->comment('Momento exato da falha do job');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
