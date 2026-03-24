<?php

require_once '../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
//consultas

function consultar_metodos_pago (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM metodos_pago') ;
    return $sql ;
    }
    
function consultar_metodo_pago_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM metodos_pago WHERE id_metodo_pago = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// CRUD 
function crear_metodo_pago ($nombre){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `metodos_pago`(`nombre`) VALUES (?)");  
    mysqli_stmt_bind_param($sql, 's', $nombre);
    return mysqli_stmt_execute($sql);
}

function actualizar_metodo_pago ($id, $nombre){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `metodos_pago` SET `nombre` = ? WHERE `id_metodo_pago` = ?");
    mysqli_stmt_bind_param($sql, 'si', $nombre, $id);
    return mysqli_stmt_execute($sql);
}

function eliminar_metodo_pago ($id){
    global $BD;
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM metodos_pago WHERE id_metodo_pago = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

?>

