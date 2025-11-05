<?php
session_start();
require_once("database/db.php");
$db = new Database();
$con = $db->conectar();

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    
    // Remover al usuario de cualquier partida activa
    $stmt = $con->prepare("
        DELETE FROM detalle_usuario_partida 
        WHERE id_usuario1 = ? 
        AND id_partida IN (
            SELECT id_partida 
            FROM partida 
            WHERE id_estado_part IN (3,4,5)
        )
    ");
    $stmt->execute([$id_usuario]);
    
    // Destruir la sesión
    session_unset();
    session_destroy();
}

// Redireccionar al login
header("Location: iniciosesion.php");
exit;