<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? null;
if(!$id_partida){ 
    echo json_encode([]);
    exit;
}

// Definir el tiempo límite de inactividad (2 minutos)
$limite_tiempo = date('Y-m-d H:i:s', strtotime('-2 minutes'));

// Limpiar usuarios que no han tenido actividad reciente
$sqlLimpiar = $con->prepare("
    DELETE FROM detalle_usuario_partida 
    WHERE id_partida = ? 
    AND id_usuario1 IN (
        SELECT id_usuario 
        FROM usuario 
        WHERE ultima_actividad < ?
    )
");
$sqlLimpiar->execute([$id_partida, $limite_tiempo]);

// Obtener solo usuarios con actividad reciente
$sql = $con->prepare("
    SELECT u.id_usuario, u.nomb_usu, u.vida, u.puntos, a.url_personaje
    FROM usuario u
    INNER JOIN avatar a ON u.id_avatar=a.id_avatar
    INNER JOIN detalle_usuario_partida d ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
    WHERE d.id_partida=? 
    AND u.ultima_actividad >= ?
");

// Actualizar última actividad del usuario actual
if (isset($_SESSION['id_usuario'])) {
    $updateActividad = $con->prepare("
        UPDATE usuario 
        SET ultima_actividad = NOW() 
        WHERE id_usuario = ?
    ");
    $updateActividad->execute([$_SESSION['id_usuario']]);
}
$sql->execute([$id_partida]);
$jugadores = $sql->fetchAll(PDO::FETCH_ASSOC);

foreach($jugadores as &$j){
    $j['url_personaje'] = basename($j['url_personaje']);
}

echo json_encode($jugadores);
