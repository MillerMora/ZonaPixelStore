-- ============================================================
--  ZonaPixel Store — Base de datos completa
--  Motor: MySQL 8.0+ / MariaDB 10.6+
--  Charset: utf8mb4  |  Collation: utf8mb4_unicode_ci
-- ============================================================

DROP DATABASE IF EXISTS zonapixel_db;
CREATE DATABASE zonapixel_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE zonapixel_db;

-- ============================================================
--  1. USUARIOS
-- ============================================================

CREATE TABLE roles (
  id_rol         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(30) NOT NULL UNIQUE,
  descripcion VARCHAR(120)
);

CREATE TABLE usuarios (
  id_usuario              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rol_id          TINYINT UNSIGNED NOT NULL DEFAULT 2,  -- 1=admin, 2=cliente
  nombre          VARCHAR(80)  NOT NULL,
  apellido        VARCHAR(80)  NOT NULL,
  username        VARCHAR(50)  NOT NULL UNIQUE,
  email           VARCHAR(120) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  avatar_url      VARCHAR(500),
  activo          BOOLEAN      NOT NULL DEFAULT TRUE,
  email_verificado BOOLEAN     NOT NULL DEFAULT FALSE,
  creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id_rol)
);

CREATE TABLE sesiones (
  id_sesion   CHAR(64) PRIMARY KEY,             -- token aleatorio
  usuario_id  INT UNSIGNED NOT NULL,
  ip          VARCHAR(45),
  user_agent  VARCHAR(255),
  expira_en   DATETIME NOT NULL,
  creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
CONSTRAINT fk_sesion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- ============================================================
--  2. CATÁLOGO
-- ============================================================

CREATE TABLE categorias (
  id_categoria SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL UNIQUE,
  descripcion TEXT,
  imagen_url  VARCHAR(500),
  activa      BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE plataformas (
  id_plataforma SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  icono VARCHAR(80) -- clase CSS o nombre de icono
);

CREATE TABLE generos (
  id_genero SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE marcas (
  id_marca SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  logo_url VARCHAR(500)
);

CREATE TABLE productos (
  id_producto       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id      SMALLINT UNSIGNED NOT NULL,
  marca_id          SMALLINT UNSIGNED,
  nombre            VARCHAR(200) NOT NULL,
  descripcion       TEXT,
  precio            DECIMAL(12,2) NOT NULL,
  precio_original   DECIMAL(12,2),            -- NULL = sin descuento
  stock             SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  imagen_principal  VARCHAR(500),
  destacado         BOOLEAN NOT NULL DEFAULT FALSE,
  activo            BOOLEAN NOT NULL DEFAULT TRUE,
  creado_en         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
CONSTRAINT fk_producto_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id_categoria),
CONSTRAINT fk_producto_marca     FOREIGN KEY (marca_id)     REFERENCES marcas(id_marca),
  CONSTRAINT chk_precio CHECK (precio > 0)
);

CREATE TABLE producto_imagenes (
  id_producto_imagen INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  producto_id INT UNSIGNED NOT NULL,
  url         VARCHAR(500) NOT NULL,
  orden       TINYINT UNSIGNED NOT NULL DEFAULT 0,
CONSTRAINT fk_pimg_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE CASCADE
);

-- Relación muchos-a-muchos productos ↔ plataformas
CREATE TABLE producto_plataformas (
  producto_id   INT UNSIGNED NOT NULL,
  plataforma_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (producto_id, plataforma_id),
CONSTRAINT fk_pp_producto   FOREIGN KEY (producto_id)   REFERENCES productos(id_producto)   ON DELETE CASCADE,
CONSTRAINT fk_pp_plataforma FOREIGN KEY (plataforma_id) REFERENCES plataformas(id_plataforma) ON DELETE CASCADE
);

-- Relación muchos-a-muchos productos ↔ géneros
CREATE TABLE producto_generos (
  producto_id INT UNSIGNED NOT NULL,
  genero_id   SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (producto_id, genero_id),
CONSTRAINT fk_pg_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE CASCADE,
CONSTRAINT fk_pg_genero FOREIGN KEY (genero_id) REFERENCES generos(id_genero) ON DELETE CASCADE
);

CREATE TABLE producto_ediciones (
  id_producto_edicion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  producto_id INT UNSIGNED NOT NULL,
  nombre      VARCHAR(80) NOT NULL,
  precio      DECIMAL(12,2) NOT NULL,
CONSTRAINT fk_ped_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE CASCADE
);

-- ============================================================
--  3. CARRITO Y LISTA DE DESEOS
-- ============================================================

CREATE TABLE carrito_items (
  id_carrito_item INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  edicion_id  INT UNSIGNED,
  cantidad    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  agregado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_carrito (usuario_id, producto_id, edicion_id),
  CONSTRAINT fk_ci_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_ci_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE CASCADE,
  CONSTRAINT fk_ci_edicion  FOREIGN KEY (edicion_id)  REFERENCES producto_ediciones(id_producto_edicion) ON DELETE SET NULL
);

CREATE TABLE wishlist (
  usuario_id  INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  agregado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, producto_id),
  CONSTRAINT fk_wl_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_wl_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE CASCADE
);

-- ============================================================
--  4. PEDIDOS
-- ============================================================

CREATE TABLE metodos_pago (
  id_metodo_pago TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE -- Visa, Mastercard, PSE, Nequi...
);

CREATE TABLE estados_pedido (
  id_estado_pedido TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL UNIQUE -- pendiente, procesando, enviado, completado, cancelado
);

CREATE TABLE pedidos (
  id_pedido        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id       INT UNSIGNED NOT NULL,
  estado_id        TINYINT UNSIGNED NOT NULL DEFAULT 1,
  metodo_pago_id   TINYINT UNSIGNED,
  subtotal         DECIMAL(12,2) NOT NULL,
  descuento        DECIMAL(12,2) NOT NULL DEFAULT 0,
  total            DECIMAL(12,2) NOT NULL,
  codigo_promo     VARCHAR(30),
  -- Dirección de envío (snapshot en el momento del pedido)
  envio_nombre     VARCHAR(160),
  envio_direccion  VARCHAR(300),
  envio_ciudad     VARCHAR(80),
  envio_pais       VARCHAR(60) DEFAULT 'Colombia',
  notas            TEXT,
  creado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ped_usuario FOREIGN KEY (usuario_id)     REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
  CONSTRAINT fk_ped_estado  FOREIGN KEY (estado_id)      REFERENCES estados_pedido(id_estado_pedido),
  CONSTRAINT fk_ped_metpago FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id_metodo_pago)
);

CREATE TABLE pedido_items (
  id_pedido_item INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id      INT UNSIGNED NOT NULL,
  producto_id    INT UNSIGNED NOT NULL,
  edicion_id     INT UNSIGNED,
  nombre_snapshot VARCHAR(200) NOT NULL,   -- nombre del producto al momento de comprar
  precio_unitario DECIMAL(12,2) NOT NULL,
  cantidad       SMALLINT UNSIGNED NOT NULL,
  subtotal       DECIMAL(12,2) GENERATED ALWAYS AS (precio_unitario * cantidad) STORED,
  CONSTRAINT fk_pi_pedido   FOREIGN KEY (pedido_id)   REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
  CONSTRAINT fk_pi_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE RESTRICT,
  CONSTRAINT fk_pi_edicion  FOREIGN KEY (edicion_id)  REFERENCES producto_ediciones(id_producto_edicion) ON DELETE SET NULL
);

-- ============================================================
--  5. CÓDIGOS PROMOCIONALES
-- ============================================================

