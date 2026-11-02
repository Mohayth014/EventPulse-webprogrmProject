<?php
/**
 * EventPulse - Homepage Landing
 */

$pageTitle = 'Next-Gen Event Registration & Ticketing';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Fetch featured events
$stmt = $pdo->query("
    SELECT e.*, c.name as category_name, c.icon as category_icon,
           MIN(tt.price) as min_price, MAX(tt.price) as max_price
    FROM events e
    JOIN categories c ON e.category_id = c.id
    LEFT JOIN ticket_tiers tt ON e.id = tt.event_id
    WHERE e.status = 'published'
    GROUP BY e.id
    ORDER BY e.is_featured DESC, e.start_date ASC
    LIMIT 6
");
$events = $stmt->fetchAll();

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();

// Live stats counts
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
?>

<!-- Hero Section -->
<section class="hero-gradient-overlay py-5 py-lg-6 border-bottom border-light-subtle position-relative">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary-subtle border border-primary-subtle text-primary small fw-semibold mb-3">
                    <i class="bi bi-patch-check-fill"></i> The Frontier Ticketing Experience
                </div>
                <h1 class="display-4 fw-extrabold lh-tight mb-3">
                    Experience Events in <span class="text-gradient">Dynamic Real-Time</span>.
                </h1>
                <p class="lead text-body-secondary mb-4">
                    Eliminate scalping with rotating cryptographic passes, preview your exact stage perspective on interactive venue maps, coordinate squad discounts, and generate AI-tailored schedules.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?= APP_URL ?>/events.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-compass"></i> Explore All Events
                    </a>
                    <a href="<?= APP_URL ?>/ai_planner.php" class="btn btn-outline-warning btn-lg rounded-pill px-4 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-magic"></i> Try AI Concierge
                    </a>
                </div>

                <!-- Stats Ticker -->
                <div class="row g-3 pt-3 border-top border-light-subtle">
                    <div class="col-4">
                        <h4 class="fw-bold mb-0 text-primary">100%</h4>
                        <div class="small text-body-secondary">Fraud-Proof Passes</div>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold mb-0 text-success">&lt;0.5s</h4>
                        <div class="small text-body-secondary">Gate Check-in Speed</div>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold mb-0 text-info">5 Novelties</h4>
                        <div class="small text-body-secondary">Next-Gen Features</div>
                    </div>
                </div>
            </div>

            <!-- Hero Interactive Preview Card -->
            <div class="col-lg-5">
                <div class="holographic-ticket-pass p-4 text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold mb-1">FEATURED PASS</span>
                            <h5 class="fw-bold mb-0">Global AI Summit 2026</h5>
                            <div class="small text-white-50">Silicon Horizon Mega Center</div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">VIP PLATINUM</span>
                            <div class="fs-4 fw-bold mt-1 text-info">$299</div>
                        </div>
                    </div>

                    <!-- Mini Seating Stage Preview -->
                    <div class="stage-view-canvas-wrapper my-3" style="height: 120px;">
                        <div class="stage-sim-curtain" style="font-size:0.7rem; height:32px;">MAIN STAGE 360°</div>
                        <div class="attendee-eye-pos text-white">
                            <i class="bi bi-geo-alt-fill text-warning"></i> Seat A08 &bull; 6m from Stage
                        </div>
                    </div>

                    <div class="ticket-pass-perforated">
                        <div class="ticket-notch-left"></div>
                        <div class="ticket-notch-right"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-1">
                        <div>
                            <div class="fs-xxs text-uppercase text-white-50">Dynamic Security Code</div>
                            <div class="totp-rotating-code fs-5">EP-9821-X</div>
                            <div class="fs-xxs text-success"><i class="bi bi-shield-lock-fill"></i> TOTP 30s Rotation Active</div>
                        </div>
                        <a href="<?= APP_URL ?>/event.php?id=1" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                            Pick Seat &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Trending Events Section -->
<section class="py-5">
    <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="text-primary fw-bold text-uppercase fs-xs letter-spacing-1">Upcoming Attractions</span>
                <h2 class="fw-bold mb-0">Featured Events & Experiences</h2>
            </div>
            <a href="<?= APP_URL ?>/events.php" class="btn btn-outline-primary rounded-pill px-3">
                View All Events <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($events as $ev): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card glass-card h-100 border-0 overflow-hidden hover-lift">
                        <div class="position-relative">
                            <img src="<?= e($ev['banner_image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= e($ev['title']) ?>">
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-dark bg-opacity-75 text-white backdrop-blur">
                                    <i class="bi <?= e($ev['category_icon']) ?> me-1"></i> <?= e($ev['category_name']) ?>
                                </span>
                            </div>
                            <div class="position-absolute bottom-0 end-0 m-3">
                                <span class="badge bg-primary fs-6 fw-bold shadow">
                                    From <?= format_currency($ev['min_price'] ?? 49.00) ?>
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <div class="text-muted small mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar3 text-primary"></i> <?= format_date($ev['start_date'], 'M d, Y') ?>
                                <span>&bull;</span>
                                <i class="bi bi-geo-alt text-danger"></i> <?= e($ev['city']) ?>
                            </div>

                            <h5 class="card-title fw-bold mb-2">
                                <a href="<?= APP_URL ?>/event.php?id=<?= $ev['id'] ?>" class="text-body text-decoration-none">
                                    <?= e($ev['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-body-secondary small flex-grow-1">
                                <?= e($ev['tagline']) ?>
                            </p>

                            <!-- Live countdown mini -->
                            <div class="my-3 py-2 px-3 rounded bg-body-secondary small text-center" data-countdown="<?= $ev['start_date'] ?>">
                                Loading countdown...
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top border-light-subtle">
                                <span class="small text-success fw-medium">
                                    <i class="bi bi-check-circle-fill me-1"></i> Interactive Seating
                                </span>
                                <a href="<?= APP_URL ?>/event.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                    Book Seats
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Organizer Callout -->
<section class="py-5 bg-body-tertiary border-top border-light-subtle">
    <div class="container py-3">
        <div class="glass-card p-4 p-lg-5 rounded-4 border-primary">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge bg-warning text-dark fw-bold mb-2">FOR EVENT CREATORS & VENUES</span>
                    <h3 class="fw-bold mb-2">Are You Hosting an Event, Summit or Concert?</h3>
                    <p class="text-body-secondary mb-0">
                        EventPulse gives organizers full control with zero technical overhead. Custom seat map designer, real-time ticket velocity monitors, CSV attendee export, and turn-key smartphone QR gate scanning.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= APP_URL ?>/admin/index.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow">
                        <i class="bi bi-speedometer2 me-2"></i> Open Organizer Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
