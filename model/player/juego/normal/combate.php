<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    exit("No hay sesión activa");
}

$id_usuario = $_SESSION['id_usuario'];
$id_sala = $_GET['id_sala'] ?? 0;
if (!$id_sala) die("Sala no válida");

// Obtener el nivel del usuario para el bloqueo de armas (si no existe, asumir nivel 1)
$sqlNivelUsu = $con->prepare("SELECT id_nivel FROM usuario WHERE id_usuario=? LIMIT 1");
$sqlNivelUsu->execute([$id_usuario]);
$filaNivel = $sqlNivelUsu->fetch(PDO::FETCH_ASSOC);
$nivel_usuario = ($filaNivel && $filaNivel['id_nivel']) ? intval($filaNivel['id_nivel']) : 1;

// Buscar partida activa en la sala (estado 1 = ABIERTO)
$sqlPartida = $con->prepare("SELECT * FROM partida WHERE id_sala=? AND id_estado_part=1 LIMIT 1");
$sqlPartida->execute([$id_sala]);
$partidaExistente = $sqlPartida->fetch(PDO::FETCH_ASSOC);

if ($partidaExistente) {
    $id_partida = $partidaExistente['id_partida'];
} else {
    // Crear nueva partida (estado 1 = ABIERTO)
    $stmt = $con->prepare("INSERT INTO partida (fecha_inicio, id_estado_part, id_sala) VALUES (NOW(),1,?)");
    $stmt->execute([$id_sala]);
    $id_partida = $con->lastInsertId();
}

// Insertar jugador si no existe y menos de 5 jugadores
$sqlJugadores = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
$sqlJugadores->execute([$id_partida]);
$cantidad = $sqlJugadores->fetchColumn();

$sqlYaEsta = $con->prepare("SELECT * FROM detalle_usuario_partida WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)");
$sqlYaEsta->execute([$id_partida, $id_usuario, $id_usuario]);

if (!$sqlYaEsta->fetch() && $cantidad < 5) {
    $sqlInsert = $con->prepare("INSERT INTO detalle_usuario_partida (puntos_total,id_usuario1,id_partida) VALUES (0,?,?)");
    $sqlInsert->execute([$id_usuario, $id_partida]);
}

