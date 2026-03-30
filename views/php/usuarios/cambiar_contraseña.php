<?php
session_start();
if (!isset($_SESSION['username'])) {
  header('location: /views/404.php');
  exit();
}

$logueo = $_SESSION['rol'];

include './usuarioModel.php';

if (isset($_GET['validacion'])) {
  $contraseña_actual = $_POST['current_password'];
  $fila = consultar_usuarios_correo($_SESSION['email']);
  if ($contraseña_actual === $fila['password_hash']) {
    $validacion = $_GET['validacion'];
  } else {
    $_SESSION['password_error']  = 'Contraseña incorrecta';
    header('location: ./cambiar_contraseña.php');
    exit();
  }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar Contraseña</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../../../css/style.css">
</head>

<body>
  <?php if (isset($_SESSION['password_error'])): ?>
    <div style="background: linear-gradient(90deg, #dc3545, #c82333); color: white; padding: 1rem 0; text-align: center; font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 10px rgba(220,53,69,0.3); margin-bottom: 2rem; border: none;">
      <div class="container">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($_SESSION['password_error']);
        unset($_SESSION['password_error']); ?>
      </div>
    </div>
  <?php endif; ?>
  <?php
  if ($logueo === 1) {
    include '../../plantillas/navbar_admin.php';
  } elseif ($logueo >= 2) {
    include '../../plantillas/navbar_user.php';
  }
  ?>

  <?php if (!isset($validacion)): ?>
    <main class="container py-5 my-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
          <div class="card shadow-lg border-0">
            <div class="card-body p-5">
              <h2 class="text-center mb-4 fw-bold text-primary">
                <i class="fas fa-lock me-2"></i>Cambiar Contraseña
              </h2>
              <form id="changePasswordForm" action="./cambiar_contraseña.php?validacion=1" method="post" novalidate>
                <div class="mb-4">
                  <label for="currentPassword" class="form-label fw-semibold">Contraseña Actual</label>
                  <div class="input-group">
                    <input type="password"
                      class="form-control form-control-lg border-primary shadow-sm"
                      id="currentPassword"
                      name="current_password"
                      placeholder="Ingresa tu contraseña actual"
                      required
                      minlength="6">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                  <div class="form-text">Ingresa tu contraseña actual para continuar.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
                  <i class="fas fa-arrow-right me-2"></i>Continuar
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>
  <?php endif; ?>
  <?php if (isset($validacion)): ?>
    <main class="container py-5 my-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
          <div class="card shadow-lg border-0">
            <div class="card-body p-5">
              <h2 class="text-center mb-4 fw-bold text-primary">
                <i class="fas fa-lock me-2"></i>Cambiar Contraseña
              </h2>
              <form id="changePasswordForm" action="actualizar_usuario.php" method="post" novalidate>
                <div class="mb-4">
                  <label for="currentPassword" class="form-label fw-semibold">Nueva contraseña</label>
                  <div class="input-group">
                    <input type="password"
                      class="form-control form-control-lg border-primary shadow-sm"
                      id="currentPassword"
                      name="new_password"
                      placeholder="Ingresa tu nueva contraseña"
                      required
                      minlength="6">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                  <div class="form-text">Ingresa tu nueva contraseña (debe ser mayor de 8 digitos).</div>
                </div>
                <div class="mb-4">
                  <label for="currentPassword" class="form-label fw-semibold">Confirmar contraseña</label>
                  <div class="input-group">
                    <input type="password"
                      class="form-control form-control-lg border-primary shadow-sm"
                      id="currentPassword"
                      name="confirm_password"
                      placeholder="Ingresa otra vez tu nueva contraseña"
                      required
                      minlength="6">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                  <div class="form-text">Confirma tu contraseña</div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
                  <i class="fas fa-arrow-right me-2"></i>Continuar
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>
  <?php endif; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>