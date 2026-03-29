<?php
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

$usuarios = consultar_usuarios();
$productos = consultar_productos();
$plataformas = consultar_plataformas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Opinión — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="./opiniones.php">Opiniones</a>
            <span>/</span>
            <span style="color:var(--white)">Crear Opinión</span>
        </div>
        <h1 class="page-hero-title">Crear Opinión</h1>
        <p class="page-hero-sub">Agrega una nueva opinión al sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Nueva Opinión</span>
            <a href="./opiniones.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Opiniones
            </a>
        </div>

        <form action="actualizar_opiniones.php?crear=1" method="POST">
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
                <label class="form-label">Producto ID</label>
                <select name="producto_id" required class="form-select">
                    <option value="">Seleccionar producto</option>
                    <?php while ($producto = mysqli_fetch_assoc($productos)): ?>
                    <option value="<?= $producto['id_producto'] ?>"><?= htmlspecialchars($producto['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Plataforma ID</label>
                <select name="plataforma_id" class="form-select">
                    <option value="">Seleccionar plataforma</option>
                    <?php while ($plataforma = mysqli_fetch_assoc($plataformas)): ?>
                    <option value="<?= $plataforma['id_plataforma'] ?>"><?= htmlspecialchars($plataforma['nombre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Contenido</label>
                <textarea name="contenido" required rows="5" class="form-control"></textarea>
            </div>

            <div class="form-field">
                <label class="form-label">Calificación (1-5)</label>
                <select name="calificacion" required class="form-select">
                    <option value="">Seleccionar</option>
                    <option value="1">1 estrella</option>
                    <option value="2">2 estrellas</option>
                    <option value="3">3 estrellas</option>
                    <option value="4">4 estrellas</option>
                    <option value="5">5 estrellas</option>
                </select>
            </div>

            <div class="form-field form-check mb-0">
                <input type="checkbox" name="aprobada" value="1" class="form-check-input" id="aprobada">
                <label class="form-check-label" for="aprobada">Aprobada</label>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-plus"></i> Crear Opinión
                </button>
                <a href="./opiniones.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

