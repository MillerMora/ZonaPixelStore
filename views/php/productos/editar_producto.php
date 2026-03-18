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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body>

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

<div class="container" style="padding:40px 0 80px">
    <div class="dash-card">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px">
            <span style="font-family:var(--font-display); font-weight:800; font-size:1.1rem">Editar Producto</span>
            <a href="./productos.php" class="btn-primary" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>

        <form action="actualizar_productos.php" method="POST">
            <input type="hidden" name="id_producto" value="<?= $fila['id_producto'] ?>">
            
            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Categoría ID</label>
                <select name="categoria" required 
                        style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                    <option value="">Seleccionar categoría</option>
                    <option value="1" >a</option>
                    <option value="2" >b</option>
                </select>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Marca ID</label>
                <select name="marca" required 
                        style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                    <option value="">Seleccionar marca</option>
                    <option value="1" >a</option>
                    <option value="2" >b</option>
                </select>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($fila['nombre'] ?? '') ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Descripción Corta</label>
                <textarea name="descripcion_corta" required rows="2" style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition); resize:vertical;"><?= htmlspecialchars($fila['descripcion_corta'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Descripción</label>
                <textarea name="descripcion" required rows="5" style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition); resize:vertical;"><?= htmlspecialchars($fila['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Precio</label>
                <input type="number" name="precio" step="0.01" min="0" value="<?= $fila['precio'] ?? '' ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Stock</label>
                <input type="number" name="stock" min="0" value="<?= $fila['stock'] ?? '' ?>" required 
                       style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Nueva URL Imagen Principal (dejar vacía para mantener actual)</label>
                <input type="url" name="imagen" value="<?= htmlspecialchars($fila['imagen_principal'] ?? '') ?>" placeholder="https://example.com/imagen.jpg" title="URL de imagen válida (vacío mantiene actual)" style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition);">
                <?php if($fila['imagen_principal']): ?>
                    <div style="margin-top:12px;">
                        <small style="color:var(--muted);">Actual:</small><br>
                        <?= htmlspecialchars($fila['imagen_principal']) ?><br>
                        <img src="<?= htmlspecialchars($fila['imagen_principal']) ?>" alt="Vista previa actual" style="max-width:200px; max-height:150px; margin-top:8px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">
                    <input type="checkbox" name="destacado" value="1" <?= ($fila['destacado']) == 1 ? 'checked' : '' ?> style="margin-right:8px; width:auto;"> Destacado
                </label>
            </div>

            <div class="form-field">
                <label style="display:block; font-size:.85rem; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">
                    <input type="checkbox" name="activo" value="1" <?= ($fila['activo']) == 1 ? 'checked' : '' ?> style="margin-right:8px; width:auto;"> Activo
                </label>
            </div>

            <div style="display:flex; gap:12px; margin-top:28px">
                <button type="submit" class="btn-primary" style="padding:14px 28px; font-weight:700; font-size:14px;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./productos.php" style="padding:14px 28px; border-radius:var(--radius); border:1px solid var(--border-light); background:transparent; color:var(--white); font-weight:500; font-size:14px; display:inline-flex; align-items:center; justify-content:center; transition:all var(--transition);">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

Note: Textareas need style attribute. Fixed inline.

<style for textarea:
style="width:100%; padding:12px 16px; background:var(--surface-2); border:1px solid var(--border); color:var(--white); border-radius:10px; font-size:.95rem; transition:border-color var(--transition); resize:vertical;"

Yes, add to all textarea.

But since previous calls succeeded, for this last one.

Previous editar_resena had missing style on some textarea, but succeeded.

For consistency, include.

Also autor_id vs autor: use 'autor_id' in form? Model param $autor, but DB autor_id, form name="autor_id"

Model bind autor_id column.

Use name="autor_id"

In create_resena param $autor, but likely $_POST['autor_id'] or adjust.

For now, use model param names: for resenas autor (not autor_id)

From model: $autor - name="autor"

For product categoria (categoria_id DB)

Ok.

File complete.

