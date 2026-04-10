<?php

/**
 * Página de inicio: carga métricas y bloques de catálogo (hero, ofertas, tecnología, reseñas).
 * Define helpers locales de formato duplicados del módulo público para no acoplar includes aquí.
 */
session_start();

require_once __DIR__ . '/views/php/productos/productoModel.php';
require_once __DIR__ . '/views/php/plataformas/plataformaModel.php';
require_once __DIR__ . '/views/php/usuarios/usuarioModel.php';
require_once __DIR__ . '/views/php/resenas/resenasModel.php';

// Formato de precio en pesos colombianos sin decimales
function zp_fmt_cop($valor)
{
  return '$' . number_format((float) $valor, 0, ',', '.');
}

// Resumen textual desde HTML para tarjetas del home
function zp_excerpt_plain($html, $len = 160)
{
  $t = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));
  if ($t === '') {
    return '';
  }
  if (function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($t) > $len) {
    return mb_substr($t, 0, $len) . '…';
  }
  if (strlen($t) > $len) {
    return substr($t, 0, $len) . '…';
  }
  return $t;
}

// Estrellas para promedio de opiniones en escala 1–5
function zp_estrellas_comunidad($promedio_1_a_5)
{
  $p = (float) $promedio_1_a_5;
  $llenas = (int) round($p);
  $llenas = max(0, min(5, $llenas));
  return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

// Escala editorial 0–10 mostrada como cinco estrellas
function zp_estrellas_editorial_diez($calificacion)
{
  $c = (float) $calificacion;
  $llenas = (int) round($c / 2);
  $llenas = max(0, min(5, $llenas));
  return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

// Rol numérico de sesión: 1 admin, 2+ usuario registrado, ausente invitado
$logueo = null;
if (isset($_SESSION['rol'])) {
  $logueo = $_SESSION['rol'];
}

// --- Datos agregados para cabeceras de confianza y carruseles ---
$total_productos_catalogo = total_productos_activos();
$total_clientes = total_clientes();
$promedio_opiniones = promedio_calificacion_opiniones_aprobadas();

$hero_producto = index_videojuego_mas_vendido();
$plataformas_destacadas = plataformas_con_productos_videojuegos();
$juegos_destacados = index_productos_videojuegos_destacados(8);
$oferta_especial = index_oferta_especial_videojuego();
$candidatos_ofertas = index_candidatos_ofertas_catalogo($oferta_especial ? (int) $oferta_especial['id_producto'] : 0);

// Ofertas secundarias: si hay banner principal, excluye candidatos con mejor descuento o igual descuento y mejor nota
if ($oferta_especial) {
  $pct_especial = producto_porcentaje_descuento($oferta_especial['precio'], $oferta_especial['precio_original']);
  $rating_especial = (float) ($oferta_especial['calificacion_promedio'] ?? 0);
  $ofertas_secundarias = array_slice(
    index_filtrar_ofertas_secundarias($candidatos_ofertas, $pct_especial, $rating_especial),
    0,
    8
  );
} else {
  // Sin oferta principal: ordena solo por porcentaje de descuento descendente
  usort($candidatos_ofertas, function ($a, $b) {
    return ((float) $b['pct_descuento']) <=> ((float) $a['pct_descuento']);
  });
  $ofertas_secundarias = array_slice($candidatos_ofertas, 0, 8);
}

$productos_tecnologia = index_productos_tecnologia(4);
$top_resenas = consultar_resenas_editorial_destacadas(5);

// Now shows top 5 directly, no slice needed


// Aviso puntual tras error de inicio de sesión desde este mismo archivo
if (isset($_SESSION['index_login_error'])): ?>
  <div style="background: linear-gradient(90deg, #dc3545, #c82333); color: white; padding: 1rem 0; text-align: center; font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 10px rgba(220,53,69,0.3); margin-bottom: 0; border: none;">
    <div class="container">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <?php echo htmlspecialchars($_SESSION['index_login_error']);
      unset($_SESSION['index_login_error']); ?>
    </div>
  </div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ZonaPixel — Tienda de Videojuegos y Tecnología</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <!-- ============ NAVBAR ============ -->
  <?php
  // Navegación según tipo de sesión
  if ($logueo === 1) {
    include './views/plantillas/navbar_admin.php';
  } elseif ($logueo >= 2) {
    include './views/plantillas/navbar_user.php';
  } else {
    include './views/plantillas/navbar_publico.php';
  }
  ?>

  <!-- Mobile Nav -->
  <div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav-overlay"></div>
    <div class="mobile-nav-drawer">
      <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
      <div class="mobile-nav-links">
        <a href="/index.php">Inicio</a>
        <a href="/views/catalogo.php">Catálogo</a>
        <a href="/views/resenas.php">Reseñas</a>
        <a href="/views/opiniones.php">Opiniones</a>
        <a href="/views/login.php">Iniciar sesión</a>
        <a href="/views/register.php">Registrarse</a>
      </div>
    </div>
  </div>

  <!-- ============ HERO ============ -->
  <section class="hero">
    <div class="hero-bg">
      <div class="hero-grid-overlay"></div>
    </div>
    <div class="container">
      <div class="hero-content">
        <div class="hero-eyebrow">Novedades <?php echo date('Y'); ?></div>
        <h1 class="hero-title">
          Tu próximo<br>
          <span class="line-accent">nivel comienza</span>
          aquí.
        </h1>
        <p class="hero-subtitle">
          Los mejores videojuegos, periféricos y hardware gamer al mejor precio.
          Envío rápido, devoluciones sin complicaciones.
        </p>
        <div class="hero-ctas">
          <a href="/views/catalogo.php" class="btn-primary">
            <i class="fas fa-gamepad"></i> Ver catálogo
          </a>
          <a href="/views/resenas.php" class="btn-secondary">Leer reseñas</a>
        </div>
        <div class="hero-stats">
          <div>
            <div class="hero-stat-num">+<?php echo number_format($total_productos_catalogo, 0, ',', '.'); ?></div>
            <div class="hero-stat-label">Productos</div>
          </div>
          <div>
            <div class="hero-stat-num">+<?php echo number_format($total_clientes, 0, ',', '.'); ?></div>
            <div class="hero-stat-label">Clientes</div>
          </div>
          <div>
            <div class="hero-stat-num"><?php echo $promedio_opiniones !== null
                                          ? number_format($promedio_opiniones, 1, ',', '.') . '★'
                                          : '—'; ?></div>
            <div class="hero-stat-label">Calificación</div>
          </div>
        </div>
      </div>
      <div class="hero-visual">
        <?php if ($hero_producto): ?>
          <div class="hero-product-card">
            <span class="hero-float-badge">🔥 Más vendido</span>
            <img src="<?php echo htmlspecialchars($hero_producto['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
              alt="<?php echo htmlspecialchars($hero_producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
            <div class="hero-product-info">
              <h3><?php echo htmlspecialchars($hero_producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
              <div class="price"><?php echo zp_fmt_cop($hero_producto['precio'] ?? 0); ?> COP</div>
            </div>
            <div class="hero-rating-pill">
              <?php if (!empty($hero_producto['calificacion_promedio']) && (int) ($hero_producto['total_opiniones'] ?? 0) > 0): ?>
                <span class="stars"><?php echo zp_estrellas_comunidad($hero_producto['calificacion_promedio']); ?></span>
                <span><?php echo number_format((float) $hero_producto['calificacion_promedio'], 1, ',', '.'); ?> / 5</span>
              <?php else: ?>
                <span class="stars">☆☆☆☆☆</span>
                <span>Sin valoraciones</span>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="hero-product-card">
            <span class="hero-float-badge">ZonaPixel</span>
            <div class="hero-product-info py-5">
              <h3>Próximamente</h3>
              <p class="mb-0 text-muted">Añade videojuegos al catálogo para ver el más vendido aquí.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ============ TICKER ============ -->
  <div class="ticker-strip">
    <div class="ticker-inner">
      <span class="ticker-item">Envío gratis desde $150.000</span>
      <span class="ticker-item">Nuevos juegos cada semana</span>
      <span class="ticker-item">Pago seguro con SSL</span>
      <span class="ticker-item">Devoluciones sin costo en 30 días</span>
      <?php if (!empty($plataformas_destacadas)): ?>
        <span class="ticker-item"><?php echo htmlspecialchars(implode(' · ', array_column($plataformas_destacadas, 'nombre')), ENT_QUOTES, 'UTF-8'); ?></span>
      <?php endif; ?>
      <span class="ticker-item">Envío gratis desde $150.000</span>
      <span class="ticker-item">Nuevos juegos cada semana</span>
      <span class="ticker-item">Pago seguro con SSL</span>
      <span class="ticker-item">Devoluciones sin costo en 30 días</span>
      <?php if (!empty($plataformas_destacadas)): ?>
        <span class="ticker-item"><?php echo htmlspecialchars(implode(' · ', array_column($plataformas_destacadas, 'nombre')), ENT_QUOTES, 'UTF-8'); ?></span>
      <?php endif; ?>
    </div>
  </div>

  <!-- ============ FEATURED PRODUCTS ============ -->
  <section class="section-gap">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="section-label">Destacados</div>
          <h2 class="section-title">Juegos más populares</h2>
        </div>
        <a href="/views/catalogo.php" class="section-link">Ver todos <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="category-strip" data-home-platform-filter="1">
        <button type="button" class="cat-chip active" data-plat-filter="all">Todos</button>
        <?php foreach ($plataformas_destacadas as $pl): ?>
          <button type="button" class="cat-chip" data-plat-filter="<?php echo (int) $pl['id_plataforma']; ?>">
            <?php echo htmlspecialchars($pl['nombre'], ENT_QUOTES, 'UTF-8'); ?>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="products-grid" id="featuredGamesGrid">
        <?php if (empty($juegos_destacados)): ?>
          <p class="text-muted">No hay videojuegos disponibles en el catálogo.</p>
        <?php else: ?>
          <?php foreach ($juegos_destacados as $prod):
            $en_oferta = (float) $prod['precio'] < (float) $prod['precio_original'];
            $pct = $en_oferta ? producto_porcentaje_descuento($prod['precio'], $prod['precio_original']) : 0;
            $plat_txt = $prod['plataformas_txt'] ?: 'Varias plataformas';
            $ids_raw = isset($prod['plataformas_ids']) ? (string) $prod['plataformas_ids'] : '';
          ?>
            <a href="/views/producto.php?id=<?php echo (int) $prod['id_producto']; ?>"
              class="product-card"
              data-plat-ids="<?php echo htmlspecialchars($ids_raw, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="product-img-wrap">
                <?php if ($en_oferta || !empty($prod['destacado'])): ?>
                  <span class="product-badge"><?php
                                              if ($en_oferta) {
                                                echo '<span class="badge badge-sale">-' . (int) round($pct) . '%</span>';
                                              } elseif (!empty($prod['destacado'])) {
                                                echo '<span class="badge badge-hot">Hot</span>';
                                              }
                                              ?></span>
                <?php endif; ?>
                <button type="button" class="product-wishlist" tabindex="-1"><i class="far fa-heart"></i></button>
                <img src="<?php echo htmlspecialchars($prod['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="product-overlay">
                  <button type="button" class="btn-add-cart"><i class="fas fa-cart-plus"></i> Añadir</button>
                </div>
              </div>
              <div class="product-body">
                <div class="product-platform"><?php echo htmlspecialchars($plat_txt, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="product-name"><?php echo htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="product-footer">
                  <div>
                    <span class="product-price"><?php echo zp_fmt_cop($prod['precio']); ?></span>
                    <?php if ($en_oferta): ?>
                      <span class="product-price-old"><?php echo zp_fmt_cop($prod['precio_original']); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="product-rating"><?php if (!empty($prod['calificacion_promedio']) && (int) ($prod['total_opiniones'] ?? 0) > 0): ?>
                      <span class="star">★</span> <?php echo number_format((float) $prod['calificacion_promedio'], 1, ',', '.'); ?>
                      (<?php echo (int) $prod['total_opiniones']; ?>)
                    <?php else: ?>
                      <span class="text-muted small">Sin valoraciones</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ============ FEATURED BANNER ============ -->
  <?php if ($oferta_especial):
    $img_esp = $oferta_especial['imagen_principal'] ?? '';
    $desc_esp = zp_excerpt_plain($oferta_especial['descripcion'] ?? '', 220);
    $plat_esp = $oferta_especial['plataformas_txt'] ?: '';
  ?>
    <section style="padding:0 0 80px;">
      <div class="container">
        <div class="featured-banner">
          <div class="fb-bg">
            <img src="<?php echo htmlspecialchars($img_esp, ENT_QUOTES, 'UTF-8'); ?>" alt="" />
          </div>
          <div class="fb-content">
            <div class="fb-tag">⚡ Oferta especial</div>
            <h2 class="fb-title"><?php echo htmlspecialchars($oferta_especial['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php if ($plat_esp !== ''): ?>
              <p class="small text-muted mb-2"><?php echo htmlspecialchars($plat_esp, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <p class="fb-desc"><?php echo htmlspecialchars($desc_esp, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="fb-price"><?php echo zp_fmt_cop($oferta_especial['precio']); ?> COP</div>
            <a href="/views/producto.php?id=<?php echo (int) $oferta_especial['id_producto']; ?>" class="btn-primary">
              <i class="fas fa-shopping-cart"></i> Comprar ahora
            </a>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ============ PERIFÉRICOS ============ -->
  <section class="section-gap" style="padding-top:0">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="section-label">Tecnología</div>
          <h2 class="section-title">Periféricos y hardware</h2>
        </div>
        <a href="/views/catalogo.php" class="section-link">Ver todos <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="products-grid">
        <?php if (empty($productos_tecnologia)): ?>
          <p class="text-muted">No hay productos de tecnología en el catálogo.</p>
        <?php else: ?>
          <?php foreach ($productos_tecnologia as $prod):
            $en_oferta = (float) $prod['precio'] < (float) $prod['precio_original'];
            $pct = $en_oferta ? producto_porcentaje_descuento($prod['precio'], $prod['precio_original']) : 0;
            $sub = $prod['categoria_nombre'] ?? '';
          ?>
            <a href="/views/producto.php?id=<?php echo (int) $prod['id_producto']; ?>" class="product-card">
              <div class="product-img-wrap">
                <?php if ($en_oferta || !empty($prod['destacado'])): ?>
                  <span class="product-badge"><?php
                                              if ($en_oferta) {
                                                echo '<span class="badge badge-sale">-' . (int) round($pct) . '%</span>';
                                              } elseif (!empty($prod['destacado'])) {
                                                echo '<span class="badge badge-new">Nuevo</span>';
                                              }
                                              ?></span>
                <?php endif; ?>
                <button type="button" class="product-wishlist" tabindex="-1"><i class="far fa-heart"></i></button>
                <img src="<?php echo htmlspecialchars($prod['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                  alt="<?php echo htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="product-overlay">
                  <button type="button" class="btn-add-cart"><i class="fas fa-cart-plus"></i> Añadir</button>
                </div>
              </div>
              <div class="product-body">
                <div class="product-platform"><?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="product-name"><?php echo htmlspecialchars($prod['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="product-footer">
                  <div>
                    <span class="product-price"><?php echo zp_fmt_cop($prod['precio']); ?></span>
                    <?php if ($en_oferta): ?>
                      <span class="product-price-old"><?php echo zp_fmt_cop($prod['precio_original']); ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="product-rating"><?php if (!empty($prod['calificacion_promedio']) && (int) ($prod['total_opiniones'] ?? 0) > 0): ?>
                      <span class="star">★</span> <?php echo number_format((float) $prod['calificacion_promedio'], 1, ',', '.'); ?>
                      (<?php echo (int) $prod['total_opiniones']; ?>)
                    <?php else: ?>
                      <span class="text-muted small">Sin valoraciones</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ============ DEALS ============ -->
  <?php if (!empty($ofertas_secundarias)): ?>
    <section class="section-gap" style="padding-top:0">
      <div class="container">
        <div class="section-header">
          <div>
            <div class="section-label">Precios increíbles</div>
            <h2 class="section-title">Ofertas del día</h2>
          </div>
          <a href="/views/catalogo.php" class="section-link">Más ofertas <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="deals-grid">
          <?php foreach ($ofertas_secundarias as $d):
            $plat = $d['plataformas_txt'] ?: ($d['categoria_nombre'] ?? '');
            $pct_d = (float) $d['pct_descuento'];
          ?>
            <a href="/views/producto.php?id=<?php echo (int) $d['id_producto']; ?>" class="deal-card">
              <img src="<?php echo htmlspecialchars($d['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                alt="<?php echo htmlspecialchars($d['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="deal-info">
                <div class="deal-name"><?php echo htmlspecialchars($d['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="deal-platform"><?php echo htmlspecialchars($plat, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="deal-price-row">
                  <span class="deal-price"><?php echo zp_fmt_cop($d['precio']); ?></span>
                  <span class="deal-old-price"><?php echo zp_fmt_cop($d['precio_original']); ?></span>
                  <span class="deal-discount">-<?php echo (int) round($pct_d); ?>%</span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ============ REVIEWS PREVIEW ============ -->
  <section class="section-gap" style="background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding-top:60px; padding-bottom:60px;">
    <div class="container">
      <div class="section-header">
        <div>
          <div class="section-label">Top reseñas</div>

          <h2 class="section-title">Las 5 mejores reseñas</h2>

        </div>
        <a href="/views/resenas.php" class="section-link">Ver todas <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="reviews-grid">
        <?php if (empty($top_resenas)): ?>

          <p class="text-muted mb-0">No hay reseñas editoriales publicadas.</p>
        <?php else: ?>
          <?php foreach ($top_resenas as $re):

            $autor = trim(($re['autor_nombre'] ?? '') . ' ' . ($re['autor_apellido'] ?? ''));
            if ($autor === '') {
              $autor = $re['autor_username'] ?? 'Editorial';
            }
            $img_rev = $re['imagen_portada'] ?? '';
            $plat_line = $re['plataformas_txt'] ?? '';
            $game_line = htmlspecialchars($re['producto_nombre'] ?? '', ENT_QUOTES, 'UTF-8');
            if ($plat_line !== '') {
              $game_line .= ' · ' . htmlspecialchars($plat_line, ENT_QUOTES, 'UTF-8');
            }
          ?>
            <a href="/views/resena.php?id=<?php echo (int) $re['id_resena']; ?>" class="review-card" style="display:block;">
              <div class="review-card-header">
                <img src="<?php echo htmlspecialchars($img_rev, ENT_QUOTES, 'UTF-8'); ?>" class="review-game-img" alt="" />
                <div class="review-score"><?php echo number_format((float) $re['calificacion'], 1, ',', '.'); ?></div>
              </div>
              <div class="review-title"><?php echo htmlspecialchars($re['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="review-game-name"><?php echo $game_line; ?></div>
              <p class="review-excerpt"><?php echo htmlspecialchars(zp_excerpt_plain($re['contenido'] ?? '', 200), ENT_QUOTES, 'UTF-8'); ?></p>
              <div class="review-footer">
                <span class="review-author">Por <strong><?php echo htmlspecialchars($autor, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span class="review-stars"><?php echo zp_estrellas_editorial_diez($re['calificacion']); ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ============ TRUST ============ -->
  <section class="trust-strip">
    <div class="container">
      <div class="trust-items">
        <div class="trust-item">
          <div class="trust-icon">🚀</div>
          <div>
            <div class="trust-label">Envío express</div>
            <div class="trust-sub">Recibe en 24–48 h</div>
          </div>
        </div>
        <div class="trust-item">
          <div class="trust-icon">🔒</div>
          <div>
            <div class="trust-label">Pago 100% seguro</div>
            <div class="trust-sub">SSL encriptado</div>
          </div>
        </div>
        <div class="trust-item">
          <div class="trust-icon">↩️</div>
          <div>
            <div class="trust-label">Devoluciones gratis</div>
            <div class="trust-sub">Hasta 30 días</div>
          </div>
        </div>
        <div class="trust-item">
          <div class="trust-icon">🎧</div>
          <div>
            <div class="trust-label">Soporte 24/7</div>
            <div class="trust-sub">Chat y correo</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ FOOTER ============ -->
  <?php
  include './views/plantillas/footer.php';
  ?>

  <!-- Script local: filtra tarjetas de «Juegos más populares» por ids de plataforma en data-plat-ids -->
  <script>
    (function() {
      var strip = document.querySelector('.category-strip[data-home-platform-filter="1"]');
      var grid = document.getElementById('featuredGamesGrid');
      if (!strip || !grid) return;
      strip.querySelectorAll('.cat-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
          strip.querySelectorAll('.cat-chip').forEach(function(c) {
            c.classList.remove('active');
          });
          chip.classList.add('active');
          var fid = chip.getAttribute('data-plat-filter');
          grid.querySelectorAll('.product-card').forEach(function(card) {
            if (fid === 'all') {
              card.style.display = '';
              return;
            }
            var raw = card.getAttribute('data-plat-ids') || '';
            var ids = raw.split(',').map(function(s) {
              return s.trim();
            });
            card.style.display = ids.indexOf(fid) !== -1 ? '' : 'none';
          });
        });
      });
    })();
  </script>
  <script type="module" src="js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>