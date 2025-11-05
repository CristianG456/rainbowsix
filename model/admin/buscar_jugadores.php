<?php
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

// 🔹 Parámetros
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$pagina   = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10; 
$inicio = ($pagina - 1) * $por_pagina;

$searchTerm = "%$busqueda%";


if (!empty($busqueda)) {
    $sql = $con->prepare("
        SELECT u.id_usuario, u.nomb_usu, u.correo, 
               r.nom_rol, n.nomb_nivel, e.estado
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        INNER JOIN nivel n ON u.id_nivel = n.id_nivel
        INNER JOIN estado e ON u.id_estado_usu = e.id_estado
        WHERE u.id_rol = 2
          AND (u.nomb_usu LIKE :b1 OR u.correo LIKE :b2 OR r.nom_rol LIKE :b3 OR e.estado LIKE :b4)
        ORDER BY u.id_usuario ASC
        LIMIT :inicio, :por_pagina
    ");

    $sql->bindValue(':b1', $searchTerm);
    $sql->bindValue(':b2', $searchTerm);
    $sql->bindValue(':b3', $searchTerm);
    $sql->bindValue(':b4', $searchTerm);
    $sql->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $sql->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
} else {
    $sql = $con->prepare("
        SELECT u.id_usuario, u.nomb_usu, u.correo, 
               r.nom_rol, n.nomb_nivel, e.estado
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        INNER JOIN nivel n ON u.id_nivel = n.id_nivel
        INNER JOIN estado e ON u.id_estado_usu = e.id_estado
        WHERE u.id_rol = 2
        ORDER BY u.id_usuario ASC
        LIMIT :inicio, :por_pagina
    ");
    $sql->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $sql->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
}

$sql->execute();
$resultados = $sql->fetchAll(PDO::FETCH_ASSOC);


if (!empty($busqueda)) {
    $count = $con->prepare("
        SELECT COUNT(*) 
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        INNER JOIN nivel n ON u.id_nivel = n.id_nivel
        INNER JOIN estado e ON u.id_estado_usu = e.id_estado
        WHERE u.id_rol = 2
          AND (u.nomb_usu LIKE :b1 OR u.correo LIKE :b2 OR r.nom_rol LIKE :b3 OR e.estado LIKE :b4)
    ");
    $count->bindValue(':b1', $searchTerm);
    $count->bindValue(':b2', $searchTerm);
    $count->bindValue(':b3', $searchTerm);
    $count->bindValue(':b4', $searchTerm);
} else {
    $count = $con->prepare("
        SELECT COUNT(*) 
        FROM usuario 
        WHERE id_rol = 2
    ");
}
$count->execute();
$total_registros = $count->fetchColumn();
$total_paginas = ceil($total_registros / $por_pagina);


if ($resultados) {
    echo '<table class="table table-dark table-striped text-center align-middle">';
    echo '<thead class="table-primary text-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Nivel</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
          </thead><tbody>';

    foreach ($resultados as $fila) {
        echo "<tr>
                <td>{$fila['id_usuario']}</td>
                <td>{$fila['nomb_usu']}</td>
                <td>{$fila['correo']}</td>
                <td>{$fila['nom_rol']}</td>
                <td>{$fila['nomb_nivel']}</td>
                <td>{$fila['estado']}</td>
                <td>
                    <button class='btn btn-warning btn-sm editar-btn' 
                            data-bs-toggle='modal' 
                            data-bs-target='#editarModal' 
                            data-id='{$fila['id_usuario']}'>
                        <i class='bi bi-pencil'></i> Editar
                    </button>
                </td>
              </tr>";
    }

    echo '</tbody></table>';


    if ($total_paginas > 1) {
        echo '<nav><ul class="pagination justify-content-center">';
        for ($i = 1; $i <= $total_paginas; $i++) {
            $active = ($i == $pagina) ? 'active' : '';
            echo "<li class='page-item $active'>
                    <a href='#' class='page-link pagina' data-page='$i'>$i</a>
                  </li>";
        }
        echo '</ul></nav>';
    }
} else {
    echo "<p class='text-center text-danger mt-3'>No se encontraron jugadores.</p>";
}
?>

