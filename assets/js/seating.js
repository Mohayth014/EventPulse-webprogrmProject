/**
 * EventPulse - Novelty 1: Interactive Venue Seating Map & Stage Perspective Preview
 */

class VenueSeatingManager {
    constructor(options) {
        this.container = document.querySelector(options.containerSelector || '.seats-grid');
        this.selectedSeats = [];
        this.maxSeats = options.maxSeats || 6;
        this.eventId = options.eventId;
        this.onSelectionChange = options.onSelectionChange || null;

        // Perspective elements
        this.perspectiveDistance = document.getElementById('previewDistance');
        this.perspectiveAngle = document.getElementById('previewAngle');
        this.perspectiveAcoustic = document.getElementById('previewAcoustic');
        this.stageCanvas = document.getElementById('stageCanvas');
        this.selectedSeatsDisplay = document.getElementById('selectedSeatsDisplay');
        this.selectedTotalDisplay = document.getElementById('selectedTotalDisplay');
        this.checkoutBtn = document.getElementById('proceedToCheckoutBtn');

        this.init();
    }

    init() {
        if (!this.container) return;

        this.container.querySelectorAll('.seat-unit:not(.booked)').forEach(seat => {
            seat.addEventListener('click', (e) => this.toggleSeat(e.currentTarget));
        });

        // Set initial perspective view to center row C
        this.updateStagePerspective('C', 8);
    }

    toggleSeat(seatEl) {
        const seatNumber = seatEl.getAttribute('data-seat-number');
        const price = parseFloat(seatEl.getAttribute('data-seat-price'));
        const tierName = seatEl.getAttribute('data-tier-name');
        const row = seatEl.getAttribute('data-row');
        const col = parseInt(seatEl.getAttribute('data-col'));

        const existingIndex = this.selectedSeats.findIndex(s => s.seatNumber === seatNumber);

        if (existingIndex > -1) {
            // Deselect
            this.selectedSeats.splice(existingIndex, 1);
            seatEl.classList.remove('selected');
        } else {
            // Check max seats limit
            if (this.selectedSeats.length >= this.maxSeats) {
                alert(`You can select a maximum of ${this.maxSeats} seats per booking.`);
                return;
            }
            // Select
            this.selectedSeats.push({ seatNumber, price, tierName, row, col, seatId: seatEl.getAttribute('data-seat-id') });
            seatEl.classList.add('selected');

            // Trigger perspective update
            this.updateStagePerspective(row, col);
        }

        this.updateUI();
    }

    updateStagePerspective(row, col) {
        // Distance calculation based on row: A = 6m, B = 9m, C = 13m, D = 17m, E = 22m, F = 26m
        const rowDistances = { 'A': 6, 'B': 9, 'C': 13, 'D': 17, 'E': 22, 'F': 26 };
        const dist = rowDistances[row] || 15;

        // Angle offset from center column (8.5 is center): negative is left, positive is right
        const angleOffset = Math.round((col - 8.5) * 4.5);
        const acousticScore = Math.max(78, Math.round(99 - (dist * 0.7) - Math.abs(angleOffset * 0.3)));

        if (this.perspectiveDistance) this.perspectiveDistance.textContent = `${dist} meters`;
        if (this.perspectiveAngle) this.perspectiveAngle.textContent = `${Math.abs(angleOffset)}° ${angleOffset < 0 ? 'Left Wing' : (angleOffset > 0 ? 'Right Wing' : 'Direct Center')}`;
        if (this.perspectiveAcoustic) this.perspectiveAcoustic.textContent = `${acousticScore}% Acoustic Clarity`;

        // Render visual stage viewpoint simulation in canvas
        this.renderStageCanvas(dist, angleOffset, row, col);
    }

