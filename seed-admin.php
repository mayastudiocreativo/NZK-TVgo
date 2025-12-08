<?php
require __DIR__ . '/includes/db.php';

// Cambia estos datos a lo que quieras
$name     = 'Administrador NZK';
$username = 'admin';
$password = 'Admin1234'; // la contraseña en texto plano
$role     = 'admin';

// Generar hash seguro
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
  INSERT INTO cms_users (name, username, password_hash, role)
  VALUES (?, ?, ?, ?)
");
$stmt->execute([$name, $username, $hash, $role]);

echo "Usuario admin creado. Usuario: $username - Clave: $password";