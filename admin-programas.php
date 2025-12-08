<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';

$currentUser = $_SESSION['user_name'] ?? 'Usuario';
$currentRole = $_SESSION['user_role'] ?? 'editor';
$initials    = strtoupper(substr($currentUser, 0, 2));

function clean($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// ===== ELIMINAR PROGRAMA =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM nzk_programas WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin-programas.php');
    exit;
}

// ===== CARGAR PARA EDITAR =====
$editingPrograma = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM nzk_programas WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editingPrograma = $stmt->fetch();
    }
}

// ===== GUARDAR (CREAR / EDITAR) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title       = trim($_POST['title'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail   = trim($_POST['thumbnail'] ?? '');

    if ($title === '') {
        die('El nombre del programa es obligatorio.');
    }

    // Slug automático si está vacío
    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug = trim($slug, '-');
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("
            UPDATE nzk_programas
            SET title = ?, slug = ?, description = ?, thumbnail = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $slug, $description, $thumbnail, $id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO nzk_programas (title, slug, description, thumbnail)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$title, $slug, $description, $thumbnail]);
    }

    header('Location: admin-programas.php');
    exit;
}

// ===== LISTADO =====
$stmt = $pdo->query("
    SELECT id, title, slug, thumbnail, created_at
    FROM nzk_programas
    ORDER BY created_at DESC
    LIMIT 50
");
$programas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS – Programas NZK</title>
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
        textarea { min-height: 90px; resize:vertical; }
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
    </style>
</head>
<body>

<header class="cms-topbar">
    <div class="cms-title-block">
        <h1>Programas de NZK</h1>
        <span>Gestiona las fichas de programas que aparecen en la portada.</span>
    </div>
    <div class="cms-user-box">
        <div class="cms-avatar"><?= clean($initials) ?></div>
        <div class="cms-user-text">
            <span class="cms-user-name"><?= clean($currentUser) ?></span>
            <span class="cms-user-role"><?= $currentRole === 'admin' ? 'Administrador' : 'Editor' ?></span>
        </div>
        <a href="admin-panel.php" class="btn-secondary">Volver al panel</a>
    </div>
</header>

<div class="layout">

    <!-- FORM -->
    <div class="card">
        <h2 style="margin-top:0;"><?= $editingPrograma ? 'Editar programa' : 'Nuevo programa' ?></h2>

        <form method="post" action="admin-programas.php">
            <input type="hidden" name="id" value="<?= $editingPrograma ? (int)$editingPrograma['id'] : 0 ?>">

            <label for="title">Nombre del programa *</label>
            <input type="text" id="title" name="title" required
                   value="<?= $editingPrograma ? clean($editingPrograma['title']) : '' ?>">

            <label for="slug">Slug (URL amigable)</label>
            <input type="text" id="slug" name="slug"
                   placeholder="yo-emprendedor"
                   value="<?= $editingPrograma ? clean($editingPrograma['slug']) : '' ?>">

            <label for="description">Descripción breve</label>
            <textarea id="description" name="description"
                      placeholder="Descripción corta del programa"><?= $editingPrograma ? clean($editingPrograma['description']) : '' ?></textarea>

            <label for="thumbnail">URL de imagen del programa</label>
            <input type="text" id="thumbnail" name="thumbnail"
                   placeholder="img/programas/yo-emprendedor.jpg"
                   value="<?= $editingPrograma ? clean($editingPrograma['thumbnail']) : '' ?>">

            <button type="submit" class="btn-primary">
                <?= $editingPrograma ? 'Guardar cambios' : 'Crear programa' ?>
            </button>
            <?php if ($editingPrograma): ?>
                <a href="admin-programas.php" class="btn-secondary" style="margin-left:0.6rem;">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- LISTADO -->
    <div class="card">
        <h2 style="margin-top:0;">Programas registrados</h2>

        <?php if (empty($programas)): ?>
            <p>No hay programas registrados aún.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Thumb</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($programas as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['thumbnail'])): ?>
                                <img src="<?= clean($p['thumbnail']) ?>" class="thumb-mini" alt="">
                            <?php else: ?>
                                <span class="pill">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><?= clean($p['title']) ?></td>
                        <td><span class="pill"><?= clean($p['slug']) ?></span></td>
                        <td><?= clean($p['created_at']) ?></td>
                        <td class="actions">
                            <a href="admin-programas.php?edit=<?= (int)$p['id'] ?>" style="color:#38bdf8;">Editar</a>
                            <a href="admin-programas.php?delete=<?= (int)$p['id'] ?>"
                               style="color:#f97373;"
                               onclick="return confirm('¿Eliminar este programa?');">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
