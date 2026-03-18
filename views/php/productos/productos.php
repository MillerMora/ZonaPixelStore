<?php 
include './productoModel.php';

$consulta = consultar_productos() ;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de productos — ZonaPixel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>

<div class="page-hero">
    <div class="container">
        <p class="page-hero-sub">Administra todos los productos del sistema</p>
    </div>
</div>

<div class="container" style="padding:40px 0 80px">
    <div class="dash-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px">
            <span style="font-family:var(--font-display); font-weight:800; font-size:1.1rem">Lista de Productos</span>
            <a href="crear_producto.php" class="btn-primary" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-plus"></i> Nuevo producto
            </a>
        </div>
        
        <div style="overflow-x:auto; border:1px solid var(--border); border-radius:var(--radius-lg)">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>imagen</th>
                        <th>Nombre producto</th>
                        <th>Descripcion corta</th>
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
                        <td style="color:var(--muted); font-family:monospace"><?= $filas['id_producto'] ?></td>
                        <td><img src="<?= $filas['imagen_principal'] ?>" alt="imagen"></td>
                        <td style="font-weight:600"><?= $filas['nombre'] ?></td>
                        <td style="color:var(--accent); font-weight:700"><?= $filas['descripcion_corta'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['descripcion'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['precio'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['stock'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?php if($filas['destacado'] == 1){ echo'Si';} else{echo'No';} ?></td>
                        <td style="color:var(--white); max-width:200px"><?php if($filas['activo'] == 1){ echo'Activo';} else{echo'No Activo';} ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['creado_en'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['actualizado_en'] ?></td>
                        <td style="white-space:nowrap">
                            <a href="./editar_producto.php?id=<?= $filas['id_producto'] ?>" 
                               style="width:36px;height:36px;border-radius:8px;border:1px solid var(--border);display:inline-flex;align-items:center;justify-content:center;margin-right:4px;color:var(--muted);transition:all var(--transition)" 
                               title="Editar">
                                <i class="fas fa-edit" style="font-size:13px"></i>
                            </a>
                            <a href="./productoModel.php?eliminar=<?=$filas['id_producto'] ?>" 
                               style="width:36px;height:36px;border-radius:8px;border:1px solid transparent;display:inline-flex;align-items:center;justify-content:center;color:var(--red);transition:all var(--transition)" 
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar a <?= htmlspecialchars($filas['nombre']) ?>?')">
                                <i class="fas fa-trash" style="font-size:13px"></i>
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