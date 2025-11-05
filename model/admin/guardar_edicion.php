<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ====== CARGAR CONFIGURACIÓN GLOBAL ======
require_once(__DIR__ . '/../../config.php'); // sube 2 niveles desde model/admin hasta rainbowsix

// ====== CARGA DE PHPMailer ======
if (!is_dir(PHPMailer_PATH)) {
    die("❌ No se encontró la carpeta PHPMailer en: " . PHPMailer_PATH);
}

require_once PHPMailer_PATH . '/Exception.php';
require_once PHPMailer_PATH . '/PHPMailer.php';
require_once PHPMailer_PATH . '/SMTP.php';

// ====== CONEXIÓN A LA BASE DE DATOS ======
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

// ====== ACTUALIZACIÓN DEL ESTADO DEL USUARIO ======
if (isset($_POST['id'], $_POST['id_estado'])) {

    $id_usuario = (int)$_POST['id'];
    $nuevo_estado = (int)$_POST['id_estado'];

    // Actualizar estado del usuario
    $sql = $con->prepare("UPDATE usuario SET id_estado_usu = :estado WHERE id_usuario = :id");
    $sql->execute([':estado' => $nuevo_estado, ':id' => $id_usuario]);

    if ($sql->rowCount() > 0) {

        // Obtener datos del usuario actualizado
        $stmt = $con->prepare("SELECT nomb_usu, correo FROM usuario WHERE id_usuario = :id");
        $stmt->execute([':id' => $id_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si el usuario fue activado (estado = 1)
        if ($usuario && $nuevo_estado == 1) {

            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'freefiremailadso@gmail.com';
                $mail->Password = 'arqz llic liaj iruc'; // contraseña de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Remitente y destinatario
                $mail->setFrom('freefiremailadso@gmail.com', 'Rainbow Six Siege');
                $mail->addAddress($usuario['correo'], $usuario['nomb_usu']);

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Tu cuenta ha sido activada';
                $mail->Body = '
                    <h2>¡Hola ' . htmlspecialchars($usuario['nomb_usu']) . '!</h2>
                    <p>Nos complace informarte que tu cuenta en <strong>Rainbow Six Siege</strong> ha sido <span style="color:green;">activada</span>.</p>
                    <p>Ya puedes iniciar sesión y disfrutar del juego.</p>
                    <br>
                    <p>Saludos,<br>El equipo de Rainbow Six Siege.</p>
                ';

                $mail->send();
                echo "<script>alert('✅ Estado actualizado y correo enviado correctamente'); window.location='usuarios.php';</script>";
                exit;
            } catch (Exception $e) {
                error_log('Error al enviar correo: ' . $mail->ErrorInfo);
                echo "<script>alert('⚠️ Estado actualizado, pero no se pudo enviar el correo.'); window.location='usuarios.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('✅ Estado actualizado correctamente'); window.location='usuarios.php';</script>";
            exit;
        }

    } else {
        echo "<script>alert('❌ No se pudo actualizar el estado'); window.location='usuarios.php';</script>";
        exit;
    }

} else {
    echo "<script>alert('⚠️ Datos incompletos'); window.location='usuarios.php';</script>";
    exit;
}
?>
