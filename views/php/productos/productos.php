<?php
/**
 * ABM de productos (solo admin): tabla con enlace a edición y borrado vía modelo.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}

if ($logueo != 1){
  header('location: /views/404.php') ;
}
include './productoModel.php';

$consulta = consultar_productos() ;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de productos — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <p class="page-hero-sub">Administra todos los productos del sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Lista de Productos</span>
            <a href="crear_producto.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-plus"></i> Nuevo producto
            </a>
        </div>
        
        <div class="table-card">
            <table class="dash-table table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>imagen</th>
                        <th>Nombre producto</th>
                        <th>Descripcion</th>
                        <th>precio</th>
                        <th>stock</th>
                        <th>destacado</th>
                        <th>Estado</th>
                        <th>Fecha de creacion</th>
                        <th>ultima actualizacion</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($filas = mysqli_fetch_assoc($consulta)): ?>
                    <tr>
                        <td class="table-id"><?= $filas['id_producto'] ?></td>
                        <td><img src="<?= $filas['imagen_principal'] ?>" alt="imagen" class="table-preview-img"></td>
                        <td><strong><?= $filas['nombre'] ?></strong></td>
                        <td><?= $filas['descripcion'] ?></td>
                        <td><?= $filas['precio'] ?></td>
                        <td><?= $filas['stock'] ?></td>
                        <td><?php if($filas['destacado'] == 1){ echo'Si';} else{echo'No';} ?></td>
                        <td><?php if($filas['activo'] == 1){ echo'Activo';} else{echo'No Activo';} ?></td>
                        <td><?= $filas['creado_en'] ?></td>
                        <td><?= $filas['actualizado_en'] ?></td>
                        <td class="table-actions">
                            <a href="./editar_producto.php?id=<?= $filas['id_producto'] ?>" class="action-icon-btn" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="./productoModel.php?eliminar=<?=$filas['id_producto'] ?>" 
                               class="action-icon-btn action-danger"
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar a <?= htmlspecialchars($filas['nombre']) ?>?')">
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