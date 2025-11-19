<?php
/**
 * Validation Helper Class
 * Provides validation methods for user input
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

class Validator {
    private $errors = [];

    /**
     * Check if field is required and not empty
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $message Error message
     * @return Validator
     */
    public function required($field, $value, $message = null) {
        if (empty($value) && $value !== '0') {
            $this->errors[$field] = $message ?? "{$field} is required";
        }
        return $this;
    }

    /**
     * Validate email format
     *
     * @param string $field Field name
     * @param string $value Email value
     * @param string $message Error message
     * @return Validator
     */
    public function email($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Invalid email format";
        }
        return $this;
    }

    /**
     * Validate minimum length
     *
     * @param string $field Field name
     * @param string $value Field value
     * @param int $min Minimum length
     * @param string $message Error message
     * @return Validator
     */
    public function minLength($field, $value, $min, $message = null) {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = $message ?? "{$field} must be at least {$min} characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     *
     * @param string $field Field name
     * @param string $value Field value
     * @param int $max Maximum length
     * @param string $message Error message
     * @return Validator
     */
    public function maxLength($field, $value, $max, $message = null) {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = $message ?? "{$field} must not exceed {$max} characters";
        }
        return $this;
    }

    /**
     * Validate phone number format
     *
     * @param string $field Field name
     * @param string $value Phone value
     * @param string $message Error message
     * @return Validator
     */
    public function phone($field, $value, $message = null) {
        if (!empty($value)) {
            // Remove common formatting characters
            $cleaned = preg_replace('/[\s\-\(\)\.]/', '', $value);
            // Check if it's a valid phone number (digits only, 10-15 chars)
            if (!preg_match('/^[\+]?[0-9]{10,15}$/', $cleaned)) {
                $this->errors[$field] = $message ?? "Invalid phone number format";
            }
        }
        return $this;
    }

    /**
     * Validate datetime format
     *
     * @param string $field Field name
     * @param string $value Datetime value
     * @param string $message Error message
     * @return Validator
     */
    public function datetime($field, $value, $message = null) {
        if (!empty($value)) {
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
            if (!$dt || $dt->format('Y-m-d H:i:s') !== $value) {
                $this->errors[$field] = $message ?? "Invalid datetime format";
            }
        }
        return $this;
    }

    /**
     * Validate that value is in array
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array $options Valid options
     * @param string $message Error message
     * @return Validator
     */
    public function in($field, $value, $options, $message = null) {
        if (!empty($value) && !in_array($value, $options)) {
            $this->errors[$field] = $message ?? "{$field} must be one of: " . implode(', ', $options);
        }
        return $this;
    }

    /**
     * Validate numeric value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $message Error message
     * @return Validator
     */
    public function numeric($field, $value, $message = null) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message ?? "{$field} must be a number";
        }
        return $this;
    }

    /**
     * Validate integer value
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $message Error message
     * @return Validator
     */
    public function integer($field, $value, $message = null) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $message ?? "{$field} must be an integer";
        }
        return $this;
    }

    /**
     * Validate minimum value
     *
     * @param string $field Field name
     * @param numeric $value Field value
     * @param numeric $min Minimum value
     * @param string $message Error message
     * @return Validator
     */
    public function min($field, $value, $min, $message = null) {
        if (!empty($value) && $value < $min) {
            $this->errors[$field] = $message ?? "{$field} must be at least {$min}";
        }
        return $this;
    }

    /**
     * Validate maximum value
     *
     * @param string $field Field name
     * @param numeric $value Field value
     * @param numeric $max Maximum value
     * @param string $message Error message
     * @return Validator
     */
    public function max($field, $value, $max, $message = null) {
        if (!empty($value) && $value > $max) {
            $this->errors[$field] = $message ?? "{$field} must not exceed {$max}";
        }
        return $this;
    }

    /**
     * Custom validation with callback
     *
     * @param string $field Field name
     * @param mixed $value Field value
     * @param callable $callback Validation function
     * @param string $message Error message
     * @return Validator
     */
    public function custom($field, $value, $callback, $message = null) {
        if (!$callback($value)) {
            $this->errors[$field] = $message ?? "{$field} validation failed";
        }
        return $this;
    }

    /**
     * Check if validation passed
     *
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     *
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error
     *
     * @return string|null
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Add custom error
     *
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function addError($field, $message) {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Clear all errors
     *
     * @return Validator
     */
    public function clearErrors() {
        $this->errors = [];
        return $this;
    }

    /**
     * Sanitize string input
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeString($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email input
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeEmail($value) {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }
}
