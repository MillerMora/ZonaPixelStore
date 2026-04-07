<?php
/**
 * Formulario de nueva opinión (demo): selectores estáticos y widget de estrellas #starRating.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nueva Opinión — ZonaPixel</title>
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

<!-- Hero y bloque de formulario (sin action a back-end en esta maqueta) -->
<div class="page-hero py-4">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="../index.html">Inicio</a><span>/</span><a href="opiniones.html">Opiniones</a><span>/</span><span class="text-white">Nueva opinión</span>
    </div>
    <h1 class="page-hero-title">Escribe tu opinión</h1>
    <p class="page-hero-sub">Comparte tu experiencia con la comunidad</p>
  </div>
</div>

<section class="section-gap">
  <div class="container" style="max-width:700px">
    <div class="p-4" style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl);">

      <div class="form-field">
        <label>Producto</label>
        <select class="form-input cursor-pointer">
          <option value="">Selecciona un producto...</option>
          <option>Baldur's Gate 3</option>
          <option>Zelda: Tears of the Kingdom</option>
          <option>Alan Wake 2</option>
          <option>Resident Evil 4 Remake</option>
          <option>Spider-Man 2</option>
          <option>Logitech G Pro X Superlight 2</option>
          <option>HyperX Alloy Origins TKL</option>
          <option>Otro...</option>
        </select>
      </div>

      <div class="form-field">
        <label>Plataforma</label>
        <div class="option-chips mt-0">
          <button class="option-chip active">PC</button>
          <button class="option-chip">PlayStation 5</button>
          <button class="option-chip">Xbox Series X</button>
          <button class="option-chip">Nintendo Switch</button>
        </div>
      </div>

      <div class="form-field">
        <label>Tu calificación</label>
        <div class="d-flex gap-2 mt-1 fs-28" style="color:var(--border-light)" id="starRating">
          <span class="cursor-pointer" data-val="1">★</span>
          <span class="cursor-pointer" data-val="2">★</span>
          <span class="cursor-pointer" data-val="3">★</span>
          <span class="cursor-pointer" data-val="4">★</span>
          <span class="cursor-pointer" data-val="5">★</span>
        </div>
      </div>

      <div class="form-field">
        <label>Título de tu opinión</label>
        <input type="text" class="form-input" placeholder="Ej: La mejor compra del año" />
      </div>

      <div class="form-field">
        <label>Tu opinión</label>
        <textarea class="form-input resize-vertical" rows="6" placeholder="Cuéntanos tu experiencia con el producto. Sé honesto y detallado..."></textarea>
      </div>

      <div class="d-flex gap-2 justify-content-end mt-2">
        <a href="./opiniones.html" class="btn-secondary d-inline-flex align-items-center gap-2 text-decoration-none">Cancelar</a>
        <button class="btn-primary"><i class="fas fa-paper-plane"></i> Publicar opinión</button>
      </div>
    </div>
  </div>
</section>

<?php 
  include './plantillas/footer.php';
?>

<script type="module" src="../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
