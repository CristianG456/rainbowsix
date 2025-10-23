<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? 0;
if (!$id_partida) exit(json_encode(['segundos_restantes'=>0]));

$stmt = $con->prepare("SELECT inicio_cuenta_regresiva, fecha_inicio, id_estado_part FROM partida WHERE id_partida=?");
$stmt->execute([$id_partida]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);

$segundos_restantes = 0;

if ($partida && $partida['inicio_cuenta_regresiva'] && $partida['id_estado_part']==1) {
    $segundos_restantes = 60 - (time() - strtotime($partida['inicio_cuenta_regresiva']));
    if ($segundos_restantes < 0) $segundos_restantes = 0;
}

echo json_encode(['segundos_restantes'=>$segundos_restantes]);
