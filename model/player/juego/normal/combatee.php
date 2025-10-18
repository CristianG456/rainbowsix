<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'] ?? null;
if(!$usu){
    echo json_encode(['error'=>'No hay usuario en sesión']);
    exit;
}

// Recibir datos
$id_partida = $_POST['id_partida'] ?? null;
$id_objetivo = $_POST['id_objetivo'] ?? null;
$id_arma = $_POST['id_arma'] ?? null;
$zona = $_POST['zona'] ?? null;

if(!$id_partida || !$id_objetivo || !$id_arma || !$zona){
    echo json_encode(['error'=>'Faltan parámetros']);
    exit;
}

$id_partida = intval($id_partida);
$id_objetivo = intval($id_objetivo);
$id_arma = intval($id_arma);
$zona = in_array($zona,['cuerpo','cabeza']) ? $zona : 'cuerpo';

// Datos jugador y atacante
$stmt = $con->prepare("SELECT nomb_usu, vida FROM usuario WHERE id_usuario=?");
$stmt->execute([$id_objetivo]);
$objetivo = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$objetivo){ echo json_encode(['error'=>'Jugador objetivo no encontrado']); exit; }

$stmt = $con->prepare("SELECT nomb_usu FROM usuario WHERE id_usuario=?");
$stmt->execute([$usu]);
$atacante = $stmt->fetch(PDO::FETCH_ASSOC);

// Datos arma
$stmt = $con->prepare("SELECT * FROM armas WHERE id_arma=?");
$stmt->execute([$id_arma]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$arma){ echo json_encode(['error'=>'Arma no encontrada']); exit; }

// Calcular daño según zona
$dano = ($zona=='cabeza') ? intval($arma['dano_cabeza']) : intval($arma['dano_torso']);

// Reducir vida objetivo (mínimo 0)
$nueva_vida = max(0,$objetivo['vida']-$dano);
$estado = ($nueva_vida==0) ? 'eliminado' : 'activo';
$upd = $con->prepare("UPDATE usuario SET vida=?, id_estado_usu=? WHERE id_usuario=?");
$upd->execute([$nueva_vida, $estado=='eliminado'?2:1, $id_objetivo]); // suponiendo 1=activo, 2=eliminado

// Sumar puntos al atacante en detalle_usuario_partida
$upd2 = $con->prepare("
    UPDATE detalle_usuario_partida
    SET puntos_total = puntos_total + ?, id_arma = ?
    WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)
");
$upd2->execute([$dano, $id_arma, $id_partida, $usu, $usu]);

// Sumar puntos al usuario atacante
$upd_user = $con->prepare("UPDATE usuario SET puntos = puntos + ? WHERE id_usuario=?");
$upd_user->execute([$dano, $usu]);

// Revisar cuántos jugadores activos quedan en la partida
$stmt_vivos = $con->prepare("
    SELECT id_usuario FROM usuario u
    INNER JOIN detalle_usuario_partida d ON (u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2)
    WHERE d.id_partida=? AND u.id_estado_usu=1
    GROUP BY u.id_usuario
");
$stmt_vivos->execute([$id_partida]);
$vivos = $stmt_vivos->fetchAll(PDO::FETCH_COLUMN);

$partida_finalizada = false;
$ganador_nombre = null;

if(count($vivos) === 1){
    $ganador_id = $vivos[0];

    // Guardar ganador en tabla partida (si tienes columna id_ganador)
    $upd_ganador = $con->prepare("UPDATE partida SET id_ganador=? WHERE id_partida=?");
    $upd_ganador->execute([$ganador_id, $id_partida]);

    // Reiniciar vida de todos los jugadores a 200 y estado a activo
    $upd_vidas = $con->prepare("UPDATE usuario SET vida=200, id_estado_usu=8 WHERE id_usuario IN (
        SELECT id_usuario1 FROM detalle_usuario_partida WHERE id_partida=?
        UNION
        SELECT id_usuario2 FROM detalle_usuario_partida WHERE id_partida=?
    )");
    $upd_vidas->execute([$id_partida,$id_partida]);

    $stmt_ganador = $con->prepare("SELECT nomb_usu FROM usuario WHERE id_usuario=?");
    $stmt_ganador->execute([$ganador_id]);
    $ganador_nombre = $stmt_ganador->fetchColumn();

    $partida_finalizada = true;
}

// Respuesta JSON
$response = [
    'atacante_nombre'=>$atacante['nomb_usu'],
    'objetivo_nombre'=>$objetivo['nomb_usu'],
    'arma_nombre'=>$arma['nomb_arma'],
    'zona'=>$zona,
    'dano'=>$dano,
    'vida_restante'=>$nueva_vida,
    'estado_objetivo'=>$estado,
    'partida_finalizada'=>$partida_finalizada,
    'ganador'=>$ganador_nombre
];

echo json_encode($response);
