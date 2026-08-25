<?php
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /IniciarSesion.php');
    exit();
}

require_once dirname(__DIR__, 3) . '/app/Repositories/UsuarioDAO.php';
require_once dirname(__DIR__, 3) . '/config/app.php';
require_once dirname(__DIR__, 3) . '/config/storage.php';
require_once __DIR__ . '/config/layout.php';

function admin_h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$base_url = app_base_url();
$usuarioDAO = new UsuarioDAO();
$credencialesRegistradas = $usuarioDAO->obtenerCredencialesRegistradas();
$esGestionClientes = $gestionRol === 'cliente';
$clientesActividad = $esGestionClientes ? $usuarioDAO->obtenerClientesConActividad() : [];
$usuarios = $esGestionClientes ? array_map(static fn ($item) => $item['usuario'], $clientesActividad) : $usuarioDAO->obtenerPorRol($gestionRol);
if (!$esGestionClientes) {
    usort($usuarios, static function ($a, $b): int {
        $nombreA = trim($a->getApellidoPaterno() . ' ' . $a->getApellidoMaterno() . ' ' . $a->getNombre());
        $nombreB = trim($b->getApellidoPaterno() . ' ' . $b->getApellidoMaterno() . ' ' . $b->getNombre());
        return strcasecmp($nombreA, $nombreB);
    });
}
$totalUsuarios = count($usuarios);
$rolLabel = $gestionRol === 'administrador' ? 'Administrador' : ($gestionRol === 'abogado' ? 'Abogado' : 'Cliente');
$registroPlural = $gestionRol === 'administrador' ? 'administradores' : ($gestionRol === 'abogado' ? 'abogados' : 'clientes');
$usuariosJson = array_map(static function ($item) use ($base_url, $rolLabel, $esGestionClientes): array {
    $usuario = $esGestionClientes ? $item['usuario'] : $item;
    $ciclo = $esGestionClientes ? $item['ciclo'] : [
        'estado' => $usuario->getIsActive() ? 'activo' : 'bloqueado',
        'label' => $usuario->getIsActive() ? 'Activo' : 'Inactivo',
        'descripcion' => $usuario->getIsActive() ? 'Cuenta habilitada' : 'Cuenta inactiva',
        'requiere_cambio' => false,
        'puede_eliminar' => false,
    ];
    $nombreCompleto = trim($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno() . ' ' . $usuario->getApellidoMaterno());
    return [
        'id' => (int) $usuario->getId(),
        'nombre' => $usuario->getNombre(),
        'apellidoPaterno' => $usuario->getApellidoPaterno(),
        'apellidoMaterno' => $usuario->getApellidoMaterno(),
        'nombreCompleto' => $nombreCompleto,
        'correo' => $usuario->getCorreo(),
        'telefono' => $usuario->getTelefono(),
        'telefonoDisplay' => $usuario->getTelefono() ?: 'Sin telefono',
        'usuario' => $usuario->getUsuario(),
        'imagen' => $usuario->getImagen(),
        'imagenUrl' => storage_image_url($usuario->getImagen(), $base_url),
        'rolLabel' => $rolLabel,
        'activo' => $usuario->getIsActive(),
        'cicloEstado' => $ciclo['estado'],
        'cicloLabel' => $ciclo['label'],
        'cicloDescripcion' => $ciclo['descripcion'],
        'requiereCambio' => (bool) $ciclo['requiere_cambio'],
        'puedeEliminarInactividad' => (bool) $ciclo['puede_eliminar'],
        'totalCitas' => $esGestionClientes ? (int) $item['total_citas'] : null,
        'ultimaCita' => $esGestionClientes ? ($item['ultima_cita'] ?? null) : null,
        'ultimoLogin' => $esGestionClientes ? ($item['ultimo_login'] ?? null) : null,
        'creadoEn' => $esGestionClientes ? ($item['creado_en'] ?? null) : null,
    ];
}, $esGestionClientes ? $clientesActividad : $usuarios);

