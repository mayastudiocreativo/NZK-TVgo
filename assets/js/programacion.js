// Datos de programación basados en la parrilla 2025 proporcionada
// Simplificado en tres bloques: lunes-viernes, sábado, domingo

const scheduleData = {
  weekday: [
    { start: "00:00", end: "02:00", title: "Película", type: "Película" },
    { start: "02:00", end: "04:00", title: "Película", type: "Película" },
    { start: "04:00", end: "05:00", title: "Serie", type: "Serie" },
    { start: "05:00", end: "06:00", title: "HIT TV NZK", type: "Música" },
    { start: "06:00", end: "07:00", title: "HIT TV NZK", type: "Música" },
    { start: "07:00", end: "08:30", title: "Documental", type: "Documental" },
    { start: "08:30", end: "10:00", title: "NZK Noticias - Edición Matinal", type: "Noticias" },
    { start: "10:00", end: "11:00", title: "Novela", type: "Novela" },
    { start: "11:00", end: "12:00", title: "Novela", type: "Novela" },
    { start: "12:00", end: "13:00", title: "Serie", type: "Serie" },
    { start: "13:00", end: "13:30", title: "HIT TV NZK", type: "Música" },
    { start: "13:30", end: "15:00", title: "NZK Noticias - Edición Mediodía", type: "Noticias" },
    { start: "15:00", end: "16:00", title: "Novela", type: "Novela" },
    { start: "16:00", end: "17:00", title: "Serie", type: "Serie" },
    { start: "17:00", end: "19:00", title: "Película", type: "Película" },
    { start: "19:00", end: "19:50", title: "Serie", type: "Serie" },
    { start: "19:50", end: "20:00", title: "HIT TV NZK", type: "Música" },
    { start: "20:00", end: "21:30", title: "NZK Noticias - Edición Central", type: "Noticias" },
    { start: "21:30", end: "22:30", title: "Serie", type: "Serie" },
    { start: "22:30", end: "00:00", title: "Película", type: "Película" }
  ],
  saturday: [
    { start: "00:00", end: "02:00", title: "Película", type: "Película" },
    { start: "02:00", end: "04:00", title: "Película", type: "Película" },
    { start: "04:00", end: "06:00", title: "Película", type: "Película" },
    { start: "06:00", end: "07:00", title: "HIT TV NZK", type: "Música" },
    { start: "07:00", end: "08:00", title: "HIT TV NZK", type: "Música" },
    { start: "08:00", end: "09:00", title: "Dibujo animado", type: "Infantil" },
    { start: "09:00", end: "10:00", title: "Misión Informativa - Edición Sabatina", type: "Noticias" },
    { start: "10:00", end: "11:00", title: "Misión Informativa - Edición Sabatina (continuación)", type: "Noticias" },
    { start: "11:00", end: "12:00", title: "Dibujo animado", type: "Infantil" },
    { start: "12:00", end: "12:30", title: "HIT TV NZK", type: "Música" },
    { start: "12:30", end: "13:30", title: "Yo Emprendedor", type: "Magazine" },
    { start: "13:30", end: "14:00", title: "HIT TV NZK", type: "Música" },
    { start: "14:00", end: "16:00", title: "Película", type: "Película" },
    { start: "16:00", end: "18:00", title: "Película", type: "Película" },
    { start: "18:00", end: "20:00", title: "Película", type: "Película" },
    { start: "20:00", end: "22:00", title: "Película", type: "Película" },
    { start: "22:00", end: "00:00", title: "Película", type: "Película" }
  ],
  sunday: [
    { start: "00:00", end: "02:00", title: "Película", type: "Película" },
    { start: "02:00", end: "04:00", title: "Película", type: "Película" },
    { start: "04:00", end: "06:00", title: "Película", type: "Película" },
    { start: "06:00", end: "07:00", title: "HIT TV NZK", type: "Música" },
    { start: "07:00", end: "08:00", title: "HIT TV NZK", type: "Música" },
    { start: "08:00", end: "09:00", title: "Dibujo animado", type: "Infantil" },
    { start: "09:00", end: "11:00", title: "Misión Informativa - Edición Sabatina (repetición)", type: "Noticias" },
    { start: "11:00", end: "12:00", title: "Dibujo animado", type: "Infantil" },
    { start: "12:00", end: "12:30", title: "HIT TV NZK", type: "Música" },
    { start: "12:30", end: "13:30", title: "Yo Emprendedor (repetición)", type: "Magazine" },
    { start: "13:30", end: "14:00", title: "HIT TV NZK", type: "Música" },
    { start: "14:00", end: "16:00", title: "Película", type: "Película" },
    { start: "16:00", end: "18:00", title: "Película", type: "Película" },
    { start: "18:00", end: "20:00", title: "Película", type: "Película" },
    { start: "20:00", end: "22:00", title: "Película", type: "Película" },
    { start: "22:00", end: "00:00", title: "Película", type: "Película" }
  ]
};

