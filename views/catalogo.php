<?php
/**
 * Catálogo público: interpreta filtros GET, pagina resultados y enlaces a ficha de producto contextual.
 */
session_start();

require_once __DIR__ . '/php/productos/productoModel.php';
require_once __DIR__ . '/php/generos/generoModel.php';
require_once __DIR__ . '/php/publico/paginacion_helper.php';
require_once __DIR__ . '/php/publico/listado_publico_helpers.php';

$logueo = null;
if (isset($_SESSION['rol'])) {
    $logueo = $_SESSION['rol'];
}

// Genera URL de catálogo al abrir o cerrar detalle rápido: quita paginación y facetas que la ficha reemplaza
function catalogo_url_con_ficha(array $ficha_extra) {
    $parametros = $_GET;
    unset(
        $parametros['pagina'],
        $parametros['tipo_producto'],
        $parametros['oferta'],
        $parametros['categoria'],
        $parametros['cal_estrellas'],
        $parametros['cal']
    );
    foreach ($ficha_extra as $clave => $valor) {
        if ($valor === null) {
            unset($parametros[$clave]);
        } else {
            $parametros[$clave] = $valor;
        }
    }
    $query = http_build_query($parametros);
    return 'catalogo.php' . ($query !== '' ? '?' . $query : '');
}

// --- Parámetros de filtro desde la query (arrays de ids positivos) ---
$filtro_categorias = publico_parametros_get_array('categoria');
$filtro_plataformas = publico_parametros_get_array('plataforma');
$filtro_marcas = publico_parametros_get_array('marca');
$filtro_generos = publico_parametros_get_array('genero');

// Calificación mínima por franjas de estrellas (solo si el catálogo tiene datos agregados en vista comunidad)
$calificacion_estrellas = 0;
if (isset($_GET['cal_estrellas']) && (string) $_GET['cal_estrellas'] !== '') {
    $calificacion_estrellas_raw = (int) $_GET['cal_estrellas'];
    if ($calificacion_estrellas_raw >= 1 && $calificacion_estrellas_raw <= 5) {
        $calificacion_estrellas = $calificacion_estrellas_raw;
    }
}

$tipo_producto = '';
if (isset($_GET['tipo_producto']) && in_array($_GET['tipo_producto'], ['software', 'hardware'], true)) {
    $tipo_producto = $_GET['tipo_producto'];
}

$solo_ofertas = isset($_GET['oferta']) && (string) $_GET['oferta'] === '1';

// Acota min/max solicitados al rango real de precios en BD e invierte si el usuario los cruza
$rango_precios = catalogo_rango_precios_activos();
$precio_min_solicitado = isset($_GET['precio_min']) ? (float) $_GET['precio_min'] : $rango_precios['precio_min'];
$precio_max_solicitado = isset($_GET['precio_max']) ? (float) $_GET['precio_max'] : $rango_precios['precio_max'];
if ($rango_precios['precio_max'] > 0) {
    $precio_min_solicitado = max($rango_precios['precio_min'], min($precio_min_solicitado, $rango_precios['precio_max']));
    $precio_max_solicitado = max($rango_precios['precio_min'], min($precio_max_solicitado, $rango_precios['precio_max']));
}
if ($precio_max_solicitado < $precio_min_solicitado) {
    $tmp = $precio_min_solicitado;
    $precio_min_solicitado = $precio_max_solicitado;
    $precio_max_solicitado = $tmp;
}

$orden = isset($_GET['orden']) ? (string) $_GET['orden'] : 'popular';
if (!in_array($orden, ['popular', 'precio_asc', 'precio_desc', 'nuevo', 'rating'], true)) {
    $orden = 'popular';
}

$mostrar_filtro_calificacion = catalogo_existen_calificaciones_comunidad();
if (!$mostrar_filtro_calificacion) {
    $calificacion_estrellas = 0;
}

$texto_busqueda = publico_texto_busqueda_get('q');

$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$pagina_actual = max(1, $pagina_actual);
$productos_por_pagina = 12;

