<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    exit("No hay sesión activa");
}

$id_usuario = (int)$_SESSION['id_usuario'];
$id_sala = (int)($_GET['id_sala'] ?? 0);
if (!$id_sala) die("Sala no válida");

// Obtener el nivel del usuario
$sqlNivelUsu = $con->prepare("SELECT id_nivel FROM usuario WHERE id_usuario=? LIMIT 1");
$sqlNivelUsu->execute([$id_usuario]);
$filaNivel = $sqlNivelUsu->fetch(PDO::FETCH_ASSOC);
$nivel_usuario = ($filaNivel && $filaNivel['id_nivel']) ? intval($filaNivel['id_nivel']) : 1;

// Buscar partida activa (estado 1 = ABIERTO)
$sqlPartida = $con->prepare("SELECT * FROM partida WHERE id_sala=? AND id_estado_part=1 LIMIT 1");
$sqlPartida->execute([$id_sala]);
$partidaExistente = $sqlPartida->fetch(PDO::FETCH_ASSOC);

if ($partidaExistente) {
    $id_partida = $partidaExistente['id_partida'];
} else {
    $stmt = $con->prepare("INSERT INTO partida (fecha_inicio, id_estado_part, id_sala) VALUES (NOW(),1,?)");
    $stmt->execute([$id_sala]);
    $id_partida = $con->lastInsertId();
}

// Insertar jugador si no está y hay menos de 5
$sqlJugadores = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
$sqlJugadores->execute([$id_partida]);
$cantidad = $sqlJugadores->fetchColumn();

$sqlYaEsta = $con->prepare("SELECT * FROM detalle_usuario_partida WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)");
$sqlYaEsta->execute([$id_partida, $id_usuario, $id_usuario]);

if (!$sqlYaEsta->fetch() && $cantidad < 5) {
    $sqlInsert = $con->prepare("INSERT INTO detalle_usuario_partida (puntos_total,id_usuario1,id_partida) VALUES (0,?,?)");
    $sqlInsert->execute([$id_usuario, $id_partida]);
}

// 🔹 Reiniciar vida al entrar en nueva partida
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

// Limpiar usuarios inactivos
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

