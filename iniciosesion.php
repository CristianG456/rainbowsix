<?php
require_once("database/db.php");
$db = new Database();
$con = $db->conectar();
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Iniciar Sesión</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="controller/css/style2sesion.css">
</head>

<body>

  <main class="page-center">
    <div class="auth-card">

      <div class="text-center mb-3">
        <a href="index.html"> 
          <img src="controller/img/logo4.jpg" alt="Logo" class="img-fluid mb-2 br border border-danger rounded-pill" style="max-width:90px;"> 
        </a>
        <h3 class="text fw-bold">Rainbow Six Siege</h3>
        <h5 class="text-danger fw-bold">Iniciar Sesión</h5>
      </div>

      <!-- 🔹 FORMULARIO DE LOGIN -->
      <form method="POST" action="controller/inicio.php" autocomplete="off" novalidate>
        <div class="mb-3">
          <label for="nomb_usu" class="form-label">Usuario</label>
          <input id="nomb_usu" name="nomb_usu" type="text" class="form-control" placeholder="Tu usuario" required>
        </div>

        <div class="mb-3">
          <label for="contra_usu" class="form-label">Contraseña</label>
          <div class="input-group">
            <input id="contra_usu" name="contra_usu" type="password" class="form-control" placeholder="Tu contraseña" required>
            <button id="togglePass" type="button" class="btn btn-sm btn-outline-secondary input-group-text" title="Mostrar / ocultar">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <div class="row-actions text-center">
          <a href="#" class="link-accent" data-bs-toggle="modal" data-bs-target="#recoverModal">Recuperar contraseña</a>
        </div>

        <div class="mt-4">
          <button type="submit" name="iniciar" id="iniciar" class="btn btn-danger w-100 mt-3">
            Iniciar Sesión
          </button>
        </div>

        <div class="text-center mt-3">
          <small class="small-muted">¿No tienes cuenta? 
            <a href="registro.php" class="link-accent">Regístrate</a>
          </small>
        </div>
      </form>
    </div>
  </main>

  <!-- 🔹 MODAL RECUPERAR CONTRASEÑA -->
  <div class="modal fade" id="recoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="background:#0f0f0f; border:1px solid #222;">
        <div class="modal-header border-0">
          <h5 class="modal-title text-white">Recuperar contraseña</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="small-muted">Ingresa el correo asociado a tu cuenta y te enviaremos un código.</p>

          <form id="recoverForm" method="POST" action="recu_contrasena.php" autocomplete="off">
            <div class="mb-3">
              <label for="input_correo" class="form-label">Correo electrónico</label>
              <input id="input_correo" name="input_correo" type="email" class="form-control" placeholder="tu@correo.com" required>
            </div>
            <div class="d-grid">
              <button type="submit" name="inicioc" id="inicioc" class="btn btn-danger">Enviar instrucciones</button>
            </div>
          </form>
        </div>
        <div class="modal-footer border-0">
          <small class="small-muted">Revisa tu correo.</small>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔹 SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Mostrar / ocultar contraseña
    const toggle = document.getElementById('togglePass');
    const pass = document.getElementById('contra_usu');
    toggle.addEventListener('click', () => {
      if (pass.type === 'password') {
        pass.type = 'text';
        toggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
      } else {
        pass.type = 'password';
        toggle.innerHTML = '<i class="bi bi-eye"></i>';
      }
    });

    // Validación del formulario
    (function() {
      const form = document.querySelector('form[method="POST"]');
      form.addEventListener('submit', (e) => {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
          form.classList.add('was-validated');
        }
      }, false);
    })();
  </script>
</body>
</html>
