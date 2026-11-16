<?php
// ============================================================
// LABS SEEDER — Mahendra Multiple Campus
// Seeds: Computer Lab - 1 (LAB-01) and Computer Lab - 2 (LAB-02)
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedLabs(mysqli $conn): array {
    $labs = [
        [
            'lab_name'        => 'Computer Lab - 1',
            'lab_code'        => 'LAB-01',
            'room_number'     => 'Room 101',
            'location'        => 'Block A, Ground Floor',
            'description'     => 'Main computer laboratory for programming, web tech, and database practical sessions.',
            'total_computers' => 20,
            'opening_hours'   => 'Sunday - Friday, 7:00 AM - 5:00 PM',
            'status'          => 'active'
        ],
        [
            'lab_name'        => 'Computer Lab - 2',
            'lab_code'        => 'LAB-02',
            'room_number'     => 'Room 201',
            'location'        => 'Block B, First Floor',
            'description'     => 'Secondary advanced computer laboratory for BCA project evaluations and software labs.',
            'total_computers' => 20,
            'opening_hours'   => 'Sunday - Friday, 7:00 AM - 5:00 PM',
            'status'          => 'active'
        ]
    ];

    $created = 0;
    $updated = 0;
    $labIds  = [];

    foreach ($labs as $ld) {
        $chk = $conn->prepare("SELECT id FROM labs WHERE lab_code = ? LIMIT 1");
        $chk->bind_param('s', $ld['lab_code']);
        $chk->execute();
        $res = $chk->get_result();

        if ($res && $res->num_rows > 0) {
            $id = (int)$res->fetch_assoc()['id'];
            $labIds[$ld['lab_code']] = $id;
            $up = $conn->prepare("UPDATE labs SET lab_name = ?, room_number = ?, location = ?, description = ?, opening_hours = ?, status = ? WHERE id = ?");
            $up->bind_param('ssssssi', $ld['lab_name'], $ld['room_number'], $ld['location'], $ld['description'], $ld['opening_hours'], $ld['status'], $id);
            $up->execute();
            $up->close();
            $updated++;
        } else {
            $ins = $conn->prepare("INSERT INTO labs (lab_name, lab_code, room_number, location, description, total_computers, opening_hours, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('sssssiss', $ld['lab_name'], $ld['lab_code'], $ld['room_number'], $ld['location'], $ld['description'], $ld['total_computers'], $ld['opening_hours'], $ld['status']);
            $ins->execute();
            $id = (int)$conn->insert_id;
            $labIds[$ld['lab_code']] = $id;
            $ins->close();
            $created++;
        }
        $chk->close();
    }

    return ['created' => $created, 'updated' => $updated, 'total' => count($labs), 'lab_ids' => $labIds];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedLabs($conn);
    echo "Labs Seeded: Total {$res['total']} (Created: {$res['created']}, Updated: {$res['updated']})\n";
}
