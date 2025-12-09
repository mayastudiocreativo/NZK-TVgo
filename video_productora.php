<?php
// video_productora.php
require __DIR__ . '/includes/db.php';

// ===========================
// Leer y validar slug
// ===========================
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';

if ($slug === '' || !preg_match('/^[a-z0-9\-]+$/i', $slug)) {
  header('Location: ./productora.php');
  exit;
}

// ===========================
// 1) Traer el video actual (solo categoría 'productora')
// ===========================
$stmt = $pdo->prepare("
  SELECT *
  FROM nzk_videos
  WHERE slug = ?
    AND category = 'productora'
  LIMIT 1
");
$stmt->execute([$slug]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
  http_response_code(404);
  echo 'Video no encontrado';
  exit;
}

// ===========================
// 2) Preparar URL de embed (YouTube o Facebook)
// ===========================
$rawUrl   = trim($video['fb_url'] ?? '');
$provider = 'facebook';
$embedUrl = $rawUrl;

if ($rawUrl === '') {
  $provider = 'none';
} else {
  if (stripos($rawUrl, 'youtube.com') !== false || stripos($rawUrl, 'youtu.be') !== false) {
    $provider = 'youtube';

    $ytId = null;
    if (preg_match('~(?:v=|/)([A-Za-z0-9_-]{6,})~', $rawUrl, $m)) {
      $ytId = $m[1];
    }

    if ($ytId) {
      $embedUrl = 'https://www.youtube.com/embed/' . $ytId;
    } else {
      // Fallback: usa la URL tal cual
      $embedUrl = $rawUrl;
    }
  } else {
    // No es YouTube: asumimos Facebook (como estaba)
    $provider = 'facebook';
  }
}

// ===========================
// 3) Fecha legible
// ===========================
$fechaLegible = '';
if (!empty($video['published_at'])) {
  $ts = strtotime($video['published_at']);
  if ($ts) {
    $fechaLegible = date('d/m/Y · H:i', $ts);
  }
}

// ===========================
// 4) Otras producciones recientes de NZK Productora
// ===========================
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
  $otrasProducciones = $stmtOtros->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $otrasProducciones = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($video['title'] ?? 'Producción', ENT_QUOTES, 'UTF-8') ?> | NZK tvGO</title>
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
              src="<?= htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8') ?>"
              title="<?= htmlspecialchars($video['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
            ></iframe>
          </div>

        <?php elseif ($provider === 'facebook'): ?>

          <div id="fb-root"></div>
          <div
            class="fb-video vod-embed-wrapper"
            data-href="<?= htmlspecialchars($rawUrl, ENT_QUOTES, 'UTF-8') ?>"
            data-width="100%"
            data-show-text="false"
            data-allowfullscreen="true">
          </div>

        <?php else: ?>

          <div class="vod-embed-wrapper vod-embed-placeholder">
            <p>No se encontró la URL del video para esta producción.</p>
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
            <?= htmlspecialchars($video['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>
          </h1>

          <?php if ($fechaLegible): ?>
            <p class="live-date"><?= htmlspecialchars($fechaLegible, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>

          <p class="live-desc">
            <?= nl2br(htmlspecialchars($video['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
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
            <?php
              $fechaProd = '';
              if (!empty($prod['published_at'])) {
                $tsProd = strtotime($prod['published_at']);
                if ($tsProd) {
                  $fechaProd = date('d/m/Y · H:i', $tsProd);
                }
              }
              $prodUrl = './video_productora.php?slug=' . urlencode($prod['slug']);
            ?>
            <article class="card video-card">
              <a
                href="<?= htmlspecialchars($prodUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="card-link"
              >
                <div class="thumb-placeholder">
                  <?php if (!empty($prod['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($prod['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($prod['title'], ENT_QUOTES, 'UTF-8') ?>"
                      loading="lazy"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($prod['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <?php if ($fechaProd): ?>
                  <p class="video-date">
                    <?= htmlspecialchars($fechaProd, ENT_QUOTES, 'UTF-8') ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($prod['description'])): ?>
                  <p><?= htmlspecialchars($prod['description'], ENT_QUOTES, 'UTF-8') ?></p>
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
