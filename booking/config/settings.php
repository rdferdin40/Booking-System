<?php
/**
 * Runtime Settings Loader
 * Loads system settings from database
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/../core/Database.php';

class Settings {
    private static $instance = null;
    private $settings = [];
    private $db;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->load();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load settings from database
     */
    private function load() {
        try {
            $sql = "SELECT * FROM settings LIMIT 1";
            $result = $this->db->queryOne($sql);

            if ($result) {
                $this->settings = $result;
            } else {
                // Default settings if database is empty
                $this->settings = [
                    'max_duration_minutes' => DEFAULT_MAX_DURATION,
                    'time_increment' => DEFAULT_TIME_INCREMENT,
                    'auto_release_mode' => 'instant',
                    'grace_period_minutes' => DEFAULT_GRACE_PERIOD,
                    'show_titles' => 1,
                    'screensaver_enabled' => 0,
                    'screensaver_timeout' => DEFAULT_SCREENSAVER_TIMEOUT,
                    'public_view_mode' => 'timeline',
                    'language' => DEFAULT_LANGUAGE,
                    'email_enabled' => 0,
                    'sms_enabled' => 0
                ];
            }
        } catch (Exception $e) {
            // Use defaults if database error
            $this->settings = [
                'max_duration_minutes' => DEFAULT_MAX_DURATION,
                'time_increment' => DEFAULT_TIME_INCREMENT,
                'auto_release_mode' => 'instant',
                'grace_period_minutes' => DEFAULT_GRACE_PERIOD,
                'show_titles' => 1,
                'screensaver_enabled' => 0,
                'screensaver_timeout' => DEFAULT_SCREENSAVER_TIMEOUT,
                'public_view_mode' => 'timeline',
                'language' => DEFAULT_LANGUAGE,
                'email_enabled' => 0,
                'sms_enabled' => 0
            ];
        }
    }

    /**
     * Get setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function getAll() {
        return $this->settings;
    }

    /**
     * Update setting
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function set($key, $value) {
        try {
            $sql = "UPDATE settings SET {$key} = ?, updated_at = NOW() WHERE id = 1";
            $this->db->execute($sql, [$value]);
            $this->settings[$key] = $value;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update multiple settings
     *
     * @param array $data Key-value pairs
     * @return bool
     */
    public function updateMultiple($data) {
        try {
            $fields = [];
            $values = [];

            foreach ($data as $key => $value) {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }

            $sql = "UPDATE settings SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = 1";
            $this->db->execute($sql, $values);

            // Update local cache
            foreach ($data as $key => $value) {
                $this->settings[$key] = $value;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Reload settings from database
     */
    public function reload() {
        $this->load();
    }

    /**
     * Check if email is enabled
     *
     * @return bool
     */
    public function isEmailEnabled() {
        return (bool) $this->get('email_enabled', 0);
    }

    /**
     * Check if SMS is enabled
     *
     * @return bool
     */
    public function isSMSEnabled() {
        return (bool) $this->get('sms_enabled', 0);
    }

    /**
     * Check if screensaver is enabled
     *
     * @return bool
     */
    public function isScreensaverEnabled() {
        return (bool) $this->get('screensaver_enabled', 0);
    }

    /**
     * Get max booking duration in minutes
     *
     * @return int
     */
    public function getMaxDuration() {
        return (int) $this->get('max_duration_minutes', DEFAULT_MAX_DURATION);
    }

    /**
     * Get time increment in minutes
     *
     * @return int
     */
    public function getTimeIncrement() {
        return (int) $this->get('time_increment', DEFAULT_TIME_INCREMENT);
    }

    /**
     * Get grace period in minutes
     *
     * @return int
     */
    public function getGracePeriod() {
        return (int) $this->get('grace_period_minutes', DEFAULT_GRACE_PERIOD);
    }

    /**
     * Get public view mode
     *
     * @return string timeline|calendar|list
     */
    public function getPublicViewMode() {
        return $this->get('public_view_mode', 'timeline');
    }

    /**
     * Get current language
     *
     * @return string
     */
    public function getLanguage() {
        return $this->get('language', DEFAULT_LANGUAGE);
    }

    /**
     * Check if titles should be shown on public display
     *
     * @return bool
     */
    public function shouldShowTitles() {
        return (bool) $this->get('show_titles', 1);
    }

    /**
     * Get auto-release mode
     *
     * @return string instant|grace
     */
    public function getAutoReleaseMode() {
        return $this->get('auto_release_mode', 'instant');
    }
}

// Helper function to get settings instance
function settings() {
    return Settings::getInstance();
}
