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
    <title>Estudio Jurídico Ortiz y Asociados - Derecho penal</title>
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
                        <img src="<?php echo $base_url; ?>src/img/penal.png" alt="Estudio Jurídico Ortiz y Asociados" width="200">
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
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>Penal.php">Derecho Penal</a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>Civil.php">Derecho Civil</a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>Familiar.php">Derecho Familiar</a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>Notarial.php">Derecho Notarial</a></li>
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
    </header>s

   <!-- Comienza el contenido principal -->
   <main>
        <!-- Comienza la sección de hero -->
        <section class="hero-section d-flex align-items-center">

            <div class="header-txt1" style=" width: 300px; ">
                            <h1 style="font-size: 36px;">ABOGADOS ESPECIALISTAS</h1>
                            <p style="line-height: 200% "  class="text-V">            
                            <h1 style="font-size: 36px;">DERECHO PENAL</h1>
                Expertos en la materia
                Abogados expertos en Derecho Penal 
                con años de experiencia
                en distintos casos.Te asesoraremos
                 y acompañaremos a traves del proceso
                que se presente como consecuencia de 
                una imputación de un delito. </p>
                             </div>
                        <div class="header-img">
                            <img src="src/img/penal.png" alt="" width="550px" height="350">
                        </div>
                        <h1>
                            <p1>
                            <div class="header-txt1" style=" width: 300px; ">
                <a href="citas.php" class="btn btn-warning text-dark mt-3">Agendar Una Cita</a>
                <a href="contacto.php" class="btn btn-warning text-dark mt-3">Llámanos</a>
                        </p1>
                        </h1>
                    </div>
        </section>

        <!-- Comienza la sección de servicios -->
        <section class="bg-dark py-5">
            <div class="container">
                <div class="row text-center text-white">
                    <div class="col-md-4" data-aos="fade-right">
                        <div class="card service-card border-0">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="fas fa-check-circle fa-2x text-warning" data-aos-delay="200"></i>
                                </div>
                                <p class="card-text text-dark">Te defenderemos de cualquier delito que se te haya imputado brindándote las garantias del caso.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-right" data-aos-delay="300">
                        <div class="card service-card border-0">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="fas fa-book fa-2x text-warning"></i>
                                </div>
                                <p class="card-text text-dark">Nos encargaremos de todo proceso penal que tenga, garantizándole efectividad y resultados</p>
                            </div>
                        </div>
                    </div>

            </div>
        </section>
    </main>
            </div>
        </section>
    </main>
    <!-- Sección de Socio -->
 
    <section class="socio-section bg-dark text-white py-5">
        <div class="container text-center">

        <h2>DERECHO PENAL</h2>
            <div class="row justify-content-center mt-4">
                <div class="col-md-6">
              <p>  Es la rama del derecho que sanciona y prohíbe conductas</p> 
              <p>  humanas antijurídicas contenidas en el código penal y/o</p> 
              <p>  normas especiales,con la finalidad de salvaguardar el </p> 
              <p>  Estado de Derecho, el orden y la disciplina en los </p> 
              <p>   habitantes de la soberanía Estatal.</p> 
                </div>
            </div>
        </div>
        </div>
       
    

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
                    <a href="penal.php">Derecho Penal</a>
                    <a href="Civil.php">Derecho Civil</a>
                    <a href="Familiar.php">Derecho Familiar</a>
                    <a href="Notarial.php">Derecho Notarial</a>
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
