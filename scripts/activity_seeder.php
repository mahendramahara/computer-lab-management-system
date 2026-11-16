<?php
// ============================================================
// ACTIVITY SEEDER — Mahendra Multiple Campus Computer Lab Management
// Seeds: Fault Reports, Maintenance Logs, and Campus Announcements
// Run CLI: php scripts/activity_seeder.php
// ============================================================

require_once __DIR__ . '/../config/db.php';

$isCli = php_sapi_name() === 'cli';

if (!function_exists('logSeedAct')) {
    function logSeedAct(string $msg): void {
        global $isCli;
        if ($isCli) {
            echo $msg . "\n";
        }
    }
}

function seedActivity(mysqli $conn): array {
    // ── Helper maps for IDs ───────────────────────────────────
    $users = [];
    $uRes = $conn->query("SELECT id, name, email FROM users");
    while ($row = $uRes->fetch_assoc()) {
        $users[$row['email']] = (int)$row['id'];
        $users[$row['name']]  = (int)$row['id'];
    }

    $computers = [];
    $cRes = $conn->query("SELECT id, computer_code FROM computers");
    while ($row = $cRes->fetch_assoc()) {
        $computers[$row['computer_code']] = (int)$row['id'];
    }

    $labs = [];
    $lRes = $conn->query("SELECT id, lab_code FROM labs");
    while ($row = $lRes->fetch_assoc()) {
        $labs[$row['lab_code']] = (int)$row['id'];
    }

    $ramId    = $users['ram@mmc.edu.np'] ?? ($users['user@gmail.com'] ?? 1);
    $sitaId   = $users['sita@mmc.edu.np'] ?? 1;
    $bikashId = $users['bikash@mmc.edu.np'] ?? 1;
    $anitaId  = $users['anita@mmc.edu.np'] ?? 1;
    $rameshId = $users['ramesh.kc@mmc.edu.np'] ?? ($users['admin@mmc.edu.np'] ?? 1);
    $sujanId  = $users['sujan.bhandari@mmc.edu.np'] ?? ($users['admin@mmc.edu.np'] ?? 1);

    // ── 1. Seed Realistic Fault Reports ───────────────────────
    $reportsData = [
        [
            'user_id'       => $ramId,
            'computer_code' => 'PC-014',
            'component'     => 'keyboard',
            'issue_title'   => 'Keyboard not working',
            'description'   => 'Multiple keys on the keyboard are unresponsive during morning programming practical.',
            'priority'      => 'medium',
            'status'        => 'in_progress',
            'report_image'  => 'uploads/reports/fault_keyboard_keys.jpg',
            'reported_at'   => '2026-11-14 10:45:00',
        ],
        [
            'user_id'       => $sitaId,
            'computer_code' => 'PC-007',
            'component'     => 'monitor',
            'issue_title'   => 'Monitor flickering',
            'description'   => 'Screen keeps turning black every few minutes while working on database lab.',
            'priority'      => 'high',
            'status'        => 'pending',
            'report_image'  => 'uploads/reports/fault_monitor_flicker.jpg',
            'reported_at'   => '2026-11-14 09:20:00',
        ],
        [
            'user_id'       => $bikashId,
            'computer_code' => 'PC-021',
            'component'     => 'mouse',
            'issue_title'   => 'Mouse not working',
            'description'   => 'Optical cursor sensor is not tracking and left click requires firm pressure.',
            'priority'      => 'low',
            'status'        => 'under_review',
            'report_image'  => 'uploads/reports/fault_mouse_sensor.jpg',
            'reported_at'   => '2026-11-13 14:15:00',
        ],
        [
            'user_id'       => $anitaId,
            'computer_code' => 'PC-003',
            'component'     => 'cpu',
            'issue_title'   => 'CPU not turning on',
            'description'   => 'Power supply unit was completely unresponsive. Technician inspected and repaired power rail.',
            'priority'      => 'high',
            'status'        => 'resolved',
            'report_image'  => 'uploads/reports/fault_cpu_power.jpg',
            'reported_at'   => '2026-11-12 11:30:00',
        ],
    ];

    $reportsCount = 0;
    foreach ($reportsData as $rd) {
        $cId = $computers[$rd['computer_code']] ?? 1;

        $chk = $conn->prepare("SELECT id FROM fault_reports WHERE computer_id = ? AND issue_title = ? LIMIT 1");
        $chk->bind_param('is', $cId, $rd['issue_title']);
        $chk->execute();
        $res = $chk->get_result();

        if ($res && $res->num_rows > 0) {
            $repId = $res->fetch_assoc()['id'];
            $up = $conn->prepare("UPDATE fault_reports SET status = ?, priority = ?, description = ?, report_image = ?, reported_at = ? WHERE id = ?");
            $up->bind_param('sssssi', $rd['status'], $rd['priority'], $rd['description'], $rd['report_image'], $rd['reported_at'], $repId);
            $up->execute();
            $up->close();
        } else {
            $ins = $conn->prepare("INSERT INTO fault_reports (user_id, computer_id, component, issue_title, description, priority, status, report_image, reported_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('iisssssss', $rd['user_id'], $cId, $rd['component'], $rd['issue_title'], $rd['description'], $rd['priority'], $rd['status'], $rd['report_image'], $rd['reported_at']);
            $ins->execute();
            $repId = $conn->insert_id;
            $ins->close();
        }
        $chk->close();
        $reportsCount++;
    }

    // ── 2. Seed Maintenance Records ───────────────────────────
    $maintData = [
        [
            'computer_code' => 'PC-003',
            'technician_id' => $rameshId,
            'component'     => 'Keyboard',
            'type'          => 'replacement',
            'problem'       => 'Power Supply replaced and faulty keyboard swapped with new Logitech unit.',
            'action'        => 'Keyboard Replaced',
            'cost'          => 1200.00,
            'date'          => '2026-11-14',
            'status'        => 'completed',
        ],
        [
            'computer_code' => 'PC-006',
            'technician_id' => $sujanId,
            'component'     => 'CPU / Chassis',
            'type'          => 'scheduled',
            'problem'       => 'Routine seasonal dust cleaning and heatsink thermal paste re-application.',
            'action'        => 'Dust Cleaning',
            'cost'          => 0.00,
            'date'          => '2026-11-10',
            'status'        => 'completed',
        ],
        [
            'computer_code' => 'PC-010',
            'technician_id' => $rameshId,
            'component'     => 'Monitor',
            'type'          => 'repair',
            'problem'       => 'Capacitor replacement on internal power board and display color calibration.',
            'action'        => 'Monitor Repair',
            'cost'          => 2500.00,
            'date'          => '2026-11-05',
            'status'        => 'completed',
        ],
    ];

    $maintCount = 0;
    foreach ($maintData as $md) {
        $cId = $computers[$md['computer_code']] ?? 1;

        $mchk = $conn->prepare("SELECT id FROM maintenance_records WHERE computer_id = ? AND problem_description = ? LIMIT 1");
        $mchk->bind_param('is', $cId, $md['problem']);
        $mchk->execute();
        $mres = $mchk->get_result();

        if ($mres && $mres->num_rows > 0) {
            $mId = $mres->fetch_assoc()['id'];
            $mup = $conn->prepare("UPDATE maintenance_records SET component = ?, maintenance_type = ?, repair_description = ?, repair_cost = ?, maintenance_date = ?, status = ? WHERE id = ?");
            $mup->bind_param('sssdssi', $md['component'], $md['type'], $md['action'], $md['cost'], $md['date'], $md['status'], $mId);
            $mup->execute();
            $mup->close();
        } else {
            $mins = $conn->prepare("INSERT INTO maintenance_records (computer_id, technician_id, component, maintenance_type, problem_description, repair_description, repair_cost, maintenance_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $mins->bind_param('iissssiss', $cId, $md['technician_id'], $md['component'], $md['type'], $md['problem'], $md['action'], $md['cost'], $md['date'], $md['status']);
            $mins->execute();
            $mins->close();
        }
        $mchk->close();
        $maintCount++;
    }

    // ── 3. Seed Clean Announcements ───────────────────────────
    $announcements = [
        [
            'lab_code' => 'LAB-01',
            'message'  => 'Lab 1: 2 computers are under maintenance.',
            'type'     => 'danger',
            'is_read'  => 0,
            'time'     => date('Y-m-d') . ' 10:20:00'
        ],
        [
            'lab_code' => null,
            'message'  => 'Lab hours extended: All computer labs remain open until 6:00 PM during project evaluation week.',
            'type'     => 'info',
            'is_read'  => 0,
            'time'     => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'lab_code' => 'LAB-02',
            'message'  => 'Lab 2 will be closed tomorrow for scheduled maintenance.',
            'type'     => 'warning',
            'is_read'  => 0,
            'time'     => date('Y-m-d H:i:s', strtotime('-3 days'))
        ],
    ];

    $notifCount = 0;
    foreach ($announcements as $an) {
        $targetLabId = !empty($an['lab_code']) ? ($labs[$an['lab_code']] ?? null) : null;

        $nchk = $conn->prepare("SELECT id FROM notifications WHERE message = ? LIMIT 1");
        $nchk->bind_param('s', $an['message']);
        $nchk->execute();
        $nres = $nchk->get_result();

        if ($nres && $nres->num_rows > 0) {
            $nId = $nres->fetch_assoc()['id'];
            $nup = $conn->prepare("UPDATE notifications SET lab_id = ?, type = ?, is_read = ?, created_at = ? WHERE id = ?");
            $nup->bind_param('isisi', $targetLabId, $an['type'], $an['is_read'], $an['time'], $nId);
            $nup->execute();
            $nup->close();
        } else {
            $nins = $conn->prepare("INSERT INTO notifications (lab_id, message, type, is_read, created_at) VALUES (?, ?, ?, ?, ?)");
            $nins->bind_param('issis', $targetLabId, $an['message'], $an['type'], $an['is_read'], $an['time']);
            $nins->execute();
            $nins->close();
        }
        $nchk->close();
        $notifCount++;
    }

    return [
        'fault_reports'        => $reportsCount,
        'maintenance_records'  => $maintCount,
        'notifications'        => $notifCount,
    ];
}

if ($isCli && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "============================================================\n";
    echo "  Seeding MMC Activity (Reports, Maintenance, Notices)...\n";
    echo "============================================================\n";
    $stats = seedActivity($conn);
    echo "Done! Fault Reports: {$stats['fault_reports']}, Maintenance Records: {$stats['maintenance_records']}, Notifications: {$stats['notifications']}\n";
}
