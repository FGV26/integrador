<?php
require_once dirname(__DIR__) . '/conexiones/conexion.php';
require_once dirname(__DIR__) . '/model/Perdida.php';

class PerdidaDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function crear($perdida)
    {
        $sql = "INSERT INTO perdidas (cita_id, monto) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);

        $citaId = $perdida->getCitaId();
        $monto = $perdida->getMonto();

        $stmt->bind_param('id', $citaId, $monto);

        if ($stmt->execute()) {
            return true;
        } else {
            echo "Error en la ejecución de la declaración: " . $stmt->error;
            return false;
        }
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM perdidas";
        $result = $this->conn->query($sql);
        $perdidas = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $perdida = new Perdida();
                $perdida->setId($row['id']);
                $perdida->setCitaId($row['cita_id']);
                $perdida->setMonto($row['monto']);
                $perdida->setFecha($row['fecha']);
                $perdidas[] = $perdida;
            }
        }

        return $perdidas;
    }

}
?>
