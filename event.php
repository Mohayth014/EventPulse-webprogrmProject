<?php
/**
 * EventPulse - Single Event Details & Interactive Seating (Novelty 1)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

$eventId = intval($_GET['id'] ?? 1);
$pdo = Database::getConnection();

// Fetch Event Details
$stmt = $pdo->prepare("
    SELECT e.*, c.name as category_name, c.icon as category_icon, u.name as organizer_name, u.organization
    FROM events e
    JOIN categories c ON e.category_id = c.id
    JOIN users u ON e.organizer_id = u.id
    WHERE e.id = ? AND e.status = 'published'
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: ' . APP_URL . '/events.php');
    exit;
}

$pageTitle = $event['title'];
require_once __DIR__ . '/includes/header.php';

// Fetch Ticket Tiers
$tStmt = $pdo->prepare("SELECT * FROM ticket_tiers WHERE event_id = ? ORDER BY price DESC");
$tStmt->execute([$eventId]);
$tiers = $tStmt->fetchAll();

// Fetch Speakers
$spStmt = $pdo->prepare("SELECT * FROM event_speakers WHERE event_id = ?");
$spStmt->execute([$eventId]);
$speakers = $spStmt->fetchAll();

// Fetch Schedule
$scStmt = $pdo->prepare("SELECT * FROM event_schedule WHERE event_id = ? ORDER BY id ASC");
$scStmt->execute([$eventId]);
$schedule = $scStmt->fetchAll();

// Fetch All Seats
$seatStmt = $pdo->prepare("
    SELECT s.*, tt.name as tier_name, tt.price as tier_price, tt.color_code
    FROM seats s
    JOIN ticket_tiers tt ON s.tier_id = tt.id
    WHERE s.event_id = ?
    ORDER BY s.row_name ASC, s.col_number ASC
");
$seatStmt->execute([$eventId]);
$allSeats = $seatStmt->fetchAll();

// Group seats by row
$seatsByRow = [];
foreach ($allSeats as $seat) {
    $seatsByRow[$seat['row_name']][] = $seat;
}
?>

<!-- Event Hero Banner -->
<div class="position-relative py-5 border-bottom border-light-subtle bg-dark text-white overflow-hidden">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: url('<?= e($event['banner_image']) ?>') center/cover no-repeat; filter: blur(28px) brightness(0.25); transform: scale(1.1);"></div>

    <div class="container position-relative py-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-25 border border-primary-subtle text-white small mb-3">
                    <i class="bi <?= e($event['category_icon']) ?>"></i> <?= e($event['category_name']) ?>
                </div>
                <h1 class="display-5 fw-extrabold mb-2"><?= e($event['title']) ?></h1>
                <p class="lead text-light text-opacity-75 mb-4"><?= e($event['tagline']) ?></p>

                <div class="d-flex flex-wrap gap-4 text-light text-opacity-90 small">
                    <div>
                        <i class="bi bi-calendar-event text-primary me-1 fs-6"></i>
                        <strong>Start:</strong> <?= format_date($event['start_date']) ?>
                    </div>
                    <div>
                        <i class="bi bi-geo-alt-fill text-danger me-1 fs-6"></i>
                        <strong>Venue:</strong> <?= e($event['venue_name']) ?>, <?= e($event['city']) ?>
                    </div>
                    <div>
                        <i class="bi bi-person-badge text-info me-1 fs-6"></i>
                        <strong>Organizer:</strong> <?= e($event['organizer_name']) ?>
                    </div>
                </div>
            </div>

            <!-- Countdown Box -->
            <div class="col-lg-4 text-lg-end">
                <div class="glass-card p-4 text-center rounded-4 border-light-subtle d-inline-block text-white">
                    <span class="fs-xxs text-uppercase text-light text-opacity-75 letter-spacing-1 d-block mb-2">DOORS OPEN IN</span>
                    <div class="mb-3" data-countdown="<?= $event['start_date'] ?>">
                        Loading timer...
                    </div>
                    <a href="#seatingSection" class="btn btn-warning rounded-pill px-4 fw-bold w-100 shadow">
                        <i class="bi bi-ticket-perforated-fill me-1"></i> Choose Seats & Book
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Main Event Information -->
        <div class="col-lg-8">
            <!-- Event Description -->
            <div class="mb-5">
                <h3 class="fw-bold mb-3">About This Event</h3>
                <div class="text-body-secondary lead fs-6 lh-base">
                    <?= nl2br(e($event['description'])) ?>
                </div>
            </div>

            <!-- Key Speakers Section -->
            <?php if (!empty($speakers)): ?>
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0">Distinguished Speakers & Performers</h4>
                        <span class="badge bg-primary-subtle text-primary"><?= count($speakers) ?> Featured</span>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($speakers as $sp): ?>
                            <div class="col-md-6">
                                <div class="glass-card p-3 h-100 d-flex align-items-center gap-3">
                                    <img src="<?= e($sp['avatar']) ?>" class="rounded-circle object-fit-cover shadow-sm" width="60" height="60" alt="<?= e($sp['name']) ?>">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?= e($sp['name']) ?></h6>
                                        <div class="small text-primary fw-medium"><?= e($sp['role']) ?> &bull; <?= e($sp['company']) ?></div>
                                        <p class="small text-body-secondary mb-0 mt-1"><?= e($sp['bio']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Schedule Timeline -->
            <?php if (!empty($schedule)): ?>
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold mb-0">Official Schedule & Tracks</h4>
                        <a href="<?= APP_URL ?>/ai_planner.php" class="small text-warning fw-semibold text-decoration-none">
                            <i class="bi bi-magic me-1"></i> Tailor via AI Concierge &rarr;
                        </a>
                    </div>

                    <div class="glass-card p-3 rounded-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small text-uppercase">
                                        <th>Time</th>
                                        <th>Session & Topic</th>
                                        <th>Speaker</th>
                                        <th>Stage / Room</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedule as $sc): ?>
                                        <tr>
                                            <td class="fw-bold text-nowrap small text-primary"><?= e($sc['start_time']) ?> - <?= e($sc['end_time']) ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= e($sc['title']) ?></div>
                                                <div class="text-muted small"><?= e($sc['description']) ?></div>
                                            </td>
                                            <td class="small fw-medium"><?= e($sc['speaker'] ?? 'General Session') ?></td>
                                            <td><span class="badge bg-body-secondary text-body small"><?= e($sc['room']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- NOVELTY 1: Interactive Venue Seating Map & Stage Perspective -->
            <div id="seatingSection" class="mt-5 pt-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold mb-1">NOVELTY 1</span>
                        <h3 class="fw-bold mb-0">Interactive 3D Stage & Venue Seating Map</h3>
                    </div>
                    <div class="d-flex gap-3 small mt-2 mt-md-0">
                        <div class="d-flex align-items-center gap-1"><span class="badge" style="background:#8b5cf6;">&nbsp;</span> VIP ($299)</div>
                        <div class="d-flex align-items-center gap-1"><span class="badge" style="background:#3b82f6;">&nbsp;</span> Premium ($149)</div>
                        <div class="d-flex align-items-center gap-1"><span class="badge" style="background:#10b981;">&nbsp;</span> Standard ($79)</div>
                        <div class="d-flex align-items-center gap-1"><span class="badge bg-secondary">&nbsp;</span> Booked</div>
                        <div class="d-flex align-items-center gap-1"><span class="badge bg-warning text-dark">&nbsp;</span> Selected</div>
                    </div>
                </div>

                <div class="seating-venue-container mb-4">
                    <!-- Stage Platform -->
                    <div class="stage-platform">
                        <div class="fw-bold text-white small letter-spacing-1">STAGE / PODIUM PLATFORM</div>
                        <div class="fs-xxs text-light text-opacity-75">360° Laser & Audio Focal Point</div>
                    </div>

                    <!-- Seats Grid -->
                    <div class="seats-grid">
                        <?php foreach ($seatsByRow as $rowLetter => $rowSeats): ?>
                            <div class="seat-row" data-row-name="<?= $rowLetter ?>">
                                <span class="row-label"><?= $rowLetter ?></span>
                                <?php foreach ($rowSeats as $st): ?>
                                    <?php
                                    $tierClass = ($st['tier_price'] >= 250) ? 'vip' : (($st['tier_price'] >= 120) ? 'premium' : 'standard');
                                    $bookedClass = ($st['status'] === 'booked') ? 'booked' : '';
                                    ?>
                                    <div class="seat-unit <?= $tierClass ?> <?= $bookedClass ?>"
                                         data-seat-id="<?= $st['id'] ?>"
                                         data-seat-number="<?= $st['seat_number'] ?>"
                                         data-seat-price="<?= $st['tier_price'] ?>"
                                         data-tier-name="<?= e($st['tier_name']) ?>"
                                         data-row="<?= $st['row_name'] ?>"
                                         data-col="<?= $st['col_number'] ?>"
                                         title="Seat <?= $st['seat_number'] ?> - <?= e($st['tier_name']) ?> (<?= format_currency($st['tier_price']) ?>)">
                                        <?= $st['col_number'] ?>
                                    </div>
                                <?php endforeach; ?>
                                <span class="row-label"><?= $rowLetter ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Stage Perspective Simulation View (Novelty 1 Feature) -->
                <div class="stage-perspective-box mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-camera-reels text-warning"></i>
                            <span class="fw-bold small text-white text-uppercase letter-spacing-1">View From Selected Seat (Real-Time Perspective)</span>
                        </div>
                        <div class="small text-info" id="previewDistance">13 meters</div>
                    </div>

                    <div class="stage-view-canvas-wrapper">
                        <canvas id="stageCanvas" width="600" height="180" style="width:100%; height:100%;"></canvas>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-dark-subtle small text-muted">
                        <div><i class="bi bi-compass text-primary me-1"></i> Angle: <strong id="previewAngle" class="text-white">0° Direct Center</strong></div>
                        <div><i class="bi bi-soundwave text-success me-1"></i> Acoustic Rating: <strong id="previewAcoustic" class="text-white">96% Clarity</strong></div>
                        <div class="text-white-50"><i class="bi bi-info-circle me-1"></i> Interactive preview re-renders as you click seats</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Checkout Panel -->
        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-4 sticky-top" style="top: 90px;">
                <h4 class="fw-bold mb-3">Booking Summary</h4>

                <!-- Tiers summary list -->
                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-bold mb-2">Available Pass Tiers</label>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($tiers as $tr): ?>
                            <?php $perks = json_decode($tr['perks_json'] ?? '[]', true); ?>
                            <div class="p-3 rounded-3 border border-light-subtle bg-body-tertiary">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-body"><?= e($tr['name']) ?></span>
                                    <span class="fw-bold fs-5 text-primary"><?= format_currency($tr['price']) ?></span>
                                </div>
                                <div class="small text-muted mb-2"><?= e($tr['description']) ?></div>
                                <ul class="list-unstyled mb-0 small text-body-secondary d-flex flex-column gap-1">
                                    <?php foreach ($perks as $pk): ?>
                                        <li><i class="bi bi-check2 text-success me-1"></i> <?= e($pk) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Selected Seats Container -->
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold mb-1">Your Selected Seats</label>
                    <div id="selectedSeatsDisplay" class="p-3 rounded-3 bg-body-secondary small">
                        <span class="text-muted">No seats selected yet. Click any available seat on the venue map.</span>
                    </div>
                </div>

                <!-- Carbon Offset Notice -->
                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle text-success small mb-3">
                    <i class="bi bi-tree-fill me-1"></i> <strong>Eco-Offset Available:</strong> Add tree planting certification at checkout to earn the digital Eco-Guardian passholder badge!
                </div>

                <!-- Cart Total -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light-subtle mb-4">
                    <span class="text-body-secondary">Subtotal:</span>
                    <span class="fs-3 fw-bold text-primary" id="selectedTotalDisplay">$0.00</span>
                </div>

                <!-- Checkout CTA Button -->
                <a href="#" id="proceedToCheckoutBtn" class="btn btn-primary btn-lg rounded-pill w-100 disabled shadow">
                    Lock Seats & Checkout <i class="bi bi-arrow-right ms-1"></i>
                </a>

                <div class="text-center mt-3">
                    <span class="fs-xxs text-muted">
                        <i class="bi bi-lock-fill me-1"></i> 256-Bit SSL Encrypted Checkout &bull; 5-Minute Cart Lock
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/seating.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    new VenueSeatingManager({
        containerSelector: '.seats-grid',
        eventId: <?= $eventId ?>,
        maxSeats: 6
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
