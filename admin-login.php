<?php
session_start();
require __DIR__ . '/includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: admin-videos.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['user'] ?? '');
    $password = trim($_POST['pass'] ?? '');

    if ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM cms_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role']; // 'admin' o 'editor'
            header('Location: admin-videos.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Completa usuario y contraseña.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login CMS – NZK Videos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #1e293b, #020617 60%);
            color: #f9fafb;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #020617;
            border-radius: 1.2rem;
            padding: 2rem 2.4rem;
            width: 100%;
            max-width: 380px;
            border: 1px solid rgba(148,163,184,0.35);
            box-shadow: 0 26px 55px rgba(15,23,42,0.85);
        }
        .login-title {
            margin: 0 0 0.25rem;
            font-size: 1.3rem;
            font-weight: 700;
        }
        .login-subtitle {
            margin: 0 0 1.4rem;
            font-size: 0.9rem;
            color: #9ca3af;
        }
        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            color: #cbd5f5;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.55rem 0.7rem;
            border-radius: 0.55rem;
            border: 1px solid rgba(148,163,184,0.4);
            background: #020617;
            color: #f9fafb;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        .btn-primary {
            width: 100%;
            padding: 0.6rem 0.9rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            background: linear-gradient(90deg,#ff4a4a,#ff9f3c,#4f8cff,#9b5bff);
            color: #020617;
            margin-top: 0.4rem;
        }
        .btn-primary:hover { filter: brightness(1.05); }
        .login-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.7);
            color: #fecaca;
            font-size: 0.8rem;
            padding: 0.45rem 0.6rem;
            border-radius: 0.5rem;
            margin-bottom: 0.8rem;
        }
        .login-footer {
            margin-top: 1.3rem;
            font-size: 0.78rem;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h1 class="login-title">Acceso al CMS</h1>
    <p class="login-subtitle">NZK Noticias en video / NZK Productora</p>

    <?php if ($error): ?>
        <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="admin-login.php">
        <label for="user">Usuario</label>
        <input type="text" id="user" name="user" required>

        <label for="pass">Contraseña</label>
        <input type="password" id="pass" name="pass" required>

        <button type="submit" class="btn-primary">Ingresar</button>
    </form>

    <p class="login-footer">
        NZK Televisión · Uso interno
    </p>
</div>

</body>
</html>