function renderSchedule(dayKey) {
  const list = document.getElementById('schedule-list');
  if (!list) return;
  const items = scheduleData[dayKey] || [];
  list.innerHTML = '';
  items.forEach(item => {
    const li = document.createElement('li');

    const timeSpan = document.createElement('span');
    timeSpan.className = 'schedule-time';
    timeSpan.textContent = `${item.start} - ${item.end}`;

    const titleSpan = document.createElement('span');
    titleSpan.className = 'schedule-title';
    titleSpan.textContent = item.title;

    const metaSpan = document.createElement('span');
    metaSpan.className = 'schedule-meta';
    metaSpan.textContent = item.type;

    li.appendChild(timeSpan);
    li.appendChild(titleSpan);
    li.appendChild(metaSpan);
    list.appendChild(li);
  });
}

function getCurrentBlockForDay(dayKey, nowMinutes) {
  const items = scheduleData[dayKey] || [];
  let current = null;
  let next = null;
  let later = [];

  for (let i = 0; i < items.length; i++) {
    const item = items[i];
    const [sh, sm] = item.start.split(':').map(Number);
    const [eh, em] = item.end.split(':').map(Number);
    const startMin = sh * 60 + sm;
    const endMin = eh * 60 + em;

    if (nowMinutes >= startMin && nowMinutes < endMin) {
      current = item;
      next = items[i + 1] || null;
      later = items.slice(i + 2);
      break;
    }

    if (nowMinutes < startMin && !current && !next) {
      next = item;
      later = items.slice(i + 1);
      break;
    }
  }

  return { current, next, later };
}

function renderLiveBlocks() {
  const currentEl = document.getElementById('live-current');
  const nextEl = document.getElementById('live-next');
  const laterEl = document.getElementById('live-later');
  if (!currentEl || !nextEl || !laterEl) return;

  const now = new Date();
  const day = now.getDay(); // 0 domingo, 6 sábado
  const minutes = now.getHours() * 60 + now.getMinutes();

  let dayKey = 'weekday';
  if (day === 6) dayKey = 'saturday';
  if (day === 0) dayKey = 'sunday';

  const { current, next, later } = getCurrentBlockForDay(dayKey, minutes);

  const formatBlock = (item) => {
    if (!item) return '<p>No hay información disponible.</p>';
    return `<p><strong>${item.title}</strong></p><p>${item.start} - ${item.end} · ${item.type}</p>`;
  };

  currentEl.innerHTML = formatBlock(current);
  nextEl.innerHTML = formatBlock(next);
  if (later && later.length) {
    laterEl.innerHTML = later.slice(0, 3).map(b => (
      `<p><strong>${b.title}</strong> · ${b.start} - ${b.end}</p>`
    )).join('');
  } else {
    laterEl.innerHTML = '<p>No hay más bloques programados para hoy.</p>';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  // Programación página
  const scheduleList = document.getElementById('schedule-list');
  if (scheduleList) {
    renderSchedule('weekday');
    const buttons = document.querySelectorAll('.day-btn');
    buttons.forEach(btn => {
      btn.addEventListener('click', () => {
        const day = btn.getAttribute('data-day');
        renderSchedule(day);
      });
    });
  }

  // Bloques en vivo
  renderLiveBlocks();
  // Refrescar cada 5 minutos
  setInterval(renderLiveBlocks, 5 * 60 * 1000);
});
