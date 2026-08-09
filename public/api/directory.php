<?php
require_once __DIR__ . '/../../includes/helpers.php';

require_role(['admin']);
$pdo = db();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare('SELECT name, department, role FROM users WHERE name LIKE ? OR department LIKE ? ORDER BY name');
    $like = "%$q%";
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query('SELECT name, department, role FROM users ORDER BY name');
}

json_out(['users' => $stmt->fetchAll()]);
