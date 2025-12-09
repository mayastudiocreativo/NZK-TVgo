<?php
// admin-promo.php
require __DIR__ . '/includes/session.php';

// Solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';

// Verificación CSRF si hay POST
csrf_verify();

// Datos de usuario
$currentUser = $_SESSION['user_name'] ?? 'Usuario';
$currentRole = $_SESSION['user_role'] ?? 'editor';
$initials    = strtoupper(substr($currentUser, 0, 2));

function clean($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$error = '';

// ===== ELIMINAR PROMO (solo admin) =====
if (isset($_GET['delete']) && $currentRole === 'admin') {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM nzk_promos_live WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin-promo.php');
    exit;
}

// ===== CARGAR PARA EDITAR =====
$editingPromo = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM nzk_promos_live WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editingPromo = $stmt->fetch();
    }
}

// ===== GUARDAR (CREAR / EDITAR) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title     = trim($_POST['title'] ?? '');
    $timeLabel = trim($_POST['time_label'] ?? '');
    $category  = trim($_POST['category'] ?? '');
    $imageUrl  = trim($_POST['image_url'] ?? '');
    $linkUrl   = trim($_POST['link_url'] ?? '');

    if ($title === '') {
        $error = 'El título de la promoción es obligatorio.';
    } else {
        if ($id > 0) {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE nzk_promos_live
                SET title = ?, time_label = ?, category = ?, image_url = ?, link_url = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $timeLabel, $category, $imageUrl, $linkUrl, $id]);
        } else {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO nzk_promos_live (title, time_label, category, image_url, link_url)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $timeLabel, $category, $imageUrl, $linkUrl]);
        }

        header('Location: admin-promo.php');
        exit;
    }
}

