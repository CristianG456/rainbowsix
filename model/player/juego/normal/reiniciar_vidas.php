<?php
session_start();
require_once("../../../../database/db.php");
header('Content-Type: application/json');

$db = new Database();
$con = $db->conectar();

$id_partida = $_POST['id_partida'] ?? null;

if (!$id_partida) {
    echo json_encode(['ok' => false, 'error' => 'No se recibió id_partida']);
    exit;
}

try {
    // Obtener jugadores de la partida que siguen vivos (vida > 0)
    $stmt = $con->prepare("
        SELECT DISTINCT u.id_usuario 
        FROM detalle_usuario_partida d 
        INNER JOIN usuario u 
        ON (u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2) 
        WHERE d.id_partida = ? AND u.vida > 0
    ");
    $stmt->execute([$id_partida]);
    $jugadores = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($jugadores)) {
        // Reiniciar vida solo a los jugadores vivos
        $placeholders = implode(',', array_fill(0, count($jugadores), '?'));
        $upd = $con->prepare("UPDATE usuario SET vida = 200 WHERE id_usuario IN ($placeholders)");
        $upd->execute($jugadores);
    }

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
