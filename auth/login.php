<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ../login.php?error=missing');
    exit;
}

$stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

$success = $user && password_verify($password, $user['password_hash']);

$audit = $pdo->prepare('INSERT INTO login_audit (user_id, email, status, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
$audit->execute([
    $user['id'] ?? null,
    $email,
    $success ? 'success' : 'failed',
    $_SERVER['REMOTE_ADDR'] ?? null,
    $_SERVER['HTTP_USER_AGENT'] ?? null
]);

if (!$success) {
    header('Location: ../login.php?error=invalid');
    exit;
}

$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['full_name'],
    'email' => $user['email'],
    'role' => $user['role']
];

header('Location: ../index.php');
exit;
