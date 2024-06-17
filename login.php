<?php
require_once 'dao/UsuarioDAO.php';
require_once 'model/Usuario.php';
$base_url = 'http://localhost/INTEGRADOR/';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    $usuarioDAO = new UsuarioDAO();
    $user = $usuarioDAO->autenticar($usuario, $contraseña);

    if ($user) {
        $_SESSION['usuario'] = $user;
        $_SESSION['rol'] = $user->getRol();
        if ($user->getRol() == 'cliente') {
            header('Location: index.php');
        } elseif ($user->getRol() == 'abogado') {
            header('Location: InterfazAbogado.php');
        } elseif ($user->getRol() == 'administrador') {
            header('Location: InterfazAdministrador.php');
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
            <h1 class="left-self">Login! 👋</h1>
            <span class="description my left-self">Welcome to our App.</span>
            <span class="description my">or login with your credentials.</span>
            <form method="post" action="">
                <input class="input-field" type="text" name="usuario" placeholder="Usuario" required>
                <input class="input-field" type="password" name="contraseña" placeholder="Contraseña" required>
                <button class="btn btn-login my" type="submit">Login</button>
            </form>
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
                <div class="message success">Usuario registrado con éxito. Ahora puedes iniciar sesión.</div>
            <?php endif; ?>
            <span class="description my">Don't have an account? <a href="<?php echo $base_url; ?>register.php">Sign Up Now</a></span>
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
