<?php

require_once '../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
//consultas

function consultar_opiniones (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM opiniones') ;
    return $sql ;
    }
    
function consultar_opinion_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM opiniones WHERE id_opinion = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// CRUD

function crear_opinion ($usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `opiniones`(`usuario_id`, `producto_id`, `plataforma_id`, `titulo`, `contenido`, `calificacion`, `aprobada`) VALUES (?,?,?,?,?,?,?)" );  
    mysqli_stmt_bind_param($sql, 'iiisssi', $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_opinion ($id, $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `opiniones` SET `usuario_id` = ?, `producto_id` = ?, `plataforma_id` = ?, `titulo` = ?, `contenido` = ?, `calificacion` = ?, `aprobada` = ? WHERE `id_opinion` = ?");
    mysqli_stmt_bind_param($sql, 'iiisssii', $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_opinion ($id){
    global $BD;
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM opiniones WHERE id_opinion = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

if (isset($_GET["eliminar"])){
    eliminar_opinion($_GET["eliminar"]);
    header("location: opiniones.php");
}

?>

