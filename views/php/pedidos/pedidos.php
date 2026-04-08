<?php

/**
 * Listado de pedidos en panel admin; modelos de usuario, estado y método cargados para etiquetas en tabla.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])) {
    $logueo = $_SESSION['rol'];
}

if ($logueo != 1) {
    header('location: /views/404.php');
}
include './pedidoModel.php';
require_once '../usuarios/usuarioModel.php';
require_once '../estados_pedido/estadoPedidoModel.php';
require_once '../metodos_pago/metodoPagoModel.php';

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($busqueda !== '') {
    $consulta = buscar_pedidos($busqueda);
} else {
    $consulta = consultar_pedidos();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body class="dashboard-page">

    <div class="page-hero">
        <div class="container">
            <h1 class="page-hero-title">Gestión de Pedidos</h1>
            <p class="page-hero-sub">Administra todos los pedidos del sistema</p>
        </div>
    </div>

    <div class="container dashboard-section">
        <div class="dash-card">
            <div class="dashboard-card-header d-flex align-items-center justify-content-between">
                <span class="dashboard-card-title">Lista de Pedidos</span>
                <form method="GET" style="display: contents;">
                    <div class="dash-search-wrap position-relative">
                        <input type="search" name="q" class="dash-search" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar pedidos..." autocomplete="off" />
                        <button type="submit" class="position-absolute top-50 end-0 translate-middle-y btn-unstyled p-0" style="border:none;background:none;line-height:1;color:inherit;" title="Buscar">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
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
                                <?php $usuario = consultar_usuarios_id($filas['usuario_id']); ?>
                                <td><?= $usuario ? htmlspecialchars($usuario['nombre']) : 'Usuario no encontrado' ?></td>
                                <?php $estado = consultar_estado_pedido_id($filas['estado_id']); ?>
                                <td><?= $estado ? htmlspecialchars($estado['nombre']) : 'Estado no encontrado' ?></td>
                                <?php $metodo = consultar_metodo_pago_id($filas['metodo_pago_id']); ?>
                                <td><?= $metodo ? htmlspecialchars($metodo['nombre']) : 'Método no encontrado' ?></td>
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