// Obtener jugadores activos
$sqlJugadores = $con->prepare("
SELECT u.id_usuario,u.nomb_usu,u.vida,u.puntos,a.url_personaje
FROM usuario u
LEFT JOIN avatar a ON u.id_avatar=a.id_avatar
INNER JOIN detalle_usuario_partida d ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
WHERE d.id_partida=? AND u.id_estado_usu = 1
");
$sqlJugadores->execute([$id_partida]);
$jugadores = $sqlJugadores->fetchAll(PDO::FETCH_ASSOC);

// Si no hay jugadores activos, cerrar la partida
if (empty($jugadores)) {
    $sqlCerrarPartida = $con->prepare("UPDATE partida SET id_estado_part = 4 WHERE id_partida = ?");
    $sqlCerrarPartida->execute([$id_partida]);
    header("Location: ingreso_sala.php");
    exit;
}

// Obtener armas
$sqlArm = $con->prepare("SELECT id_arma, nomb_arma, dano_cabeza, dano_torso, id_nivel_arma FROM armas");
$sqlArm->execute();
$armasTodas = $sqlArm->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Combate</title>
    <link rel="stylesheet" href="../../../../controller/css/combate.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">
    <style>
      #log { max-height: 200px; overflow:auto; color: #fff; margin-top:12px; }
    </style>
</head>
<body>
    <h2>Partida #<?= htmlspecialchars($id_partida) ?></h2>

<div id="timerContainer" style="text-align:center; margin-bottom: 10px;">
  <h2 id="timer" style="font-size:24px; color:#ffcc00; font-weight:bold; display:block;">Esperando jugadores...</h2>
  <h3 id="timerGlobal" style="font-size:20px; color:#00ffea; font-weight:bold;"></h3>
</div>

    <div class="jugadores" id="jugadores">
        <?php foreach ($jugadores as $j):
            $esMiJugador = $j['id_usuario'] == $id_usuario;
            $vidaPorcentaje = max(0, min(100, (intval($j['vida']) / 200) * 100));
            $imgPath = htmlspecialchars(basename($j['url_personaje']));
        ?>
            <div class="jugador <?= intval($j['vida']) <= 0 ? 'eliminado' : '' ?> <?= $esMiJugador ? 'mi-jugador' : '' ?>"
                 data-id="<?= htmlspecialchars($j['id_usuario']) ?>" data-vida="<?= $vidaPorcentaje ?>">
                <div class="vida-barra"><div class="vida-fill" style="width:<?= $vidaPorcentaje ?>%"></div></div>
                <img src="/rainbowsix/controller/img/<?= $imgPath ?>" class="avatar" onerror="this.src='/rainbowsix/controller/img/enemigo_default.png'">
                <h3><?= htmlspecialchars($j['nomb_usu']) ?> <?= $esMiJugador ? '<span>(Tú)</span>' : '' ?></h3>
                <p class="puntos">Puntos: <?= htmlspecialchars($j['puntos']) ?></p>
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
                    if ($j['id_usuario'] != $id_usuario && intval($j['vida']) > 0): ?>
                        <option value="<?= htmlspecialchars($j['id_usuario']) ?>"><?= htmlspecialchars($j['nomb_usu']) ?></option>
                <?php endif; endforeach; ?>
            </select>

            <label>Arma:</label>
            <select name="id_arma" required>
                <?php
                foreach ($armasTodas as $arma) {
                    $reqNivel = isset($arma['id_nivel_arma']) ? intval($arma['id_nivel_arma']) : 1;
                    $disabled = ($reqNivel > $nivel_usuario) ? 'disabled' : '';
                    $label = htmlspecialchars($arma['nomb_arma']);
                    if ($reqNivel > 1) $label .= " (Req. nivel: $reqNivel)";
                    $dCabeza = intval($arma['dano_cabeza']);
                    $dTorso = intval($arma['dano_torso']);
                    echo "<option value='{$arma['id_arma']}' data-nivel='{$reqNivel}' data-dano-cabeza='{$dCabeza}' data-dano-torso='{$dTorso}' $disabled>" . $label . "</option>";
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
const id_usuario = <?= json_encode($id_usuario) ?>;
const id_sala = <?= json_encode($id_sala) ?>;
let id_partida = <?= json_encode($id_partida) ?>;
const userNivel = <?= json_encode($nivel_usuario) ?>;
let partidaIniciada = false;
let cuentaRegresivaInterval = null;
let timerPartidaInterval = null;
const rutaImgBase = "/rainbowsix/controller/img";

// Mostrar mensaje de timer
function mostrarMensajeTimer(texto) {
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
        id_partida = data.id_partida;
        const estado = data.estado_partida;
        const tiempo_restante = Math.min(data.tiempo_restante || 30, 30);
        if (estado === 3) {
            $("#timer").text("⏳ Esperando jugadores...").show();
            bloquearAcciones(true);
        } else if (estado === 5 && !partidaIniciada) {
            partidaIniciada = true;
            bloquearAcciones(true);
            iniciarCuentaRegresiva(tiempo_restante);
        } else if (estado === 4) {
            clearInterval(timerPartidaInterval);
            clearInterval(cuentaRegresivaInterval);
            $("#timer").text("🏁 Partida finalizada").show();
            $("#timerGlobal").text("🏁 Partida finalizada");
            bloquearAcciones(true);
        }
    });
}

// Cuenta regresiva
function iniciarCuentaRegresiva(segundos) {
    clearInterval(cuentaRegresivaInterval);
    let restante = segundos;
    bloquearAcciones(true);
    cuentaRegresivaInterval = setInterval(() => {
        if (restante > 0) {
            $("#timer").text(`⏳ Comienza en: ${restante}s`).show();
            restante--;
        } else {
            clearInterval(cuentaRegresivaInterval);
            $("#timer").text("🔥 ¡La partida ha comenzado!").show();
            bloquearAcciones(false);
            iniciarTimerPartida(300);
        }
    }, 1000);
}

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

