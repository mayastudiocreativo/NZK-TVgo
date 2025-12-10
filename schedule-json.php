<?php
// schedule-json.php
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/includes/db.php';

$stmt = $pdo->query("
  SELECT weekday, start_time, end_time, title, category, img_url, description
  FROM nzk_schedule_slots
  ORDER BY weekday ASC, start_time ASC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [
    'byDay' => [
        0 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => []
    ]
];

foreach ($rows as $r) {
    $w = (int)$r['weekday'];
    if (!isset($data['byDay'][$w])) {
        $data['byDay'][$w] = [];
    }
    $data['byDay'][$w][] = [
        'start'       => substr($r['start_time'], 0, 5),
        'end'         => substr($r['end_time'], 0, 5),
        'title'       => $r['title'],
        'category'    => $r['category'],
        'img'         => $r['img_url'] ?: 'img/placeholder.jpg',
        'description' => $r['description'] ?? ''
    ];
}

echo json_encode($data);
