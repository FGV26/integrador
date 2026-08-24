<?php
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
require_once dirname(__DIR__, 3) . '/app/Repositories/UsuarioDAO.php';
require_once __DIR__ . '/config/app.php';

session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] ?? '') !== 'cliente') {
    header('Location: /IniciarSesion.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$usuario = $usuarioDAO->obtenerPorId((int) $_SESSION['usuario']->getId());
if (!$usuario || !$usuario->getPasswordChangeRequired()) {
    header('Location: /index.php');
    exit();
}

$base_url = app_base_url();
$errores = [
    'longitud' => 'La nueva contrasena debe tener al menos 6 caracteres.',
    'confirmacion' => 'Las contrasenas no coinciden.',
    'actualizar' => 'No se pudo actualizar la contrasena. Intentalo nuevamente.',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar contrasena</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/auth.css?v=<?php echo time(); ?>">
</head>
<body class="auth-page">
    <main class="auth-main">
        <div class="auth-shell auth-layout auth-layout--single">
            <section class="auth-card auth-card--password">
                <div class="auth-card__header">
                    <span class="auth-eyebrow">Cuenta por reactivar</span>
                    <h2>Actualiza tu contrasena</h2>
                    <p>Por seguridad, tu cuenta necesita una nueva contrasena para volver a estar activa en el portal.</p>
                </div>

                <?php if (isset($_GET['error'], $errores[$_GET['error']])) : ?>
                    <div class="auth-message auth-message--error"><?php echo htmlspecialchars($errores[$_GET['error']], ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="post" action="<?php echo $base_url; ?>Controladores/ControladorCambioContrasena.php" class="auth-form">
                    <div class="auth-field">
                        <label for="nueva_contrasena">Nueva contrasena</label>
                        <input id="nueva_contrasena" type="password" name="nueva_contrasena" placeholder="Crea una nueva contrasena" required>
                    </div>
                    <div class="auth-field">
                        <label for="confirmar_contrasena">Confirmar contrasena</label>
                        <input id="confirmar_contrasena" type="password" name="confirmar_contrasena" placeholder="Repite la nueva contrasena" required>
                    </div>
                    <button class="auth-submit" type="submit">
                        <i class="bi bi-shield-check"></i>
                        <span>Actualizar y reactivar</span>
                    </button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
