<?php
require_once 'conexion.php';

class TestConexion {
    public function verificarConexion() {
        $conexion = new Conexion();
        $conn = $conexion->getConnection();

        if ($conn) {
            echo "Conexión exitosa a la base de datos.";
        } else {
            echo "Error al conectar a la base de datos.";
        }
    }
}

$test = new TestConexion();
$test->verificarConexion();
?>
