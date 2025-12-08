<nav class="bottom-nav neo-nav">
  <a href="./index"
     class="nav-item <?php echo ($currentPage === 'home') ? 'active' : ''; ?>">
    <i class="fas fa-house"></i>
    <span>Inicio</span>
  </a>

  <a href="./en-vivo"
     class="nav-item <?php echo ($currentPage === 'live') ? 'active' : ''; ?>">
    <i class="fas fa-tower-broadcast"></i>
    <span>En vivo</span>
  </a>

  <a href="./programacion"
     class="nav-item <?php echo ($currentPage === 'schedule') ? 'active' : ''; ?>">
    <i class="fas fa-calendar-days"></i>
    <span>Prog.</span>
  </a>

  <a href="./programas"
     class="nav-item <?php echo ($currentPage === 'shows') ? 'active' : ''; ?>">
    <i class="fas fa-clapperboard"></i>
    <span>Programas</span>
  </a>

  <a href="./noticias"
     class="nav-item <?php echo ($currentPage === 'news') ? 'active' : ''; ?>">
    <i class="fas fa-newspaper"></i>
    <span>Noticias</span>
  </a>
</nav>