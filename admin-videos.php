<?php
// admin-videos.php

require __DIR__ . '/includes/session.php';

// Si no hay sesión, mandar al login
if (empty($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/helpers.php';

// Verificar CSRF en peticiones POST (dentro de security.php ya se comprueba que sea POST)
csrf_verify();

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
// MANEJO DE FORMULARIOS (POST)
//  - Eliminar video
//  - Crear / editar video
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) ELIMINAR VIDEO (POST + CSRF)
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM nzk_videos WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: admin-videos.php');
        exit;
    }

    // 2) GUARDAR (CREAR / EDITAR)
    $id           = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title        = trim($_POST['title'] ?? '');
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

    // Validación sencilla
    if ($title === '' || $fb_url === '') {
        // Podrías guardar un mensaje de error en sesión si quieres mostrarlo
        header('Location: admin-videos.php');
        exit;
    }

    // Manejo de fecha de publicación
    if ($published_at === '') {
        $published_at = date('Y-m-d H:i:s');
    }
    $video_date = $published_at;

    // =========================
    // SLUG ÚNICO
    // =========================
    $rawSlug = trim($_POST['slug'] ?? '');

    // Si el usuario no ingresó slug, generarlo desde el título
    if ($rawSlug === '' && $title !== '') {
        $rawSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    }

    // Limpiar bordes de guiones
    $rawSlug = trim($rawSlug, '-');

    // Si aún queda vacío, usar un slug genérico base
    if ($rawSlug === '') {
        $rawSlug = 'video';
    }

    // Si estás editando, excluye el ID actual al comprobar duplicados
    $currentId = $id > 0 ? $id : null;

    // Aquí se garantiza que el slug sea único en la tabla nzk_videos
    $slug = generateUniqueSlug($pdo, 'nzk_videos', $rawSlug, 'id', $currentId);

    // =========================
    // INSERT / UPDATE
    // =========================
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
// LISTADO
// =========================
$stmt = $pdo->query("
    SELECT id, title, slug, thumbnail, published_at, category
    FROM nzk_videos
    ORDER BY published_at DESC
    LIMIT 50
");
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS – NZK Videos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page admin-videos">

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
                <?php csrf_field(); ?>
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

                                <form method="post" action="admin-videos.php" style="display:inline;"
                                      onsubmit="return confirm('¿Eliminar este video?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="delete_id" value="<?= (int)$v['id'] ?>">
                                    <button type="submit" class="btn-danger">Eliminar</button>
                                </form>

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
