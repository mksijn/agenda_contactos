
-- ========================================
-- AGENDA PERSONAL DE CONTACTOS
-- Asignatura: Desarrollo Web - UTPL
-- ========================================

-- IMPORTANTE:
-- Seleccionar o crear la base de datos
-- antes de ejecutar este script.
-- Compatible con XAMPP e InfinityFree.

-- ========================================
-- TABLA DE USUARIOS
-- ========================================

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLA DE CONTACTOS
-- ========================================

CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,

    -- Relacionar cada contacto con su propietario
    CONSTRAINT fk_contactos_usuarios
    FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id)
    ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
