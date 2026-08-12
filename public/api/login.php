<?php
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$in = body();
$email    = trim($in['email'] ?? '');
$password = (string)($in['password'] ?? '');
$role     = trim($in['role'] ?? '');

if ($email === '' || $password === '') {
    json_out(['error' => 'Email and password are required.'], 422);
}

$stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_out(['error' => 'Invalid email or password.'], 401);
}

if ($role && $role !== $user['role']) {
    json_out(['error' => "This account is registered as '{$user['role']}', not '{$role}'. Pick the correct role tab."], 401);
}

$_SESSION['user_id'] = $user['id'];

unset($user['password_hash']);
json_out(['user' => $user]);
