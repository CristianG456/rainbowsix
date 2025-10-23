<?php
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_POST['id_partida'] ?? 0;
$duracion = $_POST['segundos'] ?? 60; // duración configurable
if (!$id_partida) exit(json_encode(['error' => 'No hay partida']));

// Obtener si ya existe cuenta regresiva
$sql = $con->prepare("SELECT inicio_cuenta_regresiva FROM partida WHERE id_partida=?");
$sql->execute([$id_partida]);
$yaInicio = $sql->fetchColumn();

if (!$yaInicio) {
    // Crear cuenta regresiva y poner en estado 2 (preparando)
    $upd = $con->prepare("
        UPDATE partida 
        SET inicio_cuenta_regresiva = NOW(), id_estado_part = 2, duracion_cuenta = ?
        WHERE id_partida = ?
    ");
    $upd->execute([$duracion, $id_partida]);
}

echo json_encode(['ok' => true, 'duracion' => $duracion]);
