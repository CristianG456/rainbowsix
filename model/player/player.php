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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RainbowSix</title>
  <link rel="stylesheet" href="../../controller/css/lobby.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
</head>
<body>

    <!-- Video de fondo -->
    <video autoplay muted loop playsinline class="video-fondo">
        <source src="../../controller/img/animacion2.mp4" type="video/mp4">
    </video>




    <!-- Contenido centrado arriba -->
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
            <span>Agente_01</span>
            <span>Nivel: 25</span>
        </div>

<div class="usuario-progress">
    <label>
        Puntos:
        <progress id="puntosnivel" max="100" value="70">70%</progress>
    </label>

    <form method="POST">
        <input type="submit" value="Cerrar Sesión" name="cerrar" class="cerrar-sesion-btn">
    </form>
</div>

<div class="juego-container">
  <div class="mifig-container">
    <img src="../../controller/img/nokkpj.webp" alt="Personaje" class="personaje">
  </div>
</div>




  


</body>
</html>



