USE estudiojuridico;

SET @exists_usuario_id := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'admin_actividad'
    AND COLUMN_NAME = 'usuario_id'
);
SET @sql := IF(
  @exists_usuario_id = 0,
  'ALTER TABLE admin_actividad ADD COLUMN usuario_id INT NULL AFTER administrador_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exists_rol := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'admin_actividad'
    AND COLUMN_NAME = 'rol'
);
SET @sql := IF(
  @exists_rol = 0,
  'ALTER TABLE admin_actividad ADD COLUMN rol ENUM(''administrador'', ''abogado'') NOT NULL DEFAULT ''administrador'' AFTER usuario_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exists_idx_usuario := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'admin_actividad'
    AND INDEX_NAME = 'idx_admin_actividad_usuario'
);
SET @sql := IF(
  @exists_idx_usuario = 0,
  'ALTER TABLE admin_actividad ADD INDEX idx_admin_actividad_usuario (usuario_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exists_idx_rol := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'admin_actividad'
    AND INDEX_NAME = 'idx_admin_actividad_rol'
);
SET @sql := IF(
  @exists_idx_rol = 0,
  'ALTER TABLE admin_actividad ADD INDEX idx_admin_actividad_rol (rol)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
