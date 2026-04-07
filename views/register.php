<?php
/**
 * Registro de nuevo cliente: formulario que envía a actualizar_usuario.php con ?crear=1.
 * Reutiliza el mismo banner de error de sesión que el login si existe login_error.
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrarse — ZonaPixel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
<?php // Aviso de validación o duplicidad propagado desde el procesador del formulario ?>
<?php if (isset($_SESSION['login_error'])): ?>
<div style="background: linear-gradient(90deg, #dc3545, #c82333); color: white; padding: 1rem 0; text-align: center; font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 10px rgba(220,53,69,0.3); margin-bottom: 2rem; border: none;">
  <div class="container">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php echo htmlspecialchars($_SESSION['login_error']); 
    unset($_SESSION['login_error']); ?>
  </div>
</div>
<?php endif; ?>

<?php
// Barra superior según rol autenticado (invitado usa navbar público)
if ($logueo === 1){
  include './plantillas/navbar_admin.php';
} elseif ($logueo >= 2 ){
  include './plantillas/navbar_user.php';
} else {
  include './plantillas/navbar_publico.php';

}
?>


  <!-- Formulario de alta: POST hacia actualizar_usuario con flag crear -->
  <section class="auth-page py-5">
    <div class="container">
      <div class="auth-card">
        <form action="./php/usuarios/actualizar_usuario.php?crear=1" method="post">

          <div class="auth-logo">
            <span class="logo-dot" style="width:10px;height:10px"></span>
            <span class="auth-logo-text">ZonaPixel</span>
          </div>
          <h1 class="auth-title">Crea tu cuenta</h1>
          <p class="auth-sub">Únete a la comunidad gamer más grande</p>

          <div class="row g-3">
            <div class="form-field col-md-6">
              <label>Nombre</label>
              <input type="text" class="form-input" name ="nombre"  placeholder="Juan" required />
            </div>
            <div class="form-field col-md-6">
              <label>Apellido</label>
              <input type="text" class="form-input" name="apellido" placeholder="Pérez" required/>
            </div>
          </div>
          <div class="form-field">
            <label>Correo electrónico</label>
            <div class="input-icon-wrap">
              <i class="fas fa-envelope"></i>
              <input type="email" class="form-input" name="email" placeholder="juan@correo.com" required/>
            </div>
          </div>
          <div class="form-field">
            <label>Nombre de usuario</label>
            <div class="input-icon-wrap">
              <i class="fas fa-at"></i>
              <input type="text" class="form-input" name="username" placeholder="@juangamer" required/>
            </div>
          </div>
          <div class="form-field">
            <label>Contraseña</label>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="password" class="form-input" name="password" placeholder="Mínimo 8 caracteres" required/>
            </div>
          </div>
          <div class="form-field">
            <label>Confirmar Contraseña</label>
            <div class="input-icon-wrap">
              <i class="fas fa-lock"></i>
              <input type="confirm_password" class="form-input" name="confirm_password" placeholder="Mínimo 8 caracteres" required />
            </div>
          </div>

          <div class="d-flex align-items-start gap-2 mb-3" style="font-size:12px; color:var(--muted)">
            <input type="checkbox" class="form-check-input mt-1" style="accent-color:var(--accent); flex-shrink:0" required />
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
<?php 
  include './plantillas/footer.php';
?>
  <script type="module" src="../js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>