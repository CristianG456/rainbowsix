<?php
session_start();
require_once '../../../database/db.php';

$db = new Database();
$con = $db->conectar();

function actualizarEstadoPartidas($con) {
    // 🔹 Finalizar partidas que llevan más de 24h abiertas o en progreso
    $sql_actualizar = "
        UPDATE partida 
        SET id_estado_part = 2  -- 2 = Finalizada
        WHERE id_estado_part IN (1, 3, 5)
        AND TIMESTAMPDIFF(HOUR, fecha_inicio, NOW()) > 24
    ";
    $con->query($sql_actualizar);

    // 🔹 Marcar como finalizadas las partidas sin jugadores
    $sql_verificar = "
        UPDATE partida p 
        LEFT JOIN (
            SELECT id_partida, COUNT(*) AS num_jugadores 
            FROM detalle_usuario_partida 
            GROUP BY id_partida
        ) d ON p.id_partida = d.id_partida
        SET p.id_estado_part = 2  -- Finalizada
        WHERE p.id_estado_part != 2
        AND (d.num_jugadores = 0 OR d.num_jugadores IS NULL)
    ";
    $con->query($sql_verificar);
}

try {
    actualizarEstadoPartidas($con);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
