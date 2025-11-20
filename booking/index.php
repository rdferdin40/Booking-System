<?php
/**
 * Root Index - Redirect to Public Booking Interface
 *
 * This file redirects visitors from /booking/ to /booking/public/
 * making the URL cleaner and more user-friendly.
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

// Redirect to public booking interface
header('Location: public/index.php');
exit;
