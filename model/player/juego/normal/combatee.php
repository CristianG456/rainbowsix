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

// Limpiar usuarios inactivos de la partida (opcional)
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
LEFT JOIN avatar a ON u.id_avatar=a.id_avatar
INNER JOIN detalle_usuario_partida d ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
WHERE d.id_partida=? AND u.id_estado_usu = 1
");
$sqlJugadores->execute([$id_partida]);
$jugadores = $sqlJugadores->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Si solo hay un jugador real en la partida, preparamos un enemigo local temporal
$has_local_enemy = false;
$localEnemy = null;
if (count($jugadores) === 1) {
    // Intentar obtener un avatar aleatorio de la base (de un usuario activo), para que el enemigo local tenga imagen real
    $sqlAvatar = $con->prepare("
        SELECT a.url_personaje 
        FROM avatar a
        JOIN usuario u ON u.id_avatar = a.id_avatar
        WHERE u.id_estado_usu = 1
        ORDER BY RAND()
        LIMIT 1
    ");
    $sqlAvatar->execute();
    $avatarRow = $sqlAvatar->fetch(PDO::FETCH_ASSOC);

    $img = ($avatarRow && !empty($avatarRow['url_personaje'])) ? $avatarRow['url_personaje'] : 'enemigo_default.png';
    // Asegúrate de tener /controller/img/enemigo_default.png como fallback

    $localEnemy = [
        'id_usuario' => 9999, // id especial local
        'nomb_usu' => 'Enemigo Local',
        'vida' => 200,
        'puntos' => 0,
        'url_personaje' => $img
    ];
    $jugadores[] = $localEnemy;
    $has_local_enemy = true;
}

// Si no hay jugadores activos, cerrar la partida
if (empty($jugadores)) {
    $sqlCerrarPartida = $con->prepare("UPDATE partida SET id_estado_part = 4 WHERE id_partida = ?");
    $sqlCerrarPartida->execute([$id_partida]);
    header("Location: ingreso_sala.php");
    exit;
}

// Obtener todas las armas con daños y nivel requerido (para incluir en el select con data-*)
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
      /* pequeño ajuste para el log */
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
                    // incluimos datos de daño en data-*
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

// Datos del enemigo local (si existe)
const hasLocalEnemy = <?= $has_local_enemy ? 'true' : 'false' ?>;
const localEnemyData = <?= $has_local_enemy ? json_encode($localEnemy) : 'null' ?>;

// Mostrar mensaje de timer
function mostrarMensajeTimer(texto) {
    $("#timer").text(texto).show();
}

// Bloquear botones y selects
function bloquearAcciones(bloquear) {
    $("#ataqueForm button, #ataqueForm select").prop("disabled", bloquear);
}

// Verificar estado de la partida (sigue llamando a tu endpoint original)
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

// Actualizar jugadores: mantiene el enemigo local si está presente
function actualizarJugadores() {
    if (!id_partida) return;
    const selectEnemigo = $("#ataqueForm select[name='id_enemigo']");
    const seleccionActual = selectEnemigo.val();

    $.getJSON("actualizar_jugadores.php", { id_partida }, function(data) {
        // data: array de jugadores reales desde el servidor
        // Reconstruimos la lista visual pero preservando el enemigo local (si aplica)
        const existingIds = data.map(j=>String(j.id_usuario));

        // Actualizar DOM con datos reales
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
            // asegurar que esté en el select de enemigos
            if (j.id_usuario != id_usuario && selectEnemigo.find(`option[value='${j.id_usuario}']`).length === 0) {
                selectEnemigo.append(`<option value="${j.id_usuario}">${j.nomb_usu}</option>`);
            }
        });

        // Si existe enemigo local, volverlo a añadir (porque data no lo trae)
        if (hasLocalEnemy && localEnemyData) {
            const idStr = String(localEnemyData.id_usuario);
            if ($("#jugadores .jugador[data-id='" + idStr + "']").length === 0) {
                const imgSrc = rutaImgBase + "/" + localEnemyData.url_personaje.split('/').pop();
                $("#jugadores").append(`
                    <div class="jugador" data-id="${idStr}">
                        <div class="vida-barra"><div class="vida-fill" style="width:100%"></div></div>
                        <img src="${imgSrc}" class="avatar" onerror="this.src='${rutaImgBase}/enemigo_default.png'">
                        <h3>${localEnemyData.nomb_usu}</h3>
                        <p class="puntos">Puntos: ${localEnemyData.puntos}</p>
                    </div>
                `);
            }
            if (selectEnemigo.find(`option[value='${idStr}']`).length === 0) {
                selectEnemigo.append(`<option value="${idStr}">${localEnemyData.nomb_usu}</option>`);
            }
        }

        // Eliminar del DOM jugadores que ya no estén en data (sin tocar al enemigo local)
        $("#jugadores .jugador").each(function() {
            const id = String($(this).data("id"));
            if (id === (hasLocalEnemy ? String(localEnemyData.id_usuario) : null)) return; // no remover local
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

    const id_enemigo = $form.find("select[name='id_enemigo']").val();
    const id_arma = $form.find("select[name='id_arma']").val();
    const armaSel = $form.find("select[name='id_arma'] option:selected");
    const armaReqNivel = parseInt(armaSel.data('nivel') || 1, 10);
    const danoCabeza = parseInt(armaSel.data('dano-cabeza') || 0, 10);
    const danoTorso = parseInt(armaSel.data('dano-torso') || 0, 10);
    const zona = $form.find("select[name='zona']").val();

    if (armaReqNivel > userNivel) {
        alert(`No puedes usar esa arma. Requiere nivel ${armaReqNivel}. Tu nivel: ${userNivel}`);
        $btn.prop("disabled", false);
        return;
    }

    // Si el enemigo seleccionado es el local (id 9999), simulamos en cliente
    if (String(id_enemigo) === "9999") {
        const dano = (zona === 'cabeza') ? danoCabeza : danoTorso;
        // Actualizar DOM del enemigo local
        const enemyDiv = $("#jugadores .jugador[data-id='9999']");
        if (!enemyDiv.length) {
            alert("Enemigo local no encontrado en la interfaz");
            $btn.prop("disabled", false);
            return;
        }
        // Restar vida visual
        const vitaFill = enemyDiv.find(".vida-fill");
        let currentWidth = parseFloat(vitaFill.css("width")) || (100 * vitaFill.parent().width()/vitaFill.parent().width());
        // mejor calcular en porcentaje a partir del atributo data-vida si existe
        let percent = parseFloat(enemyDiv.data("vida")) || 100;
        percent = Math.max(0, percent - (dano / 200 * 100)); // dano relativo
        enemyDiv.data("vida", percent);
        vitaFill.css("width", percent + "%");
        // Mostrar log local
        $("#log").append(`<div style="color:#fff">Atacaste a <b>${localEnemyData.nomb_usu}</b> con <b>${armaSel.text()}</b> (${zona}) - Daño: ${dano}. Vida restante: ${Math.round((percent/100)*200)}</div>`);
        // Actualizar puntos del atacante en la UI
        const attackerDiv = $(`#jugadores .jugador[data-id='${id_usuario}']`);
        if (attackerDiv.length) {
            const puntosP = attackerDiv.find(".puntos");
            const text = puntosP.text();
            const current = parseInt((text.match(/\d+/)||[0])[0]);
            const nuevo = current + dano;
            puntosP.text("Puntos: " + nuevo);
        }
        // Si enemigo local queda en 0 vida -> marcar eliminado y reiniciar después
        if (percent <= 0) {
            enemyDiv.addClass("eliminado");
            $("#log").append(`<div style="color:#ff0">Has eliminado al enemigo local. Reiniciando enemigo local...</div>`);
            setTimeout(() => {
                // Reiniciar vida y puntos del enemigo local
                enemyDiv.removeClass("eliminado");
                enemyDiv.data("vida", 100);
                enemyDiv.find(".vida-fill").css("width", "100%");
                enemyDiv.find(".puntos").text("Puntos: 0");
                $("#log").append(`<div style="color:#0f0">Enemigo local reiniciado.</div>`);
            }, 2000);
        }
        // scroll log
        $("#log").scrollTop($("#log")[0].scrollHeight);
        $btn.prop("disabled", false);
        return;
    }

    // Si no es local, enviar petición normal al backend
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
                    // si el backend devuelve info estructurada, la mostramos
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
        error: function(xhr, status, err) {
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