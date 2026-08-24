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
            header('Location: ../Administrador/GestionAbogados.php?error=validacion');
            exit();
        }

        $nuevoAbogado = new Usuario();
        $nuevoAbogado->setNombre(trim($_POST['nombre']));
        $nuevoAbogado->setApellidoPaterno(trim($_POST['apellido_paterno']));
        $nuevoAbogado->setApellidoMaterno(trim($_POST['apellido_materno']));
        $nuevoAbogado->setCorreo(trim($_POST['correo']));
        $nuevoAbogado->setTelefono(trim($_POST['telefono']));
        $nuevoAbogado->setUsuario(trim($_POST['usuario']));
        $nuevoAbogado->setContrasena(password_hash($_POST['contrasena'], PASSWORD_DEFAULT));
        $nuevoAbogado->setRol('abogado');
        $nuevoAbogado->setImagen(storage_upload_user_image($_FILES['imagen'] ?? null, 'users') ?: 'default.png');

        if ($usuarioDAO->crear($nuevoAbogado)) {
            $auditLogDAO->registrar($administradorId, 'crear', 'abogado', null, 'Registro de un nuevo abogado: ' . $_POST['nombre'] . ' ' . $_POST['apellido_paterno']);
            header('Location: ../Administrador/GestionAbogados.php?success=agregar');
            exit();
        }

        header('Location: ../Administrador/GestionAbogados.php?error=agregar');
        exit();

    case 'editar':
        $id = (int) $_POST['id'];
        if (ValidadorGestionUsuario::validar($_POST, $usuarioDAO, $id, false)) {
            header('Location: ../Administrador/GestionAbogados.php?error=validacion');
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

        $abogadoEditado = new Usuario();
        $abogadoEditado->setId($id);
        $abogadoEditado->setNombre(trim($_POST['nombre']));
        $abogadoEditado->setApellidoPaterno(trim($_POST['apellido_paterno']));
        $abogadoEditado->setApellidoMaterno(trim($_POST['apellido_materno']));
        $abogadoEditado->setCorreo(trim($_POST['correo']));
        $abogadoEditado->setTelefono(trim($_POST['telefono']));
        $abogadoEditado->setUsuario(trim($_POST['usuario']));
        $abogadoEditado->setContrasena($contrasena);
        $abogadoEditado->setRol('abogado');
        $abogadoEditado->setImagen($imagen);

        if ($usuarioDAO->actualizar($abogadoEditado)) {
            $auditLogDAO->registrar($administradorId, 'editar', 'abogado', (int) $id, 'Actualizacion de datos del abogado');
            header('Location: ../Administrador/GestionAbogados.php?success=editar');
            exit();
        }

        header('Location: ../Administrador/GestionAbogados.php?error=editar');
        exit();

    case 'eliminar':
        $id = $_POST['id'] ?? $_GET['id'];
        if ($usuarioDAO->eliminar($id)) {
            $auditLogDAO->registrar($administradorId, 'eliminar', 'abogado', (int) $id, 'Eliminacion de la cuenta de abogado');
            header('Location: ../Administrador/GestionAbogados.php?success=eliminar');
            exit();
        }

        header('Location: ../Administrador/GestionAbogados.php?error=eliminar');
        exit();

    default:
        header('Location: ../Administrador/GestionAbogados.php?error=accion_no_reconocida');
        exit();
}
?>
