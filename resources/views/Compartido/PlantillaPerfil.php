<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
require_once dirname(__DIR__, 3) . '/app/Repositories/UsuarioDAO.php';
require_once __DIR__ . '/config/storage.php';
require_once __DIR__ . '/config/app.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /IniciarSesion.php');
    exit();
}

$base_url = app_base_url();
$usuarioSesion = $_SESSION['usuario'];
$usuarioId = $usuarioSesion->getId();
$rol = $usuarioSesion->getRol();

$usuarioDAO = new UsuarioDAO();
$usuario = $usuarioDAO->obtenerPorId($usuarioId);

$profilePageTitle = $profilePageTitle ?? 'Mi perfil';
$profileBackUrl = $profileBackUrl ?? ($base_url . 'index.php');
$profileBackLabel = $profileBackLabel ?? 'Volver';
$profileHomeUrl = $profileHomeUrl ?? $profileBackUrl;
$profileNavItems = $profileNavItems ?? [];

$rolLabels = [
    'cliente' => 'Cliente',
    'abogado' => 'Abogado',
    'administrador' => 'Administrador',
];

$rolLabel = $rolLabels[$rol] ?? ucfirst($rol);
$fullName = trim($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno() . ' ' . $usuario->getApellidoMaterno());
$flashMessage = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profilePageTitle); ?> - Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
</head>
<body class="profile-page">
    <header class="profile-header">
        <div class="profile-shell profile-header__inner">
            <a href="<?php echo htmlspecialchars($profileHomeUrl); ?>" class="profile-brand">
                <img src="<?php echo $base_url; ?>assets/img/Ortiz_y_Asociados.png" alt="Ortiz y Asociados" class="profile-brand__logo">
            </a>

            <?php if (!empty($profileNavItems)) : ?>
                <nav class="profile-nav" aria-label="Navegacion de perfil">
                    <?php foreach ($profileNavItems as $item) : ?>
                        <a
                            href="<?php echo htmlspecialchars($item['href']); ?>"
                            class="profile-nav__link<?php echo !empty($item['active']) ? ' is-active' : ''; ?>">
                            <?php echo htmlspecialchars($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <div class="profile-header__actions">
                <?php if (!empty($profileBackUrl)) : ?>
                    <a href="<?php echo htmlspecialchars($profileBackUrl); ?>" class="profile-header__button profile-header__button--ghost">
                        <i class="bi bi-arrow-left"></i>
                        <span><?php echo htmlspecialchars($profileBackLabel); ?></span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo $base_url; ?>CerrarSesion.php" class="profile-header__button profile-header__button--dark">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar sesion</span>
                </a>
            </div>
        </div>
    </header>

    <main class="profile-main">
        <div class="profile-shell">
            <?php if ($flashMessage) : ?>
                <div class="profile-alert" role="status">
                    <i class="bi bi-info-circle"></i>
                    <span><?php echo htmlspecialchars($flashMessage); ?></span>
                </div>
            <?php endif; ?>

            <section class="profile-layout">
                <aside class="profile-card profile-card--sidebar">
                    <div class="profile-avatar-wrap">
                        <img
                            src="<?php echo storage_image_url($usuario->getImagen(), $base_url); ?>"
                            alt="Imagen de perfil"
                            class="profile-avatar">
                        <span class="profile-role-chip"><?php echo htmlspecialchars($rolLabel); ?></span>
                    </div>

                    <div class="profile-identity">
                        <h2><?php echo htmlspecialchars($fullName); ?></h2>
                        <p><?php echo htmlspecialchars($rolLabel); ?></p>
                    </div>

                    <div class="profile-upload">
                        <form action="<?php echo $base_url; ?>Controladores/ControladorPerfil.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                            <input type="file" name="imagen" id="imagen" class="profile-upload__input" onchange="this.form.submit()">
                            <input type="hidden" name="accion" value="actualizar_imagen">
                            <button type="button" class="profile-action-button profile-action-button--primary" onclick="document.getElementById('imagen').click();">
                                <i class="bi bi-image"></i>
                                <span>Actualizar imagen</span>
                            </button>
                        </form>

                        <form action="<?php echo $base_url; ?>Controladores/ControladorPerfil.php" method="POST">
                            <input type="hidden" name="accion" value="eliminar_imagen">
                            <button type="submit" class="profile-action-button profile-action-button--muted">
                                <i class="bi bi-trash3"></i>
                                <span>Quitar imagen</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <section class="profile-card profile-card--content">
                    <div class="profile-card__head">
                        <div>
                            <span class="profile-card__eyebrow">Resumen personal</span>
                            <h2><?php echo htmlspecialchars($profilePageTitle); ?></h2>
                        </div>
                        <?php if ($rol === 'abogado' || $rol === 'cliente') : ?>
                            <button class="profile-edit-button" data-bs-toggle="modal" data-bs-target="#editPerfilModal" type="button">
                                <i class="bi bi-pencil-square"></i>
                                <span>Editar perfil</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="profile-details">
                        <article class="profile-detail">
                            <span>Nombre completo</span>
                            <strong><?php echo htmlspecialchars($fullName); ?></strong>
                        </article>
                        <article class="profile-detail">
                            <span>Correo</span>
                            <strong><?php echo htmlspecialchars($usuario->getCorreo()); ?></strong>
                        </article>
                        <article class="profile-detail">
                            <span>Telefono</span>
                            <strong><?php echo htmlspecialchars($usuario->getTelefono() ?: 'No registrado'); ?></strong>
                        </article>
                        <article class="profile-detail">
                            <span>Usuario</span>
                            <strong><?php echo htmlspecialchars($usuario->getUsuario()); ?></strong>
                        </article>
                        <article class="profile-detail">
                            <span>Rol</span>
                            <strong><?php echo htmlspecialchars($rolLabel); ?></strong>
                        </article>
                        <article class="profile-detail">
                            <span>Contrasena</span>
                            <strong>********</strong>
                        </article>
                    </div>
                </section>
            </section>
        </div>
    </main>

    <footer class="profile-footer">
        <div class="profile-shell profile-footer__inner">
            <div>
                <strong>Ortiz y Asociados</strong>
                <p>Panel de perfil integrado al sistema del estudio juridico.</p>
            </div>
        </div>
    </footer>

    <div class="modal fade" id="editPerfilModal" tabindex="-1" aria-labelledby="editPerfilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content profile-modal">
                <div class="modal-header profile-modal__header">
                    <div>
                        <span class="profile-card__eyebrow">Actualizacion de datos</span>
                        <h5 class="modal-title" id="editPerfilModalLabel">Editar perfil</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="<?php echo $base_url; ?>Controladores/ControladorPerfil.php" method="POST" class="profile-form">
                        <div class="profile-form__grid">
                            <div class="profile-form__field">
                                <label for="editNombre" class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" id="editNombre" value="<?php echo htmlspecialchars($usuario->getNombre()); ?>" required>
                            </div>
                            <div class="profile-form__field">
                                <label for="editApellidoPaterno" class="form-label">Apellido paterno</label>
                                <input type="text" name="apellido_paterno" class="form-control" id="editApellidoPaterno" value="<?php echo htmlspecialchars($usuario->getApellidoPaterno()); ?>" required>
                            </div>
                            <div class="profile-form__field">
                                <label for="editApellidoMaterno" class="form-label">Apellido materno</label>
                                <input type="text" name="apellido_materno" class="form-control" id="editApellidoMaterno" value="<?php echo htmlspecialchars($usuario->getApellidoMaterno()); ?>">
                            </div>
                            <div class="profile-form__field">
                                <label for="editCorreo" class="form-label">Correo</label>
                                <input type="email" name="correo" class="form-control" id="editCorreo" value="<?php echo htmlspecialchars($usuario->getCorreo()); ?>" required>
                            </div>
                            <div class="profile-form__field">
                                <label for="editTelefono" class="form-label">Telefono</label>
                                <input type="text" name="telefono" class="form-control" id="editTelefono" value="<?php echo htmlspecialchars($usuario->getTelefono()); ?>">
                            </div>
                            <div class="profile-form__field">
                                <label for="editUsuario" class="form-label">Usuario</label>
                                <input type="text" name="usuario" class="form-control" id="editUsuario" value="<?php echo htmlspecialchars($usuario->getUsuario()); ?>" required>
                            </div>
                        </div>

                        <div class="profile-form__field">
                            <label for="editContrasena" class="form-label">Nueva contrasena</label>
                            <input type="password" name="contrasena" class="form-control" id="editContrasena" placeholder="Solo si deseas cambiarla">
                        </div>

                        <input type="hidden" name="accion" value="actualizar_perfil">

                        <div class="profile-form__actions">
                            <button type="button" class="profile-action-button profile-action-button--muted" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="profile-action-button profile-action-button--primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
