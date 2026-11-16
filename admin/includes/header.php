<?php
require_once __DIR__ . '/auth.php';

// Path prefix for admin pages
$base       = $base ?? '';
$pageTitle  = $pageTitle ?? 'Admin Panel';
$activePage = $activePage ?? '';

$adminName      = $_SESSION['user']['name'] ?? ($_SESSION['user_name'] ?? 'Admin');
$adminRole      = $currentUserRole === 'admin' ? 'Super Admin' : 'Lab Assistant';
$pendingReports = 5;
$unreadNotes    = 3;

try {
    $rCount = $conn->query("SELECT COUNT(*) FROM fault_reports WHERE status = 'pending'")->fetch_row()[0] ?? 0;
    if ($rCount > 0) $pendingReports = (int)$rCount;

    $nCount = $conn->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetch_row()[0] ?? 0;
    if ($nCount > 0) $unreadNotes = (int)$nCount;
} catch (mysqli_sql_exception $e) {
    // Continue with default indicator counts
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | MMC Admin Panel</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/admin-root.css">
    <link rel="stylesheet" href="<?= $base ?>assets/css/admin-style.css">
    <?php if (!empty($pageCss)): ?>
        <link rel="stylesheet" href="<?= $base ?>assets/css/<?= (substr($pageCss, -4) === '.css') ? $pageCss : $pageCss . '.css' ?>">
    <?php endif; ?>
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="admin-main">

        <header class="admin-header">
            <button type="button" class="admin-toggle" id="adminToggle" aria-label="Toggle menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="admin-header-title">
                <h1>Computer Laboratory Management System</h1>
                <p>Mahendra Multiple Campus</p>
            </div>

            <div class="admin-header-right">
                <a href="<?= $base ?>notifications.php" class="admin-bell" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($unreadNotes > 0): ?>
                        <span class="bell-badge"><?= $unreadNotes ?></span>
                    <?php endif; ?>
                </a>

                <div class="admin-date-pill">
                    <i class="fa-regular fa-calendar"></i>
                    <span><?= date('M d, Y') ?></span>
                </div>

                <div class="admin-user-pill" id="adminUserMenu" role="button" tabindex="0" title="Account Menu">
                    <img src="<?= $base ?>assets/images/admin-avatar.jpg" alt="Admin" class="admin-user-img" loading="lazy">
                    <span class="admin-user-name"><?= htmlspecialchars($adminName) ?></span>
                    <i class="fa-solid fa-chevron-down admin-user-caret"></i>

                    <div class="admin-user-dropdown" id="adminUserDropdown">
                        <div class="admin-dropdown-header">
                            <strong><?= htmlspecialchars($adminName) ?></strong>
                            <small><?= $adminRole ?></small>
                        </div>
                        <a href="<?= $base ?>settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
                        <a href="<?= $base ?>notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
                        <div class="admin-dropdown-divider"></div>
                        <a href="<?= $base ?>logout.php" class="admin-logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content">
