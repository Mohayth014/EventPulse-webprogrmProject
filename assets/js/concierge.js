/**
 * EventPulse - Novelty 3: AI Concierge & Smart Itinerary Generator
 */

document.addEventListener('DOMContentLoaded', () => {
    const generateBtn = document.getElementById('generateItineraryBtn');
    const roleSelect = document.getElementById('conciergeRole');
    const goalSelect = document.getElementById('conciergeGoal');
    const paceSelect = document.getElementById('conciergePace');
    const resultContainer = document.getElementById('aiResultsSection');

    if (!generateBtn || !resultContainer) return;

    generateBtn.addEventListener('click', async () => {
        const role = roleSelect.value;
        const goal = goalSelect.value;
        const pace = paceSelect.value;

        generateBtn.disabled = true;
        generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Synthesizing Personalized Schedule...';

        try {
            const response = await fetch('api/ai_concierge.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ role, goal, pace })
            });

            const data = await response.json();

            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="bi bi-magic me-2"></i> Regenerate Custom Schedule';

            if (data.success) {
                renderAIResults(data);
            } else {
                alert('Failed to generate itinerary: ' + (data.error || 'Unknown error'));
            }
        } catch (err) {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="bi bi-magic me-2"></i> Generate Intelligent Schedule';
            alert('Error generating itinerary: ' + err.message);
        }
    });

    function renderAIResults(data) {
        resultContainer.classList.remove('d-none');
        resultContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const stepsHtml = data.itinerary.map(item => `
            <div class="ai-itinerary-step">
                <div class="step-dot"></div>
                <div class="glass-card p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">${item.time}</span>
                        <span class="badge bg-secondary-subtle text-secondary small">${item.room}</span>
                    </div>
                    <h6 class="fw-bold mb-1">${item.title}</h6>
                    <p class="small text-muted mb-2">${item.description}</p>
                    <div class="d-flex align-items-center justify-content-between small">
                        <span class="text-body-secondary"><i class="bi bi-person-fill text-info me-1"></i> ${item.speaker || 'Networking Cohort'}</span>
                        <span class="badge bg-success-subtle text-success"><i class="bi bi-stars"></i> ${item.relevanceScore}% Match</span>
                    </div>
                </div>
            </div>
        `).join('');

        const cohortsHtml = data.networkingMatches.map(cohort => `
            <div class="col-md-6">
                <div class="glass-card p-3 h-100">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="rounded-circle bg-primary-subtle p-2 text-primary">
                            <i class="bi ${cohort.icon} fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold">${cohort.name}</div>
                            <span class="badge bg-success">${cohort.matchScore}% Synergy</span>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">${cohort.description}</p>
                </div>
            </div>
        `).join('');

        resultContainer.innerHTML = `
            <div class="glass-card p-4 rounded-4 mb-4 border-primary">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom border-light-subtle">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark"><i class="bi bi-stars"></i> Tailored Itinerary</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">${data.attendeeProfile.role} Track</span>
                        </div>
                        <h4 class="fw-bold mt-2 mb-1">${data.summaryTitle}</h4>
                        <p class="small text-muted mb-0">${data.summaryDescription}</p>
                    </div>
                    <div>
                        <button id="downloadIcsBtn" class="btn btn-outline-success rounded-pill d-inline-flex align-items-center gap-2">
                            <i class="bi bi-calendar2-plus"></i> Export to Calendar (.ICS)
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <h6 class="fw-bold text-uppercase fs-xs text-primary mb-3 letter-spacing-1">Optimized Session Sequence</h6>
                        <div class="ps-2">
                            ${stepsHtml}
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h6 class="fw-bold text-uppercase fs-xs text-primary mb-3 letter-spacing-1">Curated Networking Matches</h6>
                        <div class="row g-3">
                            ${cohortsHtml}
                        </div>
                        <div class="alert alert-info border-0 rounded-3 mt-4 small">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            <strong>AI Tip:</strong> Wear your digital badge QR at registration to automatically join the ${data.attendeeProfile.role} lounge table.
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Wire up .ICS export
        document.getElementById('downloadIcsBtn').addEventListener('click', () => {
            downloadCalendarFile(data);
        });
    }

    function downloadCalendarFile(data) {
        let icsContent = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//EventPulse//AI Concierge//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH'
        ];

        data.itinerary.forEach((item, idx) => {
            icsContent.push('BEGIN:VEVENT');
            icsContent.push(`UID:eventpulse-${Date.now()}-${idx}@eventpulse.io`);
            icsContent.push(`DTSTAMP:20261118T090000Z`);
            icsContent.push(`DTSTART:20261118T090000Z`);
            icsContent.push(`DTEND:20261118T170000Z`);
            icsContent.push(`SUMMARY:${item.title}`);
            icsContent.push(`DESCRIPTION:${item.description.replace(/,/g, '\\,')}`);
            icsContent.push(`LOCATION:${item.room}`);
            icsContent.push('STATUS:CONFIRMED');
            icsContent.push('END:VEVENT');
        });

        icsContent.push('END:VCALENDAR');

        const blob = new Blob([icsContent.join('\r\n')], { type: 'text/calendar;charset=utf-8' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.setAttribute('download', 'EventPulse_Custom_Itinerary.ics');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});
