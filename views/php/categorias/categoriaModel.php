<?php
require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection();
}

function listar_categorias() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM categorias WHERE activa = 1 ORDER BY nombre ASC');
    $categorias = [];
    while ($row = mysqli_fetch_assoc($sql)) {
        $categorias[] = $row;
    }
    return $categorias;
}

function consultar_categoria_id($id) {
    global $BD;
    $sql = mysqli_prepare($BD, 'SELECT * FROM categorias WHERE id_categoria = ?');
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    return mysqli_fetch_assoc($resultado);
}
?>

