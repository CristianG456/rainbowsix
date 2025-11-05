<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id = $_GET['id'] ?? '';
if (!$id) exit('ID no válido');

$stmt = $con->prepare("SELECT * FROM armas WHERE id_arma=?");
$stmt->execute([$id]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$arma) exit('Arma no encontrada');

$tipos = $con->query("SELECT * FROM tipo_arma")->fetchAll(PDO::FETCH_ASSOC);
$niveles = $con->query("SELECT * FROM nivel")->fetchAll(PDO::FETCH_ASSOC);
?>

<form id="form-editar" enctype="multipart/form-data">
    <input type="hidden" name="id_arma" value="<?= $arma['id_arma'] ?>">

    <input type="text" name="nomb_arma" value="<?= htmlspecialchars($arma['nomb_arma']) ?>" class="form-control mb-2" required>
    <input type="number" name="dano_cabeza" value="<?= $arma['dano_cabeza'] ?>" class="form-control mb-2" required>
    <input type="number" name="dano_torso" value="<?= $arma['dano_torso'] ?>" class="form-control mb-2" required>

    <select name="id_tipo_arma" class="form-control mb-2" required>
        <?php foreach($tipos as $tipo): ?>
            <option value="<?= $tipo['id_tipo_arma'] ?>" <?= $arma['id_tipo_arma']==$tipo['id_tipo_arma']?'selected':'' ?>>
                <?= htmlspecialchars($tipo['tipo_arma']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="cant_balas" value="<?= $arma['cant_balas'] ?>" class="form-control mb-2" required>

    <select name="id_nivel_arma" class="form-control mb-2" required>
        <?php foreach($niveles as $nivel): ?>
            <option value="<?= $nivel['id_nivel'] ?>" <?= $arma['id_nivel_arma']==$nivel['id_nivel']?'selected':'' ?>>
                <?= htmlspecialchars($nivel['nomb_nivel']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="mb-2">
        Imagen actual:<br>
        <?php if($arma['img_arma'] && file_exists("../../controller/img/".$arma['img_arma'])): ?>
            <img src="../../controller/img/<?= htmlspecialchars($arma['img_arma']) ?>" width="50">
        <?php else: ?>-
        <?php endif; ?>
    </div>

    <input type="file" name="img_arma" accept=".png" class="form-control mb-2">
    <button type="submit" class="btn btn-warning w-100">Actualizar</button>
</form>

<script>
document.getElementById('form-editar').addEventListener('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);

    fetch('actualizar_arma.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if(res.success){
                alert(res.message);
                location.reload();
            } else {
                alert(res.message);
            }
        })
        .catch(err => alert('Error: '+err));
});
</script>
