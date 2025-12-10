<?php
// editar-bloque.php
require __DIR__ . '/includes/session.php';

// Solo admins
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: admin-login.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/security.php';

// Verificación CSRF (la función internamente solo valida en POST)
csrf_verify();

function clean($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$error = null;

// -----------------------------------------
// Cargar ID del slot
// -----------------------------------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : $id;
}

if ($id <= 0) {
    header('Location: admin-parrilla.php');
    exit;
}

// -----------------------------------------
// Cargar datos actuales del slot
// -----------------------------------------
$stmt = $pdo->prepare("SELECT * FROM nzk_schedule_slots WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$slot = $stmt->fetch();

if (!$slot) {
    header('Location: admin-parrilla.php');
    exit;
}

// Para inputs type="time", recortamos a HH:MM
$startValue = substr($slot['start_time'], 0, 5);
$endValue   = substr($slot['end_time'], 0, 5);

// Nombres de días para mostrar en el select
$weekdayNames = [
    0 => 'Domingo',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
];

// Helper para decidir subcarpeta en /img según categoría
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
        case 'Especial':
        case 'Entretenimiento':
            return 'programas';
        default:
            return 'programas';
    }
}

// -----------------------------------------
// Si POST: actualizar
// -----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weekday      = isset($_POST['weekday']) ? (int)$_POST['weekday'] : 0; // 0..6
    $start_time   = trim($_POST['start_time'] ?? '');
    $end_time     = trim($_POST['end_time'] ?? '');
    $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $img_url      = trim($_POST['img_url'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $program_slug = trim($_POST['program_slug'] ?? '');

    // Validación básica
    if ($weekday < 0 || $weekday > 6) {
        $weekday = 0;
    }

    if ($start_time === '' || $end_time === '' || $title === '' || $category === '') {
        $error = 'Todos los campos obligatorios deben estar completos.';
    } else {
        // Normalizar horas a formato HH:MM:SS si vienen como HH:MM
        if (strlen($start_time) === 5) {
            $start_time .= ':00';
        }
        if (strlen($end_time) === 5) {
            $end_time .= ':00';
        }
    }

    // Valor base de imagen:
    // - si el usuario escribió algo en img_url, usamos eso
    // - si no, dejamos la que ya tenía el slot
    $finalImgUrl = $img_url !== '' ? $img_url : ($slot['img_url'] ?? '');

    // -----------------------------------------
    // Subir imagen desde el CMS (opcional)
    // -----------------------------------------
    if (empty($error) && isset($_FILES['img_file'])) {
        $fileError = $_FILES['img_file']['error'];

        // Si el usuario seleccionó archivo (no es "no file")
        if ($fileError !== UPLOAD_ERR_NO_FILE) {
            if ($fileError === UPLOAD_ERR_OK) {
                $tmpName  = $_FILES['img_file']['tmp_name'];
                $fileSize = (int)$_FILES['img_file']['size'];

                // Límite de tamaño: 3 MB
                if ($fileSize > 3 * 1024 * 1024) {
                    $error = 'La imagen es demasiado pesada (máx. 3 MB).';
                } else {
                    // Detectar MIME real
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

                        // Carpeta física: /img/<segment>/
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
                                // Ruta relativa que usará el front
                                $finalImgUrl = './img/' . $segment . '/' . $fileName;
                            } else {
                                $error = 'No se pudo guardar la imagen en el servidor (move_uploaded_file falló).';
                            }
                        }
                    }
                }
            } else {
                // Otros códigos de error de subida
                $error = 'Error al subir la imagen (código ' . $fileError . ').';
            }
        }
    }

    // -----------------------------------------
    // Si no hubo errores, actualizar en BD
    // -----------------------------------------
    if (empty($error)) {
        $stmt = $pdo->prepare("
            UPDATE nzk_schedule_slots
            SET weekday      = :weekday,
                start_time   = :start_time,
                end_time     = :end_time,
                title        = :title,
                category     = :category,
                img_url      = :img_url,
                description  = :description,
                program_slug = :program_slug
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([
            ':weekday'      => $weekday,
            ':start_time'   => $start_time,
            ':end_time'     => $end_time,
            ':title'        => $title,
            ':category'     => $category,
            ':img_url'      => $finalImgUrl !== '' ? $finalImgUrl : null,
            ':description'  => $description !== '' ? $description : null,
            ':program_slug' => $program_slug !== '' ? $program_slug : null,
            ':id'           => $id,
        ]);

        header('Location: admin-parrilla.php');
        exit;
    }

    // Si hubo error, actualizamos valores para que el formulario
    // muestre lo que el usuario intentó guardar
    $slot['weekday']      = $weekday;
    $slot['start_time']   = $start_time;
    $slot['end_time']     = $end_time;
    $slot['title']        = $title;
    $slot['category']     = $category;
    $slot['img_url']      = $finalImgUrl;
    $slot['description']  = $description;
    $slot['program_slug'] = $program_slug;

    // Recalcular HH:MM para los inputs type="time"
    $startValue = substr($slot['start_time'], 0, 5);
    $endValue   = substr($slot['end_time'], 0, 5);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar bloque de programación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background:#050b18;
      color:#f9fafb;
      margin:0;
      padding:2rem;
    }
    .card {
      max-width:720px;
      margin:0 auto;
      background:#0b1020;
      border-radius:1rem;
      padding:1.5rem 1.75rem;
      border:1px solid rgba(148,163,184,0.35);
    }
    h1 {
      margin-top:0;
      font-size:1.4rem;
    }
    label {
      display:block;
      font-size:0.85rem;
      margin-bottom:0.25rem;
      color:#cbd5f5;
    }
    input[type="text"],
    input[type="time"],
    select,
    textarea {
      width:100%;
      padding:0.45rem 0.6rem;
      border-radius:0.55rem;
      border:1px solid rgba(148,163,184,0.5);
      background:#020617;
      color:#f9fafb;
      font-size:0.9rem;
      margin-bottom:0.9rem;
      box-sizing:border-box;
    }
    textarea {
      min-height:80px;
      resize:vertical;
    }
    .row-2 {
      display:flex;
      gap:0.75rem;
    }
    .row-2 > div {
      flex:1;
    }
    .btn-primary,
    .btn-secondary {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:0.45rem 0.95rem;
      border-radius:999px;
      border:1px solid transparent;
      font-size:0.85rem;
      cursor:pointer;
      text-decoration:none;
      gap:0.3rem;
    }
    .btn-primary {
      background:#4f8cff;
      color:#050b18;
      font-weight:600;
    }
    .btn-secondary {
      background:transparent;
      border-color:rgba(148,163,184,0.7);
      color:#e5e7eb;
    }
    .actions {
      display:flex;
      gap:0.5rem;
      margin-top:0.5rem;
    }
    .error {
      background:rgba(239,68,68,0.1);
      border:1px solid rgba(239,68,68,0.7);
      color:#fecaca;
      padding:0.6rem 0.8rem;
      border-radius:0.75rem;
      font-size:0.85rem;
      margin-bottom:0.9rem;
    }
    a { color:#93c5fd; }
    .hint {
      font-size:0.8rem;
      color:#9ca3af;
      margin-top:-0.4rem;
      margin-bottom:0.8rem;
    }
    code {
      font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size:0.8rem;
      background:#020617;
      padding:0.1rem 0.3rem;
      border-radius:0.25rem;
    }

    /* Estilo bonito para el botón de subir imagen */
    .file-input-wrapper {
      margin-bottom:0.9rem;
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

  <div class="card">
    <h1>Editar bloque de programación</h1>
    <p style="font-size:0.85rem;color:#9ca3af;margin-top:-0.3rem;">
      ID #<?= (int)$slot['id']; ?> · Día actual: <?= $weekdayNames[(int)$slot['weekday']] ?? '—'; ?>
    </p>

    <?php if (!empty($error)): ?>
      <div class="error"><?= clean($error) ?></div>
    <?php endif; ?>

    <form method="post"
          action="editar-bloque.php?id=<?= (int)$slot['id'] ?>"
          enctype="multipart/form-data">
      <?php csrf_field(); ?>
      <input type="hidden" name="id" value="<?= (int)$slot['id'] ?>">

      <div class="row-2">
        <div>
          <label for="weekday">Día de la semana</label>
          <select id="weekday" name="weekday">
            <?php foreach ($weekdayNames as $num => $name): ?>
              <option value="<?= $num ?>"
                <?= (int)$slot['weekday'] === $num ? 'selected' : '' ?>>
                <?= $name ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label>Horario</label>
          <div class="row-2">
            <div>
              <input type="time" name="start_time" value="<?= clean($startValue) ?>" required>
            </div>
            <div>
              <input type="time" name="end_time" value="<?= clean($endValue) ?>" required>
            </div>
          </div>
        </div>
      </div>

      <label for="title">Título del programa</label>
      <input type="text" id="title" name="title"
             value="<?= clean($slot['title']) ?>" required>

      <label for="category">Categoría</label>
      <select id="category" name="category" required>
        <?php
          $categories = ['Noticias','Película','Serie','Novela','Música','Dibujo Animado','Entretenimiento','Especial'];
          $currentCat = $slot['category'];
        ?>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= clean($cat) ?>"
            <?= $currentCat === $cat ? 'selected' : '' ?>>
            <?= $cat ?>
          </option>
        <?php endforeach; ?>
        <?php if ($currentCat && !in_array($currentCat, $categories, true)): ?>
          <option value="<?= clean($currentCat) ?>" selected>
            <?= clean($currentCat) ?> (actual)
          </option>
        <?php endif; ?>
      </select>

      <label for="img_url">Imagen (ruta relativa manual)</label>
      <input type="text" id="img_url" name="img_url"
             placeholder="./img/series/mi-serie.jpg"
             value="<?= clean($slot['img_url']) ?>">
      <p class="hint">
        Puedes escribir una ruta como
        <code>./img/series/gooddoctor.jpg</code>
        o subir una nueva imagen abajo.  
        Si subes un archivo, se guardará automáticamente en la carpeta
        <code>img/&lt;segmento&gt;/</code> según la categoría (cine, series, novelas, etc).
      </p>

      <label>Subir nueva imagen (opcional)</label>
      <div class="file-input-wrapper">
        <label class="file-input-label">
          <span class="icon">📁</span>
          <span>Elegir archivo</span>
          <input type="file" id="img_file" name="img_file" accept="image/*">
        </label>
      </div>
      <?php if (!empty($slot['img_url'])): ?>
        <p class="hint">
          Imagen actual: <code><?= clean($slot['img_url']) ?></code>
        </p>
      <?php endif; ?>

      <label for="description">Descripción</label>
      <textarea id="description" name="description"
                placeholder="Descripción breve del bloque..."><?= clean($slot['description']) ?></textarea>

      <label for="program_slug">Slug de programa (opcional)</label>
      <input type="text" id="program_slug" name="program_slug"
             placeholder="ej: mi-programa"
             value="<?= clean($slot['program_slug']) ?>">

      <div class="actions">
        <button type="submit" class="btn-primary">Guardar cambios</button>
        <a href="admin-parrilla.php" class="btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>

</body>
</html>
