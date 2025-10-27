<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.html");
    exit();
}

$usu = $_SESSION['id_usuario'];

// Datos del usuario
$sql = $con->prepare("
    SELECT u.*, r.nom_rol 
    FROM usuario u 
    INNER JOIN rol r ON u.id_rol = r.id_rol 
    WHERE u.id_usuario = ?
");
$sql->execute([$usu]);
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// Avatar del usuario
$sqlAvatar = $con->prepare("
    SELECT url_avatar, url_personaje
    FROM avatar
    WHERE id_avatar = ?
");
$sqlAvatar->execute([$fila['id_avatar']]);
$avatar = $sqlAvatar->fetch(PDO::FETCH_ASSOC);

// Nivel y progreso
$puntosTotales = $fila['puntos'] ?? 0;
$puntosPorNivel = 250;
$nivelId = min(floor($puntosTotales / $puntosPorNivel) + 1, 4);
$puntosDentroNivel = $puntosTotales % $puntosPorNivel;
$puntosMaximosNivel = $puntosPorNivel;
$porcentaje = ($puntosMaximosNivel > 0) ? min(100, ($puntosDentroNivel / $puntosMaximosNivel) * 100) : 0;

// Actualizar nivel si cambió
if ($fila['id_nivel'] != $nivelId) {
    $sqlActualizarNivel = $con->prepare("UPDATE usuario SET id_nivel = ? WHERE id_usuario = ?");
    $sqlActualizarNivel->execute([$nivelId, $usu]);
}

// Datos del nivel
$sqlNivel = $con->prepare("SELECT nomb_nivel, url_nivel FROM nivel WHERE id_nivel = ?");
$sqlNivel->execute([$nivelId]);
$nivel = $sqlNivel->fetch(PDO::FETCH_ASSOC);
$nombreNivel = $nivel['nomb_nivel'] ?? '';
$urlIcono = $nivel['url_nivel'] ?? '';

// Cerrar sesión
if (isset($_POST['cerrar'])) {
    session_destroy();
    header("Location: ../../index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RainbowSix</title>
    <link rel="stylesheet" href="../../controller/css/lobby.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">



</head>

<body>
    <video autoplay muted loop playsinline class="video-fondo">
        <source src="../../controller/img/animacion2.mp4" type="video/mp4">
    </video>

    <div class="contenedor">
        <h1>Rainbow Six</h1>

        <div class="menu">
            <a href="juego/juego.php">Jugar</a>
            <a href="agentes/agentes.php">Agentes</a>
            <a href="armas/armas.php">Armas</a>
            <a href="partidas/partidas.php">Partidas Jugadas</a>
        </div>

        <div class="puntos">
            <div class="usuario-info">
                <span class="nombre-usuario"><?php echo htmlspecialchars($fila['nomb_usu']); ?></span>

                <?php if ($avatar && !empty($avatar['url_avatar'])): ?>
                    <img src="../../controller/img/<?php echo htmlspecialchars($avatar['url_avatar']); ?>"
                         alt="Avatar del usuario" class="avatar-usuario">
                <?php else: ?>
                    <img src="../../controller/img/default.webp"
                         alt="Avatar por defecto" class="avatar-usuario">
                <?php endif; ?>

                <span class="nivel">Nivel: <?php echo $nivelId; ?></span>

                <?php if ($urlIcono): ?>
                    <img src="../../controller/img/<?php echo htmlspecialchars($nivel['url_nivel']); ?>"
                         class="icono-rango" alt="Icono de nivel">
                <?php endif; ?>
            </div>

            <div class="usuario-progress">
                <label>
                    Rango: <?php echo htmlspecialchars($nombreNivel); ?><br>
                    Puntos: <?php echo $puntosDentroNivel . " / " . $puntosMaximosNivel; ?>
                </label>
                <br>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%;"></div>
                </div>
                <br>
                <form method="POST">
                    <input type="submit" value="Cerrar Sesión" name="cerrar" class="cerrar-sesion-btn">
                </form>
            </div>
        </div>

        <div class="juego-container">
            <div class="mifig-container">
                <?php if ($avatar && !empty($avatar['url_personaje'])): ?>
                    <img src="../../controller/img/<?php echo htmlspecialchars($avatar['url_personaje']); ?>"
                         alt="Personaje Seleccionado" class="personaje">
                <?php else: ?>
                    <a href="../player/agentes/agentes.php" class="enlace-personaje">
                        <img src="../../controller/img/default.webp" alt="Selecciona un personaje" class="personaje1">
                        Selecciona tu personaje
                    </a>
                <?php endif; ?>
            </div>

            <!-- ✅ Aquí se inserta el logo -->
            <img src="../../controller/img/logo.png" alt="Logo del juego" class="logo">
        </div>
    </div>
</body>
</html>
