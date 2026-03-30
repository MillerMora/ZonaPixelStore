<?php
include_once "../usuarioModel.php";
session_start();

function iniciar_sesion($usuario, $password)
{
    if (empty($usuario) || empty($password)) {
        $_SESSION['index_login_error'] = 'Error en el sistema de login. Campos vacíos. Intenta más tarde.';
        return header('location: ../../../../index.php');
    }

    if (filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
        $consulta = consultar_usuarios_correo($usuario);
        if (!$consulta) {
            $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
            return header('location: ../../../login.php');
        }
        if ($consulta['password_hash'] != $password) {
            $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
            return header('location: ../../../login.php');
        }
    } else {
        $consulta = consultar_usuarios_nombreUsuario($usuario);
        if (!$consulta) {
            $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
            return header('location: ../../../login.php');
        }
        if ($consulta['password_hash'] != $password) {
            $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
            return header('location: ../../../login.php');
        }
    }

    $_SESSION['rol'] = $consulta['rol_id'];
    $_SESSION['nombre'] = $consulta['nombre'];
    $_SESSION['email'] = $consulta['email'];
    $_SESSION['username'] = $consulta['username'];
    $_SESSION['password'] = $consulta['password_hash'];
    return header('location: ../../../../index.php');
}

function cerrar_sesion (){

    session_unset();

    session_destroy();

    return header('location: ../../../../index.php');

}
if (isset($_GET['iniciar'])) {
    iniciar_sesion($_POST['login'], $_POST['password']);
}
if (isset($_GET['cerrar'])) {
    cerrar_sesion();
}
