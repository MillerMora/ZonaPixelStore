<?php

/**
 * Modelo de productos: CRUD administrativo, consultas de escaparate (inicio y catálogo)
 * y agregaciones para ranking, filtros y estadísticas de comunidad.
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}

// --- Lectura administrativa ---

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

// --- Altas, bajas y actualizaciones ---

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
    // Borrado con integridad referencial relajada (relaciones plataforma/género/opiniones)
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM productos WHERE id_producto = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

// --- Contadores globales ---

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

// Promedio de estrellas (1–5) solo entre opiniones con aprobada = 1; null si no hay datos
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

// Porcentaje de rebaja respecto al precio tachado; 0 si no aplica oferta válida
function producto_porcentaje_descuento($precio, $precio_original) {
    $precio = (float) $precio;
    $orig = (float) $precio_original;
    if ($orig <= 0 || $precio >= $orig) {
        return 0.0;
    }
    return ($orig - $precio) / $orig * 100.0;
}

/**
 * Videojuego activo con más unidades vendidas en pedidos en estado completado (estado_id = 4).
 * Incluye vista de calificaciones y texto de plataformas para la tarjeta destacada del inicio.
 */
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

// Carrusel o rejilla de videojuegos activos con datos de plataforma y rating agregado
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

// Una oferta aleatoria de videojuego con descuento real, con opiniones y nota media alta (umbral en SQL)
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

// Productos de categorías distintas a «Videojuegos» (hardware u otros), para bloque «tecnología»
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

// Conjunto de productos en oferta (precio < original) con etiqueta de categoría y % calculado en SQL; opcional exclusión por id
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

/**
 * Ofertas «secundarias» respecto al banner principal: quedan las que tienen menor descuento
 * o igual descuento pero peor valoración; el resultado se ordena por % desc y luego por rating.
 */
function index_filtrar_ofertas_secundarias(array $candidatos, $pct_especial, $rating_especial) {
    $pct_especial = (float) $pct_especial;
    $rating_especial = (float) $rating_especial;
    $filtrados = [];
    foreach ($candidatos as $row) {
        $pct = (float) $row['pct_descuento'];
        $rating = isset($row['calificacion_promedio']) && $row['calificacion_promedio'] !== null
            ? (float) $row['calificacion_promedio'] : 0.0;
        // Comparaciones con margen numérico para no depender de igualdad exacta en flotantes
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

// Últimos productos dados de alta (orden por id descendente)
function productos_recientes($limit = 5) {
    global $BD;
    $sql = mysqli_query($BD, "SELECT * FROM productos ORDER BY id_producto DESC LIMIT $limit");
    return $sql;
}

// Ranking por unidades en ítems de pedidos completados (estado_id = 4)
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

// --- Facetas y catálogo público (filtros, rangos, búsqueda) ---

function catalogo_categorias_con_productos_activos() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT c.id_categoria, c.nombre
        FROM categorias c
        INNER JOIN productos p ON p.categoria_id = c.id_categoria AND p.activo = 1
        WHERE c.activa = 1
        ORDER BY c.nombre ASC
    ");
    $filas = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $filas[] = $row;
        }
    }
    return $filas;
}

/**
 * Plataformas que tienen al menos un producto activo enlazado (tabla producto_plataformas).
 */
function catalogo_plataformas_con_productos_activos() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT pl.id_plataforma, pl.nombre
        FROM plataformas pl
        INNER JOIN producto_plataformas pp ON pp.plataforma_id = pl.id_plataforma
        INNER JOIN productos p ON p.id_producto = pp.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        ORDER BY pl.nombre ASC
    ");
    $filas = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $filas[] = $row;
        }
    }
    return $filas;
}

/**
 * Marcas con al menos un producto activo (campo marca_id en productos).
 */
function catalogo_marcas_con_productos_activos() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT m.id_marca, m.nombre
        FROM marcas m
        INNER JOIN productos p ON p.marca_id = m.id_marca AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        ORDER BY m.nombre ASC
    ");
    $filas = [];
    if ($sql) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $filas[] = $row;
        }
    }
    return $filas;
}

