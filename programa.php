<?php
require __DIR__ . '/includes/db.php';

$slug = $_GET['slug'] ?? '';
$slug = trim($slug);

// Si no hay slug, redirigimos
if ($slug === '') {
    header('Location: index.php');
    exit;
}

// Buscar programa por slug
$stmtProg = $pdo->prepare("
    SELECT id, title, description, thumbnail, created_at
    FROM nzk_programas
    WHERE slug = ?
    LIMIT 1
");
$stmtProg->execute([$slug]);
$programa = $stmtProg->fetch();

if (!$programa) {
    header('Location: index.php');
    exit;
}

// Buscar videos asociados a este programa
$stmtVideos = $pdo->prepare("
    SELECT id, title, slug, description, thumbnail, published_at
    FROM nzk_videos
    WHERE program_id = ?
    ORDER BY published_at DESC
");
$stmtVideos->execute([$programa['id']]);
$videosPrograma = $stmtVideos->fetchAll();

// Helper fecha
function formatDateTime($dt) {
    if (!$dt) return '';
    $ts = strtotime($dt);
    if (!$ts) return '';
    return date('d/m/Y · H:i', $ts);
}

// Para marcar activa la sección Programas en el header
$currentPage = 'programs';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($programa['title']) ?> – Programas · NZKtvGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/splash.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="page-content">

    <section class="program-hero-wrapper">
    <div class="program-hero-card">

        <div class="program-hero-media">
            <?php if (!empty($programa['thumbnail'])): ?>
                <img
                    src="<?= htmlspecialchars($programa['thumbnail']) ?>"
                    alt="<?= htmlspecialchars($programa['title']) ?>"
                    class="program-hero-image"
                >
            <?php endif; ?>
        </div>

        <div class="program-hero-info">
            <h1><?= htmlspecialchars($programa['title']) ?></h1>

            <?php if (!empty($programa['description'])): ?>
                <p class="program-hero-description">
                    <?= nl2br(htmlspecialchars($programa['description'])) ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($programa['created_at'])): ?>
                <p class="program-meta">Desde: <?= formatDateTime($programa['created_at']) ?></p>
            <?php endif; ?>
        </div>

    </div>
</section>


    <section class="section-episodes container">
        <div class="section-header">
            <h2>Episodios y contenidos de este programa</h2>
        </div>

        <?php if (empty($videosPrograma)): ?>
            <p>Por ahora no hay videos asociados a este programa.</p>
        <?php else: ?>
            <div class="episodes-row">
            <?php foreach ($videosPrograma as $video): ?>
                <article class="episode-card">
                <a href="./video.php?slug=<?= urlencode($video['slug']) ?>" class="episode-link">
                    <div class="episode-thumb">
                    <?php if (!empty($video['thumbnail'])): ?>
                        <img
                        src="<?= htmlspecialchars($video['thumbnail']) ?>"
                        alt="<?= htmlspecialchars($video['title']) ?>"
                        loading="lazy"
                        >
                    <?php else: ?>
                        <span>Sin imagen</span>
                    <?php endif; ?>
                    </div>

                    <div class="episode-info">
                    <h3 class="episode-title">
                        <?= htmlspecialchars($video['title']) ?>
                    </h3>

                    <?php if (!empty($video['published_at'])): ?>
                        <p class="episode-meta">
                        <?= formatDateTime($video['published_at']) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($video['description'])): ?>
                        <p class="episode-description">
                        <?= htmlspecialchars($video['description']) ?>
                        </p>
                    <?php endif; ?>
                    </div>
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
