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