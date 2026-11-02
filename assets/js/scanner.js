/**
 * EventPulse - Novelty 2: Gatekeeper Live QR Scanner & Anti-Scalp Verification
 */

class GatekeeperScanner {
    constructor() {
        this.html5QrCode = null;
        this.isScanning = false;
        this.audioCtx = null;
        this.scanResultContainer = document.getElementById('scanResultDisplay');
        this.verifiedCountEl = document.getElementById('liveVerifiedCount');
        this.verifiedCount = 0;

        this.initAudio();
        this.initEventListeners();
    }

    initAudio() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            this.audioCtx = new AudioContext();
        } catch (e) {
            console.warn('Web Audio API not supported on this browser');
        }
    }

    playSuccessChime() {
        if (!this.audioCtx) return;
        if (this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }
        const now = this.audioCtx.currentTime;
        // Two-tone bright chime (587.33Hz D5 -> 880Hz A5)
        const osc1 = this.audioCtx.createOscillator();
        const gain1 = this.audioCtx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, now);
        osc1.frequency.exponentialRampToValueAtTime(880, now + 0.15);
        gain1.gain.setValueAtTime(0.3, now);
        gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.45);
        osc1.connect(gain1);
        gain1.connect(this.audioCtx.destination);
        osc1.start(now);
        osc1.stop(now + 0.45);
    }

    playErrorBuzz() {
        if (!this.audioCtx) return;
        if (this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }
        const now = this.audioCtx.currentTime;
        // Low saw wave buzz (140Hz)
        const osc = this.audioCtx.createOscillator();
        const gain = this.audioCtx.createGain();
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(140, now);
        gain.gain.setValueAtTime(0.4, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.5);
        osc.connect(gain);
        gain.connect(this.audioCtx.destination);
        osc.start(now);
        osc.stop(now + 0.5);
    }

    initEventListeners() {
        const startBtn = document.getElementById('startScannerBtn');
        const stopBtn = document.getElementById('stopScannerBtn');
        const manualVerifyBtn = document.getElementById('manualVerifyBtn');
        const manualCodeInput = document.getElementById('manualCodeInput');

        if (startBtn) {
            startBtn.addEventListener('click', () => this.startCamera());
        }
        if (stopBtn) {
            stopBtn.addEventListener('click', () => this.stopCamera());
        }
        if (manualVerifyBtn && manualCodeInput) {
            manualVerifyBtn.addEventListener('click', () => {
                const code = manualCodeInput.value.trim();
                if (code) {
                    this.verifyTicket(code);
                }
            });
            manualCodeInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const code = manualCodeInput.value.trim();
                    if (code) this.verifyTicket(code);
                }
            });
        }
    }

    startCamera() {
        const qrRegion = document.getElementById('qr-reader');
        if (!qrRegion) return;

        this.html5QrCode = new Html5Qrcode('qr-reader');
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        this.html5QrCode.start(
            { facingMode: 'environment' },
            config,
            (decodedText) => {
                // Pause scanner momentarily to prevent multiple reads
                this.html5QrCode.pause();
                this.verifyTicket(decodedText);
                setTimeout(() => {
                    try { this.html5QrCode.resume(); } catch(e){}
                }, 2500);
            },
            (errorMsg) => {
                // scanning frame error (ignore continuous scan noise)
            }
        ).then(() => {
            this.isScanning = true;
            document.getElementById('startScannerBtn').classList.add('d-none');
            document.getElementById('stopScannerBtn').classList.remove('d-none');
            document.getElementById('scannerStatusBadge').textContent = 'Live Scanner Active';
            document.getElementById('scannerStatusBadge').className = 'badge bg-success';
        }).catch(err => {
            alert('Camera access denied or unavailable: ' + err);
        });
    }

    stopCamera() {
        if (this.html5QrCode && this.isScanning) {
            this.html5QrCode.stop().then(() => {
                this.isScanning = false;
                document.getElementById('startScannerBtn').classList.remove('d-none');
                document.getElementById('stopScannerBtn').classList.add('d-none');
                document.getElementById('scannerStatusBadge').textContent = 'Scanner Standby';
                document.getElementById('scannerStatusBadge').className = 'badge bg-secondary';
            });
        }
    }

    async verifyTicket(ticketCode) {
        if (!this.scanResultContainer) return;

        this.scanResultContainer.innerHTML = `
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted small">Cryptographically verifying ticket pass...</div>
            </div>
        `;

        try {
            const res = await fetch('../api/scan_qr.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: ticketCode })
            });

            const data = await res.json();

            if (data.success && data.status === 'VALID') {
                this.playSuccessChime();
                this.verifiedCount++;
                if (this.verifiedCountEl) this.verifiedCountEl.textContent = this.verifiedCount;

                this.scanResultContainer.innerHTML = `
                    <div class="alert alert-success border-0 shadow-sm p-4 rounded-4 animate__animated animate__fadeIn">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-check-lg fs-3"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold text-success">ACCESS GRANTED</h4>
                                <div class="text-secondary small">Ticket Hash: <code>${data.ticket.ticket_hash.substring(0, 16)}...</code></div>
                            </div>
                        </div>
                        <div class="row g-2 border-top border-success-subtle pt-3">
                            <div class="col-6"><span class="text-muted small">Attendee:</span> <div class="fw-bold">${data.ticket.attendee_name}</div></div>
                            <div class="col-6"><span class="text-muted small">Tier:</span> <div><span class="badge bg-primary">${data.ticket.tier_name}</span></div></div>
                            <div class="col-6"><span class="text-muted small">Seat Assigned:</span> <div class="fw-bold text-warning">${data.ticket.seat_number || 'General Entry'}</div></div>
                            <div class="col-6"><span class="text-muted small">Event:</span> <div class="fw-semibold text-truncate">${data.ticket.event_title}</div></div>
                            ${data.ticket.eco_badge_granted ? '<div class="col-12 mt-2"><span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-tree-fill"></i> Eco-Guardian Passholder Verified</span></div>' : ''}
                        </div>
                    </div>
                `;
            } else if (data.status === 'ALREADY_CHECKED_IN') {
                this.playErrorBuzz();
                this.scanResultContainer.innerHTML = `
                    <div class="alert alert-warning border-0 shadow-sm p-4 rounded-4">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-warning">ALREADY CHECKED IN!</h4>
                                <div class="small">Duplicate pass entry attempt detected.</div>
                            </div>
                        </div>
                        <p class="small text-muted mb-1">Pass was previously verified at: <strong>${data.ticket.checkin_time}</strong></p>
                        <p class="small mb-0">Attendee: <strong>${data.ticket.attendee_name}</strong> (${data.ticket.tier_name})</p>
                    </div>
                `;
            } else {
                this.playErrorBuzz();
                this.scanResultContainer.innerHTML = `
                    <div class="alert alert-danger border-0 shadow-sm p-4 rounded-4">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-x-circle-fill fs-2 text-danger"></i>
                            <div>
                                <h4 class="mb-0 fw-bold text-danger">INVALID OR COUNTERFEIT PASS</h4>
                                <div class="small text-muted">${data.error || 'The scanned ticket token does not exist in the database.'}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch (err) {
            this.playErrorBuzz();
            this.scanResultContainer.innerHTML = `
                <div class="alert alert-danger">Error connecting to verification server: ${err.message}</div>
            `;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('qr-reader') || document.getElementById('manualVerifyBtn')) {
        window.gatekeeper = new GatekeeperScanner();
    }
});
