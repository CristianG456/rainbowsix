<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();



$sql = $con->prepare("SELECT * FROM armas where armas.id_arma order by armas.id_arma ASC");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armas</title>
     <link rel="stylesheet" href="../../../controller/css/armas.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
    
</head>
<body>
     <!-- Video de fondo -->
    <video autoplay muted loop playsinline class="video-fondo">
        <source src="../../../controller/img/armas.mp4" type="video/mp4">
    </video>

    <!-- Contenido centrado arriba -->
    <div class="contenedor">
        
        <!-- volver  a lobby-->
        <a href="../player.php" class="btn-volver">Volver</a>
        
        <div>
            <h2 class="page-title-armamento">Armamento</h2>

            <!-- armas y fondo oscuro -->

            <div class="armas-wrapper">
                <div class="armas-container">
                    <?php
                    
                    if (!empty($resultado)) {
                        foreach ($resultado as $arma) {
                            
                            $img = $arma['img_arma'];
                            $img = trim($img, '"');
                            $img = trim($img, '"');
                            
                            $imgBasename = basename($img);
                            $imgPath = '../../../controller/img/' . $imgBasename;
                            $nombre = htmlspecialchars($arma['nomb_arma']);

                            echo "<div class=\"arma\">";
                            echo "<img src=\"{$imgPath}\" alt=\"{$nombre}\">";
                            echo "<p>{$nombre}</p>";
                            
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No hay armas registradas.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div>
            

            
            

                </div>
            </div>
        </div>

    </div>
    
</body>
</html>
