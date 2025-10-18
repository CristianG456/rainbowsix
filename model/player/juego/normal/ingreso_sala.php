<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'] ?? null;
if (!$usu) {
    header('Location: ../../../iniciosesion.php');
    exit;
}

/* 🔹 Inicializar salas base */
function inicializarSalasBase($con, $cantidad = 5) {
    $check = $con->prepare("SELECT COUNT(*) FROM sala");
    $check->execute();
    $numSalas = $check->fetchColumn();

    for ($i = $numSalas + 1; $i <= $cantidad; $i++) {
        $stmt = $con->prepare("
            INSERT INTO sala (fecha_creacion, id_estado_sala, id_mundo, id_nivel, url_sala)
            VALUES (NOW(), 3, 1, 1, :url)
        ");
        $stmt->execute([':url' => 'auto_'.time().'_'.$i]);
        usleep(1000); // Evitar IDs duplicados en la URL
    }
}

/* 🔹 Función para unir jugador a sala */
function unirJugador($con, $id_usuario, $id_sala, $maxJugadores = 5) {
    $con->beginTransaction();
    try {
        // Buscar última partida activa de la sala
        $stmt = $con->prepare("
            SELECT * FROM partida 
            WHERE id_sala = ? AND id_estado_part NOT IN (4,8)
            ORDER BY fecha_inicio DESC 
            LIMIT 1 FOR UPDATE
        ");
        $stmt->execute([$id_sala]);
        $partida = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($partida) {
            $id_partida = $partida['id_partida'];

            // Contar jugadores activos
            $cnt = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida = ?");
            $cnt->execute([$id_partida]);
            $jugadoresActuales = $cnt->fetchColumn();

            if ($jugadoresActuales < $maxJugadores) {
                // Insertar jugador solo si no existe ya
                $check = $con->prepare("
                    SELECT * FROM detalle_usuario_partida 
                    WHERE id_partida = ? AND (id_usuario1 = ? OR id_usuario2 = ?)
                ");
                $check->execute([$id_partida, $id_usuario, $id_usuario]);
                if (!$check->fetch()) {
                    $sql = $con->prepare("
                        INSERT INTO detalle_usuario_partida 
                        (puntos_total, id_usuario1, id_usuario2, id_partida, id_arma)
                        VALUES (0, :id_usuario, NULL, :id_partida, 1)
                    ");
                    $sql->execute([':id_usuario' => $id_usuario, ':id_partida' => $id_partida]);
                }
            } else {
                // Partida llena → crear nueva
                $stmt = $con->prepare("
                    INSERT INTO partida (fecha_inicio, fecha_fin, cantidad_jug, id_estado_part, id_sala)
                    VALUES (NOW(), NOW(), 0, 3, ?)
                ");
                $stmt->execute([$id_sala]);
                $id_partida = $con->lastInsertId();

                $sql = $con->prepare("
                    INSERT INTO detalle_usuario_partida 
                    (puntos_total, id_usuario1, id_usuario2, id_partida, id_arma)
                    VALUES (0, :id_usuario, NULL, :id_partida, 1)
                ");
                $sql->execute([':id_usuario' => $id_usuario, ':id_partida' => $id_partida]);
            }
        } else {
            // Primera partida de la sala
            $stmt = $con->prepare("
                INSERT INTO partida (fecha_inicio, fecha_fin, cantidad_jug, id_estado_part, id_sala)
                VALUES (NOW(), NOW(), 0, 3, ?)
            ");
            $stmt->execute([$id_sala]);
            $id_partida = $con->lastInsertId();

            $sql = $con->prepare("
                INSERT INTO detalle_usuario_partida 
                (puntos_total, id_usuario1, id_usuario2, id_partida, id_arma)
                VALUES (0, :id_usuario, NULL, :id_partida, 1)
            ");
            $sql->execute([':id_usuario' => $id_usuario, ':id_partida' => $id_partida]);
        }

        // Actualizar cantidad_jug
        $cnt = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida = ?");
        $cnt->execute([$id_partida]);
        $nueva = $cnt->fetchColumn();

        $upd = $con->prepare("UPDATE partida SET cantidad_jug = :nueva WHERE id_partida = :id_partida");
        $upd->execute([':nueva' => $nueva, ':id_partida' => $id_partida]);

        $con->commit();
        return $id_partida;
    } catch(Exception $e) {
        if ($con->inTransaction()) $con->rollBack();
        throw $e;
    }
}

/* 🔹 Inicializar salas base */
inicializarSalasBase($con, 5);

/* 🔹 Listar todas las salas */
$sqlSalas = $con->prepare("
    SELECT s.id_sala, s.id_mundo, m.nomb_mundo, s.id_estado_sala, e.estado
    FROM sala s
    INNER JOIN mundo m ON s.id_mundo = m.id_mundo
    INNER JOIN estado e ON s.id_estado_sala = e.id_estado
    ORDER BY s.id_sala ASC
");
$sqlSalas->execute();
$salas = $sqlSalas->fetchAll(PDO::FETCH_ASSOC);

/* 🔹 Procesar unión a sala */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_sala'])) {
    $id_sala = intval($_POST['id_sala']);
    try {
        $id_partida = unirJugador($con, $usu, $id_sala, 5);
        header("Location: ./combate.php?partida=$id_partida&sala=$id_sala");
        exit;
    } catch(PDOException $e) {
        echo "<script>alert('Error al unirse: ".addslashes($e->getMessage())."');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Salas - Modo Normal</title>
<link rel="stylesheet" href="../../../../controller/css/normal.css">
</head>
<body>
<a href="../juego.php" class="btn-volver">Volver</a>
<main class="contenedor-salas">
    <h1>Salas - Modo Normal</h1>

    <?php if(empty($salas)): ?>
        <p class="sin-salas">No hay salas disponibles.</p>
    <?php else: ?>
        <section class="lista-salas">
        <?php foreach($salas as $s): ?>
            <article class="sala-card">
                <img src='../../../../controller/img/desierto.webp' alt='Mundo'>
                <div class="sala-info">
                    <h3>Sala #<?= htmlspecialchars($s['id_sala']) ?> - <?= htmlspecialchars($s['nomb_mundo']) ?></h3>
                    <p>Estado: <?= htmlspecialchars($s['estado']) ?></p>
                    <form method='post'>
                        <input type='hidden' name='id_sala' value='<?= htmlspecialchars($s['id_sala']) ?>'>
                        <button type='submit' class="btn-unirse">Unirse</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
