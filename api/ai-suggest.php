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

$action = $payload['action'] ?? '';
$topic = trim((string) ($payload['topic'] ?? ''));
$form = is_array($payload['form'] ?? null) ? $payload['form'] : [];

function ai_slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string) $text, '-');
}

function ai_guess_topic(array $form, string $topic): string
{
    if ($topic !== '') {
        return $topic;
    }
    $title = trim((string) ($form['title'] ?? ''));
    if ($title !== '') {
        return $title;
    }
    return 'general feedback';
}

function ai_generate_meta(array $form, string $topic): array
{
    $resolvedTopic = ai_guess_topic($form, $topic);
    $title = ucwords($resolvedTopic) . ' Form';
    $description = 'Please complete this form about ' . strtolower($resolvedTopic) . '. Your responses will help us improve quality and accessibility.';

    return [
        'title' => $title,
        'description' => $description,
        'notes' => [
            'Generated using local AI rules mode.',
            'You can edit title and description manually before publishing.'
        ]
    ];
}

function ai_suggest_fields(array $form, string $topic): array
{
    $resolvedTopic = strtolower(ai_guess_topic($form, $topic));
    $seed = ai_slugify($resolvedTopic);

    $fields = [
        [
            'id' => 'ai-' . $seed . '-name-' . time(),
            'type' => 'text',
            'label' => 'Full Name',
            'required' => true,
            'options' => []
        ],
        [
            'id' => 'ai-' . $seed . '-email-' . (time() + 1),
            'type' => 'email',
            'label' => 'Email Address',
            'required' => true,
            'options' => []
        ]
    ];

    if (str_contains($resolvedTopic, 'event') || str_contains($resolvedTopic, 'workshop')) {
        $fields[] = [
            'id' => 'ai-' . $seed . '-attendance-' . (time() + 2),
            'type' => 'radio',
            'label' => 'Will you attend?',
            'required' => true,
            'options' => ['Yes', 'No', 'Maybe']
        ];
        $fields[] = [
            'id' => 'ai-' . $seed . '-session-' . (time() + 3),
            'type' => 'dropdown',
            'label' => 'Preferred Session',
            'required' => false,
            'options' => ['Morning', 'Afternoon', 'Evening']
        ];
    } elseif (str_contains($resolvedTopic, 'course') || str_contains($resolvedTopic, 'class') || str_contains($resolvedTopic, 'student')) {
        $fields[] = [
            'id' => 'ai-' . $seed . '-satisfaction-' . (time() + 2),
            'type' => 'radio',
            'label' => 'Overall Satisfaction',
            'required' => true,
            'options' => ['Excellent', 'Good', 'Average', 'Needs Improvement']
        ];
        $fields[] = [
            'id' => 'ai-' . $seed . '-improve-' . (time() + 3),
            'type' => 'checkbox',
            'label' => 'Areas to Improve',
            'required' => false,
            'options' => ['Content', 'Pace', 'Interaction', 'Assessment']
        ];
    } else {
        $fields[] = [
            'id' => 'ai-' . $seed . '-rating-' . (time() + 2),
            'type' => 'radio',
            'label' => 'How would you rate your experience?',
            'required' => true,
            'options' => ['5 - Excellent', '4 - Good', '3 - Average', '2 - Poor', '1 - Very Poor']
        ];
        $fields[] = [
            'id' => 'ai-' . $seed . '-comments-' . (time() + 3),
            'type' => 'text',
            'label' => 'Additional Comments',
            'required' => false,
            'options' => []
        ];
    }

    return [
        'fields' => $fields,
        'notes' => [
            'Suggestions generated using local AI rules mode.',
            'Review each field before publishing your form.'
        ]
    ];
}

if ($action === 'generate_meta') {
    echo json_encode([
        'ok' => true,
        'mode' => 'local-rules',
        'result' => ai_generate_meta($form, $topic)
    ]);
    exit;
}

if ($action === 'suggest_fields') {
    echo json_encode([
        'ok' => true,
        'mode' => 'local-rules',
        'result' => ai_suggest_fields($form, $topic)
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Unsupported action.']);
