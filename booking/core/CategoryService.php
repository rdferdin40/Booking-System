<?php
/**
 * Category Service Class
 * Handles booking category management
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';

class CategoryService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new category
     *
     * @param array $data Category data
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
        $color = !empty($data['color']) ? $data['color'] : '#3498db';

        // Validate color format (hex)
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            return ['success' => false, 'error' => 'Invalid color format'];
        }

        $sql = "INSERT INTO categories (name, color) VALUES (?, ?)";

        try {
            $this->db->execute($sql, [$name, $color]);
            $categoryId = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $this->getById($categoryId)
            ];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'unique_name') !== false) {
                return ['success' => false, 'error' => 'Category name already exists'];
            }
            return ['success' => false, 'error' => 'Failed to create category: ' . $e->getMessage()];
        }
    }

    /**
     * Update existing category
     *
     * @param int $id Category ID
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
        $color = !empty($data['color']) ? $data['color'] : '#3498db';

        // Validate color format (hex)
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            return ['success' => false, 'error' => 'Invalid color format'];
        }

        $sql = "UPDATE categories SET
                name = ?,
                color = ?,
                updated_at = NOW()
                WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$name, $color, $id]);

            if ($affected > 0 || $this->getById($id)) {
                return [
                    'success' => true,
                    'message' => 'Category updated successfully',
                    'category' => $this->getById($id)
                ];
            } else {
                return ['success' => false, 'error' => 'Category not found'];
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'unique_name') !== false) {
                return ['success' => false, 'error' => 'Category name already exists'];
            }
            return ['success' => false, 'error' => 'Failed to update category: ' . $e->getMessage()];
        }
    }

    /**
     * Delete category
     *
     * @param int $id Category ID
     * @return array Success status and message
     */
    public function delete($id) {
        // Check if category is being used
        $sql = "SELECT COUNT(*) as count FROM reservations WHERE category_id = ?";
        $result = $this->db->queryOne($sql, [$id]);

        if ($result['count'] > 0) {
            return ['success' => false, 'error' => 'Cannot delete category that is being used in reservations'];
        }

        $sql = "DELETE FROM categories WHERE id = ?";

        try {
            $affected = $this->db->execute($sql, [$id]);

            if ($affected > 0) {
                return ['success' => true, 'message' => 'Category deleted successfully'];
            } else {
                return ['success' => false, 'error' => 'Category not found'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete category: ' . $e->getMessage()];
        }
    }

    /**
     * Get category by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function getAll() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return $this->db->query($sql);
    }

    /**
     * Get category usage statistics
     *
     * @param int $id Category ID
     * @return array
     */
    public function getStats($id) {
        // Total bookings
        $sql = "SELECT COUNT(*) as total FROM reservations WHERE category_id = ?";
        $total = $this->db->queryOne($sql, [$id]);

        // Upcoming bookings
        $sql = "SELECT COUNT(*) as upcoming FROM reservations WHERE category_id = ? AND start_time >= NOW()";
        $upcoming = $this->db->queryOne($sql, [$id]);

        return [
            'total_bookings' => $total['total'] ?? 0,
            'upcoming_bookings' => $upcoming['upcoming'] ?? 0
        ];
    }
}
