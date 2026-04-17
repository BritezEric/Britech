-- ╔══════════════════════════════════════════════════════════╗
-- ║  BRITECH — Setup completo de base de datos              ║
-- ║  Ejecutá esto UNA VEZ en tu base de datos "brit"        ║
-- ╚══════════════════════════════════════════════════════════╝

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── 1. Tabla roles (necesaria para precios) ─────────────────
CREATE TABLE IF NOT EXISTS roles (
  id_rol   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre   VARCHAR(50)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (id_rol, nombre) VALUES
  (1, 'minorista'),
  (2, 'mayorista');

-- ─── 2. Tabla productos ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
  id_producto INT UNSIGNED     NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(255)     NOT NULL,
  descripcion TEXT             DEFAULT NULL,
  stock       INT              NOT NULL DEFAULT 0,
  activo      TINYINT(1)       NOT NULL DEFAULT 1,
  creado_en   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. Tabla precios ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS precios (
  id_precio   INT UNSIGNED   NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_producto INT UNSIGNED   NOT NULL,
  id_rol      INT UNSIGNED   NOT NULL,
  precio      DECIMAL(12,2)  NOT NULL DEFAULT 0,
  CONSTRAINT fk_precio_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
  CONSTRAINT fk_precio_rol      FOREIGN KEY (id_rol)      REFERENCES roles(id_rol)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Unique key para poder hacer upsert (ON DUPLICATE KEY UPDATE)
ALTER TABLE precios
  ADD UNIQUE KEY IF NOT EXISTS uq_precio_producto_rol (id_producto, id_rol);

-- ─── 4. Tabla movimientos de stock ───────────────────────────
CREATE TABLE IF NOT EXISTS movimientos (
  id_movimiento INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_producto   INT UNSIGNED  NOT NULL,
  tipo          ENUM('ingreso','egreso') NOT NULL,
  cantidad      INT           NOT NULL,
  descripcion   VARCHAR(255)  DEFAULT NULL,
  creado_en     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mov_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 5. Tabla storage (ventas, envíos, combos, etc.) ─────────
CREATE TABLE IF NOT EXISTS storage (
  entity     VARCHAR(100) NOT NULL PRIMARY KEY,
  payload    JSON         NOT NULL,
  updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ─── Verificación final ──────────────────────────────────────
SELECT 'Setup completado correctamente' AS resultado;
SHOW TABLES;
