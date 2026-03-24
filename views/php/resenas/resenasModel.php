<?php

require '../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
// consulta
function consultar_resenas(){

    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM resenas');
    return $sql;
    }
    
function consultar_resenas_id($id){
    global $BD;
    $sql = mysqli_prepare($BD, "SELECT * FROM resenas WHERE id_resena = ?");
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    return $resultado; 
}

// CRUD
function crear_resena($imagen,$id_producto,$autor, $titulo,$contenido,$calificacion,$publicada){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `resenas`(`producto_id`, `autor_id`, `titulo`, `contenido`, `calificacion`, `imagen_portada`, `publicada`) VALUES (?,?,?,?,?,?,?)");
    if (!$sql) return false;
    mysqli_stmt_bind_param($sql, "iissdsi", $id_producto, $autor, $titulo, $contenido, $calificacion, $imagen, $publicada);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}
function actualizar_resena($id,$imagen,$id_producto,$autor, $titulo,$contenido,$calificacion,$publicada){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `resenas` SET `producto_id`=?,`autor_id`=?,`titulo`=?,`contenido`=?,`calificacion`=?,`imagen_portada`=?,`publicada`=? WHERE id_resena = ?");
    if (!$sql) return false;
mysqli_stmt_bind_param($sql, "iissdsii", $id_producto,$autor,$titulo,$contenido,$calificacion,$imagen,$publicada,$id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}
function eliminar_resena($id){
    global $BD;
    $sql = mysqli_prepare($BD, "DELETE FROM `resenas` WHERE id_resena =?");
    mysqli_stmt_bind_param($sql, "i", $id);
    return mysqli_stmt_execute($sql);
}

if (isset($_GET['eliminar'])){
    eliminar_resena($_GET['eliminar']);
    header("location: resenas.php");
}

?>
