<?php
/**
 * Admin Dashboard
 * Main admin panel overview with statistics
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/RoomService.php';
require_once __DIR__ . '/../core/TimeHelpers.php';

$auth = new AdminAuth();
$auth->requireAuth();

$pageTitle = 'Dashboard';

$db = Database::getInstance();
$reservationService = new ReservationService();
$roomService = new RoomService();

// Get statistics
$sql = "SELECT COUNT(*) as total FROM reservations WHERE start_time >= NOW()";
$upcomingCount = $db->queryOne($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM reservations WHERE DATE(start_time) = CURDATE()";
$todayCount = $db->queryOne($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM rooms WHERE is_active = 1";
$activeRoomsCount = $db->queryOne($sql)['total'];

$sql = "SELECT COUNT(*) as total FROM reservations";
$totalBookings = $db->queryOne($sql)['total'];

// Get today's reservations
$todayReservations = $db->query("
    SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
    FROM reservations r
    INNER JOIN rooms rm ON r.room_id = rm.id
    INNER JOIN categories c ON r.category_id = c.id
    WHERE DATE(r.start_time) = CURDATE()
    ORDER BY r.start_time ASC
    LIMIT 10
");

// Get recent reservations
$recentReservations = $db->query("
    SELECT r.*, rm.name AS room_name, c.name AS category_name, c.color AS category_color
    FROM reservations r
    INNER JOIN rooms rm ON r.room_id = rm.id
    INNER JOIN categories c ON r.category_id = c.id
    ORDER BY r.created_at DESC
    LIMIT 5
");

include __DIR__ . '/partials/header.php';
?>

<div class="content-header">
    <h2>Dashboard</h2>
    <p>Welcome back, <?php echo e($auth->getUsername()); ?>!</p>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Upcoming Bookings</div>
        <div style="font-size: 36px; font-weight: bold;"><?php echo $upcomingCount; ?></div>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Today's Bookings</div>
        <div style="font-size: 36px; font-weight: bold;"><?php echo $todayCount; ?></div>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Active Rooms</div>
        <div style="font-size: 36px; font-weight: bold;"><?php echo $activeRoomsCount; ?></div>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Total Bookings</div>
        <div style="font-size: 36px; font-weight: bold;"><?php echo $totalBookings; ?></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">Quick Actions</div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?php echo ADMIN_URL; ?>/reservations.php?action=new" class="btn btn-primary">New Booking</a>
        <a href="<?php echo ADMIN_URL; ?>/rooms.php?action=new" class="btn btn-success">Add Room</a>
        <a href="<?php echo ADMIN_URL; ?>/categories.php?action=new" class="btn btn-warning">Add Category</a>
        <a href="<?php echo PUBLIC_URL; ?>/index.php" class="btn btn-secondary" target="_blank">View Public Site</a>
    </div>
</div>

<!-- Today's Reservations -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">Today's Reservations</div>
    <?php if (empty($todayReservations)): ?>
        <p style="color: #999; padding: 20px; text-align: center;">No bookings for today</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Title</th>
                    <th>Booked By</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todayReservations as $res):
                    $isCurrent = TimeHelpers::isCurrent($res['start_time'], $res['end_time']);
                    $isPast = TimeHelpers::isPast($res['end_time']);
                ?>
                <tr>
                    <td>
                        <?php echo TimeHelpers::format($res['start_time'], 'g:i A'); ?><br>
                        <small style="color: #999;"><?php echo TimeHelpers::format($res['end_time'], 'g:i A'); ?></small>
                    </td>
                    <td><?php echo e($res['room_name']); ?></td>
                    <td><strong><?php echo e($res['title']); ?></strong></td>
                    <td><?php echo e($res['full_name']); ?></td>
                    <td>
                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; background: <?php echo e($res['category_color']); ?>; color: white;">
                            <?php echo e($res['category_name']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($isCurrent): ?>
                            <span style="color: #2ecc71; font-weight: bold;">● In Progress</span>
                        <?php elseif ($isPast): ?>
                            <span style="color: #95a5a6;">Completed</span>
                        <?php else: ?>
                            <span style="color: #3498db;">Upcoming</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding: 15px; text-align: center; border-top: 1px solid #ecf0f1;">
            <a href="<?php echo ADMIN_URL; ?>/reservations.php" class="btn btn-primary btn-sm">View All Reservations</a>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Bookings -->
<div class="card">
    <div class="card-header">Recent Bookings</div>
    <?php if (empty($recentReservations)): ?>
        <p style="color: #999; padding: 20px; text-align: center;">No recent bookings</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Created</th>
                    <th>Room</th>
                    <th>Title</th>
                    <th>Date/Time</th>
                    <th>Booked By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentReservations as $res): ?>
                <tr>
                    <td><?php echo TimeHelpers::getRelativeTime($res['created_at']); ?></td>
                    <td><?php echo e($res['room_name']); ?></td>
                    <td><strong><?php echo e($res['title']); ?></strong></td>
                    <td>
                        <?php echo TimeHelpers::format($res['start_time'], 'M d, g:i A'); ?>
                    </td>
                    <td><?php echo e($res['full_name']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
