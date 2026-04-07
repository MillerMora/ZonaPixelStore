<?php
/**
 * Listado público de opiniones de comunidad (aprobadas) con los mismos patrones de filtro que reseñas.
 */
session_start();

require_once __DIR__ . '/php/opiniones/opinionModel.php';
require_once __DIR__ . '/php/publico/paginacion_helper.php';
require_once __DIR__ . '/php/publico/listado_publico_helpers.php';
require_once __DIR__ . '/php/publico/listado_reseñas_opiniones_shared.php';

$logueo = null;
if (isset($_SESSION['rol'])) {
    $logueo = $_SESSION['rol'];
}

// Facetas basadas únicamente en opiniones aprobadas
$opciones_plataforma = opiniones_publicas_opciones_plataforma();
$opciones_marca = opiniones_publicas_opciones_marca();
$tipos_existen = opiniones_publicas_existen_por_tipo_producto();
$opciones_calificacion = opiniones_publicas_opciones_calificacion();

$filtro_plataformas = publico_parametros_get_array('plataforma');
$filtro_marcas = publico_parametros_get_array('marca');

// Calificación exacta 1–5 validada contra conteos reales por estrella
$estrellas_permitidas_opiniones = array_map('intval', array_column($opciones_calificacion, 'valor'));
$filtro_estrellas_comunidad = 0;
if (isset($_GET['estrellas']) && (string) $_GET['estrellas'] !== '') {
    $estrellas_solicitadas = (int) $_GET['estrellas'];
    if (in_array($estrellas_solicitadas, $estrellas_permitidas_opiniones, true)) {
        $filtro_estrellas_comunidad = $estrellas_solicitadas;
    }
}

$tipo_producto = '';
if (isset($_GET['tipo_producto']) && in_array($_GET['tipo_producto'], ['software', 'hardware'], true)) {
    $tipo_producto = $_GET['tipo_producto'];
}

$texto_busqueda = publico_texto_busqueda_get('q');

$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$pagina_actual = max(1, $pagina_actual);
$elementos_por_pagina = 9;

$resultado_listado = opiniones_publicas_listado([
    'filtro_plataformas' => $filtro_plataformas,
    'filtro_marcas' => $filtro_marcas,
    'filtro_estrellas_comunidad' => $filtro_estrellas_comunidad,
    'tipo_producto' => $tipo_producto,
    'busqueda' => $texto_busqueda,
    'offset' => ($pagina_actual - 1) * $elementos_por_pagina,
    'limite' => $elementos_por_pagina,
]);

$filas_opiniones = $resultado_listado['filas'];
$total_resultados = (int) $resultado_listado['total'];

// Resalta chip «Todos» cuando no hay tipo ni facetas aplicadas
$claves_ficha_rapida = ['tipo_producto', 'plataforma', 'marca', 'estrellas'];
$ficha_todos_activa = $tipo_producto === '' && count($filtro_plataformas) === 0 && count($filtro_marcas) === 0 && $filtro_estrellas_comunidad === 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Opiniones — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<?php
// Navegación según sesión
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
      <a href="/index.php">Inicio</a><span>/</span><span class="text-white">Opiniones</span>
    </div>
    <h1 class="page-hero-title">Opiniones de la comunidad</h1>
    <p class="page-hero-sub"><?php echo (int) $total_resultados; ?> opinión(es) con los filtros actuales</p>
  </div>
</div>

