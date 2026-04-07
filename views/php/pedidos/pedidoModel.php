<?php

/**
 * Modelo de pedidos: gestión de órdenes, totales para informes y enlace con ítems vía consultas externas.
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}

// --- Lecturas ---

// Listado completo de pedidos (mysqli result)
function consultar_pedidos (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM pedidos') ;
    return $sql ;
    }

// Detalle de un pedido por id
function consultar_pedido_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM pedidos WHERE id_pedido = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// --- Altas, bajas y actualizaciones ---

function crear_pedido ($usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `pedidos`(`usuario_id`, `estado_id`, `metodo_pago_id`, `subtotal`, `descuento`, `total`, `codigo_promo`, `envio_nombre`, `envio_direccion`, `envio_ciudad`, `envio_pais`, `notas`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)" );  
    mysqli_stmt_bind_param($sql, 'iiiddsssssss', $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_pedido ($id, $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `pedidos` SET `usuario_id` = ?, `estado_id` = ?, `metodo_pago_id` = ?, `subtotal` = ?, `descuento` = ?, `total` = ?, `codigo_promo` = ?, `envio_nombre` = ?, `envio_direccion` = ?, `envio_ciudad` = ?, `envio_pais` = ?, `notas` = ? WHERE `id_pedido` = ?");
    mysqli_stmt_bind_param($sql, 'iiiddsssssssi', $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_pedido ($id){
    global $BD;
    // Evita fallo por ítems u otras FK ligadas al pedido durante el borrado
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM pedidos WHERE id_pedido = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

// --- Métricas e historial reciente (dashboard) ---

function total_pedidos() {
    global $BD;
    // Conteo global de pedidos almacenados
    $sql = mysqli_query($BD, 'SELECT COUNT(*) as total FROM pedidos');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

// Pedidos creados en el mes calendario vigente
function pedidos_mes() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) as total FROM pedidos WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE())');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

// Suma de importes totales de pedidos del mes actual (ingreso bruto almacenado en cada pedido)
function ingresos_mes() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COALESCE(SUM(total), 0) as total FROM pedidos WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE())');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

// Suma histórica de campo total sobre todos los pedidos
function ingresos_totales() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COALESCE(SUM(total), 0) as total FROM pedidos');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

// Últimos pedidos por fecha de creación (tablero)
function pedidos_recientes($limit = 5) {
    global $BD;
    $sql = mysqli_query($BD, "SELECT * FROM pedidos ORDER BY creado_en DESC LIMIT $limit");
    return $sql;
}

// Borrado vía querystring en el listado administrativo de pedidos
if (isset($_GET["eliminar"])){
    eliminar_pedido($_GET["eliminar"]);
    header("location: pedidos.php");
}
?>