// ===== LISTADO DE PROMOS =====
$stmt = $pdo->query("
    SELECT *
    FROM nzk_promos_live
    ORDER BY created_at DESC
    LIMIT 20
");
$promos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS – Card de promoción en vivo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #050b18;
            color: #f5f7ff;
            margin: 0;
            padding: 2rem;
        }
        .cms-topbar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:1.8rem;
        }
        .cms-title-block h1 {
            margin:0;
            font-size:1.4rem;
        }
        .cms-title-block span {
            font-size:0.85rem;
            color:#9ca3af;
        }
        .cms-user-box {
            display:flex;
            align-items:center;
            gap:0.7rem;
        }
        .cms-avatar {
            width:34px;height:34px;border-radius:999px;
            background:radial-gradient(circle at top left,#4f46e5,#020617);
            display:flex;align-items:center;justify-content:center;
            font-size:0.8rem;font-weight:700;
            border:1px solid rgba(148,163,184,0.6);
        }
        .cms-user-text { display:flex;flex-direction:column; }
        .cms-user-name { font-size:0.9rem; }
        .cms-user-role { font-size:0.75rem;color:#9ca3af; }
        .btn-secondary {
            padding:0.35rem 0.8rem;
            border-radius:999px;
            border:1px solid rgba(148,163,184,0.7);
            background:transparent;
            color:#e5e7eb;
            font-size:0.8rem;
            text-decoration:none;
            margin-left:0.4rem;
        }

        .layout {
            display:grid;
            grid-template-columns:minmax(0,1.1fr) minmax(0,1.3fr);
            gap:2rem;
        }
        @media (max-width:900px){
            body { padding:1.5rem 1rem; }
            .layout { grid-template-columns:1fr; }
            .cms-topbar { flex-direction:column;align-items:flex-start;gap:0.8rem; }
        }

        .card {
            background:#0b1020;
            border-radius:1rem;
            padding:1.2rem 1.5rem;
            border:1px solid rgba(255,255,255,0.08);
        }

        label {
            display:block;
            font-size:0.85rem;
            margin-bottom:0.25rem;
            color:#cbd5f5;
        }
        input[type="text"],
        textarea {
            width:100%;
            padding:0.5rem 0.65rem;
            border-radius:0.55rem;
            border:1px solid rgba(148,163,184,0.4);
            background:#020617;
            color:#f9fafb;
            font-size:0.9rem;
            margin-bottom:0.7rem;
        }
        textarea { min-height:70px; resize:vertical; }

        .btn-primary {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0.45rem 0.9rem;
            border-radius:999px;
            border:none;
            cursor:pointer;
            font-size:0.85rem;
            background:#4f8cff;
            color:#050b18;
            font-weight:600;
        }

        table {
            width:100%;
            border-collapse:collapse;
            font-size:0.85rem;
        }
        th,td {
            padding:0.4rem 0.5rem;
            border-bottom:1px solid rgba(31,41,55,0.9);
            text-align:left;
        }
        th { font-weight:600;color:#cbd5f5; }
        tr:hover td { background:rgba(15,23,42,0.7); }

        .thumb-mini {
            width:80px;height:45px;
            border-radius:6px;
            object-fit:cover;
            background:#111827;
        }
        .pill {
            display:inline-block;
            padding:0.16rem 0.6rem;
            border-radius:999px;
            background:rgba(148,163,184,0.25);
            font-size:0.72rem;
        }
        .actions a {
            margin-right:0.5rem;
            font-size:0.8rem;
            text-decoration:none;
        }
        .alert-error {
            background:rgba(248,113,113,0.1);
            border:1px solid rgba(248,113,113,0.8);
            color:#fecaca;
            padding:0.5rem 0.7rem;
            border-radius:0.5rem;
            font-size:0.8rem;
            margin-bottom:0.8rem;
        }
    </style>
</head>
<body>

<header class="cms-topbar">
    <div class="cms-title-block">
        <h1>Card de promoción – En vivo</h1>
        <span>Configura la tarjeta "Muy pronto" que aparece en la página En vivo.</span>
    </div>
    <div class="cms-user-box">
        <div class="cms-avatar"><?= clean($initials) ?></div>
        <div class="cms-user-text">
            <span class="cms-user-name"><?= clean($currentUser) ?></span>
            <span class="cms-user-role"><?= $currentRole === 'admin' ? 'Administrador' : 'Editor' ?></span>
        </div>
        <a href="admin-panel.php" class="btn-secondary">Volver al panel</a>
        <a href="admin-logout.php" class="btn-secondary">Cerrar sesión</a>
    </div>
</header>

<div class="layout">

    <!-- FORMULARIO -->
    <div class="card">
        <h2 style="margin-top:0;">
            <?= $editingPromo ? 'Editar promoción' : 'Nueva promoción' ?>
        </h2>

        <?php if ($error): ?>
            <div class="alert-error"><?= clean($error) ?></div>
        <?php endif; ?>

        <form method="post" action="admin-promo.php">
            <?php csrf_field(); ?>
            <input type="hidden" name="id"
                   value="<?= $editingPromo ? (int)$editingPromo['id'] : 0 ?>">

            <label for="title">Título de la card *</label>
            <input type="text" id="title" name="title" required
                   placeholder="Ej: Estreno de novela: Mi Camino es Amarte"
                   value="<?= $editingPromo ? clean($editingPromo['title']) : '' ?>">

            <label for="time_label">Texto de tiempo / franja</label>
            <input type="text" id="time_label" name="time_label"
                   placeholder="Ej: Próximamente, 21:00 - 22:00"
                   value="<?= $editingPromo ? clean($editingPromo['time_label']) : '' ?>">

            <label for="category">Categoría / tipo</label>
            <input type="text" id="category" name="category"
                   placeholder="Ej: Novela, Película, Serie"
                   value="<?= $editingPromo ? clean($editingPromo['category']) : '' ?>">

            <label for="image_url">URL de imagen</label>
            <input type="text" id="image_url" name="image_url"
                   placeholder="img/novelas/miCaminoEsAmarte.jpg"
                   value="<?= $editingPromo ? clean($editingPromo['image_url']) : '' ?>">

            <label for="link_url">URL al contenido (opcional)</label>
            <input type="text" id="link_url" name="link_url"
                   placeholder="Ej: video.php?slug=mi-novela-ep01"
                   value="<?= $editingPromo ? clean($editingPromo['link_url']) : '' ?>">

            <button type="submit" class="btn-primary">
                <?= $editingPromo ? 'Guardar cambios' : 'Crear promoción' ?>
            </button>

            <?php if ($editingPromo): ?>
                <a href="admin-promo.php" class="btn-secondary" style="margin-left:0.5rem;">
                    Cancelar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- LISTADO -->
    <div class="card">
        <h2 style="margin-top:0;">Promociones creadas</h2>

        <?php if (empty($promos)): ?>
            <p>No hay promociones registradas todavía.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Thumb</th>
                        <th>Título</th>
                        <th>Franja</th>
                        <th>Categoría</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($promos as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['image_url'])): ?>
                                <img src="<?= clean($p['image_url']) ?>" class="thumb-mini" alt="">
                            <?php else: ?>
                                <span class="pill">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><?= clean($p['title']) ?></td>
                        <td><?= clean($p['time_label']) ?></td>
                        <td><span class="pill"><?= clean($p['category']) ?></span></td>
                        <td><?= clean($p['created_at']) ?></td>
                        <td class="actions">
                            <a href="admin-promo.php?edit=<?= (int)$p['id'] ?>" style="color:#38bdf8;">
                                Editar
                            </a>
                            <?php if ($currentRole === 'admin'): ?>
                                <a href="admin-promo.php?delete=<?= (int)$p['id'] ?>"
                                   style="color:#f97373;"
                                   onclick="return confirm('¿Eliminar esta promoción?');">
                                   Eliminar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p style="font-size:0.8rem;color:#9ca3af;margin-top:0.8rem;">
                La página <strong>en-vivo.php</strong> usará siempre la promoción más reciente
                (la que aparece primero en esta lista).
            </p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
