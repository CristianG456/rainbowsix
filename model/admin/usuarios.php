<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

// ✅ Verificación segura de sesión
if (empty($_SESSION['id_usuario']) || !isset($_SESSION['id_usuario'])) {
    session_unset();
    session_destroy();
    header('Location: ../../index.html');
    exit;
}

$usu = (int)$_SESSION['id_usuario'];

// ✅ Validar que el usuario exista
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id_rol WHERE usuario.id_usuario = :usu");
$sql->bindParam(':usu', $usu, PDO::PARAM_INT);
$sql->execute();
$fila = $sql->fetch(PDO::FETCH_ASSOC);

if (!$fila) {
    session_unset();
    session_destroy();
    header('Location: ../../index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - R6</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="../../controller/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="admin.php">
                <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar">
                <span class="fw-bold ms-2"> Rainbow Six Siege </span>
            </a>
        </div>
        <div class="links-header">
            <a class="volver bi bi-arrow-left-circle" href="admin.php"> Volver </a>
        </div>
    </nav>
</header>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-center mb-4">
        <input type="text" id="buscar" class="form-control w-50 me-2" placeholder="Buscar jugador...">
    </div>
</div>

<!-- Contenedor donde se cargan los resultados -->
<div class="container" id="resultado-busqueda">
    <div class="text-center text-secondary">Cargando jugadores...</div>
</div>

<!-- Modal de edición -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Editar Jugador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modal-body-editar">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const inputBuscar = document.getElementById('buscar');
const contenedor = document.getElementById('resultado-busqueda');

// función para cargar jugadores (búsqueda o lista completa con paginación)
function cargarJugadores(pagina = 1, termino = '') {
    pagina = parseInt(pagina) || 1;
    contenedor.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
    `;

    const url = `buscar_jugadores.php?pagina=${pagina}&buscar=${encodeURIComponent(termino)}&t=${Date.now()}`; // t evita cache
    fetch(url)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.text();
        })
        .then(html => {
            contenedor.innerHTML = html;
            // no es necesario volver a ligar listeners si usamos delegación (abajo)
        })
        .catch(err => {
            console.error('Error al cargar jugadores:', err);
            contenedor.innerHTML = "<p class='text-danger text-center'>Error al cargar jugadores.</p>";
        });
}

// Delegación de eventos para botones "Editar" y para paginación
document.addEventListener('click', function(e) {
    // --- Editar (botón dentro de la tabla) ---
    const editarBtn = e.target.closest('.editar-btn');
    if (editarBtn) {
        const userId = editarBtn.getAttribute('data-id');
        const modalBody = document.getElementById('modal-body-editar');

        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;

        fetch(`editar_jugador_modal.php?id=${encodeURIComponent(userId)}`)
            .then(res => res.text())
            .then(html => modalBody.innerHTML = html)
            .catch(() => modalBody.innerHTML = "<p class='text-danger'>Error al cargar el formulario.</p>");

        return; // importante: si hizo click en editar, salimos
    }

    // --- Paginación (enlaces con clase .pagina) ---
    const paginaBtn = e.target.closest('.pagina');
    if (paginaBtn) {
        e.preventDefault();
        const pagina = parseInt(paginaBtn.getAttribute('data-page')) || 1;
        const termino = inputBuscar.value.trim();
        cargarJugadores(pagina, termino);
        // scroll opcional al top de la tabla
        window.scrollTo({ top: contenedor.getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
        return;
    }
});

// Cargar todos al iniciar
window.addEventListener('DOMContentLoaded', () => cargarJugadores());

// Buscar en tiempo real con debounce (para no saturar peticiones)
let debounceTimer = null;
inputBuscar.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    const valor = inputBuscar.value.trim();
    debounceTimer = setTimeout(() => {
        cargarJugadores(1, valor);
    }, 250); // 250 ms de espera
});
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

