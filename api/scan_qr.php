<?php
/**
 * EventPulse - API: Scan QR Gatekeeper Check-in
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');

if (empty($token)) {
    echo json_encode(['success' => false, 'error' => 'No ticket token or hash provided']);
    exit;
}

$pdo = Database::getConnection();

// The token scanned could be:
// 1) The ticket_hash
// 2) The booking qr_token
// 3) A JSON string or URL containing the token
if (strpos($token, '{') === 0) {
    $parsed = json_decode($token, true);
    if (!empty($parsed['hash'])) {
        $token = $parsed['hash'];
    } elseif (!empty($parsed['ref'])) {
        $token = $parsed['ref'];
    }
}

// Check by ticket_hash or booking reference / qr_token
$stmt = $pdo->prepare("
    SELECT 
        ti.id as ticket_item_id,
        ti.attendee_name,
        ti.attendee_email,
        ti.ticket_hash,
        ti.checkin_status,
        ti.checkin_time,
        ti.eco_badge_granted,
        tt.name as tier_name,
        s.seat_number,
        e.title as event_title,
        b.booking_reference,
        b.payment_status
    FROM ticket_items ti
    JOIN bookings b ON ti.booking_id = b.id
    JOIN events e ON b.event_id = e.id
    JOIN ticket_tiers tt ON ti.tier_id = tt.id
    LEFT JOIN seats s ON ti.seat_id = s.id
    WHERE ti.ticket_hash = ? OR b.qr_token = ? OR b.booking_reference = ?
    LIMIT 1
");
$stmt->execute([$token, $token, $token]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo json_encode([
        'success' => false,
        'status' => 'INVALID',
        'error' => 'Invalid ticket token. No record found.'
    ]);
    exit;
}

// Check if already checked in
if ($ticket['checkin_status'] === 'checked_in') {
    echo json_encode([
        'success' => false,
        'status' => 'ALREADY_CHECKED_IN',
        'ticket' => $ticket,
        'error' => 'Pass already used!'
    ]);
    exit;
}

// Mark ticket as checked in
$updateStmt = $pdo->prepare("
    UPDATE ticket_items 
    SET checkin_status = 'checked_in', checkin_time = NOW() 
    WHERE id = ?
");
$updateStmt->execute([$ticket['ticket_item_id']]);

$ticket['checkin_status'] = 'checked_in';
$ticket['checkin_time'] = date('Y-m-d H:i:s');

echo json_encode([
    'success' => true,
    'status' => 'VALID',
    'ticket' => $ticket,
    'message' => 'Check-in confirmed successfully!'
]);
