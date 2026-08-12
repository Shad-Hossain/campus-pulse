<?php
require_once __DIR__ . '/../../includes/helpers.php';

require_login();
$pdo = db();

$q   = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? 'all');

$items = [];
foreach ($pdo->query('SELECT category, title, description, meta FROM news') as $r) $items[] = $r;
foreach ($pdo->query("SELECT category, title, description, meta FROM events WHERE status = 'approved'") as $r) $items[] = $r;
foreach ($pdo->query('SELECT category, title, description, meta FROM achievements') as $r) $items[] = $r;
foreach ($pdo->query("SELECT 'Admin' AS category, title, description, '' AS meta FROM research_grants WHERE status = 'approved'") as $r) $items[] = $r;

if ($cat !== 'all') {
    $items = array_values(array_filter($items, fn($i) => $i['category'] === $cat));
}
if ($q !== '') {
    $ql = strtolower($q);
    $items = array_values(array_filter($items, fn($i) =>
        str_contains(strtolower($i['title']), $ql) || str_contains(strtolower($i['description'] ?? ''), $ql)
    ));
}

json_out(['items' => $items]);
