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
        $upd = $con->prepare("UPDATE partida SET id_estado_part=4, fecha_fin=NOW() WHERE id_partida=?");
        $upd->execute([$id_partida]);
        $tiempo_restante = 0;
    }
}

$status = ($estado === 5) ? 'activa' : 'cerrada';
echo json_encode(['status'=>$status,'tiempo_restante'=>$tiempo_restante]);
