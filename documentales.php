<?php
  $currentPage = ''; // si luego quieres marcar un item del menú
  require __DIR__ . '/includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Documentales y Entrevistas | NZK tvGO</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="page-list">

  <?php
    include __DIR__ . '/includes/header.php';
    include __DIR__ . '/includes/bottom-nav.php';
  ?>

  <main class="page">
    <section class="section-carousel section-full-grid">
      <div class="section-header">
        <h2>Documentales y entrevistas</h2>
        <p style="font-size:0.9rem;color:#9ca3af;">
          Explora todas las producciones especiales de NZK: entrevistas, reportajes y documentales completos.
        </p>
      </div>

      <?php
        $stmt = $pdo->query("
          SELECT id, title, slug, description, thumbnail, published_at
          FROM nzk_videos
          WHERE category = 'doc_entrevistas'
          ORDER BY published_at DESC
        ");
        $docs = $stmt->fetchAll();
      ?>

      <div class="cards-row cards-row-grid">
        <?php if (empty($docs)): ?>
          <p>No hay documentales ni entrevistas cargados todavía.</p>
        <?php else: ?>
          <?php foreach ($docs as $video): ?>
            <?php
              $fecha = '';
              if (!empty($video['published_at'])) {
                $ts = strtotime($video['published_at']);
                if ($ts) {
                  $fecha = date('d/m/Y · H:i', $ts);
                }
              }
            ?>
            <article class="card video-card">
              <a href="./video.php?slug=<?= urlencode($video['slug']) ?>" class="card-link">
                <div class="thumb-placeholder">
                  <?php if (!empty($video['thumbnail'])): ?>
                    <img
                      src="<?= htmlspecialchars($video['thumbnail']) ?>"
                      alt="<?= htmlspecialchars($video['title']) ?>"
                      style="width:100%;height:100%;object-fit:cover;border-radius:0.75rem;"
                    >
                  <?php else: ?>
                    <span>Thumbnail</span>
                  <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($video['title']) ?></h3>
                <?php if ($fecha): ?>
                  <p class="video-date"><?= $fecha ?></p>
                <?php endif; ?>
                <p><?= htmlspecialchars($video['description']) ?></p>
              </a>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>