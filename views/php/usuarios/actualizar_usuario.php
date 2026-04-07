<?php
/**
 * Procesador de formularios de usuario: alta (?crear=1), actualización con id, o solo cambio de contraseña.
 * Valida longitud y coincidencia de contraseñas; redirige al referer o a listados según rol.
 */
session_start();

include "./usuarioModel.php";
// Identificador para actualización de fila existente (panel admin)
if (isset($_POST['id_usuario'])) {
    $id = $_POST['id_usuario'];
}
// Campos comunes enviados desde registro o edición (rol por defecto cliente = 2)
if (isset($_POST['email']) && isset($_POST['password'])) {
    $contrasena = $_POST['password'] ?? $_POST['new_password'];
    $confirmar_contrasena = $_POST['confirm_password'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['email'];
    $id_rol = $_POST['rol_id'] ?? 2;
}
if (isset($contrasena)) {

    // Política mínima de complejidad antes de tocar la base de datos
    if (strlen($contrasena) < 8) {
        $_SESSION['login_error'] = 'La contraseña debe tener al menos 8 caracteres.';
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
if (isset($confirmar_contrasena)) {

    // Username tomado de sesión o del POST según el flujo del formulario
    if ($contrasena === $confirmar_contrasena) {
        $usuario = $_SESSION['username'] ?? $_POST['username'];;
    } else {
        $_SESSION['login_error'] = 'Ambas contraseñas no coinciden';
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Rama: registro desde tienda u alta con rol explícito
if (isset($_GET['crear'])) {
    $crear_datos = crear_usuario_rol($nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $crear_datos;
} elseif (isset($id)) {
    // Rama: edición completa en panel
    $actualizar_datos = actualizar_usuario($id, $nombre, $apellido, $usuario, $correo, $contrasena, $id_rol);
    $succes = $actualizar_datos;
} elseif ($contrasena) {
    // Rama: solo hash nuevo conservando el mismo username
    $actualizar_datos = actualizar_contraseña($usuario, $contrasena);
    if ($actualizar_datos) {
        header('location: ./editar_usuario_cliente.php');
        exit();
    }
}

// Post-proceso: admin vuelve al ABM; resto al login de la tienda
if ($succes) {
    if ($_SESSION['rol'] === 1) {
        header('Location: usuario.php');
        exit();
    }
    header('Location: ../../login.php');
    exit();
} else {
    $_SESSION ['login_error'] = 'Se ha producido un error al procesar los datos. Inténtalo de nuevo más tarde.';
    header('Location: '. $_SERVER['HTTP_REFERER']);
    exit();
    
}
