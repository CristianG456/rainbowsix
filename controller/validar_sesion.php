<?php
// Iniciar sesión solo si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  Ruta segura y dinámica hacia la base de datos
require_once(__DIR__ . "/../database/db.php");

$db = new Database();
$con = $db->conectar();
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//  Verificar sesión activa
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header("Location: ../iniciosesion.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

//  Consultar el estado actual del usuario
$sql = $con->prepare("SELECT id_estado_usu, id_rol FROM usuario WHERE id_usuario = ?");
$sql->execute([$id_usuario]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

//  Si no existe el usuario o fue eliminado
if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../iniciosesion.php");
    exit();
}

//  Si el usuario está bloqueado
if ((int)$user['id_estado_usu'] === 2) {
    session_unset();
    session_destroy();
    echo "<script>
        alert('Tu cuenta ha sido bloqueada por un administrador.');
        window.location='../iniciosesion.php';
    </script>";
    exit();
}

//  Mantener los valores actualizados en la sesión
$_SESSION['estado'] = (int)$user['id_estado_usu'];
$_SESSION['rol'] = (int)$user['id_rol'];
?>
