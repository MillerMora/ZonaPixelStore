<?php 
include './resenasModel.php';
$id = $_GET['id'] ?? 0;
$resultado = consultar_resenas_id($id);
$fila = mysqli_fetch_assoc($resultado) ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reseña — ZonaPixel Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="./resenas.php">Reseñas</a>
            <span>/</span>
            <span style="color:var(--white)">Editar Reseña</span>
        </div>
        <h1 class="page-hero-title">Editar Reseña</h1>
        <p class="page-hero-sub">Modifica la reseña seleccionada</p>
    </div>
</div>

<div class="container" style="padding:40px 0 80px">
    <div class="dash-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px">
            <span style="font-family:var(--font-display); font-weight:800; font-size:1.1rem">Editar Reseña</span>
            <a href="./resenas.php" class="btn-primary" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Reseñas
            </a>
        </div>

<form action="actualizar_resena.php" method="POST">
            <input type="hidden" name="id" value="<?= $fila['id_resena'] ?? '' ?>">
            
            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Nueva URL Imagen Portada (dejar vacía para mantener actual)</label>
                <input type="url" name="imagen" value="<?= htmlspecialchars($fila['imagen_portada'] ?? '') ?>" placeholder="https://example.com/imagen.jpg" title="URL de imagen válida (vacío mantiene actual)" style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                <?php if($fila['imagen_portada']): ?>
                    <div style="margin-top:12px;">
                        <small style="color:var(--muted);">Actual:</small><br>
                        <?= htmlspecialchars($fila['imagen_portada']) ?><br>
                        <img src="<?= htmlspecialchars($fila['imagen_portada']) ?>" alt="Vista previa actual" style="max-width:200px; max-height:150px; margin-top:8px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">ID Producto</label>
                <select name="id_producto" required 
                        style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                    <option value="">Seleccionar producto</option>
                    <option value="1" <?= ($fila['producto_id'] ?? '' ) == '1' ? 'selected' : '' ?>>Producto 1</option>
                    <option value="2" <?= ($fila['producto_id'] ?? '' ) == '2' ? 'selected' : '' ?>>Producto 2</option>
                </select>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Autor ID</label>
                <select name="autor" required 
                        style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                    <option value="">Seleccionar autor</option>
                    <option value="1" <?= ($fila['autor_id'] ?? '' ) == '1' ? 'selected' : '' ?>>Admin</option>
                    <option value="2" <?= ($fila['autor_id'] ?? '' ) == '2' ? 'selected' : '' ?>>Usuario</option>
                </select>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Título</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($fila['titulo'] ?? '') ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Resumen</label>
                <textarea name="resumen" required rows="3" style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition); resize:vertical;"><?= htmlspecialchars($fila['resumen'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Contenido</label>
                <textarea name="contenido" required rows="6" style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition); resize:vertical;"><?= htmlspecialchars($fila['contenido'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Calificación</label>
                <select name="calificacion" required 
                        style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                    <option value="">Seleccionar calificación</option>
                    <option value="5" <?= ($fila['calificacion'] ?? '') == '5' ? 'selected' : '' ?>>5 Estrellas</option>
                    <option value="4" <?= ($fila['calificacion'] ?? '') == '4' ? 'selected' : '' ?>>4 Estrellas</option>
                    <option value="3" <?= ($fila['calificacion'] ?? '') == '3' ? 'selected' : '' ?>>3 Estrellas</option>
                    <option value="2" <?= ($fila['calificacion'] ?? '') == '2' ? 'selected' : '' ?>>2 Estrellas</option>
                    <option value="1" <?= ($fila['calificacion'] ?? '') == '1' ? 'selected' : '' ?>>1 Estrella</option>
                </select>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">
                    <input type="checkbox" name="publicada" value="1" <?= ($fila['publicada'] ?? 0) == 1 ? 'checked' : '' ?> style="margin-right:8px; width:auto;"> Publicada
                </label>
            </div>

            <div style="display:flex; gap:12px; margin-top:28px">
                <button type="submit" class="btn-primary" style="padding:14px 28px; font-weight:700; font-size:14px;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./resenas.php" style="padding:14px 28px; border-radius:var(--radius); border:1px solid var(--border-light); background:transparent; color:var(--white); font-weight:500; font-size:14px; display:inline-flex; align-items:center; justify-content:center; transition:all var(--transition);">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
