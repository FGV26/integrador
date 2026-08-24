USE estudiojuridico;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'fecha_solicitud');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN fecha_solicitud DATETIME NULL AFTER estado', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'fecha_confirmacion');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN fecha_confirmacion DATETIME NULL AFTER fecha_solicitud', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'fecha_atencion');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN fecha_atencion DATETIME NULL AFTER fecha_confirmacion', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'fecha_cancelacion');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN fecha_cancelacion DATETIME NULL AFTER fecha_atencion', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'motivo_cancelacion');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN motivo_cancelacion VARCHAR(100) NULL AFTER fecha_cancelacion', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'modalidad');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN modalidad VARCHAR(30) NULL AFTER motivo_cancelacion', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'canal_solicitud');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN canal_solicitud VARCHAR(30) NULL AFTER modalidad', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'es_primera_consulta');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN es_primera_consulta TINYINT(1) NULL AFTER canal_solicitud', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'estado_origen');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN estado_origen VARCHAR(30) NULL AFTER es_primera_consulta', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'origen_datos');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN origen_datos VARCHAR(50) NULL AFTER estado_origen', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND COLUMN_NAME = 'referencia_externa');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE citas ADD COLUMN referencia_externa VARCHAR(190) NULL AFTER origen_datos', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'citas' AND INDEX_NAME = 'uq_citas_referencia_externa');
SET @sql = IF(@index_exists = 0, 'CREATE UNIQUE INDEX uq_citas_referencia_externa ON citas (referencia_externa)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
