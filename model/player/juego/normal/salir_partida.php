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

    //  Eliminar al jugador de la partida
    $stmt = $con->prepare("
        DELETE FROM detalle_usuario_partida 
        WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)
    ");
    $stmt->execute([$id_partida, $id_usuario, $id_usuario]);

    //  Contar jugadores restantes
    $stmt2 = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
    $stmt2->execute([$id_partida]);
    $cantidad = $stmt2->fetchColumn();

    // Actualizar cantidad de jugadores en la partida (opcional)
    $upd = $con->prepare("UPDATE partida SET cantidad_jug=? WHERE id_partida=?");
    $upd->execute([$cantidad, $id_partida]);

    //  Cerrar partida si no quedan jugadores
    if ($cantidad == 0) {
        // Marcar partida como cerrada, registrar fecha_fin y cantidad_jug a 0
        $con->prepare("UPDATE partida SET id_estado_part=4, fecha_fin=NOW(), cantidad_jug=0 WHERE id_partida=?")->execute([$id_partida]);

        // Obtener la sala asociada y marcarla como disponible (ABIERTO = 3)
        $s = $con->prepare("SELECT id_sala FROM partida WHERE id_partida=? LIMIT 1");
        $s->execute([$id_partida]);
        $rs = $s->fetch(PDO::FETCH_ASSOC);
        if ($rs && isset($rs['id_sala'])) {
            // Sólo abrir la sala si pertenece a las primeras 3 salas disponibles
            $updSala = $con->prepare(
                "UPDATE sala SET id_estado_sala=3 WHERE id_sala = ? AND id_sala IN (SELECT id_sala FROM (SELECT id_sala FROM sala ORDER BY id_sala ASC LIMIT 3) AS t)"
            );
            $updSala->execute([$rs['id_sala']]);
        }
    }

    $con->commit();
    echo json_encode(['ok'=>true,'cantidad_restante'=>$cantidad]);

} catch(Exception $e) {
    if($con->inTransaction()) $con->rollBack();
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
