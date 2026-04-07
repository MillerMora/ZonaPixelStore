<?php

/**
 * Utilidades de presentación compartidas entre listados públicos (reseñas, opiniones, tarjetas).
 */

// Extrae texto plano de HTML y lo trunca a una longitud máxima con sufijo elipsis
function publico_texto_resumen($html, $longitud = 180) {
    $texto = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));
    if ($texto === '') {
        return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($texto) > $longitud) {
        return mb_substr($texto, 0, $longitud) . '…';
    }
    if (strlen($texto) > $longitud) {
        return substr($texto, 0, $longitud) . '…';
    }
    return $texto;
}

// Representación visual de calificación comunitaria (1–5) con caracteres de estrella
function publico_estrellas_texto_1_a_5($calificacion) {
    $n = (int) round((float) $calificacion);
    $n = max(1, min(5, $n));
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
}

// Escala editorial 0–10 proyectada a cinco estrellas visibles (división por dos y redondeo)
function publico_estrellas_texto_editorial_10($calificacion) {
    $c = (float) $calificacion;
    $llenas = (int) round($c / 2);
    $llenas = max(0, min(5, $llenas));
    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

// Iniciales para avatares: nombre+apellido, solo nombre, o primeros dos del username
function publico_iniciales_usuario($nombre, $apellido, $username) {
    $nombre = trim((string) $nombre);
    $apellido = trim((string) $apellido);
    $primero = function ($str, $len) {
        if (function_exists('mb_substr')) {
            return mb_substr($str, 0, $len);
        }
        return substr($str, 0, $len);
    };
    if ($nombre !== '' && $apellido !== '') {
        return strtoupper($primero($nombre, 1) . $primero($apellido, 1));
    }
    if ($nombre !== '') {
        return strtoupper($primero($nombre, 2));
    }
    $username = trim((string) $username);
    if ($username !== '') {
        return strtoupper(substr($username, 0, 2));
    }
    return '??';
}

// Formato de moneda COP sin decimales (separador de miles punto)
function publico_fmt_precio_cop($valor) {
    return '$' . number_format((float) $valor, 0, ',', '.');
}

/**
 * Clona los parámetros de consulta, aplica $cambios (null elimina clave) y reinicia la paginación.
 *
 * @param array<string,mixed> $parametros_actuales Típicamente una copia de $_GET
 * @param array<string,mixed> $cambios Sobrescrituras; null en un valor borra esa clave
 */
function publico_fusionar_query_params(array $parametros_actuales, array $cambios) {
    $salida = $parametros_actuales;
    foreach ($cambios as $clave => $valor) {
        if ($valor === null) {
            unset($salida[$clave]);
        } else {
            $salida[$clave] = $valor;
        }
    }
    unset($salida['pagina']);
    return $salida;
}

// Envoltorio de http_build_query para arreglos anidados en filtros (p. ej. claves[])
function publico_http_build_query_multidimensional(array $params) {
    return http_build_query($params);
}

?>
