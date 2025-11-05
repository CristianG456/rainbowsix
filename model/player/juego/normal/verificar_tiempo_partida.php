<?php
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = intval($_GET['id_partida'] ?? 0);
if (!$id_partida) exit(json_encode(["status"=>"error","message"=>"Partida no válida"]));

/* contar jugadores activos - mismo SQL robusto */
$sqlCount = "
  SELECT COUNT(DISTINCT t.uid) AS cnt FROM (
    SELECT d.id_usuario AS uid FROM detalle_usuario_partida d WHERE d.id_partida = ?
    UNION
    SELECT d.id_usuario1 AS uid FROM detalle_usuario_partida d WHERE d.id_partida = ?
    UNION
    SELECT d.id_usuario2 AS uid FROM detalle_usuario_partida d WHERE d.id_partida = ?
  ) AS t
  JOIN usuario u ON u.id_usuario = t.uid
  WHERE u.id_estado_usu = 1 AND u.vida > 0
";
$stmt = $con->prepare($sqlCount);
$stmt->execute([$id_partida,$id_partida,$id_partida]);
$jugadores = (int)$stmt->fetchColumn();

/* Traer partida */
$sql = $con->prepare("SELECT * FROM partida WHERE id_partida = ?");
$sql->execute([$id_partida]);
$partida = $sql->fetch(PDO::FETCH_ASSOC);
if (!$partida) exit(json_encode(["status"=>"error","message"=>"No encontrada"]));

/* Reglas: si jugadores < 2 -> limpiar inicio */
if ($jugadores < 2 && !empty($partida['inicio_cuenta_regresiva'])) {
    $con->prepare("UPDATE partida SET inicio_cuenta_regresiva = NULL, id_estado_part = 3 WHERE id_partida = ?")->execute([$id_partida]);
    $partida['inicio_cuenta_regresiva'] = null;
    $partida['id_estado_part'] = 3;
}

/* Si pre-count existe y partida estado = 3, responder con tiempo restante */
if ($partida['id_estado_part'] == 3 && !empty($partida['inicio_cuenta_regresiva'])) {
    $inicio = strtotime($partida['inicio_cuenta_regresiva']);
    $segundos_restantes = max(0, 30 - (time() - $inicio));
    if ($segundos_restantes <= 0) {
        // iniciar partida
        $con->prepare("UPDATE partida SET id_estado_part = 5, fecha_inicio = NOW() WHERE id_partida = ?")->execute([$id_partida]);
        echo json_encode(["status"=>"iniciando","tiempo_restante"=>0]);
        exit;
    }
    echo json_encode(["status"=>"preparando","tiempo_restante"=>$segundos_restantes,"jugadores"=>$jugadores]);
    exit;
}

/* Si partida ya en juego (5) manejar tiempo de juego */
if ($partida['id_estado_part'] == 5 && $partida['fecha_inicio']) {
    $inicio = new DateTime($partida['fecha_inicio']);
    $ahora = new DateTime();
    $diff = $ahora->getTimestamp() - $inicio->getTimestamp();
    $tiempo_restante = max(0, 300 - $diff);
    if ($tiempo_restante <= 0) {
        $con->prepare("UPDATE partida SET id_estado_part = 4, fecha_fin = NOW() WHERE id_partida = ?")->execute([$id_partida]);
        echo json_encode(["status"=>"cerrada","message"=>"finalizada","jugadores"=>$jugadores]);
        exit;
    }
    echo json_encode(["status"=>"activa","tiempo_restante"=>$tiempo_restante,"jugadores"=>$jugadores]);
    exit;
}

echo json_encode(["status"=>"esperando","jugadores"=>$jugadores]);
