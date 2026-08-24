<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__) . '/Services/ValidadorGestionUsuario.php';
require_once dirname(__DIR__, 2) . '/config/Storage.php';
require_once dirname(__DIR__) . '/Repositories/AuditLogDAO.php';

session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'administrador') {
    header('Location: ../IniciarSesion.php');
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$usuarioDAO = new UsuarioDAO();
$auditLogDAO = new AuditLogDAO();
$administradorId = (int) $_SESSION['usuario']->getId();

switch ($accion) {
    case 'agregar':
        if (ValidadorGestionUsuario::validar($_POST, $usuarioDAO, 0, true)) {
            header('Location: ../Administrador/GestionAdministradores.php?error=validacion');
            exit();
        }

        $nuevoAdministrador = new Usuario();
        $nuevoAdministrador->setNombre(trim($_POST['nombre']));
        $nuevoAdministrador->setApellidoPaterno(trim($_POST['apellido_paterno']));
        $nuevoAdministrador->setApellidoMaterno(trim($_POST['apellido_materno']));
        $nuevoAdministrador->setCorreo(trim($_POST['correo']));
        $nuevoAdministrador->setTelefono(trim($_POST['telefono']));
        $nuevoAdministrador->setUsuario(trim($_POST['usuario']));
        $nuevoAdministrador->setContrasena(password_hash($_POST['contrasena'], PASSWORD_DEFAULT));
        $nuevoAdministrador->setRol('administrador');
        $nuevoAdministrador->setImagen(storage_upload_user_image($_FILES['imagen'] ?? null, 'users') ?: 'default.png');

        if ($usuarioDAO->crear($nuevoAdministrador)) {
            $auditLogDAO->registrar($administradorId, 'crear', 'administrador', null, 'Registro de un nuevo administrador: ' . $_POST['nombre'] . ' ' . $_POST['apellido_paterno']);
            header('Location: ../Administrador/GestionAdministradores.php?success=agregar');
            exit();
        }

        header('Location: ../Administrador/GestionAdministradores.php?error=agregar');
        exit();

    case 'editar':
        $id = (int) $_POST['id'];
        if (ValidadorGestionUsuario::validar($_POST, $usuarioDAO, $id, false)) {
            header('Location: ../Administrador/GestionAdministradores.php?error=validacion');
            exit();
        }

        $usuarioActual = $usuarioDAO->obtenerPorId($id);
        $contrasena = !empty($_POST['contrasena'])
            ? password_hash($_POST['contrasena'], PASSWORD_DEFAULT)
            : ($usuarioActual ? $usuarioActual->getContrasena() : '');

        $imagen = $_POST['imagen_actual'] ?: 'default.png';
        $imagenSubida = storage_upload_user_image($_FILES['imagen'] ?? null, 'users');
        if ($imagenSubida) {
            $imagen = $imagenSubida;
        }

        $administradorEditado = new Usuario();
        $administradorEditado->setId($id);
        $administradorEditado->setNombre(trim($_POST['nombre']));
        $administradorEditado->setApellidoPaterno(trim($_POST['apellido_paterno']));
        $administradorEditado->setApellidoMaterno(trim($_POST['apellido_materno']));
        $administradorEditado->setCorreo(trim($_POST['correo']));
        $administradorEditado->setTelefono(trim($_POST['telefono']));
        $administradorEditado->setUsuario(trim($_POST['usuario']));
        $administradorEditado->setContrasena($contrasena);
        $administradorEditado->setRol('administrador');
        $administradorEditado->setImagen($imagen);

        if ($usuarioDAO->actualizar($administradorEditado)) {
            $auditLogDAO->registrar($administradorId, 'editar', 'administrador', (int) $id, 'Actualizacion de datos del administrador');
            header('Location: ../Administrador/GestionAdministradores.php?success=editar');
            exit();
        }

        header('Location: ../Administrador/GestionAdministradores.php?error=editar');
        exit();

    case 'eliminar':
        $id = $_POST['id'] ?? $_GET['id'];
        if ($administradorId === (int) $id) {
            header('Location: ../Administrador/GestionAdministradores.php?error=propia_cuenta');
            exit();
        }
        if ($usuarioDAO->eliminar($id)) {
            $auditLogDAO->registrar($administradorId, 'eliminar', 'administrador', (int) $id, 'Eliminacion de una cuenta administrativa');
            header('Location: ../Administrador/GestionAdministradores.php?success=eliminar');
            exit();
        }

        header('Location: ../Administrador/GestionAdministradores.php?error=eliminar');
        exit();

    default:
        header('Location: ../Administrador/GestionAdministradores.php?error=accion_no_reconocida');
        exit();
}
?>