// Paquete consumido por catalogo_productos_filtrados: incluye offset derivado de la página
$opciones_consulta = [
    'filtro_categorias' => $filtro_categorias,
    'filtro_plataformas' => $filtro_plataformas,
    'filtro_marcas' => $filtro_marcas,
    'filtro_generos' => $filtro_generos,
    'calificacion_estrellas' => $calificacion_estrellas,
    'tipo_producto' => $tipo_producto,
    'solo_ofertas' => $solo_ofertas,
    'precio_min' => $rango_precios['precio_max'] > 0 ? $precio_min_solicitado : null,
    'precio_max' => $rango_precios['precio_max'] > 0 ? $precio_max_solicitado : null,
    'busqueda' => $texto_busqueda,
    'orden' => $orden,
    'offset' => ($pagina_actual - 1) * $productos_por_pagina,
    'limite' => $productos_por_pagina,
];

$resultado_catalogo = catalogo_productos_filtrados($opciones_consulta);
$productos_lista = $resultado_catalogo['productos'];
$total_resultados = (int) $resultado_catalogo['total'];
// Total global para el subtítulo del hero (independiente de filtros activos)
$total_productos_catalogo = total_productos_activos();

// Valores posibles de cada faceta: solo entidades que tienen al menos un producto listable
$categorias_opciones = catalogo_categorias_con_productos_activos();
$plataformas_opciones = catalogo_plataformas_con_productos_activos();
$marcas_opciones = catalogo_marcas_con_productos_activos();
$generos_opciones = catalogo_generos_con_productos_activos();

// Chip «Todos» activo cuando no hay tipo, ni oferta, ni categoría aplicada por ficha rápida
$ficha_activa_todos = $tipo_producto === '' && !$solo_ofertas && count($filtro_categorias) === 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Catálogo — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<?php
// Barra superior según rol (patrón común en vistas públicas)
if ($logueo === 1) {
    include './plantillas/navbar_admin.php';
} elseif ($logueo >= 2) {
    include './plantillas/navbar_user.php';
} else {
    include './plantillas/navbar_publico.php';
}
?>
<div class="mobile-nav" id="mobileNav">
  <div class="mobile-nav-overlay"></div>
  <div class="mobile-nav-drawer">
    <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
    <div class="mobile-nav-links">
      <a href="/index.php">Inicio</a>
      <a href="/views/catalogo.php">Catálogo</a>
      <a href="/views/resenas.php">Reseñas</a>
      <a href="/views/opiniones.php">Opiniones</a>
    </div>
  </div>
</div>

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="/index.php">Inicio</a><span>/</span><span class="text-white">Catálogo</span>
    </div>
    <h1 class="page-hero-title">Catálogo completo</h1>
    <p class="page-hero-sub"><?php echo (int) $total_productos_catalogo; ?> productos activos — Videojuegos, periféricos y hardware</p>
  </div>
</div>

