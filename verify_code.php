<?php
session_start();
require_once("database/db.php");
$db = new database;
$con = $db->conectar();

if (isset($_POST['enviar'])) {
    $codigo = $_POST['codigo'] ?? '';

    if (empty($codigo)) {
        echo "<script>alert('Por favor ingresa el código.');</script>";
    } elseif (isset($_SESSION['code']) && trim($codigo) === trim((string)$_SESSION['code'])) {
        header("Location: cambio_contra.php");
        exit;
    } else {
        echo "<script>alert('Código incorrecto');</script>";
    }
}
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
  <link rel="stylesheet" href="controller/css/verificar_codigo.css">


</head>

<body>

  <main class="page-center">
    <div class="auth-card">


        <div class="text-center mb-3">
            <a href="index.html"> <img src="controller/img/logo4.jpg" alt="Logo" class="img-fluid mb-2 br border border-danger rounded-pill" style="max-width:90px;"> </a>
            <h3 class="text fw-bold">Rainbow Six Siege</h3>
            <h5 class="text-danger fw-bold">CÓDIGO DE VERIFICACIÓN</h5>
        </div>


            <form action="" method="POST" enctype="multipart/form-data" class="formulario">


                <div class="mb-3">
                <label for="codigo" class="form-label">Código</label>
                <input id="codigo" name="codigo" type="text" class="form-control" placeholder="Codigo" required>
                </div>
                    
                

                    <div class="mt-4">
                        <button type="submit" name="enviar" id="enviar" class="btn btn-danger w-100 mt-3"></i>Verificar</button>
                    </div>
            </form>

    </div>
</body>
</html>

