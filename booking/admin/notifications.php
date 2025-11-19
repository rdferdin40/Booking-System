<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';
require_once __DIR__ . '/../core/NotificationService.php';
$auth = new AdminAuth();
$auth->requireAuth();
$pageTitle = 'Notifications';
$notificationService = new NotificationService();
if (isPost()) {
    $action = input('action');
    if ($action === 'update') {
        $result = $notificationService->updateSettings($_POST);
        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect(ADMIN_URL . '/notifications.php');
        } else {
            $error = $result['error'];
        }
    } elseif ($action === 'test_email') {
        $result = $notificationService->testEmail(input('test_email'));
        if ($result['success']) {
            setFlash('success', 'Test email sent successfully');
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/notifications.php');
    } elseif ($action === 'test_sms') {
        $result = $notificationService->testSMS(input('test_phone'));
        if ($result['success']) {
            setFlash('success', 'Test SMS sent successfully');
        } else {
            setFlash('error', $result['error']);
        }
        redirect(ADMIN_URL . '/notifications.php');
    }
}
$db = Database::getInstance();
$settings = $db->queryOne("SELECT * FROM notification_settings LIMIT 1");
include __DIR__ . '/partials/header.php';
?>
<div class="content-header"><h2>Notification Settings</h2><p>Configure email and SMS notifications</p></div>
<form method="POST">
<input type="hidden" name="action" value="update">
<div class="card" style="margin-bottom:20px;"><div class="card-header">Email Settings (SMTP)</div>
<div class="form-group"><label>From Email</label><input type="email" name="email_from" class="form-control" value="<?php echo e($settings['email_from']); ?>"></div>
<div class="form-group"><label>SMTP Host</label><input type="text" name="smtp_host" class="form-control" value="<?php echo e($settings['smtp_host']); ?>" placeholder="smtp.gmail.com"></div>
<div class="form-group"><label>SMTP Username</label><input type="text" name="smtp_username" class="form-control" value="<?php echo e($settings['smtp_username']); ?>"></div>
<div class="form-group"><label>SMTP Password</label><input type="password" name="smtp_password" class="form-control" placeholder="Leave blank to keep current"></div>
<div class="form-group"><label>SMTP Port</label><input type="number" name="smtp_port" class="form-control" value="<?php echo $settings['smtp_port']; ?>" placeholder="587"></div>
<div class="form-group"><label>Encryption</label><select name="smtp_encryption" class="form-control">
<option value="tls" <?php echo $settings['smtp_encryption'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
<option value="ssl" <?php echo $settings['smtp_encryption'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
</select></div>
</div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">SMS Settings (API)</div>
<div class="form-group"><label>SMS API URL</label><input type="url" name="sms_api_url" class="form-control" value="<?php echo e($settings['sms_api_url']); ?>" placeholder="https://api.sms-provider.com/send"></div>
<div class="form-group"><label>API Key</label><input type="text" name="sms_api_key" class="form-control" value="<?php echo e($settings['sms_api_key']); ?>"></div>
<div class="form-group"><label>Method</label><select name="sms_method" class="form-control">
<option value="POST" <?php echo $settings['sms_method'] == 'POST' ? 'selected' : ''; ?>>POST</option>
<option value="GET" <?php echo $settings['sms_method'] == 'GET' ? 'selected' : ''; ?>>GET</option>
</select></div>
<div class="form-group"><label>Phone Parameter Name</label><input type="text" name="sms_phone_field" class="form-control" value="<?php echo e($settings['sms_phone_field']); ?>" placeholder="to"></div>
<div class="form-group"><label>Message Parameter Name</label><input type="text" name="sms_message_field" class="form-control" value="<?php echo e($settings['sms_message_field']); ?>" placeholder="message"></div>
</div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Notification Triggers</div>
<div class="form-group"><label><input type="checkbox" name="send_on_create" value="1" <?php echo $settings['send_on_create'] ? 'checked' : ''; ?>> Send on booking creation</label></div>
<div class="form-group"><label><input type="checkbox" name="send_on_update" value="1" <?php echo $settings['send_on_update'] ? 'checked' : ''; ?>> Send on booking update</label></div>
<div class="form-group"><label><input type="checkbox" name="send_on_cancel" value="1" <?php echo $settings['send_on_cancel'] ? 'checked' : ''; ?>> Send on booking cancellation</label></div>
</div>
<button type="submit" class="btn btn-primary">Save Settings</button>
</form>
<div class="card" style="margin-top:20px;"><div class="card-header">Test Notifications</div>
<form method="POST" style="margin-bottom:15px;">
<input type="hidden" name="action" value="test_email">
<div class="form-group"><label>Test Email</label><input type="email" name="test_email" class="form-control" placeholder="test@example.com" required></div>
<button type="submit" class="btn btn-success">Send Test Email</button>
</form>
<form method="POST">
<input type="hidden" name="action" value="test_sms">
<div class="form-group"><label>Test Phone</label><input type="tel" name="test_phone" class="form-control" placeholder="+1234567890" required></div>
<button type="submit" class="btn btn-success">Send Test SMS</button>
</form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
