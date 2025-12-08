<?php
// Asegurar que $currentPage exista para evitar errores
if (!isset($currentPage)) {
  $currentPage = '';
}
?>

<header class="site-header">
  <div class="header-inner">

    <!-- Logo -->
    <a href="./index.php" class="logo logo-img">
      <img src="./img/logoNZKtvgo.png" alt="NZK tvGO">
    </a>

    <!-- Navegación principal (desktop) -->
    <nav class="main-nav">
      <a href="./index.php"
         class="nav-link <?php echo ($currentPage === 'home') ? 'is-active' : ''; ?>">
        Inicio
      </a>

      <a href="./en-vivo.php"
         class="nav-link nav-live <?php echo ($currentPage === 'live') ? 'is-active' : ''; ?>">
        <span class="live-dot"></span> En vivo
      </a>

      <a href="./programacion.php"
         class="nav-link <?php echo ($currentPage === 'schedule') ? 'is-active' : ''; ?>">
        Programación
      </a>

      <a href="./programas.php"
         class="nav-link <?php echo ($currentPage === 'shows') ? 'is-active' : ''; ?>">
        Programas
      </a>

      <a href="./noticias.php"
         class="nav-link <?php echo ($currentPage === 'news') ? 'is-active' : ''; ?>">
        Noticias
      </a>

      <a href="./eventos.php"
         class="nav-link <?php echo ($currentPage === 'events') ? 'is-active' : ''; ?>">
        Eventos especiales
      </a>
    </nav>

    </nav>

<!-- Firebase Cloud Messaging centralizado -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-app.js";
  import {
    getMessaging,
    getToken,
    onMessage,
    isSupported
  } from "https://www.gstatic.com/firebasejs/11.5.0/firebase-messaging.js";

  const firebaseConfig = {
    apiKey: "AIzaSyAqfSjiV_Hz4CHCwc02KNGZog2iIWEXkPI",
    authDomain: "tvgonzk.firebaseapp.com",
    projectId: "tvgonzk",
    storageBucket: "tvgonzk.firebasestorage.app",
    messagingSenderId: "147196989004",
    appId: "1:147196989004:web:cf9abd115c51c7ece1aee9",
    measurementId: "G-HG7PKX9FL6"
  };

  (async () => {
    try {
      const supported = await isSupported();
      if (!supported) {
        console.log("🔕 FCM no soportado en este navegador.");
        return;
      }

      const app = initializeApp(firebaseConfig);
      const messaging = getMessaging(app);

      // Obtener token de notificaciones (una vez por navegador/dispositivo)
      const currentToken = await getToken(messaging, {
        vapidKey: "BHL4krypMZ2tn5JkojMSlVZTDurfnhZ5RyJj76Zjnyyxl6SBQppXOECAiG_H3ZGwKx2IGEBLzg5gAFIuY1zfqIc"
      });

      if (currentToken) {
        console.log("🔑 Token de notificación:", currentToken);
        // Aquí si quieres luego puedes enviarlo a tu backend
      } else {
        console.log("⚠️ No se pudo obtener token de FCM.");
      }

      // Mensajes en primer plano (NO mostramos notificación del sistema aquí
      // para evitar duplicados con el Service Worker)
      onMessage(messaging, (payload) => {
        console.log("📩 FCM en primer plano:", payload);

        // Si quieres más adelante:
        // - mostrar un toast
        // - actualizar un contador de "nuevas noticias"
        // pero NO hacer new Notification(...) aquí.
      });
    } catch (err) {
      console.error("🚨 Error inicializando Firebase Messaging:", err);
    }
  })();
</script>


    <!-- Botón derecha -->
    <a href="https://nzktv.com/" class="action-gradient-btn" target="_blank" rel="noopener">
      Portal NZK Noticias
    </a>

    <!-- Botón menú móvil -->
    <button class="nav-toggle" aria-label="Abrir menú">
      <i class="fa fa-bars"></i>
    </button>

  </div>
</header>

<!-- NAV MÓVIL (BOTTOM NAV) -->
<nav class="bottom-nav neo-nav">

  <a href="./index.php"
     class="nav-item <?php echo ($currentPage === 'home') ? 'active' : ''; ?>">
    <i class="fas fa-house"></i>
    <span>Inicio</span>
  </a>

  <a href="./en-vivo.php"
     class="nav-item <?php echo ($currentPage === 'live') ? 'active' : ''; ?>">
    <i class="fas fa-tower-broadcast"></i>
    <span>En vivo</span>
  </a>

  <a href="./programacion.php"
     class="nav-item <?php echo ($currentPage === 'schedule') ? 'active' : ''; ?>">
    <i class="fas fa-calendar-days"></i>
    <span>Prog.</span>
  </a>

  <a href="./programas.php"
     class="nav-item <?php echo ($currentPage === 'shows') ? 'active' : ''; ?>">
    <i class="fas fa-clapperboard"></i>
    <span>Programas</span>
  </a>

  <a href="./noticias.php"
     class="nav-item <?php echo ($currentPage === 'news') ? 'active' : ''; ?>">
    <i class="fas fa-newspaper"></i>
    <span>Noticias</span>
  </a>

</nav>