<?php
session_start();
$logueo = null;
if (isset($_SESSION['rol'])) {
    $logueo = $_SESSION['rol'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | ZonaPixel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .error-hero {
            min-height: 80vh;
            display: flex;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, var(--surface) 0%, var(--black) 100%);
            overflow: hidden;
        }
        .error-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 50% 40% at 80% 20%, var(--accent-glow) 0%, transparent 50%);
            opacity: 0.3;
        }
        .error-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 24px;
            color: var(--white);
        }
        .error-code {
            font-family: var(--font-display);
            font-size: clamp(6rem, 20vw, 18rem);
            font-weight: 900;
            letter-spacing: -0.1em;
            background: linear-gradient(135deg, var(--accent) 0%, var(--cyan) 70%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 60px var(--accent-glow);
            margin-bottom: 0.3em;
            animation: glow 2s ease-in-out infinite alternate;
        }
        .error-title {
            font-family: var(--font-display);
            font-size: clamp(1.8rem, 5vw, 3rem);
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--white);
        }
        .error-subtitle {
            font-size: 1.3rem;
            color: var(--muted);
            margin-bottom: 2.5rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        @keyframes glow {
            from { filter: drop-shadow(0 0 20px var(--accent-glow)); }
            to { filter: drop-shadow(0 0 40px var(--accent-glow)); }
        }
        .error-actions { display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap; margin-bottom: 3rem; }
        .error-links { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .error-link {
            padding: 0.8rem 1.8rem;
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--white);
            border-radius: var(--radius-lg);
            font-weight: 500;
            transition: all var(--transition);
        }
        .error-link:hover {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 8px 25px var(--accent-glow);
        }
        .mb-4 { margin-bottom: 1.5rem !important; }
        .me-2 { margin-right: 0.5rem !important; }
    </style>
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

    <section class="error-hero">
        <div class="error-content">
            <div class="hero-eyebrow text-accent mb-4">
                <i class="fas fa-exclamation-triangle"></i> Error 404
            </div>
            <h1 class="error-code">404</h1>
            <h2 class="error-title">¡Página no encontrada!</h2>
            <p class="error-subtitle">
                <i class="fas fa-gamepad text-accent me-2"></i>
                Ups! Parece que esta página se perdió en el mundo pixelado. ¡No te preocupes, aquí tienes algunas opciones!
            </p>
            <div class="error-actions">
                <a href="/index.php" class="btn-primary" style="padding: 1.2rem 3rem; font-size: 1.1rem;">
                    <i class="fas fa-home me-2"></i>Ir al Inicio
                </a>
                <a href="/views/catalogo.php" class="btn-secondary" style="padding: 1.2rem 3rem; font-size: 1.1rem;">
                    <i class="fas fa-gamepad me-2"></i>Explorar Catálogo
                </a>
            </div>
            <div class="error-links">
                <a href="/views/catalogo.php" class="error-link"><i class="fas fa-list me-1"></i>Catálogo</a>
                <a href="/views/resenas.php" class="error-link"><i class="fas fa-star me-1"></i>Reseñas</a>
                <a href="/views/carrito.php" class="error-link"><i class="fas fa-shopping-cart me-1"></i>Carrito</a>
            </div>
        </div>
    </section>

    <?php include './plantillas/footer.php'; ?>
    
    <script type="module" src="../js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
