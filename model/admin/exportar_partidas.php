<?php
require_once("../../database/db.php");

$db = new Database();
$con = $db->conectar();

// Consulta de partidas
$sql = $con->prepare("
    SELECT 
        p.id_partida,
        p.id_sala,
        p.fecha_inicio,
        p.fecha_fin,
        p.id_ganador,
        u.nomb_usu AS ganador
    FROM partida p
    LEFT JOIN usuario u ON p.id_ganador = u.id_usuario
    ORDER BY p.fecha_inicio DESC
");
$sql->execute();
$partidas = $sql->fetchAll(PDO::FETCH_ASSOC);

// Nombre del archivo
$nombreArchivo = "Historial_Partidas_" . date('Y-m-d_His') . ".csv";

// Forzar descarga
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$nombreArchivo");

// Abrir output stream
$output = fopen('php://output', 'w');

// Escribir encabezados
fputcsv($output, ['ID Partida','ID Sala','Fecha Inicio','Fecha Fin','ID Ganador','Nombre Ganador']);

// Escribir los datos
foreach ($partidas as $p) {
    fputcsv($output, [
        $p['id_partida'],
        $p['id_sala'],
        $p['fecha_inicio'],
        $p['fecha_fin'],
        $p['id_ganador'],
        $p['ganador'] ?? 'Sin ganador'
    ]);
}

fclose($output);
exit;
