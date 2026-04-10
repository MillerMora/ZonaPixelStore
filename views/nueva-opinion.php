<?php
/**
 * Formulario dinámico de nueva opinión pública.
 * Soporta ?id=PRODUCTO (prefill read-only) o acceso directo (dropdown completo).
 */
session_start();

$logueo = $_SESSION['rol'] ?? null;

// Redirect si no logueado
if (!$logueo) {
    $_SESSION['index_login_error'] = 'Debes iniciar sesión para enviar opiniones.';
    header('Location: ../login.php');
    exit;
}

// Sanitize product ID
$producto_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$producto = null;
$productos_query = null;

require_once __DIR__ . '/php/conexion/conexion.php';
require_once __DIR__ . '/php/productos/productoModel.php';
require_once __DIR__ . '/php/plataformas/plataformaModel.php';

$plataformas_result = consultar_plataformas();

if ($producto_id > 0) {
    $producto = consultar_producto_id($producto_id);
    if (!$producto) {
        $_SESSION['error'] = 'Producto no encontrado.';
        header('Location: ../catalogo.php');
        exit;
    }
} else {
    $productos_result = consultar_productos();
}

$plataformas = consultar_plataformas(); // Dinámicas

// Mensajes flash
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $producto ? htmlspecialchars($producto['nombre']) . ' — ' : '' ?>Nueva Opinión — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
<?php
if ($logueo === 1){
  include './plantillas/navbar_admin.php';
} elseif ($logueo >= 2 ){
  include './plantillas/navbar_user.php';
} else {
  include './plantillas/navbar_publico.php';
}
?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
  <?= htmlspecialchars($success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">
  <?= htmlspecialchars($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-hero py-4">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="../index.php">Inicio</a><span>/</span><a href="./opiniones.php">Opiniones</a><span>/</span><span class="text-white">Nueva opinión<?= $producto ? ' — ' . htmlspecialchars($producto['nombre']) : '' ?></span>
    </div>
    <h1 class="page-hero-title">Escribe tu opinión</h1>
    <p class="page-hero-sub">Comparte tu experiencia con la comunidad<?= $producto ? ' sobre ' . htmlspecialchars($producto['nombre']) : '' ?></p>
  </div>
</div>

<section class="section-gap">
  <div class="container" style="max-width:700px">
    <form method="POST" action="php/opiniones/crear_opinion_publica.php<?= $producto_id > 0 ? '?producto_id=' . $producto_id : '' ?>">
      <div class="p-4" style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl);">
        
        <!-- Producto -->
        <div class="form-field mb-4">
          <label>Producto</label>
          <?php if ($producto): ?>
            <input type="hidden" name="producto_id" value="<?= $producto_id ?>">
            <div class="form-input bg-secondary text-white p-3" style="pointer-events: none;">
              <?= htmlspecialchars($producto['nombre']) ?> <small class="opacity-75">(Prefijado)</small>
            </div>
          <?php else: ?>
            <select name="producto_id" class="form-input cursor-pointer" required>
              <option value="">Seleccionar producto...</option>
              <?php while ($fila = mysqli_fetch_assoc($productos_result ?? [])): ?>
                <option value="<?= $fila['id_producto'] ?>"><?= htmlspecialchars($fila['nombre']) ?></option>
              <?php endwhile; ?>
            </select>
          <?php endif; ?>
        </div>

        <!-- Plataforma -->
        <div class="form-field mb-4">
          <label>Plataforma</label>
          <select name="plataforma_id" class="form-input cursor-pointer">
            <option value="">Cualquier plataforma</option>
            <?php while ($plat = mysqli_fetch_assoc($plataformas_result)): ?>
              <option value="<?= $plat['id_plataforma'] ?>"><?= htmlspecialchars($plat['nombre']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- Calificación (JS stars) -->
        <div class="form-field mb-4">
          <label>Tu calificación</label>
          <div class="d-flex gap-2 mt-1 fs-28" style="color:var(--border-light)" id="starRating">
            <span class="cursor-pointer" data-val="1">★</span>
            <span class="cursor-pointer" data-val="2">★</span>
            <span class="cursor-pointer" data-val="3">★</span>
            <span class="cursor-pointer" data-val="4">★</span>
            <span class="cursor-pointer" data-val="5">★</span>
          </div>
          <input type="hidden" name="calificacion" id="ratingValue" value="" min="1" max="5" required />
          <small class="text-muted">Haz clic en las estrellas</small>
        </div>

        <!-- Título -->
        <div class="form-field mb-4">
          <label>Título de tu opinión</label>
          <input type="text" name="titulo" class="form-input" placeholder="Ej: La mejor compra del año" required maxlength="100" />
        </div>

        <!-- Contenido -->
        <div class="form-field mb-4">
          <label>Tu opinión</label>
          <textarea name="contenido" class="form-input resize-vertical" rows="6" placeholder="Cuéntanos tu experiencia con el producto. Sé honesto y detallado..." required maxlength="2000"></textarea>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-2">
          <a href="<?= $producto ? './producto.php?id=' . $producto_id : './opiniones.php' ?>" class="btn-secondary d-inline-flex align-items-center gap-2 text-decoration-none">Cancelar</a>
          <button type="submit" class="btn-primary d-inline-flex align-items-center gap-2">
            <i class="fas fa-paper-plane"></i> Enviar opinión
          </button>
        </div>
      </div>
    </form>
  </div>
</section>

<?php include './plantillas/footer.php'; ?>

<script type="module" src="../js/main.js"></script>
<!-- Star rating JS (asumir modules/starRating.js maneja #starRating y #ratingValue) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Star rating handler (fallback si module no lo hace)
document.addEventListener('DOMContentLoaded', function() {
  const stars = document.querySelectorAll('#starRating span');
  const ratingInput = document.getElementById('ratingValue');
  
  stars.forEach((star, index) => {
    star.addEventListener('click', () => {
      const val = index + 1;
      ratingInput.value = val;
      stars.forEach((s, i) => {
        s.style.color = i < val ? 'var(--accent-strong)' : 'var(--border-light)';
      });
    });
    star.addEventListener('mouseover', () => {
      stars.forEach((s, i) => {
        s.style.color = i < index + 1 ? 'var(--accent-strong)' : 'var(--border-light)';
      });
    });
  });
  stars[0].dispatchEvent(new Event('mouseover')); // Hover inicial
});
</script>
</body>
</html>

