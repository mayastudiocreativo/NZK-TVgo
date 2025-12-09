<?php
// productora.php
$currentPage = 'productora';

require __DIR__ . '/includes/db.php';

// Traer TODOS los videos de la productora
$stmtProd = $pdo->query("
  SELECT id, title, slug, thumbnail, description, published_at
  FROM nzk_videos
  WHERE category = 'productora'
  ORDER BY published_at DESC
");
$producerVideos = $stmtProd->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO -->
  <title>NZK Productora Audiovisual: streaming, multicámara y spots | NZK TV</title>
  <meta name="description" content="NZK Productora Audiovisual ofrece streaming en vivo, producción multicámara, drones, CCTV para eventos, enlaces vía Starlink y spots publicitarios para marcas en todo el Perú.">

  <!-- Open Graph / Social -->
  <meta property="og:site_name" content="TvGoNZK" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://tvgo.nzktv.com/productora.php" />
  <meta property="og:title" content="NZK Productora Audiovisual: producciones y transmisiones en vivo" />
  <meta property="og:description" content="Conoce el portafolio de NZK Productora Audiovisual: conciertos, festivales, ferias y campañas institucionales producidas y transmitidas en vivo." />
  <meta property="og:image" content="https://tvgo.nzktv.com/img/productora/portada.jpg" />
  <meta property="og:image:alt" content="NZK Productora Audiovisual" />

  <!-- Canonical -->
  <link rel="canonical" href="https://tvgo.nzktv.com/productora.php" />

  <!-- PWA / Iconos -->
  <meta name="theme-color" content="#0a1630" />
  <link rel="manifest" href="manifest.json">

  <link rel="icon" type="image/png" href="./img/IconAndroid/iconhdpi.png" />
  <link rel="apple-touch-icon" href="./img/IconIOS/icon.png" />
  <link rel="apple-touch-icon" sizes="180x180" href="./img/IconIOS/icon@3x.png" />
  <link rel="apple-touch-icon" sizes="120x120" href="./img/IconIOS/icon@2x.png" />

  <!-- Estilos -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="page-home page-productora">
  <?php
    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/bottom-nav.php';
  ?>

  <main class="page">

    <!-- HERO PRINCIPAL PRODUCTORA -->
    <section class="hero producer-hero">
      <div class="hero-content">
        <p class="hero-kicker">Servicios profesionales de producción</p>

        <h1>NZK Productora Audiovisual</h1>

        <p class="hero-lead">
          Streaming en vivo en Full HD, producción multicámara, drones, CCTV para eventos,
          enlaces vía Starlink y spots publicitarios para marcas, instituciones y eventos
          en todo el Perú.
        </p>

        <div class="hero-actions">
          <a href="#portafolio" class="btn btn-live-cta hero-live-button">
            <i class="fa-solid fa-circle-play"></i>
            <span>Ver portafolio</span>
          </a>

          <a href="https://wa.me/956000000" class="btn secondary" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp"></i>
            <span>Solicitar cotización</span>
          </a>
        </div>
      </div>

      <div class="hero-side">
        <aside class="live-meta-panel live-meta-panel-home">
          <div class="live-meta-poster">
            <img src="./img/productora/portada.jpg" alt="Portafolio NZK Productora Audiovisual">
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
              Conciertos, aniversarios, fiestas patronales y campañas institucionales
              producidas por el equipo de NZK Productora Audiovisual.
            </p>

            <a href="#galeria-producciones" class="btn btn-live-cta">
              <i class="fa-solid fa-camera"></i>
              <span>Ver proyectos</span>
            </a>
          </div>
        </aside>
      </div>
    </section>

    <!-- Mini highlights debajo del hero (full width) -->
    <div class="hero-highlights">
      <article class="hero-highlight-card">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-tower-broadcast"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>Streaming Full HD</h3>
          <p>Transmisiones en vivo para conciertos, festivales y eventos masivos.</p>
        </div>
      </article>

      <article class="hero-highlight-card">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-video"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>CCTV multicámara</h3>
          <p>Conciertos, conferencias, ferias y eventos corporativos.</p>
        </div>
      </article>

      <article class="hero-highlight-card">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-photo-film"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>Spots publicitarios</h3>
          <p>Producción comercial y contenido para marcas e instituciones.</p>
        </div>
      </article>
    </div>

    <!-- CTA PRINCIPAL -->
    <section class="section-list section-cta-productora">
      <div class="section-header">
        <h2>¿Listo para llevar tu evento al siguiente nivel?</h2>
      </div>

      <div class="cards-column">
        <article class="card news-card cta-card">
          <div class="cta-layout">
            <!-- ICONO TIPO CARD -->
            <div class="hero-highlight-icon cta-icon">
              <i class="fa-solid fa-bolt"></i>
            </div>

            <div class="cta-content">
              <h3>Producción integral — de la idea a la emisión</h3>
              <p>
                Nos encargamos de todo: planificación técnica, producción, sonido, mezcla multicámara,
                gráficos on-air, cámara, grabación master y versiones para redes sociales.
                Ideal para conciertos, eventos corporativos, ferias, ceremonias y lanzamientos.
              </p>

              <div class="cta-actions">
                <a href="https://wa.me/956000000" target="_blank" rel="noopener" class="btn btn-live-cta">
                  <i class="fa-brands fa-whatsapp"></i>
                  <span>Contactar Producción</span>
                </a>
                <a href="mailto:produccion@nzktv.com" class="btn secondary">
                  <i class="fa-solid fa-envelope"></i>
                  <span>Enviar Briefing</span>
                </a>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- SERVICIOS (4 cards, 2x2) -->
    <section class="section-list producer-services">
  <div class="section-header">
    <h2>Servicios que ofrecemos</h2>
  </div>

  <div class="hero-highlights producer-services-grid">

    <!-- 1 -->
    <article class="hero-highlight-card producer-service-card">
      <!-- IMAGEN SUPERIOR -->
      <div class="producer-service-media">
        <img src="./img/productora/servicios/streaming.jpg" alt="Streaming profesional">
      </div>

      <div class="producer-service-body">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-tower-broadcast"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>Streaming / Live Streaming profesional</h3>
          <p>
            Transmisión simultánea en plataformas como YouTube, Facebook, Zoom
            o landing privada. Ideal para eventos corporativos, conferencias,
            webinars y conciertos en vivo.
          </p>
        </div>
      </div>
    </article>

    <!-- 2 -->
    <article class="hero-highlight-card producer-service-card">
      <div class="producer-service-media">
        <img src="./img/productora/servicios/multicamara.jpg" alt="Producción multicámara">
      </div>

      <div class="producer-service-body">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-video"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>Producción multicámara y cobertura completa</h3>
          <p>
            Desde cámaras en grúa hasta drones, estabilizadores y grabación master.
            Cubrimos eventos deportivos, conciertos, shows, ceremonias y eventos sociales.
          </p>
        </div>
      </div>
    </article>

    <!-- 3 -->
    <article class="hero-highlight-card producer-service-card">
      <div class="producer-service-media">
        <img src="./img/productora/servicios/spots.jpg" alt="Spots publicitarios">
      </div>

      <div class="producer-service-body">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>Spots publicitarios, post-producción y difusión</h3>
          <p>
            Producción audiovisual para campañas, anuncios en redes o televisión,
            con edición, color grading, motion graphics y master final listo
            para emisión o digital.
          </p>
        </div>
      </div>
    </article>

    <!-- 4 -->
    <article class="hero-highlight-card producer-service-card">
      <div class="producer-service-media">
        <img src="./img/productora/servicios/drones.jpg" alt="Cobertura con drones">
      </div>

      <div class="producer-service-body">
        <div class="hero-highlight-icon">
          <i class="fa-solid fa-helicopter"></i>
        </div>
        <div class="hero-highlight-text">
          <h3>Cobertura con drones y video aéreo</h3>
          <p>
            Grabaciones con drones, ideales para eventos al aire libre,
            conciertos, ferias e imágenes aéreas impactantes para marcas
            e instituciones.
          </p>
        </div>
      </div>
    </article>

  </div>
</section>


    <!-- GALERÍA TIPO NETFLIX: TODAS LAS PRODUCCIONES -->
    <section id="galeria-producciones" class="section-carousel">
      <div class="section-header">
        <h2 id="portafolio">Producciones de NZK Productora</h2>
        <p style="color:#8f9bbd;font-size:0.9rem;">
          Explora nuestro portafolio: selecciona una producción para ver el video completo.
        </p>
      </div>

      <?php if (empty($producerVideos)): ?>
        <p style="padding: 0 1.5rem 2rem;">Aún no hay producciones cargadas en NZK Productora.</p>
      <?php else: ?>
        <div class="cards-row">
          <?php foreach ($producerVideos as $video): ?>
            <?php
              $fechaProd = '';
              if (!empty($video['published_at'])) {
                $ts2 = strtotime($video['published_at']);
                if ($ts2) {
                  $fechaProd = date('d/m/Y · H:i', $ts2);
                }
              }
            ?>
            <article class="card video-card">
              <a href="./video_productora.php?slug=<?= urlencode($video['slug']) ?>" class="card-link">
                <div class="thumb-placeholder">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail']) ?>"
                      alt="<?= htmlspecialchars($video['title']) ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                      loading="lazy"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($video['title']) ?></h3>

                <?php if ($fechaProd): ?>
                  <p class="video-date"><?= $fechaProd ?></p>
                <?php endif; ?>

                <?php if (!empty($video['description'])): ?>
                  <p><?= htmlspecialchars($video['description']) ?></p>
                <?php endif; ?>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script src="./assets/js/main.js"></script>
</body>
</html>
