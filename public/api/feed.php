<?php
require_once __DIR__ . '/../../includes/helpers.php';

require_login();
$pdo = db();

$news        = $pdo->query('SELECT * FROM news ORDER BY created_at DESC')->fetchAll();
$achievements = $pdo->query('SELECT * FROM achievements ORDER BY created_at DESC')->fetchAll();

json_out(['news' => $news, 'achievements' => $achievements]);
