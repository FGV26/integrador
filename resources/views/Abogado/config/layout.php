<?php

function abogado_dashboard_nav_items(string $baseUrl, string $activePage): array
{
    return [
        ['label' => 'Inicio', 'href' => $baseUrl . 'Abogado/PanelPrincipal.php', 'active' => $activePage === 'inicio'],
        ['label' => 'Perfil', 'href' => $baseUrl . 'Abogado/Perfil.php', 'active' => $activePage === 'perfil'],
        ['label' => 'Citas', 'href' => $baseUrl . 'Abogado/Citas.php', 'active' => $activePage === 'citas'],
    ];
}

function render_abogado_dashboard_header(string $baseUrl, string $activePage, ?string $backUrl = null, string $backLabel = 'Volver al panel'): void
{
    $navItems = abogado_dashboard_nav_items($baseUrl, $activePage);
    $backUrl = $backUrl ?? ($baseUrl . 'Abogado/PanelPrincipal.php');
    ?>
    <header class="profile-header">
        <div class="profile-shell profile-header__inner">
            <a href="<?php echo htmlspecialchars($baseUrl . 'Abogado/PanelPrincipal.php', ENT_QUOTES, 'UTF-8'); ?>" class="profile-brand">
                <img src="<?php echo htmlspecialchars($baseUrl . 'assets/img/Ortiz_y_Asociados.png', ENT_QUOTES, 'UTF-8'); ?>" alt="Ortiz y Asociados" class="profile-brand__logo">
            </a>

            <nav class="profile-nav" aria-label="Navegacion abogado">
                <?php foreach ($navItems as $item) : ?>
                    <a
                        href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"
                        class="profile-nav__link<?php echo !empty($item['active']) ? ' is-active' : ''; ?>">
                        <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="profile-header__actions">
                <a href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="profile-header__button profile-header__button--ghost">
                    <i class="bi bi-arrow-left"></i>
                    <span><?php echo htmlspecialchars($backLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
                <a href="<?php echo htmlspecialchars($baseUrl . 'CerrarSesion.php', ENT_QUOTES, 'UTF-8'); ?>" class="profile-header__button profile-header__button--dark">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar sesion</span>
                </a>
            </div>
        </div>
    </header>
    <?php
}

function render_abogado_dashboard_footer(): void
{
    ?>
    <footer class="profile-footer">
        <div class="profile-shell profile-footer__inner">
            <div>
                <strong>Ortiz y Asociados</strong>
                <p>Panel operativo del abogado dentro del sistema del estudio juridico.</p>
            </div>
        </div>
    </footer>
    <?php
}
