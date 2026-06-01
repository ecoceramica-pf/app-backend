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
        // Atualizar sp_registrar_coleta para incluir data_agendamento
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_registrar_coleta;");
        DB::unprepared("
            CREATE PROCEDURE sp_registrar_coleta(
                IN p_oferta_id BIGINT UNSIGNED,
                IN p_coletor_id BIGINT UNSIGNED,
                IN p_data_reserva DATETIME,
                IN p_data_agendamento DATETIME
            )
            BEGIN
                DECLARE v_status VARCHAR(20);
                DECLARE v_fabrica_id BIGINT UNSIGNED;
                DECLARE v_max_coletas INT;
                DECLARE v_coletas_dia INT;

                -- Lock na oferta para evitar concorrência
                SELECT status, user_id INTO v_status, v_fabrica_id
                FROM ofertas_residuos WHERE id = p_oferta_id FOR UPDATE;

                IF v_status = 'disponivel' THEN
                    -- Verificar limite de coletas da fábrica no dia
                    SELECT COALESCE(fd.max_coletas_dia, 999) INTO v_max_coletas
                    FROM fabrica_disponibilidades fd
                    WHERE fd.user_id = v_fabrica_id;

                    SELECT COUNT(*) INTO v_coletas_dia
                    FROM coletas c
                    INNER JOIN ofertas_residuos o ON c.oferta_residuo_id = o.id
                    WHERE o.user_id = v_fabrica_id
                      AND DATE(c.data_agendamento) = DATE(p_data_agendamento)
                      AND c.status = 'pendente'
                      AND c.deleted_at IS NULL;

                    IF v_coletas_dia >= v_max_coletas THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Limite de coletas da fábrica atingido para este dia.';
                    END IF;

                    INSERT INTO coletas (oferta_residuo_id, coletor_id, data_reserva, data_agendamento, status, created_at, updated_at)
                    VALUES (p_oferta_id, p_coletor_id, p_data_reserva, p_data_agendamento, 'pendente', NOW(), NOW());

                    UPDATE ofertas_residuos
                    SET status = 'em processo', updated_at = NOW()
                    WHERE id = p_oferta_id;
                ELSE
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'A oferta não está mais disponível para coleta.';
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar versão anterior sem data_agendamento
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_registrar_coleta;");
        DB::unprepared("
            CREATE PROCEDURE sp_registrar_coleta(
                IN p_oferta_id BIGINT UNSIGNED,
                IN p_coletor_id BIGINT UNSIGNED,
                IN p_data_reserva DATETIME
            )
            BEGIN
                DECLARE v_status VARCHAR(20);

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
    }
};
