<?php
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'];

$sql = $con->prepare("SELECT id_partida, inicio, estado FROM partida WHERE id_partida = ?");
$sql->execute([$id_partida]);
$partida = $sql->fetch();

if (!$partida) exit("no encontrada");

// Verificar tiempo (5 minutos)
$tiempo = $con->prepare("SELECT TIMESTAMPDIFF(MINUTE, inicio, NOW()) AS minutos FROM partida WHERE id_partida = ?");
$tiempo->execute([$id_partida]);
$min = $tiempo->fetchColumn();

// Contar jugadores vivos y activos
$vivos = $con->prepare("
    SELECT COUNT(*) FROM usuario u
    INNER JOIN detalle_usuario_partida d ON u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2
    WHERE d.id_partida = ? AND u.vida > 0 AND u.id_estado_usu = 1
");
$vivos->execute([$id_partida]);
$num_vivos = $vivos->fetchColumn();

if ($num_vivos <= 1 || $min >= 5) {
    // Cerrar partida y reiniciar vidas
    $con->prepare("UPDATE partida SET estado = 'cerrada' WHERE id_partida = ?")->execute([$id_partida]);
    $con->prepare("UPDATE usuario SET vida = 200 WHERE vida <= 0")->execute();
    echo "cerrada";
} else {
    echo "activa";
}
