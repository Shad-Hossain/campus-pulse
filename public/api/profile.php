<?php
require_once __DIR__ . '/../../includes/helpers.php';

$user = require_login();
$pdo  = db();
$UPLOAD_DIR = __DIR__ . '/../uploads/avatars/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$name = trim($_POST['name'] ?? '');
$dept = trim($_POST['department'] ?? '');
$bio  = trim($_POST['bio'] ?? '');
if ($name === '') $name = $user['name'];
if ($dept === '') $dept = $user['department'];

$avatarPath = $user['avatar_path'];
if (!empty($_FILES['avatar']['name'])) {
    if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0775, true);
    $safeName = 'u' . $user['id'] . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['avatar']['name']);
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $UPLOAD_DIR . $safeName)) {
        $avatarPath = 'uploads/avatars/' . $safeName;
    }
}

$stmt = $pdo->prepare('UPDATE users SET name = ?, department = ?, bio = ?, avatar_path = ? WHERE id = ?');
$stmt->execute([$name, $dept, $bio, $avatarPath, $user['id']]);

json_out(['ok' => true, 'user' => [
    'id' => $user['id'], 'name' => $name, 'email' => $user['email'], 'role' => $user['role'],
    'department' => $dept, 'bio' => $bio, 'avatar_path' => $avatarPath,
]]);
