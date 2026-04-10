<?php

/**
 * Cambio de contraseña mejorado en dos pasos:
 * 1. Verificar contraseña actual (compatibilidad DB legacy).
 * 2. Nueva contraseña con hashing seguro.
 * Incluye CSRF, validación básica, manejo de errores.
 */
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
  header('Location: /views/404.php');
  exit();
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include './usuarioModel.php';

// Paso 1: Verificar contraseña actual
$validation_passed = false;
if (isset($_POST['step']) && $_POST['step'] === '1') {
  if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token inválido.';
    header('Location: ./cambiar_contraseña.php');
    exit();
  }
  $current_password = $_POST['current_password'] ?? '';
  $user_data = consultar_usuarios_correo($_SESSION['email']);
  if ($user_data && $current_password === trim($user_data['password_hash'])) {  // Compatibilidad DB con contraseñas en texto plano
    $validation_passed = true;
    $_SESSION['pw_change_step'] = 2;  // Mantener estado del paso
  } else {
    $_SESSION['error'] = 'Contraseña actual incorrecta. Verifica que ingreses el hash exacto almacenado (legacy DB).';
    header('Location: ./cambiar_contraseña.php');
    exit();
  }
}

// Paso 2: Procesar nueva contraseña
if (isset($_POST['step']) && $_POST['step'] === '2' && (isset($_SESSION['pw_change_step']) && $_SESSION['pw_change_step'] == 2)) {
  if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Token inválido.';
    header('Location: ./cambiar_contraseña.php');
    exit();
  }
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Validación del lado servidor - simplificada
  if (strlen($new_password) < 8) {
    $_SESSION['error'] = 'Contraseña mínimo 8 caracteres.';
    header('Location: ./cambiar_contraseña.php');
    exit();
  }
  if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'Contraseñas no coinciden.';
    header('Location: ./cambiar_contraseña.php');
    exit();
  }

  $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
  $update_result = actualizar_contraseña($_SESSION['username'], $hashed_password);
  if ($update_result) {
    $_SESSION['success'] = '¡Contraseña cambiada exitosamente!';
    unset($_SESSION['csrf_token']);
    unset($_SESSION['pw_change_step']); // Reset step
    header('Location: ./cambiar_contraseña.php');
    exit();
  } else {
    unset($_SESSION['pw_change_step']); // Reiniciar paso en error
    $_SESSION['error'] = 'Error al actualizar. Intenta de nuevo.';
    header('Location: ./cambiar_contraseña.php');
    exit();
  }
}

$logueo = $_SESSION['rol'] ?? 0;

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
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show container mt-4" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']);
                                                      unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show container mt-4" role="alert">
      <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']);
                                              unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($logueo === 1) {
    include '../../plantillas/navbar_admin.php';
  } elseif ($logueo >= 2) {
    include '../../plantillas/navbar_user.php';
  } ?>

  <main class="container py-5 my-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card shadow-lg border-0">
          <div class="card-body p-5">
            <h2 class="text-center mb-4 fw-bold text-primary">
              <i class="fas fa-lock me-2"></i><?php echo $validation_passed ? 'Nueva Contraseña' : 'Verificar Actual'; ?>
            </h2>

            <?php if (!$validation_passed): // Step 1 
            ?>
              <form id="verifyForm" method="post" novalidate>
                <input type="hidden" name="step" value="1">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-4">
                  <label for="currentPw" class="form-label fw-semibold">Contraseña Actual</label>
                  <div class="input-group">
                    <input type="password" class="form-control form-control-lg border-primary shadow-sm" id="currentPw" name="current_password" placeholder="Ingresa tu contraseña actual" required minlength="8">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePw('currentPw')">
                      <i class="fas fa-eye" id="currentEye"></i>
                    </button>
                  </div>
                  <div class="form-text">Para continuar con el cambio.</div>
                  <div class="invalid-feedback d-block" id="currentPwFeedback"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
                  <i class="fas fa-arrow-right me-2"></i>Continuar
                </button>
              </form>
            <?php else: // Step 2 
            ?>
              <form id="changeForm" method="post" novalidate>
                <input type="hidden" name="step" value="2">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-4">
                  <label for="newPw" class="form-label fw-semibold">Nueva Contraseña</label>
                  <div class="input-group">
                    <input type="password" class="form-control form-control-lg border-primary shadow-sm" id="newPw" name="new_password" placeholder="Nueva contraseña segura" required minlength="8">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePw('newPw')">
                      <i class="fas fa-eye" id="newPwEye"></i>
                    </button>
                  </div>
                  <div class="form-text">Mín. 6 chars (cualquier texto).</div>
                  <div class="invalid-feedback d-block" id="newPwFeedback"></div>
                </div>
                <div class="mb-4">
                  <label for="confirmPw" class="form-label fw-semibold">Confirmar Nueva</label>
                  <div class="input-group">
                    <input type="password" class="form-control form-control-lg border-primary shadow-sm" id="confirmPw" name="confirm_password" placeholder="Confirma la nueva" required minlength="8">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePw('confirmPw')">
                      <i class="fas fa-eye" id="confirmEye"></i>
                    </button>
                  </div>
                  <div class="form-text">Debe coincidir exactamente.</div>
                  <div class="invalid-feedback d-block" id="confirmPwFeedback"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
                  <i class="fas fa-save me-2"></i>Cambiar Contraseña
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePw(fieldId) {
      const field = document.getElementById(fieldId);
      const eye = document.getElementById(fieldId + 'Eye');
      if (field.type === 'password') {
        field.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
      }
    }

    // Client-side validation
    ['verifyForm', 'changeForm'].forEach(formId => {
      const form = document.getElementById(formId);
      if (form) {
        form.addEventListener('submit', e => {
          let valid = true;
          form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value || input.value.length < 6) {
              input.classList.add('is-invalid');
              document.getElementById(input.id + 'Feedback').textContent = 'Mín 6 chars';
              valid = false;
            } else {
              input.classList.remove('is-invalid');
              document.getElementById(input.id + 'Feedback').textContent = '';
            }
          });
          if (formId === 'changeForm') {
            const newPw = document.getElementById('newPw').value;
            const confirmPw = document.getElementById('confirmPw').value;
            if (newPw !== confirmPw) {
              document.getElementById('confirmPwFeedback').textContent = 'No coinciden';
              valid = false;
            }
    // Validación relajada - cualquier texto si 6+ chars
    if (newPw.length < 6) {
      document.getElementById('newPwFeedback').textContent = 'Mín 6 chars';
      valid = false;
    }
  }
          if (!valid) e.preventDefault();
        });
      }
    });
  </script>
</body>

</html>