<?php
require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../usuarios/usuarioModel.php';

if (!isset($BD)) {
    $BD = connection();
}

function carrito_usuario_id_actual()
{
    if (isset($_SESSION['username']) && $_SESSION['username'] !== '') {
        $u = consultar_usuarios_nombreUsuario($_SESSION['username']);
        if ($u && isset($u['id_usuario'])) {
            return (int) $u['id_usuario'];
        }
    }
    return 0;
}

function carrito_guest_key($producto_id, $edicion_id = null)
{
    $pid = (int) $producto_id;
    $eid = $edicion_id !== null ? (int) $edicion_id : 0;
    return $pid . '_' . $eid;
}

function carrito_contar_items_actuales()
{
    global $BD;
    $uid = carrito_usuario_id_actual();
    if ($uid > 0) {
        $sql = mysqli_prepare($BD, "SELECT COALESCE(SUM(cantidad), 0) AS total FROM carrito_items WHERE usuario_id = ?");
        mysqli_stmt_bind_param($sql, 'i', $uid);
        mysqli_stmt_execute($sql);
        $res = mysqli_stmt_get_result($sql);
        $row = mysqli_fetch_assoc($res);
        return (int) ($row['total'] ?? 0);
    }

    $total = 0;
    $items = $_SESSION['carrito_invitado'] ?? [];
    foreach ($items as $it) {
        $total += (int) ($it['cantidad'] ?? 0);
    }
    return $total;
}

