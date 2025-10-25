<?php
session_start();
require_once("../database/db.php");

// Actualizar última actividad del usuario
function actualizarActividad($id_usuario, $con) {
    $stmt = $con->prepare("UPDATE usuario SET ultima_actividad = NOW() WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
}

if (isset($_SESSION['id_usuario'])) {
    $db = new Database();
    $con = $db->conectar();
    actualizarActividad($_SESSION['id_usuario'], $con);
    echo json_encode(['status' => 'updated']);
} else {
    echo json_encode(['status' => 'no_session']);
}