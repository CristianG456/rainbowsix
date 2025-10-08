<?php
session_start();
require_once("../../../../database/db.php");
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
    <title>Desierto</title>
    <link rel="stylesheet" href="../../../../controller/css/desierto.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 


</head>
<body>
   <a href="../juego.php" class="btn-volver">Volver</a>
    
    <div>
        





    </div>


    
</body>
</html>