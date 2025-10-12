<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

// Simple join handler: increments or creates a partida for the sala and updates sala state
$id_sala = null;
// Accept POST first (form submit), fallback to GET
if(isset($_POST['id']) && is_numeric($_POST['id'])){
    $id_sala = (int)$_POST['id'];
} elseif(isset($_GET['id']) && is_numeric($_GET['id'])){
    $id_sala = (int)$_GET['id'];
}

if(!$id_sala){
    header('Location: normal.php');
    exit;
}
$usu = $_SESSION['id_usuario'] ?? null;
if(!$usu){
    // Not logged in - redirect to login or back
    header('Location: ../../../iniciosesion.php');
    exit;
}

try{
    // Max players
    $max = 5;

    // Verificar que el usuario no esté ya en otra partida activa (o en esta misma)
    $chk = $con->prepare("SELECT p.* FROM detalle_usuario_partida d JOIN partida p ON d.id_partida = p.id_partida WHERE (d.id_usuario1 = :usu OR d.id_usuario2 = :usu) AND p.id_estado_part NOT IN (4,8)");
    $chk->execute([':usu' => $usu]);
    $exist = $chk->fetch(PDO::FETCH_ASSOC);
    if($exist){
        // Si ya está en la misma sala/partida, redirigir al combate de esa partida
        if((int)$exist['id_sala'] === $id_sala){
            header('Location: ../combate.php?partida=' . urlencode($exist['id_partida']) . '&sala=' . urlencode($id_sala));
            exit;
        }
        // Ya participa en otra partida activa: no puede unirse a otra
        header('Location: ../normal/normal.php');
        exit;
    }

    // Obtener la última partida de la sala
    $sql = $con->prepare("SELECT * FROM partida WHERE id_sala = :id_sala ORDER BY fecha_inicio DESC LIMIT 1");
    $sql->execute([':id_sala' => $id_sala]);
    $partida = $sql->fetch(PDO::FETCH_ASSOC);

    if($partida){
        $id_partida = (int)$partida['id_partida'];

        // Use transaction and row locking to avoid race conditions
        $con->beginTransaction();
        // Lock the partida row
        $lock = $con->prepare("SELECT * FROM partida WHERE id_partida = :id_partida FOR UPDATE");
        $lock->execute([':id_partida' => $id_partida]);

        // Contar participantes únicos en detalle_usuario_partida para esta partida (revisamos ambas columnas)
        $cntStmt = $con->prepare(
            "SELECT COUNT(DISTINCT u.usuario_id) FROM (
                 SELECT id_usuario1 AS usuario_id FROM detalle_usuario_partida WHERE id_partida = :pid
                 UNION
                 SELECT id_usuario2 AS usuario_id FROM detalle_usuario_partida WHERE id_partida = :pid
            ) u"
        );
        $cntStmt->execute([':pid' => $id_partida]);
        $cantidad = (int)$cntStmt->fetchColumn();

        if($cantidad >= $max){
            // Sala llena
            $con->rollBack();
            header('Location: ../normal/normal.php');
            exit;
        }

        // Verificar si el usuario ya figura en los participantes de esta partida
        $already = $con->prepare("SELECT * FROM detalle_usuario_partida WHERE id_partida = :pid AND (id_usuario1 = :usu OR id_usuario2 = :usu) LIMIT 1");
        $already->execute([':pid' => $id_partida, ':usu' => $usu]);
        if($already->fetch(PDO::FETCH_ASSOC)){
            $con->commit();
            header('Location: ../combate.php?partida=' . urlencode($id_partida) . '&sala=' . urlencode($id_sala));
            exit;
        }

        // Insertar al usuario en detalle_usuario_partida (guardamos el usuario en ambas columnas para compatibilidad)
        $ins_det = $con->prepare("INSERT INTO detalle_usuario_partida (puntos_total, id_usuario1, id_usuario2, id_partida, id_arma) VALUES (0, :id_usuario, :id_usuario, :id_partida, 1)");
        $ins_det->execute([':id_usuario' => $usu, ':id_partida' => $id_partida]);

        // Recalcular cantidad y actualizar partida
        $cntStmt->execute([':pid' => $id_partida]);
        $nueva = (int)$cntStmt->fetchColumn();
        $upd = $con->prepare("UPDATE partida SET cantidad_jug = :nueva WHERE id_partida = :id_partida");
        $upd->execute([':nueva' => $nueva, ':id_partida' => $id_partida]);

        // Commit transaction
        $con->commit();

    } else {
        // Crear nueva partida con estado ABIERTO (3) pero cantidad inicial 0; luego insertaremos al participante
        $con->beginTransaction();
        $ins = $con->prepare("INSERT INTO partida (fecha_inicio, fecha_fin, cantidad_jug, id_estado_part, id_sala) VALUES (NOW(), NOW(), 0, 3, :id_sala)");
        $ins->execute([':id_sala' => $id_sala]);
        $id_partida = $con->lastInsertId();

        // Insertar al usuario en detalle_usuario_partida (guardamos el usuario en ambas columnas para compatibilidad)
        $ins_det = $con->prepare("INSERT INTO detalle_usuario_partida (puntos_total, id_usuario1, id_usuario2, id_partida, id_arma) VALUES (0, :id_usuario, :id_usuario, :id_partida, 1)");
        $ins_det->execute([':id_usuario' => $usu, ':id_partida' => $id_partida]);

        $nueva = 1;
        $upd = $con->prepare("UPDATE partida SET cantidad_jug = :nueva WHERE id_partida = :id_partida");
        $upd->execute([':nueva' => $nueva, ':id_partida' => $id_partida]);

        $con->commit();
    }

    // Actualizar estado de sala: si lleno poner EN JUEGO (id 5), si no ABIERTO (id 3)
    $nuevo_estado = ($nueva >= $max) ? 5 : 3;
    $upd2 = $con->prepare("UPDATE sala SET id_estado_sala = :estado WHERE id_sala = :id_sala");
    $upd2->execute([':estado' => $nuevo_estado, ':id_sala' => $id_sala]);

    // Si la sala quedó llena, crear automáticamente una nueva sala disponible con el mismo mundo/nivel
    // Además asegurar un pool mínimo de salas abiertas por mundo/nivel (N)
    if($nuevo_estado === 5){
        // Parámetro: número mínimo de salas abiertas por mundo/nivel
        $minPool = 2; // ajusta si quieres más

        // Obtener mundo y nivel de la sala original
        $sinfo = $con->prepare("SELECT id_mundo, id_nivel FROM sala WHERE id_sala = :id_sala");
        $sinfo->execute([':id_sala' => $id_sala]);
        $orig = $sinfo->fetch(PDO::FETCH_ASSOC);
        if($orig){
            // Crear una sala inmediata
            $newUrl = 'auto_' . $id_sala . '_' . time();
            $createSala = $con->prepare("INSERT INTO sala (fecha_creacion, url_sala, id_estado_sala, id_mundo, id_nivel) VALUES (NOW(), :url, 3, :mundo, :nivel)");
            $createSala->execute([':url' => $newUrl, ':mundo' => $orig['id_mundo'], ':nivel' => $orig['id_nivel']]);

            // Contar cuántas salas abiertas hay actualmente para ese mundo/nivel
            $countOpen = $con->prepare("SELECT COUNT(*) FROM sala WHERE id_mundo = :mundo AND id_nivel = :nivel AND id_estado_sala = 3");
            $countOpen->execute([':mundo' => $orig['id_mundo'], ':nivel' => $orig['id_nivel']]);
            $openCount = (int)$countOpen->fetchColumn();

            // Crear salas automáticas adicionales hasta alcanzar minPool
            while($openCount < $minPool){
                $newUrl = 'auto_' . $id_sala . '_' . time() . '_' . rand(100,999);
                $createSala->execute([':url' => $newUrl, ':mundo' => $orig['id_mundo'], ':nivel' => $orig['id_nivel']]);
                $openCount++;
            }
        }
    }

} catch (Exception $e){
    // Log or ignore
}

    // Redirect to combate page after successful join
    header('Location: ../combate.php?partida=' . urlencode($id_partida) . '&sala=' . urlencode($id_sala));
    exit;

?>
