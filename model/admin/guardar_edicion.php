<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $estado = $_POST['id_estado'];

    if (!empty($id) && !empty($estado)) {
        $update = $con->prepare("UPDATE usuario 
            SET id_estado_usu = :estado 
            WHERE id_usuario = :id");

        $update->execute([
            ':estado' => $estado,
            ':id' => $id
        ]);
    }
}

header('Location: usuarios.php');
exit;
