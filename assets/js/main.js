// ========================================
// Service Worker para PWA
// ========================================
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/service-worker.js")
      .catch((err) => {
        console.warn("No se pudo registrar el service worker:", err);
      });
  });

  // Recargar página si el SW (Firebase) envía mensaje de reload
  navigator.serviceWorker.addEventListener("message", (event) => {
    if (event.data && event.data.type === "reloadPage") {
      window.location.reload();
    }
  });
}

// ========================================
// Player EN VIVO con HLS + HD / SD
// ========================================

const STREAM_SRC_HD = "https://eu1.servers10.com:8081/8258/index.m3u8";
// Cuando tengas versión SD, cámbiala aquí:
const STREAM_SRC_SD = "https://eu1.servers10.com:8081/8258/index.m3u8";

let hlsInstance = null;
let currentQuality = "hd";

/**
 * Inicializa el player en vivo si existe #videoPlayer en la página.
 */
function initLivePlayer(initialQuality = "hd") {
  const video = document.getElementById("videoPlayer");
  if (!video) return;

  video.setAttribute("playsinline", "true");
  video.setAttribute("webkit-playsinline", "true");

  currentQuality = initialQuality === "sd" ? "sd" : "hd";
  const source = currentQuality === "hd" ? STREAM_SRC_HD : STREAM_SRC_SD;

  // Limpiar instancia previa
  if (hlsInstance) {
    try {
      hlsInstance.stopLoad();
      hlsInstance.detachMedia();
      hlsInstance.destroy();
    } catch (e) {
      console.log("Error limpiando instancia HLS previa:", e);
    }
    hlsInstance = null;
  }

  // Navegadores modernos con MediaSource
  if (typeof Hls !== "undefined" && Hls.isSupported()) {
    hlsInstance = new Hls({
      enableWorker: true,
      lowLatencyMode: true
    });
    hlsInstance.loadSource(source);
    hlsInstance.attachMedia(video);
    hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
      tryAutoplay(video);
    });
  } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
    // Safari / iOS con HLS nativo
    video.src = source;
    video.addEventListener("loadedmetadata", () => {
      tryAutoplay(video);
    });
  } else {
    console.warn("HLS no soportado en este navegador");
  }
}

/**
 * Autoplay con sonido.
 * Si el navegador bloquea el autoplay, se inicia al primer click.
 */
function tryAutoplay(videoEl) {
  if (!videoEl) return;

  videoEl.muted = false;
  videoEl.volume = 1.0;

  const playPromise = videoEl.play();
  if (playPromise !== undefined) {
    playPromise
      .then(() => {
        console.log("✅ Reproducción automática con sonido");
      })
      .catch((err) => {
        console.warn("⚠️ Autoplay bloqueado, esperando interacción", err);
        document.addEventListener(
          "click",
          () => {
            videoEl.play().catch(() => {});
          },
          { once: true }
        );
      });
  }
}

/**
 * Botón de cambio HD / SD dentro del player.
 */
function setupQualityToggle() {
  const qualityBtn = document.getElementById("qualityToggle");
  const video = document.getElementById("videoPlayer");
  if (!qualityBtn || !video) return;

  // Estado inicial
  updateQualityButtonLabel(qualityBtn, currentQuality);

  qualityBtn.addEventListener("click", () => {
    currentQuality = currentQuality === "hd" ? "sd" : "hd";
    updateQualityButtonLabel(qualityBtn, currentQuality);
    initLivePlayer(currentQuality);
  });
}

function updateQualityButtonLabel(btn, quality) {
  if (!btn) return;
  btn.textContent = quality === "hd" ? "Calidad: HD" : "Calidad: SD";
}

// ========================================
// PROGRAMACIÓN DESDE BD (schedule-json.php)
// ========================================

let scheduleDataByDay = null; // {0: [...],1:[...],...,6:[...]}

/**
 * Normaliza diferentes formatos de respuesta de schedule-json.php
 * a un objeto { 0: [...], 1: [...], ... 6: [...] }
 */
function normalizeScheduleJson(json) {
  if (!json) return {};

  // Caso 1: json.byDay ya es objeto con 0..6
  if (json.byDay && !Array.isArray(json.byDay) && typeof json.byDay === "object") {
    return json.byDay;
  }

  // Caso 2: json.byDay es array de 7 posiciones
  if (Array.isArray(json.byDay)) {
    const map = {};
    json.byDay.forEach((slots, idx) => {
      map[idx] = slots || [];
    });
    return map;
  }

  // Caso 3: json.week / json.weekend
  if (json.week || json.weekend) {
    const map = { 0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: [] };
    const week = json.week || [];
    const weekend = json.weekend || [];
    // Lunes-viernes -> 1..5
    for (let d = 1; d <= 5; d++) {
      map[d] = week;
    }
    // Domingo (0) y sábado (6)
    map[0] = weekend;
    map[6] = weekend;
    return map;
  }

  return {};
}

