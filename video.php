<?php
// video.php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
if ($slug === '') {
  header('Location: ./');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM nzk_videos WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$video = $stmt->fetch();

if (!$video) {
  http_response_code(404);
  echo "Video no encontrado";
  exit;
}

// URL puede ser Facebook o YouTube
$rawUrl = trim($video['fb_url']);
$provider = 'facebook';
$embedUrl = $rawUrl;

// Detectar YouTube
if (stripos($rawUrl, 'youtube.com') !== false || stripos($rawUrl, 'youtu.be') !== false) {
  $provider = 'youtube';

  // Sacar ID de YouTube
  $ytId = null;
  if (preg_match('~(?:v=|\/)([A-Za-z0-9_-]{6,})~', $rawUrl, $m)) {
    $ytId = $m[1];
  }

  if ($ytId) {
    $embedUrl = 'https://www.youtube.com/embed/' . $ytId;
  } else {
    // fallback: usa la url tal cual
    $embedUrl = $rawUrl;
  }
}

// Fecha legible
$fechaLegible = '';
if (!empty($video['published_at'])) {
  $ts = strtotime($video['published_at']);
  if ($ts) {
    $fechaLegible = date('d/m/Y · H:i', $ts);
  }
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
    <section class="live-hero-inner">
      <!-- PLAYER VOD -->
      <div class="live-player-frame vod-player-frame">
        <?php if ($provider === 'youtube'): ?>

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
            <?php if (($video['category'] ?? '') === 'productora'): ?>
              NZK PRODUCTORA AUDIOVISUAL
            <?php else: ?>
              NZK NOTICIAS EN VIDEO
            <?php endif; ?>
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