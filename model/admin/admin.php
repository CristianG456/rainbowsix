<?php
session_start();
require_once("../../database/db.php");

// Evitar que el navegador use versiones en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Verificar sesión activa antes de continuar
require_once("../../controller/validar_sesion.php"); 

// Conexión a la base de datos
$db = new Database();
$con = $db->conectar();
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtener datos del usuario actual
$usu = $_SESSION['id_usuario'] ?? 0;
$sql = $con->prepare("SELECT * FROM usuario 
                      INNER JOIN rol ON usuario.id_rol = rol.id_rol 
                      WHERE usuario.id_usuario = ?");
$sql->execute([$usu]);
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// -------------------- LOGOUT --------------------
if (isset($_POST['cerrar'])) {
    // Eliminar todas las variables de sesión
    $_SESSION = array();

    // Destruir la sesión completamente
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    session_write_close();

    // Redirigir al inicio de sesión
    header("Location: ../../iniciosesion.php");
    exit();
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
                <a class="navbar-brand d-flex align-items-center" href="#">
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

    <div class="admin-title mt-5 pt-5">
        <h1>BIENVENIDO ADMINISTRADOR</h1>
    </div>

    <div class="bloques-container">
        <div class="usuarios-bloque">
            <div class="usuarios-text">Jugadores</div>
            <a href="usuarios.php"><img src="../../controller/img/usuario.jpg" alt="Usuarios" class="usuarios-img"></a>
        </div>

        <div class="usuarios-bloque">
            <div class="usuarios-text">Historial</div>
            <a href="historial.php"><img src="../../controller/img/historial.jpg" alt="Usuarios" class="usuarios-img"></a>
        </div>

        <div class="usuarios-bloque">
            <div class="usuarios-text">Armas</div>
            <a href="nueva_arma.php"><img src="../../controller/img/arma.webp" alt="Usuarios" class="usuarios-img"></a>
        </div>

        <div class="usuarios-bloque">
            <div class="usuarios-text">Personajes</div>
            <a href="nuevopj.php"><img src="../../controller/img/personaje.avif" alt="Usuarios" class="usuarios-img"></a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>