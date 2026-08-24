<?php
require_once dirname(__DIR__, 3) . '/app/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $usuarioDAO = new UsuarioDAO();
    $user = $usuarioDAO->autenticar($usuario, $contrasena);

    if ($user) {
        $_SESSION['usuario'] = $user;
        $_SESSION['rol'] = $user->getRol();
        $cicloCliente = $user->getRol() === 'cliente' ? $usuarioDAO->obtenerEstadoCicloCliente((int) $user->getId()) : null;
        if ($cicloCliente && !empty($cicloCliente['requiere_cambio'])) {
            $usuarioDAO->marcarCambioContrasenaRequerido((int) $user->getId(), true, $cicloCliente['estado']);
            $_SESSION['requiere_cambio_contrasena'] = true;
            header('Location: /CambiarContrasena.php');
            exit();
        }
        $usuarioDAO->registrarLogin((int) $user->getId());
        $user = $usuarioDAO->obtenerPorId((int) $user->getId()) ?: $user;
        $_SESSION['usuario'] = $user;
        if ($user->getRol() == 'cliente') {
            header('Location: /index.php');
        } elseif ($user->getRol() == 'abogado') {
            header('Location: /Abogado/PanelPrincipal.php');
        } elseif ($user->getRol() == 'administrador') {
            header('Location: /Administrador/PanelPrincipal.php');
        }
        exit();
    } else {
        $estadoAcceso = $usuarioDAO->obtenerEstadoAccesoPorUsuario($usuario);
        if ($estadoAcceso && (int) ($estadoAcceso['is_active'] ?? 1) !== 1) {
            if (($estadoAcceso['rol'] ?? '') === 'abogado') {
                $error = "Tu cuenta de abogado esta inactiva. Comunicate con el administrador para restaurar el acceso.";
            } elseif (($estadoAcceso['rol'] ?? '') === 'administrador') {
                $error = "Tu cuenta administrativa esta inactiva. Otro administrador debe restaurar tu acceso.";
            } else {
                $error = "Tu cuenta esta inactiva. Comunicate con el estudio para restaurar el acceso.";
            }
        } elseif (!$usuarioDAO->existeUsuario($usuario) && $usuarioDAO->fueClienteEliminado($usuario)) {
            $error = "Tu cuenta fue eliminada por inactividad. Registrate nuevamente para volver a usar el portal.";
        } else {
            $error = "Usuario o contrasena incorrectos";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesion</title>
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
                    <span class="auth-eyebrow">Acceso al sistema</span>
                    <h1>Ingresa a tu <span>espacio</span> y continua tu gestion legal.</h1>
                </div>
            </section>

            <section class="auth-card">
                <div class="auth-card__header">
                    <h2>Bienvenido de nuevo</h2>
                    <p>Ingresa tus credenciales para acceder al sistema del estudio juridico.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="auth-message auth-message--error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso'): ?>
                    <div class="auth-message auth-message--success">Usuario registrado con exito. Ahora puedes iniciar sesion.</div>
                <?php endif; ?>

                <form method="post" action="" class="auth-form" novalidate>
                    <div class="auth-field">
                        <label for="usuario">Usuario</label>
                        <input id="usuario" type="text" name="usuario" placeholder="Ingresa tu usuario" required>
                    </div>
                    <div class="auth-field">
                        <label for="contrasena">Contrasena</label>
                        <input id="contrasena" type="password" name="contrasena" placeholder="Ingresa tu contrasena" required>
                    </div>
                    <button class="auth-submit" type="submit">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Entrar al sistema</span>
                    </button>
                </form>

                <p class="auth-card__footer">
                    Aun no tienes cuenta.
                    <a href="<?php echo $base_url; ?>RegistrarUsuario.php">Registrate aqui</a>
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
