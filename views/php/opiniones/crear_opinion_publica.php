<?php

/**
 * Handler público para crear opiniones desde nueva-opinion.php.
 * Requiere login, sets aprobada=0 (pendiente moderación).
 */
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['rol'])) {
    $_SESSION['index_login_error'] = 'Debes iniciar sesión para enviar opiniones.';
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/opinionModel.php';
require_once __DIR__ . '/../usuarios/usuarioModel.php';
$usuario = consultar_usuarios_nombreUsuario($_SESSION['username']);
if (!$usuario || !isset($usuario['id_usuario']) || $usuario['id_usuario'] < 1) {
    $_SESSION['error'] = 'Usuario no encontrado. Login de nuevo.';
    header('Location: ../../nueva-opinion.php');
    exit;
}
$usuario_id = (int) $usuario['id_usuario'];

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = (int) ($_POST['producto_id'] ?? 0);
    $plataforma_id = !empty($_POST['plataforma_id']) ? (int) $_POST['plataforma_id'] : null;
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $calificacion = (int) ($_POST['calificacion'] ?? 0);

    if ($producto_id < 1 || empty($titulo) || empty($contenido) || $calificacion < 1 || $calificacion > 5) {
        $_SESSION['error'] = 'Datos inválidos.';
        header('Location: ../../nueva-opinion.php');
        exit;
    }

    $aprobada = 0; // Pendiente moderación
    $success = crear_opinion($usuario_id, $producto_id, $plataforma_id, $titulo, $contenido, $calificacion, $aprobada);

    if ($success) {
        $_SESSION['success'] = '¡Opinión enviada! Espera aprobación.';
        if (isset($_GET['producto_id'])) {
            header('Location: ../../producto.php?id=' . $producto_id);
            exit;
        } else {
            header('Location: ../../opiniones.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Error al enviar opinión. Intenta de nuevo.';
        header('Location: ../../nueva-opinion.php' . ($_GET['id'] ?? ''));
    }
    exit;
}
?>

