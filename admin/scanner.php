<?php
/**
 * EventPulse - Novelty 2: Gatekeeper Live QR Scanner Check-in Station
 */

$pageTitle = 'Gatekeeper Live Check-In Station';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = Database::getConnection();

// Metrics
$totalCheckedIn = $pdo->query("SELECT COUNT(*) FROM ticket_items WHERE checkin_status = 'checked_in'")->fetchColumn();
$totalTickets = $pdo->query("SELECT COUNT(*) FROM ticket_items")->fetchColumn();
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success">NOVELTY 2</span>
                    <span class="badge bg-secondary" id="scannerStatusBadge">Scanner Standby</span>
                </div>
                <h2 class="fw-bold mb-0"><i class="bi bi-qr-code-scan text-success me-2"></i> Gatekeeper Check-In Station</h2>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="small text-muted text-uppercase fs-xxs">Gate Check-in Velocity</div>
                    <div class="fw-bold fs-5 text-success">
                        <span id="liveVerifiedCount"><?= $totalCheckedIn ?></span> / <?= $totalTickets ?> Checked In
                    </div>
                </div>
                <a href="<?= APP_URL ?>/admin/index.php" class="btn btn-outline-secondary rounded-pill btn-sm">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <!-- Scanner Feed Column -->
        <div class="col-lg-6">
            <div class="glass-card p-4 rounded-4 shadow-lg border-success">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-camera-video text-primary me-2"></i> Live Optical Scanner</h5>
                    <div class="d-flex gap-2">
                        <button type="button" id="startScannerBtn" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                            <i class="bi bi-play-fill"></i> Start Camera
                        </button>
                        <button type="button" id="stopScannerBtn" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none">
                            <i class="bi bi-stop-fill"></i> Pause Camera
                        </button>
                    </div>
                </div>

                <!-- Video Viewport -->
                <div class="bg-black rounded-3 overflow-hidden position-relative mb-3 d-flex align-items-center justify-content-center" style="min-height: 320px;">
                    <div id="qr-reader" style="width: 100%;"></div>
                </div>

                <div class="text-center small text-muted mb-3">
                    <i class="bi bi-info-circle me-1"></i> Point attendee’s phone screen or printed pass towards the camera lens.
                </div>

                <!-- Manual Code Input Fallback -->
                <div class="pt-3 border-top border-light-subtle">
                    <label class="small text-muted fw-bold mb-1">Manual Pass Verification / Handheld Scanner Input</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-upc-scan text-muted"></i></span>
                        <input type="text" id="manualCodeInput" class="form-control font-monospace" placeholder="Enter Booking Ref (e.g. EP-2026-X8910) or Hash">
                        <button class="btn btn-primary px-3" type="button" id="manualVerifyBtn">Validate Pass</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Telemetry Output Column -->
        <div class="col-lg-6">
            <div class="glass-card p-4 rounded-4 shadow-lg h-100">
                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success"></i> Verification Telemetry
                </h5>

                <div id="scanResultDisplay">
                    <div class="text-center py-5 text-body-secondary">
                        <i class="bi bi-upc-scan display-1 text-muted opacity-50 mb-3 d-block"></i>
                        <h5 class="fw-bold">Awaiting Passholder Scan</h5>
                        <p class="small text-muted max-w-400 mx-auto">
                            When a pass is detected, the cryptographic HMAC signature is verified instantly and the result is signaled with Web Audio sound effects.
                        </p>
                    </div>
                </div>

                <!-- Audio Haptics Notice -->
                <div class="p-3 rounded-3 bg-body-tertiary border border-light-subtle small mt-auto">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-volume-up text-primary fs-5"></i>
                        <div>
                            <strong>Audio Haptic Feedback Active:</strong> Two-tone bright chime on valid access; warning buzz on duplicate/counterfeit tickets.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/scanner.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
