CREATE DATABASE IF NOT EXISTS tesis_system;

USE tesis_system;

-- usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100)  NOT NULL,
    apellido   VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    rol        ENUM('estudiante', 'tutor', 'coordinador') NOT NULL,
    activo     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- proyectos
CREATE TABLE IF NOT EXISTS proyectos (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo             VARCHAR(200) NOT NULL,
    descripcion        TEXT         DEFAULT NULL,
    area_conocimiento  VARCHAR(100) NOT NULL,
    estado             ENUM('borrador','en_revision','aprobado','en_curso','finalizado','archivado') NOT NULL DEFAULT 'borrador',
    fecha_inicio       DATE         NOT NULL,
    fecha_fin_estimada DATE         DEFAULT NULL,
    estudiante_id      INT UNSIGNED NOT NULL,
    tutor_id           INT UNSIGNED DEFAULT NULL,
    created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_proyecto_estudiante FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    CONSTRAINT fk_proyecto_tutor      FOREIGN KEY (tutor_id)      REFERENCES usuarios(id)
);

-- historial de cambios
CREATE TABLE IF NOT EXISTS historial_estado (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id     INT UNSIGNED NOT NULL,
    estado_anterior VARCHAR(50)  DEFAULT NULL,
    estado_nuevo    VARCHAR(50)  NOT NULL,
    cambiado_por    INT UNSIGNED NOT NULL,
    motivo          TEXT         DEFAULT NULL,
    fecha_cambio    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_historial_proyecto FOREIGN KEY (proyecto_id)  REFERENCES proyectos(id) ON DELETE CASCADE,
    CONSTRAINT fk_historial_usuario  FOREIGN KEY (cambiado_por) REFERENCES usuarios(id)
);

-- avances
CREATE TABLE IF NOT EXISTS avances (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id       INT UNSIGNED NOT NULL,
    descripcion       TEXT         NOT NULL,
    porcentaje_avance INT          NOT NULL DEFAULT 0 CHECK (porcentaje_avance BETWEEN 0 AND 100),
    estado            ENUM('pendiente','revisado','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    comentario_tutor  TEXT         DEFAULT NULL,
    archivo_url       VARCHAR(500) DEFAULT NULL,
    fecha_registro    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_avance_proyecto FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE
);

-- tutorias
CREATE TABLE IF NOT EXISTS tutorias (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id        INT UNSIGNED NOT NULL,
    tutor_id           INT UNSIGNED NOT NULL,
    fecha_hora         DATETIME     NOT NULL,
    duracion_min       INT          NOT NULL DEFAULT 60 COMMENT 'Duración en minutos',
    modalidad          ENUM('presencial','virtual','asincrona') NOT NULL DEFAULT 'virtual',
    estado             ENUM('programada','realizada','cancelada','reprogramada') NOT NULL DEFAULT 'programada',
    observaciones      TEXT         DEFAULT NULL,
    created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_tutoria_proyecto FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    CONSTRAINT fk_tutoria_tutor    FOREIGN KEY (tutor_id)    REFERENCES usuarios(id)
);

-- asistencias
CREATE TABLE IF NOT EXISTS asistencias (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proyecto_id   INT UNSIGNED NOT NULL,
    estudiante_id INT UNSIGNED NOT NULL,
    tutor_id      INT UNSIGNED NOT NULL,
    fecha         DATE         NOT NULL,
    estado        ENUM('presente','ausente','justificado') NOT NULL,
    modalidad     ENUM('presencial','virtual')             NOT NULL DEFAULT 'virtual',
    observaciones TEXT         DEFAULT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_asistencia_proyecto   FOREIGN KEY (proyecto_id)   REFERENCES proyectos(id) ON DELETE CASCADE,
    CONSTRAINT fk_asistencia_estudiante FOREIGN KEY (estudiante_id) REFERENCES usuarios(id),
    CONSTRAINT fk_asistencia_tutor      FOREIGN KEY (tutor_id)      REFERENCES usuarios(id)
);

-- criterio de evaluacion
CREATE TABLE IF NOT EXISTS criterio_evaluacion (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asistencia_id INT UNSIGNED   NOT NULL,
    hora_inicio   TIME           NOT NULL,
    hora_fin      TIME           NOT NULL,
    duracion      DECIMAL(5,2)   NOT NULL COMMENT 'Duración en horas',
    validado      TINYINT(1)     NOT NULL DEFAULT 0,

    CONSTRAINT fk_criterio_asistencia FOREIGN KEY (asistencia_id) REFERENCES asistencias(id) ON DELETE CASCADE
);

-- DATOS SEED (datos de prueba iniciales)
-- Contraseña para todos: "password123"  (hash bcrypt)
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('Ana',     'García',   'coordinador@tesis.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinador'),
('Carlos',  'Mendoza',  'tutor@tesis.edu',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tutor'),
('Daniel',  'Mora',     'estudiante@tesis.edu',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante'),
('María',   'López',    'estudiante2@tesis.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante');

INSERT INTO proyectos (titulo, descripcion, area_conocimiento, estado, fecha_inicio, fecha_fin_estimada, estudiante_id, tutor_id) VALUES
('Sistema de Gestión de Inventario con IA',
 'Sistema para control de inventarios en PYMES usando predicción de demanda.',
 'Inteligencia Artificial', 'en_curso', '2026-02-01', '2026-07-01', 3, 2),

('Plataforma E-learning para Zonas Rurales',
 'Plataforma web accesible sin conexión para estudiantes de zonas rurales.',
 'Educación Digital', 'aprobado', '2026-03-01', '2026-08-01', 4, NULL);

INSERT INTO avances (proyecto_id, descripcion, porcentaje_avance, estado, fecha_registro) VALUES
(1, 'Análisis detallado de los requerimientos del sistema', 20, 'aprobado',  '2026-02-15 10:00:00'),
(1, 'Diseño de la arquitectura del sistema',               35, 'pendiente', '2026-03-10 14:30:00');

INSERT INTO tutorias (proyecto_id, tutor_id, fecha_hora, duracion_min, modalidad, estado, observaciones) VALUES
(1, 2, '2026-03-05 10:00:00', 120, 'presencial', 'realizada',  'Revisión de requerimientos completada'),
(1, 2, '2026-03-20 15:00:00',  90, 'virtual',    'programada', NULL);

INSERT INTO asistencias (proyecto_id, estudiante_id, tutor_id, fecha, estado, modalidad) VALUES
(1, 3, 2, '2026-03-05', 'presente', 'presencial');
