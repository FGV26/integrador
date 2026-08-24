<?php
require_once dirname(__DIR__) . '/Helpers/Conexion.php';

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

    public function obtenerDiasConCitasPorMes($year, $month)
    {
        $sql = "SELECT fecha, COUNT(*) AS citas, COUNT(DISTINCT cliente_id) AS clientes
                FROM citas
                WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?
                GROUP BY fecha";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[$row['fecha']] = [
                'citas' => (int) $row['citas'],
                'clientes' => (int) $row['clientes'],
            ];
        }

        return $data;
    }

    public function obtenerResumenPorDia($fecha)
    {
        $sql = "SELECT
                    COUNT(*) AS citas,
                    SUM(CASE WHEN estado = 'terminado' THEN 1 ELSE 0 END) AS terminadas,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) AS canceladas
                FROM citas
                WHERE fecha = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $sqlIngresos = "SELECT ROUND(SUM(monto), 2) AS total FROM ingresos WHERE fecha = ?";
        $stmtIngresos = $this->conn->prepare($sqlIngresos);
        $stmtIngresos->bind_param('s', $fecha);
        $stmtIngresos->execute();
        $ingresos = $stmtIngresos->get_result()->fetch_assoc();

        $sqlPerdidas = "SELECT ROUND(SUM(monto), 2) AS total FROM perdidas WHERE fecha = ?";
        $stmtPerdidas = $this->conn->prepare($sqlPerdidas);
        $stmtPerdidas->bind_param('s', $fecha);
        $stmtPerdidas->execute();
        $perdidas = $stmtPerdidas->get_result()->fetch_assoc();

        return [
            'fecha' => $fecha,
            'citas' => (int) ($row['citas'] ?? 0),
            'terminadas' => (int) ($row['terminadas'] ?? 0),
            'canceladas' => (int) ($row['canceladas'] ?? 0),
            'ingresos' => $ingresos['total'] ? round((float) $ingresos['total'], 2) : 0,
            'perdidas' => $perdidas['total'] ? round((float) $perdidas['total'], 2) : 0,
        ];
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

    public function obtenerBalanceDiarioPorMes($year, $month)
    {
        $diasDelMes = (int) (new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
        $ingresos = array_fill(0, $diasDelMes, 0);
        $perdidas = array_fill(0, $diasDelMes, 0);

        $sqlIngresos = "SELECT DAY(fecha) as dia, SUM(monto) as total
                        FROM ingresos
                        WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?
                        GROUP BY DAY(fecha)";
        $stmtIngresos = $this->conn->prepare($sqlIngresos);
        $stmtIngresos->bind_param('ii', $year, $month);
        $stmtIngresos->execute();
        $resultIngresos = $stmtIngresos->get_result();

        while ($row = $resultIngresos->fetch_assoc()) {
            $ingresos[(int) $row['dia'] - 1] = round((float) $row['total'], 2);
        }

        $sqlPerdidas = "SELECT DAY(fecha) as dia, SUM(monto) as total
                        FROM perdidas
                        WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?
                        GROUP BY DAY(fecha)";
        $stmtPerdidas = $this->conn->prepare($sqlPerdidas);
        $stmtPerdidas->bind_param('ii', $year, $month);
        $stmtPerdidas->execute();
        $resultPerdidas = $stmtPerdidas->get_result();

        while ($row = $resultPerdidas->fetch_assoc()) {
            $perdidas[(int) $row['dia'] - 1] = round((float) $row['total'], 2);
        }

        return [
            'labels' => range(1, $diasDelMes),
            'ingresos' => $ingresos,
            'perdidas' => $perdidas,
        ];
    }

    public function obtenerCantidadCitasPorAnio($year)
    {
        $sql = "SELECT MONTH(fecha) AS mes, COUNT(*) AS cantidad FROM citas WHERE YEAR(fecha) = ? GROUP BY MONTH(fecha)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = array_fill(0, 12, 0);
        while ($row = $result->fetch_assoc()) {
            $data[(int) $row['mes'] - 1] = (int) $row['cantidad'];
        }
        return $data;
    }

    public function obtenerTotalCitas()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM citas");
        return (int) $result->fetch_assoc()['total'];
    }

    public function obtenerCitasPorEstado()
    {
        $data = ['pendiente' => 0, 'confirmada' => 0, 'cancelada' => 0, 'terminado' => 0];
        $result = $this->conn->query("SELECT estado, COUNT(*) AS total FROM citas GROUP BY estado");
        while ($row = $result->fetch_assoc()) {
            $data[$row['estado']] = (int) $row['total'];
        }
        return $data;
    }

    public function obtenerCitasPorEstadoPorMes($year, $month)
    {
        $data = ['pendiente' => 0, 'confirmada' => 0, 'cancelada' => 0, 'terminado' => 0];
        $sql = "SELECT estado, COUNT(*) AS total
                FROM citas
                WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?
                GROUP BY estado";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $data[$row['estado']] = (int) $row['total'];
        }

        return $data;
    }
}