// Mínimo y máximo de precio actual entre productos activos y categorías activas (control deslizante del filtro)
function catalogo_rango_precios_activos() {
    global $BD;
    $sql = mysqli_query($BD, '
        SELECT COALESCE(MIN(p.precio), 0) AS precio_min, COALESCE(MAX(p.precio), 0) AS precio_max
        FROM productos p
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        WHERE p.activo = 1
    ');
    $row = mysqli_fetch_assoc($sql);
    return [
        'precio_min' => (float) ($row['precio_min'] ?? 0),
        'precio_max' => (float) ($row['precio_max'] ?? 0),
    ];
}

// Indica si la vista de calificaciones tiene al menos un producto con opiniones aprobadas agregadas
function catalogo_existen_calificaciones_comunidad() {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) AS n FROM v_calificacion_productos WHERE total_opiniones > 0');
    $row = mysqli_fetch_assoc($sql);
    return ((int) ($row['n'] ?? 0)) > 0;
}

/**
 * Fragmento de condición LIKE reutilizable con cinco marcadores ? (nombre, descripción, marca, categoría, plataforma en tabla puente).
 */
function catalogo_busqueda_sql_preparada() {
    return '(p.nombre LIKE CONCAT(\'%\', ?, \'%\') OR p.descripcion LIKE CONCAT(\'%\', ?, \'%\')'
        . ' OR (m.id_marca IS NOT NULL AND m.nombre LIKE CONCAT(\'%\', ?, \'%\'))'
        . ' OR c.nombre LIKE CONCAT(\'%\', ?, \'%\')'
        . ' OR EXISTS ('
        . 'SELECT 1 FROM producto_plataformas ppq'
        . ' INNER JOIN plataformas plq ON plq.id_plataforma = ppq.plataforma_id'
        . ' WHERE ppq.producto_id = p.id_producto AND plq.nombre LIKE CONCAT(\'%\', ?, \'%\')))';
}

/**
 * Misma condición de búsqueda con el término escapado para consultas no preparadas (compatibilidad sin mysqli_stmt_get_result).
 */
function catalogo_busqueda_sql_escapada($conexion, $busqueda) {
    $esc = mysqli_real_escape_string($conexion, $busqueda);
    return "(p.nombre LIKE '%$esc%' OR p.descripcion LIKE '%$esc%'"
        . " OR (m.id_marca IS NOT NULL AND m.nombre LIKE '%$esc%')"
        . " OR c.nombre LIKE '%$esc%'"
        . ' OR EXISTS ('
        . 'SELECT 1 FROM producto_plataformas ppq'
        . ' INNER JOIN plataformas plq ON plq.id_plataforma = ppq.plataforma_id'
        . " WHERE ppq.producto_id = p.id_producto AND plq.nombre LIKE '%$esc%'))";
}

/**
 * Lista paginada de productos para el catálogo con filtros múltiples y orden configurable.
 * Retorna claves 'productos' (filas) y 'total' (conteo para paginación) respetando mismos criterios WHERE.
 */
