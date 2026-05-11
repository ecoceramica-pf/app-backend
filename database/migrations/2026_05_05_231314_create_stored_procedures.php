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
        // Criar um registro de coleta de forma segura, garantindo que dois coletores não reservem a mesma oferta simultaneamente
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_registrar_coleta;");
        DB::unprepared("
            CREATE PROCEDURE sp_registrar_coleta(
                IN p_oferta_id BIGINT UNSIGNED,
                IN p_coletor_id BIGINT UNSIGNED,
                IN p_data_reserva DATETIME
            )
            BEGIN
                DECLARE v_status VARCHAR(20);

                -- Usa o FOR UPDATE para evitar concorrência (lock na linha)
                SELECT status INTO v_status FROM ofertas_residuos WHERE id = p_oferta_id FOR UPDATE;

                IF v_status = 'disponivel' THEN
                    INSERT INTO coletas (oferta_residuo_id, coletor_id, data_reserva, status, created_at, updated_at)
                    VALUES (p_oferta_id, p_coletor_id, p_data_reserva, 'pendente', NOW(), NOW());

                    UPDATE ofertas_residuos
                    SET status = 'em processo', updated_at = NOW()
                    WHERE id = p_oferta_id;
                ELSE
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'A oferta não está mais disponível para coleta.';
                END IF;
            END;
        ");

        // Finalizar uma coleta e dar baixa automática na oferta de resíduo vinculada.
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_concluir_coleta;");
        DB::unprepared("
            CREATE PROCEDURE sp_concluir_coleta(
                IN p_coleta_id BIGINT UNSIGNED
            )
            BEGIN
                DECLARE v_status VARCHAR(20);
                DECLARE v_oferta_id BIGINT UNSIGNED;

                -- Usa FOR UPDATE para lock na coleta
                SELECT status, oferta_residuo_id INTO v_status, v_oferta_id
                FROM coletas
                WHERE id = p_coleta_id FOR UPDATE;

                IF v_status = 'pendente' THEN
                    UPDATE coletas
                    SET status = 'concluido', data_conclusao = NOW(), updated_at = NOW()
                    WHERE id = p_coleta_id;

                    UPDATE ofertas_residuos
                    SET status = 'concluido', updated_at = NOW()
                    WHERE id = v_oferta_id;
                ELSE
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'A coleta já foi concluída, cancelada ou não existe.';
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_registrar_coleta;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_concluir_coleta;");
    }
};