/**
 * Carga la parrilla desde PHP una sola vez y la cachea.
 */
async function loadScheduleData() {
  if (scheduleDataByDay) return scheduleDataByDay;

  const urls = ["/schedule-json.php", "./schedule-json.php", "schedule-json.php"];

  let lastError = null;

  for (const url of urls) {
    try {
      const res = await fetch(url, {
        headers: { Accept: "application/json" }
      });
      if (!res.ok) {
        lastError = new Error("HTTP " + res.status + " en " + url);
        continue;
      }
      const json = await res.json();
      scheduleDataByDay = normalizeScheduleJson(json);
      console.log("✅ Parrilla cargada desde", url, scheduleDataByDay);
      return scheduleDataByDay;
    } catch (err) {
      lastError = err;
    }
  }

  console.error("❌ No se pudo cargar la parrilla:", lastError);
  scheduleDataByDay = {};
  return scheduleDataByDay;
}

/**
 * Devuelve el arreglo de programación según el día de la semana JS.
 * jsDay: 0 = domingo ... 6 = sábado
 */
function getScheduleForJsDay(jsDay) {
  if (!scheduleDataByDay) return [];
  const d = Number(jsDay);
  return scheduleDataByDay[d] || [];
}

/**
 * Helper para obtener el bloque de programación con offset
 * (wrap-around al inicio del array).
 */
function getProgram(schedule, currentIndex, offset) {
  const len = schedule.length;
  if (!len) return null;
  return schedule[(currentIndex + offset + len) % len];
}

/**
 * Helper para actualizar una card por prefijo de id
 * (ej: "next" => next-img, next-title, next-time, next-category).
 */
function updateCard(prefix, data) {
  if (!data) return;

  const imgEl = document.getElementById(`${prefix}-img`);
  const titleEl = document.getElementById(`${prefix}-title`);
  const timeEl = document.getElementById(`${prefix}-time`);
  const catEl = document.getElementById(`${prefix}-category`);

  if (imgEl) {
    imgEl.src = data.img;
    imgEl.alt = data.title || "";
  }
  if (titleEl) titleEl.innerText = data.title;
  if (timeEl) timeEl.innerText = `${data.start} - ${data.end}`;
  if (catEl) catEl.innerText = data.category;
}

// ========================================
// LÓGICA DE PROGRAMACIÓN (EN VIVO / HOME / PROGRAMACIÓN)
// ========================================

/**
 * Descripción genérica para cada tipo de programa.
 * Si el slot viene con p.description desde BD, se respeta esa.
 */
function getProgramDescription(p) {
  if (!p) return "";
  if (p.description) return p.description;

  switch (p.category) {
    case "Noticias":
      return "Espacio informativo con las noticias más importantes de Nasca, Ica y el Perú.";
    case "Película":
      return "Película seleccionada de nuestra franja Cine en Casa, ideal para ver en familia.";
    case "Serie":
      return "Episodio de nuestra franja de series favoritas de la audiencia de NZK.";
    case "Novela":
      return "Capítulo de una de las novelas más vistas en la programación de NZK Televisión.";
    case "Música":
      return "Bloque musical con los mejores éxitos en la pantalla de NZK.";
    case "Dibujo Animado":
      return "Espacio de dibujos animados para los más pequeños (y no tan pequeños).";
    default:
      return "Programa de la parrilla diaria de NZK Televisión.";
  }
}

/**
 * Actualiza panel lateral y strip de programación según la hora actual.
 * Panel: "Ahora en vivo"
 * Cards: A continuación / Más adelante / Próximamente / Muy pronto
 */
