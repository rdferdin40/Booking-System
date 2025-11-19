<?php
/**
 * Admin Logout
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';

$auth = new AdminAuth();
$auth->logout();

setFlash('success', 'You have been logged out successfully');
redirect(ADMIN_URL . '/login.php');
