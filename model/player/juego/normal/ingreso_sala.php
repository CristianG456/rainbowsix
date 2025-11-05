<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'] ?? null;
if (!$usu) {
    header('Location: ../../../../iniciosesion.php');
    exit;
}

// Verificar y crear columna ultima_actividad si no existe
$check = $con->query("SHOW COLUMNS FROM usuario LIKE 'ultima_actividad'");
if ($check->rowCount() == 0) {
    $con->query("ALTER TABLE usuario ADD COLUMN ultima_actividad DATETIME DEFAULT CURRENT_TIMESTAMP");
    // Actualizar todos los usuarios existentes con la hora actual
    $con->query("UPDATE usuario SET ultima_actividad = NOW()");
}

// Verificar que el usuario existe
$sqlUser = $con->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ?");
$sqlUser->execute([$usu]);
if (!$sqlUser->fetchColumn()) exit("Usuario no válido");

/* 🔹 Inicializar salas base si no existen */
function inicializarSalasBase($con, $cantidad = 8) {
    $check = $con->prepare("SELECT COUNT(*) FROM sala");
    $check->execute();
    $numSalas = $check->fetchColumn();

    for ($i = $numSalas + 1; $i <= $cantidad; $i++) {
        $stmt = $con->prepare("
            INSERT INTO sala (fecha_creacion, id_estado_sala, id_mundo, id_nivel, url_sala)
            VALUES (NOW(), 3, 1, 1, :url)
        ");
        $stmt->execute([':url' => 'auto_' . time() . '_' . $i]);
        usleep(1000);
    }
}

/* 🔹 Función para unir jugador a sala */
function unirJugador($con, $id_usuario, $id_sala, $maxJugadores = 5) {
    $con->beginTransaction();
    try {
        // Verificar que el usuario está activo y conectado
        $stmt = $con->prepare("SELECT id_estado_usu FROM usuario WHERE id_usuario = ? AND id_estado_usu IN (1, 6, 7, 8)");
        $stmt->execute([$id_usuario]);
        if (!$stmt->fetch()) {
            throw new Exception("El usuario no está activo");
        }

        // Buscar partida abierta o cerrada (no en juego)
        $stmt = $con->prepare("
            SELECT p.*, COUNT(dup.id_usuario1) as jugadores_activos 
            FROM partida p 
            LEFT JOIN detalle_usuario_partida dup ON p.id_partida = dup.id_partida 
            LEFT JOIN usuario u ON dup.id_usuario1 = u.id_usuario
            WHERE p.id_sala = ? AND p.id_estado_part IN (3,4)
            AND (u.id_estado_usu = 1 OR u.id_estado_usu IS NULL)
            GROUP BY p.id_partida 
            ORDER BY p.fecha_inicio DESC
            LIMIT 1 FOR UPDATE
        ");
        $stmt->execute([$id_sala]);
        $partida = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($partida) {
            $id_partida = $partida['id_partida'];
            $jugadoresActuales = intval($partida['jugadores_activos']);

            // Revisar si el jugador ya está
            $check = $con->prepare("
                SELECT * FROM detalle_usuario_partida
                WHERE id_partida = ? AND id_usuario1 = ?
            ");
            $check->execute([$id_partida, $id_usuario]);

            if (!$check->fetch()) {
                if ($jugadoresActuales < $maxJugadores) {
                    $sql = $con->prepare("
                        INSERT INTO detalle_usuario_partida 
                        (puntos_total, id_usuario1, id_partida, id_arma)
                        VALUES (0, :id_usuario, :id_partida, 1)
                    ");
                    $sql->execute([':id_usuario' => $id_usuario, ':id_partida' => $id_partida]);
                } else {
                    throw new Exception("La sala ya está llena con jugadores activos");
                }
            }
        } else {
            // Crear nueva partida
            $stmt = $con->prepare("
                INSERT INTO partida (fecha_inicio, id_estado_part, id_sala)
                VALUES (NOW(), 3, ?)
            ");
            $stmt->execute([$id_sala]);
            $id_partida = $con->lastInsertId();

            $sql = $con->prepare("
                INSERT INTO detalle_usuario_partida 
                (puntos_total, id_usuario1, id_partida, id_arma)
                VALUES (0, :id_usuario, :id_partida, 1)
            ");
            $sql->execute([':id_usuario' => $id_usuario, ':id_partida' => $id_partida]);
        }

        // Actualizar cantidad de jugadores
        $cnt = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida = ?");
        $cnt->execute([$id_partida]);
        $nueva = $cnt->fetchColumn();
        $upd = $con->prepare("UPDATE partida SET cantidad_jug = :nueva WHERE id_partida = :id_partida");
        $upd->execute([':nueva' => $nueva, ':id_partida' => $id_partida]);
        
        // Bloquear sala si alcanza 5 jugadores
        if ($nueva >= 5) {
            $updSala = $con->prepare("UPDATE sala SET id_estado_sala = 2 WHERE id_sala = ?");
            $updSala->execute([$id_sala]);
        }

        // Reiniciar vida y estado de todos los jugadores en la partida
        $sqlJugadores = $con->prepare("
            SELECT id_usuario1 FROM detalle_usuario_partida WHERE id_partida=?
        ");
        $sqlJugadores->execute([$id_partida]);
        $jugadores = $sqlJugadores->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($jugadores)) {
            $placeholders = implode(',', array_fill(0, count($jugadores), '?'));
            $updVida = $con->prepare("UPDATE usuario SET vida=200, id_estado_usu=1 WHERE id_usuario IN ($placeholders)");
            $updVida->execute($jugadores);
        }

        $con->commit();
        return $id_partida;
    } catch (Exception $e) {
        if ($con->inTransaction()) $con->rollBack();
        throw $e;
    }
}

inicializarSalasBase($con, 8);

// Asegurar que sólo 8 salas estén disponibles: bloquear el resto
try {
    $lim = 8;
    $stmtKeep = $con->prepare("SELECT id_sala FROM sala ORDER BY id_sala ASC LIMIT ?");
    $stmtKeep->execute([$lim]);
    $keep = $stmtKeep->fetchAll(PDO::FETCH_COLUMN);

    if (count($keep) > 0) {
        // Activar (disponibles) las primeras 8 salas
        $placeholders = implode(',', array_fill(0, count($keep), '?'));
        $sqlUnlock = $con->prepare("UPDATE sala SET id_estado_sala = 3 WHERE id_sala IN ($placeholders)");
        $sqlUnlock->execute($keep);

        // Bloquear todas las demás
        $sqlBlock = $con->prepare("UPDATE sala SET id_estado_sala = 2 WHERE id_sala NOT IN ($placeholders)");
        $sqlBlock->execute($keep);
    }
} catch (Exception $e) {
    // No detener ejecución si algo falla
}

// Definir el tiempo límite de inactividad (2 minutos)
$limite_tiempo = date('Y-m-d H:i:s', strtotime('-2 minutes'));

/* 🔹 Limpiar usuarios inactivos de todas las partidas */
$sqlLimpiarInactivos = $con->prepare("
    DELETE FROM detalle_usuario_partida 
    WHERE id_usuario1 IN (
        SELECT id_usuario 
        FROM usuario 
        WHERE ultima_actividad < ?
    )
");
$sqlLimpiarInactivos->execute([$limite_tiempo]);

/* 🔹 Obtener solo las primeras 3 salas con información de jugadores activos */
$sqlSalas = $con->prepare("
    SELECT 
        s.id_sala, 
        s.id_mundo, 
        m.nomb_mundo, 
        s.id_estado_sala, 
        e.estado,
        COUNT(DISTINCT dup.id_usuario1) as jugadores_activos
    FROM sala s
    INNER JOIN mundo m ON s.id_mundo = m.id_mundo
    INNER JOIN estado e ON s.id_estado_sala = e.id_estado
    LEFT JOIN partida p ON s.id_sala = p.id_sala AND p.id_estado_part IN (3,4,5)
    LEFT JOIN detalle_usuario_partida dup ON p.id_partida = dup.id_partida
    LEFT JOIN usuario u ON dup.id_usuario1 = u.id_usuario AND u.ultima_actividad >= ?
    WHERE s.id_sala IN (SELECT id_sala FROM (SELECT id_sala FROM sala ORDER BY id_sala ASC LIMIT 8) AS t)
    GROUP BY s.id_sala, s.id_mundo, m.nomb_mundo, s.id_estado_sala, e.estado
    ORDER BY s.id_sala ASC");
$sqlSalas->execute([$limite_tiempo]);
$salas = $sqlSalas->fetchAll(PDO::FETCH_ASSOC);

/* 🔹 Unirse a sala */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_sala'])) {
    $id_sala = intval($_POST['id_sala']);
    try {
        $id_partida = unirJugador($con, $usu, $id_sala, 5); // Máximo 5 jugadores
        header("Location: ./combate.php?id_sala=$id_sala&partida=$id_partida");
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error al unirse: " . addslashes($e->getMessage()) . "');</script>";
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
            <p>Jugadores activos: <?= $s['cantidad_jug'] ?>/5</p>
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

