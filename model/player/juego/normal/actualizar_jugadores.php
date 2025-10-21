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

$sql = $con->prepare("
    SELECT u.id_usuario, u.nomb_usu, u.vida, u.puntos, a.url_personaje
    FROM usuario u
    INNER JOIN avatar a ON u.id_avatar=a.id_avatar
    INNER JOIN detalle_usuario_partida d ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
    WHERE d.id_partida=?
");
$sql->execute([$id_partida]);
$jugadores = $sql->fetchAll(PDO::FETCH_ASSOC);

foreach($jugadores as &$j){
    $j['url_personaje'] = basename($j['url_personaje']);
}

echo json_encode($jugadores);
