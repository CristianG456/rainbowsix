<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_POST['id_partida'] ?? null;
if(!$id_partida){ exit; }

// Obtener jugadores de la partida
$stmt = $con->prepare("SELECT DISTINCT u.id_usuario FROM detalle_usuario_partida d INNER JOIN usuario u ON (u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2) WHERE d.id_partida=?");
$stmt->execute([$id_partida]);
$jugadores = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Reiniciar vida a 200
$placeholders = implode(',', array_fill(0,count($jugadores),'?'));
$upd = $con->prepare("UPDATE usuario SET vida=200 WHERE id_usuario IN ($placeholders)");
$upd->execute($jugadores);
