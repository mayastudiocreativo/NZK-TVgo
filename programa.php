<?php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
if ($slug === '') {
  // si no hay slug, vuelve al listado de programas
  header('Location: ./programas.php');
  exit;
}

/**
 * 1) Traer datos del PROGRAMA (tabla nzk_programas)
 */
$stmtProg = $pdo->prepare("
  SELECT *
  FROM nzk_programas
  WHERE slug = ?
  LIMIT 1
");
$stmtProg->execute([$slug]);
$programa = $stmtProg->fetch();

if (!$programa) {
  http_response_code(404);
  echo 'Programa no encontrado';
  exit;
}

/**
 * 2) Traer EPISODIOS de ese programa (tabla nzk_videos)
 *    Suponiendo que los episodios tienen slugs tipo:
 *    en-primera-ep01-temp01, en-primera-ep02-temp01, etc.
 */
$stmtEp = $pdo->prepare("
  SELECT *
  FROM nzk_videos
  WHERE category = 'programas'
    AND slug LIKE ?
  ORDER BY published_at DESC
");
$stmtEp->execute([$slug . '-%']);
$videosPrograma = $stmtEp->fetchAll();

// fecha legible del programa (opcional)
$fechaPrograma = '';
if (!empty($programa['created_at'])) {
  $ts = strtotime($programa['created_at']);
  if ($ts) {
    $fechaPrograma = date('d/m/Y · H:i', $ts);
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($programa['title']) ?> | NZK tvGO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="page-live-ott page-video">

  <?php
    $currentPage = '';
    include __DIR__ . '/includes/header.php';
  ?>

  <main class="page">
    <!-- HERO DEL PROGRAMA (sin player, solo ficha) -->
    <section class="live-hero-inner">
      <!-- Izquierda: imagen del programa -->
      <div class="live-player-frame vod-player-frame">
        <div class="vod-embed-wrapper">
          <?php if (!empty($programa['thumbnail'])): ?>
            <img
              src="<?= htmlspecialchars($programa['thumbnail']) ?>"
              alt="<?= htmlspecialchars($programa['title']) ?>"
              style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
            >
          <?php else: ?>
            <div class="thumb-placeholder" style="width:100%;height:100%;border-radius:0.75rem;">
              <span>Imagen del programa</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Derecha: ficha del programa -->
      <aside class="live-meta-panel">
        <div class="live-meta-content">
          <span class="live-tag">
            <span class="live-dot"></span>
            Programa de NZK
          </span>

          <h1 class="live-title">
            <?= htmlspecialchars($programa['title']) ?>
          </h1>

          <?php if ($fechaPrograma): ?>
            <p class="live-date"><?= $fechaPrograma ?></p>
          <?php endif; ?>

          <?php if (!empty($programa['description'])): ?>
            <p class="live-desc">
              <?= nl2br(htmlspecialchars($programa['description'])) ?>
            </p>
          <?php endif; ?>

          <a href="./en-vivo.php" class="btn btn-live-cta">
            <i class="fa-solid fa-play"></i>
            <span>Volver a señal en vivo</span>
          </a>
        </div>
      </aside>
    </section>

    <!-- LISTA / CARRUSEL DE EPISODIOS DEL PROGRAMA -->
    <section class="section-episodes container">
      <div class="section-header">
        <h2>Episodios y contenidos de este programa</h2>
      </div>

      <?php if (empty($videosPrograma)): ?>
        <p>Por ahora no hay videos asociados a este programa.</p>
      <?php else: ?>
        <div class="episodes-row">
          <?php foreach ($videosPrograma as $video): ?>
            <?php
              $fechaEp = '';
              if (!empty($video['published_at'])) {
                $tsEp = strtotime($video['published_at']);
                if ($tsEp) {
                  $fechaEp = date('d/m/Y · H:i', $tsEp);
                }
              }

              // URL al reproductor de video, indicando también el programa
              $epUrl = './video.php?slug=' . urlencode($video['slug']) .
                       '&program=' . urlencode($programa['slug']);
            ?>
            <article class="episode-card card">
              <a href="<?= $epUrl ?>" class="episode-link">
                <div class="episode-thumb">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail']) ?>"
                      alt="<?= htmlspecialchars($video['title']) ?>"
                      loading="lazy"
                    >
                  <?php else: ?>
                    <span>Sin imagen</span>
                  <?php endif; ?>
                </div>

                <div class="episode-info">
                  <h3 class="episode-title">
                    <?= htmlspecialchars($video['title']) ?>
                  </h3>

                  <?php if ($fechaEp): ?>
                    <p class="episode-meta">
                      <?= $fechaEp ?>
                    </p>
                  <?php endif; ?>

                  <?php if (!empty($video['description'])): ?>
                    <p class="episode-description">
                      <?= htmlspecialchars($video['description']) ?>
                    </p>
                  <?php endif; ?>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
