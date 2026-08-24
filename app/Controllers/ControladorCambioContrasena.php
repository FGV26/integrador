<?php
require_once dirname(__DIR__) . '/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__) . '/Models/Usuario.php';

session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'cliente') {
    header('Location: ../IniciarSesion.php');
    exit();
}

$usuario = $_SESSION['usuario'];
$usuarioDAO = new UsuarioDAO();
$clienteActual = $usuarioDAO->obtenerPorId((int) $usuario->getId());

if (!$clienteActual || !$clienteActual->getPasswordChangeRequired()) {
    header('Location: ../index.php');
    exit();
}

$nuevaContrasena = (string) ($_POST['nueva_contrasena'] ?? '');
$confirmarContrasena = (string) ($_POST['confirmar_contrasena'] ?? '');

if (strlen($nuevaContrasena) < 6) {
    header('Location: ../CambiarContrasena.php?error=longitud');
    exit();
}

if ($nuevaContrasena !== $confirmarContrasena) {
    header('Location: ../CambiarContrasena.php?error=confirmacion');
    exit();
}

$resultado = $usuarioDAO->actualizarContrasenaYReactivarCliente(
    (int) $clienteActual->getId(),
    password_hash($nuevaContrasena, PASSWORD_DEFAULT)
);

if ($resultado) {
    $_SESSION['usuario'] = $usuarioDAO->obtenerPorId((int) $clienteActual->getId()) ?: $clienteActual;
    unset($_SESSION['requiere_cambio_contrasena']);
    header('Location: ../Cliente/Perfil.php?success=reactivado');
    exit();
}

header('Location: ../CambiarContrasena.php?error=actualizar');
exit();
