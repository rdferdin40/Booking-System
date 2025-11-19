<?php
/**
 * Room Service Class
 * Handles room management operations
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';

class RoomService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new room
     *
     * @param array $data Room data
     * @return array Success status and message/data
     */
    public function create($data) {
        $validator = new Validator();
        $validator
            ->required('name', $data['name'] ?? '')
            ->maxLength('name', $data['name'] ?? '', 255);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }

        $name = Validator::sanitizeString($data['name']);
        $description = !empty($data['description']) ? Validator::sanitizeString($data['description']) : null;
        $isActive = isset($data['is_active']) ? (int) $data['is_active'] : 1;
        $isLocked = isset($data['is_locked']) ? (int) $data['is_locked'] : 0;
        $viewMode = !empty($data['view_mode']) ? $data['view_mode'] : 'timeline';

        $sql = "INSERT INTO rooms (name, description, is_active, is_locked, view_mode)
                VALUES (?, ?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [$name, $description, $isActive, $isLocked, $viewMode]);
            $roomId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Room created successfully',
                'room' => $this->getById($roomId)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to create room: ' . $e->getMessage()];
        }
    }

    /**
     * Update existing room
     *
     * @param int $id Room ID
     * @param array $data Updated data
     * @return array Success status and message
     */
    public function update($id, $data) {
        $validator = new Validator();
        $validator
            ->required('name', $data['name'] ?? '')
            ->maxLength('name', $data['name'] ?? '', 255);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }

        $name = Validator::sanitizeString($data['name']);
        $description = !empty($data['description']) ? Validator::sanitizeString($data['description']) : null;
        $isActive = isset($data['is_active']) ? (int) $data['is_active'] : 1;
        $isLocked = isset($data['is_locked']) ? (int) $data['is_locked'] : 0;
        $viewMode = !empty($data['view_mode']) ? $data['view_mode'] : 'timeline';

        $sql = "UPDATE rooms SET
                name = ?,
                description = ?,
                is_active = ?,
                is_locked = ?,
                view_mode = ?,
                updated_at = NOW()
                WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$name, $description, $isActive, $isLocked, $viewMode, $id]);

            if ($affected > 0 || $this->getById($id)) {
                return [
                    'success' => true,
                    'message' => 'Room updated successfully',
                    'room' => $this->getById($id)
                ];
            } else {
                return ['success' => false, 'error' => 'Room not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update room: ' . $e->getMessage()];
        }
    }

    /**
     * Delete room
     *
     * @param int $id Room ID
     * @return array Success status and message
     */
    public function delete($id) {
        // Check if room has any reservations
        $sql = "SELECT COUNT(*) as count FROM reservations WHERE room_id = ?";
        $result = $this->db->queryOne($sql, [$id]);

        if ($result['count'] > 0) {
            return ['success' => false, 'error' => 'Cannot delete room with existing reservations'];
        }

        $sql = "DELETE FROM rooms WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Room deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'Room not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete room: ' . $e->getMessage()];
        }
    }

    /**
     * Get room by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $sql = "SELECT * FROM rooms WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get all rooms
     *
     * @param bool $activeOnly Get only active rooms
     * @return array
     */
    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM rooms";

        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }

        $sql .= " ORDER BY name ASC";

        return $this->db->query($sql);
    }

    /**
     * Activate room
     *
     * @param int $id Room ID
     * @return array Success status and message
     */
    public function activate($id) {
        $sql = "UPDATE rooms SET is_active = 1, updated_at = NOW() WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Room activated successfully'];
            } else {
                return ['success' => false, 'error' => 'Room not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to activate room: ' . $e->getMessage()];
        }
    }

    /**
     * Deactivate room
     *
     * @param int $id Room ID
     * @return array Success status and message
     */
    public function deactivate($id) {
        $sql = "UPDATE rooms SET is_active = 0, updated_at = NOW() WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Room deactivated successfully'];
            } else {
                return ['success' => false, 'error' => 'Room not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to deactivate room: ' . $e->getMessage()];
        }
    }

    /**
     * Lock room (prevent new bookings)
     *
     * @param int $id Room ID
     * @return array Success status and message
     */
    public function lock($id) {
        $sql = "UPDATE rooms SET is_locked = 1, updated_at = NOW() WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Room locked successfully'];
            } else {
                return ['success' => false, 'error' => 'Room not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to lock room: ' . $e->getMessage()];
        }
    }

    /**
     * Unlock room
     *
     * @param int $id Room ID
     * @return array Success status and message
     */
    public function unlock($id) {
        $sql = "UPDATE rooms SET is_locked = 0, updated_at = NOW() WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Room unlocked successfully'];
            } else {
                return ['success' => false, 'error' => 'Room not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to unlock room: ' . $e->getMessage()];
        }
    }

    /**
     * Get room statistics
     *
     * @param int $id Room ID
     * @return array
     */
    public function getStats($id) {
        // Total bookings
        $sql = "SELECT COUNT(*) as total_bookings FROM reservations WHERE room_id = ?";
        $totalBookings = $this->db->queryOne($sql, [$id]);

        // Upcoming bookings
        $sql = "SELECT COUNT(*) as upcoming FROM reservations WHERE room_id = ? AND start_time >= NOW()";
        $upcoming = $this->db->queryOne($sql, [$id]);

        // Today's bookings
        $sql = "SELECT COUNT(*) as today FROM reservations WHERE room_id = ? AND DATE(start_time) = CURDATE()";
        $today = $this->db->queryOne($sql, [$id]);

        // Current booking
        $sql = "SELECT * FROM reservations WHERE room_id = ? AND start_time <= NOW() AND end_time >= NOW() LIMIT 1";
        $current = $this->db->queryOne($sql, [$id]);

        return [
            'total_bookings' => $totalBookings['total_bookings'] ?? 0,
            'upcoming_bookings' => $upcoming['upcoming'] ?? 0,
            'todays_bookings' => $today['today'] ?? 0,
            'current_booking' => $current
        ];
    }

    /**
     * Check if room is available at given time
     *
     * @param int $id Room ID
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public function isAvailable($id, $startTime, $endTime) {
        $sql = "SELECT COUNT(*) as count FROM reservations
                WHERE room_id = ?
                AND (
                    (start_time < ? AND end_time > ?)
                )";

        $result = $this->db->queryOne($sql, [$id, $endTime, $startTime]);
        return $result['count'] == 0;
    }

    /**
     * Get available time slots for a room on a specific date
     *
     * @param int $id Room ID
     * @param string $date Date in Y-m-d format
     * @param int $increment Minutes increment
     * @return array
     */
    public function getAvailableSlots($id, $date, $increment = 15) {
        require_once __DIR__ . '/TimeHelpers.php';

        // Get all time slots for the day
        $allSlots = TimeHelpers::getBusinessHoursSlots($date, $increment);

        // Get bookings for this room on this date
        $sql = "SELECT start_time, end_time FROM reservations
                WHERE room_id = ? AND DATE(start_time) = ?
                ORDER BY start_time ASC";

        $bookings = $this->db->query($sql, [$id, $date]);

        $availableSlots = [];

        foreach ($allSlots as $slot) {
            $isAvailable = true;

            // Check if slot conflicts with any booking
            foreach ($bookings as $booking) {
                if (TimeHelpers::hasOverlap($slot, TimeHelpers::addMinutes($slot, $increment), $booking['start_time'], $booking['end_time'])) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableSlots[] = $slot;
            }
        }

        return $availableSlots;
    }
}
