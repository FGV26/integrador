<?php
require_once dirname(__DIR__) . '/Helpers/Conexion.php';
require_once dirname(__DIR__) . '/Models/Usuario.php';

class UsuarioDAO
{
    private $conn;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->conn = $conexion->getConnection();
        $this->ensureClienteLifecycleSchema();
    }

    public function crear($usuario)
    {
        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, correo, telefono, usuario, contrasena, rol, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        $nombre = $usuario->getNombre();
        $apellidoPaterno = $usuario->getApellidoPaterno();
        $apellidoMaterno = $usuario->getApellidoMaterno();
        $correo = $usuario->getCorreo();
        $telefono = $usuario->getTelefono();
        $usuarioNombre = $usuario->getUsuario();
        $contrasena = $usuario->getContrasena();
        $rol = $usuario->getRol();
        $imagen = $usuario->getImagen();

        $stmt->bind_param(
            'sssssssss',
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $correo,
            $telefono,
            $usuarioNombre,
            $contrasena,
            $rol,
            $imagen
        );
        return $stmt->execute();
    }

    public function actualizar($usuario)
    {
        $sql = "UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, correo = ?, telefono = ?, usuario = ?, contrasena = ?, rol = ?, imagen = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        $nombre = $usuario->getNombre();
        $apellidoPaterno = $usuario->getApellidoPaterno();
        $apellidoMaterno = $usuario->getApellidoMaterno();
        $correo = $usuario->getCorreo();
        $telefono = $usuario->getTelefono();
        $usuarioNombre = $usuario->getUsuario();
        $contrasena = $usuario->getContrasena();
        $rol = $usuario->getRol();
        $imagen = $usuario->getImagen();
        $id = $usuario->getId();

        $stmt->bind_param(
            'sssssssssi',
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $correo,
            $telefono,
            $usuarioNombre,
            $contrasena,
            $rol,
            $imagen,
            $id
        );

        return $stmt->execute();
    }

    public function obtenerPorRol($rol)
    {
        $sql = "SELECT * FROM usuarios WHERE rol = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $rol);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $this->mapearUsuario($row);
            }
        }

        return $usuarios;
    }

    public function obtenerTodos()
    {
        $sql = "SELECT * FROM usuarios WHERE rol = 'abogado'";
        $result = $this->conn->query($sql);
        $usuarios = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $usuarios[] = $this->mapearUsuario($row);
            }
        }

        return $usuarios;
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            return $this->mapearUsuario($row);
        }

        return null;
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function autenticar($usuario, $contrasena)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = ? AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($contrasena, $row['contrasena'])) {
                return $this->mapearUsuario($row);
            }
        }

        return null;
    }

    public function existeUsuario($usuario)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    public function existeUsuarioEnOtroId($usuario, int $id = 0): bool
    {
        $usuario = trim((string) $usuario);
        if ($usuario === '') {
            return false;
        }

        $sql = "SELECT id FROM usuarios WHERE usuario = ? AND id <> ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $usuario, $id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function obtenerEstadoAccesoPorUsuario($usuario): ?array
    {
        $sql = "SELECT id, rol, is_active, estado_cliente FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function existeCorreo($correo)
    {
        $sql = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    public function existeCorreoEnOtroId($correo, int $id = 0): bool
    {
        $correo = trim((string) $correo);
        if ($correo === '') {
            return false;
        }

        $sql = "SELECT id FROM usuarios WHERE correo = ? AND id <> ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $correo, $id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function obtenerCredencialesRegistradas(): array
    {
        $result = $this->conn->query("SELECT id, usuario, correo FROM usuarios");
        $usuarios = [];

        while ($row = $result->fetch_assoc()) {
            $usuarios[] = [
                'id' => (int) $row['id'],
                'usuario' => $row['usuario'],
                'correo' => $row['correo'],
            ];
        }

        return $usuarios;
    }

    public function obtenerPaginadosPorRol($rol, $pagina = 1, $porPagina = 25, $nombreExacto = '')
    {
        $pagina = max(1, (int) $pagina);
        $porPagina = max(1, min(100, (int) $porPagina));
        $offset = ($pagina - 1) * $porPagina;
        $nombreExacto = trim((string) $nombreExacto);

        if ($nombreExacto !== '') {
            $sql = "SELECT * FROM usuarios
                    WHERE rol = ?
                      AND TRIM(CONCAT_WS(' ', nombre, apellido_paterno, NULLIF(apellido_materno, ''))) = ?
                    ORDER BY apellido_paterno, apellido_materno, nombre
                    LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssii', $rol, $nombreExacto, $porPagina, $offset);
        } else {
            $sql = "SELECT * FROM usuarios WHERE rol = ?
                    ORDER BY apellido_paterno, apellido_materno, nombre
                    LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('sii', $rol, $porPagina, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->mapearUsuario($row);
        }
        return $usuarios;
    }

    public function contarPorRol($rol, $nombreExacto = '')
    {
        $nombreExacto = trim((string) $nombreExacto);
        if ($nombreExacto !== '') {
            $sql = "SELECT COUNT(*) AS total FROM usuarios
                    WHERE rol = ?
                      AND TRIM(CONCAT_WS(' ', nombre, apellido_paterno, NULLIF(apellido_materno, ''))) = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ss', $rol, $nombreExacto);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE rol = ?");
            $stmt->bind_param('s', $rol);
        }
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function contarActivosPorRol($rol)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE rol = ? AND is_active = 1");
        $stmt->bind_param('s', $rol);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function cambiarEstadoActivo($id, $isActive)
    {
        $id = (int) $id;
        $isActive = $isActive ? 1 : 0;
        $estadoCliente = $isActive ? 'activo' : 'bloqueado';
        $stmt = $this->conn->prepare("UPDATE usuarios SET is_active = ?, estado_cliente = IF(rol = 'cliente', ?, estado_cliente) WHERE id = ?");
        $stmt->bind_param('isi', $isActive, $estadoCliente, $id);
        return $stmt->execute() && $stmt->affected_rows >= 0;
    }

    public function registrarLogin(int $id): bool
    {
        $stmt = $this->conn->prepare("UPDATE usuarios SET last_login_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function marcarCambioContrasenaRequerido(int $id, bool $requerido, string $estadoCliente = ''): bool
    {
        $requeridoValor = $requerido ? 1 : 0;
        if ($estadoCliente !== '') {
            $stmt = $this->conn->prepare("UPDATE usuarios SET password_change_required = ?, estado_cliente = ? WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('isi', $requeridoValor, $estadoCliente, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE usuarios SET password_change_required = ? WHERE id = ? AND rol = 'cliente'");
            $stmt->bind_param('ii', $requeridoValor, $id);
        }
        return $stmt->execute();
    }

    public function actualizarContrasenaYReactivarCliente(int $id, string $hashContrasena): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios
             SET contrasena = ?, password_change_required = 0, estado_cliente = 'activo', is_active = 1, last_login_at = NOW()
             WHERE id = ? AND rol = 'cliente'"
        );
        $stmt->bind_param('si', $hashContrasena, $id);
        return $stmt->execute() && $stmt->affected_rows >= 0;
    }

    public function obtenerEstadoCicloCliente(int $id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.is_active, u.creado_en, u.last_login_at, u.password_change_required, u.estado_cliente,
                    COUNT(c.id) AS total_citas,
                    MAX(c.fecha) AS ultima_cita
             FROM usuarios u
             LEFT JOIN citas c ON c.cliente_id = u.id
             WHERE u.id = ? AND u.rol = 'cliente'
             GROUP BY u.id"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            return ['estado' => 'desconocido', 'requiere_cambio' => false, 'puede_eliminar' => false];
        }
        return $this->calcularCicloCliente($row);
    }

    public function obtenerClientesConActividad(): array
    {
        $result = $this->conn->query(
            "SELECT u.*,
                    COUNT(c.id) AS total_citas,
                    MAX(c.fecha) AS ultima_cita
             FROM usuarios u
             LEFT JOIN citas c ON c.cliente_id = u.id
             WHERE u.rol = 'cliente'
             GROUP BY u.id
             ORDER BY u.apellido_paterno, u.apellido_materno, u.nombre"
        );
        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $ciclo = $this->calcularCicloCliente($row);
            $clientes[] = [
                'usuario' => $this->mapearUsuario($row),
                'total_citas' => (int) ($row['total_citas'] ?? 0),
                'ultima_cita' => $row['ultima_cita'] ?? null,
                'ultimo_login' => $row['last_login_at'] ?? null,
                'creado_en' => $row['creado_en'] ?? null,
                'ciclo' => $ciclo,
            ];
        }
        return $clientes;
    }

    public function eliminarClientePorInactividad(int $id, int $administradorId, string $motivo): array
    {
        $cliente = $this->obtenerPorId($id);
        if (!$cliente || $cliente->getRol() !== 'cliente') {
            return ['ok' => false, 'error' => 'usuario_no_valido'];
        }

        $ciclo = $this->obtenerEstadoCicloCliente($id);
        if (empty($ciclo['puede_eliminar'])) {
            return ['ok' => false, 'error' => 'cliente_no_depurable'];
        }

        $this->conn->begin_transaction();
        try {
            $usuarioHash = hash('sha256', strtolower(trim($cliente->getUsuario())));
            $correoHash = $cliente->getCorreo() ? hash('sha256', strtolower(trim($cliente->getCorreo()))) : null;
            $stmt = $this->conn->prepare(
                "INSERT INTO clientes_eliminados (usuario_hash, correo_hash, motivo, eliminado_por)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE motivo = VALUES(motivo), eliminado_por = VALUES(eliminado_por), eliminado_en = CURRENT_TIMESTAMP"
            );
            $stmt->bind_param('sssi', $usuarioHash, $correoHash, $motivo, $administradorId);
            $stmt->execute();

            $stmtDelete = $this->conn->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'cliente'");
            $stmtDelete->bind_param('i', $id);
            $stmtDelete->execute();
            $this->conn->commit();
            return ['ok' => true];
        } catch (Throwable $error) {
            $this->conn->rollback();
            return ['ok' => false, 'error' => 'eliminar'];
        }
    }

    public function fueClienteEliminado(string $identificador): bool
    {
        $identificador = strtolower(trim($identificador));
        if ($identificador === '') {
            return false;
        }
        $hash = hash('sha256', $identificador);
        $stmt = $this->conn->prepare(
            "SELECT id FROM clientes_eliminados WHERE usuario_hash = ? OR correo_hash = ? LIMIT 1"
        );
        $stmt->bind_param('ss', $hash, $hash);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function calcularCicloCliente(array $row): array
    {
        if ((int) ($row['is_active'] ?? 1) !== 1 || ($row['estado_cliente'] ?? '') === 'bloqueado') {
            return [
                'estado' => 'bloqueado',
                'label' => 'Bloqueado',
                'descripcion' => 'Acceso suspendido por administracion',
                'requiere_cambio' => false,
                'puede_eliminar' => false,
            ];
        }

        $totalCitas = (int) ($row['total_citas'] ?? 0);
        $creadoEn = $this->fechaNormalizada($row['creado_en'] ?? null);
        $ultimoLogin = $this->fechaNormalizada($row['last_login_at'] ?? null);
        $ultimaCita = $this->fechaNormalizada($row['ultima_cita'] ?? null);
        $fechasActividad = array_filter([$ultimoLogin, $ultimaCita, $creadoEn]);
        $ultimaActividad = null;
        foreach ($fechasActividad as $fechaActividad) {
            if (!$ultimaActividad || $fechaActividad > $ultimaActividad) {
                $ultimaActividad = $fechaActividad;
            }
        }
        $hoy = new DateTimeImmutable('today');

        if ($totalCitas === 0 && $creadoEn && $creadoEn <= $hoy->modify('-6 months')) {
            return [
                'estado' => 'sin_uso',
                'label' => 'Sin uso',
                'descripcion' => 'Registrado hace mas de 6 meses y sin citas',
                'requiere_cambio' => false,
                'puede_eliminar' => true,
            ];
        }

        if ($totalCitas > 0 && $ultimaActividad && $ultimaActividad <= $hoy->modify('-3 years')) {
            return [
                'estado' => 'depurable',
                'label' => 'Depurable',
                'descripcion' => 'Mas de 3 anios sin actividad',
                'requiere_cambio' => true,
                'puede_eliminar' => true,
            ];
        }

        if ($totalCitas > 0 && $ultimaActividad && $ultimaActividad <= $hoy->modify('-2 years')) {
            return [
                'estado' => 'historico',
                'label' => 'Historico',
                'descripcion' => 'Debe cambiar contrasena para reactivar',
                'requiere_cambio' => true,
                'puede_eliminar' => false,
            ];
        }

        if ($totalCitas > 0 && $ultimaActividad && $ultimaActividad <= $hoy->modify('-1 year')) {
            return [
                'estado' => 'dormido',
                'label' => 'Dormido',
                'descripcion' => 'Debe cambiar contrasena para volver a activo',
                'requiere_cambio' => true,
                'puede_eliminar' => false,
            ];
        }

        return [
            'estado' => 'activo',
            'label' => 'Activo',
            'descripcion' => 'Actividad vigente',
            'requiere_cambio' => (bool) ($row['password_change_required'] ?? false),
            'puede_eliminar' => false,
        ];
    }

    private function fechaNormalizada($fecha): ?DateTimeImmutable
    {
        if (!$fecha) {
            return null;
        }
        return new DateTimeImmutable(substr((string) $fecha, 0, 10));
    }

    private function ensureClienteLifecycleSchema(): void
    {
        $this->ensureColumn('usuarios', 'last_login_at', 'DATETIME NULL AFTER creado_en');
        $this->ensureColumn('usuarios', 'password_change_required', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER last_login_at');
        $this->ensureColumn('usuarios', 'estado_cliente', "VARCHAR(30) NOT NULL DEFAULT 'activo' AFTER password_change_required");
        $this->conn->query(
            "CREATE TABLE IF NOT EXISTS clientes_eliminados (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                usuario_hash CHAR(64) NOT NULL,
                correo_hash CHAR(64) NULL,
                motivo VARCHAR(160) NOT NULL,
                eliminado_por INT NULL,
                eliminado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_clientes_eliminados_usuario_hash (usuario_hash),
                INDEX idx_clientes_eliminados_correo_hash (correo_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function ensureColumn(string $table, string $column, string $definition): void
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

    private function mapearUsuario(array $row)
    {
        $usuario = new Usuario();
        $usuario->setId($row['id']);
        $usuario->setNombre($row['nombre']);
        $usuario->setApellidoPaterno($row['apellido_paterno']);
        $usuario->setApellidoMaterno($row['apellido_materno']);
        $usuario->setCorreo($row['correo']);
        $usuario->setTelefono($row['telefono']);
        $usuario->setUsuario($row['usuario']);
        $usuario->setContrasena($row['contrasena']);
        $usuario->setRol($row['rol']);
        $usuario->setImagen($row['imagen']);
        $usuario->setIsActive($row['is_active'] ?? 1);
        $usuario->setCreadoEn($row['creado_en'] ?? null);
        $usuario->setLastLoginAt($row['last_login_at'] ?? null);
        $usuario->setPasswordChangeRequired($row['password_change_required'] ?? 0);
        $usuario->setEstadoCliente($row['estado_cliente'] ?? 'activo');
        return $usuario;
    }

}
