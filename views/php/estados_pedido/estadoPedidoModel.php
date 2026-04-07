<?php

/**
 * Modelo de estados de pedido (flujo de la orden: pendiente, enviado, etc.).
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}

// --- Lecturas ---

// Catálogo completo de estados posibles del pedido
function consultar_estados_pedido (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM estados_pedido') ;
    return $sql ;
    }

// Estado puntual por id
function consultar_estado_pedido_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM estados_pedido WHERE id_estado_pedido = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// --- Altas, bajas y actualizaciones ---

function crear_estado_pedido ($nombre){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `estados_pedido`(`nombre`) VALUES (?)");  
    mysqli_stmt_bind_param($sql, 's', $nombre);
    return mysqli_stmt_execute($sql);
}

function actualizar_estado_pedido ($id, $nombre){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `estados_pedido` SET `nombre` = ? WHERE `id_estado_pedido` = ?");
    mysqli_stmt_bind_param($sql, 'si', $nombre, $id);
    return mysqli_stmt_execute($sql);
}

function eliminar_estado_pedido ($id){
    global $BD;
    // Relajar FK temporalmente si pedidos aún apuntan al estado
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM estados_pedido WHERE id_estado_pedido = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

?>

