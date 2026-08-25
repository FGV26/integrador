<?php
require_once dirname(__DIR__) . '/Helpers/Conexion.php';
require_once dirname(__DIR__) . '/Models/Cita.php';
require_once dirname(__DIR__) . '/Models/Usuario.php';

class CitaDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
        $this->ensureAtencionSchema();
    }

    public function crear($cita)
    {
        $sql = "INSERT INTO citas (cliente_id, abogado_id, fecha, hora, tipo_de_caso_id, mensaje, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $clienteId = $cita->getClienteId();
        $abogadoId = $cita->getAbogadoId();
        $fecha = $cita->getFecha();
        $hora = $cita->getHora();
        $tipoDeCasoId = $cita->getTipoDeCasoId();
        $mensaje = $cita->getMensaje();
        $estado = $cita->getEstado();

        $stmt->bind_param(
            'iisssss',
            $clienteId,
            $abogadoId,
            $fecha,
            $hora,
            $tipoDeCasoId,
            $mensaje,
            $estado
        );

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM citas";
        $result = $this->conn->query($sql);
        $citas = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cita = new Cita();
                $cita->setId($row['id']);
                $cita->setClienteId($row['cliente_id']);
                $cita->setAbogadoId($row['abogado_id']);
                $cita->setFecha($row['fecha']);
                $cita->setHora($row['hora']);
                $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
                $cita->setMensaje($row['mensaje']);
                $cita->setEstado($row['estado']);
                $citas[] = $cita;
            }
        }

        return $citas;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM citas WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            return $this->mapearCita($result->fetch_assoc());
        }

        return null;
    }

    public function actualizar($cita)
    {
        $sql = "UPDATE citas SET cliente_id = ?, abogado_id = ?, fecha = ?, hora = ?, tipo_de_caso_id = ?, mensaje = ?, estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        $clienteId = $cita->getClienteId();
        $abogadoId = $cita->getAbogadoId();
        $fecha = $cita->getFecha();
        $hora = $cita->getHora();
        $tipoDeCasoId = $cita->getTipoDeCasoId();
        $mensaje = $cita->getMensaje();
        $estado = $cita->getEstado();
        $id = $cita->getId();

        $stmt->bind_param('iisssssi', $clienteId, $abogadoId, $fecha, $hora, $tipoDeCasoId, $mensaje, $estado, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM citas WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function obtenerAbogados()
    {
        $sql = "SELECT * FROM usuarios WHERE rol = 'abogado'";
        $result = $this->conn->query($sql);
        $abogados = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $abogado = new Usuario();
                $abogado->setId($row['id']);
                $abogado->setNombre($row['nombre']);
                $abogado->setApellidoPaterno($row['apellido_paterno']);
                $abogado->setApellidoMaterno($row['apellido_materno']);
                $abogados[] = $abogado;
            }
        }

        return $abogados;
    }

    public function obtenerCitasPorFecha($fecha)
    {
        $sql = "SELECT hora FROM citas WHERE fecha = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $result = $stmt->get_result();

        $horas = [];
        while ($row = $result->fetch_assoc()) {
            $horas[] = $row['hora'];
        }

        return $horas;
    }

    public function obtenerCitasPorCliente($clienteId)
    {
        $sql = "SELECT * FROM citas WHERE cliente_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $clienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $citas = [];

        while ($row = $result->fetch_assoc()) {
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            $citas[] = $cita;
        }

        return $citas;
    }

    public function obtenerCitasPorAbogado($abogadoId)
    {
        $sql = "SELECT * FROM citas WHERE abogado_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $abogadoId);
        $stmt->execute();
        $result = $stmt->get_result();

        $citas = [];
        while ($row = $result->fetch_assoc()) {
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            $citas[] = $cita;
        }

        return $citas;
    }

    public function cancelarCita($id)
    {
        $sql = "UPDATE citas SET estado = 'cancelada' WHERE id = ? AND estado = 'pendiente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function obtenerCitasActivasPorCliente($clienteId)
    {
        $sql = "SELECT * FROM citas WHERE cliente_id = ? AND estado IN ('pendiente', 'confirmada', 'en_atencion')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $clienteId);
        $stmt->execute();
        $result = $stmt->get_result();
        $citas = [];

        while ($row = $result->fetch_assoc()) {
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            $citas[] = $cita;
        }

        return $citas;
    }

    public function obtenerCitasActivasPorAbogado($abogadoId)
    {
        $this->cerrarAtencionesVencidasPorAbogado((int) $abogadoId);
        $sql = "SELECT * FROM citas WHERE abogado_id = ? AND estado IN ('pendiente', 'confirmada', 'en_atencion')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $abogadoId);
        $stmt->execute();
        $result = $stmt->get_result();

        $citas = [];
        while ($row = $result->fetch_assoc()) {
            $cita = new Cita();
            $cita->setId($row['id']);
            $cita->setClienteId($row['cliente_id']);
            $cita->setAbogadoId($row['abogado_id']);
            $cita->setFecha($row['fecha']);
            $cita->setHora($row['hora']);
            $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
            $cita->setMensaje($row['mensaje']);
            $cita->setEstado($row['estado']);
            $citas[] = $cita;
        }

        return $citas;
    }

    public function confirmarCita($id)
    {
        $sql = "UPDATE citas SET estado = 'confirmada', fecha_confirmacion = COALESCE(fecha_confirmacion, NOW()) WHERE id = ? AND estado = 'pendiente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function terminarCita($id)
    {
        $sql = "UPDATE citas SET estado = 'terminado', hora_fin_at = COALESCE(hora_fin_at, NOW()) WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function cancelarCitaPorAbogado($id, $motivo = '')
    {
        $sql = "UPDATE citas SET estado = 'cancelada', fecha_cancelacion = COALESCE(fecha_cancelacion, NOW()), motivo_cancelacion = ? WHERE id = ? AND estado <> 'terminado'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $motivo, $id);
        return $stmt->execute();
    }

    public function horarioDisponible($fecha, $hora, $excluirCitaId = 0)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM citas
                WHERE fecha = ?
                  AND hora = ?
                  AND id <> ?
                  AND estado IN ('pendiente', 'confirmada', 'en_atencion')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $fecha, $hora, $excluirCitaId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['total'] ?? 0) === 0;
    }

    public function reajustarCitaPorAbogado($id, $abogadoId, $fecha, $hora)
    {
        $sql = "UPDATE citas
                SET fecha = ?,
                    hora = ?,
                    fecha_confirmacion = NULL,
                    hora_inicio_at = NULL,
                    hora_fin_at = NULL,
                    fecha_atencion = NULL
                WHERE id = ?
                  AND abogado_id = ?
                  AND estado IN ('pendiente', 'confirmada')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssii', $fecha, $hora, $id, $abogadoId);
        return $stmt->execute();
    }

    public function crearAvisoCliente($clienteId, $citaId, $tipo, $titulo, $mensaje)
    {
        $sql = "INSERT INTO cita_avisos_cliente (cliente_id, cita_id, tipo, titulo, mensaje) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('iisss', $clienteId, $citaId, $tipo, $titulo, $mensaje);
        return $stmt->execute();
    }

    public function obtenerAvisosCliente($clienteId)
    {
        $sql = "SELECT *
                FROM cita_avisos_cliente
                WHERE cliente_id = ? AND oculto_cliente = 0
                ORDER BY creado_en DESC, id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $clienteId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function ocultarAvisoCliente($avisoId, $clienteId)
    {
        $sql = "UPDATE cita_avisos_cliente
                SET oculto_cliente = 1, ocultado_en = NOW()
                WHERE id = ? AND cliente_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $avisoId, $clienteId);
        return $stmt->execute();
    }

    public function iniciarAtencion($id, $abogadoId)
    {
        $sql = "UPDATE citas
                SET estado = 'en_atencion',
                    hora_inicio_at = COALESCE(hora_inicio_at, NOW()),
                    fecha_atencion = COALESCE(fecha_atencion, NOW())
                WHERE id = ? AND abogado_id = ? AND estado = 'confirmada'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $id, $abogadoId);
        if (!$stmt->execute()) {
            return false;
        }

        if ($stmt->affected_rows > 0) {
            return true;
        }

        $cita = $this->obtenerPorId($id);
        return $cita && (int) $cita->getAbogadoId() === (int) $abogadoId && $cita->getEstado() === 'en_atencion';
    }

    public function finalizarAtencion($id, $abogadoId, $observacionFinal, $requiereNuevaCita, $requiereCambioEspecialidad)
    {
        $observacionFinal = trim((string) $observacionFinal);
        $requiereNuevaCita = $requiereNuevaCita ? 1 : 0;
        $requiereCambioEspecialidad = $requiereCambioEspecialidad ? 1 : 0;
        $sql = "UPDATE citas
                SET estado = 'terminado',
                    hora_fin_at = NOW(),
                    observacion_final = ?,
                    requiere_nueva_cita = ?,
                    requiere_cambio_especialidad = ?
                WHERE id = ? AND abogado_id = ? AND estado = 'en_atencion'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('siiii', $observacionFinal, $requiereNuevaCita, $requiereCambioEspecialidad, $id, $abogadoId);
        return $stmt->execute();
    }

    public function cerrarAtencionesVencidasPorAbogado($abogadoId)
    {
        $nota = 'Cierre automatico por tiempo cumplido.';
        $sql = "UPDATE citas
                SET estado = 'terminado',
                    hora_fin_at = DATE_ADD(hora_inicio_at, INTERVAL 40 MINUTE),
                    observacion_final = IF(observacion_final IS NULL OR observacion_final = '', ?, observacion_final)
                WHERE abogado_id = ?
                  AND estado = 'en_atencion'
                  AND hora_inicio_at IS NOT NULL
                  AND DATE_ADD(hora_inicio_at, INTERVAL 40 MINUTE) <= NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $nota, $abogadoId);
        return $stmt->execute();
    }

    public function obtenerNotasAbogado($citaId)
    {
        $stmt = $this->conn->prepare(
            "SELECT n.*, CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno) AS abogado
             FROM cita_notas_abogado n
             LEFT JOIN usuarios u ON u.id = n.abogado_id
             WHERE n.cita_id = ?
             ORDER BY n.creado_en DESC, n.id DESC"
        );
        $stmt->bind_param('i', $citaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarNotaAbogado($citaId, $abogadoId, $nota)
    {
        $nota = trim((string) $nota);
        if ($nota === '') {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO cita_notas_abogado (cita_id, abogado_id, nota) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $citaId, $abogadoId, $nota);
        return $stmt->execute();
    }

    public function obtenerDocumentosCita($citaId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cita_documentos WHERE cita_id = ? ORDER BY creado_en DESC, id DESC");
        $stmt->bind_param('i', $citaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarDocumento($citaId, $clienteId, $archivo, $nombreOriginal)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO cita_documentos (cita_id, cliente_id, archivo, nombre_original) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiss', $citaId, $clienteId, $archivo, $nombreOriginal);
        return $stmt->execute();
    }

    public function obtenerHistorialCliente($clienteId, $citaActualId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, t.tipo AS tipo_caso, CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS abogado
             FROM citas c
             LEFT JOIN tipos_de_caso t ON t.id = c.tipo_de_caso_id
             LEFT JOIN usuarios a ON a.id = c.abogado_id
             WHERE c.cliente_id = ? AND c.id <> ?
             ORDER BY c.fecha DESC, c.hora DESC
             LIMIT 8"
        );
        $stmt->bind_param('ii', $clienteId, $citaActualId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function mapearCita(array $row)
    {
        $cita = new Cita();
        $cita->setId($row['id']);
        $cita->setClienteId($row['cliente_id']);
        $cita->setAbogadoId($row['abogado_id']);
        $cita->setFecha($row['fecha']);
        $cita->setHora($row['hora']);
        $cita->setTipoDeCasoId($row['tipo_de_caso_id']);
        $cita->setMensaje($row['mensaje']);
        $cita->setEstado($row['estado']);
        $cita->setHoraInicioAt($row['hora_inicio_at'] ?? null);
        $cita->setHoraFinAt($row['hora_fin_at'] ?? null);
        $cita->setObservacionFinal($row['observacion_final'] ?? null);
        $cita->setRequiereNuevaCita($row['requiere_nueva_cita'] ?? 0);
        $cita->setRequiereCambioEspecialidad($row['requiere_cambio_especialidad'] ?? 0);
        return $cita;
    }

    private function ensureAtencionSchema()
    {
        $estadoColumn = $this->conn->query(
            "SELECT COLUMN_TYPE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'estado'
             LIMIT 1"
        );
        $estadoType = $estadoColumn ? (string) ($estadoColumn->fetch_assoc()['COLUMN_TYPE'] ?? '') : '';
        if (strpos($estadoType, 'en_atencion') === false) {
            $this->conn->query("ALTER TABLE citas MODIFY estado ENUM('pendiente', 'confirmada', 'en_atencion', 'cancelada', 'terminado') NOT NULL DEFAULT 'pendiente'");
        }

        $this->ensureColumn('citas', 'hora_inicio_at', 'DATETIME NULL AFTER fecha_atencion');
        $this->ensureColumn('citas', 'hora_fin_at', 'DATETIME NULL AFTER hora_inicio_at');
        $this->ensureColumn('citas', 'observacion_final', 'TEXT NULL AFTER motivo_cancelacion');
        $this->ensureColumn('citas', 'requiere_nueva_cita', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER observacion_final');
        $this->ensureColumn('citas', 'requiere_cambio_especialidad', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER requiere_nueva_cita');

        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS cita_notas_abogado (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                cita_id INT NOT NULL,
                abogado_id INT NOT NULL,
                nota TEXT NOT NULL,
                creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cita_notas_cita (cita_id),
                INDEX idx_cita_notas_abogado (abogado_id),
                CONSTRAINT fk_cita_notas_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
                CONSTRAINT fk_cita_notas_abogado FOREIGN KEY (abogado_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS cita_documentos (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                cita_id INT NOT NULL,
                cliente_id INT NOT NULL,
                archivo VARCHAR(255) NOT NULL,
                nombre_original VARCHAR(180) NOT NULL,
                creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_cita_documentos_cita (cita_id),
                INDEX idx_cita_documentos_cliente (cliente_id),
                CONSTRAINT fk_cita_documentos_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
                CONSTRAINT fk_cita_documentos_cliente FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS cita_avisos_cliente (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                cliente_id INT NOT NULL,
                cita_id INT NULL,
                tipo ENUM('cancelacion', 'reajuste') NOT NULL,
                titulo VARCHAR(160) NOT NULL,
                mensaje TEXT NOT NULL,
                oculto_cliente TINYINT(1) NOT NULL DEFAULT 0,
                creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ocultado_en DATETIME NULL,
                INDEX idx_cita_avisos_cliente (cliente_id, oculto_cliente),
                INDEX idx_cita_avisos_cita (cita_id),
                CONSTRAINT fk_cita_avisos_cliente FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                CONSTRAINT fk_cita_avisos_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function ensureColumn(string $table, string $column, string $definition)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
        );
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $exists = (int) $stmt->get_result()->fetch_assoc()['total'] > 0;
        if (!$exists) {
            $this->conn->query("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

}