function catalogo_productos_filtrados(array $opciones) {
    global $BD;

    // Límite de página acotado y offset no negativo
    $limite = isset($opciones['limite']) ? (int) $opciones['limite'] : 12;
    $offset = isset($opciones['offset']) ? (int) $opciones['offset'] : 0;
    $limite = max(1, min(48, $limite));
    $offset = max(0, $offset);

    $filtro_categorias = isset($opciones['filtro_categorias']) ? $opciones['filtro_categorias'] : [];
    $filtro_plataformas = isset($opciones['filtro_plataformas']) ? $opciones['filtro_plataformas'] : [];
    $filtro_marcas = isset($opciones['filtro_marcas']) ? $opciones['filtro_marcas'] : [];
    $filtro_generos = isset($opciones['filtro_generos']) ? $opciones['filtro_generos'] : [];
    $calificacion_estrellas = isset($opciones['calificacion_estrellas']) ? (int) $opciones['calificacion_estrellas'] : 0;

    $tipo_producto = isset($opciones['tipo_producto']) ? $opciones['tipo_producto'] : '';
    $solo_ofertas = !empty($opciones['solo_ofertas']);
    $busqueda = isset($opciones['busqueda']) ? trim((string) $opciones['busqueda']) : '';
    $orden = isset($opciones['orden']) ? $opciones['orden'] : 'popular';

    // Rango de precios opcional; se concatena como literal numérico tras cast (ids de filtros ya enteros)
    $precio_min = isset($opciones['precio_min']) ? (float) $opciones['precio_min'] : null;
    $precio_max = isset($opciones['precio_max']) ? (float) $opciones['precio_max'] : null;

    // Siempre restrictivo a catálogo visible: producto y categoría activos
    $where = ['p.activo = 1', 'c.activa = 1'];

    if ($tipo_producto === 'software') {
        $where[] = "c.nombre = 'Videojuegos'";
    } elseif ($tipo_producto === 'hardware') {
        $where[] = "c.nombre <> 'Videojuegos'";
    }

    if ($solo_ofertas) {
        $where[] = 'p.precio < p.precio_original';
    }

    if (!empty($filtro_categorias)) {
        $ids = implode(',', array_map('intval', $filtro_categorias));
        $where[] = "p.categoria_id IN ($ids)";
    }

    if (!empty($filtro_marcas)) {
        $ids = implode(',', array_map('intval', $filtro_marcas));
        $where[] = "p.marca_id IN ($ids)";
    }

    if (!empty($filtro_plataformas)) {
        $ids = implode(',', array_map('intval', $filtro_plataformas));
        $where[] = "EXISTS (
            SELECT 1 FROM producto_plataformas ppf
            WHERE ppf.producto_id = p.id_producto AND ppf.plataforma_id IN ($ids)
        )";
    }

    if (!empty($filtro_generos)) {
        $ids = implode(',', array_map('intval', $filtro_generos));
        $where[] = "EXISTS (
            SELECT 1 FROM producto_generos pgf
            WHERE pgf.producto_id = p.id_producto AND pgf.genero_id IN ($ids)
        )";
    }

    if ($precio_min !== null) {
        $where[] = 'p.precio >= ' . (float) $precio_min;
    }
    if ($precio_max !== null) {
        $where[] = 'p.precio <= ' . (float) $precio_max;
    }

    // Filtro por estrellas: traduce a rangos de promedio en vista v_calificacion_productos (franjas tipo TripAdvisor)
    if ($calificacion_estrellas >= 1 && $calificacion_estrellas <= 5) {
        $where[] = 'v.total_opiniones > 0';
        if ($calificacion_estrellas === 5) {
            $where[] = 'v.calificacion_promedio >= 4.5';
        } elseif ($calificacion_estrellas === 4) {
            $where[] = 'v.calificacion_promedio >= 3.5 AND v.calificacion_promedio < 4.5';
        } elseif ($calificacion_estrellas === 3) {
            $where[] = 'v.calificacion_promedio >= 2.5 AND v.calificacion_promedio < 3.5';
        } elseif ($calificacion_estrellas === 2) {
            $where[] = 'v.calificacion_promedio >= 1.5 AND v.calificacion_promedio < 2.5';
        } else {
            $where[] = 'v.calificacion_promedio >= 1 AND v.calificacion_promedio < 1.5';
        }
    }

    $busqueda_activa = $busqueda !== '';
    $busqueda_sql_preparada = catalogo_busqueda_sql_preparada();
    if ($busqueda_activa) {
        $where[] = $busqueda_sql_preparada;
    }

    $where_sql = implode(' AND ', $where);

    // Subconsulta de unidades vendidas solo en pedidos completados, para orden «popular»
    $join_ventas = "
        LEFT JOIN (
            SELECT pi.producto_id, SUM(pi.cantidad) AS unidades_vendidas
            FROM pedido_items pi
            INNER JOIN pedidos pe ON pe.id_pedido = pi.pedido_id AND pe.estado_id = 4
            GROUP BY pi.producto_id
        ) ventas ON ventas.producto_id = p.id_producto
    ";

    $order_sql = 'p.id_producto DESC';
    if ($orden === 'precio_asc') {
        $order_sql = 'p.precio ASC, p.id_producto ASC';
    } elseif ($orden === 'precio_desc') {
        $order_sql = 'p.precio DESC, p.id_producto DESC';
    } elseif ($orden === 'nuevo') {
        $order_sql = 'p.creado_en DESC, p.id_producto DESC';
    } elseif ($orden === 'rating') {
        $order_sql = 'v.calificacion_promedio IS NULL, v.calificacion_promedio DESC, p.id_producto DESC';
    } elseif ($orden === 'popular') {
        $order_sql = 'COALESCE(ventas.unidades_vendidas, 0) DESC, p.id_producto DESC';
    }

    $from_sql = "
        FROM productos p
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id
        LEFT JOIN marcas m ON m.id_marca = p.marca_id
        LEFT JOIN v_calificacion_productos v ON v.producto_id = p.id_producto
        $join_ventas
        WHERE $where_sql
    ";

    $sql_count = 'SELECT COUNT(DISTINCT p.id_producto) AS total ' . $from_sql;
    $sql_lista = "
        SELECT
            p.id_producto,
            p.categoria_id,
            p.marca_id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.precio_original,
            p.stock,
            p.imagen_principal,
            p.destacado,
            p.activo,
            p.creado_en,
            c.nombre AS categoria_nombre,
            v.calificacion_promedio,
            v.total_opiniones,
            COALESCE(ventas.unidades_vendidas, 0) AS unidades_vendidas,
            (SELECT GROUP_CONCAT(pl.nombre ORDER BY pl.nombre SEPARATOR ' · ')
             FROM producto_plataformas pp2
             INNER JOIN plataformas pl ON pl.id_plataforma = pp2.plataforma_id
             WHERE pp2.producto_id = p.id_producto) AS plataformas_txt
        $from_sql
        ORDER BY $order_sql
        LIMIT $offset, $limite
    ";

    $total = 0;
    $filas = [];

    // Ejecuta conteo o listado: con búsqueda usa prepare + cinco parámetros idénticos; si falla get_result, vuelve a consulta escapada
    $ejecutar_consulta_catalogo = function ($sql_texto) use ($BD, $busqueda_activa, $busqueda, $busqueda_sql_preparada) {
        if (!$busqueda_activa) {
            return mysqli_query($BD, $sql_texto);
        }
        $stmt = mysqli_prepare($BD, $sql_texto);
        if (!$stmt) {
            $sql_fallback = str_replace($busqueda_sql_preparada, catalogo_busqueda_sql_escapada($BD, $busqueda), $sql_texto);
            return mysqli_query($BD, $sql_fallback);
        }
        $param1 = $busqueda;
        $param2 = $busqueda;
        $param3 = $busqueda;
        $param4 = $busqueda;
        $param5 = $busqueda;
        mysqli_stmt_bind_param($stmt, 'sssss', $param1, $param2, $param3, $param4, $param5);
        mysqli_stmt_execute($stmt);
        $resultado = null;
        if (function_exists('mysqli_stmt_get_result')) {
            $resultado = mysqli_stmt_get_result($stmt);
        }
        if ($resultado !== false && $resultado !== null) {
            mysqli_stmt_close($stmt);
            return $resultado;
        }
        mysqli_stmt_close($stmt);
        $sql_fallback = str_replace($busqueda_sql_preparada, catalogo_busqueda_sql_escapada($BD, $busqueda), $sql_texto);
        return mysqli_query($BD, $sql_fallback);
    };

    $res_count = $ejecutar_consulta_catalogo($sql_count);
    if ($res_count) {
        $row_count = mysqli_fetch_assoc($res_count);
        $total = (int) ($row_count['total'] ?? 0);
    }

    $res_lista = $ejecutar_consulta_catalogo($sql_lista);
    if ($res_lista) {
        while ($row = mysqli_fetch_assoc($res_lista)) {
            $filas[] = $row;
        }
    }

    return ['productos' => $filas, 'total' => $total];
}

// Invocación directa desde la URL del panel: ?eliminar=id redirige tras borrar
if (isset($_GET['eliminar'])) {
    eliminar_producto($_GET['eliminar']);
    header('location: productos.php');
    exit;
}
?>

