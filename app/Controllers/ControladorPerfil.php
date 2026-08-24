<?php
require_once dirname(__DIR__) . '/Models/Usuario.php';
require_once dirname(__DIR__) . '/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__, 2) . '/config/Storage.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../IniciarSesion.php');
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

$usuario = $_SESSION['usuario'];
$usuarioId = $usuario->getId();
$rol = $usuario->getRol();
$usuarioDAO = new UsuarioDAO();

$perfilRedireccion = match ($rol) {
    'administrador' => 'Administrador/Perfil.php',
    'cliente' => 'Cliente/Perfil.php',
    'abogado' => 'Abogado/Perfil.php',
    default => 'IniciarSesion.php'
};

switch ($accion) {
    case 'actualizar_perfil':
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $usuarioNombre = $_POST['usuario'];
        $contrasena = !empty($_POST['contrasena']) ? password_hash($_POST['contrasena'], PASSWORD_DEFAULT) : $usuario->getContrasena();
    
        $imagenActual = $usuario->getImagen();
    
        $usuarioEditado = new Usuario();
        $usuarioEditado->setId($usuarioId);
        $usuarioEditado->setNombre($nombre);
        $usuarioEditado->setApellidoPaterno($apellido_paterno);
        $usuarioEditado->setApellidoMaterno($apellido_materno);
        $usuarioEditado->setCorreo($correo);
        $usuarioEditado->setTelefono($telefono);
        $usuarioEditado->setUsuario($usuarioNombre);
        $usuarioEditado->setContrasena($contrasena);
        $usuarioEditado->setRol($rol);
        $usuarioEditado->setImagen($imagenActual); 
    
        if ($usuarioDAO->actualizar($usuarioEditado)) {
            if ($rol === 'cliente' && !empty($_POST['contrasena'])) {
                $usuarioDAO->marcarCambioContrasenaRequerido((int) $usuarioId, false, 'activo');
                $usuarioEditado = $usuarioDAO->obtenerPorId((int) $usuarioId) ?: $usuarioEditado;
            }
            $_SESSION['usuario'] = $usuarioEditado; 
            $_SESSION['mensaje'] = 'Perfil actualizado correctamente.';
        } else {
            $_SESSION['mensaje'] = 'Error al actualizar el perfil.';
        }
        header("Location: ../$perfilRedireccion");
        exit();
    

    case 'actualizar_imagen':
        if (!empty($_FILES['imagen']['name'])) {
            try {
                $imagen = storage_upload_user_image($_FILES['imagen'], 'users');
                if ($imagen) {
                    $usuario->setImagen($imagen);

                    if ($usuarioDAO->actualizar($usuario)) {
                        $_SESSION['mensaje'] = 'Imagen actualizada correctamente.';
                        $_SESSION['usuario']->setImagen($imagen);
                    } else {
                        $_SESSION['mensaje'] = 'Error al actualizar la imagen.';
                    }
                } else {
                    $_SESSION['mensaje'] = 'El archivo no es una imagen valida.';
                }
            } catch (Throwable $error) {
                $_SESSION['mensaje'] = 'No se pudo subir la imagen en este momento.';
            }
        } else {
            $_SESSION['mensaje'] = 'Selecciona una imagen antes de actualizar.';
        }
        header("Location: ../$perfilRedireccion");
        exit();

    case 'eliminar_imagen':
        // Establecer la imagen predeterminada
        $imagen_predeterminada = 'default.png';
        $usuario->setImagen($imagen_predeterminada);

        if ($usuarioDAO->actualizar($usuario)) {
            $_SESSION['mensaje'] = 'Imagen eliminada correctamente.';
            $_SESSION['usuario']->setImagen($imagen_predeterminada); 
        } else {
            $_SESSION['mensaje'] = 'Error al eliminar la imagen.';
        }
        header("Location: ../$perfilRedireccion");
        exit();

    default:
        $_SESSION['mensaje'] = 'Acción no reconocida.';
        header("Location: ../$perfilRedireccion");
        exit();
}
?>
