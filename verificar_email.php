<?php
session_start();
require_once 'classes/Database.php';

$pdo = Database::getInstance()->getConnection();
$mensaje = '';
$tipo = 'danger';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("
        SELECT id_cuenta, token_expiracion, email_verificado 
        FROM cuentas 
        WHERE token_verificacion = ?
    ");
    $stmt->execute([$token]);
    $cuenta = $stmt->fetch();

    if (!$cuenta) {
        $mensaje = 'El enlace de verificación no es válido.';
    } elseif ($cuenta['email_verificado']) {
        $mensaje = 'Tu cuenta ya fue verificada anteriormente. Puedes iniciar sesión.';
        $tipo = 'success';
    } elseif (new DateTime() > new DateTime($cuenta['token_expiracion'])) {
        $mensaje = 'El enlace de verificación ha expirado. Regístrate de nuevo o solicita un nuevo enlace.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE cuentas 
            SET email_verificado = 1, token_verificacion = NULL, token_expiracion = NULL 
            WHERE id_cuenta = ?
        ");
        $stmt->execute([$cuenta['id_cuenta']]);
        $mensaje = '¡Cuenta verificada exitosamente! Ya puedes iniciar sesión.';
        $tipo = 'success';
    }
} else {
    $mensaje = 'Token de verificación no proporcionado.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Email - XHESS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4 text-center" style="max-width: 450px; width: 100%;">
        <img src="img/logo.png" alt="XHESS" style="width:60px;" class="mx-auto mb-3">
        <h4>XHESS - Verificación de Email</h4>
        <div class="alert alert-<?= $tipo ?> mt-3"><?= htmlspecialchars($mensaje) ?></div>
        <a href="index.php" class="btn btn-primary mt-2">Ir al Inicio</a>
    </div>
</body>
</html>