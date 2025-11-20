<?php
/**
 * Tablet/Kiosk View - Hybrid Modern Pro Design
 * Modern, professional tablet interface for wall-mounted displays
 *
 * Usage: /public/tablet.php?room_id=1
 *
 * @package ConferenceBooking
 * @version 2.4.0 - Fully Responsive for All Tablet Sizes
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/RoomService.php';
require_once __DIR__ . '/../core/CategoryService.php';
require_once __DIR__ . '/../core/TimeHelpers.php';
require_once __DIR__ . '/../config/settings.php';

$roomId = input('room_id');
if (!$roomId) {
    die('Error: room_id parameter required. Example: tablet.php?room_id=1');
}

$roomService = new RoomService();
$reservationService = new ReservationService();
$categoryService = new CategoryService();
$settings = Settings::getInstance();

$room = $roomService->getById($roomId);
if (!$room) {
    die('Error: Room not found');
}

$date = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// Get today's reservations for this room
$reservations = $reservationService->getByRoomAndDateRange($roomId, $date);

// Find current booking
$currentBooking = null;
$nextBooking = null;
$isAvailable = true;
$timeRemaining = 0;
$progressPercent = 0;

foreach ($reservations as $res) {
    if (TimeHelpers::isCurrent($res['start_time'], $res['end_time'])) {
        $currentBooking = $res;
        $isAvailable = false;

        // Calculate time remaining and progress
        $totalDuration = TimeHelpers::getDurationMinutes($res['start_time'], $res['end_time']);
        $elapsed = TimeHelpers::getDurationMinutes($res['start_time'], $now);
        $timeRemaining = $totalDuration - $elapsed;
        $progressPercent = ($elapsed / $totalDuration) * 100;
        break;
    }
}

// Find next booking
foreach ($reservations as $res) {
    if (TimeHelpers::isFuture($res['start_time'])) {
        $nextBooking = $res;
        break;
    }
}

$categories = $categoryService->getAll();
$screensaverEnabled = $settings->isScreensaverEnabled();
$screensaverTimeout = $settings->get('screensaver_timeout', 300) * 1000;

// Generate timeline hours (7 AM - 6 PM based on business hours)
$timelineStart = BUSINESS_START_HOUR;
$timelineEnd = BUSINESS_END_HOUR;
$currentHour = (int)date('G');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?php echo e($room['name']); ?> - Status</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Light mode colors */
            --bg-gradient-available-1: #10B981;
            --bg-gradient-available-2: #34D399;
            --bg-gradient-available-3: #6EE7B7;
            --bg-gradient-occupied-1: #EF4444;
            --bg-gradient-occupied-2: #F87171;
            --bg-gradient-occupied-3: #FCA5A5;

            --card-bg: #ffffff;
            --card-text: #111827;
            --card-text-secondary: #6B7280;
            --card-text-light: #9CA3AF;

            --status-available-bg: #D1FAE5;
            --status-available-text: #065F46;
            --status-occupied-bg: #FEE2E2;
            --status-occupied-text: #991B1B;

            --meeting-info-bg: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            --next-meeting-bg: #F9FAFB;

            --input-bg: #ffffff;
            --input-border: #E5E7EB;
            --input-text: #111827;

            --timeline-border: #E5E7EB;
            --timeline-label: #9CA3AF;

            --modal-bg: #ffffff;
        }

        body.night-mode {
            /* Dark mode colors */
            --bg-gradient-available-1: #065F46;
            --bg-gradient-available-2: #047857;
            --bg-gradient-available-3: #059669;
            --bg-gradient-occupied-1: #7F1D1D;
            --bg-gradient-occupied-2: #991B1B;
            --bg-gradient-occupied-3: #B91C1C;

            --card-bg: #1F2937;
            --card-text: #F9FAFB;
            --card-text-secondary: #D1D5DB;
            --card-text-light: #9CA3AF;

            --status-available-bg: #064E3B;
            --status-available-text: #A7F3D0;
            --status-occupied-bg: #7F1D1D;
            --status-occupied-text: #FCA5A5;

            --meeting-info-bg: linear-gradient(135deg, #374151 0%, #4B5563 100%);
            --next-meeting-bg: #374151;

            --input-bg: #374151;
            --input-border: #4B5563;
            --input-text: #F9FAFB;

            --timeline-border: #4B5563;
            --timeline-label: #6B7280;

            --modal-bg: #1F2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #000;
            color: var(--card-text);
            overflow: hidden;
            height: 100vh;
            width: 100vw;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.3s ease;
        }

        /* Background Gradient */
        .tablet-container {
            height: 100vh;
            width: 100vw;
            display: flex;
            padding: 30px;
            gap: 20px;
            transition: background 0.6s ease;
        }

        .tablet-container.available {
            background: linear-gradient(135deg, var(--bg-gradient-available-1) 0%, var(--bg-gradient-available-2) 50%, var(--bg-gradient-available-3) 100%);
        }

        .tablet-container.occupied {
            background: linear-gradient(135deg, var(--bg-gradient-occupied-1) 0%, var(--bg-gradient-occupied-2) 50%, var(--bg-gradient-occupied-3) 100%);
        }

        /* Main Content Card */
        .main-card {
            flex: 1;
            background: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 40px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            transition: background-color 0.3s ease;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            border-radius: 100px;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 30px;
            align-self: flex-start;
            transition: all 0.3s ease;
        }

        .status-badge.available {
            background: var(--status-available-bg);
            color: var(--status-available-text);
        }

        .status-badge.occupied {
            background: var(--status-occupied-bg);
            color: var(--status-occupied-text);
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-badge.available .status-dot {
            background: #10B981;
        }

        .status-badge.occupied .status-dot {
            background: #EF4444;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }

        /* Room Header */
        .room-header {
            margin-bottom: 30px;
        }

        .room-name {
            font-size: 48px;
            font-weight: 700;
            color: var(--card-text);
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .room-details {
            display: flex;
            gap: 20px;
            font-size: 20px;
            color: var(--card-text-secondary);
        }

        .room-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Status Section */
        .status-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 40px 0;
        }

        .status-display {
            text-align: center;
        }

        .current-time {
            font-size: 72px;
            font-weight: 300;
            color: var(--card-text);
            margin-bottom: 20px;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.02em;
        }

        .status-text {
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 30px;
            line-height: 1;
        }

        .status-text.available {
            color: #10B981;
        }

        .status-text.occupied {
            color: #EF4444;
        }

        /* Current Meeting Info */
        .meeting-info {
            background: var(--meeting-info-bg);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            border-left: 4px solid #EF4444;
        }

        .meeting-title {
            font-size: 32px;
            font-weight: 600;
            color: var(--card-text);
            margin-bottom: 12px;
        }

        .meeting-organizer {
            font-size: 24px;
            color: var(--card-text-secondary);
            margin-bottom: 16px;
        }

        .meeting-time {
            font-size: 28px;
            font-weight: 500;
            color: var(--card-text);
            margin-bottom: 20px;
        }

        /* Progress Circle */
        .progress-container {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        .progress-circle {
            position: relative;
            width: 200px;
            height: 200px;
        }

        .progress-circle svg {
            transform: rotate(-90deg);
        }

        .progress-circle-bg {
            fill: none;
            stroke: #F3F4F6;
            stroke-width: 12;
        }

        .progress-circle-fill {
            fill: none;
            stroke: #EF4444;
            stroke-width: 12;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.3s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .progress-minutes {
            font-size: 48px;
            font-weight: 700;
            color: var(--card-text);
            line-height: 1;
        }

        .progress-label {
            font-size: 18px;
            color: var(--card-text-secondary);
            margin-top: 4px;
        }

        /* Next Meeting */
        .next-meeting {
            background: var(--next-meeting-bg);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 3px solid #10B981;
        }

        .next-meeting-label {
            font-size: 16px;
            font-weight: 600;
            color: var(--card-text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .next-meeting-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--card-text);
            margin-bottom: 6px;
        }

        .next-meeting-time {
            font-size: 20px;
            color: var(--card-text-secondary);
        }

        /* Quick Book Buttons */
        .quick-book {
            margin-top: auto;
        }

        .quick-book-label {
            font-size: 18px;
            font-weight: 600;
            color: var(--card-text-secondary);
            margin-bottom: 16px;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .book-btn {
            padding: 20px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .book-btn:active {
            transform: translateY(0);
        }

        .book-btn.custom {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }

        /* Timeline Sidebar */
        .timeline-sidebar {
            width: 280px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 30px 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            transition: background-color 0.3s ease;
        }

        .timeline-header {
            margin-bottom: 20px;
        }

        .timeline-date {
            font-size: 16px;
            font-weight: 600;
            color: var(--card-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .timeline-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--card-text);
        }

        .timeline-current-time {
            font-size: 36px;
            font-weight: 700;
            color: var(--card-text);
            margin-bottom: 24px;
            font-variant-numeric: tabular-nums;
        }

        /* Timeline */
        .timeline {
            flex: 1;
            position: relative;
            overflow-y: auto;
            padding-right: 10px;
        }

        .timeline::-webkit-scrollbar {
            width: 4px;
        }

        .timeline::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 2px;
        }

        .timeline-hour {
            position: relative;
            height: 60px;
            border-left: 2px solid var(--timeline-border);
            padding-left: 20px;
            margin-bottom: 8px;
        }

        .timeline-hour.current {
            border-left-color: #10B981;
        }

        .timeline-hour-label {
            position: absolute;
            left: -40px;
            top: -8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--timeline-label);
        }

        .timeline-hour.current .timeline-hour-label {
            color: #10B981;
            font-weight: 700;
        }

        .timeline-event {
            background: linear-gradient(135deg, #DBEAFE 0%, #BFDBFE 100%);
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 4px;
            border-left: 3px solid #3B82F6;
        }

        .timeline-event.current {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            border-left-color: #EF4444;
        }

        .timeline-event-title {
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .timeline-event-time {
            font-size: 11px;
            color: #4B5563;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: var(--modal-bg);
            padding: 50px;
            border-radius: 24px;
            width: 90%;
            max-width: 700px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease;
        }

        .modal-header {
            font-size: 36px;
            font-weight: 700;
            color: var(--card-text);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 18px;
            font-weight: 600;
            color: var(--card-text);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--input-bg);
            color: var(--input-text);
        }

        .form-control:focus {
            outline: none;
            border-color: #10B981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .btn {
            padding: 18px 32px;
            font-size: 20px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background: #F3F4F6;
            color: #6B7280;
        }

        .btn-secondary:hover {
            background: #E5E7EB;
        }

        .btn-block {
            width: 100%;
            margin-bottom: 12px;
        }

        /* Control Buttons */
        .control-buttons {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1500;
            display: flex;
            gap: 12px;
        }

        .control-btn {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .control-btn:hover {
            background: rgba(0, 0, 0, 0.3);
            transform: scale(1.05);
        }

        .control-btn:active {
            transform: scale(0.95);
        }

        .control-btn .icon {
            font-size: 28px;
            transition: transform 0.3s ease;
        }

        body.night-mode .control-btn.night-mode-toggle .icon {
            transform: rotate(180deg);
        }

        /* QR Code Modal */
        .qr-modal {
            display: none;
            position: fixed;
            z-index: 1500;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            align-items: center;
            justify-content: center;
        }

        .qr-modal.active {
            display: flex;
        }

        .qr-modal-content {
            background: var(--modal-bg);
            padding: 50px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            transition: background-color 0.3s ease;
        }

        .qr-modal-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--card-text);
            margin-bottom: 16px;
        }

        .qr-modal-subtitle {
            font-size: 18px;
            color: var(--card-text-secondary);
            margin-bottom: 30px;
        }

        .qr-code-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            display: inline-block;
            margin-bottom: 24px;
        }

        .qr-code-container img {
            display: block;
            width: 300px;
            height: 300px;
        }

        .qr-url {
            font-size: 16px;
            color: var(--card-text-secondary);
            font-weight: 500;
            margin-bottom: 24px;
            font-family: 'Monaco', 'Courier New', monospace;
        }

        /* Screensaver */
        .screensaver {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            z-index: 2000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .screensaver.active {
            display: flex;
        }

        .screensaver-text {
            font-size: 72px;
            font-weight: 300;
            color: white;
            margin-bottom: 30px;
            animation: fadeInOut 3s infinite;
        }

        .screensaver-time {
            font-size: 120px;
            font-weight: 700;
            color: white;
            font-variant-numeric: tabular-nums;
        }

        .screensaver-tap {
            font-size: 32px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 50px;
            animation: fadeInOut 2s infinite;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Responsive Design for All Tablet Sizes */

        /* Large tablets and above (12"+ iPad Pro, Galaxy Tab S8+) - Default styles above work well */

        /* Medium tablets (10-11" iPad, Galaxy Tab S, Fire HD 10) */
        @media (max-width: 1200px) {
            .tablet-container {
                padding: 24px;
                gap: 16px;
            }
            .timeline-sidebar {
                width: 240px;
                padding: 24px 16px;
            }
            .main-card {
                padding: 32px;
            }
            .room-name {
                font-size: 40px;
            }
            .current-time {
                font-size: 64px;
            }
            .status-text {
                font-size: 56px;
            }
            .meeting-title {
                font-size: 28px;
            }
            .progress-circle {
                width: 180px;
                height: 180px;
            }
            .progress-circle svg {
                width: 180px;
                height: 180px;
            }
            .progress-minutes {
                font-size: 42px;
            }
        }

        /* Small tablets (7-8" Fire 7/8, Galaxy Tab A 8") */
        @media (max-width: 900px) {
            .tablet-container {
                padding: 16px;
                gap: 12px;
            }
            .timeline-sidebar {
                width: 200px;
                padding: 20px 12px;
            }
            .timeline-current-time {
                font-size: 28px;
            }
            .timeline-title {
                font-size: 20px;
            }
            .main-card {
                padding: 24px;
            }
            .room-name {
                font-size: 32px;
            }
            .room-details {
                font-size: 16px;
            }
            .current-time {
                font-size: 48px;
            }
            .status-text {
                font-size: 40px;
            }
            .status-badge {
                font-size: 16px;
                padding: 10px 20px;
            }
            .meeting-title {
                font-size: 24px;
            }
            .meeting-organizer {
                font-size: 20px;
            }
            .meeting-time {
                font-size: 22px;
            }
            .progress-circle {
                width: 150px;
                height: 150px;
            }
            .progress-circle svg {
                width: 150px;
                height: 150px;
            }
            .progress-minutes {
                font-size: 36px;
            }
            .next-meeting-title {
                font-size: 20px;
            }
            .next-meeting-time {
                font-size: 18px;
            }
            .book-btn {
                font-size: 20px;
                padding: 16px;
            }
            .control-buttons {
                top: 16px;
                right: 16px;
                gap: 8px;
            }
            .control-btn {
                width: 50px;
                height: 50px;
            }
            .control-btn .icon {
                font-size: 24px;
            }
            .modal-content {
                padding: 32px;
                max-width: 90%;
            }
            .modal-header {
                font-size: 28px;
            }
            .form-control {
                font-size: 16px;
                padding: 14px;
            }
        }

        /* Very small tablets (landscape phones, 6-7" devices) */
        @media (max-width: 700px) {
            .tablet-container {
                padding: 12px;
            }
            .timeline-sidebar {
                display: none; /* Hide timeline on very small screens */
            }
            .main-card {
                padding: 20px;
            }
            .room-name {
                font-size: 28px;
            }
            .current-time {
                font-size: 40px;
            }
            .status-text {
                font-size: 32px;
            }
            .book-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .qr-code-container img {
                width: 240px;
                height: 240px;
            }
            .control-btn {
                width: 44px;
                height: 44px;
            }
            .control-btn .icon {
                font-size: 20px;
            }
        }

        /* Portrait orientation - stack vertically */
        @media (orientation: portrait) {
            .tablet-container {
                flex-direction: column;
            }
            .timeline-sidebar {
                width: 100%;
                height: auto;
                max-height: 160px;
                padding: 16px 20px;
                order: -1; /* Move timeline to top */
            }
            .timeline {
                display: none; /* Hide detailed timeline in portrait */
            }
            .timeline-current-time {
                display: none; /* Already shown in main area */
            }
            .main-card {
                flex: 1;
            }

            /* Adjust for portrait mode */
            .current-time {
                font-size: 56px;
            }
            .status-text {
                font-size: 48px;
            }
        }

        /* Portrait + Small screen (iPad Mini portrait, small Android tablets) */
        @media (orientation: portrait) and (max-width: 900px) {
            .timeline-sidebar {
                padding: 12px 16px;
            }
            .timeline-title {
                font-size: 18px;
            }
            .room-name {
                font-size: 28px;
            }
            .current-time {
                font-size: 42px;
            }
            .status-text {
                font-size: 36px;
            }
            .book-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .book-btn {
                padding: 14px;
                font-size: 18px;
            }
        }

        /* Landscape mode optimizations */
        @media (orientation: landscape) and (max-height: 600px) {
            .tablet-container {
                padding: 12px;
            }
            .main-card {
                padding: 20px;
            }
            .status-section {
                margin: 20px 0;
            }
            .current-time {
                font-size: 48px;
                margin-bottom: 12px;
            }
            .status-text {
                font-size: 40px;
                margin-bottom: 16px;
            }
            .progress-circle {
                width: 140px;
                height: 140px;
            }
            .progress-circle svg {
                width: 140px;
                height: 140px;
            }
            .meeting-info,
            .next-meeting {
                padding: 16px;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Control Buttons -->
    <div class="control-buttons">
        <button class="control-btn qr-btn" id="qrButton" aria-label="Show QR code">
            <span class="icon">📱</span>
        </button>
        <button class="control-btn night-mode-toggle" id="nightModeToggle" aria-label="Toggle night mode">
            <span class="icon">🌙</span>
        </button>
    </div>

    <!-- Main Container -->
    <div class="tablet-container <?php echo $isAvailable ? 'available' : 'occupied'; ?>">

        <!-- Main Content Card -->
        <div class="main-card">
            <!-- Status Badge -->
            <div class="status-badge <?php echo $isAvailable ? 'available' : 'occupied'; ?>">
                <span class="status-dot"></span>
                <?php echo $isAvailable ? 'Available' : 'Occupied'; ?>
            </div>

            <!-- Room Header -->
            <div class="room-header">
                <h1 class="room-name"><?php echo e($room['name']); ?></h1>
                <div class="room-details">
                    <div class="room-detail-item">
                        <span>👥</span>
                        <span>Capacity: 8</span>
                    </div>
                    <div class="room-detail-item">
                        <span>🖥️</span>
                        <span>4K Display</span>
                    </div>
                    <div class="room-detail-item">
                        <span>📹</span>
                        <span>Video Conference</span>
                    </div>
                </div>
            </div>

            <?php if ($isAvailable): ?>
                <!-- Available State -->
                <div class="status-section">
                    <div class="status-display">
                        <div class="current-time" id="currentTime"></div>
                        <div class="status-text available">AVAILABLE</div>
                    </div>
                </div>

                <?php if ($nextBooking): ?>
                    <div class="next-meeting">
                        <div class="next-meeting-label">Next Meeting</div>
                        <?php if ($settings->shouldShowTitles()): ?>
                            <div class="next-meeting-title"><?php echo e($nextBooking['title']); ?></div>
                        <?php endif; ?>
                        <div class="next-meeting-time">
                            <?php echo TimeHelpers::format($nextBooking['start_time'], 'g:i A'); ?> -
                            <?php echo TimeHelpers::format($nextBooking['end_time'], 'g:i A'); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="quick-book">
                    <div class="quick-book-label">Quick Book</div>
                    <div class="book-grid">
                        <button class="book-btn" onclick="quickBook(15)">15min</button>
                        <button class="book-btn" onclick="quickBook(30)">30min</button>
                        <button class="book-btn" onclick="quickBook(60)">1hr</button>
                        <button class="book-btn custom" onclick="openBookingModal()">Custom</button>
                    </div>
                </div>

            <?php else: ?>
                <!-- Occupied State -->
                <div class="status-section">
                    <div class="current-time" id="currentTime"></div>
                    <div class="status-text occupied">IN USE</div>

                    <div class="progress-container">
                        <div class="progress-circle">
                            <svg width="200" height="200">
                                <circle class="progress-circle-bg" cx="100" cy="100" r="90"></circle>
                                <circle class="progress-circle-fill" cx="100" cy="100" r="90"
                                    stroke-dasharray="565.48"
                                    stroke-dashoffset="<?php echo 565.48 * (1 - $progressPercent / 100); ?>"></circle>
                            </svg>
                            <div class="progress-text">
                                <div class="progress-minutes"><?php echo $timeRemaining; ?></div>
                                <div class="progress-label">min left</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="meeting-info">
                    <?php if ($settings->shouldShowTitles()): ?>
                        <div class="meeting-title"><?php echo e($currentBooking['title']); ?></div>
                    <?php endif; ?>
                    <div class="meeting-organizer"><?php echo e($currentBooking['full_name']); ?></div>
                    <div class="meeting-time">
                        <?php echo TimeHelpers::format($currentBooking['start_time'], 'g:i A'); ?> -
                        <?php echo TimeHelpers::format($currentBooking['end_time'], 'g:i A'); ?>
                    </div>
                </div>

                <?php if ($nextBooking): ?>
                    <div class="next-meeting">
                        <div class="next-meeting-label">Next Meeting</div>
                        <?php if ($settings->shouldShowTitles()): ?>
                            <div class="next-meeting-title"><?php echo e($nextBooking['title']); ?></div>
                        <?php endif; ?>
                        <div class="next-meeting-time">
                            <?php echo TimeHelpers::format($nextBooking['start_time'], 'g:i A'); ?> -
                            <?php echo TimeHelpers::format($nextBooking['end_time'], 'g:i A'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Timeline Sidebar -->
        <div class="timeline-sidebar">
            <div class="timeline-header">
                <div class="timeline-date">Today</div>
                <div class="timeline-title"><?php echo date('l'); ?></div>
            </div>
            <div class="timeline-current-time" id="sidebarTime"></div>
            <div class="timeline">
                <?php for ($hour = $timelineStart; $hour < $timelineEnd; $hour++):
                    $isCurrent = ($hour == $currentHour);
                    $hourFormatted = date('g A', strtotime("{$hour}:00"));
                ?>
                <div class="timeline-hour<?php echo $isCurrent ? ' current' : ''; ?>">
                    <div class="timeline-hour-label"><?php echo $hourFormatted; ?></div>
                    <?php
                    // Find events for this hour
                    foreach ($reservations as $res) {
                        $resHour = (int)date('G', strtotime($res['start_time']));
                        if ($resHour == $hour) {
                            $isCurrentEvent = TimeHelpers::isCurrent($res['start_time'], $res['end_time']);
                    ?>
                        <div class="timeline-event<?php echo $isCurrentEvent ? ' current' : ''; ?>">
                            <?php if ($settings->shouldShowTitles()): ?>
                                <div class="timeline-event-title"><?php echo e($res['title']); ?></div>
                            <?php endif; ?>
                            <div class="timeline-event-time">
                                <?php echo TimeHelpers::format($res['start_time'], 'g:i A'); ?>
                            </div>
                        </div>
                    <?php }} ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">Book <?php echo e($room['name']); ?></div>
            <form id="bookingForm" method="POST" action="create.php">
                <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                <input type="hidden" id="start_time" name="start_time">
                <input type="hidden" id="end_time" name="end_time">

                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" id="booking_date" name="date" class="form-control" value="<?php echo $date; ?>" min="<?php echo $date; ?>" required>
                </div>

                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="time" id="start_time_display" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Duration *</label>
                    <select id="duration" class="form-control" required>
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="90">1.5 hours</option>
                        <option value="120">2 hours</option>
                        <option value="180">3 hours</option>
                        <option value="240">4 hours</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Select category...</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Meeting Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Email (optional)</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">BOOK NOW</button>
                <button type="button" class="btn btn-secondary btn-block" onclick="closeBookingModal()">CANCEL</button>
            </form>
        </div>
    </div>

    <!-- Screensaver -->
    <div class="screensaver" id="screensaver">
        <div class="screensaver-text">Room Available</div>
        <div class="screensaver-time" id="screensaverTime"></div>
        <div class="screensaver-tap">Tap to Book</div>
    </div>

    <!-- QR Code Modal -->
    <div class="qr-modal" id="qrModal">
        <div class="qr-modal-content">
            <div class="qr-modal-title">Book from Your Phone</div>
            <div class="qr-modal-subtitle">Scan this QR code to access the booking system</div>
            <div class="qr-code-container">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https://ladcportal.com/booking"
                     alt="QR Code for booking system"
                     loading="lazy">
            </div>
            <div class="qr-url">ladcportal.com/booking</div>
            <button type="button" class="btn btn-secondary" onclick="closeQrModal()">CLOSE</button>
        </div>
    </div>

    <script>
        const roomId = <?php echo $roomId; ?>;
        const screensaverEnabled = <?php echo $screensaverEnabled ? 'true' : 'false'; ?>;
        const screensaverTimeout = <?php echo $screensaverTimeout; ?>;
        const isAvailable = <?php echo $isAvailable ? 'true' : 'false'; ?>;
        let screensaverTimer;

        // Night mode toggle
        function toggleNightMode() {
            document.body.classList.toggle('night-mode');
            const isNightMode = document.body.classList.contains('night-mode');
            localStorage.setItem('nightMode', isNightMode ? 'true' : 'false');

            // Update toggle icon
            const toggleIcon = document.querySelector('.night-mode-toggle .icon');
            toggleIcon.textContent = isNightMode ? '☀️' : '🌙';
        }

        // Load night mode preference
        function loadNightModePreference() {
            const nightMode = localStorage.getItem('nightMode');
            if (nightMode === 'true') {
                document.body.classList.add('night-mode');
                document.querySelector('.night-mode-toggle .icon').textContent = '☀️';
            }
        }

        // QR Modal functions
        function openQrModal() {
            document.getElementById('qrModal').classList.add('active');
            resetScreensaver();
        }

        function closeQrModal() {
            document.getElementById('qrModal').classList.remove('active');
            resetScreensaver();
        }

        // Update all clocks
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            const simpleTime = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

            const currentTime = document.getElementById('currentTime');
            const sidebarTime = document.getElementById('sidebarTime');
            const screensaverTime = document.getElementById('screensaverTime');

            if (currentTime) currentTime.textContent = timeStr;
            if (sidebarTime) sidebarTime.textContent = simpleTime;
            if (screensaverTime) screensaverTime.textContent = timeStr;
        }

        // Quick book function
        function quickBook(minutes) {
            const now = new Date();
            const roundedMinutes = Math.ceil(now.getMinutes() / 15) * 15;
            now.setMinutes(roundedMinutes);
            now.setSeconds(0);

            const hours = String(now.getHours()).padStart(2, '0');
            const mins = String(now.getMinutes()).padStart(2, '0');

            document.getElementById('start_time_display').value = hours + ':' + mins;
            document.getElementById('duration').value = minutes;

            openBookingModal();
        }

        // Modal functions
        function openBookingModal() {
            document.getElementById('bookingModal').classList.add('active');
            resetScreensaver();
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.remove('active');
            resetScreensaver();
        }

        // Screensaver functions
        function resetScreensaver() {
            clearTimeout(screensaverTimer);
            document.getElementById('screensaver').classList.remove('active');
            if (screensaverEnabled && isAvailable) {
                screensaverTimer = setTimeout(showScreensaver, screensaverTimeout);
            }
        }

        function showScreensaver() {
            if (isAvailable) {
                document.getElementById('screensaver').classList.add('active');
            }
        }

        // Form submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const startTimeValue = document.getElementById('start_time_display').value;
            const duration = parseInt(document.getElementById('duration').value);
            const date = document.getElementById('booking_date').value;
            const startDateTime = date + ' ' + startTimeValue + ':00';

            const start = new Date(startDateTime);
            const end = new Date(start.getTime() + duration * 60000);
            const endHours = String(end.getHours()).padStart(2, '0');
            const endMins = String(end.getMinutes()).padStart(2, '0');
            const endDateTime = date + ' ' + endHours + ':' + endMins + ':00';

            document.getElementById('start_time').value = startDateTime;
            document.getElementById('end_time').value = endDateTime;
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load night mode preference
            loadNightModePreference();

            // Night mode toggle event
            document.getElementById('nightModeToggle').addEventListener('click', toggleNightMode);

            // QR button event
            document.getElementById('qrButton').addEventListener('click', openQrModal);

            // Close QR modal on backdrop click
            document.getElementById('qrModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeQrModal();
                }
            });

            updateTime();
            setInterval(updateTime, 1000);

            // Auto-refresh every 60 seconds
            setInterval(() => location.reload(), 60000);

            // Set up screensaver
            if (screensaverEnabled) {
                resetScreensaver();
                document.addEventListener('touchstart', resetScreensaver);
                document.addEventListener('click', resetScreensaver);
                document.addEventListener('mousemove', resetScreensaver);
            }

            // Set default start time
            const now = new Date();
            const minutes = now.getMinutes();
            const roundedMinutes = Math.ceil(minutes / 15) * 15;
            now.setMinutes(roundedMinutes);
            now.setSeconds(0);
            const hours = String(now.getHours()).padStart(2, '0');
            const mins = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('start_time_display').value = hours + ':' + mins;
        });

        // Prevent accidental zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(e) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                e.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>
</body>
</html>
