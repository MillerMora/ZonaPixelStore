<?php

require_once __DIR__ . '/../conexion/conexion.php';
if (!isset($BD)){
    $BD = connection() ;
}
// consulta
function consultar_resenas(){

    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM resenas');
    return $sql;
    }
    
function consultar_resenas_id($id){
    global $BD;
    $sql = mysqli_prepare($BD, "SELECT * FROM resenas WHERE id_resena = ?");
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql);
    return $resultado; 
}

// CRUD
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

if (isset($_GET['eliminar'])){
    eliminar_resena($_GET['eliminar']);
    header("location: resenas.php");
}

?>
