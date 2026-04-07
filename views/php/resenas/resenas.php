<?php
/**
 * ABM de reseñas editoriales (admin): listado con acceso a crear/editar y borrado por modelo.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}

if ($logueo != 1){
  header('location: /views/404.php') ;
}
include './resenasModel.php';
require_once '../productos/productoModel.php';
require_once '../usuarios/usuarioModel.php';
$consulta = consultar_resenas() ;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reseñas — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Gestión de Reseñas</h1>
        <p class="page-hero-sub">Administra todas las reseñas del sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Lista de Reseñas</span>
            <div class="dash-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="dash-search" placeholder="Buscar..." />
                </div>
            <a href="crear_resena.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-plus"></i> Nueva Reseña
            </a>
        </div>
        
        <div class="table-card">
            <table class="dash-table table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>imagen</th>
                        <th>Producto</th>
                        <th>autor</th>
                        <th>titulo</th>
                        <th>contenido</th>
                        <th>calificaciones</th>
                        <th>publicada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($filas = mysqli_fetch_assoc($consulta)): ?>
                    <tr>
                        <td class="table-id"><?= $filas['id_resena'] ?></td>
                        <td><img src="<?= $filas['imagen_portada'] ?>" alt="" class="table-preview-img"></td>
                        <td class="text-accent-strong"><?php $producto = consultar_producto_id($filas['producto_id']); echo $producto ? htmlspecialchars($producto['nombre']) : 'Producto no encontrado'; ?></td>
                        <td>
                            <span class="pill-muted">
                                <?php $usuario = consultar_usuarios_id($filas['autor_id']); echo $usuario ? htmlspecialchars($usuario ['nombre']) : 'Autor no encontrado'; ?>
                            </span>
                        </td>
                        <td><?= $filas['titulo'] ?></td>
                        <td><?= $filas['contenido'] ?></td>
                        <td><?= $filas['calificacion'] ?></td>
                        <td><?= $filas['publicada'] ?></td>
                        <td class="table-actions">
                            <a href="./editar_resena.php?id=<?= $filas['id_resena'] ?>" class="action-icon-btn" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="./resenasModel.php?eliminar=<?= $filas['id_resena'] ?>" 
                               class="action-icon-btn action-danger"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar a <?= htmlspecialchars($filas['titulo']) ?>?')">
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


