<?php
/**
 * Vista dinámica de reseña individual desde BD.
 * Reemplaza contenido hardcodeado preservando 100% estructura HTML/CSS.
 */
session_start();
$logueo = isset($_SESSION['rol']) ? $_SESSION['rol'] : null;

// Require model (requiere conexion internamente)
require_once './php/resenas/resenasModel.php';

// Parse ID from URL
$id_resena = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$resena = consultar_resena_por_id($id_resena);

if (!$resena) {
    // 404 simple - preservar diseño pero indicar no encontrada
    $titulo_resena = 'Reseña no encontrada';
    $producto_nombre = '';
} else {
    $titulo_resena = htmlspecialchars($resena['titulo'], ENT_QUOTES, 'UTF-8');
    $producto_nombre = htmlspecialchars($resena['producto_nombre'], ENT_QUOTES, 'UTF-8');
}

// Title dinámico
$page_title = $resena ? "Reseña: {$titulo_resena} — {$producto_nombre} — ZonaPixel" : 'Reseña no encontrada — ZonaPixel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $page_title; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<?php if ($logueo === 1): ?>
  <?php include './plantillas/navbar_admin.php'; ?>
<?php elseif ($logueo >= 2): ?>
  <?php include './plantillas/navbar_user.php'; ?>
<?php else: ?>
  <?php include './plantillas/navbar_publico.php'; ?>
<?php endif; ?>

<div class="mobile-nav" id="mobileNav">
  <div class="mobile-nav-overlay"></div>
  <div class="mobile-nav-drawer">
    <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
    <div class="mobile-nav-links">
      <a href="../index.php">Inicio</a>
      <a href="resenas.php">Reseñas</a>
    </div>
  </div>
</div>

<div class="page-hero py-4">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="../index.php">Inicio</a><span>/</span>
      <a href="resenas.php">Reseñas</a><span>/</span>
      <span class="text-white"><?php echo $producto_nombre ?: 'Reseña no encontrada'; ?></span>
    </div>
  </div>
</div>

<div class="container py-5" style="max-width:860px;">

  <?php if (!$resena): ?>
    <div style="text-align:center; padding:60px 20px; color:var(--muted);">
      <h2 style="font-family:var(--font-display); color:var(--white);">Reseña no disponible</h2>
      <p>La reseña solicitada no existe o no está publicada.</p>
      <a href="resenas.php" class="btn-primary" style="text-decoration:none;">Ver todas las reseñas</a>
    </div>
  <?php else: ?>

  <!-- HEADER dinámico -->
  <div class="d-flex align-items-start gap-4 mb-4 flex-wrap">
    <img src="<?php echo htmlspecialchars($resena['imagen_portada'] ?: $resena['imagen_principal'], ENT_QUOTES); ?>"
      style="width:120px; height:160px; object-fit:cover; border-radius:var(--radius-lg); flex-shrink:0" 
      alt="<?php echo htmlspecialchars($producto_nombre, ENT_QUOTES); ?>" />
    <div style="flex:1; min-width:200px">
      <div style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--accent); margin-bottom:8px">
        Reseña Editorial · RPG
      </div>
      <h1 style="font-family:var(--font-display); font-size:clamp(1.8rem,4vw,2.6rem); font-weight:800; letter-spacing:-1.5px; line-height:1.1; margin-bottom:12px">
        <?php echo $titulo_resena; ?>
      </h1>
      <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap; color:var(--muted); font-size:.88rem; margin-bottom:16px">
        <span>Por <strong style="color:var(--white)"><?php echo htmlspecialchars($resena['autor_completo'] ?: $resena['autor_username'], ENT_QUOTES); ?></strong></span>
        <span><?php echo date('j F Y', strtotime($resena['publicada_en'])); ?></span>
        <?php if ($resena['plataformas_txt']): ?>
          <span><?php echo htmlspecialchars($resena['plataformas_txt'], ENT_QUOTES); ?></span>
        <?php endif; ?>
      </div>
      <div style="display:flex; align-items:center; gap:16px">
        <div style="background:var(--accent); color:var(--black); font-family:var(--font-display); font-weight:800; font-size:2.5rem; width:72px; height:72px; border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center">
          <?php echo number_format($resena['calificacion'], 1, ',', '.'); ?>
        </div>
        <div>
          <div style="color:#ffd700; font-size:1.4rem">
            <?php 
            $estrellas = min(5, floor($resena['calificacion'] / 2));
            echo str_repeat('★', $estrellas) . str_repeat('☆', 5 - $estrellas);
            ?>
          </div>
          <div style="font-size:.82rem; color:var(--muted)">
            <?php 
            $calif = $resena['calificacion'];
            if ($calif >= 9) echo 'Excepcional';
            elseif ($calif >= 7) echo 'Sobresaliente';
            elseif ($calif >= 5) echo 'Muy bueno';
            elseif ($calif >= 3) echo 'Bueno';
            else echo 'Regular';
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- BODY dinámico -->
  <div style="display:flex; flex-direction:column; gap:20px; color:var(--muted); font-size:1rem; line-height:1.85">
    <?php echo $resena['contenido']; ?>
  </div>

  <!-- BUY CTA dinámico -->
  <div style="margin-top:40px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl); padding:28px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px">
    <div>
      <div style="font-size:.82rem; color:var(--muted); margin-bottom:6px">Disponible en ZonaPixel</div>
      <div style="font-family:var(--font-display); font-weight:800; font-size:1.6rem; color:var(--accent)">
        $<?php echo number_format($resena['precio'], 0, ',', '.'); ?> COP
      </div>
    </div>
    <a href="producto.php?id=<?php echo $resena['producto_id']; ?>" class="btn-primary" style="text-decoration:none">
      <i class="fas fa-shopping-cart"></i> Comprar ahora
    </a>
  </div>

  <!-- COMMENTS dinámico -->
  <!-- <div style="margin-top:48px">
    <h3 style="font-family:var(--font-display); font-weight:800; font-size:1.3rem; margin-bottom:24px">Comentarios</h3>
    <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); padding:20px; margin-bottom:16px">
      <textarea class="form-input" rows="3" placeholder="Escribe un comentario..." style="resize:none"></textarea>
      <div style="display:flex; justify-content:flex-end; margin-top:12px">
        <button class="btn-primary" style="padding:10px 20px; font-size:.88rem">
          <i class="fas fa-paper-plane"></i> Comentar
        </button>
      </div>
    </div>
    <div class="d-flex flex-column gap-3">
      <?php 
      $comentarios = consultar_comentarios_resena($id_resena);
      if (empty($comentarios)): ?>
        <p class="text-muted" style="padding:20px;">Aún no hay comentarios. ¡Sé el primero!</p>
      <?php else: 
        foreach ($comentarios as $com): 
          $avatar_iniciales = strtoupper(substr($com['username'], 0, 2));
      ?>
        <div class="opinion-card">
          <div class="opinion-card-header">
            <div class="opinion-avatar"><?php echo $avatar_iniciales; ?></div>
            <div>
              <div class="opinion-username"><?php echo htmlspecialchars($com['username'], ENT_QUOTES); ?></div>
              <div class="opinion-game"><?php echo $com['fecha_texto']; ?></div>
            </div>
          </div>
          <p class="opinion-text">"<?php echo htmlspecialchars($com['contenido'], ENT_QUOTES); ?>"</p>
        </div>
      <?php endforeach; 
      endif; ?>
    </div>
  </div> -->

  <?php endif; ?>

</div>

<?php include './plantillas/footer.php'; ?>
<script type="module" src="../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
