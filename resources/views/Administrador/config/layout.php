<?php

function administrador_nav_items(string $baseUrl, string $activePage): array
{
    return [
        ['label' => 'Inicio', 'href' => $baseUrl . 'Administrador/PanelPrincipal.php', 'active' => $activePage === 'inicio'],
        ['label' => 'Actividad', 'href' => $baseUrl . 'Administrador/Actividad.php', 'active' => $activePage === 'actividad'],
        ['label' => 'Clientes', 'href' => $baseUrl . 'Administrador/GestionClientes.php', 'active' => $activePage === 'clientes'],
        ['label' => 'Abogados', 'href' => $baseUrl . 'Administrador/GestionAbogados.php', 'active' => $activePage === 'abogados'],
        ['label' => 'Administradores', 'href' => $baseUrl . 'Administrador/GestionAdministradores.php', 'active' => $activePage === 'administradores'],
    ];
}

function render_administrador_header(string $baseUrl, string $activePage): void
{
    $navItems = administrador_nav_items($baseUrl, $activePage);
    ?>
    <header class="profile-header admin-header">
        <div class="profile-shell profile-header__inner admin-header__inner">
            <a href="<?php echo htmlspecialchars($baseUrl . 'Administrador/PanelPrincipal.php', ENT_QUOTES, 'UTF-8'); ?>" class="profile-brand">
                <img src="<?php echo htmlspecialchars($baseUrl . 'assets/img/Ortiz_y_Asociados.png', ENT_QUOTES, 'UTF-8'); ?>" alt="Ortiz y Asociados" class="profile-brand__logo">
            </a>
            <nav class="profile-nav admin-nav" aria-label="Navegacion administrador">
                <?php foreach ($navItems as $item) : ?>
                    <a href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" class="profile-nav__link<?php echo $item['active'] ? ' is-active' : ''; ?>">
                        <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="profile-header__actions">
                <a href="<?php echo htmlspecialchars($baseUrl . 'Administrador/Perfil.php', ENT_QUOTES, 'UTF-8'); ?>" class="profile-header__button profile-header__button--ghost profile-header__button--icon" aria-label="Perfil">
                    <i class="bi bi-person-circle"></i>
                </a>
                <a href="<?php echo htmlspecialchars($baseUrl . 'CerrarSesion.php', ENT_QUOTES, 'UTF-8'); ?>" class="profile-header__button profile-header__button--dark">
                    <i class="bi bi-box-arrow-right"></i><span>Cerrar sesion</span>
                </a>
            </div>
        </div>
    </header>
    <?php
}

function render_administrador_footer(): void
{
    ?>
    <footer class="profile-footer admin-footer">
        <div class="profile-shell profile-footer__inner">
            <div>
                <strong>Ortiz y Asociados</strong>
                <p>Centro administrativo y control operativo del estudio juridico.</p>
            </div>
        </div>
    </footer>
    <?php
}

function render_admin_pagination(string $baseUrl, int $pagina, int $totalPaginas, string $busqueda): void
{
    if ($totalPaginas <= 1) {
        return;
    }
    $desde = max(1, $pagina - 2);
    $hasta = min($totalPaginas, $pagina + 2);
    ?>
    <nav class="admin-pagination" aria-label="Paginacion de usuarios">
        <a class="admin-pagination__button<?php echo $pagina <= 1 ? ' is-disabled' : ''; ?>" href="<?php echo $pagina > 1 ? htmlspecialchars($baseUrl . '?' . http_build_query(['buscar' => $busqueda, 'pagina' => $pagina - 1])) : '#'; ?>" aria-label="Pagina anterior">
            <i class="bi bi-chevron-left"></i>
        </a>
        <?php for ($i = $desde; $i <= $hasta; $i++) : ?>
            <a class="admin-pagination__button<?php echo $i === $pagina ? ' is-active' : ''; ?>" href="<?php echo htmlspecialchars($baseUrl . '?' . http_build_query(['buscar' => $busqueda, 'pagina' => $i])); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <a class="admin-pagination__button<?php echo $pagina >= $totalPaginas ? ' is-disabled' : ''; ?>" href="<?php echo $pagina < $totalPaginas ? htmlspecialchars($baseUrl . '?' . http_build_query(['buscar' => $busqueda, 'pagina' => $pagina + 1])) : '#'; ?>" aria-label="Pagina siguiente">
            <i class="bi bi-chevron-right"></i>
        </a>
    </nav>
    <?php
}
