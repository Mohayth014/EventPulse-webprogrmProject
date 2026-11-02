<?php
/**
 * EventPulse - Booking Confirmation & Pass Access
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$bookingRef = trim($_GET['ref'] ?? '');
if (empty($bookingRef)) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$pdo = Database::getConnection();

// Fetch Booking
$bStmt = $pdo->prepare("
    SELECT b.*, e.title as event_title, e.start_date, e.venue_name, e.city, e.banner_image,
           u.name as booker_name, u.email as booker_email,
           s.squad_name, s.squad_code
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN squads s ON b.squad_id = s.id
    WHERE b.booking_reference = ?
");
$bStmt->execute([$bookingRef]);
$booking = $bStmt->fetch();

if (!$booking) {
    header('Location: ' . APP_URL . '/index.php');
    exit;
}

$pageTitle = 'Reservation Confirmed - ' . $booking['booking_reference'];
require_once __DIR__ . '/includes/header.php';

// Fetch Ticket Items
$tiStmt = $pdo->prepare("
    SELECT ti.*, tt.name as tier_name, tt.price as tier_price, s.seat_number, s.row_name, s.col_number
    FROM ticket_items ti
    JOIN ticket_tiers tt ON ti.tier_id = tt.id
    LEFT JOIN seats s ON ti.seat_id = s.id
    WHERE ti.booking_id = ?
");
$tiStmt->execute([$booking['id']]);
$ticketItems = $tiStmt->fetchAll();

// Check if carbon offset was funded
$coStmt = $pdo->prepare("SELECT * FROM carbon_offsets WHERE booking_id = ?");
$coStmt->execute([$booking['id']]);
$carbonOffset = $coStmt->fetch();
?>

<div class="container py-5">
    <div class="text-center max-w-700 mx-auto mb-5">
        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3 shadow-lg" style="width: 72px; height: 72px;">
            <i class="bi bi-check-lg fs-1"></i>
        </div>
        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle">PAYMENT CONFIRMED</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($ticketItems) ?> PASSES GENERATED</span>
        </div>
        <h1 class="fw-extrabold display-6">You're Going to <?= e($booking['event_title']) ?>!</h1>
        <p class="text-body-secondary lead fs-6">
            Booking Reference: <code class="fw-bold fs-5 text-primary"><?= e($booking['booking_reference']) ?></code>
        </p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Ticket Items Cards (Pass Previews) -->
        <div class="col-lg-8">
            <h4 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-primary"></i> Your Digital Ticket Passes
            </h4>

            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($ticketItems as $idx => $t): ?>
                    <div class="holographic-ticket-pass p-4 text-white">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold mb-1"><?= e($t['tier_name']) ?></span>
                                <h4 class="fw-bold mb-0 text-white"><?= e($booking['event_title']) ?></h4>
                                <div class="small text-white-50"><i class="bi bi-geo-alt"></i> <?= e($booking['venue_name']) ?>, <?= e($booking['city']) ?></div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info text-dark fw-bold">SEAT <?= e($t['seat_number'] ?? 'General') ?></span>
                                <div class="fs-4 fw-bold mt-1 text-white"><?= format_currency($t['tier_price']) ?></div>
                            </div>
                        </div>

                        <div class="ticket-pass-perforated">
                            <div class="ticket-notch-left"></div>
                            <div class="ticket-notch-right"></div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-2">
                            <div>
                                <div class="fs-xxs text-uppercase text-white-50">Attendee</div>
                                <div class="fw-bold text-white"><?= e($t['attendee_name']) ?></div>
                                <div class="fs-xxs text-white-50"><?= e($t['attendee_email']) ?></div>
                            </div>

                            <?php if ($t['eco_badge_granted']): ?>
                                <div>
                                    <span class="eco-guardian-badge">
                                        <i class="bi bi-tree-fill"></i> Eco-Guardian
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <a href="<?= APP_URL ?>/ticket.php?hash=<?= urlencode($t['ticket_hash']) ?>" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                                    <i class="bi bi-qr-code-scan me-1"></i> Open Pass &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Eco-Guardian Certificate Panel if funded -->
            <?php if ($carbonOffset): ?>
                <div class="glass-card p-4 rounded-4 mb-4 border-success">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="rounded-circle bg-success text-white p-3">
                            <i class="bi bi-tree-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-success mb-0">Verified Carbon Offset Contribution</h5>
                            <div class="small text-body-secondary">Official Climate Impact Certificate</div>
                        </div>
                    </div>
                    <p class="small text-body-secondary mb-2">
                        Your booking funded <strong><?= $carbonOffset['trees_funded'] ?> native trees</strong> and offset an estimated <strong><?= number_format($carbonOffset['kg_co2_offset'], 1) ?> kg of carbon emissions</strong>.
                    </p>
                    <div class="small font-monospace text-muted">
                        Certificate Hash: <code><?= $carbonOffset['certificate_hash'] ?></code>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Receipt & Quick Action Links -->
        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold mb-3">Order Receipt</h5>

                <div class="d-flex flex-column gap-2 small pb-3 border-bottom border-light-subtle mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-body-secondary">Reference:</span>
                        <span class="fw-bold"><?= e($booking['booking_reference']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-body-secondary">Date:</span>
                        <span><?= format_date($booking['created_at']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-body-secondary">Payment Method:</span>
                        <span><?= e($booking['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-body-secondary">Tickets Subtotal:</span>
                        <span><?= format_currency($booking['total_amount']) ?></span>
                    </div>
                    <?php if ($booking['discount_amount'] > 0): ?>
                        <div class="d-flex justify-content-between text-info">
                            <span>Squad Discount:</span>
                            <span>-<?= format_currency($booking['discount_amount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($booking['carbon_offset_fee'] > 0): ?>
                        <div class="d-flex justify-content-between text-success">
                            <span>Carbon Offset:</span>
                            <span>+<?= format_currency($booking['carbon_offset_fee']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between pt-2 border-top border-light-subtle fw-bold fs-6">
                        <span>Grand Total Paid:</span>
                        <span class="text-primary"><?= format_currency($booking['final_amount']) ?></span>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="<?= APP_URL ?>/ai_planner.php" class="btn btn-outline-warning rounded-pill w-100">
                        <i class="bi bi-magic me-1"></i> Plan Itinerary with AI
                    </a>
                    <a href="<?= APP_URL ?>/my_tickets.php" class="btn btn-outline-primary rounded-pill w-100">
                        <i class="bi bi-wallet2 me-1"></i> View in Ticket Wallet
                    </a>
                    <a href="javascript:window.print();" class="btn btn-outline-secondary rounded-pill w-100">
                        <i class="bi bi-printer me-1"></i> Print / Save Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Fire celebratory confetti!
    if (typeof confetti === 'function') {
        confetti({
            particleCount: 80,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
