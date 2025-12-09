<?php
// includes/security.php

// Asegurarnos de que la sesión esté inicializada usando la configuración segura
// de includes/session.php (cookie params, httponly, secure, samesite, etc.).
require_once __DIR__ . '/session.php';

/**
 * Genera y devuelve el token CSRF actual.
 *
 * @return string
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Imprime el campo hidden con el token CSRF dentro de un <form>.
 *
 * Uso:
 * <form method="post">
 *     <?php csrf_field(); ?>
 *     ...
 * </form>
 */
function csrf_field(): void
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verifica el token CSRF en peticiones POST.
 * Llama a esta función al inicio de scripts que procesan formularios (después de cargar la sesión).
 *
 * Si el token no es válido, responde con 403 y detiene la ejecución.
 */
function csrf_verify(): void
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method !== 'POST') {
        return;
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postedToken  = $_POST['csrf_token'] ?? '';

    if (!$sessionToken || !$postedToken || !hash_equals($sessionToken, $postedToken)) {
        http_response_code(403);
        exit('Solicitud no válida (CSRF detectado).');
    }
}
