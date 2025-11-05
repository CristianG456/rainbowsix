<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_arma = (int)($_POST['id_arma'] ?? 0);
if(!$id_arma){
    echo json_encode(['success'=>false,'message'=>'ID no válido']);
    exit;
}

$nomb_arma = $_POST['nomb_arma'] ?? '';
$dano_cabeza = (int)($_POST['dano_cabeza'] ?? 0);
$dano_torso = (int)($_POST['dano_torso'] ?? 0);
$id_tipo_arma = (int)($_POST['id_tipo_arma'] ?? 0);
$cant_balas = (int)($_POST['cant_balas'] ?? 0);
$id_nivel_arma = (int)($_POST['id_nivel_arma'] ?? 0);

// Traer arma actual
$stmt = $con->prepare("SELECT img_arma FROM armas WHERE id_arma=?");
$stmt->execute([$id_arma]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$arma){
    echo json_encode(['success'=>false,'message'=>'Arma no encontrada']);
    exit;
}

$img_arma = $arma['img_arma']; // imagen actual

// Subir nueva imagen si se envía
if(isset($_FILES['img_arma']) && $_FILES['img_arma']['name'] != ''){
    $ext = strtolower(pathinfo($_FILES['img_arma']['name'], PATHINFO_EXTENSION));
    if($ext !== 'png'){
        echo json_encode(['success'=>false,'message'=>'Solo PNG permitidos']);
        exit;
    }

    $nuevoNombre = uniqid().'_arma.png';
    if(move_uploaded_file($_FILES['img_arma']['tmp_name'], "../../controller/img/".$nuevoNombre)){
        // Borrar la anterior
        if($img_arma && file_exists("../../controller/img/".$img_arma)){
            unlink("../../controller/img/".$img_arma);
        }
        $img_arma = $nuevoNombre;
    } else {
        echo json_encode(['success'=>false,'message'=>'Error al subir la imagen']);
        exit;
    }
}

// Actualizar arma
$update = $con->prepare("UPDATE armas SET 
    nomb_arma=?, 
    dano_cabeza=?, 
    dano_torso=?, 
    id_tipo_arma=?, 
    cant_balas=?, 
    id_nivel_arma=?, 
    img_arma=? 
    WHERE id_arma=?");

$res = $update->execute([$nomb_arma, $dano_cabeza, $dano_torso, $id_tipo_arma, $cant_balas, $id_nivel_arma, $img_arma, $id_arma]);

if($res){
    echo json_encode(['success'=>true,'message'=>'Arma actualizada correctamente']);
}else{
    echo json_encode(['success'=>false,'message'=>'Error al actualizar']);
}