CREATE TABLE codigos_promo (
  id_codigo_promo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo          VARCHAR(30) NOT NULL UNIQUE,
  tipo            ENUM('porcentaje','fijo') NOT NULL DEFAULT 'porcentaje',
  valor           DECIMAL(10,2) NOT NULL,         -- % o monto fijo COP
  minimo_compra   DECIMAL(12,2) NOT NULL DEFAULT 0,
  usos_maximos    SMALLINT UNSIGNED,              -- NULL = ilimitado
  usos_actuales   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  activo          BOOLEAN NOT NULL DEFAULT TRUE,
  fecha_inicio    DATETIME,
  fecha_fin       DATETIME,
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  6. RESEÑAS EDITORIALES
-- ============================================================

CREATE TABLE resenas (
  id_resena       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  producto_id     INT UNSIGNED NOT NULL,
  autor_id        INT UNSIGNED NOT NULL,           -- usuario con rol editor/admin
  titulo          VARCHAR(200) NOT NULL,
  contenido       LONGTEXT NOT NULL,
  calificacion    DECIMAL(3,1) NOT NULL,           -- 0.0 – 10.0
  imagen_portada  VARCHAR(500),
  publicada       BOOLEAN NOT NULL DEFAULT FALSE,
  publicada_en    DATETIME,
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
CONSTRAINT fk_res_producto FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE CASCADE,
CONSTRAINT fk_res_autor    FOREIGN KEY (autor_id)    REFERENCES usuarios(id_usuario)  ON DELETE RESTRICT,
  CONSTRAINT chk_calificacion CHECK (calificacion BETWEEN 0 AND 10)
);

-- ============================================================
--  7. OPINIONES DE COMUNIDAD
-- ============================================================

CREATE TABLE opiniones (
  id_opinion  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  plataforma_id SMALLINT UNSIGNED,
  titulo      VARCHAR(200) NOT NULL,
  contenido   TEXT NOT NULL,
  calificacion TINYINT UNSIGNED NOT NULL,         -- 1-5 estrellas
  aprobada    BOOLEAN NOT NULL DEFAULT FALSE,     -- moderación
  creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_op_usuario    FOREIGN KEY (usuario_id)    REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_op_producto   FOREIGN KEY (producto_id)   REFERENCES productos(id_producto) ON DELETE CASCADE,
  CONSTRAINT fk_op_plataforma FOREIGN KEY (plataforma_id) REFERENCES plataformas(id_plataforma) ON DELETE SET NULL,
  CONSTRAINT chk_estrellas    CHECK (calificacion BETWEEN 1 AND 5),
  UNIQUE KEY uq_opinion_usuario_producto (usuario_id, producto_id)
);

CREATE TABLE opinion_imagenes (
  id_opinion_imagen INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  opinion_id INT UNSIGNED NOT NULL,
  url        VARCHAR(500) NOT NULL,
  CONSTRAINT fk_oi_opinion FOREIGN KEY (opinion_id) REFERENCES opiniones(id_opinion) ON DELETE CASCADE
);

-- ============================================================
--  8. COMENTARIOS (en reseñas y opiniones)
-- ============================================================

CREATE TABLE comentarios (
  id_comentario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id   INT UNSIGNED NOT NULL,
  resena_id    INT UNSIGNED,
  opinion_id   INT UNSIGNED,
  contenido    TEXT NOT NULL,
  aprobado     BOOLEAN NOT NULL DEFAULT TRUE,
  creado_en    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_com_usuario  FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_com_resena   FOREIGN KEY (resena_id)  REFERENCES resenas(id_resena) ON DELETE CASCADE,
  CONSTRAINT fk_com_opinion  FOREIGN KEY (opinion_id) REFERENCES opiniones(id_opinion) ON DELETE CASCADE,
  -- Debe pertenecer a exactamente uno
  CONSTRAINT chk_com_destino CHECK (
    (resena_id IS NOT NULL AND opinion_id IS NULL) OR
    (resena_id IS NULL     AND opinion_id IS NOT NULL)
  )
);

-- ============================================================
--  9. DIRECCIONES DE ENVÍO
-- ============================================================

CREATE TABLE direcciones (
  id_direccion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id   INT UNSIGNED NOT NULL,
  alias        VARCHAR(40) DEFAULT 'Casa',
  nombre       VARCHAR(160) NOT NULL,
  direccion    VARCHAR(300) NOT NULL,
  ciudad       VARCHAR(80)  NOT NULL,
  departamento VARCHAR(80),
  codigo_postal VARCHAR(12),
  pais         VARCHAR(60)  NOT NULL DEFAULT 'Colombia',
  telefono     VARCHAR(20),
  predeterminada BOOLEAN NOT NULL DEFAULT FALSE,
  CONSTRAINT fk_dir_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- ============================================================
--  10. ÍNDICES DE RENDIMIENTO
-- ============================================================

CREATE INDEX idx_productos_categoria  ON productos(categoria_id);
CREATE INDEX idx_productos_activo     ON productos(activo);
CREATE INDEX idx_productos_destacado  ON productos(destacado);
CREATE INDEX idx_pedidos_usuario      ON pedidos(usuario_id);
CREATE INDEX idx_pedidos_estado       ON pedidos(estado_id);
CREATE INDEX idx_pedidos_creado       ON pedidos(creado_en);
CREATE INDEX idx_opiniones_producto   ON opiniones(producto_id);
CREATE INDEX idx_opiniones_aprobada   ON opiniones(aprobada);
CREATE INDEX idx_resenas_producto     ON resenas(producto_id);
CREATE INDEX idx_resenas_publicada    ON resenas(publicada);
CREATE INDEX idx_comentarios_resena   ON comentarios(resena_id);
CREATE INDEX idx_comentarios_opinion  ON comentarios(opinion_id);
CREATE INDEX idx_carrito_usuario      ON carrito_items(usuario_id);

-- ============================================================
--  11. DATOS SEMILLA (INSERT)
-- ============================================================

-- Roles
INSERT INTO roles (nombre, descripcion) VALUES
  ('admin',  'Acceso total al panel de administración'),
  ('cliente','Usuario comprador registrado'),
  ('editor', 'Puede crear y publicar reseñas editoriales');

-- Plataformas
INSERT INTO plataformas (nombre, icono) VALUES
  ('PlayStation 5',  'fab fa-playstation'),
  ('PlayStation 4',  'fab fa-playstation'),
  ('Xbox Series X',  'fab fa-xbox'),
  ('Nintendo Switch','fas fa-gamepad'),
  ('PC / Steam',     'fab fa-steam');

-- Géneros
INSERT INTO generos (nombre) VALUES
  ('Acción / Aventura'),
  ('RPG'),
  ('Terror / Survival'),
  ('Estrategia'),
  ('Deportes'),
  ('Indie'),
  ('Plataformas');

-- Categorías
INSERT INTO categorias (nombre, descripcion) VALUES
  ('Videojuegos', 'Juegos para todas las plataformas'),
  ('Periféricos',  'Teclados, ratones, auriculares y más'),
  ('Hardware',     'Tarjetas gráficas, RAM, almacenamiento'),
  ('Accesorios',   'Fundas, cables, soportes y extras'),
  ('Consolas',     'Consolas nuevas y reacondicionadas');

-- Marcas (sin slug)
INSERT INTO `marcas` (`id_marca`, `nombre`, `logo_url`) VALUES
(1, 'Larian Studios', NULL),
(2, 'Nintendo', NULL),
(3, 'Insomniac Games', NULL),
(4, 'Remedy Entertainment', NULL),
(5, 'Capcom', NULL),
(6, 'HyperX', NULL),
(7, 'Logitech', NULL),
(8, 'SteelSeries', NULL),
(9, 'LG', NULL),
(10, 'FromSoftware', NULL),
(11, 'Naughty Dog', NULL),
(12, 'CD Projekt Red', NULL),
(13, 'Bethesda', NULL),
(14, 'Ubisoft', NULL),
(15, 'Bandai Namco', NULL),
(16, 'Square Enix', NULL),
(17, 'Sega', NULL),
(18, 'Sony Interactive', NULL),
(19, 'Razer', NULL),
(20, 'Corsair', NULL),
(21, 'ASUS ROG', NULL),
(22, 'MSI', NULL),
(23, 'Kingston', NULL),
(24, 'Samsung', NULL),
(25, 'NVIDIA', NULL),
(26, 'AMD', NULL),
(27, 'Thrustmaster', NULL),
(28, '8BitDo', NULL),
(29, 'Elgato', NULL);

-- Métodos de pago
INSERT INTO metodos_pago (nombre) VALUES
  ('Visa'),('Mastercard'),('PSE'),('Nequi'),('Daviplata'),('Efecty');

-- Estados de pedido
INSERT INTO estados_pedido (nombre) VALUES
  ('pendiente'),('procesando'),('enviado'),('completado'),('cancelado');

-- Usuarios
INSERT INTO `usuarios` (`id_usuario`, `rol_id`, `nombre`, `apellido`, `username`, `email`, `password_hash`, `avatar_url`, `activo`, `email_verificado`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'Admin', 'ZonaPixel', 'admin_zp', 'admin@zonapixel.com', 'OtroXD1234', NULL, 1, 1, '2026-03-23 14:05:27', '2026-03-29 18:28:01'),
(2, 3, 'Equipo', 'Editorial', 'zp_staff', 'staff@zonapixel.com', 'asd12345', NULL, 1, 1, '2026-03-23 14:05:27', '2026-03-28 16:08:44'),
(3, 2, 'Carlos', 'Salcedo', 'carlos_s', 'carlos@example.com', 'petro123', NULL, 1, 1, '2026-03-23 14:05:27', '2026-03-28 16:09:08'),
(4, 2, 'Laura', 'García', 'laura_g', 'laura@example.com', '$2y$12$ClientHashPlaceholder2XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', NULL, 1, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(5, 2, 'Juan', 'Rodríguez', 'pixel_master', 'pixel@example.com', '$2y$12$ClientHashPlaceholder3XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', NULL, 1, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(6, 2, 'Ana', 'Devoto', 'ana_dev', 'ana@example.com', '$2y$12$ClientHashPlaceholder4XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', NULL, 1, 0, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(9, 2, 'Santiago', 'Mora', 'santi_mora', 'santiago.mora@gmail.com', '$2y$12$HashU008XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(10, 2, 'Valentina', 'Ospina', 'vale_ospina', 'valentina.ospina@hotmail.com', '$2y$12$HashU009XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(11, 2, 'Sebastián', 'Vargas', 'sebas_v', 'sebastian.v@gmail.com', '$2y$12$HashU010XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(12, 2, 'Isabella', 'Castro', 'isa_castro', 'isabella.castro@outlook.com', '$2y$12$HashU011XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(13, 2, 'Mateo', 'Jiménez', 'mateo_j', 'mateo.jimenez@gmail.com', '$2y$12$HashU012XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(14, 2, 'Camila', 'Herrera', 'cami_h', 'camila.h@yahoo.com', '$2y$12$HashU013XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(15, 2, 'Nicolás', 'Reyes', 'nico_reyes', 'nicolas.reyes@gmail.com', '$2y$12$HashU014XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(16, 2, 'Sofía', 'Mendoza', 'sofi_mendoza', 'sofia.mendoza@gmail.com', '$2y$12$HashU015XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(17, 2, 'Felipe', 'Gutiérrez', 'pipe_g', 'felipe.g@hotmail.com', '$2y$12$HashU016XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(18, 2, 'Daniela', 'Ramírez', 'dani_ramirez', 'daniela.r@gmail.com', '$2y$12$HashU017XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(19, 2, 'Alejandro', 'Torres', 'alejo_torres', 'alejandro.t@outlook.com', '$2y$12$HashU018XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(20, 2, 'Mariana', 'Díaz', 'mari_diaz', 'mariana.diaz@gmail.com', '$2y$12$HashU019XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(21, 2, 'David', 'Londoño', 'david_l', 'david.londono@gmail.com', '$2y$12$HashU020XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(22, 2, 'Paula', 'Martínez', 'pau_martinez', 'paula.martinez@yahoo.com', '$2y$12$HashU021XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(23, 2, 'Andrés', 'Ruiz', 'andres_ruiz', 'andres.ruiz@gmail.com', '$2y$12$HashU022XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 0, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(24, 2, 'Juliana', 'Gómez', 'juli_gomez', 'juliana.gomez@gmail.com', '$2y$12$HashU023XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(25, 2, 'Esteban', 'Peña', 'este_pena', 'esteban.pena@hotmail.com', '$2y$12$HashU024XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(26, 2, 'Natalia', 'Ríos', 'nata_rios', 'natalia.rios@gmail.com', '$2y$12$HashU025XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(27, 2, 'Miguel', 'Suárez', 'miguel_s', 'miguel.suarez@outlook.com', '$2y$12$HashU026XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(28, 2, 'Laura', 'Pineda', 'laura_pineda', 'laura.pineda@gmail.com', '$2y$12$HashU027XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(29, 2, 'Tomás', 'Arango', 'tomas_a', 'tomas.arango@gmail.com', '$2y$12$HashU028XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(30, 2, 'Carolina', 'Bernal', 'caro_bernal', 'carolina.bernal@yahoo.com', '$2y$12$HashU029XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(31, 2, 'Ricardo', 'Salazar', 'ricard_s', 'ricardo.salazar@gmail.com', '$2y$12$HashU030XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(32, 2, 'Manuela', 'Cárdenas', 'manu_cardenas', 'manuela.c@hotmail.com', '$2y$12$HashU031XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(33, 2, 'Jhon', 'Villamizar', 'jhon_v', 'jhon.v@gmail.com', '$2y$12$HashU032XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(34, 2, 'Andrea', 'Patiño', 'andrea_p', 'andrea.patino@gmail.com', '$2y$12$HashU033XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(35, 2, 'Luis', 'Caicedo', 'luis_cai', 'luis.caicedo@outlook.com', '$2y$12$HashU034XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(36, 3, 'Gabriela', 'Montoya', 'gabi_editor', 'gabi.montoya@zonapixel.com', '$2y$12$HashU035XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(37, 2, 'Cristian', 'Hurtado', 'cris_hurtado', 'cristian.h@gmail.com', '$2y$12$HashU036XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(38, 2, 'Paola', 'Escobar', 'paola_e', 'paola.escobar@yahoo.com', '$2y$12$HashU037XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(39, 2, 'Héctor', 'Zuleta', 'hector_z', 'hector.z@gmail.com', '$2y$12$HashU038XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 0, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(40, 2, 'Vanessa', 'Bermúdez', 'vanessa_b', 'vanessa.b@gmail.com', '$2y$12$HashU039XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(41, 2, 'Jorge', 'Acosta', 'jorge_acosta', 'jorge.acosta@hotmail.com', '$2y$12$HashU040XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(42, 2, 'Lina', 'Velásquez', 'lina_vel', 'lina.velasquez@gmail.com', '$2y$12$HashU041XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(43, 2, 'Óscar', 'Corredor', 'oscar_c', 'oscar.corredor@gmail.com', '$2y$12$HashU042XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(44, 3, 'Renata', 'Ibáñez', 'renata_ed', 'renata.ibanez@zonapixel.com', '$2y$12$HashU043XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(45, 2, 'Jerónimo', 'Pedraza', 'jero_p', 'jeronimo.p@gmail.com', '$2y$12$HashU044XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(46, 2, 'Sara', 'Nieto', 'sara_nieto', 'sara.nieto@outlook.com', '$2y$12$HashU045XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(47, 2, 'Iván', 'Cano', 'ivan_cano', 'ivan.cano@gmail.com', '$2y$12$HashU046XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(48, 2, 'Diana', 'Posada', 'diana_pos', 'diana.posada@yahoo.com', '$2y$12$HashU047XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(49, 2, 'Simón', 'Betancur', 'simon_bet', 'simon.bet@gmail.com', '$2y$12$HashU048XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(50, 2, 'Tatiana', 'Quintero', 'tati_q', 'tatiana.q@gmail.com', '$2y$12$HashU049XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(51, 2, 'Mauricio', 'Álvarez', 'mauri_alv', 'mauricio.a@hotmail.com', '$2y$12$HashU050XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(52, 2, 'Yesenia', 'Franco', 'yese_franco', 'yesenia.f@gmail.com', '$2y$12$HashU051XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(53, 2, 'Rodrigo', 'Barrera', 'rodri_b', 'rodrigo.b@gmail.com', '$2y$12$HashU052XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(54, 2, 'Melissa', 'Calderón', 'meli_cal', 'melissa.c@outlook.com', '$2y$12$HashU053XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(55, 2, 'Javier', 'Oliveros', 'javi_oli', 'javier.o@gmail.com', '$2y$12$HashU054XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 0, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(56, 2, 'Ximena', 'Sánchez', 'xime_s', 'ximena.s@gmail.com', '$2y$12$HashU055XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(57, 2, 'Brayan', 'Rincón', 'brayan_r', 'brayan.r@yahoo.com', '$2y$12$HashU056XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 0, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(58, 2, 'Marcela', 'Useche', 'marce_u', 'marcela.u@gmail.com', '$2y$12$HashU057XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx', NULL, 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43');


-- Productos 
INSERT INTO `productos` (`id_producto`, `categoria_id`, `marca_id`, `nombre`, `descripcion`, `precio`, `precio_original`, `stock`, `imagen_principal`, `destacado`, `activo`, `creado_en`, `actualizado_en`) VALUES
(4, 4, 4, 'Alan Wake 2', 'Remedy entrega su obra más ambiciosa: una secuela 13 años después que mezcla terror psicológico, acción cinematográfica y metaficción en dos historias paralelas entrelazadas.', 159900.00, NULL, 70, 'https://cdn1.epicgames.com/offer/c4763f236d08423eb47b4c3008779c84/EGS_AlanWake2_RemedyEntertainment_S2_1200x1600-c7c8091ddac0f9669c8e5905bca88aaa', 1, 1, '2026-03-23 14:05:27', '2026-03-24 17:33:38'),
(5, 1, 5, 'Resident Evil 4 Remake', 'Capcom reimagina su obra maestra de 2005 con gráficos de última generación, combate renovado, nuevas mecánicas de sigilo y una narrativa expandida que respeta y mejora el original.', 139900.00, 199900.00, 90, 'https://image.api.playstation.com/vulcan/ap/rnd/202210/0706/EVWyZD63pahuh95eKloFaJuC.png', 0, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(6, 1, 2, 'Super Mario Bros. Wonder', 'Nintendo introduce las Flores Maravilla: ítems que transforman radicalmente los niveles de formas inesperadas. Con multijugador local hasta 4 jugadores y más de 100 niveles únicos.', 199900.00, 249900.00, 55, 'https://assets.nintendo.com/image/upload/ar_16:9,c_lpad,w_1240/b_white/f_auto/q_auto/store/software/switch/70010000068688/1c5583f6bbce5bccdc923c25c35ba8f42128b55df84f4a2fbeea74b6d1d1516e', 0, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(7, 2, 6, 'HyperX Alloy Origins Core TKL', 'Construcción en aluminio CNC de grado aeroespacial, switches HyperX Red lineales, iluminación RGB por tecla y diseño TKL sin teclado numérico para mayor espacio en el escritorio.', 289900.00, 369900.00, 30, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(8, 2, 7, 'Logitech G Pro X Superlight 2', 'Diseñado con los mejores esports players del mundo. Pesa menos de 60g, sensor HERO 2, conectividad LIGHTSPEED 2.4GHz con hasta 95h de batería y pies de PTFE de grado 1.', 359900.00, 449900.00, 25, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 1, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(9, 2, 8, 'SteelSeries Arctis Nova Pro', 'Drivers de 40mm de neodimio de alta fidelidad, cancelación activa de ruido (ANC), transreceptores intercambiables para diferentes plataformas y hasta 22h de batería con carga rápida.', 619900.00, NULL, 18, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&q=80', 0, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(10, 2, 9, 'LG 27GP83B-B 27\" QHD IPS 165Hz', 'Panel IPS de 2560x1440, 165Hz con OC, tiempo de respuesta de 1ms GtG, AMD FreeSync Premium, HDR10, sRGB 99%. Ideal para gaming competitivo y creación de contenido.', 1189900.00, NULL, 12, 'https://images.unsplash.com/photo-1616763355603-9755a640a287?w=500&q=80', 0, 1, '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(13, 1, 2, 'fdafafafa', 'adasd', 1234.00, 1234.00, 21, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2SgUP564PlawSTNAUq9xyUc3b55f__1OarA&s', 0, 1, '2026-03-23 15:07:58', '2026-03-23 15:07:58'),
(14, 2, 1, 'minecraft', 'dfsjkmdafjknhknsdafm', 1231.00, NULL, 65535, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2SgUP564PlawSTNAUq9xyUc3b55f__1OarA&s', 1, 1, '2026-03-23 15:16:48', '2026-03-23 15:18:16'),
(16, 1, 10, 'Elden Ring', 'El RPG de acción de FromSoftware con mundo abierto codesarrollado con George R.R. Martin. Explora las Tierras Intermedias en un viaje de aventura, combate y descubrimiento sin parangón.', 159900.00, 219900.00, 110, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(17, 1, 10, 'Dark Souls III', 'La conclusión épica de la saga Souls. Combate exigente, mundo interconectado y lore profundo. Edición de fuego con todos los DLCs incluidos.', 99900.00, 149900.00, 75, 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(18, 1, 10, 'Sekiro: Shadows Die Twice', 'Un samurái en el Japón feudal. FromSoftware abandona el rol para apostar por sigilo, prótesis y un sistema de combate de deflecciones que redefine el género.', 119900.00, 119900.00, 60, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(19, 1, 11, 'The Last of Us Part I', 'La remasterización definitiva del clásico de Naughty Dog, reconstruida desde cero para PS5 con fidelidad gráfica completa, IA mejorada y una narrativa que sigue siendo de las mejores.', 219900.00, 269900.00, 50, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(20, 1, 11, 'Uncharted: Legacy of Thieves', 'Doble dosis de aventura con Nathan Drake y Chloe Frazer. Gráficos renovados, 60 fps y modo foto para PS5 y PC.', 129900.00, 179900.00, 40, 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(21, 1, 12, 'Cyberpunk 2077: Phantom Liberty', 'La expansión que transformó Cyberpunk en el juego que debió ser desde el inicio. Nueva zona, agente secreto, árbol de habilidades renovado y misiones a la altura de The Witcher 3.', 119900.00, 119900.00, 90, 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(22, 1, 12, 'The Witcher 3: Wild Hunt — Complete Edition', 'Considerado por muchos el mejor RPG de mundo abierto jamás creado. Incluye los DLCs Hearts of Stone y Blood & Wine. La versión next-gen añade modo ray-tracing y texturas 4K.', 89900.00, 139900.00, 120, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(23, 1, 13, 'Starfield', 'El primer RPG espacial de Bethesda en 25 años. Más de 1 000 planetas explorables, creación de personaje profunda y el motor Creation Engine 2 llevando la exploración a las estrellas.', 169900.00, 219900.00, 65, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(24, 1, 13, 'The Elder Scrolls V: Skyrim Anniversary Edition', 'La edición definitiva del RPG de fantasía más vendido de la historia. Más de 500 piezas de contenido adicional, todo el contenido Creation Club y compatibilidad mejorada.', 79900.00, 79900.00, 200, 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(25, 1, 14, 'Assassin\'s Creed Mirage', 'Un regreso a los orígenes con Basim en Bagdad del siglo IX. Parkour, sigilo y misiones de asesinato al estilo clásico de la saga, mapa compacto y narrativa centrada.', 149900.00, 189900.00, 55, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(26, 1, 15, 'Tekken 8', 'El rey de los juegos de lucha regresa con motor Unreal Engine 5, 32 luchadores en el lanzamiento, modo historia cinematográfico y el nuevo sistema Heat que transforma el combate.', 179900.00, 179900.00, 70, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(27, 1, 15, 'Dragon\'s Dogma 2', 'Capcom relanza su RPG de acción cult con un mundo completamente simulado. Cada NPC tiene rutinas propias, el sistema de Peones regresa mejorado y el combate es espectacular.', 189900.00, 189900.00, 45, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(28, 1, 16, 'Final Fantasy XVI', 'Un RPG de acción maduro ambientado en Valisthea. Clive Rosfield protagoniza una historia épica de invocaciones, guerras de naciones y un sistema de combate espectacular sin turnos.', 199900.00, 249900.00, 40, 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(29, 1, 16, 'Crisis Core: Final Fantasy VII Reunion', 'La precuela de FFVII completamente remasterizada. Sigue a Zack Fair en la historia que precedió los eventos del juego original, con combate modernizado y nuevo doblaje.', 119900.00, 159900.00, 35, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(30, 1, 17, 'Sonic Frontiers', 'Sonic abraza el mundo abierto en las Starfall Islands. Exploración libre, combate contra titanes y una banda sonora aclamada que reconcilió a la franquicia con sus fans.', 99900.00, 149900.00, 80, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(31, 1, 17, 'Persona 5 Royal', 'El JRPG de rol más premiado de la generación pasada. Los Phantom Thieves roban corazones con estilo inigualable, combate por turnos profundo y más de 130 horas de contenido.', 99900.00, 99900.00, 95, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(32, 1, 18, 'Returnal', 'Housemarque reimagina el roguelike con controles de bullet-hell y ambientación de terror psicológico. Cada ciclo redefine la narrativa en un loop de pesadilla adictivo.', 169900.00, 219900.00, 30, 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(33, 1, 2, 'Metroid Dread', 'El regreso triunfal de Samus Aran al 2D. Nació un nuevo estándar para el metroidvania moderno con los aterradores robots EMMI y un combate fluido y exigente.', 179900.00, 219900.00, 45, 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(34, 1, 2, 'Pikmin 4', 'La cuarta entrega de la saga de estrategia de Nintendo. Nuevas criaturas Pikmin, mapa expandido, modo cooperativo y el compañero canino Oatchi que cambia la exploración.', 179900.00, 179900.00, 50, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(35, 1, 14, 'Avatar: Frontiers of Pandora', 'Exploración de mundo abierto en Pandora con gráficos Snowdrop de última generación. Juego en primera persona desde la perspectiva de un Na\'vi con fauna y flora únicas.', 149900.00, 199900.00, 60, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(36, 2, 19, 'Razer DeathAdder V3 HyperSpeed', 'Diseño ergonómico icónico con sensor Focus Pro de 30 000 DPI, conectividad inalámbrica de ultra baja latencia y hasta 90 horas de batería. El mouse más popular rediseñado.', 279900.00, 349900.00, 35, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(38, 2, 20, 'Corsair K70 RGB Pro', 'Switches Cherry MX Red, construcción full aluminio, reposa-muñecas magnético de cuero PU y teclas PBT doble tiro. Iluminación RGB por tecla con ICUE.', 449900.00, 549900.00, 18, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(39, 2, 20, 'Corsair HS80 RGB Wireless', 'Auriculares inalámbricos con dolby atmos, drivers de 50mm, micrófono omni-direccional con supresión de ruido y hasta 20 horas de batería en modo inalámbrico.', 359900.00, 419900.00, 25, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(40, 2, 21, 'ASUS ROG Strix Impact III', 'Mouse ultra ligero de 59g diseñado para FPS competitivo. Sensor ROG AimPoint de 36 000 DPI, switches ópticos, cable paracord y diseño ambidiestro.', 199900.00, 249900.00, 40, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(41, 2, 21, 'ASUS ROG Swift OLED PG27AQDM', 'Monitor OLED 2560×1440 a 240Hz con tiempo de respuesta 0.03ms, cobertura DCI-P3 99%, brillo de 1 000 nits en HDR y soporte FreeSync Premium Pro.', 2389900.00, 2389900.00, 8, 'https://images.unsplash.com/photo-1616763355603-9755a640a287?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(42, 2, 7, 'Logitech MX Keys S', 'Teclado inalámbrico premium para productividad y gaming. Teclas de perfil bajo con retroiluminación inteligente, multi-dispositivo 3-en-1 y batería de 10 días.', 359900.00, 419900.00, 30, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(43, 2, 8, 'SteelSeries Rival 5', 'Mouse gaming con 9 botones programables, sensor TrueMove Air de 18 000 DPI, zona de descanso de pulgar ergonómica y 8 zonas de iluminación RGB individual.', 189900.00, 239900.00, 45, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(44, 2, 27, 'Thrustmaster T248 Racing Wheel', 'Volante de carreras con pedales, fuerza de respuesta magnética (HYBRID DRIVE), pantalla de telemetría integrada y compatibilidad con PS5, PS4 y PC.', 849900.00, 849900.00, 12, 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(45, 2, 28, '8BitDo Ultimate Bluetooth Controller', 'El mando de terceros más completo del mercado. Stick hall-effect, personalización total vía app, vibración dual y hasta 22 horas de juego. Compatible con Switch, PC y Android.', 219900.00, 269900.00, 55, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(46, 3, 25, 'NVIDIA GeForce RTX 4070 Super 12GB', 'Arquitectura Ada Lovelace con DLSS 3.5, Frame Generation y ray-tracing de cuarta generación. Ideal para 1440p ultra y 4K en títulos exigentes. TDP de solo 220W.', 1989900.00, 2299900.00, 10, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(47, 3, 25, 'NVIDIA GeForce RTX 4060 Ti 8GB', 'La opción ideal para 1080p y 1440p con soporte completo DLSS 3, ray-tracing y codificación AV1. Precio/rendimiento excepcional para gaming en resoluciones medias.', 1189900.00, 1389900.00, 15, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(48, 3, 26, 'AMD Radeon RX 7800 XT 16GB', '16GB GDDR6, arquitectura RDNA 3 con soporte FSR 3, ray-tracing mejorado y DisplayPort 2.1 para resoluciones hasta 8K. Competidor directo de la RTX 4070.', 1299900.00, 1299900.00, 12, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(49, 3, 23, 'Kingston Fury Beast DDR5 32GB (2×16) 6000MHz', 'Kit de memoria DDR5 XMP 3.0 optimizado para plataformas Intel y AMD AM5. Latencias CL36 y disipadores de aluminio de perfil bajo.', 489900.00, 589900.00, 30, 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(50, 3, 24, 'Samsung 990 Pro NVMe SSD 2TB', 'SSD PCIe 4.0 con velocidades de lectura de 7 450 MB/s y escritura de 6 900 MB/s. La opción premium para almacenamiento en PS5, consolas compatibles y PC.', 689900.00, 789900.00, 20, 'https://images.unsplash.com/photo-1531492746076-161ca9bcad58?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(51, 3, 22, 'MSI MAG B650 Tomahawk WiFi', 'Placa base AM5 con PCIe 5.0, DDR5 hasta 6600MHz OC, 2.5G LAN, WiFi 6E y soporte para Ryzen 7000. Diseño de 14+2+1 fases de potencia.', 679900.00, 679900.00, 10, 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(52, 3, 21, 'ASUS ROG STRIX 850W Gold ATX 3.0', 'Fuente de poder 80 Plus Gold con conector PCIe 5.0 nativo, protecciones OVP/OCP/SCP y cables planos para gestión fácil del cableado.', 589900.00, 689900.00, 14, 'https://images.unsplash.com/photo-1531492746076-161ca9bcad58?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(53, 3, 9, 'LG 32GQ850-B 32\" UHD IPS 144Hz', 'Panel Nano IPS 3840×2160 a 144Hz con tiempo de respuesta 1ms GtG, HDMI 2.1, DisplayPort 1.4, compatibilidad G-Sync y FreeSync Premium Pro.', 2899900.00, 2899900.00, 6, 'https://images.unsplash.com/photo-1616763355603-9755a640a287?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(54, 3, 20, 'Corsair iCUE H150i ELITE LCD', 'Refrigeración líquida AIO 360mm con pantalla LCD personalizable, tres ventiladores LL120 RGB, bomba de 2 400 RPM y soporte para Intel LGA 1700 y AMD AM5.', 579900.00, 699900.00, 8, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(55, 3, 22, 'MSI Clutch GM41 Lightweight V2', 'Mouse ultraligero de 55g para gaming profesional con sensor PMW-3370 de 26 000 DPI, switches Omron de 80 millones de clics y cable USB-C.', 159900.00, 199900.00, 50, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(56, 4, 29, 'Elgato Stream Deck MK.2', '15 teclas LCD personalizables para streaming, edición y automatización. Integración con OBS, Twitch, YouTube y más de 300 aplicaciones. Diseño modular con panel frontal intercambiable.', 489900.00, 549900.00, 20, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(57, 4, 29, 'Elgato 4K60 Pro MK.2 Capture Card', 'Capturadora interna PCIe para streaming y grabación en 4K60 HDR10 con VRR. Latencia ultra baja y compatibilidad con PS5, Xbox Series X y PC.', 849900.00, 849900.00, 10, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(58, 4, 6, 'HyperX ChargePlay Duo', 'Estación de carga dual para mandos DualSense y DualSense Edge. Carga en menos de 3 horas, LED indicador de estado y diseño compacto para escritorio.', 99900.00, 129900.00, 40, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(59, 4, NULL, 'Soporte Dual de Auriculares y Mandos — RGB', 'Soporte de aluminio con dos ganchos para auriculares y cuatro ranuras para mandos. Hub USB-A 3.0 integrado y tira LED RGB direccionable de 16.8M colores.', 139900.00, 139900.00, 35, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(60, 4, 19, 'Razer Mouse Bungee V3 Chroma', 'Soporte de cable con sujeción de resorte de 360° para movimiento sin obstáculos. Base con contrapeso de 204g, hub USB y cinco zonas de iluminación Chroma.', 99900.00, 129900.00, 28, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(61, 4, 20, 'Corsair MM700 RGB Extended Mousepad', 'Mousepad extendido 930×400mm con borde cosido, superficie micro-texturizada para máxima precisión y LED RGB de 360° con 16.8M colores y control ICUE.', 189900.00, 239900.00, 22, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(62, 5, 18, 'PlayStation 5 Slim — Edición Digital', 'La PS5 en su forma más compacta y ligera. 1TB SSD interno (expandible), soporte 8K, ray-tracing de hardware y retrocompatibilidad con toda la biblioteca PS4.', 1799900.00, 1799900.00, 20, 'https://images.unsplash.com/photo-1607853202273-232359dbb162?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(63, 5, 18, 'PlayStation 5 — God of War Bundle', 'Consola PS5 estándar con lector de disco, DualSense incluido y código descargable de God of War Ragnarök. Stock limitado.', 2099900.00, 2099900.00, 8, 'https://images.unsplash.com/photo-1607853202273-232359dbb162?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(64, 5, 2, 'Nintendo Switch OLED — Edición Especial Zelda', 'Switch OLED con pantalla de 7 pulgadas vibrante, diseño temático de The Legend of Zelda: Tears of the Kingdom en Joy-Con y base. Incluye 64GB de almacenamiento interno.', 1399900.00, 1599900.00, 12, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 1, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(65, 5, 18, 'DualSense Edge — Mando Inalámbrico Pro', 'El mando profesional de Sony para PS5. Palancas y gatillos intercambiables, perfiles de configuración guardados en la nube, cable trenzado de gran longitud y estuche de transporte.', 679900.00, 679900.00, 25, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(66, 5, 28, '8BitDo Retro Mechanical Keyboard — N Edition', 'Teclado mecánico con diseño inspirado en la NES, switches Hall Effect de 65g, conectividad USB-C/Bluetooth, iluminación RGB y soporte para Switch, PC y Mac.', 329900.00, 399900.00, 18, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-04-06 18:16:43', '2026-04-06 18:16:43'),
(67, 1, 10, 'Elden Ring', 'El RPG de acción de FromSoftware con mundo abierto codesarrollado con George R.R. Martin. Explora las Tierras Intermedias en un viaje de aventura, combate y descubrimiento sin parangón.', 159900.00, 219900.00, 110, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(68, 1, 10, 'Dark Souls III', 'La conclusión épica de la saga Souls. Combate exigente, mundo interconectado y lore profundo. Edición de fuego con todos los DLCs incluidos.', 99900.00, 149900.00, 75, 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(69, 1, 10, 'Sekiro: Shadows Die Twice', 'Un samurái en el Japón feudal. FromSoftware abandona el rol para apostar por sigilo, prótesis y un sistema de combate de deflecciones que redefine el género.', 119900.00, 119900.00, 60, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(70, 1, 11, 'The Last of Us Part I', 'La remasterización definitiva del clásico de Naughty Dog, reconstruida desde cero para PS5 con fidelidad gráfica completa, IA mejorada y una narrativa que sigue siendo de las mejores.', 219900.00, 269900.00, 50, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(71, 1, 11, 'Uncharted: Legacy of Thieves', 'Doble dosis de aventura con Nathan Drake y Chloe Frazer. Gráficos renovados, 60 fps y modo foto para PS5 y PC.', 129900.00, 179900.00, 40, 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(72, 1, 12, 'Cyberpunk 2077: Phantom Liberty', 'La expansión que transformó Cyberpunk en el juego que debió ser desde el inicio. Nueva zona, agente secreto, árbol de habilidades renovado y misiones a la altura de The Witcher 3.', 119900.00, 119900.00, 90, 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(73, 1, 12, 'The Witcher 3: Wild Hunt — Complete Edition', 'Considerado por muchos el mejor RPG de mundo abierto jamás creado. Incluye los DLCs Hearts of Stone y Blood & Wine. La versión next-gen añade modo ray-tracing y texturas 4K.', 89900.00, 139900.00, 120, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(74, 1, 13, 'Starfield', 'El primer RPG espacial de Bethesda en 25 años. Más de 1 000 planetas explorables, creación de personaje profunda y el motor Creation Engine 2 llevando la exploración a las estrellas.', 169900.00, 219900.00, 65, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(75, 1, 13, 'The Elder Scrolls V: Skyrim Anniversary Edition', 'La edición definitiva del RPG de fantasía más vendido de la historia. Más de 500 piezas de contenido adicional, todo el contenido Creation Club y compatibilidad mejorada.', 79900.00, 79900.00, 200, 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(76, 1, 14, 'Assassin\'s Creed Mirage', 'Un regreso a los orígenes con Basim en Bagdad del siglo IX. Parkour, sigilo y misiones de asesinato al estilo clásico de la saga, mapa compacto y narrativa centrada.', 149900.00, 189900.00, 55, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(77, 1, 15, 'Tekken 8', 'El rey de los juegos de lucha regresa con motor Unreal Engine 5, 32 luchadores en el lanzamiento, modo historia cinematográfico y el nuevo sistema Heat que transforma el combate.', 179900.00, 179900.00, 70, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(78, 1, 15, 'Dragon\'s Dogma 2', 'Capcom relanza su RPG de acción cult con un mundo completamente simulado. Cada NPC tiene rutinas propias, el sistema de Peones regresa mejorado y el combate es espectacular.', 189900.00, 189900.00, 45, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(79, 1, 16, 'Final Fantasy XVI', 'Un RPG de acción maduro ambientado en Valisthea. Clive Rosfield protagoniza una historia épica de invocaciones, guerras de naciones y un sistema de combate espectacular sin turnos.', 199900.00, 249900.00, 40, 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(80, 1, 16, 'Crisis Core: Final Fantasy VII Reunion', 'La precuela de FFVII completamente remasterizada. Sigue a Zack Fair en la historia que precedió los eventos del juego original, con combate modernizado y nuevo doblaje.', 119900.00, 159900.00, 35, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(81, 1, 17, 'Sonic Frontiers', 'Sonic abraza el mundo abierto en las Starfall Islands. Exploración libre, combate contra titanes y una banda sonora aclamada que reconcilió a la franquicia con sus fans.', 99900.00, 149900.00, 80, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(82, 1, 17, 'Persona 5 Royal', 'El JRPG de rol más premiado de la generación pasada. Los Phantom Thieves roban corazones con estilo inigualable, combate por turnos profundo y más de 130 horas de contenido.', 99900.00, 99900.00, 95, 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(83, 1, 18, 'Returnal', 'Housemarque reimagina el roguelike con controles de bullet-hell y ambientación de terror psicológico. Cada ciclo redefine la narrativa en un loop de pesadilla adictivo.', 169900.00, 219900.00, 30, 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(84, 1, 2, 'Metroid Dread', 'El regreso triunfal de Samus Aran al 2D. Nació un nuevo estándar para el metroidvania moderno con los aterradores robots EMMI y un combate fluido y exigente.', 179900.00, 219900.00, 45, 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(85, 1, 2, 'Pikmin 4', 'La cuarta entrega de la saga de estrategia de Nintendo. Nuevas criaturas Pikmin, mapa expandido, modo cooperativo y el compañero canino Oatchi que cambia la exploración.', 179900.00, 179900.00, 50, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(86, 1, 14, 'Avatar: Frontiers of Pandora', 'Exploración de mundo abierto en Pandora con gráficos Snowdrop de última generación. Juego en primera persona desde la perspectiva de un Na\'vi con fauna y flora únicas.', 149900.00, 199900.00, 60, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(87, 2, 19, 'Razer DeathAdder V3 HyperSpeed', 'Diseño ergonómico icónico con sensor Focus Pro de 30 000 DPI, conectividad inalámbrica de ultra baja latencia y hasta 90 horas de batería. El mouse más popular rediseñado.', 279900.00, 349900.00, 35, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(88, 2, 19, 'Razer BlackWidow V4 Pro', 'Teclado mecánico inalámbrico con switches Razer Yellow lineales, panel de control multimedia, modos de iluminación Chroma RGB y transmisión 2.4GHz con 200h de batería.', 589900.00, 589900.00, 20, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(89, 2, 20, 'Corsair K70 RGB Pro', 'Switches Cherry MX Red, construcción full aluminio, reposa-muñecas magnético de cuero PU y teclas PBT doble tiro. Iluminación RGB por tecla con ICUE.', 449900.00, 549900.00, 18, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(90, 2, 20, 'Corsair HS80 RGB Wireless', 'Auriculares inalámbricos con dolby atmos, drivers de 50mm, micrófono omni-direccional con supresión de ruido y hasta 20 horas de batería en modo inalámbrico.', 359900.00, 419900.00, 25, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(91, 2, 21, 'ASUS ROG Strix Impact III', 'Mouse ultra ligero de 59g diseñado para FPS competitivo. Sensor ROG AimPoint de 36 000 DPI, switches ópticos, cable paracord y diseño ambidiestro.', 199900.00, 249900.00, 40, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(92, 2, 21, 'ASUS ROG Swift OLED PG27AQDM', 'Monitor OLED 2560×1440 a 240Hz con tiempo de respuesta 0.03ms, cobertura DCI-P3 99%, brillo de 1 000 nits en HDR y soporte FreeSync Premium Pro.', 2389900.00, 2389900.00, 8, 'https://images.unsplash.com/photo-1616763355603-9755a640a287?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(93, 2, 7, 'Logitech MX Keys S', 'Teclado inalámbrico premium para productividad y gaming. Teclas de perfil bajo con retroiluminación inteligente, multi-dispositivo 3-en-1 y batería de 10 días.', 359900.00, 419900.00, 30, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(94, 2, 8, 'SteelSeries Rival 5', 'Mouse gaming con 9 botones programables, sensor TrueMove Air de 18 000 DPI, zona de descanso de pulgar ergonómica y 8 zonas de iluminación RGB individual.', 189900.00, 239900.00, 45, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(95, 2, 27, 'Thrustmaster T248 Racing Wheel', 'Volante de carreras con pedales, fuerza de respuesta magnética (HYBRID DRIVE), pantalla de telemetría integrada y compatibilidad con PS5, PS4 y PC.', 849900.00, 849900.00, 12, 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(96, 2, 28, '8BitDo Ultimate Bluetooth Controller', 'El mando de terceros más completo del mercado. Stick hall-effect, personalización total vía app, vibración dual y hasta 22 horas de juego. Compatible con Switch, PC y Android.', 219900.00, 269900.00, 55, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(97, 3, 25, 'NVIDIA GeForce RTX 4070 Super 12GB', 'Arquitectura Ada Lovelace con DLSS 3.5, Frame Generation y ray-tracing de cuarta generación. Ideal para 1440p ultra y 4K en títulos exigentes. TDP de solo 220W.', 1989900.00, 2299900.00, 10, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(98, 3, 25, 'NVIDIA GeForce RTX 4060 Ti 8GB', 'La opción ideal para 1080p y 1440p con soporte completo DLSS 3, ray-tracing y codificación AV1. Precio/rendimiento excepcional para gaming en resoluciones medias.', 1189900.00, 1389900.00, 15, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(99, 3, 26, 'AMD Radeon RX 7800 XT 16GB', '16GB GDDR6, arquitectura RDNA 3 con soporte FSR 3, ray-tracing mejorado y DisplayPort 2.1 para resoluciones hasta 8K. Competidor directo de la RTX 4070.', 1299900.00, 1299900.00, 12, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(100, 3, 23, 'Kingston Fury Beast DDR5 32GB (2×16) 6000MHz', 'Kit de memoria DDR5 XMP 3.0 optimizado para plataformas Intel y AMD AM5. Latencias CL36 y disipadores de aluminio de perfil bajo.', 489900.00, 589900.00, 30, 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(101, 3, 24, 'Samsung 990 Pro NVMe SSD 2TB', 'SSD PCIe 4.0 con velocidades de lectura de 7 450 MB/s y escritura de 6 900 MB/s. La opción premium para almacenamiento en PS5, consolas compatibles y PC.', 689900.00, 789900.00, 20, 'https://images.unsplash.com/photo-1531492746076-161ca9bcad58?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(102, 3, 22, 'MSI MAG B650 Tomahawk WiFi', 'Placa base AM5 con PCIe 5.0, DDR5 hasta 6600MHz OC, 2.5G LAN, WiFi 6E y soporte para Ryzen 7000. Diseño de 14+2+1 fases de potencia.', 679900.00, 679900.00, 10, 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(103, 3, 21, 'ASUS ROG STRIX 850W Gold ATX 3.0', 'Fuente de poder 80 Plus Gold con conector PCIe 5.0 nativo, protecciones OVP/OCP/SCP y cables planos para gestión fácil del cableado.', 589900.00, 689900.00, 14, 'https://images.unsplash.com/photo-1531492746076-161ca9bcad58?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(104, 3, 9, 'LG 32GQ850-B 32\" UHD IPS 144Hz', 'Panel Nano IPS 3840×2160 a 144Hz con tiempo de respuesta 1ms GtG, HDMI 2.1, DisplayPort 1.4, compatibilidad G-Sync y FreeSync Premium Pro.', 2899900.00, 2899900.00, 6, 'https://images.unsplash.com/photo-1616763355603-9755a640a287?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(105, 3, 20, 'Corsair iCUE H150i ELITE LCD', 'Refrigeración líquida AIO 360mm con pantalla LCD personalizable, tres ventiladores LL120 RGB, bomba de 2 400 RPM y soporte para Intel LGA 1700 y AMD AM5.', 579900.00, 699900.00, 8, 'https://images.unsplash.com/photo-1591799265444-d66432b91588?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(106, 3, 22, 'MSI Clutch GM41 Lightweight V2', 'Mouse ultraligero de 55g para gaming profesional con sensor PMW-3370 de 26 000 DPI, switches Omron de 80 millones de clics y cable USB-C.', 159900.00, 199900.00, 50, 'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(107, 4, 29, 'Elgato Stream Deck MK.2', '15 teclas LCD personalizables para streaming, edición y automatización. Integración con OBS, Twitch, YouTube y más de 300 aplicaciones. Diseño modular con panel frontal intercambiable.', 489900.00, 549900.00, 20, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(108, 4, 29, 'Elgato 4K60 Pro MK.2 Capture Card', 'Capturadora interna PCIe para streaming y grabación en 4K60 HDR10 con VRR. Latencia ultra baja y compatibilidad con PS5, Xbox Series X y PC.', 849900.00, 849900.00, 10, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(109, 4, 6, 'HyperX ChargePlay Duo', 'Estación de carga dual para mandos DualSense y DualSense Edge. Carga en menos de 3 horas, LED indicador de estado y diseño compacto para escritorio.', 99900.00, 129900.00, 40, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(110, 4, NULL, 'Soporte Dual de Auriculares y Mandos — RGB', 'Soporte de aluminio con dos ganchos para auriculares y cuatro ranuras para mandos. Hub USB-A 3.0 integrado y tira LED RGB direccionable de 16.8M colores.', 139900.00, 139900.00, 35, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(111, 4, 19, 'Razer Mouse Bungee V3 Chroma', 'Soporte de cable con sujeción de resorte de 360° para movimiento sin obstáculos. Base con contrapeso de 204g, hub USB y cinco zonas de iluminación Chroma.', 99900.00, 129900.00, 28, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(112, 4, 20, 'Corsair MM700 RGB Extended Mousepad', 'Mousepad extendido 930×400mm con borde cosido, superficie micro-texturizada para máxima precisión y LED RGB de 360° con 16.8M colores y control ICUE.', 189900.00, 239900.00, 22, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(113, 5, 18, 'PlayStation 5 Slim — Edición Digital', 'La PS5 en su forma más compacta y ligera. 1TB SSD interno (expandible), soporte 8K, ray-tracing de hardware y retrocompatibilidad con toda la biblioteca PS4.', 1799900.00, 1799900.00, 20, 'https://images.unsplash.com/photo-1607853202273-232359dbb162?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(114, 5, 18, 'PlayStation 5 — God of War Bundle', 'Consola PS5 estándar con lector de disco, DualSense incluido y código descargable de God of War Ragnarök. Stock limitado.', 2099900.00, 2099900.00, 8, 'https://images.unsplash.com/photo-1607853202273-232359dbb162?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(115, 5, 2, 'Nintendo Switch OLED — Edición Especial Zelda', 'Switch OLED con pantalla de 7 pulgadas vibrante, diseño temático de The Legend of Zelda: Tears of the Kingdom en Joy-Con y base. Incluye 64GB de almacenamiento interno.', 1399900.00, 1599900.00, 12, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 1, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(116, 5, 18, 'DualSense Edge — Mando Inalámbrico Pro', 'El mando profesional de Sony para PS5. Palancas y gatillos intercambiables, perfiles de configuración guardados en la nube, cable trenzado de gran longitud y estuche de transporte.', 679900.00, 679900.00, 25, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20'),
(117, 5, 28, '8BitDo Retro Mechanical Keyboard — N Edition', 'Teclado mecánico con diseño inspirado en la NES, switches Hall Effect de 65g, conectividad USB-C/Bluetooth, iluminación RGB y soporte para Switch, PC y Mac.', 329900.00, 399900.00, 18, 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80', 0, 1, '2026-04-06 18:18:20', '2026-04-06 18:18:20');

-- Ediciones especiales
INSERT INTO producto_ediciones (producto_id, nombre, precio) VALUES
  (1, 'Edición Estándar',       189900.00),
  (1, 'Edición Deluxe',         229900.00),
  (3, 'Edición Estándar',       179900.00),
  (3, 'Edición Digital Deluxe', 219900.00);

-- Plataformas por producto
INSERT INTO `producto_plataformas` (`producto_id`, `plataforma_id`) VALUES
(1, 1),
(1, 5),
(2, 4),
(3, 1),
(4, 1),
(4, 3),
(4, 5),
(5, 1),
(5, 2),
(5, 3),
(5, 5),
(6, 4),
(11, 1),
(11, 2),
(11, 3),
(11, 5),
(12, 2),
(12, 3),
(12, 5),
(13, 2),
(13, 3),
(13, 5),
(14, 1),
(14, 5),
(15, 1),
(15, 5),
(16, 1),
(16, 3),
(16, 5),
(17, 1),
(17, 3),
(17, 5),
(18, 3),
(18, 5),
(19, 1),
(19, 3),
(19, 5),
(20, 1),
(20, 3),
(20, 5),
(21, 1),
(21, 3),
(21, 5),
(22, 1),
(22, 3),
(22, 5),
(23, 1),
(24, 2),
(24, 4),
(24, 5),
(25, 1),
(25, 2),
(25, 3),
(25, 4),
(25, 5),
(26, 1),
(26, 2),
(26, 4),
(26, 5),
(27, 1),
(27, 5),
(28, 4),
(29, 4),
(30, 1),
(30, 3),
(30, 5);

-- Géneros por producto
INSERT INTO `producto_generos` (`producto_id`, `genero_id`) VALUES
(1, 2),
(2, 1),
(3, 1),
(4, 3),
(5, 3),
(6, 7),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(13, 1),
(14, 1),
(14, 3),
(15, 1),
(16, 1),
(16, 2),
(17, 1),
(17, 2),
(18, 1),
(18, 2),
(19, 1),
(19, 2),
(20, 1),
(21, 1),
(22, 1),
(22, 2),
(23, 1),
(23, 2),
(24, 1),
(24, 2),
(25, 1),
(26, 2),
(27, 3),
(28, 1),
(28, 7),
(29, 4),
(30, 1);


-- Reseñas editoriales (sin columnas slug ni resumen)
INSERT INTO `resenas` (`id_resena`, `producto_id`, `autor_id`, `titulo`, `contenido`, `calificacion`, `imagen_portada`, `publicada`, `publicada_en`, `creado_en`, `actualizado_en`) VALUES
(2, 1, 2, 'Baldur\'s Gate 3 — El RPG que redefinió el género', '<p>Larian Studios ha entregado algo que va más allá de lo que cualquier fan del género podía esperar. BG3 no es solo el mejor RPG de la generación — es una declaración de principios sobre diseño que respeta la inteligencia del jugador.</p><p>El sistema de combate por turnos basado en D&D 5e resulta profundo sin ser inaccesible. La cooperativa de hasta 4 jugadores añade una dimensión social que pocos RPGs logran.</p>', 1.0, 'https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png', 1, '2023-08-10 09:00:00', '2026-03-23 14:05:27', '2026-03-23 20:11:47'),
(3, 4, 2, 'Alan Wake 2 — Terror narrativo sin precedentes', '<p>Remedy ha construido algo verdaderamente singular. Alan Wake 2 es una secuela que no se parece a ningún otro juego: mezcla survival horror clásico con elementos de thriller nórdico y metaficción descarada que rompe la cuarta pared de formas sorprendentes.</p>', 9.0, 'https://cdn1.epicgames.com/offer/c4763f236d08423eb47b4c3008779c84/EGS_AlanWake2_RemedyEntertainment_S2_1200x1600-c7c8091ddac0f9669c8e5905bca88aaa', 1, '2023-10-27 08:00:00', '2026-03-23 14:05:27', '2026-03-23 14:05:27'),
(6, 2, 1, 'adsadasda', 'sadasdadsa', 4.0, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2SgUP564PlawSTNAUq9xyUc3b55f__1OarA&s', 1, NULL, '2026-03-23 14:58:01', '2026-03-23 14:58:01'),
(7, 5, 2, 'sddaasdfsadsadsada', 'fdaafdfadfadssa', 2.0, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQDJAgktsuYQT8cG00HL-MvpFRagtSqnpmd0Q&s', 1, NULL, '2026-03-24 16:18:52', '2026-03-24 16:19:12'),
(8, 16, 2, 'Elden Ring — La cumbre del género de acción RPG', '<p>FromSoftware tomó su fórmula consagrada y la abrió al mundo. El resultado es el RPG de acción más ambicioso que el estudio ha producido, con una colaboración narrativa con George R.R. Martin que eleva el lore a cotas insospechadas.</p><p>Las Tierras Intermedias son un mundo coherente y aterrador: cada zona tiene su propia lógica visual, sus propios jefes y sus propias recompensas narrativas. El sistema de combate mantiene la exigencia clásica de la saga pero añade caballo, sigilo y una variedad de builds sin precedentes.</p><p><strong>Veredicto:</strong> Un hito del género. Imprescindible.</p>', 9.8, NULL, 1, '2022-02-25 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(9, 19, 2, 'The Last of Us Part I — La remasterización que justifica el hardware', '<p>Naughty Dog no se limitó a subir la resolución. Reconstruyó cada modelo, cada animación y cada sistema de IA desde cero para PS5. El resultado es el juego más fiel visualmente a lo que la dirección artística original pretendía.</p><p>La narrativa sigue siendo de las más poderosas del medio. Joel y Ellie conforman uno de los dúos más memorables de la historia del videojuego, y el ritmo entre acción, sigilo y momentos de calma sigue siendo magistral.</p><p><strong>Veredicto:</strong> Referencia técnica y narrativa para la generación actual.</p>', 9.5, NULL, 1, '2022-09-02 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(10, 21, 36, 'Phantom Liberty — La expansión que cerró todas las deudas', '<p>CD Projekt Red tardó tres años en convertir Cyberpunk 2077 en el juego que prometió. Phantom Liberty es la confirmación de que lo lograron. La nueva zona de Dogtown es densa, vertical y peligrosa; las misiones de Reed y Songbird están al nivel de las mejores de The Witcher 3.</p><p>El árbol de habilidades rediseñado añade profundidad de build sin abrumar. Y la banda sonora —con Idris Elba en el reparto— se convierte en uno de los mejores papeles del año en cualquier medio.</p>', 9.3, NULL, 1, '2023-09-26 08:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(11, 28, 44, 'Final Fantasy XVI — Madurez narrativa al precio del rol clásico', '<p>Square Enix apostó por un tono adulto y un combate de acción puro, alejándose de los turnos que definieron la saga. El resultado es divisivo pero valiente. La historia de Clive Rosfield es la más oscura y cinematográfica de la franquicia: guerra, esclavitud y sacrificio sin concesiones.</p><p>El sistema de combate con invocaciones es espectacular —los enfrentamientos de Eikon son set pieces de primer nivel— pero la profundidad de rol es mínima comparada con entregas anteriores. Un juego de acción sobresaliente; un FF que polarizará a los fans del RPG clásico.</p>', 8.7, NULL, 1, '2023-06-22 11:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(12, 26, 2, 'Tekken 8 — El rey regresa con más músculo que nunca', '<p>Bandai Namco sabía que tenía que dar un golpe de efecto tras los años de Tekken 7. Lo consiguió. El motor Unreal Engine 5 entrega personajes con un nivel de detalle inédito en el género; cada impacto se siente en la pantalla y en el mando.</p><p>El sistema Heat transforma peleas que se creían perdidas en remontas épicas. El modo Historia cinematográfico, aunque narrativamente excesivo, es el más largo y variado de la saga. El roster de 32 personajes en el lanzamiento es generoso y equilibrado.</p>', 9.0, NULL, 1, '2024-01-26 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(13, 32, 36, 'Returnal — Housemarque redefine el roguelike de tercera persona', '<p>Pocos juegos de PS5 exigen tanto al jugador como Returnal. El ciclo de muerte y resurrección en Atropos no es frustrante —es adictivo. Cada run aporta nuevas armas, nuevos parásitos y nuevas piezas del rompecabezas narrativo.</p><p>El combate bullet-hell en tercera persona es preciso y exigente. La ambientación de terror cósmico funciona a la perfección y la banda sonora envuelve cada jefe en una tensión casi insoportable. No es para todos, pero los que conecten con él no podrán parar.</p>', 8.9, NULL, 1, '2021-04-30 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(14, 17, 44, 'Dark Souls III — La despedida perfecta de la saga Souls', '<p>FromSoftware cerró la trilogía con su entrega más rápida y más generosa en términos de jefes. Dark Souls III toma lo mejor de cada juego anterior —la interconexión de DS1, el lore de DS2, la velocidad de BloodBorne— y lo sintetiza en un producto acabado y equilibrado.</p><p>Los DLCs Ashes of Ariandel y The Ringed City son dos de los mejores contenidos descargables del estudio. La edición completa es la forma canónica de disfrutar este cierre épico.</p>', 9.2, NULL, 1, '2022-11-15 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(15, 22, 2, 'The Witcher 3 — Nueve años después sigue siendo el estándar', '<p>La actualización next-gen añade ray-tracing, texturas 4K y una lista de mejoras visuales que hacen que el juego se vea mejor que muchos títulos de 2023. Pero lo que hace grande a The Witcher 3 no es la técnica: es la escritura.</p><p>Las misiones secundarias de Blood and Wine y Hearts of Stone son mejores que la trama principal de muchos RPGs triple-A. Geralt, Yennefer, Ciri y compañía siguen siendo el reparto mejor construido del género. El estándar del RPG de mundo abierto hasta nuevo aviso.</p>', 9.7, NULL, 1, '2022-12-14 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(16, 33, 36, 'Metroid Dread — El mejor metroidvania en años', '<p>Nintendo y MercurySteam devolvieron a Samus Aran con un juego que respeta la herencia de la saga y al mismo tiempo establece nuevas cotas para el género. Los robots EMMI son el diseño de enemigo más angustiante que ha producido Nintendo: inevitables, silenciosos y mortales.</p><p>La movilidad de Samus es lo más fluida que ha sido nunca en un juego 2D. El diseño de niveles esconde sus rutas opcionales con maestría. Una lección de diseño de plataformas y exploración para toda la industria.</p>', 9.4, NULL, 1, '2021-10-08 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(17, 18, 44, 'Sekiro: Shadows Die Twice — El combate más puro de FromSoftware', '<p>FromSoftware abandonó el rol para centrarse en una sola cosa: el combate. Y la decisión fue brillante. El sistema de deflecciones y postura convierte cada enfrentamiento en un diálogo violento con reglas propias. No hay builds, no hay farming de stats: solo habilidad.</p><p>El Japón feudal de Sekiro es visualmente espectacular y narrativamente más accesible que los mundos Souls. Los jefes son los mejores diseñados del estudio. Un juego que exige mucho y da más a cambio.</p>', 9.5, NULL, 1, '2019-03-22 11:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(18, 31, 2, 'Persona 5 Royal — El JRPG más estiloso de su generación', '<p>Atlus tomó uno de los mejores JRPGs de la generación y lo hizo todavía mejor. Persona 5 Royal añade un nuevo semestre, un nuevo Palacio, personajes nuevos y ajustes de balance que mejoran cada aspecto del original.</p><p>El sistema de combate por turnos es profundo pero accesible. La dirección artística —con una paleta de rojo, negro y blanco que se extiende desde los menús hasta las mazmorras— es inimitable. Las 130+ horas se pasan sin sentir.</p>', 9.6, NULL, 1, '2020-03-31 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(19, 30, 36, 'Sonic Frontiers — Un mundo abierto imperfecto y sorprendentemente emocionante', '<p>Sonic Team hizo algo inesperado: intentó algo nuevo. Sonic Frontiers no es un juego perfecto —el mapa abierto es desigual y algunas misiones secundarias son tediosas— pero cuando funciona, captura la velocidad y la emoción de la saga mejor que cualquier entrega 3D en años.</p><p>Los combates contra los Titanes son set pieces que emocionan genuinamente, y la banda sonora de Tomoya Ohtani es una de las mejores del año. Un paso en la dirección correcta para la franquicia.</p>', 7.8, NULL, 1, '2022-11-08 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(20, 27, 44, 'Dragon\'s Dogma 2 — Un RPG vivo como pocos', '<p>Capcom diseñó Gransys como un mundo realmente simulado. Los NPCs tienen rutinas propias, las noches son genuinamente peligrosas y los caminos guardan encuentros que nunca son exactamente iguales. El sistema de Peones —compañeros creados por otros jugadores que aprenden de sus dueños— sigue siendo uno de los diseños multijugador más originales del género.</p><p>El combate es físico, impactante y tácticamente rico en las clases más complejas. Algunos problemas de rendimiento y la economía de la teletransportación generaron controversia, pero el juego en sí es una experiencia de RPG de acción excepcional.</p>', 8.8, NULL, 1, '2024-03-22 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(21, 23, 2, 'Starfield — La exploración espacial de Bethesda, para bien y para mal', '<p>Bethesda lleva sus fortalezas al espacio: worldbuilding denso, libertad de personaje enorme y un sistema de creación de naves adictivo. El Creation Engine 2 entrega planetas que, aunque generados proceduralmente, esconden suficientes puntos de interés para justificar la exploración.</p><p>El juego sufre cuando se compara con la visión romántica que muchos tenían: las transiciones a pie quiebran la inmersión, y la narrativa principal es la más anodina de Bethesda en años. Un RPG sólido y enorme que no alcanza la genialidad de Morrowind o Skyrim.</p>', 7.6, NULL, 1, '2023-09-06 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(22, 25, 36, 'AC Mirage — La vuelta a las raíces que los fans llevaban años pidiendo', '<p>Ubisoft escuchó. Mirage abandona los mapas de 100 horas y las listas de tareas interminables para ofrecer un asesino clásico en un Bagdad medieval recreado con mimo. El sistema de contrato, el parkour renovado y el énfasis en el sigilo son el regreso que la saga necesitaba.</p><p>El juego es corto —25 horas en completar al 100%— pero cada hora tiene sustancia. Basim es un protagonista interesante y la ciudad es un escenario de patio de recreo perfectamente diseñado. No reinventa nada; no necesita hacerlo.</p>', 8.2, NULL, 1, '2023-10-05 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(23, 29, 44, 'Crisis Core Reunion — La historia de Zack merece ser contada así', '<p>Square Enix remasterizó Crisis Core con un respeto infrecuente por el material original. El nuevo doblaje en varios idiomas y la banda sonora reorquestada elevan una historia que muchos jugadores de FF7 Remake encontrarán imprescindible para entender el destino de Cloud.</p><p>El combate modernizado funciona mejor que el original de PSP, aunque el sistema de ruleta sigue siendo idiosincrático. La duración es justa y el impacto emocional del tercer acto sigue siendo devastador décadas después.</p>', 8.5, NULL, 1, '2022-12-13 11:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(24, 34, 2, 'Pikmin 4 — La saga estratégica de Nintendo en su mejor momento', '<p>Nintendo diseñó Pikmin 4 pensando tanto en nuevos jugadores como en veteranos. La incorporación de Oatchi, el compañero canino, no es un gimmick: transforma la exploración y la gestión de Pikmin de formas que se integran perfectamente en el diseño de puzzles.</p><p>El modo nocturno añade una capa de defensa de bases que contrasta con el ritmo diurno de recolección. Las cuevas están llenas de ideas brillantes. El multijugador cooperativo funciona sin fisuras. El mejor Pikmin hasta la fecha.</p>', 9.1, NULL, 1, '2023-07-21 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(25, 20, 36, 'Uncharted: Legacy of Thieves — Dos aventuras que justifican el doble de precio', '<p>Naughty Dog empaquetó Uncharted 4 y The Lost Legacy con mejoras visuales para PS5 y el resultado es el paquete de aventuras más accesible del estudio. Los 60fps estables transforman la experiencia de juego, especialmente en las secciones de acción.</p><p>Drake y Chloe siguen siendo dos de los protagonistas más carismáticos del medio. Las cinemáticas aguantan la comparación con cualquier producción de Hollywood. El modo foto añade valor para los amantes de la dirección artística.</p>', 8.8, NULL, 1, '2022-01-28 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(26, 35, 44, 'Avatar: Frontiers of Pandora — El mundo vive, la jugabilidad flaquea', '<p>Lo que Ubisoft consiguió con el motor Snowdrop es difícilmente igualable: Pandora es el entorno de mundo abierto más bello y coherente que el estudio ha creado. La fauna y flora tienen vida propia, los cielos cambian con la hora y los biomas son radicalmente distintos entre sí.</p><p>El problema es que la jugabilidad sigue el manual de Ubisoft sin desviarse: actividades repetitivas, bases enemigas y árbol de habilidades convencional. Un mundo extraordinario atrapado en un diseño de juego ordinario.</p>', 7.4, NULL, 1, '2023-12-07 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(27, 46, 2, 'RTX 4070 Super — La GPU que redefinió el valor en gama media-alta', '<p>NVIDIA sorprendió al mercado con la revisión Super de su línea Ada Lovelace. La RTX 4070 Super ofrece rendimiento cercano a la 4070 Ti original a un precio considerablemente menor, con DLSS 3.5 y Frame Generation convirtiendo títulos de 4K en experiencias completamente fluidas.</p><p>El TDP de 220W la hace compatible con fuentes de poder modestas y su temperatura de operación es sobresaliente. Para quien juegue en 1440p o quiera explorar el 4K sin invertir en la gama ultra, es la elección obvia del momento.</p>', 9.2, NULL, 1, '2024-01-17 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(28, 8, 36, 'G Pro X Superlight 2 — El estándar de los esports revisado', '<p>Logitech no reinventó la rueda: la perfeccionó. El Superlight 2 toma lo que hacía grande al original —peso mínimo, sensor de clase mundial, conectividad impecable— y mejora cada especificación. El sensor HERO 2 con 32 000 DPI es el más preciso que ha fabricado la compañía.</p><p>La batería dura hasta 95 horas en uso real, la base de carga POWERPLAY es compatible y el peso de menos de 60 gramos se nota en las sesiones largas de FPS. El precio es elevado pero el rendimiento lo justifica plenamente.</p>', 9.4, NULL, 1, '2023-03-14 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(29, 41, 44, 'ROG Swift OLED PG27AQDM — El panel de gaming definitivo (por ahora)', '<p>ASUS entregó el monitor de gaming más completo del mercado en el momento de su lanzamiento. El panel OLED QHD a 240Hz combina la profundidad de negros característica de la tecnología con una tasa de refresco que ningún panel LCD puede igualar en movimiento.</p><p>El tiempo de respuesta de 0.03ms hace que el ghosting sea historia. El brillo en SDR es competitivo y en HDR es impresionante. El precio es elevado pero para quienes el monitor es la inversión central del setup, ninguna alternativa LCD compite.</p>', 9.3, NULL, 1, '2023-08-22 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(30, 62, 2, 'PS5 Slim Digital — La mejor forma de entrar al ecosistema Sony', '<p>Sony rediseñó la PS5 con un objetivo claro: hacerla más accesible en precio y en espacio físico. La versión Slim Digital consigue ambos objetivos sin sacrificar rendimiento. El SSD sigue siendo el más rápido de cualquier consola del mercado y la retrocompatibilidad con PS4 funciona a la perfección.</p><p>La ausencia de lector de disco es la única concesión real para quien todavía colecciona físico. Para el jugador digital, es la compra de consola más inteligente del año.</p>', 9.0, NULL, 1, '2023-11-10 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(31, 64, 36, 'Switch OLED Edición Zelda — Coleccionismo y rendimiento en un paquete', '<p>Nintendo combinó el mejor hardware portátil de su catálogo con uno de los diseños más bonitos de la saga. La pantalla OLED de 7 pulgadas transforma la experiencia portátil: los colores de Hyrule nunca se habían visto así en una Switch.</p><p>El almacenamiento de 64GB es un paso en la dirección correcta aunque todavía escaso para una biblioteca digital completa. Los Joy-Con temáticos son el punto alto del paquete. Para fans de Zelda o compradores de primera Switch, es el paquete perfecto.</p>', 9.1, NULL, 1, '2023-05-12 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(32, 7, 44, 'HyperX Alloy Origins Core — El teclado mecánico de referencia para gaming', '<p>HyperX lleva años perfeccionando su línea Alloy y el resultado es evidente. El Origins Core TKL tiene la construcción más sólida de su rango de precio: aluminio CNC verdadero, no plástico con acabado metálico. Los switches Red lineales tienen la actuación más limpia del mercado en esta gama.</p><p>La iluminación RGB por tecla es brillante y configurable. El formato TKL libera espacio de escritorio sin sacrificar teclas de función. Una compra difícil de criticar para cualquier jugador que busque su primer teclado mecánico serio.</p>', 9.0, NULL, 1, '2023-02-14 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(33, 50, 2, 'Samsung 990 Pro — La referencia del almacenamiento NVMe para consolas y PC', '<p>Samsung estableció un nuevo estándar con el 990 Pro. Las velocidades de lectura secuencial de 7 450 MB/s son las más rápidas disponibles en formato PCIe 4.0, y el rendimiento aleatorio es igualmente sobresaliente. En PS5, el tiempo de carga en los juegos optimizados mejora respecto al SSD interno original.</p><p>La temperatura de operación es controlada incluso sin disipador, lo que la hace ideal para instalación en consolas donde el espacio es limitado. La garantía de 5 años de Samsung añade tranquilidad a largo plazo.</p>', 9.3, NULL, 1, '2023-03-20 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(34, 38, 36, 'Corsair K70 RGB Pro — La construcción premium que esperabas', '<p>Corsair ha perfeccionado la K70 a lo largo de varios años y la versión Pro es el resultado más refinado. El aluminio anodizado del chasis no cruje, los switches Cherry MX Red tienen la actuación lineal más suave del mercado en esta gama, y el reposapuñecas de cuero sintético es genuinamente cómodo en sesiones largas.</p><p>La integración con el ecosistema iCUE es su punto diferencial: sincronización RGB con periféricos, juegos y hasta temperatura de la CPU. Un teclado para quien quiere el mejor setup integrado de Corsair.</p>', 8.8, NULL, 1, '2023-07-05 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(35, 56, 44, 'Stream Deck MK.2 — La herramienta que todo creador de contenido necesita', '<p>Elgato redefinió el flujo de trabajo del streamer con el Stream Deck original, y la versión MK.2 refina cada aspecto del diseño. Las 15 teclas LCD con retroiluminación son completamente personalizables y la integración con más de 300 aplicaciones —OBS, Twitch, YouTube, Adobe Premiere, Spotify— lo convierte en un hub de control universal.</p><p>El panel frontal intercambiable permite personalización física sin costo adicional. Para cualquier creador de contenido con más de tres aplicaciones abiertas simultáneamente, es una inversión que se amortiza rápidamente en productividad.</p>', 9.1, NULL, 1, '2023-09-15 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(36, 44, 2, 'Thrustmaster T248 — El volante que convierte a los no-simrracers', '<p>Thrustmaster encontró el punto dulce entre accesibilidad y prestaciones con el T248. El sistema HYBRID DRIVE combina motor y correa magnética para ofrecer una fuerza de respuesta que supera a su predecesor en casi todos los escenarios. La pantalla de telemetría integrada es el detalle que diferencia al T248 de la competencia en su rango de precio.</p><p>La compatibilidad con PS5, PS4 y PC lo hace versátil. Los pedales incluidos son sólidos aunque no alcanzan el nivel de unidades independientes de gama alta. El mejor punto de entrada al simracing serio del mercado.</p>', 8.7, NULL, 1, '2023-10-20 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(37, 45, 36, '8BitDo Ultimate Bluetooth — El tercer mando que supera a los primeros', '<p>8BitDo lleva años sorprendiendo al mercado con periféricos de calidad a precios razonables. El Ultimate Bluetooth es su apuesta más ambiciosa y la más exitosa. Los sticks con efecto Hall eliminan el drift de forma permanente —el problema endémico de DualSense y Joy-Con— y la ergonomía es comparable a un mando oficial de primera calidad.</p><p>La aplicación de configuración permite remapear botones, ajustar curvas de sticks y guardar perfiles. La compatibilidad con Switch, PC y Android lo hace el mando definitivo para quien juega en múltiples plataformas. Un referente de calidad/precio en periféricos de gaming.</p>', 9.2, NULL, 1, '2023-11-03 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(38, 48, 44, 'RX 7800 XT — AMD contraataca con 16GB y arquitectura RDNA 3', '<p>AMD hizo la apuesta correcta con la RX 7800 XT: 16GB de VRAM en una GPU de gama media es una ventaja práctica real en una era donde los juegos exigentes superan los 10GB con texturas en ultra. La arquitectura RDNA 3 mejora sustancialmente el rendimiento en rasterización respecto a la generación anterior.</p><p>FSR 3 sigue sin alcanzar la calidad de imagen de DLSS 3 en la mayoría de implementaciones, pero la brecha se está cerrando. Para el jugador en 1440p que prioriza no recortar texturas sobre otras consideraciones, es una compra muy sólida.</p>', 8.6, NULL, 1, '2023-09-06 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(39, 49, 2, 'Kingston Fury Beast DDR5 — La RAM que necesita tu plataforma AM5 o Intel', '<p>Kingston entró al mercado DDR5 con un kit que equilibra velocidad, latencias y precio mejor que prácticamente cualquier competidor. Los 6 000 MHz a CL36 son el punto óptimo para plataformas Ryzen 7000 y la compatibilidad XMP 3.0 garantiza que la configuración sea plug-and-play en placas base modernas.</p><p>El perfil bajo del disipador de aluminio permite instalar refrigeradores de gran tamaño sin problemas de espacio. Para quien está construyendo un sistema de nueva generación desde cero, es el punto de partida recomendado en memoria.</p>', 9.0, NULL, 1, '2023-05-25 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(40, 61, 36, 'Corsair MM700 Extended — El mousepad que completa cualquier setup', '<p>El MM700 Extended de Corsair es la referencia del mousepad de tela gaming en formato XL. La superficie micro-texturizada ofrece un equilibrio entre deslizamiento y control que satisface tanto a jugadores de bajo DPI como a los de configuraciones más rápidas.</p><p>El LED RGB perimetral con control iCUE permite sincronización con el resto del ecosistema Corsair. El borde cosido a mano aguanta el uso intensivo sin deshilacharse. Para quien quiere cubrir todo el escritorio con una superficie de calidad premium, no hay mejor opción.</p>', 8.9, NULL, 1, '2023-06-10 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(41, 51, 44, 'MSI MAG B650 Tomahawk — La placa base AM5 que no requiere compromisos', '<p>MSI diseñó la B650 Tomahawk para el jugador que quiere un sistema Ryzen 7000 completo sin pagar precio de X670E. El sistema de fases de potencia 14+2+1 maneja sin problemas los Ryzen 9 de alta potencia, y el soporte WiFi 6E junto con el puerto 2.5G LAN la hacen lista para cualquier conexión de red actual.</p><p>El soporte de DDR5 hasta 6 600 MHz OC cubre las kits más rápidas del mercado. El BIOS es limpio e intuitivo. Para armar un PC AM5 sin concesiones reales pero con presupuesto controlado, es la elección obvia.</p>', 9.0, NULL, 1, '2023-04-15 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(42, 9, 2, 'SteelSeries Arctis Nova Pro — El mejor sonido en auriculares gaming', '<p>SteelSeries posicionó el Arctis Nova Pro en el tope absoluto del mercado de auriculares gaming y las prestaciones lo justifican. Los drivers de neodimio de alta fidelidad producen un sonido que rivaliza con auriculares audiófilos a precio similar, y la cancelación activa de ruido es la mejor implementación en la categoría gaming.</p><p>El sistema de transreceptores intercambiables permite cambiar de PC a PlayStation a Nintendo sin reconectar nada. La gestión de batería dual —una carga mientras la otra funciona— elimina los tiempos muertos. El estándar definitivo de la categoría.</p>', 9.5, NULL, 1, '2022-08-10 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(43, 10, 36, 'LG 27GP83B-B — El monitor QHD IPS para gaming sin concesiones', '<p>LG lleva años perfeccionando sus paneles IPS para gaming y el 27GP83B-B es el resultado más equilibrado. El panel IPS a 165Hz con 1ms GtG ofrece la combinación de color preciso y respuesta rápida que los paneles VA y TN no pueden igualar simultáneamente.</p><p>FreeSync Premium y compatibilidad G-Sync eliminan el tearing en cualquier configuración. La cobertura sRGB del 99% lo hace apto tanto para jugar como para edición de contenido básica. El punto de partida ideal para cualquier setup de gaming serio en 1440p.</p>', 9.1, NULL, 1, '2023-01-20 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(44, 65, 44, 'DualSense Edge — El mando pro que PS5 merecía desde el primer día', '<p>Sony tardó en lanzar su respuesta al Xbox Elite Controller pero la espera valió la pena. El DualSense Edge mantiene toda la tecnología haptic y de gatillos adaptativos del DualSense estándar mientras añade palancas y traseros intercambiables, perfiles guardados en la nube y un cable trenzado de longitud generosa.</p><p>La batería es ligeramente menor que la del DualSense estándar debido a los componentes adicionales, pero la carga rápida mitiga el inconveniente. Para jugadores competitivos de PS5, es una inversión que cambia la experiencia de forma tangible.</p>', 9.0, NULL, 1, '2023-01-26 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(45, 52, 2, 'ASUS ROG STRIX 850W — La fuente que no te dará problemas en años', '<p>Una fuente de poder es el componente que menos glamour genera pero más crítico resulta cuando falla. ASUS diseñó la ROG STRIX 850W Gold con estándares de calidad que se notan en el uso diario: ventilador semi-pasivo silencioso, cables planos que facilitan la gestión, y el conector PCIe 5.0 nativo para GPUs de última generación.</p><p>La certificación 80 Plus Gold garantiza eficiencia real superior al 90% en carga parcial. Las protecciones contra sobrevoltaje, sobrecorriente y cortocircuito son completas. Inversión una vez, tranquilidad por una década.</p>', 8.9, NULL, 1, '2023-07-18 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(46, 54, 36, 'Corsair H150i ELITE LCD — Refrigeración y personalización en un paquete', '<p>Corsair combinó la eficiencia probada de su línea H150i con un diferencial claro: la pantalla LCD personalizable en la cabeza de la bomba. El resultado es un AIO que compite técnicamente con las mejores soluciones del mercado y añade un elemento visual único sin afectar el rendimiento.</p><p>Los tres ventiladores LL120 RGB generan ruido audible bajo carga máxima pero la temperatura de los procesadores de alta potencia —Ryzen 9 y Core i9— se mantiene bajo control incluso en overclock moderado. La compatibilidad con AM5 y LGA1700 cubre todas las plataformas actuales.</p>', 8.7, NULL, 1, '2023-08-30 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(47, 37, 44, 'Razer BlackWidow V4 Pro — El teclado inalámbrico que demuestra que se puede', '<p>Razer resolvió el problema histórico de los teclados inalámbricos gaming: la latencia. La conexión HyperSpeed 2.4GHz es indistinguible de cable en cualquier escenario competitivo real. Las 200 horas de batería con iluminación reducida o las ~40 con Chroma completo son cifras que eliminan la ansiedad de carga.</p><p>Los switches Yellow lineales son de los más suaves que Razer ha fabricado. El panel de control multimedia —una rueda y botones dedicados— es útil en sesiones de streaming. El precio es el único argumento en contra.</p>', 9.0, NULL, 1, '2023-04-20 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(48, 57, 2, 'Elgato 4K60 Pro MK.2 — La capturadora de referencia para content creators', '<p>Elgato estableció el estándar de las capturadoras internas con el 4K60 Pro y la versión MK.2 consolida esa posición. La captura en 4K60 con HDR10 y soporte VRR es la opción más completa disponible en formato PCIe, y la latencia ultra baja permite jugar a través de la capturadora sin perder inmersión.</p><p>La compatibilidad con PS5 y Xbox Series X para captura 4K es transparente. El software 4K Capture Utility es intuitivo y la integración con OBS Studio funciona sin configuración adicional. El referente de la categoría.</p>', 9.2, NULL, 1, '2023-10-30 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(49, 47, 36, 'RTX 4060 Ti — DLSS 3 democratiza el gaming en alta calidad', '<p>La RTX 4060 Ti tiene un argumento único en su rango de precio: Frame Generation exclusivo de Ada Lovelace. En los títulos que lo implementan, multiplica los fotogramas de forma visible, convirtiendo juegos exigentes en 1080p/1440p en experiencias completamente fluidas.</p><p>El rendimiento en rasterización pura es sólido aunque no revolucionario respecto a la generación anterior. Los 8GB de VRAM empiezan a ser un límite en algunos juegos con texturas en ultra. El precio/rendimiento en DLSS ON es excelente; en rasterización pura, la competencia de AMD ofrece más VRAM por precio similar.</p>', 8.4, NULL, 1, '2023-07-18 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(50, 36, 44, 'DeathAdder V3 HyperSpeed — La ergonomía icónica ahora inalámbrica', '<p>El DeathAdder es el ratón de gaming más vendido de la historia por una razón: su forma cabe en manos de casi cualquier jugador de agarre palm y claw. La versión V3 HyperSpeed toma esa forma y la combina con el sensor Focus Pro de 30 000 DPI y la conectividad HyperSpeed de Razer.</p><p>Las 90 horas de batería son genuinamente clase alta. El peso de 88g es razonable aunque no compite con los ultraligeros de menos de 60g del mercado. Para quien lleva años con un DeathAdder con cable, la transición inalámbrica no tiene contraindicaciones.</p>', 9.0, NULL, 1, '2023-05-10 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(51, 42, 2, 'Logitech MX Keys S — La productividad antes que el gaming, y funciona', '<p>Logitech diseñó el MX Keys S pensando en el trabajador que también juega, y el resultado es sorprendentemente competente para ambos usos. Las teclas de perfil bajo con retroiluminación que se adapta a la luz ambiental son cómodas en sesiones de escritura largas, y la respuesta es suficientemente precisa para gaming casual.</p><p>La conexión Bluetooth multi-dispositivo que permite saltar entre tres equipos con una tecla es el diferencial real. La batería de 10 días con retroiluminación activa es clase alta. Para setups mixtos de trabajo y gaming, no hay mejor teclado inalámbrico en el mercado.</p>', 8.8, NULL, 1, '2023-06-22 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(52, 40, 36, 'ASUS ROG Strix Impact III — 59g sin sacrificar botones ni sensor', '<p>ASUS entró tarde al mercado de los ratones ultraligeros pero lo hizo bien. El Strix Impact III pesa 59g gracias a un chasis sin perforaciones que mantiene la integridad estructural mientras elimina masa. El sensor AimPoint de 36 000 DPI es el más capaz que ha fabricado ROG.</p><p>Los switches ópticos eliminan el debounce delay sin sacrificar durabilidad. El diseño ambidiestro lo hace accesible a cualquier tipo de agarre. El cable paracord no genera resistencia en el mousepad. Una propuesta sólida en el segmento ultraligero con cable.</p>', 8.6, NULL, 1, '2023-09-28 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(53, 43, 44, 'SteelSeries Rival 5 — El MMO mouse que también funciona para FPS', '<p>SteelSeries diseñó el Rival 5 para jugadores que cambian de género con frecuencia. Los 9 botones programables cuidan al jugador de MMO y MOBA sin comprometer la forma para el FPS. El sensor TrueMove Air de 18 000 DPI es preciso y estable a cualquier velocidad.</p><p>Las 8 zonas de iluminación RGB individual son el toque de personalización más granular disponible en un ratón de su precio. La zona de descanso de pulgar ergonómica reduce la fatiga en sesiones largas. Un ratón con más versatilidad de la que el mercado suele ofrecer en su rango.</p>', 8.7, NULL, 1, '2023-11-15 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(54, 39, 2, 'Corsair HS80 RGB Wireless — 20 horas de Dolby Atmos sin cables', '<p>Corsair apostó por Dolby Atmos en el HS80 y la implementación es de las mejores disponibles en auriculares gaming. El audio espacial es convincente en juegos que lo soportan y la cancelación de ruido del micrófono omni-direccional filtra el ambiente de forma efectiva.</p><p>Los drivers de 50mm generan un sonido equilibrado con bajos bien definidos. Las 20 horas de batería son suficientes para sesiones largas sin ansiedad de carga. El precio está en el punto correcto para las prestaciones que ofrece. Una alternativa sólida al Arctis Nova Pro para quien no necesita ANC.</p>', 8.5, NULL, 1, '2023-04-08 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(55, 63, 36, 'PS5 Bundle God of War — El paquete de entrada perfecto al ecosistema Sony', '<p>Sony eligió bien el juego para este bundle. God of War Ragnarök es probablemente el exclusivo PS5 que mejor representa las capacidades de la consola: tiempos de carga instantáneos, uso de los gatillos adaptativos del DualSense, y un nivel de detalle visual que todavía no tienen rival en consola.</p><p>El DualSense incluido justifica por sí solo el diferencial de precio respecto a comprar consola y juego por separado. Para el comprador que llega al ecosistema PlayStation por primera vez, no hay mejor punto de entrada disponible en este momento.</p>', 9.2, NULL, 1, '2023-11-24 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(56, 66, 44, '8BitDo Retro Keyboard — La nostalgia no tiene por qué sacrificar rendimiento', '<p>8BitDo tomó el diseño de la NES —colores crema, proporciones robustas, tipografía retro— y lo construyó con tecnología 2024. Los switches Hall Effect son inmunes al drift y tienen la actuación más lineal y predecible del mercado. La compatibilidad con Switch, PC y Mac hace el teclado genuinamente versátil.</p><p>La conectividad dual USB-C y Bluetooth funciona sin latencia apreciable en modo cable. La iluminación RGB es un contraste interesante con la estética retro. Para fans de Nintendo con nostalgia de la era 8-bit, es una compra que no decepciona.</p>', 8.8, NULL, 1, '2023-12-01 09:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(57, 55, 2, 'MSI Clutch GM41 — La propuesta ultraligera más accesible del mercado', '<p>MSI demostró que los ratones ultraligeros no tienen que costar una fortuna. El GM41 V2 pesa 55g —menos que muchos competidores de precio doble— y el sensor PMW-3370 de 26 000 DPI es de los mejores disponibles en su categoría de precio.</p><p>Los switches Omron con 80 millones de clics garantizan durabilidad real. El cable USB-C detachable es un detalle infrecuente en esta gama de precio. Para el jugador competitivo que quiere pasar a un ultraligero sin inversión grande, es la puerta de entrada correcta.</p>', 8.8, NULL, 1, '2023-10-12 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(58, 58, 36, 'HyperX ChargePlay Duo — Simplicidad y utilidad en una base de carga', '<p>HyperX no intentó reinventar la rueda con el ChargePlay Duo: diseñó una estación de carga que hace exactamente lo que promete, de forma fiable y con buen acabado. Los 3 horas de carga completa para mandos DualSense son competitivos con cualquier alternativa del mercado.</p><p>El LED indicador de estado elimina la incertidumbre de si el mando está cargando correctamente. El diseño compacto cabe en cualquier escritorio. Para el jugador de PS5 que tiene dos mandos y está cansado de buscar cables, es la solución más limpia disponible.</p>', 8.6, NULL, 1, '2023-07-30 09:30:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(59, 53, 44, 'LG 32GQ850-B — El 4K144Hz que cambia el estándar de la gama alta', '<p>LG combinó la tecnología Nano IPS con 144Hz y HDMI 2.1 en un monitor que tiene pocos argumentos en contra más allá del precio. El 4K nativo a 144Hz con soporte G-Sync y FreeSync Premium Pro simultáneo elimina la limitación de plataforma: funciona óptimamente con PS5, Xbox Series X, RTX y RX por igual.</p><p>El tiempo de respuesta de 1ms GtG es el mejor disponible en tecnología IPS en este formato. La cobertura DCI-P3 amplia lo hace apto para edición de vídeo y fotografía además del gaming. La referencia de los monitores 4K para gaming en 32 pulgadas.</p>', 9.4, NULL, 1, '2023-09-22 10:00:00', '2026-04-07 22:39:55', '2026-04-07 22:39:55'),
(60, 16, 2, 'Elden Ring — La cumbre del género de acción RPG', '<p>FromSoftware tomó su fórmula consagrada y la abrió al mundo. El resultado es el RPG de acción más ambicioso que el estudio ha producido, con una colaboración narrativa con George R.R. Martin que eleva el lore a cotas insospechadas.</p><p>Las Tierras Intermedias son un mundo coherente y aterrador: cada zona tiene su propia lógica visual, sus propios jefes y sus propias recompensas narrativas. El sistema de combate mantiene la exigencia clásica de la saga pero añade caballo, sigilo y una variedad de builds sin precedentes.</p><p><strong>Veredicto:</strong> Un hito del género. Imprescindible.</p>', 9.8, NULL, 1, '2022-02-25 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(61, 19, 2, 'The Last of Us Part I — La remasterización que justifica el hardware', '<p>Naughty Dog no se limitó a subir la resolución. Reconstruyó cada modelo, cada animación y cada sistema de IA desde cero para PS5. El resultado es el juego más fiel visualmente a lo que la dirección artística original pretendía.</p><p>La narrativa sigue siendo de las más poderosas del medio. Joel y Ellie conforman uno de los dúos más memorables de la historia del videojuego, y el ritmo entre acción, sigilo y momentos de calma sigue siendo magistral.</p><p><strong>Veredicto:</strong> Referencia técnica y narrativa para la generación actual.</p>', 9.5, NULL, 1, '2022-09-02 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(62, 21, 36, 'Phantom Liberty — La expansión que cerró todas las deudas', '<p>CD Projekt Red tardó tres años en convertir Cyberpunk 2077 en el juego que prometió. Phantom Liberty es la confirmación de que lo lograron. La nueva zona de Dogtown es densa, vertical y peligrosa; las misiones de Reed y Songbird están al nivel de las mejores de The Witcher 3.</p><p>El árbol de habilidades rediseñado añade profundidad de build sin abrumar. Y la banda sonora —con Idris Elba en el reparto— se convierte en uno de los mejores papeles del año en cualquier medio.</p>', 9.3, NULL, 1, '2023-09-26 08:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(63, 28, 44, 'Final Fantasy XVI — Madurez narrativa al precio del rol clásico', '<p>Square Enix apostó por un tono adulto y un combate de acción puro, alejándose de los turnos que definieron la saga. El resultado es divisivo pero valiente. La historia de Clive Rosfield es la más oscura y cinematográfica de la franquicia: guerra, esclavitud y sacrificio sin concesiones.</p><p>El sistema de combate con invocaciones es espectacular —los enfrentamientos de Eikon son set pieces de primer nivel— pero la profundidad de rol es mínima comparada con entregas anteriores. Un juego de acción sobresaliente; un FF que polarizará a los fans del RPG clásico.</p>', 8.7, NULL, 1, '2023-06-22 11:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(64, 26, 2, 'Tekken 8 — El rey regresa con más músculo que nunca', '<p>Bandai Namco sabía que tenía que dar un golpe de efecto tras los años de Tekken 7. Lo consiguió. El motor Unreal Engine 5 entrega personajes con un nivel de detalle inédito en el género; cada impacto se siente en la pantalla y en el mando.</p><p>El sistema Heat transforma peleas que se creían perdidas en remontas épicas. El modo Historia cinematográfico, aunque narrativamente excesivo, es el más largo y variado de la saga. El roster de 32 personajes en el lanzamiento es generoso y equilibrado.</p>', 9.0, NULL, 1, '2024-01-26 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(65, 32, 36, 'Returnal — Housemarque redefine el roguelike de tercera persona', '<p>Pocos juegos de PS5 exigen tanto al jugador como Returnal. El ciclo de muerte y resurrección en Atropos no es frustrante —es adictivo. Cada run aporta nuevas armas, nuevos parásitos y nuevas piezas del rompecabezas narrativo.</p><p>El combate bullet-hell en tercera persona es preciso y exigente. La ambientación de terror cósmico funciona a la perfección y la banda sonora envuelve cada jefe en una tensión casi insoportable. No es para todos, pero los que conecten con él no podrán parar.</p>', 8.9, NULL, 1, '2021-04-30 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(66, 17, 44, 'Dark Souls III — La despedida perfecta de la saga Souls', '<p>FromSoftware cerró la trilogía con su entrega más rápida y más generosa en términos de jefes. Dark Souls III toma lo mejor de cada juego anterior —la interconexión de DS1, el lore de DS2, la velocidad de BloodBorne— y lo sintetiza en un producto acabado y equilibrado.</p><p>Los DLCs Ashes of Ariandel y The Ringed City son dos de los mejores contenidos descargables del estudio. La edición completa es la forma canónica de disfrutar este cierre épico.</p>', 9.2, NULL, 1, '2022-11-15 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(67, 22, 2, 'The Witcher 3 — Nueve años después sigue siendo el estándar', '<p>La actualización next-gen añade ray-tracing, texturas 4K y una lista de mejoras visuales que hacen que el juego se vea mejor que muchos títulos de 2023. Pero lo que hace grande a The Witcher 3 no es la técnica: es la escritura.</p><p>Las misiones secundarias de Blood and Wine y Hearts of Stone son mejores que la trama principal de muchos RPGs triple-A. Geralt, Yennefer, Ciri y compañía siguen siendo el reparto mejor construido del género. El estándar del RPG de mundo abierto hasta nuevo aviso.</p>', 9.7, NULL, 1, '2022-12-14 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(68, 33, 36, 'Metroid Dread — El mejor metroidvania en años', '<p>Nintendo y MercurySteam devolvieron a Samus Aran con un juego que respeta la herencia de la saga y al mismo tiempo establece nuevas cotas para el género. Los robots EMMI son el diseño de enemigo más angustiante que ha producido Nintendo: inevitables, silenciosos y mortales.</p><p>La movilidad de Samus es lo más fluida que ha sido nunca en un juego 2D. El diseño de niveles esconde sus rutas opcionales con maestría. Una lección de diseño de plataformas y exploración para toda la industria.</p>', 9.4, NULL, 1, '2021-10-08 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(69, 18, 44, 'Sekiro: Shadows Die Twice — El combate más puro de FromSoftware', '<p>FromSoftware abandonó el rol para centrarse en una sola cosa: el combate. Y la decisión fue brillante. El sistema de deflecciones y postura convierte cada enfrentamiento en un diálogo violento con reglas propias. No hay builds, no hay farming de stats: solo habilidad.</p><p>El Japón feudal de Sekiro es visualmente espectacular y narrativamente más accesible que los mundos Souls. Los jefes son los mejores diseñados del estudio. Un juego que exige mucho y da más a cambio.</p>', 9.5, NULL, 1, '2019-03-22 11:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(70, 31, 2, 'Persona 5 Royal — El JRPG más estiloso de su generación', '<p>Atlus tomó uno de los mejores JRPGs de la generación y lo hizo todavía mejor. Persona 5 Royal añade un nuevo semestre, un nuevo Palacio, personajes nuevos y ajustes de balance que mejoran cada aspecto del original.</p><p>El sistema de combate por turnos es profundo pero accesible. La dirección artística —con una paleta de rojo, negro y blanco que se extiende desde los menús hasta las mazmorras— es inimitable. Las 130+ horas se pasan sin sentir.</p>', 9.6, NULL, 1, '2020-03-31 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(71, 30, 36, 'Sonic Frontiers — Un mundo abierto imperfecto y sorprendentemente emocionante', '<p>Sonic Team hizo algo inesperado: intentó algo nuevo. Sonic Frontiers no es un juego perfecto —el mapa abierto es desigual y algunas misiones secundarias son tediosas— pero cuando funciona, captura la velocidad y la emoción de la saga mejor que cualquier entrega 3D en años.</p><p>Los combates contra los Titanes son set pieces que emocionan genuinamente, y la banda sonora de Tomoya Ohtani es una de las mejores del año. Un paso en la dirección correcta para la franquicia.</p>', 7.8, NULL, 1, '2022-11-08 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(72, 27, 44, 'Dragon\'s Dogma 2 — Un RPG vivo como pocos', '<p>Capcom diseñó Gransys como un mundo realmente simulado. Los NPCs tienen rutinas propias, las noches son genuinamente peligrosas y los caminos guardan encuentros que nunca son exactamente iguales. El sistema de Peones —compañeros creados por otros jugadores que aprenden de sus dueños— sigue siendo uno de los diseños multijugador más originales del género.</p><p>El combate es físico, impactante y tácticamente rico en las clases más complejas. Algunos problemas de rendimiento y la economía de la teletransportación generaron controversia, pero el juego en sí es una experiencia de RPG de acción excepcional.</p>', 8.8, NULL, 1, '2024-03-22 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(73, 23, 2, 'Starfield — La exploración espacial de Bethesda, para bien y para mal', '<p>Bethesda lleva sus fortalezas al espacio: worldbuilding denso, libertad de personaje enorme y un sistema de creación de naves adictivo. El Creation Engine 2 entrega planetas que, aunque generados proceduralmente, esconden suficientes puntos de interés para justificar la exploración.</p><p>El juego sufre cuando se compara con la visión romántica que muchos tenían: las transiciones a pie quiebran la inmersión, y la narrativa principal es la más anodina de Bethesda en años. Un RPG sólido y enorme que no alcanza la genialidad de Morrowind o Skyrim.</p>', 7.6, NULL, 1, '2023-09-06 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(74, 25, 36, 'AC Mirage — La vuelta a las raíces que los fans llevaban años pidiendo', '<p>Ubisoft escuchó. Mirage abandona los mapas de 100 horas y las listas de tareas interminables para ofrecer un asesino clásico en un Bagdad medieval recreado con mimo. El sistema de contrato, el parkour renovado y el énfasis en el sigilo son el regreso que la saga necesitaba.</p><p>El juego es corto —25 horas en completar al 100%— pero cada hora tiene sustancia. Basim es un protagonista interesante y la ciudad es un escenario de patio de recreo perfectamente diseñado. No reinventa nada; no necesita hacerlo.</p>', 8.2, NULL, 1, '2023-10-05 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(75, 29, 44, 'Crisis Core Reunion — La historia de Zack merece ser contada así', '<p>Square Enix remasterizó Crisis Core con un respeto infrecuente por el material original. El nuevo doblaje en varios idiomas y la banda sonora reorquestada elevan una historia que muchos jugadores de FF7 Remake encontrarán imprescindible para entender el destino de Cloud.</p><p>El combate modernizado funciona mejor que el original de PSP, aunque el sistema de ruleta sigue siendo idiosincrático. La duración es justa y el impacto emocional del tercer acto sigue siendo devastador décadas después.</p>', 8.5, NULL, 1, '2022-12-13 11:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(76, 34, 2, 'Pikmin 4 — La saga estratégica de Nintendo en su mejor momento', '<p>Nintendo diseñó Pikmin 4 pensando tanto en nuevos jugadores como en veteranos. La incorporación de Oatchi, el compañero canino, no es un gimmick: transforma la exploración y la gestión de Pikmin de formas que se integran perfectamente en el diseño de puzzles.</p><p>El modo nocturno añade una capa de defensa de bases que contrasta con el ritmo diurno de recolección. Las cuevas están llenas de ideas brillantes. El multijugador cooperativo funciona sin fisuras. El mejor Pikmin hasta la fecha.</p>', 9.1, NULL, 1, '2023-07-21 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01');
INSERT INTO `resenas` (`id_resena`, `producto_id`, `autor_id`, `titulo`, `contenido`, `calificacion`, `imagen_portada`, `publicada`, `publicada_en`, `creado_en`, `actualizado_en`) VALUES
(77, 20, 36, 'Uncharted: Legacy of Thieves — Dos aventuras que justifican el doble de precio', '<p>Naughty Dog empaquetó Uncharted 4 y The Lost Legacy con mejoras visuales para PS5 y el resultado es el paquete de aventuras más accesible del estudio. Los 60fps estables transforman la experiencia de juego, especialmente en las secciones de acción.</p><p>Drake y Chloe siguen siendo dos de los protagonistas más carismáticos del medio. Las cinemáticas aguantan la comparación con cualquier producción de Hollywood. El modo foto añade valor para los amantes de la dirección artística.</p>', 8.8, NULL, 1, '2022-01-28 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(78, 35, 44, 'Avatar: Frontiers of Pandora — El mundo vive, la jugabilidad flaquea', '<p>Lo que Ubisoft consiguió con el motor Snowdrop es difícilmente igualable: Pandora es el entorno de mundo abierto más bello y coherente que el estudio ha creado. La fauna y flora tienen vida propia, los cielos cambian con la hora y los biomas son radicalmente distintos entre sí.</p><p>El problema es que la jugabilidad sigue el manual de Ubisoft sin desviarse: actividades repetitivas, bases enemigas y árbol de habilidades convencional. Un mundo extraordinario atrapado en un diseño de juego ordinario.</p>', 7.4, NULL, 1, '2023-12-07 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(79, 46, 2, 'RTX 4070 Super — La GPU que redefinió el valor en gama media-alta', '<p>NVIDIA sorprendió al mercado con la revisión Super de su línea Ada Lovelace. La RTX 4070 Super ofrece rendimiento cercano a la 4070 Ti original a un precio considerablemente menor, con DLSS 3.5 y Frame Generation convirtiendo títulos de 4K en experiencias completamente fluidas.</p><p>El TDP de 220W la hace compatible con fuentes de poder modestas y su temperatura de operación es sobresaliente. Para quien juegue en 1440p o quiera explorar el 4K sin invertir en la gama ultra, es la elección obvia del momento.</p>', 9.2, NULL, 1, '2024-01-17 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(80, 8, 36, 'G Pro X Superlight 2 — El estándar de los esports revisado', '<p>Logitech no reinventó la rueda: la perfeccionó. El Superlight 2 toma lo que hacía grande al original —peso mínimo, sensor de clase mundial, conectividad impecable— y mejora cada especificación. El sensor HERO 2 con 32 000 DPI es el más preciso que ha fabricado la compañía.</p><p>La batería dura hasta 95 horas en uso real, la base de carga POWERPLAY es compatible y el peso de menos de 60 gramos se nota en las sesiones largas de FPS. El precio es elevado pero el rendimiento lo justifica plenamente.</p>', 9.4, NULL, 1, '2023-03-14 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(81, 41, 44, 'ROG Swift OLED PG27AQDM — El panel de gaming definitivo (por ahora)', '<p>ASUS entregó el monitor de gaming más completo del mercado en el momento de su lanzamiento. El panel OLED QHD a 240Hz combina la profundidad de negros característica de la tecnología con una tasa de refresco que ningún panel LCD puede igualar en movimiento.</p><p>El tiempo de respuesta de 0.03ms hace que el ghosting sea historia. El brillo en SDR es competitivo y en HDR es impresionante. El precio es elevado pero para quienes el monitor es la inversión central del setup, ninguna alternativa LCD compite.</p>', 9.3, NULL, 1, '2023-08-22 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(82, 62, 2, 'PS5 Slim Digital — La mejor forma de entrar al ecosistema Sony', '<p>Sony rediseñó la PS5 con un objetivo claro: hacerla más accesible en precio y en espacio físico. La versión Slim Digital consigue ambos objetivos sin sacrificar rendimiento. El SSD sigue siendo el más rápido de cualquier consola del mercado y la retrocompatibilidad con PS4 funciona a la perfección.</p><p>La ausencia de lector de disco es la única concesión real para quien todavía colecciona físico. Para el jugador digital, es la compra de consola más inteligente del año.</p>', 9.0, NULL, 1, '2023-11-10 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(83, 64, 36, 'Switch OLED Edición Zelda — Coleccionismo y rendimiento en un paquete', '<p>Nintendo combinó el mejor hardware portátil de su catálogo con uno de los diseños más bonitos de la saga. La pantalla OLED de 7 pulgadas transforma la experiencia portátil: los colores de Hyrule nunca se habían visto así en una Switch.</p><p>El almacenamiento de 64GB es un paso en la dirección correcta aunque todavía escaso para una biblioteca digital completa. Los Joy-Con temáticos son el punto alto del paquete. Para fans de Zelda o compradores de primera Switch, es el paquete perfecto.</p>', 9.1, NULL, 1, '2023-05-12 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(84, 7, 44, 'HyperX Alloy Origins Core — El teclado mecánico de referencia para gaming', '<p>HyperX lleva años perfeccionando su línea Alloy y el resultado es evidente. El Origins Core TKL tiene la construcción más sólida de su rango de precio: aluminio CNC verdadero, no plástico con acabado metálico. Los switches Red lineales tienen la actuación más limpia del mercado en esta gama.</p><p>La iluminación RGB por tecla es brillante y configurable. El formato TKL libera espacio de escritorio sin sacrificar teclas de función. Una compra difícil de criticar para cualquier jugador que busque su primer teclado mecánico serio.</p>', 9.0, NULL, 1, '2023-02-14 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(85, 50, 2, 'Samsung 990 Pro — La referencia del almacenamiento NVMe para consolas y PC', '<p>Samsung estableció un nuevo estándar con el 990 Pro. Las velocidades de lectura secuencial de 7 450 MB/s son las más rápidas disponibles en formato PCIe 4.0, y el rendimiento aleatorio es igualmente sobresaliente. En PS5, el tiempo de carga en los juegos optimizados mejora respecto al SSD interno original.</p><p>La temperatura de operación es controlada incluso sin disipador, lo que la hace ideal para instalación en consolas donde el espacio es limitado. La garantía de 5 años de Samsung añade tranquilidad a largo plazo.</p>', 9.3, NULL, 1, '2023-03-20 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(86, 38, 36, 'Corsair K70 RGB Pro — La construcción premium que esperabas', '<p>Corsair ha perfeccionado la K70 a lo largo de varios años y la versión Pro es el resultado más refinado. El aluminio anodizado del chasis no cruje, los switches Cherry MX Red tienen la actuación lineal más suave del mercado en esta gama, y el reposapuñecas de cuero sintético es genuinamente cómodo en sesiones largas.</p><p>La integración con el ecosistema iCUE es su punto diferencial: sincronización RGB con periféricos, juegos y hasta temperatura de la CPU. Un teclado para quien quiere el mejor setup integrado de Corsair.</p>', 8.8, NULL, 1, '2023-07-05 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(87, 56, 44, 'Stream Deck MK.2 — La herramienta que todo creador de contenido necesita', '<p>Elgato redefinió el flujo de trabajo del streamer con el Stream Deck original, y la versión MK.2 refina cada aspecto del diseño. Las 15 teclas LCD con retroiluminación son completamente personalizables y la integración con más de 300 aplicaciones —OBS, Twitch, YouTube, Adobe Premiere, Spotify— lo convierte en un hub de control universal.</p><p>El panel frontal intercambiable permite personalización física sin costo adicional. Para cualquier creador de contenido con más de tres aplicaciones abiertas simultáneamente, es una inversión que se amortiza rápidamente en productividad.</p>', 9.1, NULL, 1, '2023-09-15 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(88, 44, 2, 'Thrustmaster T248 — El volante que convierte a los no-simrracers', '<p>Thrustmaster encontró el punto dulce entre accesibilidad y prestaciones con el T248. El sistema HYBRID DRIVE combina motor y correa magnética para ofrecer una fuerza de respuesta que supera a su predecesor en casi todos los escenarios. La pantalla de telemetría integrada es el detalle que diferencia al T248 de la competencia en su rango de precio.</p><p>La compatibilidad con PS5, PS4 y PC lo hace versátil. Los pedales incluidos son sólidos aunque no alcanzan el nivel de unidades independientes de gama alta. El mejor punto de entrada al simracing serio del mercado.</p>', 8.7, NULL, 1, '2023-10-20 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(89, 45, 36, '8BitDo Ultimate Bluetooth — El tercer mando que supera a los primeros', '<p>8BitDo lleva años sorprendiendo al mercado con periféricos de calidad a precios razonables. El Ultimate Bluetooth es su apuesta más ambiciosa y la más exitosa. Los sticks con efecto Hall eliminan el drift de forma permanente —el problema endémico de DualSense y Joy-Con— y la ergonomía es comparable a un mando oficial de primera calidad.</p><p>La aplicación de configuración permite remapear botones, ajustar curvas de sticks y guardar perfiles. La compatibilidad con Switch, PC y Android lo hace el mando definitivo para quien juega en múltiples plataformas. Un referente de calidad/precio en periféricos de gaming.</p>', 9.2, NULL, 1, '2023-11-03 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(90, 48, 44, 'RX 7800 XT — AMD contraataca con 16GB y arquitectura RDNA 3', '<p>AMD hizo la apuesta correcta con la RX 7800 XT: 16GB de VRAM en una GPU de gama media es una ventaja práctica real en una era donde los juegos exigentes superan los 10GB con texturas en ultra. La arquitectura RDNA 3 mejora sustancialmente el rendimiento en rasterización respecto a la generación anterior.</p><p>FSR 3 sigue sin alcanzar la calidad de imagen de DLSS 3 en la mayoría de implementaciones, pero la brecha se está cerrando. Para el jugador en 1440p que prioriza no recortar texturas sobre otras consideraciones, es una compra muy sólida.</p>', 8.6, NULL, 1, '2023-09-06 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(91, 49, 2, 'Kingston Fury Beast DDR5 — La RAM que necesita tu plataforma AM5 o Intel', '<p>Kingston entró al mercado DDR5 con un kit que equilibra velocidad, latencias y precio mejor que prácticamente cualquier competidor. Los 6 000 MHz a CL36 son el punto óptimo para plataformas Ryzen 7000 y la compatibilidad XMP 3.0 garantiza que la configuración sea plug-and-play en placas base modernas.</p><p>El perfil bajo del disipador de aluminio permite instalar refrigeradores de gran tamaño sin problemas de espacio. Para quien está construyendo un sistema de nueva generación desde cero, es el punto de partida recomendado en memoria.</p>', 9.0, NULL, 1, '2023-05-25 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(92, 61, 36, 'Corsair MM700 Extended — El mousepad que completa cualquier setup', '<p>El MM700 Extended de Corsair es la referencia del mousepad de tela gaming en formato XL. La superficie micro-texturizada ofrece un equilibrio entre deslizamiento y control que satisface tanto a jugadores de bajo DPI como a los de configuraciones más rápidas.</p><p>El LED RGB perimetral con control iCUE permite sincronización con el resto del ecosistema Corsair. El borde cosido a mano aguanta el uso intensivo sin deshilacharse. Para quien quiere cubrir todo el escritorio con una superficie de calidad premium, no hay mejor opción.</p>', 8.9, NULL, 1, '2023-06-10 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(93, 51, 44, 'MSI MAG B650 Tomahawk — La placa base AM5 que no requiere compromisos', '<p>MSI diseñó la B650 Tomahawk para el jugador que quiere un sistema Ryzen 7000 completo sin pagar precio de X670E. El sistema de fases de potencia 14+2+1 maneja sin problemas los Ryzen 9 de alta potencia, y el soporte WiFi 6E junto con el puerto 2.5G LAN la hacen lista para cualquier conexión de red actual.</p><p>El soporte de DDR5 hasta 6 600 MHz OC cubre las kits más rápidas del mercado. El BIOS es limpio e intuitivo. Para armar un PC AM5 sin concesiones reales pero con presupuesto controlado, es la elección obvia.</p>', 9.0, NULL, 1, '2023-04-15 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(94, 9, 2, 'SteelSeries Arctis Nova Pro — El mejor sonido en auriculares gaming', '<p>SteelSeries posicionó el Arctis Nova Pro en el tope absoluto del mercado de auriculares gaming y las prestaciones lo justifican. Los drivers de neodimio de alta fidelidad producen un sonido que rivaliza con auriculares audiófilos a precio similar, y la cancelación activa de ruido es la mejor implementación en la categoría gaming.</p><p>El sistema de transreceptores intercambiables permite cambiar de PC a PlayStation a Nintendo sin reconectar nada. La gestión de batería dual —una carga mientras la otra funciona— elimina los tiempos muertos. El estándar definitivo de la categoría.</p>', 9.5, NULL, 1, '2022-08-10 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(95, 10, 36, 'LG 27GP83B-B — El monitor QHD IPS para gaming sin concesiones', '<p>LG lleva años perfeccionando sus paneles IPS para gaming y el 27GP83B-B es el resultado más equilibrado. El panel IPS a 165Hz con 1ms GtG ofrece la combinación de color preciso y respuesta rápida que los paneles VA y TN no pueden igualar simultáneamente.</p><p>FreeSync Premium y compatibilidad G-Sync eliminan el tearing en cualquier configuración. La cobertura sRGB del 99% lo hace apto tanto para jugar como para edición de contenido básica. El punto de partida ideal para cualquier setup de gaming serio en 1440p.</p>', 9.1, NULL, 1, '2023-01-20 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(96, 65, 44, 'DualSense Edge — El mando pro que PS5 merecía desde el primer día', '<p>Sony tardó en lanzar su respuesta al Xbox Elite Controller pero la espera valió la pena. El DualSense Edge mantiene toda la tecnología haptic y de gatillos adaptativos del DualSense estándar mientras añade palancas y traseros intercambiables, perfiles guardados en la nube y un cable trenzado de longitud generosa.</p><p>La batería es ligeramente menor que la del DualSense estándar debido a los componentes adicionales, pero la carga rápida mitiga el inconveniente. Para jugadores competitivos de PS5, es una inversión que cambia la experiencia de forma tangible.</p>', 9.0, NULL, 1, '2023-01-26 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(97, 52, 2, 'ASUS ROG STRIX 850W — La fuente que no te dará problemas en años', '<p>Una fuente de poder es el componente que menos glamour genera pero más crítico resulta cuando falla. ASUS diseñó la ROG STRIX 850W Gold con estándares de calidad que se notan en el uso diario: ventilador semi-pasivo silencioso, cables planos que facilitan la gestión, y el conector PCIe 5.0 nativo para GPUs de última generación.</p><p>La certificación 80 Plus Gold garantiza eficiencia real superior al 90% en carga parcial. Las protecciones contra sobrevoltaje, sobrecorriente y cortocircuito son completas. Inversión una vez, tranquilidad por una década.</p>', 8.9, NULL, 1, '2023-07-18 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(98, 54, 36, 'Corsair H150i ELITE LCD — Refrigeración y personalización en un paquete', '<p>Corsair combinó la eficiencia probada de su línea H150i con un diferencial claro: la pantalla LCD personalizable en la cabeza de la bomba. El resultado es un AIO que compite técnicamente con las mejores soluciones del mercado y añade un elemento visual único sin afectar el rendimiento.</p><p>Los tres ventiladores LL120 RGB generan ruido audible bajo carga máxima pero la temperatura de los procesadores de alta potencia —Ryzen 9 y Core i9— se mantiene bajo control incluso en overclock moderado. La compatibilidad con AM5 y LGA1700 cubre todas las plataformas actuales.</p>', 8.7, NULL, 1, '2023-08-30 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(99, 37, 44, 'Razer BlackWidow V4 Pro — El teclado inalámbrico que demuestra que se puede', '<p>Razer resolvió el problema histórico de los teclados inalámbricos gaming: la latencia. La conexión HyperSpeed 2.4GHz es indistinguible de cable en cualquier escenario competitivo real. Las 200 horas de batería con iluminación reducida o las ~40 con Chroma completo son cifras que eliminan la ansiedad de carga.</p><p>Los switches Yellow lineales son de los más suaves que Razer ha fabricado. El panel de control multimedia —una rueda y botones dedicados— es útil en sesiones de streaming. El precio es el único argumento en contra.</p>', 9.0, NULL, 1, '2023-04-20 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(100, 57, 2, 'Elgato 4K60 Pro MK.2 — La capturadora de referencia para content creators', '<p>Elgato estableció el estándar de las capturadoras internas con el 4K60 Pro y la versión MK.2 consolida esa posición. La captura en 4K60 con HDR10 y soporte VRR es la opción más completa disponible en formato PCIe, y la latencia ultra baja permite jugar a través de la capturadora sin perder inmersión.</p><p>La compatibilidad con PS5 y Xbox Series X para captura 4K es transparente. El software 4K Capture Utility es intuitivo y la integración con OBS Studio funciona sin configuración adicional. El referente de la categoría.</p>', 9.2, NULL, 1, '2023-10-30 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(101, 47, 36, 'RTX 4060 Ti — DLSS 3 democratiza el gaming en alta calidad', '<p>La RTX 4060 Ti tiene un argumento único en su rango de precio: Frame Generation exclusivo de Ada Lovelace. En los títulos que lo implementan, multiplica los fotogramas de forma visible, convirtiendo juegos exigentes en 1080p/1440p en experiencias completamente fluidas.</p><p>El rendimiento en rasterización pura es sólido aunque no revolucionario respecto a la generación anterior. Los 8GB de VRAM empiezan a ser un límite en algunos juegos con texturas en ultra. El precio/rendimiento en DLSS ON es excelente; en rasterización pura, la competencia de AMD ofrece más VRAM por precio similar.</p>', 8.4, NULL, 1, '2023-07-18 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(102, 36, 44, 'DeathAdder V3 HyperSpeed — La ergonomía icónica ahora inalámbrica', '<p>El DeathAdder es el ratón de gaming más vendido de la historia por una razón: su forma cabe en manos de casi cualquier jugador de agarre palm y claw. La versión V3 HyperSpeed toma esa forma y la combina con el sensor Focus Pro de 30 000 DPI y la conectividad HyperSpeed de Razer.</p><p>Las 90 horas de batería son genuinamente clase alta. El peso de 88g es razonable aunque no compite con los ultraligeros de menos de 60g del mercado. Para quien lleva años con un DeathAdder con cable, la transición inalámbrica no tiene contraindicaciones.</p>', 9.0, NULL, 1, '2023-05-10 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(103, 42, 2, 'Logitech MX Keys S — La productividad antes que el gaming, y funciona', '<p>Logitech diseñó el MX Keys S pensando en el trabajador que también juega, y el resultado es sorprendentemente competente para ambos usos. Las teclas de perfil bajo con retroiluminación que se adapta a la luz ambiental son cómodas en sesiones de escritura largas, y la respuesta es suficientemente precisa para gaming casual.</p><p>La conexión Bluetooth multi-dispositivo que permite saltar entre tres equipos con una tecla es el diferencial real. La batería de 10 días con retroiluminación activa es clase alta. Para setups mixtos de trabajo y gaming, no hay mejor teclado inalámbrico en el mercado.</p>', 8.8, NULL, 1, '2023-06-22 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(104, 40, 36, 'ASUS ROG Strix Impact III — 59g sin sacrificar botones ni sensor', '<p>ASUS entró tarde al mercado de los ratones ultraligeros pero lo hizo bien. El Strix Impact III pesa 59g gracias a un chasis sin perforaciones que mantiene la integridad estructural mientras elimina masa. El sensor AimPoint de 36 000 DPI es el más capaz que ha fabricado ROG.</p><p>Los switches ópticos eliminan el debounce delay sin sacrificar durabilidad. El diseño ambidiestro lo hace accesible a cualquier tipo de agarre. El cable paracord no genera resistencia en el mousepad. Una propuesta sólida en el segmento ultraligero con cable.</p>', 8.6, NULL, 1, '2023-09-28 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(105, 43, 44, 'SteelSeries Rival 5 — El MMO mouse que también funciona para FPS', '<p>SteelSeries diseñó el Rival 5 para jugadores que cambian de género con frecuencia. Los 9 botones programables cuidan al jugador de MMO y MOBA sin comprometer la forma para el FPS. El sensor TrueMove Air de 18 000 DPI es preciso y estable a cualquier velocidad.</p><p>Las 8 zonas de iluminación RGB individual son el toque de personalización más granular disponible en un ratón de su precio. La zona de descanso de pulgar ergonómica reduce la fatiga en sesiones largas. Un ratón con más versatilidad de la que el mercado suele ofrecer en su rango.</p>', 8.7, NULL, 1, '2023-11-15 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(106, 39, 2, 'Corsair HS80 RGB Wireless — 20 horas de Dolby Atmos sin cables', '<p>Corsair apostó por Dolby Atmos en el HS80 y la implementación es de las mejores disponibles en auriculares gaming. El audio espacial es convincente en juegos que lo soportan y la cancelación de ruido del micrófono omni-direccional filtra el ambiente de forma efectiva.</p><p>Los drivers de 50mm generan un sonido equilibrado con bajos bien definidos. Las 20 horas de batería son suficientes para sesiones largas sin ansiedad de carga. El precio está en el punto correcto para las prestaciones que ofrece. Una alternativa sólida al Arctis Nova Pro para quien no necesita ANC.</p>', 8.5, NULL, 1, '2023-04-08 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(107, 63, 36, 'PS5 Bundle God of War — El paquete de entrada perfecto al ecosistema Sony', '<p>Sony eligió bien el juego para este bundle. God of War Ragnarök es probablemente el exclusivo PS5 que mejor representa las capacidades de la consola: tiempos de carga instantáneos, uso de los gatillos adaptativos del DualSense, y un nivel de detalle visual que todavía no tienen rival en consola.</p><p>El DualSense incluido justifica por sí solo el diferencial de precio respecto a comprar consola y juego por separado. Para el comprador que llega al ecosistema PlayStation por primera vez, no hay mejor punto de entrada disponible en este momento.</p>', 9.2, NULL, 1, '2023-11-24 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(108, 66, 44, '8BitDo Retro Keyboard — La nostalgia no tiene por qué sacrificar rendimiento', '<p>8BitDo tomó el diseño de la NES —colores crema, proporciones robustas, tipografía retro— y lo construyó con tecnología 2024. Los switches Hall Effect son inmunes al drift y tienen la actuación más lineal y predecible del mercado. La compatibilidad con Switch, PC y Mac hace el teclado genuinamente versátil.</p><p>La conectividad dual USB-C y Bluetooth funciona sin latencia apreciable en modo cable. La iluminación RGB es un contraste interesante con la estética retro. Para fans de Nintendo con nostalgia de la era 8-bit, es una compra que no decepciona.</p>', 8.8, NULL, 1, '2023-12-01 09:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(109, 55, 2, 'MSI Clutch GM41 — La propuesta ultraligera más accesible del mercado', '<p>MSI demostró que los ratones ultraligeros no tienen que costar una fortuna. El GM41 V2 pesa 55g —menos que muchos competidores de precio doble— y el sensor PMW-3370 de 26 000 DPI es de los mejores disponibles en su categoría de precio.</p><p>Los switches Omron con 80 millones de clics garantizan durabilidad real. El cable USB-C detachable es un detalle infrecuente en esta gama de precio. Para el jugador competitivo que quiere pasar a un ultraligero sin inversión grande, es la puerta de entrada correcta.</p>', 8.8, NULL, 1, '2023-10-12 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(110, 58, 36, 'HyperX ChargePlay Duo — Simplicidad y utilidad en una base de carga', '<p>HyperX no intentó reinventar la rueda con el ChargePlay Duo: diseñó una estación de carga que hace exactamente lo que promete, de forma fiable y con buen acabado. Los 3 horas de carga completa para mandos DualSense son competitivos con cualquier alternativa del mercado.</p><p>El LED indicador de estado elimina la incertidumbre de si el mando está cargando correctamente. El diseño compacto cabe en cualquier escritorio. Para el jugador de PS5 que tiene dos mandos y está cansado de buscar cables, es la solución más limpia disponible.</p>', 8.6, NULL, 1, '2023-07-30 09:30:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01'),
(111, 53, 44, 'LG 32GQ850-B — El 4K144Hz que cambia el estándar de la gama alta', '<p>LG combinó la tecnología Nano IPS con 144Hz y HDMI 2.1 en un monitor que tiene pocos argumentos en contra más allá del precio. El 4K nativo a 144Hz con soporte G-Sync y FreeSync Premium Pro simultáneo elimina la limitación de plataforma: funciona óptimamente con PS5, Xbox Series X, RTX y RX por igual.</p><p>El tiempo de respuesta de 1ms GtG es el mejor disponible en tecnología IPS en este formato. La cobertura DCI-P3 amplia lo hace apto para edición de vídeo y fotografía además del gaming. La referencia de los monitores 4K para gaming en 32 pulgadas.</p>', 9.4, NULL, 1, '2023-09-22 10:00:00', '2026-04-07 22:40:01', '2026-04-07 22:40:01');

-- Opiniones de comunidad
INSERT INTO `opiniones` (`id_opinion`, `usuario_id`, `producto_id`, `plataforma_id`, `titulo`, `contenido`, `calificacion`, `aprobada`, `creado_en`) VALUES
(1, 3, 4, 1, 'Una obra maestra del terror narrativo', 'La atmósfera es increíble y la historia te atrapa desde el primer minuto. De las mejores experiencias de la generación. Compré en ZonaPixel y llegó en 24h.', 5, 1, '2026-03-23 14:05:27'),
(2, 4, 2, 4, 'Expande el mundo de BotW de forma increíble', 'Expande el mundo de BotW de forma que no creía posible. El sistema de construcción es adictivo aunque a veces el rendimiento baja en zonas densas.', 4, 1, '2026-03-23 14:05:27'),
(3, 5, 1, 5, 'Simplemente el mejor RPG en décadas', 'La libertad que ofrece es abrumadora en el buen sentido. Larian Studios ha creado algo histórico. Más de 300 horas y todavía descubriendo cosas.', 5, 1, '2026-03-23 14:05:27'),
(4, 6, 1, 1, 'Gran juego con una historia fascinante', 'La historia es increíble y los personajes son de los mejores que he visto. El combate puede ser complejo al principio pero vale la pena aprenderlo.', 4, 1, '2026-03-23 14:05:27'),
(5, 1, 8, NULL, 'El mejor mouse que he tenido', 'El G Pro X Superlight 2 es increíble. Ligero, preciso, batería que dura días. Definitivamente vale cada peso invertido.', 5, 1, '2026-03-23 14:05:27'),
(6, 3, 5, 3, 'adsadsada', 'dsadadasdasd', 2, 1, '2026-03-23 21:04:11'),
(72, 9, 16, 1, 'La mejor experiencia gaming de mi vida', 'Nunca pensé que un juego pudiera hacerme sentir tanta satisfacción al vencer un jefe. Cada área del mapa tiene algo que descubrir. Lo compré aquí y llegó en perfectas condiciones.', 5, 1, '2026-04-07 22:40:01'),
(73, 10, 16, 5, 'Exigente pero increíblemente recompensante', 'El mapa abierto hace que el juego sea menos frustrante que los Souls clásicos porque siempre puedes ir a explorar otra zona cuando un jefe te bloquea. Obra maestra.', 5, 1, '2026-04-07 22:40:01'),
(74, 11, 16, 3, 'Buen juego pero no para todos', 'La curva de dificultad es real. Me llevó más de 10 horas solo pasar el primer área. Si buscas algo relajado este no es tu juego, pero si te gustan los desafíos es perfecto.', 4, 1, '2026-04-07 22:40:01'),
(75, 12, 22, 5, 'El RPG definitivo sigue siendo este', 'Nueve años y ningún juego ha igualado la calidad de sus misiones secundarias. La actualización next-gen lo hace ver increíble. Precio de escándalo en ZonaPixel.', 5, 1, '2026-04-07 22:40:01'),
(76, 13, 22, 1, 'La edición completa con los DLCs es obligatoria', 'Hearts of Stone y Blood & Wine son mejores que muchos juegos completos. Si no los han jugado se están perdiendo las mejores horas de The Witcher 3.', 5, 1, '2026-04-07 22:40:01'),
(77, 14, 21, 5, 'No me esperaba que fuera tan bueno', 'Compré Cyberpunk cuando salió y fue una decepción. La expansión Phantom Liberty junto con la actualización 2.0 lo convierten en un juego completamente diferente. Idris Elba es fantástico.', 5, 1, '2026-04-07 22:40:01'),
(78, 15, 21, 3, 'La redención de CD Projekt', 'El árbol de habilidades nuevo, Dogtown y la historia de Reed son todo lo que quería de la expansión. Por fin el juego que prometieron en 2020.', 5, 1, '2026-04-07 22:40:01'),
(79, 16, 17, 1, 'El mejor punto de entrada a la saga Souls', 'Empecé con DS3 porque me dijeron que era el más accesible del estudio y acertaron. Acabé jugando todos los de FromSoftware después. Los DLCs son absolutamente brutales en el buen sentido.', 5, 1, '2026-04-07 22:40:01'),
(80, 17, 17, 5, 'Mi fromsoft favorito junto a Elden Ring', 'El combate es más rápido que DS1 o DS2 y los jefes están entre los mejores diseñados del estudio. La Ringed City es probablemente el mejor DLC que han hecho.', 5, 1, '2026-04-07 22:40:01'),
(81, 18, 28, 1, 'Historia brutal, gameplay más simple de lo esperado', 'La narrativa es la más oscura y adulta de la franquicia. Los enfrentamientos de Eikon son espectaculares. Pero los fans del rol por turnos se llevarán una sorpresa: esto es action game puro.', 4, 1, '2026-04-07 22:40:01'),
(82, 19, 28, 1, 'Clive es el mejor protagonista de FF en décadas', 'La escritura de los personajes es excelente. Jill, Cid y Joshua son memorables. El juego es largo y hay zonas donde la densidad de misiones secundarias es excesiva pero la historia principal es 10/10.', 4, 1, '2026-04-07 22:40:01'),
(83, 20, 26, 1, 'El rey de los juegos de lucha vuelve a su trono', 'El sistema Heat transforma peleas que pensabas perdidas. El modo historia es el más cinematográfico que han hecho. Y los gráficos en PS5 son alucinantes. Devin en ZonaPixel fue muy rápido.', 5, 1, '2026-04-07 22:40:01'),
(84, 22, 26, 5, 'Mejor entrada para nuevos jugadores que T7', 'El modo arcade es genial para aprender los fundamentos sin frustrarse. El online está bien optimizado. Pocos juegos de lucha tienen un paquete completo tan bueno en el lanzamiento.', 5, 1, '2026-04-07 22:40:01'),
(85, 24, 32, 1, 'El roguelike más adictivo que he jugado', 'Returnal no es para todos pero cuando conectas con él es imposible parar. Cada run revela algo nuevo de la historia. El audio binaural con auriculares es de las mejores experiencias sensoriales en PS5.', 5, 1, '2026-04-07 22:40:01'),
(86, 25, 32, 1, 'Brutal pero justo', 'Morí cientos de veces pero nunca sentí que el juego hacía trampa. Siempre era un error mío. Cuando finalmente terminé el tercer bioma tuve que levantarme y celebrarlo. Altamente recomendado para los que buscan un reto real.', 5, 1, '2026-04-07 22:40:01'),
(87, 26, 31, 5, 'El JRPG más estiloso que existe', 'La dirección artística, la música, los personajes, la historia. Todo es 10/10. Las 130 horas se pasan en un suspiro. La versión Royal añade suficiente contenido para justificar el replay si ya jugaste el original.', 5, 1, '2026-04-07 22:40:01'),
(88, 27, 31, 5, 'Compra obligatoria para fans del género', 'Empecé P5R sin haber jugado ningún Persona y me enamoré del género. El sistema de calendario y los lazos con los personajes secundarios son adictivos de una forma que ningún otro RPG ha conseguido para mí.', 5, 1, '2026-04-07 22:40:01'),
(89, 28, 33, 4, 'El mejor juego de Switch del año de su lanzamiento', 'Los EMMI son terroríficos de una forma que Nintendo raramente logra. El combate de Samus es fluido y satisfactorio. El diseño de niveles es una obra maestra de pistas visuales que te guían sin explicarte nada.', 5, 1, '2026-04-07 22:40:01'),
(90, 29, 33, 4, 'Un metroidvania que define el género', 'Si no has jugado metroidvanias antes, este es el punto de entrada perfecto. Si eres fan del género, Dread redefine el estándar. La dificultad de los jefes es alta pero justa.', 5, 1, '2026-04-07 22:40:01'),
(91, 30, 6, 4, 'Nintendo en su estado más creativo', 'Las Flores Maravilla transforman cada nivel de forma completamente diferente. Nunca sé qué va a pasar cuando toco una. El multijugador con mi familia es caótico y divertidísimo. Compra obligatoria para Switch.', 5, 1, '2026-04-07 22:40:01'),
(92, 31, 6, 4, 'El mejor Mario 2D desde World', 'La variedad de niveles es asombrosa. Cada mundo tiene su propia identidad y las sorpresas no paran. Nintendo demostró que todavía puede hacer cosas completamente nuevas con una fórmula de 35 años.', 5, 1, '2026-04-07 22:40:01'),
(93, 32, 18, 5, 'El juego que me enseñó a ser paciente', 'Sekiro me frustró como ningún otro juego hasta que el sistema de deflecciones hizo clic. A partir de ese momento es el juego más satisfactorio que he jugado. Genichiro primer intento después de 40 muertes fue mi momento de juego del año.', 5, 1, '2026-04-07 22:40:01'),
(94, 33, 18, 1, 'No hay builds, solo habilidad. Y eso es perfecto.', 'No poder farmear stats y tener que mejorar de verdad es refrescante. Los jefes son los mejor diseñados de FromSoftware. Isshin es el mejor enfrentamiento final que he vivido en un videojuego.', 5, 1, '2026-04-07 22:40:01'),
(95, 34, 19, 1, 'Todavía la mejor historia del medio', 'Ya había jugado el original y la remasterización en PS4. Esta versión para PS5 es definitivamente diferente: la IA de los enemigos y aliados es otro nivel. Las expresiones faciales son de película.', 5, 1, '2026-04-07 22:40:01'),
(96, 35, 19, 1, 'Una remasterización que respeta el original', 'No cambiaron la historia ni el gameplay, solo lo mejoraron técnicamente. Ese respeto al material original es lo que separa una buena remasterización de un simple port con texturas nuevas.', 5, 1, '2026-04-07 22:40:01'),
(97, 37, 34, 4, 'Oatchi lo cambia todo', 'Nunca había jugado Pikmin y este es el punto de entrada perfecto. Oatchi hace la exploración más ágil y los puzzles más creativos. Las cuevas tienen el mejor diseño de la saga según los fans veteranos que conozco.', 5, 1, '2026-04-07 22:40:01'),
(98, 38, 34, 4, 'Modo nocturno es una sorpresa increíble', 'La defensa de base nocturna es como un juego completamente diferente dentro de Pikmin 4. Nintendo sorprende con cuánto contenido variado metieron. La duración justifica perfectamente el precio.', 4, 1, '2026-04-07 22:40:01'),
(99, 39, 23, 5, 'Bethesda siendo Bethesda en el espacio', 'Si te gustan los RPGs de Bethesda te va a encantar. Hay cientos de horas de contenido, libertad de build enorme y el sistema de construcción de naves es adictivo. No es el juego de ciencia ficción que algunos esperaban pero es un Bethesda sólido.', 4, 1, '2026-04-07 22:40:01'),
(100, 40, 23, 5, 'Enorme pero desigual', 'Las misiones de las facciones son buenas. La exploración planetaria es repetitiva. El protagonista sin personalidad propia es la decisión de diseño que más me costó. Aún así llevé 80 horas sin aburrirme.', 3, 1, '2026-04-07 22:40:01'),
(101, 41, 20, 1, 'Dos juegos extraordinarios en un paquete', 'The Lost Legacy es mi favorita de la saga. Chloe y Nadine tienen más química que Drake en algunos momentos. Ver ambos juegos a 60fps en PS5 es cómo deberían haberse jugado siempre.', 5, 1, '2026-04-07 22:40:01'),
(102, 42, 20, 5, 'Mejor paquete para PC de toda la saga', 'La versión PC corre perfectamente y el soporte ultrawide es genuino. Si solo van a jugar un Uncharted en PC que sea este: abarca lo mejor que Naughty Dog hizo con la saga antes de The Last of Us.', 5, 1, '2026-04-07 22:40:01'),
(103, 43, 4, 1, 'Remedy en estado de gracia', 'Alan Wake 2 no se parece a nada que haya jugado. Los escenarios de Bright Falls con el manuscrito intercalado en el gameplay son experiencias únicas. La parte de Saga en la mente criminal es brillante. Compra obligatoria.', 5, 1, '2026-04-07 22:40:01'),
(104, 45, 4, 5, 'Terror psicológico inteligente y original', 'No busques sustos baratos aquí. El horror de Alan Wake 2 es el de no saber qué es real. La metatextualidad se va descontrolando de formas que me tienen dando vueltas en la cabeza días después.', 5, 1, '2026-04-07 22:40:01'),
(105, 46, 5, 1, 'La mejor remake de la historia del videojuego', 'Capcom tomó lo que hacía grande al original y lo actualizó sin arruinarlo. El sigilo añade profundidad táctica real. Ashley tiene agencia ahora. Las mecánicas de cuchillo transforman el loop de gameplay. Obra maestra.', 5, 1, '2026-04-07 22:40:01'),
(106, 47, 5, 5, 'El original sigue siendo mi favorito pero este es increíble', 'La nueva ambientación gótica del castillo se ve espectacular. Luis es mejor personaje en el remake. El DLC de Ada Wong añade perspectiva narrativa valiosa. Capcom no hizo trampa con el legacy.', 5, 1, '2026-04-07 22:40:01'),
(107, 48, 1, 5, 'El RPG que esperaba desde Baldur\'s Gate 2', 'Llevo 400 horas y todavía estoy descubriendo rutas de diálogo y consecuencias que no había visto. El Acto 3 en la ciudad tiene más contenido que muchos juegos completos. Larian Studios merece todos los premios que recibió.', 5, 1, '2026-04-07 22:40:01'),
(108, 49, 1, 1, 'La cooperativa es la mejor forma de jugarlo', 'Jugar en co-op de 4 jugadores con amigos que toman decisiones radicalmente distintas hace que cada partida sea única. Las discusiones en tiempo real sobre qué hacer en los diálogos son parte del juego.', 5, 1, '2026-04-07 22:40:01'),
(109, 50, 2, 4, 'Más grande que BotW en todos los sentidos', 'El sistema de construcción me tiene invirtiendo más tiempo en construir gadgets que en avanzar la historia y estoy feliz con eso. Las profundidades son una capa de contenido completa que tardé en descubrir.', 5, 1, '2026-04-07 22:40:01'),
(110, 51, 2, 4, 'Un mundo vertical que cambia el concepto de exploración', 'Tener islas flotantes, superficie y profundidades triplica el espacio jugable de forma orgánica. Los Santuarios esta vez tienen más variedad de puzzles. Nintendo sigue sin tener rivales en diseño de mundo abierto.', 5, 1, '2026-04-07 22:40:01'),
(111, 52, 8, NULL, 'Vale cada centavo', 'Venía de un mouse de 15 dólares y el salto fue brutal. La precisión del sensor HERO 2 es incomparable. El peso de menos de 60g se nota en los FPS competitivos. La batería dura semanas con uso diario.', 5, 1, '2026-04-07 22:40:01'),
(112, 53, 8, NULL, 'El mejor mouse que el dinero puede comprar en su categoría', 'Probé el Superlight 1 y el 2 es una mejora real, no solo de marketing. El sensor es más preciso a velocidades altas y la conectividad LIGHTSPEED 2 no tiene latencia apreciable incluso en gaming competitivo.', 5, 1, '2026-04-07 22:40:01'),
(113, 54, 7, NULL, 'Mi primer mecánico y no vuelvo a los de membrana', 'La construcción en aluminio es sólida como ningún teclado que haya tenido. Los switches Red son suaves y rápidos. El RGB por tecla es brillante. Para quien busca su primer mecánico de calidad, no hay mejor opción en este precio.', 5, 1, '2026-04-07 22:40:01'),
(114, 55, 7, NULL, 'Perfecto para gaming y trabajo', 'El formato TKL me da espacio para el mouse en el escritorio. Los switches Red son silenciosos suficiente para trabajar en oficina abierta. La construcción en aluminio hace que se sienta el doble de caro de lo que costó.', 5, 1, '2026-04-07 22:40:01'),
(115, 56, 9, NULL, 'El mejor sonido gaming que he escuchado', 'Los drivers de neodimio producen audio que rivaliza con auriculares audiófilos a precio similar. El ANC es efectivo en vuelos y espacios ruidosos. El sistema de batería dual elimina los tiempos muertos. Inversión que vale la pena.', 5, 1, '2026-04-07 22:40:01'),
(116, 57, 9, NULL, 'Los auriculares más completos del mercado', 'Compatibilidad con PC, PlayStation y Switch en el mismo par de auriculares sin perder funciones en ninguna plataforma. El sonido espacial en shooters competitivos da una ventaja real. El precio es alto pero la calidad lo justifica.', 5, 1, '2026-04-07 22:40:01'),
(117, 58, 10, NULL, 'El panel QHD de referencia para gaming', 'Venía de un monitor de 60Hz y el salto a 165Hz cambió completamente cómo juego. El panel IPS tiene colores precisos y la respuesta de 1ms elimina el ghosting incluso en FPS rápidos. Compra que no me arrepiento.', 5, 1, '2026-04-07 22:40:01'),
(118, 3, 46, NULL, '4K a 60fps en todo lo que le pongo', 'La RTX 4070 Super con DLSS 3 Quality en 4K supera los 60fps en todos los juegos que he probado. Frame Generation es transformador en los títulos que lo implementan bien. Llegó perfectamente embalada desde ZonaPixel.', 5, 1, '2026-04-07 22:40:01'),
(119, 4, 62, 1, 'La mejor generación de consolas del momento', 'Los tiempos de carga en juegos optimizados para PS5 son de otro planeta. El DualSense con sus haptics cambia cómo se siente jugar. La edición slim es más manejable y silenciosa que el modelo original.', 5, 1, '2026-04-07 22:40:01'),
(120, 5, 64, 4, 'La pantalla OLED es una revelación', 'Los colores de Hyrule en la pantalla OLED son impresionantes en modo portátil. El diseño temático de los Joy-Con y la base es bonito sin ser llamativo. Bien embalado por ZonaPixel, sin daños en el envío.', 5, 1, '2026-04-07 22:40:01'),
(121, 6, 26, 2, 'El mejor juego de lucha del año sin duda', 'Jin vs Kazuya en el modo historia es uno de los mejores enfrentamientos finales del género. El balance inicial del roster es sólido y el netcode online es de los mejores que ha tenido la saga.', 5, 1, '2026-04-07 22:40:01'),
(122, 9, 27, 1, 'Un mundo RPG que realmente vive', 'Los NPCs tienen rutinas reales. Encontré a un personaje que había conocido en la ciudad luego en otro lugar completamente diferente. El sistema de Peones de otros jugadores aprende y cambia. Nada más hace esto.', 5, 1, '2026-04-07 22:40:01'),
(123, 10, 35, 1, 'El mundo más bello de un videojuego', 'Pandora es visualmente impresionante. Cada bioma tiene su propia paleta, fauna y desafíos. Si buscas un juego para pasear y explorar un entorno hermoso, es difícil igualarlo. El gameplay es convencional pero el mundo compensa.', 4, 1, '2026-04-07 22:40:01'),
(124, 11, 29, 5, 'Imprescindible para fans de FF7 Remake', 'La historia de Zack es el complemento perfecto para entender plenamente el destino de Cloud en el remake. El doblaje nuevo es excelente y la banda sonora reorquestada es bellísima. Dura poco pero cada hora vale.', 5, 1, '2026-04-07 22:40:01'),
(125, 12, 30, 1, 'Sonic que finalmente funciona en 3D', 'Los combates contra los Titanes son set pieces genuinamente emocionantes. La banda sonora de Ohtani es una de mis favoritas del año. El mundo abierto tiene zonas desiguales pero la dirección general es la correcta para la franquicia.', 4, 1, '2026-04-07 22:40:01'),
(126, 13, 45, 4, 'El mejor mando de terceros del mercado', 'Los sticks Hall Effect eliminaron mi problema de drift permanentemente. La app de configuración es intuitiva. Compatible con Switch, PC y Android. Ergonomía excelente. El único mando de terceros que recomendaría sobre los oficiales.', 5, 1, '2026-04-07 22:40:01'),
(127, 14, 56, NULL, 'Transformó completamente mi flujo de streaming', 'Antes tardaba minutos en cambiar escenas y configurar OBS durante el directo. Ahora es un clic. La integración con Spotify para controlar la música en directo sin cortar la partida es el detalle que más uso.', 5, 1, '2026-04-07 22:40:01'),
(128, 15, 36, NULL, 'La forma más cómoda que existe en un mouse', 'Llevo 10 años con DeathAdder y la versión HyperSpeed inalámbrica no me da razones para cambiar. La forma es la misma que me enamoró, el sensor es mejor, y las 90 horas de batería son reales.', 5, 1, '2026-04-07 22:40:01'),
(129, 16, 50, NULL, 'Los tiempos de carga en PS5 son ridículamente rápidos', 'Instalé el 990 Pro en mi PS5 para expandir el almacenamiento y la diferencia con juegos optimizados para el SSD nativo es mínima. Las velocidades de lectura son las más altas que he medido en formato PCIe 4.0.', 5, 1, '2026-04-07 22:40:01'),
(130, 17, 38, NULL, 'El teclado más sólido que he tenido', 'El chasis de aluminio no cruje en ningún punto. Los switches Cherry MX Red son exactamente lo que un gamer de FPS necesita. El reposapuñecas de cuero sintético hace diferencia en sesiones de más de 3 horas.', 5, 1, '2026-04-07 22:40:01'),
(131, 18, 44, NULL, 'Convirtió a un no-simracer en adicto', 'Nunca me habían gustado los juegos de carreras hasta que probé GT7 con el T248. La fuerza de respuesta hace que cada curva sea diferente. La pantalla de telemetría te enseña a mejorar. El mejor punto de entrada al simracing.', 5, 1, '2026-04-07 22:40:01'),
(132, 19, 48, NULL, 'Los 16GB de VRAM marcan la diferencia', 'En juegos con texturas ultra los 16GB del 7800 XT se notan sobre los 12GB de la competencia. El FSR 3 en Frame Generation ha mejorado muchísimo. Para gaming 1440p con todas las texturas en máximo es la opción correcta.', 5, 1, '2026-04-07 22:40:01'),
(133, 20, 65, 1, 'El mando pro que PS5 necesitaba', 'Los gatillos adaptativos intercambiables en posición corta hacen diferencia real en shooters. Los perfiles guardados en la nube son convenientes. El peso es ligeramente mayor que el DualSense estándar pero equilibrado.', 5, 1, '2026-04-07 22:40:01'),
(134, 22, 54, NULL, 'La pantalla LCD es un diferencial real', 'Tener temperatura, frecuencia y estado del sistema en la cabeza de la bomba elimina la necesidad de tener iCUE abierto constantemente. El rendimiento de refrigeración en mi Ryzen 9 7900X es sobresaliente.', 5, 1, '2026-04-07 22:40:01'),
(135, 24, 41, NULL, 'El salto de LCD a OLED es permanente', 'Jamás vuelvo a un LCD para gaming después de este monitor. Los negros del OLED son absolutos. El 240Hz hace que los FPS se vean suaves como nunca. Costoso pero si el monitor es la inversión principal de tu setup, no hay rival.', 5, 1, '2026-04-07 22:40:01'),
(136, 25, 37, NULL, 'El inalámbrico que demuestra que el gaming sin cable funciona', 'La latencia del HyperSpeed es imperceptible en gaming competitivo. Las 200h de batería eliminan la ansiedad de carga. Los switches Yellow son suaves y silenciosos. El precio es alto pero para gaming inalámbrico sin compromisos es el mejor.', 5, 1, '2026-04-07 22:40:01');


-- Pedidos de ejemplo
INSERT INTO pedidos (usuario_id, estado_id, metodo_pago_id, subtotal, descuento, total, envio_nombre, envio_direccion, envio_ciudad) VALUES
(3, 4, 1, 189900.00,  0.00,     189900.00, 'Carlos Salcedo', 'Cra 15 #93-47, Apto 502', 'Bogotá'),
(4, 4, 3, 439800.00, 50000.00, 389800.00, 'Laura García',   'Cll 80 #12-30',           'Medellín'),
(5, 3, 2, 359900.00,  0.00,     359900.00, 'Juan Rodríguez', 'Av El Dorado #68B-85',    'Bogotá'),
(7, 2, 4, 619900.00,  0.00,     619900.00, 'Marta Kita',     'Cll 10 #5-20',            'Cali');

INSERT INTO pedido_items (pedido_id, producto_id, edicion_id, nombre_snapshot, precio_unitario, cantidad) VALUES
(1, 1, 1,    'Baldur''s Gate 3 — Edición Estándar', 189900.00, 1),
(2, 2, NULL, 'Zelda: Tears of the Kingdom',          219900.00, 1),
(2, 5, NULL, 'Resident Evil 4 Remake',               139900.00, 1),
(2, 7, NULL, 'Logitech G Pro X Superlight 2',        359900.00, 1),
(3, 8, NULL, 'Logitech G Pro X Superlight 2',        359900.00, 1),
(4, 9, NULL, 'SteelSeries Arctis Nova Pro',          619900.00, 1);

-- Códigos promocionales
INSERT INTO codigos_promo (codigo, tipo, valor, minimo_compra, usos_maximos) VALUES
('GAMER10',    'porcentaje', 10.00,  100000.00, 500),
('BIENVENIDO', 'fijo',       20000.00, 50000.00, 1000),
('NAVIDAD25',  'porcentaje', 25.00,  200000.00, 200);

-- Comentarios
INSERT INTO comentarios (usuario_id, resena_id, contenido) VALUES
(5, 1, 'Totalmente de acuerdo. TotK es un juego que redefine lo que puede ser un mundo abierto.'),
(4, 1, 'El sistema de construcción me parece increíble. Hice un tanque que destruyó a Ganondorf.'),
(3, 2, 'La reseña captura perfectamente la experiencia. Compré aquí y el servicio fue excelente.');

-- ============================================================
--  12. VISTA ÚTIL — Calificación promedio por producto
-- ============================================================

CREATE OR REPLACE VIEW v_calificacion_productos AS
SELECT
  p.id_producto                       AS producto_id,
  p.nombre                            AS producto,
  COUNT(o.id_opinion)                 AS total_opiniones,
  ROUND(AVG(o.calificacion), 2)       AS calificacion_promedio
FROM productos p
LEFT JOIN opiniones o ON o.producto_id = p.id_producto AND o.aprobada = TRUE
GROUP BY p.id_producto, p.nombre;

-- ============================================================
--  13. STORED PROCEDURE — Resumen de ventas por producto
-- ============================================================

DELIMITER $$

CREATE PROCEDURE sp_ventas_por_producto(
  IN p_fecha_inicio DATE,
  IN p_fecha_fin    DATE
)
BEGIN
  SELECT
    pr.id_producto,
    pr.nombre                  AS producto,
    SUM(pi.cantidad)           AS unidades_vendidas,
    SUM(pi.subtotal)           AS ingresos_cop
  FROM pedido_items pi
  JOIN productos pr ON pr.id_producto = pi.producto_id
  JOIN pedidos pe   ON pe.id_pedido   = pi.pedido_id
  WHERE pe.estado_id = 4              -- solo pedidos completados
    AND DATE(pe.creado_en) BETWEEN p_fecha_inicio AND p_fecha_fin
  GROUP BY pr.id_producto, pr.nombre
  ORDER BY ingresos_cop DESC;
END$$

DELIMITER ;

-- ============================================================
--  TRIGGERS para productos: Auto-set precio_original
-- ============================================================

DELIMITER $$

CREATE TRIGGER tr_productos_before_insert
BEFORE INSERT ON productos
FOR EACH ROW
BEGIN
  IF NEW.precio_original IS NULL THEN
    SET NEW.precio_original = NEW.precio;
  END IF;
END$$

CREATE TRIGGER tr_productos_before_update
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
  IF NEW.precio != OLD.precio AND OLD.precio_original IS NOT NULL THEN
    SET NEW.precio_original = OLD.precio_original;
  END IF;
END$$

DELIMITER ;

-- ============================================================
--  FIN DEL SCRIPT - MySQL Compatible
-- ============================================================
--  Ejecutar: mysql -u root -p < zonapixel_db_corregido.sql
-- ============================================================