<?php
include "./usuarioModel.php";
if (isset($_POST['id_usuario'])){
    $id = $_POST['id_usuario'];
}
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$usuario = $_POST['username'];
$correo = $_POST['email'];
$contrasena = $_POST['password'];
$id_rol = $_POST['rol_id'];


$succes;
if (isset($_GET['crear'])){
    $crear_datos = crear_usuario_rol($nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $crear_datos;
} elseif(isset($id)){
    $actualizar_datos = actualizar_usuario($id, $nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $actualizar_datos;
}

if ($succes) {
    header('Location: usuario.php');
    exit;
} else {
    echo "Error al actualizar el usuario";
}
?>

