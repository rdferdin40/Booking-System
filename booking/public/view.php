<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/TimeHelpers.php';
$pageTitle = 'View Booking';
$id = input('id');
if (!$id) {
    redirect(PUBLIC_URL . '/edit.php');
}
$reservationService = new ReservationService();
$reservation = $reservationService->getById($id);
if (!$reservation) {
    setFlash('error', 'Booking not found');
    redirect(PUBLIC_URL . '/index.php');
}
include __DIR__ . '/partials/header.php';
?>
<div class="view-booking-container">
    <h2><?php echo e($reservation['title']); ?></h2>
    
    <div class="booking-card-large">
        <div class="booking-header" style="background:<?php echo $reservation['category_color']; ?>">
            <span><?php echo e($reservation['category_name']); ?></span>
        </div>
        
        <div class="booking-body">
            <div class="detail-section">
                <div class="detail-label">Room</div>
                <div class="detail-value"><?php echo e($reservation['room_name']); ?></div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Date</div>
                <div class="detail-value"><?php echo TimeHelpers::format($reservation['start_time'], 'l, F j, Y'); ?></div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Time</div>
                <div class="detail-value">
                    <?php echo TimeHelpers::format($reservation['start_time'], 'g:i A'); ?> - 
                    <?php echo TimeHelpers::format($reservation['end_time'], 'g:i A'); ?>
                    <small>(<?php echo TimeHelpers::formatDuration(TimeHelpers::getDurationMinutes($reservation['start_time'], $reservation['end_time'])); ?>)</small>
                </div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Booked By</div>
                <div class="detail-value"><?php echo e($reservation['full_name']); ?></div>
            </div>
            
            <div class="detail-section">
                <div class="detail-label">Department</div>
                <div class="detail-value"><?php echo e($reservation['department']); ?></div>
            </div>
            
            <?php if ($reservation['email']): ?>
            <div class="detail-section">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo e($reservation['email']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($reservation['phone']): ?>
            <div class="detail-section">
                <div class="detail-label">Phone</div>
                <div class="detail-value"><?php echo e($reservation['phone']); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="detail-section">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <?php if (TimeHelpers::isCurrent($reservation['start_time'], $reservation['end_time'])): ?>
                        <span class="status-badge status-current">In Progress</span>
                    <?php elseif (TimeHelpers::isFuture($reservation['start_time'])): ?>
                        <span class="status-badge status-upcoming">Upcoming</span>
                    <?php else: ?>
                        <span class="status-badge status-past">Completed</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="<?php echo PUBLIC_URL; ?>/index.php" class="btn btn-secondary">Back to Timeline</a>
        <a href="<?php echo PUBLIC_URL; ?>/edit.php?pin=<?php echo $reservation['pin_code']; ?>" class="btn btn-primary">Manage Booking</a>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
