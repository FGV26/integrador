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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Derecho Familiar</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    
    <!-- Slick CSS for slider -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

  
    
    <style>
        .flip-container {
            perspective: 1000px;
        }

        .flip-card {
            width: 100%;
            height: 200px; /* Ajusta la altura según tus necesidades */
            position: relative;
            transition: transform 0.6s;
            transform-style: preserve-3d;
            cursor: pointer;
        }

        .flip-card:hover {
            transform: rotateY(180deg);
        }

        .flip-card-inner {
            position: absolute;
            width: 100%;
            height: 100%;
            transition: transform 0.6s;
            transform-style: preserve-3d;
        }

        .flip-card-front, .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .flip-card-front {
            background-color: #FFD433; /* Color amarillo en Tailwind */
            color: white;
        }

        .flip-card-back {
            background-color: #f97316; /* Color naranja en Tailwind */
            color: white;
            transform: rotateY(180deg);
            padding: 1rem;
            text-align: center;
        }
        .slick-slide {
            display: flex;
            justify-content: center;
        }
    </style>

</head>
<body class="bg-black text-white">
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
                                    <li><a class="dropdown-item" href="Penal.php">Derecho Penal</a></li>
                                    <li><a class="dropdown-item" href="Civil.php">Derecho Civil</a></li>
                                    <li><a class="dropdown-item" href="Familiar.php">Derecho Familiar</a></li>
                                    <li><a class="dropdown-item" href="Notarial.php">Derecho Notarial</a></li>
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

    <main class="max-w-7xl mx-auto p-4">
        <section class="flex flex-col md:flex-row" data-aos="fade-up">
            <div class="md:w-2/3 p-8">
                <h1 class="text-4xl font-bold">Derecho CIVIL</h1>
                <p class="text-xl mt-2">Expertos en la Materia</p>
                <p class="mt-4">Abogados especializados en Derecho Civil con años de experiencia en diversos casos. Te brindaremos asesoramiento y te acompañaremos en cada etapa del proceso que enfrentes, garantizando la protección de tus derechos y la resolución efectiva de tus asuntos legales.</p>
                <div class="mt-6 space-x-4">
                    <a href="citas.php" class="bg-white text-black px-4 py-2 rounded-md">Agendar Una Cita</a>
                    <a href="tel:+51991259680" class="bg-white text-black px-4 py-2 rounded-md">Llámanos</a>
                </div>
                <section class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8" data-aos="fade-up" data-aos-delay="200">
                    <div class="flip-container">
                        <div class="flip-card">
                            <div class="flip-card-inner">
                                <div class="flip-card-front p-4 rounded-md">
                                    <i class="fas fa-gavel text-5xl"></i>
                                </div>
                                <div class="flip-card-back rounded-md">
                                    <p class="text-center">Te asistimos en cualquier conflicto civil, asegurando la protección de tus derechos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flip-container">
                        <div class="flip-card">
                            <div class="flip-card-inner">
                                <div class="flip-card-front p-4 rounded-md">
                                    <i class="fas fa-balance-scale text-5xl"></i>
                                </div>
                                <div class="flip-card-back rounded-md">
                                    <p class="text-center">Gestionamos todos tus asuntos civiles, garantizándote efectividad y resultados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="md:w-1/3 p-8">
                <img src="src/img/abogado-civil.jpg" alt="Abogado con traje sosteniendo documentos">
            </div>
        </section>

        <section class="flex flex-col md:flex-row items-center" data-aos="fade-up" data-aos-delay="200">
            <div class="md:w-2/3">
                <h2 class="text-3xl font-bold">Derecho Civil</h2>
                <p class="mt-4">El Derecho Civil es la rama del derecho que regula las relaciones privadas entre individuos y/o entidades. Comprende aspectos como el estado civil de las personas, las relaciones familiares, las obligaciones y contratos, la propiedad y demás derechos reales. Su finalidad es establecer un marco jurídico que garantice el orden y la justicia en las relaciones civiles, asegurando el respeto a los derechos y deberes de cada persona dentro de la sociedad.</p>
            </div>
            <div class="md:w-1/3 flex flex-col items-center text-center mt-4 md:mt-0">
                <ul class="space-y-2">
                    <li><i class="fas fa-check-circle text-yellow-500"></i> Obligaciones y contratos</li>
                    <li><i class="fas fa-check-circle text-yellow-500"></i> Drechos reales como hipotecas</li>
                    <li><i class="fas fa-check-circle text-yellow-500"></i> Sucesiones, herencias</li>
                    <li><i class="fas fa-check-circle text-yellow-500"></i> Responsabilidad civil</li>
                </ul>
            </div>
        </section>

        
        
        <!-- Nuevo apartado de comentarios de clientes con slider -->
        <section class="mt-8 bg-gray-800 p-8 rounded-md" data-aos="fade-up" data-aos-delay="500">
            <h2 class="text-3xl font-bold text-center">Comentarios de Clientes</h2>
            <div class="slick-slider mt-6">
                <div class="flex items-center p-4 bg-gray-700 rounded-md">
                    <img src="src/img/familiar1.jpg" alt="Foto de María González" class="w-24 h-24 rounded-full mr-4">
                    <div>
                        <p class="text-lg">"Excelente servicio en Derecho Civil. El equipo fue muy profesional y eficiente en la resolución de nuestro caso. Nos sentimos muy bien asesorados y confiados en cada paso del proceso."


                        </p>
                        <p class="mt-2 text-yellow-500 font-bold">- María González</p>
                    </div>
                </div>
                <div class="flex items-center p-4 bg-gray-700 rounded-md">
                    <img src="src/img/familiar2.jpg" alt="Foto de Juan Pérez" class="w-24 h-24 rounded-full mr-4">
                    <div>
                        <p class="text-lg">"Nos ayudaron con la redacción y revisión de contratos de manera rápida y sin complicaciones. Su atención al detalle y conocimiento del proceso legal hicieron que todo fuera mucho más fácil para nosotros."

                        </p>
                        <p class="mt-2 text-yellow-500 font-bold">- Juan Pérez</p>
                    </div>
                </div>
                <div class="flex items-center p-4 bg-gray-700 rounded-md">
                    <img src="src/img/familiar3.jpg" alt="Foto de Laura Martínez" class="w-24 h-24 rounded-full mr-4">
                    <div>
                        <p class="text-lg">"Muy satisfecho con el asesoramiento en la gestión de propiedades. Los abogados fueron claros y pacientes, explicándonos cada detalle y asegurándose de que todo estuviera en orden."

                        </p>
                        <p class="mt-2 text-yellow-500 font-bold">- Laura Martínez</p>
                    </div>
                </div>
                <div class="flex items-center p-4 bg-gray-700 rounded-md">
                    <img src="src/img/familiar4.jpg" alt="Foto de Carlos López" class="w-24 h-24 rounded-full mr-4">
                    <div>
                        <p class="text-lg">"Gran atención al detalle y compromiso en la resolución de disputas civiles. Su profesionalismo nos dio la tranquilidad de que nuestros intereses estaban bien protegidos."</p>
                        <p class="mt-2 text-yellow-500 font-bold">- Carlos López</p>
                    </div>
                </div>
            </div>
        </section>
        <br><br><br>

    </main>
   


    <!-- Comienza el footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
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
    <!-- Termina el footer -->


    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&callback=initMap" async defer></script>
    <script src="src/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            once: false // Permitir animaciones en cada scroll sin necesidad de actualizar
        });
    </script>
    
    <!-- Slick JS for slider -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script>
        $(document).ready(function(){
            $('.slick-slider').slick({
                dots: true,
                infinite: true,
                speed: 500,
                slidesToShow: 3,
                slidesToScroll: 3,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true,
                prevArrow: "<button type="button" class="slick-prev text-yellow-500"><i class="fas fa-chevron-left"></i></button>",
                nextArrow: "<button type="button" class="slick-next text-yellow-500"><i class="fas fa-chevron-right"></i></button>"
            });
        });
    </script>

</body>
</html>


