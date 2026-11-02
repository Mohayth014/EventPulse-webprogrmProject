    </main>
    <!-- Main Content wrapper ends -->

    <!-- Global Footer -->
    <footer class="footer mt-auto py-5 border-top border-light-subtle bg-body-tertiary">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="brand-logo-icon">
                            <i class="bi bi-ticket-perforated-fill"></i>
                        </span>
                        <span class="fs-4 fw-bold">Event<span class="text-gradient">Pulse</span></span>
                    </div>
                    <p class="text-body-secondary small mb-3">
                        The next-generation smart event ticketing platform engineered with interactive SVG venue mapping, dynamic anti-scalping TOTP passes, AI itinerary matchmaking, and team squad perks.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">PHP 8.3</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">MySQL 8.0</span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Bootstrap 5.3</span>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">5 Novelties</span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold mb-3 text-uppercase fs-xs letter-spacing-1 text-primary">Platform</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="<?= APP_URL ?>/events.php" class="text-body-secondary text-decoration-none hover-primary">Discover Events</a></li>
                        <li><a href="<?= APP_URL ?>/ai_planner.php" class="text-body-secondary text-decoration-none hover-primary">AI Concierge</a></li>
                        <li><a href="<?= APP_URL ?>/squad.php" class="text-body-secondary text-decoration-none hover-primary">Squad RSVP Pass</a></li>
                        <li><a href="<?= APP_URL ?>/my_tickets.php" class="text-body-secondary text-decoration-none hover-primary">Ticket Wallet</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-bold mb-3 text-uppercase fs-xs letter-spacing-1 text-primary">Organizers</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="<?= APP_URL ?>/admin/index.php" class="text-body-secondary text-decoration-none hover-primary">Live Dashboard</a></li>
                        <li><a href="<?= APP_URL ?>/admin/scanner.php" class="text-body-secondary text-decoration-none hover-primary">Gatekeeper Scanner</a></li>
                        <li><a href="<?= APP_URL ?>/admin/events.php" class="text-body-secondary text-decoration-none hover-primary">Event Builder</a></li>
                        <li><a href="<?= APP_URL ?>/admin/attendees.php" class="text-body-secondary text-decoration-none hover-primary">Attendee Manifest</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="fw-bold mb-3 text-uppercase fs-xs letter-spacing-1 text-primary">Project Artifacts</h6>
                    <p class="small text-body-secondary mb-2">View the complete engineering documentation, academic project report, and presentation slide deck:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= APP_URL ?>/presentation.html" target="_blank" class="btn btn-sm btn-outline-warning rounded-pill d-inline-flex align-items-center gap-1">
                            <i class="bi bi-file-earmark-slides-fill"></i> Web Slide Deck
                        </a>
                        <a href="<?= APP_URL ?>/PROJECT_REPORT.html" target="_blank" class="btn btn-sm btn-outline-info rounded-pill d-inline-flex align-items-center gap-1">
                            <i class="bi bi-file-earmark-richtext-fill"></i> Project Report
                        </a>
                        <a href="<?= APP_URL ?>/EventPulse_Presentation.pptx" download class="btn btn-sm btn-outline-light rounded-pill d-inline-flex align-items-center gap-1">
                            <i class="bi bi-download"></i> PPT (.pptx)
                        </a>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-light-subtle">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-body-secondary">
                <p class="mb-0">&copy; <?= date('Y') ?> EventPulse Inc. All rights reserved. Built with PHP, MySQL & Bootstrap.</p>
                <div class="d-flex gap-3 mt-2 mt-sm-0">
                    <span class="text-success"><i class="bi bi-shield-check me-1"></i> SSL & SHA-256 Verified</span>
                    <span><i class="bi bi-globe2 me-1"></i> Global CDN</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js for Live Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- HTML5 QR Code Scanner Library for WebCam Gatekeeper Check-in -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- Canvas Confetti for Booking Celebrations -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <!-- Application Script -->
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
