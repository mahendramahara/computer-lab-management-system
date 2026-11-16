<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'User Management';
$activePage = 'users';
$pageJs     = 'users.js';

// System roles
$roles = [
    'student'       => 'Student',
    'lab_assistant' => 'Lab Assistant',
    'admin'         => 'Administrator',
];

// Flash messages
$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Handle Actions Directly at the Top of this File ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task   = $_POST['task'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);

    try {
        if ($task === 'delete') {
            if ($userId === (int)$currentUserId) {
                throw new Exception('You cannot delete your own logged-in account.');
            }
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'User account deleted successfully.';
            $_SESSION['flash_type']    = 'success';
        } elseif ($task === 'toggle_status') {
            $curStatus = $_POST['status'] ?? 'active';
            $newStatus = ($curStatus === 'active') ? 'inactive' : 'active';
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $newStatus, $userId);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash_message'] = 'User status updated to ' . ucfirst($newStatus) . '.';
            $_SESSION['flash_type']    = 'success';
        } elseif ($task === 'add' || $task === 'edit') {
            $name       = trim($_POST['name'] ?? '');
            $email      = trim($_POST['email'] ?? '');
            $rollNumber = trim($_POST['roll_number'] ?? '');
            $role       = $_POST['role'] ?? 'student';
            $status     = $_POST['status'] ?? 'active';
            $program    = trim($_POST['program'] ?? '');
            $semester   = trim($_POST['semester'] ?? '');
            $password   = $_POST['password'] ?? '';

            if (empty($name) || empty($email)) {
                throw new Exception('Name and Email are required.');
            }

            $rollVal = $rollNumber !== '' ? $rollNumber : null;
            $progVal = $program !== '' ? $program : null;
            $semVal  = $semester !== '' ? $semester : null;

            if ($task === 'add') {
                if (empty($password)) {
                    throw new Exception('Password is required for new accounts.');
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "INSERT INTO users (name, email, roll_number, password, program, semester, role, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('ssssssss', $name, $email, $rollVal, $hash, $progVal, $semVal, $role, $status);
                $stmt->execute();
                $newId = $conn->insert_id;
                $stmt->close();

                // Create notification for admin
                $roleLabel = $roles[$role] ?? ucfirst($role);
                $adminNotice = "New user account created: " . $name . " (" . $roleLabel . ")";
                $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (NULL, ?, 'info')");
                $stmtNotif->bind_param('s', $adminNotice);
                $stmtNotif->execute();
                $stmtNotif->close();

                $_SESSION['flash_message'] = 'User account created successfully.';
                $_SESSION['flash_type']    = 'success';
            } else {
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare(
                        "UPDATE users 
                         SET name = ?, email = ?, roll_number = ?, password = ?, program = ?, semester = ?, role = ?, status = ?
                         WHERE id = ?"
                    );
                    $stmt->bind_param('ssssssssi', $name, $email, $rollVal, $hash, $progVal, $semVal, $role, $status, $userId);
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE users 
                         SET name = ?, email = ?, roll_number = ?, program = ?, semester = ?, role = ?, status = ?
                         WHERE id = ?"
                    );
                    $stmt->bind_param('sssssssi', $name, $email, $rollVal, $progVal, $semVal, $role, $status, $userId);
                }
                $stmt->execute();
                $stmt->close();

                $_SESSION['flash_message'] = 'User details updated successfully.';
                $_SESSION['flash_type']    = 'success';
            }
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $_SESSION['flash_message'] = 'Email or Roll Number is already registered.';
        } elseif ($e->getCode() === 1451) {
            $_SESSION['flash_message'] = 'Cannot delete user because they have submitted fault reports. Mark them as Inactive instead.';
        } else {
            $_SESSION['flash_message'] = 'Database error: ' . $e->getMessage();
        }
        $_SESSION['flash_type'] = 'danger';
    } catch (Exception $e) {
        $_SESSION['flash_message'] = $e->getMessage();
        $_SESSION['flash_type']    = 'danger';
    }

    header('Location: users.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

// ── Overview Statistics for User Management ───────────────────
$userStats = [
    'total'    => 0,
    'students' => 0,
    'staff'    => 0,
    'active'   => 0,
];
try {
    $userStats['total']    = (int)($conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0);
    $userStats['students'] = (int)($conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0] ?? 0);
    $userStats['staff']    = (int)($conn->query("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'lab_assistant')")->fetch_row()[0] ?? 0);
    $userStats['active']   = (int)($conn->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetch_row()[0] ?? 0);
} catch (Exception $e) {}

// Filter parameters
$roleFilter   = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$perPage      = 10;

$where    = " WHERE 1 = 1";
$params   = [];
$typesStr = "";

