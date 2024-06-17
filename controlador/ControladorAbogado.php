<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../model/Usuario.php';
require_once '../dao/UsuarioDAO.php';

echo "Datos recibidos:<br>";
var_dump($_POST);
var_dump($_GET);
var_dump($_FILES);  // Añadir esto para ver los archivos subidos

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

echo "Acción: $accion<br>";

$usuarioDAO = new UsuarioDAO();

switch ($accion) {
    case 'agregar':
        echo "Proceso de agregar abogado iniciado.<br>";
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $usuario = $_POST['usuario'];
        $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
        $rol = 'abogado';

        // Subir la imagen
        $imagen = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../src/img/";
            $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            echo "Target file: $target_file<br>";

            // Verificar que es una imagen
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if ($check !== false) {
                echo "El archivo es una imagen - " . $check["mime"] . ".<br>";
                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                    echo "La imagen " . basename($_FILES["imagen"]["name"]) . " ha sido subida.<br>";
                    $imagen = basename($_FILES["imagen"]["name"]);
                } else {
                    echo "Error al subir la imagen.<br>";
                }
            } else {
                echo "El archivo no es una imagen.<br>";
            }
        } else {
            echo "No se ha proporcionado una imagen o hubo un error en la subida.<br>";
        }

        $nuevoAbogado = new Usuario();
        $nuevoAbogado->setNombre($nombre);
        $nuevoAbogado->setApellidoPaterno($apellido_paterno);
        $nuevoAbogado->setApellidoMaterno($apellido_materno);
        $nuevoAbogado->setCorreo($correo);
        $nuevoAbogado->setTelefono($telefono);
        $nuevoAbogado->setUsuario($usuario);
        $nuevoAbogado->setContraseña($contraseña);
        $nuevoAbogado->setRol($rol);
        $nuevoAbogado->setImagen($imagen);

        echo "Nuevo abogado:<br>";
        var_dump($nuevoAbogado);

        if ($usuarioDAO->crear($nuevoAbogado)) {
            echo "Abogado agregado correctamente.<br>";
            header('Location: ../gestion_abogados.php?success=agregar');
            exit();  // Asegúrate de terminar el script después de la redirección
        } else {
            echo "Error al agregar el abogado.<br>";
            header('Location: ../gestion_abogados.php?error=agregar');
            exit();
        }
        break;

    case 'editar':
        echo "Datos del formulario para editar:<br>";
        var_dump($_POST);
        var_dump($_FILES);

        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $usuario = $_POST['usuario'];
        $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);
        $rol = 'abogado';

        // Subir la imagen
        $imagen = $_POST['imagen_actual'];
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../src/img/";
            $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            echo "Target file: $target_file<br>";

            // Verificar que es una imagen
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if ($check !== false) {
                echo "El archivo es una imagen - " . $check["mime"] . ".<br>";
                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                    echo "La imagen " . basename($_FILES["imagen"]["name"]) . " ha sido subida.<br>";
                    $imagen = basename($_FILES["imagen"]["name"]);
                } else {
                    echo "Error al subir la imagen.<br>";
                }
            } else {
                echo "El archivo no es una imagen.<br>";
            }
        } else {
            echo "No se ha proporcionado una nueva imagen o hubo un error en la subida.<br>";
        }

        $abogadoEditado = new Usuario();
        $abogadoEditado->setId($id);
        $abogadoEditado->setNombre($nombre);
        $abogadoEditado->setApellidoPaterno($apellido_paterno);
        $abogadoEditado->setApellidoMaterno($apellido_materno);
        $abogadoEditado->setCorreo($correo);
        $abogadoEditado->setTelefono($telefono);
        $abogadoEditado->setUsuario($usuario);
        $abogadoEditado->setContraseña($contraseña);
        $abogadoEditado->setRol($rol);
        $abogadoEditado->setImagen($imagen);

        echo "Abogado editado:<br>";
        var_dump($abogadoEditado);

        if ($usuarioDAO->actualizar($abogadoEditado)) {
            echo "Abogado editado correctamente.<br>";
            header('Location: ../gestion_abogados.php?success=editar');
            exit();
        } else {
            echo "Error al editar el abogado.<br>";
            header('Location: ../gestion_abogados.php?error=editar');
            exit();
        }
        break;

    case 'eliminar':
        echo "Datos de eliminación:<br>";
        var_dump($_GET);

        $id = $_GET['id'];
        if ($usuarioDAO->eliminar($id)) {
            echo "Abogado eliminado correctamente.<br>";
            header('Location: ../gestion_abogados.php?success=eliminar');
            exit();
        } else {
            echo "Error al eliminar el abogado.<br>";
            header('Location: ../gestion_abogados.php?error=eliminar');
            exit();
        }
        break;

    default:
        echo "Acción no reconocida.<br>";
        header('Location: ../gestion_abogados.php?error=accion_no_reconocida');
        exit();
}
?>
