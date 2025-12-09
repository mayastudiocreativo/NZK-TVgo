<?php
  // Página actual para marcar el menú activo
  $currentPage = 'live';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO -->
  <title>NZK TV EN VIVO | Señal online de tvGoNZK las 24 horas</title>
  <meta name="description" content="Mira la señal en vivo de NZK Televisión desde tvGoNZK. Noticias, programas en directo, eventos especiales y más, gratis y en línea las 24 horas.">

  <!-- Open Graph / Social -->
  <meta property="og:site_name" content="TvGoNZK" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://tvgo.nzktv.com/en-vivo.php" />
  <meta property="og:title" content="NZK TV EN VIVO | Señal online de tvGoNZK las 24 horas">
  <meta property="og:description" content="Conéctate a la señal en vivo de NZK Televisión desde tvGoNZK y disfruta de nuestra programación continua desde cualquier dispositivo.">
  <meta property="og:image" content="https://tvgo.nzktv.com/img/IconIOS/icon.png" />
  <meta property="og:image:alt" content="TvGoNZK" />

  <!-- Canonical -->
  <link rel="canonical" href="https://tvgo.nzktv.com/en-vivo.php" />

  <!-- PWA -->
  <meta name="theme-color" content="#0a1630" />
  <link rel="manifest" href="./manifest.json">

  <!-- Favicon / Iconos NZKtvgo -->
  <link rel="icon" type="image/png" href="./img/IconAndroid/iconhdpi.png" />
  <link rel="apple-touch-icon" href="./img/IconIOS/icon.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="./img/IconIOS/icon@3x.png" />
  <link rel="apple-touch-icon" sizes="120x120" href="./img/IconIOS/icon@2x.png" />

  <!-- Estilos -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <!-- HLS.js para el player en vivo -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

  <!-- Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-HQLB82PH72"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-HQLB82PH72');
  </script>