function updateCarousel() {
  const now = new Date();
  const day = now.getDay(); // 0 domingo, 6 sábado
  const hour = now.getHours();
  const minute = now.getMinutes();
  const currentTime = `${hour.toString().padStart(2, "0")}:${minute
    .toString()
    .padStart(2, "0")}`;

  const schedule = getScheduleForJsDay(day);
  if (!schedule || !schedule.length) return;

  let currentIndex = schedule.findIndex(
    (p) => p.start <= currentTime && p.end > currentTime
  );
  if (currentIndex === -1) currentIndex = schedule.length - 1;

  // Bloques: actual + próximos 4
  const current = getProgram(schedule, currentIndex, 0);
  const next = getProgram(schedule, currentIndex, 1);
  const later = getProgram(schedule, currentIndex, 2);
  const soon = getProgram(schedule, currentIndex, 3);
  const verySoon = getProgram(schedule, currentIndex, 4);

  // Panel lateral EN VIVO (página en-vivo.php)
  const mainTitleEl = document.getElementById("live-main-title");
  const mainTimeEl = document.getElementById("live-main-time");
  const mainImgEl = document.getElementById("live-main-img");
  const mainDescEl = document.getElementById("live-main-desc");

  if (current) {
    if (mainTitleEl) mainTitleEl.innerText = current.title;
    if (mainTimeEl)
      mainTimeEl.innerText = `${current.start} - ${current.end} · ${current.category}`;
    if (mainImgEl) {
      mainImgEl.src = current.img;
      mainImgEl.alt = current.title || "";
    }
    if (mainDescEl) mainDescEl.textContent = getProgramDescription(current);
  }

  // Card "Estás viendo" (si aún existe en el strip)
  updateCard("current", current);

  // Cards del slider:
  updateCard("next", next);
  updateCard("later", later);
  updateCard("soon", soon);
  updateCard("verysoon", verySoon);
}

/**
 * Rellena el card destacado del home con el programa actual.
 */
function updateHomeHeroLiveHighlight() {
  const titleEl = document.getElementById("home-live-title");
  const timeEl = document.getElementById("home-live-time");
  const descEl = document.getElementById("home-live-desc");
  const imgEl = document.getElementById("home-live-img");

  // Si no existe el card, no hacemos nada
  if (!titleEl && !timeEl && !descEl && !imgEl) return;

  const now = new Date();
  const day = now.getDay(); // 0 domingo, 6 sábado
  const hour = now.getHours();
  const minute = now.getMinutes();
  const currentTime = `${hour.toString().padStart(2, "0")}:${minute
    .toString()
    .padStart(2, "0")}`;

  const schedule = getScheduleForJsDay(day);
  if (!schedule || !schedule.length) return;

  let currentIndex = schedule.findIndex(
    (p) => p.start <= currentTime && p.end > currentTime
  );
  if (currentIndex === -1) currentIndex = schedule.length - 1;

  const current = getProgram(schedule, currentIndex, 0);
  if (!current) return;

  if (titleEl) titleEl.textContent = current.title;
  if (timeEl)
    timeEl.textContent = `${current.start} – ${current.end} · ${current.category}`;
  if (descEl) descEl.textContent = getProgramDescription(current);
  if (imgEl) {
    imgEl.src = current.img;
    imgEl.alt = current.title;
  }
}

/**
 * Slider izquierda (si lo usas en alguna versión desktop).
 */
function updateHomeLeftSlider() {
  const now = new Date();
  const day = now.getDay();
  const hour = now.getHours();
  const minute = now.getMinutes();
  const currentTime = `${hour.toString().padStart(2, "0")}:${minute
    .toString()
    .padStart(2, "0")}`;

  const schedule = getScheduleForJsDay(day);
  if (!schedule || !schedule.length) return;

  let idx = schedule.findIndex(
    (p) => p.start <= currentTime && p.end > currentTime
  );
  if (idx === -1) idx = schedule.length - 1;

  const blocks = [
    { prefix: "pc-current", data: getProgram(schedule, idx, 0) },
    { prefix: "pc-next", data: getProgram(schedule, idx, 1) },
    { prefix: "pc-later", data: getProgram(schedule, idx, 2) }
  ];

  blocks.forEach((b) => {
    if (!b.data) return;

    const p = b.data;
    const desc = getProgramDescription(p);

    const imgEl = document.getElementById(`${b.prefix}-img`);
    const titleEl = document.getElementById(`${b.prefix}-title`);
    const timeEl = document.getElementById(`${b.prefix}-time`);
    const catEl = document.getElementById(`${b.prefix}-cat`);
    const descEl = document.getElementById(`${b.prefix}-desc`);

    if (imgEl) {
      imgEl.src = p.img;
      imgEl.alt = p.title;
    }
    if (titleEl) titleEl.textContent = p.title;
    if (timeEl) timeEl.textContent = `${p.start} – ${p.end}`;
    if (catEl) catEl.textContent = p.category;
    if (descEl) descEl.textContent = desc;
  });
}

/**
 * Renderiza la lista completa de programación para un día concreto.
 * jsDay: 0 = domingo ... 6 = sábado
 * dateObj: instancia de Date de ese día (para el título).
 */
