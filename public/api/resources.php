<?php
require_once __DIR__ . '/../../includes/helpers.php';

$user = require_login();
$pdo  = db();
$UPLOAD_DIR = __DIR__ . '/../uploads/resources/';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $approved = $pdo->query("SELECT r.*, u.name AS uploader_name FROM resources r LEFT JOIN users u ON u.id = r.uploader_id WHERE r.status = 'approved' ORDER BY r.created_at DESC")->fetchAll();

    $pending = [];
    if ($user['role'] === 'admin') {
        $pending = $pdo->query("SELECT r.*, u.name AS uploader_name FROM resources r LEFT JOIN users u ON u.id = r.uploader_id WHERE r.status = 'pending' ORDER BY r.created_at DESC")->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT r.*, u.name AS uploader_name FROM resources r LEFT JOIN users u ON u.id = r.uploader_id WHERE r.status = 'pending' AND r.uploader_id = ? ORDER BY r.created_at DESC");
        $stmt->execute([$user['id']]);
        $pending = $stmt->fetchAll();
    }

    json_out(['approved' => $approved, 'pending' => $pending]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // file uploads arrive as multipart/form-data, not JSON
    $action = $_POST['action'] ?? (body()['action'] ?? 'create');

    if ($action === 'create') {
        $course = trim($_POST['course_code'] ?? '');
        $title  = trim($_POST['title'] ?? '');
        $kind   = ($_POST['kind'] ?? 'notes') === 'qbank' ? 'qbank' : 'notes';
        if ($course === '' || $title === '') json_out(['error' => 'Course code and title are required.'], 422);

        $filePath = null;
        if (!empty($_FILES['file']['name'])) {
            if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0775, true);
            $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $UPLOAD_DIR . $safeName)) {
                $filePath = 'uploads/resources/' . $safeName;
            }
        }

        $status = ($user['role'] === 'admin') ? 'approved' : 'pending';
        $stmt = $pdo->prepare('INSERT INTO resources (kind, course_code, title, file_path, uploader_id, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$kind, $course, $title, $filePath, $user['id'], $status]);
        json_out(['ok' => true, 'status' => $status]);
    }

    $in = body();

    if ($action === 'approve' || $action === 'reject') {
        require_role(['admin']);
        $id = (int)($in['id'] ?? $_POST['id'] ?? 0);
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $pdo->prepare('UPDATE resources SET status = ? WHERE id = ?')->execute([$status, $id]);
        json_out(['ok' => true]);
    }

    if ($action === 'download') {
        $id = (int)($in['id'] ?? $_POST['id'] ?? 0);
        $pdo->prepare('UPDATE resources SET downloads = downloads + 1 WHERE id = ?')->execute([$id]);
        $stmt = $pdo->prepare('SELECT downloads, file_path FROM resources WHERE id = ?');
        $stmt->execute([$id]);
        json_out(['ok' => true] + $stmt->fetch());
    }

    json_out(['error' => 'Unknown action.'], 400);
}

json_out(['error' => 'Method not allowed'], 405);
