# Release v1.0.0 — Production Release
### Mahendra Multiple Campus &middot; Computer Laboratory Management System

Welcome to the initial production-ready release of the **Computer Laboratory Management System (CLMS)** for Mahendra Multiple Campus (Dang), developed for the **BCA 4th Semester (2024 Batch)**.

---

## 🚀 What's New in v1.0.0

### 💻 Laboratory & Terminal Management
* **Real-time Computer Directory**: Interactive workstation cards with color-coded operational states (`Available`, `Under Maintenance`, `Out of Service`).
* **Complete CRUD Engine**: Add, edit, inspect, and delete computer terminals with full hardware specs (Processor, RAM, Storage, OS, IP address, and purchase date).
* **Multi-Lab Configuration**: Dedicated management for BCA Lab 1, BCA Lab 2, CSIT Lab, and Research Lab.

### ⚠️ Incident & Fault Reporting Lifecycle
* **Student Incident Submission**: Intuitive reporting interface with hardware component classification (CPU, Monitor, Keyboard, Mouse, Network, Power) and photo evidence uploads (up to 5MB).
* **Administrative Review Center**: Interactive photo evidence lightbox viewer, real-time ticket review modal, priority tagging (`High`, `Medium`, `Low`), status progression (`Pending` → `Under Review` → `In Progress` → `Resolved` / `Rejected`), and technician assignment.
* **Audit Trail**: Real-time status update logs with timestamps and administrative remarks.

### 🔧 Maintenance & Hardware Logs
* Comprehensive maintenance ledger tracking component repairs, replacements, and routine diagnostics.
* Expense and cost ledger with technician accountability.

### 🛡️ Security, Authentication & Session Management
* Dedicated multi-role RBAC for **Administrator**, **Lab Assistant**, and **Student**.
* Dual-portal security guards with robust session timeout handling.
* Remember-Me cookie synchronization and clean session termination across all portals (`admin/logout.php` & `logout.php`).
* Input sanitation, SQL injection protection via prepared statements, and XSS filtering.

### 🎨 Modular UI & Modern Responsive Design
* Clean, Apple-inspired interface with 8px/12px/16px radius standards and zero gaudy gradients.
* Full responsiveness tailored for 14-inch laptops (1366x768) through large desktop displays.
* Zero inline scripts in view templates: 100% modular external JavaScript architecture (`assets/js/` and `admin/assets/js/`).
* High-resolution, authentic campus photographic graphics.

---

## 📦 Getting Started

### Database Installation
1. Start Apache & MySQL in **XAMPP**.
2. Create database `lab_management_system_db` in phpMyAdmin.
3. Import `database.sql`.

### Default Access Accounts
* **Admin Portal**: `admin@gmail.com` | `Admin@123`
* **Student Portal**: `user@gmail.com` | `User@123`

---

## 🏷️ Release Commands (Git)

```bash
git add .
git commit -m "feat: complete production-ready v1.0.0 release of Computer Laboratory Management System"
git tag -a v1.0.0 -m "Release v1.0.0: Mahendra Multiple Campus Computer Laboratory Management System"
```
