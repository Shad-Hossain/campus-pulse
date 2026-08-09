<?php
require_once __DIR__ . '/../../includes/helpers.php';

$user = require_login();
$pdo  = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $approved = $pdo->query("SELECT * FROM events WHERE status = 'approved' ORDER BY created_at DESC")->fetchAll();

    $pending = [];
    if ($user['role'] === 'admin') {
        $pending = $pdo->query("SELECT * FROM events WHERE status = 'pending' ORDER BY created_at DESC")->fetchAll();
    } elseif ($user['role'] === 'faculty') {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE status = 'pending' AND created_by = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
        $pending = $stmt->fetchAll();
    }

    json_out(['approved' => $approved, 'pending' => $pending]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in     = body();
    $action = $in['action'] ?? 'create';

    if ($action === 'create') {
        require_role(['faculty', 'admin']);
        $title = trim($in['title'] ?? '');
        $cat   = trim($in['category'] ?? 'Academic');
        $meta  = trim($in['meta'] ?? '') ?: 'Pending schedule';
        if ($title === '') json_out(['error' => 'Event title is required.'], 422);

        // admin-created events go live immediately; faculty submissions need approval
        $status = ($user['role'] === 'admin') ? 'approved' : 'pending';

        $stmt = $pdo->prepare('INSERT INTO events (category, title, description, meta, status, created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$cat, $title, 'Posted for approval by faculty.', $meta, $status, $user['id']]);
        json_out(['ok' => true, 'status' => $status]);
    }

    if ($action === 'approve' || $action === 'reject') {
        require_role(['admin']);
        $id = (int)($in['id'] ?? 0);
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $pdo->prepare('UPDATE events SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        json_out(['ok' => true]);
    }

    if ($action === 'interest') {
        $id = (int)($in['id'] ?? 0);
        try {
            $stmt = $pdo->prepare('INSERT INTO event_interests (event_id, user_id) VALUES (?,?)');
            $stmt->execute([$id, $user['id']]);
            $pdo->prepare('UPDATE events SET interest_count = interest_count + 1 WHERE id = ?')->execute([$id]);
            $pinged = true;
        } catch (PDOException $e) {
            // already pinged — un-ping (toggle off)
            $pdo->prepare('DELETE FROM event_interests WHERE event_id = ? AND user_id = ?')->execute([$id, $user['id']]);
            $pdo->prepare('UPDATE events SET interest_count = GREATEST(0, interest_count - 1) WHERE id = ?')->execute([$id]);
            $pinged = false;
        }
        $count = $pdo->prepare('SELECT interest_count FROM events WHERE id = ?');
        $count->execute([$id]);
        json_out(['ok' => true, 'pinged' => $pinged, 'interest_count' => (int)$count->fetchColumn()]);
    }

    json_out(['error' => 'Unknown action.'], 400);
}

json_out(['error' => 'Method not allowed'], 405);
