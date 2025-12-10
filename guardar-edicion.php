<?php
require '../includes/db.php';
require '../includes/security.php';
csrf_verify();

$id = intval($_POST['id'] ?? 0);

$stmt = $pdo->prepare("
    UPDATE nzk_schedule_slots SET
        weekday = ?,
        start_time = ?,
        end_time = ?,
        title = ?,
        category = ?,
        img_url = ?,
        description = ?,
        program_slug = ?
    WHERE id = ?
");

$stmt->execute([
    $_POST['weekday'],
    $_POST['start_time'],
    $_POST['end_time'],
    $_POST['title'],
    $_POST['category'],
    $_POST['img_url'],
    $_POST['description'],
    $_POST['program_slug'],
    $id
]);

header("Location: admin-parrilla.php?edit_ok=1");
exit;
