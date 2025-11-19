<?php
/**
 * Main Configuration File
 * Core application settings and constants
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/New_York');

// Application Constants
define('APP_NAME', 'Conference Room Booking System');
define('APP_VERSION', '1.0.0');

// Base URL Configuration
// IMPORTANT: Update this to match your installation path
// For local development: http://localhost/booking
// For production: https://yourdomain.com/booking
define('BASE_URL', 'http://localhost/booking');
define('BASE_PATH', dirname(__DIR__));

// Admin Path
define('ADMIN_URL', BASE_URL . '/admin');
define('PUBLIC_URL', BASE_URL . '/public');
define('ASSETS_URL', BASE_URL . '/assets');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_NAME', 'booking_admin_session');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Pagination
define('ITEMS_PER_PAGE', 20);

// File Upload Limits
define('MAX_UPLOAD_SIZE', 5242880); // 5MB

// Date/Time Formats
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_TIME_FORMAT', 'g:i A');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y g:i A');

// Business Hours (24-hour format)
define('BUSINESS_START_HOUR', 8);  // 8:00 AM
define('BUSINESS_END_HOUR', 18);   // 6:00 PM

// Default Settings (can be overridden in database)
define('DEFAULT_MAX_DURATION', 240);      // 4 hours
define('DEFAULT_TIME_INCREMENT', 15);     // 15 minutes
define('DEFAULT_GRACE_PERIOD', 0);        // No grace period
define('DEFAULT_SCREENSAVER_TIMEOUT', 300); // 5 minutes

// Supported Languages
define('SUPPORTED_LANGUAGES', ['en', 'es']);
define('DEFAULT_LANGUAGE', 'en');

// Color Palette for Categories
define('CATEGORY_COLORS', [
    '#3498db', // Blue
    '#2ecc71', // Green
    '#9b59b6', // Purple
    '#e74c3c', // Red
    '#f39c12', // Orange
    '#1abc9c', // Turquoise
    '#34495e', // Dark Blue
    '#95a5a6', // Gray
    '#e67e22', // Carrot
    '#16a085', // Dark Turquoise
]);

// Status Colors for Timeline
define('STATUS_AVAILABLE', '#2ecc71');   // Green
define('STATUS_BOOKED', '#e74c3c');      // Red
define('STATUS_GRACE', '#f39c12');       // Yellow/Orange
define('STATUS_PAST', '#95a5a6');        // Gray
define('STATUS_ADMIN_OVERRIDE', '#3498db'); // Blue

// Email Templates
define('EMAIL_TEMPLATE_PATH', BASE_PATH . '/templates/email');

// Log Configuration
define('LOG_ENABLED', true);
define('LOG_PATH', BASE_PATH . '/logs');
define('LOG_FILE', LOG_PATH . '/app.log');

// Backup Configuration
define('BACKUP_PATH', BASE_PATH . '/backups');

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper Functions

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get current URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get POST/GET parameter
 */
function input($key, $default = null) {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    if (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return $default;
}

/**
 * Flash message helper
 */
function setFlash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Log message
 */
function logMessage($message, $level = 'INFO') {
    if (!LOG_ENABLED) {
        return;
    }

    if (!file_exists(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
}
