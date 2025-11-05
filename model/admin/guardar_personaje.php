<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

// --- VALIDACIONES ---
$nomb_avat = trim($_POST['nomb_avat'] ?? '');
if ($nomb_avat === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre del personaje es obligatorio']);
    exit;
}

if (empty($_FILES['url_personaje']['name']) || empty($_FILES['url_avatar']['name'])) {
    echo json_encode(['success' => false, 'message' => 'Debes subir ambas imágenes (personaje y avatar)']);
    exit;
}

// --- SUBIDA DE IMÁGENES ---
$imgDir = "../../controller/img/";
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0777, true);
}

$personajeExt = strtolower(pathinfo($_FILES['url_personaje']['name'], PATHINFO_EXTENSION));
$avatarExt = strtolower(pathinfo($_FILES['url_avatar']['name'], PATHINFO_EXTENSION));

$extValidas = ['png', 'webp'];

if (!in_array($personajeExt, $extValidas) || !in_array($avatarExt, $extValidas)) {
    echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes PNG o WEBP (WEBP recomendado)']);
    exit;
}

// Nombres únicos
$personajeName = uniqid('personaje_') . '.' . $personajeExt;
$avatarName = uniqid('avatar_') . '.' . $avatarExt;

$personajePath = $imgDir . $personajeName;
$avatarPath = $imgDir . $avatarName;

if (!move_uploaded_file($_FILES['url_personaje']['tmp_name'], $personajePath) ||
    !move_uploaded_file($_FILES['url_avatar']['tmp_name'], $avatarPath)) {
    echo json_encode(['success' => false, 'message' => 'Error al subir las imágenes']);
    exit;
}

// --- INSERTAR EN BASE DE DATOS ---
try {
    $sql = $con->prepare("INSERT INTO avatar (nomb_avat, url_personaje, url_avatar) VALUES (?, ?, ?)");
    $res = $sql->execute([$nomb_avat, $personajeName, $avatarName]);

    echo json_encode([
        'success' => $res,
        'message' => $res ? 'Personaje agregado correctamente' : 'Error al guardar el personaje'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
