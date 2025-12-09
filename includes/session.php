<?php
// includes/session.php

// Solo configurar la sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {

    // Detectar si la conexión es HTTPS
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );

    // Configuración segura de la cookie de sesión
    session_set_cookie_params([
        'lifetime' => 0,        // La sesión dura hasta cerrar el navegador
        'path'     => '/',
        'domain'   => '',       // Vacío normalmente está bien (dominio actual)
        'secure'   => $isHttps, // TRUE solo si usas HTTPS
        'httponly' => true,     // No accesible vía JavaScript
        'samesite' => 'Lax',    // 'Strict' si quieres ser más estricto
    ]);

    // Opcional: nombre personalizado de la sesión
    // session_name('NZKSESSID');

    session_start();
}
