<?php
// ============================================================
// USERS SEEDER — Mahendra Multiple Campus
// BCA 2024 Batch Students & Campus Staff
// ============================================================

require_once __DIR__ . '/../config/db.php';

function seedUsers(mysqli $conn): array {
    $users = [
        // Administrator Account (Admin@123)
        [
            'name'        => 'Campus Admin',
            'email'       => 'admin@gmail.com',
            'roll_number' => null,
            'password'    => 'Admin@123',
            'program'     => null,
            'semester'    => null,
            'role'        => 'admin',
            'status'      => 'active',
        ],

        // Lab Staff & Technicians
        [
            'name'        => 'Bikash Adhikari',
            'email'       => 'lab@gmail.com',
            'roll_number' => null,
            'password'    => 'Admin@123',
            'program'     => null,
            'semester'    => null,
            'role'        => 'lab_assistant',
            'status'      => 'active',
        ],
        [
            'name'        => 'Ramesh KC',
            'email'       => 'ramesh.kc@gmail.com',
            'roll_number' => null,
            'password'    => 'User@123',
            'program'     => null,
            'semester'    => null,
            'role'        => 'lab_assistant',
            'status'      => 'active',
        ],
        [
            'name'        => 'Sujan Bhandari',
            'email'       => 'sujan.bhandari@gmail.com',
            'roll_number' => null,
            'password'    => 'User@123',
            'program'     => null,
            'semester'    => null,
            'role'        => 'lab_assistant',
            'status'      => 'active',
        ],

        // Extra Student Account with Random Nepali Name (User@123)
        [
            'name'        => 'Suresh Pokhrel',
            'email'       => 'user@gmail.com',
            'roll_number' => '5402021',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],

        // BCA 2024 Batch Students (Roll Number, First.Last@gmail.com, User@123)
        [
            'name'        => 'Anjana K.C.',
            'email'       => 'anjana.kc@gmail.com',
            'roll_number' => '5402022',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Babin Dangi',
            'email'       => 'babin.dangi@gmail.com',
            'roll_number' => '5402023',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Barun Chaudhary',
            'email'       => 'barun.chaudhary@gmail.com',
            'roll_number' => '5402024',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Dipendra Oli',
            'email'       => 'dipendra.oli@gmail.com',
            'roll_number' => '5402025',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Grish Rana',
            'email'       => 'grish.rana@gmail.com',
            'roll_number' => '5402026',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Krishna Gorathoki',
            'email'       => 'krishna.gorathoki@gmail.com',
            'roll_number' => '5402027',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Kshitiz Thapa',
            'email'       => 'kshitiz.thapa@gmail.com',
            'roll_number' => '5402028',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Prajwol Chaudhary',
            'email'       => 'prajwol.chaudhary@gmail.com',
            'roll_number' => '5402030',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Rekha Rijal',
            'email'       => 'rekha.rijal@gmail.com',
            'roll_number' => '5402031',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Samana Oli',
            'email'       => 'samana.oli@gmail.com',
            'roll_number' => '5402032',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
        [
            'name'        => 'Yashu Dangi',
            'email'       => 'yashu.dangi@gmail.com',
            'roll_number' => '5402033',
            'password'    => 'User@123',
            'program'     => 'BCA',
            'semester'    => '4th Semester',
            'role'        => 'student',
            'status'      => 'active',
        ],
    ];

    $created = 0;
    $updated = 0;

    foreach ($users as $u) {
        $hashed = password_hash($u['password'], PASSWORD_BCRYPT);

        // Check if user exists by email or roll_number
        $stmt = null;
        if (!empty($u['roll_number'])) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR roll_number = ? LIMIT 1");
            $stmt->bind_param('ss', $u['email'], $u['roll_number']);
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $u['email']);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $up = $conn->prepare("UPDATE users SET name = ?, email = ?, roll_number = ?, password = ?, program = ?, semester = ?, role = ?, status = ? WHERE id = ?");
            $up->bind_param('ssssssssi', $u['name'], $u['email'], $u['roll_number'], $hashed, $u['program'], $u['semester'], $u['role'], $u['status'], $row['id']);
            $up->execute();
            $up->close();
            $updated++;
        } else {
            $ins = $conn->prepare("INSERT INTO users (name, email, roll_number, password, program, semester, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('ssssssss', $u['name'], $u['email'], $u['roll_number'], $hashed, $u['program'], $u['semester'], $u['role'], $u['status']);
            $ins->execute();
            $ins->close();
            $created++;
        }
    }

    return ['created' => $created, 'updated' => $updated, 'total' => count($users)];
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = seedUsers($conn);
    echo "Users Seeded: Total {$res['total']} (Created: {$res['created']}, Updated: {$res['updated']})\n";
}
