<?php
/**
 * Persistencia de pedido: creación con ?crear o actualización con id_pedido en POST.
 */
include "./pedidoModel.php";
if (isset($_POST['id_pedido'])){
    $id = $_POST['id_pedido'];
}
$usuario_id = $_POST['usuario_id'];
$estado_id = $_POST['estado_id'];
$metodo_pago_id = $_POST['metodo_pago_id'] ?? null;
$subtotal = $_POST['subtotal'];
$descuento = $_POST['descuento'] ?? 0;
$total = $_POST['total'];
$codigo_promo = $_POST['codigo_promo'] ?? null;
$envio_nombre = $_POST['envio_nombre'];
$envio_direccion = $_POST['envio_direccion'];
$envio_ciudad = $_POST['envio_ciudad'];
$envio_pais = $_POST['envio_pais'] ?? 'Colombia';
$notas = $_POST['notas'] ?? null;

$success = false;
if (isset($_GET['crear'])){
    // Inserción desde crear_pedido.php
    $crear_datos = crear_pedido($usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas);
    $success = $crear_datos;
} elseif (isset($id)) {
    // Modificación de pedido existente
    $actualizar_datos = actualizar_pedido($id, $usuario_id, $estado_id, $metodo_pago_id, $subtotal, $descuento, $total, $codigo_promo, $envio_nombre, $envio_direccion, $envio_ciudad, $envio_pais, $notas);
    $success = $actualizar_datos;
} else {
    echo "Error: ID de pedido requerido para actualización.";
}

if ($success) {
    header('Location: pedidos.php');
    exit;
} else {
    echo "Error al procesar el pedido";
}
?>

