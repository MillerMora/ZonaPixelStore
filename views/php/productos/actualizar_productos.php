<?php
include "./productoModel.php";
if (isset($_POST['id_producto'])){
    $id = $_POST['id_producto'];
}
$categoria = $_POST['categoria'];
$marca = $_POST['marca'];
$nombre = $_POST['nombre'];
$descripcion_corta = $_POST['descripcion_corta'];
$descripcion = $_POST['descripcion'];
$precio = $_POST['precio'];
$precio_original = null;
$stock = $_POST['stock'];
$imagen = $_POST['imagen'];
$destacado = $_POST['destacado'] ?? 0;
$activo = $_POST['activo'] ?? 1;

$success = false;
if (isset($_GET['crear'])){
    $crear_datos = crear_producto($categoria, $marca, $nombre, $descripcion_corta, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo);
    $success = $crear_datos;
} elseif (isset($id)) {
    $actualizar_datos = actualizar_producto($id, $categoria, $marca, $nombre, $descripcion_corta, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo);
    $success = $actualizar_datos;
} else {
    echo "Error: ID de producto requerido para actualización.";
}

if ($success) {
    header('Location: productos.php');
    exit;
} else {
    echo "Error al procesar el producto";
}
?>
