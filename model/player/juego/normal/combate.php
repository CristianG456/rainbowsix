<?php
session_start();
require_once("../../../../database/db.php");
$db = new Database();
$con = $db->conectar();

$usu = $_SESSION['id_usuario'] ?? null;
if(!$usu){
    header("Location: ingreso_sala.php");
    exit;
}

if(!isset($_GET['partida']) || !isset($_GET['sala'])){
    echo "Parámetros de partida o sala faltantes.";
    exit;
}

$id_partida = intval($_GET['partida']);
$id_sala = intval($_GET['sala']);

// Obtener jugadores de la partida
$stmt = $con->prepare("
    SELECT DISTINCT u.id_usuario, u.nomb_usu, u.vida
    FROM detalle_usuario_partida d
    INNER JOIN usuario u ON (u.id_usuario = d.id_usuario1 OR u.id_usuario = d.id_usuario2)
    WHERE d.id_partida = ?
");
$stmt->execute([$id_partida]);
$jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener armas
$stmt2 = $con->prepare("SELECT * FROM armas");
$stmt2->execute();
$armas = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Combate - Partida #<?= htmlspecialchars($id_partida) ?></title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body{font-family:Arial,sans-serif;padding:16px}
h1{margin-bottom:8px}
#jugadores{display:flex;flex-wrap:wrap;gap:12px}
.jugador{border:1px solid #ccc;padding:10px;border-radius:6px;width:160px;text-align:center;background:#fafafa}
.vida{font-weight:bold;color:green}
.eliminado{font-weight:bold;color:red;}
form{margin-top:18px;display:flex;flex-wrap:wrap;gap:8px;align-items:center}
form label{font-size:14px;margin-right:6px}
select,button[type="submit"]{padding:6px}
#log-combate{border:1px solid #ddd;padding:10px;margin-top:16px;height:210px;overflow:auto;background:#fff}
#log-combate p{margin:6px 0;font-size:14px}
</style>
</head>
<body>

<h1>Combate — Partida #<?= htmlspecialchars($id_partida) ?></h1>

<div id="jugadores">
    <?php foreach($jugadores as $j): ?>
        <div class="jugador" data-id="<?= (int)$j['id_usuario'] ?>">
            <h4><?= htmlspecialchars($j['nomb_usu']) ?> <?= ($j['id_usuario']==$usu) ? "<small>(Tú)</small>" : "" ?></h4>
            <p>Vida: <span class="vida"><?= (int)$j['vida'] ?></span></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Formulario de ataque -->
<form id="form-ataque">
    <label for="sel-objetivo">Objetivo:</label>
    <select id="sel-objetivo" name="id_objetivo" required>
        <?php foreach($jugadores as $j): if($j['id_usuario']==$usu) continue; ?>
            <option value="<?= (int)$j['id_usuario'] ?>"><?= htmlspecialchars($j['nomb_usu']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="sel-arma">Arma:</label>
    <select id="sel-arma" name="id_arma" required>
        <?php foreach($armas as $a): ?>
            <option value="<?= (int)$a['id_arma'] ?>"><?= htmlspecialchars($a['nomb_arma']) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="sel-zona">Zona:</label>
    <select id="sel-zona" name="zona" required>
        <option value="cuerpo">Cuerpo</option>
        <option value="cabeza">Cabeza</option>
    </select>

    <button type="submit">Atacar</button>
</form>

<div id="log-combate">
    <p><em>Log de combate</em></p>
</div>

<script>
const PARTIDA = <?= json_encode($id_partida) ?>;

// Actualizar vidas de jugadores
function actualizarVidas(){
    const ids = $('.jugador').map(function(){ return $(this).data('id'); }).get();
    if(ids.length===0) return;

    $.ajax({
        url: 'estado_combate.php',
        method: 'POST',
        data: { ids: ids.join(',') },
        dataType: 'json',
        success(res){
            if(res.vidas){
                let vivos = 0;
                let ultimoVivo = null;

                for(const id in res.vidas){
                    const vida = res.vidas[id];
                    const elem = $('.jugador[data-id="'+id+'"] .vida');
                    if(vida <= 0){
                        elem.text('Eliminado').addClass('eliminado');
                    } else {
                        elem.text(vida).removeClass('eliminado');
                        vivos++;
                        ultimoVivo = id;
                    }
                }

                // Revisar si solo queda un jugador vivo
                if(vivos===1){
                    $('#log-combate').prepend('<p style="color:green;font-weight:bold;">¡Jugador #' + ultimoVivo + ' ha ganado la partida!</p>');
                    // Reiniciar vidas de todos los jugadores a 200
                    $.ajax({
                        url: 'reiniciar_vidas.php',
                        method: 'POST',
                        data: { id_partida: PARTIDA },
                        success(){ console.log('Vidas reiniciadas'); }
                    });
                }
            }
        }
    });
}
setInterval(actualizarVidas,1500);
$(document).ready(actualizarVidas);

// Enviar ataque
$('#form-ataque').on('submit', function(e){
    e.preventDefault();
    const id_objetivo = $('#sel-objetivo').val();
    const id_arma = $('#sel-arma').val();
    const zona = $('#sel-zona').val();
    if(!id_objetivo || !id_arma || !zona){
        alert('Selecciona objetivo, arma y zona.');
        return;
    }

    $.ajax({
        url: 'combatee.php',
        method: 'POST',
        data: { id_partida: PARTIDA, id_objetivo, id_arma, zona },
        dataType: 'json',
        success(res){
            if(res.error){
                $('#log-combate').prepend('<p style="color:red;">'+res.error+'</p>');
                return;
            }
            const msg = `${res.atacante_nombre} ha atacado a ${res.objetivo_nombre} con ${res.arma_nombre} en ${res.zona} causando ${res.dano} de daño. Vida restante: ${res.vida_restante}`;
            $('#log-combate').prepend('<p>'+msg+'</p>');
            actualizarVidas();
        },
        error(xhr){ console.error('Error en AJAX combate:', xhr.responseText); }
    });
});
</script>
</body>
</html>