<section class="section-gap">
  <div class="container">
    <div class="d-flex justify-content-end mb-3">
      <a href="/views/nueva-opinion.php" class="btn-primary text-decoration-none">
        <i class="fas fa-pen"></i> Escribir opinión
      </a>
    </div>

    <form method="get" action="opiniones.php" id="opinionesFiltroForm">
      <div class="category-strip align-items-center w-100">
        <div class="input-group flex-grow-1 min-w-0" style="min-width:min(100%,240px)">
          <input type="search" name="q" id="opinionesBusquedaInput" class="form-control" autocomplete="off"
            style="background:var(--surface-2);border-color:var(--border);color:var(--white)"
            placeholder="Producto, marca, plataforma, género, título, usuario…"
            value="<?php echo htmlspecialchars($texto_busqueda, ENT_QUOTES, 'UTF-8'); ?>" />
          <button type="submit" class="btn" id="opinionesBusquedaBtn" style="background:var(--accent);color:var(--black);border:none;font-weight:700;" aria-label="Buscar" title="Buscar">
            <i class="fas fa-search" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <div class="row g-4">
      <div class="col-lg-3">
        <div class="filter-card mb-3">
          <div class="filter-title">Tipo de producto</div>
          <div class="filter-group">
            <label class="filter-check">
              <input type="radio" name="tipo_producto" value=""<?php echo $tipo_producto === '' ? ' checked' : ''; ?> /> Todos
            </label>
            <?php if (!empty($tipos_existen['software'])): ?>
            <label class="filter-check">
              <input type="radio" name="tipo_producto" value="software"<?php echo $tipo_producto === 'software' ? ' checked' : ''; ?> /> Software
            </label>
            <?php endif; ?>
            <?php if (!empty($tipos_existen['hardware'])): ?>
            <label class="filter-check">
              <input type="radio" name="tipo_producto" value="hardware"<?php echo $tipo_producto === 'hardware' ? ' checked' : ''; ?> /> Hardware
            </label>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($opciones_plataforma)): ?>
        <div class="filter-card mb-3">
          <div class="filter-title">Plataforma</div>
          <div class="filter-group">
            <?php foreach ($opciones_plataforma as $plataforma_fila): ?>
            <label class="filter-check">
              <input type="checkbox" name="plataforma[]" value="<?php echo (int) $plataforma_fila['id_plataforma']; ?>"
                <?php echo in_array((int) $plataforma_fila['id_plataforma'], $filtro_plataformas, true) ? ' checked' : ''; ?> />
              <?php echo htmlspecialchars($plataforma_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($opciones_marca)): ?>
        <div class="filter-card mb-3">
          <div class="filter-title">Marca</div>
          <div class="filter-group">
            <?php foreach ($opciones_marca as $marca_fila): ?>
            <label class="filter-check">
              <input type="checkbox" name="marca[]" value="<?php echo (int) $marca_fila['id_marca']; ?>"
                <?php echo in_array((int) $marca_fila['id_marca'], $filtro_marcas, true) ? ' checked' : ''; ?> />
              <?php echo htmlspecialchars($marca_fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($opciones_calificacion)): ?>
        <div class="filter-card mb-3">
          <div class="filter-title">Calificación (1–5)</div>
          <div class="filter-group">
            <label class="filter-check">
              <input type="radio" name="estrellas" value=""<?php echo $filtro_estrellas_comunidad === 0 ? ' checked' : ''; ?> />
              Todas
            </label>
            <?php foreach ($opciones_calificacion as $opcion_cal): ?>
            <label class="filter-check">
              <input type="radio" name="estrellas" value="<?php echo (int) $opcion_cal['valor']; ?>"
                <?php echo $filtro_estrellas_comunidad === (int) $opcion_cal['valor'] ? ' checked' : ''; ?> />
              <?php echo htmlspecialchars($opcion_cal['etiqueta'], ENT_QUOTES, 'UTF-8'); ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-sm w-100" style="background:var(--accent);color:var(--black);font-weight:700;border:none;">Aplicar filtros</button>
      </div>

      <div class="col-lg-9">
        <div class="d-grid gap-3" style="grid-template-columns:repeat(auto-fill, minmax(280px,1fr));">
          <?php if (empty($filas_opiniones)): ?>
          <p class="text-muted">No hay opiniones que coincidan con los filtros.</p>
          <?php else: ?>
          <?php foreach ($filas_opiniones as $opinion_fila):
              $iniciales = publico_iniciales_usuario(
                  $opinion_fila['autor_nombre'] ?? '',
                  $opinion_fila['autor_apellido'] ?? '',
                  $opinion_fila['autor_username'] ?? ''
              );
              $nombre_mostrado = trim(($opinion_fila['autor_nombre'] ?? '') . ' ' . ($opinion_fila['autor_apellido'] ?? ''));
              if ($nombre_mostrado === '') {
                  $nombre_mostrado = $opinion_fila['autor_username'] ?? 'Usuario';
              }
              $sub_linea = publico_estrellas_texto_1_a_5($opinion_fila['calificacion']) . ' · ';
              $sub_linea .= htmlspecialchars($opinion_fila['producto_nombre'] ?? '', ENT_QUOTES, 'UTF-8');
              if (!empty($opinion_fila['plataforma_opinion_nombre'])) {
                  $sub_linea .= ' · ' . htmlspecialchars($opinion_fila['plataforma_opinion_nombre'], ENT_QUOTES, 'UTF-8');
              }
              ?>
          <div class="opinion-card">
            <div class="opinion-card-header">
              <div class="opinion-avatar"><?php echo htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8'); ?></div>
              <div>
                <div class="opinion-username"><?php echo htmlspecialchars($nombre_mostrado, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="opinion-game"><?php echo $sub_linea; ?></div>
              </div>
            </div>
            <p class="opinion-text"><?php
              $fragmento_opinion = '';
              if (trim((string) ($opinion_fila['titulo'] ?? '')) !== '') {
                  $fragmento_opinion = publico_texto_resumen($opinion_fila['titulo'], 120) . ' — ';
              }
              $fragmento_opinion .= publico_texto_resumen($opinion_fila['contenido'] ?? '', 300);
              echo htmlspecialchars($fragmento_opinion, ENT_QUOTES, 'UTF-8');
            ?></p>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php publico_renderizar_paginacion('opiniones.php', $pagina_actual, $total_resultados, $elementos_por_pagina); ?>
      </div>
      </div>
    </form>
  </div>
</section>

<?php include './plantillas/footer.php'; ?>

<script type="module" src="../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
