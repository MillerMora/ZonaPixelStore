<?php
include './pedidoModel.php';

$consulta = consultar_pedidos();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de pedidos — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body class="dashboard-page">

    <div class="page-hero">
        <div class="container">
            <p class="page-hero-sub">Administra todos los pedidos del sistema</p>
        </div>
    </div>

    <div class="container dashboard-section">
        <div class="dash-card">
            <div class="dashboard-card-header d-flex align-items-center justify-content-between">
                <span class="dashboard-card-title">Lista de Pedidos</span>
                <a href="crear_pedido.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                    <i class="fas fa-plus"></i> Nuevo pedido
                </a>
            </div>

            <div class="table-card">
                <table class="dash-table table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Método Pago</th>
                            <th>Total</th>
                            <th>Fecha creación</th>
                            <th>Última actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($filas = mysqli_fetch_assoc($consulta)): ?>
                            <tr>
                                <td class="table-id"><?= $filas['id_pedido'] ?></td>
                                <td><?= $filas['usuario_id'] ?></td>
                                <td><?= $filas['estado_id'] ?></td>
                                <td><?= $filas['metodo_pago_id'] ?></td>
                                <td><?= $filas['total'] ?></td>
                                <td><?= $filas['creado_en'] ?></td>
                                <td><?= $filas['actualizado_en'] ?></td>
                                <td class="table-actions">
                                    <a href="./editar_pedido.php?id=<?= $filas['id_pedido'] ?>" class="action-icon-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="./pedidoModel.php?eliminar=<?= $filas['id_pedido'] ?>"
                                        class="action-icon-btn action-danger"
                                        title="Eliminar"
                                        onclick="return confirm('¿Eliminar pedido #<?= htmlspecialchars($filas['id_pedido']) ?>?')">
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
</body>

</html>