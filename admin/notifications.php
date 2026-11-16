<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Notifications & Announcements';
$activePage = 'notifications';
$pageJs     = 'notifications.js';

$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Handle notification actions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        $nid = (int) ($_POST['notif_id'] ?? 0);
        if ($nid > 0) {
            $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $nid");
        }
        header('Location: notifications.php');
        exit;
    }

    if ($action === 'mark_all_read') {
        $conn->query("UPDATE notifications SET is_read = 1");
        $_SESSION['flash_message'] = 'All notifications marked as read.';
        $_SESSION['flash_type']    = 'success';
        header('Location: notifications.php');
        exit;
    }

    if ($action === 'delete') {
        $nid = (int) ($_POST['notif_id'] ?? 0);
        if ($nid > 0) {
            $conn->query("DELETE FROM notifications WHERE id = $nid");
        }
        $_SESSION['flash_message'] = 'Notification deleted.';
        $_SESSION['flash_type']    = 'success';
        header('Location: notifications.php');
        exit;
    }

    if ($action === 'broadcast') {
        $msg     = trim($_POST['message'] ?? '');
        $labId   = !empty($_POST['lab_id']) ? (int) $_POST['lab_id'] : null;
        $msgType = $_POST['type'] ?? 'info';

        if (!empty($msg)) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, lab_id, message, type) VALUES (NULL, ?, ?, ?)");
            $stmt->bind_param('iss', $labId, $msg, $msgType);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'Announcement broadcasted successfully to all students.';
            $_SESSION['flash_type']    = 'success';
        }
        header('Location: notifications.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';

// Fetch notifications & labs
$notifications = [];
$labs          = [];
$unreadCount   = 0;

try {
    $labs = $conn->query("SELECT id, lab_name FROM labs ORDER BY lab_name")->fetch_all(MYSQLI_ASSOC);

    $sql = "SELECT n.*, l.lab_name 
            FROM notifications n
            LEFT JOIN labs l ON l.id = n.lab_id
            ORDER BY n.created_at DESC";
    $notifications = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    foreach ($notifications as $n) {
        if ($n['is_read'] == 0) $unreadCount++;
    }
} catch (mysqli_sql_exception $e) {
    // Continue
}
?>

<div class="computers-head">
    <div>
        <h2>Notifications & Announcements</h2>
        <small><a href="index.php">Dashboard</a> &gt; Notifications</small>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn" id="openBroadcastModal">
            <i class="fa-solid fa-bullhorn"></i> New Announcement
        </button>
        <?php if ($unreadCount > 0): ?>
            <form method="post" action="notifications.php" style="display: inline;">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-light"><i class="fa-solid fa-check-double"></i> Mark All as Read</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Notification Items List -->
<div class="card" style="padding: 10px 20px;">
    <?php if (empty($notifications)): ?>
        <p class="empty-cell" style="padding: 40px 0;">No notifications found.</p>
    <?php else: ?>
        <ul style="list-style: none; display: flex; flex-direction: column;">
            <?php foreach ($notifications as $n): ?>
                <?php
                $isUnread = ($n['is_read'] == 0);
                $iconClass = match($n['type']) {
                    'danger'  => 'fa-circle-exclamation',
                    'warning' => 'fa-triangle-exclamation',
                    'success' => 'fa-circle-check',
                    default   => 'fa-circle-info'
                };
                $bgClass = match($n['type']) {
                    'danger'  => 'background: #fde8e8; color: #dc2626;',
                    'warning' => 'background: #fff3dc; color: #d97706;',
                    'success' => 'background: #e7f6ec; color: #16a34a;',
                    default   => 'background: #e8f0fe; color: #1d63e8;'
                };
                ?>
                <li style="display: flex; align-items: flex-start; gap: 16px; padding: 16px 8px; border-bottom: 1px solid #edf2f7; <?= $isUnread ? 'background: #f8fbff; border-radius: 8px;' : '' ?>">
                    <div style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; <?= $bgClass ?>">
                        <i class="fa-solid <?= $iconClass ?>"></i>
                    </div>

                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                            <?php if ($isUnread): ?>
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #1d63e8; display: inline-block;"></span>
                            <?php endif; ?>
                            <strong style="color: #0f1b3d; font-size: 14px;"><?= htmlspecialchars($n['message']) ?></strong>
                            <?php if ($n['lab_name']): ?>
                                <span class="badge badge-primary"><?= htmlspecialchars($n['lab_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <small style="color: #94a3b8; font-size: 12px;">
                            <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                            <?= date('M d, Y &middot; h:i A', strtotime($n['created_at'])) ?>
                        </small>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <?php if ($isUnread): ?>
                            <form method="post" action="notifications.php" style="display: inline;">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="notif_id" value="<?= $n['id'] ?>">
                                <button type="submit" class="labs-tool" title="Mark as Read"><i class="fa-solid fa-check"></i></button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="notifications.php" style="display: inline;" onsubmit="return confirm('Delete this notification?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="notif_id" value="<?= $n['id'] ?>">
                            <button type="submit" class="labs-tool labs-tool-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Broadcast Announcement Modal -->
<div class="labs-modal" id="broadcastModal">
    <form action="notifications.php" method="post" class="labs-dialog" style="max-width: 560px;">
        <input type="hidden" name="action" value="broadcast">

        <div class="card-title">
            <span><i class="fa-solid fa-bullhorn" style="color: #1d63e8;"></i> New Campus Announcement</span>
            <button type="button" class="btn btn-light btn-sm" id="closeBroadcastBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="form-group">
            <label for="b_message">Announcement Message *</label>
            <textarea name="message" id="b_message" class="form-control" rows="3"
                      placeholder="e.g. Lab 2 will be closed tomorrow for scheduled maintenance." required></textarea>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="b_lab">Target Laboratory</label>
                <select name="lab_id" id="b_lab" class="form-control">
                    <option value="">All Laboratories (Campus Wide)</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?= $lab['id'] ?>"><?= htmlspecialchars($lab['lab_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="b_type">Severity Level</label>
                <select name="type" id="b_type" class="form-control">
                    <option value="info">Info (Blue)</option>
                    <option value="warning">Warning / Maintenance (Orange)</option>
                    <option value="success">Success / Availability (Green)</option>
                    <option value="danger">Urgent / Alert (Red)</option>
                </select>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
            <button type="button" class="btn btn-light" id="cancelBroadcastBtn">Cancel</button>
            <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Publish Announcement</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
