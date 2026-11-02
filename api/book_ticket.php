<?php
/**
 * EventPulse - API: Complete Ticket Booking & Payment Execution
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

$eventId = intval($input['event_id'] ?? 0);
$seatIds = $input['seat_ids'] ?? [];
$attendees = $input['attendees'] ?? [];
$squadCode = trim($input['squad_code'] ?? '');
$enableEcoOffset = !empty($input['carbon_offset']);
$paymentMethod = $input['payment_method'] ?? 'Digital Pass Instant Card';

if ($eventId <= 0 || empty($seatIds)) {
    echo json_encode(['success' => false, 'error' => 'Please select at least one seat/tier.']);
    exit;
}

$pdo = Database::getConnection();

// Check if user is logged in, else use guest attendee account or create on fly
$userId = is_logged_in() ? current_user()['id'] : 3; // Default to attendee user if guest

try {
    $pdo->beginTransaction();

    // 1. Fetch Event details
    $eStmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $eStmt->execute([$eventId]);
    $event = $eStmt->fetch();

    if (!$event) {
        throw new Exception("Event not found.");
    }

    // 2. Fetch and lock seats for update
    $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
    $sStmt = $pdo->prepare("SELECT s.*, tt.price, tt.name as tier_name FROM seats s JOIN ticket_tiers tt ON s.tier_id = tt.id WHERE s.id IN ($placeholders) FOR UPDATE");
    $sStmt->execute($seatIds);
    $seats = $sStmt->fetchAll();

    if (count($seats) !== count($seatIds)) {
        throw new Exception("One or more selected seats could not be found.");
    }

    // Check availability
    foreach ($seats as $seat) {
        if ($seat['status'] === 'booked') {
            throw new Exception("Seat " . $seat['seat_number'] . " has already been purchased by another attendee.");
        }
    }

    // 3. Calculate Pricing
    $totalAmount = 0.0;
    foreach ($seats as $seat) {
        $totalAmount += floatval($seat['price']);
    }

    // Check Squad discount (Novelty 4)
    $discountAmount = 0.0;
    $squadId = null;
    if (!empty($squadCode)) {
        $sqStmt = $pdo->prepare("SELECT id, discount_percent FROM squads WHERE squad_code = ? AND event_id = ?");
        $sqStmt->execute([$squadCode, $eventId]);
        $squad = $sqStmt->fetch();
        if ($squad) {
            $squadId = $squad['id'];
            $discountAmount = ($totalAmount * ($squad['discount_percent'] / 100));
            // Increment squad member count
            $pdo->prepare("UPDATE squads SET member_count = member_count + ? WHERE id = ?")->execute([count($seats), $squadId]);
        }
    }

    // Carbon Offset Add-on (Novelty 5)
    $carbonOffsetFee = $enableEcoOffset ? (5.00 * count($seats)) : 0.00;
    $finalAmount = max(0.00, ($totalAmount - $discountAmount) + $carbonOffsetFee);

    // 4. Create Booking
    $bookingRef = 'EP-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
    $qrToken = bin2hex(random_bytes(16));

    $bStmt = $pdo->prepare("
        INSERT INTO bookings (booking_reference, user_id, event_id, squad_id, total_amount, discount_amount, carbon_offset_fee, final_amount, payment_status, payment_method, qr_token)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', ?, ?)
    ");
    $bStmt->execute([$bookingRef, $userId, $eventId, $squadId, $totalAmount, $discountAmount, $carbonOffsetFee, $finalAmount, $paymentMethod, $qrToken]);
    $bookingId = $pdo->lastInsertId();

    // 5. Update Seats & Insert Ticket Items
    $updateSeatStmt = $pdo->prepare("UPDATE seats SET status = 'booked', locked_by_session = NULL, locked_at = NULL WHERE id = ?");
    $tiStmt = $pdo->prepare("INSERT INTO ticket_items (booking_id, tier_id, seat_id, attendee_name, attendee_email, ticket_hash, checkin_status, eco_badge_granted) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");

    foreach ($seats as $idx => $seat) {
        $updateSeatStmt->execute([$seat['id']]);

        $attName = !empty($attendees[$idx]['name']) ? trim($attendees[$idx]['name']) : (is_logged_in() ? current_user()['name'] : 'Guest Attendee');
        $attEmail = !empty($attendees[$idx]['email']) ? trim($attendees[$idx]['email']) : (is_logged_in() ? current_user()['email'] : 'attendee@eventpulse.io');

        $ticketHash = hash('sha256', $bookingRef . '-' . $seat['seat_number'] . '-' . $attName . '-' . APP_SECRET_KEY);

        $tiStmt->execute([
            $bookingId,
            $seat['tier_id'],
            $seat['id'],
            $attName,
            $attEmail,
            $ticketHash,
            $enableEcoOffset ? 1 : 0
        ]);

        // Increment sold count on tier
        $pdo->prepare("UPDATE ticket_tiers SET sold_count = sold_count + 1 WHERE id = ?")->execute([$seat['tier_id']]);
    }

    // 6. Record Carbon Offset if chosen
    if ($enableEcoOffset) {
        $treesCount = count($seats) * 2;
        $kgCo2 = count($seats) * 50.00;
        $certHash = hash('sha256', $bookingId . time() . 'eco_certificate');
        $coStmt = $pdo->prepare("INSERT INTO carbon_offsets (booking_id, trees_funded, kg_co2_offset, certificate_hash) VALUES (?, ?, ?, ?)");
        $coStmt->execute([$bookingId, $treesCount, $kgCo2, $certHash]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'booking_reference' => $bookingRef,
        'redirect_url' => APP_URL . '/confirmation.php?ref=' . urlencode($bookingRef)
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
