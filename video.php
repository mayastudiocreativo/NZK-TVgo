<?php
// video.php
require __DIR__ . '/includes/db.php';

// ===========================
// Leer y validar parámetros
// ===========================
$slug           = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$programFromUrl = isset($_GET['program']) ? trim((string)$_GET['program']) : null;

// Validar que el slug tenga el formato esperado (coincide con tu generador de slugs)
if ($slug === '' || !preg_match('/^[a-z0-9\-]+$/i', $slug)) {
    header('Location: ./');
    exit;
}

// Validar también el "program" si viene
if ($programFromUrl !== null && $programFromUrl !== '' && !preg_match('/^[a-z0-9\-]+$/i', $programFromUrl)) {
    $programFromUrl = null;
}

// ===========================
// Traer el video actual por slug
// ===========================
$stmt = $pdo->prepare("SELECT * FROM nzk_videos WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    http_response_code(404);
    echo "Video no encontrado";
    exit;
}

// ¿Es video de la productora?
$isProductora = (($video['category'] ?? '') === 'productora');

// -----------------------------------------
// Determinar el "slug" del programa
// prioridad:
// 1) parámetro ?program= (desde programa.php / video_programa.php)
// 2) patrón del slug del episodio: antes de "-Ep"
// -----------------------------------------
$programSlugBase = $programFromUrl;

if (!$programSlugBase && preg_match('~^(.*?)-Ep[0-9]+~i', $slug, $m)) {
    // ej: yo-emprendedor-Ep01-Temp01 -> yo-emprendedor
    $programSlugBase = $m[1];
}

// ===========================
// Preparar URL de video (Facebook / YouTube)
// ===========================
$rawUrl   = trim($video['fb_url'] ?? '');
$provider = 'facebook';
$embedUrl = $rawUrl;

if ($rawUrl === '') {
    // Sin URL -> sin provider
    $provider = 'none';
} else {
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
    } else {
        // Si no se detecta YouTube, asumimos Facebook (como estaba)
        $provider = 'facebook';
    }
}

// ===========================
// Fecha legible
// ===========================
$fechaLegible = '';
if (!empty($video['published_at'])) {
    $ts = strtotime($video['published_at']);
    if ($ts) {
        $fechaLegible = date('d/m/Y · H:i', $ts);
    }
}

// ===========================
// EPISODIOS / PRODUCCIONES RECIENTES
// ===========================
$episodiosRecientes = [];

