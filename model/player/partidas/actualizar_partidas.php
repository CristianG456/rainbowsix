<?php
session_start();
require_once '../../../database/db.php';

function actualizarEstadoPartidas($conn) {
    // Actualizar partidas que han expirado (más de 24 horas sin actividad)
    $sql_actualizar = "UPDATE partida SET estado = 'Finalizada' 
                      WHERE estado IN ('En progreso', 'Abierta') 
                      AND TIMESTAMPDIFF(HOUR, fecha_creacion, NOW()) > 24";
    $conn->query($sql_actualizar);

    // Limpiar jugadores inactivos (más de 30 minutos sin actividad)
    $sql_limpiar = "DELETE FROM detalle_usuario_partida 
                    WHERE TIMESTAMPDIFF(MINUTE, ultima_actividad, NOW()) > 30 
                    AND estado_jugador != 'Finalizado'";
    $conn->query($sql_limpiar);

    // Verificar si hay partidas que se quedaron sin jugadores
    $sql_verificar = "UPDATE partida p 
                     LEFT JOIN (
                         SELECT id_partida, COUNT(*) as num_jugadores 
                         FROM detalle_usuario_partida 
                         GROUP BY id_partida
                     ) d ON p.id = d.id_partida 
                     SET p.estado = 'Finalizada' 
                     WHERE p.estado != 'Finalizada' 
                     AND (d.num_jugadores = 0 OR d.num_jugadores IS NULL)";
    $conn->query($sql_verificar);

    // Actualizar tiempo restante para partidas en progreso
    $sql_tiempo = "UPDATE partida 
                   SET tiempo_restante = GREATEST(0, 
                       TIME_TO_SEC(TIMEDIFF(
                           DATE_ADD(fecha_inicio, INTERVAL duracion_minutos MINUTE),
                           NOW()
                       ))
                   ) 
                   WHERE estado = 'En progreso'";
    $conn->query($sql_tiempo);
}

// Ejecutar las actualizaciones
try {
    actualizarEstadoPartidas($conn);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>