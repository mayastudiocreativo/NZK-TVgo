<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

$currentUser = $_SESSION['user_name'] ?? 'Usuario';
$currentRole = $_SESSION['user_role'] ?? 'editor';
$initials    = strtoupper(substr($currentUser, 0, 2));

function clean($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del CMS – NZK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/admin.css">

</head>
<body class="admin-page admin-panel">

<header class="cms-topbar">
    <div class="cms-title-block">
        <h1>Panel del CMS – NZK</h1>
        <span>Elige qué quieres registrar o configurar.</span>
    </div>

    <div class="cms-user-box">
        <div class="cms-avatar"><?= clean($initials) ?></div>
        <div class="cms-user-text">
            <span class="cms-user-name"><?= clean($currentUser) ?></span>
            <span class="cms-user-role"><?= $currentRole === 'admin' ? 'Administrador' : 'Editor' ?></span>
        </div>
        <a href="admin-logout.php" class="btn-secondary">Cerrar sesión</a>
    </div>
</header>

<main class="panel-grid">

    <article class="panel-card">
        <div>
            <h2>Ingresar Programa</h2>
            <p>Crear o editar la ficha de cada programa. Esto alimenta las cards del inicio.</p>
        </div>
        <div class="panel-card-footer">
            <a href="admin-programas.php" class="btn-primary">📺 Gestionar programas</a>
        </div>
    </article>

    <article class="panel-card">
        <div>
            <h2>Ingresar Contenido</h2>
            <p>Subir y gestionar videos individuales (noticias, episodios, eventos, etc.).</p>
        </div>
        <div class="panel-card-footer">
            <a href="admin-videos.php" class="btn-primary">🎬 Gestionar contenido</a>
        </div>
    </article>

    <!-- NUEVA OPCIÓN: CARD DE PROMOCIÓN -->
    <article class="panel-card">
        <div>
            <h2>Ingresar card de promoción</h2>
            <p>
                Configura la tarjeta <strong>“Muy pronto”</strong> que se muestra en la página En vivo
                para promocionar una serie, película o novela.
            </p>
        </div>
        <div class="panel-card-footer">
            <a href="admin-promo.php" class="btn-primary">🌟 Gestionar card de promoción</a>
        </div>
    </article>
    <article class="panel-card">
    <div>
        <h2>Parrilla de programación</h2>
        <p>Editar los bloques horarios de la programación diaria (Lunes a Domingo).</p>
    </div>
    <div class="panel-card-footer">
        <a href="admin-parrilla.php" class="btn-primary">📺 Editar parrilla</a>
    </div>
</article>


    <?php if ($currentRole === 'admin'): ?>
    <article class="panel-card">
        <div>
            <h2>Usuarios del CMS</h2>
            <p>Crear y administrar usuarios (solo administradores).</p>
        </div>
        <div class="panel-card-footer">
            <a href="admin-users.php" class="btn-primary">👤 Gestionar usuarios</a>
        </div>
    </article>
    
    <?php endif; ?>

</main>

</body>
</html>
