<?php

require '../conexion/conexion.php';

//consultas

function mostrar_productos (){
    
    $BD = connection();
    $sql = mysqli_query($BD ,'SELECT * FROM productos') ;
    return $sql ;
    }
    
function mostrar_producto_id ($id){
    $BD = connection();
    $sql = mysqli_prepare($BD,"SELECT * FROM productos WHERE = '$id'");
    mysqli_stmt_bind_param($sql, 'i', $id );
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;    
}


// CRUD


function crear_producto (){
    
    $BD = connection();
    $sql = mysqli_prepare($BD, "INSERT INTO `productos`(`categoria_id`, `marca_id`, `nombre`, `slug`, `descripcion_corta`, `descripcion`, `precio`, `precio_original`, `stock`, `imagen_principal`, `destacado`, `activo`, `creado_en`, `actualizado_en`) VALUES ('[value-2]','[value-3]','[value-4]','[value-5]','[value-6]','[value-7]','[value-8]','[value-9]','[value-10]','[value-11]','[value-12]','[value-13]','[value-14]','[value-15]')");  
}

function actualizar_producto ($id,$categoria, $marca, $nombre, $descripcion_corta, $descripcion,$precio,$stock,$imagen,$destacado, $activo){

    $BD = connection();

    $sql = mysqli_prepare($BD, "UPDATE `productos` SET `categoria_id`='$categoria',`marca_id`='$marca',`nombre`='$nombre',
                        `descripcion_corta`='$descripcion_corta',`descripcion`='$descripcion',`precio`='$precio',
                        `stock`='$stock',`imagen_principal`='$imagen',`destacado`='$destacado',`activo`='$activo' 
                        WHERE id_producto = $id");

    mysqli_stmt_bind_param($sql, 'sssssiisii', $categoria,$marca, $nombre,$descripcion_corta, $descripcion,$precio,$stock,$imagen,$destacado,$activo);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_producto ($id){
    $BD = connection();
    $sql = mysqli_prepare($BD, "DELETE FROM productos WHERE id_producto = $id");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado ;
}



?>