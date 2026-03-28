<?php 
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
  <title>Opiniones — ZonaPixel</title>
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


<div class="mobile-nav" id="mobileNav">
  <div class="mobile-nav-overlay"></div>
  <div class="mobile-nav-drawer">
    <button class="mobile-nav-close" id="mobileNavClose"><i class="fas fa-times"></i></button>
<a href="../index.html">Inicio</a><a href="./catalogo.html">Catálogo</a><a href="./resenas.html">Reseñas</a><a href="./opiniones.html">Opiniones</a></div>
  </div>
</div>

<div class="page-hero">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="../index.html">Inicio</a><span>/</span><span class="text-white">Opiniones</span>
    </div>
    <h1 class="page-hero-title">Opiniones de la comunidad</h1>
    <p class="page-hero-sub">Lo que dicen los jugadores sobre sus compras</p>
  </div>
</div>

<section class="section-gap">
  <div class="container">
    <div class="d-flex justify-content-end mb-4">
      <a href="nueva-opinion.html" class="btn-primary text-decoration-none">
        <i class="fas fa-pen"></i> Escribir opinión
      </a>
    </div>

    <div class="category-strip">
      <button class="cat-chip active">Todas</button>
      <button class="cat-chip">Videojuegos</button>
      <button class="cat-chip">Periféricos</button>
      <button class="cat-chip">Hardware</button>
      <button class="cat-chip">★★★★★</button>
    </div>

    <div class="d-grid gap-3" style="grid-template-columns:repeat(auto-fill, minmax(280px,1fr));">
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">CS</div>
          <div><div class="opinion-username">Carlos S.</div><div class="opinion-game">★★★★★ · Alan Wake 2</div></div>
        </div>
        <p class="opinion-text">"Una obra maestra del terror narrativo. La atmósfera es increíble y la historia te atrapa desde el primer minuto. De las mejores experiencias de la generación."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">LG</div>
          <div><div class="opinion-username">Laura_G</div><div class="opinion-game">★★★★☆ · Zelda TotK</div></div>
        </div>
        <p class="opinion-text">"Expande el mundo de BotW de forma que no creía posible. El sistema de construcción es adictivo. A veces el rendimiento baja en zonas densas."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">PM</div>
          <div><div class="opinion-username">PixelMaster</div><div class="opinion-game">★★★★★ · Baldur's Gate 3</div></div>
        </div>
        <p class="opinion-text">"Simplemente el mejor RPG en décadas. La libertad es abrumadora en el buen sentido. Larian Studios ha creado algo histórico."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">AD</div>
          <div><div class="opinion-username">AnaDev</div><div class="opinion-game">★★★☆☆ · Starfield</div></div>
        </div>
        <p class="opinion-text">"Un universo vasto con potencial pero que se siente vacío. Las mecánicas se sienten anticuadas para lo que prometía. Con mods es mejor."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">JR</div>
          <div><div class="opinion-username">JuanRPG</div><div class="opinion-game">★★★★★ · RE4 Remake</div></div>
        </div>
        <p class="opinion-text">"Capcom lo volvió a lograr. Tomaron el original y lo superaron. El ritmo es perfecto, los gráficos increíbles y el combate fluidísimo."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">MK</div>
          <div><div class="opinion-username">MariKit</div><div class="opinion-game">★★★★★ · Mouse Logitech</div></div>
        </div>
        <p class="opinion-text">"El G Pro X Superlight 2 es el mejor mouse que he tenido. Ligero, preciso, batería que dura días. Vale cada peso invertido."</p>
      </div>
    </div>

    <div class="pagination-wrap">
      <button class="page-btn active">1</button>
      <button class="page-btn">2</button>
      <button class="page-btn">3</button>
      <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
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
