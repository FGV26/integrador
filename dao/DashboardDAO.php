<?php
require_once dirname(__DIR__) . '/conexiones/conexion.php';

class DashboardDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function obtenerAniosDisponibles()
    {
        $sql = "SELECT DISTINCT YEAR(fecha) as year FROM ingresos UNION SELECT DISTINCT YEAR(fecha) FROM perdidas ORDER BY year";
        $result = $this->conn->query($sql);

        $years = [];
        while ($row = $result->fetch_assoc()) {
            $years[] = $row['year'];
        }

        return $years;
    }

    public function obtenerDatosFiltrados($anio, $mes)
    {
        $params = [];
        $sql = "SELECT fecha, COUNT(*) as totalCitas, 
                (SELECT SUM(monto) FROM ingresos WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?) as totalIngresos, 
                (SELECT SUM(monto) FROM perdidas WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?) as totalPerdidas 
                FROM citas WHERE YEAR(fecha) = ? AND MONTH(fecha) = ? GROUP BY fecha";

        array_push($params, $anio, $mes, $anio, $mes, $anio, $mes);

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            die("Error en la preparación de la consulta: " . $this->conn->error);
        }

        $stmt->bind_param('iiiiii', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = [];

        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }

        return $datos;
    }

    public function obtenerTotalIngresosPorMes($year, $month)
    {
        $sql = "SELECT ROUND(SUM(monto), 2) AS total FROM ingresos WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ? round($row['total'], 2) : 0;
    }

    public function obtenerTotalPerdidasPorMes($year, $month)
    {
        $sql = "SELECT ROUND(SUM(monto), 2) AS total FROM perdidas WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ? round($row['total'], 2) : 0;
    }

    public function obtenerCantidadIngresosPorMes($year, $month)
    {
        $sql = "SELECT COUNT(*) AS cantidad FROM ingresos WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['cantidad'];
    }

    public function obtenerCantidadPerdidasPorMes($year, $month)
    {
        $sql = "SELECT COUNT(*) AS cantidad FROM perdidas WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['cantidad'];
    }

    public function obtenerCantidadCitasPorMes($year, $month)
    {
        $sql = "SELECT COUNT(*) AS cantidad FROM citas WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['cantidad'];
    }

    public function obtenerCantidadIngresosPorAnio($year)
    {
        $sql = "SELECT MONTH(fecha) as mes, COUNT(*) as cantidad FROM ingresos WHERE YEAR(fecha) = ? GROUP BY MONTH(fecha)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array_fill(0, 12, 0); // Inicializar con ceros para los 12 meses
        while ($row = $result->fetch_assoc()) {
            $data[$row['mes'] - 1] = $row['cantidad'];
        }
        return $data;
    }

    public function obtenerCantidadPerdidasPorAnio($year)
    {
        $sql = "SELECT MONTH(fecha) as mes, COUNT(*) as cantidad FROM perdidas WHERE YEAR(fecha) = ? GROUP BY MONTH(fecha)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array_fill(0, 12, 0); // Inicializar con ceros para los 12 meses
        while ($row = $result->fetch_assoc()) {
            $data[$row['mes'] - 1] = $row['cantidad'];
        }
        return $data;
    }

    public function obtenerMontoIngresosPorAnio($year)
    {
        $sql = "SELECT MONTH(fecha) as mes, SUM(monto) as total FROM ingresos WHERE YEAR(fecha) = ? GROUP BY MONTH(fecha)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array_fill(0, 12, 0); // Inicializar con ceros para los 12 meses
        while ($row = $result->fetch_assoc()) {
            $data[$row['mes'] - 1] = round($row['total']);
        }
        return $data;
    }

    public function obtenerMontoPerdidasPorAnio($year)
    {
        $sql = "SELECT MONTH(fecha) as mes, SUM(monto) as total FROM perdidas WHERE YEAR(fecha) = ? GROUP BY MONTH(fecha)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array_fill(0, 12, 0); // Inicializar con ceros para los 12 meses
        while ($row = $result->fetch_assoc()) {
            $data[$row['mes'] - 1] = round($row['total']);
        }
        return $data;
    }
}
?>
    