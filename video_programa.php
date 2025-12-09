<?php
// video_programa.php
// Pequeño wrapper para reutilizar video.php, pero fijando el "program" según el slug

$slug = $_GET['slug'] ?? '';
if ($slug === '') {
  header('Location: ./');
  exit;
}

// Intentamos extraer el slug base del programa a partir del slug del episodio
// Ejemplo: yo-emprendedor-Ep01-Temp01 -> yo-emprendedor
$programSlugBase = null;
if (preg_match('~^(.*?)-Ep[0-9]+~i', $slug, $m)) {
  $programSlugBase = $m[1];
}

// Si encontramos base, lo metemos en $_GET['program'] para que video.php lo use
if ($programSlugBase) {
  $_GET['program'] = $programSlugBase;
}

// Reutilizamos TODO el código de video.php
require __DIR__ . '/video.php';


?>
