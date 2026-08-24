USE estudiojuridico;

SET @is_active_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'is_active'
);
SET @is_active_sql = IF(
  @is_active_exists = 0,
  'ALTER TABLE usuarios ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER imagen',
  'SELECT 1'
);
PREPARE is_active_statement FROM @is_active_sql;
EXECUTE is_active_statement;
DEALLOCATE PREPARE is_active_statement;

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
