<?php
require_once 'model/Usuario.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'abogado') {
    header('Location: /IniciarSesion.php');
    exit();
}

require_once __DIR__ . '/config/app.php';
$base_url = app_base_url();

$profilePageTitle = 'Perfil del abogado';
$profileHomeUrl = $base_url . 'Abogado/PanelPrincipal.php';
$profileBackUrl = $base_url . 'Abogado/PanelPrincipal.php';
$profileBackLabel = 'Volver al panel';
$profileNavItems = [
    ['label' => 'Inicio', 'href' => $base_url . 'Abogado/PanelPrincipal.php', 'active' => false],
    ['label' => 'Perfil', 'href' => $base_url . 'Abogado/Perfil.php', 'active' => true],
    ['label' => 'Citas', 'href' => $base_url . 'Abogado/Citas.php', 'active' => false],
];

include __DIR__ . '/../Compartido/PlantillaPerfil.php';
