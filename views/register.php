<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrarse — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <nav class="navbar">
    <div class="container">
      <a href="../index.html" class="navbar-brand"><span class="logo-dot"></span>ZonaPixel</a>
      <div class="nav-links"><a href="../index.html">Inicio</a><a href="./catalogo.html">Catálogo</a></div>
      <div class="nav-actions"><a href="carrito.html" class="nav-icon-btn"><i class="fas fa-shopping-cart"></i></a></div>
    </div>
  </nav>

  <section class="auth-page py-5">
    <div class="container">
      <div class="auth-card">
        <form action="./php/usuarios/editar_usuario.php?crear=1" method="post">

          <div class="auth-logo">
            <span class="logo-dot" style="width:10px;height:10px"></span>
            <span class="auth-logo-text">ZonaPixel</span>
          </div>
          <h1 class="auth-title">Crea tu cuenta</h1>
          <p class="auth-sub">Únete a la comunidad gamer más grande</p>

          <div class="row g-3">
            <div class="form-field col-md-6">
              <label>Nombre</label>
              <input type="text" class="form-input" name ="nombre"  placeholder="Juan" />
            </div>
            <div class="form-field col-md-6">
              <label>Apellido</label>
              <input type="text" class="form-input" name="apellido" placeholder="Pérez" />
            </div>
          </div>
          <div class="form-field">
            <label>Correo electrónico</label>
            <div class="input-icon-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" class="form-input" name="email" placeholder="juan@correo.com" />
            </div>
          </div>
          <div class="form-field">
            <label>Nombre de usuario</label>
            <div class="input-icon-wrap">
              <i class="fas fa-at"></i>
              <input type="text" class="form-input" name="username" placeholder="@juangamer" />
            </div>
          </div>
          <div class="form-field">
            <label>Contraseña</label>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" class="form-input" name="password" placeholder="Mínimo 8 caracteres" />
            </div>
          </div>
          <div class="form-field">
            <label>Confirmar Contraseña</label>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="confirm_password" class="form-input" name="confirm_password" placeholder="Mínimo 8 caracteres" />
            </div>
          </div>

          <div class="d-flex align-items-start gap-2 mb-3" style="font-size:12px; color:var(--muted)">
            <input type="checkbox" class="form-check-input mt-1" style="accent-color:var(--accent); flex-shrink:0" />
            <span>Acepto los <a href="#" style="color:var(--accent)">Términos de servicio</a> y la <a href="#" style="color:var(--accent)">Política de privacidad</a> de ZonaPixel.</span>
          </div>

          <button class="btn-auth-submit"><i class="fas fa-user-plus"></i> &nbsp;Crear cuenta</button>

          <div class="auth-divider">o regístrate con</div>

          <button class="btn-social-auth">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" height="18" alt="Google" style="display:inline-block" />
            Continuar con Google
          </button>

          <div class="auth-switch">
            ¿Ya tienes cuenta? <a href="login.html">Iniciar sesión</a>
          </div>
      </div>
    </div>
    </form>
  </section>

  <script type="module" src="../js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>