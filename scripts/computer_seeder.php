<?php
// ============================================================
// COMPUTER & LAB SEEDER — Mahendra Multiple Campus Computer Lab Management
// Seeds: Laboratories, Computers (40), and Hardware Components
// Run CLI: php scripts/computer_seeder.php
// ============================================================

require_once __DIR__ . '/../config/db.php';

$isCli = php_sapi_name() === 'cli';

if (!function_exists('logSeedPC')) {
    function logSeedPC(string $msg): void {
        global $isCli;
        if ($isCli) {
            echo $msg . "\n";
        }
    }
}

function seedComputers(mysqli $conn): array {
    // ── 1. Seed Labs ──────────────────────────────────────────
    $labsData = [
        [
            'lab_name'        => 'Computer Lab - 1',
            'lab_code'        => 'LAB-01',
            'room_number'     => 'Room 101',
            'location'        => 'Block A, Ground Floor',
            'description'     => 'Main computer laboratory for programming and practical classes.',
            'total_computers' => 20,
            'opening_hours'   => 'Sunday - Friday, 7:00 AM - 5:00 PM',
            'status'          => 'active'
        ],
        [
            'lab_name'        => 'Computer Lab - 2',
            'lab_code'        => 'LAB-02',
            'room_number'     => 'Room 201',
            'location'        => 'Block B, First Floor',
            'description'     => 'Secondary computer laboratory for web development and project work.',
            'total_computers' => 20,
            'opening_hours'   => 'Sunday - Friday, 7:00 AM - 5:00 PM',
            'status'          => 'active'
        ]
    ];

    $labIds = [];
    $labsCount = 0;

    foreach ($labsData as $ld) {
        $check = $conn->prepare("SELECT id FROM labs WHERE lab_code = ? LIMIT 1");
        $check->bind_param('s', $ld['lab_code']);
        $check->execute();
        $res = $check->get_result();

        if ($res && $res->num_rows > 0) {
            $id = $res->fetch_assoc()['id'];
            $labIds[$ld['lab_code']] = $id;
            $upLab = $conn->prepare("UPDATE labs SET lab_name = ?, room_number = ?, location = ?, description = ?, opening_hours = ?, status = ? WHERE id = ?");
            $upLab->bind_param('ssssssi', $ld['lab_name'], $ld['room_number'], $ld['location'], $ld['description'], $ld['opening_hours'], $ld['status'], $id);
            $upLab->execute();
            $upLab->close();
            logSeedPC("  [UPDATE] Lab: {$ld['lab_name']} ({$ld['lab_code']})");
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO labs (lab_name, lab_code, room_number, location, description, total_computers, opening_hours, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'sssssiss',
                $ld['lab_name'], $ld['lab_code'], $ld['room_number'], $ld['location'],
                $ld['description'], $ld['total_computers'], $ld['opening_hours'], $ld['status']
            );
            $stmt->execute();
            $id = $conn->insert_id;
            $labIds[$ld['lab_code']] = $id;
            $stmt->close();
            logSeedPC("  [INSERT] Lab: {$ld['lab_name']} ({$ld['lab_code']})");
        }
        $check->close();
        $labsCount++;
    }

    // ── 2. Seed 40 Computers ──────────────────────────────────
    // Status distribution matching UI dashboard screenshot:
    // 32 Available, 5 Under Maintenance, 3 Out of Service
    $maintenanceCodes  = ['PC-006', 'PC-009', 'PC-025', 'PC-031', 'PC-036'];
    $outOfServiceCodes = ['PC-004', 'PC-015', 'PC-028'];

    $pcCreated = 0;
    $pcUpdated = 0;

    for ($i = 1; $i <= 40; $i++) {
        $num  = str_pad($i, 3, '0', STR_PAD_LEFT);
        $code = 'PC-' . $num;

        // 20 PCs in Lab 1, 20 PCs in Lab 2
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
            $pcId = $pcRes->fetch_assoc()['id'];
            $up = $conn->prepare(
                "UPDATE computers SET lab_id = ?, computer_name = ?, processor = ?, ram = ?, storage = ?,
                 operating_system = ?, monitor = ?, keyboard = ?, mouse = ?, ip_address = ?, status = ?
                 WHERE id = ?"
            );
            $up->bind_param('issssssssssi', $labId, $name, $proc, $ram, $storage, $os, $monitor, $keyboard, $mouse, $ip, $status, $pcId);
            $up->execute();
            $up->close();
            $pcUpdated++;
        } else {
            $ins = $conn->prepare(
                "INSERT INTO computers (lab_id, computer_code, computer_name, processor, ram, storage, operating_system, monitor, keyboard, mouse, ip_address, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->bind_param('isssssssssss', $labId, $code, $name, $proc, $ram, $storage, $os, $monitor, $keyboard, $mouse, $ip, $status);
            $ins->execute();
            $pcId = $conn->insert_id;
            $ins->close();
            $pcCreated++;
        }
        $chk->close();

        // ── 3. Seed Components for this PC ─────────────────────
        $components = [
            ['cpu', 'Intel Core i5/i7 System Unit', 'good'],
            ['monitor', 'Dell 24" IPS Monitor', (in_array($code, ['PC-009', 'PC-025'], true) ? 'under_repair' : 'good')],
            ['keyboard', 'Logitech K120 Keyboard', (in_array($code, ['PC-004', 'PC-006'], true) ? 'under_repair' : 'good')],
            ['mouse', 'Logitech B100 Optical Mouse', 'good'],
            ['speaker', 'Realtek HD Audio Output', 'good'],
        ];

        foreach ($components as $cmp) {
            $cType  = $cmp[0];
            $cModel = $cmp[1];
            $cCond  = $cmp[2];

            $cChk = $conn->prepare("SELECT id FROM computer_components WHERE computer_id = ? AND component_type = ? LIMIT 1");
            $cChk->bind_param('is', $pcId, $cType);
            $cChk->execute();
            $cRes = $cChk->get_result();

            if ($cRes && $cRes->num_rows > 0) {
                $cId = $cRes->fetch_assoc()['id'];
                $cUp = $conn->prepare("UPDATE computer_components SET model_name = ?, condition_status = ? WHERE id = ?");
                $cUp->bind_param('ssi', $cModel, $cCond, $cId);
                $cUp->execute();
                $cUp->close();
            } else {
                $cIns = $conn->prepare("INSERT INTO computer_components (computer_id, component_type, model_name, condition_status) VALUES (?, ?, ?, ?)");
                $cIns->bind_param('isss', $pcId, $cType, $cModel, $cCond);
                $cIns->execute();
                $cIns->close();
            }
            $cChk->close();
        }
    }

    // Refresh lab computer counts
    $conn->query("UPDATE labs SET total_computers = (SELECT COUNT(*) FROM computers WHERE computers.lab_id = labs.id)");

    return [
        'labs'         => $labsCount,
        'computers'    => 40,
        'created'      => $pcCreated,
        'updated'      => $pcUpdated,
        'available'    => 32,
        'maintenance'  => 5,
        'out_of_service' => 3
    ];
}

if ($isCli && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "============================================================\n";
    echo "  Seeding MMC Laboratories & Computers (40 PCs)...\n";
    echo "============================================================\n";
    $stats = seedComputers($conn);
    echo "Done! Labs: {$stats['labs']}, Computers Processed: {$stats['computers']} (Created: {$stats['created']}, Updated: {$stats['updated']})\n";
}
