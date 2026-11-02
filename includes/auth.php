<?php
/**
 * EventPulse - Authentication & Access Control
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

class Auth {
    /**
     * Authenticate user with email and password
     */
    public static function attempt(string $email, string $password): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->execute([trim(strtolower($email))]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Remove password hash from session storage
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }

    /**
     * Register a new user
     */
    public static function register(string $name, string $email, string $password, string $role = 'attendee', ?string $phone = null, ?string $organization = null): array {
        $pdo = Database::getConnection();

        // Validate unique email
        $check = $pdo->prepare("SELECT id FROM `users` WHERE `email` = ?");
        $check->execute([trim(strtolower($email))]);
        if ($check->fetch()) {
            return ['success' => false, 'error' => 'An account with this email address already exists.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters.'];
        }

        $allowedRoles = ['attendee', 'organizer'];
        $userRole = in_array($role, $allowedRoles) ? $role : 'attendee';

        $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `phone`, `organization`, `avatar`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6366f1&color=fff&size=150';
        $success = $stmt->execute([
            trim($name),
            trim(strtolower($email)),
            password_hash($password, PASSWORD_BCRYPT),
            $userRole,
            trim($phone ?? ''),
            trim($organization ?? ''),
            $avatar
        ]);

        if ($success) {
            $userId = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => $userId,
                'name' => trim($name),
                'email' => trim(strtolower($email)),
                'role' => $userRole,
                'phone' => trim($phone ?? ''),
                'organization' => trim($organization ?? ''),
                'avatar' => $avatar
            ];
            return ['success' => true, 'user_id' => $userId];
        }

        return ['success' => false, 'error' => 'Failed to create account. Please try again.'];
    }

    /**
     * Terminate user session
     */
    public static function logout(): void {
        unset($_SESSION['user']);
        session_destroy();
    }

    /**
     * Require authentication guard
     */
    public static function requireLogin(): void {
        if (!is_logged_in()) {
            set_flash('warning', 'Please sign in to access this page.');
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }

    /**
     * Require organizer or admin guard
     */
    public static function requireOrganizer(): void {
        self::requireLogin();
        if (!is_organizer()) {
            set_flash('danger', 'Access denied. Organizer or Administrator privileges required.');
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }
}
