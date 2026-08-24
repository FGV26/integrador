<?php
require_once __DIR__ . '/config/app.php';
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
session_start();

$base_url = app_base_url();
$usuario = $_SESSION['usuario'] ?? null;
$isLoggedIn = $usuario && $usuario->getRol() === 'cliente';

if ($usuario && !$isLoggedIn) {
    header('Location: ' . $base_url . 'CerrarSesion.php');
    exit();
}

$displayName = $isLoggedIn ? $usuario->getNombre() : '';
$firstName = $isLoggedIn ? explode(' ', trim($displayName))[0] : '';
$initial = $isLoggedIn ? strtoupper(substr($firstName, 0, 1)) : '';

$specialties = [
    [
        'icon' => 'bi bi-shield-lock',
        'title' => 'Derecho Penal',
        'description' => 'Defensa penal integral ante procesos criminales, medidas cautelares y estrategias de representacion para imputados y victimas.',
        'areas' => ['Delitos patrimoniales', 'Delitos informaticos', 'Defensa inmediata', 'Acompanamiento procesal'],
    ],
    [
        'icon' => 'bi bi-file-earmark-text',
        'title' => 'Derecho Civil',
        'description' => 'Asesoria sobre contratos, obligaciones, responsabilidad civil, sucesiones y conflictos patrimoniales entre particulares.',
        'areas' => ['Contratos', 'Indemnizaciones', 'Sucesiones', 'Obligaciones'],
    ],
    [
        'icon' => 'bi bi-people',
        'title' => 'Derecho Familiar',
        'description' => 'Acompanamiento legal con enfoque humano para divorcios, alimentos, tenencia, regimen de visitas y procesos familiares sensibles.',
        'areas' => ['Divorcio', 'Alimentos', 'Custodia', 'Regimen de visitas'],
    ],
    [
        'icon' => 'bi bi-journal-text',
        'title' => 'Derecho Notarial',
        'description' => 'Orientacion y formalizacion de actos notariales que requieren seguridad juridica y documentacion valida ante terceros.',
        'areas' => ['Poderes', 'Escrituras', 'Testamentos', 'Declaraciones'],
    ],
];

$services = [
    ['icon' => 'bi bi-shield-check', 'title' => 'Servicio garantizado', 'description' => 'Abordamos cada caso con estrategia, seguimiento y foco real en resultados.'],
    ['icon' => 'bi bi-award', 'title' => 'Especialistas certificados', 'description' => 'Equipo con experiencia en Derecho Penal, Civil, Familiar y Notarial.'],
    ['icon' => 'bi bi-people', 'title' => 'Soporte personalizado', 'description' => 'Asesoria cercana y adaptada al contexto de cada cliente y cada proceso.'],
];

$stats = [
    ['value' => '1200', 'label' => 'Casos atendidos', 'suffix' => '+'],
    ['value' => '2000', 'label' => 'Consultas atendidas', 'suffix' => '+'],
    ['value' => '8', 'label' => 'Anios de experiencia', 'suffix' => '+'],
    ['value' => '4', 'label' => 'Servicios clave', 'suffix' => ''],
];

$sectors = [
    ['title' => 'Empresas', 'description' => 'Asesoria corporativa y resolucion de conflictos operativos.'],
    ['title' => 'Particulares', 'description' => 'Defensa personal, familiar y patrimonial.'],
    ['title' => 'Organizaciones', 'description' => 'Acompanamiento para asociaciones y entidades.'],
];

$contactCards = [
    ['icon' => 'bi bi-geo-alt', 'label' => 'Ubicacion', 'value' => 'Parque Bolivar 12 Noviembre, San Juan de Miraflores, Lima'],
    ['icon' => 'bi bi-telephone', 'label' => 'Telefonos', 'value' => "(01) 525-4514\n991-259-688"],
    ['icon' => 'bi bi-envelope', 'label' => 'Correo', 'value' => 'asociacionortiz@gmail.com'],
];

