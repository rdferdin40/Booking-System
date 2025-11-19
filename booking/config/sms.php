<?php
/**
 * SMS Configuration
 * SMS gateway settings for notifications
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

// NOTE: SMS settings are stored in the database (notification_settings table)
// This file provides documentation and examples

/**
 * Generic SMS Gateway Configuration
 *
 * The system supports any HTTP-based SMS gateway API.
 * Configure these settings in the admin panel (Notifications page):
 *
 * - API URL: Full URL to the SMS gateway endpoint
 * - API Key: Authentication key/token (if required)
 * - Method: POST or GET
 * - Phone Field: Parameter name for phone number (e.g., 'to', 'phone', 'number')
 * - Message Field: Parameter name for message text (e.g., 'message', 'text', 'body')
 */

/**
 * Popular SMS Gateway Examples
 *
 * 1. TWILIO
 *    API URL: https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json
 *    Method: POST
 *    Phone Field: To
 *    Message Field: Body
 *    Additional: From (your Twilio phone number)
 *    Auth: Basic Auth with Account SID and Auth Token
 *    Documentation: https://www.twilio.com/docs/sms
 *
 * 2. NEXMO (Vonage)
 *    API URL: https://rest.nexmo.com/sms/json
 *    Method: POST
 *    Phone Field: to
 *    Message Field: text
 *    Additional: from (sender ID), api_key, api_secret
 *    Documentation: https://developer.nexmo.com/messaging/sms/overview
 *
 * 3. PLIVO
 *    API URL: https://api.plivo.com/v1/Account/{auth_id}/Message/
 *    Method: POST
 *    Phone Field: dst
 *    Message Field: text
 *    Additional: src (sender phone)
 *    Auth: Basic Auth
 *    Documentation: https://www.plivo.com/docs/sms/api/message
 *
 * 4. MESSAGEBIRD
 *    API URL: https://rest.messagebird.com/messages
 *    Method: POST
 *    Phone Field: recipients
 *    Message Field: body
 *    Additional: originator (sender ID)
 *    Auth: API Key in header
 *    Documentation: https://developers.messagebird.com/api/sms-messaging/
 *
 * 5. CLICKATELL
 *    API URL: https://platform.clickatell.com/messages/http/send
 *    Method: GET or POST
 *    Phone Field: to
 *    Message Field: content
 *    Additional: apiKey
 *    Documentation: https://www.clickatell.com/developers/api-documentation/
 *
 * 6. SINCH
 *    API URL: https://sms.api.sinch.com/xms/v1/{service_plan_id}/batches
 *    Method: POST
 *    Phone Field: to
 *    Message Field: body
 *    Auth: Bearer token
 *    Documentation: https://developers.sinch.com/docs/sms/
 *
 * 7. TEXTLOCAL (UK)
 *    API URL: https://api.textlocal.in/send/
 *    Method: POST
 *    Phone Field: numbers
 *    Message Field: message
 *    Additional: apikey, sender
 *    Documentation: https://api.textlocal.in/docs/
 *
 * 8. LOCAL PROVIDER / CUSTOM API
 *    Configure according to your local SMS provider's API documentation
 */

/**
 * SMS Message Templates
 *
 * Maximum message length varies by provider (typically 160 characters for single SMS)
 * Use these variables in your templates:
 * - {full_name}
 * - {room_name}
 * - {title}
 * - {start_time}
 * - {end_time}
 * - {pin_code}
 * - {edit_url} (shortened URL recommended)
 */

/**
 * Example SMS Message Formats
 */
define('SMS_TEMPLATES', [
    'created' => 'Booking confirmed: {title} in {room_name} on {start_time}. PIN: {pin_code}. Edit: {edit_url}',
    'updated' => 'Booking updated: {title} in {room_name}. New time: {start_time} - {end_time}. PIN: {pin_code}',
    'cancelled' => 'Booking cancelled: {title} in {room_name} on {start_time}. PIN: {pin_code}',
    'reminder' => 'Reminder: {title} in {room_name} starts in 15 minutes. PIN: {pin_code}'
]);

/**
 * Phone Number Formatting
 */
function formatPhoneForSMS($phone, $countryCode = '+1') {
    // Remove all non-numeric characters
    $cleaned = preg_replace('/[^0-9]/', '', $phone);

    // Add country code if not present
    if (substr($cleaned, 0, 1) !== '+' && strlen($cleaned) === 10) {
        $cleaned = $countryCode . $cleaned;
    }

    return $cleaned;
}

/**
 * Validate phone number
 */
function isValidPhone($phone) {
    $cleaned = preg_replace('/[^0-9+]/', '', $phone);
    return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
}

/**
 * Test SMS configuration
 */
function testSMSConfiguration($testPhone, $settings) {
    // Implementation in NotificationService
    return true;
}

/**
 * URL Shortening for SMS
 *
 * To save characters in SMS messages, consider using a URL shortener:
 * - Bitly API: https://dev.bitly.com/
 * - TinyURL: https://tinyurl.com/
 * - Your own URL shortener
 */
function shortenURL($longUrl) {
    // Implement URL shortening if needed
    // For now, return original URL
    return $longUrl;
}
