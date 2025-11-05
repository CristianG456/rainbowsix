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

// Traer todos los personajes
$stmt = $con->query("SELECT * FROM avatar");
$personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de Personajes - Admin</title>
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

<div class="admin-title">GESTIÓN DE PERSONAJES</div>

<div class="table-container">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="text-warning mb-0">Listado de Personajes</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#agregarModal">Nuevo Personaje</button>
</div>

<div class="table-responsive">
    <table class="table table-dark table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Personaje</th>
                <th>Avatar</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="personajes-table">
            <?php foreach ($personajes as $p): ?>
                <tr id="personaje-<?= $p['id_avatar'] ?>">
                    <td><?= $p['id_avatar'] ?></td>
                    <td><?= htmlspecialchars($p['nomb_avat']) ?></td>
                    <td>
                        <?php if ($p['url_personaje'] && file_exists("../../controller/img/" . $p['url_personaje'])): ?>
                            <img src="../../controller/img/<?= htmlspecialchars($p['url_personaje']) ?>" width="70" alt="Personaje">
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['url_avatar'] && file_exists("../../controller/img/" . $p['url_avatar'])): ?>
                            <img src="../../controller/img/<?= htmlspecialchars($p['url_avatar']) ?>" width="50" alt="Avatar">
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm editar-btn" data-id="<?= $p['id_avatar'] ?>" data-bs-toggle="modal" data-bs-target="#editarModal">Editar</button>
                        <button class="btn btn-danger btn-sm eliminar-btn" data-id="<?= $p['id_avatar'] ?>">Eliminar</button>
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
      <h5 class="mb-3 text-center">Agregar Nuevo Personaje</h5>

      <form id="form-agregar" enctype="multipart/form-data">
        <!-- Nombre -->
        <div class="mb-3">
          <input 
            type="text" 
            name="nomb_avat" 
            class="form-control" 
            placeholder="Nombre del Personaje" 
            required>
        </div>

        <!-- Imagen del Personaje -->
        <div class="mb-3">
          <label class="form-label fw-bold">Imagen del Personaje</label>
          <input 
            type="file" 
            name="url_personaje" 
            class="form-control" 
            accept=".png,.webp" 
            required>
          <small class="text-muted">
            Formatos permitidos: PNG o WEBP <b>(WEBP recomendado)</b>
          </small>
        </div>

        <!-- Imagen del Avatar -->
        <div class="mb-3">
          <label class="form-label fw-bold">Imagen del Avatar</label>
          <input 
            type="file" 
            name="url_avatar" 
            class="form-control" 
            accept=".png,.webp" 
            required>
          <small class="text-muted">
            Formatos permitidos: PNG o WEBP <b>(WEBP recomendado)</b>
          </small>
        </div>

        <button class="btn btn-success w-100" type="submit">Agregar</button>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content text-dark p-3" id="editar-body">
        <h5>Editar Personaje</h5>
        <div class="text-center text-secondary">Cargando...</div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Agregar personaje
const formAgregar = document.getElementById('form-agregar');
formAgregar.addEventListener('submit', e => {
    e.preventDefault();
    const data = new FormData(formAgregar);
    fetch('guardar_personaje.php', { method: 'POST', body: data })
    .then(res => res.json())
    .then(res => { if(res.success) location.reload(); else alert(res.message); });
});

// Editar personaje
document.querySelectorAll('.editar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        fetch(`editar_personaje.php?id=${id}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('editar-body').innerHTML = html;

                // listener para enviar actualización
                const formEditar = document.getElementById('form-editar');
                formEditar.addEventListener('submit', function(e){
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('actualizar_personaje.php', { method:'POST', body: formData })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success){
                                alert(res.message);
                                location.reload();
                            } else alert(res.message);
                        })
                        .catch(err => alert('Error: ' + err));
                });
            });
    });
});

// Eliminar personaje
document.querySelectorAll('.eliminar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if(!confirm('¿Seguro que quieres eliminar este personaje?')) return;
        const id = btn.dataset.id;
        fetch(`eliminar_personaje.php?id=${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(data => { if(data.success) document.getElementById(`personaje-${id}`).remove(); else alert(data.message); });
    });
});
</script>
</body>
</html>
