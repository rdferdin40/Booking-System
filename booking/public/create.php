<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/NotificationService.php';
$pageTitle = 'Create Booking';
if (!isPost()) {
    redirect(PUBLIC_URL . '/index.php');
}
$reservationService = new ReservationService();
$notificationService = new NotificationService();
$result = $reservationService->create($_POST);
if ($result['success']) {
    $reservation = $result['reservation'];
    $notificationService->sendCreatedNotification($reservation);
    $pageTitle = 'Booking Confirmed';
    include __DIR__ . '/partials/header.php';
    ?>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h2>Booking Confirmed!</h2>
        <p class="lead">Your reservation has been successfully created.</p>
        
        <div class="booking-details">
            <h3>Booking Details</h3>
            <div class="detail-row"><strong>Room:</strong> <?php echo e($reservation['room_name']); ?></div>
            <div class="detail-row"><strong>Date & Time:</strong> <?php echo TimeHelpers::format($reservation['start_time']); ?> - <?php echo TimeHelpers::format($reservation['end_time'], 'g:i A'); ?></div>
            <div class="detail-row"><strong>Title:</strong> <?php echo e($reservation['title']); ?></div>
            <div class="detail-row"><strong>Category:</strong> <span style="background:<?php echo $reservation['category_color']; ?>;color:white;padding:4px 8px;border-radius:4px;"><?php echo e($reservation['category_name']); ?></span></div>
            <div class="detail-row"><strong>Booked by:</strong> <?php echo e($reservation['full_name']); ?></div>
        </div>
        
        <div class="pin-display">
            <h3>Your PIN Code</h3>
            <div class="pin-code"><?php echo $reservation['pin_code']; ?></div>
            <p class="pin-note">Save this PIN! You'll need it to view, edit, or cancel your booking.</p>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo PUBLIC_URL; ?>/index.php" class="btn btn-primary">Back to Timeline</a>
            <a href="<?php echo PUBLIC_URL; ?>/edit.php?pin=<?php echo $reservation['pin_code']; ?>" class="btn btn-secondary">View Booking</a>
        </div>
    </div>
    <?php
    include __DIR__ . '/partials/footer.php';
} else {
    setFlash('error', $result['error'] ?? 'Failed to create booking');
    redirect(PUBLIC_URL . '/index.php');
}
?>
