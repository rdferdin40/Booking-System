-- ============================================================
-- CONFERENCE ROOM BOOKING SYSTEM - DATABASE SCHEMA
-- Phase 1 - Production Ready
-- MySQL 5.7+ / MariaDB 10.2+
-- ============================================================

-- Drop existing tables (in reverse order of dependencies)
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS notification_settings;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS admin_users;

-- ============================================================
-- TABLE: rooms
-- Stores conference room information
-- ============================================================
CREATE TABLE rooms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_locked TINYINT(1) NOT NULL DEFAULT 0,
    view_mode ENUM('timeline', 'calendar', 'list') NOT NULL DEFAULT 'timeline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_locked (is_locked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: categories
-- Booking categories with color coding
-- ============================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#3498db',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: reservations
-- Core reservation/booking data
-- ============================================================
CREATE TABLE reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id INT UNSIGNED NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(500) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    pin_code VARCHAR(6) NOT NULL,
    is_admin_override TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_room_time (room_id, start_time, end_time),
    INDEX idx_pin (pin_code),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_start_time (start_time),
    INDEX idx_end_time (end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: settings
-- Global system settings (single-row configuration)
-- ============================================================
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    max_duration_minutes INT NOT NULL DEFAULT 240,
    time_increment INT NOT NULL DEFAULT 15,
    auto_release_mode ENUM('instant', 'grace') NOT NULL DEFAULT 'instant',
    grace_period_minutes INT NOT NULL DEFAULT 0,
    show_titles TINYINT(1) NOT NULL DEFAULT 1,
    screensaver_enabled TINYINT(1) NOT NULL DEFAULT 0,
    screensaver_timeout INT NOT NULL DEFAULT 300,
    public_view_mode ENUM('timeline', 'calendar', 'list') NOT NULL DEFAULT 'timeline',
    language ENUM('en', 'es') NOT NULL DEFAULT 'en',
    email_enabled TINYINT(1) NOT NULL DEFAULT 0,
    sms_enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: admin_users
-- Admin authentication
-- ============================================================
CREATE TABLE admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notification_settings
-- Email and SMS notification configuration
-- ============================================================
CREATE TABLE notification_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email_from VARCHAR(255) NULL,
    smtp_host VARCHAR(255) NULL,
    smtp_username VARCHAR(255) NULL,
    smtp_password VARCHAR(255) NULL,
    smtp_port INT NULL DEFAULT 587,
    smtp_encryption VARCHAR(10) NULL DEFAULT 'tls',
    sms_api_url VARCHAR(500) NULL,
    sms_api_key VARCHAR(255) NULL,
    sms_method ENUM('POST', 'GET') DEFAULT 'POST',
    sms_phone_field VARCHAR(100) DEFAULT 'to',
    sms_message_field VARCHAR(100) DEFAULT 'message',
    send_on_create TINYINT(1) NOT NULL DEFAULT 1,
    send_on_update TINYINT(1) NOT NULL DEFAULT 1,
    send_on_cancel TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INITIAL DATA
-- ============================================================

-- Default admin user (username: admin, password: admin123)
-- IMPORTANT: Change this password immediately after installation!
INSERT INTO admin_users (username, password_hash) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Default settings
INSERT INTO settings (
    max_duration_minutes,
    time_increment,
    auto_release_mode,
    grace_period_minutes,
    show_titles,
    screensaver_enabled,
    screensaver_timeout,
    public_view_mode,
    language,
    email_enabled,
    sms_enabled
) VALUES (
    240,        -- 4 hours max
    15,         -- 15 minute increments
    'instant',  -- instant release mode
    0,          -- no grace period by default
    1,          -- show titles
    0,          -- screensaver disabled by default
    300,        -- 5 minutes timeout
    'timeline', -- timeline view as default
    'en',       -- English
    0,          -- email disabled until configured
    0           -- SMS disabled until configured
);

-- Default notification settings (empty, will be configured by admin)
INSERT INTO notification_settings (
    send_on_create,
    send_on_update,
    send_on_cancel
) VALUES (1, 1, 1);

-- Sample categories
INSERT INTO categories (name, color) VALUES
('Meeting', '#3498db'),
('Training', '#2ecc71'),
('Interview', '#9b59b6'),
('Conference Call', '#e74c3c'),
('Workshop', '#f39c12'),
('Team Sync', '#1abc9c'),
('Client Meeting', '#34495e'),
('Other', '#95a5a6');

-- Sample rooms (you can add your actual rooms here)
INSERT INTO rooms (name, description, is_active, is_locked, view_mode) VALUES
('Conference Room A', 'Main conference room with projector and video conferencing', 1, 0, 'timeline'),
('Conference Room B', 'Small meeting room for up to 6 people', 1, 0, 'timeline'),
('Board Room', 'Executive board room with premium AV equipment', 1, 0, 'timeline');

-- ============================================================
-- VIEWS (Optional - for reporting)
-- ============================================================

-- View for active reservations with room and category details
CREATE OR REPLACE VIEW active_reservations AS
SELECT
    r.id,
    r.full_name,
    r.department,
    r.title,
    r.email,
    r.phone,
    r.start_time,
    r.end_time,
    r.pin_code,
    r.is_admin_override,
    r.created_at,
    rm.name AS room_name,
    rm.id AS room_id,
    c.name AS category_name,
    c.color AS category_color
FROM reservations r
INNER JOIN rooms rm ON r.room_id = rm.id
INNER JOIN categories c ON r.category_id = c.id
WHERE r.end_time >= NOW()
ORDER BY r.start_time ASC;

-- View for today's reservations
CREATE OR REPLACE VIEW todays_reservations AS
SELECT
    r.id,
    r.full_name,
    r.department,
    r.title,
    r.email,
    r.phone,
    r.start_time,
    r.end_time,
    r.pin_code,
    r.is_admin_override,
    rm.name AS room_name,
    rm.id AS room_id,
    c.name AS category_name,
    c.color AS category_color
FROM reservations r
INNER JOIN rooms rm ON r.room_id = rm.id
INNER JOIN categories c ON r.category_id = c.id
WHERE DATE(r.start_time) = CURDATE()
ORDER BY r.start_time ASC;

-- ============================================================
-- STORED PROCEDURES (Optional - for complex operations)
-- ============================================================

DELIMITER $$

-- Check for booking conflicts
CREATE PROCEDURE check_conflict(
    IN p_room_id INT,
    IN p_start_time DATETIME,
    IN p_end_time DATETIME,
    IN p_exclude_id INT
)
BEGIN
    SELECT COUNT(*) AS conflict_count
    FROM reservations
    WHERE room_id = p_room_id
    AND id != IFNULL(p_exclude_id, 0)
    AND (
        (start_time < p_end_time AND end_time > p_start_time)
    );
END$$

-- Generate unique PIN code
CREATE PROCEDURE generate_pin()
BEGIN
    DECLARE pin VARCHAR(6);
    DECLARE pin_exists INT DEFAULT 1;

    WHILE pin_exists > 0 DO
        SET pin = LPAD(FLOOR(RAND() * 1000000), 6, '0');
        SELECT COUNT(*) INTO pin_exists FROM reservations WHERE pin_code = pin;
    END WHILE;

    SELECT pin;
END$$

DELIMITER ;

-- ============================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================

-- Additional composite indexes for common queries
-- Note: Using room_id + start_time (without DATE function for compatibility)
CREATE INDEX idx_room_start ON reservations(room_id, start_time);
CREATE INDEX idx_department ON reservations(department);
CREATE INDEX idx_fullname ON reservations(full_name);

-- ============================================================
-- DATABASE COMPLETE
-- ============================================================
