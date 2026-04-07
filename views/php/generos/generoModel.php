<?php

/**
 * Modelo de géneros: consultas usadas en filtros del catálogo (solo géneros con stock publicable).
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)) {
    $BD = connection();
}

// Lista géneros distintos que tienen al menos un producto activo en categoría activa (para facetas del catálogo)
function catalogo_generos_con_productos_activos() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT g.id_genero, g.nombre
        FROM generos g
        INNER JOIN producto_generos pg ON pg.genero_id = g.id_genero
        INNER JOIN productos p ON p.id_producto = pg.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        ORDER BY g.nombre ASC
    ");
    $generos = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $generos[] = $row;
        }
    }
    return $generos;
}

?>
