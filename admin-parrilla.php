<?php
// admin-parrilla.php
require __DIR__ . '/includes/session.php';

// Solo admins
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$error = null;

// ---------------------------------------------
// Helper: segmentar carpeta según categoría
// ---------------------------------------------
function category_to_segment(string $category): string {
    switch ($category) {
        case 'Película':
            return 'cine';
        case 'Serie':
            return 'series';
        case 'Novela':
            return 'novelas';
        case 'Música':
            return 'musica';
        case 'Noticias':
            return 'noticieros';
        case 'Dibujo Animado':
            return 'dibujos';
        case 'Productora':
            return 'productora';
        default:
            return 'programas';
    }
}

// ------------------------------------------------------
// ALTA / ELIMINAR BLOQUE
// ------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $action = $_POST['action'] ?? '';

    // ---------------------------------
    // Añadir bloque
    // ---------------------------------
    if ($action === 'add_slot') {
        $group         = $_POST['day_group'] ?? 'week'; // week | weekend | single
        $weekdaySingle = isset($_POST['weekday']) ? (int)$_POST['weekday'] : 1;

        $startTime   = trim($_POST['start_time'] ?? '');
        $endTime     = trim($_POST['end_time'] ?? '');
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $imgUrlInput = trim($_POST['img_url'] ?? '');
        $desc        = trim($_POST['description'] ?? '');
        $programSlug = trim($_POST['program_slug'] ?? '');

        if ($startTime === '' || $endTime === '' || $title === '' || $category === '') {
            $error = "Faltan datos obligatorios.";
        } else {
            // Determinar a qué weekdays se aplica
            $weekdays = [];
            if ($group === 'week') {
                // Lunes (1) a Viernes (5)
                $weekdays = [1,2,3,4,5];
            } elseif ($group === 'weekend') {
                // Sábado (6) y Domingo (0)
                $weekdays = [6,0];
            } else {
                // Solo un día específico
                $weekdaySingle = max(0, min(6, $weekdaySingle));
                $weekdays = [$weekdaySingle];
            }

            // Normalizar horas HH:MM a HH:MM:SS
            if (strlen($startTime) === 5) {
                $startTime .= ':00';
            }
            if (strlen($endTime) === 5) {
                $endTime .= ':00';
            }

            // --------- resolver imagen final -----------
            // Por defecto, lo que escribió el usuario en el input
            $finalImgUrl = $imgUrlInput;

            // Si no hay error previo, vemos si se subió archivo
            if (!$error && isset($_FILES['img_file'])) {
                $fileError = $_FILES['img_file']['error'];

                if ($fileError !== UPLOAD_ERR_NO_FILE) {
                    if ($fileError === UPLOAD_ERR_OK) {
                        $tmpName  = $_FILES['img_file']['tmp_name'];
                        $fileSize = (int)$_FILES['img_file']['size'];

                        // máx 3 MB
                        if ($fileSize > 3 * 1024 * 1024) {
                            $error = 'La imagen es demasiado pesada (máx. 3 MB).';
                        } else {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mime  = finfo_file($finfo, $tmpName);
                            finfo_close($finfo);

                            $allowed = [
                                'image/jpeg' => 'jpg',
                                'image/png'  => 'png',
                                'image/webp' => 'webp',
                            ];

                            if (!isset($allowed[$mime])) {
                                $error = 'Formato de imagen no permitido. Usa JPG, PNG o WEBP.';
                            } else {
                                // Extensión según MIME
                                $extFromMime = $allowed[$mime];
                                $segment     = category_to_segment($category);

                                // Nombre original del archivo
                                $originalName = $_FILES['img_file']['name'];
                                $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                                $extUser  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                                // Limpiar nombre: solo letras, números, guion y guion bajo
                                $baseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
                                if ($baseName === '') {
                                    $baseName = 'imagen';
                                }

                                // Extensión final
                                $validExts = ['jpg','jpeg','png','webp'];
                                if (!in_array($extUser, $validExts, true)) {
                                    $ext = $extFromMime;
                                } else {
                                    $ext = $extUser;
                                }

                                // Carpeta física (ajusta a ../img/ si fuera necesario)
                                $uploadDir = __DIR__ . '/img/' . $segment . '/';

                                if (!is_dir($uploadDir)) {
                                    if (!mkdir($uploadDir, 0755, true)) {
                                        $error = 'No se pudo crear la carpeta: ' . $uploadDir;
                                    }
                                }

                                if (empty($error) && !is_writable($uploadDir)) {
                                    $error = 'La carpeta no tiene permisos de escritura: ' . $uploadDir .
                                             '. Asigna permisos 775 o 777 mientras desarrollas.';
                                }

                                if (empty($error)) {
                                    // Nombre final evitando sobrescribir
                                    $fileName = $baseName . '.' . $ext;
                                    $destPath = $uploadDir . $fileName;
                                    $counter  = 1;

                                    while (file_exists($destPath)) {
                                        $fileName = $baseName . '-' . $counter . '.' . $ext;
                                        $destPath = $uploadDir . $fileName;
                                        $counter++;
                                    }

                                    if (move_uploaded_file($tmpName, $destPath)) {
                                        // ruta relativa que se guarda en BD
                                        $finalImgUrl = './img/' . $segment . '/' . $fileName;
                                    } else {
                                        $error = 'No se pudo guardar la imagen en el servidor (move_uploaded_file falló).';
                                    }
                                }
                            }
                        }
                    } else {
                        $error = 'Error al subir la imagen (código ' . $fileError . ').';
                    }
                }
            }

            // Si no hubo errores, insertar en BD
            if (!$error) {
                $stmt = $pdo->prepare("
                  INSERT INTO nzk_schedule_slots
                  (weekday, start_time, end_time, title, category, img_url, description, program_slug)
                  VALUES (:weekday, :start_time, :end_time, :title, :category, :img_url, :description, :program_slug)
                ");

                foreach ($weekdays as $wd) {
                    $stmt->execute([
                        ':weekday'     => $wd,
                        ':start_time'  => $startTime,
                        ':end_time'    => $endTime,
                        ':title'       => $title,
                        ':category'    => $category,
                        ':img_url'     => $finalImgUrl !== '' ? $finalImgUrl : null,
                        ':description' => $desc !== '' ? $desc : null,
                        ':program_slug'=> $programSlug ?: null,
                    ]);
                }

                header('Location: admin-parrilla.php?ok=1');
                exit;
            }
        }
    }

    // ---------------------------------
    // Eliminar un bloque puntual
    // ---------------------------------
    if ($action === 'delete_slot') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM nzk_schedule_slots WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: admin-parrilla.php');
        exit;
    }
}

