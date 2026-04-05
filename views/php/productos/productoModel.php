<?php

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
//consultas

function consultar_productos (){
    
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM productos') ;
    return $sql ;
    }
    
function consultar_producto_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// CRUD

function crear_producto ($categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo){
    
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `productos`(`categoria_id`, `marca_id`, `nombre`, `descripcion`, `precio`, `precio_original`, `stock`, `imagen_principal`, `destacado`, `activo`) VALUES (?,?,?,?,?,?,?,?,?,?)");  
    mysqli_stmt_bind_param($sql, 'iissddisii', $categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_producto ($id, $categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo){

    global $BD;

    $sql = mysqli_prepare($BD, "UPDATE `productos` SET `categoria_id` = ?, `marca_id` = ?, `nombre` = ?, `descripcion` = ?, `precio` = ?, `precio_original` = ?, `stock` = ?, `imagen_principal` = ?, `destacado` = ?, `activo` = ? WHERE `id_producto` = ?");

    mysqli_stmt_bind_param($sql, 'iissddisiii', $categoria, $marca, $nombre, $descripcion, $precio, $precio_original, $stock, $imagen, $destacado, $activo, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_producto ($id){
    global $BD;
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

function total_productos () {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) as total FROM productos');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

function total_productos_activos() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) AS total FROM productos WHERE activo = 1');
    $row = mysqli_fetch_assoc($sql);
    return (int) ($row['total'] ?? 0);
}

function promedio_calificacion_opiniones_aprobadas() {
    global $BD;
    $sql = mysqli_query($BD, '
        SELECT ROUND(AVG(calificacion), 2) AS promedio
        FROM opiniones
        WHERE aprobada = 1
    ');
    $row = mysqli_fetch_assoc($sql);
    if (!$row || $row['promedio'] === null) {
        return null;
    }
    return (float) $row['promedio'];
}

function producto_porcentaje_descuento($precio, $precio_original) {
    $precio = (float) $precio;
    $orig = (float) $precio_original;
    if ($orig <= 0 || $precio >= $orig) {
        return 0.0;
    }
    return ($orig - $precio) / $orig * 100.0;
}

function index_videojuego_mas_vendido() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT
            p.*,
            COALESCE(SUM(pi.cantidad), 0) AS unidades_vendidas,
            v.calificacion_promedio,
            v.total_opiniones,
            (SELECT GROUP_CONCAT(pl2.nombre ORDER BY pl2.nombre SEPARATOR ' · ')
             FROM producto_plataformas pp2
             INNER JOIN plataformas pl2 ON pl2.id_plataforma = pp2.plataforma_id
             WHERE pp2.producto_id = p.id_producto) AS plataformas_txt
        FROM productos p
        INNER JOIN categorias cat ON cat.id_categoria = p.categoria_id
            AND cat.nombre = 'Videojuegos' AND cat.activa = 1
        LEFT JOIN pedido_items pi ON pi.producto_id = p.id_producto
        LEFT JOIN pedidos pe ON pe.id_pedido = pi.pedido_id AND pe.estado_id = 4
        LEFT JOIN v_calificacion_productos v ON v.producto_id = p.id_producto
        WHERE p.activo = 1
        GROUP BY p.id_producto, p.categoria_id, p.marca_id, p.nombre, p.descripcion, p.precio,
            p.precio_original, p.stock, p.imagen_principal, p.destacado, p.activo, p.creado_en, p.actualizado_en,
            v.calificacion_promedio, v.total_opiniones
        ORDER BY unidades_vendidas DESC, p.id_producto ASC
        LIMIT 1
    ");
    if (!$sql) {
        return null;
    }
    return mysqli_fetch_assoc($sql);
}

function index_productos_videojuegos_destacados($limite = 12) {
    global $BD;
    $limite = (int) $limite;
    if ($limite < 1) {
        $limite = 12;
    }
    $sql = mysqli_query($BD, "
        SELECT
            p.*,
            GROUP_CONCAT(DISTINCT pl.nombre ORDER BY pl.nombre SEPARATOR ' · ') AS plataformas_txt,
            GROUP_CONCAT(DISTINCT pl.id_plataforma ORDER BY pl.nombre SEPARATOR ',') AS plataformas_ids,
            v.calificacion_promedio,
            v.total_opiniones
        FROM productos p
        INNER JOIN categorias cat ON cat.id_categoria = p.categoria_id
            AND cat.nombre = 'Videojuegos' AND cat.activa = 1
        LEFT JOIN producto_plataformas pp ON pp.producto_id = p.id_producto
        LEFT JOIN plataformas pl ON pl.id_plataforma = pp.plataforma_id
        LEFT JOIN v_calificacion_productos v ON v.producto_id = p.id_producto
        WHERE p.activo = 1
        GROUP BY p.id_producto, p.categoria_id, p.marca_id, p.nombre, p.descripcion, p.precio,
            p.precio_original, p.stock, p.imagen_principal, p.destacado, p.activo, p.creado_en, p.actualizado_en,
            v.calificacion_promedio, v.total_opiniones
        ORDER BY p.destacado DESC, p.id_producto DESC
        LIMIT $limite
    ");
    $out = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $out[] = $row;
        }
    }
    return $out;
}

function index_oferta_especial_videojuego() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT
            p.*,
            v.calificacion_promedio,
            v.total_opiniones,
            (SELECT GROUP_CONCAT(pl2.nombre ORDER BY pl2.nombre SEPARATOR ' · ')
             FROM producto_plataformas pp2
             INNER JOIN plataformas pl2 ON pl2.id_plataforma = pp2.plataforma_id
             WHERE pp2.producto_id = p.id_producto) AS plataformas_txt
        FROM productos p
        INNER JOIN categorias cat ON cat.id_categoria = p.categoria_id
            AND cat.nombre = 'Videojuegos' AND cat.activa = 1
        INNER JOIN v_calificacion_productos v ON v.producto_id = p.id_producto
        WHERE p.activo = 1
            AND p.precio < p.precio_original
            AND v.total_opiniones > 0
            AND v.calificacion_promedio >= 4
        ORDER BY RAND()
        LIMIT 1
    ");
    if (!$sql) {
        return null;
    }
    return mysqli_fetch_assoc($sql);
}

function index_productos_tecnologia($limite = 4) {
    global $BD;
    $limite = (int) $limite;
    if ($limite < 1) {
        $limite = 4;
    }
    $sql = mysqli_query($BD, "
        SELECT
            p.*,
            cat.nombre AS categoria_nombre,
            v.calificacion_promedio,
            v.total_opiniones
        FROM productos p
        INNER JOIN categorias cat ON cat.id_categoria = p.categoria_id
            AND cat.nombre <> 'Videojuegos' AND cat.activa = 1
        LEFT JOIN v_calificacion_productos v ON v.producto_id = p.id_producto
        WHERE p.activo = 1
        ORDER BY p.destacado DESC, p.id_producto DESC
        LIMIT $limite
    ");
    $out = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $out[] = $row;
        }
    }
    return $out;
}

