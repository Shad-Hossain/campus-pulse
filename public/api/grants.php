<?php
require_once __DIR__ . '/../../includes/helpers.php';

$user = require_login();
$pdo  = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $approved = $pdo->query("SELECT g.*, u.name AS submitter_name FROM research_grants g LEFT JOIN users u ON u.id = g.submitted_by WHERE g.status = 'approved' ORDER BY g.created_at DESC")->fetchAll();

    $mine = [];
    $stmt = $pdo->prepare('SELECT * FROM research_grants WHERE submitted_by = ? ORDER BY created_at DESC');
    $stmt->execute([$user['id']]);
    $mine = $stmt->fetchAll();

    $pending = [];
    if ($user['role'] === 'admin') {
        $pending = $pdo->query("SELECT g.*, u.name AS submitter_name FROM research_grants g LEFT JOIN users u ON u.id = g.submitted_by WHERE g.status = 'pending' ORDER BY g.created_at DESC")->fetchAll();
    }

    json_out(['approved' => $approved, 'mine' => $mine, 'pending' => $pending]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in     = body();
    $action = $in['action'] ?? 'create';

    if ($action === 'create') {
        require_role(['faculty', 'admin']);
        $title = trim($in['title'] ?? '');
        $desc  = trim($in['description'] ?? '') ?: 'No description provided.';
        if ($title === '') json_out(['error' => 'Title is required.'], 422);

        $status = ($user['role'] === 'admin') ? 'approved' : 'pending';
        $stmt = $pdo->prepare('INSERT INTO research_grants (title, description, status, submitted_by) VALUES (?,?,?,?)');
        $stmt->execute([$title, $desc, $status, $user['id']]);
        json_out(['ok' => true, 'status' => $status]);
    }

    if ($action === 'approve' || $action === 'reject') {
        require_role(['admin']);
        $id = (int)($in['id'] ?? 0);
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $pdo->prepare('UPDATE research_grants SET status = ? WHERE id = ?')->execute([$status, $id]);
        json_out(['ok' => true]);
    }

    json_out(['error' => 'Unknown action.'], 400);
}

json_out(['error' => 'Method not allowed'], 405);
