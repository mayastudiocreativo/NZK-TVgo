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

<style>
    body {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #050b18;
        color: #f5f7ff;
        margin: 0;
        padding: 1.8rem 1.5rem 2.5rem;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* TOPBAR */
    .cms-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        max-width: 1080px;
        width: 100%;
        margin: 0 auto 1.8rem;
    }
    .cms-title-block h1 {
        margin: 0;
        font-size: 1.6rem;
    }
    .cms-title-block span {
        font-size: 0.9rem;
        color: #9ca3af;
    }
    .cms-user-box {
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .cms-avatar {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: radial-gradient(circle at top left,#4f46e5,#020617);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid rgba(148,163,184,0.6);
    }
    .cms-user-text { display: flex; flex-direction: column; }
    .cms-user-name { font-size: 0.9rem; }
    .cms-user-role { font-size: 0.75rem; color: #9ca3af; }

    .btn-secondary {
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,0.7);
        background: transparent;
        color: #e5e7eb;
        font-size: 0.8rem;
        text-decoration: none;
        white-space: nowrap;
    }

    /* CONTENEDOR CENTRAL */
    .panel-grid {
        max-width: 720px;
        width: 100%;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* TARJETAS */
    .panel-card {
        background: radial-gradient(circle at top left, #0f172a, #020617 85%);
        border-radius: 1.1rem;
        padding: 1.4rem 1.5rem 1.3rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.2rem;
        box-shadow: 0 18px 45px rgba(15,23,42,0.8);
        transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }
    .panel-card:hover {
        border-color: rgba(96,165,250,0.8);
        transform: translateY(-2px);
        box-shadow: 0 22px 50px rgba(15,23,42,0.95);
        background: radial-gradient(circle at top left, #111827, #020617 90%);
    }
    .panel-card h2 {
        margin: 0 0 0.35rem;
        font-size: 1.05rem;
    }
    .panel-card p {
        margin: 0;
        font-size: 0.9rem;
        color: #9ca3af;
        max-width: 420px;
    }

    .panel-card-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    .btn-primary {
        padding: 0.55rem 1.2rem;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        background: #2563eb;
        color: #f9fafb;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        box-shadow: 0 10px 30px rgba(37,99,235,0.45);
        transition: transform 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
    }
    .btn-primary:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 12px 34px rgba(37,99,235,0.65);
    }

    @media (max-width: 768px) {
        .cms-topbar {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.9rem;
        }
        .panel-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .panel-card-footer {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

</head>
<body>

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
