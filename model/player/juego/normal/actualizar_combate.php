<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id_atacante = $_SESSION['id_usuario'] ?? null;
$id_enemigo = $_POST['id_enemigo'] ?? null;
$id_arma = $_POST['id_arma'] ?? null;
$zona = $_POST['zona'] ?? null;
$id_partida = $_POST['id_partida'] ?? null;

// Validar parámetros
if (!$id_atacante || !$id_enemigo || !$id_arma || !$zona || !$id_partida) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Obtener partida y estado
$stmt = $con->prepare("SELECT id_estado_part, inicio_cuenta_regresiva FROM partida WHERE id_partida=?");
$stmt->execute([$id_partida]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$partida) {
    echo json_encode(['error' => 'Partida no encontrada']);
    exit;
}

// Verificar tiempo restante de inicio (60s desde inicio)
$inicio = strtotime($partida['inicio_cuenta_regresiva']);
$ahora = time();
$segundos_restantes = max(0, 60 - ($ahora - $inicio));


// Verificar vida del atacante
$stmt = $con->prepare("SELECT nomb_usu, vida FROM usuario WHERE id_usuario = ?");
$stmt->execute([$id_atacante]);
$atacante = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$atacante || $atacante['vida'] <= 0) {
    echo json_encode(['error' => 'Estás eliminado y no puedes atacar.']);
    exit;
}

// Obtener arma y daño
$stmt = $con->prepare("SELECT nomb_arma, dano_cabeza, dano_torso FROM armas WHERE id_arma = ?");
$stmt->execute([$id_arma]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$arma) {
    echo json_encode(['error' => 'Arma inválida.']);
    exit;
}
$danio = ($zona === 'cabeza') ? $arma['dano_cabeza'] : $arma['dano_torso'];

// Obtener datos del enemigo
$stmt = $con->prepare("SELECT id_usuario, nomb_usu, vida FROM usuario WHERE id_usuario = ?");
$stmt->execute([$id_enemigo]);
$enemigo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$enemigo) {
    echo json_encode(['error' => 'Enemigo no encontrado.']);
    exit;
}

// Calcular nueva vida y actualizar
$nuevaVida = max(0, $enemigo['vida'] - $danio);
$con->prepare("UPDATE usuario SET vida = ? WHERE id_usuario = ?")->execute([$nuevaVida, $id_enemigo]);

// Sumar puntos al atacante
$con->prepare("UPDATE usuario SET puntos = puntos + ? WHERE id_usuario = ?")->execute([$danio, $id_atacante]);
$con->prepare("
    UPDATE detalle_usuario_partida
    SET puntos_total = puntos_total + ?
    WHERE id_partida = ? AND (id_usuario1 = ? OR id_usuario2 = ?)
")->execute([$danio, $id_partida, $id_atacante, $id_atacante]);

// Mensaje del ataque
$mensaje = "<p>{$atacante['nomb_usu']} atacó a {$enemigo['nomb_usu']} con <strong>{$arma['nomb_arma']}</strong> en la {$zona}, causando <strong>{$danio}</strong> de daño. Vida restante: {$nuevaVida}.</p>";

// Si el enemigo murió
if ($nuevaVida <= 0) {
    $mensaje .= "<p>{$atacante['nomb_usu']} eliminó a {$enemigo['nomb_usu']}.</p>";
    $con->prepare("UPDATE usuario SET id_estado_usu = 8 WHERE id_usuario = ?")->execute([$id_enemigo]);

    // Verificar si queda un jugador vivo
    $stmt = $con->prepare("
        SELECT u.id_usuario, u.nomb_usu
        FROM usuario u
        INNER JOIN detalle_usuario_partida d ON u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2
        WHERE d.id_partida = ? AND u.vida > 0
    ");
    $stmt->execute([$id_partida]);
    $vivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($vivos) === 1) {
        $ganador = $vivos[0];

        // Actualizar partida y estados de jugadores
        $con->prepare("UPDATE partida SET id_estado_part = 6, id_ganador = ?, fecha_fin = NOW() WHERE id_partida = ?")
            ->execute([$ganador['id_usuario'], $id_partida]);
        $con->prepare("UPDATE usuario SET id_estado_usu = 6 WHERE id_usuario = ?")->execute([$ganador['id_usuario']]);
        $con->prepare("
            UPDATE usuario 
            SET id_estado_usu = 7
            WHERE id_usuario IN (
                SELECT id_usuario1 FROM detalle_usuario_partida WHERE id_partida = ?
                UNION
                SELECT id_usuario2 FROM detalle_usuario_partida WHERE id_partida = ?
            ) AND id_usuario != ?
        ")->execute([$id_partida, $id_partida, $ganador['id_usuario']]);

        $mensaje .= "<p>La sala ha sido cerrada. Has ganado.</p>";
    }
}

// Devolver respuesta
echo json_encode(["msg" => $mensaje], JSON_UNESCAPED_UNICODE);
exit;
