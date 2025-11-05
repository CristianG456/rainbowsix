<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='text-danger'>ID inválido.</p>";
    exit;
}

$id = (int)$_GET['id'];
$stmt = $con->prepare("SELECT * FROM usuario WHERE id_usuario = :id");
$stmt->execute([':id' => $id]);
$jugador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jugador) {
    echo "<p class='text-danger'>Jugador no encontrado.</p>";
    exit;
}

$estados = $con->prepare("SELECT * FROM estado WHERE id_estado IN (1, 2)");
$estados->execute();
$estados = $estados->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-white text-dark p-3 rounded">
<form method="POST" action="guardar_edicion.php">
    <input type="hidden" name="id" value="<?= $jugador['id_usuario'] ?>">

    <div class="mb-3">
        <label>Nombre</label>
        <input
            type="text"
            name="nomb_usu"
            class="form-control"
            value="<?= htmlspecialchars($jugador['nomb_usu']) ?>"
            readonly
        />
    </div>

    <div class="mb-3">
        <label>Correo</label>
        <input
            type="email"
            name="correo"
            class="form-control"
            value="<?= htmlspecialchars($jugador['correo']) ?>"
            readonly
        />
    </div>

    <div class="mb-3">
        <label>Rol</label>
        <input
            type="text"
            class="form-control"
            value="<?php
                $rol = $con->prepare("SELECT nom_rol FROM rol WHERE id_rol = ?");
                $rol->execute([$jugador['id_rol']]);
                echo htmlspecialchars($rol->fetchColumn());
            ?>"
            readonly
        />
    </div>

    <div class="mb-3">
        <label>Nivel</label>
        <input
            type="text"
            class="form-control"
            value="<?php
                $nivel = $con->prepare("SELECT nomb_nivel FROM nivel WHERE id_nivel = ?");
                $nivel->execute([$jugador['id_nivel']]);
                echo htmlspecialchars($nivel->fetchColumn());
            ?>"
            readonly
        />
    </div>

    <div class="mb-3">
        <label>Estado</label>
        <select name="id_estado" class="form-control" required>
            <?php foreach ($estados as $e): ?>
                <option
                    value="<?= $e['id_estado'] ?>"
                    <?= $jugador['id_estado_usu'] == $e['id_estado'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($e['estado']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="d-flex justify-content-between">
        <button type="submit" name="guardar" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancelar
        </button>
    </div>
</form>
</div>
