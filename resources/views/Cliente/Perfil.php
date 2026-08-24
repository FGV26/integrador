<?php
require_once 'model/Usuario.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']->getRol() !== 'cliente') {
    header('Location: /IniciarSesion.php');
    exit();
}

require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();

$profilePageTitle = 'Mi perfil';
$profileHomeUrl = $base_url . 'index.php';
$profileBackUrl = $base_url . 'index.php';
$profileBackLabel = 'Volver al inicio';
$profileNavItems = [
    ['label' => 'Perfil', 'href' => $base_url . 'Cliente/Perfil.php', 'active' => true],
    ['label' => 'Mis citas', 'href' => $base_url . 'Cliente/Citas.php', 'active' => false],
    ['label' => 'Agendar cita', 'href' => $base_url . 'Cliente/SolicitarCita.php', 'active' => false],
];

include __DIR__ . '/../Compartido/PlantillaPerfil.php';
