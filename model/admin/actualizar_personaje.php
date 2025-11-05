<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id = (int)($_POST['id_avatar'] ?? 0);
$nomb_avat = $_POST['nomb_avat'] ?? '';

if(!$id){ echo json_encode(['success'=>false,'message'=>'ID inválido']); exit; }

$update = "UPDATE avatar SET nomb_avat=?";
$params = [$nomb_avat];

if(isset($_FILES['url_personaje']) && $_FILES['url_personaje']['name']){
    $img = uniqid().".png";
    move_uploaded_file($_FILES['url_personaje']['tmp_name'], "../../controller/img/".$img);
    $update .= ", url_personaje=?";
    $params[] = $img;
}
if(isset($_FILES['url_avatar']) && $_FILES['url_avatar']['name']){
    $ava = uniqid().".png";
    move_uploaded_file($_FILES['url_avatar']['tmp_name'], "../../controller/img/".$ava);
    $update .= ", url_avatar=?";
    $params[] = $ava;
}

$update .= " WHERE id_avatar=?";
$params[] = $id;

$sql = $con->prepare($update);
$res = $sql->execute($params);
echo json_encode(['success'=>$res,'message'=>$res?'Personaje actualizado':'Error al actualizar']);
