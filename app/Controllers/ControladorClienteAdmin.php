<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__) . '/Repositories/AuditLogDAO.php';

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../IniciarSesion.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$id = (int) ($_POST['id'] ?? 0);
$accion = (string) ($_POST['accion'] ?? 'editar');
$clienteActual = $usuarioDAO->obtenerPorId($id);

if (!$clienteActual || $clienteActual->getRol() !== 'cliente') {
    header('Location: ../Administrador/GestionClientes.php?error=usuario_no_valido');
    exit();
}

$administradorId = (int) $_SESSION['usuario']->getId();

if (in_array($accion, ['forzar_cambio', 'eliminar_inactividad'], true)) {
    $administradorActual = $usuarioDAO->obtenerPorId($administradorId);
    $contrasenaConfirmacion = (string) ($_POST['contrasena_confirmacion'] ?? '');

    if (!$administradorActual || !password_verify($contrasenaConfirmacion, $administradorActual->getContrasena())) {
        header('Location: ../Administrador/GestionClientes.php?error=contrasena');
        exit();
    }
}

if ($accion === 'forzar_cambio') {
    $ciclo = $usuarioDAO->obtenerEstadoCicloCliente($id);
    $estado = in_array($ciclo['estado'] ?? '', ['dormido', 'historico', 'depurable'], true)
        ? $ciclo['estado']
        : 'dormido';
    $resultado = $usuarioDAO->marcarCambioContrasenaRequerido($id, true, $estado);

    if ($resultado) {
        (new AuditLogDAO())->registrar(
            $administradorId,
            'forzar_cambio_contrasena',
            'cliente',
            $id,
            'Se solicito cambio de contrasena para reactivar al cliente'
        );
    }

    header('Location: ../Administrador/GestionClientes.php?' . ($resultado ? 'success=forzar_cambio' : 'error=estado'));
    exit();
}

if ($accion === 'eliminar_inactividad') {
    $resultado = $usuarioDAO->eliminarClientePorInactividad(
        $id,
        $administradorId,
        'Eliminado por inactividad segun politica del estudio'
    );

    if (!empty($resultado['ok'])) {
        (new AuditLogDAO())->registrar(
            $administradorId,
            'eliminar_inactividad',
            'cliente',
            $id,
            'Cliente eliminado por inactividad'
        );
        header('Location: ../Administrador/GestionClientes.php?success=eliminar_inactividad');
        exit();
    }

    header('Location: ../Administrador/GestionClientes.php?error=' . ($resultado['error'] ?? 'eliminar'));
    exit();
}

$cliente = new Usuario();
$cliente->setId($id);
$cliente->setNombre(trim($_POST['nombre'] ?? ''));
$cliente->setApellidoPaterno(trim($_POST['apellido_paterno'] ?? ''));
$cliente->setApellidoMaterno(trim($_POST['apellido_materno'] ?? ''));
$cliente->setCorreo(trim($_POST['correo'] ?? ''));
$cliente->setTelefono(trim($_POST['telefono'] ?? ''));
$cliente->setUsuario(trim($_POST['usuario'] ?? ''));
$cliente->setContrasena(!empty($_POST['contrasena']) ? password_hash($_POST['contrasena'], PASSWORD_DEFAULT) : $clienteActual->getContrasena());
$cliente->setRol('cliente');
$cliente->setImagen($clienteActual->getImagen());

$resultado = $usuarioDAO->actualizar($cliente);
if ($resultado) {
    (new AuditLogDAO())->registrar(
        (int) $_SESSION['usuario']->getId(),
        'editar',
        'cliente',
        $id,
        'Actualizacion de datos del cliente'
    );
}

header('Location: ../Administrador/GestionClientes.php?' . ($resultado ? 'success=editar' : 'error=editar'));
exit();