// Actualizar jugadores
function actualizarJugadores() {
    if (!id_partida) return;
    const selectEnemigo = $("#ataqueForm select[name='id_enemigo']");
    const seleccionActual = selectEnemigo.val();

    $.getJSON("actualizar_jugadores.php", { id_partida }, function(data) {
        const existingIds = data.map(j=>String(j.id_usuario));

        data.forEach(j => {
            const idStr = String(j.id_usuario);
            const vidaPorcentaje = Math.max(0, Math.min(100, (j.vida / 200) * 100));
            const jugadorDiv = $("#jugadores .jugador[data-id='" + idStr + "']");
            if (jugadorDiv.length) {
                jugadorDiv.find(".vida-fill").css("width", vidaPorcentaje + "%");
                jugadorDiv.find(".puntos").text("Puntos: " + j.puntos);
                if (j.vida <= 0) jugadorDiv.addClass("eliminado");
            } else {
                const imgSrc = rutaImgBase + "/" + (j.url_personaje ? j.url_personaje.split('/').pop() : 'enemigo_default.png');
                $("#jugadores").append(`
                    <div class="jugador" data-id="${idStr}">
                        <div class="vida-barra"><div class="vida-fill" style="width:${vidaPorcentaje}%"></div></div>
                        <img src="${imgSrc}" class="avatar" onerror="this.src='${rutaImgBase}/enemigo_default.png'">
                        <h3>${j.nomb_usu}</h3>
                        <p class="puntos">Puntos: ${j.puntos}</p>
                    </div>
                `);
            }
            if (j.id_usuario != id_usuario && selectEnemigo.find(`option[value='${j.id_usuario}']`).length === 0) {
                selectEnemigo.append(`<option value="${j.id_usuario}">${j.nomb_usu}</option>`);
            }
        });

        // Eliminar jugadores que ya no estén
        $("#jugadores .jugador").each(function() {
            const id = String($(this).data("id"));
            if (!existingIds.includes(id)) {
                $(this).remove();
                selectEnemigo.find(`option[value='${id}']`).remove();
            }
        });

        if (selectEnemigo.find(`option[value='${seleccionActual}']`).length > 0) {
            selectEnemigo.val(seleccionActual);
        }
    }).fail(function(xhr) {
        console.error("Error al obtener jugadores:", xhr.responseText);
    });
}

// Enviar ataque
$("#ataqueForm").submit(function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $form.find("button[type='submit']");
    $btn.prop("disabled", true);

    const armaSel = $form.find("select[name='id_arma'] option:selected");
    const armaReqNivel = parseInt(armaSel.data('nivel') || 1, 10);
    const userNivelInt = parseInt(userNivel, 10);

    if (armaReqNivel > userNivelInt) {
        alert(`No puedes usar esa arma. Requiere nivel ${armaReqNivel}. Tu nivel: ${userNivelInt}`);
        $btn.prop("disabled", false);
        return;
    }

    $.ajax({
        url: "actualizar_combate.php",
        type: "POST",
        data: $form.serialize(),
        dataType: "json",
        success: function(response) {
            if (response.error) {
                alert(response.error);
            } else {
                if (response.msg) {
                    $("#log").append(response.msg);
                } else {
                    const at = response.atacante_nombre || '';
                    const obj = response.objetivo_nombre || '';
                    const dano = response.dano || '';
                    const vida = response.vida_restante || '';
                    $("#log").append(`<div style="color:#fff">${at} golpeó a ${obj} con ${response.arma_nombre || ''} (${response.zona || ''}) → Daño: ${dano}. Vida restante: ${vida}</div>`);
                }
            }
            actualizarJugadores();
            $btn.prop("disabled", false);
            $("#log").scrollTop($("#log")[0].scrollHeight);
        },
        error: function(xhr) {
            console.error("Error AJAX:", xhr.responseText);
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
