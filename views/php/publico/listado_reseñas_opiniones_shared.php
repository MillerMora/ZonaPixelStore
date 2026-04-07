<?php

/**
 * Construye URL de listado limpiando parámetros de «ficha» lateral y aplicando sustituciones.
 *
 * @param string $script Nombre del PHP de listado
 * @param array<string,mixed> $parametros_get Estado actual de la query
 * @param array<int,string> $claves_ficha Claves GET que se omiten al volver al listado (detalle abierto)
 * @param array<string,mixed> $fusion Valores finales por clave; null elimina
 */
function listado_publico_url_con_ficha($script, array $parametros_get, array $claves_ficha, array $fusion) {
    $parametros = $parametros_get;
    unset($parametros['pagina']);
    foreach ($claves_ficha as $clave) {
        unset($parametros[$clave]);
    }
    foreach ($fusion as $clave => $valor) {
        if ($valor === null) {
            unset($parametros[$clave]);
        } else {
            $parametros[$clave] = $valor;
        }
    }
    $query = http_build_query($parametros);
    return $script . ($query !== '' ? '?' . $query : '');
}

// Intersección entre valores permitidos por datos reales y los enviados por el usuario (evita filtros fantasma)
function listado_publico_filtros_calificacion_validos(array $opciones_cal, array $solicitud) {
    $permitidos = array_column($opciones_cal, 'valor');
    return array_values(array_intersect($permitidos, $solicitud));
}

?>
