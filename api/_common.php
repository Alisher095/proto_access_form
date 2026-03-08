<?php

function api_bootstrap_auth(): void
{
    session_start();
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user'])) {
        api_error('Authentication required.', 401);
    }
}

function api_require_role(string $role): void
{
    $currentRole = $_SESSION['user']['role'] ?? '';
    if ($currentRole !== $role) {
        api_error(ucfirst($role) . ' role required.', 403);
    }
}

function api_error(string $message, int $status = 400): void
{
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

function api_success(array $payload = []): void
{
    echo json_encode(array_merge(['ok' => true], $payload));
    exit;
}

function api_require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        api_error('Method not allowed.', 405);
    }
}

function api_read_json(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        api_error('Invalid JSON payload.', 400);
    }
    return $data;
}

function api_allowed_field_type(string $type): string
{
    $allowed = ['text', 'email', 'number', 'checkbox', 'radio', 'dropdown'];
    return in_array($type, $allowed, true) ? $type : 'text';
}
