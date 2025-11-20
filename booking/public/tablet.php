<?php
/**
 * Tablet/Kiosk View
 * Dedicated display for wall-mounted tablets by conference room doors
 *
 * Usage: /public/tablet.php?room_id=1
 *
 * @package ConferenceBooking
 * @version 1.0.0
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

foreach ($reservations as $res) {
    if (TimeHelpers::isCurrent($res['start_time'], $res['end_time'])) {
        $currentBooking = $res;
        $isAvailable = false;
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
$screensaverTimeout = $settings->get('screensaver_timeout', 300) * 1000; // Convert to milliseconds
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?php echo e($room['name']); ?> - Room Status</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #000;
            color: #fff;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }

        .tablet-view {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
        }

        .tablet-view.available {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .tablet-view.occupied {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        }

        .room-name {
            font-size: 48px;
            font-weight: 300;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .status-icon {
            font-size: 120px;
            margin: 30px 0;
        }

        .status-text {
            font-size: 72px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .current-time {
            font-size: 64px;
            font-weight: 300;
            margin-bottom: 40px;
            font-variant-numeric: tabular-nums;
        }

        .booking-info {
            background: rgba(0, 0, 0, 0.2);
            padding: 40px;
            border-radius: 20px;
            margin: 30px 0;
            max-width: 800px;
        }

        .booking-title {
            font-size: 36px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .booking-details {
            font-size: 28px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .booking-time {
            font-size: 32px;
            font-weight: 500;
            margin: 15px 0;
        }

        .next-booking {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            font-size: 24px;
        }

        .book-button {
            background: rgba(255, 255, 255, 0.9);
            color: #000;
            border: none;
            padding: 30px 60px;
            border-radius: 50px;
            font-size: 36px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s;
        }

        .book-button:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
        }

        .book-button:active {
            transform: scale(0.98);
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
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .screensaver.active {
            display: flex;
        }

        .screensaver-text {
            font-size: 96px;
            font-weight: 300;
            margin-bottom: 40px;
            animation: pulse 2s infinite;
        }

        .screensaver-time {
            font-size: 120px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .screensaver-tap {
            font-size: 48px;
            margin-top: 60px;
            opacity: 0.7;
            animation: fadeInOut 3s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Booking Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 50px;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            color: #333;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 20px;
            font-size: 24px;
            border: 2px solid #ddd;
            border-radius: 10px;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            padding: 25px 50px;
            font-size: 28px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-block {
            width: 100%;
        }

        .time-picker {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .time-picker select {
            flex: 1;
            padding: 20px;
            font-size: 28px;
            border: 2px solid #ddd;
            border-radius: 10px;
        }

        @media (orientation: landscape) {
            .room-name { font-size: 56px; }
            .status-text { font-size: 84px; }
            .current-time { font-size: 72px; }
        }
    </style>
</head>
<body>
    <!-- Main Tablet View -->
    <div class="tablet-view <?php echo $isAvailable ? 'available' : 'occupied'; ?>" id="mainView">
        <div class="room-name"><?php echo e($room['name']); ?></div>

        <?php if ($isAvailable): ?>
            <div class="status-icon">✓</div>
            <div class="status-text">AVAILABLE</div>
            <div class="current-time" id="currentTime"></div>

            <?php if ($nextBooking): ?>
                <div class="next-booking">
                    <strong>Next Booking:</strong><br>
                    <?php echo TimeHelpers::format($nextBooking['start_time'], 'g:i A'); ?> -
                    <?php echo TimeHelpers::format($nextBooking['end_time'], 'g:i A'); ?><br>
                    <?php if ($settings->shouldShowTitles()): ?>
                        <?php echo e($nextBooking['title']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <button class="book-button" onclick="openBookingModal()">
                TAP TO BOOK
            </button>

        <?php else: ?>
            <div class="status-icon">●</div>
            <div class="status-text">OCCUPIED</div>
            <div class="current-time" id="currentTime"></div>

            <div class="booking-info">
                <?php if ($settings->shouldShowTitles()): ?>
                    <div class="booking-title"><?php echo e($currentBooking['title']); ?></div>
                <?php endif; ?>
                <div class="booking-time">
                    <?php echo TimeHelpers::format($currentBooking['start_time'], 'g:i A'); ?> -
                    <?php echo TimeHelpers::format($currentBooking['end_time'], 'g:i A'); ?>
                </div>
                <div class="booking-details">
                    <?php echo e($currentBooking['full_name']); ?><br>
                    <?php echo e($currentBooking['category_name']); ?>
                </div>
            </div>

            <?php if ($nextBooking): ?>
                <div class="next-booking">
                    <strong>Next Booking:</strong><br>
                    <?php echo TimeHelpers::format($nextBooking['start_time'], 'g:i A'); ?> -
                    <?php echo TimeHelpers::format($nextBooking['end_time'], 'g:i A'); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Screensaver -->
    <div class="screensaver" id="screensaver">
        <div class="screensaver-text">Room Available</div>
        <div class="screensaver-time" id="screensaverTime"></div>
        <div class="screensaver-tap">Tap to Book</div>
    </div>

    <!-- Booking Modal -->
    <div class="modal" id="bookingModal">
        <div class="modal-content">
            <div class="modal-header">Book <?php echo e($room['name']); ?></div>
            <form id="bookingForm" method="POST" action="create.php">
                <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" id="start_time" name="start_time">
                <input type="hidden" id="end_time" name="end_time">

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

    <script>
        const roomId = <?php echo $roomId; ?>;
        const screensaverEnabled = <?php echo $screensaverEnabled ? 'true' : 'false'; ?>;
        const screensaverTimeout = <?php echo $screensaverTimeout; ?>;
        let screensaverTimer;
        let currentTime, screensaverTime;

        // Update clocks
        function updateTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

            if (currentTime) currentTime.textContent = timeStr;
            if (screensaverTime) screensaverTime.textContent = timeStr;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            currentTime = document.getElementById('currentTime');
            screensaverTime = document.getElementById('screensaverTime');

            updateTime();
            setInterval(updateTime, 1000);

            // Auto-refresh page every 60 seconds
            setInterval(() => location.reload(), 60000);

            // Screensaver setup
            if (screensaverEnabled) {
                resetScreensaver();
                document.addEventListener('touchstart', resetScreensaver);
                document.addEventListener('click', resetScreensaver);
                document.addEventListener('mousemove', resetScreensaver);
            }

            // Set default start time to now (rounded to next 15 min)
            const now = new Date();
            const minutes = now.getMinutes();
            const roundedMinutes = Math.ceil(minutes / 15) * 15;
            now.setMinutes(roundedMinutes);
            now.setSeconds(0);

            const hours = String(now.getHours()).padStart(2, '0');
            const mins = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('start_time_display').value = hours + ':' + mins;
        });

        function resetScreensaver() {
            clearTimeout(screensaverTimer);
            document.getElementById('screensaver').classList.remove('active');

            if (screensaverEnabled) {
                screensaverTimer = setTimeout(showScreensaver, screensaverTimeout);
            }
        }

        function showScreensaver() {
            if (<?php echo $isAvailable ? 'true' : 'false'; ?>) {
                document.getElementById('screensaver').classList.add('active');
            }
        }

        function openBookingModal() {
            document.getElementById('bookingModal').classList.add('active');
            resetScreensaver();
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.remove('active');
            resetScreensaver();
        }

        // Handle form submission
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const startTimeValue = document.getElementById('start_time_display').value;
            const duration = parseInt(document.getElementById('duration').value);

            const date = '<?php echo $date; ?>';
            const startDateTime = date + ' ' + startTimeValue + ':00';

            // Calculate end time
            const start = new Date(startDateTime);
            const end = new Date(start.getTime() + duration * 60000);
            const endHours = String(end.getHours()).padStart(2, '0');
            const endMins = String(end.getMinutes()).padStart(2, '0');
            const endDateTime = date + ' ' + endHours + ':' + endMins + ':00';

            document.getElementById('start_time').value = startDateTime;
            document.getElementById('end_time').value = endDateTime;
        });

        // Prevent accidental zoom on double tap
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
