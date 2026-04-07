<?php

/**
 * Modelo de opiniones de comunidad: moderación (aprobada), CRUD y listado público con filtros avanzados.
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}

// --- Lecturas administrativas ---

function consultar_opiniones (){
    global $BD;
    $sql = mysqli_query($BD ,'SELECT * FROM opiniones') ;
    return $sql ;
    }
    
function consultar_opinion_id ($id){
    global $BD;
    $sql = mysqli_prepare($BD,"SELECT * FROM opiniones WHERE id_opinion = ?") ;
    mysqli_stmt_bind_param($sql, 'i', $id );
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado) ;    
}

// --- Altas, bajas y actualizaciones ---

function crear_opinion ($usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `opiniones`(`usuario_id`, `producto_id`, `plataforma_id`, `titulo`, `contenido`, `calificacion`, `aprobada`) VALUES (?,?,?,?,?,?,?)" );  
    mysqli_stmt_bind_param($sql, 'iiisssi', $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_opinion ($id, $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `opiniones` SET `usuario_id` = ?, `producto_id` = ?, `plataforma_id` = ?, `titulo` = ?, `contenido` = ?, `calificacion` = ?, `aprobada` = ? WHERE `id_opinion` = ?");
    mysqli_stmt_bind_param($sql, 'iiisssii', $usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_opinion ($id){
    global $BD;
    // Relajación temporal de FK por posibles enlaces o historial
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, "DELETE FROM opiniones WHERE id_opinion = ?");
    mysqli_stmt_bind_param($sql, "i", $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD, "SET FOREIGN_KEY_CHECKS = 1");
    return $resultado ;
}

// --- Listado público y facetas (opiniones aprobadas) ---

function opiniones_publicas_fetch_all($resultado) {
    $filas = [];
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $filas[] = $row;
        }
    }
    return $filas;
}

// Plataformas asociadas a productos que tienen al menos una opinión aprobada
function opiniones_publicas_opciones_plataforma() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT pl.id_plataforma, pl.nombre
        FROM plataformas pl
        INNER JOIN producto_plataformas pp ON pp.plataforma_id = pl.id_plataforma
        INNER JOIN productos p ON p.id_producto = pp.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        INNER JOIN opiniones o ON o.producto_id = p.id_producto AND o.aprobada = 1
        ORDER BY pl.nombre ASC
    ");
    return opiniones_publicas_fetch_all($sql);
}

// Marcas de productos con opiniones aprobadas
function opiniones_publicas_opciones_marca() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT m.id_marca, m.nombre
        FROM marcas m
        INNER JOIN productos p ON p.marca_id = m.id_marca AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        INNER JOIN opiniones o ON o.producto_id = p.id_producto AND o.aprobada = 1
        ORDER BY m.nombre ASC
    ");
    return opiniones_publicas_fetch_all($sql);
}

// Presencia de opiniones por tipo de categoría (software vs resto) para la navegación por pestañas
function opiniones_publicas_existen_por_tipo_producto() {
    global $BD;
    $out = ['software' => false, 'hardware' => false];
    $sql = mysqli_query($BD, "
        SELECT
            SUM(CASE WHEN c.nombre = 'Videojuegos' THEN 1 ELSE 0 END) AS sw,
            SUM(CASE WHEN c.nombre <> 'Videojuegos' THEN 1 ELSE 0 END) AS hw
        FROM opiniones o
        INNER JOIN productos p ON p.id_producto = o.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        WHERE o.aprobada = 1
    ");
    if ($sql) {
        $row = mysqli_fetch_assoc($sql);
        $out['software'] = ((int) ($row['sw'] ?? 0)) > 0;
        $out['hardware'] = ((int) ($row['hw'] ?? 0)) > 0;
    }
    return $out;
}

// Opciones de filtro por calificación exacta 1–5 solo si existen filas en ese valor
function opiniones_publicas_opciones_calificacion() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT
            SUM(CASE WHEN o.calificacion = 1 THEN 1 ELSE 0 END) AS e1,
            SUM(CASE WHEN o.calificacion = 2 THEN 1 ELSE 0 END) AS e2,
            SUM(CASE WHEN o.calificacion = 3 THEN 1 ELSE 0 END) AS e3,
            SUM(CASE WHEN o.calificacion = 4 THEN 1 ELSE 0 END) AS e4,
            SUM(CASE WHEN o.calificacion = 5 THEN 1 ELSE 0 END) AS e5
        FROM opiniones o
        INNER JOIN productos p ON p.id_producto = o.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        WHERE o.aprobada = 1
    ");
    $opciones = [];
    if ($sql) {
        $row = mysqli_fetch_assoc($sql);
        for ($estrella = 1; $estrella <= 5; $estrella++) {
            $clave = 'e' . $estrella;
            if (((int) ($row[$clave] ?? 0)) > 0) {
                $opciones[] = [
                    'valor' => $estrella,
                    'etiqueta' => str_repeat('★', $estrella) . str_repeat('☆', 5 - $estrella),
                ];
            }
        }
    }
    return $opciones;
}

/**
 * Opiniones aprobadas paginadas con filtros por plataforma (incluye heurística si plataforma_id es NULL),
 * marca, estrellas y búsqueda full-text sobre varias tablas.
 */
