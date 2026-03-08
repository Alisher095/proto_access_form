<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Authentication required.']);
    exit;
}

if (($_SESSION['user']['role'] ?? '') !== 'creator') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Creator role required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

$form = is_array($payload['form'] ?? null) ? $payload['form'] : [];
$fields = is_array($form['fields'] ?? null) ? $form['fields'] : [];
$warnings = [];

$title = trim((string) ($form['title'] ?? ''));
if ($title === '') {
    $warnings[] = [
        'code' => 'missing_title',
        'severity' => 'high',
        'message' => 'Form title is missing. Add a clear title for screen readers and context.'
    ];
}

$description = trim((string) ($form['description'] ?? ''));
if ($description === '' || strlen($description) < 20) {
    $warnings[] = [
        'code' => 'weak_description',
        'severity' => 'medium',
        'message' => 'Add a longer description to explain purpose and instructions.'
    ];
}

if (count($fields) === 0) {
    $warnings[] = [
        'code' => 'no_fields',
        'severity' => 'high',
        'message' => 'Form has no fields. Add at least one question.'
    ];
}

$labelMap = [];
$requiredCount = 0;

foreach ($fields as $index => $field) {
    $label = trim((string) ($field['label'] ?? ''));
    $type = (string) ($field['type'] ?? 'text');
    $required = (bool) ($field['required'] ?? false);
    $options = is_array($field['options'] ?? null) ? $field['options'] : [];

    if ($required) {
        $requiredCount++;
    }

    if ($label === '') {
        $warnings[] = [
            'code' => 'empty_label',
            'severity' => 'high',
            'message' => 'Field ' . ($index + 1) . ' has no label.'
        ];
    } else {
        $key = strtolower($label);
        $labelMap[$key] = ($labelMap[$key] ?? 0) + 1;
    }

    if (in_array($type, ['checkbox', 'radio', 'dropdown'], true)) {
        if (count($options) < 2) {
            $warnings[] = [
                'code' => 'few_options',
                'severity' => 'medium',
                'message' => 'Field "' . ($label !== '' ? $label : 'Untitled') . '" needs at least 2 options.'
            ];
        }
        foreach ($options as $opt) {
            if (trim((string) $opt) === '') {
                $warnings[] = [
                    'code' => 'empty_option',
                    'severity' => 'low',
                    'message' => 'Field "' . ($label !== '' ? $label : 'Untitled') . '" contains an empty option.'
                ];
                break;
            }
        }
    }
}

foreach ($labelMap as $label => $count) {
    if ($count > 1) {
        $warnings[] = [
            'code' => 'duplicate_label',
            'severity' => 'medium',
            'message' => 'Duplicate label detected: "' . $label . '" appears ' . $count . ' times.'
        ];
    }
}

if (count($fields) > 0 && $requiredCount === count($fields)) {
    $warnings[] = [
        'code' => 'all_required',
        'severity' => 'low',
        'message' => 'All fields are required. Consider making some optional for better usability.'
    ];
}

$score = max(0, 100 - (count($warnings) * 12));

$response = [
    'ok' => true,
    'mode' => 'local-rules',
    'result' => [
        'score' => $score,
        'warnings' => $warnings,
        'summary' => count($warnings) === 0 ? 'Great! No major accessibility issues found.' : 'Accessibility review found ' . count($warnings) . ' item(s).'
    ]
];

echo json_encode($response);
