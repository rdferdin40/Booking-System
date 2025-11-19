<?php
/**
 * Admin Authentication Class
 * Handles admin user authentication and authorization
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/Database.php';

class AdminAuth {
    private $db;
    private static $sessionKey = 'admin_logged_in';
    private static $sessionUserId = 'admin_user_id';
    private static $sessionUsername = 'admin_username';

    public function __construct() {
        $this->db = Database::getInstance();
        $this->startSession();
    }

    /**
     * Start session if not already started
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate admin user
     *
     * @param string $username
     * @param string $password
     * @return array Result with success status and message
     */
    public function login($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'error' => 'Username and password are required'];
        }

        // Get user from database
        $sql = "SELECT * FROM admin_users WHERE username = ?";
        $user = $this->db->queryOne($sql, [$username]);

        if (!$user) {
            return ['success' => false, 'error' => 'Invalid username or password'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid username or password'];
        }

        // Set session variables
        $_SESSION[self::$sessionKey] = true;
        $_SESSION[self::$sessionUserId] = $user['id'];
        $_SESSION[self::$sessionUsername] = $user['username'];

        // Regenerate session ID for security
        session_regenerate_id(true);

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ];
    }

    /**
     * Logout admin user
     *
     * @return array Result
     */
    public function logout() {
        // Clear session variables
        unset($_SESSION[self::$sessionKey]);
        unset($_SESSION[self::$sessionUserId]);
        unset($_SESSION[self::$sessionUsername]);

        // Destroy session
        session_destroy();

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if admin is logged in
     *
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION[self::$sessionKey]) && $_SESSION[self::$sessionKey] === true;
    }

    /**
     * Get current admin user ID
     *
     * @return int|null
     */
    public function getUserId() {
        return $_SESSION[self::$sessionUserId] ?? null;
    }

    /**
     * Get current admin username
     *
     * @return string|null
     */
    public function getUsername() {
        return $_SESSION[self::$sessionUsername] ?? null;
    }

    /**
     * Require authentication (redirect if not logged in)
     *
     * @param string $redirectTo URL to redirect to if not authenticated
     */
    public function requireAuth($redirectTo = '/admin/login.php') {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    /**
     * Create new admin user
     *
     * @param string $username
     * @param string $password
     * @return array Result
     */
    public function createUser($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'error' => 'Username and password are required'];
        }

        if (strlen($username) < 3) {
            return ['success' => false, 'error' => 'Username must be at least 3 characters'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters'];
        }

        // Check if username already exists
        $sql = "SELECT id FROM admin_users WHERE username = ?";
        $existing = $this->db->queryOne($sql, [$username]);

        if ($existing) {
            return ['success' => false, 'error' => 'Username already exists'];
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $sql = "INSERT INTO admin_users (username, password_hash) VALUES (?, ?)";

        try {
            $this->db->execute($sql, [$username, $passwordHash]);
            $userId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Admin user created successfully',
                'user_id' => $userId
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create user: ' . $e->getMessage()];
        }
    }

    /**
     * Update admin user password
     *
     * @param int $userId
     * @param string $newPassword
     * @return array Result
     */
    public function updatePassword($userId, $newPassword) {
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters'];
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE admin_users SET password_hash = ? WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$passwordHash, $userId]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                return ['success' => false, 'error' => 'User not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update password: ' . $e->getMessage()];
        }
    }

    /**
     * Delete admin user
     *
     * @param int $userId
     * @return array Result
     */
    public function deleteUser($userId) {
        // Prevent deleting the last admin user
        $sql = "SELECT COUNT(*) as count FROM admin_users";
        $result = $this->db->queryOne($sql);

        if ($result['count'] <= 1) {
            return ['success' => false, 'error' => 'Cannot delete the last admin user'];
        }

        // Prevent deleting self
        if ($userId == $this->getUserId()) {
            return ['success' => false, 'error' => 'Cannot delete your own account'];
        }

        $sql = "DELETE FROM admin_users WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$userId]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Admin user deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'User not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete user: ' . $e->getMessage()];
        }
    }

    /**
     * Get all admin users
     *
     * @return array
     */
    public function getAllUsers() {
        $sql = "SELECT id, username, created_at FROM admin_users ORDER BY username ASC";
        return $this->db->query($sql);
    }

    /**
     * Get admin user by ID
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId) {
        $sql = "SELECT id, username, created_at FROM admin_users WHERE id = ?";
        return $this->db->queryOne($sql, [$userId]);
    }

    /**
     * Check if username is available
     *
     * @param string $username
     * @param int|null $excludeUserId Exclude this user ID (for updates)
     * @return bool
     */
    public function isUsernameAvailable($username, $excludeUserId = null) {
        $sql = "SELECT id FROM admin_users WHERE username = ? AND id != ?";
        $result = $this->db->queryOne($sql, [$username, $excludeUserId ?? 0]);
        return $result === null;
    }
}
