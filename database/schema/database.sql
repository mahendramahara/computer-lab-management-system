-- ============================================================
-- COMPUTER LAB MANAGEMENT SYSTEM
-- Database: Use your existing database
-- MySQL / MySQLi compatible
-- ============================================================


-- ============================================================
-- 1. USERS
-- Stores student, lab assistant and administrator accounts.
-- Program and semester are applicable to student accounts.
-- ============================================================

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    roll_number   VARCHAR(50) NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    program       VARCHAR(50) NULL,
    semester      VARCHAR(50) NULL,
    role          ENUM('student', 'lab_assistant', 'admin') NOT NULL DEFAULT 'student',
    profile_image VARCHAR(255) NULL,
    status        ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- ============================================================
-- 2. COMPUTER LABS
-- Stores laboratory information and its basic configuration.
-- Each laboratory can contain multiple computers.
-- ============================================================

CREATE TABLE labs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    lab_name        VARCHAR(100) NOT NULL,
    lab_code        VARCHAR(30) NOT NULL UNIQUE,
    room_number     VARCHAR(50) NOT NULL,
    total_computers INT NOT NULL DEFAULT 0,
    location        VARCHAR(150) NULL,
    description     TEXT NULL,
    opening_hours   VARCHAR(100) NOT NULL DEFAULT 'Sunday - Friday, 7:00 AM - 5:00 PM',
    status          ENUM('active', 'maintenance', 'inactive') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- 3. COMPUTERS
-- Stores computer details, hardware specifications and status.
-- Every computer belongs to one laboratory.
-- ============================================================

