CREATE TABLE IF NOT EXISTS cita_avisos_cliente (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