if ($roleFilter !== '' && isset($roles[$roleFilter])) {
    $where    .= " AND u.role = ?";
    $params[]  = $roleFilter;
    $typesStr .= "s";
}

if ($statusFilter !== '' && in_array($statusFilter, ['active', 'inactive'], true)) {
    $where    .= " AND u.status = ?";
    $params[]  = $statusFilter;
    $typesStr .= "s";
}

if ($search !== '') {
    $where    .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.roll_number LIKE ? OR u.program LIKE ?)";
    $like      = "%$search%";
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $typesStr .= "ssss";
}

$users      = [];
$totalCount = 0;
$totalPages = 1;

try {
    // Count total rows
    $cStmt = $conn->prepare("SELECT COUNT(*) FROM users u" . $where);
    if ($params) {
        $cStmt->bind_param($typesStr, ...$params);
    }
    $cStmt->execute();
    $totalCount = $cStmt->get_result()->fetch_row()[0];
    $cStmt->close();

    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Fetch users with real report count from database
    $sql = "SELECT u.*, 
                   (SELECT COUNT(*) FROM fault_reports r WHERE r.user_id = u.id) AS report_count
            FROM users u" . $where . " 
            ORDER BY u.id DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($typesStr . "ii", ...array_merge($params, [$perPage, $offset]));
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $message = 'Error loading users: ' . $e->getMessage();
    $type    = 'danger';
}

$filters = array_filter(['role' => $roleFilter, 'status' => $statusFilter, 'search' => $search]);
?>

<div class="computers-head">
    <div>
        <h2>User Management</h2>
        <small><a href="index.php">Dashboard</a> &gt; Users</small>
    </div>
    <button type="button" class="btn" id="addUserBtn"><i class="fa-solid fa-user-plus"></i> Add New User</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- User Management Stats Overview Cards -->
<div class="stat-cards-grid" style="margin-bottom: 22px;">
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Users</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $userStats['total'] ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-success">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Students</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $userStats['students'] ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-warning">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Staff & Admins</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $userStats['staff'] ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-main">
            <div class="stat-icon-box stat-icon-purple">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Active Accounts</span>
                <div class="stat-value-row">
                    <strong class="stat-number"><?= $userStats['active'] ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<form method="get" class="admin-filters">
    <div class="admin-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Search by name, email, roll number, or program..."
               value="<?= htmlspecialchars($search) ?>">
    </div>

    <select name="role" class="form-control" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <?php foreach ($roles as $k => $label): ?>
            <option value="<?= $k ?>" <?= $roleFilter === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>

    <select name="status" class="form-control" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
    </select>

    <button type="submit" class="admin-filters-btn">
        <i class="fa-solid fa-magnifying-glass"></i> Search
    </button>

    <?php if (!empty($search) || !empty($roleFilter) || !empty($statusFilter)): ?>
        <a href="users.php" class="admin-filters-clear" title="Clear Filters">
            <i class="fa-solid fa-xmark"></i> Clear Filters
        </a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="width: 35px;">#</th>
                    <th>User / Profile</th>
                    <th>Email Address</th>
                    <th>Roll / ID</th>
                    <th>Academic Program</th>
                    <th style="text-align: center;">Role</th>
                    <th style="text-align: center;">Reports</th>
                    <th style="text-align: center;">Status</th>
                    <th>Joined Date</th>
                    <th style="width: 120px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="10" class="empty-cell">No users found matching your criteria.</td></tr>
                <?php endif; ?>

                <?php foreach ($users as $idx => $u): ?>
                    <?php
                    $rBadge = match($u['role']) {
                        'admin'         => 'danger',
                        'lab_assistant' => 'purple',
                        'student'       => 'primary',
                        default         => 'primary'
                    };
                    $sBadge = ($u['status'] === 'active') ? 'success' : 'danger';
                    ?>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;"><?= $offset + $idx + 1 ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if (!empty($u['profile_image']) && file_exists(__DIR__ . '/../uploads/profiles/' . $u['profile_image'])): ?>
                                    <img src="<?= BASE_URL ?>uploads/profiles/<?= htmlspecialchars($u['profile_image']) ?>" 
                                         alt="Avatar" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1.5px solid #e2e8f0;">
                                <?php else: ?>
                                    <span style="width: 34px; height: 34px; border-radius: 50%; background: #e8f0fe; color: #1d63e8; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                                <div>
                                    <strong style="display: block; font-size: 13.5px;"><?= htmlspecialchars($u['name']) ?></strong>
                                    <small style="color: #94a3b8; font-size: 11px;">ID #<?= $u['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 13px; color: #334155;"><?= htmlspecialchars($u['email']) ?></span>
                        </td>
                        <td>
                            <?php if (!empty($u['roll_number'])): ?>
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 600;"><?= htmlspecialchars($u['roll_number']) ?></span>
                            <?php else: ?>
                                <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['program']): ?>
                                <strong style="font-size: 12.5px; display: block;"><?= htmlspecialchars($u['program']) ?></strong>
                                <small style="color: #64748b;"><?= htmlspecialchars($u['semester'] ?? '') ?></small>
                            <?php else: ?>
                                <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-<?= $rBadge ?>"><?= $roles[$u['role']] ?? ucfirst($u['role']) ?></span>
                        </td>
                        <td style="text-align: center;">
                            <?php if ((int)$u['report_count'] > 0): ?>
                                <a href="fault-reports.php?search=<?= urlencode($u['name']) ?>" class="badge badge-primary" title="View user reports" style="text-decoration: none;">
                                    <?= (int)$u['report_count'] ?> Reports
                                </a>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 12px;">0</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <form action="users.php" method="post" style="display: inline-block;">
                                <input type="hidden" name="task" value="toggle_status">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="status" value="<?= $u['status'] ?>">
                                <button type="submit" class="badge badge-<?= $sBadge ?>" style="border: 0; cursor: pointer;" title="Click to toggle status">
                                    <i class="fa-solid fa-circle-dot" style="font-size: 9px; margin-right: 4px;"></i><?= ucfirst($u['status']) ?>
                                </button>
                            </form>
                        </td>
                        <td style="font-size: 12px; color: #64748b;">
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                            <small style="display: block; font-size: 11px;"><?= date('h:i A', strtotime($u['created_at'])) ?></small>
                        </td>
                        <td style="text-align: center;">
                            <div class="labs-tools" style="justify-content: center;">
                                <button type="button" class="labs-tool edit-user-btn" title="Edit User"
                                        data-user="<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <?php if ($u['id'] !== (int)$currentUserId): ?>
                                    <form action="users.php" method="post" onsubmit="return confirm('Are you sure you want to permanently delete user <?= htmlspecialchars($u['name'], ENT_QUOTES) ?>?');">
                                        <input type="hidden" name="task" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="labs-tool labs-tool-danger" title="Delete User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="computers-pagination" style="display: flex; justify-content: center; gap: 8px; margin-top: 20px;">
            <a class="computers-page <?= $page <= 1 ? 'disabled' : '' ?>"
               href="?<?= http_build_query($filters + ['page' => $page - 1]) ?>">
                <i class="fa-solid fa-chevron-left"></i>
            </a>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="computers-page <?= $i === $page ? 'active' : '' ?>"
                   href="?<?= http_build_query($filters + ['page' => $i]) ?>"><?= $i ?></a>
            <?php endfor; ?>

            <a class="computers-page <?= $page >= $totalPages ? 'disabled' : '' ?>"
               href="?<?= http_build_query($filters + ['page' => $page + 1]) ?>">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Add / Edit User Modal (Submits directly to users.php) -->
