<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id_rol WHERE usuario.id_usuario = ?");
$sql->execute([$usu]);
$fila = $sql->fetch();

/**
 * 🔹 Crea salas base si no existen
 */
function inicializarSalasBase($con, $cantidad = 5)
{
    $check = $con->prepare("SELECT COUNT(*) FROM sala");
    $check->execute();
    $numSalas = $check->fetchColumn();

    if ($numSalas < $cantidad) {
        for ($i = $numSalas + 1; $i <= $cantidad; $i++) {
            $stmt = $con->prepare("
                INSERT INTO sala (fecha_creacion, id_estado_sala, id_mundo, id_nivel)
                VALUES (NOW(), 3, 1, 1)
            ");
            $stmt->execute();
        }
    }
}

/**
 * 🔹 Unir jugador a una partida o crear una nueva
 */
function unirJugador($con, $id_usuario, $id_sala, $maxJugadores = 5)
{
    $stmt = $con->prepare("SELECT * FROM partida WHERE id_sala = ? ORDER BY id_partida DESC LIMIT 1");
    $stmt->execute([$id_sala]);
    $partida = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partida) {
        // Crear nueva partida
        $stmt = $con->prepare("
            INSERT INTO partida (fecha_inicio, cantidad_jug, id_estado_part, id_sala)
            VALUES (NOW(), ?, 5, ?)
        ");
        $stmt->execute([$maxJugadores, $id_sala]);
        $id_partida = $con->lastInsertId();

        // Primer jugador
        $sql = $con->prepare("
            INSERT INTO detalle_usuario_partida (id_partida, id_usuario1, id_arma)
            VALUES (?, ?, 1)
        ");
        $sql->execute([$id_partida, $id_usuario]);
        return $id_partida;
    } else {
        // Contar jugadores actuales
        $sql = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida = ?");
        $sql->execute([$partida['id_partida']]);
        $jugadoresActuales = $sql->fetchColumn() * 2;

        if ($jugadoresActuales < $maxJugadores) {
            // Buscar espacio libre
            $sql = $con->prepare("
                SELECT id_usuario_partida, id_usuario1, id_usuario2 
                FROM detalle_usuario_partida 
                WHERE id_partida = ? AND id_usuario2 IS NULL 
                LIMIT 1
            ");
            $sql->execute([$partida['id_partida']]);
            $detalleLibre = $sql->fetch(PDO::FETCH_ASSOC);

            if ($detalleLibre) {
                // Completar como jugador2
                $update = $con->prepare("UPDATE detalle_usuario_partida SET id_usuario2 = ? WHERE id_usuario_partida = ?");
                $update->execute([$id_usuario, $detalleLibre['id_usuario_partida']]);
                return $partida['id_partida'];
            } else {
                // Nuevo duelo dentro de la misma partida
                $sql = $con->prepare("
                    INSERT INTO detalle_usuario_partida (id_partida, id_usuario1, id_arma)
                    VALUES (?, ?, 1)
                ");
                $sql->execute([$partida['id_partida'], $id_usuario]);
                return $partida['id_partida'];
            }
        } else {
            // Sala llena → crear nueva
            $stmtNew = $con->prepare("
                INSERT INTO sala (fecha_creacion, id_estado_sala, id_mundo, id_nivel)
                VALUES (NOW(), 3, 1, 1)
            ");
            $stmtNew->execute();
            $nuevaSalaId = $con->lastInsertId();

            $stmtP = $con->prepare("
                INSERT INTO partida (fecha_inicio, cantidad_jug, id_estado_part, id_sala)
                VALUES (NOW(), ?, 5, ?)
            ");
            $stmtP->execute([$maxJugadores, $nuevaSalaId]);
            $id_partida = $con->lastInsertId();

            // Jugador entra en nueva partida
            $sql = $con->prepare("
                INSERT INTO detalle_usuario_partida (id_partida, id_usuario1, id_arma)
                VALUES (?, ?, 1)
            ");
            $sql->execute([$id_partida, $id_usuario]);
            return $id_partida;
        }
    }
}

// 🔹 Inicializar salas base
inicializarSalasBase($con, 5);

// 🔹 Consultar máximo 5 salas visibles
$sqlSalas = $con->prepare("
    SELECT s.id_sala, s.id_mundo, m.nomb_mundo, s.id_estado_sala, e.estado
    FROM sala s
    INNER JOIN mundo m ON s.id_mundo = m.id_mundo
    INNER JOIN estado e ON s.id_estado_sala = e.id_estado
    ORDER BY s.id_sala ASC
    LIMIT 5
");
$sqlSalas->execute();
$salas = $sqlSalas->fetchAll(PDO::FETCH_ASSOC);

$maxJug = 5;

/* 🔹 Unirse a una sala → Redirigir a combate.php */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_sala'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $id_sala = $_POST['id_sala'];

    try {
        $id_partida = unirJugador($con, $id_usuario, $id_sala, $maxJug);

        // ✅ Redirección limpia
        header("Location: ../combate.php?partida=$id_partida&sala=$id_sala");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error al unirse: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modo Clasificatorio</title>
    <link rel="stylesheet" href="../../../../controller/css/clasificatoria.css">
</head>
<body>
    <a href="../juego.php" class="btn-volver">Volver</a>

    <main class="contenedor-salas">
        <h1>Salas - Modo (Clasificatoria)</h1>

        <?php if (empty($salas)): ?>
            <p class="sin-salas">No hay salas disponibles.</p>
        <?php else: ?>
            <section class="lista-salas">
                <?php foreach ($salas as $s): ?>
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
