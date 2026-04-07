<?php
/**
 * Alta de reseña editorial: elige producto y autor (staff) desde listados mysqli.
 */
session_start();
$logueo = null;
if (isset($_SESSION['rol'])){
  $logueo = $_SESSION['rol'];
}

if ($logueo != 1){
  header('location: /views/404.php') ;
}
include './resenasModel.php';
require_once '../productos/productoModel.php';
require_once '../usuarios/usuarioModel.php';
$productos = consultar_productos();
$usuarios = consultar_usuarios();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reseña — ZonaPixel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../css/style.css">
</head>
<body class="dashboard-page">

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="./resenas.php">Reseñas</a>
            <span>/</span>
            <span style="color:var(--white)">Crear Reseña</span>
        </div>
        <h1 class="page-hero-title">Crear Reseña</h1>
        <p class="page-hero-sub">Agrega una nueva reseña al sistema</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Nueva Reseña</span>
            <a href="./resenas.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Reseñas
            </a>
        </div>

<form action="actualizar_resena.php?crear=1" method="POST">
            
            <div class="form-field">
                <label class="form-label">URL Imagen Portada</label>
                <input type="url" name="imagen" required placeholder="https://example.com/imagen.jpg" title="Ingrese una URL válida de imagen" class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">ID Producto</label>
                <select name="id_producto" required class="form-select">
                    <option value="">Seleccionar producto</option>
                    <?php while ($producto = mysqli_fetch_assoc($productos)): ?>
                    <option value=<?= $producto['id_producto'];  ?> ><?= $producto['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Autor ID</label>
                <select name="autor" required class="form-select">
                    <option value="">Seleccionar Autor</option>
                    <?php while ($usuario = mysqli_fetch_assoc($usuarios)): ?>
                    <option value=<?= $usuario['id_usuario'];  ?> ><?= $usuario['nombre']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" required class="form-control">
            </div>

            <div class="form-field">


            <div class="form-field">
                <label class="form-label">Contenido</label>
                <textarea name="contenido" required rows="6" class="form-control"></textarea>
            </div>

            <div class="form-field">
                <label class="form-label">Calificación</label>
                <select name="calificacion" required class="form-select">
                    <option value="">Seleccionar calificación</option>
                    <option value="5">5 Estrellas</option>
                    <option value="4">4 Estrellas</option>
                    <option value="3">3 Estrellas</option>
                    <option value="2">2 Estrellas</option>
                    <option value="1">1 Estrella</option>
                </select>
            </div>

            <div class="form-field form-check mb-0">
                <input type="checkbox" name="publicada" value="1" checked class="form-check-input" id="publicada">
                <label class="form-check-label" for="publicada">Publicada</label>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-plus"></i> Crear Reseña
                </button>
                <a href="./resenas.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
