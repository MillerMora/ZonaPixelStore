<?php

/**
 * Pinta la franja de paginación (anterior, números, siguiente) manteniendo la query actual.
 * Los parámetros extra se mezclan con $_GET y sustituyen valores homónimos; siempre se reinicia la página al cambiar filtros vía $parametros_extra.
 *
 * @param string $ruta_base Ruta del script destino, p. ej. catalogo.php
 * @param int $pagina_actual Página actual (base 1)
 * @param int $total_elementos Total de ítems que cumplen el filtro
 * @param int $por_pagina Tamaño de página
 * @param array<string,mixed> $parametros_extra Pares clave-valor aplicados sobre $_GET
 */
function publico_renderizar_paginacion($ruta_base, $pagina_actual, $total_elementos, $por_pagina, array $parametros_extra = []) {
    $total_paginas = (int) max(1, ceil($total_elementos / max(1, $por_pagina)));
    $pagina_actual = (int) max(1, min($pagina_actual, $total_paginas));

    $query = array_merge($_GET, $parametros_extra);
    unset($query['pagina']);

    // Cierre sobre $query: reutiliza filtros vigentes y solo cambia el índice de página
    $construir = function ($num_pagina) use ($ruta_base, $query) {
        $query['pagina'] = $num_pagina;
        $qs = http_build_query($query);
        return htmlspecialchars($ruta_base . ($qs !== '' ? '?' . $qs : ''), ENT_QUOTES, 'UTF-8');
    };

    if ($total_paginas <= 1) {
        return;
    }

    echo '<div class="pagination-wrap">';
    if ($pagina_actual > 1) {
        echo '<a class="page-btn" href="' . $construir($pagina_actual - 1) . '" aria-label="Anterior"><i class="fas fa-chevron-left"></i></a>';
    } else {
        echo '<span class="page-btn" style="opacity:.4;pointer-events:none"><i class="fas fa-chevron-left"></i></span>';
    }

    // Números cercanos a la página actual, más primera y última; elipsis entre segmentos
    $rango = 2;
    for ($n = 1; $n <= $total_paginas; $n++) {
        if ($n === 1 || $n === $total_paginas || ($n >= $pagina_actual - $rango && $n <= $pagina_actual + $rango)) {
            $clase = $n === $pagina_actual ? 'page-btn active' : 'page-btn';
            echo '<a class="' . $clase . '" href="' . $construir($n) . '">' . (int) $n . '</a>';
        } elseif ($n === $pagina_actual - $rango - 1 || $n === $pagina_actual + $rango + 1) {
            echo '<span class="page-btn" style="border:none;cursor:default">…</span>';
        }
    }

    if ($pagina_actual < $total_paginas) {
        echo '<a class="page-btn" href="' . $construir($pagina_actual + 1) . '" aria-label="Siguiente"><i class="fas fa-chevron-right"></i></a>';
    } else {
        echo '<span class="page-btn" style="opacity:.4;pointer-events:none"><i class="fas fa-chevron-right"></i></span>';
    }
    echo '</div>';
}

// Normaliza un parámetro GET repetible (checkboxes) a lista de enteros positivos
function publico_parametros_get_array($clave) {
    if (!isset($_GET[$clave])) {
        return [];
    }
    $raw = $_GET[$clave];
    if (!is_array($raw)) {
        $raw = [$raw];
    }
    return array_values(array_filter(array_map('intval', $raw), function ($v) {
        return $v > 0;
    }));
}

// Término de búsqueda recortado y seguro para pasar a capa modelo (clave por defecto «q»)
function publico_texto_busqueda_get($clave = 'q') {
    if (!isset($_GET[$clave]) || !is_string($_GET[$clave])) {
        return '';
    }
    return trim($_GET[$clave]);
}

?>
