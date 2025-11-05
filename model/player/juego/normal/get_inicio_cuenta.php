<?php
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_partida = $_GET['id_partida'] ?? 0;
if(!$id_partida) exit(json_encode(['error'=>'No hay partida']));

$sql = $con->prepare("SELECT inicio_cuenta_regresiva, id_estado_part FROM partida WHERE id_partida=?");
$sql->execute([$id_partida]);
$data = $sql->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