$successMessages = [
    'agregar' => $rolLabel . ' registrado correctamente.',
    'editar' => 'Datos actualizados correctamente.',
    'eliminar' => 'Cuenta eliminada correctamente.',
    'estado' => 'Estado de la cuenta actualizado correctamente.',
    'forzar_cambio' => 'El cliente debera cambiar su contrasena al iniciar sesion.',
    'eliminar_inactividad' => 'Cliente eliminado por inactividad. Si vuelve, debera registrarse nuevamente.',
];
$errorMessages = [
    'agregar' => 'No se pudo registrar la cuenta.',
    'editar' => 'No se pudieron actualizar los datos.',
    'eliminar' => 'No se pudo eliminar la cuenta.',
    'estado' => 'No se pudo cambiar el estado de la cuenta.',
    'contrasena' => 'La contrasena de validacion es incorrecta. No se realizo ningun cambio.',
    'propia_cuenta' => 'No puedes desactivar ni eliminar tu propia cuenta administrativa.',
    'usuario_no_valido' => 'La cuenta seleccionada no es valida para esta gestion.',
    'cliente_no_depurable' => 'Este cliente aun no cumple las condiciones para ser eliminado por inactividad.',
    'validacion' => 'Revisa los datos ingresados. Hay campos con formato incorrecto o duplicados.',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo admin_h($gestionTitulo); ?> - Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo admin_h($base_url); ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?php echo admin_h($base_url); ?>assets/css/administrador-dashboard.css?v=<?php echo time(); ?>">
</head>
<body class="admin-page">
<?php render_administrador_header($base_url, $gestionActivePage); ?>
<main class="admin-main">
    <div class="admin-shell">
        <section class="admin-page-heading">
            <div>
                <p class="admin-eyebrow">Directorio del sistema</p>
                <h1 class="admin-title"><?php echo admin_h($gestionTitulo); ?></h1>
                <p class="admin-description"><?php echo admin_h($gestionDescripcion); ?></p>
            </div>
            <?php if ($gestionPermiteAgregar) : ?>
                <button class="admin-button admin-button--gold" type="button" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="bi bi-person-plus"></i><span>Agregar <?php echo admin_h(strtolower($rolLabel)); ?></span>
                </button>
            <?php endif; ?>
        </section>

        <?php if (isset($_GET['success'], $successMessages[$_GET['success']])) : ?>
            <div class="admin-alert admin-alert--success" role="status">
                <i class="bi bi-check-circle"></i>
                <span><?php echo admin_h($successMessages[$_GET['success']]); ?></span>
                <button class="admin-alert__close" type="button" aria-label="Cerrar aviso"><i class="bi bi-x-lg"></i></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'], $errorMessages[$_GET['error']])) : ?>
            <div class="admin-alert admin-alert--danger" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <span><?php echo admin_h($errorMessages[$_GET['error']]); ?></span>
                <button class="admin-alert__close" type="button" aria-label="Cerrar aviso"><i class="bi bi-x-lg"></i></button>
            </div>
        <?php endif; ?>

        <div class="admin-toolbar admin-toolbar--live">
            <div class="admin-search">
                <label for="buscarUsuario">Buscar por usuario</label>
                <div class="admin-search__row">
                    <input class="admin-input" id="buscarUsuario" type="search" autocomplete="off" placeholder="Ejemplo: fgv">
                </div>
            </div>
            <div class="admin-toolbar__side">
                <p class="admin-toolbar__summary">
                    <span id="usuariosResultadoConteo"><?php echo $totalUsuarios; ?></span>
                    <span id="usuariosResultadoTipo"><?php echo admin_h($registroPlural); ?></span>
                </p>
                <div class="admin-filter-popover" id="estadoFilterPopover">
                    <button class="admin-filter-toggle" id="estadoFilterToggle" type="button" aria-expanded="false" aria-controls="estadoFilterMenu">
                        <i class="bi bi-funnel"></i>
                        <span>Filtros</span>
                        <strong id="estadoFilterCount">Todos</strong>
                    </button>
                    <div class="admin-filter-menu" id="estadoFilterMenu" aria-label="Filtrar por estado">
                        <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="todos" checked><span>Todos</span></label>
                        <?php if ($esGestionClientes) : ?>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="activo"><span>Activos</span></label>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="sin_uso"><span>Sin uso</span></label>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="dormido"><span>Dormidos</span></label>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="historico"><span>Historicos</span></label>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="depurable"><span>Depurables</span></label>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="bloqueado"><span>Bloqueados</span></label>
                        <?php else : ?>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="activo"><span>Activos</span></label>
                            <label class="admin-filter-check"><input class="admin-status-checkbox" type="checkbox" value="inactivo"><span>No activos</span></label>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <section class="admin-table-panel">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Persona</th><th>Contacto</th><th>Usuario</th><?php if ($esGestionClientes) : ?><th>Actividad</th><?php endif; ?><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody id="usuariosTableBody">
                    <?php foreach ($usuarios as $usuario) :
                        $nombreCompleto = trim($usuario->getNombre() . ' ' . $usuario->getApellidoPaterno() . ' ' . $usuario->getApellidoMaterno());
                    ?>
                        <tr>
                            <td>
                                <div class="admin-person">
                                    <img class="admin-person__avatar" src="<?php echo admin_h(storage_image_url($usuario->getImagen(), $base_url)); ?>" alt="">
                                    <div><strong><?php echo admin_h($nombreCompleto); ?></strong></div>
                                </div>
                            </td>
                            <td><strong><?php echo admin_h($usuario->getCorreo()); ?></strong><br><span class="admin-cell-muted"><?php echo admin_h($usuario->getTelefono() ?: 'Sin telefono'); ?></span></td>
                            <td><?php echo admin_h($usuario->getUsuario()); ?></td>
                            <?php if ($esGestionClientes) : ?>
                                <td class="admin-cell-muted">Calculando...</td>
                            <?php endif; ?>
                            <td class="admin-cell-status"><span class="admin-status <?php echo $usuario->getIsActive() ? 'admin-status--active' : 'admin-status--inactive'; ?>"><?php echo $usuario->getIsActive() ? 'Activo' : 'Inactivo'; ?></span></td>
                            <td class="admin-cell-actions">
                                <div class="admin-table__actions">
                                    <button class="admin-button admin-button--outline admin-button--small js-edit" type="button" data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="<?php echo (int) $usuario->getId(); ?>" data-nombre="<?php echo admin_h($usuario->getNombre()); ?>" data-apellido-paterno="<?php echo admin_h($usuario->getApellidoPaterno()); ?>" data-apellido-materno="<?php echo admin_h($usuario->getApellidoMaterno()); ?>" data-correo="<?php echo admin_h($usuario->getCorreo()); ?>" data-telefono="<?php echo admin_h($usuario->getTelefono()); ?>" data-usuario="<?php echo admin_h($usuario->getUsuario()); ?>" data-imagen="<?php echo admin_h($usuario->getImagen()); ?>">
                                        <i class="bi bi-pencil"></i><span>Editar</span>
                                    </button>
                                    <button
                                        class="admin-button admin-button--small js-toggle-status <?php echo $usuario->getIsActive() ? 'admin-button--danger' : 'admin-button--gold'; ?>"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEstado"
                                        data-id="<?php echo (int) $usuario->getId(); ?>"
                                        data-nombre="<?php echo admin_h($nombreCompleto); ?>"
                                        data-estado="<?php echo $usuario->getIsActive() ? 0 : 1; ?>"
                                        data-accion="<?php echo $usuario->getIsActive() ? 'desactivar' : 'activar'; ?>"
                                        title="<?php echo $usuario->getIsActive() ? 'Desactivar cuenta' : 'Activar cuenta'; ?>">
                                        <i class="bi <?php echo $usuario->getIsActive() ? 'bi-person-dash' : 'bi-person-check'; ?>"></i>
                                        <span><?php echo $usuario->getIsActive() ? 'Desactivar' : 'Activar'; ?></span>
                                    </button>
                                    <?php if ($gestionPermiteEliminar) : ?>
                                        <button class="admin-button admin-button--danger admin-button--small js-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalEliminar" data-id="<?php echo (int) $usuario->getId(); ?>" data-nombre="<?php echo admin_h($nombreCompleto); ?>" title="Eliminar cuenta"><i class="bi bi-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$usuarios) : ?>
                        <tr><td colspan="<?php echo $esGestionClientes ? 6 : 5; ?>"><div class="admin-empty"><i class="bi bi-search"></i><p>No se encontraron registros.</p></div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav class="admin-pagination" id="usuariosPagination" aria-label="Paginacion de usuarios"></nav>
        </section>
    </div>
</main>

<?php if ($gestionPermiteAgregar) : ?>
<div class="modal fade admin-modal" id="modalAgregar" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <form class="js-admin-account-form" action="<?php echo admin_h($base_url . $gestionEndpoint); ?>" method="post" enctype="multipart/form-data" novalidate>
        <div class="modal-header"><h2 class="modal-title fs-5">Agregar <?php echo admin_h(strtolower($rolLabel)); ?></h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body"><div class="admin-form-grid">
            <div class="admin-form-field"><label>Nombre</label><input class="admin-input" name="nombre" maxlength="40" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Apellido paterno</label><input class="admin-input" name="apellido_paterno" maxlength="40" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Apellido materno</label><input class="admin-input" name="apellido_materno" maxlength="40"><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Telefono</label><div class="admin-phone-field"><span>+51</span><input class="admin-input" name="telefono" inputmode="numeric" maxlength="9" pattern="\d{9}" required></div><small class="admin-field-error"></small></div>
            <div class="admin-form-field admin-form-field--wide"><label>Correo</label><input class="admin-input" type="email" name="correo" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Usuario</label><input class="admin-input" name="usuario" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Contrasena</label><input class="admin-input" type="password" name="contrasena" minlength="8" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field admin-form-field--wide"><label>Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div>
        </div></div>
        <div class="modal-footer"><button class="admin-button admin-button--outline" type="button" data-bs-dismiss="modal">Cancelar</button><button class="admin-button admin-button--gold" type="submit">Guardar cuenta</button></div>
        <input type="hidden" name="accion" value="agregar">
    </form>
</div></div></div>
<?php endif; ?>

<div class="modal fade admin-modal" id="modalEditar" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <form class="js-admin-account-form" action="<?php echo admin_h($base_url . $gestionEndpoint); ?>" method="post" enctype="multipart/form-data" novalidate>
        <div class="modal-header"><h2 class="modal-title fs-5">Editar <?php echo admin_h(strtolower($rolLabel)); ?></h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body"><div class="admin-form-grid">
            <div class="admin-form-field"><label>Nombre</label><input class="admin-input" id="editNombre" name="nombre" maxlength="40" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Apellido paterno</label><input class="admin-input" id="editApellidoPaterno" name="apellido_paterno" maxlength="40" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Apellido materno</label><input class="admin-input" id="editApellidoMaterno" name="apellido_materno" maxlength="40"><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Telefono</label><div class="admin-phone-field"><span>+51</span><input class="admin-input" id="editTelefono" name="telefono" inputmode="numeric" maxlength="9" pattern="\d{9}" required></div><small class="admin-field-error"></small></div>
            <div class="admin-form-field admin-form-field--wide"><label>Correo</label><input class="admin-input" id="editCorreo" type="email" name="correo" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Usuario</label><input class="admin-input" id="editUsuario" name="usuario" required><small class="admin-field-error"></small></div>
            <div class="admin-form-field"><label>Nueva contrasena</label><input class="admin-input" type="password" name="contrasena" minlength="8" placeholder="Dejar vacio para conservarla"><small class="admin-field-error"></small></div>
            <?php if ($gestionRol !== 'cliente') : ?><div class="admin-form-field admin-form-field--wide"><label>Nueva imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div><?php endif; ?>
        </div></div>
        <div class="modal-footer"><button class="admin-button admin-button--outline" type="button" data-bs-dismiss="modal">Cancelar</button><button class="admin-button admin-button--gold" type="submit">Guardar cambios</button></div>
        <input type="hidden" name="id" id="editId"><input type="hidden" name="imagen_actual" id="editImagenActual"><input type="hidden" name="accion" value="editar">
    </form>
</div></div></div>

<div class="modal fade admin-modal" id="modalEstado" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form action="<?php echo admin_h($base_url); ?>Controladores/ControladorEstadoUsuario.php" method="post" autocomplete="off">
        <div class="modal-header"><h2 class="modal-title fs-5" id="estadoModalTitulo">Confirmar cambio de estado</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <div class="admin-security-summary">
                <div class="admin-security-summary__icon"><i class="bi bi-shield-lock"></i></div>
                <div><strong id="estadoModalPregunta"></strong><p id="estadoModalDetalle"></p></div>
            </div>
            <div class="admin-form-field admin-form-field--wide">
                <label for="estadoContrasena">Contrasena del administrador</label>
                <div class="admin-password-field">
                    <input class="admin-input" id="estadoContrasena" type="password" name="contrasena_confirmacion" placeholder="Ingresa tu contrasena para validar" required autocomplete="current-password">
                    <button type="button" class="admin-password-toggle" id="toggleEstadoContrasena" title="Mostrar u ocultar contrasena"><i class="bi bi-eye"></i></button>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button class="admin-button admin-button--outline" type="button" data-bs-dismiss="modal">Cancelar</button><button class="admin-button admin-button--gold" id="estadoSubmit" type="submit"><i class="bi bi-shield-check"></i><span>Validar y continuar</span></button></div>
        <input type="hidden" name="id" id="estadoId"><input type="hidden" name="rol" value="<?php echo admin_h($gestionRol); ?>"><input type="hidden" name="is_active" id="estadoValor">
    </form>
</div></div></div>

<?php if ($esGestionClientes) : ?>
<div class="modal fade admin-modal" id="modalClienteCiclo" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form action="<?php echo admin_h($base_url . $gestionEndpoint); ?>" method="post" autocomplete="off">
        <div class="modal-header"><h2 class="modal-title fs-5" id="clienteCicloTitulo">Confirmar accion</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <div class="admin-security-summary">
                <div class="admin-security-summary__icon"><i class="bi bi-shield-lock"></i></div>
                <div><strong id="clienteCicloNombre"></strong><p id="clienteCicloDescripcion"></p></div>
            </div>
            <div class="admin-form-field admin-form-field--wide">
                <label for="clienteCicloContrasena">Contrasena del administrador</label>
                <div class="admin-password-field">
                    <input class="admin-input" id="clienteCicloContrasena" type="password" name="contrasena_confirmacion" placeholder="Ingresa tu contrasena para validar" required autocomplete="current-password">
                    <button type="button" class="admin-password-toggle" id="toggleClienteCicloContrasena" title="Mostrar u ocultar contrasena"><i class="bi bi-eye"></i></button>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button class="admin-button admin-button--outline" type="button" data-bs-dismiss="modal">Cancelar</button><button class="admin-button admin-button--gold" id="clienteCicloSubmit" type="submit"><i class="bi bi-shield-check"></i><span>Validar y continuar</span></button></div>
        <input type="hidden" name="id" id="clienteCicloId"><input type="hidden" name="accion" id="clienteCicloAccion">
    </form>
</div></div></div>
<?php endif; ?>

<?php if ($gestionPermiteEliminar) : ?>
<div class="modal fade admin-modal" id="modalEliminar" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form action="<?php echo admin_h($base_url . $gestionEndpoint); ?>" method="post">
        <div class="modal-header"><h2 class="modal-title fs-5">Eliminar cuenta</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body"><p>Esta accion eliminara permanentemente a <strong id="deleteNombre"></strong>. Para conservar su historial es preferible desactivar la cuenta.</p></div>
        <div class="modal-footer"><button class="admin-button admin-button--outline" type="button" data-bs-dismiss="modal">Cancelar</button><button class="admin-button admin-button--danger" type="submit">Eliminar definitivamente</button></div>
        <input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" id="deleteId">
    </form>
</div></div></div>
<?php endif; ?>

<?php render_administrador_footer(); ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const usuariosDirectorio = <?php echo json_encode($usuariosJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const credencialesRegistradas = <?php echo json_encode($credencialesRegistradas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const permiteEliminar = <?php echo $gestionPermiteEliminar ? 'true' : 'false'; ?>;
const esGestionClientes = <?php echo $esGestionClientes ? 'true' : 'false'; ?>;
const validarGestionCuenta = !esGestionClientes;
let usuariosFiltrados = [...usuariosDirectorio];
let paginaUsuarios = 1;
const usuariosPorPagina = 25;

const escaparHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
}[char]));

