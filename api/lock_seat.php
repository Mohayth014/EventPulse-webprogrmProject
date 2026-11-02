<?php
/**
 * EventPulse - API: Lock Seat (Novelty 1)
 * Holds selected seat for 5 minutes for the current session
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$seatId = intval($input['seat_id'] ?? 0);
$action = $input['action'] ?? 'lock'; // lock or unlock
$sessionId = session_id();

if ($seatId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid seat ID']);
    exit;
}

$pdo = Database::getConnection();

// Clean up expired locks (> 5 mins)
$pdo->exec("UPDATE seats SET status = 'available', locked_by_session = NULL, locked_at = NULL WHERE status = 'reserved' AND locked_at < (NOW() - INTERVAL 5 MINUTE)");

if ($action === 'lock') {
    // Check if available or locked by me
    $stmt = $pdo->prepare("SELECT id, status, locked_by_session FROM seats WHERE id = ?");
    $stmt->execute([$seatId]);
    $seat = $stmt->fetch();

    if (!$seat) {
        echo json_encode(['success' => false, 'error' => 'Seat not found']);
        exit;
    }

    if ($seat['status'] === 'booked') {
        echo json_encode(['success' => false, 'error' => 'Seat already sold out']);
        exit;
    }

    if ($seat['status'] === 'reserved' && $seat['locked_by_session'] !== $sessionId) {
        echo json_encode(['success' => false, 'error' => 'Seat is currently being reserved by another customer']);
        exit;
    }

    // Lock seat
    $lockStmt = $pdo->prepare("UPDATE seats SET status = 'reserved', locked_by_session = ?, locked_at = NOW() WHERE id = ?");
    $lockStmt->execute([$sessionId, $seatId]);

    echo json_encode(['success' => true, 'message' => 'Seat held for 5 minutes', 'expires_in' => 300]);
} else {
    // Unlock
    $unlockStmt = $pdo->prepare("UPDATE seats SET status = 'available', locked_by_session = NULL, locked_at = NULL WHERE id = ? AND locked_by_session = ?");
    $unlockStmt->execute([$seatId, $sessionId]);

    echo json_encode(['success' => true, 'message' => 'Seat unlocked']);
}
