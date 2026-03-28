<?php
include_once "../usuarioModel.php";
session_start();

function iniciar_sesion($usuario, $password)
{
    if (isset($usuario) && isset($password)) {
        if (filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
            $consulta = consultar_usuarios_correo($usuario);
            if (!$consulta) {
                echo 'correo incorrecto';
                return header('location: ../../../../index.php');
            }
            if ($consulta['password_hash'] != $password) {
                echo 'contraseña incorrecta';
                return header('location: ../../../../index.php');
            }

            $_SESSION['rol'] = $consulta['rol_id'];
            $_SESSION['nombre'] = $consulta['nombre'];
            $_SESSION['email'] = $consulta['email'];
            $_SESSION['username'] = $consulta['username'];
            $_SESSION['password'] = $consulta['password_hash'];
            echo 'inicio de sesion exitoso';
            return header('location: ../../../../index.php');
        }


        $consulta = consultar_usuarios_nombreUsuario($usuario);
        if (!$consulta) {
            echo 'nombre de usuario incorrecto';
            return header('location: ../../../../index.php') ;
        }
        if ($consulta['password_hash'] != $password) {
            echo 'contraseña incorrecta';
            return header('location: ../../../../index.php');
        }

        $_SESSION['rol'] = $consulta['rol_id'];
        $_SESSION['nombre'] = $consulta['nombre'];
        $_SESSION['email'] = $consulta['email'];
        $_SESSION['username'] = $consulta['username'];
        $_SESSION['password'] = $consulta['password_hash'];
        echo 'inicio de sesion exitoso';
        return header('location: ../../../../index.php');
    }
    echo 'error datos nulos';
    return header('location: ../../../../index.php');
}

function cerrar_sesion (){

    session_unset();

    session_destroy();

    return header('location: ../../../../index.php');

}
if (isset($_GET['iniciar'])) {
    iniciar_sesion($usuario= $_POST['login'], $password = $_POST['password']);
}
if (isset($_GET['cerrar'])) {
    cerrar_sesion();
}