const crearFilaUsuario = (usuario) => {
    const estadoTexto = esGestionClientes ? usuario.cicloLabel : (usuario.activo ? 'Activo' : 'Inactivo');
    const estadoClase = esGestionClientes ? `admin-status--${usuario.cicloEstado}` : (usuario.activo ? 'admin-status--active' : 'admin-status--inactive');
    const accionTexto = esGestionClientes
        ? (usuario.activo ? 'Bloquear' : 'Restaurar')
        : (usuario.activo ? 'Desactivar' : 'Activar');
    const accionClase = usuario.activo ? 'admin-button--danger' : 'admin-button--gold';
    const accionIcono = usuario.activo ? 'bi-person-dash' : 'bi-person-check';
    const accionValor = usuario.activo ? 0 : 1;
    const accionNombre = usuario.activo ? 'desactivar' : 'activar';
    const actividad = esGestionClientes ? `
        <td>
            <div class="admin-client-activity">
                <strong>${usuario.totalCitas} cita${usuario.totalCitas === 1 ? '' : 's'}</strong>
                <span>Ultima cita: ${escaparHtml(formatearFechaCorta(usuario.ultimaCita))}</span>
                <span>Ultimo acceso: ${escaparHtml(formatearFechaCorta(usuario.ultimoLogin))}</span>
            </div>
        </td>` : '';
    const forzarCambio = esGestionClientes && usuario.requiereCambio ? `
        <button class="admin-button admin-button--outline admin-button--small js-client-action" type="button" data-bs-toggle="modal" data-bs-target="#modalClienteCiclo"
            data-id="${usuario.id}" data-nombre="${escaparHtml(usuario.nombreCompleto)}" data-accion="forzar_cambio" data-titulo="Forzar cambio de contrasena" data-descripcion="El cliente debera crear una nueva contrasena para volver a estado activo.">
            <i class="bi bi-key"></i><span>Forzar cambio</span>
        </button>` : '';
    const eliminarInactividad = esGestionClientes && usuario.puedeEliminarInactividad ? `
        <button class="admin-button admin-button--danger admin-button--small js-client-action" type="button" data-bs-toggle="modal" data-bs-target="#modalClienteCiclo"
            data-id="${usuario.id}" data-nombre="${escaparHtml(usuario.nombreCompleto)}" data-accion="eliminar_inactividad" data-titulo="Eliminar por inactividad" data-descripcion="Se eliminara la cuenta y se guardara una huella minima para avisar si intenta volver a ingresar.">
            <i class="bi bi-archive"></i><span>Eliminar por inactividad</span>
        </button>` : '';
    const eliminar = permiteEliminar ? `
        <button class="admin-button admin-button--danger admin-button--small js-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalEliminar" data-id="${usuario.id}" data-nombre="${escaparHtml(usuario.nombreCompleto)}" title="Eliminar cuenta">
            <i class="bi bi-trash"></i>
        </button>` : '';

    return `
        <tr>
            <td>
                <div class="admin-person">
                    <img class="admin-person__avatar" src="${escaparHtml(usuario.imagenUrl)}" alt="">
                    <div><strong>${escaparHtml(usuario.nombreCompleto)}</strong></div>
                </div>
            </td>
            <td><strong>${escaparHtml(usuario.correo)}</strong><br><span class="admin-cell-muted">${escaparHtml(usuario.telefonoDisplay)}</span></td>
            <td>${escaparHtml(usuario.usuario)}</td>
            ${actividad}
            <td class="admin-cell-status"><span class="admin-status ${estadoClase}">${escaparHtml(estadoTexto)}</span></td>
            <td class="admin-cell-actions">
                <div class="admin-table__actions">
                    <button class="admin-button admin-button--outline admin-button--small js-edit" type="button" data-bs-toggle="modal" data-bs-target="#modalEditar"
                        data-id="${usuario.id}" data-nombre="${escaparHtml(usuario.nombre)}" data-apellido-paterno="${escaparHtml(usuario.apellidoPaterno)}" data-apellido-materno="${escaparHtml(usuario.apellidoMaterno)}" data-correo="${escaparHtml(usuario.correo)}" data-telefono="${escaparHtml(usuario.telefono)}" data-usuario="${escaparHtml(usuario.usuario)}" data-imagen="${escaparHtml(usuario.imagen)}">
                        <i class="bi bi-pencil"></i><span>Editar</span>
                    </button>
                    <button class="admin-button admin-button--small js-toggle-status ${accionClase}" type="button" data-bs-toggle="modal" data-bs-target="#modalEstado"
                        data-id="${usuario.id}" data-nombre="${escaparHtml(usuario.nombreCompleto)}" data-estado="${accionValor}" data-accion="${accionNombre}" title="${escaparHtml(accionTexto)} cuenta">
                        <i class="bi ${accionIcono}"></i><span>${accionTexto}</span>
                    </button>
                    ${forzarCambio}
                    ${eliminarInactividad}
                    ${eliminar}
                </div>
            </td>
        </tr>`;
};

