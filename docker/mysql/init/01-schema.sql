CREATE DATABASE IF NOT EXISTS estudiojuridico
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE estudiojuridico;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido_paterno VARCHAR(100) NOT NULL,
  apellido_materno VARCHAR(100) NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  telefono VARCHAR(30) NULL,
  usuario VARCHAR(80) NOT NULL UNIQUE,
  `contrasena` VARCHAR(255) NOT NULL,
  rol ENUM('cliente', 'abogado', 'administrador') NOT NULL DEFAULT 'cliente',
  imagen VARCHAR(255) NOT NULL DEFAULT 'default.png',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL,
  password_change_required TINYINT(1) NOT NULL DEFAULT 0,
  estado_cliente VARCHAR(30) NOT NULL DEFAULT 'activo',
  INDEX idx_usuarios_estado_cliente (rol, estado_cliente, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes_eliminados (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  usuario_hash CHAR(64) NOT NULL,
  correo_hash CHAR(64) NULL,
  motivo VARCHAR(160) NOT NULL,
  eliminado_por INT NULL,
  eliminado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_clientes_eliminados_usuario_hash (usuario_hash),
  INDEX idx_clientes_eliminados_correo_hash (correo_hash),
  CONSTRAINT fk_clientes_eliminados_admin
    FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  administrador_id INT NULL,
  accion VARCHAR(80) NOT NULL,
  entidad VARCHAR(50) NOT NULL,
  entidad_id INT NULL,
  detalle VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_logs_fecha (creado_en),
  INDEX idx_audit_logs_administrador (administrador_id),
  CONSTRAINT fk_audit_logs_administrador FOREIGN KEY (administrador_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_actividad (
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
  CONSTRAINT fk_admin_actividad_administrador FOREIGN KEY (administrador_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tipos_de_caso (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS citas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  abogado_id INT NOT NULL,
  fecha DATE NOT NULL,
  hora TIME NOT NULL,
  tipo_de_caso_id INT NOT NULL,
  mensaje TEXT NOT NULL,
  estado ENUM('pendiente', 'confirmada', 'en_atencion', 'cancelada', 'terminado') NOT NULL DEFAULT 'pendiente',
  fecha_solicitud DATETIME NULL,
  fecha_confirmacion DATETIME NULL,
  fecha_atencion DATETIME NULL,
  hora_inicio_at DATETIME NULL,
  hora_fin_at DATETIME NULL,
  fecha_cancelacion DATETIME NULL,
  motivo_cancelacion VARCHAR(100) NULL,
  observacion_final TEXT NULL,
  requiere_nueva_cita TINYINT(1) NOT NULL DEFAULT 0,
  requiere_cambio_especialidad TINYINT(1) NOT NULL DEFAULT 0,
  modalidad VARCHAR(30) NULL,
  canal_solicitud VARCHAR(30) NULL,
  es_primera_consulta TINYINT(1) NULL,
  estado_origen VARCHAR(30) NULL,
  origen_datos VARCHAR(50) NULL,
  referencia_externa VARCHAR(190) NULL UNIQUE,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_citas_cliente (cliente_id),
  INDEX idx_citas_abogado (abogado_id),
  INDEX idx_citas_tipo_de_caso (tipo_de_caso_id),
  INDEX idx_citas_fecha_hora (fecha, hora),
  CONSTRAINT fk_citas_cliente FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_citas_abogado FOREIGN KEY (abogado_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_citas_tipo_de_caso FOREIGN KEY (tipo_de_caso_id) REFERENCES tipos_de_caso(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cita_notas_abogado (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  cita_id INT NOT NULL,
  abogado_id INT NOT NULL,
  nota TEXT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cita_notas_cita (cita_id),
  INDEX idx_cita_notas_abogado (abogado_id),
  CONSTRAINT fk_cita_notas_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
  CONSTRAINT fk_cita_notas_abogado FOREIGN KEY (abogado_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cita_documentos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ingresos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cita_id INT NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
  INDEX idx_ingresos_cita (cita_id),
  INDEX idx_ingresos_fecha (fecha),
  CONSTRAINT fk_ingresos_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS perdidas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cita_id INT NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
  INDEX idx_perdidas_cita (cita_id),
  INDEX idx_perdidas_fecha (fecha),
  CONSTRAINT fk_perdidas_cita FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tipos_de_caso (tipo) VALUES
  ('Derecho Penal'),
  ('Derecho Civil'),
  ('Derecho Familiar'),
  ('Derecho Notarial')
ON DUPLICATE KEY UPDATE tipo = VALUES(tipo);

INSERT INTO usuarios (
  nombre,
  apellido_paterno,
  apellido_materno,
  correo,
  telefono,
  usuario,
  `contrasena`,
  rol,
  imagen
) VALUES
  (
    'Admin',
    'Principal',
    '',
    'admin@example.test',
    '999999999',
    'admin',
    '$2y$10$v89wTGzMVe0x/Hxow6441OvLedZXauviow/BYv.lts4m2WzB59zvq',
    'administrador',
    'default.png'
  ),
  (
    'Abogado',
    'Demo',
    '',
    'abogado@example.test',
    '999999998',
    'abogado',
    '$2y$10$v89wTGzMVe0x/Hxow6441OvLedZXauviow/BYv.lts4m2WzB59zvq',
    'abogado',
    'default.png'
  ),
  (
    'Cliente',
    'Demo',
    '',
    'cliente@example.test',
    '999999997',
    'cliente',
    '$2y$10$v89wTGzMVe0x/Hxow6441OvLedZXauviow/BYv.lts4m2WzB59zvq',
    'cliente',
    'default.png'
  )
ON DUPLICATE KEY UPDATE usuario = VALUES(usuario);
