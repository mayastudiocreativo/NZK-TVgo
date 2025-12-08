<?php
// admin-videos.php
session_start();

// Si no hay sesión, mandar al login
if (!isset($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';

// =========================
// Datos de usuario logueado
// =========================
$currentUser = $_SESSION['user_name'] ?? 'Usuario';
$currentRole = $_SESSION['user_role'] ?? 'editor'; // 'admin' / 'editor'
$initials    = strtoupper(substr($currentUser, 0, 2));

// =========================
// Helpers simples
// =========================
function clean($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Map de categorías (código DB => etiqueta visible)
$CATEGORY_LABELS = [
    'noticias'        => 'Noticias',
    'programas'       => 'Programas de NZK',
    'doc_entrevistas' => 'Documentales / Entrevistas',
    'deportes'        => 'Deportes',
    'eventos'         => 'Eventos especiales',
    'productora'      => 'Productora',
];

$CATEGORY_KEYS = array_keys($CATEGORY_LABELS);

// =========================
// LISTA DE PROGRAMAS (para asociar videos)
// =========================
$stmtProgList = $pdo->query("
    SELECT id, title
    FROM nzk_programas
    ORDER BY title ASC
");
$PROGRAM_LIST = $stmtProgList->fetchAll(PDO::FETCH_ASSOC);


// =========================
// ELIMINAR VIDEO
// =========================
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM nzk_videos WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin-videos.php');
    exit;
}

// =========================
// CARGAR VIDEO PARA EDITAR
// =========================
$editingVideo = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM nzk_videos WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editingVideo = $stmt->fetch();
    }
}

// =========================
// GUARDAR (CREAR / EDITAR)
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title        = trim($_POST['title'] ?? '');
    $slug         = trim($_POST['slug'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $fb_url       = trim($_POST['fb_url'] ?? '');
    $thumbnail    = trim($_POST['thumbnail'] ?? '');
    $published_at = trim($_POST['published_at'] ?? '');
    $category     = $_POST['category'] ?? 'noticias';
    $program_id   = isset($_POST['program_id']) && $_POST['program_id'] !== ''
        ? (int)$_POST['program_id']
        : null;


    // Normalizar categoría
    if (!in_array($category, $CATEGORY_KEYS, true)) {
        $category = 'noticias';
    }

    // Slug automático si está vacío
    if ($slug === '' && $title !== '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
        $slug = trim($slug, '-');
    }

    // Fecha de publicación
    if ($published_at === '') {
        $published_at = date('Y-m-d H:i:s');
    }

    // Si quieres mantener también video_date, usamos la misma fecha
    $video_date = $published_at;

    if ($id > 0) {
        // UPDATE
        $stmt = $pdo->prepare("
    UPDATE nzk_videos
    SET title = ?, slug = ?, description = ?, fb_url = ?, thumbnail = ?, 
        published_at = ?, video_date = ?, category = ?, program_id = ?
    WHERE id = ?
");
$stmt->execute([
    $title,
    $slug,
    $description,
    $fb_url,
    $thumbnail,
    $published_at,
    $video_date,
    $category,
    $program_id,
    $id
]);

    } else {
        // INSERT
        $stmt = $pdo->prepare("
    INSERT INTO nzk_videos (title, slug, description, thumbnail, fb_url, video_date, published_at, category, program_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $title,
    $slug,
    $description,
    $thumbnail,
    $fb_url,
    $video_date,
    $published_at,
    $category,
    $program_id
]);

    }

    header('Location: admin-videos.php');
    exit;
}

// =========================
// LISTADO
// =========================
$stmt = $pdo->query("
    SELECT id, title, slug, thumbnail, published_at, category
    FROM nzk_videos
    ORDER BY published_at DESC
    LIMIT 50
");
$videos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS – NZK Videos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #050b18;
            color: #f5f7ff;
            margin: 0;
            padding: 2rem;
        }
        h1 {
            margin-top: 0;
        }
        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.3fr);
            gap: 2rem;
        }
        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }
        .card {
            background: #0b1020;
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            border: 1px solid rgba(255,255,255,0.08);
        }
        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            color: #cbd5f5;
        }
        input[type="text"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 0.5rem 0.65rem;
            border-radius: 0.55rem;
            border: 1px solid rgba(148,163,184,0.4);
            background: #020617;
            color: #f9fafb;
            font-size: 0.9rem;
            margin-bottom: 0.7rem;
        }
        textarea {
            min-height: 90px;
            resize: vertical;
        }
        .btn-primary,
        .btn-secondary,
        .btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            gap: 0.35rem;
        }
        .btn-primary {
            background: #4f8cff;
            color: #050b18;
            font-weight: 600;
        }
        .btn-secondary {
            background: transparent;
            color: #e5e7eb;
            border: 1px solid rgba(148,163,184,0.7);
            text-decoration: none;
        }
        .btn-secondary.small {
            padding: 0.3rem 0.7rem;
            font-size: 0.8rem;
        }
        .btn-danger {
            background: #dc2626;
            color: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        th, td {
            padding: 0.4rem 0.5rem;
            border-bottom: 1px solid rgba(31,41,55,0.9);
            text-align: left;
        }
        th {
            font-weight: 600;
            color: #cbd5f5;
        }
        tr:hover td {
            background: rgba(15,23,42,0.7);
        }
        .thumb-mini {
            width: 80px;
            height: 45px;
            border-radius: 6px;
            object-fit: cover;
            background: #111827;
        }
        .actions a {
            margin-right: 0.5rem;
            text-decoration: none;
            font-size: 0.8rem;
        }
        .pill {
            display: inline-block;
            padding: 0.16rem 0.6rem;
            border-radius: 999px;
            background: rgba(148,163,184,0.25);
            font-size: 0.72rem;
        }

        /* ======= TOPBAR DEL CMS ======= */
        .cms-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
        }
        .cms-title-block {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .cms-title-block h1 {
            font-size: 1.4rem;
            margin: 0;
        }
        .cms-title-block span {
            font-size: 0.85rem;
            color: #9ca3af;
        }
        .cms-user-box {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        .cms-avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: radial-gradient(circle at top left,#4f46e5,#020617);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid rgba(148,163,184,0.6);
        }
        .cms-user-name {
            font-size: 0.9rem;
            color: #e5e7eb;
        }
        .cms-user-role {
            display: block;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        .cms-user-text {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        @media (max-width: 700px) {
            body {
                padding: 1.5rem 1rem;
            }
            .cms-topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }
        }
    </style>
</head>
<body>

    <header class="cms-topbar">
        <div class="cms-title-block">
            <h1>CMS – NZK Videos</h1>
            <span>Gestor de videos para portada y secciones (Noticias, Programas, Deportes, etc.)</span>
        </div>

        <div class="cms-user-box">
            <div class="cms-avatar"><?= clean($initials) ?></div>
            <div class="cms-user-text">
                <span class="cms-user-name"><?= clean($currentUser) ?></span>
                <span class="cms-user-role">
                    <?= $currentRole === 'admin' ? 'Administrador' : 'Editor' ?>
                </span>
            </div>
            <a href="admin-panel.php" class="btn-secondary">Volver al panel</a>
            <a href="admin-logout.php" class="btn-secondary small">Cerrar sesión</a>
        </div>
    </header>

    <div class="layout">

        <!-- FORMULARIO CREAR / EDITAR -->
        <div class="card">
            <h2 style="margin-top:0;">
                <?= $editingVideo ? 'Editar video' : 'Nuevo video' ?>
            </h2>

            <form method="post" action="admin-videos.php">
                <input type="hidden" name="id" value="<?= $editingVideo ? (int)$editingVideo['id'] : 0 ?>">

                <label for="title">Título *</label>
                <input type="text" id="title" name="title"
                       required
                       value="<?= $editingVideo ? clean($editingVideo['title']) : '' ?>">

                <label for="slug">Slug (URL amigable)</label>
                <input type="text" id="slug" name="slug"
                       placeholder="nzk-noticias-mediodia-02-12-2025"
                       value="<?= $editingVideo ? clean($editingVideo['slug']) : '' ?>">

                <label for="description">Descripción</label>
                <textarea id="description" name="description"
                          placeholder="Breve descripción del video"><?= $editingVideo ? clean($editingVideo['description']) : '' ?></textarea>

                <label for="fb_url">URL del video en Facebook / YouTube *</label>
                <input type="text" id="fb_url" name="fb_url"
                       required
                       placeholder="https://www.facebook.com/... o https://www.youtube.com/watch?v=..."
                       value="<?= $editingVideo ? clean($editingVideo['fb_url']) : '' ?>">

                <label for="thumbnail">URL de imagen / thumbnail</label>
                <input type="text" id="thumbnail" name="thumbnail"
                       placeholder="img/noticias/nzk-mediodia-2025-12-02.jpg"
                       value="<?= $editingVideo ? clean($editingVideo['thumbnail']) : '' ?>">

                <label for="category">Categoría</label>
                <select id="category" name="category">
                    <?php
                    $currentCat = $editingVideo['category'] ?? 'noticias';
                    if (!in_array($currentCat, $CATEGORY_KEYS, true)) {
                        $currentCat = 'noticias';
                    }
                    foreach ($CATEGORY_LABELS as $key => $label): ?>
                        <option value="<?= clean($key) ?>"
                            <?= $key === $currentCat ? 'selected' : '' ?>>
                            <?= clean($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="program_id">Programa (opcional)</label>
                    <select id="program_id" name="program_id">
                        <option value="">Sin programa / General</option>
                        <?php
                        $currentProgramId = $editingVideo['program_id'] ?? null;
                        foreach ($PROGRAM_LIST as $prog):
                            $pid = (int)$prog['id'];
                        ?>
                        <option value="<?= $pid ?>"
                            <?= $currentProgramId == $pid ? 'selected' : '' ?>>
                            <?= clean($prog['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                <label for="published_at">Fecha de publicación</label>
                <input type="datetime-local" id="published_at" name="published_at"
                       value="<?php
                         if ($editingVideo && !empty($editingVideo['published_at'])) {
                             echo date('Y-m-d\TH:i', strtotime($editingVideo['published_at']));
                         }
                       ?>">

                <div style="margin-top:0.8rem;display:flex;gap:0.6rem;align-items:center;">
                    <button type="submit" class="btn-primary">
                        <?= $editingVideo ? 'Guardar cambios' : 'Crear video' ?>
                    </button>

                    <?php if ($editingVideo): ?>
                        <a href="admin-videos.php" class="btn-secondary">Cancelar edición</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- LISTADO -->
        <div class="card">
            <h2 style="margin-top:0;">Videos cargados</h2>

            <?php if (empty($videos)): ?>
                <p>No hay videos registrados aún.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Thumb</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Slug</th>
                            <th>Publicado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($videos as $v): ?>
                        <tr>
                            <td>
                                <?php if (!empty($v['thumbnail'])): ?>
                                    <img src="<?= clean($v['thumbnail']) ?>" class="thumb-mini" alt="">
                                <?php else: ?>
                                    <span class="pill">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td><?= clean($v['title']) ?></td>
                            <td>
                                <span class="pill">
                                    <?= clean($CATEGORY_LABELS[$v['category']] ?? $v['category']) ?>
                                </span>
                            </td>
                            <td><span class="pill"><?= clean($v['slug']) ?></span></td>
                            <td><?= clean($v['published_at']) ?></td>
                            <td class="actions">
                                <a href="admin-videos.php?edit=<?= (int)$v['id'] ?>" style="color:#38bdf8;">Editar</a>
                                <a href="admin-videos.php?delete=<?= (int)$v['id'] ?>"
                                   style="color:#f97373;"
                                   onclick="return confirm('¿Eliminar este video?');">
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