<?php

function cliente_dashboard_nav_items(string $baseUrl, string $activePage): array
{
    return [
        ['label' => 'Perfil', 'href' => $baseUrl . 'Cliente/Perfil.php', 'active' => $activePage === 'perfil'],
        ['label' => 'Mis citas', 'href' => $baseUrl . 'Cliente/Citas.php', 'active' => $activePage === 'citas'],
        ['label' => 'Agendar cita', 'href' => $baseUrl . 'Cliente/SolicitarCita.php', 'active' => $activePage === 'solicitar'],
    ];
}

function render_cliente_dashboard_header(string $baseUrl, string $activePage, ?string $backUrl = null, string $backLabel = 'Volver al inicio'): void
{
    $navItems = cliente_dashboard_nav_items($baseUrl, $activePage);
    $backUrl = $backUrl ?? ($baseUrl . 'index.php');
    ?>
    <header class="profile-header">
        <div class="profile-shell profile-header__inner">
            <a href="<?php echo htmlspecialchars($baseUrl . 'index.php', ENT_QUOTES, 'UTF-8'); ?>" class="profile-brand">
                <img src="<?php echo htmlspecialchars($baseUrl . 'assets/img/Ortiz_y_Asociados.png', ENT_QUOTES, 'UTF-8'); ?>" alt="Ortiz y Asociados" class="profile-brand__logo">
            </a>

            <nav class="profile-nav" aria-label="Navegacion cliente">
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

function render_cliente_dashboard_footer(): void
{
    ?>
    <footer class="profile-footer">
        <div class="profile-shell profile-footer__inner">
            <div>
                <strong>Ortiz y Asociados</strong>
                <p>Panel de cliente integrado al sistema del estudio juridico.</p>
            </div>
        </div>
    </footer>
    <?php
}
