<?php

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
//consultas

function consultar_plataformas (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM plataformas') ;
    return $sql ;
    }
    
function consultar_plataforma_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM plataformas WHERE id_plataforma = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// CRUD 
function crear_plataforma ($nombre, $icono){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `plataformas`(`nombre`, `icono`) VALUES (?,?)");  
    mysqli_stmt_bind_param($sql, 'ss', $nombre, $icono);
    return mysqli_stmt_execute($sql);
}

function actualizar_plataforma ($id, $nombre, $icono){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `plataformas` SET `nombre` = ?, `icono` = ? WHERE `id_plataforma` = ?");
    mysqli_stmt_bind_param($sql, 'ssi', $nombre, $icono, $id);
    return mysqli_stmt_execute($sql);
}

function eliminar_plataforma ($id){
    global $BD;
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM plataformas WHERE id_plataforma = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

?>

