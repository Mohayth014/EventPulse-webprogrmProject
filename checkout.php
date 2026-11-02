<?php
/**
 * EventPulse - Checkout & Payment Execution
 */

$pageTitle = 'Secure Checkout';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';

$eventId = intval($_GET['event_id'] ?? 1);
$seatsParam = trim($_GET['seats'] ?? '');

$pdo = Database::getConnection();

// Fetch Event Details
$eStmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$eStmt->execute([$eventId]);
$event = $eStmt->fetch();

if (!$event) {
    header('Location: ' . APP_URL . '/events.php');
    exit;
}

// Fetch Selected Seats
$seatIds = array_filter(array_map('intval', explode(',', $seatsParam)));

if (empty($seatIds)) {
    header('Location: ' . APP_URL . '/event.php?id=' . $eventId);
    exit;
}

$placeholders = implode(',', array_fill(0, count($seatIds), '?'));
$sStmt = $pdo->prepare("
    SELECT s.*, tt.name as tier_name, tt.price as tier_price 
    FROM seats s 
    JOIN ticket_tiers tt ON s.tier_id = tt.id 
    WHERE s.id IN ($placeholders)
");
$sStmt->execute($seatIds);
$selectedSeats = $sStmt->fetchAll();

$subtotal = 0.0;
foreach ($selectedSeats as $st) {
    $subtotal += floatval($st['tier_price']);
}

$user = current_user();
?>

<div class="py-4 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="<?= APP_URL ?>/event.php?id=<?= $eventId ?>" class="text-body-secondary text-decoration-none small">
                &larr; Back to Event Seating
            </a>
        </div>
        <h2 class="fw-bold mb-0">Complete Your Reservation</h2>
        <p class="text-body-secondary mb-0"><?= e($event['title']) ?> &bull; <?= format_date($event['start_date']) ?></p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Left: Attendee Form & Perks Engine -->
        <div class="col-lg-8">
            <!-- 5-Minute Cart Reservation Hold Banner -->
            <div class="alert alert-warning border-0 d-flex align-items-center justify-content-between p-3 rounded-4 shadow-sm mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-stopwatch-fill fs-4 text-warning"></i>
                    <div>
                        <strong>Seats Held For You:</strong> Your selected seats are temporarily reserved.
                    </div>
                </div>
                <div class="fw-bold fs-5 font-heading text-dark" id="cartHoldTimer">04:59</div>
            </div>

            <!-- Attendee Details -->
            <div class="glass-card p-4 rounded-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-people text-primary me-2"></i> Attendee Passholder Information</h5>
                <p class="small text-muted mb-4">Each ticket will be generated with a cryptographic QR pass for the designated attendee.</p>

                <div id="attendeeInputsContainer" class="d-flex flex-column gap-3">
                    <?php foreach ($selectedSeats as $idx => $st): ?>
                        <div class="p-3 rounded-3 border border-light-subtle bg-body-tertiary attendee-row" data-seat-id="<?= $st['id'] ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">Ticket #<?= $idx + 1 ?>: Seat <?= $st['seat_number'] ?> (<?= e($st['tier_name']) ?>)</span>
                                <span class="fw-bold text-primary"><?= format_currency($st['tier_price']) ?></span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Full Name</label>
                                    <input type="text" class="form-control attendee-name-input" value="<?= ($idx === 0 && $user) ? e($user['name']) : '' ?>" placeholder="e.g. Alex Rivera" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="small text-muted mb-1">Email (for Pass Delivery)</label>
                                    <input type="email" class="form-control attendee-email-input" value="<?= ($idx === 0 && $user) ? e($user['email']) : '' ?>" placeholder="alex@company.com" required>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- NOVELTY 4: Squad RSVP & Group Perks Engine -->
            <div class="glass-card p-4 rounded-4 mb-4 border-info">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-info text-dark fw-bold mb-1">NOVELTY 4</span>
                        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-info me-2"></i> Squad RSVP & Team Discount</h5>
                    </div>
                    <a href="<?= APP_URL ?>/squad.php" target="_blank" class="small text-info text-decoration-none fw-semibold">
                        Create New Squad &rarr;
                    </a>
                </div>
                <p class="small text-body-secondary mb-3">
                    Have a Squad Code from your team or friends? Enter it below to unlock a 15% group discount.
                </p>

                <div class="input-group mb-2">
                    <span class="input-group-text bg-body border-end-0"><i class="bi bi-ticket-perforated text-info"></i></span>
                    <input type="text" id="squadCodeInput" class="form-control border-start-0" placeholder="e.g. SQ-TECH-9281">
                    <button class="btn btn-info text-dark fw-bold px-3" type="button" id="applySquadBtn">Apply Code</button>
                </div>
                <div id="squadFeedback" class="small"></div>
            </div>

            <!-- NOVELTY 5: Carbon Offset & Verifiable Eco-Guardian Badge -->
            <div class="glass-card p-4 rounded-4 mb-4 border-success">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-success fw-bold mb-1">NOVELTY 5</span>
                        <h5 class="fw-bold mb-0 text-success"><i class="bi bi-tree-fill me-2"></i> Verified Carbon Offset & Eco-Guardian Badge</h5>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success">+$5.00 / ticket</span>
                </div>
                <p class="small text-body-secondary mb-3">
                    Neutralize your travel footprint. Each contribution funds 2 certified native trees, removes 50kg CO₂, and unlocks the verifiable <strong>Eco-Guardian Green Badge</strong> on your digital pass.
                </p>

                <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                    <input class="form-check-input ms-0 fs-4" type="checkbox" role="switch" id="ecoOffsetToggle" checked>
                    <label class="form-check-label fw-semibold cursor-pointer" for="ecoOffsetToggle">
                        Yes, certify my booking with the Eco-Guardian Carbon Offset
                    </label>
                </div>
            </div>

            <!-- Payment Simulation -->
            <div class="glass-card p-4 rounded-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-credit-card-2-front text-primary me-2"></i> Payment Information</h5>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payMethod" id="payCard" value="Instant Credit Card" checked>
                        <label class="form-check-label fw-medium" for="payCard"><i class="bi bi-credit-card me-1"></i> Credit / Debit Card</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payMethod" id="payWallet" value="Apple/Google Digital Wallet">
                        <label class="form-check-label fw-medium" for="payWallet"><i class="bi bi-wallet2 me-1"></i> Digital Wallet Pass</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="small text-muted mb-1">Card Number</label>
                        <input type="text" class="form-control font-monospace" placeholder="4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242" value="4242 8891 0023 4242">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">Expiration Date</label>
                        <input type="text" class="form-control font-monospace" placeholder="MM/YY" value="12/28">
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted mb-1">CVC / Security Code</label>
                        <input type="text" class="form-control font-monospace" placeholder="CVC" value="882">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Order Summary sticky -->
        <div class="col-lg-4">
            <div class="glass-card p-4 rounded-4 sticky-top" style="top: 90px;">
                <h4 class="fw-bold mb-3">Order Summary</h4>

                <div class="mb-3 pb-3 border-bottom border-light-subtle">
                    <div class="fw-bold"><?= e($event['title']) ?></div>
                    <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= e($event['venue_name']) ?></div>
                    <div class="small text-muted"><i class="bi bi-calendar"></i> <?= format_date($event['start_date']) ?></div>
                </div>

                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex justify-content-between text-body-secondary small">
                        <span>Tickets (<?= count($selectedSeats) ?>x):</span>
                        <span id="summarySubtotal"><?= format_currency($subtotal) ?></span>
                    </div>

                    <div class="d-flex justify-content-between text-info small d-none" id="summarySquadRow">
                        <span>Squad Discount (15%):</span>
                        <span id="summarySquadAmount">-$0.00</span>
                    </div>

                    <div class="d-flex justify-content-between text-success small" id="summaryEcoRow">
                        <span>Eco Carbon Offset:</span>
                        <span id="summaryEcoAmount"><?= format_currency(count($selectedSeats) * 5.00) ?></span>
                    </div>

                    <div class="d-flex justify-content-between text-body-secondary small">
                        <span>Platform & Processing Fee:</span>
                        <span class="text-success fw-semibold">FREE ($0.00)</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top border-light-subtle mb-4">
                    <span class="fs-5 fw-bold">Total Due:</span>
                    <span class="fs-2 fw-bold text-primary font-heading" id="summaryGrandTotal">
                        <?= format_currency($subtotal + (count($selectedSeats) * 5.00)) ?>
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="button" id="confirmPaymentBtn" class="btn btn-primary btn-lg rounded-pill w-100 shadow mb-3">
                    <i class="bi bi-lock-fill me-1"></i> Authorize & Issue Passes
                </button>

                <div class="text-center small text-body-secondary">
                    <i class="bi bi-shield-check text-success"></i> Includes Instant Dynamic Pass with Anti-Scalp Rotating TOTP.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const baseSubtotal = <?= $subtotal ?>;
    const ticketCount = <?= count($selectedSeats) ?>;
    const eventId = <?= $eventId ?>;
    const seatIds = <?= json_encode($seatIds) ?>;

    let squadDiscountPercent = 0;
    let verifiedSquadCode = '';
    let isEcoOffset = true;

    const ecoToggle = document.getElementById('ecoOffsetToggle');
    const squadInput = document.getElementById('squadCodeInput');
    const applySquadBtn = document.getElementById('applySquadBtn');
    const squadFeedback = document.getElementById('squadFeedback');

    const summarySubtotal = document.getElementById('summarySubtotal');
    const summarySquadRow = document.getElementById('summarySquadRow');
    const summarySquadAmount = document.getElementById('summarySquadAmount');
    const summaryEcoRow = document.getElementById('summaryEcoRow');
    const summaryEcoAmount = document.getElementById('summaryEcoAmount');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');
    const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');

    // 5-minute timer countdown
    let secondsLeft = 299;
    const timerEl = document.getElementById('cartHoldTimer');
    const timerInterval = setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            clearInterval(timerInterval);
            alert('Your 5-minute seat reservation has expired. Please select your seats again.');
            window.location.href = `event.php?id=${eventId}`;
            return;
        }
        const m = Math.floor(secondsLeft / 60);
        const s = secondsLeft % 60;
        timerEl.textContent = `${m < 10 ? '0' + m : m}:${s < 10 ? '0' + s : s}`;
    }, 1000);

    // Eco toggle listener
    ecoToggle.addEventListener('change', () => {
        isEcoOffset = ecoToggle.checked;
        if (isEcoOffset) {
            summaryEcoRow.classList.remove('d-none');
        } else {
            summaryEcoRow.classList.add('d-none');
        }
        recalculateTotals();
    });

    // Squad Code verification
    applySquadBtn.addEventListener('click', async () => {
        const code = squadInput.value.trim();
        if (!code) return;

        applySquadBtn.disabled = true;
        applySquadBtn.textContent = 'Verifying...';

        try {
            const res = await fetch('api/squad_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'verify_squad', code: code })
            });
            const data = await res.json();
            applySquadBtn.disabled = false;
            applySquadBtn.textContent = 'Apply Code';

            if (data.success) {
                squadDiscountPercent = data.discount_percent || 15;
                verifiedSquadCode = code;
                squadFeedback.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill"></i> Squad "${data.squad.squad_name}" unlocked! 15% discount applied.</span>`;
                summarySquadRow.classList.remove('d-none');
                recalculateTotals();
            } else {
                squadDiscountPercent = 0;
                verifiedSquadCode = '';
                squadFeedback.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ${data.error || 'Invalid squad code.'}</span>`;
                summarySquadRow.classList.add('d-none');
                recalculateTotals();
            }
        } catch (e) {
            applySquadBtn.disabled = false;
            applySquadBtn.textContent = 'Apply Code';
            squadFeedback.innerHTML = `<span class="text-danger">Error connecting to server.</span>`;
        }
    });

    function recalculateTotals() {
        const discountAmount = baseSubtotal * (squadDiscountPercent / 100);
        const ecoFee = isEcoOffset ? (ticketCount * 5.00) : 0.00;
        const finalTotal = Math.max(0, (baseSubtotal - discountAmount) + ecoFee);

        summarySquadAmount.textContent = `-$${discountAmount.toFixed(2)}`;
        summaryEcoAmount.textContent = `$${ecoFee.toFixed(2)}`;
        summaryGrandTotal.textContent = `$${finalTotal.toFixed(2)}`;
    }

    // Process Payment Submit
    confirmPaymentBtn.addEventListener('click', async () => {
        const attendeeRows = document.querySelectorAll('.attendee-row');
        const attendees = [];

        let hasError = false;
        attendeeRows.forEach(row => {
            const nameInput = row.querySelector('.attendee-name-input');
            const emailInput = row.querySelector('.attendee-email-input');

            if (!nameInput.value.trim() || !emailInput.value.trim()) {
                hasError = true;
                nameInput.classList.add('is-invalid');
                emailInput.classList.add('is-invalid');
            } else {
                nameInput.classList.remove('is-invalid');
                emailInput.classList.remove('is-invalid');
            }

            attendees.push({
                name: nameInput.value.trim(),
                email: emailInput.value.trim()
            });
        });

        if (hasError) {
            alert('Please fill out the name and email for every ticket passholder.');
            return;
        }

        confirmPaymentBtn.disabled = true;
        confirmPaymentBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Issuing Cryptographic Passes...';

        try {
            const res = await fetch('api/book_ticket.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    event_id: eventId,
                    seat_ids: seatIds,
                    attendees: attendees,
                    squad_code: verifiedSquadCode,
                    carbon_offset: isEcoOffset
                })
            });

            const data = await res.json();

            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Booking failed: ' + (data.error || 'Unknown error'));
                confirmPaymentBtn.disabled = false;
                confirmPaymentBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Authorize & Issue Passes';
            }
        } catch (err) {
            alert('Payment processing error: ' + err.message);
            confirmPaymentBtn.disabled = false;
            confirmPaymentBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Authorize & Issue Passes';
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
