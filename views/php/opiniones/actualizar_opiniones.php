<?php
/**
 * Persistencia de opinión: alta (?crear) o actualización por id_opinion; checkbox aprobada → 0/1.
 */
include "./opinionModel.php";
if (isset($_POST['id_opinion'])){
    $id = $_POST['id_opinion'];
}
$usuario_id = $_POST['usuario_id'];
$producto_id = $_POST['producto_id'];
$plataforma_id = !empty($_POST['plataforma_id']) ? (int)$_POST['plataforma_id'] : null;
$titulo = trim($_POST['titulo']);
$contenido = trim($_POST['contenido']);
$calificacion = (int)$_POST['calificacion'];

$aprobada = isset($_POST['aprobada']) ? 1 : 0;

// Debug log (remove in production)
error_log("Updating opinion: id=" . ($_POST['id_opinion'] ?? 'new') . ", plataforma_id=" . var_export($plataforma_id, true));

$success = false;
if (isset($_GET['crear'])){
    // Inserción desde crear_opinion.php
    $crear_datos = crear_opinion($usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada);
    $success = $crear_datos;
} elseif (isset($id)) {
    // Actualización de fila moderada
    $actualizar_datos = actualizar_opinion($id, $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada);
    $success = $actualizar_datos;
} else {
    echo "Error: ID de opinión requerido para actualización.";
}

if ($success) {
    header('Location: opiniones.php');
    exit;
} else {
    echo "Error al procesar la opinión";
}
?>

