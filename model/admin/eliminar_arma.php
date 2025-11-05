<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success'=>false,'message'=>'No autorizado']);
    exit;
}

$id = $_GET['id'] ?? '';
if(!$id){
    echo json_encode(['success'=>false,'message'=>'ID no válido']);
    exit;
}

// Borrar imagen de la carpeta
$stmt = $con->prepare("SELECT img_arma FROM armas WHERE id_arma=?");
$stmt->execute([$id]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);
if($arma && $arma['img_arma'] && file_exists("../../controller/img/".$arma['img_arma'])){
    unlink("../../controller/img/".$arma['img_arma']);
}

// Eliminar de la base de datos
$stmt = $con->prepare("DELETE FROM armas WHERE id_arma=?");
if($stmt->execute([$id])){
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Error al eliminar arma']);
}
