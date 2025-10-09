<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No hay sesión']);
    exit();
}

$usu = $_SESSION['id_usuario'];

$sqlNivel = $con->prepare("
    SELECT n.id_nivel, n.nomb_nivel, n.puntos_nivel
    FROM nivel n
    INNER JOIN usuario u ON n.id_nivel = u.id_nivel
    WHERE u.id_usuario = ?
");
$sqlNivel->execute([$usu]);
$nivel = $sqlNivel->fetch(PDO::FETCH_ASSOC);
$puntosMaximos = $nivel['puntos_nivel'] ?? 100;

$sqlPuntos = $con->prepare("SELECT puntos, id_nivel FROM usuario WHERE id_usuario = ?");
$sqlPuntos->execute([$usu]);
$fila = $sqlPuntos->fetch(PDO::FETCH_ASSOC);
$puntosActuales = $fila['puntos'] ?? 0;

$puntosGanados = 10;
$puntosActuales += $puntosGanados;

if ($puntosActuales >= $puntosMaximos) {
    $nuevoNivel = $nivel['id_nivel'] + 1;
    $puntosActuales -= $puntosMaximos;

    $sqlSubirNivel = $con->prepare("UPDATE usuario SET id_nivel = ?, puntos = ? WHERE id_usuario = ?");
    $sqlSubirNivel->execute([$nuevoNivel, $puntosActuales, $usu]);
} else {
    $sqlActualizar = $con->prepare("UPDATE usuario SET puntos = ? WHERE id_usuario = ?");
    $sqlActualizar->execute([$puntosActuales, $usu]);
}

$porcentaje = ($puntosMaximos > 0) ? min(100, ($puntosActuales / $puntosMaximos) * 100) : 0;

echo json_encode([
    'puntosActuales' => $puntosActuales,
    'puntosMaximos' => $puntosMaximos,
    'porcentaje' => $porcentaje,
    'nivel' => $nivel['nomb_nivel']
]);
