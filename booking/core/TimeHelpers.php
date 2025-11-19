<?php
/**
 * Time Helper Class
 * Utility functions for time/date operations
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

class TimeHelpers {
    /**
     * Round time to nearest increment
     *
     * @param DateTime $datetime
     * @param int $increment Minutes increment (15, 30, etc.)
     * @return DateTime
     */
    public static function roundToIncrement($datetime, $increment = 15) {
        $minutes = $datetime->format('i');
        $rounded = round($minutes / $increment) * $increment;

        $datetime->setTime(
            $datetime->format('H'),
            $rounded,
            0
        );

        return $datetime;
    }

    /**
     * Get time slots for a day
     *
     * @param string $date Date in Y-m-d format
     * @param int $increment Minutes increment
     * @param string $startTime Start time (H:i)
     * @param string $endTime End time (H:i)
     * @return array
     */
    public static function getTimeSlots($date, $increment = 15, $startTime = '00:00', $endTime = '23:59') {
        $slots = [];
        $start = new DateTime("{$date} {$startTime}");
        $end = new DateTime("{$date} {$endTime}");

        while ($start <= $end) {
            $slots[] = $start->format('Y-m-d H:i:s');
            $start->modify("+{$increment} minutes");
        }

        return $slots;
    }

    /**
     * Check if time is in the past
     *
     * @param string $datetime
     * @return bool
     */
    public static function isPast($datetime) {
        $dt = new DateTime($datetime);
        $now = new DateTime();
        return $dt < $now;
    }

    /**
     * Check if time is in the future
     *
     * @param string $datetime
     * @return bool
     */
    public static function isFuture($datetime) {
        $dt = new DateTime($datetime);
        $now = new DateTime();
        return $dt > $now;
    }

    /**
     * Check if time is now (current)
     *
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    public static function isCurrent($startTime, $endTime) {
        $now = new DateTime();
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);

        return $now >= $start && $now <= $end;
    }

    /**
     * Get duration in minutes
     *
     * @param string $startTime
     * @param string $endTime
     * @return int
     */
    public static function getDurationMinutes($startTime, $endTime) {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $diff = $start->diff($end);

        return ($diff->h * 60) + $diff->i;
    }

    /**
     * Format duration for display
     *
     * @param int $minutes
     * @return string
     */
    public static function formatDuration($minutes) {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    /**
     * Check if two time ranges overlap
     *
     * @param string $start1
     * @param string $end1
     * @param string $start2
     * @param string $end2
     * @return bool
     */
    public static function hasOverlap($start1, $end1, $start2, $end2) {
        $s1 = new DateTime($start1);
        $e1 = new DateTime($end1);
        $s2 = new DateTime($start2);
        $e2 = new DateTime($end2);

        return ($s1 < $e2) && ($e1 > $s2);
    }

    /**
     * Add minutes to datetime
     *
     * @param string $datetime
     * @param int $minutes
     * @return string
     */
    public static function addMinutes($datetime, $minutes) {
        $dt = new DateTime($datetime);
        $dt->modify("+{$minutes} minutes");
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Subtract minutes from datetime
     *
     * @param string $datetime
     * @param int $minutes
     * @return string
     */
    public static function subtractMinutes($datetime, $minutes) {
        $dt = new DateTime($datetime);
        $dt->modify("-{$minutes} minutes");
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Get current datetime in MySQL format
     *
     * @return string
     */
    public static function now() {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get current date
     *
     * @return string
     */
    public static function today() {
        return date('Y-m-d');
    }

    /**
     * Format datetime for display
     *
     * @param string $datetime
     * @param string $format
     * @return string
     */
    public static function format($datetime, $format = 'M d, Y g:i A') {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    }

    /**
     * Get time only from datetime
     *
     * @param string $datetime
     * @return string
     */
    public static function getTimeOnly($datetime) {
        $dt = new DateTime($datetime);
        return $dt->format('H:i');
    }

    /**
     * Get date only from datetime
     *
     * @param string $datetime
     * @return string
     */
    public static function getDateOnly($datetime) {
        $dt = new DateTime($datetime);
        return $dt->format('Y-m-d');
    }

    /**
     * Get day name from date
     *
     * @param string $date
     * @return string
     */
    public static function getDayName($date) {
        $dt = new DateTime($date);
        return $dt->format('l');
    }

    /**
     * Check if date is today
     *
     * @param string $date
     * @return bool
     */
    public static function isToday($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d');
    }

    /**
     * Check if date is tomorrow
     *
     * @param string $date
     * @return bool
     */
    public static function isTomorrow($date) {
        return date('Y-m-d', strtotime($date)) === date('Y-m-d', strtotime('+1 day'));
    }

    /**
     * Get relative time (e.g., "2 hours ago", "in 3 days")
     *
     * @param string $datetime
     * @return string
     */
    public static function getRelativeTime($datetime) {
        $dt = new DateTime($datetime);
        $now = new DateTime();
        $diff = $now->diff($dt);

        if ($diff->invert) {
            // Past
            if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
            if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
            if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
            if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            return 'Just now';
        } else {
            // Future
            if ($diff->y > 0) return 'in ' . $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
            if ($diff->m > 0) return 'in ' . $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
            if ($diff->d > 0) return 'in ' . $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
            if ($diff->h > 0) return 'in ' . $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
            if ($diff->i > 0) return 'in ' . $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
            return 'Now';
        }
    }

    /**
     * Generate PIN code (6 digits)
     *
     * @return string
     */
    public static function generatePin() {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parse user-friendly date input
     *
     * @param string $input
     * @return string|null Y-m-d format or null
     */
    public static function parseDate($input) {
        $input = strtolower(trim($input));

        if ($input === 'today') {
            return date('Y-m-d');
        }
        if ($input === 'tomorrow') {
            return date('Y-m-d', strtotime('+1 day'));
        }
        if ($input === 'yesterday') {
            return date('Y-m-d', strtotime('-1 day'));
        }

        $timestamp = strtotime($input);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Get business hours slots
     *
     * @param string $date
     * @param int $increment
     * @return array
     */
    public static function getBusinessHoursSlots($date, $increment = 15) {
        return self::getTimeSlots($date, $increment, '08:00', '18:00');
    }
}
