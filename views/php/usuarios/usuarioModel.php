<?php
include "../conexion/conexion.php";

function crear_usuario($nombre, $apellido, $username, $email, $password_hash)
{
    $conn = connection();
    $sql = mysqli_prepare($conn, 'INSERT INTO usuarios (nombre, apellido, username, email, password_hash, rol_id) VALUES (?, ?, ?, ?, ?, 2)');
    mysqli_stmt_bind_param($sql, 'sssss', $nombre, $apellido, $username, $email, $password_hash);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function crear_usuario_rol($nombre, $apellido, $username, $email, $password_hash,$id_rol)
{
    $conn = connection();
    $sql = mysqli_prepare($conn, 'INSERT INTO usuarios (rol_id, nombre, apellido, username, email, password_hash) VALUES (?, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($sql, 'isssss', $id_rol, $nombre, $apellido, $username, $email, $password_hash);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function actualizar_usuario($id, $nombre, $apellido, $usuario, $correo, $contrasena, $id_rol)
{
    $conn = connection();
    $sql = mysqli_prepare($conn, 'UPDATE usuarios SET nombre = ?, apellido = ?, username = ?, email = ?, password_hash = ?, rol_id = ? WHERE id_usuario = ?');
    mysqli_stmt_bind_param($sql, 'sssssis', $nombre, $apellido, $usuario, $correo,$contrasena, $id_rol, $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

function eliminar_usuario($id)
{
    $conn = connection();
    $sql = mysqli_prepare($conn, 'DELETE FROM usuarios WHERE id_usuario = ?');
    mysqli_stmt_bind_param($sql, 'i', $id);
    $resultado = mysqli_stmt_execute($sql);
    return $resultado;
}

// Consultas

function consultar_usuarios()
{
    $conn = connection();
    $sql = mysqli_query($conn, 'SELECT * FROM usuarios;');
    return $sql;
}

function consultar_usuarios_id($id)
{
    $conn = connection();
    $sql =  mysqli_prepare($conn,'SELECT * FROM usuarios WHERE id_usuario = ?;');
    mysqli_stmt_bind_param($sql, 'i', $id);
    mysqli_stmt_execute($sql);
    $resultado = mysqli_stmt_get_result($sql); 
    return mysqli_fetch_assoc($resultado);
}

function consultar_usuarios_rol()
{
    $conn = connection();
    $sql = mysqli_query($conn, 'SELECT U.*, R.nombre AS nombre_rol FROM usuarios as U LEFT JOIN roles as R ON U.rol_id = R.id_rol;');
    return $sql;
}


if (isset($_GET['eliminar'])){
    eliminar_usuario($_GET['eliminar']);
    header("location: usuario.php");
}
?>

