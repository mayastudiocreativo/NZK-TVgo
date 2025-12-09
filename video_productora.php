<?php
// video_productora.php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
if ($slug === '') {
  header('Location: ./productora.php');
  exit;
}

// 1) Traer el video actual (de la categoría productora, por seguridad)
$stmt = $pdo->prepare("
  SELECT *
  FROM nzk_videos
  WHERE slug = ?
  LIMIT 1
");
$stmt->execute([$slug]);
$video = $stmt->fetch();

if (!$video) {
  http_response_code(404);
  echo 'Video no encontrado';
  exit;
}

// 2) Preparar URL de embed (YouTube o Facebook)
$rawUrl   = trim($video['fb_url'] ?? '');
$provider = 'facebook';
$embedUrl = $rawUrl;

if (stripos($rawUrl, 'youtube.com') !== false || stripos($rawUrl, 'youtu.be') !== false) {
  $provider = 'youtube';

  $ytId = null;
  if (preg_match('~(?:v=|/)([A-Za-z0-9_-]{6,})~', $rawUrl, $m)) {
    $ytId = $m[1];
  }

  if ($ytId) {
    $embedUrl = 'https://www.youtube.com/embed/' . $ytId;
  }
}

// 3) Fecha legible
$fechaLegible = '';
if (!empty($video['published_at'])) {
  $ts = strtotime($video['published_at']);
  if ($ts) {
    $fechaLegible = date('d/m/Y · H:i', $ts);
  }
}

// 4) Otras producciones recientes de NZK Productora
try {
  $stmtOtros = $pdo->prepare("
    SELECT *
    FROM nzk_videos
    WHERE category = 'productora'
      AND slug <> ?
    ORDER BY published_at DESC
    LIMIT 12
  ");
  $stmtOtros->execute([$slug]);
  $otrasProducciones = $stmtOtros->fetchAll();
} catch (Exception $e) {
  $otrasProducciones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($video['title']) ?> | NZK tvGO</title>
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
    <!-- HERO PRINCIPAL – MISMO LAYOUT QUE video.php -->
    <section class="live-hero-inner">
      <!-- PLAYER VOD -->
      <div class="live-player-frame vod-player-frame">
        <?php if ($provider === 'youtube' && $embedUrl): ?>
          <div class="vod-embed-wrapper">
            <iframe
              src="<?= htmlspecialchars($embedUrl) ?>"
              title="<?= htmlspecialchars($video['title']) ?>"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
            ></iframe>
          </div>
        <?php else: ?>
          <div id="fb-root"></div>
          <div
            class="fb-video vod-embed-wrapper"
            data-href="<?= htmlspecialchars($rawUrl) ?>"
            data-width="100%"
            data-show-text="false"
            data-allowfullscreen="true">
          </div>
        <?php endif; ?>
      </div>

      <!-- FICHA DERECHA -->
      <aside class="live-meta-panel">
        <div class="live-meta-content">
          <span class="live-tag">
            <span class="live-dot"></span>
            NZK PRODUCTORA AUDIOVISUAL
          </span>

          <h1 class="live-title">
            <?= htmlspecialchars($video['title']) ?>
          </h1>

          <?php if ($fechaLegible): ?>
            <p class="live-date"><?= $fechaLegible ?></p>
          <?php endif; ?>

          <p class="live-desc">
            <?= nl2br(htmlspecialchars($video['description'])) ?>
          </p>

          <a href="./en-vivo.php" class="btn btn-live-cta">
            <i class="fa-solid fa-play"></i>
            <span>Volver a señal en vivo</span>
          </a>
        </div>
      </aside>
    </section>

    <!-- CARRUSEL DE PRODUCCIONES RECIENTES -->
    <?php if (!empty($otrasProducciones)): ?>
      <section class="section-carousel section-carousel-episodes container">
        <div class="section-header">
          <h2>Producciones recientes de NZK Productora</h2>
        </div>

        <div class="cards-row">
          <?php foreach ($otrasProducciones as $prod): ?>
            <article class="card video-card">
              <a
                href="./video_productora.php?slug=<?= urlencode($prod['slug']) ?>"
                class="card-link"
              >
                <div class="thumb-placeholder">
                  <?php if (!empty($prod['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($prod['thumbnail']) ?>"
                      alt="<?= htmlspecialchars($prod['title']) ?>"
                      loading="lazy"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($prod['title']) ?></h3>

                <?php if (!empty($prod['published_at'])): ?>
                  <p class="video-date">
                    <?= date('d/m/Y · H:i', strtotime($prod['published_at'])) ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($prod['description'])): ?>
                  <p><?= htmlspecialchars($prod['description']) ?></p>
                <?php endif; ?>
              </a>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="section-more-programs">
          <span class="section-more-line"></span>
          <a href="./productora.php" class="section-more-text">
            Ver todas las producciones
          </a>
          <span class="section-more-line"></span>
        </div>
      </section>
    <?php endif; ?>

  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <?php if ($provider === 'facebook'): ?>
    <!-- SDK de Facebook SOLO si es video de Facebook -->
    <script async defer crossorigin="anonymous"
            src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v18.0"
            nonce="nzkTvGo">
    </script>
  <?php endif; ?>
</body>
</html>
