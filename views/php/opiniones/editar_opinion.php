<?php 
include './opinionModel.php';
$id = $_GET['id'];
$fila = consultar_opinion_id($id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Opinión — ZonaPixel Admin</title>
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
            <span style="color:var(--white)">Editar Opinión</span>
        </div>
        <h1 class="page-hero-title">Editar Opinión</h1>
        <p class="page-hero-sub">Modifica la opinión seleccionada</p>
    </div>
</div>

<div class="container dashboard-section">
    <div class="dash-card">
        <div class="dashboard-card-header d-flex align-items-center justify-content-between">
            <span class="dashboard-card-title">Editar Opinión #<?= $fila['id_opinion'] ?></span>
            <a href="./opiniones.php" class="btn-primary btn btn-sm" style="font-size:13px; padding:8px 20px">
                <i class="fas fa-arrow-left"></i> Volver a Opiniones
            </a>
        </div>

        <form action="actualizar_opiniones.php" method="POST">
            <input type="hidden" name="id_opinion" value="<?= $fila['id_opinion'] ?>">
            
            <div class="form-field">
                <label class="form-label">Usuario ID</label>
                <select name="usuario_id" required class="form-select">
                    <option value="">Seleccionar usuario</option>
                    <option value="1" <?= ($fila['usuario_id'] == 1) ? 'selected' : '' ?>>Usuario 1</option>
                    <option value="2" <?= ($fila['usuario_id'] == 2) ? 'selected' : '' ?>>Usuario 2</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Producto ID</label>
                <select name="producto_id" required class="form-select">
                    <option value="">Seleccionar producto</option>
                    <option value="1" <?= ($fila['producto_id'] == 1) ? 'selected' : '' ?>>Producto 1</option>
                    <option value="2" <?= ($fila['producto_id'] == 2) ? 'selected' : '' ?>>Producto 2</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Plataforma ID</label>
                <select name="plataforma_id" class="form-select">
                    <option value="">Seleccionar plataforma</option>
                    <option value="1" <?= ($fila['plataforma_id'] == 1) ? 'selected' : '' ?>>PS5</option>
                    <option value="2" <?= ($fila['plataforma_id'] == 2) ? 'selected' : '' ?>>PC</option>
                </select>
            </div>

            <div class="form-field">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($fila['titulo'] ?? '') ?>" required class="form-control">
            </div>

            <div class="form-field">
                <label class="form-label">Contenido</label>
                <textarea name="contenido" required rows="5" class="form-control"><?= htmlspecialchars($fila['contenido'] ?? '') ?></textarea>
            </div>

            <div class="form-field">
                <label class="form-label">Calificación (1-5)</label>
                <select name="calificacion" required class="form-select">
                    <option value="">Seleccionar</option>
                    <option value="1" <?= ($fila['calificacion'] == 1) ? 'selected' : '' ?>>1 estrella</option>
                    <option value="2" <?= ($fila['calificacion'] == 2) ? 'selected' : '' ?>>2 estrellas</option>
                    <option value="3" <?= ($fila['calificacion'] == 3) ? 'selected' : '' ?>>3 estrellas</option>
                    <option value="4" <?= ($fila['calificacion'] == 4) ? 'selected' : '' ?>>4 estrellas</option>
                    <option value="5" <?= ($fila['calificacion'] == 5) ? 'selected' : '' ?>>5 estrellas</option>
                </select>
            </div>

            <div class="form-field form-check mb-0">
                <input type="checkbox" name="aprobada" value="1" <?= ($fila['aprobada'] == 1) ? 'checked' : '' ?> class="form-check-input" id="aprobada">
                <label class="form-check-label" for="aprobada">Aprobada</label>
            </div>

            <div class="d-flex gap-2 mt-4 flex-wrap">
                <button type="submit" class="btn-primary btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="./opiniones.php" class="btn-outline-theme btn">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

