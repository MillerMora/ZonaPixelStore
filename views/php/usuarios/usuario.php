<?php
/**
 * Listado administrativo de usuarios con rol resuelto; solo rol id 1.
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

$usuario = consultar_usuarios_rol();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Gestión de Usuarios</h1>
        <p class="page-hero-sub">Administra todos los usuarios del sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Lista de Usuarios</span>
            <a href="crear_usuario.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-plus"></i> Nuevo Usuario
            </a>
        </div>
        
        <div class="table-card">
            <table class="dash-table table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($filas = mysqli_fetch_assoc($usuario)): ?>
                    <tr>
                        <td class="table-id"><?= $filas['id_usuario'] ?></td>
                        <td><strong><?= $filas['nombre'] ?> <?= $filas['apellido'] ?></strong></td>
                        <td class="text-accent-strong"><?= $filas['username'] ?></td>
                        <td><?= $filas['email'] ?></td>
                        <td>
                            <span class="pill-muted">
                                <?= $filas['nombre_rol'] ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="./editar_usuario.php?id=<?= $filas['id_usuario'] ?>" class="action-icon-btn" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="./usuarioModel.php?eliminar=<?= $filas['id_usuario'] ?>" 
                               class="action-icon-btn action-danger"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar a <?= htmlspecialchars($filas['username']) ?>?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script type="module" src="../../../js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


