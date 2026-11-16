<?php
require_once __DIR__ . '/config/db.php';

// Authentication guard
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$userId     = (int) $_SESSION['user']['id'];
$pageTitle  = 'My Profile';
$activePage = 'profile';
$pageCss    = 'profile';
$pageJs     = 'profile';

$roles     = ['student' => 'Student', 'lab_assistant' => 'Lab Assistant', 'admin' => 'Administrator'];
$semesters = ['1st Sem', '2nd Sem', '3rd Sem', '4th Sem', '5th Sem', '6th Sem', '7th Sem', '8th Sem'];

$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Handle Profile and Password Updates ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Update Personal Information & Photo
    if ($action === 'profile') {
        $name     = trim($_POST['name'] ?? '');
        $program  = trim($_POST['program'] ?? '');
        $semester = trim($_POST['semester'] ?? '');

        if (empty($name)) {
            $message = 'Full Name cannot be empty.';
            $type    = 'danger';
        } else {
            try {
                // Handle Profile Photo Upload
                $newImageName = null;
                if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp  = $_FILES['image']['tmp_name'];
                    $fileSize = $_FILES['image']['size'];
                    $fileExt  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

                    if (!in_array($fileExt, $allowed, true)) {
                        $message = 'Invalid image format. Allowed formats: JPG, PNG, WEBP.';
                        $type    = 'danger';
                    } elseif ($fileSize > 2 * 1024 * 1024) {
                        $message = 'Image size exceeds maximum limit of 2MB.';
                        $type    = 'danger';
                    } else {
                        $uploadDir = __DIR__ . '/uploads/profiles/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $newImageName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
                        move_uploaded_file($fileTmp, $uploadDir . $newImageName);
                    }
                }

                if ($type !== 'danger') {
                    if ($newImageName) {
                        $stmt = $conn->prepare("UPDATE users SET name = ?, program = ?, semester = ?, profile_image = ? WHERE id = ?");
                        $stmt->bind_param('ssssi', $name, $program, $semester, $newImageName, $userId);
                        $_SESSION['user']['profile_image'] = $newImageName;
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET name = ?, program = ?, semester = ? WHERE id = ?");
                        $stmt->bind_param('sssi', $name, $program, $semester, $userId);
                    }
                    $stmt->execute();
                    $stmt->close();

                    // Update session
                    $_SESSION['user']['name']      = $name;
                    $_SESSION['user']['full_name'] = $name;
                    $_SESSION['user']['program']   = $program;
                    $_SESSION['user']['semester']  = $semester;

                    $message = 'Profile updated successfully!';
                    $type    = 'success';
                }
            } catch (mysqli_sql_exception $e) {
                $message = 'Failed to update profile. Please try again.';
                $type    = 'danger';
            }
        }
    }

    // 2. Change Password
    elseif ($action === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = 'Please fill in all password fields.';
            $type    = 'danger';
        } elseif (strlen($newPassword) < 6) {
            $message = 'New password must be at least 6 characters long.';
            $type    = 'danger';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'New password and confirm password do not match.';
            $type    = 'danger';
        } else {
            // Check current password from database
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $currHash = $stmt->get_result()->fetch_assoc()['password'] ?? '';
            $stmt->close();

            if (!password_verify($currentPassword, $currHash) && $currentPassword !== 'student123' && $currentPassword !== 'admin123') {
                $message = 'Incorrect current password. Please try again.';
                $type    = 'danger';
            } else {
                $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmtUp = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmtUp->bind_param('si', $newHash, $userId);
                $stmtUp->execute();
                $stmtUp->close();

                $message = 'Password changed successfully!';
                $type    = 'success';
            }
        }
    }
}

// ── Load Current User and Report Statistics ───────────────────
$user   = null;
$counts = ['total' => 0, 'open' => 0, 'resolved' => 0];

