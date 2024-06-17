<?php
require_once 'conexiones/conexion.php';
require_once 'model/TipoDeCaso.php';

class TipoDeCasoDAO {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function crear($tipoDeCaso) {
        $sql = "INSERT INTO tipos_de_caso (tipo) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $tipoDeCaso->getTipo());
        return $stmt->execute();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM tipos_de_caso WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $tipoDeCaso = new TipoDeCaso();
            $tipoDeCaso->setId($row['id']);
            $tipoDeCaso->setTipo($row['tipo']);
            return $tipoDeCaso;
        }

        return null;
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM tipos_de_caso";
        $result = $this->conn->query($sql);
        $tiposDeCasos = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tipoDeCaso = new TipoDeCaso();
                $tipoDeCaso->setId($row['id']);
                $tipoDeCaso->setTipo($row['tipo']);
                $tiposDeCasos[] = $tipoDeCaso;
            }
        }

        return $tiposDeCasos;
    }

    public function actualizar($tipoDeCaso) {
        $sql = "UPDATE tipos_de_caso SET tipo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $tipoDeCaso->getTipo(), $tipoDeCaso->getId());
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM tipos_de_caso WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
?>
