<?php
session_start();
require_once("../../../database/db.php");
$db = new Database();
$con = $db->conectar();

//  Verificar sesión activa antes de usarla
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    session_unset();
    session_destroy();
    header("Location: ../../../iniciosesion.php");
    exit;
}

$usu = $_SESSION['id_usuario'];

// Verificar que el usuario existe y obtener sus datos
$sql = $con->prepare("
    SELECT 
        u.*, 
        r.nom_rol AS nombre_rol,
        (SELECT COUNT(*) FROM detalle_usuario_partida dup WHERE dup.id_usuario1 = u.id_usuario) AS total_partidas,
        (SELECT SUM(puntos_total) FROM detalle_usuario_partida dup WHERE dup.id_usuario1 = u.id_usuario) AS puntos_totales
    FROM usuario u 
    INNER JOIN rol r ON u.id_rol = r.id_rol 
    WHERE u.id_usuario = ?
");
$sql->execute([$usu]);
$fila = $sql->fetch(PDO::FETCH_ASSOC);

if (!$fila) {
    session_unset();
    session_destroy();
    exit("Usuario no encontrado. <a href='../../../iniciosesion.php'>Inicia sesión nuevamente</a>");
}

// Consulta para obtener el historial de partidas del usuario con detalles
$sql = $con->prepare("
    SELECT 
        m.nomb_mundo,
        s.id_sala,
        p.id_partida,
        p.fecha_inicio,
        p.fecha_fin,|
        p.cantidad_jug,
        p.id_estado_part,
        dup.puntos_total AS mis_puntos,
        u.nomb_usu,
        (
            SELECT COUNT(*) 
            FROM detalle_usuario_partida d2 
            INNER JOIN usuario u2 ON d2.id_usuario1 = u2.id_usuario
            WHERE d2.id_partida = p.id_partida AND u2.vida <= 0
        ) AS jugadores_eliminados,
        (
            SELECT GROUP_CONCAT(DISTINCT u2.nomb_usu SEPARATOR ', ') 
            FROM detalle_usuario_partida d2 
            INNER JOIN usuario u2 ON d2.id_usuario1 = u2.id_usuario 
            WHERE d2.id_partida = p.id_partida
        ) AS participantes
    FROM 
        mundo m
    INNER JOIN 
        sala s ON m.id_mundo = s.id_mundo
    INNER JOIN 
        partida p ON s.id_sala = p.id_sala
    INNER JOIN 
        detalle_usuario_partida dup ON p.id_partida = dup.id_partida
    INNER JOIN 
        usuario u ON dup.id_usuario1 = u.id_usuario
    WHERE 
        dup.id_usuario1 = ?
    ORDER BY 
        p.fecha_inicio DESC
");
$sql->execute([$usu]);
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partidas Jugadas</title>
    <link rel="stylesheet" href="../../../controller/css/partidas.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">

    <script>
        //  Evitar volver atrás después de cerrar sesión
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.location.replace("../../../iniciosesion.php");
        };
    </script>
</head>

<body>        
<div class="contenedor">
    <a href="../player.php" class="btn-volver">Volver</a>
    
    <div class="stats-container">
        <h2 class="page-title-armamento">PARTIDAS JUGADAS</h2>
        <div class="player-stats">
            <div class="stat-item">
                <span class="stat-label">Total Partidas:</span>
                <span class="stat-value"><?= $fila['total_partidas'] ?? 0 ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Puntos Totales:</span>
                <span class="stat-value"><?= $fila['puntos_totales'] ?? 0 ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Jugador:</span>
                <span class="stat-value"><?= htmlspecialchars($fila['nomb_usu']) ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Rol:</span>
                <span class="stat-value"><?= htmlspecialchars($fila['nombre_rol']) ?></span>
            </div>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                <div>Mapa</div>
                <div>Sala</div>
                <div>Partida</div>
                <div>Inicio</div>
                <div>Fin</div>
                <div>Jugadores</div>
                <div>Mis Puntos</div>
                <div>Estado</div>
                <div>Participantes</div>
            </div>

            <?php
            if (empty($resultado)) {
                echo "<div class='table-row no-partidas'>No hay partidas jugadas.</div>";
            } else {
                foreach ($resultado as $fila_partida) {
                    $estado = '';
                    switch ($fila_partida['id_estado_part']) {
                        case 1: $estado = 'En progreso'; break;
                        case 2: $estado = 'Finalizada'; break;
                        case 3: $estado = 'Abierta'; break;
                        case 4: $estado = 'Cerrada'; break;
                        case 5: $estado = 'En juego'; break;
                        default: $estado = 'Desconocido';
                    }

                    $fecha_inicio = new DateTime($fila_partida['fecha_inicio']);
                    $fecha_fin = $fila_partida['fecha_fin'] ? new DateTime($fila_partida['fecha_fin']) : null;

                    echo "<div class='table-row'>";
                    echo "<div>" . htmlspecialchars($fila_partida['nomb_mundo']) . "</div>";
                    echo "<div>#" . htmlspecialchars($fila_partida['id_sala']) . "</div>";
                    echo "<div>#" . htmlspecialchars($fila_partida['id_partida']) . "</div>";
                    echo "<div>" . $fecha_inicio->format('d/m/Y H:i') . "</div>";
                    echo "<div>" . ($fecha_fin ? $fecha_fin->format('d/m/Y H:i') : '-') . "</div>";
                    echo "<div>" . htmlspecialchars($fila_partida['cantidad_jug']) . "/5" . 
                         " (" . $fila_partida['jugadores_eliminados'] . " eliminados)</div>";
                    echo "<div class='puntos'>" . htmlspecialchars($fila_partida['mis_puntos']) . "</div>";
                    echo "<div class='estado-" . strtolower(str_replace(' ', '-', $estado)) . "'>" . $estado . "</div>";
                    echo "<div class='participantes'>" . htmlspecialchars($fila_partida['participantes']) . "</div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