function opiniones_publicas_listado(array $opciones) {
    global $BD;

    $limite = isset($opciones['limite']) ? (int) $opciones['limite'] : 9;
    $offset = isset($opciones['offset']) ? (int) $opciones['offset'] : 0;
    $limite = max(1, min(36, $limite));
    $offset = max(0, $offset);

    $filtro_plataformas = isset($opciones['filtro_plataformas']) ? $opciones['filtro_plataformas'] : [];
    $filtro_marcas = isset($opciones['filtro_marcas']) ? $opciones['filtro_marcas'] : [];
    $filtro_estrellas_comunidad = isset($opciones['filtro_estrellas_comunidad']) ? (int) $opciones['filtro_estrellas_comunidad'] : 0;
    $tipo_producto = isset($opciones['tipo_producto']) ? $opciones['tipo_producto'] : '';
    $busqueda = isset($opciones['busqueda']) ? trim((string) $opciones['busqueda']) : '';

    // Contenido moderado y publicable en tienda
    $where = ['o.aprobada = 1', 'p.activo = 1', 'c.activa = 1'];

    if ($tipo_producto === 'software') {
        $where[] = "c.nombre = 'Videojuegos'";
    } elseif ($tipo_producto === 'hardware') {
        $where[] = "c.nombre <> 'Videojuegos'";
    }

    if (!empty($filtro_plataformas)) {
        $ids = implode(',', array_map('intval', $filtro_plataformas));
        // Coincidencia directa con plataforma de la opinión o, si nula, cualquier plataforma del producto
        $where[] = "(
            o.plataforma_id IN ($ids)
            OR (o.plataforma_id IS NULL AND EXISTS (
                SELECT 1 FROM producto_plataformas ppf
                WHERE ppf.producto_id = o.producto_id AND ppf.plataforma_id IN ($ids)
            ))
        )";
    }

    if (!empty($filtro_marcas)) {
        $ids = implode(',', array_map('intval', $filtro_marcas));
        $where[] = "p.marca_id IN ($ids)";
    }

    if ($filtro_estrellas_comunidad >= 1 && $filtro_estrellas_comunidad <= 5) {
        $where[] = 'o.calificacion = ' . $filtro_estrellas_comunidad;
    }

    $join_marca = '';
    if ($busqueda !== '') {
        // Búsqueda repartida en muchos LIKE con el mismo patrón (bind repetido más abajo)
        $join_marca = 'LEFT JOIN marcas mar ON mar.id_marca = p.marca_id';
        $where[] = '(o.titulo LIKE ? OR o.contenido LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ?
            OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.username LIKE ? OR c.nombre LIKE ?
            OR mar.nombre LIKE ?
            OR pl_op.nombre LIKE ?
            OR EXISTS (
                SELECT 1 FROM producto_plataformas ppq
                INNER JOIN plataformas plq ON plq.id_plataforma = ppq.plataforma_id
                WHERE ppq.producto_id = p.id_producto AND plq.nombre LIKE ?
            )
            OR EXISTS (
                SELECT 1 FROM producto_generos pg
                INNER JOIN generos g ON g.id_genero = pg.genero_id
                WHERE pg.producto_id = p.id_producto AND g.nombre LIKE ?
            ))';
    }

    $where_sql = implode(' AND ', $where);

    $from_sql = "
        FROM opiniones o
        INNER JOIN productos p ON p.id_producto = o.producto_id
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id
        INNER JOIN usuarios u ON u.id_usuario = o.usuario_id AND u.activo = 1
        LEFT JOIN plataformas pl_op ON pl_op.id_plataforma = o.plataforma_id
        $join_marca
        WHERE $where_sql
    ";

    $sql_count = 'SELECT COUNT(*) AS total ' . $from_sql;
    $total = 0;

    if ($busqueda !== '') {
        $patron_busqueda = '%' . $busqueda . '%';
        $stmt_cuenta = mysqli_prepare($BD, $sql_count);
        if ($stmt_cuenta) {
            mysqli_stmt_bind_param(
                $stmt_cuenta,
                'ssssssssssss',
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda
            );
            mysqli_stmt_execute($stmt_cuenta);
            $resultado_cuenta = mysqli_stmt_get_result($stmt_cuenta);
            if ($resultado_cuenta) {
                $fila_cuenta = mysqli_fetch_assoc($resultado_cuenta);
                $total = (int) ($fila_cuenta['total'] ?? 0);
            }
            mysqli_stmt_close($stmt_cuenta);
        }
    } else {
        // Sin término de búsqueda: evita prepare y enlaza directamente
        $resultado_cuenta = mysqli_query($BD, $sql_count);
        if ($resultado_cuenta) {
            $fila_cuenta = mysqli_fetch_assoc($resultado_cuenta);
            $total = (int) ($fila_cuenta['total'] ?? 0);
        }
    }

    $sql_lista = "
        SELECT
            o.id_opinion,
            o.titulo,
            o.contenido,
            o.calificacion,
            o.creado_en,
            p.nombre AS producto_nombre,
            p.id_producto AS producto_id,
            u.nombre AS autor_nombre,
            u.apellido AS autor_apellido,
            u.username AS autor_username,
            pl_op.nombre AS plataforma_opinion_nombre,
            (SELECT GROUP_CONCAT(pl.nombre ORDER BY pl.nombre SEPARATOR ' · ')
             FROM producto_plataformas pp2
             INNER JOIN plataformas pl ON pl.id_plataforma = pp2.plataforma_id
             WHERE pp2.producto_id = p.id_producto) AS plataformas_producto_txt
        $from_sql
        ORDER BY o.creado_en DESC, o.id_opinion DESC
        LIMIT $offset, $limite
    ";

    $filas_opiniones = [];
    if ($busqueda !== '') {
        $patron_busqueda = '%' . $busqueda . '%';
        $stmt_lista = mysqli_prepare($BD, $sql_lista);
        if ($stmt_lista) {
            mysqli_stmt_bind_param(
                $stmt_lista,
                'ssssssssssss',
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda,
                $patron_busqueda
            );
            mysqli_stmt_execute($stmt_lista);
            $resultado_lista = mysqli_stmt_get_result($stmt_lista);
            $filas_opiniones = opiniones_publicas_fetch_all($resultado_lista);
            mysqli_stmt_close($stmt_lista);
        }
    } else {
        $filas_opiniones = opiniones_publicas_fetch_all(mysqli_query($BD, $sql_lista));
    }

    return ['filas' => $filas_opiniones, 'total' => $total];
}

// Solo ejecuta borrado por querystring cuando este archivo es el script principal
if (isset($_GET['eliminar'])) {
    eliminar_opinion( $_GET['eliminar']);
    header('location: opiniones.php');
    exit;
}

?>

