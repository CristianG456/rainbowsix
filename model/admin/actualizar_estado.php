<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (isset($_POST['id_usuario'], $_POST['estado'])) {
    $id_usuario = (int)$_POST['id_usuario'];
    $nuevo_estado = (int)$_POST['estado'];

    $sql = $con->prepare("UPDATE usuario SET id_estado_usu = ? WHERE id_usuario = ?");
    $sql->execute([$nuevo_estado, $id_usuario]);

    if ($sql->rowCount() > 0) {
        echo "<script>alert('Estado actualizado correctamente'); window.location='usuarios.php';</script>";
    } else {
        echo "<script>alert('No se pudo actualizar el estado'); window.location='usuarios.php';</script>";
    }
} else {
    echo "<script>alert('Datos incompletos'); window.location='usuarios.php';</script>";
}
?>
