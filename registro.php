<?php
require_once("database/db.php");
$db = new database;
$con = $db->conectar();
session_start();

if (isset($_POST["validar"])) {
    $user = ($_POST['nomb_usu']);
    $contras = ($_POST['contra_usu']); 
    $correo = ($_POST['correo']);
    
    $sql = $con->prepare("SELECT * FROM usuario WHERE nomb_usu = ? OR correo = ?");
    $sql->execute([$user, $correo]);
    $fila = $sql->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        echo '<script>alert("El usuario o correo ya existen, por favor cámbielos.");</script>';
        echo '<script>window.location="registro.php";</script>';
    }
    elseif ($user == "" || $contras == "" || $correo == "") {
        echo '<script>alert("Existen datos vacíos, por favor complete todos los campos.");</script>';
        echo '<script>window.location="registro.php";</script>';
    }
    else{
        $passhash = password_hash($contras, PASSWORD_DEFAULT, array("cost"=>5));


        $insertSQL = $con->prepare
        (" INSERT INTO usuario 
                (nomb_usu, contra_usu, correo, vida, ultimo_ingreso, id_rol, id_nivel, id_estado_usu, id_avatar)
            VALUES 
                (?, ?, ?, 200, NOW(), 2, 1, 2, NULL)");
        
        $resultado = $insertSQL->execute([$user, $passhash, $correo]);

    if ($resultado) {
        echo '<script>alert("Registro exitoso.");</script>';
        echo '<script>window.location="registro.php";</script>';
    } else {
        echo '<script>alert("Error al registrar el usuario.");</script>';
        echo '<script>window.location="registro.php";</script>';
    }
}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
   <link rel="stylesheet" href="controller/css/style2sesion.css">
   <link rel="icon" type="image/png" href="img/logo4.jpg"/>
  
</head>

<body>

  <main class="page-center">
    <div class="auth-card">


      <div class="text-center mb-3">
        <a href="index.html"><img src="controller/img/logo4.jpg" alt="Logo" class="img-fluid mb-2 br border border-danger rounded-pill" style="max-width:90px;"></a>
        <h3 class="text fw-bold">Rainbow Six Siege</h3>
        <h5 class="text-danger fw-bold">Registrarse</h5>
      </div>


      <form method="POST" name="registrar" autocomplete="off" novalidate>
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input id="nomb_usu" name="nomb_usu" type="text" class="form-control" placeholder="Usuario" required>
        </div>

        
        <div class="mb-3">
          <label for="pass" class="form-label">Contraseña</label>
          <div class="input-group">
            <input id="contra_usu" name="contra_usu" type="password" class="form-control" placeholder="***************" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="correo" class="form-label">Correo</label>
          <input id="correo" name="correo" type="email" class="form-control" placeholder="Correo" required>
        </div>

        <div class="mt-4">
          <button type="submit" name='validar' id='validar' class="btn btn-danger w-100 mt-3"></i>Registrate</button>

        
        </div>

        <div class="text-center mt-3">
          <small class="small-muted">¿Ya tienes cuenta? <a href="iniciosesion.php" class="link-accent">Inicia Sesión</a></small>
        </div>
      </form>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>