<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (isset($_POST['id_avatar'])) {
    $id_avatar = $_POST['id_avatar'];
    $id_usuario = $_SESSION['id_usuario'];

    $sql = $con->prepare("SELECT * FROM avatar WHERE id_avatar = ?");
    $sql->execute([$id_avatar]);
    $avatar = $sql->fetch(PDO::FETCH_ASSOC);

    if ($avatar) {
        $_SESSION['id_avatar'] = $id_avatar;
        $_SESSION['url_personaje'] = $avatar['url_personaje'];

        $update = $con->prepare("UPDATE usuario SET id_avatar = ? WHERE id_usuario = ?");
        $update->execute([$id_avatar, $id_usuario]);

        echo "ok";
    } else {
        echo "error";
    }
}
