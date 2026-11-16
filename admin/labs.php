<?php
require_once __DIR__ . '/includes/auth.php';

$base       = '';
$pageTitle  = 'Lab Overview';
$activePage = 'labs';
$pageCss    = 'labs.css';
$pageJs     = 'labs.js';

$statuses = [
    'active'      => 'Active',
    'maintenance' => 'Under Maintenance',
    'inactive'    => 'Inactive',
];

// Flash messages from CRUD operations
$message = $_SESSION['flash_message'] ?? '';
$type    = $_SESSION['flash_type'] ?? 'danger';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Handle Actions Directly at the Top of this File ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task     = $_POST['task'] ?? '';
    $id       = (int) ($_POST['lab_id'] ?? 0);
    $validStatuses = ['active', 'maintenance', 'inactive'];
    $error    = '';
    $success  = '';

    $fields = ['lab_name', 'lab_code', 'room_number', 'location', 'opening_hours', 'status', 'description'];
    $data   = [];
    foreach ($fields as $field) {
        $value        = trim($_POST[$field] ?? '');
        $data[$field] = $value === '' ? null : $value;
    }
    $data['opening_hours'] = $data['opening_hours'] ?? 'Sunday - Friday, 7:00 AM - 5:00 PM';

    try {
        if ($task === 'delete') {
            $stmt = $conn->prepare("DELETE FROM labs WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $success = 'Lab deleted successfully.';
        } elseif ($task === 'add' || $task === 'edit') {
            if ($data['lab_name'] === null || strlen($data['lab_name']) > 100) {
                $error = 'Please enter a lab name (maximum 100 characters).';
            } elseif ($data['lab_code'] === null || strlen($data['lab_code']) > 30) {
                $error = 'Please enter a lab code (maximum 30 characters).';
            } elseif ($data['room_number'] === null || strlen($data['room_number']) > 50) {
                $error = 'Please enter a room number (maximum 50 characters).';
            } elseif (!in_array($data['status'], $validStatuses, true)) {
                $error = 'Please select a valid status.';
            } elseif ($task === 'edit' && $id <= 0) {
                $error = 'Invalid lab record.';
            } elseif ($task === 'add') {
                $columns = implode(', ', array_keys($data));
                $marks   = implode(', ', array_fill(0, count($data), '?'));
                $stmt = $conn->prepare("INSERT INTO labs ($columns) VALUES ($marks)");
                $stmt->bind_param(str_repeat('s', count($data)), ...array_values($data));
                $stmt->execute();
                $stmt->close();
                $success = 'Lab added successfully.';
            } else {
                $set = implode(' = ?, ', array_keys($data)) . ' = ?';
                $stmt = $conn->prepare("UPDATE labs SET $set WHERE id = ?");
                $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...array_merge(array_values($data), [$id]));
                $stmt->execute();
                $stmt->close();
                $success = 'Lab updated successfully.';
            }
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $error = 'This lab code already exists.';
        } elseif ($e->getCode() === 1451) {
            $error = 'This lab has computers assigned. Set status to Inactive instead of deleting.';
        } else {
            $error = 'Database error: ' . $e->getMessage();
        }
    }

    $_SESSION['flash_message'] = $error ?: $success;
    $_SESSION['flash_type']    = $error ? 'danger' : 'success';
    header('Location: labs.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';

$labs = [];

try {
    // Fetch all labs with their computer counts
    $labs = $conn->query(
        "SELECT l.*,
                COUNT(c.id) AS total,
                COALESCE(SUM(c.status = 'available'), 0) AS available
         FROM labs l
         LEFT JOIN computers c ON c.lab_id = l.id
         GROUP BY l.id
         ORDER BY l.lab_name"
    )->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $message = 'Could not load labs. Please try again.';
    $type    = 'danger';
}
?>

<div class="computers-head">
    <div>
        <h2>Lab Overview</h2>
        <small><a href="index.php">Dashboard</a> &gt; Labs</small>
    </div>
    <button type="button" class="btn" id="labAdd"><i class="fa-solid fa-plus"></i> Add Lab</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Lab Name</th>
                    <th style="width: 160px; text-align: center;">Total Computers</th>
                    <th style="width: 140px; text-align: center;">Available</th>
                    <th style="width: 140px; text-align: center;">Status</th>
                    <th style="width: 100px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($labs)): ?>
                    <tr><td colspan="6" class="empty-cell">No labs found.</td></tr>
                <?php endif; ?>

                <?php foreach ($labs as $i => $lab): ?>
                    <tr>
                        <td style="color: #94a3b8; font-weight: 600;"><?= $i + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($lab['lab_name']) ?></strong>
                            <small class="labs-sub" style="color: #64748b; font-size: 12px; display: block;">
                                <?= htmlspecialchars($lab['lab_code']) ?> &middot; <?= htmlspecialchars($lab['room_number']) ?> &middot; <?= htmlspecialchars($lab['location'] ?? 'Main Campus') ?>
                            </small>
                        </td>
                        <td style="text-align: center; font-weight: 600;"><?= $lab['total'] ?></td>
                        <td style="text-align: center; color: #16a34a; font-weight: 600;"><?= $lab['available'] ?></td>
                        <td style="text-align: center;">
                            <span class="badge labs-badge-<?= $lab['status'] ?>"><?= $statuses[$lab['status']] ?></span>
                        </td>
                        <td style="text-align: center;">
                            <div class="labs-tools" style="justify-content: center;">
                                <button type="button" class="labs-tool labs-edit" title="Edit"
                                        data-lab="<?= htmlspecialchars(json_encode($lab), ENT_QUOTES) ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <form action="labs.php" method="post" class="labs-delete"
                                      data-name="<?= htmlspecialchars($lab['lab_name']) ?>">
                                    <input type="hidden" name="task" value="delete">
                                    <input type="hidden" name="lab_id" value="<?= $lab['id'] ?>">
                                    <button type="submit" class="labs-tool labs-tool-danger" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal Popup -->
<div class="labs-modal" id="labModal">
    <form action="labs.php" method="post" class="labs-dialog" id="labForm">
        <input type="hidden" name="task" value="add">
        <input type="hidden" name="lab_id" value="">

        <div class="card-title">
            <span id="labModalTitle">Add Lab</span>
            <button type="button" class="btn btn-light btn-sm" id="labClose"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="labs-form-grid">
            <div class="form-group">
                <label for="lab_name">Lab Name *</label>
                <input type="text" name="lab_name" id="lab_name" class="form-control" maxlength="100" placeholder="Computer Lab - 1" required>
            </div>
            <div class="form-group">
                <label for="lab_code">Lab Code *</label>
                <input type="text" name="lab_code" id="lab_code" class="form-control" maxlength="30" placeholder="LAB-01" required>
            </div>

            <div class="form-group">
                <label for="room_number">Room Number *</label>
                <input type="text" name="room_number" id="room_number" class="form-control" maxlength="50" placeholder="Room 101" required>
            </div>
            <div class="form-group">
                <label for="status">Status *</label>
                <select name="status" id="status" class="form-control" required>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" class="form-control" maxlength="150" placeholder="Block A, First Floor">
            </div>
            <div class="form-group">
                <label for="opening_hours">Opening Hours</label>
                <input type="text" name="opening_hours" id="opening_hours" class="form-control" maxlength="100"
                       placeholder="Sunday - Friday, 7:00 AM - 5:00 PM">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn">Save Lab</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
