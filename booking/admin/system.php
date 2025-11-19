<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';
$auth = new AdminAuth();
$auth->requireAuth();
$pageTitle = 'System';
$db = Database::getInstance();
if (isPost()) {
    $action = input('action');
    if ($action === 'create_user') {
        $result = $auth->createUser(input('username'), input('password'));
        if ($result['success']) {
            setFlash('success', $result['message']);
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/system.php');
    } elseif ($action === 'delete_user') {
        $result = $auth->deleteUser(input('user_id'));
        if ($result['success']) {
            setFlash('success', $result['message']);
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/system.php');
    } elseif ($action === 'backup') {
        $backupFile = BASE_PATH . '/backups/backup_' . date('Y-m-d_His') . '.sql';
        if (!file_exists(BASE_PATH . '/backups')) {
            mkdir(BASE_PATH . '/backups', 0755, true);
        }
        $cmd = sprintf('mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            escapeshellarg($backupFile)
        );
        exec($cmd, $output, $return);
        if ($return === 0) {
            setFlash('success', 'Database backup created: ' . basename($backupFile));
        } else {
            setFlash('error', 'Failed to create backup');
        }
        redirect(ADMIN_URL . '/system.php');
    }
}
$users = $auth->getAllUsers();
$backupFiles = [];
if (file_exists(BASE_PATH . '/backups')) {
    $backupFiles = glob(BASE_PATH . '/backups/backup_*.sql');
    rsort($backupFiles);
}
include __DIR__ . '/partials/header.php';
?>
<div class="content-header"><h2>System Management</h2><p>Manage users, backups, and logs</p></div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Admin Users</div>
<form method="POST" style="margin-bottom:20px;">
<input type="hidden" name="action" value="create_user">
<div style="display:flex;gap:10px;">
<input type="text" name="username" class="form-control" placeholder="Username" required style="flex:1;">
<input type="password" name="password" class="form-control" placeholder="Password" required style="flex:1;">
<button type="submit" class="btn btn-primary">Add User</button>
</div>
</form>
<table class="table"><thead><tr><th>ID</th><th>Username</th><th>Created</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($users as $user): ?>
<tr><td><?php echo $user['id']; ?></td><td><?php echo e($user['username']); ?></td><td><?php echo e($user['created_at']); ?></td>
<td><?php if ($user['id'] != $auth->getUserId()): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
<input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>
<?php else: ?><span style="color:#999;">Current User</span><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table>
</div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Database Backup</div>
<form method="POST"><input type="hidden" name="action" value="backup"><button type="submit" class="btn btn-primary">Create Backup Now</button></form>
<?php if (!empty($backupFiles)): ?><h4 style="margin-top:20px;">Recent Backups</h4><ul>
<?php foreach (array_slice($backupFiles, 0, 10) as $file): ?>
<li><?php echo basename($file); ?> (<?php echo number_format(filesize($file)/1024, 2); ?> KB)</li>
<?php endforeach; ?></ul><?php endif; ?>
</div>
<div class="card"><div class="card-header">System Information</div>
<table class="table"><tr><td><strong>App Version</strong></td><td><?php echo APP_VERSION; ?></td></tr>
<tr><td><strong>PHP Version</strong></td><td><?php echo PHP_VERSION; ?></td></tr>
<tr><td><strong>Database</strong></td><td><?php echo DB_NAME; ?></td></tr>
<tr><td><strong>Base Path</strong></td><td><?php echo BASE_PATH; ?></td></tr></table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