const formatearFechaCorta = (fecha) => {
    if (!fecha) {
        return 'Sin registro';
    }
    const partes = String(fecha).slice(0, 10).split('-');
    if (partes.length !== 3) {
        return fecha;
    }
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
};

const actualizarResumenUsuarios = () => {
    const total = usuariosFiltrados.length;
    $('#usuariosResultadoConteo').text(total);
};

const renderizarPaginacionUsuarios = () => {
    const totalPaginas = Math.max(1, Math.ceil(usuariosFiltrados.length / usuariosPorPagina));
    paginaUsuarios = Math.min(paginaUsuarios, totalPaginas);
    const desde = Math.max(1, paginaUsuarios - 2);
    const hasta = Math.min(totalPaginas, paginaUsuarios + 2);
    const botones = [];

    if (totalPaginas <= 1) {
        $('#usuariosPagination').empty();
        return;
    }

    botones.push(`<button type="button" class="admin-pagination__button ${paginaUsuarios <= 1 ? 'is-disabled' : ''}" data-page="${paginaUsuarios - 1}" aria-label="Pagina anterior"><i class="bi bi-chevron-left"></i></button>`);
    for (let i = desde; i <= hasta; i += 1) {
        botones.push(`<button type="button" class="admin-pagination__button ${i === paginaUsuarios ? 'is-active' : ''}" data-page="${i}">${i}</button>`);
    }
    botones.push(`<button type="button" class="admin-pagination__button ${paginaUsuarios >= totalPaginas ? 'is-disabled' : ''}" data-page="${paginaUsuarios + 1}" aria-label="Pagina siguiente"><i class="bi bi-chevron-right"></i></button>`);
    $('#usuariosPagination').html(botones.join(''));
};

