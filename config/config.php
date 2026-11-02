<?php
/**
 * EventPulse - Configuration File
 */

// Error reporting (development)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session setup
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// App Settings
define('APP_NAME', 'EventPulse');
define('APP_TAGLINE', 'Next-Gen Intelligent Event Registration & Ticketing Ecosystem');
define('APP_URL', 'http://localhost:8000');
define('CURRENCY_SYMBOL', '$');

// Database Settings
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'eventpulse_db');
define('DB_USER', 'root');
define('DB_PASS', 'moheth');

// Secret salt for HMAC ticket pass hashing
define('APP_SECRET_KEY', 'eventpulse_cryptographic_secret_key_2026_x89f!');
