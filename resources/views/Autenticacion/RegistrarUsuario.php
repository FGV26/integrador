<?php
require_once dirname(__DIR__, 3) . '/app/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
require_once dirname(__DIR__, 3) . '/app/Services/ValidadorRegistroUsuario.php';
require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim((string) ($_POST['nombre'] ?? ''));
    $apellido_paterno = trim((string) ($_POST['apellido_paterno'] ?? ''));
    $apellido_materno = trim((string) ($_POST['apellido_materno'] ?? ''));
    $correo = trim((string) ($_POST['correo'] ?? ''));
    $telefono = trim((string) ($_POST['telefono'] ?? ''));
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $contrasenaPlano = (string) ($_POST['contrasena'] ?? '');

    $usuarioDAO = new UsuarioDAO();
    $errores = ValidadorRegistroUsuario::validar($_POST);

    if ($usuarioDAO->existeUsuario($usuario)) {
        $errores['usuario'] = 'El nombre de usuario ya se encuentra registrado.';
    }

    if ($usuarioDAO->existeCorreo($correo)) {
        $errores['correo'] = 'El correo ya se encuentra registrado.';
    }

    if (empty($errores)) {
        $cliente = new Usuario();
        $cliente->setNombre($nombre);
        $cliente->setApellidoPaterno($apellido_paterno);
        $cliente->setApellidoMaterno($apellido_materno);
        $cliente->setCorreo($correo);
        $cliente->setTelefono($telefono);
        $cliente->setUsuario($usuario);
        $cliente->setContrasena(password_hash($contrasenaPlano, PASSWORD_DEFAULT));
        $cliente->setRol('cliente');
        $cliente->setImagen('default.png');

        if ($usuarioDAO->crear($cliente)) {
            header('Location: /IniciarSesion.php?registro=exitoso');
            exit();
        }

        $error = "Error al registrar el usuario";
    } else {
        $error = implode(' ', array_values($errores));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/auth.css?v=<?php echo time(); ?>">
</head>
<body class="auth-page">
    <header class="auth-topbar">
        <a href="<?php echo $base_url; ?>index.php" class="auth-back-link">
            <i class="bi bi-arrow-left"></i>
            <span>Volver al inicio</span>
        </a>
    </header>

    <main class="auth-main">
        <div class="auth-shell auth-layout">
            <section class="auth-hero">
                <div class="auth-hero__content">
                    <span class="auth-eyebrow">Crear cuenta</span>
                    <h1>Abre tu acceso y <span>agenda</span> tu asesoria paso a paso.</h1>
                </div>
            </section>

            <section class="auth-card">
                <div class="auth-card__header">
                    <h2>Crea tu cuenta</h2>
                    <p>Completa tus datos principales para comenzar a usar el portal.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="auth-message auth-message--error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="post" action="" class="auth-form" novalidate>
                    <div class="auth-form__grid">
                        <div class="auth-field">
                            <label for="nombre">Nombre</label>
                            <input id="nombre" type="text" name="nombre" placeholder="Tu nombre" value="<?php echo htmlspecialchars($nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="auth-field">
                            <label for="apellido_paterno">Apellido paterno</label>
                            <input id="apellido_paterno" type="text" name="apellido_paterno" placeholder="Tu apellido paterno" value="<?php echo htmlspecialchars($apellido_paterno ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="auth-field">
                            <label for="apellido_materno">Apellido materno</label>
                            <input id="apellido_materno" type="text" name="apellido_materno" placeholder="Tu apellido materno" value="<?php echo htmlspecialchars($apellido_materno ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="auth-field">
                            <label for="telefono">Telefono</label>
                            <input id="telefono" type="text" name="telefono" placeholder="Tu telefono" value="<?php echo htmlspecialchars($telefono ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="auth-field auth-field--full">
                            <label for="correo">Correo</label>
                            <input id="correo" type="email" name="correo" placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($correo ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="auth-field">
                            <label for="usuario">Usuario</label>
                            <input id="usuario" type="text" name="usuario" placeholder="Tu nombre de usuario" value="<?php echo htmlspecialchars($usuario ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="auth-field">
                            <label for="contrasena">Contrasena</label>
                            <input id="contrasena" type="password" name="contrasena" placeholder="Crea tu contrasena" required>
                        </div>
                    </div>
                    <button class="auth-submit" type="submit">
                        <i class="bi bi-person-plus"></i>
                        <span>Crear cuenta</span>
                    </button>
                </form>

                <p class="auth-card__footer">
                    Ya tienes cuenta.
                    <a href="<?php echo $base_url; ?>IniciarSesion.php">Ingresa aqui</a>
                </p>
            </section>
        </div>
    </main>

    <script>
        setTimeout(function() {
            var messages = document.querySelectorAll('.auth-message');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 3000);
    </script>
</body>
</html>
