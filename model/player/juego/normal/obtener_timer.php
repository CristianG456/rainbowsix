<?php
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? 0;
if (!$id_partida) exit(json_encode(['status'=>'cerrada','tiempo_restante'=>0]));

$stmt = $con->prepare("SELECT * FROM partida WHERE id_partida=?");
$stmt->execute([$id_partida]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$partida) exit(json_encode(['status'=>'cerrada','tiempo_restante'=>0]));

$estado = (int)$partida['id_estado_part'];
$tiempo_restante = 0;

if ($estado === 5) {
    $inicio = strtotime($partida['fecha_inicio']);
    $diff = time() - $inicio;
    $tiempo_restante = max(0, 5*60 - $diff);

    if ($tiempo_restante <= 0) {
        $estado = 4;
        // Marcar partida como finalizada y limpiar datos para que la sala quede vacía
        $upd = $con->prepare("UPDATE partida SET id_estado_part=4, fecha_fin=NOW(), cantidad_jug=0 WHERE id_partida=?");
        $upd->execute([$id_partida]);

        // Obtener id_sala y marcar sala como ABIERTO para que esté disponible
        $s = $con->prepare("SELECT id_sala FROM partida WHERE id_partida=? LIMIT 1");
        $s->execute([$id_partida]);
        $rs = $s->fetch(PDO::FETCH_ASSOC);
        if ($rs && isset($rs['id_sala'])) {
            // Sólo marcar la sala como ABIERTO si está dentro de las primeras 3 salas
            $updSala = $con->prepare(
                "UPDATE sala SET id_estado_sala=3 WHERE id_sala = ? AND id_sala IN (SELECT id_sala FROM (SELECT id_sala FROM sala ORDER BY id_sala ASC LIMIT 3) AS t)"
            );
            $updSala->execute([$rs['id_sala']]);
        }

        // Eliminar jugadores de detalle para reiniciar la sala
        $del = $con->prepare("DELETE FROM detalle_usuario_partida WHERE id_partida=?");
        $del->execute([$id_partida]);
        $tiempo_restante = 0;
    }
}

$status = ($estado === 5) ? 'activa' : 'cerrada';
echo json_encode(['status'=>$status,'tiempo_restante'=>$tiempo_restante]);