CREATE TABLE computers (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    lab_id           INT NOT NULL,
    computer_code    VARCHAR(30) NOT NULL UNIQUE,
    computer_name    VARCHAR(100) NULL,
    processor        VARCHAR(100) NULL,
    ram              VARCHAR(50) NULL,
    storage          VARCHAR(50) NULL,
    ip_address       VARCHAR(45) NULL,
    operating_system VARCHAR(100) NULL,
    monitor          VARCHAR(100) NULL,
    keyboard         VARCHAR(100) NULL,
    mouse            VARCHAR(100) NULL,
    purchase_date    DATE NULL,
    status           ENUM('available', 'in_use', 'under_maintenance', 'out_of_service') NOT NULL DEFAULT 'available',
    specifications   TEXT NULL,
    remarks          TEXT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_computers_lab
        FOREIGN KEY (lab_id) REFERENCES labs(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);


-- ============================================================
-- 4. COMPUTER COMPONENTS
-- Tracks the individual condition of computer hardware parts.
-- A faulty component does not automatically disable the computer.
-- ============================================================

CREATE TABLE computer_components (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    computer_id      INT NOT NULL,
    component_type   ENUM('cpu', 'monitor', 'keyboard', 'mouse', 'speaker') NOT NULL,
    model_name       VARCHAR(100) NULL,
    serial_number    VARCHAR(80) NULL,
    condition_status ENUM('good', 'faulty', 'under_repair') NOT NULL DEFAULT 'good',
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_computer_component (computer_id, component_type),

    CONSTRAINT fk_components_computer
        FOREIGN KEY (computer_id) REFERENCES computers(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);


-- ============================================================
-- 5. FAULT REPORTS
-- Stores hardware and software problems reported by students.
-- Reports retain their history and cannot lose their owners.
-- ============================================================

CREATE TABLE fault_reports (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    computer_id   INT NOT NULL,
    component     ENUM('cpu', 'monitor', 'keyboard', 'mouse', 'speaker', 'network', 'software', 'other') NOT NULL,
    issue_title   VARCHAR(150) NOT NULL,
    description   TEXT NOT NULL,
    priority      ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status        ENUM('pending', 'under_review', 'in_progress', 'resolved', 'rejected') NOT NULL DEFAULT 'pending',
    assigned_to   INT NULL,
    admin_remarks VARCHAR(255) NULL,
    report_image  VARCHAR(255) NULL,
    reported_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at   DATETIME NULL,

    CONSTRAINT fk_reports_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_reports_computer
        FOREIGN KEY (computer_id) REFERENCES computers(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_reports_assigned
        FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);


-- ============================================================
-- 6. REPORT STATUS HISTORY
-- Maintains a timeline of every status change and its remarks.
-- Each entry records who changed the report status and when.
-- ============================================================

CREATE TABLE report_status_history (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    fault_report_id INT NOT NULL,
    status          ENUM('pending', 'under_review', 'in_progress', 'resolved', 'rejected') NOT NULL,
    changed_by      INT NULL,
    note            VARCHAR(255) NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_history_report
        FOREIGN KEY (fault_report_id) REFERENCES fault_reports(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_history_user
        FOREIGN KEY (changed_by) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);


-- ============================================================
-- 7. MAINTENANCE RECORDS
-- Stores computer repair, replacement and scheduled maintenance.
-- Previous maintenance records remain available for future reference.
-- ============================================================

CREATE TABLE maintenance_records (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    computer_id         INT NOT NULL,
    fault_report_id     INT NULL,
    technician_id       INT NULL,
    component           VARCHAR(100) NOT NULL,
    maintenance_type    ENUM('repair', 'replacement', 'inspection', 'scheduled') NOT NULL DEFAULT 'repair',
    problem_description TEXT NOT NULL,
    repair_description  TEXT NULL,
    repair_cost         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    maintenance_date    DATE NOT NULL,
    status              ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    remarks             TEXT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT chk_repair_cost CHECK (repair_cost >= 0),

    CONSTRAINT fk_maintenance_computer
        FOREIGN KEY (computer_id) REFERENCES computers(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_maintenance_report
        FOREIGN KEY (fault_report_id) REFERENCES fault_reports(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_maintenance_technician
        FOREIGN KEY (technician_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);


-- ============================================================
-- 8. NOTIFICATIONS
-- Supports personal notifications and laboratory-wide announcements.
-- Read status and notification type help organize dashboard messages.
-- ============================================================

CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NULL,
    lab_id     INT NULL,
    message    VARCHAR(255) NOT NULL,
    type       ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_notifications_lab
        FOREIGN KEY (lab_id) REFERENCES labs(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);


-- ============================================================
-- 9. INDEXES
-- Improves filtering and sorting for frequently accessed records.
-- Foreign key columns are already indexed by MySQL when required.
-- ============================================================

CREATE INDEX idx_computers_status
    ON computers(status);

CREATE INDEX idx_reports_status
    ON fault_reports(status);

CREATE INDEX idx_reports_reported_at
    ON fault_reports(reported_at);

CREATE INDEX idx_maintenance_date
    ON maintenance_records(maintenance_date);

CREATE INDEX idx_notifications_date
    ON notifications(created_at);


-- ============================================================
-- 10. LAB AVAILABILITY VIEW
-- Provides computer availability statistics for each active lab.
-- Used by the administrator dashboard and laboratory overview.
-- ============================================================

CREATE VIEW v_lab_availability AS
SELECT
    l.id AS lab_id,
    l.lab_name,
    l.location,
    COUNT(c.id) AS total_computers,

    COALESCE(SUM(c.status = 'available'), 0) AS available_computers,

    COALESCE(SUM(c.status = 'under_maintenance'), 0) AS maintenance_computers,

    COALESCE(SUM(c.status = 'out_of_service'), 0) AS out_of_service_computers

FROM labs l
LEFT JOIN computers c ON c.lab_id = l.id

WHERE l.status = 'active'

GROUP BY
    l.id,
    l.lab_name,
    l.location;


-- ============================================================
-- 11. REPORT DETAILS VIEW
-- Combines student, computer, laboratory and assigned staff details.
-- Retrieves the latest maintenance record associated with each report.
-- ============================================================

CREATE VIEW v_report_details AS
SELECT
    r.id,
    r.user_id,
    u.name AS student_name,
    u.roll_number,
    c.computer_code,
    l.lab_name,
    r.component,
    r.issue_title,
    r.description,
    r.priority,
    r.status,
    r.admin_remarks,
    r.report_image,
    r.reported_at,
    r.updated_at,
    r.resolved_at,
    t.name AS assigned_name,
    m.repair_description,
    m.repair_cost,
    m.maintenance_date

FROM fault_reports r

JOIN users u
    ON u.id = r.user_id

JOIN computers c
    ON c.id = r.computer_id

JOIN labs l
    ON l.id = c.lab_id

LEFT JOIN users t
    ON t.id = r.assigned_to

LEFT JOIN maintenance_records m
    ON m.id = (
        SELECT MAX(mr.id)
        FROM maintenance_records mr
        WHERE mr.fault_report_id = r.id
    );


-- ============================================================
-- 12. FAULT STATISTICS VIEW
-- Aggregates fault reports and resolution rates per laboratory.
-- ============================================================

CREATE VIEW v_fault_statistics AS
SELECT
    l.id AS lab_id,
    l.lab_name,
    COUNT(r.id) AS total_faults,
    COALESCE(SUM(r.status = 'pending'), 0) AS pending_faults,
    COALESCE(SUM(r.status = 'in_progress'), 0) AS in_progress_faults,
    COALESCE(SUM(r.status = 'resolved'), 0) AS resolved_faults,
    COALESCE(SUM(r.priority = 'high'), 0) AS high_priority_faults
FROM labs l
LEFT JOIN computers c ON c.lab_id = l.id
LEFT JOIN fault_reports r ON r.computer_id = c.id
GROUP BY l.id, l.lab_name;


-- ============================================================
-- 13. MAINTENANCE SUMMARY VIEW
-- Combines maintenance records with computer code and lab details.
-- ============================================================

CREATE VIEW v_maintenance_summary AS
SELECT
    m.id,
    m.computer_id,
    c.computer_code,
    l.lab_name,
    m.component,
    m.maintenance_type,
    m.problem_description,
    m.repair_description,
    m.repair_cost,
    m.maintenance_date,
    m.status,
    t.name AS technician_name
FROM maintenance_records m
JOIN computers c ON c.id = m.computer_id
JOIN labs l ON l.id = c.lab_id
LEFT JOIN users t ON t.id = m.technician_id;


-- ============================================================
-- DEMONSTRATION DATA
-- Sample accounts, laboratories, computers and maintenance records.
-- Demo account password: password
-- ============================================================


-- ============================================================
-- 12. DEMO USERS
-- Creates administrator, staff, and BCA 2024 batch student accounts.
-- Admin password: Admin@123
-- Student password: User@123
-- ============================================================

INSERT INTO users (
    name,
    email,
    roll_number,
    password,
    program,
    semester,
    role,
    status
) VALUES
(
    'Campus Admin',
    'admin@gmail.com',
    NULL,
    '$2y$10$HVBa82oysK/ZaWKVsqlUnOEKTnCJu0rDvdQ.3NB5VxGSJ7YjoYFd2',
    NULL,
    NULL,
    'admin',
    'active'
),
(
    'Bikash Adhikari',
    'lab@gmail.com',
    NULL,
    '$2y$10$HVBa82oysK/ZaWKVsqlUnOEKTnCJu0rDvdQ.3NB5VxGSJ7YjoYFd2',
    NULL,
    NULL,
    'lab_assistant',
    'active'
),
(
    'Ramesh KC',
    'ramesh.kc@gmail.com',
    NULL,
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    NULL,
    NULL,
    'lab_assistant',
    'active'
),
(
    'Sujan Bhandari',
    'sujan.bhandari@gmail.com',
    NULL,
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    NULL,
    NULL,
    'lab_assistant',
    'active'
),
(
    'Student User',
    'user@gmail.com',
    '5402021',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Anjana K.C.',
    'anjana.kc@gmail.com',
    '5402022',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Babin Dangi',
    'babin.dangi@gmail.com',
    '5402023',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Barun Chaudhary',
    'barun.chaudhary@gmail.com',
    '5402024',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Dipendra Oli',
    'dipendra.oli@gmail.com',
    '5402025',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Grish Rana',
    'grish.rana@gmail.com',
    '5402026',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Krishna Gorathoki',
    'krishna.gorathoki@gmail.com',
    '5402027',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Kshitiz Thapa',
    'kshitiz.thapa@gmail.com',
    '5402028',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Prajwol Chaudhary',
    'prajwol.chaudhary@gmail.com',
    '5402030',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Rekha Rijal',
    'rekha.rijal@gmail.com',
    '5402031',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Samana Oli',
    'samana.oli@gmail.com',
    '5402032',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
),
(
    'Yashu Dangi',
    'yashu.dangi@gmail.com',
    '5402033',
    '$2y$10$Nd1pMrkBWS9bKEyKpezTmOrhEw28aob5oEP5MEQYLvJdL.iAdZJ9m',
    'BCA',
    '4th Semester',
    'student',
    'active'
);


-- ============================================================
-- 13. DEMO LABORATORIES
-- Creates two computer laboratories with their room information.
-- Each laboratory will receive 20 demonstration computers.
-- ============================================================

INSERT INTO labs (
    lab_name,
    lab_code,
    room_number,
    location,
    description,
    total_computers
) VALUES
(
    'Computer Lab - 1',
    'LAB-01',
    'Room 101',
    'Block A, Ground Floor',
    'Main computer laboratory for programming and practical classes.',
    20
),
(
    'Computer Lab - 2',
    'LAB-02',
    'Room 201',
    'Block A, First Floor',
    'Secondary computer laboratory for web development and project work.',
    20
);


-- ============================================================
-- 14. DEMO COMPUTERS
-- Generates 20 computers for each laboratory automatically.
-- Computer codes range from PC-001 to PC-040.
-- ============================================================

INSERT INTO computers (
    lab_id,
    computer_code,
    computer_name,
    processor,
    ram,
    storage,
    operating_system,
    monitor,
    keyboard,
    mouse
)
SELECT
    l.id,
    CONCAT('PC-', LPAD((l.id - 1) * 20 + n.num, 3, '0')),
    CONCAT(l.lab_code, ' / PC ', LPAD(n.num, 2, '0')),
    'Intel Core i5',
    '8 GB',
    '256 GB SSD',
    'Windows 10 Pro',
    '19-inch LED',
    'USB Keyboard',
    'USB Mouse'

FROM labs l

CROSS JOIN (
    SELECT t.d * 10 + u.d + 1 AS num
    FROM (
        SELECT 0 AS d UNION ALL SELECT 1
    ) t
    CROSS JOIN (
        SELECT 0 AS d UNION ALL SELECT 1 UNION ALL SELECT 2
        UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
        UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
        UNION ALL SELECT 9
    ) u
) n

ORDER BY l.id, n.num;


-- ============================================================
-- 15. DEMO COMPUTER STATUS
-- Sets sample computers to maintenance and out-of-service states.
-- Dashboard availability will show 32 available and 8 unavailable.
-- ============================================================

UPDATE computers
SET status = 'under_maintenance'
WHERE computer_code IN (
    'PC-004',
    'PC-009',
    'PC-025',
    'PC-031',
    'PC-036'
);

UPDATE computers
SET status = 'out_of_service'
WHERE computer_code IN (
    'PC-015',
    'PC-018',
    'PC-028'
);


-- ============================================================
-- 16. DEMO COMPUTER COMPONENTS
-- Creates individual component records for every computer.
-- Initial component condition is set to good.
-- ============================================================

INSERT INTO computer_components (
    computer_id,
    component_type,
    model_name
)
SELECT
    c.id,
    p.component_type,
    p.model_name

FROM computers c

CROSS JOIN (
    SELECT 'cpu' AS component_type, 'Core i5 System Unit' AS model_name
    UNION ALL SELECT 'monitor', '19-inch LED Monitor'
    UNION ALL SELECT 'keyboard', 'USB Keyboard'
    UNION ALL SELECT 'mouse', 'USB Mouse'
) p;


-- ============================================================
-- 17. DEMO COMPONENT CONDITIONS
-- Marks two individual components as requiring repair.
-- The overall computer status remains managed separately.
-- ============================================================

UPDATE computer_components cc

JOIN computers c
    ON c.id = cc.computer_id

SET cc.condition_status = 'under_repair'

WHERE
    (c.computer_code = 'PC-004' AND cc.component_type = 'keyboard')
    OR
    (c.computer_code = 'PC-009' AND cc.component_type = 'monitor');


-- ============================================================
-- 18. DEMO ACCOUNT REFERENCES
-- Retrieves account and computer IDs using their unique identifiers.
-- These references are used to insert related demonstration records.
-- ============================================================

SET @admin = (SELECT id FROM users WHERE email = 'admin@gmail.com' LIMIT 1);
SET @assistant = (SELECT id FROM users WHERE email = 'lab@gmail.com' LIMIT 1);
SET @tech_ramesh = (SELECT id FROM users WHERE email = 'ramesh.kc@gmail.com' LIMIT 1);
SET @tech_sujan = (SELECT id FROM users WHERE email = 'sujan.bhandari@gmail.com' LIMIT 1);

SET @user_demo = (SELECT id FROM users WHERE email = 'user@gmail.com' LIMIT 1);
SET @stu_anjana = (SELECT id FROM users WHERE email = 'anjana.kc@gmail.com' LIMIT 1);
SET @stu_babin = (SELECT id FROM users WHERE email = 'babin.dangi@gmail.com' LIMIT 1);
SET @stu_barun = (SELECT id FROM users WHERE email = 'barun.chaudhary@gmail.com' LIMIT 1);
SET @stu_dipendra = (SELECT id FROM users WHERE email = 'dipendra.oli@gmail.com' LIMIT 1);
SET @stu_grish = (SELECT id FROM users WHERE email = 'grish.rana@gmail.com' LIMIT 1);
SET @stu_krishna = (SELECT id FROM users WHERE email = 'krishna.gorathoki@gmail.com' LIMIT 1);
SET @stu_kshitiz = (SELECT id FROM users WHERE email = 'kshitiz.thapa@gmail.com' LIMIT 1);
SET @stu_prajwol = (SELECT id FROM users WHERE email = 'prajwol.chaudhary@gmail.com' LIMIT 1);

SET @pc03 = (SELECT id FROM computers WHERE computer_code = 'PC-003' LIMIT 1);
SET @pc04 = (SELECT id FROM computers WHERE computer_code = 'PC-004' LIMIT 1);
SET @pc06 = (SELECT id FROM computers WHERE computer_code = 'PC-006' LIMIT 1);
SET @pc07 = (SELECT id FROM computers WHERE computer_code = 'PC-007' LIMIT 1);
SET @pc09 = (SELECT id FROM computers WHERE computer_code = 'PC-009' LIMIT 1);
SET @pc10 = (SELECT id FROM computers WHERE computer_code = 'PC-010' LIMIT 1);
SET @pc12 = (SELECT id FROM computers WHERE computer_code = 'PC-012' LIMIT 1);
SET @pc14 = (SELECT id FROM computers WHERE computer_code = 'PC-014' LIMIT 1);
SET @pc18 = (SELECT id FROM computers WHERE computer_code = 'PC-018' LIMIT 1);
SET @pc21 = (SELECT id FROM computers WHERE computer_code = 'PC-021' LIMIT 1);


-- ============================================================
-- 19. DEMO FAULT REPORTS
-- Creates realistic student reports in Romanized Nepali with photo evidence.
-- Each report is associated with a student and laboratory workstation.
-- ============================================================

INSERT INTO fault_reports (
    id,
    user_id,
    computer_id,
    component,
    issue_title,
    description,
    priority,
    status,
    report_image,
    assigned_to,
    reported_at,
    resolved_at
) VALUES

(
    1,
    @stu_babin,
    @pc14,
    'keyboard',
    'Keyboard chalna xodyo ani keys thichidaina',
    'Lab ma programming practical kam garda gardai khoi k problem vayexa keyboard chalna xodyo, spacebar ra enter key thichdai thichidaina, practical garnai garho vayo.',
    'medium',
    'in_progress',
    'uploads/reports/fault_keyboard_keys.jpg',
    @assistant,
    NOW() - INTERVAL 2 DAY,
    NULL
),

(
    2,
    @stu_anjana,
    @pc07,
    'monitor',
    'Monitor jhim-jhim garera screen black hunchha',
    'Database lab ma SQL query lekhda lekhdai monitor screen achanak jhim-jhim garchha ani black screen vaihalchha. Power cable halka hilayo vane ekchhin aaunchha feri bilkul janchha.',
    'high',
    'pending',
    'uploads/reports/fault_monitor_flicker.jpg',
    NULL,
    NOW() - INTERVAL 1 DAY,
    NULL
),

(
    3,
    @stu_barun,
    @pc21,
    'mouse',
    'Mouse cursor hudda herdai adkine ra click nahune',
    'Mouse ko optical sensor le ramrari kaam gareko xaina, cursor ekkai thau ma adkinchha ra left click 2-3 choti jabarjasti thichnu parxha, kaam garna asajilo vayo.',
    'low',
    'under_review',
    'uploads/reports/fault_mouse_sensor.jpg',
    @assistant,
    NOW() - INTERVAL 3 DAY,
    NULL
),

(
    4,
    @stu_dipendra,
    @pc03,
    'cpu',
    'CPU power button thichda pani on hudai vayena',
    'Aaja bihana class ma aauda power button thichyo tara CPU bata kunai response aayena, fan pani ghumnna ani picheko green light pani balena, power supply bigreko jasto chha.',
    'high',
    'resolved',
    'uploads/reports/fault_cpu_power.jpg',
    @tech_ramesh,
    NOW() - INTERVAL 4 DAY,
    NOW() - INTERVAL 2 DAY
),

(
    5,
    @stu_grish,
    @pc18,
    'network',
    'Ethernet cable clip bhachiyera internet chhutiranchha',
    'Network cable ko plastic clip bhachiyeko rahechha, table halka matra hallida pani internet disconnect vaihalchha ani lab project submit garna garho vayo.',
    'medium',
    'in_progress',
    'uploads/reports/fault_network_cable.jpg',
    @assistant,
    NOW() - INTERVAL 1 DAY,
    NULL
),

(
    6,
    @stu_krishna,
    @pc12,
    'speaker',
    'Front audio port vitra headphone pin bhachiyera adkiyo',
    'Headphone ko 3.5mm jack pin agadi ko port vitra nai bhachiyera thuniyo, nikalna pani milena ani sound pani suninna, port safa garidinus.',
    'low',
    'under_review',
    'uploads/reports/fault_audio_jack.jpg',
    @assistant,
    NOW() - INTERVAL 18 HOUR,
    NULL
),

(
    7,
    @stu_kshitiz,
    @pc04,
    'software',
    'Kam garda gardai Blue Screen (BSOD) aayera restart vayo',
    'Lab evaluation ko lagi C++ program run garna lagda achanak blue screen (MEMORY_MANAGEMENT) error aayo ani PC restart vayo, gari rakheko adha project file save nai vayana.',
    'high',
    'pending',
    'uploads/reports/fault_bsod_screen.jpg',
    NULL,
    NOW() - INTERVAL 7 HOUR,
    NULL
),

(
    8,
    @stu_prajwol,
    @pc09,
    'other',
    'Power strip ma spark aayera switch board tatyo',
    'Computer chalai rakheko bela switch strip ma chat-chat spark aayo ani plastic poleko jasto gandha aayo, short circuit huna sakchha turunta heridinus.',
    'high',
    'in_progress',
    'uploads/reports/fault_power_strip.jpg',
    @tech_sujan,
    NOW() - INTERVAL 4 HOUR,
    NULL
);


-- ============================================================
-- 20. DEMO REPORT STATUS HISTORY
-- Creates historical status entries for the sample fault reports.
-- These records provide the timeline shown on report details pages.
-- ============================================================

INSERT INTO report_status_history (
    fault_report_id,
    status,
    changed_by,
    note,
    created_at
) VALUES

(1, 'pending', @stu_babin, 'Report submitted by Babin Dangi', NOW() - INTERVAL 2 DAY),
(1, 'under_review', @assistant, 'Lab Assistant le keyboard inspect gardai', NOW() - INTERVAL 45 HOUR),
(1, 'in_progress', @assistant, 'Naya keyboard replacement ko lagi process suru vayo', NOW() - INTERVAL 1 DAY),

(2, 'pending', @stu_anjana, 'Report submitted by Anjana K.C.', NOW() - INTERVAL 1 DAY),

(3, 'pending', @stu_barun, 'Report submitted by Barun Chaudhary', NOW() - INTERVAL 3 DAY),
(3, 'under_review', @assistant, 'Mouse sensor check gariyo', NOW() - INTERVAL 2 DAY),

(4, 'pending', @stu_dipendra, 'Report submitted by Dipendra Oli', NOW() - INTERVAL 4 DAY),
(4, 'in_progress', @tech_ramesh, 'Technician Ramesh KC le SMPS check gardai', NOW() - INTERVAL 3 DAY),
(4, 'resolved', @tech_ramesh, 'Naya power supply unit halera computer chalaiyo', NOW() - INTERVAL 2 DAY),

(7, 'pending', @stu_kshitiz, 'Report submitted by Kshitiz Thapa', NOW() - INTERVAL 7 HOUR);


-- ============================================================
-- 21. DEMO MAINTENANCE RECORDS
-- Creates sample repair and replacement records for reported issues.
-- Maintenance records retain their repair descriptions and costs.
-- ============================================================

INSERT INTO maintenance_records (
    computer_id,
    fault_report_id,
    technician_id,
    component,
    maintenance_type,
    problem_description,
    repair_description,
    repair_cost,
    maintenance_date,
    status
) VALUES

(
    @pc03,
    4,
    @tech_ramesh,
    'CPU / Power Supply',
    'replacement',
    'Power Supply unit burnt out completely.',
    'Swapped with brand new 450W power supply and tested voltage rails.',
    1200.00,
    CURDATE() - INTERVAL 2 DAY,
    'completed'
),

(
    @pc06,
    NULL,
    @tech_sujan,
    'CPU / Chassis',
    'scheduled',
    'Routine seasonal dust cleaning and heatsink thermal paste re-application.',
    'Cleaned internal chassis fans and dusted motherboard components.',
    0.00,
    CURDATE() - INTERVAL 6 DAY,
    'completed'
),

(
    @pc10,
    NULL,
    @tech_ramesh,
    'Monitor',
    'repair',
    'Capacitor replacement on internal power board and display color calibration.',
    'Replaced bulging filter capacitor on monitor power inverter board.',
    350.00,
    CURDATE() - INTERVAL 10 DAY,
    'completed'
);


-- ============================================================
-- 22. DEMO NOTIFICATIONS
-- Creates laboratory announcements for the dashboard.
-- ============================================================

INSERT INTO notifications (
    lab_id,
    message,
    type,
    created_at
)

SELECT
    l.id,
    'Lab 1: 2 computers are under maintenance.',
    'danger',
    CONCAT(CURDATE(), ' 10:20:00')
FROM labs l
WHERE l.lab_code = 'LAB-01'

UNION ALL

SELECT
    NULL,
    'Lab hours extended: All computer labs remain open until 6:00 PM during project evaluation week.',
    'info',
    NOW() - INTERVAL 1 DAY

UNION ALL

SELECT
    l.id,
    'Lab 2 will be closed tomorrow for scheduled maintenance.',
    'warning',
    NOW() - INTERVAL 3 DAY
FROM labs l
WHERE l.lab_code = 'LAB-02';


-- ============================================================
-- END OF DATABASE SCRIPT
-- ============================================================