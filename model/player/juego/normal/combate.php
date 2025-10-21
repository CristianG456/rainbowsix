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

// -----------------------------
// Buscar partida activa en la sala
// -----------------------------
$sqlPartida = $con->prepare("SELECT * FROM partida WHERE id_sala=? AND id_estado_part=1 LIMIT 1");
$sqlPartida->execute([$id_sala]);
$partidaExistente = $sqlPartida->fetch(PDO::FETCH_ASSOC);

if ($partidaExistente) {
    $id_partida = $partidaExistente['id_partida'];
} else {
    // Crear nueva partida
    $stmt = $con->prepare("INSERT INTO partida (fecha_inicio, id_estado_part, id_sala) VALUES (NOW(),1,?)");
    $stmt->execute([$id_sala]);
    $id_partida = $con->lastInsertId();
}

// -----------------------------
// Insertar jugador si no existe y menos de 5 jugadores
// -----------------------------
$sqlJugadores = $con->prepare("SELECT COUNT(*) FROM detalle_usuario_partida WHERE id_partida=?");
$sqlJugadores->execute([$id_partida]);
$cantidad = $sqlJugadores->fetchColumn();

$sqlYaEsta = $con->prepare("SELECT * FROM detalle_usuario_partida WHERE id_partida=? AND (id_usuario1=? OR id_usuario2=?)");
$sqlYaEsta->execute([$id_partida,$id_usuario,$id_usuario]);

if (!$sqlYaEsta->fetch() && $cantidad<5) {
    $sqlInsert = $con->prepare("INSERT INTO detalle_usuario_partida (puntos_total,id_usuario1,id_partida) VALUES (0,?,?)");
    $sqlInsert->execute([$id_usuario,$id_partida]);
}

// -----------------------------
// 🔹 Reiniciar vida de jugadores al entrar a una nueva partida
// -----------------------------
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
    $upd = $con->prepare("UPDATE usuario SET vida = 200 WHERE id_usuario IN ($placeholders) AND vida = 0");
    $upd->execute($jugadoresEnPartida);
}

