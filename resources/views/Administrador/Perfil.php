<?php
require_once dirname(__DIR__, 3) . '/app/Models/Usuario.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /IniciarSesion.php');
    exit();
}

require_once dirname(__DIR__, 3) . '/config/app.php';
$base_url = app_base_url();

$profilePageTitle = 'Perfil del administrador';
$profileHomeUrl = $base_url . 'Administrador/PanelPrincipal.php';
$profileBackUrl = '';
$profileBackLabel = '';
$profileNavItems = [
    ['label' => 'Inicio', 'href' => $base_url . 'Administrador/PanelPrincipal.php', 'active' => false],
    ['label' => 'Actividad', 'href' => $base_url . 'Administrador/Actividad.php', 'active' => false],
    ['label' => 'Clientes', 'href' => $base_url . 'Administrador/GestionClientes.php', 'active' => false],
    ['label' => 'Abogados', 'href' => $base_url . 'Administrador/GestionAbogados.php', 'active' => false],
    ['label' => 'Administradores', 'href' => $base_url . 'Administrador/GestionAdministradores.php', 'active' => false],
];

include __DIR__ . '/../Compartido/PlantillaPerfil.php';
