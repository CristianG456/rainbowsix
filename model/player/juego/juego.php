<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol= rol.id_rol WHERE usuario.id_usuario =$usu");
$sql->execute();
$fila = $sql->fetch();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mundos</title>
    <link rel="stylesheet" href="../../../controller/css/juego.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 

</head>
<body>

    <!-- Video de fondo -->
        <video autoplay muted loop playsinline class="video-fondo">
            <source src="../../../controller/img/mundos2.mp4" type="video/mp4">
        </video>

        
        <div>
            
            
            <h1>SELECIONA UN MUNDO</h1>
            
            <a href="../player.php" class="btn-volver">Volver</a>

            
        <div class="menu-mundos">
            <div class="mundo">
               
                <h2>Ciudad (Normal)</h2>
                <a href="normal/ingreso_sala.php">
                    <img src="../../../controller/img/ciudad.jpg" alt="desierto" class="imagen-mundo">
                </a>
            </div>

            <div class="mundo">
                <h2>Ciudad (Clasificatoria)</h2>
                <a href="clasificatoria/clasificatoria.php">            
                    <img src="../../../controller/img/ciudad.jpg" alt="ciudad" class="imagen-mundo">
                </a>
            </div>
        </div>


    </div>


    


    
</body>
</html>