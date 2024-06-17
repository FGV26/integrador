<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'model/Usuario.php';
require_once 'dao/UsuarioDAO.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del usuario desde la sesión
$usuario = $_SESSION['usuario'];
$usuarioId = $usuario->getId(); // Obtener el ID del usuario directamente del objeto
$rol = $usuario->getRol(); // Obtener el rol del usuario directamente del objeto

$usuarioDAO = new UsuarioDAO();
$usuario = $usuarioDAO->obtenerPorId($usuarioId);

$base_url = 'http://localhost/INTEGRADOR/';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Estudio Jurídico Ortiz y Asociados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css">
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
        .profile-actions {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container" style="margin-top: 70px;">
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="form-group">
                    <label for="imagen">Imagen de Perfil</label><br>
                    <img src="<?php echo $base_url . 'src/img/' . $usuario->getImagen(); ?>" alt="Imagen de Perfil" class="img-fluid profile-img mb-2">
                    <div class="profile-actions">
                        <form action="controlador/ControladorPerfil.php" method="POST" enctype="multipart/form-data" id="uploadForm" style="display:inline;">
                            <input type="file" name="imagen" id="imagen" class="form-control mt-2" style="display:none;" onchange="this.form.submit()">
                            <input type="hidden" name="accion" value="actualizar_imagen">
                            <button type="button" class="btn btn-primary mt-2" onclick="document.getElementById('imagen').click();"><i class="fas fa-upload"></i> Actualizar Imagen</button>
                        </form>
                        <form action="controlador/ControladorPerfil.php" method="POST" style="display:inline;">
                            <input type="hidden" name="accion" value="eliminar_imagen">
                            <button type="submit" class="btn btn-danger mt-2"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </div>
                </div>
                <div class="form-group mt-4">
                    <strong>Mi rol es:</strong> <?php echo ucfirst($rol); ?>
                </div>
            </div>
            <div class="col-md-8">
                <h2>Hola, <?php echo $usuario->getNombre(); ?></h2>
                <p><strong>Nombre:</strong> <?php echo $usuario->getNombre(); ?></p>
                <p><strong>Apellido Paterno:</strong> <?php echo $usuario->getApellidoPaterno(); ?></p>
                <p><strong>Apellido Materno:</strong> <?php echo $usuario->getApellidoMaterno(); ?></p>
                <p><strong>Correo:</strong> <?php echo $usuario->getCorreo(); ?></p>
                <p><strong>Teléfono:</strong> <?php echo $usuario->getTelefono(); ?></p>
                <p><strong>Usuario:</strong> <?php echo $usuario->getUsuario(); ?></p>
                <p><strong>Contraseña: ********</strong> 
                <br>
                <?php if ($rol == 'abogado' || $rol == 'cliente') : ?>
                    <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#editPerfilModal">Editar Perfil</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="editPerfilModal" tabindex="-1" aria-labelledby="editPerfilModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPerfilModalLabel">Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="controlador/ControladorPerfil.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" id="editNombre" value="<?php echo $usuario->getNombre(); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editApellidoPaterno" class="form-label">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" id="editApellidoPaterno" value="<?php echo $usuario->getApellidoPaterno(); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editApellidoMaterno" class="form-label">Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" id="editApellidoMaterno" value="<?php echo $usuario->getApellidoMaterno(); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editCorreo" class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control" id="editCorreo" value="<?php echo $usuario->getCorreo(); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTelefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" id="editTelefono" value="<?php echo $usuario->getTelefono(); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editUsuario" class="form-label">Usuario</label>
                            <input type="text" name="usuario" class="form-control" id="editUsuario" value="<?php echo $usuario->getUsuario(); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContraseña" class="form-label">Contraseña</label>
                            <input type="password" name="contraseña" class="form-control" id="editContraseña" placeholder="Nueva Contraseña">
                        </div>
                        <input type="hidden" name="accion" value="actualizar_perfil">
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
