<?php
require_once dirname(__DIR__) . '/conexiones/conexion.php';
require_once dirname(__DIR__) . '/model/Ingreso.php';

class IngresoDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function crear($ingreso) {
        $sql = "INSERT INTO ingresos (cita_id, monto) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            echo "Error en la preparación de la declaración: " . $this->conn->error;
            return false;
        }

        $citaId = $ingreso->getCitaId();
        $monto = $ingreso->getMonto();

        $stmt->bind_param('id', $citaId, $monto);

        if ($stmt->execute()) {
            return true;
        } else {
            echo "Error en la ejecución de la declaración: " . $stmt->error;
            return false;
        }
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM ingresos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $ingreso = new Ingreso();
            $ingreso->setId($row['id']);
            $ingreso->setCitaId($row['cita_id']);
            $ingreso->setMonto($row['monto']);
            $ingreso->setFecha($row['fecha']);
            return $ingreso;
        }

        return null;
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM ingresos";
        $result = $this->conn->query($sql);
        $ingresos = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ingreso = new Ingreso();
                $ingreso->setId($row['id']);
                $ingreso->setCitaId($row['cita_id']);
                $ingreso->setMonto($row['monto']);
                $ingreso->setFecha($row['fecha']);
                $ingresos[] = $ingreso;
            }
        }

        return $ingresos;
    }

    public function actualizar($ingreso) {
        $sql = "UPDATE ingresos SET cita_id = ?, monto = ?, fecha = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        $citaId = $ingreso->getCitaId();
        $monto = $ingreso->getMonto();
        $fecha = $ingreso->getFecha();
        $id = $ingreso->getId();

        $stmt->bind_param('idssi', $citaId, $monto, $fecha, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo "Error en la actualización: " . $stmt->error;
            return false;
        }
    }

    public function eliminar($id) {
        $sql = "DELETE FROM ingresos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

}
?>
