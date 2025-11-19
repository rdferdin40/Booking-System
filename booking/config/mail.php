<?php
/**
 * Email Configuration
 * SMTP settings for email notifications
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

// NOTE: Email settings are stored in the database (notification_settings table)
// This file provides helper functions and default configurations

/**
 * PHPMailer Configuration (Optional)
 *
 * If you want to use PHPMailer for more robust email sending:
 *
 * 1. Install PHPMailer via Composer:
 *    composer require phpmailer/phpmailer
 *
 * 2. Or download manually from:
 *    https://github.com/PHPMailer/PHPMailer
 *
 * 3. Uncomment and configure the code below
 */

/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // If using Composer

function sendEmailWithPHPMailer($to, $toName, $subject, $body, $settings) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = $settings['smtp_encryption']; // 'tls' or 'ssl'
        $mail->Port = $settings['smtp_port'];

        // Recipients
        $mail->setFrom($settings['email_from'], APP_NAME);
        $mail->addAddress($to, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        logMessage("Email error: {$mail->ErrorInfo}", 'ERROR');
        return false;
    }
}
*/

/**
 * Common Email Providers SMTP Settings
 *
 * Gmail:
 * - SMTP Host: smtp.gmail.com
 * - Port: 587
 * - Encryption: TLS
 * - Note: You need to enable "Less secure app access" or use App Passwords
 *
 * Outlook/Office365:
 * - SMTP Host: smtp.office365.com
 * - Port: 587
 * - Encryption: STARTTLS
 *
 * Yahoo:
 * - SMTP Host: smtp.mail.yahoo.com
 * - Port: 587
 * - Encryption: TLS
 *
 * SendGrid:
 * - SMTP Host: smtp.sendgrid.net
 * - Port: 587
 * - Username: apikey
 * - Password: Your SendGrid API Key
 *
 * Mailgun:
 * - SMTP Host: smtp.mailgun.org
 * - Port: 587
 * - Username: Your Mailgun SMTP username
 * - Password: Your Mailgun SMTP password
 */

/**
 * Email Template Variables
 *
 * Available variables for email templates:
 * - {full_name} - User's full name
 * - {room_name} - Room name
 * - {title} - Booking title
 * - {category_name} - Category name
 * - {category_color} - Category color
 * - {start_time} - Start date/time
 * - {end_time} - End date/time
 * - {duration} - Duration in human-readable format
 * - {department} - Department name
 * - {pin_code} - 6-digit PIN code
 * - {email} - User's email
 * - {phone} - User's phone
 * - {edit_url} - URL to edit/view booking
 */

// Email validation helper
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Test email configuration
function testEmailConfiguration($testEmail, $settings) {
    // This would test the email settings
    // Implementation in NotificationService
    return true;
}
