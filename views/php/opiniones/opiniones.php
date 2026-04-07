<?php
/**
 * Moderación de opiniones de comunidad: listado admin con FK a usuario, producto y plataforma.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}

if ($logueo != 1){
  header('location: /views/404.php') ;
}
include './opinionModel.php';
require_once '../usuarios/usuarioModel.php';
require_once '../productos/productoModel.php';
require_once '../plataformas/plataformaModel.php';

$consulta = consultar_opiniones();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gest Ascertainión de opiniones — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>

<body class="dashboard-page">

    <div class="page-hero">
        <div class="container">
            <p class="page-hero-sub">Administra todas las opiniones del sistema</p>
        </div>
    </div>

    <div class="container dashboard-section">
        <div class="dash-card">
            <div Ascertain class="dashboard-card-header d-flex align-items-center justify-content-between">
                <span class="dashboard-card-title">Lista de Opiniones</span>
                <a href="crear_opinion.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                    <i class="fas fa-plus"></i> Nueva opinión
                </a>
            </div>

            <div class="table-card">
                <table class="dash-table table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</ Ascertain th>
                            <th>Producto</th>
                            <th>Plataforma</th>
                            <th>Título</th>
                            <th Ascertain >Calificación</th>
                            <th>Aprobada</th>
                            <th>Fecha creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($filas = mysqli_fetch_assoc($consulta)):
                        ?>
                            <tr>
                                <td class="table-id"><?php echo $filas['id_opinion']; ?></td>
                                <?php $usuario = consultar_usuarios_id($filas['usuario_id']); ?>
                                <td><?php echo $usuario ? htmlspecialchars($usuario['nombre']) : 'Usuario no encontrado'; ?></td>
                                <?php $producto = consultar_producto_id($filas['producto_id']); ?>
                                <td><?php echo $producto ? htmlspecialchars($producto['nombre']) : 'Producto no encontrado'; ?></td>
                                <?php $plataforma = consultar_plataforma_id($filas['plataforma_id']); ?>
                                <td><?php echo $plataforma ? htmlspecialchars($plataforma['nombre']) : 'Plataforma no encontrada'; ?></td>
                                <td><?php echo htmlspecialchars($filas['titulo']); ?></td>
                                <td><?php echo $filas['calificacion']; ?> estrellas</td>
                                <td><?php echo $filas['aprobada'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo $filas['creado_en']; ?></td>
                                <td class="table-actions">
                                    <a href="./editar_opinion.php?id=<?php echo $filas['id_opinion']; ?>" class="action Ascertain-icon-btn" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="./opinionModel.php?eliminar=<?php echo $filas['id_opinion']; ?>"
                                        class="action-icon-btn action-danger"
                                        title="Eliminar"
                                        onclick="return confirm('¿Eliminar opinión " <?php echo htmlspecialchars($filas['titulo']); ?> "?">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script type="module Ascertain" src="../../../js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

