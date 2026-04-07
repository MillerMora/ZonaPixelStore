<?php

/**
 * Modelo de plataformas: CRUD administrativo y listados para la vitrina (videojuegos).
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}

// --- Lecturas ---

// Todas las filas de plataformas (resultado mysqli)
function consultar_plataformas (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM plataformas') ;
    return $sql ;
    }

// Una plataforma por id
function consultar_plataforma_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM plataformas WHERE id_plataforma = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// --- Altas, bajas y actualizaciones ---

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
    // Permite borrar aunque existan productos enlazados por FK (se restaura la comprobación después)
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM plataformas WHERE id_plataforma = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

// Plataformas que aparecen en al menos un producto activo de categoría «Videojuegos» (filtros públicos)
function plataformas_con_productos_videojuegos() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT pl.id_plataforma, pl.nombre, pl.icono
        FROM plataformas pl
        INNER JOIN producto_plataformas pp ON pp.plataforma_id = pl.id_plataforma
        INNER JOIN productos p ON p.id_producto = pp.producto_id AND p.activo = 1
        INNER JOIN categorias c ON p.categoria_id = c.id_categoria
            AND c.nombre = 'Videojuegos' AND c.activa = 1
        ORDER BY pl.nombre ASC
    ");
    $out = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $out[] = $row;
        }
    }
    return $out;
}

?>

