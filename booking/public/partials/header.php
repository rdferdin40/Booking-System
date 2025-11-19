<?php
/**
 * Public Interface Header
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/public.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1><?php echo APP_NAME; ?></h1>
            <div class="header-actions">
                <?php if (!isset($hideBackButton) || !$hideBackButton): ?>
                <a href="<?php echo PUBLIC_URL; ?>/index.php" class="btn btn-secondary">Timeline</a>
                <?php endif; ?>
                <a href="<?php echo PUBLIC_URL; ?>/edit.php" class="btn btn-primary">Find Booking</a>
            </div>
        </header>

        <main class="app-content">
            <?php
            $flash = getFlash();
            if ($flash):
                $alertClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
            ?>
                <div class="alert <?php echo $alertClass; ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>
