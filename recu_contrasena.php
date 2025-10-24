<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require_once("database/db.php");

$db = new database;
$con = $db->conectar();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inicioc'])) {

    $email = trim($_POST['input_correo'] ?? '');

    // Validación segura del correo
    if (empty($email)) {
        echo '<script>alert("Por favor, completa todos los campos."); window.history.back();</script>';
        exit;
    }

    // Buscar correo
    $stmt = $con->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo '<script>alert("El correo no existe en la base de datos."); window.history.back();</script>';
        exit;
    }

    // Si existe, generamos código
    $numero_aleatorio = rand(1000, 9999);
    $_SESSION['nombre'] = $row['nomb_usu'];
    $_SESSION['code'] = $numero_aleatorio;

    // Envío del correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'freefiremailadso@gmail.com';
        $mail->Password = 'arqz llic liaj iruc';
        $mail->Port = 587;

        $mail->setFrom('freefiremailadso@gmail.com', 'Rainbow Six Siege');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperar Contraseña';
        $mail->Body = 'Tu código de recuperación es: <b>' . $_SESSION['code'] . '</b>';
        $mail->AltBody = 'Tu código de recuperación es: ' . $_SESSION['code'];

        $mail->send();

        echo '<script>alert("Se ha enviado un código de recuperación a tu correo."); window.location="verify_code.php";</script>';
    } catch (Exception $e) {
        echo '<script>alert("No se pudo enviar el mensaje. Error: ' . $mail->ErrorInfo . '"); window.history.back();</script>';
    }
}
?>
