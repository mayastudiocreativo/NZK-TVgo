<?php
require __DIR__ . '/includes/session.php';

if (empty($_SESSION['user_id'])) {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';
require __DIR__ . '/includes/helpers.php';

// Verificar CSRF (la función internamente solo actúa si es POST)
csrf_verify();

$currentUser = $_SESSION['user_name'] ?? 'Usuario';
$currentRole = $_SESSION['user_role'] ?? 'editor';
$initials    = strtoupper(substr($currentUser, 0, 2));

function clean($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// ===== MANEJO DE FORMULARIOS (POST) =====
//  - Eliminar programa
//  - Crear / editar programa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) ELIMINAR PROGRAMA (POST + CSRF)
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM nzk_programas WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: admin-programas.php');
        exit;
    }

    // 2) GUARDAR (CREAR / EDITAR)
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title       = trim($_POST['title'] ?? '');
    $rawSlug     = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail   = trim($_POST['thumbnail'] ?? '');

    if ($title === '') {
        // Podrías mejorar esto guardando el error en sesión, pero por ahora redirigimos
        header('Location: admin-programas.php');
        exit;
    }

    // =========================
    // SLUG ÚNICO
    // =========================

    // Si el usuario no ingresó slug, generarlo desde el título
    if ($rawSlug === '' && $title !== '') {
        $rawSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
    }

    // Limpiar bordes
    $rawSlug = trim($rawSlug, '-');

    // Si aún está vacío, asignar base genérica
    if ($rawSlug === '') {
        $rawSlug = 'programa';
    }

    // Si estás editando, excluir el ID actual al comprobar duplicados
    $currentId = $id > 0 ? $id : null;

    // Garantizar que el slug sea único en nzk_programas
    $slug = generateUniqueSlug($pdo, 'nzk_programas', $rawSlug, 'id', $currentId);

    // INSERT / UPDATE
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

// ===== CARGAR PARA EDITAR (GET) =====
$editingPrograma = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM nzk_programas WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editingPrograma = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ===== LISTADO =====
$stmt = $pdo->query("
    SELECT id, title, slug, thumbnail, created_at
    FROM nzk_programas
    ORDER BY created_at DESC
    LIMIT 50
");
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS – Programas NZK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-page admin-programas">
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
            <?php csrf_field(); ?>
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

                            <form method="post" action="admin-programas.php"
                                  style="display:inline;"
                                  onsubmit="return confirm('¿Eliminar este programa?');">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="delete_id" value="<?= (int)$p['id'] ?>">
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
