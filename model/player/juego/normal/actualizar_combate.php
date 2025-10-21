<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    exit("No hay sesión activa");
}

$id_usuario = $_SESSION['id_usuario'];
$id_enemigo = $_POST['id_enemigo'] ?? null;
$id_arma = $_POST['id_arma'] ?? null;
$zona = $_POST['zona'] ?? null;

if (!$id_enemigo || !$id_arma || !$zona) {
    exit("Faltan parámetros del ataque");
}

// 🔹 Obtener daño del arma según zona
$sqlArma = $con->prepare("SELECT nomb_arma, dano_cabeza, dano_torso FROM armas WHERE id_arma = ?");
$sqlArma->execute([$id_arma]);
$arma = $sqlArma->fetch(PDO::FETCH_ASSOC);
if (!$arma) exit("Arma no encontrada");

$danio = ($zona == "cabeza") ? $arma["dano_cabeza"] : $arma["dano_torso"];

// 🔹 Obtener datos del enemigo
$sqlEnemigo = $con->prepare("SELECT id_usuario, nomb_usu, vida FROM usuario WHERE id_usuario = ?");
$sqlEnemigo->execute([$id_enemigo]);
$enemigo = $sqlEnemigo->fetch(PDO::FETCH_ASSOC);
if (!$enemigo) exit("Enemigo no encontrado");

$nuevaVida = max(0, $enemigo["vida"] - $danio);

// 🔹 Actualizar vida del enemigo
$con->prepare("UPDATE usuario SET vida = ? WHERE id_usuario = ?")->execute([$nuevaVida, $id_enemigo]);

// 🔹 Sumar puntos al atacante
$puntosGanados = $danio;
$con->prepare("UPDATE usuario SET puntos = puntos + ? WHERE id_usuario = ?")->execute([$puntosGanados, $id_usuario]);

// 🔹 Actualizar detalle de la partida
$sqlDetalle = $con->prepare("SELECT id_partida FROM detalle_usuario_partida WHERE id_usuario1 = ? LIMIT 1");
$sqlDetalle->execute([$id_usuario]);
$detalle = $sqlDetalle->fetch(PDO::FETCH_ASSOC);

if ($detalle) {
    $con->prepare("
        UPDATE detalle_usuario_partida 
        SET puntos_total = puntos_total + ? 
        WHERE id_partida = ? AND id_usuario1 = ?
    ")->execute([$puntosGanados, $detalle['id_partida'], $id_usuario]);
}

$id_partida = $detalle['id_partida'];

// 🔹 Mensaje de ataque
$mensaje = "Has atacado a <b>{$enemigo['nomb_usu']}</b> con <b>{$arma['nomb_arma']}</b> en la <b>$zona</b>, causando <b>$danio</b> de daño. Vida restante: <b>{$nuevaVida}</b>.";

// 🔹 Si el enemigo murió
if ($nuevaVida <= 0) {
    $mensaje = "Has eliminado a <b>{$enemigo['nomb_usu']}</b> con <b>{$arma['nomb_arma']}</b>.<br>";

    // 🔹 Verificar jugadores vivos
    $sqlVivos = $con->prepare("
        SELECT u.id_usuario, u.nomb_usu 
        FROM usuario u 
        INNER JOIN detalle_usuario_partida d 
            ON u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2 
        WHERE d.id_partida = ? AND u.vida > 0
    ");
    $sqlVivos->execute([$id_partida]);
    $vivos = $sqlVivos->fetchAll(PDO::FETCH_ASSOC);

    if (count($vivos) == 1) {
        $ganador = $vivos[0];

        // 🔹 Registrar ganador
        $con->prepare("INSERT INTO ganadores (id_usuario, id_partida, fecha_ganado) VALUES (?, ?, NOW())")
            ->execute([$ganador['id_usuario'], $id_partida]);

        // 🔹 Cerrar partida
        $con->prepare("UPDATE partida SET id_estado_part = 4 WHERE id_partida = ?")->execute([$id_partida]);

        // 🔹 Reiniciar vidas
        $con->prepare("UPDATE usuario SET vida = 200 WHERE vida <= 0")->execute();

        $mensaje .= "<b>{$ganador['nomb_usu']}</b> ha ganado la partida.";
    }
}

echo $mensaje;
