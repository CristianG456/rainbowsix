<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol= rol.id_rol WHERE usuario.id_usuario = ?");
$sql->execute([$usu]);
$fila = $sql->fetch();

$sql = $con->prepare("SELECT * FROM avatar");
$sql->execute();
$personajes = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agentes</title>
    <link rel="stylesheet" href="../../../controller/css/agentes.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">
</head>

<body>

    <div class="contenedor">
        <a href="../player.php" class="btn-volver">Volver</a>
        <h2 class="page-title-armamento">Selecciona tu agente</h2>
    </div>

    <div class="contenedor">
        <?php foreach ($personajes as $p): ?>
            <?php
            $imagen_personaje = basename(str_replace('"', '', $p['url_personaje']));
            $imagen_avatar = basename(str_replace('"', '', $p['url_avatar']));
            ?>
            <div class="personaje" data-id="<?php echo $p['id_avatar']; ?>">
                <div class="avatar-box">
                    <img src="../../../controller/img/<?php echo htmlspecialchars($imagen_avatar); ?>"
                        alt="Avatar de <?php echo htmlspecialchars($p['nomb_avat']); ?>"
                        class="avatar">
                </div>

                <div class="personaje-box">
                    <img src="../../../controller/img/<?php echo htmlspecialchars($imagen_personaje); ?>"
                        alt="Personaje <?php echo htmlspecialchars($p['nomb_avat']); ?>"
                        class="personaje-img">
                </div>

                <h3><?php echo htmlspecialchars($p['nomb_avat']); ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".personaje").click(function() {
                const idAvatar = $(this).data("id");

                $.ajax({
                    url: "seleccionar_personaje.php",
                    method: "POST",
                    data: {
                        id_avatar: idAvatar
                    },
                    success: function(response) {
                        window.location.href = "../player.php";
                    },
                    error: function() {
                        alert("Error al seleccionar el personaje.");
                    }
                });
            });
        });
    </script>

</body>

</html>