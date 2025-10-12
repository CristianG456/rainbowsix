<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_GET['partida']) || !isset($_GET['sala'])) {
    header("Location: ./juego.php");
    exit;
}

$id_partida = $_GET['partida'];
$id_sala = $_GET['sala'];

// Cargar datos básicos de la partida
$sql = $con->prepare("
    SELECT p.*, s.id_mundo, m.nomb_mundo
    FROM partida p
    INNER JOIN sala s ON p.id_sala = s.id_sala
    INNER JOIN mundo m ON s.id_mundo = m.id_mundo
    WHERE p.id_partida = ?
");
$sql->execute([$id_partida]);
$partida = $sql->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Combate</title>
    <link rel="stylesheet" href="../../controller/css/combate.css">
</head>
<body>
    <h1>Combate en <?= htmlspecialchars($partida['nomb_mundo']) ?></h1>
    <p>ID de sala: <?= htmlspecialchars($id_sala) ?></p>
    <p>ID de partida: <?= htmlspecialchars($id_partida) ?></p>

    <p>Esperando jugadores o iniciando combate...</p>
</body>
</html>
