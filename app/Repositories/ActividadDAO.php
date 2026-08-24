<?php
require_once dirname(__DIR__) . '/Helpers/Conexion.php';

class ActividadDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS admin_actividad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            administrador_id INT NULL,
            usuario_id INT NULL,
            rol ENUM('administrador', 'abogado') NOT NULL DEFAULT 'administrador',
            fecha DATE NOT NULL,
            tipo ENUM('nota', 'recordatorio') NOT NULL DEFAULT 'nota',
            titulo VARCHAR(120) NOT NULL,
            detalle TEXT NULL,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_actividad_fecha (fecha),
            INDEX idx_admin_actividad_tipo (tipo),
            INDEX idx_admin_actividad_usuario (usuario_id),
            INDEX idx_admin_actividad_rol (rol),
            CONSTRAINT fk_admin_actividad_administrador
                FOREIGN KEY (administrador_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $this->conn->query($sql);
        $this->ensureColumn('usuario_id', 'INT NULL AFTER administrador_id');
        $this->ensureColumn('rol', "ENUM('administrador', 'abogado') NOT NULL DEFAULT 'administrador' AFTER usuario_id");
        $this->ensureIndex('idx_admin_actividad_usuario', 'usuario_id');
        $this->ensureIndex('idx_admin_actividad_rol', 'rol');
    }

    private function columnExists(string $column): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'admin_actividad'
               AND COLUMN_NAME = ?"
        );
        $stmt->bind_param('s', $column);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    private function ensureColumn(string $column, string $definition): void
    {
        if (!$this->columnExists($column)) {
            $this->conn->query("ALTER TABLE admin_actividad ADD COLUMN {$column} {$definition}");
        }
    }

    private function indexExists(string $index): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'admin_actividad'
               AND INDEX_NAME = ?"
        );
        $stmt->bind_param('s', $index);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    private function ensureIndex(string $index, string $column): void
    {
        if (!$this->indexExists($index)) {
            $this->conn->query("ALTER TABLE admin_actividad ADD INDEX {$index} ({$column})");
        }
    }

    public function contarNotasPorDia(string $fecha): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM admin_actividad WHERE fecha = ? AND tipo = 'nota'");
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    private function existeDuplicadoReciente(int $usuarioId, string $fecha, string $tipo, string $titulo, string $detalle): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM admin_actividad
             WHERE usuario_id = ?
               AND fecha = ?
               AND tipo = ?
               AND titulo = ?
               AND COALESCE(detalle, '') = ?
               AND creado_en >= DATE_SUB(NOW(), INTERVAL 10 SECOND)"
        );
        $stmt->bind_param('issss', $usuarioId, $fecha, $tipo, $titulo, $detalle);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    public function crear(int $usuarioId, string $fecha, string $tipo, string $titulo, string $detalle = '', string $rol = 'administrador'): array
    {
        $tipo = $tipo === 'recordatorio' ? 'recordatorio' : 'nota';
        $rol = $rol === 'abogado' ? 'abogado' : 'administrador';
        $administradorId = $rol === 'administrador' ? $usuarioId : null;
        $titulo = trim($titulo);
        $detalle = trim($detalle);

        if ($titulo === '') {
            return ['ok' => false, 'message' => 'El titulo es obligatorio.'];
        }

        if (strlen($titulo) > 120) {
            return ['ok' => false, 'message' => 'El titulo no puede superar 120 caracteres.'];
        }

        if ($tipo === 'nota' && $this->contarNotasPorDia($fecha) >= 3) {
            return ['ok' => false, 'message' => 'Solo se pueden crear hasta 3 notas por dia.'];
        }

        if ($this->existeDuplicadoReciente($usuarioId, $fecha, $tipo, $titulo, $detalle)) {
            return ['ok' => false, 'message' => 'Esta actividad ya fue registrada hace un momento.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO admin_actividad (administrador_id, usuario_id, rol, fecha, tipo, titulo, detalle)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iisssss', $administradorId, $usuarioId, $rol, $fecha, $tipo, $titulo, $detalle);
        $stmt->execute();

        return ['ok' => true, 'id' => (int) $this->conn->insert_id];
    }

    public function obtenerPorDia(string $fecha): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS administrador
             FROM admin_actividad a
             LEFT JOIN usuarios u ON u.id = COALESCE(a.usuario_id, a.administrador_id)
             WHERE a.fecha = ?
             ORDER BY a.tipo ASC, a.creado_en DESC, a.id DESC"
        );
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS administrador
             FROM admin_actividad a
             LEFT JOIN usuarios u ON u.id = COALESCE(a.usuario_id, a.administrador_id)
             WHERE a.id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function eliminarNota(int $id, int $usuarioId): array
    {
        $actividad = $this->obtenerPorId($id);
        if (!$actividad || $actividad['tipo'] !== 'nota' || (int) $actividad['usuario_id'] !== $usuarioId) {
            return ['ok' => false, 'message' => 'No se encontro la nota que deseas eliminar.'];
        }

        $stmt = $this->conn->prepare("DELETE FROM admin_actividad WHERE id = ? AND tipo = 'nota' AND usuario_id = ?");
        $stmt->bind_param('ii', $id, $usuarioId);
        $stmt->execute();

        return [
            'ok' => $stmt->affected_rows > 0,
            'message' => $stmt->affected_rows > 0 ? 'Nota eliminada.' : 'No se pudo eliminar la nota.',
            'actividad' => $actividad,
        ];
    }

    public function obtenerConteoNotasPorMes(int $year, int $month): array
    {
        $stmt = $this->conn->prepare(
            "SELECT fecha, COUNT(*) AS total
             FROM admin_actividad
             WHERE tipo = 'nota' AND YEAR(fecha) = ? AND MONTH(fecha) = ?
             GROUP BY fecha"
        );
        $stmt->bind_param('ii', $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[$row['fecha']] = (int) $row['total'];
        }

        return $data;
    }

    public function obtenerConteoNotasPorRango(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT fecha, COUNT(*) AS total
             FROM admin_actividad
             WHERE tipo = 'nota' AND fecha BETWEEN ? AND ?
             GROUP BY fecha"
        );
        $stmt->bind_param('ss', $inicio, $fin);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[$row['fecha']] = (int) $row['total'];
        }

        return $data;
    }

    public function obtenerConteoRecordatoriosPorRango(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT fecha, COUNT(*) AS total
             FROM admin_actividad
             WHERE tipo = 'recordatorio' AND fecha BETWEEN ? AND ?
             GROUP BY fecha"
        );
        $stmt->bind_param('ss', $inicio, $fin);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[$row['fecha']] = (int) $row['total'];
        }

        return $data;
    }

    public function obtenerRecordatoriosProximos(int $limite = 12): array
    {
        $this->eliminarRecordatoriosVencidos();
        $limite = max(1, min(30, $limite));
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM admin_actividad
             WHERE tipo = 'recordatorio'
               AND fecha >= DATE(CONVERT_TZ(NOW(), '+00:00', '-05:00'))
             ORDER BY fecha ASC, creado_en DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function eliminarRecordatoriosVencidos(): void
    {
        $this->conn->query(
            "DELETE FROM admin_actividad
             WHERE tipo = 'recordatorio'
               AND fecha < DATE(CONVERT_TZ(NOW(), '+00:00', '-05:00'))"
        );
    }
}
