<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

header('Content-Type: application/json');

$id_partida = $_POST['id_partida'] ?? null;
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_partida || !$id_usuario) {
    echo json_encode(['ok'=>false,'error'=>'Parámetros faltantes']);
    exit;
}

try {
    $con->beginTransaction();

    // Eliminar al jugador de detalle_usuario_partida
    $stmt = $con->prepare("DELETE FROM detalle_usuario_partida WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)");
    $stmt->execute([$id_partida, $id_usuario, $id_usuario]);

    // Contar jugadores restantes
    $stmt2 = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
    $stmt2->execute([$id_partida]);
    $cantidad = $stmt2->fetchColumn();

    // Actualizar cantidad de jugadores en partida
    $upd = $con->prepare("UPDATE partida SET cantidad_jug=? WHERE id_partida=?");
    $upd->execute([$cantidad, $id_partida]);

    // Si no quedan jugadores, cerrar partida
    if ($cantidad == 0) {
        $con->prepare("UPDATE partida SET id_estado_part=4 WHERE id_partida=?")->execute([$id_partida]);
    }

    $con->commit();
    echo json_encode(['ok'=>true,'cantidad_restante'=>$cantidad]);
} catch(Exception $e) {
    if($con->inTransaction()) $con->rollBack();
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
