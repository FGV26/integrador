<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__) . '/Repositories/AuditLogDAO.php';

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../IniciarSesion.php');
    exit();
}

$id = (int) ($_POST['id'] ?? 0);
$rol = (string) ($_POST['rol'] ?? '');
$nuevoEstado = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1;
$rutas = [
    'cliente' => 'GestionClientes.php',
    'abogado' => 'GestionAbogados.php',
    'administrador' => 'GestionAdministradores.php',
];

if (!$id || !isset($rutas[$rol])) {
    header('Location: ../Administrador/PanelPrincipal.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$usuarioObjetivo = $usuarioDAO->obtenerPorId($id);
$administradorId = (int) $_SESSION['usuario']->getId();
$administradorActual = $usuarioDAO->obtenerPorId($administradorId);
$contrasenaConfirmacion = (string) ($_POST['contrasena_confirmacion'] ?? '');

if (!$usuarioObjetivo || $usuarioObjetivo->getRol() !== $rol) {
    header('Location: ../Administrador/' . $rutas[$rol] . '?error=usuario_no_valido');
    exit();
}

if (!$administradorActual || $administradorActual->getRol() !== 'administrador' || !password_verify($contrasenaConfirmacion, $administradorActual->getContrasena())) {
    header('Location: ../Administrador/' . $rutas[$rol] . '?error=contrasena');
    exit();
}

if ($rol === 'administrador' && $id === $administradorId && !$nuevoEstado) {
    header('Location: ../Administrador/' . $rutas[$rol] . '?error=propia_cuenta');
    exit();
}

$resultado = $usuarioDAO->cambiarEstadoActivo($id, $nuevoEstado);
if ($resultado) {
    $nombre = trim($usuarioObjetivo->getNombre() . ' ' . $usuarioObjetivo->getApellidoPaterno());
    $accion = $nuevoEstado ? 'activar' : 'desactivar';
    (new AuditLogDAO())->registrar(
        $administradorId,
        $accion,
        $rol,
        $id,
        ucfirst($accion) . ' cuenta de ' . $rol . ': ' . $nombre
    );
}

header('Location: ../Administrador/' . $rutas[$rol] . '?' . ($resultado ? 'success=estado' : 'error=estado'));
exit();