const renderizarUsuarios = () => {
    const inicio = (paginaUsuarios - 1) * usuariosPorPagina;
    const usuariosPagina = usuariosFiltrados.slice(inicio, inicio + usuariosPorPagina);
    const contenido = usuariosPagina.length
        ? usuariosPagina.map(crearFilaUsuario).join('')
        : `<tr><td colspan="${esGestionClientes ? 6 : 5}"><div class="admin-empty"><i class="bi bi-search"></i><p>No se encontraron usuarios con esos filtros.</p></div></td></tr>`;
    $('#usuariosTableBody').html(contenido);
    actualizarResumenUsuarios();
    renderizarPaginacionUsuarios();
};

const cerrarAdminAlert = (alerta) => {
    if (!alerta || alerta.classList.contains('is-closing')) {
        return;
    }
    alerta.classList.add('is-closing');
    window.setTimeout(() => alerta.remove(), 220);
};

document.querySelectorAll('.admin-alert').forEach((alerta) => {
    alerta.querySelector('.admin-alert__close')?.addEventListener('click', () => cerrarAdminAlert(alerta));
    window.setTimeout(() => cerrarAdminAlert(alerta), 3000);
});

const aplicarFiltrosUsuarios = () => {
    const texto = $('#buscarUsuario').val().trim().toLowerCase();
    const estados = $('.admin-status-checkbox:checked').map(function () {
        return this.value;
    }).get();
    usuariosFiltrados = usuariosDirectorio.filter((usuario) => {
        const coincideUsuario = !texto || usuario.usuario.toLowerCase().includes(texto);
        const coincideEstado = esGestionClientes
            ? (estados.includes('todos') || estados.includes(usuario.cicloEstado))
            : (estados.includes('todos') || (estados.includes('activo') && usuario.activo) || (estados.includes('inactivo') && !usuario.activo));
        return coincideUsuario && coincideEstado;
    });
    paginaUsuarios = 1;
    renderizarUsuarios();
};

