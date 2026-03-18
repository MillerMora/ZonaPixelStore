<?php
include './resenasModel.php';
$consulta = consultar_resenas() ;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reseñas — ZonaPixel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Gestión de Reseñas</h1>
        <p class="page-hero-sub">Administra todas las reseñas del sistema</p>
    </div>
</div>

<div class="container" style="padding:40px 0 80px">
    <div class="dash-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px">
            <span style="font-family:var(--font-display); font-weight:800; font-size:1.1rem">Lista de Reseñas</span>
            <a href="crear_resena.php" class="btn-primary" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-plus"></i> Nueva Reseña
            </a>
        </div>
        
        <div style="overflow-x:auto; border:1px solid var(--border); border-radius:var(--radius-lg)">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>imagen</th>
                        <th>Producto</th>
                        <th>autor</th>
                        <th>titulo</th>
                        <th>resumen</th>
                        <th>contenido</th>
                        <th>calificaciones</th>
                        <th>publicada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($filas = mysqli_fetch_assoc($consulta)): ?>
                    <tr>
                        <td style="color:var(--muted); font-family:monospace"><?= $filas['id_resena'] ?></td>
                        <td style="font-weight:600"> <img src="<?= $filas['imagen_portada'] ?>" alt=""></td>
                        <td style="color:var(--accent); font-weight:700"><?= $filas['producto_id'] ?></td>
                        <td>
                            <span style="padding:4px 12px; background:var(--surface-2); border:1px solid var(--border); border-radius:20px; font-size:12px; font-weight:600">
                                <?= $filas['autor_id'] ?>
                            </span>
                        </td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['titulo'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['resumen'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['contenido'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['calificacion'] ?></td>
                        <td style="color:var(--white); max-width:200px"><?= $filas['publicada'] ?></td>
                        <td style="white-space:nowrap">
                            <a href="./editar_resena.php?id=<?= $filas['id_resena'] ?>" 
                               style="width:36px;height:36px;border-radius:8px;border:1px solid var(--border);display:inline-flex;align-items:center;justify-content:center;margin-right:4px;color:var(--muted);transition:all var(--transition)" 
                               title="Editar">
                                <i class="fas fa-edit" style="font-size:13px"></i>
                            </a>
                            <a href="./resenasModel.php?eliminar=<?= $filas['id_resena'] ?>" 
                               style="width:36px;height:36px;border-radius:8px;border:1px solid transparent;display:inline-flex;align-items:center;justify-content:center;color:var(--red);transition:all var(--transition)" 
                               title="Eliminar"
                               onclick="return confirm('¿Eliminar a <?= htmlspecialchars($filas['titulo']) ?>?')">
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


