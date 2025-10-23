<?php
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? 0;
if (!$id_partida) {
    echo json_encode(["status" => "error", "message" => "Partida no válida"]);
    exit;
}

$sql = $con->prepare("SELECT fecha_inicio, fecha_fin, id_estado_part FROM partida WHERE id_partida = ?");
$sql->execute([$id_partida]);
$partida = $sql->fetch(PDO::FETCH_ASSOC);

if (!$partida) {
    echo json_encode(["status" => "error", "message" => "No se encontró la partida"]);
    exit;
}

if ($partida['id_estado_part'] == 4) {
    echo json_encode(["status" => "cerrada", "message" => "Partida ya finalizada."]);
    exit;
}

// Solo contar si está en juego
if ($partida['id_estado_part'] == 5 && $partida['fecha_inicio']) {
    $inicio = new DateTime($partida['fecha_inicio']);
    $ahora = new DateTime();
    $diff = $ahora->getTimestamp() - $inicio->getTimestamp();

    $tiempo_restante = max(0, 300 - $diff); // 5 minutos

    if ($tiempo_restante <= 0) {
        $update = $con->prepare("UPDATE partida SET id_estado_part = 4, fecha_fin = NOW() WHERE id_partida = ?");
        $update->execute([$id_partida]);
        echo json_encode(["status" => "cerrada", "message" => "⏰ La partida ha finalizado por tiempo."]);
        exit;
    }

    echo json_encode(["status" => "activa", "tiempo_restante" => $tiempo_restante]);
} else {
    echo json_encode(["status" => "esperando"]);
}
