<?php
/**
 * EventPulse - User Registration Page
 */

$pageTitle = 'Create an Account';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'attendee');
    $organization = trim($_POST['organization'] ?? '');

    $result = Auth::register($name, $email, $password, $role, null, $organization);

    if ($result['success']) {
        set_flash('success', 'Your account has been registered successfully! Welcome to EventPulse.');
        if ($role === 'organizer') {
            header('Location: ' . APP_URL . '/admin/index.php');
        } else {
            header('Location: ' . APP_URL . '/events.php');
        }
        exit;
    } else {
        $error = $result['error'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="glass-card p-4 p-md-5 rounded-4 border-light-subtle shadow-lg">
                <div class="text-center mb-4">
                    <span class="brand-logo-icon mb-2">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </span>
                    <h3 class="fw-bold mb-1">Join EventPulse</h3>
                    <p class="text-body-secondary small">Choose your account type and start reserving or hosting</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small border-0"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Alex Rivera" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="alex@company.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Account Role</label>
                        <select name="role" class="form-select">
                            <option value="attendee" selected>Attendee (Reserve passes, join squads, attend)</option>
                            <option value="organizer">Event Organizer (Publish events, seat maps, gate scanner)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Company / Organization (Optional)</label>
                        <input type="text" name="organization" class="form-control" placeholder="e.g. Synthetix Labs">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password (min 6 characters)</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold shadow-sm mb-3">
                        Create My Account &rarr;
                    </button>
                </form>

                <div class="text-center mt-3 small text-body-secondary">
                    Already have an account? <a href="<?= APP_URL ?>/login.php" class="text-primary fw-semibold">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
