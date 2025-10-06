<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol= rol.id_rol WHERE usuario.id_usuario =$usu");
$sql->execute();
$fila = $sql->fetch();

$sql = $con->prepare("SELECT 
    m.nomb_mundo, 
    s.id_sala, 
    p.id_partida, 
    p.fecha_inicio, 
    p.fecha_fin, 
    p.cantidad_jug
FROM 
    mundo m
INNER JOIN 
    sala s ON m.id_mundo = s.id_mundo
INNER JOIN 
    partida p ON s.id_sala = p.id_sala;");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partidas Jugadas</title>
    <link rel="stylesheet" href="../../../controller/css/partidas.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
</head>
<body>        
<div class="contenedor">
    <a href="../player.php" class="btn-volver">Volver</a>
    
    <div>
        <h2 class="page-title-armamento">PARTIDAS JUGADAS</h2>
        
        <div class="table-container">
    <div class="table-header">
        <div>Nombre del Mundo</div>
        <div>N° Sala</div>
        <div>N° Partida</div>
        <div>Fecha de Inicio</div>
        <div>Fecha de Fin</div>
        <div>Cantidad de Jugadores</div>
        <div>Resultado</div>
    </div>

    <?php
    if (empty($resultado)) {
        echo "<div class='table-row no-partidas'>No hay partidas jugadas.</div>";
    } else {
        foreach ($resultado as $fila_partida) {
            echo "<div class='table-row'>";
            echo "<div>" . htmlspecialchars($fila_partida['nomb_mundo']) . "</div>";
            echo "<div>" . htmlspecialchars($fila_partida['id_sala']) . "</div>";
            echo "<div>" . htmlspecialchars($fila_partida['id_partida']) . "</div>";
            echo "<div>" . htmlspecialchars($fila_partida['fecha_inicio']) . "</div>";
            echo "<div>" . htmlspecialchars($fila_partida['fecha_fin']) . "</div>";
            echo "<div>" . htmlspecialchars($fila_partida['cantidad_jug']) . "</div>";
            echo "<div>Por determinar</div>";
            echo "</div>";
        }
    }
    ?>
</div>
    </div>
</div>


</body>
</html>