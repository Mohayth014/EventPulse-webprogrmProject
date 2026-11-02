<?php
/**
 * EventPulse - Novelty 3: AI Concierge & Smart Itinerary Matchmaker
 */

$pageTitle = 'AI Concierge & Smart Schedule Matchmaker';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="py-5 bg-body-tertiary border-bottom border-light-subtle">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark fw-bold mb-2">NOVELTY 3</span>
                <h1 class="display-6 fw-extrabold mb-2">AI Event Concierge & Session Matchmaker</h1>
                <p class="lead text-body-secondary mb-0">
                    Eliminate conference decision paralysis. Our AI analyzes your professional background, learning targets, and networking objectives to generate a personalized agenda and 1-click sync to your Google/Apple Calendar.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-inline-flex align-items-center gap-2 p-2 px-3 rounded-pill glass-card">
                    <i class="bi bi-robot text-warning fs-4"></i>
                    <div class="text-start">
                        <div class="fs-xxs text-uppercase text-muted">Concierge Model</div>
                        <div class="small fw-bold">Gemini Multimodal Reasoning</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <!-- Interactive Questionnaire Card -->
    <div class="glass-card p-4 p-lg-5 rounded-4 mb-5 border-warning">
        <div class="row g-4 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-bold small text-uppercase letter-spacing-1 text-primary">
                    <i class="bi bi-person-workspace me-1"></i> Your Primary Role
                </label>
                <select id="conciergeRole" class="form-select form-select-lg">
                    <option value="Software Engineer" selected>Software Engineer / ML Practitioner</option>
                    <option value="Founder / Executive">Founder / Tech Executive / Investor</option>
                    <option value="Product & UI/UX Designer">Product Manager / UI/UX Designer</option>
                </select>
            </div>

            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-bold small text-uppercase letter-spacing-1 text-primary">
                    <i class="bi bi-bullseye me-1"></i> Main Conference Objective
                </label>
                <select id="conciergeGoal" class="form-select form-select-lg">
                    <option value="Technical Masterclasses" selected>Hands-on Labs & Architecture Demos</option>
                    <option value="Venture Capital & Deals">Venture Deals, Pitching & Seed Capital</option>
                    <option value="Career & Talent">Hiring Talent & Career Connections</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-bold small text-uppercase letter-spacing-1 text-primary">
                    <i class="bi bi-speedometer2 me-1"></i> Desired Pace
                </label>
                <select id="conciergePace" class="form-select form-select-lg">
                    <option value="High Energy">Intensive & Packed</option>
                    <option value="Balanced" selected>Balanced Track</option>
                    <option value="Relaxed">Leisure & Networking</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <button type="button" id="generateItineraryBtn" class="btn btn-warning btn-lg text-dark fw-bold rounded-pill w-100 shadow">
                    <i class="bi bi-magic me-1"></i> Synthesize
                </button>
            </div>
        </div>
    </div>

    <!-- AI Results Container (Populated dynamically by concierge.js) -->
    <div id="aiResultsSection" class="d-none">
        <!-- Rendered by assets/js/concierge.js -->
    </div>

    <!-- Feature Pillars -->
    <div class="row g-4 mt-4">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 d-inline-flex mb-3">
                    <i class="bi bi-signpost-split-fill fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Zero Session Clashes</h5>
                <p class="small text-muted mb-0">Our scheduling engine dynamically calculates physical transit time between halls and stages to prevent double bookings.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <div class="rounded-3 bg-success-subtle text-success p-3 d-inline-flex mb-3">
                    <i class="bi bi-calendar2-check-fill fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">1-Click Calendar Export (.ICS)</h5>
                <p class="small text-muted mb-0">Export your personalized conference itinerary directly into Google Calendar, Apple iCal, or Microsoft Outlook with precise alarms.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100">
                <div class="rounded-3 bg-info-subtle text-info p-3 d-inline-flex mb-3">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Intelligent Networking Cohorts</h5>
                <p class="small text-muted mb-0">Discover matched attendees and roundtable cohorts with complementary skills, venture interests, and tech stacks.</p>
            </div>
        </div>
    </div>
</div>

<script src="<?= APP_URL ?>/assets/js/concierge.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
