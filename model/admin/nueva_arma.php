<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

// Verificación de sesión y rol de administrador
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../iniciosesion.php');
    exit;
}

$usu = $_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id_rol WHERE usuario.id_usuario = ? AND rol.id_rol = 1");
$sql->execute([$usu]);
if (!$sql->fetch()) {
    header('Location: ../../index.html');
    exit;
}

// Traer todas las armas
$stmt = $con->query("SELECT a.*, t.tipo_arma, n.nomb_nivel FROM armas a 
                     LEFT JOIN tipo_arma t ON a.id_tipo_arma = t.id_tipo_arma
                     LEFT JOIN nivel n ON a.id_nivel_arma = n.id_nivel");
$armas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traer tipos y niveles para los select
$tipos = $con->query("SELECT * FROM tipo_arma")->fetchAll(PDO::FETCH_ASSOC);
$niveles = $con->query("SELECT * FROM nivel")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de Armas - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../controller/css/admin.css">
<style>
    body { background: #0b0b0b; color: #fff; font-family: 'Montserrat', sans-serif; }
    .admin-title { text-align: center; margin-top: 6rem; color: #ffd700; font-size: 2rem; }
    .table-container { margin: 2rem auto; width: 90%; background: rgba(20, 20, 20, 0.85); border-radius: 10px; padding: 1.5rem; box-shadow: 0 0 20px rgba(255, 215, 0, 0.1); }
    table { color: #fff; text-align: center; }
    th { color: #ffd700; background: rgba(255, 255, 255, 0.1); }
    td { vertical-align: middle; }
    tr:hover { background-color: rgba(255, 255, 255, 0.05); }
    .btn-warning { color: #000; }
</style>
</head>
<body>
<header>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="admin.php">
            <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar me-2">
            <span class="fw-bold">Rainbow Six Siege</span>
        </a>
        <div class="links-header">
            <a class="volver bi bi-arrow-left-circle" href="admin.php"> Volver </a>
        </div>
    </div>
</nav>
</header>

<div class="admin-title">GESTIÓN DE ARMAS</div>

<div class="table-container">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="text-warning mb-0">Listado de Armas</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarModal">Nueva Arma</button>
</div>

<div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Daño Cabeza</th>
                <th>Daño Torso</th>
                <th>Tipo</th>
                <th>Balas</th>
                <th>Nivel</th>
                <th>Imagen</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="armas-table">
            <?php foreach ($armas as $a): ?>
                <tr id="arma-<?= $a['id_arma'] ?>">
                    <td><?= $a['id_arma'] ?></td>
                    <td><?= htmlspecialchars($a['nomb_arma']) ?></td>
                    <td><?= $a['dano_cabeza'] ?></td>
                    <td><?= $a['dano_torso'] ?></td>
                    <td><?= htmlspecialchars($a['tipo_arma'] ?? '-') ?></td>
                    <td><?= $a['cant_balas'] ?></td>
                    <td><?= htmlspecialchars($a['nomb_nivel'] ?? '-') ?></td>
                    <td>
                        <?php if ($a['img_arma'] && file_exists("../../controller/img/" . $a['img_arma'])): ?>
                            <img src="../../controller/img/<?= htmlspecialchars($a['img_arma']) ?>" width="50" alt="<?= htmlspecialchars($a['nomb_arma']) ?>">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm editar-btn" data-id="<?= $a['id_arma'] ?>" data-bs-toggle="modal" data-bs-target="#editarModal">Editar</button>
                        <button class="btn btn-danger btn-sm eliminar-btn" data-id="<?= $a['id_arma'] ?>">Eliminar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="agregarModal" tabindex="-1" aria-labelledby="agregarModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content text-dark p-3">
        <h5>Agregar Nueva Arma</h5>
        <form id="form-agregar" enctype="multipart/form-data">
            <div class="mb-2"><input type="text" name="nomb_arma" class="form-control" placeholder="Nombre" required></div>
            <div class="mb-2"><input type="number" name="dano_cabeza" class="form-control" placeholder="Daño Cabeza" required></div>
            <div class="mb-2"><input type="number" name="dano_torso" class="form-control" placeholder="Daño Torso" required></div>

            <div class="mb-2">
                <select name="id_tipo_arma" class="form-control" required>
                    <option value="">Seleccionar Tipo</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= $tipo['id_tipo_arma'] ?>"><?= htmlspecialchars($tipo['tipo_arma']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-2"><input type="number" name="cant_balas" class="form-control" placeholder="Cantidad de Balas" required></div>

            <div class="mb-2">
                <select name="id_nivel_arma" class="form-control" required>
                    <option value="">Seleccionar Nivel</option>
                    <?php foreach ($niveles as $nivel): ?>
                        <option value="<?= $nivel['id_nivel'] ?>"><?= htmlspecialchars($nivel['nomb_nivel']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-2"><input type="file" name="img_arma" class="form-control" accept=".png"></div>
            <button class="btn btn-success w-100" type="submit">Agregar</button>
        </form>
    </div>
</div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content text-dark p-3" id="editar-body">
        <h5>Editar Arma</h5>
        <div class="text-center text-secondary">Cargando...</div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const formAgregar = document.getElementById('form-agregar');
formAgregar.addEventListener('submit', e => {
    e.preventDefault();
    const data = new FormData(formAgregar);
    fetch('guardar_arma.php', { method: 'POST', body: data })
    .then(res => res.json())
    .then(res => { if(res.success) location.reload(); else alert(res.message); });
});

// Editar arma con listener dinámico
document.querySelectorAll('.editar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch(`editar_arma.php?id=${id}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('editar-body').innerHTML = html;

                // Listener para actualizar el arma
                const formEditar = document.getElementById('form-editar');
                formEditar.addEventListener('submit', function(e){
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('actualizar_arma.php', { method:'POST', body: formData })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success){
                                alert(res.message);
                                location.reload(); // recarga la tabla
                            } else {
                                alert(res.message);
                            }
                        })
                        .catch(err => alert('Error: ' + err));
                });
            });
    });
});

// Eliminar arma
document.querySelectorAll('.eliminar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if(!confirm('¿Seguro que quieres eliminar esta arma?')) return;
        const id = btn.dataset.id;
        fetch(`eliminar_arma.php?id=${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(data => { if(data.success) document.getElementById(`arma-${id}`).remove(); else alert(data.message); });
    });
});
</script>
</body>
</html>
