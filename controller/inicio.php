<?php
session_start();
require_once("../database/db.php");

$db = new Database();
$con = $db->conectar();

$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST["iniciar"])) {

   $nombre = trim($_POST["nomb_usu"] ?? '');
   $contra = $_POST["contra_usu"] ?? '';

   if ($nombre === '' || $contra === '') {
       echo "<script>alert('Complete usuario y contraseña.'); window.location='../iniciosesion.php';</script>";
       exit();
   }

   $sql = $con->prepare("SELECT id_usuario, nomb_usu, contra_usu, id_rol FROM usuario WHERE nomb_usu = :nombre LIMIT 1");
   $sql->execute([':nombre' => $nombre]);
   $fila = $sql->fetch(PDO::FETCH_ASSOC);

   if (!$fila) {
       echo "<script>alert('Usuario no encontrado'); window.location='../iniciosesion.php';</script>";
       exit();
   }

   if (password_verify($contra, $fila['contra_usu'])) {

      $_SESSION['id_usuario'] = $fila['id_usuario'];
      $_SESSION['usuario'] = $fila['nomb_usu']; 
      $_SESSION['rol'] = (int)$fila['id_rol'];

      switch ($_SESSION['rol']) {
         case 1:
            header("Location: ../model/admin/admin.php");
            exit();

         case 2:
            header("Location: ../model/player/player.php");
            exit();

         default:
            header("Location: ../inicio.php");
            exit();
      }

   } else {
      echo "<script>alert('Usuario o contraseña incorrecto'); window.location='../iniciosesion.php';</script>";
      exit();
   }
}
?>