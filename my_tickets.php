<?php
/**
 * EventPulse - User's Ticket Wallet & Pass Manifest
 */

$pageTitle = 'My Ticket Wallet';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Default to user 3 (Sarah Connor) if not signed in for immediate preview, or logged in user
$userId = is_logged_in() ? current_user()['id'] : 3;

$stmt = $pdo->prepare("
    SELECT ti.*, tt.name as tier_name, tt.price as tier_price,
           s.seat_number,
           b.booking_reference, b.created_at as booked_at, b.final_amount,
           e.id as event_id, e.title as event_title, e.start_date, e.end_date, e.venue_name, e.city, e.banner_image
    FROM ticket_items ti
    JOIN bookings b ON ti.booking_id = b.id
    JOIN events e ON b.event_id = e.id
    JOIN ticket_tiers tt ON ti.tier_id = tt.id
    LEFT JOIN seats s ON ti.seat_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$userId]);
$myTickets = $stmt->fetchAll();
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-wallet2 text-primary me-2"></i> My Digital Ticket Wallet</h2>
                <p class="text-body-secondary mb-0">Manage active event passes, anti-scalp dynamic QR codes, and booking receipts.</p>
            </div>
            <a href="<?= APP_URL ?>/events.php" class="btn btn-primary rounded-pill px-4 btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Browse More Events
            </a>
        </div>
    </div>
</div>

<div class="container py-5">
    <?php if (empty($myTickets)): ?>
        <div class="text-center py-5 glass-card rounded-4 p-5">
            <i class="bi bi-ticket-detailed display-1 text-muted mb-3 d-block"></i>
            <h4 class="fw-bold">Your Wallet is Currently Empty</h4>
            <p class="text-body-secondary">You haven't reserved any tickets yet. Explore trending events and pick your seats!</p>
            <a href="<?= APP_URL ?>/events.php" class="btn btn-primary rounded-pill px-4 mt-2">Explore Events</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($myTickets as $t): ?>
                <div class="col-lg-6">
                    <div class="holographic-ticket-pass p-4 text-white h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-warning text-dark fw-bold mb-1"><?= e($t['tier_name']) ?></span>
                                    <h4 class="fw-bold mb-0 text-white"><?= e($t['event_title']) ?></h4>
                                    <div class="small text-white-50"><i class="bi bi-geo-alt"></i> <?= e($t['venue_name']) ?>, <?= e($t['city']) ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-xxs text-uppercase text-white-50">Seat</div>
                                    <span class="badge bg-info text-dark fw-bold fs-6">
                                        <?= e($t['seat_number'] ?? 'General') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row g-2 py-2 border-top border-bottom border-white border-opacity-10 my-3 small">
                                <div class="col-6">
                                    <div class="text-white-50 fs-xxs text-uppercase">Attendee</div>
                                    <div class="fw-bold text-truncate"><?= e($t['attendee_name']) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-white-50 fs-xxs text-uppercase">Event Date</div>
                                    <div class="fw-bold"><?= format_date($t['start_date'], 'M d, Y') ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-white-50 fs-xxs text-uppercase">Booking Reference</div>
                                    <div class="font-monospace fw-bold text-info"><?= e($t['booking_reference']) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-white-50 fs-xxs text-uppercase">Gate Status</div>
                                    <?php if ($t['checkin_status'] === 'checked_in'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-check-circle"></i> USED</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="bi bi-shield-check"></i> VALID PASS</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="ticket-pass-perforated">
                            <div class="ticket-notch-left"></div>
                            <div class="ticket-notch-right"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <?php if ($t['eco_badge_granted']): ?>
                                <span class="eco-guardian-badge">
                                    <i class="bi bi-tree-fill"></i> Eco-Guardian
                                </span>
                            <?php else: ?>
                                <span class="fs-xxs text-white-50"><i class="bi bi-shield-lock-fill text-success"></i> 30s Dynamic TOTP</span>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <a href="<?= APP_URL ?>/ticket.php?hash=<?= urlencode($t['ticket_hash']) ?>" target="_blank" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                                    <i class="bi bi-qr-code-scan me-1"></i> Open Live Pass &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
