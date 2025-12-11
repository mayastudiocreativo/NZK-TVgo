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
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page admin-promo">
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
