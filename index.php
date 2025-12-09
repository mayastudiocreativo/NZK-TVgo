<?php
  // Página actual para marcar el menú activo
  $currentPage = 'home';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO -->
  <title>NZK TV en vivo: programación de tvGoNZK hoy gratis</title>
  <meta name="description" content="Disfruta la señal en vivo de NZK Televisión desde tvGoNZK: noticias, entretenimiento, series y películas gratis en línea las 24 horas.">

  <!-- Open Graph / Social -->
  <meta property="og:site_name" content="TvGoNZK" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://tvgo.nzktv.com/" />
  <meta property="og:title" content="NZK Televisión en vivo: disfruta nuestra programación gratis hoy | NZK TV">
  <meta property="og:description" content="Disfruta la programación en vivo de NZK Televisión en tvGoNZK: noticias, entretenimiento, series y películas gratis las 24 horas. TV libre para Nasca, Ica y toda la región. ¡Míralo ahora!">
  <meta property="og:image" content="https://tvgo.nzktv.com/img/IconIOS/icon.png" />
  <meta property="og:image:alt" content="TvGoNZK" />

  <!-- Canonical -->
  <link rel="canonical" href="https://tvgo.nzktv.com/" />

  <!-- PWA -->
  <meta name="theme-color" content="#0a1630" />
  <link rel="manifest" href="manifest.json">

  <!-- Favicon / Iconos NZKtvgo -->
  <link rel="icon" type="image/png" href="./img/IconAndroid/iconhdpi.png" />
  <link rel="apple-touch-icon" href="./img/IconIOS/icon.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="./img/IconIOS/icon@3x.png" />
  <link rel="apple-touch-icon" sizes="120x120" href="./img/IconIOS/icon@2x.png" />

  <!-- Estilos -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <!-- HLS.js (por si luego quieres player en home) -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

  <!-- Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-HQLB82PH72"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-HQLB82PH72');
  </script>

  <!-- Estilos locales solo para el botón de instalar app -->
  <style>
    .install-app-btn {
      position: fixed;
      right: 1rem;
      bottom: 5rem;
      z-index: 1000;
      display: none;
      padding: 0.55rem 1.1rem;
      border-radius: 999px;
      border: 1px solid transparent;
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      align-items: center;
      gap: 0.4rem;
      color: #f5f7ff;
      background:
        linear-gradient(#050b18, #050b18) padding-box,
        linear-gradient(90deg, #ff4a4a, #ff9f3c, #4f8cff, #9b5bff) border-box;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.4);
    }

    .install-app-btn i {
      font-size: 0.9rem;
    }

    .install-app-btn:hover {
      background:
        linear-gradient(#07101f, #07101f) padding-box,
        linear-gradient(90deg, #ff6a6a, #ffb35c, #6f9cff, #b07bff) border-box;
      transform: translateY(-1px);
    }

    @media (min-width: 1024px) {
      .install-app-btn {
        bottom: 2rem;
      }
    }
  </style>
</head>
<body class="page-home">
  <?php include __DIR__ . '/includes/splash.php'; ?>

  <?php
    // Página actual para marcar el menú activo
    $currentPage = 'home';

    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/bottom-nav.php';

    // Conexión a BD para todas las secciones dinámicas
    require __DIR__ . '/includes/db.php';
  ?>

  <main class="page">

    <?php include __DIR__ . '/includes/banner-top.php'; ?>

    <!-- HERO PRINCIPAL -->
    <section class="hero">
      <div class="hero-content">
        <p class="hero-kicker">Plataforma oficial de NZK Televisión</p>

        <h1>Todo NZK en un solo lugar</h1>

        <p class="hero-lead">
          Noticias en vivo, series, películas y eventos especiales desde Nasca, Ica y para todo el Perú.
        </p>

        <div class="hero-actions">
          <a href="./en-vivo.php" class="btn btn-live-cta hero-live-button">
            <i class="fa-solid fa-play"></i>
            <span>Mirar en vivo</span>
          </a>
          <a class="btn secondary" href="./programacion.php">
            <i class="fa-solid fa-calendar-days"></i>
            <span>Ver programación</span>
          </a>
        </div>

        <!-- Mini highlights debajo de los botones -->
        <div class="hero-highlights">
          <article class="hero-highlight-card">
            <div class="hero-highlight-icon">
              <i class="fa-solid fa-newspaper"></i>
            </div>
            <div class="hero-highlight-text">
              <h3>Noticias en video</h3>
              <p>Últimas ediciones de NZK Noticias para ver cuando quieras.</p>
            </div>
          </article>

          <article class="hero-highlight-card">
            <div class="hero-highlight-icon">
              <i class="fa-solid fa-clapperboard"></i>
            </div>
            <div class="hero-highlight-text">
              <h3>Series y novelas</h3>
              <p>Historias completas, temporadas y repeticiones destacadas.</p>
            </div>
          </article>

          <article class="hero-highlight-card">
            <div class="hero-highlight-icon">
              <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <div class="hero-highlight-text">
              <h3>Eventos especiales</h3>
              <p>Fiestas patronales, deportes y transmisiones exclusivas de NZK.</p>
            </div>
          </article>
        </div>
      </div>

      <div class="hero-side">
        <aside class="live-meta-panel live-meta-panel-home">
          <div class="live-meta-poster">
            <img id="home-live-img" src="./img/placeholder.jpg" alt="Programa en vivo">
          </div>

          <div class="live-meta-content">
            <span class="live-tag">
              <span class="live-dot"></span>
              AHORA EN VIVO
            </span>

            <h2 class="live-title" id="home-live-title">
              Cargando programación...
            </h2>

            <div class="live-time" id="home-live-time">
              --:-- – --:-- · —
            </div>

            <p class="live-desc" id="home-live-desc">
              Disfruta nuestra selección de contenidos en la pantalla de NZK Televisión.
            </p>

            <a href="./en-vivo.php" class="btn btn-live-cta hero-live-button">
              <i class="fa-solid fa-play"></i>
              <span>Mirar en vivo</span>
            </a>
          </div>
        </aside>
      </div>
    </section>

    <!-- =========================
         SECCIÓN: ÚLTIMAS NOTICIAS
         category = 'noticias'
    ========================== -->
    <?php
      $stmtNoticias = $pdo->query("
        SELECT id, title, slug, description, thumbnail, published_at
        FROM nzk_videos
        WHERE category = 'noticias'
        ORDER BY published_at DESC
        LIMIT 6
      ");
      $videosNoticias = $stmtNoticias->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-carousel">
      <div class="section-header">
        <h2>Últimas noticias en video</h2>
        <a href="./noticias.php">Ver todas</a>
      </div>

      <div class="cards-row">
        <?php if (empty($videosNoticias)): ?>
          <p>No hay videos registrados todavía.</p>
        <?php else: ?>
          <?php foreach ($videosNoticias as $video): ?>
            <?php
              $fecha = '';
              if (!empty($video['published_at'])) {
                $ts = strtotime($video['published_at']);
                if ($ts) {
                  $fecha = date('d/m/Y · H:i', $ts);
                }
              }
              $videoUrl = './video.php?slug=' . urlencode($video['slug']);
            ?>
            <article class="card video-card">
              <a
                href="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="card-link"
                style="display:block;"
              >

                <div class="thumb-placeholder">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <?php if ($fecha): ?>
                  <p class="video-date"><?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <p><?= htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- =========================
         SECCIÓN: PROGRAMAS DE NZK
         category = 'programas'
    ========================== -->
    <?php
      $stmtProgramas = $pdo->query("
        SELECT id, title, slug, description, thumbnail, created_at
        FROM nzk_programas
        ORDER BY created_at DESC
        LIMIT 20
      ");
      $programasHome = $stmtProgramas->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-carousel section-carousel-programas">
      <div class="section-header">
        <h2>Programas de NZK</h2>
        <!-- Este enlace apuntaría a un listado general de programas si lo creas -->
        <!-- <a href="./programas.php">Ver todos</a> -->
      </div>

      <?php if (empty($programasHome)): ?>
        <p>Todavía no hay programas cargados.</p>
      <?php else: ?>
        <div class="cards-row cards-row-programas">
          <?php foreach ($programasHome as $prog): ?>
            <?php
              $programUrl = './programa.php?slug=' . urlencode($prog['slug']);
            ?>
            <article class="card video-card video-card--programa">
              <!-- Card completo clickeable hacia la ficha del programa -->
              <a
                href="<?= htmlspecialchars($programUrl, ENT_QUOTES, 'UTF-8') ?>"
                class="card-link card-link--full"
              >
                <div class="thumb-placeholder">
                  <?php if (!empty($prog['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($prog['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($prog['title'], ENT_QUOTES, 'UTF-8') ?>"
                      loading="lazy"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Imagen programa</span>
                  <?php endif; ?>
                </div>

                <div class="card-body">
                  <h3 class="video-title">
                    <?= htmlspecialchars($prog['title'], ENT_QUOTES, 'UTF-8') ?>
                  </h3>

                  <?php if (!empty($prog['description'])): ?>
                    <p class="video-description">
                      <?= htmlspecialchars($prog['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                  <?php endif; ?>

                  <span class="card-cta card-cta-programa">Ver programa</span>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- =========================
         SECCIÓN: DOCUMENTALES / ENTREVISTAS
         category = 'doc_entrevistas'
    ========================== -->
    <?php
      $stmtDocs = $pdo->query("
        SELECT id, title, slug, description, thumbnail, published_at
        FROM nzk_videos
        WHERE category = 'doc_entrevistas'
        ORDER BY published_at DESC
        LIMIT 6
      ");
      $videosDocs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-carousel">
      <div class="section-header">
        <h2>Documentales / Entrevistas</h2>
      </div>
      <div class="cards-row">
        <?php if (empty($videosDocs)): ?>
          <p>Aún no hay documentales o entrevistas cargados.</p>
        <?php else: ?>
          <?php foreach ($videosDocs as $video): ?>
            <?php
              $fechaDoc = '';
              if (!empty($video['published_at'])) {
                $tsDoc = strtotime($video['published_at']);
                if ($tsDoc) {
                  $fechaDoc = date('d/m/Y · H:i', $tsDoc);
                }
              }
              $videoUrl = './video.php?slug=' . urlencode($video['slug']);
            ?>
            <article class="card video-card">
              <a href="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>" class="card-link">
                <div class="thumb-placeholder">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <?php if ($fechaDoc): ?>
                  <p class="video-date"><?= htmlspecialchars($fechaDoc, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <p><?= htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- =========================
         SECCIÓN: DEPORTES
         category = 'deportes'
    ========================== -->
    <?php
      $stmtDep = $pdo->query("
        SELECT id, title, slug, description, thumbnail, published_at
        FROM nzk_videos
        WHERE category = 'deportes'
        ORDER BY published_at DESC
        LIMIT 6
      ");
      $videosDep = $stmtDep->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-carousel">
      <div class="section-header">
        <h2>Deportes</h2>
      </div>
      <div class="cards-row">
        <?php if (empty($videosDep)): ?>
          <p>No hay videos deportivos cargados todavía.</p>
        <?php else: ?>
          <?php foreach ($videosDep as $video): ?>
            <?php
              $fechaDep = '';
              if (!empty($video['published_at'])) {
                $tsDep = strtotime($video['published_at']);
                if ($tsDep) {
                  $fechaDep = date('d/m/Y · H:i', $tsDep);
                }
              }
              $videoUrl = './video.php?slug=' . urlencode($video['slug']);
            ?>
            <article class="card video-card">
              <a href="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>" class="card-link">
                <div class="thumb-placeholder">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <?php if ($fechaDep): ?>
                  <p class="video-date"><?= htmlspecialchars($fechaDep, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <p><?= htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>


    <!-- =========================
         SECCIÓN: EVENTOS ESPECIALES
         category = 'eventos'
    ========================== -->
    <?php
      $stmtEventos = $pdo->query("
        SELECT id, title, slug, description, thumbnail, published_at
        FROM nzk_videos
        WHERE category = 'eventos'
        ORDER BY published_at DESC
        LIMIT 6
      ");
      $videosEventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-carousel">
      <div class="section-header">
        <h2>Eventos especiales</h2>
        <a href="/eventos.php">Ver eventos</a>
      </div>
      <div class="cards-row">
        <?php if (empty($videosEventos)): ?>
          <p>No hay eventos especiales registrados todavía.</p>
        <?php else: ?>
          <?php foreach ($videosEventos as $video): ?>
            <?php
              $fechaEvt = '';
              if (!empty($video['published_at'])) {
                $tsEvt = strtotime($video['published_at']);
                if ($tsEvt) {
                  $fechaEvt = date('d/m/Y · H:i', $tsEvt);
                }
              }
              $videoUrl = './video.php?slug=' . urlencode($video['slug']);
            ?>
            <article class="card video-card">
              <a href="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>" class="card-link">
                <div class="thumb-placeholder thumb-placeholder-news">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <?php if ($fechaEvt): ?>
                  <p class="video-date"><?= htmlspecialchars($fechaEvt, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <p><?= htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- HERO NZK PRODUCTORA AUDIOVISUAL -->
    <section class="hero producer-hero">
      <div class="hero-content">
        <p class="hero-kicker">Servicios profesionales de producción</p>

        <h1>NZK Productora Audiovisual</h1>

        <p class="hero-lead">
          Streaming en vivo, multicámara, drones, CCTV para eventos, enlaces vía Starlink y producción de spots
          publicitarios para marcas y eventos en todo el Perú.
        </p>

        <div class="hero-actions">
          <a href="./productora.php" class="btn btn-live-cta hero-live-button">
            <i class="fa-solid fa-circle-play"></i>
            <span>Ver portafolio</span>
          </a>

          <a href="https://wa.me/956000000" class="btn secondary">
            <i class="fa-brands fa-whatsapp"></i>
            <span>Solicitar cotización</span>
          </a>
        </div>

        <div class="hero-highlights">
          <article class="hero-highlight-card">
            <div class="hero-highlight-icon"><i class="fa-solid fa-tower-broadcast"></i></div>
            <div class="hero-highlight-text">
              <h3>Streaming Full HD</h3>
              <p>Transmisiones en vivo para eventos masivos.</p>
            </div>
          </article>

          <article class="hero-highlight-card">
            <div class="hero-highlight-icon"><i class="fa-solid fa-video"></i></div>
            <div class="hero-highlight-text">
              <h3>CCTV multicámara</h3>
              <p>Conciertos, conferencias y eventos corporativos.</p>
            </div>
          </article>

          <article class="hero-highlight-card">
            <div class="hero-highlight-icon"><i class="fa-solid fa-photo-film"></i></div>
            <div class="hero-highlight-text">
              <h3>Spots publicitarios</h3>
              <p>Producción comercial y contenido para marcas.</p>
            </div>
          </article>
        </div>
      </div>

      <div class="hero-side">
        <aside class="live-meta-panel live-meta-panel-home">
          <div class="live-meta-poster">
            <img src="./img/productora/portada.jpg" alt="NZK Productora Audiovisual">
          </div>

          <div class="live-meta-content">
            <span class="live-tag">
              <span class="live-dot"></span>
              Portafolio NZK
            </span>

            <h2 class="live-title">
              Producciones destacadas
            </h2>

            <p class="live-desc">
              Eventos deportivos, festivales, ferias y campañas institucionales
              producidas por el equipo de NZK Productora.
            </p>

            <a href="./productora.php" class="btn btn-live-cta">
              <i class="fa-solid fa-camera"></i>
              <span>Ver proyectos</span>
            </a>
          </div>
        </aside>
      </div>
    </section>

    <!-- Carrusel de videos de NZK Productora -->
    <?php
      $stmtProd = $pdo->query("
        SELECT id, title, slug, thumbnail, description, published_at
        FROM nzk_videos
        WHERE category = 'productora'
        ORDER BY published_at DESC
        LIMIT 6
      ");
      $producerVideos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section class="section-carousel">
      <div class="section-header">
        <h2>Producciones de NZK Productora</h2>
        <a href="./productora.php">Ver todas</a>
      </div>

      <div class="cards-row">
        <?php if (empty($producerVideos)): ?>
          <p>Aún no hay videos cargados en NZK Productora.</p>
        <?php else: ?>
          <?php foreach ($producerVideos as $video): ?>
            <?php
              $fechaProd = '';
              if (!empty($video['published_at'])) {
                $ts2 = strtotime($video['published_at']);
                if ($ts2) {
                  $fechaProd = date('d/m/Y · H:i', $ts2);
                }
              }
              $videoUrl = './video_productora.php?slug=' . urlencode($video['slug']);
            ?>
            <article class="card video-card">
              <!-- Para la productora usamos el wrapper video_productora.php -->
              <a href="<?= htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8') ?>" class="card-link">

                <div class="thumb-placeholder">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail'], ENT_QUOTES, 'UTF-8') ?>"
                      alt="<?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8') ?></h3>

                <?php if ($fechaProd): ?>
                  <p class="video-date"><?= htmlspecialchars($fechaProd, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <p><?= htmlspecialchars($video['description'], ENT_QUOTES, 'UTF-8') ?></p>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- Banner inferior -->
    <?php include __DIR__ . '/includes/banner-bottom.php'; ?>

  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- BOTÓN FLOTANTE PARA INSTALAR LA APP -->
  <button id="installAppBtn" class="install-app-btn" type="button">
    <i class="fa-solid fa-download"></i>
    <span>Instalar app</span>
  </button>

  <!-- JS principal -->
  <script src="./assets/js/main.js"></script>

  <!-- Registro del Service Worker + lógica de instalación PWA -->
  <script>
    if ("serviceWorker" in navigator) {
      window.addEventListener("load", () => {
        navigator.serviceWorker
          .register("/service-worker.js")
          .then(() => console.log("✅ Service Worker registrado"))
          .catch((err) => console.log("❌ Error al registrar SW:", err));
      });
    }

    let deferredPrompt;
    const installBtn = document.getElementById("installAppBtn");

    window.addEventListener("beforeinstallprompt", (e) => {
      e.preventDefault();
      deferredPrompt = e;

      if (installBtn) {
        installBtn.style.display = "flex";
      }
      console.log("✅ PWA instalable, se muestra botón de instalar");
    });

    if (installBtn) {
      installBtn.addEventListener("click", async () => {
        if (!deferredPrompt) return;

        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log("Resultado de la instalación:", outcome);

        deferredPrompt = null;
        installBtn.style.display = "none";
      });
    }

    const items = document.querySelectorAll(".nav-item");
    items.forEach(item => {
      if (item.href === window.location.href) {
        item.classList.add("active");
      }
    });
  </script>

</body>
</html>
