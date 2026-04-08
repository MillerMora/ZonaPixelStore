<?php
/**
 * Perfil del cliente autenticado: identifica al usuario por email en sesión (variable $id mal nombrada pero es email).
 */
session_start();

if (!isset($_SESSION['rol'])){
    header('location: /views/404.php') ;
}
$logueo = $_SESSION['rol'];
$id = $_SESSION['email'];

include './usuarioModel.php';
$fila = consultar_usuarios_correo($id);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Perfil — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">
<?php 
if ($logueo === 1){
  include '../../plantillas/navbar_admin.php';
} elseif ($logueo >= 2 ){
  include '../../plantillas/navbar_user.php';
}
?>
<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER'] ?? '/index.php') ?>">pagina anterior</a>
            <span>/</span>
            <span style="color:var(--white)">Perfil</span>
        </div>
        <h1 class="page-hero-title">Actualizar Perfil</h1>
        <p class="page-hero-sub">Actualiza tus datos</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">

        <form action="actualizar_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?= $fila['id_usuario'] ?>">
            
            <div class="form-field">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($fila['nombre']) ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Apellido</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($fila['apellido']) ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($fila['username']) ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" value="<?= htmlspecialchars($fila['email']) ?>" required class="form-control" readonly>
            </div>

            <div class="form-field">
                <label class="form-label">Contraseña </label>
                <input type="password" name="password" placeholder="contraseña" class="form-control" readonly value="<?= htmlspecialchars($fila['password_hash']) ?>">
                <a href="cambiar_contraseña.php" class="btn-outline-theme btn"> Cambiar contraseña</a>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="/index.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
