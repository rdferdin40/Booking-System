<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';
require_once __DIR__ . '/../core/ReservationService.php';
require_once __DIR__ . '/../core/RoomService.php';
require_once __DIR__ . '/../core/CategoryService.php';
require_once __DIR__ . '/../core/TimeHelpers.php';
$auth = new AdminAuth();
$auth->requireAuth();
$pageTitle = 'Reservations';
$reservationService = new ReservationService();
$roomService = new RoomService();
$categoryService = new CategoryService();
if (isPost()) {
    $action = input('action');
    if ($action === 'delete') {
        $result = $reservationService->cancel(input('id'));
        if ($result['success']) { setFlash('success', $result['message']); } else { setFlash('error', $result['error']); }
        redirect(ADMIN_URL . '/reservations.php');
    }
}
$filters = ['search' => input('search'), 'room_id' => input('room_id'), 'category_id' => input('category_id')];
$reservations = $reservationService->getAll($filters);
$rooms = $roomService->getAll();
$categories = $categoryService->getAll();
include __DIR__ . '/partials/header.php';
?>
<div class="content-header"><h2>Reservations</h2><p>Manage all bookings</p></div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Filter Reservations</div>
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
<input type="text" name="search" placeholder="Search name, email, phone..." class="form-control" style="flex:1;min-width:200px;" value="<?php echo e(input('search')); ?>">
<select name="room_id" class="form-control" style="width:200px;"><option value="">All Rooms</option><?php foreach($rooms as $r): ?><option value="<?php echo $r['id']; ?>" <?php echo input('room_id') == $r['id'] ? 'selected' : ''; ?>><?php echo e($r['name']); ?></option><?php endforeach; ?></select>
<select name="category_id" class="form-control" style="width:200px;"><option value="">All Categories</option><?php foreach($categories as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo input('category_id') == $c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option><?php endforeach; ?></select>
<button type="submit" class="btn btn-primary">Filter</button>
<a href="<?php echo ADMIN_URL; ?>/reservations.php" class="btn btn-secondary">Clear</a>
</form></div>
<div class="card"><div class="card-header">All Reservations (<?php echo count($reservations); ?>)</div>
<?php if (empty($reservations)): ?><p style="padding:20px;text-align:center;color:#999;">No reservations found</p><?php else: ?>
<table class="table"><thead><tr><th>ID</th><th>Room</th><th>Title</th><th>Booked By</th><th>Date/Time</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($reservations as $res):
$isCurrent = TimeHelpers::isCurrent($res['start_time'], $res['end_time']);
$isPast = TimeHelpers::isPast($res['end_time']);
?>
<tr><td><?php echo $res['id']; ?></td><td><?php echo e($res['room_name']); ?></td><td><strong><?php echo e($res['title']); ?></strong><br><small><?php echo e($res['category_name']); ?></small></td>
<td><?php echo e($res['full_name']); ?><br><small><?php echo e($res['department']); ?></small></td>
<td><?php echo TimeHelpers::format($res['start_time'], 'M d, g:i A'); ?><br><small>to <?php echo TimeHelpers::format($res['end_time'], 'g:i A'); ?></small></td>
<td><?php if ($isCurrent): ?><span style="color:#2ecc71;">● Active</span><?php elseif ($isPast): ?><span style="color:#95a5a6;">Completed</span><?php else: ?><span style="color:#3498db;">Upcoming</span><?php endif; ?></td>
<td><form method="POST" style="display:inline;" onsubmit="return confirm('Cancel this reservation?');">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $res['id']; ?>">
<button type="submit" class="btn btn-sm btn-danger">Cancel</button></form></td></tr>
<?php endforeach; ?></tbody></table><?php endif; ?></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
