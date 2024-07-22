<?php
require_once 'model/Usuario.php';
require_once 'dao/UsuarioDAO.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'administrador') {
    header('Location: login.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$administradores = $usuarioDAO->obtenerPorRol('administrador');
$base_url = 'http://localhost/INTEGRADOR/';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores - Estudio Jurídico Ortiz y Asociados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/admin.css?v=<?php echo time(); ?>">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="bg-dark text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Estudio Jurídico Ortiz y Asociados</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="InterfazAdministrador.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="PerfilAdmin.php">Perfil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_abogados.php">Gestión de Abogados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestion_administradores.php">Gestión de Administradores</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestionCitas.php">Gestión de Citas</a>
                        </li>
                    </ul>
                </div>
                <a href="logout.php" class="btn btn-warning text-dark">Cerrar Sesión</a>
            </div>
        </nav>
    </header>


    <main class="container flex-grow-1 pt-5">
        <h1 class="mt-4">Gestión de Administradores</h1>
        <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#agregarAdministradorModal">Agregar Administrador</button>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($administradores as $administrador) : ?>
                    <tr>
                        <td><?php echo $administrador->getId(); ?></td>
                        <td><?php echo $administrador->getNombre(); ?></td>
                        <td><?php echo $administrador->getApellidoPaterno(); ?></td>
                        <td><?php echo $administrador->getApellidoMaterno(); ?></td>
                        <td><?php echo $administrador->getCorreo(); ?></td>
                        <td><?php echo $administrador->getTelefono(); ?></td>
                        <td><?php echo $administrador->getUsuario(); ?></td>
                        <td>
                            <button class="btn btn-warning editBtn" data-bs-toggle="modal" data-bs-target="#editAdministradorModal" data-id="<?php echo $administrador->getId(); ?>" data-nombre="<?php echo $administrador->getNombre(); ?>" data-apellido-paterno="<?php echo $administrador->getApellidoPaterno(); ?>" data-apellido-materno="<?php echo $administrador->getApellidoMaterno(); ?>" data-correo="<?php echo $administrador->getCorreo(); ?>" data-telefono="<?php echo $administrador->getTelefono(); ?>" data-usuario="<?php echo $administrador->getUsuario(); ?>" data-imagen="<?php echo $administrador->getImagen(); ?>">
                                Editar
                            </button>
                            <a href="controlador/ControladorAdministrador.php?accion=eliminar&id=<?php echo $administrador->getId(); ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este administrador?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <!-- Modal para agregar administrador -->
    <div class="modal fade" id="agregarAdministradorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Administrador</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="controlador/ControladorAdministrador.php" method="POST" enctype="multipart/form-data">
                        <!-- Campos del formulario -->
                        <div class="form-group">
                            <label for="txtNombre">Nombre</label>
                            <input type="text" name="nombre" id="txtNombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtApellidoPaterno">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" id="txtApellidoPaterno" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtApellidoMaterno">Apellido Materno</label>
                            <input type="text" name="apellido_materno" id="txtApellidoMaterno" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtCorreo">Correo</label>
                            <input type="email" name="correo" id="txtCorreo" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtTelefono">Teléfono</label>
                            <input type="text" name="telefono" id="txtTelefono" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtUsuario">Usuario</label>
                            <input type="text" name="usuario" id="txtUsuario" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtContraseña">Contraseña</label>
                            <input type="password" name="contraseña" id="txtContraseña" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="txtImagen">Imagen</label>
                            <input type="file" name="imagen" id="txtImagen" class="form-control">
                        </div>
                        <div style="display: flex; justify-content: center;">
                            <input type="hidden" name="accion" value="agregar">
                            <input type="submit" name="submit" value="Agregar" class="btn btn-info">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Editar Administrador -->
    <div class="modal fade" id="editAdministradorModal" tabindex="-1" aria-labelledby="editAdministradorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAdministradorModalLabel">Editar Administrador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="controlador/ControladorAdministrador.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" id="editNombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editApellidoPaterno" class="form-label">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" id="editApellidoPaterno" required>
                        </div>
                        <div class="mb-3">
                            <label for="editApellidoMaterno" class="form-label">Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" id="editApellidoMaterno">
                        </div>
                        <div class="mb-3">
                            <label for="editCorreo" class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control" id="editCorreo" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTelefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" id="editTelefono">
                        </div>
                        <div class="mb-3">
                            <label for="editUsuario" class="form-label">Usuario</label>
                            <input type="text" name="usuario" class="form-control" id="editUsuario" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContraseña" class="form-label">Contraseña</label>
                            <input type="password" name="contraseña" class="form-control" id="editContraseña" required>
                        </div>
                        <div class="form-group">
                            <label for="editImagen">Imagen</label>
                            <input type="file" name="imagen" id="editImagen" class="form-control">
                        </div>
                        <input type="hidden" name="imagen_actual" id="imagenActual">
                        <button type="submit" name="accion" value="editar" class="btn btn-success">Editar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer bg-dark text-white text-center py-3 mt-3">
        <p>&copy; 2023 Abogados Estudio Jurídico Ortiz y Asociados - Todos los derechos reservados</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        // Script para pasar los datos al modal de edición
        document.querySelectorAll('.editBtn').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                const apellidoPaterno = button.getAttribute('data-apellido-paterno');
                const apellidoMaterno = button.getAttribute('data-apellido-materno');
                const correo = button.getAttribute('data-correo');
                const telefono = button.getAttribute('data-telefono');
                const usuario = button.getAttribute('data-usuario');
                const imagen = button.getAttribute('data-imagen');

                document.getElementById('editId').value = id;
                document.getElementById('editNombre').value = nombre;
                document.getElementById('editApellidoPaterno').value = apellidoPaterno;
                document.getElementById('editApellidoMaterno').value = apellidoMaterno;
                document.getElementById('editCorreo').value = correo;
                document.getElementById('editTelefono').value = telefono;
                document.getElementById('editUsuario').value = usuario;
                document.getElementById('imagenActual').value = imagen;
            });
        });
    </script>
</body>

</html>