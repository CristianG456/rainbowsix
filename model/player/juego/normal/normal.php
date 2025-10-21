<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'] ?? null;
if(!$usu){
    die("No has iniciado sesión. <a href='../../../../iniciosesion.php'>Login</a>");
}

$id_partida = $_GET['partida'] ?? null;
$id_sala = $_GET['sala'] ?? null;

if(!$id_partida || !$id_sala){
    die("Parámetros de partida o sala faltantes. Ve a <a href='ingreso_sala.php'>Salas</a>");
}

// Traer jugadores de la partida
$stmt = $con->prepare("
    SELECT u.id_usuario, u.usuario, u.vida
    FROM detalle_usuario_partida d
    JOIN usuario u ON (u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2)
    WHERE d.id_partida = ?
");
$stmt->execute([$id_partida]);
$jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pelea en Tiempo Real</title>
<link rel="stylesheet" href="../../../../controller/css/normal.css">
<style>
  .player { border:2px solid #444; padding:10px; margin:10px; border-radius:8px; display:inline-block; vertical-align:top; width:200px; }
  .vida-bar { background:#ccc; width:100%; height:15px; border-radius:5px; overflow:hidden; margin-top:5px; }
  .vida-fill { height:100%; background:#4CAF50; width:100%; transition: width 0.3s; }
  .vida-text { font-weight:bold; margin-top:5px; }
  .btn { margin:3px; padding:5px 10px; cursor:pointer; }
  #log { margin-top:20px; max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:5px; }
</style>
</head>
<body>
<a href="../juego.php" class="btn">Volver</a>
<h1>Partida #<?= $id_partida ?> - Sala #<?= $id_sala ?></h1>

<div id="jugadores">
<?php foreach($jugadores as $p): ?>
  <div class="player" data-id="<?= $p['id_usuario'] ?>">
    <div class="nombre"><?= htmlspecialchars($p['usuario']) ?></div>
    <div class="vida-text">Vida: <span class="vida-num"><?= $p['vida'] ?></span></div>
    <div class="vida-bar"><div class="vida-fill" style="width:<?= $p['vida'] ?>%"></div></div>
    <div>
      <button class="btn atacar" data-id-arma="1" data-zona="cabeza">Disparar cabeza</button>
      <button class="btn atacar" data-id-arma="1" data-zona="cuerpo">Disparar cuerpo</button>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div id="log"></div>

<script>
const partidaId = <?= $id_partida ?>;
const salaId = <?= $id_sala ?>;
const usuarioId = <?= $usu ?>;
const jugadoresDiv = document.getElementById("jugadores");
const logDiv = document.getElementById("log");

async function actualizarVidas(){
    try{
        const form = new FormData();
        let ids = Array.from(jugadoresDiv.querySelectorAll(".player")).map(p=>p.dataset.id).join(",");
        form.append("ids", ids);
        const resp = await fetch("estado_combate.php", {method:"POST", body:form});
        const data = await resp.json();
        if(data.vidas){
            for(let id in data.vidas){
                let playerDiv = jugadoresDiv.querySelector(`.player[data-id='${id}']`);
                if(playerDiv){
                    let vida = data.vidas[id];
                    playerDiv.querySelector(".vida-num").textContent = vida;
                    playerDiv.querySelector(".vida-fill").style.width = vida + "%";
                    let botones = playerDiv.querySelectorAll(".atacar");
                    botones.forEach(b=>b.disabled = vida<=0);
                }
            }
        }
    }catch(e){ console.error(e); }
}

async function disparar(objetivoId, armaId, zona){
    try{
        const form = new FormData();
        form.append("id_objetivo", objetivoId);
        form.append("id_arma", armaId);
        form.append("zona", zona);
        const resp = await fetch("combatee.php", {method:"POST", body:form});
        const data = await resp.json();
        if(data.error){
            logDiv.innerHTML += `<div style="color:red;">${data.error}</div>`;
        } else {
            logDiv.innerHTML += `<div>${data.mensaje}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
            if(data.id_objetivo){
                let playerDiv = jugadoresDiv.querySelector(`.player[data-id='${data.id_objetivo}']`);
                if(playerDiv){
                    let vida = data.vida_restante;
                    playerDiv.querySelector(".vida-num").textContent = vida;
                    playerDiv.querySelector(".vida-fill").style.width = vida + "%";
                    let botones = playerDiv.querySelectorAll(".atacar");
                    botones.forEach(b=>b.disabled = vida<=0);
                }
            }
        }
    }catch(e){ console.error(e); }
}

jugadoresDiv.addEventListener("click", function(e){
    if(e.target.classList.contains("atacar")){
        const playerDiv = e.target.closest(".player");
        const objetivoId = playerDiv.dataset.id;
        const armaId = e.target.dataset.idArma;
        const zona = e.target.dataset.zona;
        if(parseInt(objetivoId)!==usuarioId) disparar(objetivoId, armaId, zona);
    }
});

// Actualizar vidas cada 2 segundos
setInterval(actualizarVidas, 2000);
</script>
</body>
</html>