    renderStageCanvas(dist, angle, row, col) {
        if (!this.stageCanvas) return;
        const ctx = this.stageCanvas.getContext('2d');
        const w = this.stageCanvas.width;
        const h = this.stageCanvas.height;

        ctx.clearRect(0, 0, w, h);

        // Ambient concert / stage lighting backdrop
        const grad = ctx.createRadialGradient(w / 2 + angle * 2, h * 0.3, 10, w / 2, h / 2, w / 1.4);
        grad.addColorStop(0, 'rgba(124, 58, 237, 0.45)');
        grad.addColorStop(0.5, 'rgba(30, 27, 75, 0.7)');
        grad.addColorStop(1, '#030712');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);

        // Perspective scale based on distance
        const scaleFactor = Math.max(0.4, 1.4 - (dist / 24));
        const stageWidth = 240 * scaleFactor;
        const stageHeight = 55 * scaleFactor;
        const stageX = (w - stageWidth) / 2 - (angle * 1.8);
        const stageY = 35 + (dist * 1.8);

        // Draw Stage Platform with neon glow
        ctx.shadowColor = '#8b5cf6';
        ctx.shadowBlur = 20;
        ctx.fillStyle = '#6366f1';
        ctx.beginPath();
        ctx.roundRect(stageX, stageY, stageWidth, stageHeight, 8);
        ctx.fill();

        // Stage LED Screen content
        ctx.shadowBlur = 0;
        ctx.fillStyle = '#ffffff';
        ctx.font = `bold ${Math.round(14 * scaleFactor)}px "Space Grotesk", sans-serif`;
        ctx.textAlign = 'center';
        ctx.fillText('MAIN STAGE 360°', stageX + stageWidth / 2, stageY + stageHeight / 2 + 5);

        // Light beams coming down from ceiling to stage
        ctx.strokeStyle = 'rgba(236, 72, 153, 0.3)';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(w * 0.2, 0);
        ctx.lineTo(stageX + 20, stageY);
        ctx.moveTo(w * 0.8, 0);
        ctx.lineTo(stageX + stageWidth - 20, stageY);
        ctx.stroke();

        // Attendee seat position marker in foreground
        ctx.fillStyle = '#f59e0b';
        ctx.beginPath();
        ctx.arc(w / 2, h - 20, 10, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#ffffff';
        ctx.font = '10px sans-serif';
        ctx.fillText(`Seat ${row}${col < 10 ? '0' + col : col}`, w / 2, h - 6);
    }

    updateUI() {
        if (!this.selectedSeatsDisplay) return;

        if (this.selectedSeats.length === 0) {
            this.selectedSeatsDisplay.innerHTML = '<span class="text-muted small">No seats selected yet. Click any available seat above.</span>';
            if (this.selectedTotalDisplay) this.selectedTotalDisplay.textContent = '$0.00';
            if (this.checkoutBtn) {
                this.checkoutBtn.classList.add('disabled');
                this.checkoutBtn.setAttribute('href', '#');
            }
            return;
        }

        let total = 0;
        const seatBadges = this.selectedSeats.map(s => {
            total += s.price;
            return `<span class="badge bg-warning text-dark me-1 p-2"><i class="bi bi-geo-alt-fill"></i> ${s.seatNumber} (${s.tierName} - $${s.price.toFixed(2)})</span>`;
        }).join('');

        this.selectedSeatsDisplay.innerHTML = seatBadges;
        if (this.selectedTotalDisplay) this.selectedTotalDisplay.textContent = `$${total.toFixed(2)}`;

        if (this.checkoutBtn) {
            this.checkoutBtn.classList.remove('disabled');
            const seatIdsParam = this.selectedSeats.map(s => s.seatId).join(',');
            this.checkoutBtn.setAttribute('href', `checkout.php?event_id=${this.eventId}&seats=${encodeURIComponent(seatIdsParam)}`);
        }

        if (this.onSelectionChange) {
            this.onSelectionChange(this.selectedSeats, total);
        }
    }
}
window.VenueSeatingManager = VenueSeatingManager;
