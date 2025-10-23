<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_sala = $_GET['id_sala'] ?? 0;
if (!$id_sala || !$id_usuario) exit(json_encode(['error'=>'Datos inválidos']));

// Buscar partida activa en la sala (3=Abierto, 5=En juego)
$sqlPartida = $con->prepare("SELECT * FROM partida WHERE id_sala=? AND id_estado_part IN (3,5) ORDER BY id_partida DESC LIMIT 1");
$sqlPartida->execute([$id_sala]);
$partida = $sqlPartida->fetch(PDO::FETCH_ASSOC);

// Si no existe, crear nueva partida Abierto
if (!$partida) {
    $stmt = $con->prepare("INSERT INTO partida (fecha_inicio, id_estado_part, id_sala, inicio_cuenta_regresiva) VALUES (NOW(),3,?,NOW())");
    $stmt->execute([$id_sala]);
    $id_partida = $con->lastInsertId();
    $partida = [
        'id_partida' => $id_partida,
        'id_estado_part' => 3,
        'inicio_cuenta_regresiva' => date('Y-m-d H:i:s')
    ];
} else {
    $id_partida = $partida['id_partida'];
}

// Insertar jugador si no existe y menos de 5 jugadores
$sqlJug = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
$sqlJug->execute([$id_partida]);
$cantidad = $sqlJug->fetchColumn();

$sqlYaEsta = $con->prepare("SELECT * FROM detalle_usuario_partida WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)");
$sqlYaEsta->execute([$id_partida, $id_usuario, $id_usuario]);

if (!$sqlYaEsta->fetch() && $cantidad < 5) {
    $sqlInsert = $con->prepare("INSERT INTO detalle_usuario_partida (puntos_total,id_usuario1,id_partida) VALUES (0,?,?)");
    $sqlInsert->execute([$id_usuario, $id_partida]);
}

// Contar jugadores de nuevo
$sqlJug->execute([$id_partida]);
$jugadores = $sqlJug->fetchColumn();

// Si hay ≥2 jugadores y la partida está Abierto (3), pasar a En juego (5) y fijar inicio
if ($jugadores >= 2 && $partida['id_estado_part'] == 3) {
    $upd = $con->prepare("UPDATE partida SET id_estado_part=5, inicio_cuenta_regresiva=NOW() WHERE id_partida=?");
    $upd->execute([$id_partida]);

    // Leer fila actualizada
    $sqlPartida->execute([$id_sala]);
    $partida = $sqlPartida->fetch(PDO::FETCH_ASSOC);
}

// Calcular tiempo restante (60s desde inicio)
$inicio = strtotime($partida['inicio_cuenta_regresiva'] ?? date('Y-m-d H:i:s'));
$ahora = time();
$tiempo_restante = max(0, 60 - ($ahora - $inicio));

echo json_encode([
    'estado_partida' => (int)$partida['id_estado_part'],
    'tiempo_restante' => $tiempo_restante,
    'id_partida' => $id_partida,
    'jugadores' => $jugadores
]);
