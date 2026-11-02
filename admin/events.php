<?php
/**
 * EventPulse - Event Builder & CRUD Management
 */

$pageTitle = 'Manage Events';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = Database::getConnection();

// Handle New Event Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_event') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 1);
    $tagline = trim($_POST['tagline'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $venueName = trim($_POST['venue_name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $startDate = trim($_POST['start_date'] ?? date('Y-m-d H:i:s'));
    $bannerImage = trim($_POST['banner_image'] ?? 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title)) . '-' . mt_rand(100, 999);

    $eStmt = $pdo->prepare("
        INSERT INTO events (organizer_id, category_id, title, slug, tagline, description, venue_name, venue_address, city, start_date, end_date, banner_image, seating_type, total_seats, is_featured, status)
        VALUES (2, ?, ?, ?, ?, ?, ?, 'Convention Center Plaza', ?, ?, DATE_ADD(?, INTERVAL 2 DAY), ?, 'seated', 96, 1, 'published')
    ");
    $eStmt->execute([$categoryId, $title, $slug, $tagline, $description, $venueName, $city, $startDate, $startDate, $bannerImage]);
    $newEventId = $pdo->lastInsertId();

    // Generate Standard Tiers & Seats
    $tStmt = $pdo->prepare("INSERT INTO ticket_tiers (event_id, name, description, price, base_price, capacity, perks_json, color_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $tStmt->execute([$newEventId, 'VIP Sovereign Pass', 'Front row seating & VIP lounge', 299.00, 299.00, 24, json_encode(['VIP Lounge', 'Speaker Mixer']), '#8b5cf6']);
    $vipTierId = $pdo->lastInsertId();

    $tStmt->execute([$newEventId, 'Premium Pass', 'Center auditorium seating', 149.00, 149.00, 36, json_encode(['Center Rows', 'Fast Entry']), '#3b82f6']);
    $premTierId = $pdo->lastInsertId();

    $tStmt->execute([$newEventId, 'Standard Access', 'Main stage entry pass', 79.00, 79.00, 36, json_encode(['Standard Rows', 'Certificate']), '#10b981']);
    $stdTierId = $pdo->lastInsertId();

    // Create 96 seats
    $sStmt = $pdo->prepare("INSERT INTO seats (event_id, tier_id, seat_number, row_name, col_number, status) VALUES (?, ?, ?, ?, ?, 'available')");
    $rows = ['A', 'B', 'C', 'D', 'E', 'F'];
    foreach ($rows as $rIdx => $rName) {
        $assignedTier = ($rIdx < 2) ? $vipTierId : (($rIdx < 4) ? $premTierId : $stdTierId);
        for ($col = 1; $col <= 16; $col++) {
            $seatNumber = sprintf('%s%02d', $rName, $col);
            $sStmt->execute([$newEventId, $assignedTier, $seatNumber, $rName, $col]);
        }
    }

    set_flash('success', 'Event "' . htmlspecialchars($title) . '" and 96 interactive seats generated successfully!');
    header('Location: ' . APP_URL . '/admin/events.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

// Fetch all events
$events = $pdo->query("
    SELECT e.*, c.name as category_name,
           (SELECT COUNT(*) FROM seats s WHERE s.event_id = e.id) as seat_count,
           (SELECT COUNT(*) FROM seats s WHERE s.event_id = e.id AND s.status = 'booked') as booked_seat_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    ORDER BY e.start_date ASC
")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-calendar-event text-primary me-2"></i> Event Management</h2>
                <p class="text-body-secondary mb-0">Publish new summits, manage interactive venue layouts, and view seat occupancy.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createEventModal">
                <i class="bi bi-plus-lg me-1"></i> Launch New Event
            </button>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="glass-card p-4 rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Event & Category</th>
                        <th>Dates & Venue</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $ev): ?>
                        <?php
                        $occupancyRate = ($ev['seat_count'] > 0) ? round(($ev['booked_seat_count'] / $ev['seat_count']) * 100) : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e($ev['banner_image']) ?>" class="rounded-3 object-fit-cover shadow-sm" width="55" height="40" alt="">
                                    <div>
                                        <div class="fw-bold"><?= e($ev['title']) ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary fs-xxs"><?= e($ev['category_name']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= format_date($ev['start_date'], 'M d, Y') ?></div>
                                <div class="small text-muted"><?= e($ev['venue_name']) ?>, <?= e($ev['city']) ?></div>
                            </td>
                            <td>
                                <div class="small fw-bold mb-1"><?= $ev['booked_seat_count'] ?> / <?= $ev['seat_count'] ?> Seats Sold (<?= $occupancyRate ?>%)</div>
                                <div class="progress" style="height: 6px; width: 140px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $occupancyRate ?>%;"></div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">PUBLISHED</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?= APP_URL ?>/event.php?id=<?= $ev['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?= APP_URL ?>/event.php?id=<?= $ev['id'] ?>#seatingSection" target="_blank" class="btn btn-sm btn-outline-warning rounded-pill">
                                        <i class="bi bi-grid-3x3-gap"></i> Seating Map
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card border-primary">
            <div class="modal-header border-bottom border-light-subtle">
                <h5 class="modal-title fw-bold" id="createEventModalLabel"><i class="bi bi-plus-circle text-primary me-2"></i> Launch New Event with Auto-Seating</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="events.php">
                <input type="hidden" name="action" value="create_event">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Event Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Quantum World Summit 2026" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category_id" class="form-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catchy Tagline</label>
                        <input type="text" name="tagline" class="form-control" placeholder="A one-line preview of the event's focal point" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detailed Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Comprehensive event overview, keynotes, schedule, and attendee profile..." required></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Venue Name</label>
                            <input type="text" name="venue_name" class="form-control" placeholder="e.g. Metro Arts Pavilion" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">City & State</label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Boston, MA" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Start Date & Time</label>
                            <input type="datetime-local" name="start_date" class="form-control" value="2026-11-20T09:00" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">Banner Image URL</label>
                        <input type="url" name="banner_image" class="form-control" value="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80" required>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 small mt-3 mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> Creating this event will automatically synthesize 96 interactive venue seats across VIP, Premium, and Standard tiers.
                    </div>
                </div>
                <div class="modal-footer border-top border-light-subtle">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Publish Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
