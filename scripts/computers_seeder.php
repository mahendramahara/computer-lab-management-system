<?php
// ============================================================
// COMPUTERS SEEDER — Mahendra Multiple Campus
// Seeds: 40 Workstations (PC-001 to PC-040) across Lab 1 and Lab 2
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedComputers(mysqli $conn): array {
    // Fetch lab IDs
    $labIds = [];
    $lRes = $conn->query("SELECT id, lab_code FROM labs");
    while ($row = $lRes->fetch_assoc()) {
        $labIds[$row['lab_code']] = (int)$row['id'];
    }

    $maintenanceCodes  = ['PC-006', 'PC-009', 'PC-025', 'PC-031', 'PC-036'];
    $outOfServiceCodes = ['PC-004', 'PC-015', 'PC-028'];

    $created = 0;
    $updated = 0;

    for ($i = 1; $i <= 40; $i++) {
        $num  = str_pad($i, 3, '0', STR_PAD_LEFT);
        $code = 'PC-' . $num;

        $labCode = ($i <= 20) ? 'LAB-01' : 'LAB-02';
        $labId   = $labIds[$labCode] ?? 1;
        $pcNum   = ($i <= 20) ? str_pad($i, 2, '0', STR_PAD_LEFT) : str_pad($i - 20, 2, '0', STR_PAD_LEFT);
        $name    = "$labCode / PC $pcNum";

        if (in_array($code, $maintenanceCodes, true)) {
            $status = 'under_maintenance';
        } elseif (in_array($code, $outOfServiceCodes, true)) {
            $status = 'out_of_service';
        } else {
            $status = 'available';
        }

        $ip      = '192.168.1.' . (100 + $i);
        $proc    = ($i % 3 === 0) ? 'Intel Core i7-11700 (2.50 GHz)' : 'Intel Core i5-10400 (2.90 GHz)';
        $ram     = '16 GB DDR4';
        $storage = '512 GB NVMe SSD';
        $os      = 'Windows 11 Pro 64-bit';
        $monitor = 'Dell 24" IPS FHD (1920x1080)';
        $keyboard= 'Logitech K120 USB Keyboard';
        $mouse   = 'Logitech B100 Optical USB Mouse';

        $chk = $conn->prepare("SELECT id FROM computers WHERE computer_code = ? LIMIT 1");
        $chk->bind_param('s', $code);
        $chk->execute();
        $pcRes = $chk->get_result();

        if ($pcRes && $pcRes->num_rows > 0) {
            $pcId = (int)$pcRes->fetch_assoc()['id'];
            $up = $conn->prepare(
                "UPDATE computers SET lab_id = ?, computer_name = ?, processor = ?, ram = ?, storage = ?,
                 operating_system = ?, monitor = ?, keyboard = ?, mouse = ?, ip_address = ?, status = ?
                 WHERE id = ?"
            );
            $up->bind_param('issssssssssi', $labId, $name, $proc, $ram, $storage, $os, $monitor, $keyboard, $mouse, $ip, $status, $pcId);
            $up->execute();
            $up->close();
            $updated++;
        } else {
            $ins = $conn->prepare(
                "INSERT INTO computers (lab_id, computer_code, computer_name, processor, ram, storage, operating_system, monitor, keyboard, mouse, ip_address, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->bind_param('isssssssssss', $labId, $code, $name, $proc, $ram, $storage, $os, $monitor, $keyboard, $mouse, $ip, $status);
            $ins->execute();
            $ins->close();
            $created++;
        }
        $chk->close();
    }

    $conn->query("UPDATE labs SET total_computers = (SELECT COUNT(*) FROM computers WHERE computers.lab_id = labs.id)");

    return ['created' => $created, 'updated' => $updated, 'total' => 40];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedComputers($conn);
    echo "Computers Seeded: Total {$res['total']} (Created: {$res['created']}, Updated: {$res['updated']})\n";
}