const actualizarEtiquetaFiltro = () => {
    const checks = $('.admin-status-checkbox:checked');
    const valores = checks.map(function () {
        return this.value;
    }).get();
    const label = document.getElementById('estadoFilterCount');

    if (valores.includes('todos') || valores.length === 0) {
        label.textContent = 'Todos';
    } else if (valores.length === 1) {
        label.textContent = checks.first().siblings('span').text();
    } else {
        label.textContent = `${valores.length} filtros`;
    }
};

const limpiarDigitos = (valor) => String(valor ?? '').replace(/\D/g, '');
const correoValido = (correo) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
const existeCredencial = (campo, valor, idActual = 0) => {
    const normalizado = String(valor ?? '').trim().toLowerCase();
    if (!normalizado) {
        return false;
    }
    return credencialesRegistradas.some((item) => (
        Number(item.id) !== Number(idActual)
        && String(item[campo] ?? '').trim().toLowerCase() === normalizado
    ));
};
const setCampoError = (input, mensaje) => {
    const field = input.closest('.admin-form-field');
    const error = field ? field.querySelector('.admin-field-error') : null;
    input.classList.toggle('is-invalid', Boolean(mensaje));
    input.classList.remove('admin-input--ring');
    if (mensaje) {
        void input.offsetWidth;
        input.classList.add('admin-input--ring');
    }
    if (field) {
        field.classList.toggle('has-error', Boolean(mensaje));
    }
    if (error) {
        error.textContent = mensaje || '';
    }
};
const validarFormularioCuenta = (form, mostrarRing = false) => {
    if (!validarGestionCuenta) {
        return true;
    }

    const idActual = Number(form.querySelector('[name="id"]')?.value || 0);
    const accion = form.querySelector('[name="accion"]')?.value || '';
    const campos = {
        nombre: form.querySelector('[name="nombre"]'),
        apellido_paterno: form.querySelector('[name="apellido_paterno"]'),
        apellido_materno: form.querySelector('[name="apellido_materno"]'),
        telefono: form.querySelector('[name="telefono"]'),
        correo: form.querySelector('[name="correo"]'),
        usuario: form.querySelector('[name="usuario"]'),
        contrasena: form.querySelector('[name="contrasena"]'),
    };
    const errores = {};

    ['nombre', 'apellido_paterno', 'apellido_materno'].forEach((campo) => {
        const valor = campos[campo]?.value.trim() || '';
        if (campo !== 'apellido_materno' && valor === '') {
            errores[campo] = 'Este campo es obligatorio.';
        } else if (valor.length > 40) {
            errores[campo] = 'Maximo 40 caracteres.';
        }
    });

    if (campos.telefono) {
        campos.telefono.value = limpiarDigitos(campos.telefono.value).slice(0, 9);
        if (!/^\d{9}$/.test(campos.telefono.value)) {
            errores.telefono = 'Debe tener exactamente 9 digitos.';
        }
    }

    const correo = campos.correo?.value.trim() || '';
    if (!correoValido(correo)) {
        errores.correo = 'Ingresa un correo valido.';
    } else if (existeCredencial('correo', correo, idActual)) {
        errores.correo = 'Este correo ya esta registrado.';
    }

    const usuario = campos.usuario?.value.trim() || '';
    if (usuario === '') {
        errores.usuario = 'El usuario es obligatorio.';
    } else if (existeCredencial('usuario', usuario, idActual)) {
        errores.usuario = 'Este usuario ya existe.';
    }

    const contrasena = campos.contrasena?.value || '';
    if ((accion === 'agregar' || contrasena !== '') && (contrasena.length < 8 || !/\d/.test(contrasena))) {
        errores.contrasena = 'Minimo 8 caracteres y al menos un numero.';
    }

    Object.entries(campos).forEach(([campo, input]) => {
        if (!input) {
            return;
        }
        const mensaje = errores[campo] || '';
        if (!mostrarRing && !input.classList.contains('is-invalid')) {
            const field = input.closest('.admin-form-field');
            const error = field ? field.querySelector('.admin-field-error') : null;
            if (error) {
                error.textContent = mensaje;
            }
            input.classList.toggle('is-invalid', Boolean(mensaje));
            if (field) {
                field.classList.toggle('has-error', Boolean(mensaje));
            }
            return;
        }
        setCampoError(input, mensaje);
    });

    return Object.keys(errores).length === 0;
};