<div class="labs-modal" id="userModal">
    <form action="users.php" method="post" class="labs-dialog" id="userForm" style="max-width: 620px;">
        <input type="hidden" name="task" id="userTask" value="add">
        <input type="hidden" name="user_id" id="userId" value="">

        <div class="card-title">
            <span id="userModalTitle">Add New User</span>
            <button type="button" class="btn btn-light btn-sm" id="userClose"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="form_name">Full Name *</label>
                <input type="text" name="name" id="form_name" class="form-control" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="form_email">Email Address *</label>
                <input type="email" name="email" id="form_email" class="form-control" maxlength="150" required>
            </div>

            <div class="form-group">
                <label for="form_roll">Roll Number (Students)</label>
                <input type="text" name="roll_number" id="form_roll" class="form-control" maxlength="50" placeholder="e.g. MMC-BCA-2026-001">
            </div>

            <div class="form-group">
                <label for="form_role">System Role *</label>
                <select name="role" id="form_role" class="form-control" required>
                    <?php foreach ($roles as $k => $label): ?>
                        <option value="<?= $k ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="form_program">Academic Program</label>
                <input type="text" name="program" id="form_program" class="form-control" placeholder="BCA / CSIT / BIT">
            </div>

            <div class="form-group">
                <label for="form_semester">Semester</label>
                <input type="text" name="semester" id="form_semester" class="form-control" placeholder="4th Semester">
            </div>

            <div class="form-group">
                <label for="form_status">Account Status *</label>
                <select name="status" id="form_status" class="form-control" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label for="form_password" id="passLabel">Password *</label>
                <input type="password" name="password" id="form_password" class="form-control" placeholder="Min. 6 characters">
                <small id="passHint" style="display: none; color: #64748b;">Leave blank to keep existing password.</small>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
            <button type="button" class="btn btn-light" id="userCancel">Cancel</button>
            <button type="submit" class="btn"><i class="fa-solid fa-check"></i> Save User</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
