<?php 
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>

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

<div class="container" style="padding:40px 0 80px">
    <div class="dash-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px">
            <span style="font-family:var(--font-display); font-weight:800; font-size:1.1rem">Editar Usuario</span>
            <a href="./usuario.php" class="btn-primary" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Usuarios
            </a>
        </div>

        <form action="editar_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?= $fila['id_usuario'] ?>">
            
            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($fila['nombre']) ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Apellido</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($fila['apellido']) ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($fila['username']) ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Correo Electrónico</label>
                <input type="email" name="email" value="<?= htmlspecialchars($fila['email']) ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Nueva Contraseña (dejar vacía para no cambiar)</label>
                <input type="password" name="password" placeholder="Nueva contraseña" 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                <small style="color:var(--muted);">Dejar vacío para mantener la contraseña actual</small>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Rol</label>
                <select name="rol_id" required 
                        style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                    <option value="1" <?= ($fila['rol_id'] == 1) ? 'selected' : '' ?>>Administrador</option>
                    <option value="2" <?= ($fila['rol_id'] == 2) ? 'selected' : '' ?>>Usuario / Cliente</option>
                    <option value="3" <?= ($fila['rol_id'] == 3) ? 'selected' : '' ?>>Editor</option>
                </select>
            </div>

            <div style="display:flex; gap:12px; margin-top:28px">
                <button type="submit" class="btn-primary" style="padding:14px 28px; font-weight:700; font-size:14px;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./usuario.php" style="padding:14px 28px; border-radius:var(--radius); border:1px solid var(--border-light); background:transparent; color:var(--white); font-weight:500; font-size:14px; display:inline-flex; align-items:center; justify-content:center; transition:all var(--transition);">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

