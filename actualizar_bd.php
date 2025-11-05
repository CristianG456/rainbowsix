<?php
require_once("database/db.php");
$db = new Database();
$con = $db->conectar();

// Verificar si la columna ultima_actividad existe
$check = $con->query("SHOW COLUMNS FROM usuario LIKE 'ultima_actividad'");
if ($check->rowCount() == 0) {
    // Agregar la columna si no existe
    $con->query("ALTER TABLE usuario ADD COLUMN ultima_actividad DATETIME DEFAULT CURRENT_TIMESTAMP");
}

echo "Base de datos actualizada correctamente";