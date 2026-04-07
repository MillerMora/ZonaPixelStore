<?php
/**
 * Edición de usuario existente (admin): carga la fila por id en ?id= y envía a actualizar_usuario.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}

if ($logueo != 1){
  header('location: /views/404.php') ;
}
include './usuarioModel.php';
$id = $_GET['id'];

$fila = consultar_usuarios_id($id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Usuario — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="./usuario.php">Usuarios</a>
            <span>/</span>
            <span style="color:var(--white)">Actualizar Usuario</span>
        </div>
        <h1 class="page-hero-title">Actualizar Usuario</h1>
        <p class="page-hero-sub">Modifica los datos del usuario seleccionado</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Editar Usuario</span>
            <a href="./usuario.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Usuarios
            </a>
        </div>

        <form action="actualizar_usuario" method="POST">
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
                <input type="email" name="email" value="<?= htmlspecialchars($fila['email']) ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Nueva Contraseña (dejar vacía para no cambiar)</label>
                <input type="password" name="password" placeholder="Nueva contraseña" class="form-control">
                <small class="form-text">Dejar vacío para mantener la contraseña actual</small>
            </div>

            <div class="form-field">
                <label class="form-label">Rol</label>
                <select name="rol_id" required class="form-select">
                    <option value="1" <?= ($fila['rol_id'] == 1) ? 'selected' : '' ?>>Administrador</option>
                    <option value="2" <?= ($fila['rol_id'] == 2) ? 'selected' : '' ?>>Usuario / Cliente</option>
                    <option value="3" <?= ($fila['rol_id'] == 3) ? 'selected' : '' ?>>Editor</option>
                </select>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./usuario.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

