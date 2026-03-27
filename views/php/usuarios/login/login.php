<?php 
include_once "../usuarioModel.php";
session_start();

function iniciar_sesion(){
    if ((isset($_POST['email']) || isset($_POST['username'])) && isset($_POST['password'])){
        if ($_POST['email'] != null){
            $consulta = consultar_usuarios_correo($_POST['email']);
            if (!$consulta){
                echo 'correo incorrecto';
                return;
            }
            if ($consulta['password_hash'] != $_POST ['passowrd']) {
                echo 'contraseña incorrecta';
                return;
            }

            $_SESSION['rol'] = $consulta['rol'];
            $_SESSION['nombre'] = $consulta['nombre'];
            $_SESSION['email'] = $consulta['email'];
            $_SESSION['username'] = $consulta['username'];
            $_SESSION['password'] = $consulta['password_hash'];
            echo 'inicio de sesion exitoso';
        }
        if ($_POST['username'] != null){
            $consulta = consultar_usuarios_nombreUsuario($_POST['username']);
            if (!$consulta){
                echo 'nombre de usuario incorrecto';
                return;
            }
            if ($consulta['password_hash'] != $_POST ['passowrd']) {
                echo 'contraseña incorrecta';
                return;
            }

            $_SESSION['rol'] = $consulta['rol'];
            $_SESSION['nombre'] = $consulta['nombre'];
            $_SESSION['email'] = $consulta['email'];
            $_SESSION['username'] = $consulta['username'];
            $_SESSION['password'] = $consulta['password_hash'];
            echo 'inicio de sesion exitoso';
            return;
        }
    }
    echo 'error datos nulos';
    return;
}

if (isset($_GET['iniciar'])){
    iniciar_sesion();
}
?>