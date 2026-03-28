<?php
require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection();
}

function listar_marcas() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM marcas ORDER BY nombre ASC');
    $marcas = [];
    while ($row = mysqli_fetch_assoc($sql)) {
        $marcas[] = $row;
    }
    return $marcas;
}

function consultar_marca_id($id) {
    global $BD;
    $sql = mysqli_prepare($BD, 'SELECT * FROM marcas WHERE id_marca = ?');
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    return mysqli_fetch_assoc($resultado);
}
?>

