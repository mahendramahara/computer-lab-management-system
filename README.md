# Computer Laboratory Management System
### Mahendra Multiple Campus (Dang) — BCA 4th Semester Project (2024 Batch)

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.x-777bb4.svg)
![Database](https://img.shields.io/badge/MySQL-MariaDB-00758f.svg)
![Status](https://img.shields.io/badge/Release-Production--Ready-success.svg)

---

## 📌 Project Overview

The **Computer Laboratory Management System for Mahendra Multiple Campus** is a web-based application designed to simplify and organize the management of computer laboratories within the campus. The system allows administrators and lab assistants to maintain records of computers, monitor their availability and working conditions, and manage laboratory resources efficiently. Each computer is assigned a unique identification number along with its specifications, assigned laboratory, and current status, such as Available, In Use, Under Maintenance, or Out of Service. The dashboard provides an overview of the total computers, their conditions, laboratory-wise distribution, and recent system activities.

The system also provides a facility for students to report problems related to computers and their hardware components, including CPUs, monitors, keyboards, and mice. Students can select the affected computer, specify the faulty component, describe the issue, and submit a fault report. Administrators and lab assistants can review submitted reports, update their progress, manage repair activities, and record maintenance details, including repair descriptions, dates, technicians, and expenses. Students can also track the status of their submitted reports.

The application consists of three main user roles: **Administrator, Lab Assistant, and Student**, each with specific permissions and functionalities. The administrator manages the overall system, including users, laboratories, computers, maintenance records, and reports, while lab assistants handle computer monitoring, fault verification, and maintenance activities. Students can view computer availability and submit or track fault reports. The system follows CRUD (Create, Read, Update, and Delete) operations for managing its records and uses a centralized database to maintain organized information, improve maintenance tracking, reduce manual record-keeping, and support efficient computer laboratory management at Mahendra Multiple Campus.

---

## ✨ Key Features

### 👨‍💼 Administrator Portal
* **Live Operations Dashboard**: High-level telemetry of total computers, status donut charts, monthly issue trends, and quick navigation.
* **Computer Terminal Management**: Full CRUD operations for computers, hardware specs (CPU, RAM, Storage, OS, IP address), and real-time status toggling.
* **Lab Facility Management**: Multi-lab configuration (BCA Lab 1, BCA Lab 2, CSIT Lab, Research Lab) with room allocations and capacity metrics.
* **Fault Report Review Center**: Review reported issues with photo evidence lightbox, status updates (`Pending`, `Under Review`, `In Progress`, `Resolved`, `Rejected`), and technician assignments.
* **Maintenance & Repair Logging**: Hardware maintenance tracking with component replacement logs, repair cost records, and technician audit trails.
* **User Management**: Student, lab assistant, and administrator account directory with search filters and status moderation.
* **Broadcast Announcements**: Publish campus-wide lab notices and operational notifications.

### 🔬 Lab Assistant Portal
* Monitor real-time workstation status and hardware health.
* Verify student-reported hardware issues on the lab floor.
* Log repairs, component swaps, and maintenance activities.

### 🎓 Student Portal
* **Live Computer Directory**: View available terminals across campus labs before entering.
* **Quick Fault Reporting**: Step-by-step reporting with auto-suggestions, hardware component picker, and photo evidence upload (JPG/PNG/WebP up to 5MB).
* **My Reports Tracker**: Real-time status tracker for submitted tickets with admin remarks and resolution timestamps.
* **Profile Management**: Update academic details, program, semester, and credentials.

---

## 🛠️ Technology Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend** | PHP 8.x (Native, OOP / Modular Architecture, Prepared Statements) |
| **Database** | MySQL / MariaDB (InnoDB, Foreign Key Integrity, Indexed Queries) |
| **Frontend** | Vanilla JavaScript (ES6+, Event Delegation, Modular Asset Architecture) |
| **Styling** | Custom Responsive CSS3 (CSS Variables, Apple-inspired Typography, Flexbox/Grid) |
| **Icons & Media** | FontAwesome 6 Free, Photographic Campus Imagery, SVG Sparklines |
| **Server Environment** | Apache (XAMPP / WAMP / LAMP) |

---

## 📂 Project Architecture

```
computer-lab-management-system/
├── admin/                     # Administrative & Staff Portal
│   ├── assets/                # Admin-specific CSS, JavaScript, and Images
│   │   ├── css/               # admin-style.css, admin-root.css
│   │   ├── js/                # Modular scripts (computers.js, users.js, fault-reports.js, etc.)
│   │   └── images/            # Campus banners, avatars, lab operations graphics
│   ├── includes/              # Admin layout components (header, sidebar, footer, auth guard)
│   ├── computers.php          # Computer inventory management
│   ├── fault-reports.php      # Incident review & resolution center
│   ├── index.php              # Operational dashboard & telemetry
│   ├── labs.php               # Laboratory room management
│   ├── login.php              # Admin authentication gateway
│   ├── logout.php             # Secure admin session termination
│   ├── maintenance.php       # Hardware repair logs & expense tracking
│   ├── notifications.php      # Campus broadcast notifications
│   └── users.php              # User directory & account management
├── assets/                    # Public / Student Portal Assets
│   ├── css/                   # Responsive stylesheets (login.css, register.css, style.css)
│   ├── js/                    # Modular JavaScript (login.js, register.js, report-issue.js, app.js)
│   └── images/                # Brand emblems, hardware fault photos, lab backgrounds
├── config/
│   └── db.php                 # Central database connection & session security layer
├── includes/                  # Student portal layout components (navbar, footer, auth guards)
├── scripts/                   # Database seeders (users_seeder.php, fault_reports_seeder.php)
├── uploads/                   # User-uploaded fault evidence photos
├── database.sql               # Production schema and initial relational seed
├── index.php                  # Student home & lab workstation directory
├── login.php                  # Student login with role navigation
├── register.php               # Student self-registration gateway
├── report-issue.php           # Hardware problem submission form
├── my-reports.php             # Submitted tickets & status tracker
├── profile.php                # Student account profile management
└── README.md                  # System documentation
```

---

## 🚀 Installation & Local Setup

### Prerequisites
* [XAMPP](https://www.apachefriends.org/) (PHP 8.0+ and MySQL / MariaDB)
* Modern web browser (Chrome, Edge, Firefox, Safari)

### Step 1: Clone or Place into `htdocs`
Place the project directory inside your local web root:
```bash
C:\xampp\htdocs\computer-lab-management-system
```

### Step 2: Database Setup
1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Navigate to [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser.
3. Create a new database named:
   ```sql
   lab_management_system_db
   ```
4. Import the provided schema file:
   ```
   database.sql
   ```
   *(Alternatively, run the database seeder scripts inside `scripts/` to seed the official BCA 2024 batch student roster and real-world fault reports).*

### Step 3: Access the Application
* **Student & Public Portal**:
  `http://localhost/computer-lab-management-system/`
* **Administrator Portal**:
  `http://localhost/computer-lab-management-system/admin/login.php`

---

## 🔑 Default Credentials

| Portal | Role | Username / Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | Administrator | `admin@gmail.com` | `Admin@123` |
| **Student** | Student (Demo) | `user@gmail.com` | `User@123` |
| **Student** | BCA 2024 Batch | *(Any student email, e.g. `samyukta.rijal@gmail.com`)* | `User@123` |

---

## 👥 Authors & Academic Context

* **Institution**: Mahendra Multiple Campus (Dang)
* **Affiliation**: Tribhuvan University, Faculty of Humanities and Social Sciences
* **Academic Program**: Bachelor in Computer Application (BCA)
* **Semester**: 4th Semester (2024 Batch)
* **Course Work**: Project-I / Computer Lab Management System

---

## 📄 License
Developed for academic and institutional evaluation at Mahendra Multiple Campus. All rights reserved.
