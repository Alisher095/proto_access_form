<?php
require __DIR__ . '/_common.php';
require __DIR__ . '/../auth/db.php';

api_bootstrap_auth();

function resolve_form_id(PDO $pdo): ?int
{
    $requested = isset($_GET['form_id']) ? (int) $_GET['form_id'] : 0;
    $role = $_SESSION['user']['role'] ?? '';
    $userId = (int) ($_SESSION['user']['id'] ?? 0);

    if ($requested > 0) {
        if ($role === 'creator') {
            $stmt = $pdo->prepare('SELECT id FROM forms WHERE id = ? AND user_id = ? LIMIT 1');
            $stmt->execute([$requested, $userId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            return (int) $row['id'];
        }

        $stmt = $pdo->prepare('SELECT id FROM forms WHERE id = ? LIMIT 1');
        $stmt->execute([$requested]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id'] : null;
    }

    if ($role === 'creator') {
        $stmt = $pdo->prepare('SELECT id FROM forms WHERE user_id = ? AND is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['id'] : null;
    }

    $stmt = $pdo->query('SELECT id FROM forms WHERE is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $formId = resolve_form_id($pdo);
    if (!$formId) {
        api_success(['responses' => []]);
    }

    $stmt = $pdo->prepare('SELECT response_json, submitted_at FROM form_responses WHERE form_id = ? ORDER BY submitted_at DESC, id DESC');
    $stmt->execute([$formId]);

    $responses = [];
    while ($row = $stmt->fetch()) {
        $values = json_decode((string) $row['response_json'], true);
        $responses[] = [
            'submittedAt' => $row['submitted_at'],
            'values' => is_array($values) ? $values : []
        ];
    }

    api_success(['responses' => $responses]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = api_read_json();
    $formId = (int) ($payload['formId'] ?? 0);
    $values = is_array($payload['values'] ?? null) ? $payload['values'] : null;

    if ($formId <= 0 || !$values) {
        api_error('Valid formId and values are required.', 400);
    }

    $check = $pdo->prepare('SELECT id FROM forms WHERE id = ? LIMIT 1');
    $check->execute([$formId]);
    if (!$check->fetch()) {
        api_error('Form not found.', 404);
    }

    $insert = $pdo->prepare('INSERT INTO form_responses (form_id, submitted_by, response_json) VALUES (?, ?, ?)');
    $insert->execute([
        $formId,
        (int) ($_SESSION['user']['id'] ?? 0),
        json_encode($values)
    ]);

    api_success(['message' => 'Response submitted successfully.']);
}

api_error('Method not allowed.', 405);