// -----------------------------
// Obtener jugadores de la partida
// -----------------------------
$sqlJugadores = $con->prepare("
SELECT u.id_usuario,u.nomb_usu,u.vida,u.puntos,a.url_personaje
FROM usuario u
INNER JOIN avatar a ON u.id_avatar=a.id_avatar
INNER JOIN detalle_usuario_partida d ON u.id_usuario=d.id_usuario1 OR u.id_usuario=d.id_usuario2
WHERE d.id_partida=?
");
$sqlJugadores->execute([$id_partida]);
$jugadores = $sqlJugadores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Combate</title>
<link rel="stylesheet" href="../../../../controller/css/combate.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<h2>Partida #<?= $id_partida ?></h2>

<div class="jugadores" id="jugadores">
<?php foreach($jugadores as $j):
    $esMiJugador = $j['id_usuario']==$id_usuario;
    $vidaPorcentaje = max(0,min(100,($j['vida']/200)*100));
?>
<div class="jugador <?= $j['vida']<=0?'eliminado':'' ?> <?= $esMiJugador?'mi-jugador':'' ?>" data-id="<?= $j['id_usuario'] ?>" data-vida="<?= $vidaPorcentaje ?>">
    <div class="vida-barra"><div class="vida-fill" style="width:<?= $vidaPorcentaje ?>%"></div></div>
    <img src="/rainbowsix/controller/img/<?= basename($j['url_personaje']) ?>" class="avatar">
    <h3><?= htmlspecialchars($j['nomb_usu']) ?> <?= $esMiJugador?'<span>(Tú)</span>':'' ?></h3>
    <p>Puntos: <?= $j['puntos'] ?></p>
</div>
<?php endforeach; ?>
</div>

<div class="acciones">
<form id="ataqueForm">
    <label>Enemigo:</label>
    <select name="id_enemigo" required>
        <option value="">--Selecciona--</option>
        <?php foreach($jugadores as $j):
            if($j['id_usuario']!=$id_usuario && $j['vida']>0): ?>
                <option value="<?= $j['id_usuario'] ?>"><?= htmlspecialchars($j['nomb_usu']) ?></option>
        <?php endif; endforeach; ?>
    </select>

    <label>Arma:</label>
    <select name="id_arma" required>
    <?php
    $armas = $con->prepare("SELECT id_arma,nomb_arma FROM armas");
    $armas->execute();
    foreach($armas->fetchAll() as $arma){
        echo "<option value='{$arma['id_arma']}'>{$arma['nomb_arma']}</option>";
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
const id_partida = <?= $id_partida ?>;
const id_usuario = <?= $id_usuario ?>;
const rutaImgBase = "/rainbowsix/controller/img";

// Guardamos los IDs de jugadores que ya están en pantalla
let jugadoresActuales = {};
$("#jugadores .jugador").each(function(){
    const id = $(this).data("id").toString();
    jugadoresActuales[id] = parseFloat($(this).attr("data-vida"));
});

// ---------------------------
// Actualizar jugadores en tiempo real
// ---------------------------
function actualizarJugadores(){
    $.getJSON("actualizar_jugadores.php?id_partida="+id_partida, function(data){
        data.forEach(j => {
            const idStr = j.id_usuario.toString();
            const vidaPorcentaje = Math.max(0, Math.min(100, (j.vida/200)*100));
            const esMiJugador = j.id_usuario == id_usuario;

            // Solo actualizar la vida y puntos de ese jugador
            const jugadorDiv = $("#jugadores .jugador[data-id='"+idStr+"']");
            if(jugadorDiv.length){
                const vidaFill = jugadorDiv.find(".vida-fill");
                vidaFill.css("width", vidaPorcentaje + "%"); // sin animación
                jugadorDiv.find("p").text("Puntos: " + j.puntos);
                if(j.vida <= 0) jugadorDiv.addClass("eliminado");
            } else {
                // Si es un jugador nuevo, agregarlo
                const imgSrc = rutaImgBase + "/" + j.url_personaje;
                $("#jugadores").append(`
                    <div class="jugador" data-id="${idStr}" data-vida="${vidaPorcentaje}">
                        <div class="vida-barra"><div class="vida-fill" style="width:${vidaPorcentaje}%"></div></div>
                        <img src="${imgSrc}" class="avatar">
                        <h3>${j.nomb_usu}${esMiJugador?' (Tú)':''}</h3>
                        <p>Puntos: ${j.puntos}</p>
                    </div>
                `);
                // Agregar al select si no soy yo
                if(!esMiJugador){
                    $("#ataqueForm select[name='id_enemigo']").append(`<option value="${idStr}">${j.nomb_usu}</option>`);
                }
            }
        });

        // Eliminar jugadores que ya no están
        $("#jugadores .jugador").each(function(){
            const id = $(this).data("id").toString();
            if(!data.some(j => j.id_usuario.toString()===id)){
                $(this).remove();
                $("#ataqueForm select[name='id_enemigo'] option[value='"+id+"']").remove();
            }
        });
    });
}

// Polling cada 2 segundos
setInterval(actualizarJugadores, 2000);

// ---------------------------
// Botón Salir de la partida
// ---------------------------
$("#salirBtn").click(async function(){
    if(confirm("¿Salir de la partida?")){
        try {
            const form = new FormData();
            form.append("id_partida", id_partida);

            const resp = await fetch("salir_partida.php", { method:"POST", body:form });
            const data = await resp.json();

            if(data.ok){
                alert("Has salido de la partida.");
                window.location.href="ingreso_sala.php";
            } else {
                alert("Error al salir: "+(data.error||"Desconocido"));
            }
        } catch(err){
            console.error(err);
            alert("Error al conectar con el servidor.");
        }
    }
});

// ---------------------------
// Ataques
// ---------------------------
$("#ataqueForm").submit(function(e){
    e.preventDefault();
    $.post("actualizar_combate.php", $(this).serialize(), function(res){
        $("#log").prepend("<p>"+res+"</p>");
        actualizarJugadores(); // refrescar inmediatamente
    });
});
</script>

</body>
</html>
