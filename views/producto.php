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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Baldur's Gate 3 — ZonaPixel</title>
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
    <div class="mobile-nav-links">
      <a href="../index.html">Inicio</a>
      <a href="catalogo.html">Catálogo</a>
    </div>
  </div>
</div>

<div class="page-hero py-4">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="/index.php">Inicio</a><span>/</span>
      <a href="/views/catalogo.php">Catálogo</a><span>/</span>
      <a href="/views/catalogo.php">RPG</a><span>/</span>
      <span class="text-white">Baldur's Gate 3</span>
    </div>
  </div>
</div>

<div class="container">
  <div class="product-detail-grid">

    <!-- GALLERY -->
    <div class="product-gallery">
      <div class="gallery-main">
        <img src="https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png" alt="Baldur's Gate 3" id="mainImg" />
      </div>
      <div class="gallery-thumbs">
        <div class="gallery-thumb active">
          <img src="https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png" alt="" />
        </div>
        <div class="gallery-thumb">
          <img src="https://upload.wikimedia.org/wikipedia/en/f/fb/The_Legend_of_Zelda_Tears_of_the_Kingdom_cover.jpg" alt="" />
        </div>
        <div class="gallery-thumb">
          <img src="https://cdn1.epicgames.com/offer/c4763f236d08423eb47b4c3008779c84/EGS_AlanWake2_RemedyEntertainment_S2_1200x1600-c7c8091ddac0f9669c8e5905bca88aaa" alt="" />
        </div>
        <div class="gallery-thumb">
          <img src="https://image.api.playstation.com/vulcan/ap/rnd/202210/0706/EVWyZD63pahuh95eKloFaJuC.png" alt="" />
        </div>
      </div>
    </div>

    <!-- INFO -->
    <div class="product-detail-info">
      <div class="product-detail-platform">
        <span>RPG</span> <span class="text-muted">·</span>
        <span class="badge badge-hot">GOTY 2023</span>
      </div>
      <h1 class="product-detail-title">Baldur's Gate 3</h1>
      <div class="product-detail-rating">
        <div class="stars-row">
          <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
          <i class="fas fa-star"></i><i class="fas fa-star"></i>
        </div>
        <span class="rating-count">9.6 · 3,842 reseñas</span>
      </div>

      <div class="product-detail-price">$189.900 COP</div>
      <div class="product-detail-price-old">Precio original: <s>$239.900</s> — Ahorras $50.000</div>

      <p class="product-detail-desc">
        Baldur's Gate 3 es un juego de rol de aventura por turnos desarrollado y publicado por Larian Studios. Explora el mundo de Faerûn con hasta cuatro jugadores en cooperativo, tomando decisiones que impactan la historia de maneras inesperadas.
      </p>

      <div class="product-options-title">Plataforma</div>
      <div class="option-chips">
        <button class="option-chip active">PC (Steam)</button>
        <button class="option-chip">PlayStation 5</button>
        <button class="option-chip">Xbox Series X</button>
      </div>

      <div class="product-options-title">Edición</div>
      <div class="option-chips">
        <button class="option-chip active">Estándar — $189.900</button>
        <button class="option-chip">Deluxe — $229.900</button>
      </div>

      <div class="qty-cart-row">
        <div class="qty-control">
          <button class="qty-btn qty-minus">−</button>
          <input type="number" class="qty-input" value="1" min="1" />
          <button class="qty-btn qty-plus">+</button>
        </div>
        <button class="btn-add-to-cart">
          <i class="fas fa-shopping-cart"></i> Agregar al carrito
        </button>
        <button class="btn-wishlist-lg"><i class="far fa-heart"></i></button>
      </div>

      <ul class="product-meta-list">
        <li><span class="meta-key">Desarrollador</span> Larian Studios</li>
        <li><span class="meta-key">Género</span> RPG por turnos</li>
        <li><span class="meta-key">Lanzamiento</span> 3 agosto 2023</li>
        <li><span class="meta-key">Idioma</span> Español incluido</li>
        <li><span class="meta-key">Multijugador</span> Co-op hasta 4 jugadores</li>
        <li><span class="meta-key">Envío</span> <span class="fg-success"><i class="fas fa-check"></i> Gratis · 24–48 h</span></li>
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
      <a href="nueva-opinion.html" class="btn-primary text-decoration-none">
        <i class="fas fa-pen"></i> Escribir opinión
      </a>
    </div>
    <div class="d-grid gap-3" style="grid-template-columns:repeat(auto-fill, minmax(280px,1fr));">
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">PM</div>
          <div>
            <div class="opinion-username">PixelMaster</div>
            <div class="opinion-game">★★★★★ · PS5</div>
          </div>
        </div>
        <p class="opinion-text">"Simplemente el mejor RPG en décadas. La libertad que ofrece es abrumadora en el buen sentido. Larian Studios ha creado algo monumental."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">LG</div>
          <div>
            <div class="opinion-username">Laura_G</div>
            <div class="opinion-game">★★★★½ · PC</div>
          </div>
        </div>
        <p class="opinion-text">"Más de 200 horas y todavía descubro cosas nuevas. La historia es increíble y los personajes son de lo mejor que he visto en un videojuego."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">AD</div>
          <div>
            <div class="opinion-username">AnaDev</div>
            <div class="opinion-game">★★★★☆ · PC</div>
          </div>
        </div>
        <p class="opinion-text">"El juego que más me ha enganchado en años. Algunos bugs menores al inicio pero Larian los fue arreglando rápido. Muy recomendado."</p>
      </div>
    </div>
    <div class="text-center mt-4">
      <a href="opiniones.html" class="btn-secondary d-inline-flex align-items-center gap-2">
        Ver todas las opiniones <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </section>
</div>

<?php 
  include './plantillas/footer.php';
?>

<script type="module" src="../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
