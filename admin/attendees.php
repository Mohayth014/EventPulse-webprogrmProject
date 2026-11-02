<?php
/**
 * EventPulse - Attendee Manifest & Check-in Control
 */

$pageTitle = 'Attendee Manifest & Roster';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$pdo = Database::getConnection();

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=EventPulse_Attendee_Manifest_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ticket Hash', 'Booking Ref', 'Event Title', 'Attendee Name', 'Attendee Email', 'Tier', 'Seat', 'Check-in Status', 'Check-in Time', 'Eco Badge']);

    $rows = $pdo->query("
        SELECT ti.ticket_hash, b.booking_reference, e.title as event_title,
               ti.attendee_name, ti.attendee_email, tt.name as tier_name,
               COALESCE(s.seat_number, 'GA') as seat_num,
               ti.checkin_status, ti.checkin_time, ti.eco_badge_granted
        FROM ticket_items ti
        JOIN bookings b ON ti.booking_id = b.id
        JOIN events e ON b.event_id = e.id
        JOIN ticket_tiers tt ON ti.tier_id = tt.id
        LEFT JOIN seats s ON ti.seat_id = s.id
        ORDER BY ti.id DESC
    ")->fetchAll();

    foreach ($rows as $r) {
        fputcsv($output, [
            $r['ticket_hash'], $r['booking_reference'], $r['event_title'],
            $r['attendee_name'], $r['attendee_email'], $r['tier_name'],
            $r['seat_num'], $r['checkin_status'], $r['checkin_time'],
            $r['eco_badge_granted'] ? 'YES' : 'NO'
        ]);
    }
    fclose($output);
    exit;
}

// Handle Manual Check-in Toggle via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_checkin'])) {
    $itemId = intval($_POST['ticket_item_id'] ?? 0);
    $currentStatus = $_POST['current_status'] ?? 'pending';
    $newStatus = ($currentStatus === 'checked_in') ? 'pending' : 'checked_in';
    $newTime = ($newStatus === 'checked_in') ? date('Y-m-d H:i:s') : null;

    $uStmt = $pdo->prepare("UPDATE ticket_items SET checkin_status = ?, checkin_time = ? WHERE id = ?");
    $uStmt->execute([$newStatus, $newTime, $itemId]);

    set_flash('success', 'Passholder attendance status updated successfully.');
    header('Location: ' . APP_URL . '/admin/attendees.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

// Filter parameters
$search = trim($_GET['q'] ?? '');
$filterEvent = intval($_GET['event_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? '');

$sql = "
    SELECT ti.*, b.booking_reference, b.created_at as booked_at,
           e.title as event_title, tt.name as tier_name,
           s.seat_number
    FROM ticket_items ti
    JOIN bookings b ON ti.booking_id = b.id
    JOIN events e ON b.event_id = e.id
    JOIN ticket_tiers tt ON ti.tier_id = tt.id
    LEFT JOIN seats s ON ti.seat_id = s.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (ti.attendee_name LIKE ? OR ti.attendee_email LIKE ? OR b.booking_reference LIKE ? OR s.seat_number LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}
if ($filterEvent > 0) {
    $sql .= " AND e.id = ?";
    $params[] = $filterEvent;
}
if (!empty($filterStatus)) {
    $sql .= " AND ti.checkin_status = ?";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY ti.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendees = $stmt->fetchAll();

$allEvents = $pdo->query("SELECT id, title FROM events ORDER BY title ASC")->fetchAll();
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-person-lines-fill text-primary me-2"></i> Attendee Manifest & Roster</h2>
                <p class="text-body-secondary mb-0">Search registered passholders, manage manual check-in status, and export manifests.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="attendees.php?export=csv" class="btn btn-outline-success rounded-pill px-3">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
                </a>
                <a href="<?= APP_URL ?>/admin/scanner.php" class="btn btn-success rounded-pill px-3">
                    <i class="bi bi-qr-code-scan me-1"></i> Live Gate Scanner
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Filter Controls -->
    <div class="glass-card p-4 rounded-4 mb-4">
        <form method="GET" action="attendees.php" class="row g-3 align-items-center">
            <div class="col-lg-5 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-body"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by name, email, booking ref, seat..." value="<?= e($search) ?>">
                </div>
            </div>

            <div class="col-lg-3 col-md-3">
                <select name="event_id" class="form-select">
                    <option value="">All Events</option>
                    <?php foreach ($allEvents as $ev): ?>
                        <option value="<?= $ev['id'] ?>" <?= ($filterEvent == $ev['id']) ? 'selected' : '' ?>><?= e($ev['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : '' ?>>Pending Entry</option>
                    <option value="checked_in" <?= ($filterStatus === 'checked_in') ? 'selected' : '' ?>>Checked-In</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter</button>
                <?php if (!empty($search) || $filterEvent > 0 || !empty($filterStatus)): ?>
                    <a href="attendees.php" class="btn btn-outline-secondary rounded-pill" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Attendees Table -->
    <div class="glass-card p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Registered Passes (<?= count($attendees) ?> Found)</h5>
            <span class="small text-muted"><i class="bi bi-shield-check text-success"></i> Instant Verification</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Attendee</th>
                        <th>Event & Tier</th>
                        <th>Seat</th>
                        <th>Booking Ref</th>
                        <th>Gate Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendees)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No attendees match your filter criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendees as $att): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= e($att['attendee_name']) ?></div>
                                    <div class="small text-muted"><?= e($att['attendee_email']) ?></div>
                                    <?php if ($att['eco_badge_granted']): ?>
                                        <span class="badge bg-success-subtle text-success fs-xxs"><i class="bi bi-tree-fill"></i> Eco-Guardian</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 220px;"><?= e($att['event_title']) ?></div>
                                    <span class="badge bg-primary-subtle text-primary fs-xxs"><?= e($att['tier_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark fw-bold"><?= e($att['seat_number'] ?? 'General') ?></span>
                                </td>
                                <td>
                                    <code class="small text-info fw-bold"><?= e($att['booking_reference']) ?></code>
                                </td>
                                <td>
                                    <?php if ($att['checkin_status'] === 'checked_in'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> CHECKED-IN</span>
                                        <div class="fs-xxs text-muted mt-1"><?= $att['checkin_time'] ?></div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">PENDING ENTRY</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="attendees.php" class="d-inline">
                                            <input type="hidden" name="toggle_checkin" value="1">
                                            <input type="hidden" name="ticket_item_id" value="<?= $att['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $att['checkin_status'] ?>">
                                            <?php if ($att['checkin_status'] === 'checked_in'): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill" title="Reset Check-in">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Manually Check In">
                                                    <i class="bi bi-check-lg"></i> Check In
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <a href="<?= APP_URL ?>/ticket.php?hash=<?= urlencode($att['ticket_hash']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="bi bi-qr-code"></i> Pass
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
