<?php 
include './pedidoModel.php';
$id = $_GET['id'];
$fila = consultar_pedido_id($id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="./pedidos.php">Pedidos</a>
            <span>/</span>
            <span style="color:var(--white)">Editar Pedido</span>
        </div>
        <h1 class="page-hero-title">Editar Pedido</h1>
        <p class="page-hero-sub">Modifica el pedido seleccionado</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Editar Pedido #<?= $fila['id_pedido'] ?></span>
            <a href="./pedidos.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Pedidos
            </a>
        </div>

        <form action="actualizar_pedidos.php" method="POST">
            <input type="hidden" name="id_pedido" value="<?= $fila['id_pedido'] ?>">
            
            <div class="form-field">
                <label class="form-label">Usuario ID</label>
                <select name="usuario_id" required class="form-select">
                    <option value="">Seleccionar usuario</option>
                    <option value="1" <?= $fila['usuario_id'] == 1 ? 'selected' : '' ?>>Admin (1)</option>
                    <option value="2" <?= $fila['usuario_id'] == 2 ? 'selected' : '' ?>>Cliente (2)</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Estado ID</label>
                <select name="estado_id" required class="form-select">
                    <option value="">Seleccionar estado</option>
                    <option value="1" <?= $fila['estado_id'] == 1 ? 'selected' : '' ?>>Pendiente</option>
                    <option value="2" <?= $fila['estado_id'] == 2 ? 'selected' : '' ?>>Procesando</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Método Pago ID</label>
                <select name="metodo_pago_id" class="form-select">
                    <option value="">Seleccionar método</option>
                    <option value="1" <?= $fila['metodo_pago_id'] == 1 ? 'selected' : '' ?>>Visa</option>
                    <option value="2" <?= $fila['metodo_pago_id'] == 2 ? 'selected' : '' ?>>Mastercard</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Subtotal</label>
                <input type="number" name="subtotal" step="0.01" min="0" value="<?= $fila['subtotal'] ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Descuento</label>
                <input type="number" name="descuento" step="0.01" min="0" value="<?= $fila['descuento'] ?>" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Total</label>
                <input type="number" name="total" step="0.01" min="0" value="<?= $fila['total'] ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Código Promo</label>
                <input type="text" name="codigo_promo" value="<?= htmlspecialchars($fila['codigo_promo'] ?? '') ?>" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío Nombre</label>
                <input type="text" name="envio_nombre" value="<?= htmlspecialchars($fila['envio_nombre'] ?? '') ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío Dirección</label>
                <input type="text" name="envio_direccion" value="<?= htmlspecialchars($fila['envio_direccion'] ?? '') ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío Ciudad</label>
                <input type="text" name="envio_ciudad" value="<?= htmlspecialchars($fila['envio_ciudad'] ?? '') ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío País</label>
                <input type="text" name="envio_pais" value="<?= htmlspecialchars($fila['envio_pais'] ?? 'Colombia') ?>" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Notas</label>
                <textarea name="notas" rows="3" class="form-control"><?= htmlspecialchars($fila['notas'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./pedidos.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

