<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Recibir datos del formulario
$nombre = $_POST['nomb_arma'] ?? '';
$dano_cabeza = $_POST['dano_cabeza'] ?? 0;
$dano_torso = $_POST['dano_torso'] ?? 0;
$id_tipo_arma = $_POST['id_tipo_arma'] ?? '';
$cant_balas = $_POST['cant_balas'] ?? 0;
$id_nivel_arma = $_POST['id_nivel_arma'] ?? '';
$img_nombre = null;

// Validar imagen PNG
if (!empty($_FILES['img_arma']['name'])) {
    $img = $_FILES['img_arma'];
    $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
    if ($ext !== 'png') {
        echo json_encode(['success'=>false,'message'=>'Solo se permiten imágenes PNG']);
        exit;
    }
    $img_nombre = uniqid() . '.png';
    move_uploaded_file($img['tmp_name'], "../../controller/img/".$img_nombre);
} else {
    echo json_encode(['success'=>false,'message'=>'Debe subir una imagen PNG']);
    exit;
}

// Insertar en la base de datos
$stmt = $con->prepare("INSERT INTO armas (nomb_arma,dano_cabeza,dano_torso,id_tipo_arma,cant_balas,id_nivel_arma,img_arma)
                       VALUES (?,?,?,?,?,?,?)");
if ($stmt->execute([$nombre,$dano_cabeza,$dano_torso,$id_tipo_arma,$cant_balas,$id_nivel_arma,$img_nombre])) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'message'=>'Error al guardar arma']);
}
