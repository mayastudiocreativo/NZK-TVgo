<?php
// includes/db.php

// Cargar config
$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    die('Falta el archivo includes/config.php. Copia config.example.php y configura tus credenciales.');
}

require $configFile;

$DB_HOST = $DB_CONFIG['host'] ?? 'localhost';
$DB_NAME = $DB_CONFIG['name'] ?? 'db_tvgo';
$DB_USER = $DB_CONFIG['user'] ?? 'root';
$DB_PASS = $DB_CONFIG['pass'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // En producción NO muestres el mensaje completo
    error_log('Error de conexión a BD: ' . $e->getMessage());
    die('Error de conexión a la base de datos.');
}
