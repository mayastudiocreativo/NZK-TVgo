// ======================================================
// NZK tvGO – SERVICE WORKER + FIREBASE MESSAGING
// ======================================================

// Versión de cache
const CACHE_NAME = "nzk-tvgo-v1.2";

// Archivos estáticos que se cachean
const STATIC_ASSETS = [
  "/",
  "/index.php",
  "/en-vivo.php",
  "/programacion.php",
  "/programas.php",
  "/noticias.php",
  "/eventos.php",

  "/assets/css/style.css",
  "/assets/js/main.js",
  "/assets/js/programacion.js",

  "/img/IconAndroid/iconldpi.png",
  "/img/IconAndroid/iconmdpi.png",
  "/img/IconAndroid/iconhdpi.png",
  "/img/IconAndroid/iconxhdpi.png",
  "/img/IconAndroid/iconxxhdpi.png",
  "/img/IconAndroid/iconxxxhdpi.png",

  "/img/IconIOS/icon.png",
  "/img/IconIOS/icon@2x.png",
  "/img/IconIOS/icon@3x.png"
];

// ======================================================
// INSTALACIÓN
// ======================================================
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

// ======================================================
// ACTIVACIÓN – Limpiar versiones viejas
// ======================================================
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ======================================================
// FETCH – Cache + control de streaming HLS
// ======================================================
self.addEventListener("fetch", (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Ignorar chrome-extension:// y similares
  if (url.protocol !== "http:" && url.protocol !== "https:") return;

  // Solo manejar recursos de tu dominio
  if (url.origin !== self.location.origin) return;

  // ⚠️ NO tocar streaming HLS (.m3u8 / .ts)
  if (
    url.pathname.endsWith(".m3u8") ||
    url.pathname.endsWith(".ts") ||
    url.pathname.includes("/hls/")
  ) {
    return;
  }

  // Navegaciones → network-first
  if (req.mode === "navigate") {
    event.respondWith(
      fetch(req)
        .then((res) => {
          caches.open(CACHE_NAME).then((c) => c.put(req, res.clone()));
          return res;
        })
        .catch(() => caches.match("/index.php"))
    );
    return;
  }

  // Otros GET → cache-first
  if (req.method === "GET") {
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;

        return fetch(req)
          .then((res) => {
            caches.open(CACHE_NAME).then((c) => c.put(req, res.clone()));
            return res;
          })
          .catch(() => cached);
      })
    );
  }
});

// ======================================================
// 🔔 FIREBASE PUSH NOTIFICATIONS
// ======================================================

// Importar Firebase en el Service Worker
importScripts("https://www.gstatic.com/firebasejs/11.5.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/11.5.0/firebase-messaging-compat.js");

// ⬇️ MISMO firebaseConfig QUE YA USABAS
const firebaseConfig = {
  apiKey: "AIzaSyAqfSjiV_Hz4CHCwc02KNGZog2iIWEXkPI",
  authDomain: "tvgonzk.firebaseapp.com",
  projectId: "tvgonzk",
  storageBucket: "tvgonzk.firebasestorage.app",
  messagingSenderId: "147196989004",
  appId: "1:147196989004:web:cf9abd115c51c7ece1aee9",
  measurementId: "G-HG7PKX9FL6"
};

// Inicializar Firebase dentro del SW
firebase.initializeApp(firebaseConfig);

// Instancia de messaging
const messaging = firebase.messaging();

// ======================================================
// Notificaciones en BACKGROUND
// ======================================================
messaging.onBackgroundMessage((payload) => {
  console.log("[SW] Notificación en background:", payload);

  const notif = payload.notification || {};
  const data  = payload.data || {};

  const title = data.title || notif.title || "TvGo NZK";
  const body  = data.body  || notif.body  || "";
  const image = data.image || notif.image;

  const options = {
    body,
    // 👇 Forzamos SIEMPRE nuestro icono local
    icon: "/img/icon-192.png",
    badge: "/img/icon-192.png",
    image: image || undefined,
  };

  self.registration.showNotification(title, options);
});