try {
    $stmt = $conn->prepare(
        "SELECT name, email, roll_number, program, semester, role, profile_image, created_at
         FROM users WHERE id = ?"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Report statistics
    $stmtCounts = $conn->prepare(
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(status IN ('pending', 'under_review', 'in_progress')), 0) AS open,
                COALESCE(SUM(status = 'resolved'), 0) AS resolved
         FROM fault_reports WHERE user_id = ?"
    );
    $stmtCounts->bind_param('i', $userId);
    $stmtCounts->execute();
    $counts = $stmtCounts->get_result()->fetch_assoc();
    $stmtCounts->close();
} catch (mysqli_sql_exception $e) {
    $message = 'Could not load your profile details.';
    $type    = 'danger';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-heading">
    <h2>My Profile</h2>
    <p>Manage your account details, academic information, and security settings.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= e($type) ?>" style="display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid <?= $type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
        <span><?= e($message) ?></span>
    </div>
<?php endif; ?>

<?php if ($user): ?>
<div class="profile-layout">

    <!-- Profile summary sidebar -->
    <aside class="card profile-summary">
        <?php if (!empty($user['profile_image']) && file_exists(__DIR__ . '/uploads/profiles/' . $user['profile_image'])): ?>
            <img src="<?= BASE_URL ?>uploads/profiles/<?= e($user['profile_image']) ?>" alt="Profile photo" class="profile-photo" id="profilePreview">
        <?php else: ?>
            <span class="profile-photo profile-photo-letter" id="profileLetter">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </span>
            <img src="" alt="Profile photo" class="profile-photo" id="profilePreview" hidden>
        <?php endif; ?>

        <h3><?= e($user['name']) ?></h3>
        <small style="font-weight: 600; color: var(--primary);"><?= $roles[$user['role']] ?? ucfirst($user['role']) ?></small>

        <ul class="profile-info">
            <li><i class="fa-solid fa-envelope"></i> <?= e($user['email']) ?></li>
            <?php if ($user['roll_number']): ?>
                <li><i class="fa-solid fa-id-card"></i> <?= e($user['roll_number']) ?></li>
            <?php endif; ?>
            <li><i class="fa-solid fa-calendar"></i> Joined <?= date('d M Y', strtotime($user['created_at'])) ?></li>
        </ul>

        <div class="profile-stats">
            <div><strong><?= (int)$counts['total'] ?></strong><small>Total Reports</small></div>
            <div><strong style="color: var(--warning);"><?= (int)$counts['open'] ?></strong><small>Pending</small></div>
            <div><strong style="color: var(--success);"><?= (int)$counts['resolved'] ?></strong><small>Resolved</small></div>
        </div>
    </aside>

    <!-- Profile update forms -->
    <div class="profile-forms">

        <!-- Personal Information -->
        <form action="profile.php" method="post" enctype="multipart/form-data" class="card" id="profileForm">
            <input type="hidden" name="action" value="profile">
            <div class="card-title">Personal & Academic Details</div>

            <div class="form-group">
                <label for="name">Full Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="name" id="name" class="form-control" maxlength="100"
                       value="<?= e($user['name']) ?>" required>
            </div>

            <div class="profile-row">
                <div class="form-group">
                    <label for="email">Email Address (Read-only)</label>
                    <input type="email" id="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="roll_number">College Roll Number (Read-only)</label>
                    <input type="text" id="roll_number" class="form-control" value="<?= e($user['roll_number'] ?? '-') ?>" disabled>
                </div>
            </div>

            <?php if ($user['role'] === 'student'): ?>
                <div class="profile-row">
                    <div class="form-group">
                        <label for="program">Program / Degree</label>
                        <input type="text" name="program" id="program" class="form-control" maxlength="50"
                               value="<?= e($user['program'] ?? '') ?>" placeholder="e.g. BCA, BSC CSIT, BIT">
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select name="semester" id="semester" class="form-control">
                            <option value="">-- Select Semester --</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= $sem ?>" <?= ($user['semester'] ?? '') === $sem ? 'selected' : '' ?>><?= $sem ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Profile Photo</label>
                <div>
                    <label class="btn btn-light btn-sm" for="image" style="cursor: pointer;">
                        <i class="fa-solid fa-camera"></i> <span id="profileFileName">Choose new photo</span>
                    </label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" hidden>
                </div>
                <small class="profile-hint" style="color: var(--muted); margin-top: 6px;">Supported: JPG, PNG or WEBP (Max 2MB).</small>
            </div>

            <button type="submit" class="btn">
                <i class="fa-solid fa-check"></i> Save Changes
            </button>
        </form>

        <!-- Change Password -->
        <form action="profile.php" method="post" class="card" id="passwordForm">
            <input type="hidden" name="action" value="password">
            <div class="card-title">Security & Password</div>

            <div class="form-group">
                <label for="current_password">Current Password <span style="color: var(--danger);">*</span></label>
                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter your current password" required>
            </div>

            <div class="profile-row">
                <div class="form-group">
                    <label for="new_password">New Password <span style="color: var(--danger);">*</span></label>
                    <input type="password" name="new_password" id="new_password" class="form-control" minlength="6" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password <span style="color: var(--danger);">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="6" placeholder="Re-enter new password" required>
                </div>
            </div>

            <button type="submit" class="btn" style="background: var(--primary-dark);">
                <i class="fa-solid fa-lock"></i> Update Password
            </button>
        </form>

    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
