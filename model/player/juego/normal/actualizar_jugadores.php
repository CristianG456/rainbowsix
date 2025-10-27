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

// 🔹 Asegurarse de que exista la columna ultima_actividad
try {
    $con->query("ALTER TABLE usuario ADD COLUMN IF NOT EXISTS ultima_actividad DATETIME NULL DEFAULT NOW()");
} catch (Exception $e) {
    // Ignorar si ya existe
}

// 🔹 Actualizar la última actividad del usuario actual (para presencia en tiempo real)
if ($id_usuario) {
    $updateActividad = $con->prepare("UPDATE usuario SET ultima_actividad = NOW() WHERE id_usuario = ?");
    $updateActividad->execute([$id_usuario]);
}

// 🔹 Obtener todos los jugadores activos de esta partida (sin enemigo local)
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
");
$sql->execute([$id_partida]);
$jugadores = $sql->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Si no hay jugadores aún, no devolver nada
if (empty($jugadores)) {
    echo json_encode([]);
    exit;
}

// 🔹 Asegurar que las rutas sean solo nombres de archivo
foreach ($jugadores as &$j) {
    $j['url_personaje'] = basename($j['url_personaje']);
}

echo json_encode($jugadores);
