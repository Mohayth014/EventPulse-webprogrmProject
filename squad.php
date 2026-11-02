<?php
/**
 * EventPulse - Novelty 4: Squad RSVP & Group Perks Dashboard
 */

$pageTitle = 'Squad RSVP & Group Perks';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();

// Fetch events for squad creation dropdown
$events = $pdo->query("SELECT id, title, start_date FROM events WHERE status = 'published' ORDER BY start_date ASC")->fetchAll();

// Fetch existing active squads
$squads = $pdo->query("
    SELECT s.*, e.title as event_title, u.name as leader_name 
    FROM squads s 
    JOIN events e ON s.event_id = e.id 
    JOIN users u ON s.squad_leader_id = u.id 
    ORDER BY s.id DESC 
    LIMIT 6
")->fetchAll();

$user = current_user();
?>

<div class="py-5 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-info text-dark fw-bold mb-2">NOVELTY 4</span>
                <h1 class="display-6 fw-extrabold mb-2">Squad RSVP & Group Perks Engine</h1>
                <p class="lead text-body-secondary mb-0">
                    Never attend alone. Form a squad with your colleagues, hackathon squad, or friends, unlock <strong>15% to 20% group volume discounts</strong>, and generate unified digital team passes.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button type="button" class="btn btn-info text-dark btn-lg rounded-pill px-4 shadow fw-bold" data-bs-toggle="modal" data-bs-target="#createSquadModal">
                    <i class="bi bi-plus-circle me-1"></i> Form a New Squad
                </button>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center">
                <div class="rounded-circle bg-info-subtle text-info p-3 d-inline-flex mb-3">
                    <i class="bi bi-person-plus-fill fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2">1. Create or Join Squad</h5>
                <p class="small text-muted mb-0">Set up your squad name, choose your event, and receive an instant cryptographic share link.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center">
                <div class="rounded-circle bg-success-subtle text-success p-3 d-inline-flex mb-3">
                    <i class="bi bi-tag-fill fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2">2. Unlock 15% Group Discount</h5>
                <p class="small text-muted mb-0">The moment your squad code is entered during checkout, our smart pricing engine applies the discount automatically.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center">
                <div class="rounded-circle bg-warning-subtle text-warning p-3 d-inline-flex mb-3">
                    <i class="bi bi-award-fill fs-2"></i>
                </div>
                <h5 class="fw-bold mb-2">3. Cohort Badges & Reserved Blocks</h5>
                <p class="small text-muted mb-0">Squad passes share the same team moniker and grant access to designated group discussion tables at the venue.</p>
            </div>
        </div>
    </div>

    <!-- Active Squads Gallery -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Active Squads & Delegation Cohorts</h4>
            <div class="small text-muted">Join an existing public team or copy their referral code</div>
        </div>
        <button class="btn btn-outline-info rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#createSquadModal">
            <i class="bi bi-people-fill me-1"></i> Start My Team
        </button>
    </div>

    <div class="row g-4" id="squadsGallery">
        <?php foreach ($squads as $sq): ?>
            <?php
            $percent = min(100, round(($sq['member_count'] / $sq['target_size']) * 100));
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="glass-card p-4 h-100 border-info">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-info text-dark fw-bold"><?= $sq['discount_percent'] ?>% GROUP OFF</span>
                        <span class="badge bg-body-secondary text-body small font-monospace"><?= e($sq['squad_code']) ?></span>
                    </div>

                    <h5 class="fw-bold mb-1"><?= e($sq['squad_name']) ?></h5>
                    <div class="small text-primary mb-2 fw-medium"><?= e($sq['event_title']) ?></div>
                    <div class="small text-muted mb-3">Created by: <strong><?= e($sq['leader_name']) ?></strong></div>

                    <!-- Progress bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Members</span>
                            <span><strong><?= $sq['member_count'] ?></strong> of <?= $sq['target_size'] ?> joined</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto pt-2 border-top border-light-subtle">
                        <button class="btn btn-sm btn-outline-secondary w-50 rounded-pill" data-copy-text="<?= $sq['squad_code'] ?>">
                            <i class="bi bi-clipboard"></i> Copy Code
                        </button>
                        <a href="<?= APP_URL ?>/event.php?id=<?= $sq['event_id'] ?>" class="btn btn-sm btn-info text-dark w-50 rounded-pill fw-bold">
                            Join & Book
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Create Squad Modal -->
<div class="modal fade" id="createSquadModal" tabindex="-1" aria-labelledby="createSquadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-info">
            <div class="modal-header border-bottom border-light-subtle">
                <h5 class="modal-title fw-bold" id="createSquadModalLabel"><i class="bi bi-people-fill text-info me-2"></i> Form a New Event Squad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Choose Event</label>
                    <select id="modalEventSelect" class="form-select">
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>"><?= e($ev['title']) ?> (<?= format_date($ev['start_date'], 'M d') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Squad / Team Name</label>
                    <input type="text" id="modalSquadName" class="form-control" placeholder="e.g. Autonomous Agents Research Guild" value="AI Innovators Squad">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Target Team Size</label>
                    <select id="modalTargetSize" class="form-select">
                        <option value="3">3 Members (15% Off)</option>
                        <option value="4" selected>4 Members (15% Off)</option>
                        <option value="6">6 Members (20% Off)</option>
                        <option value="8">8+ Members (20% Off)</option>
                    </select>
                </div>

                <div id="modalSquadResult" class="d-none alert alert-success small mb-0"></div>
            </div>
            <div class="modal-footer border-top border-light-subtle">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
                <button type="button" id="modalSubmitSquadBtn" class="btn btn-info text-dark fw-bold rounded-pill px-4">Generate Squad Code</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const submitBtn = document.getElementById('modalSubmitSquadBtn');
    const resultBox = document.getElementById('modalSquadResult');

    submitBtn.addEventListener('click', async () => {
        const eventId = document.getElementById('modalEventSelect').value;
        const squadName = document.getElementById('modalSquadName').value.trim();
        const targetSize = document.getElementById('modalTargetSize').value;

        if (!squadName) {
            alert('Please enter a squad name.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Generating...';

        try {
            const res = await fetch('api/squad_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create_squad',
                    event_id: eventId,
                    squad_name: squadName,
                    target_size: targetSize
                })
            });

            const data = await res.json();
            submitBtn.disabled = false;
            submitBtn.textContent = 'Generate Squad Code';

            if (data.success) {
                resultBox.classList.remove('d-none');
                resultBox.innerHTML = `
                    <div class="fw-bold mb-1">Squad Created Successfully!</div>
                    <div>Your Squad Code: <code class="fs-5 text-dark fw-bold">${data.squad.code}</code></div>
                    <div class="mt-2 text-muted">Use this code at checkout to unlock your 15% discount. Refresh page to see it in gallery!</div>
                `;
            } else {
                alert('Error: ' + (data.error || 'Unable to create squad'));
            }
        } catch (e) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Generate Squad Code';
            alert('Network error: ' + e.message);
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
