<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? null;
if (!$id_partida) {
    echo json_encode([]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'] ?? null;

// 🔹 Asegurar columna ultima_actividad (solo una vez, no borra datos)
try {
    $con->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS ultima_actividad DATETIME NULL DEFAULT NOW()");
} catch (Exception $e) {
    // ignorar si ya existe
}

// 🔹 Actualizar última actividad del usuario actual (mantiene presencia online)
if ($id_usuario) {
    $updateActividad = $con->prepare("UPDATE usuario SET ultima_actividad = NOW() WHERE id_usuario = ?");
    $updateActividad->execute([$id_usuario]);
}

// 🔹 Limpiar jugadores realmente inactivos (+3 min sin conexión)
$limiteTiempo = date('Y-m-d H:i:s', strtotime('-3 minutes'));
$sqlLimpiar = $con->prepare("
    DELETE FROM detalle_usuario_partida 
    WHERE id_partida = ? 
    AND id_usuario1 IN (
        SELECT id_usuario FROM usuario WHERE ultima_actividad < ?
    )
");
$sqlLimpiar->execute([$id_partida, $limiteTiempo]);

// 🔹 Obtener solo jugadores activos de la partida
$sql = $con->prepare("
    SELECT 
        u.id_usuario, 
        u.nomb_usu, 
        u.vida, 
        u.puntos, 
        COALESCE(a.url_personaje, 'enemigo_default.png') AS url_personaje
    FROM usuario u
    INNER JOIN avatar a ON u.id_avatar = a.id_avatar
    INNER JOIN detalle_usuario_partida d 
        ON (u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2)
    WHERE d.id_partida = ?
      AND u.id_estado_usu = 1
");
$sql->execute([$id_partida]);
$jugadores = $sql->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Asegurar que no se muestren enemigos locales ni vacíos
if (count($jugadores) === 0) {
    echo json_encode([]);
    exit;
}

// 🔹 Limpiar rutas
foreach ($jugadores as &$j) {
    $j['url_personaje'] = basename($j['url_personaje']);
}

echo json_encode($jugadores);
