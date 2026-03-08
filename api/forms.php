<?php
require __DIR__ . '/_common.php';
require __DIR__ . '/../auth/db.php';

api_bootstrap_auth();

function map_form(PDO $pdo, array $formRow): array
{
    $stmt = $pdo->prepare('SELECT field_key, field_type, label, is_required, options_json FROM form_fields WHERE form_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$formRow['id']]);
    $fields = [];
    while ($row = $stmt->fetch()) {
        $options = json_decode((string) $row['options_json'], true);
        $fields[] = [
            'id' => $row['field_key'],
            'type' => $row['field_type'],
            'label' => $row['label'],
            'required' => (bool) $row['is_required'],
            'options' => is_array($options) ? $options : []
        ];
    }

    return [
        'id' => 'form-' . $formRow['id'],
        'dbId' => (int) $formRow['id'],
        'title' => $formRow['title'],
        'description' => (string) ($formRow['description'] ?? ''),
        'fields' => $fields
    ];
}

function create_default_form(PDO $pdo, int $userId): array
{
    $pdo->beginTransaction();
    try {
        $insertForm = $pdo->prepare('INSERT INTO forms (user_id, title, description, is_active) VALUES (?, ?, ?, 1)');
        $insertForm->execute([$userId, 'Student Feedback Form', 'Help us improve by sharing your experience.']);
        $formId = (int) $pdo->lastInsertId();

        $fieldStmt = $pdo->prepare('INSERT INTO form_fields (form_id, field_key, field_type, label, is_required, options_json, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $seedFields = [
            ['f1', 'text', 'Full name', 1, json_encode([]), 0],
            ['f2', 'email', 'Email address', 1, json_encode([]), 1],
            ['f3', 'radio', 'Overall satisfaction', 1, json_encode(['Excellent', 'Good', 'Average', 'Needs improvement']), 2],
            ['f4', 'dropdown', 'Program', 0, json_encode(['BSCS', 'BBA', 'MBA', 'Other']), 3]
        ];

        foreach ($seedFields as $field) {
            $fieldStmt->execute([$formId, $field[0], $field[1], $field[2], $field[3], $field[4], $field[5]]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        api_error('Failed to create default form.', 500);
    }

    return [
        'id' => $formId,
        'title' => 'Student Feedback Form',
        'description' => 'Help us improve by sharing your experience.'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = $_SESSION['user'];
    $userId = (int) ($user['id'] ?? 0);
    $role = $user['role'] ?? '';

    if ($role === 'creator') {
        $stmt = $pdo->prepare('SELECT id, title, description FROM forms WHERE user_id = ? AND is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
        $stmt->execute([$userId]);
        $formRow = $stmt->fetch();

        if (!$formRow) {
            $formRow = create_default_form($pdo, $userId);
        }
    } else {
        $stmt = $pdo->query('SELECT id, title, description FROM forms WHERE is_active = 1 ORDER BY updated_at DESC, id DESC LIMIT 1');
        $formRow = $stmt->fetch();
        if (!$formRow) {
            api_success(['form' => null]);
        }
    }

    api_success(['form' => map_form($pdo, $formRow)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    api_require_role('creator');
    $payload = api_read_json();
    $form = is_array($payload['form'] ?? null) ? $payload['form'] : null;
    if (!$form) {
        api_error('Form payload is required.', 400);
    }

    $title = trim((string) ($form['title'] ?? ''));
    $description = trim((string) ($form['description'] ?? ''));
    $fields = is_array($form['fields'] ?? null) ? $form['fields'] : [];
    if ($title === '') {
        $title = 'Untitled form';
    }

    $userId = (int) ($_SESSION['user']['id'] ?? 0);
    $incomingDbId = isset($form['dbId']) ? (int) $form['dbId'] : 0;

    $pdo->beginTransaction();
    try {
        if ($incomingDbId > 0) {
            $ownStmt = $pdo->prepare('SELECT id FROM forms WHERE id = ? AND user_id = ? LIMIT 1');
            $ownStmt->execute([$incomingDbId, $userId]);
            $owned = $ownStmt->fetch();
            if (!$owned) {
                throw new RuntimeException('Form ownership validation failed.');
            }

            $update = $pdo->prepare('UPDATE forms SET title = ?, description = ?, is_active = 1 WHERE id = ?');
            $update->execute([$title, $description, $incomingDbId]);
            $formId = $incomingDbId;
        } else {
            $deactivate = $pdo->prepare('UPDATE forms SET is_active = 0 WHERE user_id = ?');
            $deactivate->execute([$userId]);

            $insert = $pdo->prepare('INSERT INTO forms (user_id, title, description, is_active) VALUES (?, ?, ?, 1)');
            $insert->execute([$userId, $title, $description]);
            $formId = (int) $pdo->lastInsertId();
        }

        $clearFields = $pdo->prepare('DELETE FROM form_fields WHERE form_id = ?');
        $clearFields->execute([$formId]);

        $insertField = $pdo->prepare('INSERT INTO form_fields (form_id, field_key, field_type, label, is_required, options_json, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');

        foreach ($fields as $index => $field) {
            if (!is_array($field)) {
                continue;
            }
            $fieldKey = trim((string) ($field['id'] ?? ''));
            if ($fieldKey === '') {
                $fieldKey = 'f-' . $formId . '-' . $index;
            }
            $type = api_allowed_field_type((string) ($field['type'] ?? 'text'));
            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                $label = 'Question ' . ($index + 1);
            }
            $required = !empty($field['required']) ? 1 : 0;
            $options = is_array($field['options'] ?? null) ? array_values(array_filter(array_map('strval', $field['options']), static fn($v) => trim($v) !== '')) : [];
            $optionsJson = in_array($type, ['checkbox', 'radio', 'dropdown'], true) ? json_encode($options) : json_encode([]);

            $insertField->execute([$formId, $fieldKey, $type, $label, $required, $optionsJson, $index]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        api_error('Failed to save form.', 500);
    }

    $savedStmt = $pdo->prepare('SELECT id, title, description FROM forms WHERE id = ? LIMIT 1');
    $savedStmt->execute([$formId]);
    $savedForm = $savedStmt->fetch();

    api_success(['form' => map_form($pdo, $savedForm)]);
}

api_error('Method not allowed.', 405);
