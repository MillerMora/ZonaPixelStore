<?php

/**
 * Modelo de reseñas editoriales: CRUD, listados públicos filtrados y utilidades para facetas de la vitrina.
 */

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}

// --- Lecturas administrativas ---

function consultar_resenas(){

    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM resenas');
    return $sql;
    }

// Resultado mysqli de una reseña por id (el consumidor debe iterar o leer filas; no es un array asociativo único)
function consultar_resenas_id($id){
    global $BD;
    $sql = mysqli_prepare($BD, "SELECT * FROM resenas WHERE id_resena = ?");
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    return $resultado; 
}

// --- Altas, bajas y actualizaciones ---

function crear_resena($imagen,$id_producto,$autor, $titulo,$contenido,$calificacion,$publicada){
    global $BD;
    $sql = mysqli_prepare($BD, "INSERT INTO `resenas`(`producto_id`, `autor_id`, `titulo`, `contenido`, `calificacion`, `imagen_portada`, `publicada`) VALUES (?,?,?,?,?,?,?)");
    if (!$sql) return false;
    mysqli_stmt_bind_param($sql, "iissdsi", $id_producto, $autor, $titulo, $contenido, $calificacion, $imagen, $publicada);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_resena($id,$imagen,$id_producto,$autor, $titulo,$contenido,$calificacion,$publicada){
    global $BD;
    $sql = mysqli_prepare($BD, "UPDATE `resenas` SET `producto_id`=?,`autor_id`=?,`titulo`=?,`contenido`=?,`calificacion`=?,`imagen_portada`=?,`publicada`=? WHERE id_resena = ?");
    if (!$sql) return false;
mysqli_stmt_bind_param($sql, "iissdsii", $id_producto,$autor,$titulo,$contenido,$calificacion,$imagen,$publicada,$id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}
function eliminar_resena($id){
    global $BD;
    $sql = mysqli_prepare($BD, "DELETE FROM `resenas` WHERE id_resena =?");
    mysqli_stmt_bind_param($sql, "i", $id);
    return mysqli_stmt_execute($sql);
}

// --- Contenido editorial público (home y sección reseñas) ---

function consultar_resenas_editorial_destacadas($limite = 6) {
    global $BD;
    $limite = (int) $limite;
    if ($limite < 1) {
        $limite = 6;
    }
    $sql = mysqli_query($BD, "
        SELECT
            r.id_resena,
            r.producto_id,
            r.autor_id,
            r.titulo,
            r.contenido,
            r.calificacion,
            r.imagen_portada,
            r.publicada_en,
            p.nombre AS producto_nombre,
            u.nombre AS autor_nombre,
            u.apellido AS autor_apellido,
            u.username AS autor_username,
            (SELECT GROUP_CONCAT(pl.nombre ORDER BY pl.nombre SEPARATOR ' · ')
             FROM producto_plataformas pp2
             INNER JOIN plataformas pl ON pl.id_plataforma = pp2.plataforma_id
             WHERE pp2.producto_id = p.id_producto) AS plataformas_txt
        FROM resenas r
        INNER JOIN productos p ON p.id_producto = r.producto_id AND p.activo = 1
        INNER JOIN usuarios u ON u.id_usuario = r.autor_id AND u.activo = 1
        INNER JOIN roles ro ON ro.id_rol = u.rol_id AND ro.nombre IN ('editor', 'admin')
        WHERE r.publicada = 1
        ORDER BY r.calificacion DESC, r.publicada_en DESC
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

// Valores distintos de plataforma entre productos que tienen reseña publicada (filtros del listado público)
function resenas_publicas_opciones_plataforma() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT pl.id_plataforma, pl.nombre
        FROM plataformas pl
        INNER JOIN producto_plataformas pp ON pp.plataforma_id = pl.id_plataforma
        INNER JOIN productos p ON p.id_producto = pp.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        INNER JOIN resenas r ON r.producto_id = p.id_producto AND r.publicada = 1
        ORDER BY pl.nombre ASC
    ");
    return resenas_publicas_fetch_all($sql);
}

// Marcas presentes en productos con reseña publicada
function resenas_publicas_opciones_marca() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT DISTINCT m.id_marca, m.nombre
        FROM marcas m
        INNER JOIN productos p ON p.marca_id = m.id_marca AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        INNER JOIN resenas r ON r.producto_id = p.id_producto AND r.publicada = 1
        ORDER BY m.nombre ASC
    ");
    return resenas_publicas_fetch_all($sql);
}

// Flags para mostrar u ocultar pestañas software/hardware según existencia de reseñas en cada categoría
function resenas_publicas_existen_por_tipo_producto() {
    global $BD;
    $out = ['software' => false, 'hardware' => false];
    $sql = mysqli_query($BD, "
        SELECT
            SUM(CASE WHEN c.nombre = 'Videojuegos' THEN 1 ELSE 0 END) AS sw,
            SUM(CASE WHEN c.nombre <> 'Videojuegos' THEN 1 ELSE 0 END) AS hw
        FROM resenas r
        INNER JOIN productos p ON p.id_producto = r.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        WHERE r.publicada = 1
    ");
    if ($sql) {
        $row = mysqli_fetch_assoc($sql);
        $out['software'] = ((int) ($row['sw'] ?? 0)) > 0;
        $out['hardware'] = ((int) ($row['hw'] ?? 0)) > 0;
    }
    return $out;
}

/**
 * Traduce estrellas visuales (1–5) a condición SQL sobre calificación editorial 0–10,
 * coherente con la vista que dibuja estrellas a partir de calificación/2.
 *
 * @return string fragmento SQL para r.calificación o cadena vacía si el filtro no aplica
 */
function resenas_publicas_sql_calificacion_por_estrellas_visuales($estrellas) {
    $estrellas = (int) $estrellas;
    switch ($estrellas) {
        case 1:
            return 'r.calificacion >= 1 AND r.calificacion < 3';
        case 2:
            return 'r.calificacion >= 3 AND r.calificacion < 5';
        case 3:
            return 'r.calificacion >= 5 AND r.calificacion < 7';
        case 4:
            return 'r.calificacion >= 7 AND r.calificacion < 9';
        case 5:
            return 'r.calificacion >= 9 AND r.calificacion <= 10';
        default:
            return '';
    }
}

// Opciones de filtro por estrellas solo si existen reseñas en ese tramo (etiqueta con caracteres de estrella)
function resenas_publicas_opciones_calificacion() {
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT
            SUM(CASE WHEN r.calificacion >= 1 AND r.calificacion < 3 THEN 1 ELSE 0 END) AS e1,
            SUM(CASE WHEN r.calificacion >= 3 AND r.calificacion < 5 THEN 1 ELSE 0 END) AS e2,
            SUM(CASE WHEN r.calificacion >= 5 AND r.calificacion < 7 THEN 1 ELSE 0 END) AS e3,
            SUM(CASE WHEN r.calificacion >= 7 AND r.calificacion < 9 THEN 1 ELSE 0 END) AS e4,
            SUM(CASE WHEN r.calificacion >= 9 AND r.calificacion <= 10 THEN 1 ELSE 0 END) AS e5
        FROM resenas r
        INNER JOIN productos p ON p.id_producto = r.producto_id AND p.activo = 1
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id AND c.activa = 1
        WHERE r.publicada = 1
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

// Normaliza un resultado mysqli en arreglo de filas asociativas
function resenas_publicas_fetch_all($resultado) {
    $filas = [];
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $filas[] = $row;
        }
    }
    return $filas;
}

/**
 * Listado paginado de reseñas publicadas con filtros de plataforma, marca, tipo de producto y texto libre.
 * Retorna 'filas' y 'total' para la UI de paginación.
 */
function resenas_publicas_listado(array $opciones) {
    global $BD;

    $limite = isset($opciones['limite']) ? (int) $opciones['limite'] : 9;
    $offset = isset($opciones['offset']) ? (int) $opciones['offset'] : 0;
    $limite = max(1, min(36, $limite));
    $offset = max(0, $offset);

    $filtro_plataformas = isset($opciones['filtro_plataformas']) ? $opciones['filtro_plataformas'] : [];
    $filtro_marcas = isset($opciones['filtro_marcas']) ? $opciones['filtro_marcas'] : [];
    $filtro_estrellas_editorial = isset($opciones['filtro_estrellas_editorial']) ? (int) $opciones['filtro_estrellas_editorial'] : 0;
    $tipo_producto = isset($opciones['tipo_producto']) ? $opciones['tipo_producto'] : '';
    $busqueda = isset($opciones['busqueda']) ? trim((string) $opciones['busqueda']) : '';

    // Solo contenido aprobado para tienda y producto/categoría visibles
    $where = ['r.publicada = 1', 'p.activo = 1', 'c.activa = 1'];

    if ($tipo_producto === 'software') {
        $where[] = "c.nombre = 'Videojuegos'";
    } elseif ($tipo_producto === 'hardware') {
        $where[] = "c.nombre <> 'Videojuegos'";
    }

    if (!empty($filtro_plataformas)) {
        $ids = implode(',', array_map('intval', $filtro_plataformas));
        $where[] = "EXISTS (
            SELECT 1 FROM producto_plataformas ppf
            WHERE ppf.producto_id = p.id_producto AND ppf.plataforma_id IN ($ids)
        )";
    }

    if (!empty($filtro_marcas)) {
        $ids = implode(',', array_map('intval', $filtro_marcas));
        $where[] = "p.marca_id IN ($ids)";
    }

    $sql_estrellas = resenas_publicas_sql_calificacion_por_estrellas_visuales($filtro_estrellas_editorial);
    if ($sql_estrellas !== '') {
        $where[] = '(' . $sql_estrellas . ')';
    }

    $join_marca = '';
    if ($busqueda !== '') {
        // Ampliar búsqueda a marca del producto; placeholders repetidos en prepare más abajo
        $join_marca = 'LEFT JOIN marcas mar ON mar.id_marca = p.marca_id';
        $where[] = '(r.titulo LIKE ? OR r.contenido LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ?
            OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.username LIKE ? OR c.nombre LIKE ?
            OR mar.nombre LIKE ?
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
        FROM resenas r
        INNER JOIN productos p ON p.id_producto = r.producto_id
        INNER JOIN categorias c ON c.id_categoria = p.categoria_id
        INNER JOIN usuarios u ON u.id_usuario = r.autor_id AND u.activo = 1
        $join_marca
        WHERE $where_sql
    ";

    $sql_count = 'SELECT COUNT(*) AS total ' . $from_sql;
    $total = 0;

    if ($busqueda !== '') {
        $patron_busqueda = '%' . $busqueda . '%';
        $stmt_c = mysqli_prepare($BD, $sql_count);
        if ($stmt_c) {
            mysqli_stmt_bind_param(
                $stmt_c,
                'sssssssssss',
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
            mysqli_stmt_execute($stmt_c);
            $res_c = mysqli_stmt_get_result($stmt_c);
            if ($res_c) {
                $row_c = mysqli_fetch_assoc($res_c);
                $total = (int) ($row_c['total'] ?? 0);
            }
            mysqli_stmt_close($stmt_c);
        }
    } else {
        // Sin texto de búsqueda: conteo directo sin sentencias preparadas
        $res_c = mysqli_query($BD, $sql_count);
        if ($res_c) {
            $row_c = mysqli_fetch_assoc($res_c);
            $total = (int) ($row_c['total'] ?? 0);
        }
    }

    $sql_lista = "
        SELECT
            r.id_resena,
            r.titulo,
            r.contenido,
            r.calificacion,
            r.imagen_portada,
            r.publicada_en,
            p.nombre AS producto_nombre,
            p.id_producto AS producto_id,
            u.nombre AS autor_nombre,
            u.apellido AS autor_apellido,
            u.username AS autor_username,
            (SELECT GROUP_CONCAT(pl.nombre ORDER BY pl.nombre SEPARATOR ' · ')
             FROM producto_plataformas pp2
             INNER JOIN plataformas pl ON pl.id_plataforma = pp2.plataforma_id
             WHERE pp2.producto_id = p.id_producto) AS plataformas_txt
        $from_sql
        ORDER BY r.publicada_en DESC, r.id_resena DESC
        LIMIT $offset, $limite
    ";

    $filas = [];
    if ($busqueda !== '') {
        $patron_busqueda = '%' . $busqueda . '%';
        $stmt_l = mysqli_prepare($BD, $sql_lista);
        if ($stmt_l) {
            mysqli_stmt_bind_param(
                $stmt_l,
                'sssssssssss',
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
            mysqli_stmt_execute($stmt_l);
            $res_l = mysqli_stmt_get_result($stmt_l);
            $filas = resenas_publicas_fetch_all($res_l);
            mysqli_stmt_close($stmt_l);
        }
    } else {
        $filas = resenas_publicas_fetch_all(mysqli_query($BD, $sql_lista));
    }

    return ['filas' => $filas, 'total' => $total];
}

// --- Consulta única de reseña por ID para página pública (con joins) ---

/**
 * Retorna datos completos de UNA reseña publicada por ID (array asociativo o null).
 * Incluye producto, autor, plataformas concatenadas como en mockup.
 */
function consultar_resena_por_id($id_resena) {
    global $BD;
    $stmt = mysqli_prepare($BD, "
        SELECT 
            r.*,
            p.nombre AS producto_nombre,
            p.precio,
            CONCAT_WS(' ', u.nombre, u.apellido) AS autor_completo,
            COALESCE(u.username, 'ZonaPixel Staff') AS autor_username,
            GROUP_CONCAT(pl.nombre ORDER BY pl.nombre SEPARATOR ' · ') AS plataformas_txt,
            p.imagen_principal
        FROM resenas r
        INNER JOIN productos p ON p.id_producto = r.producto_id AND p.activo = 1
        INNER JOIN usuarios u ON u.id_usuario = r.autor_id AND u.activo = 1
        LEFT JOIN producto_plataformas pp ON pp.producto_id = r.producto_id
        LEFT JOIN plataformas pl ON pl.id_plataforma = pp.plataforma_id
        WHERE r.id_resena = ? AND r.publicada = 1
        GROUP BY r.id_resena, p.id_producto, u.id_usuario
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_resena);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $resena = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $resena ?: null;
}

/**
 * Comentarios aprobados para una reseña específica (array de arrays).
 */
function consultar_comentarios_resena($id_resena) {
    global $BD;
    $stmt = mysqli_prepare($BD, "
        SELECT 
            c.id_comentario,
            c.contenido,
            u.username,
            DATE_FORMAT(c.creado_en, '%e %M') AS fecha_texto
        FROM comentarios c
        INNER JOIN usuarios u ON u.id_usuario = c.usuario_id
        WHERE c.resena_id = ? AND c.aprobado = 1
        ORDER BY c.creado_en DESC
        LIMIT 5
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_resena);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comentarios = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comentarios[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $comentarios;
}

// Invocación directa desde la URL del panel: ?eliminar=id redirige tras borrar
function buscar_resenas($busqueda) {
    global $BD;
    $busq = "%$busqueda%";
    $sql = mysqli_prepare($BD, "SELECT * FROM resenas WHERE CONCAT(titulo, ' ', contenido) LIKE ?");
    mysqli_stmt_bind_param($sql, 's', $busq);
    mysqli_stmt_execute($sql);
    return mysqli_stmt_get_result($sql);
}

if (isset($_GET['eliminar'])) {
    eliminar_resena($_GET['eliminar']);
    header('location: resenas.php');
    exit;
}
?>

