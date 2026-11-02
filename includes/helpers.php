<?php
/**
 * EventPulse - Helper Functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Generate or get CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render hidden CSRF form input
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf_token(?string $token): bool {
    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize output
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function format_currency(float $amount): string {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Format date & time
 */
function format_date(string $datetime, string $format = 'M d, Y - h:i A'): string {
    $dt = new DateTime($datetime);
    return $dt->format($format);
}

/**
 * Set flash alert message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Get current user session array
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if current user is an organizer or admin
 */
function is_organizer(): bool {
    return is_logged_in() && in_array($_SESSION['user']['role'], ['organizer', 'admin']);
}

/**
 * Check if current user is an admin
 */
function is_admin(): bool {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

/**
 * Generate dynamic rotating TOTP watermarked token for anti-scalping (Novelty 2)
 * Rotates every 30 seconds based on Unix time slot
 */
function generate_dynamic_pass_totp(string $ticketHash): array {
    $timeStep = 30; // 30-second window
    $currentSlot = floor(time() / $timeStep);
    $totp = strtoupper(substr(hash_hmac('sha256', (string)$currentSlot, $ticketHash . APP_SECRET_KEY), 0, 8));
    $secondsRemaining = $timeStep - (time() % $timeStep);
    return [
        'totp' => $totp,
        'slot' => $currentSlot,
        'remaining' => $secondsRemaining
    ];
}

/**
 * Verify a ticket dynamic pass code against current and previous 30s window (prevents clock skew)
 */
function verify_dynamic_pass_totp(string $ticketHash, string $providedTotp): bool {
    $timeStep = 30;
    $currentSlot = floor(time() / $timeStep);
    // Check current slot and slot - 1
    for ($i = 0; $i <= 1; $i++) {
        $slot = $currentSlot - $i;
        $expected = strtoupper(substr(hash_hmac('sha256', (string)$slot, $ticketHash . APP_SECRET_KEY), 0, 8));
        if (hash_equals($expected, strtoupper($providedTotp))) {
            return true;
        }
    }
    return false;
}
