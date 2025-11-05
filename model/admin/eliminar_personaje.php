<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id = (int)($_GET['id'] ?? 0);
if(!$id){
    echo json_encode(['success'=>false,'message'=>'ID inválido']);
    exit;
}

$sql = $con->prepare("DELETE FROM avatar WHERE id_avatar=?");
$res = $sql->execute([$id]);
echo json_encode(['success'=>$res,'message'=>$res?'Eliminado correctamente':'Error al eliminar']);