<div class="container">
  <div class="category-strip mb-3 flex-wrap">
    <a href="<?php echo htmlspecialchars(catalogo_url_con_ficha([]), ENT_QUOTES, 'UTF-8'); ?>"
       class="cat-chip<?php echo $ficha_activa_todos ? ' active' : ''; ?>">Todos</a>
    <a href="<?php echo htmlspecialchars(catalogo_url_con_ficha(['tipo_producto' => 'software']), ENT_QUOTES, 'UTF-8'); ?>"
       class="cat-chip<?php echo $tipo_producto === 'software' && count($filtro_categorias) === 0 && !$solo_ofertas ? ' active' : ''; ?>">Videojuegos</a>
    <?php foreach ($categorias_opciones as $categoria_fila): ?>
    <a href="<?php echo htmlspecialchars(catalogo_url_con_ficha(['categoria' => [(int) $categoria_fila['id_categoria']]]), ENT_QUOTES, 'UTF-8'); ?>"
       class="cat-chip<?php echo count($filtro_categorias) === 1 && (int) $filtro_categorias[0] === (int) $categoria_fila['id_categoria'] && $tipo_producto === '' && !$solo_ofertas ? ' active' : ''; ?>">
      <?php echo htmlspecialchars($categoria_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
    </a>
    <?php endforeach; ?>
    <a href="<?php echo htmlspecialchars(catalogo_url_con_ficha(['oferta' => '1']), ENT_QUOTES, 'UTF-8'); ?>"
       class="cat-chip<?php echo $solo_ofertas ? ' active' : ''; ?>">Ofertas</a>
  </div>

  <div class="catalog-layout">
    <form method="get" action="catalogo.php" id="catalogoFiltroForm">
    <aside class="filters-sidebar">
      <?php if (!empty($categorias_opciones)): ?>
      <div class="filter-card">
        <div class="filter-title">Categoría <span class="filter-arrow"><i class="fas fa-chevron-down" style="font-size:11px"></i></span></div>
        <div class="filter-group">
          <?php foreach ($categorias_opciones as $categoria_fila): ?>
          <label class="filter-check">
            <input type="checkbox" name="categoria[]" value="<?php echo (int) $categoria_fila['id_categoria']; ?>"
              <?php echo in_array((int) $categoria_fila['id_categoria'], $filtro_categorias, true) ? ' checked' : ''; ?> />
            <?php echo htmlspecialchars($categoria_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="filter-card">
        <div class="filter-title">Tipo de producto</div>
        <div class="filter-group">
          <label class="filter-check">
            <input type="radio" name="tipo_producto" value=""<?php echo $tipo_producto === '' ? ' checked' : ''; ?> /> Todos
          </label>
          <label class="filter-check">
            <input type="radio" name="tipo_producto" value="software"<?php echo $tipo_producto === 'software' ? ' checked' : ''; ?> /> Software (videojuegos)
          </label>
          <label class="filter-check">
            <input type="radio" name="tipo_producto" value="hardware"<?php echo $tipo_producto === 'hardware' ? ' checked' : ''; ?> /> Hardware y otros
          </label>
        </div>
      </div>

      <?php if (!empty($plataformas_opciones)): ?>
      <div class="filter-card">
        <div class="filter-title">Plataforma</div>
        <div class="filter-group">
          <?php foreach ($plataformas_opciones as $plataforma_fila): ?>
          <label class="filter-check">
            <input type="checkbox" name="plataforma[]" value="<?php echo (int) $plataforma_fila['id_plataforma']; ?>"
              <?php echo in_array((int) $plataforma_fila['id_plataforma'], $filtro_plataformas, true) ? ' checked' : ''; ?> />
            <?php echo htmlspecialchars($plataforma_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($marcas_opciones)): ?>
      <div class="filter-card">
        <div class="filter-title">Marca</div>
        <div class="filter-group">
          <?php foreach ($marcas_opciones as $marca_fila): ?>
          <label class="filter-check">
            <input type="checkbox" name="marca[]" value="<?php echo (int) $marca_fila['id_marca']; ?>"
              <?php echo in_array((int) $marca_fila['id_marca'], $filtro_marcas, true) ? ' checked' : ''; ?> />
            <?php echo htmlspecialchars($marca_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($generos_opciones)): ?>
      <div class="filter-card">
        <div class="filter-title">Género</div>
        <div class="filter-group">
          <?php foreach ($generos_opciones as $genero_fila): ?>
          <label class="filter-check">
            <input type="checkbox" name="genero[]" value="<?php echo (int) $genero_fila['id_genero']; ?>"
              <?php echo in_array((int) $genero_fila['id_genero'], $filtro_generos, true) ? ' checked' : ''; ?> />
            <?php echo htmlspecialchars($genero_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($rango_precios['precio_max'] > 0): ?>
      <div class="filter-card">
        <div class="filter-title">Precio (COP)</div>
        <div class="filter-group catalog-precio-rango" id="catalogoPrecioRango"
          data-precio-abs-min="<?php echo (int) $rango_precios['precio_min']; ?>"
          data-precio-abs-max="<?php echo (int) $rango_precios['precio_max']; ?>"
          data-precio-step="1000">
          <div class="catalog-precio-rango__labels small text-muted d-flex justify-content-between w-100">
            <span>Mínimo: <strong class="text-white" id="catalogoPrecioMinTxt"><?php echo htmlspecialchars(publico_fmt_precio_cop($precio_min_solicitado), ENT_QUOTES, 'UTF-8'); ?></strong></span>
            <span>Máximo: <strong class="text-white" id="catalogoPrecioMaxTxt"><?php echo htmlspecialchars(publico_fmt_precio_cop($precio_max_solicitado), ENT_QUOTES, 'UTF-8'); ?></strong></span>
          </div>
          <label class="filter-check flex-column align-items-stretch w-100 gap-1 mb-0">
            <span class="small text-muted">Desde</span>
            <input type="range" class="form-range catalog-precio-range" id="catalogoPrecioRangoMin"
              name="precio_min"
              min="<?php echo (int) $rango_precios['precio_min']; ?>"
              max="<?php echo (int) $rango_precios['precio_max']; ?>"
              step="1000"
              value="<?php echo (int) $precio_min_solicitado; ?>" />
          </label>
          <label class="filter-check flex-column align-items-stretch w-100 gap-1 mb-0">
            <span class="small text-muted">Hasta</span>
            <input type="range" class="form-range catalog-precio-range" id="catalogoPrecioRangoMax"
              name="precio_max"
              min="<?php echo (int) $rango_precios['precio_min']; ?>"
              max="<?php echo (int) $rango_precios['precio_max']; ?>"
              step="1000"
              value="<?php echo (int) $precio_max_solicitado; ?>" />
          </label>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($mostrar_filtro_calificacion): ?>
      <div class="filter-card">
        <div class="filter-title">Calificación (1–5)</div>
        <div class="filter-group">
          <label class="filter-check">
            <input type="radio" name="cal_estrellas" value=""<?php echo $calificacion_estrellas === 0 ? ' checked' : ''; ?> />
            Todos
          </label>
          <?php for ($estrella = 1; $estrella <= 5; $estrella++): ?>
          <label class="filter-check">
            <input type="radio" name="cal_estrellas" value="<?php echo (int) $estrella; ?>"<?php echo $calificacion_estrellas === $estrella ? ' checked' : ''; ?> />
            <?php echo str_repeat('★', $estrella) . str_repeat('☆', 5 - $estrella); ?>
          </label>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>

      <label class="filter-check mb-2">
        <input type="checkbox" name="oferta" value="1"<?php echo $solo_ofertas ? ' checked' : ''; ?> /> Solo ofertas
      </label>

      <button type="submit" class="btn btn-sm w-100" style="background:var(--accent);color:var(--black);font-weight:700;border:none;">Aplicar filtros</button>
    </aside>

    <div class="catalog-main">
      <div class="catalog-toolbar">
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1 me-md-3">
          <div class="catalog-busqueda input-group input-group-sm" style="max-width:360px;">
            <input type="search" name="q" id="catalogoBusquedaInput" class="form-control" autocomplete="off"
              style="background:var(--surface-2);border-color:var(--border);color:var(--white)"
              placeholder="Buscar producto, marca, plataforma, categoría…"
              value="<?php echo htmlspecialchars($texto_busqueda, ENT_QUOTES, 'UTF-8'); ?>" />
            <button type="submit" class="btn btn-sm" id="catalogoBusquedaBtn" style="background:var(--accent);color:var(--black);border:none;font-weight:700;" aria-label="Buscar" title="Buscar">
              <i class="fas fa-search" aria-hidden="true"></i>
            </button>
          </div>
          <span class="catalog-count">Mostrando <strong class="text-white"><?php echo count($productos_lista); ?></strong> de <?php echo (int) $total_resultados; ?> resultado(s)</span>
        </div>
        <div class="catalog-sort">
          <label class="visually-hidden" for="catalogoOrdenSelect">Ordenar por</label>
          <select class="sort-select" name="orden" id="catalogoOrdenSelect" form="catalogoFiltroForm">
            <option value="popular"<?php echo $orden === 'popular' ? ' selected' : ''; ?>>Más populares</option>
            <option value="precio_asc"<?php echo $orden === 'precio_asc' ? ' selected' : ''; ?>>Precio: menor a mayor</option>
            <option value="precio_desc"<?php echo $orden === 'precio_desc' ? ' selected' : ''; ?>>Precio: mayor a menor</option>
            <option value="nuevo"<?php echo $orden === 'nuevo' ? ' selected' : ''; ?>>Más recientes</option>
            <option value="rating"<?php echo $orden === 'rating' ? ' selected' : ''; ?>>Mejor calificación</option>
          </select>
          <div class="view-btns">
            <button type="button" class="view-btn active" data-view="grid" title="Cuadrícula"><i class="fas fa-th-large"></i></button>
            <button type="button" class="view-btn" data-view="list" title="Lista"><i class="fas fa-list"></i></button>
          </div>
        </div>
      </div>

      <div class="products-grid" id="catalogProductGrid">
        <?php if (empty($productos_lista)): ?>
        <p class="text-muted">No hay productos que coincidan con los filtros.</p>
        <?php else: ?>
        <?php foreach ($productos_lista as $producto_fila):
            $precio_producto = (float) $producto_fila['precio'];
            $precio_original_producto = (float) $producto_fila['precio_original'];
            $en_oferta = $precio_producto < $precio_original_producto;
            $pct = $en_oferta ? producto_porcentaje_descuento($precio_producto, $precio_original_producto) : 0;
            $plataformas_texto = $producto_fila['plataformas_txt'] ?: ($producto_fila['categoria_nombre'] ?? '');
            ?>
        <a href="/views/producto.php?id=<?php echo (int) $producto_fila['id_producto']; ?>" class="product-card text-decoration-none text-reset">
          <div class="product-img-wrap">
            <?php if ($en_oferta || !empty($producto_fila['destacado'])): ?>
            <span class="product-badge"><?php
            if ($en_oferta) {
                echo '<span class="badge badge-sale">-' . (int) round($pct) . '%</span>';
            } elseif (!empty($producto_fila['destacado'])) {
                echo '<span class="badge badge-hot">Destacado</span>';
            }
            ?></span>
            <?php endif; ?>
            <button type="button" class="product-wishlist" tabindex="-1" onclick="event.preventDefault();"><i class="far fa-heart"></i></button>
            <img src="<?php echo htmlspecialchars($producto_fila['imagen_principal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo htmlspecialchars($producto_fila['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
            <div class="product-overlay">
              <span class="btn-add-cart"><i class="fas fa-cart-plus"></i> Añadir</span>
            </div>
          </div>
          <div class="product-body">
            <div class="product-platform"><?php echo htmlspecialchars($plataformas_texto, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="product-name"><?php echo htmlspecialchars($producto_fila['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="product-footer">
              <div>
                <span class="product-price"><?php echo publico_fmt_precio_cop($precio_producto); ?></span>
                <?php if ($en_oferta): ?>
                <span class="product-price-old"><?php echo publico_fmt_precio_cop($precio_original_producto); ?></span>
                <?php endif; ?>
              </div>
              <div class="product-rating"><?php if (!empty($producto_fila['calificacion_promedio']) && (int) ($producto_fila['total_opiniones'] ?? 0) > 0): ?>
                <span class="star">★</span> <?php echo number_format((float) $producto_fila['calificacion_promedio'], 1, ',', '.'); ?>
                <?php else: ?>
                <span class="text-muted small">Sin valoraciones</span>
                <?php endif; ?></div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php
        publico_renderizar_paginacion('catalogo.php', $pagina_actual, $total_resultados, $productos_por_pagina);
      ?>
    </div>
    </form>
  </div>
</div>

<?php include './plantillas/footer.php'; ?>

<script>
(function () {
  var form = document.getElementById('catalogoFiltroForm');
  var selectOrden = document.getElementById('catalogoOrdenSelect');
  if (form && selectOrden) {
    selectOrden.addEventListener('change', function () {
      form.submit();
    });
  }

  var wrap = document.getElementById('catalogoPrecioRango');
  if (wrap && form) {
    var minEl = document.getElementById('catalogoPrecioRangoMin');
    var maxEl = document.getElementById('catalogoPrecioRangoMax');
    var minTxt = document.getElementById('catalogoPrecioMinTxt');
    var maxTxt = document.getElementById('catalogoPrecioMaxTxt');
    var absMin = parseFloat(wrap.getAttribute('data-precio-abs-min') || '0');
    var absMax = parseFloat(wrap.getAttribute('data-precio-abs-max') || '0');

    function fmtCop(n) {
      var v = Math.round(Number(n) || 0);
      return '$' + v.toLocaleString('es-CO');
    }

    function clamp(v, lo, hi) {
      return Math.min(hi, Math.max(lo, v));
    }

    function sync() {
      if (!minEl || !maxEl) {
        return;
      }
      var vmin = parseFloat(minEl.value);
      var vmax = parseFloat(maxEl.value);
      if (vmin > vmax) {
        var t = vmin;
        vmin = vmax;
        vmax = t;
        minEl.value = String(vmin);
        maxEl.value = String(vmax);
      }
      if (minTxt) {
        minTxt.textContent = fmtCop(vmin);
      }
      if (maxTxt) {
        maxTxt.textContent = fmtCop(vmax);
      }
    }

    minEl.addEventListener('input', function () {
      var vmin = parseFloat(minEl.value);
      var vmax = parseFloat(maxEl.value);
      if (vmin > vmax) {
        maxEl.value = String(vmin);
      }
      minEl.value = String(clamp(vmin, absMin, absMax));
      sync();
    });
    maxEl.addEventListener('input', function () {
      var vmin = parseFloat(minEl.value);
      var vmax = parseFloat(maxEl.value);
      if (vmax < vmin) {
        minEl.value = String(vmax);
      }
      maxEl.value = String(clamp(vmax, absMin, absMax));
      sync();
    });
    sync();
  }

})();
</script>
<script type="module" src="../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
