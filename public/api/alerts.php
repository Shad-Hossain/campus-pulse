<?php
require_once __DIR__ . '/../../includes/helpers.php';

$user = require_login();
$pdo  = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $alerts = $pdo->query('SELECT * FROM alerts ORDER BY created_at DESC')->fetchAll();
    $status = $pdo->query('SELECT status FROM campus_status WHERE id = 1')->fetchColumn() ?: 'normal';
    json_out(['alerts' => $alerts, 'status' => $status]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_role(['admin']);
    $in     = body();
    $action = $in['action'] ?? '';

    if ($action === 'post') {
        $title = trim($in['title'] ?? '');
        $type  = trim($in['type'] ?? 'Campus notice');
        if ($title === '') json_out(['error' => 'Alert title is required.'], 422);
        $pdo->prepare('INSERT INTO alerts (type, message) VALUES (?,?)')->execute([$type, $title]);
        json_out(['ok' => true]);
    }

    if ($action === 'remove') {
        $id = (int)($in['id'] ?? 0);
        $pdo->prepare('DELETE FROM alerts WHERE id = ?')->execute([$id]);
        json_out(['ok' => true]);
    }

    if ($action === 'status') {
        $status = $in['status'] ?? 'normal';
        if (!in_array($status, ['normal', 'alert', 'critical'], true)) json_out(['error' => 'Invalid status.'], 422);
        $pdo->prepare('UPDATE campus_status SET status = ? WHERE id = 1')->execute([$status]);
        json_out(['ok' => true]);
    }

    json_out(['error' => 'Unknown action.'], 400);
}

json_out(['error' => 'Method not allowed'], 405);
