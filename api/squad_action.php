<?php
/**
 * EventPulse - API: Squad Management (Novelty 4)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Please sign in to manage squads']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$currentUser = current_user();
$pdo = Database::getConnection();

if ($action === 'create_squad') {
    $eventId = intval($input['event_id'] ?? 1);
    $squadName = trim($input['squad_name'] ?? ($currentUser['name'] . '’s Squad'));
    $targetSize = intval($input['target_size'] ?? 4);

    $code = 'SQ-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $squadName), 0, 4)) . '-' . mt_rand(1000, 9999);

    $stmt = $pdo->prepare("INSERT INTO squads (event_id, squad_leader_id, squad_code, squad_name, discount_percent, member_count, target_size) VALUES (?, ?, ?, ?, 15, 1, ?)");
    $stmt->execute([$eventId, $currentUser['id'], $code, $squadName, $targetSize]);
    $squadId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'squad' => [
            'id' => $squadId,
            'code' => $code,
            'name' => $squadName,
            'discount' => 15,
            'share_url' => APP_URL . '/squad.php?code=' . $code
        ],
        'message' => 'Squad created successfully! Share your squad code with your team.'
    ]);
    exit;
}

if ($action === 'verify_squad') {
    $code = trim($input['code'] ?? '');
    $stmt = $pdo->prepare("SELECT s.*, e.title as event_title, u.name as leader_name FROM squads s JOIN events e ON s.event_id = e.id JOIN users u ON s.squad_leader_id = u.id WHERE s.squad_code = ?");
    $stmt->execute([$code]);
    $squad = $stmt->fetch();

    if ($squad) {
        echo json_encode([
            'success' => true,
            'squad' => $squad,
            'discount_percent' => $squad['discount_percent']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid or expired squad code.']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unrecognized action']);
