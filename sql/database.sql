-- ============================================================
-- Base de datos: SolVolt Energía
-- Trabajo Final PHP - Sitio web de empresa de energías renovables
-- ============================================================

-- Asegura que los acentos y la "ñ" se importen correctamente
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS solvolt_energia
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE solvolt_energia;

-- ------------------------------------------------------------
-- Tabla: users_data
-- ------------------------------------------------------------
CREATE TABLE users_data (
    idUser INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    direccion VARCHAR(255),
    sexo ENUM('Hombre', 'Mujer', 'Otro') DEFAULT 'Otro'
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: users_login
-- ------------------------------------------------------------
CREATE TABLE users_login (
    idLogin INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL UNIQUE,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    CONSTRAINT fk_login_user FOREIGN KEY (idUser)
        REFERENCES users_data(idUser)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: citas
-- ------------------------------------------------------------
CREATE TABLE citas (
    idCita INT AUTO_INCREMENT PRIMARY KEY,
    idUser INT NOT NULL,
    fecha_cita DATE NOT NULL,
    motivo_cita TEXT,
    CONSTRAINT fk_citas_user FOREIGN KEY (idUser)
        REFERENCES users_data(idUser)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabla: noticias
-- ------------------------------------------------------------
CREATE TABLE noticias (
    idNoticia INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL UNIQUE,
    imagen VARCHAR(255) NOT NULL,
    texto LONGTEXT NOT NULL,
    fecha DATE NOT NULL,
    idUser INT NOT NULL,
    CONSTRAINT fk_noticias_user FOREIGN KEY (idUser)
        REFERENCES users_data(idUser)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Datos de ejemplo: un administrador y un usuario normal
-- Contraseña en texto plano para ambos: "Admin1234" y "User1234"
-- (En el sitio real se guarda encriptada con password_hash de PHP;
--  estos hashes de ejemplo corresponden a esas contraseñas)
-- ------------------------------------------------------------
INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo) VALUES
('Laura', 'Martínez Gómez', 'admin@solvolt.com', '600111222', '1988-04-12', 'Calle del Sol 10, Albacete', 'Mujer'),
('Carlos', 'Ruiz Pérez', 'carlos.ruiz@example.com', '600333444', '1995-09-23', 'Avenida Eólica 5, Madrid', 'Hombre');

-- Hashes reales generados con bcrypt (compatibles con password_verify de PHP)
INSERT INTO users_login (idUser, usuario, password, rol) VALUES
(1, 'admin', '$2y$10$.L9ZE2spmK3UI8cMCcxHrOky0wJcpXHEnRDyJAoyuwMeueiYU8wHu', 'admin'),
(2, 'carlosruiz', '$2y$10$Bm.9Vr40g/DdBtDZrq1pL.9fHrr4IE8c0b.CdgfI9nVSIZklH4/qy', 'user');

INSERT INTO noticias (titulo, imagen, texto, fecha, idUser) VALUES
('SolVolt instala su parque solar número 50', 'images/noticia1.jpg', 'SolVolt Energía ha inaugurado su parque solar número 50, ampliando su capacidad de generación limpia y acercándose a su objetivo de suministrar energía 100% renovable a más de un millón de hogares antes de 2030.', '2026-06-15', 1),
('Nuevas tarifas de autoconsumo para 2026', 'images/noticia2.jpg', 'A partir de este mes, SolVolt lanza nuevas tarifas de autoconsumo pensadas para hogares y pequeñas empresas, con condiciones más flexibles y mayor compensación por excedentes de energía solar.', '2026-07-01', 1),
('SolVolt firma un acuerdo de energía eólica marina', 'images/noticia3.jpg', 'La compañía ha firmado un acuerdo estratégico para el desarrollo de un nuevo parque eólico marino, que se espera esté operativo en los próximos tres años y suponga un importante avance en la transición energética.', '2026-08-20', 1);
