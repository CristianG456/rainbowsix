<?php
require_once("conection/db.php");
$db = new database;
$con = $db->conectar();
session_start();



if (isset($_POST["validar"])) {
    $user = ($_POST['usu']);
    $contras = ($_POST['clave']); 
    $correo = ($_POST['correo']);
    

    $sql = $con->prepare("SELECT * FROM usuario WHERE nomb_usu = ? ");
    $sql->execute([$user]);
    $fila = $sql->fetch(PDO::FETCH_ASSOC);

    if ($fila) {
        echo '<script>alert("Documento o usuario ya existen, por favor cambielos.");</script>';
        echo '<script>window.location="registro.php";</script>';
    }
    elseif ($user == "" || $contras == "" || $correo == "") {
        echo '<script>alert("Existen datos vacíos, por favor complete todos los campos.");</script>';
        echo '<script>window.location="registro.php";</script>';
    }
    else{
        $CODIFICAR=password_hash($contras,PASSWORD_DEFAULT,array("cost"=>5));
        $insertSQL = $con->prepare("INSERT INTO usuario (nomb_usu , contra_usu, correo)
         VALUES (?, ?, ?)");
        $resultado = $insertSQL->execute([$user,$CODIFICAR,$correo]);
        echo '<script>alert("Registro exitoso.");</script>';
        echo '<script>window.location="iniciosesion.html";</script>';
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
   <link rel="stylesheet" href="css/style2registro.css">
   <link rel="icon" type="image/png" href="img/logo4.jpg"/>
  
</head>
<body>
<!-- fromulario -->

  <div class="card shadow p-4">
    <div class="text-center mb-3">
      <img src="img/logo4.jpg" alt="Logo" class="img-fluid mb-2 br border border-danger rounded-pill" style="max-width:90px;">
      <h3 class="text fw-bold">Rainbow Six Siege</h3>

      <h5 class="text-danger fw-bold">Registro de Usuario</h5>
    </div>

    <form method="POST"  autocomplete="off">
      <div class="mb-3">
        <label for="nombre" class="form-label">Usuario</label>
        <input type="text" class="form-control" name="usu" id="usu" placeholder="Ingrese su usuario">
      </div>
      <div class="mb-3">
        <label for="usuario" class="form-label">Contraseña</label>
        <input type="text" class="form-control" name="clave" id="clave" placeholder="Cree un contraseña">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Correo</label>
        <input type="email" class="form-control" name="correo" id="correo" placeholder="ejemplo@correo.com">
      </div>
      
      
      <button type="submit" name='validar' id='validar' class="btn btn-danger w-100 mt-3"></i> Registrarse</button>

      <div class="text-center mt-3">
          <small class="small-muted">¿Ya tienes cuenta? <a href="iniciosesion.php" class="link-accent">Inicia Sesión</a></small>
        </div>
      
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>