USE estudiojuridico;

SET @column_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'last_login_at'
);
SET @sql = IF(
  @column_exists = 0,
  'ALTER TABLE usuarios ADD COLUMN last_login_at DATETIME NULL AFTER creado_en',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'password_change_required'
);
SET @sql = IF(
  @column_exists = 0,
  'ALTER TABLE usuarios ADD COLUMN password_change_required TINYINT(1) NOT NULL DEFAULT 0 AFTER last_login_at',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'estado_cliente'
);
SET @sql = IF(
  @column_exists = 0,
  'ALTER TABLE usuarios ADD COLUMN estado_cliente VARCHAR(30) NOT NULL DEFAULT ''activo'' AFTER password_change_required',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @index_exists = (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND INDEX_NAME = 'idx_usuarios_estado_cliente'
);
SET @sql = IF(
  @index_exists = 0,
  'CREATE INDEX idx_usuarios_estado_cliente ON usuarios (rol, estado_cliente, is_active)',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

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
