<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();


$sql = $con->prepare("SELECT * FROM armas");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armas</title>
     <link rel="stylesheet" href="../../../controller/css/lobby.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
</head>
<body>
     <!-- Video de fondo -->
    <video autoplay muted loop playsinline class="video-fondo">
        <source src="../../../controller/img/armas.mp4" type="video/mp4">
    </video>

    <!-- Contenido centrado arriba -->
    <div class="contenedor">
        <h1>Armamento</h1>
        

        <div>
            <h2 style="margin-top: 40px;">Armas</h2>

            <!-- armas y fondo oscuro -->

            <div class="armas-wrapper">
                <div class="armas-container">
                    <div class="arma">
                        <img src="../../../controller/img/puño.jpg" alt="Puño">
                        <p>Puño</p>
                    </div>

                    <div class="arma">
                        <img src="../../../controller/img/cuchillo.png" alt="Cuchillo" style="width: 65px; height: auto;">
                        <p>Cuchillo</p>
                    </div>

                    <div class="arma">
                        <img src="../../../controller/img/pistola.png" alt="Pistola">
                        <p>Pistola</p>
                    </div>

                    <div class="arma">
                        <img src="../../../controller/img/SUBFUSIL.png" alt="M249" style="width: 150px; height: auto;">
                        <p>Subfusil</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            

            
            <div class="armas-wrapper">
                <div class="armas-container">
                    <div class="arma">
                        <img src="../../../controller/img/fusilasalto.png" alt="M9">
                        <p>Fuisil de Asalto</p>
                    </div>

                    <div class="arma">
                        <img src="../../../controller/img/escopeta.png" alt="Bailiff 410">
                        <p>Escopeta</p>
                    </div>

                    <div class="arma">
                        <img src="../../../controller/img/franco.png" alt="Pistola SD">
                        <p>Rifle de Presicion</p>
                    </div>

                </div>
            </div>
        </div>

    </div>
    
</body>
</html>
