/**
 * EventPulse - Global Application JS
 */

document.addEventListener('DOMContentLoaded', () => {
    // Theme Switcher Logic (Dark / Light)
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const htmlElement = document.documentElement;

    const savedTheme = localStorage.getItem('eventpulse_theme') || 'dark';
    setTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(nextTheme);
        });
    }

    function setTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('eventpulse_theme', theme);
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill text-warning';
            } else {
                themeIcon.className = 'bi bi-moon-stars-fill text-primary';
            }
        }
    }

    // Initialize Bootstrap Tooltips & Popovers
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Dynamic Live Countdown Tickers
    const countdownElements = document.querySelectorAll('[data-countdown]');
    countdownElements.forEach(el => {
        const targetDate = new Date(el.getAttribute('data-countdown')).getTime();
        function updateTimer() {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                el.innerHTML = '<span class="text-danger fw-bold">Event in Progress / Concluded</span>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            el.innerHTML = `
                <div class="d-inline-flex gap-2 text-center">
                    <div class="px-2 py-1 rounded bg-body-tertiary border"><div class="fw-bold">${days}</div><div class="fs-xxs text-muted">DAYS</div></div>
                    <div class="px-2 py-1 rounded bg-body-tertiary border"><div class="fw-bold">${hours}</div><div class="fs-xxs text-muted">HRS</div></div>
                    <div class="px-2 py-1 rounded bg-body-tertiary border"><div class="fw-bold">${minutes}</div><div class="fs-xxs text-muted">MIN</div></div>
                    <div class="px-2 py-1 rounded bg-body-tertiary border"><div class="fw-bold">${seconds}</div><div class="fs-xxs text-muted">SEC</div></div>
                </div>
            `;
        }
        updateTimer();
        setInterval(updateTimer, 1000);
    });

    // Copy to Clipboard buttons
    document.querySelectorAll('[data-copy-text]').forEach(btn => {
        btn.addEventListener('click', () => {
            const textToCopy = btn.getAttribute('data-copy-text');
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
                btn.classList.add('btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                }, 2000);
            });
        });
    });
});
