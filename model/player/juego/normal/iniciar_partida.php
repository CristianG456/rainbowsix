<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? 0;
if (!$id_partida) exit(json_encode(['error' => 'No hay partida']));

// Obtener estado actual de la partida
$sql = $con->prepare("SELECT id_estado_part, inicio_cuenta_regresiva FROM partida WHERE id_partida=? LIMIT 1");
$sql->execute([$id_partida]);
$partida = $sql->fetch(PDO::FETCH_ASSOC);

if (!$partida) exit(json_encode(['error' => 'Partida no encontrada']));

$estado = (int)$partida['id_estado_part'];
$tiempo_restante = 0;

// Contar jugadores
$sqlJug = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
$sqlJug->execute([$id_partida]);
$cantJugadores = (int)$sqlJug->fetchColumn();

// Si hay 2 o más jugadores y está Abierto (3), pasar a En juego (5) y guardar inicio de cuenta regresiva
if ($estado === 3 && $cantJugadores >= 2) {
    // Establecemos 60s de cuenta regresiva
    $inicio = time();
    $sqlUpd = $con->prepare("UPDATE partida SET id_estado_part=5, inicio_cuenta_regresiva=? WHERE id_partida=?");
    $sqlUpd->execute([$inicio, $id_partida]);
    $estado = 5;
    $tiempo_restante = 60; // cuenta regresiva inicial
} elseif ($estado === 5) {
    // Si ya está en juego, calcular tiempo restante
    $inicio = $partida['inicio_cuenta_regresiva'] ?? time();
    $tiempo_transcurrido = time() - $inicio;
    $tiempo_restante = max(0, 300 - $tiempo_transcurrido); // 5 minutos totales
}

// Retornar JSON
echo json_encode([
    'estado_partida' => $estado,
    'tiempo_restante' => $tiempo_restante
]);
