<?php 
include './usuarioModel.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario — ZonaPixel Admin</title>
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
            <span style="color:var(--white)">Crear Usuario</span>
        </div>
        <h1 class="page-hero-title">Crear Usuario</h1>
        <p class="page-hero-sub">Agrega un nuevo usuario al sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Nuevo Usuario</span>
            <a href="./usuario.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Usuarios
            </a>
        </div>

        <form action="./editar_usuario.php?crear=1" method="POST">
            
            <div class="form-field">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Apellido</label>
                <input type="text" name="apellido" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Username</label>
                <input type="text" name="username" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Rol</label>
                <select name="rol_id" required class="form-select">
                    <option value="">Seleccionar rol</option>
                    <option value="1">Administrador</option>
                    <option value="2">Usuario</option>
                </select>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-plus"></i> Crear Usuario
                </button>
                <a href="./usuario.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

