<?php
/**
 * Modelo de usuarios: registro, actualización, búsquedas y métricas del panel.
 * Las contraseñas deben llegar ya hasheadas desde la capa de presentación o controlador.
 */

require_once __DIR__ . "/../conexion/conexion.php";
if (!isset($BD)){
    $BD = connection() ;
}

// Registro de cliente: fija rol_id = 2 (cliente) según el esquema actual
function crear_usuario($nombre, $apellido, $username, $email, $password_hash)
{
    global $BD;
    $sql = mysqli_prepare($BD, 'INSERT INTO usuarios (nombre, apellido, username, email, password_hash, rol_id) VALUES (?, ?, ?, ?, ?, 2)');
    mysqli_stmt_bind_param($sql, 'sssss', $nombre, $apellido, $username, $email, $password_hash);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

// Alta de usuario con rol explícito (administración: staff u otros roles)
function crear_usuario_rol($nombre, $apellido, $username, $email, $password_hash,$id_rol)
{
    global $BD;
    $sql = mysqli_prepare($BD, 'INSERT INTO usuarios (rol_id, nombre, apellido, username, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($sql, 'isssss', $id_rol, $nombre, $apellido, $username, $email, $password_hash);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

// Actualiza perfil completo incluyendo hash de contraseña y rol
function actualizar_usuario($id, $nombre, $apellido, $usuario, $correo, $contrasena, $id_rol)
{
    global $BD;
    $sql = mysqli_prepare($BD, 'UPDATE usuarios SET nombre = ?, apellido = ?, username = ?, email = ?, password_hash = ?, rol_id = ? WHERE id_usuario = ?');
    mysqli_stmt_bind_param($sql, 'sssssis', $nombre, $apellido, $usuario, $correo,$contrasena, $id_rol, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

// Cambio de contraseña identificando al usuario por nombre de usuario
function actualizar_contraseña ($username, $password){
    global $BD;
    $sql = mysqli_prepare($BD,'UPDATE usuarios SET password_hash = ? WHERE username = ?' );
    mysqli_stmt_bind_param($sql, 'ss', $password, $username);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_usuario($id)
{
    global $BD;
    // Desactiva comprobación de claves foráneas mientras se elimina la fila (evita error si hay referencias mal gestionadas)
    mysqli_query($BD,"SET FOREIGN_KEY_CHECKS = 0");
    $sql = mysqli_prepare($BD, 'DELETE FROM usuarios WHERE id_usuario = ?');
    mysqli_stmt_bind_param($sql, 'i', $id);
    $resultado = mysqli_stmt_execute($sql);
    mysqli_query($BD,"SET FOREIGN_KEY_CHECKS = 1");
    return $resultado;
}

// --- Lecturas y agregados para listados y reportes ---

// Resultado mysqli con todas las filas de usuarios (el llamador itera)
function consultar_usuarios()
{
    global $BD;
    $sql = mysqli_query($BD, 'SELECT * FROM usuarios;');
    return $sql;
}

// Últimos registros ordenados por fecha de creación (widgets del dashboard)
function consultar_usuarios_recientes( $limit = 5)
{
    global $BD;
    $sql = mysqli_query($BD, "SELECT * FROM usuarios ORDER BY creado_en DESC LIMIT $limit;");
    return $sql;
}

// Detalle de un usuario por id
function consultar_usuarios_id($id)
{
    global $BD;
    $sql =  mysqli_prepare($BD,'SELECT * FROM usuarios WHERE id_usuario = ?;');
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado);
}

// Busca usuario por email (inicio de sesión, recuperación o validación de duplicados)
function consultar_usuarios_correo($email)
{
    global $BD;
    $sql =  mysqli_prepare($BD,'SELECT * FROM usuarios WHERE email = ?;');
    mysqli_stmt_bind_param($sql, 's', $email);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado);
}

// Busca usuario por nombre de usuario único
function consultar_usuarios_nombreUsuario($username)
{
    global $BD;
    $sql =  mysqli_prepare($BD,'SELECT * FROM usuarios WHERE username = ?;');
    mysqli_stmt_bind_param($sql, 's', $username);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado);
}

// Listado con nombre de rol resuelto por JOIN (vista de administración de usuarios)
function consultar_usuarios_rol()
{
    global $BD;
    $sql = mysqli_query($BD, 'SELECT U.*, R.nombre AS nombre_rol FROM usuarios as U LEFT JOIN roles as R ON U.rol_id = R.id_rol;');
    return $sql;
}

// Conteo total de filas en usuarios
function total_usuarios()
{
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) as total FROM usuarios');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

// Altas de usuario en el mes calendario actual (métrica del dashboard)
function usuarios_mes()
{
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) as total FROM usuarios WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE())');
    $row = mysqli_fetch_assoc($sql);
    return $row['total'];
}

// Usuarios cuyo rol se denomina 'cliente' en la tabla roles
function total_clientes()
{
    global $BD;
    $sql = mysqli_query($BD, "
        SELECT COUNT(*) AS total
        FROM usuarios u
        INNER JOIN roles r ON r.id_rol = u.rol_id AND r.nombre = 'cliente'
    ");
    $row = mysqli_fetch_assoc($sql);
    return (int) ($row['total'] ?? 0);
}

// Conteo de pedidos por usuario específico (para dashboard y reportes)
function pedidos_por_usuario($id_usuario) {
    global $BD;
    $sql = mysqli_query($BD, 'SELECT COUNT(*) as total FROM pedidos WHERE usuario_id = ' . (int)$id_usuario);
    $row = mysqli_fetch_assoc($sql);
    return (int)$row['total'];
}

// Invocación directa desde la URL del panel: ?eliminar=id redirige tras borrar
function buscar_usuarios($busqueda) {
    global $BD;
    $busq = "%$busqueda%";
    $sql = mysqli_prepare($BD, "SELECT U.*, R.nombre AS nombre_rol FROM usuarios U LEFT JOIN roles R ON U.rol_id = R.id_rol WHERE CONCAT(U.nombre, ' ', U.apellido, ' ', U.username, ' ', U.email) LIKE ?");
    mysqli_stmt_bind_param($sql, 's', $busq);
    mysqli_stmt_execute($sql);
    return mysqli_stmt_get_result($sql);
}

if (isset($_GET['eliminar'])){
    eliminar_usuario($_GET['eliminar']);
    header("location: usuario.php");
}

?>

