<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit;
}

$fullName = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if ($fullName === '' || $email === '' || $password === '') {
    header('Location: ../signup.php?error=missing');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../signup.php?error=email');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$fullName, $email, $hash]);
    $userId = $pdo->lastInsertId();

    $_SESSION['user'] = [
        'id' => $userId,
        'name' => $fullName,
        'email' => $email,
        'role' => 'creator'
    ];

    header('Location: ../index.php');
    exit;
} catch (PDOException $e) {
    $error = $e->getCode() === '23000' ? 'exists' : 'server';
    header('Location: ../signup.php?error=' . $error);
    exit;
}
