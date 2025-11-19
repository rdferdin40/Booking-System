<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/RoomService.php';
require_once __DIR__ . '/../core/CategoryService.php';
require_once __DIR__ . '/../core/TimeHelpers.php';
require_once __DIR__ . '/../config/settings.php';
$pageTitle = 'Room Booking';
$roomService = new RoomService();
$reservationService = new ReservationService();
$categoryService = new CategoryService();
$settings = Settings::getInstance();
$date = input('date', date('Y-m-d'));
$roomId = input('room_id');
$rooms = $roomService->getAll(true);
if (!$roomId && !empty($rooms)) { $roomId = $rooms[0]['id']; }
$room = $roomService->getById($roomId);
$reservations = $reservationService->getByRoomAndDateRange($roomId, $date);
$categories = $categoryService->getAll();
include __DIR__ . '/partials/header.php';
?>
<div class="timeline-controls">
    <div class="control-group">
        <label for="room-select">Room:</label>
        <select id="room-select" class="form-control" onchange="changeRoom(this.value)">
            <?php foreach ($rooms as $r): ?>
            <option value="<?php echo $r['id']; ?>" <?php echo $roomId == $r['id'] ? 'selected' : ''; ?>>
                <?php echo e($r['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="control-group">
        <label for="date-select">Date:</label>
        <input type="date" id="date-select" class="form-control" value="<?php echo $date; ?>" onchange="changeDate(this.value)">
    </div>
    <button class="btn btn-success" onclick="openBookingModal()">+ New Booking</button>
</div>

<div class="timeline-container" id="timeline">
    <div class="timeline-header">
        <h2><?php echo e($room['name'] ?? 'Select a room'); ?></h2>
        <p><?php echo date('l, F j, Y', strtotime($date)); ?></p>
    </div>
    
    <div class="timeline-grid">
        <?php
        $startHour = BUSINESS_START_HOUR;
        $endHour = BUSINESS_END_HOUR;
        $increment = $settings->getTimeIncrement();
        $totalSlots = ($endHour - $startHour) * (60 / $increment);
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $increment) {
                $time = sprintf('%02d:%02d', $hour, $minute);
                $datetime = $date . ' ' . $time . ':00';
                $slotStart = new DateTime($datetime);
                $slotEnd = clone $slotStart;
                $slotEnd->modify("+{$increment} minutes");
                
                $isBooked = false;
                $booking = null;
                $isPast = TimeHelpers::isPast($datetime);
                $isCurrent = false;
                
                foreach ($reservations as $res) {
                    if (TimeHelpers::hasOverlap($datetime, $slotEnd->format('Y-m-d H:i:s'), $res['start_time'], $res['end_time'])) {
                        $isBooked = true;
                        $booking = $res;
                        $isCurrent = TimeHelpers::isCurrent($res['start_time'], $res['end_time']);
                        break;
                    }
                }
                
                $slotClass = 'timeline-slot';
                if ($isPast) $slotClass .= ' slot-past';
                elseif ($isCurrent) $slotClass .= ' slot-current';
                elseif ($isBooked) $slotClass .= ' slot-booked';
                else $slotClass .= ' slot-available';
                
                $style = '';
                if ($isBooked && $booking) {
                    $style = 'background-color: ' . $booking['category_color'] . '; color: white;';
                }
        ?>
        <div class="<?php echo $slotClass; ?>" 
             data-time="<?php echo $datetime; ?>"
             data-booked="<?php echo $isBooked ? '1' : '0'; ?>"
             <?php if ($booking): ?>data-booking-id="<?php echo $booking['id']; ?>"<?php endif; ?>
             style="<?php echo $style; ?>"
             onclick="handleSlotClick(this)">
            <span class="slot-time"><?php echo $time; ?></span>
            <?php if ($isBooked && $booking && $settings->shouldShowTitles()): ?>
                <span class="slot-title"><?php echo e($booking['title']); ?></span>
                <span class="slot-name"><?php echo e($booking['full_name']); ?></span>
            <?php elseif ($isBooked): ?>
                <span class="slot-title">Reserved</span>
            <?php endif; ?>
        </div>
        <?php }} ?>
    </div>
</div>

<div id="bookingModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeBookingModal()">&times;</span>
        <h2>New Booking</h2>
        <form id="bookingForm" method="POST" action="<?php echo PUBLIC_URL; ?>/create.php">
            <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <input type="hidden" id="start_time" name="start_time">
            <input type="hidden" id="end_time" name="end_time">
            
            <div class="form-group">
                <label>Start Time *</label>
                <input type="time" id="start_time_display" class="form-control" required>
            </div>
            <div class="form-group">
                <label>End Time *</label>
                <input type="time" id="end_time_display" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
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
            <div class="form-group">
                <label>Phone (optional)</label>
                <input type="tel" name="phone" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Book Now</button>
        </form>
    </div>
</div>

<script>
var currentRoomId = <?php echo $roomId; ?>;
var currentDate = '<?php echo $date; ?>';
var timeIncrement = <?php echo $increment; ?>;

function changeRoom(roomId) {
    window.location.href = '?room_id=' + roomId + '&date=' + currentDate;
}

function changeDate(date) {
    window.location.href = '?room_id=' + currentRoomId + '&date=' + date;
}

function handleSlotClick(slot) {
    var isBooked = slot.dataset.booked === '1';
    var datetime = slot.dataset.time;
    
    if (isBooked) {
        var bookingId = slot.dataset.bookingId;
        window.location.href = '<?php echo PUBLIC_URL; ?>/view.php?id=' + bookingId;
    } else {
        openBookingModal(datetime);
    }
}

function openBookingModal(startTime) {
    var modal = document.getElementById('bookingModal');
    if (startTime) {
        var date = new Date(startTime);
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        var timeStr = hours + ':' + minutes;
        document.getElementById('start_time_display').value = timeStr;
        
        date.setMinutes(date.getMinutes() + timeIncrement);
        hours = String(date.getHours()).padStart(2, '0');
        minutes = String(date.getMinutes()).padStart(2, '0');
        document.getElementById('end_time_display').value = hours + ':' + minutes;
    }
    modal.style.display = 'flex';
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    var startTime = currentDate + ' ' + document.getElementById('start_time_display').value + ':00';
    var endTime = currentDate + ' ' + document.getElementById('end_time_display').value + ':00';
    document.getElementById('start_time').value = startTime;
    document.getElementById('end_time').value = endTime;
});

window.onclick = function(event) {
    var modal = document.getElementById('bookingModal');
    if (event.target == modal) {
        closeBookingModal();
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
