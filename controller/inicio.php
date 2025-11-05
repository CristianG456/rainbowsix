<?php
session_start();
require_once("../database/db.php");

$db = new Database();
$con = $db->conectar();
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//  BLOQUE DE INICIO DE SESIÓN
if (isset($_POST["iniciar"])) {

    $nombre = trim($_POST["nomb_usu"] ?? '');
    $contra = $_POST["contra_usu"] ?? '';

    if ($nombre === '' || $contra === '') {
        echo "<script>alert('Complete usuario y contraseña.'); window.location='../iniciosesion.php';</script>";
        exit();
    }

    // Traer datos del usuario incluyendo su estado
    $sql = $con->prepare("
        SELECT id_usuario, nomb_usu, contra_usu, id_rol, id_estado_usu
        FROM usuario
        WHERE nomb_usu = :nombre
        LIMIT 1
    ");
    $sql->execute([':nombre' => $nombre]);
    $fila = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$fila) {
        echo "<script>alert('Usuario no encontrado'); window.location='../iniciosesion.php';</script>";
        exit();
    }

    // Validar contraseña
    if (!password_verify($contra, $fila['contra_usu'])) {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='../iniciosesion.php';</script>";
        exit();
    }

    // Si está bloqueado (id_estado_usu = 2)
    if ((int)$fila['id_estado_usu'] === 2) {
        echo "<script>
            alert('Tu cuenta está bloqueada. Espera a que el administrador la active.');
            window.location='../iniciosesion.php';
        </script>";
        session_destroy();
        exit();
    }

    // Guardar sesión
    $_SESSION['id_usuario'] = $fila['id_usuario'];
    $_SESSION['usuario'] = $fila['nomb_usu'];
    $_SESSION['rol'] = (int)$fila['id_rol'];
    $_SESSION['estado'] = (int)$fila['id_estado_usu'];
    
    // ✅ Marcar al usuario como activo solo si no estaba bloqueado
    $stmt = $con->prepare("UPDATE usuario SET id_estado_usu = 1 WHERE id_usuario = ?");
    $stmt->execute([$fila['id_usuario']]);

    // Redirección por rol
    switch ($_SESSION['rol']) {
        case 1:
            header("Location: ../model/admin/admin.php");
            exit();

        case 2:
            header("Location: ../model/player/player.php");
            exit();

        default:
            header("Location: ../inicio.php");
            exit();
    }
}

//  BLOQUE DE CIERRE DE SESIÓN (DESTROYER)
if (isset($_GET['destroyer']) && $_GET['destroyer'] === '1') {
    if (isset($_SESSION['id_usuario'])) {
        $id_usuario = $_SESSION['id_usuario'];

        // Marcar usuario como desconectado (id_estado_usu = 0)
        $stmt = $con->prepare("UPDATE usuario SET id_estado_usu = 0 WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
    }

    // Eliminar sesión completamente
    $_SESSION = array();
    session_destroy();
    session_write_close();

    header("Location: ../iniciosesion.php");
    exit();
}
?>

