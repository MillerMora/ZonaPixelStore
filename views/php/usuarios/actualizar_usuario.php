<?php
session_start();

include "./usuarioModel.php";
if (isset($_POST['id_usuario'])){
    $id = $_POST['id_usuario'];
}
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$usuario = $_POST['username'];
$correo = $_POST['email'];
if ($_SESSION['rol'] === 1 || $_SESSION['rol'] === 'admin' ||($_POST['password'] === $_POST['confirm_password']) && (strlen($_POST['password']) >= 8 && strlen($_POST['confirm_password']) >= 8  ) ){
    $contrasena = $_POST['password'];
}
$id_rol = $_POST['rol_id'] ?? 2;


$succes;
if (isset($_GET['crear'])){
    $crear_datos = crear_usuario_rol($nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $crear_datos;
} elseif(isset($id)){
    $actualizar_datos = actualizar_usuario($id, $nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $actualizar_datos;
}

if ($succes) {
    if ($_SESSION['rol'] === 1 || $_SESSION['rol'] === 'admin'){
        header('Location: usuario.php');
        exit;
    }
    header('Location: ../../login.php');
    exit;
} else {
    echo "Error al actualizar el usuario";
}
?>

