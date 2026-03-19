<?php
include "./resenasModel.php";
if (isset($_POST['id_resena'])){

    $id = $_POST['id_resena'];
}
$imagen = $_POST['imagen'];
$id_producto = $_POST['id_producto'];
$autor = $_POST['autor'];
$titulo = $_POST['titulo'];
$resumen = $_POST['resumen'];
$contenido = $_POST['contenido'];
$calificacion = $_POST['calificacion'];
$publicada = $_POST['publicada'];
if (isset($_GET['crear'])){
    $crear_datos = crear_resena($imagen,$id_producto,$autor, $titulo,$resumen,$contenido,$calificacion,$publicada);
}elseif(isset($id)){
    $actualizar_datos = actualizar_resena($id,$imagen,$id_producto,$autor, $titulo,$resumen,$contenido,$calificacion,$publicada);
    
}

if ($actualizar_datos || $crear_datos) {
    header('Location: resenas.php');
} else {
    echo "Error al actualizar la reseña";
}
?>

