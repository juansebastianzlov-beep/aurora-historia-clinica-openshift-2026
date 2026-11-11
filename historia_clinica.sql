-- Historia Clínica Aurora | demostración académica
-- Todos los nombres y registros incluidos son ficticios.
-- Compatible con MySQL/MariaDB de XAMPP (InnoDB, utf8mb4).
CREATE DATABASE IF NOT EXISTS historia_clinica_demostracion
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE historia_clinica_demostracion;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS formulas_medicas;
DROP TABLE IF EXISTS historia_tratamientos;
DROP TABLE IF EXISTS historia_diagnosticos;
DROP TABLE IF EXISTS paciente_alergias;
DROP TABLE IF EXISTS historias_clinicas;
DROP TABLE IF EXISTS tratamientos;
DROP TABLE IF EXISTS diagnosticos;
DROP TABLE IF EXISTS medicamentos;
DROP TABLE IF EXISTS alergias;
DROP TABLE IF EXISTS pacientes;
DROP TABLE IF EXISTS tipos_sangre;
DROP TABLE IF EXISTS usuarios_aplicacion;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios_aplicacion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre_completo VARCHAR(120) NOT NULL,
  correo VARCHAR(190) NOT NULL UNIQUE,
  hash_contrasena VARCHAR(255) NOT NULL,
  rol ENUM('administrador','personal') NOT NULL DEFAULT 'personal',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tipos_sangre (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(5) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE pacientes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('CC','CE','TI','PA','OTRO') NOT NULL DEFAULT 'CC',
  numero_documento VARCHAR(30) NOT NULL UNIQUE,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  fecha_nacimiento DATE NULL,
  sexo ENUM('Mujer','Hombre','Intersexual','Prefiere no indicar') NOT NULL DEFAULT 'Prefiere no indicar',
  telefono VARCHAR(30) NULL,
  correo VARCHAR(190) NULL,
  tipo_sangre_id TINYINT UNSIGNED NULL,
  creado_por BIGINT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_documento_paciente (tipo_documento, numero_documento),
  KEY idx_nombre_paciente (apellidos, nombres),
  CONSTRAINT fk_sangre_paciente FOREIGN KEY (tipo_sangre_id) REFERENCES tipos_sangre(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_creador_paciente FOREIGN KEY (creado_por) REFERENCES usuarios_aplicacion(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE historias_clinicas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  paciente_id BIGINT UNSIGNED NOT NULL,
  usuario_autor_id BIGINT UNSIGNED NULL,
  fecha_atencion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  motivo_consulta VARCHAR(240) NOT NULL,
  sintomas TEXT NULL,
  afectaciones TEXT NULL,
  valoracion TEXT NULL,
  notas TEXT NULL,
  estado ENUM('Abierta','En seguimiento','Cerrada') NOT NULL DEFAULT 'Abierta',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_paciente_fecha_historia (paciente_id, fecha_atencion),
  CONSTRAINT fk_paciente_historia FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_autor_historia FOREIGN KEY (usuario_autor_id) REFERENCES usuarios_aplicacion(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE diagnosticos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(180) NOT NULL UNIQUE,
  descripcion TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE historia_diagnosticos (
  historia_clinica_id BIGINT UNSIGNED NOT NULL,
  diagnostico_id BIGINT UNSIGNED NOT NULL,
  diagnostico_principal TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (historia_clinica_id, diagnostico_id),
  CONSTRAINT fk_historia_diagnostico FOREIGN KEY (historia_clinica_id) REFERENCES historias_clinicas(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_diagnostico_relacion FOREIGN KEY (diagnostico_id) REFERENCES diagnosticos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE tratamientos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(180) NOT NULL UNIQUE,
  descripcion TEXT NULL
) ENGINE=InnoDB;

CREATE TABLE historia_tratamientos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  historia_clinica_id BIGINT UNSIGNED NOT NULL,
  tratamiento_id BIGINT UNSIGNED NOT NULL,
  fecha_realizacion DATE NULL,
  resultado TEXT NULL,
  KEY idx_historia_tratamiento (historia_clinica_id),
  CONSTRAINT fk_historia_tratamiento FOREIGN KEY (historia_clinica_id) REFERENCES historias_clinicas(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_tratamiento_relacion FOREIGN KEY (tratamiento_id) REFERENCES tratamientos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE alergias (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(160) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE paciente_alergias (
  paciente_id BIGINT UNSIGNED NOT NULL,
  alergia_id BIGINT UNSIGNED NOT NULL,
  reaccion VARCHAR(240) NULL,
  gravedad ENUM('Leve','Moderada','Alta','No especificada') NOT NULL DEFAULT 'No especificada',
  PRIMARY KEY (paciente_id, alergia_id),
  CONSTRAINT fk_paciente_alergia FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_alergia_relacion FOREIGN KEY (alergia_id) REFERENCES alergias(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE medicamentos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(180) NOT NULL UNIQUE,
  principio_activo VARCHAR(180) NULL,
  presentacion VARCHAR(100) NULL
) ENGINE=InnoDB;

CREATE TABLE formulas_medicas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  historia_clinica_id BIGINT UNSIGNED NOT NULL,
  medicamento_id BIGINT UNSIGNED NOT NULL,
  dosis VARCHAR(100) NULL,
  frecuencia VARCHAR(120) NULL,
  duracion VARCHAR(100) NULL,
  instrucciones TEXT NULL,
  KEY idx_historia_formula (historia_clinica_id),
  CONSTRAINT fk_historia_formula FOREIGN KEY (historia_clinica_id) REFERENCES historias_clinicas(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_medicamento_formula FOREIGN KEY (medicamento_id) REFERENCES medicamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO usuarios_aplicacion (nombre_completo,correo,hash_contrasena,rol) VALUES
('Administración de demostración','acceso@demostracion.local','$2y$10$X.yEdTPlUnOuriHrHG20dOO6NHsXce.2qfV3l218DKOtsJOyH/lbC','administrador');

INSERT INTO tipos_sangre (nombre) VALUES ('A+'),('A-'),('B+'),('B-'),('AB+'),('AB-'),('O+'),('O-');
INSERT INTO diagnosticos (nombre,descripcion) VALUES
('Valoración inicial','Registro ficticio para demostración académica.'),
('Seguimiento general','Categoría de ejemplo; no constituye diagnóstico médico.');
INSERT INTO tratamientos (nombre,descripcion) VALUES
('Orientación general','Actividad de ejemplo para demostrar la relación entre tablas.'),
('Seguimiento de síntomas','Actividad de ejemplo para demostración académica.');
INSERT INTO alergias (nombre) VALUES ('Ninguna reportada'),('Alergia de ejemplo');
INSERT INTO medicamentos (nombre,principio_activo,presentacion) VALUES
('Producto de demostración A','Ingrediente ficticio','Presentación de ejemplo'),
('Producto de demostración B','Ingrediente ficticio','Presentación de ejemplo');

INSERT INTO pacientes (tipo_documento,numero_documento,nombres,apellidos,fecha_nacimiento,sexo,telefono,correo,tipo_sangre_id,creado_por) VALUES
('OTRO','PRUEBA-1001','Laura','Ejemplo', '1998-04-12','Mujer','0000000001','laura@ejemplo-ficticio.invalid',7,1),
('OTRO','PRUEBA-1002','María','Muestra', '2001-09-03','Mujer','0000000002','maria@ejemplo-ficticio.invalid',1,1),
('OTRO','PRUEBA-1003','Ana','Ficticia', '1995-12-20','Mujer','0000000003','ana@ejemplo-ficticio.invalid',5,1);

INSERT INTO historias_clinicas (paciente_id,usuario_autor_id,fecha_atencion,motivo_consulta,sintomas,afectaciones,valoracion,notas,estado) VALUES
(1,1,'2026-10-14 09:30:00','Consulta de ejemplo por malestar general','Cansancio leve descrito en un escenario ficticio.','Refiere menor energía durante actividades cotidianas.','Registro académico de prueba; no es una evaluación clínica.','Información totalmente ficticia para verificar búsquedas y relaciones.','En seguimiento'),
(1,1,'2026-10-28 10:15:00','Seguimiento de ejemplo','La paciente de demostración informa mejoría simulada.','Sin cambios funcionales relevantes en este escenario ficticio.','Seguimiento de prueba; no constituye diagnóstico ni recomendación.','Registro ficticio vinculado a la misma paciente para probar consulta por documento.','Cerrada'),
(2,1,'2026-10-19 14:00:00','Registro preventivo de demostración','Sin síntomas consignados en este dato de prueba.','No aplica; ejemplo de interfaz.','Información simulada para fines académicos.','Paciente ficticia.','Abierta'),
(3,1,'2026-11-02 08:45:00','Consulta de ejemplo','Dolor de cabeza leve dentro de un escenario inventado.','Molestia temporal en actividades de estudio.','Registro de demostración, no es una valoración profesional.','Paciente y datos inventados.','En seguimiento');

INSERT INTO historia_diagnosticos (historia_clinica_id,diagnostico_id,diagnostico_principal) VALUES (1,1,1),(2,2,1),(3,1,1),(4,1,1);
INSERT INTO historia_tratamientos (historia_clinica_id,tratamiento_id,fecha_realizacion,resultado) VALUES
(1,1,'2026-10-14','Orientación registrada como ejemplo ficticio.'),
(1,2,'2026-10-14','Seguimiento de ejemplo.'),
(2,2,'2026-10-28','Seguimiento ficticio completado.'),
(3,1,'2026-10-19','Registro de demostración.'),
(4,1,'2026-11-02','Orientación de ejemplo.' );
INSERT INTO paciente_alergias (paciente_id,alergia_id,reaccion,gravedad) VALUES
(1,1,'Sin alergias reportadas en el registro ficticio.','No especificada'),
(2,1,'Sin alergias reportadas en el registro ficticio.','No especificada'),
(3,2,'Dato inventado para probar la tabla de alergias.','Leve');
INSERT INTO formulas_medicas (historia_clinica_id,medicamento_id,dosis,frecuencia,duracion,instrucciones) VALUES
(1,1,'No aplica','No aplica','No aplica','Fila ficticia de ejemplo; no es una prescripción real.');
