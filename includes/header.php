<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';
$currentUser = current_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . APP_NAME : APP_NAME . ' - ' . APP_TAGLINE ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Application CSS -->
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-body text-body min-vh-100 d-flex flex-column">

    <!-- Top Announcement Bar (Dynamic Demand & Early Bird Alert) -->
    <div class="top-ticker-bar py-1 px-3 text-center small text-white">
        <span class="badge bg-danger pulse-dot me-2">LIVE</span>
        <span>🔥 <strong>Early-Bird Wave 2</strong> is live! Save up to 20% on all tech & music festivals this weekend.</span>
        <a href="<?= APP_URL ?>/events.php" class="text-white text-decoration-underline ms-2 fw-semibold">Explore Events &rarr;</a>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top glass-navbar border-bottom border-light-subtle">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= APP_URL ?>/index.php">
                <span class="brand-logo-icon">
                    <i class="bi bi-ticket-perforated-fill"></i>
                </span>
                <span class="brand-title">Event<span class="text-gradient">Pulse</span></span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fs-xs">v2.4 Pro</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'events.php') ? 'active text-primary fw-semibold' : '' ?>" href="<?= APP_URL ?>/events.php">
                            <i class="bi bi-compass me-1"></i> Browse Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'ai_planner.php') ? 'active text-primary fw-semibold' : '' ?>" href="<?= APP_URL ?>/ai_planner.php">
                            <i class="bi bi-magic text-warning me-1"></i> AI Concierge <span class="badge bg-warning text-dark fs-xxs ms-1">NOVELTY</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'squad.php') ? 'active text-primary fw-semibold' : '' ?>" href="<?= APP_URL ?>/squad.php">
                            <i class="bi bi-people-fill text-info me-1"></i> Squad RSVP <span class="badge bg-info text-dark fs-xxs ms-1">NOVELTY</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'scanner.php') ? 'active text-primary fw-semibold' : '' ?>" href="<?= APP_URL ?>/admin/scanner.php">
                            <i class="bi bi-qr-code-scan text-success me-1"></i> Gatekeeper Pass <span class="badge bg-success fs-xxs ms-1">NOVELTY</span>
                        </a>
                    </li>
                </ul>

                <!-- Right Side Actions & Auth -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Theme Mode Toggle -->
                    <button class="btn btn-sm btn-outline-secondary rounded-circle theme-toggle-btn" id="themeToggleBtn" title="Toggle Light/Dark Theme">
                        <i class="bi bi-sun-fill" id="themeIcon"></i>
                    </button>

                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?= e($currentUser['avatar']) ?>" class="rounded-circle" width="26" height="26" alt="Avatar">
                                <span class="fw-medium small"><?= e($currentUser['name']) ?></span>
                                <span class="badge bg-secondary-subtle text-secondary text-uppercase fs-xxs"><?= e($currentUser['role']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-light-subtle">
                                <li class="dropdown-header">
                                    <div class="fw-bold text-truncate"><?= e($currentUser['name']) ?></div>
                                    <div class="text-muted small text-truncate"><?= e($currentUser['email']) ?></div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/my_tickets.php">
                                        <i class="bi bi-wallet2 text-primary"></i> My Ticket Wallet
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/squad.php">
                                        <i class="bi bi-people text-info"></i> My Squads
                                    </a>
                                </li>
                                <?php if (is_organizer()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><span class="dropdown-header text-uppercase fs-xxs fw-bold text-primary">Organizer Suite</span></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/admin/index.php">
                                            <i class="bi bi-speedometer2 text-warning"></i> Live Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/admin/events.php">
                                            <i class="bi bi-calendar-event"></i> Manage Events
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/admin/attendees.php">
                                            <i class="bi bi-person-lines-fill"></i> Attendee Manifest
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/admin/scanner.php">
                                            <i class="bi bi-qr-code-scan text-success"></i> Gate Check-in Station
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?= APP_URL ?>/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Sign Out
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= APP_URL ?>/login.php" class="btn btn-sm btn-outline-light rounded-pill px-3">Sign In</a>
                            <a href="<?= APP_URL ?>/register.php" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">Get Tickets</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Notifications Container -->
    <?php if ($flash): ?>
        <div class="container my-3">
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm border-0" role="alert">
                <i class="bi <?= ($flash['type'] === 'success') ? 'bi-check-circle-fill' : (($flash['type'] === 'warning') ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill') ?> fs-5"></i>
                <div><?= $flash['message'] ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content wrapper starts -->
    <main class="flex-grow-1">
