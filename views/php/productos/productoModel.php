<?php

require '../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
//consultas

function consultar_productos (){
    
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM productos') ;
    return $sql ;
    }
    
function consultar_producto_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// CRUD

function crear_producto ($categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo){
    
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `productos`(`categoria_id`, `marca_id`, `nombre`, `descripcion`, `precio`, `precio_original`, `stock`, `imagen_principal`, `destacado`, `activo`) VALUES (?,?,?,?,?,?,?,?,?,?)");  
    mysqli_stmt_bind_param($sql, 'iissddisii', $categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_producto ($id, $categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo){

    global $BD;

    $sql = mysqli_prepare($BD, "UPDATE `productos` SET `categoria_id` = ?, `marca_id` = ?, `nombre` = ?, `descripcion` = ?, `precio` = ?, `precio_original` = ?, `stock` = ?, `imagen_principal` = ?, `destacado` = ?, `activo` = ? WHERE `id_producto` = ?");

    mysqli_stmt_bind_param($sql, 'iissddisiii', $categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_producto ($id){
    global $BD;
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

if (isset($_GET["eliminar"])){
    eliminar_producto($_GET["eliminar"]);
    header("location: productos.php");
}

?>

