<?php
/**
 * Reservation Service Class
 * Handles all reservation/booking business logic
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/TimeHelpers.php';
require_once __DIR__ . '/Validator.php';

class ReservationService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new reservation
     *
     * @param array $data Reservation data
     * @param bool $isAdminOverride Allow conflicts for admin
     * @return array Success status and message/data
     */
    public function create($data, $isAdminOverride = false) {
        // Validate input
        $validator = new Validator();
        $validator
            ->required('room_id', $data['room_id'] ?? '')
            ->required('full_name', $data['full_name'] ?? '')
            ->required('department', $data['department'] ?? '')
            ->required('category_id', $data['category_id'] ?? '')
            ->required('title', $data['title'] ?? '')
            ->required('start_time', $data['start_time'] ?? '')
            ->required('end_time', $data['end_time'] ?? '')
            ->email('email', $data['email'] ?? '')
            ->phone('phone', $data['phone'] ?? '');

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }

        // Sanitize data
        $roomId = (int) $data['room_id'];
        $fullName = Validator::sanitizeString($data['full_name']);
        $department = Validator::sanitizeString($data['department']);
        $categoryId = (int) $data['category_id'];
        $title = Validator::sanitizeString($data['title']);
        $email = !empty($data['email']) ? Validator::sanitizeEmail($data['email']) : null;
        $phone = !empty($data['phone']) ? Validator::sanitizeString($data['phone']) : null;
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        // Check if start time is in the past
        if (TimeHelpers::isPast($startTime)) {
            return ['success' => false, 'error' => 'Cannot book in the past'];
        }

        // Check if end time is after start time
        if (strtotime($endTime) <= strtotime($startTime)) {
            return ['success' => false, 'error' => 'End time must be after start time'];
        }

        // Check duration limit
        $settings = $this->getSettings();
        $durationMinutes = TimeHelpers::getDurationMinutes($startTime, $endTime);

        if ($durationMinutes > $settings['max_duration_minutes']) {
            $maxDuration = TimeHelpers::formatDuration($settings['max_duration_minutes']);
            return ['success' => false, 'error' => "Maximum booking duration is {$maxDuration}"];
        }

        // Check for conflicts (unless admin override)
        if (!$isAdminOverride) {
            if ($this->hasConflict($roomId, $startTime, $endTime)) {
                return ['success' => false, 'error' => 'Time slot conflicts with existing reservation'];
            }
        }

        // Generate unique PIN
        $pinCode = $this->generateUniquePin();

        // Insert reservation
        $sql = "INSERT INTO reservations (
            room_id, full_name, department, category_id, title,
            email, phone, start_time, end_time, pin_code, is_admin_override
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [
                $roomId,
                $fullName,
                $department,
                $categoryId,
                $title,
                $email,
                $phone,
                $startTime,
                $endTime,
                $pinCode,
                $isAdminOverride ? 1 : 0
            ]);

            $reservationId = $this->db->lastInsertId();

            // Get full reservation details
            $reservation = $this->getById($reservationId);

            return [
                'success' => true,
                'message' => 'Reservation created successfully',
                'reservation' => $reservation
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create reservation: ' . $e->getMessage()];
        }
    }

    /**
     * Update existing reservation
     *
     * @param int $id Reservation ID
     * @param array $data Updated data
     * @param bool $isAdminOverride Allow conflicts for admin
     * @return array Success status and message
     */
    public function update($id, $data, $isAdminOverride = false) {
        // Validate input
        $validator = new Validator();
        $validator
            ->required('start_time', $data['start_time'] ?? '')
            ->required('end_time', $data['end_time'] ?? '')
            ->email('email', $data['email'] ?? '')
            ->phone('phone', $data['phone'] ?? '');

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }

        $reservation = $this->getById($id);
        if (!$reservation) {
            return ['success' => false, 'error' => 'Reservation not found'];
        }

        // Check if start time is in the past
        if (TimeHelpers::isPast($data['start_time'])) {
            return ['success' => false, 'error' => 'Cannot update to past time'];
        }

        // Check duration limit
        $settings = $this->getSettings();
        $durationMinutes = TimeHelpers::getDurationMinutes($data['start_time'], $data['end_time']);

        if ($durationMinutes > $settings['max_duration_minutes']) {
            $maxDuration = TimeHelpers::formatDuration($settings['max_duration_minutes']);
            return ['success' => false, 'error' => "Maximum booking duration is {$maxDuration}"];
        }

        // Check for conflicts (excluding this reservation)
        if (!$isAdminOverride) {
            if ($this->hasConflict($reservation['room_id'], $data['start_time'], $data['end_time'], $id)) {
                return ['success' => false, 'error' => 'Time slot conflicts with existing reservation'];
            }
        }

        // Update reservation
        $sql = "UPDATE reservations SET
            start_time = ?,
            end_time = ?,
            title = ?,
            email = ?,
            phone = ?,
            updated_at = NOW()
            WHERE id = ?";

        try {
            $this->db->execute($sql, [
                $data['start_time'],
                $data['end_time'],
                Validator::sanitizeString($data['title'] ?? $reservation['title']),
                !empty($data['email']) ? Validator::sanitizeEmail($data['email']) : null,
                !empty($data['phone']) ? Validator::sanitizeString($data['phone']) : null,
                $id
            ]);

            return [
                'success' => true,
                'message' => 'Reservation updated successfully',
                'reservation' => $this->getById($id)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update reservation: ' . $e->getMessage()];
        }
    }

    /**
     * Cancel/delete reservation
     *
     * @param int $id Reservation ID
     * @return array Success status and message
     */
    public function cancel($id) {
        $sql = "DELETE FROM reservations WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Reservation cancelled successfully'];
            } else {
                return ['success' => false, 'error' => 'Reservation not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to cancel reservation: ' . $e->getMessage()];
        }
    }

    /**
     * Get reservation by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $sql = "SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
                FROM reservations r
                INNER JOIN rooms rm ON r.room_id = rm.id
                INNER JOIN categories c ON r.category_id = c.id
                WHERE r.id = ?";

        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get reservation by PIN code
     *
     * @param string $pin
     * @return array|null
     */
    public function getByPin($pin) {
        $sql = "SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
                FROM reservations r
                INNER JOIN rooms rm ON r.room_id = rm.id
                INNER JOIN categories c ON r.category_id = c.id
                WHERE r.pin_code = ?";

        return $this->db->queryOne($sql, [$pin]);
    }

    /**
     * Find reservations by email or phone
     *
     * @param string $emailOrPhone
     * @return array
     */
    public function findByContact($emailOrPhone) {
        $sql = "SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
                FROM reservations r
                INNER JOIN rooms rm ON r.room_id = rm.id
                INNER JOIN categories c ON r.category_id = c.id
                WHERE (r.email = ? OR r.phone = ?)
                AND r.end_time >= NOW()
                ORDER BY r.start_time ASC";

        return $this->db->query($sql, [$emailOrPhone, $emailOrPhone]);
    }

    /**
     * Get reservations for a room and date range
     *
     * @param int $roomId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByRoomAndDateRange($roomId, $startDate, $endDate = null) {
        $endDate = $endDate ?? $startDate;

        $sql = "SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
                FROM reservations r
                INNER JOIN rooms rm ON r.room_id = rm.id
                INNER JOIN categories c ON r.category_id = c.id
                WHERE r.room_id = ?
                AND DATE(r.start_time) >= ?
                AND DATE(r.end_time) <= ?
                ORDER BY r.start_time ASC";

        return $this->db->query($sql, [$roomId, $startDate, $endDate]);
    }

    /**
     * Get all upcoming reservations
     *
     * @param int $limit
     * @return array
     */
    public function getUpcoming($limit = 50) {
        $sql = "SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
                FROM reservations r
                INNER JOIN rooms rm ON r.room_id = rm.id
                INNER JOIN categories c ON r.category_id = c.id
                WHERE r.start_time >= NOW()
                ORDER BY r.start_time ASC
                LIMIT ?";

        return $this->db->query($sql, [$limit]);
    }

    /**
     * Get all reservations with filters
     *
     * @param array $filters
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
                FROM reservations r
                INNER JOIN rooms rm ON r.room_id = rm.id
                INNER JOIN categories c ON r.category_id = c.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['room_id'])) {
            $sql .= " AND r.room_id = ?";
            $params[] = $filters['room_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND r.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (r.full_name LIKE ? OR r.email LIKE ? OR r.phone LIKE ? OR r.title LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($filters['date'])) {
            $sql .= " AND DATE(r.start_time) = ?";
            $params[] = $filters['date'];
        }

        $sql .= " ORDER BY r.start_time DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int) $filters['limit'];
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Check for booking conflicts
     *
     * @param int $roomId
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeId Exclude this reservation ID
     * @return bool
     */
    public function hasConflict($roomId, $startTime, $endTime, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM reservations
                WHERE room_id = ?
                AND id != ?
                AND (
                    (start_time < ? AND end_time > ?)
                )";

        $result = $this->db->queryOne($sql, [
            $roomId,
            $excludeId ?? 0,
            $endTime,
            $startTime
        ]);

        return $result['count'] > 0;
    }

    /**
     * Generate unique PIN code
     *
     * @return string
     */
    private function generateUniquePin() {
        do {
            $pin = TimeHelpers::generatePin();
            $exists = $this->getByPin($pin);
        } while ($exists);

        return $pin;
    }

    /**
     * Get system settings
     *
     * @return array
     */
    private function getSettings() {
        $sql = "SELECT * FROM settings LIMIT 1";
        $settings = $this->db->queryOne($sql);

        return $settings ?: [
            'max_duration_minutes' => 240,
            'time_increment' => 15,
            'auto_release_mode' => 'instant',
            'grace_period_minutes' => 0
        ];
    }

    /**
     * Clean up past reservations
     *
     * @param int $daysOld Delete reservations older than X days
     * @return int Number of deleted reservations
     */
    public function cleanupOld($daysOld = 90) {
        $sql = "DELETE FROM reservations WHERE end_time < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->execute($sql, [$daysOld]);
    }
}
