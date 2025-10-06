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
        <a href="#">Jugar</a>
        <a href="agentes/agentes.php">Agentes</a>
        <a href="armas/armas.php">Armas</a>
        <a href="#">Partidas Jugadas</a>
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
        </div>
    </div>

    <div class="header-left">
        <img src="../../controller/img/glazz.png"  alt="" class="imagen-lateral">
       
    </div>




  


</body>
</html>



