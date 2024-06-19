<?php
require_once 'dao/UsuarioDAO.php';
$base_url = 'http://localhost/INTEGRADOR/';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $usuario = $_POST['usuario'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT); // Hashing the password

    $usuarioDAO = new UsuarioDAO();
    $cliente = new Usuario();
    $cliente->setNombre($nombre);
    $cliente->setApellidoPaterno($apellido_paterno);
    $cliente->setApellidoMaterno($apellido_materno);
    $cliente->setCorreo($correo);
    $cliente->setTelefono($telefono);
    $cliente->setUsuario($usuario);
    $cliente->setContraseña($contraseña);
    $cliente->setRol('cliente');
    $cliente->setImagen('default.png');

    if ($usuarioDAO->crear($cliente)) {
        header('Location: login.php?registro=exitoso');
        exit();
    } else {
        $error = "Error al registrar el usuario";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/login.css">
    <style>
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="section-login">
        <div class="container">
            <h1 class="left-self">Register! 👋</h1>
            <span class="description my left-self">Welcome to our App.</span>
            <span class="description my">or register with your details.</span>
            <form method="post" action="">
                <input class="input-field" type="text" name="nombre" placeholder="Nombre" required>
                <input class="input-field" type="text" name="apellido_paterno" placeholder="Apellido Paterno" required>
                <input class="input-field" type="text" name="apellido_materno" placeholder="Apellido Materno">
                <input class="input-field" type="email" name="correo" placeholder="Correo" required>
                <input class="input-field" type="text" name="telefono" placeholder="Teléfono">
                <input class="input-field" type="text" name="usuario" placeholder="Usuario" required>
                <input class="input-field" type="password" name="contraseña" placeholder="Contraseña" required>
                <button class="btn btn-login my" type="submit">Register</button>
            </form>
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            <span class="description my">Already have an account? <a href="login.php">Login Now</a></span>
        </div>
    </div>
    <img src="<?php echo $base_url; ?>src/img/edificios.png" alt="" class="hero-img">
    <script>
        setTimeout(function() {
            var messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 3000);
    </script>
</body>
</html>
