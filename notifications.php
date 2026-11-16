<?php
require_once __DIR__ . '/config/db.php';

// Authentication guard
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if (($_SESSION['user']['role'] ?? '') === 'admin') {
    header('Location: ' . BASE_URL . 'admin/');
    exit;
}

$userId     = (int) $_SESSION['user']['id'];
$pageTitle  = 'Notifications';
$activePage = 'notifications';
$pageCss    = 'notifications';
$pageJs     = 'notifications';

// ── Handle Actions (Mark as Read / Mark All as Read) ──────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Mark single notification as read
    if ($action === 'mark_read') {
        $notifId = (int) ($_POST['notification_id'] ?? 0);
        if ($notifId > 0) {
            $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id IS NULL OR user_id = ?)");
            $stmt->bind_param('ii', $notifId, $userId);
            $stmt->execute();
            $stmt->close();
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header('Location: ' . BASE_URL . 'notifications.php');
        exit;
    }

    // Mark all notifications as read
    if ($action === 'mark_all_read') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id IS NULL OR user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash_message'] = 'All notifications marked as read.';
        $_SESSION['flash_type']    = 'success';
        header('Location: ' . BASE_URL . 'notifications.php');
        exit;
    }
}

// ── Fetch Notifications ───────────────────────────────────────
$notifications = [];
$unreadCount   = 0;
$labCount      = 0;
$generalCount  = 0;

try {
    $stmt = $conn->prepare(
        "SELECT n.id, n.user_id, n.lab_id, n.message, n.type, n.is_read, n.created_at,
                l.lab_name, l.lab_code
         FROM notifications n
         LEFT JOIN labs l ON l.id = n.lab_id
         WHERE n.user_id IS NULL OR n.user_id = ?
         ORDER BY n.created_at DESC"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($notifications as $n) {
        if ($n['is_read'] == 0) {
            $unreadCount++;
        }
        if (!empty($n['lab_id'])) {
            $labCount++;
        } else {
            $generalCount++;
        }
    }
} catch (mysqli_sql_exception $e) {
    $error = 'Could not load notifications. Please try again.';
}

// Flash message
$flashMsg  = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Helper for type icons
function notifIcon(string $type): string {
    return match ($type) {
        'danger'  => 'fa-triangle-exclamation',
        'warning' => 'fa-bell',
        'success' => 'fa-circle-check',
        default   => 'fa-circle-info',
    };
}

// Helper for human-readable time
function notifDate(string $datetime): string {
    $timestamp = strtotime($datetime);
    if (date('Y-m-d', $timestamp) === date('Y-m-d')) {
        return 'Today, ' . date('h:i A', $timestamp);
    }
    if (date('Y-m-d', $timestamp) === date('Y-m-d', strtotime('-1 day'))) {
        return 'Yesterday, ' . date('h:i A', $timestamp);
    }
    return date('d M Y, h:i A', $timestamp);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="notifications-header-row">
    <div class="page-heading" style="margin-bottom: 0;">
        <h2>Notifications & Announcements</h2>
        <p>Lab maintenance alerts, schedule updates, and campus announcements.</p>
    </div>

    <?php if ($unreadCount > 0): ?>
        <form method="post" action="notifications.php" style="margin: 0;">
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-sm btn-light">
                <i class="fa-solid fa-check-double"></i> Mark All as Read
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if ($flashMsg): ?>
    <div class="alert alert-<?= e($flashType) ?>" style="margin-top: 18px;"><?= e($flashMsg) ?></div>
<?php endif; ?>

<!-- Filter Tabs -->
<div class="notifications-tabs">
    <button type="button" class="notifications-tab active" data-filter="all">
        All <span class="notifications-pill"><?= count($notifications) ?></span>
    </button>
    <button type="button" class="notifications-tab" data-filter="unread">
        Unread <span class="notifications-pill"><?= $unreadCount ?></span>
    </button>
    <button type="button" class="notifications-tab" data-filter="lab">
        Lab Alerts <span class="notifications-pill"><?= $labCount ?></span>
    </button>
    <button type="button" class="notifications-tab" data-filter="general">
        Campus Announcements <span class="notifications-pill"><?= $generalCount ?></span>
    </button>
</div>

<!-- Notifications List -->
<div class="notifications-list card" id="notificationsContainer">
    <?php if (empty($notifications)): ?>
        <div class="empty-text">
            <i class="fa-regular fa-bell-slash" style="font-size: 32px; color: var(--muted); display: block; margin-bottom: 12px;"></i>
            You're all caught up! No notifications available.
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <?php
                $itemClasses = ['notifications-item'];
                if ($n['is_read'] == 0) {
                    $itemClasses[] = 'unread';
                }
                $itemClasses[] = 'notif-type-' . $n['type'];
                $itemClasses[] = !empty($n['lab_id']) ? 'filter-lab' : 'filter-general';
            ?>
            <div class="<?= implode(' ', $itemClasses) ?>" data-id="<?= $n['id'] ?>" data-unread="<?= $n['is_read'] == 0 ? '1' : '0' ?>" data-group="<?= !empty($n['lab_id']) ? 'lab' : 'general' ?>">
                
                <!-- Icon circle -->
                <div class="notifications-icon notif-icon-<?= e($n['type']) ?>">
                    <i class="fa-solid <?= notifIcon($n['type']) ?>"></i>
                </div>

                <!-- Content -->
                <div class="notifications-content">
                    <div class="notifications-meta">
                        <?php if (!empty($n['lab_name'])): ?>
                            <span class="badge badge-available">
                                <i class="fa-solid fa-flask" style="font-size: 10px;"></i> <?= e($n['lab_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-pending">
                                <i class="fa-solid fa-bullhorn" style="font-size: 10px;"></i> Campus Wide
                            </span>
                        <?php endif; ?>

                        <span class="notifications-time">
                            <i class="fa-regular fa-clock"></i> <?= notifDate($n['created_at']) ?>
                        </span>
                    </div>

                    <p class="notifications-message"><?= e($n['message']) ?></p>
                </div>

                <!-- Actions -->
                <div class="notifications-actions">
                    <?php if ($n['is_read'] == 0): ?>
                        <span class="notifications-dot" title="Unread"></span>
                        <button type="button" class="btn btn-sm btn-light btn-mark-read" data-id="<?= $n['id'] ?>" title="Mark as read">
                            <i class="fa-solid fa-check"></i>
                        </button>
                    <?php else: ?>
                        <span class="notifications-read-check" title="Read">
                            <i class="fa-solid fa-check"></i>
                        </span>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
