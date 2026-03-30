<?php
session_start();

include "./usuarioModel.php";
if (isset($_POST['id_usuario'])){
    $id = $_POST['id_usuario'];
}
if (isset($_POST['email']) && isset($_POST['password'])){
    $contrasena = $_POST['password'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['username'];
    $correo = $_POST['email'];
    $id_rol = $_POST['rol_id'] ?? 2;
}
if (isset($_POST['new_password']) && isset($_POST['confirm_password']) ){

    if ($_POST['new_password'] < 8){
        $_SESSION['password_error'] = 'la nueva contraseña debe ser mayor de 8 digitos';
        header('location: ./cambiar_contraseña.php');
        exit();
    }
    if ($_POST['new_password'] === $_POST['confirm_password']){
        $usuario = $_SESSION['username'];
        $contrasena = $_POST['new_password'];
    }
    else {
        $_SESSION['password_error'] = 'Ambas contraseñas no coinciden';
        header('location: ./cambiar_contraseña.php');
        exit();
    }

}

if (isset($_GET['crear'])){
    $crear_datos = crear_usuario_rol($nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $crear_datos;
} elseif(isset($id)){
    $actualizar_datos = actualizar_usuario($id, $nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $actualizar_datos;
} elseif ($contrasena) {
    $actualizar_datos = actualizar_contraseña($usuario,$contrasena);
    if ($actualizar_datos){
        header('location: ./editar_usuario_cliente.php');
        exit();
    }
}

if ($succes) {
    if ($_SESSION['rol'] === 1){
        header('Location: usuario.php');
        exit();
    }
    header('Location: ../../login.php');
    exit();
} else {
    echo "Error al actualizar el usuario";
}
?>

