<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | MMC Computer Lab</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <?php if (isset($pageCss)) { ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/<?= $pageCss ?>.css">
    <?php } ?>
</head>
<body>
<div class="layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="layout-main">

        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>

            <div class="topbar-title">
                <h1>Computer Lab Management System</h1>
                <p>Mahendra Multiple Campus</p>
            </div>

            <?php
            $unreadCount = 0;
            try {
                $uid = (int)($user['id'] ?? 0);
                $stmtUnread = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE (user_id IS NULL OR user_id = ?) AND is_read = 0");
                $stmtUnread->bind_param('i', $uid);
                $stmtUnread->execute();
                $unreadCount = (int)$stmtUnread->get_result()->fetch_row()[0];
                $stmtUnread->close();
            } catch (Exception $e) {}
            ?>
            <a href="<?= BASE_URL ?>notifications.php" class="topbar-bell <?= $unreadCount > 0 ? 'has-unread' : '' ?>" title="Notifications">
                <i class="fa-regular fa-bell"></i>
            </a>

            <div class="topbar-user" id="userMenu">
                <?php if (!empty($user['profile_image']) && file_exists(__DIR__ . '/../uploads/profiles/' . $user['profile_image'])): ?>
                    <img class="topbar-avatar"
                         src="<?= BASE_URL ?>uploads/profiles/<?= e($user['profile_image']) ?>"
                         alt="Profile">
                <?php else: ?>
                    <img class="topbar-avatar"
                         src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name'] ?? $user['name'] ?? 'User') ?>&background=e8f0fe&color=1d63e8&bold=true"
                         alt="Profile">
                <?php endif; ?>
                <div class="topbar-info">
                    <strong><?= e($user['full_name'] ?? $user['name'] ?? '') ?></strong>
                    <small>
                        <?php if (($user['role'] ?? '') == 'student') { ?>
                            <?= e(($user['program'] ?? '') . (!empty($user['semester']) ? ', ' . $user['semester'] : '')) ?>
                        <?php } else { ?>
                            <?= e(statusLabel($user['role'] ?? '')) ?>
                        <?php } ?>
                    </small>
                </div>
                <i class="fa-solid fa-chevron-down topbar-arrow"></i>

                <div class="topbar-dropdown">
                    <a href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user"></i> My Profile</a>
                    <a href="<?= BASE_URL ?>notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a>
                    <a href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
        </header>

        <main class="content">
            <?php showMessage(); ?>