$socialLinks = [
    ['icon' => 'bi bi-facebook', 'label' => 'Facebook', 'href' => '#'],
    ['icon' => 'bi bi-twitter-x', 'label' => 'X', 'href' => '#'],
    ['icon' => 'bi bi-linkedin', 'label' => 'LinkedIn', 'href' => '#'],
    ['icon' => 'bi bi-instagram', 'label' => 'Instagram', 'href' => '#'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudio Juridico Ortiz y Asociados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/landing.css">
</head>
<body class="landing-body">
    <div class="landing-shell">
        <nav class="landing-nav" data-nav>
            <div class="landing-nav__inner">
                <a href="#inicio" class="landing-brand">
                    <img src="<?php echo $base_url; ?>assets/img/Ortiz_y_Asociados.png" alt="Ortiz y Asociados" class="landing-brand__logo">
                </a>

                <button class="landing-nav__toggle" type="button" data-menu-toggle aria-label="Abrir menu">Menu</button>

                <div class="landing-nav__links" data-menu>
                    <a href="#inicio">Inicio</a>
                    <a href="#firma">Nuestra firma</a>
                    <a href="#especialidades">Especialidades</a>
                    <a href="#contacto">Contacto</a>
                </div>

                <div class="landing-nav__actions">
                    <?php if ($isLoggedIn) : ?>
                        <div class="landing-profile" data-profile>
                            <button class="landing-profile__button" type="button" data-profile-toggle>
                                <span class="landing-profile__avatar"><?php echo htmlspecialchars($initial); ?></span>
                                <span class="landing-profile__name"><?php echo htmlspecialchars($firstName); ?></span>
                            </button>
                            <div class="landing-profile__menu" data-profile-menu>
                                <div class="landing-profile__menu-head">
                                    <strong><?php echo htmlspecialchars($displayName); ?></strong>
                                    <span><?php echo htmlspecialchars($usuario->getCorreo()); ?></span>
                                </div>
                                <a href="<?php echo $base_url; ?>Cliente/Perfil.php">Mi perfil</a>
                                <a href="<?php echo $base_url; ?>Cliente/Citas.php">Mis citas</a>
                                <a href="<?php echo $base_url; ?>CerrarSesion.php" class="landing-profile__logout">Cerrar sesion</a>
                            </div>
                        </div>
                    <?php else : ?>
                        <a href="<?php echo $base_url; ?>IniciarSesion.php" class="landing-link-button">Iniciar sesion</a>
                        <a href="<?php echo $base_url; ?>RegistrarUsuario.php" class="landing-primary-button landing-primary-button--small">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <main>
            <section id="inicio" class="hero">
                <div class="hero__media"></div>
                <div class="hero__overlay"></div>
                <div class="hero__glow"></div>

                <div class="hero__content">
                    <div class="hero__copy">
                        <span class="hero__eyebrow">Estudio juridico en Lima, Peru</span>
                        <h1>Defendemos tus <span>derechos</span> con experiencia y compromiso</h1>
                        <p>
                            Especialistas en Derecho Penal, Civil, Familiar y Notarial. Asesoria legal personalizada
                            con un equipo comprometido en obtener los mejores resultados para cada cliente.
                        </p>
                        <div class="hero__cta">
                            <?php if ($isLoggedIn) : ?>
                                <a href="<?php echo $base_url; ?>Cliente/SolicitarCita.php" class="landing-primary-button">Agendar una cita</a>
                            <?php else : ?>
                                <a href="<?php echo $base_url; ?>RegistrarUsuario.php" class="landing-primary-button">Registrate para agendar</a>
                            <?php endif; ?>
                            <a href="#especialidades" class="landing-secondary-button">Ver especialidades</a>
                        </div>
                        <?php if (!$isLoggedIn) : ?>
                            <p class="hero__helper">
                                Ya tienes cuenta. <a href="<?php echo $base_url; ?>IniciarSesion.php">Ingresa aqui</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section id="servicios" class="services">
                <div class="section-shell">
                    <div class="services__grid">
                        <?php foreach ($services as $service) : ?>
                            <article class="service-card">
                                <div class="service-card__head">
                                    <span class="service-card__icon"><i class="<?php echo htmlspecialchars($service['icon']); ?>"></i></span>
                                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                                </div>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="stats">
                <div class="section-shell stats__grid">
                    <?php foreach ($stats as $stat) : ?>
                        <div class="stat-card">
                            <strong class="stat-card__value" data-count="<?php echo htmlspecialchars($stat['value']); ?>" data-suffix="<?php echo htmlspecialchars($stat['suffix']); ?>">0</strong>
                            <span><?php echo htmlspecialchars($stat['label']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="firma" class="firm">
                <div class="section-shell">
                    <header class="section-heading">
                        <h2>Nuestra firma</h2>
                        <p>Un equipo de profesionales comprometidos con la excelencia juridica y la defensa de los derechos de nuestros clientes.</p>
                    </header>

                    <div class="firm__grid">
                        <div class="firm__story">
                            <p>
                                Somos un estudio juridico con amplia experiencia en el sector publico y privado. Nos especializamos
                                en las ramas del Derecho Penal, Civil, Familiar y Notarial, ofreciendo una defensa integra en las
                                etapas del proceso y una representacion eficaz de los intereses de nuestros clientes.
                            </p>
                            <p>
                                Con sede en San Juan de Miraflores, atendemos a empresas, particulares y organizaciones con la misma
                                dedicacion y excelencia profesional que buscamos reflejar ahora en una sola landing page.
                            </p>
                            <p>
                                Nuestro trabajo combina experiencia tecnica, acompanamiento cercano y una estrategia legal clara para
                                que cada cliente entienda el proceso, tome decisiones con seguridad y reciba una atencion constante
                                durante cada etapa de su caso.
                            </p>
                        </div>

                        <div class="firm__sidebar">
                            <div class="firm__panel">
                                <h3>Sectores que atendemos</h3>
                                <?php foreach ($sectors as $sector) : ?>
                                    <article>
                                        <strong><?php echo htmlspecialchars($sector['title']); ?></strong>
                                        <p><?php echo htmlspecialchars($sector['description']); ?></p>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="firm__values">
                        <article>
                            <strong>Mision</strong>
                            <p>Brindar asesoria legal de calidad, protegiendo los derechos e intereses de cada cliente.</p>
                        </article>
                        <article>
                            <strong>Vision</strong>
                            <p>Ser un estudio juridico de referencia, reconocido por excelencia, etica profesional y resultados.</p>
                        </article>
                        <article>
                            <strong>Valores</strong>
                            <p>Integridad, compromiso y transparencia en cada actuacion legal.</p>
                        </article>
                        <article>
                            <strong>Trayectoria</strong>
                            <p>Anos de experiencia defendiendo con exito los intereses de nuestros clientes.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="especialidades" class="specialties">
                <div class="section-shell">
                    <header class="section-heading">
                        <span>Lo que hacemos</span>
                        <h2>Nuestras especialidades</h2>
                        <p>Cubrimos las ramas del derecho con las que un ciudadano, empresa u organizacion puede necesitar asistencia legal.</p>
                    </header>

                    <div class="specialties__grid">
                        <?php foreach ($specialties as $specialty) : ?>
                            <article class="specialty-card">
                                <span class="specialty-card__icon"><i class="<?php echo htmlspecialchars($specialty['icon']); ?>"></i></span>
                                <h3><?php echo htmlspecialchars($specialty['title']); ?></h3>
                                <p><?php echo htmlspecialchars($specialty['description']); ?></p>
                                <ul>
                                    <?php foreach ($specialty['areas'] as $area) : ?>
                                        <li><?php echo htmlspecialchars($area); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section id="contacto" class="contact">
                <div class="section-shell">
                    <header class="section-heading">
                        <h2>Contactenos</h2>
                        <p>Primera consulta sin costo. Puede ubicarnos por telefono, correo o agendar directamente desde su cuenta.</p>
                    </header>

                    <div class="contact__cards">
                        <?php foreach ($contactCards as $card) : ?>
                            <article class="contact-card">
                                <span class="contact-card__icon"><i class="<?php echo htmlspecialchars($card['icon']); ?>"></i></span>
                                <strong><?php echo htmlspecialchars($card['label']); ?></strong>
                                <p><?php echo nl2br(htmlspecialchars($card['value'])); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="contact__grid">
                        <div class="contact__panel">
                            <h3>Orientacion inicial</h3>
                            <p>
                                Esta landing concentra la informacion principal del estudio. La gestion operativa de citas,
                                perfil y autenticacion se mantiene conectada con el sistema real.
                            </p>

                            <div class="contact__actions">
                                <?php if ($isLoggedIn) : ?>
                                    <a href="<?php echo $base_url; ?>Cliente/SolicitarCita.php" class="landing-primary-button landing-primary-button--block">Agendar una cita</a>
                                    <a href="<?php echo $base_url; ?>Cliente/Perfil.php" class="landing-secondary-button landing-secondary-button--block">Ver mi perfil</a>
                                <?php else : ?>
                                    <a href="<?php echo $base_url; ?>RegistrarUsuario.php" class="landing-primary-button landing-primary-button--block">Crear una cuenta</a>
                                    <a href="<?php echo $base_url; ?>IniciarSesion.php" class="landing-secondary-button landing-secondary-button--block">Ya tengo cuenta</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="contact__map">
                            <div class="contact__map-head">
                                <h3>Nuestra ubicacion</h3>
                                <p>San Juan de Miraflores, Lima, Peru</p>
                            </div>
                            <iframe
                                title="Ubicacion Estudio Juridico Ortiz y Asociados"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3900.4906860297384!2d-76.97316268521942!3d-12.157831991387756!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105b7c52b8b9f95%3A0x6e84f82de81e2bb!2sSan%20Juan%20de%20Miraflores%2C%20Lima%2C%20Per%C3%BA!5e0!3m2!1ses!2spe!4v1700000000000!5m2!1ses!2spe"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="landing-footer">
            <div class="section-shell landing-footer__inner">
                <div class="landing-footer__brand">
                    <strong>Ortiz y Asociados</strong>
                    <small>Estudio Juridico</small>
                    <p>Estudio juridico especializado en Lima, Peru. Defensa legal con excelencia, etica e integridad.</p>
                    <div class="landing-footer__socials">
                        <?php foreach ($socialLinks as $link) : ?>
                            <a href="<?php echo htmlspecialchars($link['href']); ?>" aria-label="<?php echo htmlspecialchars($link['label']); ?>">
                                <i class="<?php echo htmlspecialchars($link['icon']); ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <span>Servicios</span>
                    <?php foreach ($specialties as $specialty) : ?>
                        <a href="#especialidades"><?php echo htmlspecialchars($specialty['title']); ?></a>
                    <?php endforeach; ?>
                </div>
                <div>
                    <span>Sectores</span>
                    <?php foreach ($sectors as $sector) : ?>
                        <a href="#firma"><?php echo htmlspecialchars($sector['title']); ?></a>
                    <?php endforeach; ?>
                </div>
                <div>
                    <span>Contacto</span>
                    <p>San Juan de Miraflores, Lima</p>
                    <p>(01) 525-4514</p>
                    <p>991-259-688</p>
                    <p>asociacionortiz@gmail.com</p>
                </div>
            </div>
            <div class="section-shell landing-footer__bottom">
                <p>&copy; 2025 Abogados Estudio Juridico Ortiz y Asociados - Todos los derechos reservados</p>
                <div class="landing-footer__bottom-links">
                    <a href="#inicio">Inicio</a>
                    <a href="#firma">Nuestra firma</a>
                    <a href="#especialidades">Especialidades</a>
                    <a href="#contacto">Contacto</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/landing.js"></script>
</body>
</html>
