<?php
  // Página actual para marcar el menú activo
  $currentPage = 'schedule';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- SEO -->
  <title>Programación NZK TV hoy | Parrilla diaria de tvGoNZK</title>
  <meta name="description" content="Consulta la programación completa de NZK Televisión. Horarios de noticias, series, películas, entretenimiento y más en la parrilla diaria de tvGoNZK.">

  <!-- Open Graph / Social -->
  <meta property="og:site_name" content="TvGoNZK" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://tvgo.nzktv.com/programacion.php" />
  <meta property="og:title" content="Programación NZK TV hoy | Parrilla diaria de tvGoNZK">
  <meta property="og:description" content="Revisa la programación de hoy de NZK Televisión: noticias, entretenimiento, series, películas y programas especiales en tvGoNZK.">
  <meta property="og:image" content="https://tvgo.nzktv.com/img/IconIOS/icon.png" />
  <meta property="og:image:alt" content="TvGoNZK" />

  <!-- Canonical -->
  <link rel="canonical" href="https://tvgo.nzktv.com/programacion.php" />

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

  <!-- HLS.js (por si algún día quieres VOD aquí) -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

  <!-- Firebase Cloud Messaging (NZKtvgo) -->
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-app.js";
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-messaging.js";

    const firebaseConfig = {
      apiKey: "AIzaSyAqfSjiV_Hz4CHCwc02KNGZog2iIWEXkPI",
      authDomain: "tvgonzk.firebaseapp.com",
      projectId: "tvgonzk",
      storageBucket: "tvgonzk.firebasestorage.app",
      messagingSenderId: "147196989004",
      appId: "1:147196989004:web:cf9abd115c51c7ece1aee9",
      measurementId: "G-HG7PKX9FL6"
    };

    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);

    // Token de notificaciones (NZKtvgo)
    getToken(messaging, {
      vapidKey: "BHL4krypMZ2tn5JkojMSlVZTDurfnhZ5RyJj76Zjnyyxl6SBQppXOECAiG_H3ZGwKx2IGEBLzg5gAFIuY1zfqIc"
    })
      .then((currentToken) => {
        if (currentToken) {
          console.log("🔑 Token de notificación:", currentToken);
        } else {
          console.log("⚠️ No se pudo obtener token.");
        }
      })
      .catch(err => console.error("🚨 Error al obtener token:", err));

    // Notificaciones en primer plano
    onMessage(messaging, (payload) => {
  console.log("📩 Notificación en primer plano:", payload);

  // Aquí podrías, si quieres, mostrar un aviso dentro de la web:
  // - un toast
  // - actualizar un badge de "nuevas noticias"
  // Pero NO crear new Notification(...), porque ya lo hace el SW.
});

  </script>

  <!-- Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-HQLB82PH72"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-HQLB82PH72');
  </script>
</head>

<body class="page-schedule">
  <?php include __DIR__ . '/includes/splash.php'; ?>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <?php include __DIR__ . '/includes/bottom-nav.php'; ?>

  <main class="page page-programacion">

    <!-- Banner superior -->
    <section class="banner-slot banner-top">
      <div class="banner-placeholder">Espacio para banner publicitario superior</div>
    </section>

    <!-- Encabezado sección -->
    <section class="section-header schedule-header">
      <div>
        <h1>Programación semanal NZK TV</h1>
        <p>Revisa la parrilla diaria de NZK Televisión. Selecciona el día para ver todos los programas y horarios.</p>
      </div>
    </section>

    <!-- NAV DE DÍAS (tipo TVPerú) -->
    <section class="schedule-tabs-wrapper">
      <div id="scheduleTabs" class="schedule-tabs">
        <!-- Los textos (Hoy, fechas, etc.) y el día activo se ajustan en main.js -->

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Lun</span>
          <span class="schedule-tab-date">01 de diciembre</span>
        </button>

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Mar</span>
          <span class="schedule-tab-date">02 de diciembre</span>
        </button>

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Mié</span>
          <span class="schedule-tab-date">03 de diciembre</span>
        </button>

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Jue</span>
          <span class="schedule-tab-date">04 de diciembre</span>
        </button>

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Vie</span>
          <span class="schedule-tab-date">05 de diciembre</span>
        </button>

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Sáb</span>
          <span class="schedule-tab-date">06 de diciembre</span>
        </button>

        <button class="schedule-tab">
          <span class="schedule-tab-weekday">Dom</span>
          <span class="schedule-tab-date">07 de diciembre</span>
        </button>
      </div>
    </section>

    <!-- LISTA DE PROGRAMAS DEL DÍA SELECCIONADO -->
    <section class="schedule-day-section">
      <div class="section-header schedule-day-header">
        <h2 id="scheduleDayTitle">Programación de hoy</h2>
      </div>

      <div id="scheduleList" class="schedule-list">
        <!-- Aquí main.js inyecta TODA la programación de 00:00 a 24:00
             con la estructura .schedule-item, usando scheduleWeek / scheduleWeekend -->
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

  <!-- Registro de Service Worker (PWA) -->
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