function renderScheduleForDay(jsDay, dateObj) {
  const listEl = document.getElementById("scheduleList");
  const titleEl = document.getElementById("scheduleDayTitle");
  if (!listEl || !titleEl) return;

  const schedule = getScheduleForJsDay(jsDay);
  if (!schedule || !schedule.length) {
    titleEl.textContent = "No hay programación configurada para este día.";
    listEl.innerHTML = "";
    return;
  }

  const months = [
    "enero",
    "febrero",
    "marzo",
    "abril",
    "mayo",
    "junio",
    "julio",
    "agosto",
    "septiembre",
    "octubre",
    "noviembre",
    "diciembre"
  ];
  const weekdaysLong = [
    "domingo",
    "lunes",
    "martes",
    "miércoles",
    "jueves",
    "viernes",
    "sábado"
  ];

  const dd = String(dateObj.getDate()).padStart(2, "0");
  const mmName = months[dateObj.getMonth()];
  const weekdayName = weekdaysLong[dateObj.getDay()];

  titleEl.textContent = `Programación de ${weekdayName} ${dd} de ${mmName}`;

  // Para saber qué bloque está al aire (solo si es el día de hoy)
  const now = new Date();
  const isToday =
    now.getFullYear() === dateObj.getFullYear() &&
    now.getMonth() === dateObj.getMonth() &&
    now.getDate() === dateObj.getDate();

  let currentIndex = -1;
  if (isToday) {
    const hour = now.getHours();
    const minute = now.getMinutes();
    const currentTime = `${hour.toString().padStart(2, "0")}:${minute
      .toString()
      .padStart(2, "0")}`;
    currentIndex = schedule.findIndex(
      (p) => p.start <= currentTime && p.end > currentTime
    );
  }

  let html = "";

  schedule.forEach((p, index) => {
    const desc = getProgramDescription(p);
    const isCurrent = isToday && index === currentIndex;
    const itemClass = isCurrent
      ? "schedule-item is-current-block"
      : "schedule-item";

    let ctaHtml = `
      <p class="schedule-live-text">
        Disponible en NZKtvGO Play
      </p>
    `;

    if (isCurrent) {
      ctaHtml = `
        <a href="./en-vivo.php" class="schedule-live-link">
          <i class="fa-solid fa-play"></i>
          <span>EN VIVO en NZKtvGO Play</span>
        </a>
      `;
    }

    html += `
      <article class="${itemClass}">
        <div class="schedule-time">
          ${p.start}
        </div>

        <div class="schedule-program">
          <div class="schedule-program-thumb">
            <img src="${p.img}" alt="${p.title}">
          </div>
          <div class="schedule-program-info">
            <h3 class="schedule-program-title">${p.title}</h3>
            <p class="schedule-program-meta">${p.category} · ${p.start} – ${p.end}</p>
            <p class="schedule-program-desc">
              ${desc}
            </p>
            <div class="schedule-program-actions">
              ${ctaHtml}
            </div>
          </div>
        </div>
      </article>
    `;
  });

  listEl.innerHTML = html;

  // Scroll automático hasta el bloque actual (si existe)
  if (isToday) {
    const currentEl = listEl.querySelector(".schedule-item.is-current-block");
    if (currentEl) {
      currentEl.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
}

/**
 * Inicializa el nav de días y la programación dinámica
 * en programacion.php
 */
function initSchedulePage() {
  const tabs = document.querySelectorAll(".schedule-tab");
  const listEl = document.getElementById("scheduleList");
  if (!tabs.length || !listEl) return;

  const now = new Date();
  const todayY = now.getFullYear();
  const todayM = now.getMonth();
  const todayD = now.getDate();

  // Lunes de la semana actual (lunes como primer día)
  const jsToday = now.getDay(); // 0 = Dom ... 6 = Sáb
  const mondayOffset = ((jsToday + 6) % 7) * -1;
  const monday = new Date(now);
  monday.setDate(now.getDate() + mondayOffset);

  const weekdayShortMondayFirst = [
    "Lun",
    "Mar",
    "Mié",
    "Jue",
    "Vie",
    "Sáb",
    "Dom"
  ];
  const months = [
    "enero",
    "febrero",
    "marzo",
    "abril",
    "mayo",
    "junio",
    "julio",
    "agosto",
    "septiembre",
    "octubre",
    "noviembre",
    "diciembre"
  ];

  let activeSet = false;

  tabs.forEach((btn, index) => {
    const d = new Date(monday);
    d.setDate(monday.getDate() + index);

    const jsDay = d.getDay(); // 0..6
    const isToday =
      d.getFullYear() === todayY &&
      d.getMonth() === todayM &&
      d.getDate() === todayD;

    const dd = String(d.getDate()).padStart(2, "0");
    const monthName = months[d.getMonth()];

    btn.dataset.jsDay = String(jsDay);
    btn.dataset.date = d.toISOString().split("T")[0];

    const weekdaySpan = btn.querySelector(".schedule-tab-weekday");
    const dateSpan = btn.querySelector(".schedule-tab-date");

    if (weekdaySpan) {
      const baseLabel = weekdayShortMondayFirst[index] || "";
      weekdaySpan.textContent = isToday ? `Hoy ${baseLabel}` : baseLabel;
    }
    if (dateSpan) {
      dateSpan.textContent = `${dd} de ${monthName}`;
    }

    btn.addEventListener("click", () => {
      tabs.forEach((b) => b.classList.remove("is-active"));
      btn.classList.add("is-active");
      const dayNumber = parseInt(btn.dataset.jsDay, 10);
      const dateForBtn = new Date(btn.dataset.date);
      renderScheduleForDay(dayNumber, dateForBtn);
    });

    if (isToday && !activeSet) {
      btn.classList.add("is-active");
      renderScheduleForDay(jsDay, d);
      activeSet = true;
    }
  });

  if (!activeSet && tabs[0]) {
    const btn = tabs[0];
    btn.classList.add("is-active");
    const jsDay = parseInt(btn.dataset.jsDay || "1", 10) || 1;
    const d = new Date(btn.dataset.date || new Date());
    renderScheduleForDay(jsDay, d);
  }
}

// ========================================
// Carrusel Programas (home)
// ========================================

function initProgramasCarousel() {
  const section = document.querySelector(".section-carousel-programas");
  if (!section) return;

  const track = section.querySelector("[data-carousel-track='programas']");
  const prevBtn = section.querySelector(".carousel-arrow--prev");
  const nextBtn = section.querySelector(".carousel-arrow--next");

  if (!track || !prevBtn || !nextBtn) return;

  const getStep = () => {
    const card = track.querySelector(".video-card--programa");
    if (!card) return 300;
    const cardStyles = window.getComputedStyle(card);
    const gap = parseFloat(
      window.getComputedStyle(track).columnGap ||
        cardStyles.marginRight ||
        16
    );
    return card.getBoundingClientRect().width + gap;
  };

  prevBtn.addEventListener("click", () => {
    const step = getStep();
    track.scrollBy({ left: -step, behavior: "smooth" });
  });

  nextBtn.addEventListener("click", () => {
    const step = getStep();
    track.scrollBy({ left: step, behavior: "smooth" });
  });
}

// ========================================
// INIT GLOBAL
// ========================================

document.addEventListener("DOMContentLoaded", async () => {
  // --- Splash NZK tvGO ---
  const splash = document.getElementById("app-splash");
  const alreadyShown = sessionStorage.getItem("nzk_splash_shown");

  if (splash) {
    if (alreadyShown) {
      splash.classList.add("is-hidden");
    } else {
      setTimeout(() => {
        splash.classList.add("is-hidden");
        sessionStorage.setItem("nzk_splash_shown", "1");
      }, 1800);
    }
  }

  // Toggle menú en móvil
  const toggle = document.querySelector(".nav-toggle");
  const nav = document.querySelector(".main-nav");
  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      nav.classList.toggle("nav-open");
    });
  }

  // Cargar parrilla desde BD antes de usarla
  await loadScheduleData();

  // Player en vivo (solo en en-vivo.php)
  const video = document.getElementById("videoPlayer");
  if (video) {
    initLivePlayer("hd");
    setupQualityToggle();
  }

  // Carrusel / panel en vivo (solo si existen elementos relacionados)
  if (
    document.getElementById("live-main-title") ||
    document.getElementById("next-title") ||
    document.getElementById("current-title")
  ) {
    updateCarousel();
    setInterval(updateCarousel, 60000); // cada minuto
  }

  // Card destacado del home (ahora en NZK TV)
  if (document.getElementById("home-live-title")) {
    updateHomeHeroLiveHighlight();
    setInterval(updateHomeHeroLiveHighlight, 60000);
  }

  // Slider izquierdo (si usas los IDs pc-current/pc-next/pc-later)
  if (document.getElementById("pc-current-title")) {
    updateHomeLeftSlider();
    setInterval(updateHomeLeftSlider, 60000);
  }

  // Página de programación
  initSchedulePage();

  // Carrusel de programas
  initProgramasCarousel();
});
