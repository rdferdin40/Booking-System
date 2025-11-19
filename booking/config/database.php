<?php
/**
 * Database Configuration
 * MySQL/MariaDB connection settings
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

// Database Credentials
// IMPORTANT: Update these values to match your MySQL setup
define('DB_HOST', 'localhost');
define('DB_NAME', 'booking_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Database Connection Options
define('DB_PORT', 3306);

// For XAMPP on Windows, these default settings should work:
// DB_HOST: localhost
// DB_USER: root
// DB_PASS: (empty string)
// DB_NAME: booking_system (you need to create this database)

/**
 * Installation Instructions:
 *
 * 1. Start XAMPP and ensure MySQL is running
 * 2. Open phpMyAdmin (http://localhost/phpmyadmin)
 * 3. Create a new database called "booking_system"
 * 4. Import the schema.sql file from /migrations/schema.sql
 * 5. Verify all tables are created successfully
 * 6. Update DB credentials above if needed
 * 7. Test the connection by accessing the application
 *
 * Default Admin Login:
 * Username: admin
 * Password: admin123
 * IMPORTANT: Change this password immediately after first login!
 */