try {
    if ($isProductora) {
        // Solo producciones de la productora
        $stmtEp = $pdo->prepare(
            "SELECT *
             FROM nzk_videos
             WHERE category = 'productora'
               AND slug <> ?
             ORDER BY published_at DESC
             LIMIT 12"
        );
        $stmtEp->execute([$slug]);

    } elseif ($programSlugBase) {
        // Episodios del mismo programa (por prefijo de slug)
        $stmtEp = $pdo->prepare(
            "SELECT *
             FROM nzk_videos
             WHERE slug LIKE ?
               AND slug <> ?
             ORDER BY published_at DESC
             LIMIT 12"
        );
        $stmtEp->execute([$programSlugBase . '%', $slug]);

    } else {
        // Fallback: últimos videos globales
        $stmtEp = $pdo->prepare(
            "SELECT *
             FROM nzk_videos
             WHERE slug <> ?
             ORDER BY published_at DESC
             LIMIT 12"
        );
        $stmtEp->execute([$slug]);
    }

    $episodiosRecientes = $stmtEp->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $episodiosRecientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($video['title'] ?? 'Video', ENT_QUOTES, 'UTF-8') ?> | NZK tvGO</title>
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
            <p>No se encontró la URL del video para este contenido.</p>
          </div>

        <?php endif; ?>
      </div>

      <!-- FICHA DERECHA -->
      <aside class="live-meta-panel">
        <div class="live-meta-content">
          <span class="live-tag">
            <span class="live-dot"></span>
            <?php if ($isProductora): ?>
              NZK PRODUCTORA AUDIOVISUAL
            <?php else: ?>
              NZK NOTICIAS EN VIDEO
            <?php endif; ?>
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

    <?php if (!empty($episodiosRecientes)): ?>
      <section class="section-carousel section-carousel-episodes container">
        <div class="section-header">
          <h2>
            <?= $isProductora
              ? 'Producciones recientes de NZK Productora'
              : 'Episodios recientes de este programa'; ?>
          </h2>
        </div>

        <div class="cards-row">
          <?php foreach ($episodiosRecientes as $ep): ?>
            <?php
              // preservamos el ?program= en los links del carrusel SOLO para programas
              $programQuery = (!$isProductora && $programSlugBase)
                ? '&program=' . urlencode($programSlugBase)
                : '';
              $epUrl = './video.php?slug=' . urlencode($ep['slug']) . $programQuery;
            ?>
            <article class="card episode-card-slider">
              <a
                href="<?= htmlspecialchars($epUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="episode-link js-episode-link"
                data-episode-url="<?= htmlspecialchars($epUrl, ENT_QUOTES, 'UTF-8') ?>"
              >
                <div class="episode-thumb">
                  <?php if (!empty($ep['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($ep['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($ep['title'], ENT_QUOTES, 'UTF-8') ?>"
                      loading="lazy"
                    >
                  <?php else: ?>
                    <span>Sin imagen</span>
                  <?php endif; ?>
                </div>

                <div class="episode-info">
                  <h3 class="episode-title">
                    <?= htmlspecialchars($ep['title'], ENT_QUOTES, 'UTF-8') ?>
                  </h3>

                  <?php if (!empty($ep['published_at'])): ?>
                    <p class="episode-meta">
                      <?= htmlspecialchars(date('d/m/Y · H:i', strtotime($ep['published_at'])), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                  <?php endif; ?>

                  <?php if (!empty($ep['description'])): ?>
                    <p class="episode-description">
                      <?= htmlspecialchars($ep['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                  <?php endif; ?>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>

        <?php if (!$isProductora && $programSlugBase): ?>
          <!-- Línea + texto "Ver más programas" SOLO para programas -->
          <div class="section-more-programs">
            <span class="section-more-line"></span>
            <a
              href="./programa.php?slug=<?= urlencode($programSlugBase) ?>"
              class="section-more-text"
            >
              Ver más programas
            </a>
            <span class="section-more-line"></span>
          </div>
        <?php endif; ?>
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

  <!-- JS para cambiar de episodio sin salir de video.php -->
  <script>
  function initEpisodeLinks() {
    const episodeLinks = document.querySelectorAll('.js-episode-link');
    if (!episodeLinks.length) return;

    episodeLinks.forEach(link => {
      link.addEventListener('click', async function (e) {
        if (!window.fetch || !window.history) return; // fallback navegando normal

        e.preventDefault();

        const url = this.dataset.episodeUrl || this.href;
        const heroContainer = document.querySelector('.page-video .live-hero-inner');

        if (!heroContainer || !url) {
          window.location.href = url;
          return;
        }

        heroContainer.style.opacity = '0.35';

        try {
          const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          const html = await res.text();

          const temp = document.createElement('div');
          temp.innerHTML = html;

          let newHero = temp.querySelector('.page-video .live-hero-inner');
          if (!newHero) {
            newHero = temp.querySelector('.live-hero-inner');
          }

          if (newHero) {
            heroContainer.replaceWith(newHero);
            window.history.pushState({}, '', url);
            // Re-inicializar enlaces en el nuevo DOM
            initEpisodeLinks();
          } else {
            window.location.href = url;
          }
        } catch (err) {
          console.error('Error cargando episodio:', err);
          window.location.href = url;
        } finally {
          const currentHero = document.querySelector('.page-video .live-hero-inner');
          if (currentHero) {
            currentHero.style.opacity = '1';
          }
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', initEpisodeLinks);
  </script>
</body>
</html>
