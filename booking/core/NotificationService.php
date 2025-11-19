<?php
/**
 * Notification Service Class
 * Handles email and SMS notifications
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/Database.php';

class NotificationService {
    private $db;
    private $settings;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }

    /**
     * Load notification settings from database
     */
    private function loadSettings() {
        $sql = "SELECT * FROM notification_settings LIMIT 1";
        $this->settings = $this->db->queryOne($sql);

        // Also get general settings
        $sql = "SELECT email_enabled, sms_enabled FROM settings LIMIT 1";
        $generalSettings = $this->db->queryOne($sql);

        $this->settings['email_enabled'] = $generalSettings['email_enabled'] ?? 0;
        $this->settings['sms_enabled'] = $generalSettings['sms_enabled'] ?? 0;
    }

    /**
     * Send notification for new reservation
     *
     * @param array $reservation Reservation data
     * @return array Result
     */
    public function sendCreatedNotification($reservation) {
        if (!$this->settings['send_on_create']) {
            return ['success' => true, 'message' => 'Notifications disabled for creation'];
        }

        $results = [];

        // Send email if configured and recipient has email
        if ($this->settings['email_enabled'] && !empty($reservation['email'])) {
            $results['email'] = $this->sendEmail($reservation, 'created');
        }

        // Send SMS if configured and recipient has phone
        if ($this->settings['sms_enabled'] && !empty($reservation['phone'])) {
            $results['sms'] = $this->sendSMS($reservation, 'created');
        }

        return $results;
    }

    /**
     * Send notification for updated reservation
     *
     * @param array $reservation Reservation data
     * @return array Result
     */
    public function sendUpdatedNotification($reservation) {
        if (!$this->settings['send_on_update']) {
            return ['success' => true, 'message' => 'Notifications disabled for updates'];
        }

        $results = [];

        if ($this->settings['email_enabled'] && !empty($reservation['email'])) {
            $results['email'] = $this->sendEmail($reservation, 'updated');
        }

        if ($this->settings['sms_enabled'] && !empty($reservation['phone'])) {
            $results['sms'] = $this->sendSMS($reservation, 'updated');
        }

        return $results;
    }

    /**
     * Send notification for cancelled reservation
     *
     * @param array $reservation Reservation data
     * @return array Result
     */
    public function sendCancelledNotification($reservation) {
        if (!$this->settings['send_on_cancel']) {
            return ['success' => true, 'message' => 'Notifications disabled for cancellation'];
        }

        $results = [];

        if ($this->settings['email_enabled'] && !empty($reservation['email'])) {
            $results['email'] = $this->sendEmail($reservation, 'cancelled');
        }

        if ($this->settings['sms_enabled'] && !empty($reservation['phone'])) {
            $results['sms'] = $this->sendSMS($reservation, 'cancelled');
        }

        return $results;
    }

    /**
     * Send email notification
     *
     * @param array $reservation Reservation data
     * @param string $type Notification type (created, updated, cancelled)
     * @return array Result
     */
    private function sendEmail($reservation, $type) {
        // Check if SMTP is configured
        if (empty($this->settings['smtp_host']) || empty($this->settings['smtp_username'])) {
            return ['success' => false, 'error' => 'Email not configured'];
        }

        require_once __DIR__ . '/../config/mail.php';

        try {
            $subject = $this->getEmailSubject($type);
            $body = $this->getEmailBody($reservation, $type);

            // Use PHP mail function or PHPMailer if available
            $sent = $this->sendSMTPEmail(
                $reservation['email'],
                $reservation['full_name'],
                $subject,
                $body
            );

            if ($sent) {
                return ['success' => true, 'message' => 'Email sent successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to send email'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Email error: ' . $e->getMessage()];
        }
    }

    /**
     * Send SMTP email
     *
     * @param string $to
     * @param string $toName
     * @param string $subject
     * @param string $body
     * @return bool
     */
    private function sendSMTPEmail($to, $toName, $subject, $body) {
        // Using PHP's mail function (basic implementation)
        // In production, you should use PHPMailer or similar
        $headers = [
            'From: ' . $this->settings['email_from'],
            'Reply-To: ' . $this->settings['email_from'],
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Send SMS notification
     *
     * @param array $reservation Reservation data
     * @param string $type Notification type
     * @return array Result
     */
    private function sendSMS($reservation, $type) {
        // Check if SMS API is configured
        if (empty($this->settings['sms_api_url'])) {
            return ['success' => false, 'error' => 'SMS not configured'];
        }

        try {
            $message = $this->getSMSMessage($reservation, $type);

            // Prepare API request
            $phoneField = $this->settings['sms_phone_field'] ?? 'to';
            $messageField = $this->settings['sms_message_field'] ?? 'message';

            $data = [
                $phoneField => $reservation['phone'],
                $messageField => $message
            ];

            // Add API key if provided
            if (!empty($this->settings['sms_api_key'])) {
                $data['api_key'] = $this->settings['sms_api_key'];
            }

            // Send request based on method
            if ($this->settings['sms_method'] === 'GET') {
                $url = $this->settings['sms_api_url'] . '?' . http_build_query($data);
                $response = file_get_contents($url);
            } else {
                // POST request
                $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => http_build_query($data)
                    ]
                ];
                $context = stream_context_create($options);
                $response = file_get_contents($this->settings['sms_api_url'], false, $context);
            }

            if ($response !== false) {
                return ['success' => true, 'message' => 'SMS sent successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to send SMS'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'SMS error: ' . $e->getMessage()];
        }
    }

    /**
     * Get email subject based on type
     *
     * @param string $type
     * @return string
     */
    private function getEmailSubject($type) {
        switch ($type) {
            case 'created':
                return 'Booking Confirmation';
            case 'updated':
                return 'Booking Updated';
            case 'cancelled':
                return 'Booking Cancelled';
            default:
                return 'Booking Notification';
        }
    }

    /**
     * Get email body HTML
     *
     * @param array $reservation
     * @param string $type
     * @return string
     */
    private function getEmailBody($reservation, $type) {
        require_once __DIR__ . '/TimeHelpers.php';

        $action = ucfirst($type);
        $startTime = TimeHelpers::format($reservation['start_time']);
        $endTime = TimeHelpers::format($reservation['end_time']);
        $duration = TimeHelpers::formatDuration(TimeHelpers::getDurationMinutes($reservation['start_time'], $reservation['end_time']));

        $editUrl = BASE_URL . '/public/edit.php?pin=' . $reservation['pin_code'];

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #3498db; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; margin: 20px 0; }
                .detail { margin: 10px 0; }
                .label { font-weight: bold; }
                .pin { font-size: 24px; color: #e74c3c; font-weight: bold; text-align: center; padding: 15px; background: #fff; border: 2px dashed #e74c3c; margin: 20px 0; }
                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                .button { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking {$action}</h1>
                </div>
                <div class='content'>
                    <p>Hello {$reservation['full_name']},</p>
                    <p>Your booking has been {$type}.</p>

                    <div class='detail'><span class='label'>Room:</span> {$reservation['room_name']}</div>
                    <div class='detail'><span class='label'>Title:</span> {$reservation['title']}</div>
                    <div class='detail'><span class='label'>Category:</span> {$reservation['category_name']}</div>
                    <div class='detail'><span class='label'>Start:</span> {$startTime}</div>
                    <div class='detail'><span class='label'>End:</span> {$endTime}</div>
                    <div class='detail'><span class='label'>Duration:</span> {$duration}</div>
                    <div class='detail'><span class='label'>Department:</span> {$reservation['department']}</div>

                    <div class='pin'>
                        PIN: {$reservation['pin_code']}
                    </div>

                    <p style='text-align: center;'>
                        <a href='{$editUrl}' class='button'>View/Edit Booking</a>
                    </p>

                    <p><small>Use your PIN code to view, edit, or cancel your booking.</small></p>
                </div>
                <div class='footer'>
                    <p>Conference Room Booking System</p>
                    <p>This is an automated message, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $html;
    }

    /**
     * Get SMS message text
     *
     * @param array $reservation
     * @param string $type
     * @return string
     */
    private function getSMSMessage($reservation, $type) {
        require_once __DIR__ . '/TimeHelpers.php';

        $action = ucfirst($type);
        $startTime = TimeHelpers::format($reservation['start_time'], 'M d, g:i A');
        $endTime = TimeHelpers::format($reservation['end_time'], 'g:i A');

        $message = "Booking {$action}: {$reservation['title']} in {$reservation['room_name']} on {$startTime} - {$endTime}. PIN: {$reservation['pin_code']}";

        if ($type === 'created' || $type === 'updated') {
            $editUrl = BASE_URL . '/public/edit.php?pin=' . $reservation['pin_code'];
            $message .= ". Edit: {$editUrl}";
        }

        return $message;
    }

    /**
     * Test email configuration
     *
     * @param string $testEmail
     * @return array Result
     */
    public function testEmail($testEmail) {
        if (empty($this->settings['smtp_host'])) {
            return ['success' => false, 'error' => 'SMTP not configured'];
        }

        $testReservation = [
            'email' => $testEmail,
            'full_name' => 'Test User',
            'room_name' => 'Test Room',
            'title' => 'Test Booking',
            'category_name' => 'Test',
            'start_time' => date('Y-m-d H:i:s'),
            'end_time' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'department' => 'Testing',
            'pin_code' => '123456'
        ];

        return $this->sendEmail($testReservation, 'created');
    }

    /**
     * Test SMS configuration
     *
     * @param string $testPhone
     * @return array Result
     */
    public function testSMS($testPhone) {
        if (empty($this->settings['sms_api_url'])) {
            return ['success' => false, 'error' => 'SMS API not configured'];
        }

        $testReservation = [
            'phone' => $testPhone,
            'full_name' => 'Test User',
            'room_name' => 'Test Room',
            'title' => 'Test Booking',
            'category_name' => 'Test',
            'start_time' => date('Y-m-d H:i:s'),
            'end_time' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'pin_code' => '123456'
        ];

        return $this->sendSMS($testReservation, 'created');
    }

    /**
     * Update notification settings
     *
     * @param array $data Settings data
     * @return array Result
     */
    public function updateSettings($data) {
        $sql = "UPDATE notification_settings SET
                email_from = ?,
                smtp_host = ?,
                smtp_username = ?,
                smtp_password = ?,
                smtp_port = ?,
                smtp_encryption = ?,
                sms_api_url = ?,
                sms_api_key = ?,
                sms_method = ?,
                sms_phone_field = ?,
                sms_message_field = ?,
                send_on_create = ?,
                send_on_update = ?,
                send_on_cancel = ?,
                updated_at = NOW()
                WHERE id = 1";

        try {
            $this->db->execute($sql, [
                $data['email_from'] ?? null,
                $data['smtp_host'] ?? null,
                $data['smtp_username'] ?? null,
                $data['smtp_password'] ?? null,
                $data['smtp_port'] ?? 587,
                $data['smtp_encryption'] ?? 'tls',
                $data['sms_api_url'] ?? null,
                $data['sms_api_key'] ?? null,
                $data['sms_method'] ?? 'POST',
                $data['sms_phone_field'] ?? 'to',
                $data['sms_message_field'] ?? 'message',
                $data['send_on_create'] ?? 1,
                $data['send_on_update'] ?? 1,
                $data['send_on_cancel'] ?? 1
            ]);

            $this->loadSettings();

            return ['success' => true, 'message' => 'Notification settings updated'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to update settings: ' . $e->getMessage()];
        }
    }
}
