<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';
require_once __DIR__ . '/../config/settings.php';
$auth = new AdminAuth();
$auth->requireAuth();
$pageTitle = 'Settings';
$settings = Settings::getInstance();
if (isPost()) {
    $data = [
        'max_duration_minutes' => (int)input('max_duration_minutes', 240),
        'time_increment' => (int)input('time_increment', 15),
        'auto_release_mode' => input('auto_release_mode', 'instant'),
        'grace_period_minutes' => (int)input('grace_period_minutes', 0),
        'show_titles' => input('show_titles') ? 1 : 0,
        'screensaver_enabled' => input('screensaver_enabled') ? 1 : 0,
        'screensaver_timeout' => (int)input('screensaver_timeout', 300),
        'public_view_mode' => input('public_view_mode', 'timeline'),
        'language' => input('language', 'en'),
        'email_enabled' => input('email_enabled') ? 1 : 0,
        'sms_enabled' => input('sms_enabled') ? 1 : 0
    ];
    if ($settings->updateMultiple($data)) {
        setFlash('success', 'Settings updated successfully');
        redirect(ADMIN_URL . '/settings.php');
    } else {
        $error = 'Failed to update settings';
    }
}
$currentSettings = $settings->getAll();
include __DIR__ . '/partials/header.php';
?>
<div class="content-header"><h2>System Settings</h2><p>Configure booking system</p></div>
<form method="POST">
<div class="card" style="margin-bottom:20px;"><div class="card-header">Booking Settings</div>
<div class="form-group"><label>Max Duration (minutes)</label><input type="number" name="max_duration_minutes" class="form-control" value="<?php echo $currentSettings['max_duration_minutes']; ?>" min="15" max="1440"></div>
<div class="form-group"><label>Time Increment (minutes)</label><select name="time_increment" class="form-control">
<option value="15" <?php echo $currentSettings['time_increment'] == 15 ? 'selected' : ''; ?>>15 minutes</option>
<option value="30" <?php echo $currentSettings['time_increment'] == 30 ? 'selected' : ''; ?>>30 minutes</option>
<option value="60" <?php echo $currentSettings['time_increment'] == 60 ? 'selected' : ''; ?>>60 minutes</option>
</select></div>
<div class="form-group"><label>Auto Release Mode</label><select name="auto_release_mode" class="form-control">
<option value="instant" <?php echo $currentSettings['auto_release_mode'] == 'instant' ? 'selected' : ''; ?>>Instant</option>
<option value="grace" <?php echo $currentSettings['auto_release_mode'] == 'grace' ? 'selected' : ''; ?>>Grace Period</option>
</select></div>
<div class="form-group"><label>Grace Period (minutes)</label><input type="number" name="grace_period_minutes" class="form-control" value="<?php echo $currentSettings['grace_period_minutes']; ?>" min="0" max="60"></div>
</div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Display Settings</div>
<div class="form-group"><label><input type="checkbox" name="show_titles" value="1" <?php echo $currentSettings['show_titles'] ? 'checked' : ''; ?>> Show titles on public display</label></div>
<div class="form-group"><label>Public View Mode</label><select name="public_view_mode" class="form-control">
<option value="timeline" <?php echo $currentSettings['public_view_mode'] == 'timeline' ? 'selected' : ''; ?>>Timeline</option>
<option value="calendar" <?php echo $currentSettings['public_view_mode'] == 'calendar' ? 'selected' : ''; ?>>Calendar</option>
<option value="list" <?php echo $currentSettings['public_view_mode'] == 'list' ? 'selected' : ''; ?>>List</option>
</select></div>
<div class="form-group"><label><input type="checkbox" name="screensaver_enabled" value="1" <?php echo $currentSettings['screensaver_enabled'] ? 'checked' : ''; ?>> Enable screensaver</label></div>
<div class="form-group"><label>Screensaver Timeout (seconds)</label><input type="number" name="screensaver_timeout" class="form-control" value="<?php echo $currentSettings['screensaver_timeout']; ?>" min="60" max="3600"></div>
<div class="form-group"><label>Language</label><select name="language" class="form-control">
<option value="en" <?php echo $currentSettings['language'] == 'en' ? 'selected' : ''; ?>>English</option>
<option value="es" <?php echo $currentSettings['language'] == 'es' ? 'selected' : ''; ?>>Español</option>
</select></div>
</div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Notifications</div>
<div class="form-group"><label><input type="checkbox" name="email_enabled" value="1" <?php echo $currentSettings['email_enabled'] ? 'checked' : ''; ?>> Enable email notifications</label></div>
<div class="form-group"><label><input type="checkbox" name="sms_enabled" value="1" <?php echo $currentSettings['sms_enabled'] ? 'checked' : ''; ?>> Enable SMS notifications</label></div>
<p><small>Configure email/SMS settings in <a href="<?php echo ADMIN_URL; ?>/notifications.php">Notifications</a> page</small></p>
</div>
<button type="submit" class="btn btn-primary">Save Settings</button>
</form>
<?php include __DIR__ . '/partials/footer.php'; ?>
