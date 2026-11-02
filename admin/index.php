<?php
/**
 * EventPulse - Organizer / Admin Live Dashboard
 */

$pageTitle = 'Organizer Analytics Dashboard';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = Database::getConnection();

// Metrics
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(final_amount), 0) FROM bookings WHERE payment_status = 'paid'")->fetchColumn();
$totalTickets = $pdo->query("SELECT COUNT(*) FROM ticket_items")->fetchColumn();
$totalCheckedIn = $pdo->query("SELECT COUNT(*) FROM ticket_items WHERE checkin_status = 'checked_in'")->fetchColumn();
$checkInRate = ($totalTickets > 0) ? round(($totalCheckedIn / $totalTickets) * 100, 1) : 0;
$totalTrees = $pdo->query("SELECT COALESCE(SUM(trees_funded), 0) FROM carbon_offsets")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();

// Recent bookings
$recentStmt = $pdo->query("
    SELECT b.*, e.title as event_title, u.name as booker_name,
           (SELECT COUNT(*) FROM ticket_items ti WHERE ti.booking_id = b.id) as ticket_count
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.id DESC
    LIMIT 6
");
$recentBookings = $recentStmt->fetchAll();

// Tiers breakdown
$tiersStmt = $pdo->query("
    SELECT tt.name, COUNT(ti.id) as tickets_sold, SUM(tt.price) as tier_revenue
    FROM ticket_items ti
    JOIN ticket_tiers tt ON ti.tier_id = tt.id
    GROUP BY tt.name
");
$tierData = $tiersStmt->fetchAll();
$tierLabels = array_column($tierData, 'name');
$tierCounts = array_column($tierData, 'tickets_sold');
$tierRevenues = array_column($tierData, 'tier_revenue');
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary">ORGANIZER SUITE</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-circle-fill fs-xxs"></i> Real-Time Telemetry</span>
                </div>
                <h2 class="fw-bold mb-0">Live Executive Telemetry</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= APP_URL ?>/admin/scanner.php" class="btn btn-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-qr-code-scan me-1"></i> Gate Scanner Station
                </a>
                <a href="<?= APP_URL ?>/admin/events.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Event
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Stat Cards Grid -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="glass-card p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-body-secondary small text-uppercase fw-bold letter-spacing-1">Gross Revenue</span>
                    <span class="badge bg-primary-subtle text-primary p-2"><i class="bi bi-currency-dollar fs-5"></i></span>
                </div>
                <h2 class="fw-extrabold mb-1 text-primary font-heading"><?= format_currency($totalRevenue) ?></h2>
                <div class="small text-success"><i class="bi bi-arrow-up-right me-1"></i> +18.4% this week</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="glass-card p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-body-secondary small text-uppercase fw-bold letter-spacing-1">Total Passes Issued</span>
                    <span class="badge bg-info-subtle text-info p-2"><i class="bi bi-ticket-perforated-fill fs-5"></i></span>
                </div>
                <h2 class="fw-extrabold mb-1 font-heading"><?= number_format($totalTickets) ?></h2>
                <div class="small text-muted">Across <?= $totalEvents ?> active summits</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="glass-card p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-body-secondary small text-uppercase fw-bold letter-spacing-1">Gate Check-in Rate</span>
                    <span class="badge bg-success-subtle text-success p-2"><i class="bi bi-check2-circle fs-5"></i></span>
                </div>
                <h2 class="fw-extrabold mb-1 text-success font-heading"><?= $checkInRate ?>%</h2>
                <div class="small text-muted"><?= $totalCheckedIn ?> verified attendees at venue</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="glass-card p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-body-secondary small text-uppercase fw-bold letter-spacing-1">Trees Funded (Eco)</span>
                    <span class="badge bg-emerald-subtle text-success p-2"><i class="bi bi-tree-fill fs-5"></i></span>
                </div>
                <h2 class="fw-extrabold mb-1 text-success font-heading"><?= number_format($totalTrees) ?></h2>
                <div class="small text-muted">Novelty 5: Carbon Neutral Pass</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="glass-card p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up text-primary me-2"></i> Ticket Sales Velocity (Demand Surge)</h5>
                    <span class="badge bg-primary-subtle text-primary">Hourly Velocity</span>
                </div>
                <canvas id="velocityChart" height="250"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart text-info me-2"></i> Tier Revenue Share</h5>
                    <span class="badge bg-info-subtle text-info">By Pass Type</span>
                </div>
                <canvas id="tierChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Bookings Manifest Table -->
    <div class="glass-card p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-0">Recent Order Stream</h5>
                <div class="small text-muted">Live incoming customer reservations</div>
            </div>
            <a href="<?= APP_URL ?>/admin/attendees.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                Full Attendee Manifest &rarr;
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Reference</th>
                        <th>Booker / Attendee</th>
                        <th>Event</th>
                        <th>Passes</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $rb): ?>
                        <tr>
                            <td class="font-monospace fw-bold text-primary"><?= e($rb['booking_reference']) ?></td>
                            <td>
                                <div class="fw-semibold"><?= e($rb['booker_name']) ?></div>
                                <div class="small text-muted"><?= format_date($rb['created_at']) ?></div>
                            </td>
                            <td class="fw-medium text-truncate" style="max-width: 220px;"><?= e($rb['event_title']) ?></td>
                            <td><span class="badge bg-body-secondary text-body"><?= $rb['ticket_count'] ?> Tickets</span></td>
                            <td class="fw-bold text-success"><?= format_currency($rb['final_amount']) ?></td>
                            <td><span class="badge bg-success-subtle text-success border border-success-subtle">PAID</span></td>
                            <td>
                                <a href="<?= APP_URL ?>/confirmation.php?ref=<?= urlencode($rb['booking_reference']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Velocity Chart (Chart.js)
    const ctxVelocity = document.getElementById('velocityChart');
    if (ctxVelocity) {
        new Chart(ctxVelocity, {
            type: 'line',
            data: {
                labels: ['10:00 AM', '11:00 AM', '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'],
                datasets: [{
                    label: 'Passes Issued',
                    data: [12, 19, 28, 45, 62, 58, 79, 94],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                    x: { grid: { color: 'rgba(255, 255, 255, 0.05)' } }
                }
            }
        });
    }

    // Tier Share Chart
    const ctxTier = document.getElementById('tierChart');
    if (ctxTier) {
        new Chart(ctxTier, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($tierLabels) ?>,
                datasets: [{
                    data: <?= json_encode($tierCounts) ?>,
                    backgroundColor: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
