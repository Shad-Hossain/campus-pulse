<?php
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed'], 405);

$in = body();
$name       = trim($in['name'] ?? '');
$email      = trim($in['email'] ?? '');
$password   = (string)($in['password'] ?? '');
$role       = trim($in['role'] ?? 'student');
$department = trim($in['department'] ?? 'CSE');

if ($name === '' || $email === '' || $password === '') {
    json_out(['error' => 'Name, email and password are required.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['error' => 'Please enter a valid email address.'], 422);
}
if (strlen($password) < 6) {
    json_out(['error' => 'Password must be at least 6 characters.'], 422);
}
if (!in_array($role, ['student', 'faculty', 'admin'], true)) {
    json_out(['error' => 'Please pick a valid role.'], 422);
}

$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    json_out(['error' => 'An account with this email already exists. Try logging in instead.'], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = db()->prepare(
    'INSERT INTO users (name, email, password_hash, role, department) VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$name, $email, $hash, $role, $department]);

$userId = (int)db()->lastInsertId();
$_SESSION['user_id'] = $userId;

$stmt = db()->prepare('SELECT id, name, email, role, department, bio, avatar_path FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

json_out(['user' => $user]);