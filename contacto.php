<?php
require_once 'model/Usuario.php';
require_once 'dao/UsuarioDAO.php';
session_start();

$base_url = 'http://localhost/INTEGRADOR/';

// Validar que solo los clientes puedan acceder al index
if (isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    $rol = $usuario->getRol();
    if ($rol !== 'cliente') {
        header('Location: logout.php'); // Redirigir a logout si no es cliente
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudio Jurídico Ortiz y Asociados - Contactanos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>src/css/styles.css">
    <!-- AOS CSS ANIMACIONES CUANDO BAJAS EL SCROLL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu+Sans+Mono:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="bg-dark text-white">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
                <div class="container">
                    <!-- Logo alineado a la izquierda -->
                    <a class="navbar-brand" href="<?php echo $base_url; ?>">
                        <img src="<?php echo $base_url; ?>src/img/logo.jpg" alt="Estudio Jurídico Ortiz y Asociados" width="200">
                    </a>

                    <!-- Menú de navegación centrado -->
                    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="<?php echo $base_url; ?>">Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $base_url; ?>firma.php">Nuestra Firma</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="especialidadesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Especialidades
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="especialidadesDropdown">
                                    <li><a class="dropdown-item" href="#">Derecho Penal</a></li>
                                    <li><a class="dropdown-item" href="#">Derecho Civil</a></li>
                                    <li><a class="dropdown-item" href="#">Derecho Familiar</a></li>
                                    <li><a class="dropdown-item" href="#">Derecho Notarial</a></li>
                                </ul>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $base_url; ?>contacto.php">Contacto</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Menú de usuario alineado a la derecha -->
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['usuario'])) : ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" id="dropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Bienvenido, <?php echo $_SESSION['usuario']->getNombre(); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                                    <li><a class="dropdown-item" href="<?php echo $base_url; ?>PerfilCliente.php">Perfil</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base_url; ?>VerCitas.php">Ver Citas</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base_url; ?>logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        <?php else : ?>
                            <li class="nav-item">
                                <a href="<?php echo $base_url; ?>login.php" class="btn btn-warning text-dark">¡Registrate!</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Comienza el contenido principal -->
    <main class="bg-dark text-white py-5">
        <div class="contacto-section d-flex align-items-center justify-content-center" style="background: url('src/img/edificios.png') no-repeat center center/cover; height:400px ">
            <h2 style="color:black" ><i class="fas fa-headset"></i> Contactenos</h2>
        </div>
        <!-- Sección "Nuestro Estudio" -->
        <section class="nuestro-estudio">
            <div class="container">
                <!--
                <div class="row">
                    <div class="col-md-8" data-aos="fade-right">
                        <h2>Nuestro Estudio</h2>
                        <p>Somos un estudio integrado por profesionales con amplia experiencia en el sector público y privado y con un alto nivel de especialización en Derecho Civil, Penal, Familiar y Notarial, cuyas asesorías y patrocinios basados en la credibilidad y eficiencia determinan la certera toma de decisiones y la defensa idónea de los intereses de nuestros clientes.</p>
                    </div>
                    <div class="col-md-4" data-aos="fade-left">
                        <img src="src/img/abogadosFirma.png" alt="Nuestro Estudio">
                    </div>
                </div>
                -->
                <!-- Div para "Misión, Visión y Valores" -->
                <div class="row text-center mt-5">
                    <div class="col-md-4" data-aos="fade-up">
                        <div class="card">
                            <div class="card-body" style="color:black">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>Ubicación</h3>
                                <p>Parque Belen 12 Noviembre V28M + X3M, Lima 15806
                                    San Juan de Miraflores, Lima-Perú
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card">
                            <div class="card-body" style="color:black">
                                <i class="fas fa-phone-alt"></i>
                                <h3>Teléfonos</h3>
                                <p>(01) 525-4514 / 991-259-680</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="card">
                            <div class="card-body" style="color:black">
                                <i class="fas fa-envelope"></i>
                                <h3>Correo</h3>
                                <p>asociacionortiz@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <section class="contact-section py-5">
            <div class="container">
                <h2 class="text-center" data-aos="fade-up">Contáctenos</h2>
                <form id="contactForm" action="send_email.php" method="post" class="mt-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Asunto</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </section>



    </main>
    <!-- Sección de Socio -->
    <section class="socio-section bg-dark text-white py-5">
        <div class="container text-center">
            <h2>Socio</h2>
            <div class="row justify-content-center mt-4">
                <div class="col-md-6">
                    <div class="card bg-dark text-white border-0">
                        <img src="src/img/image.png" class="card-img-top" alt="Rudy Ortiz Espino">
                        <div class="card-body">
                            <h5 class="card-title">Rudy Ortiz Espino</h5>
                            <p class="card-text">CEO Y Socio Fundador</p>
                            <div>
                                <a href="#" class="text-white mx-2"><i class="fab fa-twitter fa-2x"></i></a>
                                <a href="#" class="text-white mx-2"><i class="fab fa-facebook fa-2x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comienza el footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-3" data-aos="fade-up">
                    <h5>Abogados Estudio Jurídico Ortiz y Asociados</h5>
                    <address>
                        Dirección: Paseo Bolivar 123<br>
                        Teléfono: +521-123-456-789<br>
                        Email: info@juridico.com
                    </address>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <h5>Servicios Legales</h5>
                    <ul class="list-unstyled">
                        <li>Derecho Penal</li>
                        <li>Derecho Civil</li>
                        <li>Derecho Familiar</li>
                        <li>Derecho Notarial</li>
                    </ul>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <h5>Sectores Atendidos</h5>
                    <ul class="list-unstyled">
                        <li>Empresas</li>
                        <li>Particulares</li>
                        <li>Organizaciones</li>
                    </ul>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <h5>Síguenos</h5>
                    <ul class="list-unstyled d-flex justify-content-center">
                        <li class="mx-2" data-aos="zoom-in" data-aos-delay="400"><a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a></li>
                        <li class="mx-2" data-aos="zoom-in" data-aos-delay="500"><a href="#" class="text-white"><i class="fab fa-twitter"></i></a></li>
                        <li class="mx-2" data-aos="zoom-in" data-aos-delay="600"><a href="#" class="text-white"><i class="fab fa-instagram"></i></a></li>
                        <li class="mx-2" data-aos="zoom-in" data-aos-delay="700"><a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="text-center mt-4">
                <p>&copy; 2023 Abogados Estudio Jurídico Ortiz y Asociados - Todos los derechos reservados</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&callback=initMap" async defer></script>
    <script src="src/js/script.js"></script>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            once: false // Permitir animaciones en cada scroll sin necesidad de actualizar
        });
    </script>
</body>
</html>
