<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../model/Usuario.php';
require_once '../dao/UsuarioDAO.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

echo "Acción: $accion<br>";
$usuarioDAO = new UsuarioDAO();

switch ($accion) {
    case 'agregar':
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $usuario = $_POST['usuario'];
        $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
        $rol = 'administrador';

        echo "Datos recibidos: ";
        var_dump($_POST, $_FILES);

        // Subir la imagen
        $target_dir = "../src/img/";
        $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        echo "Proceso de agregar administrador iniciado.<br>";
        echo "Target file: $target_file<br>";

        // Verificar que es una imagen
        if (!empty($_FILES["imagen"]["tmp_name"])) {
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                    $imagen = basename($_FILES["imagen"]["name"]);
                } else {
                    $imagen = null;
                }
            } else {
                $imagen = null;
            }
        } else {
            $imagen = null;
        }

        echo "Imagen: $imagen<br>";

        $nuevoAdministrador = new Usuario();
        $nuevoAdministrador->setNombre($nombre);
        $nuevoAdministrador->setApellidoPaterno($apellido_paterno);
        $nuevoAdministrador->setApellidoMaterno($apellido_materno);
        $nuevoAdministrador->setCorreo($correo);
        $nuevoAdministrador->setTelefono($telefono);
        $nuevoAdministrador->setUsuario($usuario);
        $nuevoAdministrador->setContraseña($contraseña);
        $nuevoAdministrador->setRol($rol);
        $nuevoAdministrador->setImagen($imagen);

        echo "Nuevo administrador:<br>";
        var_dump($nuevoAdministrador);

        if ($usuarioDAO->crear($nuevoAdministrador)) {
            echo "Administrador agregado correctamente.";
            header('Location: ../gestion_administradores.php');
        } else {
            echo "Error al crear el usuario.";
        }
        break;

    case 'editar':
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $usuario = $_POST['usuario'];
        $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
        $rol = 'administrador';

        echo "Datos recibidos: ";
        var_dump($_POST, $_FILES);

        // Subir la imagen
        if ($_FILES['imagen']['name']) {
            $target_dir = "../src/img/";
            $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Verificar que es una imagen
            if (!empty($_FILES["imagen"]["tmp_name"])) {
                $check = getimagesize($_FILES["imagen"]["tmp_name"]);
                if ($check !== false) {
                    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                        $imagen = basename($_FILES["imagen"]["name"]);
                    } else {
                        $imagen = null;
                    }
                } else {
                    $imagen = null;
                }
            } else {
                $imagen = null;
            }
        } else {
            $imagen = $_POST['imagen_actual'];
        }

        $administradorEditado = new Usuario();
        $administradorEditado->setId($id);
        $administradorEditado->setNombre($nombre);
        $administradorEditado->setApellidoPaterno($apellido_paterno);
        $administradorEditado->setApellidoMaterno($apellido_materno);
        $administradorEditado->setCorreo($correo);
        $administradorEditado->setTelefono($telefono);
        $administradorEditado->setUsuario($usuario);
        $administradorEditado->setContraseña($contraseña);
        $administradorEditado->setRol($rol);
        $administradorEditado->setImagen($imagen);

        echo "Administrador editado:<br>";
        var_dump($administradorEditado);

        if ($usuarioDAO->actualizar($administradorEditado)) {
            echo "Administrador editado correctamente.";
            header('Location: ../gestion_administradores.php');
        } else {
            echo "Error al actualizar el usuario.";
        }
        break;

    case 'eliminar':
        $id = $_GET['id'];
        if ($usuarioDAO->eliminar($id)) {
            echo "Administrador eliminado correctamente.";
            header('Location: ../gestion_administradores.php');
        } else {
            echo "Error al eliminar el usuario.";
        }
        break;

    default:
        echo "Acción no reconocida.";
        break;
}
?>
