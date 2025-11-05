<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

$id = $_GET['id'] ?? 0;
$sql = $con->prepare("SELECT * FROM avatar WHERE id_avatar = ?");
$sql->execute([$id]);
$p = $sql->fetch(PDO::FETCH_ASSOC);
if(!$p){ echo "Personaje no encontrado"; exit; }
?>
<form id="form-editar" enctype="multipart/form-data">
    <input type="hidden" name="id_avatar" value="<?= $p['id_avatar'] ?>">
    <div class="mb-2"><input type="text" name="nomb_avat" class="form-control" value="<?= htmlspecialchars($p['nomb_avat']) ?>" required></div>
    <div class="mb-2">
        <label>Imagen Personaje (opcional)</label>
        <input type="file" name="url_personaje" class="form-control" accept=".png">
        <?php if($p['url_personaje']): ?><img src="../../controller/img/<?= $p['url_personaje'] ?>" width="80"><?php endif; ?>
    </div>
    <div class="mb-2">
        <label>Imagen Avatar (opcional)</label>
        <input type="file" name="url_avatar" class="form-control" accept=".png">
        <?php if($p['url_avatar']): ?><img src="../../controller/img/<?= $p['url_avatar'] ?>" width="80"><?php endif; ?>
    </div>
    <button class="btn btn-primary w-100" type="submit">Actualizar</button>
</form>
