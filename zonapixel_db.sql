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
  slug        VARCHAR(80)  NOT NULL UNIQUE,
  descripcion TEXT,
  imagen_url  VARCHAR(500),
  activa      BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE plataformas (
  id_plataforma SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(60) NOT NULL UNIQUE,
  icono VARCHAR(80) -- clase CSS o nombre de icono
);

CREATE TABLE generos (
  id_genero SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE marcas (
  id_marca SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(80) NOT NULL UNIQUE,
  logo_url VARCHAR(500)
);

CREATE TABLE productos (
  id_producto       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id      SMALLINT UNSIGNED NOT NULL,
  marca_id          SMALLINT UNSIGNED,
  nombre            VARCHAR(200) NOT NULL,
  slug              VARCHAR(200) NOT NULL UNIQUE,
  descripcion_corta VARCHAR(300),
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
  slug            VARCHAR(200) NOT NULL UNIQUE,
  resumen         VARCHAR(400),
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
INSERT INTO plataformas (nombre, slug, icono) VALUES
  ('PlayStation 5',   'ps5',     'fab fa-playstation'),
  ('PlayStation 4',   'ps4',     'fab fa-playstation'),
  ('Xbox Series X',   'xbox',    'fab fa-xbox'),
  ('Nintendo Switch', 'switch',  'fas fa-gamepad'),
  ('PC / Steam',      'pc',      'fab fa-steam');

-- Géneros
INSERT INTO generos (nombre, slug) VALUES
  ('Acción / Aventura', 'accion-aventura'),
  ('RPG',               'rpg'),
  ('Terror / Survival', 'terror'),
  ('Estrategia',        'estrategia'),
  ('Deportes',          'deportes'),
  ('Indie',             'indie'),
  ('Plataformas',       'plataformas');

-- Categorías
INSERT INTO categorias (nombre, slug, descripcion) VALUES
  ('Videojuegos',  'videojuegos', 'Juegos para todas las plataformas'),
  ('Periféricos',  'perifericos', 'Teclados, ratones, auriculares y más'),
  ('Hardware',     'hardware',    'Tarjetas gráficas, RAM, almacenamiento'),
  ('Accesorios',   'accesorios',  'Fundas, cables, soportes y extras'),
  ('Consolas',     'consolas',    'Consolas nuevas y reacondicionadas');

-- Marcas
INSERT INTO marcas (nombre, slug) VALUES
  ('Larian Studios',  'larian'),
  ('Nintendo',        'nintendo'),
  ('Insomniac Games', 'insomniac'),
  ('Remedy Entertainment', 'remedy'),
  ('Capcom',          'capcom'),
  ('HyperX',          'hyperx'),
  ('Logitech',        'logitech'),
  ('SteelSeries',     'steelseries'),
  ('LG',              'lg');

-- Métodos de pago
INSERT INTO metodos_pago (nombre) VALUES
  ('Visa'),('Mastercard'),('PSE'),('Nequi'),('Daviplata'),('Efecty');

-- Estados de pedido
INSERT INTO estados_pedido (nombre) VALUES
  ('pendiente'),('procesando'),('enviado'),('completado'),('cancelado');

-- Usuarios (passwords: todos usan "Password123!" → bcrypt simulado)
INSERT INTO usuarios (rol_id, nombre, apellido, username, email, password_hash, activo, email_verificado) VALUES
  (1, 'Admin',   'ZonaPixel', 'admin_zp',    'admin@zonapixel.com',   '$2y$12$AdminHashPlaceholderXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', TRUE, TRUE),
  (3, 'Equipo',  'Editorial', 'zp_staff',    'staff@zonapixel.com',   '$2y$12$StaffHashPlaceholderXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', TRUE, TRUE),
  (2, 'Carlos',  'Salcedo',   'carlos_s',    'carlos@example.com',    '$2y$12$ClientHashPlaceholder1XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  TRUE, TRUE),
  (2, 'Laura',   'García',    'laura_g',     'laura@example.com',     '$2y$12$ClientHashPlaceholder2XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  TRUE, TRUE),
  (2, 'Juan',    'Rodríguez', 'pixel_master','pixel@example.com',     '$2y$12$ClientHashPlaceholder3XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  TRUE, TRUE),
  (2, 'Ana',     'Devoto',    'ana_dev',     'ana@example.com',       '$2y$12$ClientHashPlaceholder4XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  TRUE, FALSE),
  (2, 'Marta',   'Kita',      'marta_k',     'marta@example.com',     '$2y$12$ClientHashPlaceholder5XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  TRUE, TRUE);

-- Productos — Videojuegos
INSERT INTO productos (categoria_id, marca_id, nombre, slug, descripcion_corta, descripcion, precio, precio_original, stock, imagen_principal, destacado) VALUES
(1, 1,  'Baldur\'s Gate 3',
    'baldurs-gate-3',
    'El RPG definitivo de Larian Studios. Explora Faerûn con hasta 4 jugadores.',
    'Baldur\'s Gate 3 es un juego de rol por turnos ambientado en el universo de Dungeons & Dragons. Tus decisiones moldean una historia épica con consecuencias reales y un sistema de combate profundo basado en las reglas de D&D 5ª edición.',
    189900.00, 239900.00, 85,
    'https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png',
    TRUE),

(1, 2,  'The Legend of Zelda: Tears of the Kingdom',
    'zelda-tears-of-the-kingdom',
    'La secuela de Breath of the Wild. Libertad creativa absoluta en Hyrule.',
    'TotK expande el mundo de Hyrule verticalmente con nuevas islas flotantes y profundidades subterráneas. El sistema Ultrahand permite crear vehículos y construcciones sin límites.',
    219900.00, NULL, 60,
    'https://upload.wikimedia.org/wikipedia/en/f/fb/The_Legend_of_Zelda_Tears_of_the_Kingdom_cover.jpg',
    TRUE),

(1, 3,  'Marvel\'s Spider-Man 2',
    'spider-man-2',
    'Peter y Miles juntos en la aventura más espectacular del hombre araña.',
    'Insomniac lleva la franquicia a nuevas alturas con un mapa más grande, nuevos poderes, combate renovado y una historia emocionalmente madura que enfrenta a los dos Spider-Man contra Venom.',
    179900.00, 239900.00, 45,
    'https://image.api.playstation.com/vulcan/ap/rnd/202306/1219/e66c4ae18c5d8e3986a24599b293162a6f5c9eba22968d2c.jpg',
    TRUE),

(1, 4,  'Alan Wake 2',
    'alan-wake-2',
    'Terror narrativo y metaficción de Remedy Entertainment.',
    'Remedy entrega su obra más ambiciosa: una secuela 13 años después que mezcla terror psicológico, acción cinematográfica y metaficción en dos historias paralelas entrelazadas.',
    159900.00, NULL, 70,
    'https://cdn1.epicgames.com/offer/c4763f236d08423eb47b4c3008779c84/EGS_AlanWake2_RemedyEntertainment_S2_1200x1600-c7c8091ddac0f9669c8e5905bca88aaa',
    TRUE),

(1, 5,  'Resident Evil 4 Remake',
    'resident-evil-4-remake',
    'El survival horror clásico reconstruido desde cero para la nueva generación.',
    'Capcom reimagina su obra maestra de 2005 con gráficos de última generación, combate renovado, nuevas mecánicas de sigilo y una narrativa expandida que respeta y mejora el original.',
    139900.00, 199900.00, 90,
    'https://image.api.playstation.com/vulcan/ap/rnd/202210/0706/EVWyZD63pahuh95eKloFaJuC.png',
    FALSE),

(1, 2,  'Super Mario Bros. Wonder',
    'super-mario-bros-wonder',
    'Mario se reinventa con las Flores Maravilla. El 2D más innovador en décadas.',
    'Nintendo introduce las Flores Maravilla: ítems que transforman radicalmente los niveles de formas inesperadas. Con multijugador local hasta 4 jugadores y más de 100 niveles únicos.',
    199900.00, 249900.00, 55,
    'https://assets.nintendo.com/image/upload/ar_16:9,c_lpad,w_1240/b_white/f_auto/q_auto/store/software/switch/70010000068688/1c5583f6bbce5bccdc923c25c35ba8f42128b55df84f4a2fbeea74b6d1d1516e',
    FALSE);

-- Productos — Periféricos
INSERT INTO productos (categoria_id, marca_id, nombre, slug, descripcion_corta, descripcion, precio, precio_original, stock, imagen_principal, destacado) VALUES
(2, 6, 'HyperX Alloy Origins Core TKL',
    'hyperx-alloy-origins-core-tkl',
    'Teclado mecánico TKL compacto con switches HyperX Red de alto rendimiento.',
    'Construcción en aluminio CNC de grado aeroespacial, switches HyperX Red lineales, iluminación RGB por tecla y diseño TKL sin teclado numérico para mayor espacio en el escritorio. Compatible con HyperX NGENUITY.',
    289900.00, 369900.00, 30,
    'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500&q=80',
    FALSE),

(2, 7, 'Logitech G Pro X Superlight 2',
    'logitech-gpro-x-superlight-2',
    'El ratón gaming más ligero de Logitech. Sensor HERO 2 de 32.000 DPI.',
    'Diseñado con los mejores esports players del mundo. Pesa menos de 60g, tiene el nuevo sensor HERO 2, conectividad LIGHTSPEED de 2.4GHz con hasta 95h de batería y pies de PTFE de grado 1.',
    359900.00, 449900.00, 25,
    'https://images.unsplash.com/photo-1596443686812-2f45229eebc3?w=500&q=80',
    TRUE),

(2, 8, 'SteelSeries Arctis Nova Pro',
    'steelseries-arctis-nova-pro',
    'Headset gaming de referencia con audio Hi-Fi y cancelación activa de ruido.',
    'Drivers de 40mm de neodimio de alta fidelidad, cancelación activa de ruido (ANC), transreceptores intercambiables para diferentes plataformas y hasta 22h de batería con carga rápida.',
    619900.00, NULL, 18,
    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&q=80',
    FALSE),

(2, 9, 'LG 27GP83B-B 27" QHD IPS 165Hz',
    'lg-27gp83b-27-qhd-ips-165hz',
    'Monitor gaming 27" QHD IPS de 1ms y 165Hz con AMD FreeSync Premium.',
    'Panel IPS de 2560x1440, 165Hz con OC, tiempo de respuesta de 1ms GtG, AMD FreeSync Premium, HDR10, sRGB 99%. Ideal para gaming competitivo y creación de contenido.',
    1189900.00, NULL, 12,
    'https://images.unsplash.com/photo-1616763355603-9755a640a287?w=500&q=80',
    FALSE);

-- Ediciones especiales
INSERT INTO producto_ediciones (producto_id, nombre, precio) VALUES
  (1, 'Edición Estándar', 189900.00),
  (1, 'Edición Deluxe',   229900.00),
  (3, 'Edición Estándar', 179900.00),
  (3, 'Edición Digital Deluxe', 219900.00);

-- Plataformas por producto
INSERT INTO producto_plataformas (producto_id, plataforma_id) VALUES
  (1,1),(1,5),         -- BG3: PS5, PC
  (2,4),               -- Zelda: Switch
  (3,1),               -- Spider-Man 2: PS5
  (4,1),(4,3),(4,5),   -- Alan Wake 2: PS5, Xbox, PC
  (5,1),(5,2),(5,3),(5,5), -- RE4: PS5, PS4, Xbox, PC
  (6,4);               -- Mario Wonder: Switch

-- Géneros por producto
INSERT INTO producto_generos (producto_id, genero_id) VALUES
  (1,2),  -- BG3: RPG
  (2,1),  -- Zelda: Acción/Aventura
  (3,1),  -- Spider-Man: Acción/Aventura
  (4,3),  -- Alan Wake: Terror
  (5,3),  -- RE4: Terror
  (6,7);  -- Mario: Plataformas

-- Reseñas editoriales
INSERT INTO resenas (producto_id, autor_id, titulo, slug, resumen, contenido, calificacion, imagen_portada, publicada, publicada_en) VALUES
(2, 2,
  'Zelda: Tears of the Kingdom — Una obra maestra de diseño abierto',
  'zelda-totk-resena',
  'Nintendo superó lo imposible con una secuela que expande BotW en todas las dimensiones posibles.',
  '<p>Nintendo volvió a superar lo imposible. La libertad creativa que ofrece TotK es inigualable en cualquier juego de mundo abierto disponible hoy. El sistema Ultrahand permite construir desde simples plataformas hasta vehículos aéreos complejos, y la integración de estas mecánicas con el diseño de dungeons es brillante.</p><p>El mapa vertical — con islas flotantes, superficie de Hyrule y profundidades — triplica efectivamente el espacio jugable. Cada zona tiene su propia identidad visual y jugable.</p><p><strong>Veredicto:</strong> Imprescindible para cualquier poseedor de Nintendo Switch.</p>',
  9.8,
  'https://upload.wikimedia.org/wikipedia/en/f/fb/The_Legend_of_Zelda_Tears_of_the_Kingdom_cover.jpg',
  TRUE, '2023-05-15 10:00:00'),

(1, 2,
  'Baldur\'s Gate 3 — El RPG que redefinió el género',
  'baldurs-gate-3-resena',
  'Larian Studios ha creado el juego de rol más completo y ambicioso en décadas.',
  '<p>Larian Studios ha entregado algo que va más allá de lo que cualquier fan del género podía esperar. BG3 no es solo el mejor RPG de la generación — es una declaración de principios sobre diseño que respeta la inteligencia del jugador.</p><p>El sistema de combate por turnos basado en D&D 5e resulta profundo sin ser inaccesible. La cooperativa de hasta 4 jugadores añade una dimensión social que pocos RPGs logran.</p>',
  9.6,
  'https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png',
  TRUE, '2023-08-10 09:00:00'),

(4, 2,
  'Alan Wake 2 — Terror narrativo sin precedentes',
  'alan-wake-2-resena',
  'Remedy Entertainment entrega su obra más ambiciosa fusionando terror, acción y metaficción.',
  '<p>Remedy ha construido algo verdaderamente singular. Alan Wake 2 es una secuela que no se parece a ningún otro juego: mezcla survival horror clásico con elementos de thriller nórdico y metaficción descarada que rompe la cuarta pared de formas sorprendentes.</p>',
  9.0,
  'https://cdn1.epicgames.com/offer/c4763f236d08423eb47b4c3008779c84/EGS_AlanWake2_RemedyEntertainment_S2_1200x1600-c7c8091ddac0f9669c8e5905bca88aaa',
  TRUE, '2023-10-27 08:00:00');

-- Opiniones de comunidad
INSERT INTO opiniones (usuario_id, producto_id, plataforma_id, titulo, contenido, calificacion, aprobada) VALUES
(3, 4, 1,  'Una obra maestra del terror narrativo',
    'La atmósfera es increíble y la historia te atrapa desde el primer minuto. De las mejores experiencias de la generación. Compré en ZonaPixel y llegó en 24h.',
    5, TRUE),
(4, 2, 4, 'Expande el mundo de BotW de forma increíble',
    'Expande el mundo de BotW de forma que no creía posible. El sistema de construcción es adictivo aunque a veces el rendimiento baja en zonas densas.',
    4, TRUE),
(5, 1, 5, 'Simplemente el mejor RPG en décadas',
    'La libertad que ofrece es abrumadora en el buen sentido. Larian Studios ha creado algo histórico. Más de 300 horas y todavía descubriendo cosas.',
    5, TRUE),
(6, 1, 1, 'Gran juego con una historia fascinante',
    'La historia es increíble y los personajes son de los mejores que he visto. El combate puede ser complejo al principio pero vale la pena aprenderlo.',
    4, TRUE),
(7, 8, NULL, 'El mejor mouse que he tenido',
    'El G Pro X Superlight 2 es increíble. Ligero, preciso, batería que dura días. Definitivamente vale cada peso invertido.',
    5, TRUE);

-- Pedidos de ejemplo
INSERT INTO pedidos (usuario_id, estado_id, metodo_pago_id, subtotal, descuento, total, envio_nombre, envio_direccion, envio_ciudad) VALUES
(3, 4, 1, 189900.00, 0.00,    189900.00, 'Carlos Salcedo',  'Cra 15 #93-47, Apto 502', 'Bogotá'),
(4, 4, 3, 439800.00, 50000.00,389800.00, 'Laura García',    'Cll 80 #12-30',           'Medellín'),
(5, 3, 2, 359900.00, 0.00,    359900.00, 'Juan Rodríguez',  'Av El Dorado #68B-85',    'Bogotá'),
(7, 2, 4, 619900.00, 0.00,    619900.00, 'Marta Kita',      'Cll 10 #5-20',            'Cali');

INSERT INTO pedido_items (pedido_id, producto_id, edicion_id, nombre_snapshot, precio_unitario, cantidad) VALUES
(1, 1, 1, 'Baldur\'s Gate 3 — Edición Estándar', 189900.00, 1),
(2, 2, NULL, 'Zelda: Tears of the Kingdom',       219900.00, 1),
(2, 5, NULL, 'Resident Evil 4 Remake',            139900.00, 1),
(2, 7, NULL, 'Logitech G Pro X Superlight 2',     359900.00, 1),
(3, 8, NULL, 'Logitech G Pro X Superlight 2',     359900.00, 1),
(4, 9, NULL, 'SteelSeries Arctis Nova Pro',       619900.00, 1);

-- Código promo de ejemplo
INSERT INTO codigos_promo (codigo, tipo, valor, minimo_compra, usos_maximos) VALUES
('GAMER10',    'porcentaje', 10.00, 100000.00, 500),
('BIENVENIDO', 'fijo',       20000.00, 50000.00, 1000),
('NAVIDAD25',  'porcentaje', 25.00, 200000.00, 200);

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
  p.id_producto            AS producto_id,
  p.nombre        AS producto,
  COUNT(o.id_opinion)     AS total_opiniones,
  ROUND(AVG(o.calificacion), 2) AS calificacion_promedio
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
    pr.nombre                       AS producto,
    SUM(pi.cantidad)               AS unidades_vendidas,
    SUM(pi.subtotal)               AS ingresos_cop
  FROM pedido_items pi
  JOIN productos pr ON pr.id_producto = pi.producto_id
  JOIN pedidos pe   ON pe.id_pedido = pi.pedido_id
  WHERE pe.estado_id = 4              -- solo pedidos completados
    AND DATE(pe.creado_en) BETWEEN p_fecha_inicio AND p_fecha_fin
  GROUP BY pr.id_producto, pr.nombre
  ORDER BY ingresos_cop DESC;
END$$

DELIMITER ;

-- ============================================================
--  FIN DEL SCRIPT - MySQL Compatible
-- ============================================================
--  Ejecutar: mysql -u root -p zonapixel_db < zonapixel_db_fixed.sql
-- ============================================================
