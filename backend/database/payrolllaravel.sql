-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Jan 28, 2026 at 09:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payrolllaravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','hr','manager','employee') NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `employee_id`, `email`, `email_verified_at`, `password`, `password_reset_token`, `password_reset_expires_at`, `role`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
('04bb7b33-f36f-4170-8359-2ed85efd5afe', '8629cc87-6bf4-4a75-8ebf-5ad5912e7c15', 'junonishi1@gmail.com', NULL, '$2y$12$jg1YLOFlL4ukcWBfAlxk.eAy3GZoP5H/9jgDvZ5q5V/OEB0m79172', NULL, NULL, 'employee', 1, NULL, NULL, '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('14b25b10-56f0-4b11-baa2-54b810363558', NULL, 'admin@eternalbright.com', NULL, '$2y$12$irqIDReTq5kOfESdAeE4muIveU8E7dQUSQ3nTVOdM.fwk9ZkqNtQ6', NULL, NULL, 'admin', 1, NULL, NULL, '2026-01-28 08:17:20', '2026-01-28 08:17:20'),
('1ca073db-a744-48bd-8e01-b90fba2272a4', '639e8d9b-3adc-445a-b45a-f4d40d465b89', 'outdoor@gmail.com', NULL, '$2y$12$q6/3OukiqLs4QvgCAkQ2oeEX8G6PNrPW8vVaNeexBxGeucgEvija2', NULL, NULL, 'employee', 1, NULL, NULL, '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('1d30728c-1a1d-45dc-8fb5-f43d70823d35', 'b302d3d6-13d2-400c-a31d-3b598a62e18d', 'jersondev03@gmail.com', NULL, '$2y$12$5D9CvhTa8i/W2ojttsxJTetc1R68UGJ4/EaD0IDn3Vxt2bkX3Zh3i', NULL, NULL, 'hr', 1, '2026-01-28 08:17:39', NULL, '2026-01-28 08:17:18', '2026-01-28 08:17:39'),
('5f7bcf70-3db3-4a5b-ae0b-f1b87895a3fd', 'a5463392-8382-4244-9b94-ca2d91f34abc', 'thomas@gmail.com', NULL, '$2y$12$RcuIVNRTU1letE3RUUjSBeT23MBLRKwG4wYP0RNj0E3JD.vxtpQUG', NULL, NULL, 'employee', 1, NULL, NULL, '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('6aa2546b-5961-4bf8-a509-f3d6144b2b29', '5a03e841-dd71-4ff8-ae60-535936b7ff3c', 'hasong500@gmail.com', NULL, '$2y$12$RF7WCfpv08bwjjvMrohNUeomiapJQyEBoxBnN35c2.aGMIBlQIpNW', NULL, NULL, 'employee', 1, NULL, NULL, '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('c69e87d6-60b1-42d1-ba2b-e0ff708db0eb', '393914c7-5696-4941-95c0-d021cfa1bc34', 'miguelonishi1@gmail.com', NULL, '$2y$12$rVRKu6DtZQkA4Ax/ZkZTyOBL9gmprMEoP9w3q3Vns8/wtKNDLSgSq', NULL, NULL, 'employee', 1, NULL, NULL, '2026-01-28 08:17:18', '2026-01-28 08:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_exceptions`
--

CREATE TABLE `attendance_exceptions` (
  `id` char(36) NOT NULL,
  `date` date NOT NULL,
  `type` enum('holiday','company_event','emergency_closure','special_workday') NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `is_working_day` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` char(36) NOT NULL,
  `attendance_record_id` char(36) NOT NULL,
  `action` enum('created','updated','deleted','approved','rejected','time_in','time_out','break_start','break_end') NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `performed_by` char(36) NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `time_in` timestamp NULL DEFAULT NULL,
  `time_out` timestamp NULL DEFAULT NULL,
  `break_start` timestamp NULL DEFAULT NULL,
  `break_end` timestamp NULL DEFAULT NULL,
  `total_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `regular_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` enum('present','absent','late','half_day','on_leave') NOT NULL DEFAULT 'absent',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `id` char(36) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `code`, `description`, `address`, `city`, `state`, `postal_code`, `country`, `phone`, `email`, `website`, `tax_id`, `registration_number`, `is_active`, `created_at`, `updated_at`) VALUES
('c5751b07-35c6-4f06-9442-51b3fe8b0347', 'Eternal Bright', 'EB1769586954', 'Eternal Bright Company', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-01-28 07:55:54', '2026-01-28 07:55:54');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `department_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `budget` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `company_id`, `department_id`, `name`, `description`, `location`, `budget`, `created_at`, `updated_at`) VALUES
('dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'DEPT001', 'Human Resources', 'HR Department', NULL, 1000000.00, '2026-01-28 08:17:17', '2026-01-28 08:17:17');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `employee_id` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `department_id` char(36) NOT NULL,
  `position` varchar(255) NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `hire_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `company_id`, `employee_id`, `first_name`, `last_name`, `phone`, `department_id`, `position`, `salary`, `hire_date`, `created_at`, `updated_at`) VALUES
('393914c7-5696-4941-95c0-d021cfa1bc34', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'EMP002', 'Miguel', 'Onishi', '0962301657', 'dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'Software Developer', 55000.00, '2025-10-28', '2026-01-28 08:17:18', '2026-01-28 08:17:18'),
('5a03e841-dd71-4ff8-ae60-535936b7ff3c', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'EMP004', 'Steve', 'Hasong', '0931786083', 'dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'Marketing Specialist', 48000.00, '2024-09-28', '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('639e8d9b-3adc-445a-b45a-f4d40d465b89', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'EMP005', 'Luke', 'Outdoor', '0990441284', 'dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'Sales Executive', 52000.00, '2025-02-28', '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('8629cc87-6bf4-4a75-8ebf-5ad5912e7c15', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'EMP003', 'Jun', 'Onishi', '0972402852', 'dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'Project Manager', 65000.00, '2024-08-28', '2026-01-28 08:17:18', '2026-01-28 08:17:18'),
('a5463392-8382-4244-9b94-ca2d91f34abc', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'EMP006', 'Thomas', 'Andre', '0997432523', 'dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'Accountant', 58000.00, '2025-04-28', '2026-01-28 08:17:19', '2026-01-28 08:17:19'),
('b302d3d6-13d2-400c-a31d-3b598a62e18d', 'c5751b07-35c6-4f06-9442-51b3fe8b0347', 'EMP001', 'Jerson', 'Developer', '0927677938', 'dd737fb5-6f39-4b97-bea6-0b08f2526c6b', 'HR Manager', 60000.00, '2025-08-28', '2026-01-28 08:17:18', '2026-01-28 08:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `employee_schedules`
--

CREATE TABLE `employee_schedules` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `department_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Working','Day Off','Leave','Holiday','Overtime') NOT NULL DEFAULT 'Working',
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `year` int(11) NOT NULL,
  `vacation_days_total` int(11) NOT NULL DEFAULT 15,
  `vacation_days_used` int(11) NOT NULL DEFAULT 0,
  `sick_days_total` int(11) NOT NULL DEFAULT 10,
  `sick_days_used` int(11) NOT NULL DEFAULT 0,
  `personal_days_total` int(11) NOT NULL DEFAULT 5,
  `personal_days_used` int(11) NOT NULL DEFAULT 0,
  `emergency_days_total` int(11) NOT NULL DEFAULT 3,
  `emergency_days_used` int(11) NOT NULL DEFAULT 0,
  `maternity_days_total` int(11) NOT NULL DEFAULT 0,
  `maternity_days_used` int(11) NOT NULL DEFAULT 0,
  `paternity_days_total` int(11) NOT NULL DEFAULT 0,
  `paternity_days_used` int(11) NOT NULL DEFAULT 0,
  `bereavement_days_total` int(11) NOT NULL DEFAULT 0,
  `bereavement_days_used` int(11) NOT NULL DEFAULT 0,
  `study_days_total` int(11) NOT NULL DEFAULT 0,
  `study_days_used` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `leave_type` enum('vacation','sick','personal','emergency','maternity','paternity','bereavement','study') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` char(36) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` char(36) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`id`, `account_id`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, '1d30728c-1a1d-45dc-8fb5-f43d70823d35', '192.168.1.132', 'Mozilla/5.0 (Linux; Chrome)', '2026-01-23 08:17:18', '2026-01-23 08:17:18'),
(2, '1d30728c-1a1d-45dc-8fb5-f43d70823d35', '192.168.1.118', 'Mozilla/5.0 (Macintosh; Safari)', '2026-01-25 04:17:18', '2026-01-25 04:17:18'),
(3, 'c69e87d6-60b1-42d1-ba2b-e0ff708db0eb', '192.168.1.190', 'Mozilla/5.0 (Windows; Firefox)', '2026-01-27 21:17:18', '2026-01-27 21:17:18'),
(4, 'c69e87d6-60b1-42d1-ba2b-e0ff708db0eb', '192.168.1.144', 'Mozilla/5.0 (Windows; Chrome)', '2026-01-21 09:17:18', '2026-01-21 09:17:18'),
(5, 'c69e87d6-60b1-42d1-ba2b-e0ff708db0eb', '192.168.1.125', 'Mozilla/5.0 (Macintosh; Firefox)', '2026-01-26 01:17:18', '2026-01-26 01:17:18'),
(6, '04bb7b33-f36f-4170-8359-2ed85efd5afe', '192.168.1.167', 'Mozilla/5.0 (Windows; Chrome)', '2026-01-27 23:17:19', '2026-01-27 23:17:19'),
(7, '04bb7b33-f36f-4170-8359-2ed85efd5afe', '192.168.1.192', 'Mozilla/5.0 (Windows; Safari)', '2026-01-23 08:17:19', '2026-01-23 08:17:19'),
(8, '04bb7b33-f36f-4170-8359-2ed85efd5afe', '192.168.1.142', 'Mozilla/5.0 (Windows; Chrome)', '2026-01-26 19:17:19', '2026-01-26 19:17:19'),
(9, '6aa2546b-5961-4bf8-a509-f3d6144b2b29', '192.168.1.120', 'Mozilla/5.0 (Windows; Firefox)', '2026-01-22 10:17:19', '2026-01-22 10:17:19'),
(10, '6aa2546b-5961-4bf8-a509-f3d6144b2b29', '192.168.1.191', 'Mozilla/5.0 (Linux; Safari)', '2026-01-24 09:17:19', '2026-01-24 09:17:19'),
(11, '1ca073db-a744-48bd-8e01-b90fba2272a4', '192.168.1.166', 'Mozilla/5.0 (Macintosh; Safari)', '2026-01-26 21:17:19', '2026-01-26 21:17:19'),
(12, '1ca073db-a744-48bd-8e01-b90fba2272a4', '192.168.1.187', 'Mozilla/5.0 (Linux; Firefox)', '2026-01-25 18:17:19', '2026-01-25 18:17:19'),
(13, '1ca073db-a744-48bd-8e01-b90fba2272a4', '192.168.1.184', 'Mozilla/5.0 (Linux; Safari)', '2026-01-25 10:17:19', '2026-01-25 10:17:19'),
(14, '1ca073db-a744-48bd-8e01-b90fba2272a4', '192.168.1.143', 'Mozilla/5.0 (Macintosh; Firefox)', '2026-01-22 20:17:19', '2026-01-22 20:17:19'),
(15, '5f7bcf70-3db3-4a5b-ae0b-f1b87895a3fd', '192.168.1.148', 'Mozilla/5.0 (Macintosh; Firefox)', '2026-01-24 03:17:19', '2026-01-24 03:17:19'),
(16, '5f7bcf70-3db3-4a5b-ae0b-f1b87895a3fd', '192.168.1.112', 'Mozilla/5.0 (Windows; Safari)', '2026-01-22 17:17:19', '2026-01-22 17:17:19'),
(17, '5f7bcf70-3db3-4a5b-ae0b-f1b87895a3fd', '192.168.1.109', 'Mozilla/5.0 (Windows; Chrome)', '2026-01-25 09:17:19', '2026-01-25 09:17:19'),
(18, '1d30728c-1a1d-45dc-8fb5-f43d70823d35', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-28 08:17:39', '2026-01-28 08:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(40, '2025_09_18_145540_create_departments_table', 1),
(41, '2025_09_18_145544_create_employees_table', 1),
(42, '2025_09_18_145548_create_payrolls_table', 1),
(43, '2025_09_18_145721_create_accounts_table', 1),
(44, '2025_09_18_150533_create_sessions_table', 1),
(45, '2025_09_18_150601_create_cache_table', 1),
(46, '2025_09_18_150612_create_jobs_table', 1),
(47, '2025_09_18_150624_create_failed_jobs_table', 1),
(48, '2025_09_19_022316_create_attendance_records_table', 1),
(49, '2025_09_19_022330_create_overtime_requests_table', 1),
(50, '2025_09_19_022330_create_work_schedules_table', 1),
(51, '2025_09_19_022331_create_attendance_settings_table', 1),
(52, '2025_09_19_022331_create_leave_balances_table', 1),
(53, '2025_09_19_022331_create_leave_requests_table', 1),
(54, '2025_09_19_022346_create_attendance_exceptions_table', 1),
(55, '2025_09_19_022346_create_attendance_logs_table', 1),
(56, '2025_09_30_171709_create_employee_schedules_table', 1),
(57, '2025_10_18_014123_update_employee_schedules_holiday_status', 1),
(58, '2025_10_18_040316_add_night_shift_to_attendance_records_table', 1),
(59, '2025_10_19_221115_create_temp_timekeeping_table', 1),
(60, '2025_10_19_224709_create_companies_table', 1),
(61, '2025_10_19_225233_update_companies_table_use_uuid', 1),
(62, '2025_10_20_000939_add_approval_fields_to_payrolls_table', 1),
(63, '2025_10_20_011053_add_holiday_pay_fields_to_payrolls_table', 1),
(64, '2025_10_20_011838_add_special_holiday_premium_to_payrolls_table', 1),
(65, '2025_10_20_020910_add_scheduled_hours_to_payrolls_table', 1),
(66, '2025_10_20_034409_add_holiday_day_counts_to_payrolls_table', 1),
(67, '2025_10_20_074714_create_user_sessions_table', 1),
(68, '2025_10_20_083249_update_user_sessions_table_use_uuid', 1),
(69, '2025_10_24_005407_create_positions_table', 1),
(70, '2025_10_24_021817_create_periods_table', 1),
(71, '2025_10_24_023441_create_tax_brackets_table', 1),
(72, '2025_10_24_134745_update_attendance_records_status_enum', 1),
(73, '2025_10_24_135307_update_employee_schedules_status_enum_add_absent', 1),
(74, '2025_10_27_075937_add_company_id_to_departments_table', 1),
(75, '2025_10_27_075942_add_company_id_to_employees_table', 1),
(76, '2025_10_27_075950_add_company_id_to_positions_table', 1),
(77, '2025_10_27_080510_assign_existing_data_to_eternal_bright_company', 1),
(78, '2025_10_27_095544_add_password_reset_to_accounts_table', 1),
(79, '2025_11_29_010000_create_payments_table', 999),
(80, '2025_11_29_222408_update_payments_table_data_types', 1000),
(81, '2025_12_01_142945_add_payment_method_to_payments_table', 1000);

-- --------------------------------------------------------

--
-- Table structure for table `overtime_requests`
--

CREATE TABLE `overtime_requests` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `date` date NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `hours` decimal(8,2) NOT NULL,
  `rate_multiplier` decimal(3,2) NOT NULL DEFAULT 1.50,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` char(36) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payroll_id` varchar(255) DEFAULT NULL,
  `employee_id` varchar(36) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_by` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `holiday_basic_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `holiday_premium` decimal(10,2) NOT NULL DEFAULT 0.00,
  `special_holiday_premium` decimal(10,2) NOT NULL DEFAULT 0.00,
  `regular_holiday_days` int(11) NOT NULL DEFAULT 0,
  `special_holiday_days` int(11) NOT NULL DEFAULT 0,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `scheduled_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `bonuses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL,
  `net_pay` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','paid','cancelled') NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `department_id` char(36) DEFAULT NULL,
  `employee_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`employee_ids`)),
  `created_by` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `department_id` char(36) NOT NULL,
  `min_salary` decimal(10,2) DEFAULT NULL,
  `max_salary` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `responsibilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`responsibilities`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('d3LJ1maZMyTLYLyYnJxJr7dMgLlGpIvsbLxuZ8dT', '1d30728c-1a1d-45dc-8fb5-f43d70823d35', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiNU12azN4QjhuSWVSQmZvUlZBZUpvY1lrWUdlTFNnYWxVSjg5d1FnTCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZW1wbG95ZWVzIjtzOjU6InJvdXRlIjtzOjE1OiJlbXBsb3llZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7czozNjoiMWQzMDcyOGMtMWExZC00NWRjLThmYjUtZjQzZDcwODIzZDM1IjtzOjE4OiJjdXJyZW50X2NvbXBhbnlfaWQiO3M6MzY6ImM1NzUxYjA3LTM1YzYtNGYwNi05NDQyLTUxYjNmZThiMDM0NyI7fQ==', 1769589023);

-- --------------------------------------------------------

--
-- Table structure for table `tax_brackets`
--

CREATE TABLE `tax_brackets` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `min_income` decimal(15,2) NOT NULL,
  `max_income` decimal(15,2) DEFAULT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `base_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `excess_over` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_timekeeping`
--

CREATE TABLE `temp_timekeeping` (
  `id` char(36) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `time_in` timestamp NULL DEFAULT NULL,
  `time_out` timestamp NULL DEFAULT NULL,
  `break_start` timestamp NULL DEFAULT NULL,
  `break_end` timestamp NULL DEFAULT NULL,
  `total_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `regular_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `status` enum('present','absent','late','half_day','on_leave','day_off','holiday','error') NOT NULL DEFAULT 'absent',
  `schedule_status` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `validation_errors` text DEFAULT NULL,
  `import_batch_id` varchar(255) DEFAULT NULL,
  `is_processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` char(36) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text NOT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `last_activity` timestamp NULL DEFAULT NULL,
  `login_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_id`, `ip_address`, `user_agent`, `device_type`, `browser`, `os`, `location`, `is_current`, `last_activity`, `login_at`, `expires_at`, `created_at`, `updated_at`) VALUES
('f4a96d1e-377c-4e1e-91f6-f33ab32c973e', '1d30728c-1a1d-45dc-8fb5-f43d70823d35', 'd3LJ1maZMyTLYLyYnJxJr7dMgLlGpIvsbLxuZ8dT', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', 'desktop', 'Chrome', 'Windows', 'Local Development', 1, '2026-01-28 08:30:23', NULL, '2026-01-28 10:30:23', '2026-01-28 08:17:54', '2026-01-28 08:30:23');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `schedule_name` varchar(255) NOT NULL,
  `monday_start` time DEFAULT NULL,
  `monday_end` time DEFAULT NULL,
  `tuesday_start` time DEFAULT NULL,
  `tuesday_end` time DEFAULT NULL,
  `wednesday_start` time DEFAULT NULL,
  `wednesday_end` time DEFAULT NULL,
  `thursday_start` time DEFAULT NULL,
  `thursday_end` time DEFAULT NULL,
  `friday_start` time DEFAULT NULL,
  `friday_end` time DEFAULT NULL,
  `saturday_start` time DEFAULT NULL,
  `saturday_end` time DEFAULT NULL,
  `sunday_start` time DEFAULT NULL,
  `sunday_end` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounts_email_unique` (`email`),
  ADD KEY `accounts_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `attendance_exceptions`
--
ALTER TABLE `attendance_exceptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_exceptions_date_type_unique` (`date`,`type`),
  ADD KEY `attendance_exceptions_date_index` (`date`),
  ADD KEY `attendance_exceptions_type_index` (`type`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_logs_attendance_record_id_action_index` (`attendance_record_id`,`action`),
  ADD KEY `attendance_logs_performed_by_performed_at_index` (`performed_by`,`performed_at`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_records_employee_id_date_unique` (`employee_id`,`date`),
  ADD KEY `attendance_records_date_status_index` (`date`,`status`);

--
-- Indexes for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_settings_setting_key_unique` (`setting_key`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `companies_code_unique` (`code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_department_id_unique` (`department_id`),
  ADD KEY `departments_company_id_index` (`company_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_employee_id_unique` (`employee_id`),
  ADD KEY `employees_department_id_foreign` (`department_id`),
  ADD KEY `employees_company_id_index` (`company_id`);

--
-- Indexes for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_schedules_employee_id_date_unique` (`employee_id`,`date`),
  ADD KEY `employee_schedules_created_by_foreign` (`created_by`),
  ADD KEY `employee_schedules_employee_id_date_index` (`employee_id`,`date`),
  ADD KEY `employee_schedules_department_id_date_index` (`department_id`,`date`),
  ADD KEY `employee_schedules_date_index` (`date`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_balances_employee_id_year_unique` (`employee_id`,`year`),
  ADD KEY `leave_balances_year_index` (`year`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `leave_requests_employee_id_status_index` (`employee_id`,`status`),
  ADD KEY `leave_requests_start_date_end_date_index` (`start_date`,`end_date`),
  ADD KEY `leave_requests_leave_type_status_index` (`leave_type`,`status`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_logs_account_id_foreign` (`account_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `overtime_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `overtime_requests_employee_id_date_index` (`employee_id`,`date`),
  ADD KEY `overtime_requests_status_date_index` (`status`,`date`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`),
  ADD KEY `payrolls_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `periods_department_id_foreign` (`department_id`),
  ADD KEY `periods_start_date_end_date_index` (`start_date`,`end_date`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `positions_name_unique` (`name`),
  ADD UNIQUE KEY `positions_code_unique` (`code`),
  ADD KEY `positions_department_id_foreign` (`department_id`),
  ADD KEY `positions_is_active_department_id_index` (`is_active`,`department_id`),
  ADD KEY `positions_level_index` (`level`),
  ADD KEY `positions_company_id_index` (`company_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tax_brackets`
--
ALTER TABLE `tax_brackets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_brackets_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `tax_brackets_min_income_max_income_index` (`min_income`,`max_income`);

--
-- Indexes for table `temp_timekeeping`
--
ALTER TABLE `temp_timekeeping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temp_timekeeping_employee_id_date_index` (`employee_id`,`date`),
  ADD KEY `temp_timekeeping_import_batch_id_index` (`import_batch_id`),
  ADD KEY `temp_timekeeping_is_processed_index` (`is_processed`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_sessions_session_id_unique` (`session_id`),
  ADD KEY `user_sessions_user_id_is_current_index` (`user_id`,`is_current`),
  ADD KEY `user_sessions_expires_at_index` (`expires_at`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_schedules_employee_id_is_active_index` (`employee_id`,`is_active`),
  ADD KEY `work_schedules_effective_date_end_date_index` (`effective_date`,`end_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_attendance_record_id_foreign` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_logs_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_schedules`
--
ALTER TABLE `employee_schedules`
  ADD CONSTRAINT `employee_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_schedules_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD CONSTRAINT `overtime_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `overtime_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `periods`
--
ALTER TABLE `periods`
  ADD CONSTRAINT `periods_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `positions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD CONSTRAINT `work_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
