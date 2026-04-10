<?php
session_start();
require_once __DIR__ . '/php/productos/productoModel.php';

function producto_fmt_cop($valor)
{
  return '$' . number_format((float) $valor, 0, ',', '.');
}

$producto_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$producto = $producto_id > 0 ? consultar_producto_detalle_id($producto_id) : null;
$plataformas = $producto ? consultar_producto_plataformas((int) $producto['id_producto']) : [];
$ediciones = $producto ? consultar_producto_ediciones((int) $producto['id_producto']) : [];

$opiniones = [];
if ($producto) {
  global $BD;
  $sql_op = mysqli_prepare($BD, "
    SELECT o.calificacion, o.contenido, u.nombre, u.apellido, u.username, pl.nombre AS plataforma
    FROM opiniones o
    INNER JOIN usuarios u ON u.id_usuario = o.usuario_id
    LEFT JOIN plataformas pl ON pl.id_plataforma = o.plataforma_id
    WHERE o.producto_id = ? AND o.aprobada = 1
    ORDER BY o.creado_en DESC, o.id_opinion DESC
    LIMIT 6
  ");
  mysqli_stmt_bind_param($sql_op, 'i', $producto_id);
  mysqli_stmt_execute($sql_op);
  $res_op = mysqli_stmt_get_result($sql_op);
  while ($row = mysqli_fetch_assoc($res_op)) {
    $opiniones[] = $row;
  }
}

$logueo = null;
if (isset($_SESSION['rol'])) {
  $logueo = $_SESSION['rol'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($producto['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8'); ?> — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <?php
  if ($logueo === 1) {
    include './plantillas/navbar_admin.php';
  } elseif ($logueo >= 2) {
    include './plantillas/navbar_user.php';
  } else {
    include './plantillas/navbar_publico.php';
  }
  ?>


  <!-- Drawer móvil (enlaces de muestra; conviene alinear con .php reales) -->
  <div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav-overlay"></div>
    <div class="mobile-nav-drawer">
      <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
      <div class="mobile-nav-links">
        <a href="/index.php">Inicio</a>
        <a href="/views/catalogo.php">Catálogo</a>
      </div>
    </div>
  </div>

  <div class="page-hero py-4">
    <div class="container">
      <div class="breadcrumb-nav">
        <a href="/index.php">Inicio</a><span>/</span>
        <a href="/views/catalogo.php">Catálogo</a><span>/</span>
        <a href="/views/catalogo.php"><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8'); ?></a><span>/</span>
        <span class="text-white"><?php echo htmlspecialchars($producto['nombre'] ?? 'Producto', ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    </div>
  </div>

  <div class="container">
    <?php if (!$producto): ?>
      <div class="py-5 text-center text-muted">Producto no encontrado.</div>
    <?php else: ?>
      <div class="product-detail-grid">

        <!-- GALLERY -->
        <div class="product-gallery">
          <div class="gallery-main">
            <img src="<?php echo htmlspecialchars($producto['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" id="mainImg" />
          </div>
          <div class="gallery-thumbs">
            <div class="gallery-thumb active">
              <img src="<?php echo htmlspecialchars($producto['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="" />
            </div>
          </div>
        </div>

        <!-- INFO -->
        <div class="product-detail-info">
          <div class="product-detail-platform">
            <span><?php echo htmlspecialchars($producto['categoria_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if (!empty($producto['destacado'])): ?>
              <span class="text-muted">·</span><span class="badge badge-hot">Destacado</span>
            <?php endif; ?>
          </div>
          <h1 class="product-detail-title"><?php echo htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
          <div class="product-detail-rating">
            <?php
            $prom = isset($producto['calificacion_promedio']) ? (float) $producto['calificacion_promedio'] : 0;
            $stars = max(0, min(5, (int) round($prom)));
            $total_op = (int) ($producto['total_opiniones'] ?? 0);
            ?>
            <div class="stars-row" id="productStars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="<?php echo $i <= $stars ? 'fas' : 'far'; ?> fa-star"></i>
              <?php endfor; ?>
            </div>
            <span class="rating-count" id="ratingCount"><?php echo number_format($prom, 1, ',', '.'); ?> · <?php echo $total_op; ?> reseñas</span>
          </div>

          <div class="product-detail-price"><?php echo producto_fmt_cop($producto['precio'] ?? 0); ?> COP</div>
          <?php if ((float) ($producto['precio_original'] ?? 0) > (float) ($producto['precio'] ?? 0)): ?>
            <div class="product-detail-price-old">Precio original: <s><?php echo producto_fmt_cop($producto['precio_original']); ?></s></div>
          <?php endif; ?>

          <p class="product-detail-desc">
            <?php echo htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
          </p>

          <?php if (!empty($plataformas)): ?>
            <div class="product-options-title">Plataforma</div>
            <div class="option-chips">
              <?php foreach ($plataformas as $idx => $pl): ?>
                <button class="option-chip<?php echo $idx === 0 ? ' active' : ''; ?>"><?php echo htmlspecialchars($pl['nombre'], ENT_QUOTES, 'UTF-8'); ?></button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($ediciones)): ?>
            <div class="product-options-title">Edición</div>
            <div class="option-chips">
              <?php foreach ($ediciones as $idx => $ed): ?>
                <button class="option-chip<?php echo $idx === 0 ? ' active' : ''; ?>" data-edicion-id="<?php echo (int) $ed['id_producto_edicion']; ?>">
                  <?php echo htmlspecialchars($ed['nombre'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo producto_fmt_cop($ed['precio']); ?>
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="qty-cart-row">
            <div class="qty-control">
              <button class="qty-btn qty-minus">−</button>
              <input type="number" class="qty-input" value="1" min="1" />
              <button class="qty-btn qty-plus">+</button>
            </div>
            <button class="btn-add-to-cart" data-producto-id="<?php echo (int) $producto['id_producto']; ?>">
              <i class="fas fa-shopping-cart"></i> Agregar al carrito
            </button>
            <button class="btn-wishlist-lg"><i class="far fa-heart"></i></button>
          </div>

          <ul class="product-meta-list">
            <li><span class="meta-key">Marca</span> <?php echo htmlspecialchars($producto['marca_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></li>
            <li><span class="meta-key">Categoría</span> <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></li>
            <li><span class="meta-key">Stock</span> <?php echo (int) ($producto['stock'] ?? 0); ?> disponible(s)</li>
          </ul>
        </div>
      </div>

      <!-- OPINIONS SECTION -->
      <section class="py-5">
        <div class="section-header">
          <div>
            <div class="section-label">Comunidad</div>
            <h2 class="section-title">Opiniones de usuarios</h2>
          </div>
          <?php if ($logueo): ?>
            <a href="./nueva-opinion.php?id=<?=  $producto_id ?>" class="btn-primary text-decoration-none">
              <i class="fas fa-pen"></i> Escribir opinión
            </a>
            <?php endif; ?>
        </div>
        <div class="d-grid gap-3" style="grid-template-columns:repeat(auto-fill, minmax(280px,1fr));" id="opinionesContainer" data-producto-id="<?php echo (int) $producto['id_producto']; ?>">
          <?php if (empty($opiniones)): ?>
            <div class="text-muted">Este producto aún no tiene opiniones.</div>
          <?php else: ?>
            <?php foreach ($opiniones as $op): ?>
              <?php
              $autor = trim(($op['nombre'] ?? '') . ' ' . ($op['apellido'] ?? ''));
              if ($autor === '') {
                $autor = $op['username'] ?? 'Usuario';
              }
              $ini = strtoupper(substr($autor, 0, 1));
              $stars_op = max(1, min(5, (int) $op['calificacion']));
              ?>
              <div class="opinion-card">
                <div class="opinion-card-header">
                  <div class="opinion-avatar"><?php echo htmlspecialchars($ini, ENT_QUOTES, 'UTF-8'); ?></div>
                  <div>
                    <div class="opinion-username"><?php echo htmlspecialchars($autor, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="opinion-game"><?php echo str_repeat('★', $stars_op) . str_repeat('☆', 5 - $stars_op); ?><?php if (!empty($op['plataforma'])): ?> · <?php echo htmlspecialchars($op['plataforma'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></div>
                  </div>
                </div>
                <p class="opinion-text">"<?php echo htmlspecialchars($op['contenido'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"</p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="text-center mt-4">
          <a href="opiniones.php" class="btn-secondary d-inline-flex align-items-center gap-2">
            Ver todas las opiniones <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </section>
    <?php endif; ?>
  </div>

  <?php
  include './plantillas/footer.php';
  ?>

  <script type="module" src="../js/main.js"></script>
  <script>
    (function() {
      var cont = document.getElementById('opinionesContainer');
      if (cont) {
        var pid = cont.getAttribute('data-producto-id');
        fetch('/views/php/carrito/api.php?action=opiniones_producto&producto_id=' + encodeURIComponent(pid))
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (!data || !data.ok || !Array.isArray(data.opiniones)) return;
            if (data.opiniones.length === 0) return;
            cont.innerHTML = '';
            data.opiniones.forEach(function(op) {
              var nombre = [op.nombre || '', op.apellido || ''].join(' ').trim() || op.username || 'Usuario';
              var ini = (nombre.charAt(0) || 'U').toUpperCase();
              var cal = parseInt(op.calificacion || '0', 10);
              if (!Number.isFinite(cal)) cal = 0;
              cal = Math.max(1, Math.min(5, cal));
              var stars = '★'.repeat(cal) + '☆'.repeat(5 - cal);
              var plataforma = op.plataforma ? ' · ' + op.plataforma : '';
              var card = document.createElement('div');
              card.className = 'opinion-card';
              card.innerHTML = '<div class="opinion-card-header"><div class="opinion-avatar">' + ini + '</div><div><div class="opinion-username"></div><div class="opinion-game"></div></div></div><p class="opinion-text"></p>';
              card.querySelector('.opinion-username').textContent = nombre;
              card.querySelector('.opinion-game').textContent = stars + plataforma;
              card.querySelector('.opinion-text').textContent = '"' + (op.contenido || '') + '"';
              cont.appendChild(card);
            });
          });
      }
    })();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>