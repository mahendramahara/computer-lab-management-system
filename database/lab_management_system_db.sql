-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2026 at 01:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lab_management_system_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `computer_code` varchar(30) NOT NULL,
  `computer_name` varchar(100) DEFAULT NULL,
  `processor` varchar(100) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `monitor` varchar(100) DEFAULT NULL,
  `keyboard` varchar(100) DEFAULT NULL,
  `mouse` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `status` enum('available','in_use','under_maintenance','out_of_service') NOT NULL DEFAULT 'available',
  `specifications` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`id`, `lab_id`, `computer_code`, `computer_name`, `processor`, `ram`, `storage`, `ip_address`, `operating_system`, `monitor`, `keyboard`, `mouse`, `purchase_date`, `status`, `specifications`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 'PC-001', 'LAB-01 / PC 01', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.101', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(2, 1, 'PC-002', 'LAB-01 / PC 02', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.102', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(3, 1, 'PC-003', 'LAB-01 / PC 03', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.103', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(4, 1, 'PC-004', 'LAB-01 / PC 04', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.104', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'out_of_service', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(5, 1, 'PC-005', 'LAB-01 / PC 05', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.105', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(6, 1, 'PC-006', 'LAB-01 / PC 06', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.106', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'under_maintenance', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(7, 1, 'PC-007', 'LAB-01 / PC 07', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.107', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(8, 1, 'PC-008', 'LAB-01 / PC 08', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.108', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(9, 1, 'PC-009', 'LAB-01 / PC 09', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.109', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'under_maintenance', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(10, 1, 'PC-010', 'LAB-01 / PC 10', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.110', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(11, 1, 'PC-011', 'LAB-01 / PC 11', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.111', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(12, 1, 'PC-012', 'LAB-01 / PC 12', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.112', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(13, 1, 'PC-013', 'LAB-01 / PC 13', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.113', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(14, 1, 'PC-014', 'LAB-01 / PC 14', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.114', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(15, 1, 'PC-015', 'LAB-01 / PC 15', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.115', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'out_of_service', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(16, 1, 'PC-016', 'LAB-01 / PC 16', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.116', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(17, 1, 'PC-017', 'LAB-01 / PC 17', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.117', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(18, 1, 'PC-018', 'LAB-01 / PC 18', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.118', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(19, 1, 'PC-019', 'LAB-01 / PC 19', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.119', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(20, 1, 'PC-020', 'LAB-01 / PC 20', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.120', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(21, 2, 'PC-021', 'LAB-02 / PC 01', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.121', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(22, 2, 'PC-022', 'LAB-02 / PC 02', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.122', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(23, 2, 'PC-023', 'LAB-02 / PC 03', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.123', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(24, 2, 'PC-024', 'LAB-02 / PC 04', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.124', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(25, 2, 'PC-025', 'LAB-02 / PC 05', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.125', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'under_maintenance', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(26, 2, 'PC-026', 'LAB-02 / PC 06', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.126', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(27, 2, 'PC-027', 'LAB-02 / PC 07', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.127', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(28, 2, 'PC-028', 'LAB-02 / PC 08', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.128', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'out_of_service', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(29, 2, 'PC-029', 'LAB-02 / PC 09', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.129', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(30, 2, 'PC-030', 'LAB-02 / PC 10', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.130', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(31, 2, 'PC-031', 'LAB-02 / PC 11', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.131', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'under_maintenance', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(32, 2, 'PC-032', 'LAB-02 / PC 12', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.132', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(33, 2, 'PC-033', 'LAB-02 / PC 13', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.133', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(34, 2, 'PC-034', 'LAB-02 / PC 14', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.134', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(35, 2, 'PC-035', 'LAB-02 / PC 15', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.135', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(36, 2, 'PC-036', 'LAB-02 / PC 16', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.136', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'under_maintenance', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(37, 2, 'PC-037', 'LAB-02 / PC 17', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.137', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(38, 2, 'PC-038', 'LAB-02 / PC 18', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.138', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(39, 2, 'PC-039', 'LAB-02 / PC 19', 'Intel Core i7-11700 (2.50 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.139', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58'),
(40, 2, 'PC-040', 'LAB-02 / PC 20', 'Intel Core i5-10400 (2.90 GHz)', '16 GB DDR4', '512 GB NVMe SSD', '192.168.1.140', 'Windows 11 Pro 64-bit', 'Dell 24\" IPS FHD (1920x1080)', 'Logitech K120 USB Keyboard', 'Logitech B100 Optical USB Mouse', NULL, 'available', NULL, NULL, '2026-11-16 12:50:25', '2026-11-16 12:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `computer_components`
--

CREATE TABLE `computer_components` (
  `id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `component_type` enum('cpu','monitor','keyboard','mouse','speaker') NOT NULL,
  `model_name` varchar(100) DEFAULT NULL,
  `serial_number` varchar(80) DEFAULT NULL,
  `condition_status` enum('good','faulty','under_repair') NOT NULL DEFAULT 'good',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer_components`
--

INSERT INTO `computer_components` (`id`, `computer_id`, `component_type`, `model_name`, `serial_number`, `condition_status`, `updated_at`) VALUES
(1, 1, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(2, 1, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(3, 1, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(4, 1, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(5, 2, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(6, 2, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(7, 2, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(8, 2, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(9, 3, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(10, 3, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(11, 3, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(12, 3, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(13, 5, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(14, 5, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(15, 5, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(16, 5, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(17, 6, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(18, 6, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(19, 6, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'under_repair', '2026-11-16 12:50:58'),
(20, 6, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(21, 7, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(22, 7, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(23, 7, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(24, 7, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(25, 8, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(26, 8, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(27, 8, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(28, 8, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(29, 10, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(30, 10, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(31, 10, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(32, 10, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(33, 11, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(34, 11, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(35, 11, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(36, 11, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(37, 12, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(38, 12, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(39, 12, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(40, 12, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(41, 13, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(42, 13, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(43, 13, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(44, 13, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(45, 14, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(46, 14, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(47, 14, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(48, 14, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(49, 16, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(50, 16, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(51, 16, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(52, 16, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(53, 17, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(54, 17, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(55, 17, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(56, 17, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(57, 19, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(58, 19, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(59, 19, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(60, 19, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(61, 20, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(62, 20, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(63, 20, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(64, 20, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(65, 21, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(66, 21, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(67, 21, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(68, 21, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(69, 22, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(70, 22, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(71, 22, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(72, 22, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(73, 23, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(74, 23, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(75, 23, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(76, 23, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(77, 24, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(78, 24, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(79, 24, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(80, 24, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(81, 26, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(82, 26, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(83, 26, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(84, 26, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(85, 27, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(86, 27, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(87, 27, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(88, 27, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(89, 29, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(90, 29, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(91, 29, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(92, 29, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(93, 30, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(94, 30, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(95, 30, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(96, 30, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(97, 32, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(98, 32, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(99, 32, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(100, 32, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(101, 33, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(102, 33, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(103, 33, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(104, 33, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(105, 34, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(106, 34, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(107, 34, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(108, 34, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(109, 35, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(110, 35, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(111, 35, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(112, 35, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(113, 37, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(114, 37, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(115, 37, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(116, 37, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(117, 38, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(118, 38, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(119, 38, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(120, 38, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(121, 39, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(122, 39, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(123, 39, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(124, 39, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(125, 40, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(126, 40, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(127, 40, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(128, 40, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(129, 4, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(130, 4, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(131, 4, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'under_repair', '2026-11-16 12:50:58'),
(132, 4, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(133, 9, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(134, 9, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'under_repair', '2026-11-16 12:50:58'),
(135, 9, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(136, 9, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(137, 25, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(138, 25, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'under_repair', '2026-11-16 12:50:58'),
(139, 25, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(140, 25, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(141, 31, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(142, 31, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(143, 31, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(144, 31, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(145, 36, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(146, 36, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(147, 36, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(148, 36, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(149, 15, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(150, 15, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(151, 15, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(152, 15, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(153, 18, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(154, 18, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(155, 18, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(156, 18, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(157, 28, 'cpu', 'Intel Core i5/i7 System Unit', NULL, 'good', '2026-11-16 12:50:58'),
(158, 28, 'monitor', 'Dell 24\" IPS FHD Monitor', NULL, 'good', '2026-11-16 12:50:58'),
(159, 28, 'keyboard', 'Logitech K120 USB Keyboard', NULL, 'good', '2026-11-16 12:50:58'),
(160, 28, 'mouse', 'Logitech B100 Optical Mouse', NULL, 'good', '2026-11-16 12:50:58'),
(256, 1, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(257, 2, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(258, 3, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(259, 4, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(260, 5, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(261, 6, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(262, 7, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(263, 8, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(264, 9, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(265, 10, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(266, 11, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(267, 12, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(268, 13, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(269, 14, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(270, 15, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(271, 16, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(272, 17, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(273, 18, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(274, 19, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(275, 20, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(276, 21, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(277, 22, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(278, 23, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(279, 24, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(280, 25, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(281, 26, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(282, 27, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(283, 28, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(284, 29, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(285, 30, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(286, 31, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(287, 32, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(288, 33, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(289, 34, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(290, 35, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(291, 36, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(292, 37, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(293, 38, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(294, 39, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58'),
(295, 40, 'speaker', 'Realtek HD Audio Output', NULL, 'good', '2026-11-16 12:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `fault_reports`
--

CREATE TABLE `fault_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `component` enum('cpu','monitor','keyboard','mouse','speaker','network','software','other') NOT NULL,
  `issue_title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('pending','under_review','in_progress','resolved','rejected') NOT NULL DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `admin_remarks` varchar(255) DEFAULT NULL,
  `report_image` varchar(255) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fault_reports`
--

INSERT INTO `fault_reports` (`id`, `user_id`, `computer_id`, `component`, `issue_title`, `description`, `priority`, `status`, `assigned_to`, `admin_remarks`, `report_image`, `reported_at`, `updated_at`, `resolved_at`) VALUES
(1, 7, 14, 'keyboard', 'Keyboard chalna xodyo ani keys thichidaina', 'Lab ma programming practical kam garda gardai khoi k problem vayexa keyboard chalna xodyo, spacebar ra enter key thichdai thichidaina, practical garnai garho vayo.', 'medium', 'in_progress', 2, NULL, 'uploads/reports/fault_keyboard_keys.jpg', '2026-11-14 05:00:00', '2026-11-16 12:50:58', NULL),
(2, 6, 7, 'monitor', 'Monitor jhim-jhim garera screen black hunchha', 'Database lab ma SQL query lekhda lekhdai monitor screen achanak jhim-jhim garchha ani black screen vaihalchha. Power cable halka hilayo vane ekchhin aaunchha feri bilkul janchha.', 'high', 'pending', NULL, NULL, 'uploads/reports/fault_monitor_flicker.jpg', '2026-11-14 03:35:00', '2026-11-16 12:50:58', NULL),
(3, 8, 21, 'mouse', 'Mouse cursor hudda herdai adkine ra click nahune', 'Mouse ko optical sensor le ramrari kaam gareko xaina, cursor ekkai thau ma adkinchha ra left click 2-3 choti jabarjasti thichnu parxha, kaam garna asajilo vayo.', 'low', 'under_review', 2, NULL, 'uploads/reports/fault_mouse_sensor.jpg', '2026-11-13 08:30:00', '2026-11-16 12:50:58', NULL),
(4, 9, 3, 'cpu', 'CPU power button thichda pani on hudai vayena', 'Aaja bihana class ma aauda power button thichyo tara CPU bata kunai response aayena, fan pani ghumnna ani picheko green light pani balena, power supply bigreko jasto chha.', 'high', 'resolved', 3, NULL, 'uploads/reports/fault_cpu_power.jpg', '2026-11-12 05:45:00', '2026-11-16 12:50:58', '2026-11-14 18:35:26'),
(5, 10, 18, 'network', 'Ethernet cable clip bhachiyera internet chhutiranchha', 'Network cable ko plastic clip bhachiyeko rahechha, table halka matra hallida pani internet disconnect vaihalchha ani lab project submit garna garho vayo.', 'medium', 'in_progress', 2, NULL, 'uploads/reports/fault_network_cable.jpg', '2026-11-15 02:45:00', '2026-11-16 12:50:58', NULL),
(6, 11, 12, 'speaker', 'Front audio port vitra headphone pin bhachiyera adkiyo', 'Headphone ko 3.5mm jack pin agadi ko port vitra nai bhachiyera thuniyo, nikalna pani milena ani sound pani suninna, port safa garidinus.', 'low', 'under_review', 2, NULL, 'uploads/reports/fault_audio_jack.jpg', '2026-11-15 05:15:00', '2026-11-16 12:50:58', NULL),
(7, 12, 4, 'software', 'Kam garda gardai Blue Screen (BSOD) aayera restart vayo', 'Lab evaluation ko lagi C++ program run garna lagda achanak blue screen (MEMORY_MANAGEMENT) error aayo ani PC restart vayo, gari rakheko adha project file save nai vayana.', 'high', 'pending', NULL, NULL, 'uploads/reports/fault_bsod_screen.jpg', '2026-11-16 03:30:00', '2026-11-16 12:50:58', NULL),
(8, 13, 9, 'other', 'Power strip ma spark aayera switch board tatyo', 'Computer chalai rakheko bela switch strip ma chat-chat spark aayo ani plastic poleko jasto gandha aayo, short circuit huna sakchha turunta heridinus.', 'high', 'in_progress', 4, NULL, 'uploads/reports/fault_power_strip.jpg', '2026-11-16 06:00:00', '2026-11-16 12:50:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(100) NOT NULL,
  `lab_code` varchar(30) NOT NULL,
  `room_number` varchar(50) NOT NULL,
  `total_computers` int(11) NOT NULL DEFAULT 0,
  `location` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `opening_hours` varchar(100) NOT NULL DEFAULT 'Sunday - Friday, 7:00 AM - 5:00 PM',
  `status` enum('active','maintenance','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`id`, `lab_name`, `lab_code`, `room_number`, `total_computers`, `location`, `description`, `opening_hours`, `status`, `created_at`) VALUES
(1, 'Computer Lab - 1', 'LAB-01', 'Room 101', 20, 'Block A, Ground Floor', 'Main computer laboratory for programming, web tech, and database practical sessions.', 'Sunday - Friday, 7:00 AM - 5:00 PM', 'active', '2026-11-16 12:50:25'),
(2, 'Computer Lab - 2', 'LAB-02', 'Room 201', 20, 'Block B, First Floor', 'Secondary advanced computer laboratory for BCA project evaluations and software labs.', 'Sunday - Friday, 7:00 AM - 5:00 PM', 'active', '2026-11-16 12:50:25');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_records`
--

CREATE TABLE `maintenance_records` (
  `id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `fault_report_id` int(11) DEFAULT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `component` varchar(100) NOT NULL,
  `maintenance_type` enum('repair','replacement','inspection','scheduled') NOT NULL DEFAULT 'repair',
  `problem_description` text NOT NULL,
  `repair_description` text DEFAULT NULL,
  `repair_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maintenance_date` date NOT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `maintenance_records`
--

INSERT INTO `maintenance_records` (`id`, `computer_id`, `fault_report_id`, `technician_id`, `component`, `maintenance_type`, `problem_description`, `repair_description`, `repair_cost`, `maintenance_date`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 3, 'CPU / Power Supply', 'replacement', 'Power Supply replaced and faulty keyboard swapped with new Logitech unit.', 'Keyboard Replaced', 1200.00, '2026-11-14', 'completed', NULL, '2026-11-16 12:50:26', '2026-11-16 12:50:58'),
(2, 6, NULL, 4, 'CPU / Chassis', 'scheduled', 'Routine seasonal dust cleaning and heatsink thermal paste re-application.', 'Dust Cleaning', 0.00, '2026-11-10', 'completed', NULL, '2026-11-16 12:50:26', '2026-11-16 12:50:58'),
(3, 10, NULL, 3, 'Monitor', 'repair', 'Capacitor replacement on internal power board and display color calibration.', 'Monitor Repair', 2500.00, '2026-11-05', 'completed', NULL, '2026-11-16 12:50:26', '2026-11-16 12:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `type` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `lab_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, NULL, 1, 'Lab 1: 2 computers are under maintenance.', 'danger', 0, '2026-11-16 04:35:00'),
(2, NULL, NULL, 'Lab hours extended: All computer labs remain open until 6:00 PM during project evaluation week.', 'info', 0, '2026-11-15 08:05:58'),
(3, NULL, 2, 'Lab 2 will be closed tomorrow for scheduled maintenance.', 'warning', 0, '2026-11-13 08:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `report_status_history`
--

CREATE TABLE `report_status_history` (
  `id` int(11) NOT NULL,
  `fault_report_id` int(11) NOT NULL,
  `status` enum('pending','under_review','in_progress','resolved','rejected') NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_status_history`
--

INSERT INTO `report_status_history` (`id`, `fault_report_id`, `status`, `changed_by`, `note`, `created_at`) VALUES
(1, 1, 'pending', 7, 'Report submitted by Babin Dangi', '2026-11-14 12:50:26'),
(2, 1, 'under_review', 2, 'Lab Assistant le keyboard inspect gardai', '2026-11-14 15:50:26'),
(3, 1, 'in_progress', 2, 'Naya keyboard replacement ko lagi process suru vayo', '2026-11-15 12:50:26'),
(4, 2, 'pending', 6, 'Report submitted by Anjana K.C.', '2026-11-15 12:50:26'),
(5, 3, 'pending', 8, 'Report submitted by Barun Chaudhary', '2026-11-13 12:50:26'),
(6, 3, 'under_review', 2, 'Mouse sensor check gariyo', '2026-11-14 12:50:26'),
(11, 4, 'pending', NULL, 'Student le power supply on nahune samasya pathayo', '2026-11-12 05:45:00'),
(12, 4, 'in_progress', NULL, 'Technician Ramesh KC le SMPS power rail check gardai', '2026-11-12 08:15:00'),
(13, 4, 'resolved', NULL, 'Naya power supply unit halera check gariyo, sabai thik chha', '2026-11-14 10:45:00'),
(14, 7, 'pending', NULL, 'Student le Blue Screen samasya report gareko', '2026-11-16 03:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `program` varchar(50) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `role` enum('student','lab_assistant','admin') NOT NULL DEFAULT 'student',
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `roll_number`, `password`, `program`, `semester`, `role`, `profile_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Campus Admin', 'admin@gmail.com', NULL, '$2y$10$uh0BKg.SslSHjHCNKBUIw.JynPnXm4oRAXsFwmGSHHtt8Jwf7DwgW', NULL, NULL, 'admin', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(2, 'Bikash Adhikari', 'lab@gmail.com', NULL, '$2y$10$mXbyPNGJZzcPxYT8boCujOlgTJFUUJlz6WPUnVEJVLXA64n6XJOtm', NULL, NULL, 'lab_assistant', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(3, 'Ramesh KC', 'ramesh.kc@gmail.com', NULL, '$2y$10$22JtXcbGZ1/IyK0s.4LTXux.K4kgk8BPJzu0y/7F6JgTp9VjfbM1y', NULL, NULL, 'lab_assistant', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(4, 'Sujan Bhandari', 'sujan.bhandari@gmail.com', NULL, '$2y$10$PpiNrhr0Ms93FhzR4Emr0OPRgmfGXNb9NAh15dmT/Yt/2fWoM3d2.', NULL, NULL, 'lab_assistant', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(5, 'Suresh Pokhrel', 'user@gmail.com', '5402021', '$2y$10$6D9H0BzyDtvJB4Wbofs2y.qc6PzaoXCZbQJxzvtxXCNZA3eznta5W', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(6, 'Anjana K.C.', 'anjana.kc@gmail.com', '5402022', '$2y$10$2mFeCx8DoFBkLWT514CW/e6T3XE21ddN0xLjGNoYYeFvQJSZ8/4JW', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(7, 'Babin Dangi', 'babin.dangi@gmail.com', '5402023', '$2y$10$8YC.OlpqLUkv3VpO8Z7ByOViWBdkWVKOYx9heCb9EyH9ptNFn9106', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(8, 'Barun Chaudhary', 'barun.chaudhary@gmail.com', '5402024', '$2y$10$wRwK7MYS.bMNQ3gngMByA.35mcpnCN7FhDLTWXAoGLmXmTsO.8ztC', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(9, 'Dipendra Oli', 'dipendra.oli@gmail.com', '5402025', '$2y$10$A0gI2c.k.tzzQnl0tT.ufuojTgdb3iRXs6ouwTM4LkaGOM1X5nVBy', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(10, 'Grish Rana', 'grish.rana@gmail.com', '5402026', '$2y$10$b5/u85ipQ9fgv8y.pBUAbu3oK90oSW/23DOzoEqKkGIY.VJxTpSSi', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(11, 'Krishna Gorathoki', 'krishna.gorathoki@gmail.com', '5402027', '$2y$10$7wwiLSNqpCVmtRk/prYAA.3fg3Uq2UAxu7nKhyrsPWnflYFreyUly', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:56'),
(12, 'Kshitiz Thapa', 'kshitiz.thapa@gmail.com', '5402028', '$2y$10$DSMysdzotw650AKBIrkhguhR2wBp650RG.4/BxjEH.FfcjH0VuhPi', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(13, 'Prajwol Chaudhary', 'prajwol.chaudhary@gmail.com', '5402030', '$2y$10$DRBTI4gkLD3s4TPg2k3bjOXiw7tp82zaNcTfH5p2PwDbWpJUeXtYS', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(14, 'Rekha Rijal', 'rekha.rijal@gmail.com', '5402031', '$2y$10$gsP/b5gEAXGcMc7whwpcJOPjQsTLlrHEXIfXfc/v5IHyEDfA72YxO', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(15, 'Samana Oli', 'samana.oli@gmail.com', '5402032', '$2y$10$mCDDyrmC8zdIjKW81a3fReXKkPuwj97ZsbYUvt5XsyOkX19YD6si2', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:57'),
(16, 'Yashu Dangi', 'yashu.dangi@gmail.com', '5402033', '$2y$10$PnnffMz5lzy46K1MgInipem7nO.VpaKv9/GYbGECYRMDIWK8luXgq', 'BCA', '4th Semester', 'student', NULL, 'active', '2026-11-16 12:50:25', '2026-11-16 12:50:57');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_fault_statistics`
-- (See below for the actual view)
--
CREATE TABLE `v_fault_statistics` (
`lab_id` int(11)
,`lab_name` varchar(100)
,`total_faults` bigint(21)
,`pending_faults` decimal(23,0)
,`in_progress_faults` decimal(23,0)
,`resolved_faults` decimal(23,0)
,`high_priority_faults` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_lab_availability`
-- (See below for the actual view)
--
CREATE TABLE `v_lab_availability` (
`lab_id` int(11)
,`lab_name` varchar(100)
,`location` varchar(150)
,`total_computers` bigint(21)
,`available_computers` decimal(23,0)
,`maintenance_computers` decimal(23,0)
,`out_of_service_computers` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_maintenance_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_maintenance_summary` (
`id` int(11)
,`computer_id` int(11)
,`computer_code` varchar(30)
,`lab_name` varchar(100)
,`component` varchar(100)
,`maintenance_type` enum('repair','replacement','inspection','scheduled')
,`problem_description` text
,`repair_description` text
,`repair_cost` decimal(10,2)
,`maintenance_date` date
,`status` enum('pending','in_progress','completed')
,`technician_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_report_details`
-- (See below for the actual view)
--
CREATE TABLE `v_report_details` (
`id` int(11)
,`user_id` int(11)
,`student_name` varchar(100)
,`roll_number` varchar(50)
,`computer_code` varchar(30)
,`lab_name` varchar(100)
,`component` enum('cpu','monitor','keyboard','mouse','speaker','network','software','other')
,`issue_title` varchar(150)
,`description` text
,`priority` enum('low','medium','high')
,`status` enum('pending','under_review','in_progress','resolved','rejected')
,`admin_remarks` varchar(255)
,`report_image` varchar(255)
,`reported_at` timestamp
,`updated_at` timestamp
,`resolved_at` datetime
,`assigned_name` varchar(100)
,`repair_description` text
,`repair_cost` decimal(10,2)
,`maintenance_date` date
);

-- --------------------------------------------------------

--
-- Structure for view `v_fault_statistics`
--
DROP TABLE IF EXISTS `v_fault_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_fault_statistics`  AS SELECT `l`.`id` AS `lab_id`, `l`.`lab_name` AS `lab_name`, count(`r`.`id`) AS `total_faults`, coalesce(sum(`r`.`status` = 'pending'),0) AS `pending_faults`, coalesce(sum(`r`.`status` = 'in_progress'),0) AS `in_progress_faults`, coalesce(sum(`r`.`status` = 'resolved'),0) AS `resolved_faults`, coalesce(sum(`r`.`priority` = 'high'),0) AS `high_priority_faults` FROM ((`labs` `l` left join `computers` `c` on(`c`.`lab_id` = `l`.`id`)) left join `fault_reports` `r` on(`r`.`computer_id` = `c`.`id`)) GROUP BY `l`.`id`, `l`.`lab_name` ;

-- --------------------------------------------------------

--
-- Structure for view `v_lab_availability`
--
DROP TABLE IF EXISTS `v_lab_availability`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_lab_availability`  AS SELECT `l`.`id` AS `lab_id`, `l`.`lab_name` AS `lab_name`, `l`.`location` AS `location`, count(`c`.`id`) AS `total_computers`, coalesce(sum(`c`.`status` = 'available'),0) AS `available_computers`, coalesce(sum(`c`.`status` = 'under_maintenance'),0) AS `maintenance_computers`, coalesce(sum(`c`.`status` = 'out_of_service'),0) AS `out_of_service_computers` FROM (`labs` `l` left join `computers` `c` on(`c`.`lab_id` = `l`.`id`)) WHERE `l`.`status` = 'active' GROUP BY `l`.`id`, `l`.`lab_name`, `l`.`location` ;

-- --------------------------------------------------------

--
-- Structure for view `v_maintenance_summary`
--
DROP TABLE IF EXISTS `v_maintenance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_maintenance_summary`  AS SELECT `m`.`id` AS `id`, `m`.`computer_id` AS `computer_id`, `c`.`computer_code` AS `computer_code`, `l`.`lab_name` AS `lab_name`, `m`.`component` AS `component`, `m`.`maintenance_type` AS `maintenance_type`, `m`.`problem_description` AS `problem_description`, `m`.`repair_description` AS `repair_description`, `m`.`repair_cost` AS `repair_cost`, `m`.`maintenance_date` AS `maintenance_date`, `m`.`status` AS `status`, `t`.`name` AS `technician_name` FROM (((`maintenance_records` `m` join `computers` `c` on(`c`.`id` = `m`.`computer_id`)) join `labs` `l` on(`l`.`id` = `c`.`lab_id`)) left join `users` `t` on(`t`.`id` = `m`.`technician_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_report_details`
--
DROP TABLE IF EXISTS `v_report_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_report_details`  AS SELECT `r`.`id` AS `id`, `r`.`user_id` AS `user_id`, `u`.`name` AS `student_name`, `u`.`roll_number` AS `roll_number`, `c`.`computer_code` AS `computer_code`, `l`.`lab_name` AS `lab_name`, `r`.`component` AS `component`, `r`.`issue_title` AS `issue_title`, `r`.`description` AS `description`, `r`.`priority` AS `priority`, `r`.`status` AS `status`, `r`.`admin_remarks` AS `admin_remarks`, `r`.`report_image` AS `report_image`, `r`.`reported_at` AS `reported_at`, `r`.`updated_at` AS `updated_at`, `r`.`resolved_at` AS `resolved_at`, `t`.`name` AS `assigned_name`, `m`.`repair_description` AS `repair_description`, `m`.`repair_cost` AS `repair_cost`, `m`.`maintenance_date` AS `maintenance_date` FROM (((((`fault_reports` `r` join `users` `u` on(`u`.`id` = `r`.`user_id`)) join `computers` `c` on(`c`.`id` = `r`.`computer_id`)) join `labs` `l` on(`l`.`id` = `c`.`lab_id`)) left join `users` `t` on(`t`.`id` = `r`.`assigned_to`)) left join `maintenance_records` `m` on(`m`.`id` = (select max(`mr`.`id`) from `maintenance_records` `mr` where `mr`.`fault_report_id` = `r`.`id`))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `computer_code` (`computer_code`),
  ADD KEY `fk_computers_lab` (`lab_id`),
  ADD KEY `idx_computers_status` (`status`);

--
-- Indexes for table `computer_components`
--
ALTER TABLE `computer_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_computer_component` (`computer_id`,`component_type`);

--
-- Indexes for table `fault_reports`
--
ALTER TABLE `fault_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reports_user` (`user_id`),
  ADD KEY `fk_reports_computer` (`computer_id`),
  ADD KEY `fk_reports_assigned` (`assigned_to`),
  ADD KEY `idx_reports_status` (`status`),
  ADD KEY `idx_reports_reported_at` (`reported_at`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_code` (`lab_code`);

--
-- Indexes for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_maintenance_computer` (`computer_id`),
  ADD KEY `fk_maintenance_report` (`fault_report_id`),
  ADD KEY `fk_maintenance_technician` (`technician_id`),
  ADD KEY `idx_maintenance_date` (`maintenance_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_lab` (`lab_id`),
  ADD KEY `idx_notifications_date` (`created_at`);

--
-- Indexes for table `report_status_history`
--
ALTER TABLE `report_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_report` (`fault_report_id`),
  ADD KEY `fk_history_user` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `roll_number` (`roll_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `computer_components`
--
ALTER TABLE `computer_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=296;

--
-- AUTO_INCREMENT for table `fault_reports`
--
ALTER TABLE `fault_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report_status_history`
--
ALTER TABLE `report_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `computers`
--
ALTER TABLE `computers`
  ADD CONSTRAINT `fk_computers_lab` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `computer_components`
--
ALTER TABLE `computer_components`
  ADD CONSTRAINT `fk_components_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fault_reports`
--
ALTER TABLE `fault_reports`
  ADD CONSTRAINT `fk_reports_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reports_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `maintenance_records`
--
ALTER TABLE `maintenance_records`
  ADD CONSTRAINT `fk_maintenance_computer` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_report` FOREIGN KEY (`fault_report_id`) REFERENCES `fault_reports` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_maintenance_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_lab` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report_status_history`
--
ALTER TABLE `report_status_history`
  ADD CONSTRAINT `fk_history_report` FOREIGN KEY (`fault_report_id`) REFERENCES `fault_reports` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
