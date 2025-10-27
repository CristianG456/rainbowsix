<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

// Obtener usuario actual
$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("
    SELECT usuario.*, rol.nom_rol, nivel.nomb_nivel 
    FROM usuario 
    INNER JOIN rol ON usuario.id_rol = rol.id_rol 
    INNER JOIN nivel ON usuario.id_nivel = nivel.id_nivel 
    WHERE usuario.id_usuario = ?
");
$sql->execute([$usu]);
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// Obtener todas las armas
$sql = $con->prepare("SELECT * FROM armas ORDER BY id_arma ASC");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armas</title>
    <link rel="stylesheet" href="../../../controller/css/armas.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
</head>
<body>

    <div class="video-fondo">
            <iframe
            src="https://www.youtube-nocookie.com/embed/W-xnP-yw_Sc?autoplay=1&mute=1&loop=1&playlist=W-xnP-yw_Sc&controls=0&rel=0&modestbranding=1"
            allow="autoplay; fullscreen"
            allowfullscreen>
            </iframe>

    </div>



    <div class="contenedor">
        <!-- Botón para volver -->
        <a href="../player.php" class="btn-volver">Volver</a>
        
        <div>
            <h2 class="page-title-armamento">Armamento</h2>

            <div class="armas-wrapper">
                <div class="armas-container">
                    <?php
                    if (!empty($resultado)) {
                        $nivelUsuario = (int)$fila['id_nivel'];

                        // Precargar niveles en un array asociativo
                        $niveles = $con->query("SELECT id_nivel, nomb_nivel FROM nivel")->fetchAll(PDO::FETCH_KEY_PAIR);

                        foreach ($resultado as $arma) {
                            // Construir ruta de imagen
                            $imgPath = '../../../controller/img/' . htmlspecialchars($arma['img_arma']);

                            // Validar existencia (opcional)
                            if (!file_exists($imgPath)) {
                                $imgPath = '../../../controller/img/sinimagen.png'; // Imagen por defecto
                            }

                            $nombre = htmlspecialchars($arma['nomb_arma']);
                            $nivelRequerido = (int)$arma['id_nivel_arma'];
                            $bloqueada = $nivelUsuario < $nivelRequerido ? 1 : 0;

                            echo "<div class='arma' data-locked='{$bloqueada}'>";
                            echo "<img src='{$imgPath}' alt='{$nombre}'>";
                            echo "<p>{$nombre}</p>";

                            // Mostrar candado si está bloqueada
                            if ($bloqueada) {
                                $nombreNivel = $niveles[$nivelRequerido] ?? "Nivel {$nivelRequerido}";
                                echo "<div class='lock-overlay'>";
                                echo "<div class='lock-icon'>🔒</div>";
                                echo "<div class='lock-text'>{$nombreNivel}</div>";
                                echo "</div>";
                            }

                            echo "</div>";
                        }
                    } else {
                        echo "<p>No hay armas registradas.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>

