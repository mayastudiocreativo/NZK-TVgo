<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';

function clean($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

// --- ELIMINAR ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0 && $id !== (int)$_SESSION['user_id']) { // no te borras a ti mismo
        $stmt = $pdo->prepare("DELETE FROM cms_users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin-users.php');
    exit;
}

// --- CARGAR PARA EDITAR ---
$editingUser = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM cms_users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $editingUser = $stmt->fetch();
    }
}

// --- GUARDAR (CREAR / EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name     = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $role     = $_POST['role'] === 'admin' ? 'admin' : 'editor';
    $password = trim($_POST['password'] ?? '');

    if ($id > 0) {
        // UPDATE
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
              UPDATE cms_users
              SET name = ?, username = ?, role = ?, password_hash = ?
              WHERE id = ?
            ");
            $stmt->execute([$name, $username, $role, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("
              UPDATE cms_users
              SET name = ?, username = ?, role = ?
              WHERE id = ?
            ");
            $stmt->execute([$name, $username, $role, $id]);
        }
    } else {
        // INSERT
        if ($password === '') {
            die('Debes indicar una contraseña para el nuevo usuario.');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
          INSERT INTO cms_users (name, username, password_hash, role)
          VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $username, $hash, $role]);
    }

    header('Location: admin-users.php');
    exit;
}

// --- LISTADO ---
$stmt = $pdo->query("SELECT * FROM cms_users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$currentUser = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CMS – Usuarios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #050b18;
      color: #f5f7ff;
      margin: 0;
      padding: 2rem;
    }
    h1 { margin-top: 0; }
    .layout {
      display: grid;
      grid-template-columns: minmax(0,1.1fr) minmax(0,1.3fr);
      gap: 2rem;
    }
    @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }
    .card {
      background: #0b1020;
      border-radius: 1rem;
      padding: 1.2rem 1.5rem;
      border: 1px solid rgba(255,255,255,0.08);
    }
    label {
      display:block;
      font-size:0.85rem;
      margin-bottom:0.25rem;
      color:#cbd5f5;
    }
    input[type="text"],
    input[type="password"] {
      width:100%;
      padding:0.5rem 0.65rem;
      border-radius:0.55rem;
      border:1px solid rgba(148,163,184,0.4);
      background:#020617;
      color:#f9fafb;
      font-size:0.9rem;
      margin-bottom:0.7rem;
    }
    select {
      width:100%;
      padding:0.5rem 0.65rem;
      border-radius:0.55rem;
      border:1px solid rgba(148,163,184,0.4);
      background:#020617;
      color:#f9fafb;
      font-size:0.9rem;
      margin-bottom:0.7rem;
    }
    .btn-primary, .btn-secondary, .btn-danger {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:0.45rem 0.9rem;
      border-radius:999px;
      border:none;
      cursor:pointer;
      font-size:0.85rem;
      gap:0.35rem;
    }
    .btn-primary { background:#4f8cff;color:#050b18;font-weight:600; }
    .btn-secondary { background:transparent;color:#e5e7eb;border:1px solid rgba(148,163,184,0.7);text-decoration:none; }
    .btn-danger { background:#dc2626;color:#fff; }
    table{width:100%;border-collapse:collapse;font-size:0.85rem;}
    th,td{padding:0.4rem 0.5rem;border-bottom:1px solid rgba(31,41,55,0.9);text-align:left;}
    th{font-weight:600;color:#cbd5f5;}
    tr:hover td{background:rgba(15,23,42,0.7);}
    .pill{display:inline-block;padding:0.16rem 0.6rem;border-radius:999px;background:rgba(148,163,184,0.25);font-size:0.72rem;}
    .header-bar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:1.5rem;
    }
    .header-bar a { font-size:0.85rem; }
    @media(max-width:700px){
      body{padding:1.5rem 1rem;}
      .header-bar{flex-direction:column;align-items:flex-start;gap:0.6rem;}
    }
  </style>
</head>
<body>

<header class="header-bar">
  <div>
    <h1>Gestión de usuarios</h1>
    <p style="margin:0;font-size:0.85rem;color:#9ca3af;">Solo administrador. Usuario actual: <?= clean($currentUser) ?></p>
  </div>
  <div>
    <a href="admin-videos.php" class="btn-secondary">Volver a videos</a>
    <a href="admin-logout.php" class="btn-secondary">Cerrar sesión</a>
  </div>
</header>

<div class="layout">

  <!-- FORM -->
  <div class="card">
    <h2 style="margin-top:0;"><?= $editingUser ? 'Editar usuario' : 'Nuevo usuario' ?></h2>

    <form method="post" action="admin-users.php">
      <input type="hidden" name="id" value="<?= $editingUser ? (int)$editingUser['id'] : 0 ?>">

      <label for="name">Nombre completo</label>
      <input type="text" id="name" name="name"
             required
             value="<?= $editingUser ? clean($editingUser['name']) : '' ?>">

      <label for="username">Usuario (login)</label>
      <input type="text" id="username" name="username"
             required
             value="<?= $editingUser ? clean($editingUser['username']) : '' ?>">

      <label for="role">Rol</label>
      <select id="role" name="role">
        <option value="editor" <?= $editingUser && $editingUser['role']==='editor' ? 'selected' : '' ?>>Editor</option>
        <option value="admin" <?= $editingUser && $editingUser['role']==='admin' ? 'selected' : '' ?>>Admin</option>
      </select>

      <label for="password">
        Contraseña <?= $editingUser ? '(déjalo vacío para no cambiar)' : '' ?>
      </label>
      <input type="password" id="password" name="password">

      <button type="submit" class="btn-primary">
        <?= $editingUser ? 'Guardar cambios' : 'Crear usuario' ?>
      </button>

      <?php if ($editingUser): ?>
        <a href="admin-users.php" class="btn-secondary" style="margin-left:0.5rem;">Cancelar</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- LISTA -->
  <div class="card">
    <h2 style="margin-top:0;">Usuarios registrados</h2>

    <?php if (empty($users)): ?>
      <p>No hay usuarios todavía.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Creado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= clean($u['name']) ?></td>
            <td><span class="pill"><?= clean($u['username']) ?></span></td>
            <td><?= clean($u['role']) ?></td>
            <td><?= clean($u['created_at']) ?></td>
            <td>
              <a href="admin-users.php?edit=<?= (int)$u['id'] ?>" style="color:#38bdf8;">Editar</a>
              <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                <a href="admin-users.php?delete=<?= (int)$u['id'] ?>"
                   style="color:#f97373;"
                   onclick="return confirm('¿Eliminar este usuario?');">
                   Eliminar
                </a>
              <?php endif; ?>
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