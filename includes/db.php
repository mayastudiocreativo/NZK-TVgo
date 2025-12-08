<?php
// includes/db.php

$DB_HOST = 'localhost';
$DB_NAME = 'db_tvgo';   // ← TU BASE DE DATOS
$DB_USER = 'root';      // ← en XAMPP normalmente es "root"
$DB_PASS = '';          // ← contraseña vacía en localhost

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}