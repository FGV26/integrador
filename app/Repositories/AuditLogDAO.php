<?php
require_once dirname(__DIR__) . '/Helpers/Conexion.php';

class AuditLogDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
    }

    public function registrar($administradorId, $accion, $entidad, $entidadId, $detalle)
    {
        $sql = "INSERT INTO audit_logs (administrador_id, accion, entidad, entidad_id, detalle)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('issis', $administradorId, $accion, $entidad, $entidadId, $detalle);
        return $stmt->execute();
    }

    public function obtenerRecientes($limite = 8)
    {
        $limite = max(1, min(50, (int) $limite));
        $sql = "SELECT l.*, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS administrador
                FROM audit_logs l
                LEFT JOIN usuarios u ON u.id = l.administrador_id
                ORDER BY l.creado_en DESC, l.id DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorDia(string $fecha, int $limite = 30): array
    {
        $limite = max(1, min(80, $limite));
        $desde = $fecha . ' 00:00:00';
        $hasta = $fecha . ' 23:59:59';
        $sql = "SELECT l.*, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS administrador
                FROM audit_logs l
                LEFT JOIN usuarios u ON u.id = l.administrador_id
                WHERE l.creado_en BETWEEN ? AND ?
                ORDER BY l.creado_en DESC, l.id DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $desde, $hasta, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
