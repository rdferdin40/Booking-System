<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/NotificationService.php';
require_once __DIR__ . '/../core/TimeHelpers.php';
$pageTitle = 'Find Booking';
$reservationService = new ReservationService();
$notificationService = new NotificationService();
$reservation = null;
$searchResults = [];
$error = '';
if (isPost()) {
    $action = input('action');
    if ($action === 'find_by_pin') {
        $pin = input('pin');
        $reservation = $reservationService->getByPin($pin);
        if (!$reservation) {
            $error = 'No booking found with this PIN';
        }
    } elseif ($action === 'find_by_contact') {
        $contact = input('contact');
        $searchResults = $reservationService->findByContact($contact);
        if (empty($searchResults)) {
            $error = 'No bookings found for this email/phone';
        }
    } elseif ($action === 'cancel') {
        $pin = input('pin');
        $existing = $reservationService->getByPin($pin);
        if ($existing) {
            $result = $reservationService->cancel($existing['id']);
            if ($result['success']) {
                $notificationService->sendCancelledNotification($existing);
                setFlash('success', 'Booking cancelled successfully');
                redirect(PUBLIC_URL . '/index.php');
            } else {
                $error = $result['error'];
            }
        } else {
            $error = 'Booking not found';
        }
    }
} elseif (input('pin')) {
    $reservation = $reservationService->getByPin(input('pin'));
}
include __DIR__ . '/partials/header.php';
?>
<div class="find-booking-container">
    <h2>Find Your Booking</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    
    <?php if ($reservation): ?>
        <div class="booking-card">
            <h3><?php echo e($reservation['title']); ?></h3>
            <div class="booking-info">
                <div class="info-row"><strong>Room:</strong> <?php echo e($reservation['room_name']); ?></div>
                <div class="info-row"><strong>Time:</strong> <?php echo TimeHelpers::format($reservation['start_time']); ?> - <?php echo TimeHelpers::format($reservation['end_time'], 'g:i A'); ?></div>
                <div class="info-row"><strong>Category:</strong> <?php echo e($reservation['category_name']); ?></div>
                <div class="info-row"><strong>Booked by:</strong> <?php echo e($reservation['full_name']); ?> (<?php echo e($reservation['department']); ?>)</div>
                <div class="info-row"><strong>PIN:</strong> <code><?php echo $reservation['pin_code']; ?></code></div>
            </div>
            
            <?php if (TimeHelpers::isFuture($reservation['start_time'])): ?>
            <div class="booking-actions">
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="pin" value="<?php echo $reservation['pin_code']; ?>">
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </form>
            </div>
            <?php elseif (TimeHelpers::isCurrent($reservation['start_time'], $reservation['end_time'])): ?>
                <div class="alert alert-info">This booking is currently in progress</div>
            <?php else: ?>
                <div class="alert alert-warning">This booking has ended</div>
            <?php endif; ?>
        </div>
    <?php elseif (!empty($searchResults)): ?>
        <h3>Your Bookings</h3>
        <p>Select a booking to view details:</p>
        <?php foreach ($searchResults as $res): ?>
        <div class="search-result" onclick="window.location.href='?pin=<?php echo $res['pin_code']; ?>'">
            <div class="result-title"><?php echo e($res['title']); ?></div>
            <div class="result-details">
                <?php echo e($res['room_name']); ?> • <?php echo TimeHelpers::format($res['start_time'], 'M d, g:i A'); ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="search-methods">
            <div class="search-method">
                <h3>Option 1: Enter PIN</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="find_by_pin">
                    <div class="form-group">
                        <input type="text" name="pin" class="form-control" placeholder="Enter 6-digit PIN" pattern="[0-9]{6}" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Find Booking</button>
                </form>
            </div>
            
            <div class="search-divider">OR</div>
            
            <div class="search-method">
                <h3>Option 2: Email or Phone</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="find_by_contact">
                    <div class="form-group">
                        <input type="text" name="contact" class="form-control" placeholder="Enter email or phone" required>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-block">Search Bookings</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
