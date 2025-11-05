<?php
session_start();
require_once("../../database/db.php");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$db = new Database();
$con = $db->conectar();

// Verificar sesión y rol administrador
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../iniciosesion.php');
    exit;
}

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("
    SELECT * FROM usuario 
    INNER JOIN rol ON usuario.id_rol = rol.id_rol 
    WHERE usuario.id_usuario = ? AND rol.id_rol = 1
");
$sql->execute([$usu]);
if (!$sql->fetch()) {
    header('Location: ../../index.html');
    exit;
}

// === PAGINACIÓN ===
$por_pagina = 10; // número de partidas por página
$pagina_actual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $por_pagina;

// Contar total de partidas
$total_stmt = $con->query("SELECT COUNT(*) FROM partida");
$total_partidas = $total_stmt->fetchColumn();
$total_paginas = ceil($total_partidas / $por_pagina);

// Consulta de partidas con límite y offset
$sql_partidas = $con->prepare("
    SELECT 
        p.id_partida,
        p.fecha_inicio,
        p.fecha_fin,
        p.id_sala,
        p.id_ganador,
        u.nomb_usu AS ganador
    FROM partida p
    LEFT JOIN usuario u ON p.id_ganador = u.id_usuario
    ORDER BY p.fecha_inicio DESC
    LIMIT :inicio, :por_pagina
");
$sql_partidas->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$sql_partidas->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
$sql_partidas->execute();
$partidas = $sql_partidas->fetchAll(PDO::FETCH_ASSOC);
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
        body {
            background: #0b0b0b;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
        }
        .admin-title {
            text-align: center;
            margin-top: 6rem;
            color: #ffd700;
            font-size: 2rem;
        }
        .table-container {
            margin: 2rem auto;
            width: 90%;
            background: rgba(20, 20, 20, 0.85);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.1);
        }
        table {
            color: #fff;
            text-align: center;
        }
        th {
            color: #ffd700;
            background: rgba(255, 255, 255, 0.1);
        }
        td {
            vertical-align: middle;
        }
        tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .ganador {
            color: #00ff88;
            font-weight: bold;
        }
        .sin-ganador {
            color: #ff6666;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .page-link {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #ffd700;
        }
        .page-item.active .page-link {
            background-color: #ffd700;
            color: #000;
            font-weight: bold;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="admin.php">
                <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar me-2">
                <span class="fw-bold">Rainbow Six Siege</span>
            </a>
            <div class="links-header">
                <a class="volver bi bi-arrow-left-circle" href="admin.php"> Volver </a>
            </div>
        </div>
    </nav>
</header>

<div class="admin-title">HISTORIAL DE PARTIDAS</div>

<div class="table-container">

    <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="text-warning mb-0">Listado de partidas</h4>
    <a href="exportar_partidas.php" class="btn btn-success">
        Exportar a Excel
    </a>
</div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>ID Partida</th>
                    <th>ID Sala</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Ganador</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($partidas): ?>
                    <?php foreach ($partidas as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['id_partida']) ?></td>
                            <td><?= htmlspecialchars($p['id_sala'] ?? '-') ?></td>
                            <td><?= $p['fecha_inicio'] ? date('d/m/Y H:i', strtotime($p['fecha_inicio'])) : '-' ?></td>
                            <td><?= $p['fecha_fin'] ? date('d/m/Y H:i', strtotime($p['fecha_fin'])) : '-' ?></td>
                            <td class="<?= $p['ganador'] ? 'ganador' : 'sin-ganador' ?>">
                                <?= htmlspecialchars($p['ganador'] ?? 'Sin ganador') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-danger text-center">No hay partidas registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    <?php if ($total_paginas > 1): ?>
        <nav>
            <ul class="pagination">
                <li class="page-item <?= $pagina_actual == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina_actual == $total_paginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

