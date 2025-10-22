<?php
require_once("database/db.php");
$db = new database;
$con = $db->conectar();
session_start();

$sessionKey = 'nombre';  // Cambiar si usas otra clave de sesión, por ejemplo 'user'

if (!isset($_SESSION[$sessionKey])) {
    header('Location: index.html');
    exit;
}

$username = $_SESSION[$sessionKey];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar'])) {
    $contrasena = trim($_POST['new_contraseña'] ?? '');
    $contrasena_Verify = trim($_POST['confirmar_con'] ?? '');

    if ($contrasena === '' || $contrasena_Verify === '') {
        echo "<script>alert('Por favor completa todos los campos.'); history.back();</script>";
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9]+$/', $contrasena)) {
        echo "<script>alert('La contraseña solo puede contener letras y números.'); history.back();</script>";
        exit;
    }

    if ($contrasena !== $contrasena_Verify) {
        echo "<script>alert('Las contraseñas no coinciden.'); history.back();</script>";
        exit;
    }

    $hash = password_hash($contrasena, PASSWORD_BCRYPT, ["cost" => 12]);

    try {
        $stmt = $con->prepare("
            UPDATE usuario SET contra_usu = :pass WHERE nomb_usu = :user");
        $success = $stmt->execute([':pass' => $hash,':user' => $username]);

        if ($success && $stmt->rowCount() > 0) {
            unset($_SESSION['code']);
            unset($_SESSION['nombre']);
            unset($_SESSION['user']);
            session_destroy();
            echo "<script>alert('Contraseña actualizada correctamente. Inicia sesión con la nueva contraseña.'); window.location='index.html';</script>";
            exit;
        } else {
            echo "<script>alert('No se actualizó la contraseña. Usuario no encontrado o contraseña igual.'); history.back();</script>";
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error al actualizar contraseña: " . $e->getMessage());
        echo "<script>alert('Error interno. Intenta de nuevo más tarde.'); history.back();</script>";
        exit;
    }
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="controller/css/style.css">
    <title>nueva clave</title>
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
            <h5 class="text-danger fw-bold">Nueva Contraseña</h5>
        </div>

        
        <form action="" method= "POST" enctype = "multipart/form-data" class="formulario">
          
            <div class="mb-3">

               <label for="new_contraseña">Nueva contraseña:</label>
               <input type="password" class="form-control" id="new_contraseña" name="new_contraseña" placeholder="Nueva Contraseña" required>
               <span></span>
            </div>

            <div class="mb-3">

                <label for="confirmar_con">Confirmar Contraseña:</label>
                <input type="password" class="form-control" id="confirmar_con" name="confirmar_con" placeholder="Confirmar Contraseña" required>
                <span></span>
            </div>

            <div class="mt-4">
                <button type="submit"  name ="enviar" class="btn btn-danger w-100 mt-3" >Cambiar</button>
            </div>
        </form>

    </div>
        
</body>
