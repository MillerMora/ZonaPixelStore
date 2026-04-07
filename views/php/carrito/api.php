<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/carritoModel.php';
require_once __DIR__ . '/../conexion/conexion.php';

if (!isset($BD)) {
    $BD = connection();
}

function carrito_api_resumen()
{
    $items = carrito_items_actuales();
    $subtotal = 0.0;
    foreach ($items as $it) {
        $subtotal += (float) ($it['subtotal'] ?? 0);
    }
    $descuento = isset($_SESSION['carrito_descuento']) ? (float) $_SESSION['carrito_descuento'] : 0.0;
    $codigo = isset($_SESSION['carrito_codigo']) ? (string) $_SESSION['carrito_codigo'] : '';
    if ($descuento > $subtotal) {
        $descuento = $subtotal;
    }
    return [
        'items' => $items,
        'subtotal' => $subtotal,
        'descuento' => $descuento,
        'total' => $subtotal - $descuento,
        'cantidad' => carrito_contar_items_actuales(),
        'codigo' => $codigo,
    ];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $producto_id = (int) ($_POST['producto_id'] ?? 0);
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
    $edicion_id = isset($_POST['edicion_id']) && $_POST['edicion_id'] !== '' ? (int) $_POST['edicion_id'] : null;
    $ok = $producto_id > 0 ? carrito_agregar_item_actual($producto_id, $cantidad, $edicion_id) : false;
    echo json_encode(['ok' => $ok, 'resumen' => carrito_api_resumen()]);
    exit;
}

if ($action === 'update') {
    $id_item = $_POST['id_item'] ?? '';
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));
    $ok = $id_item !== '' ? carrito_actualizar_item_actual($id_item, $cantidad) : false;
    if (isset($_SESSION['carrito_codigo'])) {
        $r = carrito_api_resumen();
        $val = carrito_validar_codigo($_SESSION['carrito_codigo'], $r['subtotal']);
        if ($val['ok']) {
            $_SESSION['carrito_descuento'] = (float) $val['descuento'];
        } else {
            unset($_SESSION['carrito_codigo'], $_SESSION['carrito_descuento']);
        }
    }
    echo json_encode(['ok' => $ok, 'resumen' => carrito_api_resumen()]);
    exit;
}

if ($action === 'remove') {
    $id_item = $_POST['id_item'] ?? '';
    $ok = $id_item !== '' ? carrito_eliminar_item_actual($id_item) : false;
    if (isset($_SESSION['carrito_codigo'])) {
        $r = carrito_api_resumen();
        $val = carrito_validar_codigo($_SESSION['carrito_codigo'], $r['subtotal']);
        if ($val['ok']) {
            $_SESSION['carrito_descuento'] = (float) $val['descuento'];
        } else {
            unset($_SESSION['carrito_codigo'], $_SESSION['carrito_descuento']);
        }
    }
    echo json_encode(['ok' => $ok, 'resumen' => carrito_api_resumen()]);
    exit;
}

if ($action === 'apply_coupon') {
    $codigo = (string) ($_POST['codigo'] ?? '');
    $resumen = carrito_api_resumen();
    $val = carrito_validar_codigo($codigo, $resumen['subtotal']);
    if ($val['ok']) {
        $_SESSION['carrito_codigo'] = $val['codigo'];
        $_SESSION['carrito_descuento'] = (float) $val['descuento'];
    } else {
        unset($_SESSION['carrito_codigo'], $_SESSION['carrito_descuento']);
    }
    echo json_encode(['ok' => $val['ok'], 'mensaje' => $val['mensaje'], 'resumen' => carrito_api_resumen()]);
    exit;
}

if ($action === 'checkout') {
    $resumen = carrito_api_resumen();
    if ((int) $resumen['cantidad'] <= 0) {
        echo json_encode(['ok' => false, 'mensaje' => 'Debes agregar productos antes de pagar.']);
        exit;
    }
    carrito_vaciar_actual();
    unset($_SESSION['carrito_codigo'], $_SESSION['carrito_descuento']);
    echo json_encode(['ok' => true, 'mensaje' => 'Compra realizada con éxito.', 'resumen' => carrito_api_resumen()]);
    exit;
}

if ($action === 'get') {
    echo json_encode(['ok' => true, 'resumen' => carrito_api_resumen()]);
    exit;
}

if ($action === 'opiniones_producto') {
    $producto_id = (int) ($_GET['producto_id'] ?? 0);
    $sql = mysqli_prepare($BD, "
        SELECT
            o.id_opinion,
            o.titulo,
            o.contenido,
            o.calificacion,
            u.nombre,
            u.apellido,
            u.username,
            pl.nombre AS plataforma
        FROM opiniones o
        INNER JOIN usuarios u ON u.id_usuario = o.usuario_id
        LEFT JOIN plataformas pl ON pl.id_plataforma = o.plataforma_id
        WHERE o.producto_id = ? AND o.aprobada = 1
        ORDER BY o.creado_en DESC, o.id_opinion DESC
        LIMIT 12
    ");
    mysqli_stmt_bind_param($sql, 'i', $producto_id);
    mysqli_stmt_execute($sql);
    $res = mysqli_stmt_get_result($sql);
    $out = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $out[] = $row;
    }
    echo json_encode(['ok' => true, 'opiniones' => $out]);
    exit;
}

echo json_encode(['ok' => false, 'mensaje' => 'Acción inválida']);
?>
