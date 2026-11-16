<?php
// ============================================================
// NOTIFICATIONS SEEDER — Mahendra Multiple Campus
// Seeds: Lab Announcements and Campus-wide Notifications
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedNotifications(mysqli $conn): array {
    $labs = [];
    $lRes = $conn->query("SELECT id, lab_code FROM labs");
    while ($row = $lRes->fetch_assoc()) {
        $labs[$row['lab_code']] = (int)$row['id'];
    }

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

    $count = 0;
    foreach ($announcements as $an) {
        $targetLabId = !empty($an['lab_code']) ? ($labs[$an['lab_code']] ?? null) : null;

        $nchk = $conn->prepare("SELECT id FROM notifications WHERE message = ? LIMIT 1");
        $nchk->bind_param('s', $an['message']);
        $nchk->execute();
        $nres = $nchk->get_result();

        if ($nres && $nres->num_rows > 0) {
            $nId = (int)$nres->fetch_assoc()['id'];
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
        $count++;
    }

    return ['total_notifications' => $count];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedNotifications($conn);
    echo "Notifications Seeded: Total {$res['total_notifications']}\n";
}
