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
  <title>Reseña: Baldur's Gate 3 — ZonaPixel</title>
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
    <div class="mobile-nav-links"><a href="../index.html">Inicio</a><a href="resenas.html">Reseñas</a></div>
  </div>
</div>

<div class="page-hero py-4">
  <div class="container">
    <div class="breadcrumb-nav">
      <a href="../index.html">Inicio</a><span>/</span>
      <a href="resenas.html">Reseñas</a><span>/</span>
      <span class="text-white">Baldur's Gate 3</span>
    </div>
  </div>
</div>

<div class="container py-5" style="max-width:860px;">

  <!-- HEADER -->
  <div class="d-flex align-items-start gap-4 mb-4 flex-wrap">
    <img src="https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png"
      style="width:120px; height:160px; object-fit:cover; border-radius:var(--radius-lg); flex-shrink:0" alt="BG3" />
    <div style="flex:1; min-width:200px">
      <div style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--accent); margin-bottom:8px">
        Reseña Editorial · RPG
      </div>
      <h1 style="font-family:var(--font-display); font-size:clamp(1.8rem,4vw,2.6rem); font-weight:800; letter-spacing:-1.5px; line-height:1.1; margin-bottom:12px">
        Baldur's Gate 3: El RPG que redefinió el género
      </h1>
      <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap; color:var(--muted); font-size:.88rem; margin-bottom:16px">
        <span>Por <strong style="color:var(--white)">ZonaPixel Staff</strong></span>
        <span>3 agosto 2023</span>
        <span>PC · PlayStation 5</span>
      </div>
      <div style="display:flex; align-items:center; gap:16px">
        <div style="background:var(--accent); color:var(--black); font-family:var(--font-display); font-weight:800; font-size:2.5rem; width:72px; height:72px; border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center">
          9.6
        </div>
        <div>
          <div style="color:#ffd700; font-size:1.4rem">★★★★★</div>
          <div style="font-size:.82rem; color:var(--muted)">Excepcional</div>
        </div>
      </div>
    </div>
  </div>

  <!-- BODY -->
  <div style="display:flex; flex-direction:column; gap:20px; color:var(--muted); font-size:1rem; line-height:1.85">
    <p>Larian Studios ha entregado algo que va más allá de lo que cualquier fan del género podía esperar. <strong style="color:var(--white)">Baldur's Gate 3</strong> no es solo el mejor RPG de la generación — es una declaración de principios sobre lo que significa diseñar un juego que respeta verdaderamente la inteligencia y creatividad del jugador.</p>

    <img src="https://image.api.playstation.com/vulcan/ap/rnd/202302/2321/ba706e54d68d10a0eb6ab7c36cdad9178c58b7fb7bb03d28.png"
      style="width:100%; height:320px; object-fit:cover; border-radius:var(--radius-lg)" alt="" />

    <h2 style="font-family:var(--font-display); font-weight:800; font-size:1.4rem; color:var(--white); letter-spacing:-.5px">Una libertad narrativa sin precedentes</h2>
    <p>Cada decisión tiene peso real. Las consecuencias se despliegan a lo largo de decenas de horas, y el juego no juzga al jugador por sus elecciones. Esta filosofía de diseño, rara en el panorama actual dominado por experiencias lineales, hace que cada partida sea genuinamente única.</p>

    <h2 style="font-family:var(--font-display); font-weight:800; font-size:1.4rem; color:var(--white); letter-spacing:-.5px">Combate estratégico que engancha</h2>
    <p>El sistema de combate por turnos, adaptado fielmente de las reglas de D&D 5ta edición, resulta profundo sin ser inaccesible. La combinación de habilidades, el terreno y el posicionamiento ofrecen posibilidades casi infinitas para resolver los encuentros.</p>

    <h2 style="font-family:var(--font-display); font-weight:800; font-size:1.4rem; color:var(--white); letter-spacing:-.5px">Veredicto</h2>
    <p>Baldur's Gate 3 es una obra que definirá el género durante los próximos años. Exige tiempo y atención, pero recompensa con creces cada hora invertida. Imprescindible.</p>
  </div>

  <!-- BUY CTA -->
  <div style="margin-top:40px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-xl); padding:28px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px">
    <div>
      <div style="font-size:.82rem; color:var(--muted); margin-bottom:6px">Disponible en ZonaPixel</div>
      <div style="font-family:var(--font-display); font-weight:800; font-size:1.6rem; color:var(--accent)">$189.900 COP</div>
    </div>
    <a href="producto.html" class="btn-primary" style="text-decoration:none">
      <i class="fas fa-shopping-cart"></i> Comprar ahora
    </a>
  </div>

  <!-- COMMENTS -->
  <div style="margin-top:48px">
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
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">PM</div>
          <div><div class="opinion-username">PixelMaster</div><div class="opinion-game">Hace 2 días</div></div>
        </div>
        <p class="opinion-text">"Totalmente de acuerdo. Es un juego que redefine lo que puede ser un RPG. Compré en ZonaPixel y llegó en tiempo récord."</p>
      </div>
      <div class="opinion-card">
        <div class="opinion-card-header">
          <div class="opinion-avatar">LG</div>
          <div><div class="opinion-username">Laura_G</div><div class="opinion-game">Hace 5 días</div></div>
        </div>
        <p class="opinion-text">"La reseña captura perfectamente la experiencia. Hay que advertir que el Act 3 tiene algunos problemas de rendimiento."</p>
      </div>
    </div>
  </div>

</div>

<?php 
  include './plantillas/footer.php';
?>
<script type="module" src="../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
