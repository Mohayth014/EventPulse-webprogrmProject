<?php
/**
 * EventPulse - Database Connection Class (PDO Singleton)
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die('<div style="font-family:sans-serif;padding:30px;background:#fef2f2;border:1px solid #f87171;color:#991b1b;border-radius:8px;max-width:700px;margin:50px auto;">' .
                    '<h2>Database Connection Failed</h2>' .
                    '<p>Unable to connect to MySQL database <strong>' . DB_NAME . '</strong> on ' . DB_HOST . ':' . DB_PORT . '.</p>' .
                    '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>' .
                    '<p>Make sure MySQL service is running and credentials in <code>config/config.php</code> are valid.</p>' .
                    '</div>');
            }
        }
        return self::$instance;
    }
}
