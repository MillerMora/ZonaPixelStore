<?php
/**
 * Modelo de categorías: lectura de categorías activas y detalle por identificador.
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection();
}

// Todas las categorías marcadas como activas, orden alfabético por nombre
function listar_categorias() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM categorias WHERE activa = 1 ORDER BY nombre ASC');
    $categorias = [];
    while ($row = mysqli_fetch_assoc($sql)) {
        $categorias[] = $row;
    }
    return $categorias;
}

// Obtiene una fila de categoría por clave primaria (preparada para evitar inyección SQL)
function consultar_categoria_id($id) {
    global $BD;
    $sql = mysqli_prepare($BD, 'SELECT * FROM categorias WHERE id_categoria = ?');
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    return mysqli_fetch_assoc($resultado);
}
?>

