<?php
require_once dirname(__DIR__, 3) . '/config/app.php';
header('Location: ' . app_base_url() . 'Administrador/PanelPrincipal.php');
exit();
