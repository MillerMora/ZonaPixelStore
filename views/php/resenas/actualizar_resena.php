<?php
include "./resenasModel.php";

if (isset($_POST['id_resena'])){
    $id_resena = $_POST['id_resena'];
}
$imagen = $_POST['imagen'];
$id_producto = $_POST['id_producto'];
$autor = $_POST['autor'];
$titulo = $_POST['titulo'];
$contenido = $_POST['contenido'];
$calificacion = $_POST['calificacion'];
$publicada = $_POST['publicada'] ?? '0';

$success = false;
if (isset($id_resena)) {
    $success = actualizar_resena($id_resena, $imagen, $id_producto, $autor, $titulo, $contenido, $calificacion, $publicada);
} else {
    $success = crear_resena($imagen, $id_producto, $autor, $titulo, $contenido, $calificacion, $publicada);
}

if ($success) {
    header('Location: resenas.php');
} else {
    echo "Error Verifica los datos.";
}
?>
