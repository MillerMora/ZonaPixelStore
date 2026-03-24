<?php 
include './pedidoModel.php';
require_once '../usuarios/usuarioModel.php';
require_once '../estados_pedido/estadoPedidoModel.php';
require_once '../metodos_pago/metodoPagoModel.php';

$usuarios = consultar_usuarios();
$estados = consultar_estados_pedido();
$metodos = consultar_metodos_pago();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Pedido — ZonaPixel Admin</title>
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
            <span style="color:var(--white)">Crear Pedido</span>
        </div>
        <h1 class="page-hero-title">Crear Pedido</h1>
        <p class="page-hero-sub">Agrega un nuevo pedido al sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Nuevo Pedido</span>
            <a href="./pedidos.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Pedidos
            </a>
        </div>

        <form action="actualizar_pedidos.php?crear=1" method="POST">
            
            <div class="form-field">
                <label class="form-label">Usuario ID</label>
                <select name="usuario_id" required class="form-select">
                    <option value="">Seleccionar usuario</option>
                    <?php while ($usuario = mysqli_fetch_assoc($usuarios)): ?>
                    <option value="<?= $usuario['id_usuario'] ?>"><?= htmlspecialchars($usuario['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Estado ID</label>
                <select name="estado_id" required class="form-select">
                    <option value="">Seleccionar estado</option>
                    <?php while ($estado  = mysqli_fetch_assoc($estados)): ?>
                    <option value="<?= $estado['id_estado_pedido'] ?>"><?= htmlspecialchars($estado['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Método Pago ID</label>
                <select name="metodo_pago_id" class="form-select">
                    <option value="">Seleccionar método</option>
                    <?php while ($metodo = mysqli_fetch_assoc($metodos)): ?>
                    <option value="<?= $metodo['id_metodo_pago'] ?>"><?= htmlspecialchars($metodo['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Subtotal</label>
                <input type="number" name="subtotal" step="0.01" min="0" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Descuento</label>
                <input type="number" name="descuento" step="0.01" min="0" value="0" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Total</label>
                <input type="number" name="total" step="0.01" min="0" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Código Promo</label>
                <input type="text" name="codigo_promo" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío Nombre</label>
                <input type="text" name="envio_nombre" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío Dirección</label>
                <input type="text" name="envio_direccion" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío Ciudad</label>
                <input type="text" name="envio_ciudad" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Envío País</label>
                <input type="text" name="envio_pais" value="Colombia" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Notas</label>
                <textarea name="notas" rows="3" class="form-control"></textarea>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-plus"></i> Crear Pedido
                </button>
                <a href="./pedidos.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

