<?php 
include './productoModel.php';
$id = $_GET['id'];
$fila = consultar_producto_id($id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="./productos.php">Productos</a>
            <span>/</span>
            <span style="color:var(--white)">Editar Producto</span>
        </div>
        <h1 class="page-hero-title">Editar Producto</h1>
        <p class="page-hero-sub">Modifica el producto seleccionado</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Editar Producto</span>
            <a href="./productos.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>

        <form action="actualizar_productos.php" method="POST">
            <input type="hidden" name="id_producto" value="<?= $fila['id_producto'] ?>">
            
            <div class="form-field">
                <label class="form-label">Categoría ID</label>
                <select name="categoria" required class="form-select">
                    <option value="">Seleccionar categoría</option>
                    <option value="1">a</option>
                    <option value="2">b</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Marca ID</label>
                <select name="marca" required class="form-select">
                    <option value="">Seleccionar marca</option>
                    <option value="1">a</option>
                    <option value="2">b</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($fila['nombre'] ?? '') ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Descripción Corta</label>
                <textarea name="descripcion_corta" required rows="2" class="form-control"><?= htmlspecialchars($fila['descripcion_corta'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" required rows="5" class="form-control"><?= htmlspecialchars($fila['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label class="form-label">Precio</label>
                <input type="number" name="precio" step="0.01" min="0" value="<?= $fila['precio'] ?? '' ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" min="0" value="<?= $fila['stock'] ?? '' ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Nueva URL Imagen Principal (dejar vacía para mantener actual)</label>
                <input type="url" name="imagen" value="<?= htmlspecialchars($fila['imagen_principal'] ?? '') ?>" placeholder="https://example.com/imagen.jpg" title="URL de imagen válida (vacío mantiene actual)" class="form-control">
                <?php if($fila['imagen_principal']): ?>
                    <div class="mt-3">
                        <small class="form-text">Actual:</small><br>
                        <?= htmlspecialchars($fila['imagen_principal']) ?><br>
                        <img src="<?= htmlspecialchars($fila['imagen_principal']) ?>" alt="Vista previa actual" class="table-preview-img mt-2" style="max-width:200px; max-height:150px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-field form-check mb-0">
                <input type="checkbox" name="destacado" value="1" <?= ($fila['destacado']) == 1 ? 'checked' : '' ?> class="form-check-input" id="destacado">
                <label class="form-check-label" for="destacado">Destacado</label>
            </div>

            <div class="form-field form-check mb-0">
                <input type="checkbox" name="activo" value="1" <?= ($fila['activo']) == 1 ? 'checked' : '' ?> class="form-check-input" id="activo">
                <label class="form-check-label" for="activo">Activo</label>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./productos.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