document.querySelectorAll('.js-admin-account-form').forEach((form) => {
    form.addEventListener('input', (event) => {
        if (event.target.matches('[name="telefono"]')) {
            event.target.value = limpiarDigitos(event.target.value).slice(0, 9);
        }
        validarFormularioCuenta(form, false);
    });
    form.addEventListener('submit', (event) => {
        if (!validarFormularioCuenta(form, true)) {
            event.preventDefault();
            form.querySelector('.is-invalid')?.focus();
        }
    });
});

$('#buscarUsuario').on('input', aplicarFiltrosUsuarios);
$('#estadoFilterToggle').on('click', function () {
    const popover = $('#estadoFilterPopover');
    const isOpen = popover.toggleClass('is-open').hasClass('is-open');
    this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});
$('.admin-status-checkbox').on('change', function () {
    if (this.value === 'todos' && this.checked) {
        $('.admin-status-checkbox').not(this).prop('checked', false);
    } else if (this.checked) {
        $('.admin-status-checkbox[value="todos"]').prop('checked', false);
    }

    if ($('.admin-status-checkbox:checked').length === 0) {
        $('.admin-status-checkbox[value="todos"]').prop('checked', true);
    }

    actualizarEtiquetaFiltro();
    aplicarFiltrosUsuarios();
});
document.addEventListener('click', (event) => {
    const popover = document.getElementById('estadoFilterPopover');
    if (!popover || popover.contains(event.target)) {
        return;
    }
    popover.classList.remove('is-open');
    document.getElementById('estadoFilterToggle').setAttribute('aria-expanded', 'false');
});
$('#usuariosPagination').on('click', '.admin-pagination__button:not(.is-disabled)', function () {
    paginaUsuarios = Number($(this).data('page'));
    renderizarUsuarios();
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-edit');
    if (!button) {
        return;
    }
    document.getElementById('editId').value = button.dataset.id;
    document.getElementById('editNombre').value = button.dataset.nombre;
    document.getElementById('editApellidoPaterno').value = button.dataset.apellidoPaterno;
    document.getElementById('editApellidoMaterno').value = button.dataset.apellidoMaterno;
    document.getElementById('editCorreo').value = button.dataset.correo;
    document.getElementById('editTelefono').value = button.dataset.telefono;
    document.getElementById('editUsuario').value = button.dataset.usuario;
    document.getElementById('editImagenActual').value = button.dataset.imagen;
});
document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-delete');
    if (!button || !document.getElementById('deleteId')) {
        return;
    }
    document.getElementById('deleteId').value = button.dataset.id;
    document.getElementById('deleteNombre').textContent = button.dataset.nombre;
});
document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-toggle-status');
    if (!button) {
        return;
    }
    const accion = button.dataset.accion;
    const nombre = button.dataset.nombre;
    document.getElementById('estadoId').value = button.dataset.id;
    document.getElementById('estadoValor').value = button.dataset.estado;
    document.getElementById('estadoModalTitulo').textContent = esGestionClientes
        ? (accion === 'activar' ? 'Restaurar acceso' : 'Bloquear acceso')
        : (accion === 'activar' ? 'Activar cuenta' : 'Desactivar cuenta');
    document.getElementById('estadoModalPregunta').textContent = esGestionClientes
        ? `Estas seguro de ${accion === 'activar' ? 'restaurar el acceso de' : 'bloquear el acceso de'} este cliente?`
        : `Estas seguro de ${accion} esta cuenta?`;
    document.getElementById('estadoModalDetalle').textContent = nombre;
    document.getElementById('estadoContrasena').value = '';
    document.getElementById('estadoSubmit').classList.toggle('admin-button--danger', accion === 'desactivar');
    document.getElementById('estadoSubmit').classList.toggle('admin-button--gold', accion !== 'desactivar');
});
document.getElementById('modalEstado').addEventListener('shown.bs.modal', () => document.getElementById('estadoContrasena').focus());
document.getElementById('toggleEstadoContrasena').addEventListener('click', () => {
    const input = document.getElementById('estadoContrasena');
    const icon = document.querySelector('#toggleEstadoContrasena i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

if (esGestionClientes) {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('.js-client-action');
        if (!button) {
            return;
        }
        document.getElementById('clienteCicloId').value = button.dataset.id;
        document.getElementById('clienteCicloAccion').value = button.dataset.accion;
        document.getElementById('clienteCicloTitulo').textContent = button.dataset.titulo;
        document.getElementById('clienteCicloNombre').textContent = button.dataset.nombre;
        document.getElementById('clienteCicloDescripcion').textContent = button.dataset.descripcion;
        document.getElementById('clienteCicloContrasena').value = '';
        document.getElementById('clienteCicloSubmit').classList.toggle('admin-button--danger', button.dataset.accion === 'eliminar_inactividad');
        document.getElementById('clienteCicloSubmit').classList.toggle('admin-button--gold', button.dataset.accion !== 'eliminar_inactividad');
    });

    document.getElementById('modalClienteCiclo').addEventListener('shown.bs.modal', () => document.getElementById('clienteCicloContrasena').focus());
    document.getElementById('toggleClienteCicloContrasena').addEventListener('click', () => {
        const input = document.getElementById('clienteCicloContrasena');
        const icon = document.querySelector('#toggleClienteCicloContrasena i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
}

renderizarUsuarios();
</script>
</body>
</html>
