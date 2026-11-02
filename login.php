<?php
/**
 * EventPulse - User Login Page
 */

$pageTitle = 'Sign In';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (Auth::attempt($email, $password)) {
        set_flash('success', 'Welcome back, ' . htmlspecialchars(current_user()['name']) . '!');
        if (is_organizer()) {
            header('Location: ' . APP_URL . '/admin/index.php');
        } else {
            header('Location: ' . APP_URL . '/my_tickets.php');
        }
        exit;
    } else {
        $error = 'Invalid email address or password. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="glass-card p-4 p-md-5 rounded-4 border-light-subtle shadow-lg">
                <div class="text-center mb-4">
                    <span class="brand-logo-icon mb-2">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </span>
                    <h3 class="fw-bold mb-1">Sign in to EventPulse</h3>
                    <p class="text-body-secondary small">Access your tickets, squads, and organizer dashboard</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small border-0"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" id="loginEmail" class="form-control" placeholder="name@domain.com" required>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small fw-bold">Password</label>
                            <a href="#" class="small text-muted text-decoration-none" onclick="alert('Demo mode: Use the 1-click test buttons below.'); return false;">Forgot?</a>
                        </div>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm mb-3">
                        Sign In &rarr;
                    </button>
                </form>

                <!-- Demo Credentials Quick-Fill Buttons -->
                <div class="pt-3 border-top border-light-subtle">
                    <label class="fs-xxs text-uppercase text-muted fw-bold d-block mb-2 text-center letter-spacing-1">1-Click Demo Accounts</label>
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning w-100 rounded-pill text-start d-flex justify-content-between align-items-center" onclick="fillCreds('admin@eventpulse.io', 'admin123')">
                            <span><i class="bi bi-shield-lock-fill me-1"></i> Administrator</span>
                            <span class="fs-xxs text-muted">admin@eventpulse.io</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info w-100 rounded-pill text-start d-flex justify-content-between align-items-center" onclick="fillCreds('organizer@eventpulse.io', 'organizer123')">
                            <span><i class="bi bi-calendar-event me-1"></i> Event Organizer</span>
                            <span class="fs-xxs text-muted">organizer@eventpulse.io</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success w-100 rounded-pill text-start d-flex justify-content-between align-items-center" onclick="fillCreds('attendee@eventpulse.io', 'attendee123')">
                            <span><i class="bi bi-person-badge-fill me-1"></i> Passholder Attendee</span>
                            <span class="fs-xxs text-muted">attendee@eventpulse.io</span>
                        </button>
                    </div>
                </div>

                <div class="text-center mt-4 small text-body-secondary">
                    Don't have an account yet? <a href="<?= APP_URL ?>/register.php" class="text-primary fw-semibold">Sign Up</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(email, pwd) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pwd;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