</head>
<body class="page-live">
  <?php include __DIR__ . '/includes/splash.php'; ?>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <?php include __DIR__ . '/includes/bottom-nav.php'; ?>

  <main class="page page-live-ott">

    <!-- Banner superior -->
    <section class="banner-slot banner-top">
      <div class="banner-placeholder">Espacio para banner publicitario superior</div>
    </section>

    <!-- HERO EN VIVO: PLAYER + PANEL -->
    <section class="live-hero">
      <div class="live-hero-inner">
        <!-- Player -->
        <div class="live-player-frame">
          <video id="videoPlayer" controls autoplay playsinline></video>

          <!-- Botón de calidad tipo YouTube -->
          <button id="qualityToggle" class="quality-toggle-btn" type="button">
            HD
          </button>
        </div>

        <!-- Panel lateral (info del programa en vivo) -->
        <div class="live-meta-panel">
          <div class="live-meta-poster">
            <img id="live-main-img" src="./img/placeholder.jpg" alt="">
          </div>
          <div class="live-meta-content">
            <span class="live-tag">AHORA EN VIVO</span>
            <h2 class="live-title" id="live-main-title"></h2>
            <div class="live-time" id="live-main-time"></div>

            <!-- AHORA con ID para que JS pueda actualizar la descripción -->
            <p class="live-desc" id="live-main-desc">
              Disfruta nuestra selección de películas para toda la familia en la pantalla de NZK Televisión.
            </p>

            <a href="./programacion.php" class="btn btn-live-cta">
              <i class="fa-solid fa-list"></i>
              <span>Ver programación completa</span>
            </a>
          </div>
        </div>
      </div>

      <!-- STRIP DE 4 CARDS: A continuación / Más adelante / Próximamente / Muy pronto -->
      <section class="live-strip">
        <div class="live-strip-cards">

          <!-- Card 1: A continuación -->
          <article class="live-card" id="card-next">
            <div class="live-card-thumb">
              <img id="next-img" src="./img/cine.jpg" alt="Programa a continuación">
            </div>
            <div class="live-card-body">
              <div class="live-label live-label-next">A continuación</div>
              <h3 id="next-title" class="live-card-title">Súbete a mi Moto</h3>
              <p id="next-time" class="live-card-time">19:00 - 19:50</p>
              <p id="next-category" class="live-card-category">Serie</p>
            </div>
          </article>

          <!-- Card 2: Más adelante -->
          <article class="live-card" id="card-later">
            <div class="live-card-thumb">
              <img id="later-img" src="./img/cine.jpg" alt="Programa más adelante">
            </div>
            <div class="live-card-body">
              <div class="live-label live-label-later">Más adelante</div>
              <h3 id="later-title" class="live-card-title">HIT TV NZK</h3>
              <p id="later-time" class="live-card-time">19:50 - 20:00</p>
              <p id="later-category" class="live-card-category">Música</p>
            </div>
          </article>

          <!-- Card 3: Próximamente -->
          <article class="live-card" id="card-soon">
            <div class="live-card-thumb">
              <img id="soon-img" src="./img/cine.jpg" alt="Programa próximamente">
            </div>
            <div class="live-card-body">
              <div class="live-label live-label-soon">Próximamente</div>
              <h3 id="soon-title" class="live-card-title">Programa próximamente</h3>
              <p id="soon-time" class="live-card-time">20:00 - 21:00</p>
              <p id="soon-category" class="live-card-category">Entretenimiento</p>
            </div>
          </article>

          <!-- Card 4: Muy pronto -->
          <article class="live-card live-card-soon">
            <div class="live-card-thumb">
              <img src="./img/novelas/miCaminoEsAmarte.jpg" alt="Próximo programa">
            </div>

            <div class="live-label live-label-later">Muy pronto</div>

            <div class="live-card-body">
              <h3 class="live-card-title">Nuevo programa exclusivo</h3>
              <p class="live-card-time">Próximamente</p>
              <p class="live-card-category">Novedades</p>
            </div>
          </article>

        </div>
      </section>
    </section>

    <!-- Carrusel de repeticiones / VOD relacionados -->
    <section class="section-carousel">
      <div class="section-header">
        <h2>Repeticiones recientes</h2>
        <a href="./programa.php">Ver todos los programas</a>
      </div>
      <div class="cards-row">
        <article class="card video-card">
          <div class="thumb-placeholder">Thumbnail</div>
          <h3>Cine en Casa - Función pasada</h3>
          <p>Revive la última película emitida en NZK.</p>
        </article>
        <article class="card video-card">
          <div class="thumb-placeholder">Thumbnail</div>
          <h3>Súbete a mi Moto - Episodio reciente</h3>
          <p>Capítulo emitido en la franja de series.</p>
        </article>
        <article class="card video-card">
          <div class="thumb-placeholder">Thumbnail</div>
          <h3>HIT TV NZK - Especial musical</h3>
          <p>Repetición de los mejores videoclips.</p>
        </article>
      </div>
    </section>

    <!-- Eventos especiales -->
    <section class="section-carousel">
      <div class="section-header">
        <h2>Eventos especiales</h2>
        <a href="./eventos.php">Ver eventos</a>
      </div>
      <div class="cards-row">
        <article class="card event-card">
          <div class="thumb-placeholder">Imagen evento</div>
          <h3>Fiestas Patronales de Nasca</h3>
          <p>Repetición del día central y serenata.</p>
        </article>
        <article class="card event-card">
          <div class="thumb-placeholder">Imagen evento</div>
          <h3>Panorama Electoral</h3>
          <p>Especial con análisis y resultados.</p>
        </article>
      </div>
    </section>

    <!-- Banner inferior -->
    <section class="banner-slot banner-bottom">
      <div class="banner-placeholder">Espacio para banner publicitario inferior</div>
    </section>

  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <!-- JS principal -->
  <script src="./assets/js/main.js"></script>
  <script>
    if ("serviceWorker" in navigator) {
      window.addEventListener("load", () => {
        navigator.serviceWorker
          .register("./service-worker.js")
          .then(() => console.log("✅ Service Worker registrado"))
          .catch((err) => console.log("❌ Error al registrar SW:", err));
      });
    }
  </script>
</body>
</html>
