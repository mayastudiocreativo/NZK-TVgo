<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

function clean($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// Si ya está logueado, al panel
if (isset($_SESSION['user_id'])) {
    header('Location: admin-panel.php');
    exit;
}

$error        = '';
$selectedUser = '';

// Obtener lista de usuarios registrados para mostrarlos
$usersStmt = $pdo->query("SELECT id, username, role FROM cms_users ORDER BY username ASC");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Manejo del login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['user'] ?? '');
    $password     = trim($_POST['pass'] ?? '');
    $selectedUser = $username;

    if (mb_strlen($username) > 100 || mb_strlen($password) > 1000) {
        $error = 'Datos de acceso inválidos.';
    } elseif ($username !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM cms_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = $user['role'] ?? 'editor';

            header('Location: admin-panel.php');
            exit;
        } else {
            usleep(300000);
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Selecciona un usuario y escribe la contraseña.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login CMS – NZK tvGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 🔗 solo enlazamos admin.css -->
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page admin-login">

<div class="login-card">
    <header class="login-header">
        <div class="login-logo-circle">
            <!-- Cambia el src si tu logo tiene otro nombre o ruta -->
            <img src="img/iconIOS/icon.png" alt="NZK tvGo">
        </div>
        <div class="login-title-block">
            <h1>NZK tvGo · CMS</h1>
            <p>Sistema de gestión de programas y videos.</p>
        </div>
    </header>

    <p class="login-section-title">Selecciona tu usuario</p>
    <p class="login-section-subtitle">
        Elige un usuario del CMS y luego ingresa la contraseña.
    </p>

    <?php if (!empty($users)): ?>
        <div class="user-list">
            <?php foreach ($users as $u): ?>
                <?php
                    $uname   = $u['username'];
                    $initial = strtoupper(mb_substr($uname, 0, 2, 'UTF-8'));
                    $role    = ($u['role'] === 'admin') ? 'Administrador' : 'Editor';
                ?>
                <button type="button"
                        class="user-card"
                        data-username="<?= clean($uname) ?>">
                    <div class="user-avatar"><?= clean($initial) ?></div>
                    <div class="user-meta">
                        <span class="user-name"><?= clean($uname) ?></span>
                        <span class="user-role"><?= clean($role) ?></span>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-users">Aún no hay usuarios registrados en el CMS.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="admin-login.php" autocomplete="off">
        <!-- usuario seleccionado -->
        <input type="hidden" id="user" name="user" value="<?= clean($selectedUser) ?>">

        <p class="login-helper">
            Usuario seleccionado:
            <strong id="selectedUserLabel">
                <?= $selectedUser ? clean($selectedUser) : 'Selecciona un usuario de la lista' ?>
            </strong>
        </p>

        <label for="pass">Contraseña</label>
        <input type="password" id="pass" name="pass" required>

        <button type="submit" class="btn-primary btn-login">Ingresar</button>
    </form>

    <p class="login-footer">
        NZK Televisión · Uso interno
    </p>
</div>

<script>
(function() {
    const userCards = document.querySelectorAll('.user-card');
    const userInput = document.getElementById('user');
    const selectedLabel = document.getElementById('selectedUserLabel');
    const passInput = document.getElementById('pass');

    function setSelected(username) {
        if (!userInput || !selectedLabel) return;
        userInput.value = username || '';
        selectedLabel.textContent = username || 'Selecciona un usuario de la lista';

        userCards.forEach(card => {
            card.classList.toggle('is-selected', card.dataset.username === username);
        });
    }

    userCards.forEach(card => {
        card.addEventListener('click', () => {
            const username = card.dataset.username || '';
            setSelected(username);
            if (passInput) passInput.focus();
        });
    });

    window.addEventListener('DOMContentLoaded', () => {
        const current = userInput ? userInput.value : '';
        if (current) {
            setSelected(current);
        } else if (userCards.length > 0) {
            setSelected(userCards[0].dataset.username || '');
        }
    });
})();
</script>

</body>
</html>