function index_candidatos_ofertas_catalogo($excluir_id = null) {
    global $BD;
    $excluir_id = $excluir_id !== null ? (int) $excluir_id : 0;
    $sql = mysqli_query($BD, "
        SELECT
            p.*,
            cat.nombre AS categoria_nombre,
            v.calificacion_promedio,
            v.total_opiniones,
            GROUP_CONCAT(DISTINCT pl.nombre ORDER BY pl.nombre SEPARATOR ' · ') AS plataformas_txt,
            CASE
                WHEN p.precio_original > 0 AND p.precio < p.precio_original
                THEN ((p.precio_original - p.precio) / p.precio_original) * 100
                ELSE 0
            END AS pct_descuento
        FROM productos p
        INNER JOIN categorias cat ON cat.id_categoria = p.categoria_id AND cat.activa = 1
        LEFT JOIN producto_plataformas pp ON pp.producto_id = p.id_producto
        LEFT JOIN plataformas pl ON pl.id_plataforma = pp.plataforma_id
        LEFT JOIN v_calificacion_productos v ON v.producto_id = p.id_producto
        WHERE p.activo = 1
            AND p.precio < p.precio_original
            AND p.id_producto <> $excluir_id
        GROUP BY p.id_producto, p.categoria_id, p.marca_id, p.nombre, p.descripcion, p.precio,
            p.precio_original, p.stock, p.imagen_principal, p.destacado, p.activo, p.creado_en, p.actualizado_en,
            cat.nombre, v.calificacion_promedio, v.total_opiniones
        HAVING pct_descuento > 0
    ");
    $out = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $out[] = $row;
        }
    }
    return $out;
}

function index_filtrar_ofertas_secundarias(array $candidatos, $pct_especial, $rating_especial) {
    $pct_especial = (float) $pct_especial;
    $rating_especial = (float) $rating_especial;
    $filtrados = [];
    foreach ($candidatos as $row) {
        $pct = (float) $row['pct_descuento'];
        $rating = isset($row['calificacion_promedio']) && $row['calificacion_promedio'] !== null
            ? (float) $row['calificacion_promedio'] : 0.0;
        $menor_desc = $pct < $pct_especial - 0.005;
        $igual_desc_peor_rating = abs($pct - $pct_especial) < 0.005 && $rating < $rating_especial - 0.005;
        if ($menor_desc || $igual_desc_peor_rating) {
            $filtrados[] = $row;
        }
    }
    usort($filtrados, function ($a, $b) {
        $da = (float) $a['pct_descuento'];
        $db = (float) $b['pct_descuento'];
        if (abs($da - $db) > 0.005) {
            return $db <=> $da;
        }
        $ra = isset($a['calificacion_promedio']) ? (float) $a['calificacion_promedio'] : 0;
        $rb = isset($b['calificacion_promedio']) ? (float) $b['calificacion_promedio'] : 0;
        return $rb <=> $ra;
    });
    return $filtrados;
}

function productos_recientes($limit = 5) {
    global $BD;
    $sql = mysqli_query($BD, "SELECT * FROM productos ORDER BY id_producto DESC LIMIT $limit");
    return $sql;
}

function productos_mas_vendidos($limit = 5) {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT p.nombre, p.imagen_principal, COALESCE(SUM(pi.cantidad), 0) as unidades, COALESCE(SUM(pi.subtotal), 0) as ventas_total
        FROM productos p 
        LEFT JOIN pedido_items pi ON p.id_producto = pi.producto_id
        LEFT JOIN pedidos pe ON pi.pedido_id = pe.id_pedido AND pe.estado_id = 4
        GROUP BY p.id_producto, p.nombre, p.imagen_principal
        ORDER BY unidades DESC 
        LIMIT $limit
    ");
    return $sql;
}

if (isset($_GET["eliminar"])){
    eliminar_producto($_GET["eliminar"]);
    header("location: productos.php");
}
?>

