<?php
session_start();
require_once("../../database/db.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.html');
    exit;
}

$usu = (int)$_SESSION['id_usuario'];
$sql = $con->prepare("SELECT * FROM usuario INNER JOIN rol ON usuario.id_rol = rol.id_rol WHERE usuario.id_usuario = :usu");
$sql->bindParam(':usu', $usu, PDO::PARAM_INT);
$sql->execute();
$fila = $sql->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administrador - R6</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@900&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="../../controller/css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="admin.php">
                    <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar">
                    <span class="fw-bold"> Rainbow Six Siege </span>
                </a>
            </div>
            <div class="links-header">
                <a class="volver bi bi-arrow-left-circle" href="admin.php"> Volver </a>
            </div>
        </nav>
    </header>

    <div class="container mt-5 pt-5">
        <form method="POST" class="d-flex justify-content-center mb-4">
            <input type="text" name="buscar" class="form-control w-50 me-2" placeholder="Buscar jugador...">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>

<?php
if (isset($_POST['buscar'])) {
    $busqueda = htmlspecialchars($_POST['buscar']);
    $stmt = $con->prepare("
        SELECT u.id_usuario, u.nomb_usu, u.correo, 
               r.nom_rol, 
               n.nomb_nivel, 
               e.estado
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        INNER JOIN nivel n ON u.id_nivel = n.id_nivel
        INNER JOIN estado e ON u.id_estado_usu = e.id_estado
        WHERE u.nomb_usu LIKE :b1 OR u.correo LIKE :b2 OR r.nom_rol LIKE :b3 OR e.estado LIKE :b4
        AND u.id_rol = :id_rol
    ");
    $searchTerm = "%$busqueda%";
    $stmt->execute([
        ':b1' => $searchTerm,
        ':b2' => $searchTerm,
        ':b3' => $searchTerm,
        ':b4' => $searchTerm,
        ':id_rol' => 2
    ]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        echo '<div class="container"><table class="table table-dark table-striped">';
        echo '<thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Rol</th><th>Nivel</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';
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
        echo '</tbody></table></div>';
    } else {
        echo "<p class='text-center text-danger'>No se encontraron jugadores.</p>";
    }
}
?>

<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content text-dark">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Editar Jugador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modal-body-editar">
        <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.editar-btn').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.getAttribute('data-id');
        const modalBody = document.getElementById('modal-body-editar');

        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;

        fetch(`editar_jugador_modal.php?id=${userId}`)
            .then(res => res.text())
            .then(html => {
                modalBody.innerHTML = html;
            })
            .catch(() => {
                modalBody.innerHTML = "<p class='text-danger'>Error al cargar el formulario.</p>";
            });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

