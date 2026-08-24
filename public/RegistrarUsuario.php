<?php
$projectRoot = dirname(__DIR__);
chdir($projectRoot);
set_include_path($projectRoot . '/app/Legacy' . PATH_SEPARATOR . get_include_path());
require_once dirname(__DIR__) . '/resources/views/Autenticacion/RegistrarUsuario.php';
?>
