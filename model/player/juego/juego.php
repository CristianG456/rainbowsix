<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'] ?? null;

if (!$usu) {
    header("Location: ../../iniciosesion.php");
    exit;
}

// 🔹 Obtener nivel del usuario
$sql = $con->prepare("SELECT id_nivel FROM usuario WHERE id_usuario = ?");
$sql->execute([$usu]);
$fila = $sql->fetch(PDO::FETCH_ASSOC);
$nivelUsuario = (int)($fila['id_nivel'] ?? 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundos</title>
    <link rel="stylesheet" href="../../../controller/css/juego.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
</head>
<body>

    <!-- Fondo de video -->
    <video autoplay muted loop playsinline class="video-fondo">
        <source src="../../../controller/img/mundos2.mp4" type="video/mp4">
    </video>

    <a href="../player.php" class="btn-volver">Volver</a>

        <div class="menu-mundos">
            <div class="mundo">
               
                <h2>Ciudad (Normal)</h2>
                <a href="normal/ingreso_sala.php">
                    <img src="../../../controller/img/ciudad.jpg" alt="desierto" class="imagen-mundo">
                </a>
            </div>


        <!-- Mundo Clasificatoria -->
        <?php
        // Bloquea sólo si el usuario es BRONCE 
            $bloqueado = ($nivelUsuario === 1);
        ?>
        <div class="mundo" data-locked="<?= $bloqueado ? '1' : '0' ?>">
            <h2>Ciudad (Clasificatoria)</h2>

            <?php if ($bloqueado): ?>
                <div class="overlay-bloqueo">
                    <img src="../../../controller/img/ciudad.jpg" alt="Ciudad Clasificatoria" class="imagen-mundo bloqueada">
                    <div class="bloqueo-info">
                        <span class="candado">🔒</span>
                        <p>Nivel Plata requerido</p>
                    </div>
                </div>
            <?php else: ?>
                <a href="clasificatoria/clasificatoria.php">
                    <img src="../../../controller/img/ciudad.jpg" alt="Ciudad Clasificatoria" class="imagen-mundo">
                </a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
