<?php
/**
 * EventPulse - Events Catalog & Discovery Page
 */

$pageTitle = 'Explore Events';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Query Parameters
$search = trim($_GET['q'] ?? '');
$catSlug = trim($_GET['category'] ?? '');
$city = trim($_GET['city'] ?? '');
$sort = trim($_GET['sort'] ?? 'date_asc');

// Base query
$sql = "
    SELECT e.*, c.name as category_name, c.slug as category_slug, c.icon as category_icon,
           MIN(tt.price) as min_price, MAX(tt.price) as max_price,
           (SELECT COUNT(*) FROM seats s WHERE s.event_id = e.id AND s.status = 'available') as available_seats
    FROM events e
    JOIN categories c ON e.category_id = c.id
    LEFT JOIN ticket_tiers tt ON e.id = tt.event_id
    WHERE e.status = 'published'
";

$params = [];

if (!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR e.tagline LIKE ? OR e.description LIKE ? OR e.venue_name LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if (!empty($catSlug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $catSlug;
}

if (!empty($city)) {
    $sql .= " AND e.city LIKE ?";
    $params[] = '%' . $city . '%';
}

$sql .= " GROUP BY e.id";

// Sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY min_price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY min_price DESC";
        break;
    case 'title':
        $sql .= " ORDER BY e.title ASC";
        break;
    default:
        $sql .= " ORDER BY e.start_date ASC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

// Get all categories for filter buttons
$allCategories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Get unique cities
$cities = $pdo->query("SELECT DISTINCT city FROM events WHERE status = 'published' ORDER BY city ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <h2 class="fw-bold mb-1">Discover Events & Passes</h2>
        <p class="text-body-secondary mb-3">Browse premier conferences, electronic festivals, investor conclaves and masterclasses.</p>

        <!-- Search & Filter Form -->
        <form method="GET" action="events.php" class="row g-2 align-items-center">
            <div class="col-lg-5 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search by title, speaker, keyword..." value="<?= e($search) ?>">
                </div>
            </div>

            <div class="col-lg-3 col-md-3">
                <select name="city" class="form-select">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= e($c) ?>" <?= ($city === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-2 col-md-3">
                <select name="sort" class="form-select">
                    <option value="date_asc" <?= ($sort === 'date_asc') ? 'selected' : '' ?>>Date: Upcoming</option>
                    <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill">Filter</button>
                <?php if (!empty($search) || !empty($catSlug) || !empty($city)): ?>
                    <a href="events.php" class="btn btn-outline-secondary rounded-pill" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="d-flex flex-wrap gap-2 mt-3 pt-2">
            <a href="events.php<?= !empty($city) ? '?city=' . urlencode($city) : '' ?>" class="btn btn-sm <?= empty($catSlug) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">
                All Categories
            </a>
            <?php foreach ($allCategories as $cat): ?>
                <a href="events.php?category=<?= urlencode($cat['slug']) ?><?= !empty($city) ? '&city=' . urlencode($city) : '' ?>" class="btn btn-sm <?= ($catSlug === $cat['slug']) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill d-inline-flex align-items-center gap-1">
                    <i class="bi <?= e($cat['icon']) ?>"></i> <?= e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">Showing <?= count($events) ?> Events Found</h5>
        <span class="small text-muted"><i class="bi bi-shield-check text-success"></i> Instant Interactive Booking Enabled</span>
    </div>

    <?php if (empty($events)): ?>
        <div class="text-center py-5 glass-card rounded-4 p-5">
            <i class="bi bi-calendar-x display-1 text-muted mb-3 d-block"></i>
            <h4 class="fw-bold">No Events Match Your Search</h4>
            <p class="text-body-secondary">Try adjusting your keyword, city filter, or browse all categories.</p>
            <a href="events.php" class="btn btn-primary rounded-pill px-4 mt-2">Reset All Filters</a>
        </div>
    <?php else: ?>
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
                                    From <?= format_currency($ev['min_price'] ?? 59.00) ?>
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

                            <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded bg-body-secondary small mb-3">
                                <span><i class="bi bi-geo-alt-fill text-muted me-1"></i> <?= e($ev['venue_name']) ?></span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <?= $ev['available_seats'] ?> Seats Left
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top border-light-subtle">
                                <a href="<?= APP_URL ?>/event.php?id=<?= $ev['id'] ?>#seatingSection" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye"></i> View Seating
                                </a>
                                <a href="<?= APP_URL ?>/event.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                    Get Tickets
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
