<?php
/**
 * Admin Panel Header
 * Includes navigation and user info
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

if (!isset($auth) || !$auth->isLoggedIn()) {
    redirect(ADMIN_URL . '/login.php');
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>Admin - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/admin.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            background: #1a252f;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }

        .sidebar-nav {
            padding: 10px 0;
        }

        .nav-item {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #3498db;
        }

        .nav-item i {
            margin-right: 10px;
            width: 20px;
            display: inline-block;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 250px;
            padding: 20px;
            background: #1a252f;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .user-name {
            flex: 1;
            font-size: 14px;
        }

        .btn-logout {
            width: 100%;
            padding: 8px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            font-size: 14px;
        }

        .btn-logout:hover {
            background: #c0392b;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        .content-header {
            margin-bottom: 30px;
        }

        .content-header h2 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .content-header p {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background: #27ae60;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #d68910;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
                z-index: 1000;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Admin Panel</p>
            </div>

            <nav class="sidebar-nav">
                <a href="<?php echo ADMIN_URL; ?>/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                    <span>📊</span> Dashboard
                </a>
                <a href="<?php echo ADMIN_URL; ?>/reservations.php" class="nav-item <?php echo $currentPage === 'reservations' ? 'active' : ''; ?>">
                    <span>📅</span> Reservations
                </a>
                <a href="<?php echo ADMIN_URL; ?>/rooms.php" class="nav-item <?php echo $currentPage === 'rooms' ? 'active' : ''; ?>">
                    <span>🏢</span> Rooms
                </a>
                <a href="<?php echo ADMIN_URL; ?>/categories.php" class="nav-item <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                    <span>🏷️</span> Categories
                </a>
                <a href="<?php echo ADMIN_URL; ?>/settings.php" class="nav-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <span>⚙️</span> Settings
                </a>
                <a href="<?php echo ADMIN_URL; ?>/notifications.php" class="nav-item <?php echo $currentPage === 'notifications' ? 'active' : ''; ?>">
                    <span>📧</span> Notifications
                </a>
                <a href="<?php echo ADMIN_URL; ?>/system.php" class="nav-item <?php echo $currentPage === 'system' ? 'active' : ''; ?>">
                    <span>🔧</span> System
                </a>
                <a href="<?php echo PUBLIC_URL; ?>/index.php" class="nav-item" target="_blank">
                    <span>🌐</span> View Public Site
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($auth->getUsername(), 0, 1)); ?>
                    </div>
                    <div class="user-name">
                        <?php echo e($auth->getUsername()); ?>
                    </div>
                </div>
                <a href="<?php echo ADMIN_URL; ?>/logout.php" class="btn-logout">Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <?php
            // Display flash messages
            $flash = getFlash();
            if ($flash):
                $alertClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
            ?>
                <div class="alert <?php echo $alertClass; ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>
