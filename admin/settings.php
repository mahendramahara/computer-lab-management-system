<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Settings';
$activePage = 'settings';

$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Handle profile update for current admin
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            if (empty($name) || empty($email)) {
                throw new Exception('Name and email cannot be empty.');
            }

            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param('sssi', $name, $email, $hash, $currentUserId);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param('ssi', $name, $email, $currentUserId);
            }
            $stmt->execute();
            $stmt->close();

            $_SESSION['user']['name']  = $name;
            $_SESSION['user']['email'] = $email;
            $adminName = $name;

            $_SESSION['flash_message'] = 'Profile settings saved successfully.';
            $_SESSION['flash_type']    = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Error updating profile: ' . $e->getMessage();
            $_SESSION['flash_type']    = 'danger';
        }
        header('Location: settings.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';

// Fetch current user details
$currentUserData = [];
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param('i', $currentUserId);
    $stmt->execute();
    $currentUserData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    // Continue
}
?>

<div class="computers-head">
    <div>
        <h2>System Settings</h2>
        <small><a href="index.php">Dashboard</a> &gt; Settings</small>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="dashboard-row-tables">
    <!-- Admin Profile Settings -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-user-gear"></i> Administrator Account</span>
        </div>

        <form method="post" action="settings.php">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label for="admin_name">Display Name</label>
                <input type="text" name="name" id="admin_name" class="form-control"
                       value="<?= htmlspecialchars($currentUserData['name'] ?? $adminName) ?>" required>
            </div>

            <div class="form-group">
                <label for="admin_email">Email Address</label>
                <input type="email" name="email" id="admin_email" class="form-control"
                       value="<?= htmlspecialchars($currentUserData['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="admin_role">Assigned Role</label>
                <input type="text" id="admin_role" class="form-control"
                       value="<?= htmlspecialchars($adminRole) ?>" disabled style="background: #f1f5f9;">
            </div>

            <div class="form-group">
                <label for="admin_password">Change Password</label>
                <input type="password" name="password" id="admin_password" class="form-control"
                       placeholder="Leave blank to keep current password">
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Update Profile</button>
        </form>
    </div>

    <!-- Campus & System Information -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-circle-info"></i> System Information</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13.5px;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                <span style="color: #64748b;">Institution</span>
                <strong>Mahendra Multiple Campus, Nepalgunj</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                <span style="color: #64748b;">Application Name</span>
                <strong>Computer Laboratory Management System</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                <span style="color: #64748b;">Version</span>
                <span class="badge badge-primary">v1.0.0</span>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                <span style="color: #64748b;">PHP Runtime</span>
                <strong><?= phpversion() ?></strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                <span style="color: #64748b;">Database Driver</span>
                <strong>MySQLi (MariaDB / MySQL)</strong>
            </div>

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">
                <span style="color: #64748b;">System Health</span>
                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Operational</span>
            </div>

            <div style="margin-top: 10px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; color: #64748b; line-height: 1.5;">
                <i class="fa-solid fa-shield-halved" style="color: #1d63e8;"></i>
                Session and cookie authentication is active. Only users with the <code>admin</code> or <code>lab_assistant</code> roles are permitted to access administrative tools.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
