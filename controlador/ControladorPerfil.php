<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../model/Usuario.php';
require_once '../dao/UsuarioDAO.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

$usuario = $_SESSION['usuario'];
$usuarioId = $usuario->getId();
$rol = $usuario->getRol();
$usuarioDAO = new UsuarioDAO();

$perfilRedireccion = match ($rol) {
    'administrador' => 'PerfilAdmin.php',
    'cliente' => 'PerfilCliente.php',
    'abogado' => 'PerfilAbogado.php',
    default => 'login.php'
};

echo "Acción: $accion <br>";
echo "Redireccionar a: $perfilRedireccion <br>";

switch ($accion) {
    case 'actualizar_perfil':
        echo "Actualizando perfil...<br>";
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $usuarioNombre = $_POST['usuario'];
        $contraseña = !empty($_POST['contraseña']) ? password_hash($_POST['contraseña'], PASSWORD_DEFAULT) : $usuario->getContraseña();
    
        $imagenActual = $usuario->getImagen();
    
        $usuarioEditado = new Usuario();
        $usuarioEditado->setId($usuarioId);
        $usuarioEditado->setNombre($nombre);
        $usuarioEditado->setApellidoPaterno($apellido_paterno);
        $usuarioEditado->setApellidoMaterno($apellido_materno);
        $usuarioEditado->setCorreo($correo);
        $usuarioEditado->setTelefono($telefono);
        $usuarioEditado->setUsuario($usuarioNombre);
        $usuarioEditado->setContraseña($contraseña);
        $usuarioEditado->setRol($rol);
        $usuarioEditado->setImagen($imagenActual); 
    
        if ($usuarioDAO->actualizar($usuarioEditado)) {
            $_SESSION['usuario'] = $usuarioEditado; 
            $_SESSION['mensaje'] = 'Perfil actualizado correctamente.';
            echo "Perfil actualizado correctamente.<br>";
        } else {
            $_SESSION['mensaje'] = 'Error al actualizar el perfil.';
            echo "Error al actualizar el perfil.<br>";
        }
        header("Location: ../$perfilRedireccion");
        exit();
    

    case 'actualizar_imagen':
        echo "Actualizando imagen...<br>";
        if ($_FILES['imagen']['name']) {
            $target_dir = "../src/img/";
            $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Verificar que es una imagen
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                    $imagen = basename($_FILES["imagen"]["name"]);
                    $usuario->setImagen($imagen);

                    if ($usuarioDAO->actualizar($usuario)) {
                        $_SESSION['mensaje'] = 'Imagen actualizada correctamente.';
                        $_SESSION['usuario']->setImagen($imagen); 
                        echo "Imagen actualizada correctamente.<br>";
                    } else {
                        $_SESSION['mensaje'] = 'Error al actualizar la imagen.';
                        echo "Error al actualizar la imagen.<br>";
                    }
                } else {
                    $_SESSION['mensaje'] = 'Error al subir la imagen.';
                    echo "Error al subir la imagen.<br>";
                }
            } else {
                $_SESSION['mensaje'] = 'El archivo no es una imagen.';
                echo "El archivo no es una imagen.<br>";
            }
        }
        header("Location: ../$perfilRedireccion");
        exit();

    case 'eliminar_imagen':
        echo "Eliminando imagen...<br>";
        // Establecer la imagen predeterminada
        $imagen_predeterminada = 'default.png';
        $usuario->setImagen($imagen_predeterminada);

        if ($usuarioDAO->actualizar($usuario)) {
            $_SESSION['mensaje'] = 'Imagen eliminada correctamente.';
            $_SESSION['usuario']->setImagen($imagen_predeterminada); 
            echo "Imagen eliminada correctamente.<br>";
        } else {
            $_SESSION['mensaje'] = 'Error al eliminar la imagen.';
            echo "Error al eliminar la imagen.<br>";
        }
        header("Location: ../$perfilRedireccion");
        exit();

    default:
        $_SESSION['mensaje'] = 'Acción no reconocida.';
        echo "Acción no reconocida.<br>";
        header("Location: ../$perfilRedireccion");
        exit();
}
?>
