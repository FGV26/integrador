USE estudiojuridico;

ALTER TABLE citas
  MODIFY estado ENUM('pendiente', 'confirmada', 'en_atencion', 'cancelada', 'terminado') NOT NULL DEFAULT 'pendiente';

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'hora_inicio_at');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN hora_inicio_at DATETIME NULL AFTER fecha_atencion', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'hora_fin_at');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN hora_fin_at DATETIME NULL AFTER hora_inicio_at', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'observacion_final');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN observacion_final TEXT NULL AFTER motivo_cancelacion', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'requiere_nueva_cita');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN requiere_nueva_cita TINYINT(1) NOT NULL DEFAULT 0 AFTER observacion_final', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'requiere_cambio_especialidad');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN requiere_cambio_especialidad TINYINT(1) NOT NULL DEFAULT 0 AFTER requiere_nueva_cita', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

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
