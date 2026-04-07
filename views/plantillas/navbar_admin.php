<!-- Barra para administrador: acceso a dashboard y menú desplegable de cuenta -->
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
                <span class="badge-count">3</span>
            </a>
            <div class="user-dropdown">
                <button class="nav-icon-btn user-dropdown-toggle" title="Cuenta" aria-expanded="false">
                    <i class="fa-regular fa-user"></i>
                </button>
                <div class="user-dropdown-menu">
                    <a href="/views/dashboard.php" class="user-dropdown-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="/views/php/usuarios/editar_usuario_cliente.php" class="user-dropdown-item">
                        <i class="fas fa-user-circle"></i> Perfil
                    </a>
                    <a href="#" class="user-dropdown-item">
                        <i class="fas fa-cog"></i> Configuración
                    </a>
                    <hr class="dropdown-divider">
                    <a href="/views/php/usuarios/login/login.php?cerrar=1" class="user-dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>