// 🔹 Reiniciar vida al entrar en nueva partida (si corresponde)
$sqlJugadoresPartida = $con->prepare("
    SELECT u.id_usuario 
    FROM usuario u
    INNER JOIN detalle_usuario_partida d 
        ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
    WHERE d.id_partida=?
");
$sqlJugadoresPartida->execute([$id_partida]);
$jugadoresEnPartida = $sqlJugadoresPartida->fetchAll(PDO::FETCH_COLUMN);

if (!empty($jugadoresEnPartida)) {
    $placeholders = implode(',', array_fill(0, count($jugadoresEnPartida), '?'));
    $upd = $con->prepare("UPDATE usuario SET vida = 200, id_estado_usu=1 WHERE id_usuario IN ($placeholders)");
    $upd->execute($jugadoresEnPartida);
}

// Limpiar usuarios inactivos de la partida
$sqlLimpiar = $con->prepare("
    DELETE FROM detalle_usuario_partida 
    WHERE id_partida = ? 
    AND id_usuario1 IN (
        SELECT id_usuario 
        FROM usuario 
        WHERE id_estado_usu != 1
    )
");
$sqlLimpiar->execute([$id_partida]);

// Obtener jugadores activos de la partida (para renderizar la página)
$sqlJugadores = $con->prepare("
SELECT u.id_usuario,u.nomb_usu,u.vida,u.puntos,a.url_personaje
FROM usuario u
INNER JOIN avatar a ON u.id_avatar=a.id_avatar
INNER JOIN detalle_usuario_partida d ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
WHERE d.id_partida=? AND u.id_estado_usu = 1
");
$sqlJugadores->execute([$id_partida]);
$jugadores = $sqlJugadores->fetchAll(PDO::FETCH_ASSOC);

// Si no hay jugadores activos, cerrar la partida
if (empty($jugadores)) {
    $sqlCerrarPartida = $con->prepare("UPDATE partida SET id_estado_part = 4 WHERE id_partida = ?");
    $sqlCerrarPartida->execute([$id_partida]);
    
    // Redirigir al lobby
    header("Location: ingreso_sala.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Combate</title>
    <link rel="stylesheet" href="../../../../controller/css/combate.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">

</head>

<body>
    <h2>Partida #<?= htmlspecialchars($id_partida) ?></h2>

    <!-- Timer global de 5 minutos (se mostrará cuando la partida esté EN_JUEGO) -->

    <!-- Timer de cuenta regresiva antes del inicio (60s) -->
<div id="timerContainer" style="text-align:center; margin-bottom: 10px;">
  <h2 id="timer" style="font-size:24px; color:#ffcc00; font-weight:bold; display:block;">Esperando jugadores...</h2>
  <h3 id="timerGlobal" style="font-size:20px; color:#00ffea; font-weight:bold;"></h3>
</div>

    <div class="jugadores" id="jugadores">
        <?php foreach ($jugadores as $j):
            $esMiJugador = $j['id_usuario'] == $id_usuario;
            $vidaPorcentaje = max(0, min(100, ($j['vida'] / 200) * 100));
        ?>
            <div class="jugador <?= $j['vida'] <= 0 ? 'eliminado' : '' ?> <?= $esMiJugador ? 'mi-jugador' : '' ?>"
                 data-id="<?= $j['id_usuario'] ?>" data-vida="<?= $vidaPorcentaje ?>">
                <div class="vida-barra"><div class="vida-fill" style="width:<?= $vidaPorcentaje ?>%"></div></div>
                <img src="/rainbowsix/controller/img/<?= basename($j['url_personaje']) ?>" class="avatar">
                <h3><?= htmlspecialchars($j['nomb_usu']) ?> <?= $esMiJugador ? '<span>(Tú)</span>' : '' ?></h3>
                <p>Puntos: <?= $j['puntos'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="acciones">
        <form id="ataqueForm">
            <input type="hidden" name="id_partida" value="<?= htmlspecialchars($id_partida) ?>">
            <label>Enemigo:</label>
            <select name="id_enemigo" required>
                <option value="">--Selecciona--</option>
                <?php foreach ($jugadores as $j):
                    if ($j['id_usuario'] != $id_usuario && $j['vida'] > 0): ?>
                        <option value="<?= $j['id_usuario'] ?>"><?= htmlspecialchars($j['nomb_usu']) ?></option>
                <?php endif; endforeach; ?>
            </select>

            <label>Arma:</label>
            <select name="id_arma" required>
                <?php
                // Obtener nivel requerido de cada arma (columna id_nivel_arma en la tabla `armas`)
                $armas = $con->prepare("SELECT id_arma, nomb_arma, id_nivel_arma FROM armas");
                $armas->execute();
                foreach ($armas->fetchAll() as $arma) {
                    $reqNivel = isset($arma['id_nivel_arma']) ? intval($arma['id_nivel_arma']) : 1;
                    $disabled = ($reqNivel > $nivel_usuario) ? 'disabled' : '';
                    $label = htmlspecialchars($arma['nomb_arma']);
                    if ($reqNivel > 1) {
                        $label .= " (Req. nivel: $reqNivel)";
                    }
                    echo "<option value='" . $arma['id_arma'] . "' data-nivel='" . $reqNivel . "' $disabled>" . $label . "</option>";
                }
                ?>
            </select>

            <label>Zona:</label>
            <select name="zona" required>
                <option value="cabeza">Cabeza</option>
                <option value="torso">Torso</option>
            </select>

            <button type="submit">Atacar</button>
        </form>

        <button id="salirBtn">Salir de la partida</button>
    </div>

    <div id="log"></div>

<script>
const id_usuario = <?= $id_usuario ?>;
const id_sala = <?= $id_sala ?>; 
let id_partida = <?= $id_partida ?>;
const userNivel = <?= $nivel_usuario ?>; // nivel del usuario actual (para validación cliente)
let partidaIniciada = false;
let cuentaRegresivaInterval = null;
let timerPartidaInterval = null;
const rutaImgBase = "/rainbowsix/controller/img";

// Mostrar mensaje de timer
function mostrarMensajeTimer(texto) {
    const timerDiv = $("#timer");
    if (timerDiv.length === 0) {
        $("#timerContainer").prepend('<div id="timer" style="font-size:20px;font-weight:bold;color:#fff;margin-bottom:10px;text-align:center;"></div>');
    }
    $("#timer").text(texto).show();
}

// Bloquear botones y selects
function bloquearAcciones(bloquear) {
    $("#ataqueForm button, #ataqueForm select").prop("disabled", bloquear);
}

// Verificar estado de la partida
function verificarInicioPartida() {
    $.getJSON("actualizar_estado_partida.php", { id_sala }, function(data) {
        if (data.error) return console.error(data.error);

        id_partida = data.id_partida; // actualizar por si se creó nueva
        const estado = data.estado_partida;
        const tiempo_restante = Math.min(data.tiempo_restante || 30, 30);

        if (estado === 3) { // Abierto
            // SOLO mostrar en el timer, no en el log
            $("#timer").text("⏳ Esperando jugadores...").show();
            bloquearAcciones(true);
        } else if (estado === 5 && !partidaIniciada) { // En juego
            partidaIniciada = true;
            bloquearAcciones(true); // bloqueamos hasta que pase la cuenta regresiva
            iniciarCuentaRegresiva(tiempo_restante);
        } else if (estado === 4) { // Cerrado
            clearInterval(timerPartidaInterval);
            clearInterval(cuentaRegresivaInterval);
            $("#timer").text("🏁 Partida finalizada").show();
            $("#timerGlobal").text("🏁 Partida finalizada");
            bloquearAcciones(true);
        }
    });
}

// Cuenta regresiva de 60s
function iniciarCuentaRegresiva(segundos) {
    clearInterval(cuentaRegresivaInterval);
    let restante = segundos;

    bloquearAcciones(true); // bloqueamos al iniciar

    cuentaRegresivaInterval = setInterval(() => {
        if (restante > 0) {
            $("#timer").text(`⏳ Comienza en: ${restante}s`).show();
            restante--;
        } else {
            clearInterval(cuentaRegresivaInterval);
            $("#timer").text("🔥 ¡La partida ha comenzado!").show();
            bloquearAcciones(false); // desbloqueamos los botones
            iniciarTimerPartida(300); // 5 minutos de partida
        }
    }, 1000);
}

// Función para actualizar la actividad del usuario
function actualizarActividadUsuario() {
    fetch('/rainbowsix/controller/actualizar_actividad.php', {
        method: 'POST'
    }).catch(error => console.error('Error al actualizar actividad:', error));
}

// Actualizar actividad cada 30 segundos
setInterval(actualizarActividadUsuario, 30000);

// Timer global de la partida (5 minutos)
function iniciarTimerPartida(segundos) {
    clearInterval(timerPartidaInterval);
    let restante = segundos;

    timerPartidaInterval = setInterval(() => {
        if (restante <= 0) {
            clearInterval(timerPartidaInterval);
            mostrarMensajeTimer("⏰ Fin del combate");
            $("#timerGlobal").text("🏁 Partida finalizada");
            bloquearAcciones(true);
            return;
        }
        const min = Math.floor(restante / 60);
        const seg = restante % 60;
        $("#timerGlobal").text(`⏰ Tiempo restante: ${min}:${seg.toString().padStart(2,'0')}`);
        restante--;
    }, 1000);
}

// Actualizar jugadores y select de enemigos
function actualizarJugadores() {
    if (!id_partida) return;
    const selectEnemigo = $("#ataqueForm select[name='id_enemigo']");
    const seleccionActual = selectEnemigo.val();

    $.getJSON("actualizar_jugadores.php", { id_partida }, function(data) {
        data.forEach(j => {
            const idStr = j.id_usuario.toString();
            const vidaPorcentaje = Math.max(0, Math.min(100, (j.vida / 200) * 100));
            const esMiJugador = j.id_usuario == id_usuario;

            const jugadorDiv = $("#jugadores .jugador[data-id='" + idStr + "']");
            if (jugadorDiv.length) {
                jugadorDiv.find(".vida-fill").css("width", vidaPorcentaje + "%");
                jugadorDiv.find("p").text("Puntos: " + j.puntos);
                if (j.vida <= 0) jugadorDiv.addClass("eliminado");
            } else {
                const imgSrc = rutaImgBase + "/" + j.url_personaje;
                $("#jugadores").append(`
                    <div class="jugador" data-id="${idStr}">
                        <div class="vida-barra"><div class="vida-fill" style="width:${vidaPorcentaje}%"></div></div>
                        <img src="${imgSrc}" class="avatar">
                        <h3>${j.nomb_usu}${esMiJugador ? ' (Tú)' : ''}</h3>
                        <p>Puntos: ${j.puntos}</p>
                    </div>
                `);
                if (!esMiJugador && selectEnemigo.find(`option[value="${idStr}"]`).length === 0) {
                    selectEnemigo.append(`<option value="${idStr}">${j.nomb_usu}</option>`);
                }
            }
        });

        // Eliminar jugadores que ya no estén
        $("#jugadores .jugador").each(function() {
            const id = $(this).data("id").toString();
            if (!data.some(j => j.id_usuario.toString() === id)) {
                $(this).remove();
                selectEnemigo.find(`option[value='${id}']`).remove();
            }
        });

        if (selectEnemigo.find(`option[value='${seleccionActual}']`).length > 0) {
            selectEnemigo.val(seleccionActual);
        }
    });
}

// Enviar ataque (REVISADO: detecta JSON y HTML-error)
// Enviar ataque y mostrar resultado en el log
$("#ataqueForm").submit(function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find("button[type='submit']");
    $btn.prop("disabled", true);

    // Validación adicional en cliente: asegurar que el arma seleccionada no supere el nivel del usuario
    const armaSel = $form.find("select[name='id_arma'] option:selected");
    const armaReqNivel = parseInt(armaSel.data('nivel') || 1, 10);
    if (armaReqNivel > userNivel) {
        alert(`No puedes usar esa arma. Requiere nivel ${armaReqNivel}. Tu nivel: ${userNivel}`);
        $btn.prop("disabled", false);
        return;
    }

    $.ajax({
        url: "actualizar_combate.php",
        type: "POST",
        data: $form.serialize(),
        dataType: "json", // esperamos un JSON con { msg: "..." } o { error: "..." }
        success: function(response) {
            console.log("Respuesta del servidor:", response); //  para depurar

            if (response.error) {
                alert(response.error);
            } else if (response.msg) {
                // Mostramos el mensaje en el log
                $("#log").append(response.msg);
                // desplazamos el scroll al final
                $("#log").scrollTop($("#log")[0].scrollHeight);
            } else {
                console.warn("Respuesta inesperada:", response);
            }

            actualizarJugadores();
            $btn.prop("disabled", false);
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX:", error);
            console.log("Respuesta del servidor:", xhr.responseText);
            alert("Error al conectar con el servidor.");
            $btn.prop("disabled", false);
        }
    });
});


// Salir de la partida
$("#salirBtn").click(async function() {
    if (confirm("¿Salir de la partida?")) {
        const form = new FormData();
        form.append("id_partida", id_partida);
        const resp = await fetch("salir_partida.php", { method: "POST", body: form });
        const data = await resp.json();
        if (data.ok) {
            alert("Has salido de la partida.");
            window.location.href = "ingreso_sala.php";
        } else {
            alert("Error al salir: " + (data.error || "Desconocido"));
        }
    }
});

// Inicialización
actualizarJugadores();
setInterval(actualizarJugadores, 2000);
verificarInicioPartida();
setInterval(verificarInicioPartida, 2000);
</script>

</body>
</html>