// ------------------------------------------------------
// CARGAR PARRILLA PARA LISTAR
// ------------------------------------------------------
$stmt = $pdo->query("
  SELECT *
  FROM nzk_schedule_slots
  ORDER BY weekday ASC, start_time ASC
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por weekday
$byWeekday = [
    0 => [],
    1 => [],
    2 => [],
    3 => [],
    4 => [],
    5 => [],
    6 => [],
];

foreach ($rows as $r) {
    $wd = (int)$r['weekday'];
    if (!isset($byWeekday[$wd])) {
        $byWeekday[$wd] = [];
    }
    $byWeekday[$wd][] = $r;
}

$weekdayNames = [
    0 => 'Domingo',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Parrilla de programación – NZK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body{
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background:#050b18;
      color:#f9fafb;
      margin:0;
      padding:2rem;
    }
    a{color:#93c5fd;text-decoration:none;}
    h1,h2{margin-top:0;}
    .layout{
      display:grid;
      grid-template-columns:minmax(0,1.1fr) minmax(0,1.5fr);
      gap:2rem;
    }
    @media(max-width:1024px){
      .layout{grid-template-columns:1fr;}
    }
    .card{
      background:#0b1020;
      border-radius:1rem;
      padding:1.2rem 1.5rem;
      border:1px solid rgba(148,163,184,0.2);
    }
    label{
      display:block;
      font-size:0.85rem;
      margin-bottom:0.25rem;
      color:#cbd5f5;
    }
    input[type="text"],
    input[type="time"],
    select,
    textarea{
      width:100%;
      padding:0.5rem 0.65rem;
      border-radius:0.55rem;
      border:1px solid rgba(148,163,184,0.4);
      background:#020617;
      color:#f9fafb;
      font-size:0.9rem;
      margin-bottom:0.7rem;
    }
    textarea{min-height:80px;resize:vertical;}
    .btn-primary,
    .btn-danger,
    .btn-edit{
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
    .btn-primary{
      background:#4f8cff;
      color:#050b18;
      font-weight:600;
    }
    .btn-danger{
      background:#dc2626;
      color:#fff;
    }
    .btn-edit{
      background:transparent;
      border:1px solid rgba(148,163,184,0.8);
      color:#e5e7eb;
      text-decoration:none;
    }
    table{
      width:100%;
      border-collapse:collapse;
      font-size:0.85rem;
      margin-bottom:1.5rem;
    }
    th,td{
      padding:0.35rem 0.5rem;
      border-bottom:1px solid rgba(30,64,175,0.6);
    }
    th{color:#cbd5f5;font-weight:600;text-align:left;}
    tr:hover td{background:rgba(15,23,42,0.8);}
    .weekday-heading{
      margin-top:1.5rem;
      font-size:0.95rem;
      font-weight:600;
      color:#e5e7eb;
    }
    .pill{
      display:inline-block;
      padding:0.16rem 0.55rem;
      border-radius:999px;
      background:rgba(148,163,184,0.25);
      font-size:0.72rem;
    }
    .header-bar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:1.5rem;
    }
    @media(max-width:720px){
      body{padding:1.5rem 1rem;}
      .header-bar{flex-direction:column;align-items:flex-start;gap:0.5rem;}
    }
    .flash-ok{
      background:rgba(34,197,94,0.1);
      border:1px solid rgba(34,197,94,0.8);
      color:#bbf7d0;
      padding:0.6rem 0.8rem;
      border-radius:0.75rem;
      font-size:0.85rem;
      margin-bottom:0.8rem;
    }
    .flash-error{
      background:rgba(239,68,68,0.1);
      border:1px solid rgba(239,68,68,0.8);
      color:#fecaca;
      padding:0.6rem 0.8rem;
      border-radius:0.75rem;
      font-size:0.85rem;
      margin-bottom:0.8rem;
    }
    .hint{
      font-size:0.78rem;
      color:#9ca3af;
      margin-top:-0.4rem;
      margin-bottom:0.65rem;
    }
    code{
      font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size:0.75rem;
      background:#020617;
      padding:0.1rem 0.35rem;
      border-radius:0.25rem;
    }

    /* Estilo del botón de subir archivo (similar a editar-bloque.php) */
    .file-input-wrapper {
      margin-bottom:0.7rem;
    }
    .file-input-label {
      position:relative;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:0.45rem 0.95rem;
      border-radius:999px;
      background:#111827;
      border:1px solid rgba(148,163,184,0.8);
      color:#e5e7eb;
      font-size:0.85rem;
      cursor:pointer;
      gap:0.35rem;
    }
    .file-input-label span.icon {
      font-size:0.9rem;
    }
    .file-input-label input[type="file"] {
      position:absolute;
      inset:0;
      opacity:0;
      cursor:pointer;
    }
  </style>
</head>
<body>

<header class="header-bar">
  <div>
    <h1>Parrilla de programación</h1>
    <p style="margin:0;font-size:0.85rem;color:#9ca3af;">
      Define una sola vez la parrilla de <strong>Lunes a Viernes</strong> y de <strong>Sábado y Domingo</strong>.
    </p>
  </div>
  <div>
    <a href="admin-panel.php">← Volver al panel</a>
  </div>
</header>

<?php if (isset($_GET['ok'])): ?>
  <div class="flash-ok">Bloque(s) guardado(s) correctamente.</div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="flash-error"><?= h($error) ?></div>
<?php endif; ?>

<div class="layout">
  <!-- FORMULARIO -->
  <section class="card">
    <h2 style="margin-top:0;">Nuevo bloque</h2>

    <form method="post" enctype="multipart/form-data">
      <?php csrf_field(); ?>
      <input type="hidden" name="action" value="add_slot">

      <label for="day_group">Aplicar a:</label>
      <select name="day_group" id="day_group" required>
        <option value="week">Lunes a Viernes</option>
        <option value="weekend">Sábado y Domingo</option>
        <option value="single">Solo un día específico</option>
      </select>

      <label for="weekday">Si elegiste “Solo un día específico”:</label>
      <select name="weekday" id="weekday">
        <option value="1">Lunes</option>
        <option value="2">Martes</option>
        <option value="3">Miércoles</option>
        <option value="4">Jueves</option>
        <option value="5">Viernes</option>
        <option value="6">Sábado</option>
        <option value="0">Domingo</option>
      </select>

      <label for="start_time">Hora inicio</label>
      <input type="time" name="start_time" id="start_time" required>

      <label for="end_time">Hora fin</label>
      <input type="time" name="end_time" id="end_time" required>

      <label for="title">Título del programa</label>
      <input type="text" name="title" id="title" required>

      <label for="category">Categoría</label>
      <select name="category" id="category" required>
        <option value="Noticias">Noticias</option>
        <option value="Película">Película</option>
        <option value="Serie">Serie</option>
        <option value="Novela">Novela</option>
        <option value="Música">Música</option>
        <option value="Dibujo Animado">Dibujo Animado</option>
        <option value="Productora">Productora</option>
        <option value="Otro">Otro</option>
      </select>

      <label for="img_url">Imagen (ruta relativa manual)</label>
      <input type="text" name="img_url" id="img_url" placeholder="./img/series/gooddoctor.jpg">
      <p class="hint">
        Puedes escribir una ruta como <code>./img/series/gooddoctor.jpg</code> o subir una nueva imagen abajo.
        Si subes archivo, se guardará automáticamente en <code>img/&lt;segmento&gt;/</code> según la categoría
        (cine, series, novelas, música, noticieros, dibujos, productora, programas).
      </p>

      <label>Subir imagen (opcional)</label>
      <div class="file-input-wrapper">
        <label class="file-input-label">
          <span class="icon">📁</span>
          <span>Elegir archivo</span>
          <input type="file" name="img_file" id="img_file" accept="image/*">
        </label>
      </div>

      <label for="program_slug">Slug de programa (opcional)</label>
      <input type="text" name="program_slug" id="program_slug" placeholder="the-good-doctor">

      <label for="description">Descripción (opcional)</label>
      <textarea name="description" id="description" rows="3"></textarea>

      <button type="submit" class="btn-primary">Guardar bloque</button>
    </form>
  </section>

  <!-- LISTADO -->
  <section class="card">
    <h2 style="margin-top:0;">Parrilla actual</h2>

    <?php foreach ($byWeekday as $wd => $list): ?>
      <div class="weekday-block">
        <div class="weekday-heading">
          <?= h($weekdayNames[$wd]) ?>
          <?php if (in_array($wd, [1,2,3,4,5], true)): ?>
            <span class="pill">Lunes a Viernes</span>
          <?php elseif (in_array($wd, [0,6], true)): ?>
            <span class="pill">Sábado / Domingo</span>
          <?php endif; ?>
        </div>

        <?php if (empty($list)): ?>
          <p style="font-size:0.8rem;color:#6b7280;">Sin bloques definidos.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Hora</th>
                <th>Título</th>
                <th>Categoría</th>
                <th>Img</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $slot): ?>
              <tr>
                <td><?= h(substr($slot['start_time'],0,5)) ?> - <?= h(substr($slot['end_time'],0,5)) ?></td>
                <td><?= h($slot['title']) ?></td>
                <td><?= h($slot['category']) ?></td>
                <td><?= h($slot['img_url']) ?></td>
                <td>
                  <a href="./editar-bloque.php?id=<?= (int)$slot['id'] ?>"
                     class="btn-edit"
                     style="margin-right:6px;">
                    Editar
                  </a>

                  <form method="post" style="display:inline"
                        onsubmit="return confirm('¿Eliminar este bloque solo para este día?');">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="delete_slot">
                    <input type="hidden" name="id" value="<?= (int)$slot['id'] ?>">
                    <button type="submit" class="btn-danger">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>
</div>

<script>
// desactivar select de weekday cuando no es "single"
document.addEventListener('DOMContentLoaded', () => {
  const groupSel = document.getElementById('day_group');
  const weekdaySel = document.getElementById('weekday');

  function toggleWeekday() {
    if (!groupSel || !weekdaySel) return;
    const v = groupSel.value;
    weekdaySel.disabled = (v !== 'single');
    weekdaySel.style.opacity = (v !== 'single') ? 0.5 : 1;
  }

  if (groupSel) {
    groupSel.addEventListener('change', toggleWeekday);
    toggleWeekday();
  }
});
</script>

</body>
</html>
