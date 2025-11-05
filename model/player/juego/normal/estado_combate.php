<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$ids = [];
if(!empty($_POST['ids'])){
    $ids = array_map('intval', explode(',', $_POST['ids']));
}
if(empty($ids)){
    echo json_encode(['error'=>'No hay ids']); exit;
}

$placeholders = implode(',', array_fill(0,count($ids),'?'));
$stmt = $con->prepare("SELECT id_usuario, vida FROM usuario WHERE id_usuario IN ($placeholders)");
$stmt->execute($ids);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach($rows as $r){
    $result[intval($r['id_usuario'])] = intval($r['vida']);
}

echo json_encode(['vidas'=>$result]);
