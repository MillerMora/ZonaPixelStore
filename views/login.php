<?php

session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}

if (isset($_SESSION['login_error'])): ?>
<div style="background: linear-gradient(90deg, #dc3545, #c82333); color: white; padding: 1rem 0; text-align: center; font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 10px rgba(220,53,69,0.3); margin-bottom: 2rem; border: none;">
  <div class="container">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($_SESSION['login_error']); 
    unset($_SESSION['login_error']); ?>
  </div>
</div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión — ZonaPixel</title>
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

  <section class="auth-page py-5">
    <div class="container">
      <div class="auth-card">
        <form action="./php/usuarios/login/login.php?iniciar=1" method="post">

          <div class="auth-logo">
            <span class="logo-dot" style="width:10px;height:10px"></span>
            <span class="auth-logo-text">ZonaPixel</span>
          </div>
          <h1 class="auth-title">¡Bienvenido de nuevo!</h1>
          <p class="auth-sub">Ingresa a tu cuenta para continuar</p>

          <div class="form-field">
            <label>Usuario o correo</label>
            <div class="input-icon-wrap">
              <i class="fas fa-user"></i>
              <input type="text" class="form-input" name="login" placeholder="tu@correo.com" />
            </div>
          </div>
          <div class="form-field">
            <label>Contraseña</label>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" class="form-input" name="password" placeholder="Tu contraseña" />
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:13px">
            <label class="d-flex align-items-center gap-2" style="color:var(--muted); cursor:pointer">
              <input type="checkbox" checked class="form-check-input m-0" style="accent-color:var(--accent)" /> Recordarme
            </label>
            <a href="#" style="color:var(--accent)">¿Olvidaste tu contraseña?</a>
          </div>

          <button class="btn-auth-submit"><i class="fas fa-sign-in-alt"></i> &nbsp;Iniciar sesión</button>

          <div class="auth-divider">o continúa con</div>

          <button class="btn-social-auth">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" height="18" alt="Google" style="display:inline-block" />
            Continuar con Google
          </button>
          <button class="btn-social-auth">
            <i class="fab fa-facebook-f" style="color:#1877f2"></i> Continuar con Facebook
          </button>

          <div class="auth-switch">
            ¿No tienes cuenta? <a href="register.html">Regístrate gratis</a>
          </div>
      </div>
    </div>
    </form>
  </section>
<?php 
  include './plantillas/footer.php';
?>
  <script type="module" src="../js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>