<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../iniciosesion.php');
    exit;
}

// Verificar que el usuario es administrador
$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id_rol WHERE usuario.id_usuario = ? AND rol.id_rol = 1");
$sql->execute([$usu]);
if (!$sql->fetch()) {
    header('Location: ../../index.html');
    exit;
}

// Obtener estadísticas generales
$sql_stats = $con->prepare("
    SELECT 
        COUNT(DISTINCT p.id_partida) as total_partidas,
        COUNT(DISTINCT dup.id_usuario1) as total_jugadores,
        SUM(dup.puntos_total) as puntos_totales,
        MAX(dup.puntos_total) as mejor_puntuacion
    FROM partida p
    LEFT JOIN detalle_usuario_partida dup ON p.id_partida = dup.id_partida
");
$sql_stats->execute();
$stats = $sql_stats->fetch(PDO::FETCH_ASSOC);

// Consulta para obtener todas las partidas con detalles
$sql = $con->prepare("
    SELECT 
        p.id_partida,
        s.id_sala,
        m.nomb_mundo,
        p.fecha_inicio,
        p.fecha_fin,
        p.id_estado_part,
        p.cantidad_jug,
        GROUP_CONCAT(DISTINCT u.nomb_usu) as participantes,
        GROUP_CONCAT(DISTINCT CONCAT(u.nomb_usu, ':', dup.puntos_total)) as puntos_jugadores,
        (SELECT COUNT(*) 
         FROM usuario u2 
         INNER JOIN detalle_usuario_partida d2 ON u2.id_usuario = d2.id_usuario1
         WHERE d2.id_partida = p.id_partida AND u2.vida <= 0) as jugadores_eliminados
    FROM partida p
    LEFT JOIN sala s ON p.id_sala = s.id_sala
    LEFT JOIN mundo m ON s.id_mundo = m.id_mundo
    LEFT JOIN detalle_usuario_partida dup ON p.id_partida = dup.id_partida
    LEFT JOIN usuario u ON dup.id_usuario1 = u.id_usuario
    GROUP BY p.id_partida
    ORDER BY p.fecha_inicio DESC
");
$sql->execute();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Partidas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="../../controller/css/admin.css">
    <style>
        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 20px 0;
            padding: 20px;
            gap: 20px;
        }

        .stat-card {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
            min-width: 200px;
            border: 1px solid rgba(255, 215, 0, 0.3);
        }

        .stat-label {
            color: #ffd700;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
        }

        .table-responsive {
            margin: 20px;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            color: white;
        }

        th {
            background: rgba(0, 0, 0, 0.5);
            color: #ffd700;
            padding: 15px;
        }

        td {
            padding: 12px;
            vertical-align: middle;
        }

        .estado-1 { color: #ffcc00; } /* En progreso */
        .estado-2 { color: #00ff00; } /* Finalizada */
        .estado-3 { color: #00ffff; } /* Abierta */
        .estado-4 { color: #ff0000; } /* Cerrada */
        .estado-5 { color: #ff9900; } /* En juego */

        .participantes {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .participantes:hover {
            white-space: normal;
            max-width: none;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="admin.php">
                <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar">
                <span class="fw-bold">Rainbow Six Siege</span>
            </a>
            <div class="links-header">
                <a class="volver bi bi-arrow-left-circle" href="admin.php"> Volver </a>
            </div>
        </div>
    </nav>
</header>

<div class="admin-title mt-5 pt-5">
    <h1>HISTORIAL DE PARTIDAS</h1>
</div>

<div class="stats-container">
    <div class="stat-card">
        <div class="stat-label">Total Partidas</div>
        <div class="stat-value"><?= $stats['total_partidas'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Jugadores</div>
        <div class="stat-value"><?= $stats['total_jugadores'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Puntos Totales</div>
        <div class="stat-value"><?= $stats['puntos_totales'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Mejor Puntuación</div>
        <div class="stat-value"><?= $stats['mejor_puntuacion'] ?? 0 ?></div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>ID Partida</th>
                <th>Sala</th>
                <th>Mapa</th>
                <th>Estado</th>
                <th>Jugadores</th>
                <th>Eliminados</th>
                <th>Puntuaciones</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Participantes</th>
            </tr>
        </thead>
        <tbody>

<?php while ($partida = $sql->fetch(PDO::FETCH_ASSOC)): 
    // Procesar puntuaciones
    $puntos_array = [];
    if ($partida['puntos_jugadores']) {
        foreach (explode(',', $partida['puntos_jugadores']) as $puntuacion) {
            list($jugador, $puntos) = explode(':', $puntuacion);
            if ($puntos > 0) {
                $puntos_array[] = "$jugador: $puntos";
            }
        }
    }

    // Determinar estado
    $estado = '';
    switch($partida['id_estado_part']) {
        case 1: $estado = 'En progreso'; break;
        case 2: $estado = 'Finalizada'; break;
        case 3: $estado = 'Abierta'; break;
        case 4: $estado = 'Cerrada'; break;
        case 5: $estado = 'En juego'; break;
        default: $estado = 'Desconocido';
    }
?>
    <tr>
        <td><?= htmlspecialchars($partida['id_partida']) ?></td>
        <td><?= htmlspecialchars($partida['id_sala']) ?></td>
        <td><?= htmlspecialchars($partida['nomb_mundo']) ?></td>
        <td class="estado-<?= $partida['id_estado_part'] ?>">
            <?= htmlspecialchars($estado) ?>
        </td>
        <td><?= $partida['cantidad_jug'] ?>/5</td>
        <td><?= $partida['jugadores_eliminados'] ?></td>
        <td class="puntos" title="<?= htmlspecialchars(implode(', ', $puntos_array)) ?>">
            <?= implode(', ', array_slice($puntos_array, 0, 3)) ?><?= count($puntos_array) > 3 ? '...' : '' ?>
        </td>
        <td><?= date('d/m/Y H:i', strtotime($partida['fecha_inicio'])) ?></td>
        <td><?= $partida['fecha_fin'] ? date('d/m/Y H:i', strtotime($partida['fecha_fin'])) : '-' ?></td>
        <td class="participantes" title="<?= htmlspecialchars($partida['participantes']) ?>">
            <?= htmlspecialchars($partida['participantes']) ?>
        </td>
    </tr>
<?php endwhile; ?>

        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hacer que las puntuaciones y participantes sean expandibles al hacer hover
    const expandibles = document.querySelectorAll('.puntos, .participantes');
    expandibles.forEach(elem => {
        elem.addEventListener('mouseenter', function() {
            if (this.scrollWidth > this.clientWidth) {
                this.setAttribute('data-original-text', this.textContent);
                this.textContent = this.getAttribute('title');
            }
        });
        
        elem.addEventListener('mouseleave', function() {
            if (this.hasAttribute('data-original-text')) {
                this.textContent = this.getAttribute('data-original-text');
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
