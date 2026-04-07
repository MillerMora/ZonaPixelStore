<!-- Barra de navegación: visitante sin sesión (login y registro visibles) -->
<nav class="navbar">
    <div class="container">
        <a href="/index.php" class="navbar-brand">
            <span class="logo-dot"></span>ZonaPixel
        </a>
        <div class="nav-links">
            <a href="/index.php" class="active">Inicio</a>
            <a href="/views/catalogo.php">Catálogo</a>
            <a href="/views/resenas.php">Reseñas</a>
            <a href="/views/opiniones.php">Opiniones</a>
        </div>
        <div class="nav-actions">
            <button class="nav-icon-btn" title="Buscar"><i class="fas fa-search"></i></button>
            <a href="/views/carrito.php" class="nav-icon-btn" title="Carrito">
                <i class="fas fa-shopping-cart"></i>
            </a>
            <a href="/views/login.php" class="btn-nav-login">Iniciar sesión</a>
            <a href="/views/register.php" class="btn-nav-login">Registrarse</a>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>