<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol= rol.id_rol WHERE usuario.id_usuario =$usu");
$sql->execute();
$fila = $sql->fetch();

if (isset($_POST['cerrar'])) {
    session_destroy();
    header('location:../../index.html');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administrador - R6</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="../../controller/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="">
                    <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar">
                    <span class="fw-bold"> Rainbow Six Siege </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="d-flex">
                    <form method="POST">
                        <input type="submit" value="Cerrar Sesión" name="cerrar" class="btn btn-danger px-4 fw-bold juega-btn">
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <div class="admin-title">
        <h1>BIENVENIDO AMINISTRADOR</h1>
    </div>

<div class="bloques-container">
    <div class="usuarios-bloque">
        <div class="usuarios-text">ACTUALIZACIÓN DE JUGADORES</div>
        <a href="usuarios.php"><img src="../../controller/img/usuario.jpg" alt="Usuarios" class="usuarios-img"></a>
    </div>

    <div class="usuarios-bloque">
        <div class="usuarios-text">HISTORIAL DE PARTIDAS</div>
        <a href="historial.php"><img src="../../controller/img/usuario.jpg" alt="Usuarios" class="usuarios-img"></a>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
