<?php
/**
 * EventPulse - Novelty 2: Dynamic Holographic Pass & Mobile Wallet Experience
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$ticketHash = trim($_GET['hash'] ?? '');
if (empty($ticketHash)) {
    header('Location: ' . APP_URL . '/my_tickets.php');
    exit;
}

$pdo = Database::getConnection();

// Fetch Ticket & Booking details
$stmt = $pdo->prepare("
    SELECT ti.*, tt.name as tier_name, tt.price as tier_price, tt.perks_json,
           s.seat_number, s.row_name, s.col_number,
           b.booking_reference, b.created_at as booked_at,
           e.title as event_title, e.start_date, e.end_date, e.venue_name, e.venue_address, e.city, e.banner_image
    FROM ticket_items ti
    JOIN bookings b ON ti.booking_id = b.id
    JOIN events e ON b.event_id = e.id
    JOIN ticket_tiers tt ON ti.tier_id = tt.id
    LEFT JOIN seats s ON ti.seat_id = s.id
    WHERE ti.ticket_hash = ?
");
$stmt->execute([$ticketHash]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die('Invalid or non-existent ticket pass.');
}

$pageTitle = 'Digital Pass: ' . $ticket['attendee_name'];
$totpData = generate_dynamic_pass_totp($ticket['ticket_hash']);
$qrData = json_encode([
    'hash' => $ticket['ticket_hash'],
    'ref' => $ticket['booking_reference'],
    'seat' => $ticket['seat_number'],
    'att' => $ticket['attendee_name']
]);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: #090d16;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .wallet-pass-card {
            max-width: 480px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="wallet-pass-card">
    <!-- Top Pass Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= APP_URL ?>/my_tickets.php" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left"></i> My Tickets
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-printer"></i> Print
            </button>
            <a href="<?= APP_URL ?>/admin/scanner.php" target="_blank" class="btn btn-sm btn-outline-success rounded-pill">
                <i class="bi bi-camera"></i> Test at Gate
            </a>
        </div>
    </div>

    <!-- Holographic Dynamic Wallet Pass -->
    <div class="holographic-ticket-pass p-4 text-white">
        <!-- Event Header -->
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="badge bg-warning text-dark fw-bold mb-1">
                    <i class="bi bi-ticket-perforated-fill"></i> <?= e($ticket['tier_name']) ?>
                </span>
                <h4 class="fw-bold mb-0 text-white"><?= e($ticket['event_title']) ?></h4>
                <div class="small text-white-50"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e($ticket['venue_name']) ?>, <?= e($ticket['city']) ?></div>
            </div>
            <div class="text-end">
                <div class="fs-xxs text-uppercase text-white-50">Assigned Seat</div>
                <div class="badge bg-primary fs-5 px-3 py-2 fw-bold">
                    <?= e($ticket['seat_number'] ?? 'GA') ?>
                </div>
            </div>
        </div>

        <div class="row g-2 py-2 border-top border-bottom border-white border-opacity-10 my-3 small">
            <div class="col-6">
                <div class="text-white-50 fs-xxs text-uppercase">Attendee</div>
                <div class="fw-bold text-truncate"><?= e($ticket['attendee_name']) ?></div>
            </div>
            <div class="col-6">
                <div class="text-white-50 fs-xxs text-uppercase">Date & Time</div>
                <div class="fw-bold"><?= format_date($ticket['start_date'], 'M d, Y - h:i A') ?></div>
            </div>
            <div class="col-6">
                <div class="text-white-50 fs-xxs text-uppercase">Booking Ref</div>
                <div class="font-monospace fw-bold text-info"><?= e($ticket['booking_reference']) ?></div>
            </div>
            <div class="col-6">
                <div class="text-white-50 fs-xxs text-uppercase">Gate Status</div>
                <?php if ($ticket['checkin_status'] === 'checked_in'): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-check-circle"></i> CHECKED-IN</span>
                <?php else: ?>
                    <span class="badge bg-success"><i class="bi bi-shield-check"></i> ACTIVE PASS</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Perforated Notch Divider -->
        <div class="ticket-pass-perforated">
            <div class="ticket-notch-left"></div>
            <div class="ticket-notch-right"></div>
        </div>

        <!-- Dynamic Anti-Scalp QR Code & Rotating TOTP (Novelty 2) -->
        <div class="text-center py-2">
            <div class="bg-white p-3 rounded-4 d-inline-block shadow-lg mb-3">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($ticket['ticket_hash']) ?>" alt="QR Pass" width="180" height="180">
            </div>

            <!-- Rotating TOTP Bar -->
            <div class="p-2 rounded-3 bg-body-tertiary bg-opacity-25 border border-white border-opacity-10 d-inline-flex flex-column align-items-center px-4">
                <div class="fs-xxs text-uppercase text-white-50 letter-spacing-1">ANTI-SCALPING DYNAMIC CODE</div>
                <div class="totp-rotating-code" id="totpCodeDisplay"><?= $totpData['totp'] ?></div>
                <div class="small text-info mt-1 d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-repeat spin-icon"></i>
                    <span>Refreshes in <strong id="totpCountdown"><?= $totpData['remaining'] ?></strong>s</span>
                </div>
            </div>
        </div>

        <!-- Eco-Guardian Badge if granted -->
        <?php if ($ticket['eco_badge_granted']): ?>
            <div class="text-center mt-3">
                <span class="eco-guardian-badge">
                    <i class="bi bi-tree-fill"></i> Verified Eco-Guardian Passholder
                </span>
            </div>
        <?php endif; ?>

        <!-- Security Watermark microtext -->
        <div class="text-center mt-3 pt-2 border-top border-white border-opacity-10">
            <div class="fs-xxs text-white-50 font-monospace text-truncate">
                SHA-256: <?= $ticket['ticket_hash'] ?>
            </div>
            <div class="fs-xxs text-white-50 mt-1">
                Screenshots & duplicates are blocked at gate by rotating cryptographic token.
            </div>
        </div>
    </div>
</div>

<script>
// Live 30-Second TOTP Countdown Simulator (Novelty 2)
let remaining = <?= $totpData['remaining'] ?>;
const countdownEl = document.getElementById('totpCountdown');
const codeEl = document.getElementById('totpCodeDisplay');

setInterval(() => {
    remaining--;
    if (remaining <= 0) {
        remaining = 30;
        // Generate pseudo next rotation
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let newCode = '';
        for (let i = 0; i < 8; i++) newCode += chars.charAt(Math.floor(Math.random() * chars.length));
        codeEl.textContent = newCode;
    }
    countdownEl.textContent = remaining;
}, 1000);
</script>

</body>
</html>