function carrito_items_actuales()
{
    global $BD;
    $uid = carrito_usuario_id_actual();
    $items = [];

    if ($uid > 0) {
        $sql = mysqli_prepare($BD, "
            SELECT
                ci.id_carrito_item,
                ci.producto_id,
                ci.edicion_id,
                ci.cantidad,
                p.nombre AS producto_nombre,
                p.imagen_principal,
                p.precio AS precio_base,
                pe.nombre AS edicion_nombre,
                pe.precio AS precio_edicion
            FROM carrito_items ci
            INNER JOIN productos p ON p.id_producto = ci.producto_id
            LEFT JOIN producto_ediciones pe ON pe.id_producto_edicion = ci.edicion_id
            WHERE ci.usuario_id = ?
            ORDER BY ci.agregado_en DESC, ci.id_carrito_item DESC
        ");
        mysqli_stmt_bind_param($sql, 'i', $uid);
        mysqli_stmt_execute($sql);
        $res = mysqli_stmt_get_result($sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $precio = $row['precio_edicion'] !== null ? (float) $row['precio_edicion'] : (float) $row['precio_base'];
            $cant = max(1, (int) $row['cantidad']);
            $items[] = [
                'id_item' => (int) $row['id_carrito_item'],
                'producto_id' => (int) $row['producto_id'],
                'edicion_id' => $row['edicion_id'] !== null ? (int) $row['edicion_id'] : null,
                'nombre' => (string) ($row['producto_nombre'] ?? ''),
                'opcion' => (string) ($row['edicion_nombre'] ?? ''),
                'imagen' => (string) ($row['imagen_principal'] ?? ''),
                'precio' => $precio,
                'cantidad' => $cant,
                'subtotal' => $precio * $cant,
            ];
        }
        return $items;
    }

    $guest = $_SESSION['carrito_invitado'] ?? [];
    foreach ($guest as $key => $it) {
        $producto_id = (int) ($it['producto_id'] ?? 0);
        $edicion_id = isset($it['edicion_id']) && $it['edicion_id'] !== null ? (int) $it['edicion_id'] : null;
        $cantidad = max(1, (int) ($it['cantidad'] ?? 1));
        if ($producto_id < 1) {
            continue;
        }

        $sql = mysqli_prepare($BD, "
            SELECT
                p.id_producto,
                p.nombre AS producto_nombre,
                p.imagen_principal,
                p.precio AS precio_base,
                pe.nombre AS edicion_nombre,
                pe.precio AS precio_edicion
            FROM productos p
            LEFT JOIN producto_ediciones pe ON pe.id_producto_edicion = ?
            WHERE p.id_producto = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($sql, 'ii', $edicion_id, $producto_id);
        mysqli_stmt_execute($sql);
        $res = mysqli_stmt_get_result($sql);
        $row = mysqli_fetch_assoc($res);
        if (!$row) {
            continue;
        }
        $precio = $row['precio_edicion'] !== null ? (float) $row['precio_edicion'] : (float) $row['precio_base'];
        $items[] = [
            'id_item' => (string) $key,
            'producto_id' => $producto_id,
            'edicion_id' => $edicion_id,
            'nombre' => (string) ($row['producto_nombre'] ?? ''),
            'opcion' => (string) ($row['edicion_nombre'] ?? ''),
            'imagen' => (string) ($row['imagen_principal'] ?? ''),
            'precio' => $precio,
            'cantidad' => $cantidad,
            'subtotal' => $precio * $cantidad,
        ];
    }
    return $items;
}

function carrito_agregar_item_actual($producto_id, $cantidad, $edicion_id = null)
{
    global $BD;
    $uid = carrito_usuario_id_actual();
    $producto_id = (int) $producto_id;
    $cantidad = max(1, (int) $cantidad);
    $edicion_id = $edicion_id !== null ? (int) $edicion_id : null;

    if ($uid > 0) {
        $sql = mysqli_prepare($BD, "
            INSERT INTO carrito_items (usuario_id, producto_id, edicion_id, cantidad)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)
        ");
        mysqli_stmt_bind_param($sql, 'iiii', $uid, $producto_id, $edicion_id, $cantidad);
        return mysqli_stmt_execute($sql);
    }

    if (!isset($_SESSION['carrito_invitado']) || !is_array($_SESSION['carrito_invitado'])) {
        $_SESSION['carrito_invitado'] = [];
    }
    $key = carrito_guest_key($producto_id, $edicion_id);
    if (!isset($_SESSION['carrito_invitado'][$key])) {
        $_SESSION['carrito_invitado'][$key] = [
            'producto_id' => $producto_id,
            'edicion_id' => $edicion_id,
            'cantidad' => 0,
        ];
    }
    $_SESSION['carrito_invitado'][$key]['cantidad'] += $cantidad;
    return true;
}

function carrito_actualizar_item_actual($id_item, $cantidad)
{
    global $BD;
    $uid = carrito_usuario_id_actual();
    $cantidad = max(1, (int) $cantidad);
    if ($uid > 0) {
        $id = (int) $id_item;
        $sql = mysqli_prepare($BD, "UPDATE carrito_items SET cantidad = ? WHERE id_carrito_item = ? AND usuario_id = ?");
        mysqli_stmt_bind_param($sql, 'iii', $cantidad, $id, $uid);
        return mysqli_stmt_execute($sql);
    }

    $key = (string) $id_item;
    if (!isset($_SESSION['carrito_invitado'][$key])) {
        return false;
    }
    $_SESSION['carrito_invitado'][$key]['cantidad'] = $cantidad;
    return true;
}

function carrito_eliminar_item_actual($id_item)
{
    global $BD;
    $uid = carrito_usuario_id_actual();
    if ($uid > 0) {
        $id = (int) $id_item;
        $sql = mysqli_prepare($BD, "DELETE FROM carrito_items WHERE id_carrito_item = ? AND usuario_id = ?");
        mysqli_stmt_bind_param($sql, 'ii', $id, $uid);
        return mysqli_stmt_execute($sql);
    }

    $key = (string) $id_item;
    if (!isset($_SESSION['carrito_invitado'][$key])) {
        return false;
    }
    unset($_SESSION['carrito_invitado'][$key]);
    return true;
}

function carrito_vaciar_actual()
{
    global $BD;
    $uid = carrito_usuario_id_actual();
    if ($uid > 0) {
        $sql = mysqli_prepare($BD, "DELETE FROM carrito_items WHERE usuario_id = ?");
        mysqli_stmt_bind_param($sql, 'i', $uid);
        return mysqli_stmt_execute($sql);
    }
    $_SESSION['carrito_invitado'] = [];
    return true;
}

function carrito_validar_codigo($codigo, $subtotal)
{
    global $BD;
    $codigo = strtoupper(trim((string) $codigo));
    if ($codigo === '') {
        return ['ok' => false, 'mensaje' => 'Ingresa un código promocional.'];
    }

    $sql = mysqli_prepare($BD, "
        SELECT codigo, tipo, valor, minimo_compra, activo, usos_maximos, usos_actuales, fecha_inicio, fecha_fin
        FROM codigos_promo
        WHERE codigo = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($sql, 's', $codigo);
    mysqli_stmt_execute($sql);
    $res = mysqli_stmt_get_result($sql);
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        return ['ok' => false, 'mensaje' => 'El código no existe.'];
    }
    if ((int) $row['activo'] !== 1) {
        return ['ok' => false, 'mensaje' => 'El código no está activo.'];
    }
    $ahora = date('Y-m-d H:i:s');
    if (!empty($row['fecha_inicio']) && $ahora < $row['fecha_inicio']) {
        return ['ok' => false, 'mensaje' => 'El código aún no está disponible.'];
    }
    if (!empty($row['fecha_fin']) && $ahora > $row['fecha_fin']) {
        return ['ok' => false, 'mensaje' => 'El código ya expiró.'];
    }
    $usos_max = $row['usos_maximos'] !== null ? (int) $row['usos_maximos'] : null;
    if ($usos_max !== null && (int) $row['usos_actuales'] >= $usos_max) {
        return ['ok' => false, 'mensaje' => 'El código ya alcanzó su límite de usos.'];
    }
    $minimo = (float) ($row['minimo_compra'] ?? 0);
    if ((float) $subtotal < $minimo) {
        return ['ok' => false, 'mensaje' => 'No cumple el mínimo de compra para este código.'];
    }

    $descuento = 0.0;
    if ($row['tipo'] === 'porcentaje') {
        $descuento = ((float) $subtotal) * ((float) $row['valor'] / 100.0);
    } else {
        $descuento = (float) $row['valor'];
    }
    $descuento = max(0.0, min($descuento, (float) $subtotal));

    return [
        'ok' => true,
        'codigo' => $row['codigo'],
        'tipo' => $row['tipo'],
        'valor' => (float) $row['valor'],
        'descuento' => $descuento,
        'mensaje' => 'Código aplicado correctamente.',
    ];
}

?>
