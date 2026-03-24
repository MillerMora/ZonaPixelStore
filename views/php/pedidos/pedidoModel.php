<?php

require '../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
//consultas

function consultar_pedidos (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM pedidos') ;
    return $sql ;
    }
    
function consultar_pedido_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM pedidos WHERE id_pedido = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// CRUD

function crear_pedido ($usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `pedidos`(`usuario_id`, `estado_id`, `metodo_pago_id`, `subtotal`, `descuento`, `total`, `codigo_promo`, `envio_nombre`, `envio_direccion`, `envio_ciudad`, `envio_pais`, `notas`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)" );  
    mysqli_stmt_bind_param($sql, 'iiiddsssssss', $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_pedido ($id, $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `pedidos` SET `usuario_id` = ?, `estado_id` = ?, `metodo_pago_id` = ?, `subtotal` = ?, `descuento` = ?, `total` = ?, `codigo_promo` = ?, `envio_nombre` = ?, `envio_direccion` = ?, `envio_ciudad` = ?, `envio_pais` = ?, `notas` = ? WHERE `id_pedido` = ?");
    mysqli_stmt_bind_param($sql, 'iiiddsssssssi', $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_pedido ($id){
    global $BD;
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM pedidos WHERE id_pedido = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

if (isset($_GET["eliminar"])){
    eliminar_pedido($_GET["eliminar"]);
    header("location: pedidos.php");
}

?>

