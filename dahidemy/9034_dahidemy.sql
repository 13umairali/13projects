-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 26, 2026 at 08:15 AM
-- Server version: 10.11.19-MariaDB-ubu2404
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `9034_dahidemy`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `about_us`
--

INSERT INTO `about_us` (`id`, `institute_id`, `branch_id`, `title`, `description`, `image`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Welcome to School', 'We provide a nurturing environment, a challenging curriculum, and a dedicated faculty to ensure student success.', '2026-09-14-6aa7cf5eb2fd7.png', 1, NULL, '2026-09-14 08:41:34', NULL, NULL),
(2, 6, 6, 'CEO', NULL, '2026-09-18-6aad8d6e43d51.png', 1, NULL, '2026-09-18 17:13:50', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `absent_fines`
--

CREATE TABLE `absent_fines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `period_id` bigint(20) UNSIGNED NOT NULL,
  `fee_amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `academic_images`
--

CREATE TABLE `academic_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) DEFAULT NULL,
  `heading` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_images`
--

INSERT INTO `academic_images` (`id`, `institute_id`, `title`, `heading`, `description`, `image`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 'Science Lab', NULL, 'A Modern Science Lab', '2026-09-18-6aad8fdb5c9e6.png', 1, 7, '2026-09-18 17:24:11', NULL, NULL),
(2, 6, 'Ground', NULL, NULL, '2026-09-18-6aad906f94134.png', 1, 7, '2026-09-18 17:26:39', NULL, NULL),
(3, 6, 'Music', NULL, NULL, '2026-09-18-6aad908195d3a.png', 1, 7, '2026-09-18 17:26:57', NULL, NULL),
(4, 6, 'Clas Room', NULL, NULL, '2026-09-18-6aad90a9503e9.png', 1, 7, '2026-09-18 17:27:37', NULL, NULL),
(5, 6, 'IT Lab', NULL, NULL, '2026-09-18-6aad90c691edc.png', 1, 7, '2026-09-18 17:28:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `session` varchar(50) NOT NULL,
  `year` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `session`, `year`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024', '2024-2025', '2026-08-13 11:53:33', '2026-09-16 10:46:48', '2026-09-16 10:46:48'),
(2, '2025', '2025-2026', '2026-08-13 11:53:33', '2026-09-16 10:30:27', '2026-09-16 10:30:27'),
(3, '2026', '2026-27', '2026-08-13 11:53:33', '2026-09-17 09:50:29', '2026-09-17 09:50:29'),
(4, '2027', '2027-28', '2026-08-13 11:53:33', '2026-09-17 09:50:24', '2026-09-17 09:50:24'),
(5, '2025', '2025-26', '2026-09-17 09:46:45', '2026-09-17 09:50:19', '2026-09-17 09:50:19'),
(6, '2024', '2024-25', '2026-09-17 09:49:43', '2026-09-17 09:49:43', NULL),
(7, '2025', '2025-26', '2026-09-17 09:50:48', '2026-09-17 09:50:48', NULL),
(8, '2026', '2026-27', '2026-09-17 09:51:18', '2026-09-17 09:51:18', NULL),
(9, '2027', '2027-28', '2026-09-17 09:51:36', '2026-09-17 09:51:57', NULL),
(10, '2028', '2028-29', '2026-09-17 09:52:24', '2026-09-17 09:52:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `accounting_categories`
--

CREATE TABLE `accounting_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) DEFAULT NULL,
  `type` varchar(191) DEFAULT NULL,
  `nature` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounting_categories`
--

INSERT INTO `accounting_categories` (`id`, `institute_id`, `branch_id`, `name`, `code`, `type`, `nature`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Cash & Cash Equivalence', NULL, 'Assets', 'debit', NULL, NULL),
(2, 1, 1, 'Current Liabilities', NULL, 'Liabilities', 'credit', NULL, NULL),
(3, 1, 1, 'Non-Current Liabilities', NULL, 'Liabilities', 'credit', NULL, NULL),
(4, 1, 1, 'Owner’s Equity', NULL, 'Liabilities', 'credit', NULL, NULL),
(5, 1, 1, 'Fees Related Income', NULL, 'Income', 'credit', NULL, NULL),
(6, 1, 1, 'Others Income', NULL, 'Income', 'credit', NULL, NULL),
(7, 1, 1, 'General Expenses', NULL, 'Expense', 'debit', NULL, NULL),
(8, 6, 6, 'Admission Fee', '0001', 'income', 'credit', '2026-09-16 13:24:22', '2026-09-16 13:24:22'),
(9, 6, 6, 'Tuition Fee', '0002', 'income', 'credit', '2026-09-16 13:24:43', '2026-09-16 13:24:43'),
(10, 6, 6, 'Transport Fee', '0003', 'income', 'credit', '2026-09-16 13:25:03', '2026-09-16 13:25:03'),
(11, 6, 6, 'Library Fee', '0004', 'income', 'credit', '2026-09-16 13:25:20', '2026-09-16 13:25:20'),
(12, 6, 6, 'Canteen Charges', '0005', 'income', 'credit', '2026-09-16 13:25:39', '2026-09-16 13:25:39'),
(13, 6, 6, 'Security', '0006', 'liability', 'credit', '2026-09-16 13:26:07', '2026-09-16 13:26:07'),
(14, 6, 6, 'Salary', '0100', 'expense', 'debit', '2026-09-16 13:26:43', '2026-09-16 13:26:43'),
(15, 6, 6, 'Transport', '0101', 'expense', 'debit', '2026-09-16 13:27:03', '2026-09-16 13:27:03'),
(16, 6, 6, 'Sports Fee', '0008', 'income', 'credit', '2026-09-16 13:27:28', '2026-09-16 13:27:28'),
(17, 6, 6, 'Promotion Fee', '0007', 'income', 'credit', '2026-09-16 13:28:50', '2026-09-16 13:28:50'),
(18, 6, 6, 'Electricity Charges', '0102', 'expense', 'debit', '2026-09-16 13:31:03', '2026-09-16 13:31:03'),
(19, 6, 6, 'Gas Charges', '0103', 'expense', 'debit', '2026-09-16 13:31:27', '2026-09-16 13:31:27');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_funds`
--

CREATE TABLE `accounting_funds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `serial` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `cash_in` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_out` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounting_funds`
--

INSERT INTO `accounting_funds` (`id`, `institute_id`, `branch_id`, `serial`, `name`, `cash_in`, `cash_out`, `balance`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'General Fund', 102000.00, 0.00, 102000.00, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 6, 6, 1, 'Exam Funds Expenditure', 0.00, 0.00, 0.00, '2026-09-16 13:36:23', '2026-09-16 13:36:23'),
(3, 6, 6, 2, 'Utility Bills', 0.00, 0.00, 0.00, '2026-09-16 13:36:46', '2026-09-16 13:36:46');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_groups`
--

CREATE TABLE `accounting_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `accounting_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounting_groups`
--

INSERT INTO `accounting_groups` (`id`, `institute_id`, `branch_id`, `accounting_category_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Cash', NULL, NULL),
(2, 1, 1, 1, 'Digital Payment', NULL, NULL),
(3, 1, 1, 2, 'Accounts Payable', NULL, NULL),
(4, 1, 1, 3, 'Long Term Loan', NULL, NULL),
(5, 1, 1, 4, 'Opening Balance Equity', NULL, NULL),
(6, 1, 1, 5, 'Income From Fees', NULL, NULL),
(7, 1, 1, 5, 'Income From Fine', NULL, NULL),
(8, 1, 1, 6, 'Rental Income', NULL, NULL),
(9, 1, 1, 6, 'Old Things Sells Income', NULL, NULL),
(10, 1, 1, 6, 'Others Miscellaneous Income', NULL, NULL),
(11, 1, 1, 6, 'Donation', NULL, NULL),
(12, 1, 1, 7, 'Advertising / Promotional', NULL, NULL),
(13, 1, 1, 7, 'Occasional Expenses', NULL, NULL),
(14, 1, 1, 7, 'Bank Charge', NULL, NULL),
(15, 1, 1, 7, 'Others Miscellaneous Expenses', NULL, NULL),
(16, 1, 1, 7, 'Convenience Expenses', NULL, NULL),
(17, 1, 1, 7, 'Utilities Expenses', NULL, NULL),
(18, 1, 1, 7, 'Meals &amp; Entertainment', NULL, NULL),
(19, 1, 1, 7, 'Exam Expenses', NULL, NULL),
(20, 1, 1, 7, 'Rental Expenses', NULL, NULL),
(21, 1, 1, 7, 'Software Charge', NULL, NULL),
(22, 1, 1, 7, 'Stationary Expenses', NULL, NULL),
(23, 1, 1, 7, 'Repair & Maintenance', NULL, NULL),
(24, 1, 1, 7, 'Payroll Expenses', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `accounting_ledgers`
--

CREATE TABLE `accounting_ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `ledger_name` varchar(191) NOT NULL,
  `accounting_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `accounting_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('payment','default') NOT NULL DEFAULT 'default',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounting_ledgers`
--

INSERT INTO `accounting_ledgers` (`id`, `institute_id`, `branch_id`, `ledger_name`, `accounting_category_id`, `accounting_group_id`, `balance`, `type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Cash', 1, 1, 0.00, 'default', NULL, NULL),
(2, 1, 1, 'Digital Payment', 1, 2, 0.00, 'default', NULL, NULL),
(3, 1, 1, 'Accounts Payable', 2, 3, 0.00, 'default', NULL, NULL),
(4, 1, 1, 'Tuition Fee Refund', 2, 3, 0.00, 'default', NULL, NULL),
(5, 1, 1, 'Director`s Loan ', 3, 4, 0.00, 'default', NULL, NULL),
(6, 1, 1, 'Opening Balance Equity', 4, 5, 0.00, 'default', NULL, NULL),
(7, 1, 1, 'Admission Fees Collection', 5, 6, 75000.00, 'default', NULL, NULL),
(8, 1, 1, 'Board Fees Collection', 5, 6, 0.00, 'default', NULL, NULL),
(9, 1, 1, 'Dairy And Syllabuss Collection', 5, 6, 0.00, 'default', NULL, NULL),
(10, 1, 1, 'Exam Fees Collection', 5, 6, 0.00, 'default', NULL, NULL),
(11, 1, 1, 'Fee Collections Collection', 5, 6, 0.00, 'default', NULL, NULL),
(12, 1, 1, 'Session Charge Collection', 5, 6, 0.00, 'default', NULL, NULL),
(13, 1, 1, 'Study Materials Fees Collection', 5, 6, 0.00, 'default', NULL, NULL),
(14, 1, 1, 'TC Fees Collection', 5, 6, 0.00, 'default', NULL, NULL),
(15, 1, 1, 'Testimonial Fees Collection', 5, 6, 0.00, 'default', NULL, NULL),
(16, 1, 1, 'Tie And Id Card Collection', 5, 6, 0.00, 'default', NULL, NULL),
(17, 1, 1, 'Tuition Fees Collection', 5, 6, 27000.00, 'default', NULL, NULL),
(18, 1, 1, 'Attendance Fine', 5, 7, 0.00, 'default', NULL, NULL),
(19, 1, 1, 'Quiz Fine', 5, 7, 0.00, 'default', NULL, NULL),
(20, 1, 1, 'Lab Fine', 5, 7, 0.00, 'default', NULL, NULL),
(21, 1, 1, 'Tuition Fee Fin', 5, 7, 0.00, 'default', NULL, NULL),
(22, 1, 1, 'Late Fine', 5, 7, 0.00, 'default', NULL, NULL),
(23, 1, 1, 'Attendance Make up Fine', 5, 7, 0.00, 'default', NULL, NULL),
(24, 1, 1, 'Laboratories Class Fine', 5, 7, 0.00, 'default', NULL, NULL),
(25, 1, 1, 'ID Card Fine', 5, 7, 0.00, 'default', NULL, NULL),
(26, 1, 1, 'Discipline Fine', 5, 7, 0.00, 'default', NULL, NULL),
(27, 1, 1, 'Library Fine', 5, 7, 0.00, 'default', NULL, NULL),
(28, 1, 1, 'Miscellaneous Fine', 5, 7, 0.00, 'default', NULL, NULL),
(29, 1, 1, 'House Rent(Cr)', 6, 8, 0.00, 'default', NULL, NULL),
(30, 1, 1, 'Old Paper Sells', 6, 9, 0.00, 'default', NULL, NULL),
(31, 1, 1, 'Online Apply Fee', 6, 10, 0.00, 'default', NULL, NULL),
(32, 1, 1, 'Opening Balance', 6, 11, 0.00, 'default', NULL, NULL),
(33, 1, 1, 'Advertisement', 7, 12, 0.00, 'default', NULL, NULL),
(34, 1, 1, 'Anual Sports', 7, 13, 0.00, 'default', NULL, NULL),
(35, 1, 1, 'National Day &amp; Fastival Expanse', 7, 13, 0.00, 'default', NULL, NULL),
(36, 1, 1, 'Ocation', 7, 13, 0.00, 'default', NULL, NULL),
(37, 1, 1, 'Study Tour', 7, 13, 0.00, 'default', NULL, NULL),
(38, 1, 1, 'Bank Charges', 7, 14, 0.00, 'default', NULL, NULL),
(39, 1, 1, 'Board Fee', 7, 15, 0.00, 'default', NULL, NULL),
(40, 1, 1, 'Miscellaneous Exp.', 7, 15, 0.00, 'default', NULL, NULL),
(41, 1, 1, 'Office Expense', 7, 15, 0.00, 'default', NULL, NULL),
(42, 1, 1, 'Conveyance Expenses', 7, 16, 0.00, 'default', NULL, NULL),
(43, 1, 1, 'Electricity Bill', 7, 17, 0.00, 'default', NULL, NULL),
(44, 1, 1, 'Internet Bill', 7, 17, 0.00, 'default', NULL, NULL),
(45, 1, 1, 'Mobile Recharge Exp.', 7, 17, 0.00, 'default', NULL, NULL),
(46, 1, 1, 'Entertainment Exp.', 7, 18, 0.00, 'default', NULL, NULL),
(47, 1, 1, 'Exam Centre Fee', 7, 19, 0.00, 'default', NULL, NULL),
(48, 1, 1, 'Exam Expenses', 7, 19, 0.00, 'default', NULL, NULL),
(49, 1, 1, 'House Rent', 7, 20, 0.00, 'default', NULL, NULL),
(50, 1, 1, 'Netizen Bill Payment', 7, 21, 0.00, 'default', NULL, NULL),
(51, 1, 1, 'Office Stationary', 7, 22, 0.00, 'default', NULL, NULL),
(52, 1, 1, 'Printing & Stationary Expense', 7, 22, 0.00, 'default', NULL, NULL),
(53, 1, 1, 'Repair Expenses', 7, 23, 0.00, 'default', NULL, NULL),
(54, 1, 1, 'Salary & Honorarium (Bank Payment)', 7, 24, 0.00, 'default', NULL, NULL),
(55, 1, 1, 'Salary & Honorarium (Cash Payment)', 7, 24, 0.00, 'default', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `account_transactions`
--

CREATE TABLE `account_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_method_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `type` enum('payment','receipt','contra','fund_transfer','journal') NOT NULL,
  `reference` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_transaction_details`
--

CREATE TABLE `account_transaction_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `account_transactions_id` bigint(20) UNSIGNED NOT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_method_to_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `debit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `description` varchar(191) DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `graduates` varchar(191) DEFAULT NULL,
  `certified_teachers` varchar(191) DEFAULT NULL,
  `student_campus` varchar(191) DEFAULT NULL,
  `students` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admission_forms`
--

CREATE TABLE `admission_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(191) NOT NULL,
  `last_name` varchar(191) DEFAULT NULL,
  `father_name` varchar(191) DEFAULT NULL,
  `mother_name` varchar(191) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `religion` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `registration_no` varchar(191) DEFAULT NULL,
  `previous_school` varchar(191) DEFAULT NULL,
  `previous_class` varchar(191) DEFAULT NULL,
  `guardian_name` varchar(191) DEFAULT NULL,
  `guardian_email` varchar(191) DEFAULT NULL,
  `guardian_phone` varchar(191) DEFAULT NULL,
  `guardian_relation` varchar(191) DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_conversations`
--

CREATE TABLE `ai_conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `feature` varchar(30) NOT NULL DEFAULT 'chat',
  `title` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_messages`
--

CREATE TABLE `ai_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `ai_conversation_id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(20) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_domains`
--

CREATE TABLE `assessment_domains` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `domain_type` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessment_domains`
--

INSERT INTO `assessment_domains` (`id`, `institute_id`, `branch_id`, `session_id`, `domain_type`, `name`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'cognitive', 'Cognitive Domain', 1, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, NULL, 'affective', 'Affective Domain', 1, 2, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, NULL, 'psychomotor', 'Psychomotor Domain', 1, 3, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 3, 3, NULL, 'cognitive', 'Cognitive Domain', 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(5, 3, 3, NULL, 'affective', 'Affective Domain', 1, 2, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(6, 3, 3, NULL, 'psychomotor', 'Psychomotor Domain', 1, 3, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(7, 5, 5, NULL, 'cognitive', 'Cognitive Domain', 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(8, 5, 5, NULL, 'affective', 'Affective Domain', 1, 2, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(9, 5, 5, NULL, 'psychomotor', 'Psychomotor Domain', 1, 3, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(10, 6, 6, NULL, 'cognitive', 'Cognitive Domain', 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(11, 6, 6, NULL, 'affective', 'Affective Domain', 1, 2, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(12, 6, 6, NULL, 'psychomotor', 'Psychomotor Domain', 1, 3, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(13, 11, 11, NULL, 'cognitive', 'Cognitive Domain', 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(14, 11, 11, NULL, 'affective', 'Affective Domain', 1, 2, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(15, 11, 11, NULL, 'psychomotor', 'Psychomotor Domain', 1, 3, '2026-09-22 15:43:02', '2026-09-22 15:43:02');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_items`
--

CREATE TABLE `assessment_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `assessment_domain_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `max_score` decimal(6,2) NOT NULL DEFAULT 5.00,
  `rating_type` varchar(30) NOT NULL DEFAULT 'scale',
  `grade_scale` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`grade_scale`)),
  `remarks_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessment_items`
--

INSERT INTO `assessment_items` (`id`, `institute_id`, `branch_id`, `assessment_domain_id`, `name`, `max_score`, `rating_type`, `grade_scale`, `remarks_enabled`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Subject Knowledge', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 1, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 1, 'Problem Solving', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 2, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 1, 'Written Expression', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 3, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 2, 'Discipline', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 1, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 2, 'Cooperation', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 2, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 2, 'Responsibility', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 3, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 2, 'Attendance Attitude', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 4, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, 2, 'Leadership', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 5, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 1, 1, 3, 'Practical Skills', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 1, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(10, 1, 1, 3, 'Handwriting', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 2, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(11, 1, 1, 3, 'ICT Practical Skills', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 3, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(12, 1, 1, 3, 'Art & Craft', 5.00, 'scale', '[\"Excellent\", \"Very Good\", \"Good\", \"Satisfactory\", \"Needs Improvement\"]', 1, 4, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(13, 3, 3, 4, 'Subject Knowledge', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(14, 3, 3, 4, 'Problem Solving', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(15, 3, 3, 4, 'Written Expression', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(16, 3, 3, 5, 'Discipline', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(17, 3, 3, 5, 'Cooperation', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(18, 3, 3, 5, 'Responsibility', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(19, 3, 3, 5, 'Attendance Attitude', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(20, 3, 3, 5, 'Leadership', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 5, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(21, 3, 3, 6, 'Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(22, 3, 3, 6, 'Handwriting', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(23, 3, 3, 6, 'ICT Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(24, 3, 3, 6, 'Art & Craft', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(25, 5, 5, 7, 'Subject Knowledge', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(26, 5, 5, 7, 'Problem Solving', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(27, 5, 5, 7, 'Written Expression', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(28, 5, 5, 8, 'Discipline', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(29, 5, 5, 8, 'Cooperation', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(30, 5, 5, 8, 'Responsibility', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(31, 5, 5, 8, 'Attendance Attitude', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(32, 5, 5, 8, 'Leadership', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 5, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(33, 5, 5, 9, 'Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(34, 5, 5, 9, 'Handwriting', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(35, 5, 5, 9, 'ICT Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(36, 5, 5, 9, 'Art & Craft', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(37, 6, 6, 10, 'Subject Knowledge', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(38, 6, 6, 10, 'Problem Solving', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(39, 6, 6, 10, 'Written Expression', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(40, 6, 6, 11, 'Discipline', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(41, 6, 6, 11, 'Cooperation', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(42, 6, 6, 11, 'Responsibility', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(43, 6, 6, 11, 'Attendance Attitude', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(44, 6, 6, 11, 'Leadership', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 5, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(45, 6, 6, 12, 'Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(46, 6, 6, 12, 'Handwriting', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(47, 6, 6, 12, 'ICT Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(48, 6, 6, 12, 'Art & Craft', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(49, 11, 11, 13, 'Subject Knowledge', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(50, 11, 11, 13, 'Problem Solving', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(51, 11, 11, 13, 'Written Expression', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(52, 11, 11, 14, 'Discipline', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(53, 11, 11, 14, 'Cooperation', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(54, 11, 11, 14, 'Responsibility', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(55, 11, 11, 14, 'Attendance Attitude', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(56, 11, 11, 14, 'Leadership', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 5, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(57, 11, 11, 15, 'Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 1, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(58, 11, 11, 15, 'Handwriting', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 2, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(59, 11, 11, 15, 'ICT Practical Skills', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 3, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(60, 11, 11, 15, 'Art & Craft', 5.00, 'scale', '[\"Excellent\",\"Very Good\",\"Good\",\"Satisfactory\",\"Needs Improvement\"]', 1, 4, 1, '2026-09-22 15:43:02', '2026-09-22 15:43:02');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `deadline` date NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `file` varchar(100) NOT NULL,
  `file_2` varchar(100) DEFAULT NULL,
  `file_3` varchar(100) DEFAULT NULL,
  `file_4` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `institute_id`, `branch_id`, `session_id`, `title`, `description`, `deadline`, `class_id`, `section_id`, `subject_id`, `file`, `file_2`, `file_3`, `file_4`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 3, 'Alphabet Test', 'Write Alphabets in Capital and Small letters.', '2026-09-18', 2, 6, 1, '2026-09-18-6aacb95a2e607.pdf', NULL, NULL, NULL, '2026-09-16 14:55:54', '2026-09-18 02:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submits`
--

CREATE TABLE `assignment_submits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` int(11) NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `file` varchar(100) NOT NULL,
  `file_2` varchar(100) DEFAULT NULL,
  `file_3` varchar(100) DEFAULT NULL,
  `file_4` varchar(100) DEFAULT NULL,
  `result` double NOT NULL DEFAULT 0,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `review_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assign_shifts`
--

CREATE TABLE `assign_shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `shift_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assign_subjects`
--

CREATE TABLE `assign_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_fines`
--

CREATE TABLE `attendance_fines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `fine_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `waiver` decimal(8,2) NOT NULL DEFAULT 0.00,
  `type` enum('attendance_absent_fine','attendance_quiz_fine','attendance_lab_fine') NOT NULL DEFAULT 'attendance_absent_fine',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_waivers`
--

CREATE TABLE `attendance_waivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `attendance_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `quiz_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `lab_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_waiver` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_name` varchar(100) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `institute_id`, `branch_id`, `title`, `description`, `button_name`, `button_link`, `image`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Excellence in Education, Brighter Futures Ahead!', 'A place where students learn, grow, and succeed.', 'Enroll Now', '#', '2026-09-13-6aa6f6de8ebf0.png', 1, NULL, '2026-09-13 17:17:50', '2026-09-14 08:40:04', NULL),
(2, 1, 1, 'Learn Today, Lead Tomorrow', 'Empowering students with knowledge, values, and skills to succeed in a changing world.', 'Get Admission Today', '#', '2026-09-14-6aa7cf2e6f3a5.png', 1, NULL, '2026-09-14 08:40:46', NULL, NULL),
(4, 6, 6, 'Pegasus University School Section', 'A better Place for your child', 'Building', NULL, '2026-09-18-6aad539c42ef9.png', 1, NULL, '2026-09-18 13:07:08', '2026-09-18 13:08:19', NULL),
(5, 6, 6, 'Pegasus University School Section', 'Welcome to Center of Learning', NULL, NULL, '2026-09-18-6aad641472a9d.png', 1, NULL, '2026-09-18 14:17:24', '2026-09-18 14:18:20', NULL),
(6, 6, 6, 'Pegasus University School Section', 'Pakistan Day Celebrations', NULL, NULL, '2026-09-18-6aad66beb21cd.png', 1, NULL, '2026-09-18 14:28:46', NULL, NULL),
(7, 6, 6, 'Pegasus University School Section', 'Annual Day Celebrations', NULL, NULL, '2026-09-18-6aad66e8df3e6.png', 1, NULL, '2026-09-18 14:29:28', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `beds`
--

CREATE TABLE `beds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `bed_number` varchar(191) NOT NULL,
  `status` enum('vacant','occupied') NOT NULL DEFAULT 'vacant',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `beds`
--

INSERT INTO `beds` (`id`, `institute_id`, `branch_id`, `room_id`, `bed_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 1, '2', 'vacant', '2026-09-19 03:59:30', '2026-09-19 03:59:30');

-- --------------------------------------------------------

--
-- Table structure for table `behaviors`
--

CREATE TABLE `behaviors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `session_id` int(11) NOT NULL,
  `date` varchar(191) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

CREATE TABLE `benefits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `description` varchar(191) DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `institute_id`, `branch_id`, `name`, `slug`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 'History', 'history-xEYJ', 1, 7, '2026-09-18 12:37:45', '2026-09-18 12:37:45'),
(2, 6, 6, 'Science', 'science-PMur', 1, 7, '2026-09-18 12:38:02', '2026-09-18 12:38:02'),
(3, 6, 6, 'Institution Current Affairs', 'current-affairs-QMOH', 1, 7, '2026-09-18 12:38:21', '2026-09-18 12:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(191) DEFAULT NULL,
  `author_name` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 2 COMMENT '1=Published, 2=Draft',
  `published_at` datetime DEFAULT NULL,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `institute_id`, `branch_id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `author_name`, `status`, `published_at`, `views`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 3, 'Admission Open for session 2026-27', 'admission-open-for-session-2026-27-Iax5E', NULL, 'Admission are open for all classes from Grade 1 to Grade 8', '2026-09-18-6aad4da64ad52.jpeg', 'Zulfiqar', 1, '2026-09-18 14:41:42', 0, 7, '2026-09-18 12:41:42', '2026-09-18 12:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `datetime` date DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `book_group` int(11) DEFAULT NULL,
  `book_name` varchar(255) DEFAULT NULL,
  `book_copy_no` varchar(255) DEFAULT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `publish_year` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `total_page` varchar(255) DEFAULT NULL,
  `identification_page` varchar(255) DEFAULT NULL,
  `aa` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `edition` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` varchar(191) DEFAULT NULL,
  `price` varchar(255) DEFAULT NULL,
  `bookself` varchar(255) DEFAULT NULL,
  `rack` varchar(255) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `institute_id`, `branch_id`, `datetime`, `code`, `book_group`, `book_name`, `book_copy_no`, `publisher`, `publish_year`, `provider`, `total_page`, `identification_page`, `aa`, `author`, `edition`, `description`, `category`, `quantity`, `price`, `bookself`, `rack`, `path`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 6, NULL, 'Hist-0001', NULL, 'History of Arabs', NULL, 'Oxford', NULL, NULL, NULL, NULL, NULL, 'PK Hitti', NULL, 'An authentic source of history', NULL, '1', NULL, NULL, NULL, NULL, NULL, '2026-09-17 12:52:29', '2026-09-17 12:52:29');

-- --------------------------------------------------------

--
-- Table structure for table `book_categories`
--

CREATE TABLE `book_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `book_categories`
--

INSERT INTO `book_categories` (`id`, `institute_id`, `branch_id`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 'History', '2026-09-17 12:50:49', '2026-09-17 12:50:49'),
(2, 6, 6, 'Novel', '2026-09-17 12:50:58', '2026-09-17 12:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `book_issues`
--

CREATE TABLE `book_issues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `book_id` int(11) NOT NULL,
  `library_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `institute_id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Branch 1', 1, '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(3, 3, 'Main Branch', 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(5, 5, 'Main Branch', 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(6, 6, 'Main Branch', 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(11, 11, 'Main Branch', 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `bus_number` varchar(50) NOT NULL,
  `vehicle_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vehicle_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `model` varchar(100) NOT NULL,
  `registration_number` varchar(191) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `gps_device_id` varchar(191) DEFAULT NULL,
  `last_latitude` decimal(10,8) DEFAULT NULL,
  `last_longitude` decimal(11,8) DEFAULT NULL,
  `last_location_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `institute_id`, `branch_id`, `bus_number`, `vehicle_type_id`, `vehicle_category_id`, `model`, `registration_number`, `capacity`, `status`, `created_at`, `updated_at`, `deleted_at`, `gps_device_id`, `last_latitude`, `last_longitude`, `last_location_at`) VALUES
(1, 6, 6, 'LXZ 1416', NULL, NULL, '2001', NULL, 25, 1, '2026-09-19 04:36:54', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bus_routes`
--

CREATE TABLE `bus_routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `route_name` varchar(100) NOT NULL,
  `bus_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_location` varchar(191) NOT NULL,
  `end_location` varchar(191) NOT NULL,
  `total_distance` decimal(5,2) NOT NULL,
  `estimated_time` time NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bus_routes`
--

INSERT INTO `bus_routes` (`id`, `institute_id`, `branch_id`, `route_name`, `bus_id`, `start_location`, `end_location`, `total_distance`, `estimated_time`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 'Route 1', 1, 'Pegasus School', 'Farm Koroona', 5.00, '00:00:01', 1, '2026-09-19 04:39:08', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bus_seats`
--

CREATE TABLE `bus_seats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bus_id` bigint(20) UNSIGNED NOT NULL,
  `seat_number` varchar(191) NOT NULL,
  `status` enum('vacant','occupied') NOT NULL DEFAULT 'vacant',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bus_stops`
--

CREATE TABLE `bus_stops` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `route_id` bigint(20) UNSIGNED NOT NULL,
  `stop_name` varchar(100) NOT NULL,
  `stop_order` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bus_stops`
--

INSERT INTO `bus_stops` (`id`, `institute_id`, `branch_id`, `route_id`, `stop_name`, `stop_order`, `latitude`, `longitude`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 1, 'LXZ 1416 - 001', 1, 23.81033400, 90.41223100, 1, '2026-09-19 06:55:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(191) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(191) NOT NULL,
  `owner` varchar(191) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_issues`
--

CREATE TABLE `certificate_issues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(60) NOT NULL,
  `verification_code` varchar(40) NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_name` varchar(191) NOT NULL,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot`)),
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificate_templates`
--

CREATE TABLE `certificate_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `type` varchar(60) NOT NULL,
  `orientation` varchar(20) NOT NULL DEFAULT 'portrait',
  `background_image` varchar(191) DEFAULT NULL,
  `primary_color` varchar(20) NOT NULL DEFAULT '#1f3a5f',
  `secondary_color` varchar(20) NOT NULL DEFAULT '#2f6fed',
  `font_family` varchar(60) NOT NULL DEFAULT 'DejaVu Sans',
  `show_watermark` tinyint(1) NOT NULL DEFAULT 1,
  `watermark_text` varchar(191) DEFAULT NULL,
  `show_qr` tinyint(1) NOT NULL DEFAULT 1,
  `show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `signatures` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`signatures`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificate_templates`
--

INSERT INTO `certificate_templates` (`id`, `institute_id`, `branch_id`, `name`, `type`, `orientation`, `background_image`, `primary_color`, `secondary_color`, `font_family`, `show_watermark`, `watermark_text`, `show_qr`, `show_logo`, `signatures`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 'Admission Form', 'testimonial', 'portrait', NULL, '#1f3a5f', '#2f6fed', 'DejaVu Sans', 1, NULL, 1, 1, NULL, 1, '2026-09-18 17:40:18', '2026-09-18 17:40:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `image` varchar(191) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'inactive' COMMENT 'Chapter status: active, inactive, draft',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`id`, `institute_id`, `course_id`, `title`, `slug`, `priority`, `image`, `description`, `meta_description`, `meta_keywords`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 3, 2, 'Chapter 1', 'chapter-1-P7gS', 1, NULL, NULL, NULL, NULL, 'active', NULL, '2026-09-17 03:25:56', '2026-09-17 03:25:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_name` varchar(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `max_groups` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `institute_id`, `branch_id`, `class_name`, `status`, `max_groups`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 3, 'Class 10', 1, NULL, '2026-09-16 05:10:28', '2026-09-16 05:10:28', NULL),
(2, 6, 6, 'Kinder Garten', 1, NULL, '2026-09-16 10:25:13', '2026-09-17 09:55:09', NULL),
(3, 6, 6, 'Class Prep', 1, NULL, '2026-09-16 10:25:28', '2026-09-17 09:54:52', '2026-09-17 09:54:52'),
(4, 6, 6, 'Class 1st', 1, NULL, '2026-09-16 10:25:43', '2026-09-17 09:54:48', '2026-09-17 09:54:48'),
(5, 6, 6, 'Nursery', 1, NULL, '2026-09-17 09:55:21', '2026-09-17 09:55:21', NULL),
(6, 6, 6, 'Prep', 1, NULL, '2026-09-17 09:55:31', '2026-09-17 09:55:31', NULL),
(7, 6, 6, 'Grade 1', 1, NULL, '2026-09-17 09:55:40', '2026-09-17 09:55:40', NULL),
(8, 6, 6, 'Grade 2', 1, NULL, '2026-09-17 09:55:47', '2026-09-17 09:55:47', NULL),
(9, 6, 6, 'Grade 3', 1, NULL, '2026-09-17 09:55:56', '2026-09-17 09:55:56', NULL),
(10, 6, 6, 'Grade 4', 1, NULL, '2026-09-17 09:56:02', '2026-09-17 09:56:02', NULL),
(11, 6, 6, 'Grade 5', 1, NULL, '2026-09-17 09:56:09', '2026-09-17 09:56:09', NULL),
(12, 6, 6, 'Grade 6', 1, NULL, '2026-09-17 09:56:16', '2026-09-17 09:56:16', NULL),
(13, 6, 6, 'Grade 7', 1, NULL, '2026-09-17 09:56:23', '2026-09-17 09:56:23', NULL),
(14, 6, 6, 'Grade 8', 1, NULL, '2026-09-17 09:56:30', '2026-09-17 09:56:30', NULL),
(15, 6, 6, 'Grade 9', 1, NULL, '2026-09-17 09:57:17', '2026-09-17 09:57:17', NULL),
(16, 6, 6, 'Grade 10', 1, NULL, '2026-09-17 09:57:24', '2026-09-17 09:57:24', NULL),
(17, 6, 6, 'Grade 11', 1, NULL, '2026-09-17 09:57:32', '2026-09-17 09:57:32', NULL),
(18, 6, 6, 'Grade 12', 1, NULL, '2026-09-17 09:57:39', '2026-09-17 09:57:39', NULL),
(19, 11, 11, 'Class 10', 1, NULL, '2026-09-19 16:17:08', '2026-09-19 16:19:04', '2026-09-19 16:19:04'),
(20, 11, 11, 'Class 11', 1, NULL, '2026-09-19 16:17:25', '2026-09-21 13:00:04', '2026-09-21 13:00:04'),
(21, 11, 11, 'Class 5', 1, NULL, '2026-09-21 13:00:57', '2026-09-21 13:01:29', '2026-09-21 13:01:29'),
(22, 11, 11, 'Class 3', 1, NULL, '2026-09-21 13:49:46', '2026-09-21 13:49:50', '2026-09-21 13:49:50'),
(23, 11, 11, 'Class 11', 1, NULL, '2026-09-22 16:06:55', '2026-09-22 16:07:00', '2026-09-22 16:07:00'),
(24, 11, 11, 'Class 15', 1, NULL, '2026-09-22 16:07:24', '2026-09-23 03:22:20', '2026-09-23 03:22:20'),
(25, 11, 11, 'Class 10', 1, 50, '2026-09-23 03:29:10', '2026-09-23 03:29:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_assigns`
--

CREATE TABLE `class_assigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_days`
--

CREATE TABLE `class_days` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `day` varchar(30) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_exams`
--

CREATE TABLE `class_exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `merit_process_type_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_lessons`
--

CREATE TABLE `class_lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `session_id` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `note` varchar(255) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `file_2` varchar(255) DEFAULT NULL,
  `file_3` varchar(255) DEFAULT NULL,
  `file_4` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_routines`
--

CREATE TABLE `class_routines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `day` varchar(20) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `isSeen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `institute_id`, `branch_id`, `name`, `email`, `phone`, `message`, `user_id`, `isSeen`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'plrdpmwtyr', 'tfmfqlul@immenseignite.info', '+1-683-545-8727', 'uyjuilgqovfymfzwtrriyeuijtzvlg', NULL, 0, '2026-09-16 16:45:38', '2026-09-16 16:45:38');

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('lesson','quiz','assignment','pdf','offline') NOT NULL,
  `type_id` bigint(20) UNSIGNED NOT NULL,
  `serial` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `institute_id`, `chapter_id`, `type`, `type_id`, `serial`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 2, 'lesson', 1, 1, '2026-09-17 03:26:09', '2026-09-17 03:26:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `content_visibility`
--

CREATE TABLE `content_visibility` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `course_category_id` bigint(20) UNSIGNED NOT NULL,
  `course_sub_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `video_type` varchar(191) DEFAULT NULL,
  `video_url` longtext DEFAULT NULL,
  `embedded_url` longtext DEFAULT NULL,
  `uploaded_video_path` varchar(191) DEFAULT NULL,
  `status` enum('draft','schedule','published','private') NOT NULL DEFAULT 'draft' COMMENT 'Course status: draft, schedule, published, private',
  `publish_date` date DEFAULT NULL,
  `type` enum('single','bundle') NOT NULL DEFAULT 'single' COMMENT 'Course type: single, bundle',
  `payment_type` enum('free','recurring_payment','one_time') NOT NULL DEFAULT 'free',
  `invoice_title` varchar(191) DEFAULT NULL,
  `fake_enrolled_students` int(11) NOT NULL DEFAULT 0,
  `total_classes` int(11) NOT NULL DEFAULT 0,
  `total_notes` int(11) NOT NULL DEFAULT 0,
  `total_exams` int(11) NOT NULL DEFAULT 0,
  `regular_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `offer_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `repeat_count` int(11) NOT NULL DEFAULT 0,
  `payment_duration` varchar(191) DEFAULT NULL,
  `total_cycles` int(11) DEFAULT NULL,
  `is_infinity` tinyint(1) NOT NULL DEFAULT 0,
  `is_auto_generate_invoice` tinyint(1) NOT NULL DEFAULT 0,
  `class_routine_image` varchar(191) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `total_view` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_enrolled` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `avg_rating` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `institute_id`, `branch_id`, `course_category_id`, `course_sub_category_id`, `title`, `slug`, `image`, `video_type`, `video_url`, `embedded_url`, `uploaded_video_path`, `status`, `publish_date`, `type`, `payment_type`, `invoice_title`, `fake_enrolled_students`, `total_classes`, `total_notes`, `total_exams`, `regular_price`, `offer_price`, `repeat_count`, `payment_duration`, `total_cycles`, `is_infinity`, `is_auto_generate_invoice`, `class_routine_image`, `description`, `meta_description`, `meta_keywords`, `total_view`, `total_enrolled`, `avg_rating`, `start_date`, `end_date`, `requirements`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 3, 3, 1, NULL, 'Machine Learning', 'machine-learning-iGaVa', NULL, 'youtube', '#', NULL, NULL, 'published', NULL, 'single', 'free', NULL, 0, 0, 0, 0, 0.00, 0.00, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 0.00, NULL, NULL, NULL, NULL, '2026-09-17 03:25:25', '2026-09-17 03:25:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `bg_color` varchar(7) DEFAULT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `enable_homepage` tinyint(1) NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`id`, `institute_id`, `name`, `slug`, `image`, `bg_color`, `priority`, `enable_homepage`, `description`, `parent_id`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'Artificial Intelligence', 'artificial-intelligence-CjMy', NULL, NULL, 1, 1, 'aca', NULL, 1, NULL, '2026-09-15 13:02:04', '2026-09-15 13:02:04', NULL),
(3, 6, 'Arts Group', 'arts-group-q1a3', NULL, NULL, 1, 0, 'Compulsory Subjects\r\n         English   ,   Urdu   ,   Islamiat   ,Pakistan Sudy\r\nArts Subjects\r\n         Civics   ,   Economics   ,   Computer Science', NULL, 1, NULL, '2026-09-16 11:10:56', '2026-09-16 11:10:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_faqs`
--

CREATE TABLE `course_faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` longtext DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_features`
--

CREATE TABLE `course_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `priority` longtext DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE `custom_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(191) NOT NULL DEFAULT 'student' COMMENT 'student, teacher, employee',
  `field_name` varchar(191) NOT NULL COMMENT 'database key e.g blood_group',
  `label` varchar(191) NOT NULL COMMENT 'Display label',
  `field_type` varchar(191) NOT NULL DEFAULT 'text' COMMENT 'text, number, select, date, checkbox',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'For select, checkbox options' CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `show_in_form` tinyint(1) NOT NULL DEFAULT 1,
  `show_in_list` tinyint(1) NOT NULL DEFAULT 0,
  `show_in_profile` tinyint(1) NOT NULL DEFAULT 1,
  `serial` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_values`
--

CREATE TABLE `custom_field_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `custom_field_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `value` text DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_menus`
--

CREATE TABLE `custom_menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `label` varchar(191) NOT NULL,
  `link_type` varchar(30) NOT NULL DEFAULT 'custom_url',
  `link_value` varchar(191) DEFAULT NULL,
  `open_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `custom_menus`
--

INSERT INTO `custom_menus` (`id`, `institute_id`, `branch_id`, `parent_id`, `label`, `link_type`, `link_value`, `open_new_tab`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 3, NULL, 'Home', 'home', NULL, 0, 0, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(2, 3, 3, NULL, 'About', 'anchor', 'about', 0, 1, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(3, 3, 3, NULL, 'Academics', 'academics_pages', NULL, 0, 2, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(4, 3, 3, NULL, 'Admission', 'route', 'admission-form-page', 0, 3, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(5, 3, 3, NULL, 'Results', 'route', 'result-page', 0, 4, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(6, 3, 3, NULL, 'Gallery', 'route', 'academic-images', 0, 5, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(7, 3, 3, NULL, 'Events', 'route', 'eventenrollment.index', 0, 6, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(8, 3, 3, NULL, 'Forms', 'route', 'public-form.index', 0, 7, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(9, 3, 3, NULL, 'FAQ', 'anchor', 'faq', 0, 8, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(10, 3, 3, NULL, 'Contact', 'route', 'contact', 0, 9, 1, '2026-09-15 08:49:30', '2026-09-15 08:49:30'),
(11, 6, 6, NULL, 'Home', 'home', NULL, 0, 0, 1, '2026-09-18 11:54:09', '2026-09-18 17:37:41'),
(12, 6, 6, NULL, 'About', 'anchor', 'about', 0, 1, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(13, 6, 6, NULL, 'Academics', 'academics_pages', NULL, 0, 2, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(14, 6, 6, NULL, 'Admission', 'route', 'admission-form-page', 0, 3, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(15, 6, 6, NULL, 'Results', 'route', 'result-page', 0, 4, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(16, 6, 6, NULL, 'Gallery', 'route', 'academic-images', 0, 5, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(17, 6, 6, NULL, 'Events', 'route', 'eventenrollment.index', 0, 6, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(18, 6, 6, NULL, 'Forms', 'route', 'public-form.index', 0, 7, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(19, 6, 6, NULL, 'FAQ', 'anchor', 'faq', 0, 8, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09'),
(20, 6, 6, NULL, 'Contact', 'route', 'contact', 0, 9, 1, '2026-09-18 11:54:09', '2026-09-18 11:54:09');

-- --------------------------------------------------------

--
-- Table structure for table `demo_restrictions`
--

CREATE TABLE `demo_restrictions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'action',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `demo_restrictions`
--

INSERT INTO `demo_restrictions` (`id`, `key`, `name`, `description`, `type`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 'password_change', 'Password Change', 'Prevent password changes in demo mode', 'security', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(2, 'email_change', 'Email Change', 'Prevent email changes in demo mode', 'security', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(3, 'profile_change', 'Profile Change', 'Prevent admin profile changes', 'security', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(4, 'system_settings', 'System Settings', 'Prevent critical system settings updates', 'settings', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(5, 'payment_settings', 'Payment Settings', 'Prevent payment gateway credential changes', 'settings', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(6, 'smtp_settings', 'SMTP Settings', 'Prevent SMTP credential changes', 'settings', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(7, 'api_credentials', 'API Credentials', 'Prevent API key changes', 'settings', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(8, 'license_settings', 'License Settings', 'Prevent license configuration changes', 'settings', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(9, 'database_settings', 'Database Settings', 'Prevent database destructive operations', 'security', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(10, 'account_delete', 'Account Delete', 'Prevent permanent account deletion', 'security', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(11, 'tenant_delete', 'Tenant Delete', 'Prevent institute/tenant deletion', 'security', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(12, 'module_disable', 'Module Disable', 'Prevent disabling modules in demo', 'module', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(13, 'module_delete', 'Module Delete', 'Prevent module deletion', 'module', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(14, 'data_export', 'Data Export', 'Restrict sensitive data export', 'data', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(15, 'data_import', 'Data Import', 'Restrict bulk data import', 'data', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(16, 'system_upgrade', 'System Upgrade', 'Prevent system updates in demo mode', 'system', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `department_name` varchar(30) NOT NULL,
  `slug` varchar(40) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `institute_id`, `branch_id`, `department_name`, `slug`, `priority`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Bangla', 'bangla', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(2, 1, 1, 'English', 'english', 2, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 1, 1, 'ICT', 'ict', 3, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(4, 1, 1, 'Math', 'math', 4, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(5, 1, 1, 'Physics', 'physics', 5, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(6, 1, 1, 'Chemistry', 'chemistry', 6, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(7, 1, 1, 'Biology', 'biology', 7, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(8, 1, 1, 'Humanities', 'humanities', 8, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(9, 1, 1, 'Business Studies', 'business-studies', 9, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(10, 1, 1, 'Administration', 'administration', 10, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(11, 1, 1, 'Principal Office', 'principal-office', 11, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(12, 1, 1, 'Academic/Main Office', 'academic-main-office', 12, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(13, 1, 1, 'Exam', 'exam', 13, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(14, 1, 1, 'Account', 'account', 14, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(15, 1, 1, 'Guidance', 'guidance', 15, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(16, 1, 1, 'Information & Technology', 'information-&-technology', 16, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(17, 1, 1, 'Laboratories', 'laboratories', 17, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(18, 1, 1, 'Library', 'library', 18, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(19, 1, 1, 'Store Room', 'store-room', 19, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(20, 1, 1, 'Maintenance', 'maintenance', 20, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(21, 1, 1, 'Bookstore', 'bookstore', 21, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(22, 1, 1, 'Student Work Program', 'student-work-program', 22, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(23, 6, 6, 'Economics Department', 'economics-department-1789563488', 4, '2026-09-16 10:58:08', '2026-09-16 10:58:08', NULL),
(24, 6, 6, 'Computer Science Department', 'computer-science-department-1789563504', 1, '2026-09-16 10:58:24', '2026-09-17 10:24:47', NULL),
(25, 6, 6, 'English Department', 'english-department-1789563519', 2, '2026-09-16 10:58:39', '2026-09-16 10:58:39', NULL),
(26, 6, 6, 'Urdu Department', 'urdu-department-1789563535', 3, '2026-09-16 10:58:55', '2026-09-16 10:58:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `device_controls`
--

CREATE TABLE `device_controls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `device_access_type` enum('single','multiple','unlimited') NOT NULL DEFAULT 'single',
  `device_limit` int(10) UNSIGNED DEFAULT NULL COMMENT 'Applicable only for multiple',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `director_messages`
--

CREATE TABLE `director_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `director_name` varchar(191) DEFAULT NULL,
  `designation` varchar(191) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `photo` varchar(191) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `director_messages`
--

INSERT INTO `director_messages` (`id`, `institute_id`, `branch_id`, `director_name`, `designation`, `message`, `photo`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 'Sardar Zulfiqar Ali Shah', 'CEO', 'Director’s Message\r\n\r\nAssalamu Alaikum and Welcome to Pegasus University\r\n\r\nIt gives me immense pleasure to welcome you to our school community. At Pegasus University, we believe that education is not limited to textbooks, examinations, and academic results. True education develops knowledge, character, confidence, discipline, creativity, and the ability to face the challenges of an ever-changing world.\r\n\r\nOur aim is to provide a safe, respectful, and stimulating learning environment where every student is encouraged to discover their abilities, develop their talents, and strive for continuous improvement. We are committed to combining strong academic foundations with moral values, practical learning, critical thinking, communication skills, and responsible citizenship.\r\n\r\nWe recognize that every child is unique. Therefore, we seek to understand the individual needs, strengths, and areas for improvement of our students and provide them with appropriate guidance and encouragement. Our teachers play a central role in this process, and we continuously strive to create an environment in which teachers, students, and parents work together as partners in education.\r\n\r\nAs technology and society continue to evolve, we also believe that students must be prepared not only for today but for the opportunities and challenges of tomorrow. Our focus is therefore on developing independent learners who can think critically, communicate effectively, use technology responsibly, and approach problems with confidence and integrity.\r\n\r\nI invite parents to become active partners in their children\'s educational journey and encourage our students to make the most of every opportunity provided to them. Together, through mutual respect, cooperation, and a shared commitment to learning, we can help our young people grow into knowledgeable, confident, responsible, and compassionate members of society.\r\n\r\n**Thank you for placing your trust in Pegasus University. We look forward to growing, learning, and achieving together.**\r\n\r\n**Sardar Zulfiqar Ali Shah**\r\n**Director**\r\n**Pegasus University**', '2026-09-18-6aad4f6dc5ccd.png', 7, '2026-09-18 12:49:17', '2026-09-18 12:49:17');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone_number` varchar(25) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `assigned_bus_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `license_expiry_date` date DEFAULT NULL,
  `photo` varchar(191) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `id_proof_path` varchar(191) DEFAULT NULL,
  `emergency_contact_name` varchar(191) DEFAULT NULL,
  `emergency_contact_phone` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `institute_id`, `branch_id`, `name`, `phone_number`, `license_number`, `assigned_bus_id`, `status`, `created_at`, `updated_at`, `deleted_at`, `license_expiry_date`, `photo`, `address`, `id_proof_path`, `emergency_contact_name`, `emergency_contact_phone`) VALUES
(1, 6, 6, 'Pasham Gul', '+92313000111000', '021245121452', 1, 1, '2026-09-19 04:37:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `driver_documents`
--

CREATE TABLE `driver_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` varchar(191) NOT NULL,
  `file_path` varchar(191) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_type` enum('one_time','monthly_subscription') NOT NULL DEFAULT 'one_time',
  `enrollment_date` date NOT NULL,
  `subscription_start` timestamp NULL DEFAULT NULL,
  `subscription_end` timestamp NULL DEFAULT NULL,
  `amount_paid` decimal(8,2) NOT NULL DEFAULT 0.00,
  `payment_request_id` varchar(36) DEFAULT NULL,
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'inactive' COMMENT 'Enrollment status: active, inactive, blocked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `name` text NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `details` longtext NOT NULL,
  `location` varchar(191) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `price` decimal(10,2) DEFAULT NULL,
  `currency_code` varchar(10) DEFAULT NULL,
  `max_seats` int(10) UNSIGNED DEFAULT NULL,
  `enrollment_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `registration_deadline` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `institute_id`, `branch_id`, `start_date`, `end_date`, `name`, `image`, `details`, `location`, `is_public`, `is_paid`, `price`, `currency_code`, `max_seats`, `enrollment_enabled`, `registration_deadline`, `created_at`, `updated_at`) VALUES
(1, 3, 3, '2026-09-15 00:00:00', '2026-09-15 00:00:00', 'Sports Gala Award Ceremony', NULL, 'Sports Gala Year 2026 Award Ceremony is held on 30 Sep 2026, Everyone are invited to join.', 'New York', 1, 1, 50.00, 'USD', NULL, 1, '2026-09-22 00:00:00', '2026-09-15 09:20:41', '2026-09-15 09:20:41'),
(3, 6, 6, '2026-09-17 00:00:00', '2026-09-17 00:00:00', 'Pakistan Day', NULL, 'Pakistan Day Ceelebrations', 'Grand Hall', 1, 1, 500.00, 'Rs.', NULL, 1, '2026-09-20 00:00:00', '2026-09-17 02:28:07', '2026-09-17 02:28:07');

-- --------------------------------------------------------

--
-- Table structure for table `event_enrollments`
--

CREATE TABLE `event_enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `ticket_code` varchar(20) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency_code` varchar(10) DEFAULT NULL,
  `payment_status` smallint(6) NOT NULL DEFAULT 1,
  `payment_request_id` varchar(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `exam_code` varchar(191) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `institute_id`, `branch_id`, `session_id`, `name`, `exam_code`, `note`, `status`, `created_at`, `updated_at`) VALUES
(1, 6, 6, NULL, '1st Term', '0001', NULL, 0, '2026-09-16 10:56:10', '2026-09-16 10:56:10'),
(2, 6, 6, NULL, '2nd Term', '0002', NULL, 0, '2026-09-16 10:56:23', '2026-09-16 10:56:23'),
(3, 6, 6, NULL, '3rd Term', '0003', NULL, 0, '2026-09-16 10:56:33', '2026-09-16 10:56:33'),
(4, 6, 6, NULL, 'Final Exam', '0004', NULL, 0, '2026-09-16 10:56:45', '2026-09-16 10:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `exam_attendances`
--

CREATE TABLE `exam_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `attendance` int(11) NOT NULL DEFAULT 2,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_codes`
--

CREATE TABLE `exam_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_model_id` bigint(20) UNSIGNED NOT NULL,
  `short_code_id` bigint(20) UNSIGNED NOT NULL,
  `short_code_title` varchar(191) DEFAULT NULL,
  `short_code_note` varchar(191) DEFAULT NULL,
  `default_id` int(11) DEFAULT NULL,
  `total_mark` decimal(5,2) DEFAULT NULL,
  `accept_percent` decimal(5,2) DEFAULT NULL,
  `pass_mark` decimal(5,2) DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_codes`
--

INSERT INTO `exam_codes` (`id`, `institute_id`, `branch_id`, `class_model_id`, `short_code_id`, `short_code_title`, `short_code_note`, `default_id`, `total_mark`, `accept_percent`, `pass_mark`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 1, 1, 'SC-1', 'Short Code -1', 1, 100.00, 1.00, 0.00, 1, '2026-09-16 05:12:30', '2026-09-16 05:12:30'),
(2, 3, 3, 1, 2, 'SC-2', 'Short Code -2', 2, 100.00, 1.00, 0.00, 1, '2026-09-16 05:12:30', '2026-09-16 05:12:30'),
(3, 3, 3, 1, 3, 'SC-3', 'Short Code -3', 3, 100.00, 1.00, 0.00, 1, '2026-09-16 05:12:30', '2026-09-16 05:12:30'),
(4, 3, 3, 1, 4, 'SC-4', 'Short Code -4', 4, 100.00, 1.00, 0.00, 1, '2026-09-16 05:12:30', '2026-09-16 05:12:30'),
(5, 3, 3, 1, 5, 'SC-5', 'Short Code -5', 5, 100.00, 1.00, 0.00, 1, '2026-09-16 05:12:30', '2026-09-16 05:12:30'),
(6, 3, 3, 1, 6, 'SC-6', 'Short Code -6', 6, 100.00, 1.00, 0.00, 1, '2026-09-16 05:12:31', '2026-09-16 05:12:31');

-- --------------------------------------------------------

--
-- Table structure for table `exam_grades`
--

CREATE TABLE `exam_grades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_model_id` bigint(20) UNSIGNED NOT NULL,
  `grade_id` bigint(20) UNSIGNED NOT NULL,
  `grade_name` varchar(191) NOT NULL,
  `grade_number` decimal(5,2) NOT NULL,
  `grade_point` decimal(5,2) NOT NULL,
  `grade_range` varchar(191) NOT NULL,
  `number_high` decimal(5,2) NOT NULL,
  `number_low` decimal(5,2) NOT NULL,
  `point_high` decimal(5,2) NOT NULL,
  `point_low` decimal(5,2) NOT NULL,
  `priority` varchar(191) NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_marks`
--

CREATE TABLE `exam_marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `mark1` decimal(5,2) NOT NULL DEFAULT 0.00,
  `mark2` decimal(5,2) NOT NULL DEFAULT 0.00,
  `mark3` decimal(5,2) NOT NULL DEFAULT 0.00,
  `mark4` decimal(5,2) NOT NULL DEFAULT 0.00,
  `mark5` decimal(5,2) NOT NULL DEFAULT 0.00,
  `mark6` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_marks` decimal(5,2) NOT NULL DEFAULT 0.00,
  `percentage` decimal(6,2) NOT NULL DEFAULT 0.00,
  `grade_point` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pass',
  `remarks` varchar(255) DEFAULT NULL,
  `is_absent` tinyint(1) NOT NULL DEFAULT 0,
  `is_exempted` tinyint(1) NOT NULL DEFAULT 0,
  `source` varchar(30) NOT NULL DEFAULT 'manual',
  `grade` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_schedules`
--

CREATE TABLE `exam_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(20) NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faq_questions`
--

CREATE TABLE `faq_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` longtext DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faq_questions`
--

INSERT INTO `faq_questions` (`id`, `institute_id`, `branch_id`, `question`, `answer`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'What is an academic program?', 'The academic program is a one stop solution for students. Where every student has the opportunity to ensure A+ preparation for  board exams through interactive live classes, homework, live tests and progress analysis by the best mentors in each subject throughout the year. Once a student starts this program, he will not have to go to any coaching or private tutor after school/college.', 1, NULL, '2026-09-14 08:42:10', NULL, NULL),
(2, 1, 1, 'What is an academic program?', 'The academic program is a one stop solution for students. Where every student has the opportunity to ensure A+ preparation for  board exams through interactive live classes, homework, live tests and progress analysis by the best mentors in each subject throughout the year. Once a student starts this program, he will not have to go to any coaching or private tutor after school/college.', 1, NULL, '2026-09-14 08:42:22', NULL, NULL),
(3, 1, 1, 'What is an academic program?', 'The academic program is a one stop solution for students. Where every student has the opportunity to ensure A+ preparation for  board exams through interactive live classes, homework, live tests and progress analysis by the best mentors in each subject throughout the year. Once a student starts this program, he will not have to go to any coaching or private tutor after school/college.', 1, NULL, '2026-09-14 08:42:35', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fee_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fee_amount` decimal(8,2) NOT NULL,
  `fine_amount` decimal(8,2) DEFAULT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_date_configs`
--

CREATE TABLE `fee_date_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `fee_sub_head_id` bigint(20) UNSIGNED NOT NULL,
  `payable_date_start` date NOT NULL,
  `payable_date_end` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_heads`
--

CREATE TABLE `fee_heads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `serial` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_heads`
--

INSERT INTO `fee_heads` (`id`, `institute_id`, `branch_id`, `name`, `serial`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Admission Fees', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Admission Form Fees', 2, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'Session Charge', 3, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'Online Apply Fees', 4, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'Tuition Fees', 5, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'College Examination Fees', 6, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'Model Test Fees', 7, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, 'Re-Take Exam Fees', 8, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 1, 1, 'Form Fill Up/Registration', 9, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(10, 1, 1, 'TC/Admission Cancel', 10, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(11, 1, 1, 'Testimonial Fees', 11, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(12, 1, 1, 'Study Materials', 12, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(13, 6, 6, 'Admission Fee', 1, '2026-09-16 11:16:16', '2026-09-16 11:16:16'),
(14, 6, 6, 'Tuition Fee', 2, '2026-09-16 11:16:33', '2026-09-16 11:16:33'),
(15, 6, 6, 'Transport Fee', 3, '2026-09-16 11:16:57', '2026-09-16 11:16:57'),
(16, 6, 6, 'Hostel Fee', 4, '2026-09-16 11:17:09', '2026-09-16 11:17:09'),
(17, 6, 6, 'Examination Fee', 5, '2026-09-16 11:17:22', '2026-09-16 11:17:22'),
(18, 6, 6, 'Library Fee', 6, '2026-09-16 11:17:35', '2026-09-16 11:17:35'),
(19, 6, 6, 'Sports Fee', 7, '2026-09-16 11:17:47', '2026-09-16 11:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `fee_maps`
--

CREATE TABLE `fee_maps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `fee_head_id` bigint(20) UNSIGNED NOT NULL,
  `ledger_id` bigint(20) UNSIGNED NOT NULL,
  `fund` varchar(191) DEFAULT NULL,
  `type` enum('fee','fee_fine') NOT NULL DEFAULT 'fee',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_maps`
--

INSERT INTO `fee_maps` (`id`, `institute_id`, `branch_id`, `fee_head_id`, `ledger_id`, `fund`, `type`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 13, 7, NULL, 'fee', '2026-09-16 11:23:15', '2026-09-16 11:23:15'),
(2, 6, 6, 13, 3, NULL, 'fee', '2026-09-16 13:12:56', '2026-09-16 13:12:56');

-- --------------------------------------------------------

--
-- Table structure for table `fee_map_fee_sub_head`
--

CREATE TABLE `fee_map_fee_sub_head` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fee_map_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fee_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fee_sub_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_map_fee_sub_head`
--

INSERT INTO `fee_map_fee_sub_head` (`id`, `fee_map_id`, `fee_head_id`, `fee_sub_head_id`, `created_at`, `updated_at`) VALUES
(1, 1, 13, 45, NULL, NULL),
(2, 2, 13, 45, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_map_fund`
--

CREATE TABLE `fee_map_fund` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fee_map_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_map_fund`
--

INSERT INTO `fee_map_fund` (`id`, `fee_map_id`, `fund_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 2, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_sub_heads`
--

CREATE TABLE `fee_sub_heads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `fee_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `serial` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_sub_heads`
--

INSERT INTO `fee_sub_heads` (`id`, `institute_id`, `branch_id`, `fee_head_id`, `name`, `serial`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Admission Fee', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, NULL, 'January', 2, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, NULL, 'February', 3, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, NULL, 'March', 4, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, NULL, 'April', 5, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, NULL, 'May', 6, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, NULL, 'June', 7, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, NULL, 'July', 8, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 1, 1, NULL, 'August', 9, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(10, 1, 1, NULL, 'September', 10, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(11, 1, 1, NULL, 'October', 11, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(12, 1, 1, NULL, 'November', 12, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(13, 1, 1, NULL, 'December', 13, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(14, 1, 1, NULL, 'Session Charge', 14, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(15, 1, 1, NULL, '1st Term Exam', 15, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(16, 1, 1, NULL, '2nd Semester Exam', 16, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(17, 1, 1, NULL, '1st Semester Exam', 17, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(18, 1, 1, NULL, '3rd Semester Exam', 18, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(19, 1, 1, NULL, '1st Model test', 19, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(20, 1, 1, NULL, '2nd Model Test', 20, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(21, 1, 1, NULL, '3rd Model Test', 21, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(22, 1, 1, NULL, 'Attendance Fine', 22, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(23, 1, 1, NULL, 'Quiz Fine', 23, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(24, 1, 1, NULL, 'Lab Fine', 24, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(25, 1, 1, NULL, 'Library Fine', 25, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(26, 1, 1, NULL, 'Late Fine', 26, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(27, 1, 1, NULL, 'ID Card Fine', 27, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(28, 1, 1, NULL, 'Discipline Fine', 28, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(29, 1, 1, NULL, 'Miscellaneous Fine', 29, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(30, 1, 1, NULL, 'Laboratories Exam Fine', 30, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(31, 1, 1, NULL, 'College Examination Fee', 31, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(32, 1, 1, NULL, 'Model Test Fee', 32, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(33, 1, 1, NULL, 'Re-Take Exam Fee', 33, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(34, 1, 1, NULL, 'Tc/Admission Cancel', 34, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(35, 1, 1, NULL, 'Form Fill Up/Registration', 35, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(36, 1, 1, NULL, 'Admission Income (Form, SMS & Others)', 36, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(37, 1, 1, NULL, 'Different Type of course fees', 37, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(38, 1, 1, NULL, 'Stipend', 38, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(39, 1, 1, NULL, 'College Report', 39, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(40, 1, 1, NULL, 'Miscellaneous Income', 40, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(41, 1, 1, NULL, 'Tuition Book/Rosid Book', 41, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(42, 1, 1, NULL, 'ID Card New/Fita/Folder', 42, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(43, 1, 1, NULL, 'Online Apply Fees', 43, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(44, 1, 1, NULL, 'Testimonial Fees', 44, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(45, 6, 6, NULL, 'Library Fee for Books', 1, '2026-09-16 11:20:58', '2026-09-16 11:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `form_builder_fields`
--

CREATE TABLE `form_builder_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `form_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(191) NOT NULL,
  `field_key` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL,
  `placeholder` varchar(191) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `width` varchar(10) NOT NULL DEFAULT 'full',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_builder_fields`
--

INSERT INTO `form_builder_fields` (`id`, `form_id`, `label`, `field_key`, `type`, `placeholder`, `options`, `is_required`, `width`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'as', 'as', 'text', 'as', NULL, 1, 'full', 0, '2026-09-15 07:48:35', '2026-09-15 07:48:35'),
(2, 4, 'Admision Form No', 'admision_form_no', 'text', '', NULL, 0, 'full', 0, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(3, 4, 'Admission No.', 'admission_no', 'text', '', NULL, 0, 'full', 1, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(4, 4, 'Class', 'class', 'select', '', '[\"Kinder Garten\",\"Nursery\",\"Prep\",\"Grade 1\",\"Grade 2\",\"Grade 3\",\"Grade 4\",\"Grade 5\",\"Grade 6\",\"Grade 7\",\"Grade 8\"]', 0, 'full', 2, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(5, 4, 'Name of Student', 'name_of_student', 'text', '', NULL, 0, 'full', 3, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(6, 4, 'Father\'s/ Guardian', 'father_s_guardian', 'text', '', NULL, 0, 'full', 4, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(7, 4, 'Student (Form B or National ID No.)', 'student_form_b_or_national_id_no', 'text', '', NULL, 0, 'full', 5, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(8, 4, 'Disability', 'disability', 'checkbox', '', '[\"Yes\",\"No\"]', 0, 'full', 6, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(9, 4, 'Kind of Disability', 'kind_of_disability', 'textarea', '', NULL, 0, 'full', 7, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(10, 4, 'Student Mobile No.', 'student_mobile_no', 'text', '', NULL, 0, 'full', 8, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(11, 4, 'Student (Gmail ID)', 'student_gmail_id', 'text', '', NULL, 0, 'full', 9, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(12, 4, 'Father/ Guardian Address', 'father_guardian_address', 'text', '', NULL, 0, 'full', 10, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(13, 4, 'Mother\'s Name', 'mother_s_name', 'text', '', NULL, 0, 'full', 11, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(14, 4, 'Mother\'s Mobile No.', 'mother_s_mobile_no', 'text', '', NULL, 0, 'full', 12, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(15, 4, 'Mother\'s Gmail ID', 'mother_s_gmail_id', 'text', '', NULL, 0, 'full', 13, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(16, 4, 'Group', 'group', 'select', '', '[\"Primary\",\"Arts\",\"Science\",\"Pre Medical\",\"Pre Engineering\",\"Computer Science\",\"Inter Science\"]', 0, 'full', 14, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(17, 4, 'Compulsory Subjects', 'compulsory_subjects', 'checkbox', '', '[\"English\",\"Urdu\",\"Islamiat\",\"Pakistan Study\"]', 0, 'full', 15, '2026-09-17 04:41:47', '2026-09-17 04:41:47'),
(18, 4, 'Arts', 'arts', 'text', '', NULL, 0, 'full', 16, '2026-09-17 04:41:47', '2026-09-17 04:41:47');

-- --------------------------------------------------------

--
-- Table structure for table `form_builder_forms`
--

CREATE TABLE `form_builder_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'custom',
  `description` text DEFAULT NULL,
  `submit_button_text` varchar(100) NOT NULL DEFAULT 'Submit',
  `success_message` text DEFAULT NULL,
  `notify_email` varchar(191) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 2 COMMENT '1=Published, 2=Draft',
  `show_on_homepage` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_builder_forms`
--

INSERT INTO `form_builder_forms` (`id`, `institute_id`, `branch_id`, `name`, `slug`, `type`, `description`, `submit_button_text`, `success_message`, `notify_email`, `icon`, `status`, `show_on_homepage`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 3, 'Admission', 'admission', 'registration', 'ascasc', 'Submit', 'Thank you! Your submission has been received.', NULL, NULL, 2, 1, 4, '2026-09-15 07:48:35', '2026-09-15 07:48:51', NULL),
(2, 2, 2, 'Student Interview Form', 'student-interview-form', 'custom', 'asa', 'Submit', 'Thank you! Your submission has been received.', NULL, NULL, 1, 1, 3, '2026-09-16 02:35:04', '2026-09-16 03:58:26', NULL),
(3, 8, 8, 'Admission Form', 'admission-form', 'registration', 'asa', 'Submit', 'Thank you! Your submission has been received.', NULL, NULL, 1, 1, 9, '2026-09-17 04:02:17', '2026-09-17 04:02:17', NULL),
(4, 6, 6, 'Student Admission Form (upto Grade 8)', 'student-admission-form-upto-grade-8', 'admission', 'Complete Record of Student at the time of Admission', 'Submit', 'Thank you! Your submission has been received.', NULL, NULL, 1, 1, 7, '2026-09-17 04:41:47', '2026-09-17 04:41:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `form_builder_submissions`
--

CREATE TABLE `form_builder_submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `form_id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=New, 2=Reviewed',
  `remarks` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gamifications`
--

CREATE TABLE `gamifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `session_id` int(11) NOT NULL,
  `date` varchar(191) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `google_meet_meetings`
--

CREATE TABLE `google_meet_meetings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `duration` int(10) UNSIGNED NOT NULL COMMENT 'minutes',
  `meet_link` varchar(191) DEFAULT NULL,
  `meet_event_id` varchar(191) DEFAULT NULL,
  `organizer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('scheduled','live','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `recurrence_frequency` enum('daily','weekly') DEFAULT NULL,
  `recurrence_end_date` date DEFAULT NULL,
  `parent_meeting_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'links a recurring child instance back to the meeting it was expanded from',
  `recording_url` varchar(191) DEFAULT NULL,
  `attachment_path` varchar(191) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `visibility` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'which panels can see this meeting: teacher, student, guardian' CHECK (json_valid(`visibility`)),
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `grade_name` varchar(191) NOT NULL,
  `grade_number` decimal(5,2) NOT NULL,
  `grade_point` decimal(5,2) NOT NULL,
  `grade_range` varchar(191) NOT NULL,
  `number_high` decimal(5,2) NOT NULL,
  `number_low` decimal(5,2) NOT NULL,
  `point_high` decimal(5,2) NOT NULL,
  `point_low` decimal(5,2) NOT NULL,
  `priority` varchar(191) NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `institute_id`, `branch_id`, `grade_name`, `grade_number`, `grade_point`, `grade_range`, `number_high`, `number_low`, `point_high`, `point_low`, `priority`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'A+', 80.00, 5.00, '80-100', 100.00, 80.00, 5.00, 5.00, '1', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'A', 70.00, 4.00, '70-79', 79.99, 70.00, 4.99, 4.00, '2', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'A-', 60.00, 3.50, '60-69', 69.99, 60.00, 3.99, 3.50, '3', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'B', 50.00, 3.00, '50-59', 59.99, 50.00, 3.49, 3.00, '4', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'C', 40.00, 2.00, '40-49', 49.99, 40.00, 2.99, 2.00, '5', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'D', 33.00, 1.00, '33-39', 39.99, 33.00, 1.99, 1.00, '6', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'F', 0.00, 0.00, '00-32', 32.99, 0.00, 0.00, 0.00, '7', NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `grand_final_class_exams`
--

CREATE TABLE `grand_final_class_exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `serial_no` int(10) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_final_settings`
--

CREATE TABLE `grand_final_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `calculation_method` enum('weighted_percentage','full_marks') NOT NULL DEFAULT 'weighted_percentage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `helpers`
--

CREATE TABLE `helpers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `phone_number` varchar(191) DEFAULT NULL,
  `address` varchar(191) DEFAULT NULL,
  `emergency_contact_name` varchar(191) DEFAULT NULL,
  `emergency_contact_phone` varchar(191) DEFAULT NULL,
  `assigned_bus_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(191) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `helpers`
--

INSERT INTO `helpers` (`id`, `institute_id`, `branch_id`, `name`, `phone_number`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `assigned_bus_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 'Sparli Gul', '+92111000111', 'Mohallah Shakailaan, Kheshgi Payan', 'Nabi Gul', '+92345000111000', 1, '1', '2026-09-19 07:12:17', '2026-09-19 07:12:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `homepage_sections`
--

CREATE TABLE `homepage_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_key` varchar(50) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homepage_sections`
--

INSERT INTO `homepage_sections` (`id`, `institute_id`, `branch_id`, `section_key`, `is_enabled`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 'about', 1, 0, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(2, 3, 3, 'director-message', 1, 1, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(3, 3, 3, 'stats', 1, 2, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(4, 3, 3, 'cta-admission', 1, 3, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(5, 3, 3, 'dynamic-forms', 1, 4, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(6, 3, 3, 'courses', 1, 5, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(7, 3, 3, 'notices', 1, 6, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(8, 3, 3, 'gallery', 1, 7, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(9, 3, 3, 'teachers', 1, 8, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(10, 3, 3, 'staffs', 1, 9, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(11, 3, 3, 'mobile-app', 1, 10, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(12, 3, 3, 'events', 1, 11, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(13, 3, 3, 'upcoming-exams', 1, 12, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(14, 3, 3, 'result-announcements', 1, 13, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(15, 3, 3, 'blog', 1, 14, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(16, 3, 3, 'testimonials', 1, 15, '2026-09-16 07:49:00', '2026-09-16 15:49:37'),
(17, 3, 3, 'faq', 1, 16, '2026-09-16 07:49:00', '2026-09-16 07:49:00'),
(18, 2, NULL, 'about', 1, 0, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(19, 2, NULL, 'director-message', 1, 1, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(20, 2, NULL, 'stats', 1, 2, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(21, 2, NULL, 'cta-admission', 1, 3, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(22, 2, NULL, 'dynamic-forms', 1, 4, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(23, 2, NULL, 'courses', 1, 5, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(24, 2, NULL, 'notices', 1, 6, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(25, 2, NULL, 'gallery', 1, 7, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(26, 2, NULL, 'teachers', 1, 8, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(27, 2, NULL, 'staffs', 1, 9, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(28, 2, NULL, 'mobile-app', 1, 10, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(29, 2, NULL, 'events', 1, 11, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(30, 2, NULL, 'upcoming-exams', 1, 12, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(31, 2, NULL, 'result-announcements', 1, 13, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(32, 2, NULL, 'blog', 1, 14, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(33, 2, NULL, 'testimonials', 1, 15, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(34, 2, NULL, 'faq', 1, 16, '2026-09-16 07:49:34', '2026-09-16 07:49:34'),
(35, 1, NULL, 'about', 1, 0, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(36, 1, NULL, 'director-message', 1, 1, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(37, 1, NULL, 'stats', 1, 2, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(38, 1, NULL, 'cta-admission', 1, 3, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(39, 1, NULL, 'dynamic-forms', 1, 4, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(40, 1, NULL, 'courses', 1, 5, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(41, 1, NULL, 'notices', 1, 6, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(42, 1, NULL, 'gallery', 1, 7, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(43, 1, NULL, 'teachers', 1, 8, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(44, 1, NULL, 'staffs', 1, 9, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(45, 1, NULL, 'mobile-app', 1, 10, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(46, 1, NULL, 'events', 1, 11, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(47, 1, NULL, 'upcoming-exams', 1, 12, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(48, 1, NULL, 'result-announcements', 1, 13, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(49, 1, NULL, 'blog', 1, 14, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(50, 1, NULL, 'testimonials', 1, 15, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(51, 1, NULL, 'faq', 1, 16, '2026-09-16 07:58:29', '2026-09-16 07:58:29'),
(52, 8, NULL, 'about', 1, 0, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(53, 8, NULL, 'director-message', 1, 1, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(54, 8, NULL, 'stats', 1, 2, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(55, 8, NULL, 'cta-admission', 1, 3, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(56, 8, NULL, 'dynamic-forms', 1, 4, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(57, 8, NULL, 'courses', 1, 5, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(58, 8, NULL, 'notices', 1, 6, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(59, 8, NULL, 'gallery', 1, 7, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(60, 8, NULL, 'teachers', 1, 8, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(61, 8, NULL, 'staffs', 1, 9, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(62, 8, NULL, 'mobile-app', 1, 10, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(63, 8, NULL, 'events', 1, 11, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(64, 8, NULL, 'upcoming-exams', 1, 12, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(65, 8, NULL, 'result-announcements', 1, 13, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(66, 8, NULL, 'blog', 1, 14, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(67, 8, NULL, 'testimonials', 1, 15, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(68, 8, NULL, 'faq', 1, 16, '2026-09-17 04:29:22', '2026-09-17 04:29:22'),
(69, 6, NULL, 'about', 1, 0, '2026-09-17 09:31:40', '2026-09-18 12:44:40'),
(70, 6, NULL, 'director-message', 1, 1, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(71, 6, NULL, 'stats', 1, 2, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(72, 6, NULL, 'cta-admission', 1, 3, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(73, 6, NULL, 'dynamic-forms', 1, 4, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(74, 6, NULL, 'courses', 1, 5, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(75, 6, NULL, 'notices', 1, 6, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(76, 6, NULL, 'gallery', 1, 7, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(77, 6, NULL, 'teachers', 1, 8, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(78, 6, NULL, 'staffs', 1, 9, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(79, 6, NULL, 'mobile-app', 1, 10, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(80, 6, NULL, 'events', 1, 11, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(81, 6, NULL, 'upcoming-exams', 1, 12, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(82, 6, NULL, 'result-announcements', 1, 13, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(83, 6, NULL, 'blog', 1, 14, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(84, 6, NULL, 'testimonials', 1, 15, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(85, 6, NULL, 'faq', 1, 16, '2026-09-17 09:31:40', '2026-09-17 09:31:40'),
(86, 11, NULL, 'about', 1, 0, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(87, 11, NULL, 'director-message', 1, 1, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(88, 11, NULL, 'stats', 1, 2, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(89, 11, NULL, 'cta-admission', 1, 3, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(90, 11, NULL, 'dynamic-forms', 1, 4, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(91, 11, NULL, 'courses', 1, 5, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(92, 11, NULL, 'notices', 1, 6, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(93, 11, NULL, 'gallery', 1, 7, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(94, 11, NULL, 'teachers', 1, 8, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(95, 11, NULL, 'staffs', 1, 9, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(96, 11, NULL, 'mobile-app', 1, 10, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(97, 11, NULL, 'events', 1, 11, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(98, 11, NULL, 'upcoming-exams', 1, 12, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(99, 11, NULL, 'result-announcements', 1, 13, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(100, 11, NULL, 'blog', 1, 14, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(101, 11, NULL, 'testimonials', 1, 15, '2026-09-17 10:24:31', '2026-09-17 10:24:31'),
(102, 11, NULL, 'faq', 1, 16, '2026-09-17 10:24:31', '2026-09-17 10:24:31');

-- --------------------------------------------------------

--
-- Table structure for table `home_sections`
--

CREATE TABLE `home_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `serial` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_sections`
--

INSERT INTO `home_sections` (`id`, `institute_id`, `branch_id`, `title`, `description`, `serial`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 2, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(2, 2, 2, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(3, 2, 2, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(4, 2, 2, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(5, 2, 2, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(6, 2, 2, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(7, 2, 2, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(8, 2, 2, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(9, 3, 3, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(10, 3, 3, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(11, 3, 3, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(12, 3, 3, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(13, 3, 3, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(14, 3, 3, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(15, 3, 3, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(16, 3, 3, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(17, 4, 4, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(18, 4, 4, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(19, 4, 4, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(20, 4, 4, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(21, 4, 4, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(22, 4, 4, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(23, 4, 4, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(24, 4, 4, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(25, 5, 5, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(26, 5, 5, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(27, 5, 5, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(28, 5, 5, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(29, 5, 5, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(30, 5, 5, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(31, 5, 5, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(32, 5, 5, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(33, 6, 6, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(34, 6, 6, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(35, 6, 6, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(36, 6, 6, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(37, 6, 6, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(38, 6, 6, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(39, 6, 6, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(40, 6, 6, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(41, 7, 7, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(42, 7, 7, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(43, 7, 7, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(44, 7, 7, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(45, 7, 7, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(46, 7, 7, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(47, 7, 7, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(48, 7, 7, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(49, 8, 8, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(50, 8, 8, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(51, 8, 8, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(52, 8, 8, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(53, 8, 8, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(54, 8, 8, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(55, 8, 8, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(56, 8, 8, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(57, 9, 9, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(58, 9, 9, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(59, 9, 9, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(60, 9, 9, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(61, 9, 9, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(62, 9, 9, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(63, 9, 9, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(64, 9, 9, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(65, 10, 10, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(66, 10, 10, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(67, 10, 10, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(68, 10, 10, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(69, 10, 10, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(70, 10, 10, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(71, 10, 10, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(72, 10, 10, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(73, 11, 11, 'About Us', 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.', 1, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(74, 11, 11, 'Why Choose Us', 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.', 2, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(75, 11, 11, 'Admission', 'Start your journey with our easy registration and onboarding process designed for candidates and employers.', 3, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(76, 11, 11, 'Teachers', 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.', 4, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(77, 11, 11, 'Feedback', 'Read success stories and testimonials from job seekers and employers using our platform.', 5, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(78, 11, 11, 'Mobile App', 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.', 6, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(79, 11, 11, 'Events', 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.', 7, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(80, 11, 11, 'FAQ', 'Find answers to the most common questions about our platform, applications, subscriptions, and services.', 8, 1, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `hostel_name` varchar(80) NOT NULL,
  `type` varchar(20) NOT NULL,
  `address` longtext NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`id`, `institute_id`, `branch_id`, `hostel_name`, `type`, `address`, `note`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 'Iqbal Hostel', 'Male', 'Street # 02, 7th Road, Nowshera', 'Only for Male Students', '2026-09-19 03:43:17', '2026-09-19 03:43:17'),
(2, 6, 6, 'Fatima Jinnah Hostel', 'Female', 'Street # 1,4th Road, Nowshera', 'Only for Female Students', '2026-09-19 03:44:13', '2026-09-19 03:44:13');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_bills`
--

CREATE TABLE `hostel_bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `hostel_fee` decimal(8,2) NOT NULL,
  `meal_fee` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(8,2) NOT NULL,
  `status` enum('pending','paid','overdue') NOT NULL DEFAULT 'pending',
  `due_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_buildings`
--

CREATE TABLE `hostel_buildings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hostel_id` bigint(20) UNSIGNED NOT NULL,
  `building_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hostel_buildings`
--

INSERT INTO `hostel_buildings` (`id`, `institute_id`, `branch_id`, `hostel_id`, `building_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 1, 'Shadman Building', '2026-09-19 03:57:03', '2026-09-19 03:57:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_categories`
--

CREATE TABLE `hostel_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `hostel_id` bigint(20) UNSIGNED NOT NULL,
  `standard` varchar(20) NOT NULL,
  `hostel_fee` decimal(8,2) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hostel_categories`
--

INSERT INTO `hostel_categories` (`id`, `institute_id`, `branch_id`, `hostel_id`, `standard`, `hostel_fee`, `note`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 1, 'Best', 500.00, NULL, '2026-09-19 03:46:03', '2026-09-19 03:46:03');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_collections`
--

CREATE TABLE `hostel_collections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `room_member_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` varchar(191) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year',
  `month` varchar(191) DEFAULT NULL COMMENT 'e.g. 2026-07, null for one-off fee heads like admission/security deposit',
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_payable` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receive_ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_collection_details`
--

CREATE TABLE `hostel_collection_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hostel_collection_id` bigint(20) UNSIGNED NOT NULL,
  `fee_head` enum('admission','monthly','security_deposit','utility','maintenance') NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_refundable` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_deposit_refunds`
--

CREATE TABLE `hostel_deposit_refunds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hostel_collection_detail_id` bigint(20) UNSIGNED NOT NULL COMMENT 'the security_deposit line item being refunded',
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_date` date NOT NULL,
  `reason` varchar(191) DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_fee_configs`
--

CREATE TABLE `hostel_fee_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hostel_category_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year',
  `admission_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monthly_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `security_deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `utility_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maintenance_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_floors`
--

CREATE TABLE `hostel_floors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `building_id` bigint(20) UNSIGNED NOT NULL,
  `floor_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hostel_floors`
--

INSERT INTO `hostel_floors` (`id`, `institute_id`, `branch_id`, `building_id`, `floor_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 1, '2nd Floor', '2026-09-19 03:57:26', '2026-09-19 03:57:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_leaves`
--

CREATE TABLE `hostel_leaves` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `room_member_id` bigint(20) UNSIGNED NOT NULL,
  `leave_start` date NOT NULL,
  `leave_end` date NOT NULL,
  `reason` varchar(191) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_members`
--

CREATE TABLE `hostel_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `hostel_category_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `impersonation_tokens`
--

CREATE TABLE `impersonation_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `target_user_id` bigint(20) UNSIGNED NOT NULL,
  `impersonator_id` bigint(20) UNSIGNED NOT NULL,
  `return_url` varchar(500) DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_ip` varchar(45) DEFAULT NULL,
  `used_ip` varchar(45) DEFAULT NULL,
  `used_user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `impersonation_tokens`
--

INSERT INTO `impersonation_tokens` (`id`, `token_hash`, `institute_id`, `target_user_id`, `impersonator_id`, `return_url`, `expires_at`, `used_at`, `created_ip`, `used_ip`, `used_user_agent`, `created_at`, `updated_at`) VALUES
(1, '2571a7a9cd4d986eff28956576055225b321ad2e9540b2cf3ce83db092f61652', 6, 7, 14, 'https://dahidemy.com/admin/institutes', '2026-09-21 09:11:49', '2026-09-21 09:10:51', '2a02:c206:2238:2644::1', '103.102.159.194', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-21 09:10:49', '2026-09-21 09:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `institutes`
--

CREATE TABLE `institutes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address` varchar(191) DEFAULT NULL,
  `institute_type` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `domain` varchar(100) DEFAULT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `internal_referral_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `logo` varchar(191) DEFAULT NULL,
  `platform` enum('WEB','APP') NOT NULL DEFAULT 'APP',
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `theme_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institutes`
--

INSERT INTO `institutes` (`id`, `owner_id`, `assigned_to`, `name`, `email`, `address`, `institute_type`, `phone`, `domain`, `referral_code`, `internal_referral_enabled`, `logo`, `platform`, `status`, `created_by`, `updated_by`, `deleted_by`, `theme_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, NULL, 'Institute 1', NULL, 'ABC', 'Institute', '1234567890', '127.0.0.1', NULL, 0, NULL, 'WEB', 1, NULL, NULL, NULL, NULL, '2026-08-13 11:53:32', '2026-08-13 11:53:32', NULL),
(3, NULL, NULL, 'Children Academy', 'umairkheshgi@gmail.com', 'House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera', 'School', '03480233673', 'cak.facebook.com', NULL, 0, NULL, 'APP', 1, NULL, NULL, NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53', '2026-09-16 16:19:51'),
(5, NULL, NULL, 'Pegasus School', 'zulfiqarkheshgi@gmail.com', '2nd Floor, Khattak Plaza, Nowshera Cantt:, Khyber Pakhtunkhwa', 'School', '+923139038053', 'pegasus', NULL, 0, '2026-09-14-6aa7df2ac4d30.png', 'APP', 1, NULL, NULL, NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(6, NULL, NULL, 'Pegasus University', 'zulfiqarkheshgihome@gmail.com', '2nd Floor, Khattak Plaza, Nowshera Cantt:, Khyber Pakhtunkhwa', 'University', '+923139038053', 'pegasus-university.dahidemy.com', NULL, 0, NULL, 'APP', 1, NULL, NULL, NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:53:40', NULL),
(11, NULL, NULL, 'Children Academy', 'uape00@gmail.com', 'House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera', 'College', '03480233673', 'abc.dahidemy.com', NULL, 0, NULL, 'APP', 1, NULL, NULL, NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `institute_image_settings`
--

CREATE TABLE `institute_image_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `header_logo_light_theme` varchar(191) DEFAULT NULL,
  `header_logo_dark_theme` varchar(191) DEFAULT NULL,
  `footer_logo_light_theme` varchar(191) DEFAULT NULL,
  `footer_logo_dark_theme` varchar(191) DEFAULT NULL,
  `favicon` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institute_image_settings`
--

INSERT INTO `institute_image_settings` (`id`, `institute_id`, `header_logo_light_theme`, `header_logo_dark_theme`, `footer_logo_light_theme`, `footer_logo_dark_theme`, `favicon`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '2026-09-14-6aa7d4ce34e48.png', '2026-09-14-6aa7d4ce354ee.png', '2026-09-14-6aa7d72cefb55.png', '2026-09-14-6aa7d4ce35c8e.png', '2026-09-14-6aa7d4ce3605b.png', 1, '2026-08-13 11:53:33', '2026-09-14 09:14:52', NULL),
(2, 6, '2026-09-18-6aad92de748f8.png', '2026-09-18-6aad92de75215.png', '2026-09-18-6aad92de75319.png', '2026-09-18-6aad92de753f8.png', '2026-09-18-6aad92de754f1.png', 1, '2026-09-18 17:37:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `institute_image_s_a_a_s_settings`
--

CREATE TABLE `institute_image_s_a_a_s_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `header_logo_light_theme` varchar(191) DEFAULT NULL,
  `header_logo_dark_theme` varchar(191) DEFAULT NULL,
  `footer_logo_light_theme` varchar(191) DEFAULT NULL,
  `footer_logo_dark_theme` varchar(191) DEFAULT NULL,
  `banner_image` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institute_image_s_a_a_s_settings`
--

INSERT INTO `institute_image_s_a_a_s_settings` (`id`, `header_logo_light_theme`, `header_logo_dark_theme`, `footer_logo_light_theme`, `footer_logo_dark_theme`, `banner_image`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2026-09-18-6aad810853746.png', '2026-09-18-6aad810853e23.png', '2026-09-18-6aad810853fa4.png', '2026-09-18-6aad810854198.png', '2026-09-14-6aa786e406914.png', 1, '2026-08-13 11:53:33', '2026-09-18 16:20:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `institute_referral_settings`
--

CREATE TABLE `institute_referral_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_role` varchar(191) NOT NULL,
  `referral_type` enum('refer_institute','refer_member') NOT NULL DEFAULT 'refer_institute',
  `reward_type` enum('fee_discount','points','cash_withdrawal') NOT NULL,
  `reward_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_percentage` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `institute_types`
--

CREATE TABLE `institute_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institute_types`
--

INSERT INTO `institute_types` (`id`, `title`, `image`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'School', 'school.png', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(2, 'College', 'college.png', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 'University', 'university.png', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(4, 'Madrasa', 'madrasa.png', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', '2026-09-18 08:22:59'),
(5, 'Coaching Center', 'coaching.png', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `internal_referrals`
--

CREATE TABLE `internal_referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_user_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_user_type` varchar(191) NOT NULL,
  `referral_code_used` varchar(191) NOT NULL,
  `referred_user_id` bigint(20) UNSIGNED NOT NULL,
  `referred_user_type` varchar(191) NOT NULL,
  `status` enum('enrolled','rewarded','rejected') NOT NULL DEFAULT 'enrolled',
  `reward_type` enum('fee_discount','points','cash_withdrawal') DEFAULT NULL,
  `reward_value` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(191) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `file_path` varchar(191) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `institute_id`, `branch_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Medical Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Vacation Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'Emergency Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'Maternity/Paternity Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'Study Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'Sick Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'Family Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, 'Special Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 1, 1, 'Educational Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(10, 1, 1, 'Bereavement Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(11, 1, 1, 'Religious Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(12, 1, 1, 'Extracurricular Leave', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(13, 3, 3, 'Medical Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(14, 3, 3, 'Vacation Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(15, 3, 3, 'Emergency Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(16, 3, 3, 'Maternity/Paternity Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(17, 3, 3, 'Study Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(18, 3, 3, 'Sick Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(19, 3, 3, 'Family Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(20, 3, 3, 'Special Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(21, 3, 3, 'Educational Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(22, 3, 3, 'Bereavement Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(23, 3, 3, 'Religious Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(24, 3, 3, 'Extracurricular Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(25, 5, 5, 'Medical Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(26, 5, 5, 'Vacation Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(27, 5, 5, 'Emergency Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(28, 5, 5, 'Maternity/Paternity Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(29, 5, 5, 'Study Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(30, 5, 5, 'Sick Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(31, 5, 5, 'Family Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(32, 5, 5, 'Special Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(33, 5, 5, 'Educational Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(34, 5, 5, 'Bereavement Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(35, 5, 5, 'Religious Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(36, 5, 5, 'Extracurricular Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(37, 6, 6, 'Medical Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(38, 6, 6, 'Vacation Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(39, 6, 6, 'Emergency Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(40, 6, 6, 'Maternity/Paternity Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(41, 6, 6, 'Study Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(42, 6, 6, 'Sick Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(43, 6, 6, 'Family Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(44, 6, 6, 'Special Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(45, 6, 6, 'Educational Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(46, 6, 6, 'Bereavement Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(47, 6, 6, 'Religious Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(48, 6, 6, 'Extracurricular Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(49, 11, 11, 'Medical Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(50, 11, 11, 'Vacation Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(51, 11, 11, 'Emergency Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(52, 11, 11, 'Maternity/Paternity Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(53, 11, 11, 'Study Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(54, 11, 11, 'Sick Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(55, 11, 11, 'Family Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(56, 11, 11, 'Special Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(57, 11, 11, 'Educational Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(58, 11, 11, 'Bereavement Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(59, 11, 11, 'Religious Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(60, 11, 11, 'Extracurricular Leave', '2026-09-22 15:43:02', '2026-09-22 15:43:02');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `thumbnail_image` varchar(191) DEFAULT NULL,
  `video_type` varchar(191) DEFAULT NULL,
  `video_url` longtext DEFAULT NULL,
  `embedded_url` longtext DEFAULT NULL,
  `uploaded_video_path` varchar(191) DEFAULT NULL,
  `document_file` varchar(191) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `playback_hours` int(11) NOT NULL DEFAULT 0,
  `playback_minutes` int(11) NOT NULL DEFAULT 0,
  `playback_seconds` int(11) NOT NULL DEFAULT 0,
  `is_scheduled` tinyint(1) NOT NULL DEFAULT 0,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `visibility` enum('none','password','public') NOT NULL DEFAULT 'none',
  `password` varchar(191) DEFAULT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active' COMMENT 'Lesson status: active, inactive, draft',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `institute_id`, `course_id`, `chapter_id`, `title`, `slug`, `description`, `thumbnail_image`, `video_type`, `video_url`, `embedded_url`, `uploaded_video_path`, `document_file`, `attachments`, `priority`, `playback_hours`, `playback_minutes`, `playback_seconds`, `is_scheduled`, `scheduled_at`, `visibility`, `password`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 2, 2, 'Lesson 1', 'lesson-1-Tq0D', NULL, NULL, 'none', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 0, 0, NULL, 'none', NULL, 'active', NULL, '2026-09-17 03:26:09', '2026-09-17 03:26:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `library_fines`
--

CREATE TABLE `library_fines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `book_issue_id` int(11) NOT NULL,
  `library_id` bigint(20) UNSIGNED NOT NULL,
  `fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `lost_book` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `library_members`
--

CREATE TABLE `library_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `library_id` bigint(20) UNSIGNED NOT NULL,
  `member_type` varchar(20) NOT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_assignments`
--

CREATE TABLE `lms_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_id` bigint(20) UNSIGNED NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `title` varchar(191) NOT NULL,
  `description` longtext DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `file_path` varchar(191) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_assignment_results`
--

CREATE TABLE `lms_assignment_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `marks` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `submission_file` varchar(191) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_class_routines`
--

CREATE TABLE `lms_class_routines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `room_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_class_routine_days`
--

CREATE TABLE `lms_class_routine_days` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `routine_id` bigint(20) UNSIGNED NOT NULL,
  `day_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_days`
--

CREATE TABLE `lms_days` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `serial` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_rooms`
--

CREATE TABLE `lms_rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `serial` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lms_zoom_meetings`
--

CREATE TABLE `lms_zoom_meetings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `meeting_id` varchar(191) NOT NULL,
  `topic` text NOT NULL,
  `agenda` text DEFAULT NULL,
  `start_time` varchar(191) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `join_url` longtext NOT NULL,
  `start_url` longtext NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mark_configs`
--

CREATE TABLE `mark_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `mark_config_exam_code_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mark_config_exam_codes`
--

CREATE TABLE `mark_config_exam_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `total_marks` decimal(5,2) NOT NULL DEFAULT 100.00,
  `pass_mark` decimal(5,2) NOT NULL DEFAULT 33.00,
  `acceptance` decimal(5,2) NOT NULL DEFAULT 1.00,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `meal_name` varchar(191) NOT NULL,
  `meal_type` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_entries`
--

CREATE TABLE `meal_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `meal_id` bigint(20) UNSIGNED NOT NULL,
  `meal_price` decimal(8,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `meal_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merit_process_types`
--

CREATE TABLE `merit_process_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(191) NOT NULL,
  `serial` int(11) NOT NULL DEFAULT 1,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `merit_process_types`
--

INSERT INTO `merit_process_types` (`id`, `institute_id`, `branch_id`, `type`, `serial`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Total Mark(Sequential)', 1, NULL, NULL, NULL),
(2, 1, 1, 'Grade Point(Sequential)', 1, NULL, NULL, NULL),
(3, 3, 3, 'Total Mark(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(4, 3, 3, 'Grade Point(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(5, 5, 5, 'Total Mark(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(6, 5, 5, 'Grade Point(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(7, 6, 6, 'Total Mark(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(8, 6, 6, 'Grade Point(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(9, 11, 11, 'Total Mark(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02'),
(10, 11, 11, 'Grade Point(Sequential)', 1, NULL, '2026-09-22 15:43:02', '2026-09-22 15:43:02');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000001_create_institutes_table', 1),
(4, '0001_01_01_000002_create_branches_table', 1),
(5, '0001_01_01_000002_create_jobs_table', 1),
(6, '2014_10_12_100000_create_password_resets_table', 1),
(7, '2018_07_13_155644_create_payment_methods_table', 1),
(8, '2023_10_22_082015_create_accounting_categories_table', 1),
(9, '2023_10_22_082017_create_accounting_groups_table', 1),
(10, '2023_10_22_082056_create_accounting_ledgers_table', 1),
(11, '2023_10_22_082111_create_accounting_funds_table', 1),
(12, '2023_10_23_133635_create_account_transactions_table', 1),
(13, '2023_10_23_133653_create_account_transaction_details_table', 1),
(14, '2023_10_26_042847_create_sms_templates_table', 1),
(15, '2023_10_26_082224_create_phone_book_categories_table', 1),
(16, '2023_10_26_132603_create_phone_books_table', 1),
(17, '2023_11_06_041423_create_fee_heads_table', 1),
(18, '2023_11_06_041430_create_fee_sub_heads_table', 1),
(19, '2023_11_07_133106_create_fee_maps_table', 1),
(20, '2023_11_07_133130_create_fee_map_fee_sub_head_table', 1),
(21, '2023_11_07_133139_create_fee_map_fund_table', 1),
(22, '2023_11_10_122800_create_fees_table', 1),
(23, '2023_11_10_133218_create_absent_fines_table', 1),
(24, '2023_11_12_092104_create_fee_date_configs_table', 1),
(25, '2023_11_13_050817_create_waivers_table', 1),
(26, '2023_11_26_040459_create_sms_purchases_table', 1),
(27, '2023_11_26_051903_create_sms_balances_table', 1),
(28, '2023_12_09_202508_create_student_collections_table', 1),
(29, '2023_12_09_203730_create_student_collection_details_table', 1),
(30, '2023_12_09_204428_create_student_collection_details_sub_heads_table', 1),
(31, '2023_12_11_075037_create_student_waiver_configs_table', 1),
(32, '2024_03_27_214530_create_book_categories_table', 1),
(33, '2024_03_27_214532_create_books_table', 1),
(34, '2024_03_27_2145340_create_book_issues_table', 1),
(35, '2024_03_27_2145347_create_library_fines_table', 1),
(36, '2024_04_27_212419_create_attendance_fines_table', 1),
(37, '2024_05_06_073101_create_academic_years_table', 1),
(38, '2024_05_06_073200_create_shifts_table', 1),
(39, '2024_05_06_073201_create_classes_table', 1),
(40, '2024_05_06_073202_create_student_groups_table', 1),
(41, '2024_05_06_073203_create_sections_table', 1),
(42, '2024_05_06_073205_create_periods_table', 1),
(43, '2024_05_06_073206_create_subjects_table', 1),
(44, '2024_05_06_073207_create_subject_configs_table', 1),
(45, '2024_05_18_195819_create_departments_table', 1),
(46, '2024_05_26_175254_create_attendance_waivers_table', 1),
(47, '2024_06_01_080940_create_settings_table', 1),
(48, '2024_06_01_161632_create_picklists_table', 1),
(49, '2024_09_04_183648_create_permission_tables', 1),
(50, '2024_10_25_124652_create_merit_process_types_table', 1),
(51, '2024_10_25_124653_create_grades_table', 1),
(52, '2024_10_25_124905_create_short_codes_table', 1),
(53, '2024_10_26_074535_create_students_table', 1),
(54, '2024_10_26_074536_create_exams_table', 1),
(55, '2024_10_26_074538_create_class_exams_table', 1),
(56, '2024_11_01_174246_create_user_logs_table', 1),
(57, '2024_11_02_144004_create_exam_codes_table', 1),
(58, '2024_11_03_005102_create_exam_grades_table', 1),
(59, '2024_11_03_161726_create_mark_config_exam_codes_table', 1),
(60, '2024_11_03_161727_create_mark_configs_table', 1),
(61, '2024_11_04_015021_create_grand_final_class_exams_table', 1),
(62, '2024_11_05_013537_create_remark_configs_table', 1),
(63, '2024_11_06_160453_create_exam_marks_table', 1),
(64, '2024_11_09_162259_create_student_categories_table', 1),
(65, '2024_11_09_162261_create_signatures_table', 1),
(66, '2024_11_09_162262_leave_types', 1),
(67, '2024_11_09_162282_create_staffs_table', 1),
(68, '2024_11_09_162284_create_teachers_table', 1),
(69, '2024_11_09_162292_create_student_sessions_table', 1),
(70, '2024_11_11_015344_create_oauth_auth_codes_table', 1),
(71, '2024_11_11_015345_create_oauth_access_tokens_table', 1),
(72, '2024_11_11_015346_create_oauth_refresh_tokens_table', 1),
(73, '2024_11_11_015347_create_oauth_clients_table', 1),
(74, '2024_11_11_015348_create_oauth_personal_access_clients_table', 1),
(75, '2024_11_12_162918_create_student_migrations_table', 1),
(76, '2024_11_12_162920_create_library_members_table', 1),
(77, '2024_11_12_162921_create_student_attendances_table', 1),
(78, '2024_11_12_162922_create_staff_attendances_table', 1),
(79, '2024_11_12_162924_create_sms_logs_table', 1),
(80, '2024_11_12_162932_create_exam_schedules_table', 1),
(81, '2024_11_12_162934_create_exam_attendances_table', 1),
(82, '2024_11_12_162935_create_class_days_table', 1),
(83, '2024_11_12_162936_create_class_routines_table', 1),
(84, '2024_11_12_162941_create_assign_shifts_table', 1),
(85, '2024_11_12_162942_create_class_assigns_table', 1),
(86, '2024_11_12_162943_create_assign_subjects_table', 1),
(87, '2024_11_12_162944_create_syllabus_table', 1),
(88, '2024_11_12_162946_create_assignments_table', 1),
(89, '2024_11_12_162953_create_notices_table', 1),
(90, '2024_11_12_162955_create_user_notices_table', 1),
(91, '2024_11_12_162960_create_events_table', 1),
(92, '2024_11_12_172960_create_contacts_table', 1),
(93, '2024_11_29_014050_create_assignment_submits_table', 1),
(94, '2024_12_15_160450_create_salary_heads_table', 1),
(95, '2024_12_15_160452_create_user_payrolls_table', 1),
(96, '2024_12_15_160454_create_salary_head_user_payrolls_table', 1),
(97, '2024_12_15_160456_create_payslip_salaries_table', 1),
(98, '2024_12_15_160458_create_payslip_salary_heads_table', 1),
(99, '2024_12_15_160460_create_payslip_invoices_table', 1),
(100, '2024_12_15_160462_create_payroll_accounting_mappings_table', 1),
(101, '2024_12_15_160466_create_payments_table', 1),
(102, '2025_01_01_032931_create_class_lessons_table', 1),
(103, '2025_01_01_033554_create_prayers_table', 1),
(104, '2025_01_01_063251_create_behaviors_table', 1),
(105, '2025_01_01_063813_create_gamifications_table', 1),
(106, '2025_01_01_063919_create_resources_table', 1),
(107, '2025_01_11_051038_create_faq_questions_table', 1),
(108, '2025_01_11_051040_create_policies_table', 1),
(109, '2025_01_12_051009_create_academic_images_table', 1),
(110, '2025_01_12_093405_create_about_us_table', 1),
(111, '2025_02_11_041343_create_our_histories_table', 1),
(112, '2025_02_13_044120_create_banners_table', 1),
(113, '2025_02_23_101529_create_testimonials_table', 1),
(114, '2025_02_26_100812_create_course_categories_table', 1),
(115, '2025_02_26_101353_create_courses_table', 1),
(116, '2025_02_26_101354_create_course_features_table', 1),
(117, '2025_02_26_101355_create_course_faqs_table', 1),
(118, '2025_02_27_082122_create_chapters_table', 1),
(119, '2025_02_27_082135_create_lessons_table', 1),
(120, '2025_03_02_062951_create_quizzes_table', 1),
(121, '2025_03_02_063014_create_quiz_attempts_table', 1),
(122, '2025_03_02_063022_create_quiz_results_table', 1),
(123, '2025_03_03_050829_create_pages_table', 1),
(124, '2025_03_12_034430_create_zoom_meetings_table', 1),
(125, '2025_03_16_035921_create_onboardings_table', 1),
(126, '2025_03_17_040437_create_lms_assignments_table', 1),
(127, '2025_03_17_040458_create_lms_assignment_results_table', 1),
(128, '2025_03_18_150112_create_otp_verifications_table', 1),
(129, '2025_03_21_195239_create_why_choose_us_table', 1),
(130, '2025_03_21_213247_create_ready_to_join_us_table', 1),
(131, '2025_03_23_034819_create_payment_requests_table', 1),
(132, '2025_03_25_160046_create_mobile_app_sections_table', 1),
(133, '2025_03_28_083006_create_hostels_table', 1),
(134, '2025_03_28_083008_create_hostel_categories_table', 1),
(135, '2025_03_28_083010_create_hostel_members_table', 1),
(136, '2025_03_28_083015_create_rooms_table', 1),
(137, '2025_03_28_083030_create_room_members_table', 1),
(138, '2025_03_28_083039_create_meals_table', 1),
(139, '2025_03_28_083044_create_meal_plans_table', 1),
(140, '2025_03_28_140712_create_hostel_bills_table', 1),
(141, '2025_03_28_140735_create_meal_entries_table', 1),
(142, '2025_03_29_133534_create_buses_table', 1),
(143, '2025_03_29_133605_create_drivers_table', 1),
(144, '2025_03_29_133739_create_bus_routes_table', 1),
(145, '2025_03_29_133749_create_bus_stops_table', 1),
(146, '2025_03_29_134153_create_transport_members_table', 1),
(147, '2025_03_30_062148_create_parent_models_table', 1),
(148, '2025_04_05_043108_create_plans_table', 1),
(149, '2025_04_05_043132_create_subscriptions_table', 1),
(150, '2025_04_05_043150_create_subscription_items_table', 1),
(151, '2025_04_06_041546_create_device_controls_table', 1),
(152, '2025_04_06_043435_create_student_devices_table', 1),
(153, '2025_04_08_040639_create_payment_histories_table', 1),
(154, '2025_04_10_010805_create_s_a_a_s_faqs_table', 1),
(155, '2025_04_10_010806_create_s_a_a_s_contacts_table', 1),
(156, '2025_04_10_033705_create_subscription_upgrade_requests_table', 1),
(157, '2025_04_20_032512_create_question_bank_classes_table', 1),
(158, '2025_04_20_032529_create_question_bank_groups_table', 1),
(159, '2025_04_20_032530_create_question_categories_table', 1),
(160, '2025_04_20_032538_create_question_bank_subjects_table', 1),
(161, '2025_04_20_032550_create_question_bank_chapters_table', 1),
(162, '2025_04_20_032623_create_question_bank_topics_table', 1),
(163, '2025_04_20_032630_create_question_bank_types_table', 1),
(164, '2025_04_20_032632_create_question_bank_levels_table', 1),
(165, '2025_04_20_032638_create_question_bank_sources_table', 1),
(166, '2025_04_20_032643_create_question_bank_sub_sources_table', 1),
(167, '2025_04_20_032645_create_question_bank_tags_table', 1),
(168, '2025_04_20_032646_create_question_bank_sessions_table', 1),
(169, '2025_04_20_032650_create_question_bank_years_table', 1),
(170, '2025_04_20_040730_create_question_bank_boards_table', 1),
(171, '2025_04_20_040732_create_question_bank_difficulty_levels_table', 1),
(172, '2025_04_20_040733_create_question_bank_tests_table', 1),
(173, '2025_04_20_040734_create_questions_table', 1),
(174, '2025_04_20_040738_create_question_test_table', 1),
(175, '2025_04_20_040738_create_question_type_table', 1),
(176, '2025_04_20_040744_create_question_level_table', 1),
(177, '2025_04_20_040746_create_question_topic_table', 1),
(178, '2025_04_20_040754_create_question_source_table', 1),
(179, '2025_04_20_040756_create_question_sub_source_table', 1),
(180, '2025_04_20_040758_create_question_tag_table', 1),
(181, '2025_04_20_040759_create_question_session_table', 1),
(182, '2025_04_24_061458_create_contents_table', 1),
(183, '2025_04_29_055845_create_s_a_a_s_settings_table', 1),
(184, '2025_04_30_040523_create_s_a_a_s_subscriptions_table', 1),
(185, '2025_05_24_090100_create_lms_zoom_meetings_table', 1),
(186, '2025_06_25_070227_create_languages_table', 1),
(187, '2025_08_09_000001_create_grand_final_settings_table', 1),
(188, '2025_09_20_053933_create_quiz_topics_table', 1),
(189, '2025_10_01_091211_create_themes_table', 1),
(190, '2025_10_22_054455_create_lms_rooms_table', 1),
(191, '2025_10_22_055522_create_lms_days_table', 1),
(192, '2025_10_22_060312_create_lms_class_routines_table', 1),
(193, '2025_10_22_060314_create_class_routine_days_table', 1),
(194, '2025_10_22_060316_create_content_visibility_table', 1),
(195, '2025_10_22_060317_create_enrollments_table', 1),
(196, '2025_11_26_005955_create_institute_image_settings_table', 1),
(197, '2025_11_26_005956_create_institute_image_s_a_a_s_settings_table', 1),
(198, '2026_02_05_152100_create_teacher_signatures_table', 1),
(199, '2026_02_05_152200_create_result_cards_table', 1),
(200, '2026_03_14_065756_create_custom_fields_table', 1),
(201, '2026_03_14_065757_create_custom_field_values_table', 1),
(202, '2026_04_13_133531_create_s_a_a_s_banners_table', 1),
(203, '2026_04_13_133532_create_s_a_a_s_cores_table', 1),
(204, '2026_04_13_133532_create_s_a_a_s_panels_table', 1),
(205, '2026_04_13_133533_create_s_a_a_s_benefits_table', 1),
(206, '2026_04_13_133533_create_s_a_a_s_solutions_table', 1),
(207, '2026_04_13_133534_create_s_a_a_s_feedback_table', 1),
(208, '2026_04_18_064455_create_achievements_table', 1),
(209, '2026_04_18_064455_create_benefits_table', 1),
(210, '2026_04_27_133957_create_s_a_a_s_policies_table', 1),
(211, '2026_04_30_042655_create_institute_types_table', 1),
(212, '2026_05_07_170745_create_home_sections_table', 1),
(213, '2026_05_20_113320_create_admission_forms_table', 1),
(214, '2026_06_24_084823_create_whats_app_settings_table', 1),
(215, '2026_06_24_084824_create_whats_app_templates_table', 1),
(216, '2026_06_24_084826_create_whats_app_logs_table', 1),
(217, '2026_06_25_081110_create_optional_subject_configs_table', 1),
(218, '2026_06_25_081111_create_optional_subject_items_table', 1),
(219, '2026_06_25_081112_create_student_optional_subjects_table', 1),
(220, '2026_07_08_124706_add_reference_and_retry_to_whats_app_logs_table', 1),
(221, '2026_07_08_131011_create_google_meet_meetings_table', 1),
(222, '2026_07_08_132218_create_vehicle_types_and_categories_table', 1),
(223, '2026_07_08_132219_add_enterprise_fields_to_buses_and_drivers_table', 1),
(224, '2026_07_08_132219_create_helpers_table', 1),
(225, '2026_07_08_132220_create_bus_seats_table', 1),
(226, '2026_07_08_132220_create_driver_documents_table', 1),
(227, '2026_07_08_132221_add_seat_and_transport_id_to_transport_members_table', 1),
(228, '2026_07_08_132221_create_vehicle_maintenance_tables', 1),
(229, '2026_07_08_132402_create_transport_collections_table', 1),
(230, '2026_07_08_132402_create_transport_fee_configs_table', 1),
(231, '2026_07_08_133322_add_bus_id_to_bus_routes_table', 1),
(232, '2026_07_08_135710_add_building_floor_to_rooms_table', 1),
(233, '2026_07_08_135710_create_hostel_buildings_and_floors_table', 1),
(234, '2026_07_08_135711_add_allocation_fields_to_room_members_table', 1),
(235, '2026_07_08_135711_create_beds_table', 1),
(236, '2026_07_08_135712_create_hostel_fee_configs_table', 1),
(237, '2026_07_08_135712_create_hostel_leaves_table', 1),
(238, '2026_07_08_135713_create_hostel_collections_table', 1),
(239, '2026_07_08_135713_create_hostel_deposit_refunds_table', 1),
(240, '2026_07_08_155500_add_institute_branch_to_payroll_accounting_mappings_table', 1),
(241, '2026_07_08_160000_add_room_to_class_routines_table', 1),
(242, '2026_07_09_090000_create_certificate_templates_table', 1),
(243, '2026_07_09_090100_create_certificate_issues_table', 1),
(244, '2026_07_09_143145_add_unique_constraint_to_exam_attendances_table', 1),
(245, '2026_07_09_143200_make_group_id_nullable_on_subject_configs_table', 1),
(246, '2026_07_09_143300_add_unique_constraint_to_exam_schedules_table', 1),
(247, '2026_07_09_143310_add_unique_constraint_to_assign_shifts_table', 1),
(248, '2026_07_09_143320_add_unique_constraint_to_class_assigns_table', 1),
(249, '2026_07_09_143330_add_unique_constraint_to_library_members_table', 1),
(250, '2026_08_09_100000_create_ai_conversations_table', 1),
(251, '2026_08_09_100100_create_ai_messages_table', 1),
(252, '2026_08_13_100001_add_result_fields_to_exam_marks_table', 1),
(253, '2026_08_13_100002_create_assessment_domains_table', 1),
(254, '2026_08_13_100003_create_assessment_items_table', 1),
(255, '2026_08_13_100004_create_student_assessments_table', 1),
(256, '2026_08_13_100005_create_result_card_settings_table', 1),
(257, '2026_08_13_100006_create_online_exams_table', 1),
(258, '2026_08_13_100007_create_online_exam_questions_table', 1),
(259, '2026_08_13_100008_create_online_exam_attempts_table', 1),
(260, '2026_08_13_100009_create_online_exam_mark_transfers_table', 1),
(261, '2026_08_13_100010_make_exam_marks_group_id_nullable', 1),
(262, '2026_08_13_170001_add_context_indexes_and_payroll_unique', 1),
(263, '2026_08_13_180001_add_payslip_salaries_unique_constraint', 1),
(264, '2026_08_13_200001_create_system_settings_table', 1),
(265, '2026_08_13_200002_create_demo_restrictions_table', 1),
(266, '2026_08_13_200003_create_system_updates_table', 1),
(267, '2026_08_13_200004_create_system_update_logs_table', 1),
(268, '2026_08_13_200005_create_system_modules_table', 1),
(269, '2026_09_15_000001_backfill_website_design_settings', 2),
(270, '2026_09_15_000001_create_form_builder_forms_table', 2),
(271, '2026_09_15_000002_create_form_builder_fields_table', 2),
(272, '2026_09_15_000003_create_form_builder_submissions_table', 2),
(273, '2026_09_15_000001_add_enrollment_fields_to_events_table', 3),
(274, '2026_09_15_000002_create_event_enrollments_table', 3),
(275, '2026_09_15_000001_create_custom_menus_table', 4),
(276, '2026_09_15_000004_add_applicant_to_sms_logs_user_type', 4),
(277, '2026_09_15_000005_add_review_fields_to_form_builder_submissions', 4),
(278, '2026_09_15_000001_add_integrity_tracking_to_online_exams', 5),
(279, '2026_09_15_000001_create_popups_table', 5),
(280, '2026_09_15_000001_create_result_publications_table', 6),
(281, '2026_09_15_000001_add_payment_request_id_to_enrollments', 7),
(282, '2026_09_16_000001_create_blog_categories_table', 8),
(283, '2026_09_16_000001_create_homepage_sections_table', 8),
(284, '2026_09_16_000002_create_blog_posts_table', 8),
(285, '2026_09_16_000002_create_director_messages_table', 8),
(286, '2026_09_17_000001_make_payment_requests_institute_branch_nullable', 9),
(287, '2026_09_17_000002_add_trial_days_to_plans', 10),
(288, '2026_09_17_000003_add_referral_code_to_institutes_and_users', 11),
(289, '2026_09_17_000004_create_referral_tables', 11),
(290, '2026_09_17_000005_add_percentage_reward_type', 12),
(291, '2026_09_17_000006_create_institute_referral_settings', 12),
(292, '2026_09_17_000007_add_institute_reward_to_referrals', 12),
(293, '2026_09_18_000001_create_withdrawal_requests', 13),
(294, '2026_09_18_000002_add_payout_details_to_withdrawal_requests', 14),
(295, '2026_09_18_000003_create_withdrawal_settings', 15),
(296, '2026_09_19_000001_add_internal_referral_toggle_to_institutes', 16),
(297, '2026_09_19_000002_add_referral_type_to_institute_referral_settings', 17),
(298, '2026_09_19_000003_create_internal_referrals', 17),
(299, '2026_09_21_000001_add_unique_domain_to_institutes_table', 18),
(300, '2026_09_21_000002_create_impersonation_tokens_table', 19),
(301, '2026_09_22_000001_add_max_groups_to_classes', 20),
(302, '2026_09_22_000002_backfill_default_picklists', 20),
(303, '2026_09_22_000003_add_status_to_pages', 21),
(304, '2026_09_22_000004_create_staff_categories', 22),
(305, '2026_09_22_000005_add_conductor_support_to_transport_members', 23),
(306, '2026_09_22_000006_add_school_name_to_student_migrations', 23),
(307, '2026_09_23_000001_add_is_public_to_events', 24),
(308, '2026_09_23_000002_add_letter_type_to_notices', 25);

-- --------------------------------------------------------

--
-- Table structure for table `mobile_app_sections`
--

CREATE TABLE `mobile_app_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `heading` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `feature_one` varchar(191) DEFAULT NULL,
  `feature_two` varchar(191) DEFAULT NULL,
  `feature_three` varchar(191) DEFAULT NULL,
  `play_store_link` varchar(191) DEFAULT NULL,
  `app_store_link` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mobile_app_sections`
--

INSERT INTO `mobile_app_sections` (`id`, `institute_id`, `branch_id`, `title`, `heading`, `description`, `image`, `feature_one`, `feature_two`, `feature_three`, `play_store_link`, `app_store_link`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Stay Connected Anywhere, Anytime!', 'Mobile App', 'With our official School Mobile App, parents and students can:', NULL, 'Check schedules & notifications', 'Receive instant updates', 'Track student progress', 'https://play.google.com/store/apps/details?id=com.example.app', 'https://apps.apple.com/us/app/example-app/id123456789', '2026-09-14 08:45:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(191) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'Modules\\Authentication\\Models\\User', 1),
(1, 'Modules\\Authentication\\Models\\User', 3),
(1, 'Modules\\Authentication\\Models\\User', 4),
(1, 'Modules\\Authentication\\Models\\User', 5),
(1, 'Modules\\Authentication\\Models\\User', 6),
(1, 'Modules\\Authentication\\Models\\User', 7),
(1, 'Modules\\Authentication\\Models\\User', 8),
(1, 'Modules\\Authentication\\Models\\User', 9),
(1, 'Modules\\Authentication\\Models\\User', 10),
(1, 'Modules\\Authentication\\Models\\User', 11),
(1, 'Modules\\Authentication\\Models\\User', 12);

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` text DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'Notice',
  `recipient_staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `display_on_website` tinyint(1) NOT NULL DEFAULT 0,
  `notice` longtext NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `institute_id`, `branch_id`, `title`, `type`, `recipient_staff_id`, `display_on_website`, `notice`, `image`, `date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 'Job Assignment', 'Notice', NULL, 1, 'You Are assigned to teach English to Nursery Class', NULL, '2026-09-17', 7, '2026-09-17 02:26:25', '2026-09-17 02:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` char(36) NOT NULL,
  `name` varchar(191) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('0ea11ae2dc7ee9249e7c04393458c89b4031a0bd6baf816af773cdc011f0a2926aa5675d5e659295', 9, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-17 03:56:37', '2026-09-17 03:56:37', '2026-10-17 05:56:37'),
('1053c913efb8f2a6c5fb466f2656805d19ab38a76c303cba93326c4077a746c6575bd1a2da3bf0d9', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-16 04:46:08', '2026-09-16 04:46:08', '2026-10-16 06:46:08'),
('1b042dadc2f4e609600f80871c6bd96927eabc27138fd16955ed59e19d3caffbc3130b9db1cb95a6', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-18 04:43:21', '2026-09-18 04:43:21', '2026-10-18 06:43:21'),
('1cfd34ed45b7156549cb3abf4e759f1156a5bb1a97c03ab6f0b5eef2af4e8b7c268ff76aaa3cc85a', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 1, '2026-09-18 07:39:29', '2026-09-18 08:26:10', '2026-10-18 09:39:29'),
('1fd23800bdf6607085407848e822fffabf77df788bbb12ca8e705b764bcff71a4155e4bdc8068c56', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-17 09:14:51', '2026-09-17 09:14:51', '2026-10-17 11:14:51'),
('21f8f88aa4accc6c76d606dafff6fe0b367311ba2cf09a8cbf47d8ceaaf7c82eb39d23ff54725a9f', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 09:53:14', '2026-09-14 09:53:14', '2026-10-14 11:53:14'),
('22e5d11b8bd40c5c5c3f99161897e8ed941ddbd3b35249f2f64180a411a2b41a9a4950837738c784', 13, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 1, '2026-09-18 08:31:47', '2026-09-18 08:35:15', '2026-10-18 10:31:47'),
('299dfb24649fd6ffda197f78e42ef355f890a859ea55c6da3428aa98c546631d9dfe5a2c6b31340c', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 07:05:20', '2026-09-14 07:05:20', '2026-10-14 09:05:20'),
('315e4dc2fa4f6faebb3358f82ab63cbde1107d88cac7c611facad185691056d0bb27f642dff957fc', 14, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 1, '2026-09-18 14:32:51', '2026-09-18 15:39:58', '2026-10-18 16:32:51'),
('31f25b5c5824b09af1c3f1edf03b7cbc346276b324c986d45fab9fa7e2ab8dd23dce8236c78d9690', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 14:10:56', '2026-09-14 14:10:56', '2026-10-14 16:10:56'),
('41bcba96d6026efde971e5427e6c69e19012d591932014f0bcfce6a60445d8b80628fe15b6c960d5', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 02:51:42', '2026-09-14 02:51:42', '2026-10-14 04:51:42'),
('48ce61f02a4c71de73053dfdca42bb42675f20bd7737660eedc8f9771946d42bb107e50b6399fdf5', 14, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-21 08:49:41', '2026-09-21 08:49:41', '2026-10-21 10:49:41'),
('609bec651582047beed088a79391e376611ab8627253d482e75386a7bb86b97d7acc2ee5041731ca', 3, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-13 17:32:37', '2026-09-13 17:32:37', '2026-10-13 19:32:37'),
('694cf058d5a72a69ac55d2f0f7f2f84f6ad3b83c575f2f40bd573d789d9b98ab126b3ee695337864', 11, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-17 09:17:20', '2026-09-17 09:17:20', '2026-10-17 11:17:20'),
('69f18d79d61ae772b909d6ef3a203f16813a8bc6ec7545288668048446b77710e76c6342da14a060', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 1, '2026-09-17 08:58:26', '2026-09-17 09:00:55', '2026-10-17 10:58:26'),
('6b08b814ac1e02966f94e0d67b13d67190845cfb0af4ba4f6d3777b26ca23710aa8d98186c2eaf31', 5, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 08:15:55', '2026-09-14 08:15:55', '2026-10-14 10:15:55'),
('6de0d50d4e4bd243222d4a1133653fea285a35e0e8cdca55ddebc5cec5fcb7cb301a78c2f5011402', 8, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-16 16:25:46', '2026-09-16 16:25:46', '2026-10-16 18:25:46'),
('6e15afd71617f63c96cd4721b67761e070a04ed0bb7c1c65f348dd9bd729bd5e4d49e58a9c879bdc', 12, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-17 10:24:24', '2026-09-17 10:24:24', '2026-10-17 12:24:24'),
('72e73a4216ea787cc3d4cbc826e4fd5eb3661953be67e8cd7e02e85e59711abbb29c2a787633f4de', 14, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-19 04:36:37', '2026-09-19 04:36:37', '2026-10-19 06:36:37'),
('7fcecde73e4aa417471c70549d2913349e5e6c1a136df3146d2f4174232c26debbf9065e0cfeb619', 3, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 07:03:02', '2026-09-14 07:03:02', '2026-10-14 09:03:02'),
('80bd68a9137c075593181a3d95ed6b6a5820f21f75f2e51ad360310ab2a9db9f8ceab6fca75dd575', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-17 10:21:23', '2026-09-17 10:21:23', '2026-10-17 12:21:23'),
('8524b1c13c42f36199bdc5f5d2e1dff799633f30ccb80428d48b4e872925ec7fc24878e8869d918f', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-16 16:19:13', '2026-09-16 16:19:13', '2026-10-16 18:19:13'),
('8719b529844412af61f6a2dc30d401095b5baeb0768be1ce03bc7da5be03f461705652341dacfb77', 6, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 09:48:59', '2026-09-14 09:48:59', '2026-10-14 11:48:59'),
('87982f3782edaf3e10fe053ea34eb7593a9da906fadcd6ffca11fd30b7d353ccfca604202f8eb6e5', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-18 04:27:37', '2026-09-18 04:27:37', '2026-10-18 06:27:37'),
('8bfe5e75a1cfc56c9d71c811a795a2ab3e54bef1a6847ac9b703151b8c20b741cd6dc8aed9c92c2d', 4, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 08:06:54', '2026-09-14 08:06:54', '2026-10-14 10:06:54'),
('903efb85a56642ebda41bc793495f2439bd80b630c36ee107009011f51db17a7d79bf6fcc7a83006', 1, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 06:58:24', '2026-09-14 06:58:24', '2026-10-14 08:58:24'),
('98e646fcc40802b53e0a45f66817b9452b814f865ac7bfa462715d1a69a3dc791230f3b01f071f5a', 14, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-19 03:29:21', '2026-09-19 03:29:21', '2026-10-19 05:29:21'),
('9cad4d6eb1a9d3b277048b217088157dac625dfa8c33eb2910d20408c4586f32afdc7caee14695dc', 3, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 07:02:02', '2026-09-14 07:02:02', '2026-10-14 09:02:02'),
('a0771fa9c3c3546f39cd7335a523228d1c3947f8e61f2955c6d0595b57c01094582c01a7de19b790', 3, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 07:02:43', '2026-09-14 07:02:43', '2026-10-14 09:02:43'),
('a539d777176b74de53e884e9014183f78bdc60133b3e0a6fe0aa836ed795be9c36991e632688a153', 14, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-18 11:22:34', '2026-09-18 11:22:34', '2026-10-18 13:22:34'),
('b4cdcc25d892d3bb4b682be71533504862fbfc59d50086d359cc3f97fa802891e31c524ad0134287', 7, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-16 08:09:20', '2026-09-16 08:09:20', '2026-10-16 10:09:20'),
('b650df6f7bc9638f952e14460c98cec7b4b273f79177be0aee63274ce8aac528670f894662f4745b', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 1, '2026-09-13 17:30:13', '2026-09-13 17:31:04', '2026-10-13 19:30:13'),
('bd3f99ec90d5f7d7eedd2519f69b74b0bc427a3f11fee99af80c7aaec4fce1d40aecea8a5f028650', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-15 16:16:35', '2026-09-15 16:16:36', '2026-10-15 18:16:35'),
('be2875eb4e50daaa1cd2c077fb7bfdd33b9afa0809f45dd58fd48217387862e68f1f58f9fc3510fe', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 1, '2026-09-14 09:49:52', '2026-09-14 09:53:03', '2026-10-14 11:49:52'),
('cb3327b688edde106ebe0489712f2b92ee641ccac435c30333736eac724e4ea4debe67cf2ffd4af8', 4, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 09:34:17', '2026-09-14 09:34:17', '2026-10-14 11:34:17'),
('d1c3e477427147814158bb40e98e415c36d07c90fa5e1741eabb63600286d4a1766b83f69586fdad', 10, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-17 09:04:45', '2026-09-17 09:04:45', '2026-10-17 11:04:45'),
('e9cb0a1dd9598db25e672452b894072ddd31cbc08a383012924517674e100f9a3397b73666d8ac37', 14, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-18 15:54:10', '2026-09-18 15:54:10', '2026-10-18 17:54:10'),
('eac24a518a86d6de75a0fb317a56d9fed93a9dc1eb91de252b296a8084a978d3b8e7200244820a2b', 2, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-16 08:18:08', '2026-09-16 08:18:08', '2026-10-16 10:18:08'),
('f0fd2e41512e4aa9fdde7bfd2ef54acdb0853cd7f5c9a1b2626e96cb381eeb5957ab23183a8ee2f0', 1, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', 'app', '[]', 0, '2026-09-14 07:00:45', '2026-09-14 07:00:45', '2026-10-14 09:00:45');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` char(36) NOT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` char(36) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `secret` varchar(100) DEFAULT NULL,
  `provider` varchar(191) DEFAULT NULL,
  `redirect` text NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
('a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', NULL, 'yes', 'ddSr5JWZZVbygT8Sz3RZhwYl6lbuz44Py466SRK6', NULL, 'http://localhost', 1, 0, 0, '2026-08-13 11:56:45', '2026-08-13 11:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 'a27e8aef-13c7-4bbe-8e07-cc6e00a1434e', '2026-08-13 11:56:45', '2026-08-13 11:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) NOT NULL,
  `access_token_id` varchar(100) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onboardings`
--

CREATE TABLE `onboardings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `collected_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`collected_data`)),
  `institute_logo` varchar(191) DEFAULT NULL,
  `user_avatar` varchar(191) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `onboardings`
--

INSERT INTO `onboardings` (`id`, `collected_data`, `institute_logo`, `user_avatar`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"institute_name\":\"Children Academy\",\"institute_email\":\"cak@gmail.com\",\"institute_phone\":\"123456\",\"institute_domain\":\"cak.dahidemy.com\",\"institute_type\":\"University\",\"institute_address\":\"nn\",\"user_name\":\"Umair\",\"user_email\":\"uape00@gmail.com\",\"user_phone\":\"03480233673\",\"password\":\"$2y$12$JVqkDwVxZl2\\/20oC9Q5q5eVmgmAu771PSzYlcnsEmYc4hkEkAF2tm\"}', NULL, NULL, 'approved', NULL, '2026-09-13 17:32:37', '2026-09-13 17:32:37', '2026-09-13 17:32:37', NULL),
(2, '{\"institute_name\":\"Children Academy\",\"institute_email\":\"umairkheshgi@gmail.com\",\"institute_phone\":\"03480233673\",\"institute_domain\":\"cak.facebook.com\",\"institute_type\":\"School\",\"institute_address\":\"House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera\",\"user_name\":\"Umair\",\"user_email\":\"uape33@gmail.com\",\"user_phone\":\"0558901276\",\"password\":\"$2y$12$5S1dKons9Wl6qHHwq1F1i.MP\\/eT3mtFJ5bg1jQ.vGYpO6mTWkWiW6\"}', NULL, NULL, 'approved', NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(3, '{\"institute_name\":\"Children Academy\",\"institute_email\":\"uape01@gmail.com\",\"institute_phone\":\"0558901276\",\"institute_domain\":\"childrenacademy\",\"institute_type\":\"College\",\"institute_address\":\"Floor No 3, Building No 7068, Quddamah Bin Madun\",\"user_name\":\"Umair\",\"user_email\":\"uape01@gmail.com\",\"user_phone\":\"03480233673\",\"password\":\"$2y$12$iTjbK7gQzH7MKR6WNxiv1eVmRreRr4poQXvfSAbwo4tX0NDXom2sG\"}', NULL, NULL, 'approved', NULL, '2026-09-14 08:15:55', '2026-09-14 08:15:55', '2026-09-14 08:15:55', NULL),
(4, '{\"institute_name\":\"Pegasus School\",\"institute_email\":\"zulfiqarkheshgi@gmail.com\",\"institute_phone\":\"+923139038053\",\"institute_domain\":\"pegasus\",\"institute_type\":\"School\",\"institute_address\":\"2nd Floor, Khattak Plaza, Nowshera Cantt:, Khyber Pakhtunkhwa\",\"user_name\":\"Sardar Zulfiqar Ali Shah\",\"user_email\":\"zulfiqarkheshgi@gmail.com\",\"user_phone\":\"+923139038053\",\"password\":\"$2y$12$kWrlAC8JVfyaNysr6F9Bx.z0eFuj.OOmHE0bobP68hRTm6Pdg0.6W\"}', '2026-09-14-6aa7df2ac4d30.png', '2026-09-14-6aa7df2ac873d.png', 'approved', NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(5, '{\"institute_name\":\"Pegasus University\",\"institute_email\":\"zulfiqarkheshgihome@gmail.com\",\"institute_phone\":\"+923191122111\",\"institute_domain\":\"pegasus-university\",\"institute_type\":\"University\",\"institute_address\":\"2nd Floor, Khattak Plaza, Nowshera Cantt:, Khyber Pakhtunkhwa\",\"user_name\":\"Sardar Zulfiqar Ali Shah\",\"user_email\":\"zulfiqarkheshgihome@gmail.com\",\"user_phone\":\"+923139038053\",\"password\":\"$2y$12$3KbyKL\\/55SNd3kxpwBAMyeRFYWmC46Pu0lsCazXTgmfwRE06ofZCe\"}', NULL, NULL, 'approved', NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(6, '{\"institute_name\":\"Children Academy\",\"institute_email\":\"uape02@gmail.com\",\"institute_phone\":\"0558901276\",\"institute_domain\":\"cak\",\"institute_type\":\"University\",\"institute_address\":\"Floor No 3, Building No 7068, Quddamah Bin Madun\",\"user_name\":\"Umair\",\"user_email\":\"uape02@gmail.com\",\"user_phone\":\"03480233673\",\"password\":\"$2y$12$dgmmztBGC2eGpnd5DXNv1O.MmDRAOj\\/s50Hg.jR1PhhOPXJHnGK1K\"}', NULL, NULL, 'approved', NULL, '2026-09-16 16:25:46', '2026-09-16 16:25:46', '2026-09-16 16:25:46', NULL),
(7, '{\"institute_name\":\"abc\",\"institute_email\":\"uape33@gmail.com\",\"institute_phone\":\"05589012\",\"institute_domain\":\"abc\",\"institute_type\":\"School\",\"institute_address\":\"Floor No 3, Building No 7068, Quddamah Bin Madun\",\"user_name\":\"ALI\",\"user_email\":\"012ansr@gmail.com\",\"user_phone\":\"12345648\",\"password\":\"$2y$12$cCiEJPsPZBS3Qbv7n54ov.WEJTwvcfgs\\/eFNDLkmKW3tgLqJf5rj.\"}', NULL, NULL, 'approved', NULL, '2026-09-17 03:56:36', '2026-09-17 03:56:36', '2026-09-17 03:56:36', NULL),
(8, '{\"institute_name\":\"abc.\",\"institute_email\":\"uape33@gmail.com\",\"institute_phone\":\"05589012\",\"institute_domain\":\"abc\",\"institute_type\":\"College\",\"institute_address\":\"Floor No 3, Building No 7068, Quddamah Bin Madun\",\"user_name\":\"Umair\",\"user_email\":\"uape33@gmail.com\",\"user_phone\":\"0558901276\",\"password\":\"$2y$12$aY2vrXSG6Nu.Z3pOzCvJi.Mak6cuWYzRSgBnNBKn9Ien4qQj8qxRO\"}', NULL, NULL, 'approved', NULL, '2026-09-17 09:04:45', '2026-09-17 09:04:45', '2026-09-17 09:04:45', NULL),
(9, '{\"institute_name\":\"abc\",\"institute_email\":\"uape00@gmail.com\",\"institute_phone\":\"03480233673\",\"institute_domain\":\"abc\",\"institute_type\":\"School\",\"institute_address\":\"House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera\",\"user_name\":\"Umair\",\"user_email\":\"uape00@gmail.com\",\"user_phone\":\"0558901276\",\"password\":\"$2y$12$.l9ERUbIyWt4EG0nKpUuqeZe3BXhejyNvjldjSewdN0F1fzGBkPQi\"}', NULL, NULL, 'approved', NULL, '2026-09-17 09:17:19', '2026-09-17 09:17:19', '2026-09-17 09:17:19', NULL),
(10, '{\"institute_name\":\"Children Academy\",\"institute_email\":\"uape33@gmail.com\",\"institute_phone\":\"0558901276\",\"institute_domain\":\"cak\",\"institute_type\":\"School\",\"institute_address\":\"Floor No 3, Building No 7068, Quddamah Bin Madun\",\"plan_id\":\"2\",\"user_name\":\"Umair\",\"user_email\":\"uape33@gmail.com\",\"user_phone\":\"0558901276\",\"password\":\"$2y$12$I5VsETKkMadk6XbvDsDh4u26095PQYloarAUt4uA6VIxgfvfKfGzy\"}', NULL, NULL, 'pending', NULL, NULL, '2026-09-17 10:03:03', '2026-09-17 10:03:03', NULL),
(11, '{\"institute_name\":\"cak\",\"institute_email\":\"uape00@gmail.com\",\"institute_phone\":\"03480233673\",\"institute_domain\":\"abc\",\"institute_type\":null,\"institute_address\":\"House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera\",\"plan_id\":\"2\",\"user_name\":\"Umair\",\"user_email\":\"uape00@gmail.com\",\"user_phone\":\"03480233673\",\"password\":\"$2y$12$Mq4dfWowtvmqTzvh8D9sx.HGNl1FML\\/4xQUkSKDVtSi96C7bqWwh2\"}', NULL, NULL, 'pending', NULL, NULL, '2026-09-17 10:22:31', '2026-09-17 10:22:31', NULL),
(12, '{\"institute_name\":\"Children Academy\",\"institute_email\":\"uape00@gmail.com\",\"institute_phone\":\"03480233673\",\"institute_domain\":\"abc\",\"institute_type\":\"College\",\"institute_address\":\"House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera\",\"plan_id\":\"1\",\"user_name\":\"admin\",\"user_email\":\"uape00@gmail.com\",\"user_phone\":\"03480233673\",\"password\":\"$2y$12$597vFkhK0pJZH5ubtKvqJ.m6QRSSPPvEJot.2z42dG6Nd6Rsf9cgW\"}', NULL, NULL, 'approved', NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `online_exams`
--

CREATE TABLE `online_exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `duration_minutes` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `total_questions` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_marks` decimal(8,2) NOT NULL DEFAULT 0.00,
  `pass_marks` decimal(8,2) NOT NULL DEFAULT 0.00,
  `attempt_limit` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `negative_marking` tinyint(1) NOT NULL DEFAULT 0,
  `negative_marks_per_wrong` decimal(6,2) NOT NULL DEFAULT 0.00,
  `random_questions` tinyint(1) NOT NULL DEFAULT 0,
  `random_options` tinyint(1) NOT NULL DEFAULT 0,
  `conversion_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `online_full_marks` decimal(8,2) DEFAULT NULL,
  `academic_full_marks` decimal(8,2) DEFAULT NULL,
  `transfer_policy` varchar(20) NOT NULL DEFAULT 'ask',
  `mark_component_slot` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `proctoring_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `max_violations_before_auto_submit` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `online_exam_attempts`
--

CREATE TABLE `online_exam_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `online_exam_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `integrity_violations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`integrity_violations`)),
  `violation_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `score` decimal(8,2) NOT NULL DEFAULT 0.00,
  `converted_score` decimal(8,2) DEFAULT NULL,
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `incorrect_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `skipped_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'started',
  `started_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `time_taken` int(10) UNSIGNED DEFAULT NULL,
  `is_passed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `online_exam_mark_transfers`
--

CREATE TABLE `online_exam_mark_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `online_exam_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `attempt_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exam_mark_id` bigint(20) UNSIGNED DEFAULT NULL,
  `online_score` decimal(8,2) NOT NULL DEFAULT 0.00,
  `online_full_marks` decimal(8,2) NOT NULL DEFAULT 0.00,
  `transferred_marks` decimal(8,2) NOT NULL DEFAULT 0.00,
  `academic_full_marks` decimal(8,2) NOT NULL DEFAULT 0.00,
  `converted` tinyint(1) NOT NULL DEFAULT 0,
  `action` varchar(20) NOT NULL DEFAULT 'created',
  `transferred_by` bigint(20) UNSIGNED DEFAULT NULL,
  `transferred_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `online_exam_questions`
--

CREATE TABLE `online_exam_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `online_exam_id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'multiple_choice',
  `question_text` longtext NOT NULL,
  `image` varchar(191) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`correct_answer`)),
  `explanation` longtext DEFAULT NULL,
  `marks` decimal(6,2) NOT NULL DEFAULT 1.00,
  `difficulty` varchar(30) DEFAULT NULL,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `optional_subject_configs`
--

CREATE TABLE `optional_subject_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `total_mark` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `selection_limit` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `optional_subject_items`
--

CREATE TABLE `optional_subject_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `optional_subject_config_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `mark` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `phone` varchar(191) NOT NULL,
  `otp` varchar(191) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `our_histories`
--

CREATE TABLE `our_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `year` year(4) NOT NULL,
  `title` varchar(255) NOT NULL,
  `descriptions` text DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `type` varchar(191) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `meta_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta_data`)),
  `seo_meta_keywords` text DEFAULT NULL,
  `seo_meta_description` text DEFAULT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `institute_id`, `branch_id`, `title`, `slug`, `type`, `content`, `status`, `meta_data`, `seo_meta_keywords`, `seo_meta_description`, `author_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 'Good', 'good', NULL, '<p>sfjlksjfljslfjljfsldjfls zcxcm,nvodfjiogisjvfklj</p>', 1, NULL, NULL, NULL, 7, '2026-09-18 12:57:18', '2026-09-18 12:57:18', NULL),
(2, 6, 6, 'Better', 'better', NULL, '<p>سدکفجسکلدفجلدسجفلجسلجل</p>', 1, NULL, NULL, NULL, 7, '2026-09-18 13:02:44', '2026-09-18 13:02:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parent_models`
--

CREATE TABLE `parent_models` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_name` varchar(50) NOT NULL,
  `parent_profession` varchar(100) DEFAULT NULL,
  `parent_phone` varchar(25) DEFAULT NULL,
  `present_address` longtext DEFAULT NULL,
  `permanent_address` longtext DEFAULT NULL,
  `access_key` varchar(25) DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `year` varchar(191) NOT NULL,
  `month` varchar(191) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('salary','due','advanced','advanced_return') NOT NULL DEFAULT 'due',
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_by` bigint(20) UNSIGNED DEFAULT NULL,
  `note` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_histories`
--

CREATE TABLE `payment_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(32) NOT NULL,
  `invoice_title` varchar(191) NOT NULL DEFAULT 'Monthly fee',
  `payable` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Paid','Unpaid','Partial') NOT NULL DEFAULT 'Unpaid',
  `date_issued` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `provider_transaction_id` varchar(191) DEFAULT NULL,
  `mobile_payment_provider` varchar(191) DEFAULT NULL,
  `confirmation_status` enum('Pending','Confirmed','Failed') NOT NULL DEFAULT 'Pending',
  `attachments` varchar(191) DEFAULT NULL,
  `internal_notes` longtext DEFAULT NULL,
  `send_sms` tinyint(1) NOT NULL DEFAULT 0,
  `send_email` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `id` char(36) NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payer_id` varchar(64) DEFAULT NULL,
  `receiver_id` varchar(64) DEFAULT NULL,
  `payment_amount` decimal(24,2) NOT NULL DEFAULT 0.00,
  `gateway_callback_url` varchar(191) DEFAULT NULL,
  `success_hook` varchar(100) DEFAULT NULL,
  `failure_hook` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `currency_code` varchar(20) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` varchar(50) DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_data`)),
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `payer_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payer_information`)),
  `external_redirect_link` varchar(255) DEFAULT NULL,
  `receiver_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receiver_information`)),
  `attribute_id` varchar(64) DEFAULT NULL,
  `attribute` varchar(255) DEFAULT NULL,
  `payment_platform` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_requests`
--

INSERT INTO `payment_requests` (`id`, `institute_id`, `branch_id`, `payer_id`, `receiver_id`, `payment_amount`, `gateway_callback_url`, `success_hook`, `failure_hook`, `transaction_id`, `currency_code`, `payment_method`, `payment_type`, `name`, `phone`, `email`, `plan_id`, `notes`, `additional_data`, `is_paid`, `payer_information`, `external_redirect_link`, `receiver_information`, `attribute_id`, `attribute`, `payment_platform`, `created_at`, `updated_at`) VALUES
('0e26dae8-a989-4fd1-8ecb-6f8e4cb2f259', NULL, NULL, NULL, NULL, 99.00, NULL, 'complete_onboarding', NULL, NULL, 'usd', NULL, 'subscription_payment', NULL, NULL, NULL, 2, 'Institute onboarding - Standard', '{\"onboarding_id\":11}', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-17 10:22:32', '2026-09-17 10:22:32'),
('1f5b2c26-3bbe-4a27-8654-02299dd82d64', 11, NULL, NULL, NULL, 299.00, NULL, 'complete_subscription_upgrade', NULL, NULL, 'usd', NULL, 'subscription_payment', NULL, NULL, NULL, 3, 'Subscription upgrade - Professional', '{\"institute_id\":11,\"plan_id\":3}', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-17 10:44:58', '2026-09-17 10:44:58'),
('b14c3db1-bd70-432e-bb41-f14511fe2671', 11, NULL, NULL, NULL, 99.00, NULL, 'complete_subscription_upgrade', NULL, 'pi_3UGef64t4OQ7Rpdi1vvfhZpN', 'usd', 'stripe', 'subscription_payment', NULL, NULL, NULL, 2, 'Subscription upgrade - Standard', '{\"institute_id\":11,\"plan_id\":2}', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-17 10:43:11', '2026-09-17 10:43:39'),
('faafba40-507e-4121-a9e8-9c6447947bd9', NULL, NULL, NULL, NULL, 99.00, NULL, 'complete_onboarding', NULL, NULL, 'usd', NULL, 'subscription_payment', NULL, NULL, NULL, 2, 'Institute onboarding - Standard', '{\"onboarding_id\":10}', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-17 10:03:03', '2026-09-17 10:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_accounting_mappings`
--

CREATE TABLE `payroll_accounting_mappings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED NOT NULL,
  `fund_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_accounting_mappings`
--

INSERT INTO `payroll_accounting_mappings` (`id`, `institute_id`, `branch_id`, `ledger_id`, `fund_id`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 55, 1, '2026-08-13 11:53:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payslip_invoices`
--

CREATE TABLE `payslip_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL COMMENT 'custom generate id',
  `payslip_salary_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslip_salaries`
--

CREATE TABLE `payslip_salaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `year` varchar(191) NOT NULL,
  `month` varchar(191) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_paid` enum('0','1') NOT NULL DEFAULT '0',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslip_salary_heads`
--

CREATE TABLE `payslip_salary_heads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_payroll_id` bigint(20) UNSIGNED NOT NULL,
  `salary_head_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `periods`
--

CREATE TABLE `periods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `serial_no` int(10) UNSIGNED DEFAULT NULL,
  `period` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `periods`
--

INSERT INTO `periods` (`id`, `institute_id`, `branch_id`, `serial_no`, `period`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 1, 'English', '2026-09-16 10:49:07', '2026-09-16 10:49:07', NULL),
(2, 6, 6, 2, 'Urdu', '2026-09-16 10:49:28', '2026-09-16 10:49:28', NULL),
(3, 6, 6, 3, 'Islamiat', '2026-09-16 10:49:43', '2026-09-16 10:49:43', NULL),
(4, 6, 6, 4, 'Pakistan Study', '2026-09-17 10:10:12', '2026-09-17 10:10:12', NULL),
(5, 11, 11, 15, '52', '2026-09-21 13:03:26', '2026-09-21 13:03:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'dashboard', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(2, 'academic_configuration_academic_session', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(3, 'academic_configuration_shifts', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(4, 'academic_configuration_classes', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(5, 'academic_configuration_sections', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(6, 'academic_configuration_groups', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(7, 'academic_configuration_periods', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(8, 'academic_configuration_subjects', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(9, 'academic_configuration_subject_config', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(10, 'academic_configuration_exams', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(11, 'academic_configuration_student_categories', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(12, 'academic_configuration_departments', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(13, 'academic_configuration_picklist', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(14, 'academic_configuration_principal_signatures', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(15, 'student_information_student_index', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(16, 'student_information_student_create', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(17, 'student_information_student_edit', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(18, 'student_information_student_delete', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(19, 'student_information_student_status_change', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(20, 'student_information_student_migration', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(21, 'student_information_student_pullback', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(22, 'student_information_migration_list', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(23, 'student_information_all_student_list', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(24, 'staff_information_teachers_index', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(25, 'staff_information_teachers_create', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(26, 'staff_information_teachers_edit', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(27, 'staff_information_teachers_delete', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(28, 'staff_information_teachers_status_change', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(29, 'staff_information_staffs_index', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(30, 'staff_information_staffs_create', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(31, 'staff_information_staffs_edit', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(32, 'staff_information_staffs_delete', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(33, 'staff_information_staff_attendance_view', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(34, 'staff_information_staff_attendance_create', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(35, 'staff_information_staffs_report', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(36, 'student_attendance_student_attendance_index', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(37, 'student_attendance_student_attendance_create', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(38, 'student_attendance_exam_attendance_index', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(39, 'student_attendance_exam_attendance_schedule', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(40, 'student_attendance_student_attendance_report', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(41, 'student_attendance_student_absent_report', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(42, 'student_qr_code_attendance_index', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(43, 'fees_management_startup', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(44, 'fees_management_mapping', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(45, 'fees_management_amount_config', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(46, 'fees_management_date_config', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(47, 'fees_management_fine_waiver', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(48, 'fees_management_waiver', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(49, 'fees_management_waiver_config', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(50, 'fees_management_smart_collection', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(51, 'fees_management_paid_info', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(52, 'fees_management_unpaid_info', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(53, 'accounting_management_ledgers', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(54, 'accounting_management_funds', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(55, 'accounting_management_categories', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(56, 'accounting_management_groups', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(57, 'accounting_management_payment', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(58, 'accounting_management_receipt', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(59, 'accounting_management_contra', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(60, 'accounting_management_journal', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(61, 'accounting_management_fund_transfer', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(62, 'accounting_management_chart_of_accounts', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(63, 'accounting_report_balance_sheet', 'web', '2026-08-13 11:53:32', '2026-08-13 11:53:32'),
(64, 'accounting_report_trial_balance', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(65, 'accounting_report_cash_flow_statement', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(66, 'accounting_report_cash_flow_details', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(67, 'accounting_report_cash_book_account', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(68, 'accounting_report_ledger_book_account', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(69, 'accounting_report_income_statement', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(70, 'accounting_report_cash_summary', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(71, 'payroll_payroll_start_up', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(72, 'payroll_payroll_mapping', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(73, 'payroll_payroll_assign', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(74, 'payroll_salary_slip', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(75, 'payroll_salary', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(76, 'payroll_due', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(77, 'payroll_advance', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(78, 'payroll_return_advance_payment', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(79, 'payroll_salary_statement', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(80, 'payroll_payment_info', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(81, 'routine_management_syllabuses', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(82, 'routine_management_assignments', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(83, 'routine_management_class_routine', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(84, 'routine_management_exam_routine', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(85, 'routine_management_admit_seat_plan', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(86, 'library_management_book_category', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(87, 'library_management_books', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(88, 'library_management_members', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(89, 'library_management_book_issue_search', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(90, 'library_management_book_issue_report', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(91, 'library_management_book_barcode_print', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(92, 'exam_module_exam_start_up', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(93, 'exam_module_mark_config', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(94, 'exam_module_remarks_config', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(95, 'exam_module_mark_input', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(96, 'exam_module_exam_result', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(97, 'exam_module_print_result', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(98, 'exam_module_view_broad_sheet', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(99, 'exam_module_print_broad_sheet', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(100, 'exam_module_manage_online_exams', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(101, 'exam_module_manage_online_questions', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(102, 'exam_module_transfer_online_marks', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(103, 'exam_module_manage_assessment_domains', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(104, 'layouts_certificates_general', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(105, 'layouts_certificates_testimonial', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(106, 'layouts_certificates_attendance_certificate', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(107, 'layouts_certificates_hsc_recommendation_letter', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(108, 'layouts_certificates_transfer', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(109, 'layouts_certificates_abroad_letter', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(110, 'layouts_certificates_character', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(111, 'layouts_certificates_study', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(112, 'layouts_certificates_bonafide', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(113, 'layouts_certificates_migration', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(114, 'certificate_templates_manage', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(115, 'id_card_student', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(116, 'id_card_teacher', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(117, 'id_card_staff', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(118, 'sms_notifications_templates', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(119, 'sms_notifications_phone_book_category', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(120, 'sms_notifications_phone_book', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(121, 'sms_notifications_sent_sms', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(122, 'sms_notifications_purchase_sms', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(123, 'sms_notifications_sms_report', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(124, 'assign_shift', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(125, 'assign_subject', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(126, 'assign_class', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(127, 'notice', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(128, 'event', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(129, 'contact_message', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(130, 'user_activities', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(131, 'system_settings', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(132, 'database_backup', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(133, 'branches', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(134, 'zoom_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(135, 'zoom_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(136, 'zoom_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(137, 'zoom_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(138, 'payment_gateway_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(139, 'payment_gateway_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(140, 'payment_gateway_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(141, 'payment_gateway_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(142, 'role_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(143, 'role_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(144, 'role_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(145, 'role_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(146, 'user_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(147, 'user_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(148, 'user_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(149, 'user_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(150, 'cms_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(151, 'cms_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(152, 'cms_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(153, 'cms_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(154, 'payment_method_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(155, 'payment_method_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(156, 'payment_method_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(157, 'payment_method_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(158, 'whats_app_setting_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(159, 'whats_app_setting_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(160, 'whats_app_setting_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(161, 'whats_app_setting_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(162, 'whats_app_template_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(163, 'whats_app_template_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(164, 'whats_app_template_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(165, 'whats_app_template_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(166, 'whats_app_log_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(167, 'whats_app_log_export', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(168, 'hostel_panel', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(169, 'transport_panel', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(170, 'vehicle_type_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(171, 'vehicle_type_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(172, 'vehicle_type_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(173, 'vehicle_type_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(174, 'vehicle_category_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(175, 'vehicle_category_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(176, 'vehicle_category_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(177, 'vehicle_category_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(178, 'helper_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(179, 'helper_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(180, 'helper_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(181, 'helper_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(182, 'transport_collection_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(183, 'transport_collection_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(184, 'transport_report_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(185, 'transport_report_export', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(186, 'hostel_building_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(187, 'hostel_building_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(188, 'hostel_building_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(189, 'hostel_building_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(190, 'hostel_floor_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(191, 'hostel_floor_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(192, 'hostel_floor_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(193, 'hostel_floor_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(194, 'bed_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(195, 'bed_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(196, 'bed_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(197, 'bed_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(198, 'hostel_leave_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(199, 'hostel_leave_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(200, 'hostel_leave_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(201, 'hostel_leave_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(202, 'hostel_collection_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(203, 'hostel_collection_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(204, 'hostel_report_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(205, 'hostel_report_export', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(206, 'google_meet_panel', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(207, 'google_meet_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(208, 'google_meet_create', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(209, 'google_meet_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(210, 'google_meet_delete', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(211, 'google_meet_cancel', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(212, 'google_meet_duplicate', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(213, 'google_meet_export', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(214, 'google_meet_setting_update', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(215, 'ai_chat_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(216, 'ai_content_writer_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(217, 'ai_insights_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(218, 'ai_settings_index', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(219, 'system.update.view', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(220, 'system.update.upload', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(221, 'system.update.install', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(222, 'system.update.rollback', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(223, 'system.update.history', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(224, 'system.module.view', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(225, 'system.module.enable', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(226, 'system.module.disable', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(227, 'system.demo.manage', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(228, 'system.settings.manage', 'web', '2026-08-13 11:53:33', '2026-08-13 11:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `phone_books`
--

CREATE TABLE `phone_books` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `phone` varchar(191) NOT NULL,
  `category_id` varchar(191) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phone_book_categories`
--

CREATE TABLE `phone_book_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phone_book_categories`
--

INSERT INTO `phone_book_categories` (`id`, `institute_id`, `branch_id`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Student', 'Student', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Teacher', 'Teacher', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'Family', 'Family', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'VIP', 'VIP', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'Director', 'Director', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'Shareholder', 'Shareholder', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'Other School Teacher', 'Other School Teacher', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, 'Chairman', 'Chairman', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 6, 6, 'Teachers\' Phone Book', 'Teachers\' Phone Book Record', 1, '2026-09-17 02:29:14', '2026-09-17 02:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `picklists`
--

CREATE TABLE `picklists` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `slug` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `picklists`
--

INSERT INTO `picklists` (`id`, `institute_id`, `branch_id`, `type`, `value`, `slug`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Religion', 'Islam', 'islam', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(2, 1, 1, 'Religion', 'Christianity', 'christianity', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 1, 1, 'Religion', 'Hinduism', 'hinduism', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(4, 1, 1, 'Religion', 'Buddhism', 'buddhism', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(5, 1, 1, 'Religion', 'Others', 'others', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(6, 1, 1, 'Designation', 'Department Head', 'department-head', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(7, 1, 1, 'Designation', 'Lecturer', 'lecturer', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(8, 1, 1, 'Designation', 'Associate Professor', 'associate-professor', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(9, 1, 1, 'Designation', 'Professor', 'professor', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(10, 1, 1, 'Designation', 'Principal', 'principal', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(11, 1, 1, 'Designation', 'Director of administration and student guidance', 'director-of-administration-and-student-guidance', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(12, 1, 1, 'Staff Designation', 'Accounts Office', 'accounts-office', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(13, 1, 1, 'Staff Designation', 'Exam Office', 'exam-office', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(14, 1, 1, 'Staff Designation', 'IT Office', 'it-office', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(15, 1, 1, 'Staff Designation', 'Library', 'library', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(16, 1, 1, 'Staff Designation', 'Storekeeper', 'storekeeper', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(17, 1, 1, 'Staff Designation', 'Lab Assistant', 'lab-assistant', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(18, 1, 1, 'Staff Designation', 'Maintenance', 'maintenance', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(19, 1, 1, 'Staff Designation', 'Holy Cross Fathers Work Program', 'holy-cross-fathers-work-program', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(20, 1, 1, 'Staff Designation', 'Academic Office', 'academic-office', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(21, 3, 3, 'Designation', 'Department Head', 'department-head', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(22, 3, 3, 'Designation', 'Lecturer', 'lecturer', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(23, 3, 3, 'Designation', 'Associate Professor', 'associate-professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(24, 3, 3, 'Designation', 'Professor', 'professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(25, 3, 3, 'Designation', 'Principal', 'principal', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(26, 3, 3, 'Designation', 'Director of administration and student guidance', 'director-of-administration-and-student-guidance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(27, 3, 3, 'Staff Designation', 'Accounts Office', 'accounts-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(28, 3, 3, 'Staff Designation', 'Exam Office', 'exam-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(29, 3, 3, 'Staff Designation', 'IT Office', 'it-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(30, 3, 3, 'Staff Designation', 'Library', 'library', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(31, 3, 3, 'Staff Designation', 'Storekeeper', 'storekeeper', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(32, 3, 3, 'Staff Designation', 'Lab Assistant', 'lab-assistant', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(33, 3, 3, 'Staff Designation', 'Maintenance', 'maintenance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(34, 3, 3, 'Staff Designation', 'Academic Office', 'academic-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(35, 3, 3, 'Religion', 'Islam', 'islam', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(36, 3, 3, 'Religion', 'Christianity', 'christianity', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(37, 3, 3, 'Religion', 'Hinduism', 'hinduism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(38, 3, 3, 'Religion', 'Buddhism', 'buddhism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(39, 3, 3, 'Religion', 'Others', 'others', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(40, 5, 5, 'Designation', 'Department Head', 'department-head', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(41, 5, 5, 'Designation', 'Lecturer', 'lecturer', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(42, 5, 5, 'Designation', 'Associate Professor', 'associate-professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(43, 5, 5, 'Designation', 'Professor', 'professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(44, 5, 5, 'Designation', 'Principal', 'principal', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(45, 5, 5, 'Designation', 'Director of administration and student guidance', 'director-of-administration-and-student-guidance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(46, 5, 5, 'Staff Designation', 'Accounts Office', 'accounts-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(47, 5, 5, 'Staff Designation', 'Exam Office', 'exam-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(48, 5, 5, 'Staff Designation', 'IT Office', 'it-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(49, 5, 5, 'Staff Designation', 'Library', 'library', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(50, 5, 5, 'Staff Designation', 'Storekeeper', 'storekeeper', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(51, 5, 5, 'Staff Designation', 'Lab Assistant', 'lab-assistant', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(52, 5, 5, 'Staff Designation', 'Maintenance', 'maintenance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(53, 5, 5, 'Staff Designation', 'Academic Office', 'academic-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(54, 5, 5, 'Religion', 'Islam', 'islam', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(55, 5, 5, 'Religion', 'Christianity', 'christianity', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(56, 5, 5, 'Religion', 'Hinduism', 'hinduism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(57, 5, 5, 'Religion', 'Buddhism', 'buddhism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(58, 5, 5, 'Religion', 'Others', 'others', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(59, 6, 6, 'Designation', 'Department Head', 'department-head', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(60, 6, 6, 'Designation', 'Lecturer', 'lecturer', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(61, 6, 6, 'Designation', 'Associate Professor', 'associate-professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(62, 6, 6, 'Designation', 'Professor', 'professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(63, 6, 6, 'Designation', 'Principal', 'principal', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(64, 6, 6, 'Designation', 'Director of administration and student guidance', 'director-of-administration-and-student-guidance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(65, 6, 6, 'Staff Designation', 'Accounts Office', 'accounts-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(66, 6, 6, 'Staff Designation', 'Exam Office', 'exam-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(67, 6, 6, 'Staff Designation', 'IT Office', 'it-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(68, 6, 6, 'Staff Designation', 'Library', 'library', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(69, 6, 6, 'Staff Designation', 'Storekeeper', 'storekeeper', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(70, 6, 6, 'Staff Designation', 'Lab Assistant', 'lab-assistant', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(71, 6, 6, 'Staff Designation', 'Maintenance', 'maintenance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(72, 6, 6, 'Staff Designation', 'Academic Office', 'academic-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(73, 6, 6, 'Religion', 'Islam', 'islam', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(74, 6, 6, 'Religion', 'Christianity', 'christianity', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(75, 6, 6, 'Religion', 'Hinduism', 'hinduism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(76, 6, 6, 'Religion', 'Buddhism', 'buddhism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(77, 6, 6, 'Religion', 'Others', 'others', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(78, 11, 11, 'Designation', 'Department Head', 'department-head', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(79, 11, 11, 'Designation', 'Lecturer', 'lecturer', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(80, 11, 11, 'Designation', 'Associate Professor', 'associate-professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(81, 11, 11, 'Designation', 'Professor', 'professor', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(82, 11, 11, 'Designation', 'Principal', 'principal', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(83, 11, 11, 'Designation', 'Director of administration and student guidance', 'director-of-administration-and-student-guidance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(84, 11, 11, 'Staff Designation', 'Accounts Office', 'accounts-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(85, 11, 11, 'Staff Designation', 'Exam Office', 'exam-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(86, 11, 11, 'Staff Designation', 'IT Office', 'it-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(87, 11, 11, 'Staff Designation', 'Library', 'library', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(88, 11, 11, 'Staff Designation', 'Storekeeper', 'storekeeper', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(89, 11, 11, 'Staff Designation', 'Lab Assistant', 'lab-assistant', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(90, 11, 11, 'Staff Designation', 'Maintenance', 'maintenance', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(91, 11, 11, 'Staff Designation', 'Academic Office', 'academic-office', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(92, 11, 11, 'Religion', 'Islam', 'islam', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(93, 11, 11, 'Religion', 'Christianity', 'christianity', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(94, 11, 11, 'Religion', 'Hinduism', 'hinduism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(95, 11, 11, 'Religion', 'Buddhism', 'buddhism', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL),
(96, 11, 11, 'Religion', 'Others', 'others', '2026-09-22 15:43:02', '2026-09-22 15:43:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `student_limit` int(11) NOT NULL DEFAULT 0,
  `branch_limit` int(11) NOT NULL DEFAULT 0,
  `price` decimal(8,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(11) DEFAULT NULL,
  `trial_days` int(10) UNSIGNED DEFAULT NULL,
  `is_custom` tinyint(1) NOT NULL DEFAULT 0,
  `is_free` tinyint(1) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `description`, `student_limit`, `branch_limit`, `price`, `duration_days`, `trial_days`, `is_custom`, `is_free`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Trail', NULL, 50, 1, 0.00, 14, NULL, 0, 1, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', '2026-09-18 08:13:16'),
(2, 'Standard', NULL, 10000, 1, 5.00, 30, NULL, 0, 0, 1, '2026-08-13 11:56:14', '2026-09-18 11:23:15', NULL),
(3, 'Professional', NULL, 9999, 1, 299.00, NULL, NULL, 1, 0, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', '2026-09-18 08:13:25');

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '1=privacy, 2=cookie, 3=terms & conditions',
  `description` longtext NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `institute_id`, `branch_id`, `type`, `description`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 'This Privacy Policy explains how we collect, use, and protect your personal data when you use our Learning Management System (LMS). We prioritize your privacy and comply with data protection laws.', 1, NULL, '2026-09-14 08:48:02', NULL, NULL),
(2, 1, 1, 2, 'Our Cookie Policy explains how we use cookies to improve your experience on our LMS platform. By using our site, you agree to our use of cookies for analytics, personalization, and security.', 1, NULL, '2026-09-14 08:48:18', '2026-09-14 08:48:26', NULL),
(3, 1, 1, 3, 'These Terms & Conditions outline the rules and regulations for using our LMS. By accessing our platform, you agree to abide by our terms, including content usage, user conduct, and dispute resolution.', 1, NULL, '2026-09-14 08:48:45', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `popups`
--

CREATE TABLE `popups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `message` text DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `button_text` varchar(191) DEFAULT NULL,
  `button_link` varchar(191) DEFAULT NULL,
  `audience` varchar(20) NOT NULL DEFAULT 'everyone',
  `display_frequency` varchar(20) NOT NULL DEFAULT 'once_per_session',
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `popups`
--

INSERT INTO `popups` (`id`, `institute_id`, `branch_id`, `title`, `message`, `image`, `button_text`, `button_link`, `audience`, `display_frequency`, `start_at`, `end_at`, `priority`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 'Admission open for Session 2026-27', 'We warmly welcomes our new commers', '2026-09-18-6aad46384a70e.png', NULL, NULL, 'everyone', 'once_ever', '2026-09-17 19:09:00', '2026-12-31 19:09:00', 0, 1, 7, '2026-09-18 12:10:00', '2026-09-18 12:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `prayers`
--

CREATE TABLE `prayers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` varchar(191) NOT NULL,
  `date` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_bank_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_bank_subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_bank_chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_bank_difficulty_level_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('true_false','multiple_choice','multiple_true_false') NOT NULL,
  `question_name` text DEFAULT NULL,
  `question` longtext NOT NULL,
  `options` longtext NOT NULL,
  `correct_answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`correct_answer`)),
  `explanation` longtext DEFAULT NULL,
  `marks` int(11) NOT NULL DEFAULT 1,
  `negative_marks` int(11) NOT NULL DEFAULT 0,
  `negative_marks_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `image_file` varchar(191) DEFAULT NULL,
  `price` decimal(8,4) DEFAULT NULL,
  `question_year` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`question_year`)),
  `language` varchar(191) NOT NULL DEFAULT 'bn',
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'inactive' COMMENT 'Lesson status: active, inactive, draft',
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_boards`
--

CREATE TABLE `question_bank_boards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `board` varchar(191) NOT NULL,
  `short_name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_chapters`
--

CREATE TABLE `question_bank_chapters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `chapter_no` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_classes`
--

CREATE TABLE `question_bank_classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_difficulty_levels`
--

CREATE TABLE `question_bank_difficulty_levels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `level_name` varchar(191) NOT NULL,
  `level_code` text DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_groups`
--

CREATE TABLE `question_bank_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_levels`
--

CREATE TABLE `question_bank_levels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `level_name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_sessions`
--

CREATE TABLE `question_bank_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_sources`
--

CREATE TABLE `question_bank_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_subjects`
--

CREATE TABLE `question_bank_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `code` varchar(191) DEFAULT NULL,
  `type` varchar(191) DEFAULT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `color_code` varchar(191) DEFAULT NULL COMMENT 'Color code for the bg category',
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_sub_sources`
--

CREATE TABLE `question_bank_sub_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_id` bigint(20) UNSIGNED NOT NULL,
  `sub_source_name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_tags`
--

CREATE TABLE `question_bank_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tag_name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_tests`
--

CREATE TABLE `question_bank_tests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `test_name` varchar(191) NOT NULL,
  `test_id` varchar(191) DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `duration` varchar(191) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_topics`
--

CREATE TABLE `question_bank_topics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_chapter_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_types`
--

CREATE TABLE `question_bank_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type_name` varchar(191) NOT NULL,
  `default_mark` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_bank_years`
--

CREATE TABLE `question_bank_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` varchar(191) NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_categories`
--

CREATE TABLE `question_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `color_code` varchar(191) DEFAULT NULL COMMENT 'Color code for the bg category',
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `description` longtext DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_level`
--

CREATE TABLE `question_level` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_level_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_session`
--

CREATE TABLE `question_session` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_session_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_source`
--

CREATE TABLE `question_source` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_source_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_sub_source`
--

CREATE TABLE `question_sub_source` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_sub_source_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_tag`
--

CREATE TABLE `question_tag` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_test`
--

CREATE TABLE `question_test` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_test_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_topic`
--

CREATE TABLE `question_topic` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_topic_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_type`
--

CREATE TABLE `question_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_type_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `priority` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `title` varchar(191) NOT NULL,
  `description` longtext DEFAULT NULL,
  `guidelines` longtext DEFAULT NULL,
  `show_description_on_course_page` tinyint(1) NOT NULL DEFAULT 0,
  `type` enum('practice','mock','quick_test','exam') NOT NULL DEFAULT 'practice',
  `question_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`question_ids`)),
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `has_time_limit` tinyint(1) NOT NULL DEFAULT 0,
  `time_limit_value` int(11) DEFAULT NULL,
  `time_limit_unit` enum('seconds','minutes','hours','days','weeks') DEFAULT NULL,
  `on_expiry` enum('auto_submit','prevent_submit','grace_time') NOT NULL DEFAULT 'auto_submit',
  `marks_per_question` decimal(5,2) NOT NULL DEFAULT 1.00,
  `negative_marks_per_wrong_answer` decimal(5,2) NOT NULL DEFAULT 0.00,
  `pass_mark` decimal(5,2) NOT NULL DEFAULT 0.00,
  `attempts_allowed` int(11) DEFAULT NULL,
  `enable_negative_marking` tinyint(1) NOT NULL DEFAULT 0,
  `result_visibility` enum('immediate','after_review','never') NOT NULL DEFAULT 'immediate',
  `layout_pages` int(11) DEFAULT NULL,
  `shuffle_questions` tinyint(1) NOT NULL DEFAULT 0,
  `shuffle_options` tinyint(1) NOT NULL DEFAULT 0,
  `access_type` enum('none','password','public') NOT NULL DEFAULT 'none',
  `access_password` varchar(191) DEFAULT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active' COMMENT 'Lesson status: active, inactive, draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `score` double NOT NULL DEFAULT 0,
  `status` enum('started','submitted','reviewed') NOT NULL DEFAULT 'started',
  `started_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL COMMENT 'Time taken in seconds',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_attempt_id` bigint(20) UNSIGNED NOT NULL,
  `score` double NOT NULL DEFAULT 0,
  `negative_marks` double NOT NULL DEFAULT 0,
  `position` int(11) DEFAULT NULL,
  `question_count` int(11) DEFAULT NULL,
  `correct_count` int(11) NOT NULL DEFAULT 0,
  `incorrect_count` int(11) NOT NULL DEFAULT 0,
  `skipped_count` int(11) NOT NULL DEFAULT 0,
  `detailed_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detailed_results`)),
  `is_passed` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_topics`
--

CREATE TABLE `quiz_topics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` bigint(20) UNSIGNED NOT NULL,
  `question_bank_subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_bank_chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question_limit` int(11) NOT NULL DEFAULT 10,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ready_to_join_us`
--

CREATE TABLE `ready_to_join_us` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `button_name` varchar(100) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ready_to_join_us`
--

INSERT INTO `ready_to_join_us` (`id`, `institute_id`, `branch_id`, `title`, `description`, `icon`, `button_name`, `button_link`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Inquiry & Campus Visit', 'Get in touch or schedule a campus tour to see our facilities and learn more about what we offer.', NULL, 'Contact Us', '#', '2026-09-14 08:49:28', NULL, NULL),
(2, 1, 1, 'Submit Your Application', 'Complete the online application form to start the admission process.', NULL, 'Apply Now', '#', '2026-09-14 08:49:56', NULL, NULL),
(3, 1, 1, 'Admission Confirmation', 'Once accepted, complete your enrollment and confirm your place for the upcoming academic year.', NULL, 'Download Admission Form', '#', '2026-09-14 08:50:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_type` enum('institute','user') NOT NULL,
  `referrer_id` bigint(20) UNSIGNED NOT NULL,
  `referrer_role` varchar(191) DEFAULT NULL,
  `referral_code_used` varchar(191) NOT NULL,
  `referred_institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','converted','rewarded','rejected') NOT NULL DEFAULT 'pending',
  `reward_type` enum('days','cash','points','percentage') DEFAULT NULL,
  `reward_value` decimal(12,2) DEFAULT NULL,
  `institute_reward_type` enum('fee_discount','points','cash_withdrawal') DEFAULT NULL,
  `institute_reward_value` decimal(12,2) DEFAULT NULL,
  `institute_reward_status` enum('pending','rewarded','rejected') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `institute_rewarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_settings`
--

CREATE TABLE `referral_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_role` varchar(191) NOT NULL,
  `reward_type` enum('days','cash','points','percentage') DEFAULT NULL,
  `reward_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remark_configs`
--

CREATE TABLE `remark_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `remark_title` varchar(191) NOT NULL,
  `remarks` longtext NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remark_configs`
--

INSERT INTO `remark_configs` (`id`, `institute_id`, `branch_id`, `remark_title`, `remarks`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'A+', 'Outstanding performance. Keep up the excellent work!', NULL, NULL, NULL),
(2, 1, 1, 'A', 'Very good. A strong performance overall.', NULL, NULL, NULL),
(3, 1, 1, 'A-', 'Good job. Minor areas for improvement.', NULL, NULL, NULL),
(4, 1, 1, 'B', 'Above average. Keep pushing forward.', NULL, NULL, NULL),
(5, 1, 1, 'C', 'Average performance. Needs additional focus on key areas.', NULL, NULL, NULL),
(6, 1, 1, 'D', 'Weak performance. A lot of work is needed.', NULL, NULL, NULL),
(7, 1, 1, 'F', 'Unsatisfactory. Failed to meet the basic requirements.', NULL, NULL, NULL),
(8, 6, 6, 'Got 1st Position', 'Oustanding Performance. Best of luck.', NULL, '2026-09-17 12:21:07', '2026-09-17 12:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `session_id` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `note` varchar(255) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `file_2` varchar(255) DEFAULT NULL,
  `file_3` varchar(255) DEFAULT NULL,
  `file_4` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result_cards`
--

CREATE TABLE `result_cards` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `qr_code` varchar(191) DEFAULT NULL,
  `signature_id` bigint(20) UNSIGNED DEFAULT NULL,
  `teacher_signature_id` bigint(20) UNSIGNED DEFAULT NULL,
  `background_image` varchar(191) DEFAULT NULL,
  `header_logo` varchar(191) DEFAULT NULL,
  `stamp_image` varchar(191) DEFAULT NULL,
  `border_design` varchar(191) DEFAULT NULL,
  `watermark` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `result_card_settings`
--

CREATE TABLE `result_card_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `result_title` varchar(150) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `result_card_settings`
--

INSERT INTO `result_card_settings` (`id`, `institute_id`, `branch_id`, `session_id`, `result_title`, `settings`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'Academic Result Card', '{\"show_gpa\": true, \"show_photo\": true, \"accent_color\": \"#c9a227\", \"show_remarks\": true, \"primary_color\": \"#1a365d\", \"show_position\": true, \"show_affective\": true, \"show_cognitive\": true, \"show_watermark\": true, \"show_attendance\": true, \"show_percentage\": true, \"show_psychomotor\": true, \"signature_labels\": {\"parent\": \"Parent / Guardian\", \"principal\": \"Head Teacher / Principal\", \"class_teacher\": \"Class Teacher\"}, \"watermark_opacity\": 0.08, \"rank_failed_students\": \"with_others\", \"show_failed_subjects\": true, \"teacher_signature_id\": null, \"show_parent_signature\": true, \"principal_signature_id\": null, \"show_teacher_signature\": true, \"show_principal_signature\": true}', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 6, 6, 3, 'Result Card 1st Term', '{\"show_photo\":true,\"show_position\":true,\"show_gpa\":true,\"show_percentage\":true,\"show_failed_subjects\":true,\"show_attendance\":true,\"show_remarks\":true,\"show_cognitive\":true,\"show_affective\":true,\"show_psychomotor\":true,\"show_parent_signature\":true,\"show_teacher_signature\":true,\"show_principal_signature\":true,\"show_watermark\":true,\"watermark_opacity\":0.08,\"primary_color\":\"#1a365d\",\"accent_color\":\"#c9a227\",\"teacher_signature_id\":null,\"principal_signature_id\":null,\"signature_labels\":{\"class_teacher\":null,\"principal\":null,\"parent\":null},\"rank_failed_students\":\"with_others\"}', '2026-09-17 12:36:07', '2026-09-17 12:36:07');

-- --------------------------------------------------------

--
-- Table structure for table `result_publications`
--

CREATE TABLE `result_publications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `online_exam_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'summary',
  `file` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `result_publications`
--

INSERT INTO `result_publications` (`id`, `institute_id`, `branch_id`, `class_id`, `section_id`, `subject_id`, `exam_id`, `online_exam_id`, `title`, `description`, `type`, `file`, `status`, `submitted_by`, `reviewed_by`, `remarks`, `reviewed_at`, `published_at`, `notified_at`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 7, 9, NULL, 4, NULL, 'Final Term', NULL, 'physical', NULL, 2, 7, 7, NULL, '2026-09-18 12:31:02', '2026-09-18 12:31:02', '2026-09-18 12:31:02', '2026-09-18 12:31:02', '2026-09-18 12:31:02');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `institute_id`, `branch_id`, `created_by`, `name`, `guard_name`, `description`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, 'Admin', 'web', 'This is Admin role', '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(2, 6, 6, 7, 'CEO', 'web', NULL, '2026-09-16 08:49:49', '2026-09-16 08:49:49'),
(3, 6, 6, 7, 'Accountant', 'web', 'Account Matter', '2026-09-19 08:48:29', '2026-09-19 08:48:29');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(5, 1),
(5, 2),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(15, 1),
(15, 2),
(16, 1),
(16, 2),
(17, 1),
(17, 2),
(18, 1),
(18, 2),
(19, 1),
(19, 2),
(20, 1),
(20, 2),
(21, 1),
(21, 2),
(22, 1),
(22, 2),
(23, 1),
(23, 2),
(24, 1),
(24, 2),
(25, 1),
(25, 2),
(26, 1),
(26, 2),
(27, 1),
(27, 2),
(28, 1),
(28, 2),
(29, 1),
(29, 2),
(30, 1),
(30, 2),
(31, 1),
(31, 2),
(32, 1),
(32, 2),
(33, 1),
(33, 2),
(34, 1),
(34, 2),
(35, 1),
(35, 2),
(36, 1),
(36, 2),
(37, 1),
(37, 2),
(38, 1),
(38, 2),
(39, 1),
(39, 2),
(40, 1),
(40, 2),
(41, 1),
(41, 2),
(42, 1),
(42, 2),
(43, 1),
(43, 2),
(44, 1),
(44, 2),
(45, 1),
(45, 2),
(46, 1),
(46, 2),
(47, 1),
(47, 2),
(48, 1),
(48, 2),
(49, 1),
(49, 2),
(50, 1),
(50, 2),
(51, 1),
(51, 2),
(51, 3),
(52, 1),
(52, 2),
(52, 3),
(53, 1),
(53, 2),
(53, 3),
(54, 1),
(54, 2),
(54, 3),
(55, 1),
(55, 2),
(55, 3),
(56, 1),
(56, 2),
(56, 3),
(57, 1),
(57, 2),
(57, 3),
(58, 1),
(58, 2),
(58, 3),
(59, 1),
(59, 2),
(59, 3),
(60, 1),
(60, 2),
(60, 3),
(61, 1),
(61, 2),
(61, 3),
(62, 1),
(62, 2),
(62, 3),
(63, 1),
(63, 2),
(63, 3),
(64, 1),
(64, 2),
(64, 3),
(65, 1),
(65, 2),
(65, 3),
(66, 1),
(66, 2),
(66, 3),
(67, 1),
(67, 2),
(67, 3),
(68, 1),
(68, 2),
(68, 3),
(69, 1),
(69, 2),
(69, 3),
(70, 1),
(70, 2),
(70, 3),
(71, 1),
(71, 2),
(72, 1),
(72, 2),
(73, 1),
(73, 2),
(74, 1),
(74, 2),
(75, 1),
(75, 2),
(76, 1),
(76, 2),
(77, 1),
(77, 2),
(78, 1),
(78, 2),
(79, 1),
(79, 2),
(80, 1),
(80, 2),
(81, 1),
(81, 2),
(82, 1),
(82, 2),
(83, 1),
(83, 2),
(84, 1),
(84, 2),
(85, 1),
(85, 2),
(86, 1),
(86, 2),
(87, 1),
(87, 2),
(88, 1),
(88, 2),
(89, 1),
(89, 2),
(90, 1),
(90, 2),
(91, 1),
(91, 2),
(92, 1),
(92, 2),
(93, 1),
(93, 2),
(94, 1),
(94, 2),
(95, 1),
(95, 2),
(96, 1),
(96, 2),
(97, 1),
(97, 2),
(98, 1),
(98, 2),
(99, 1),
(99, 2),
(100, 1),
(100, 2),
(101, 1),
(101, 2),
(102, 1),
(102, 2),
(103, 1),
(103, 2),
(104, 1),
(104, 2),
(105, 1),
(105, 2),
(106, 1),
(106, 2),
(107, 1),
(107, 2),
(108, 1),
(108, 2),
(109, 1),
(109, 2),
(110, 1),
(110, 2),
(111, 1),
(111, 2),
(112, 1),
(112, 2),
(113, 1),
(113, 2),
(114, 1),
(114, 2),
(115, 1),
(115, 2),
(116, 1),
(116, 2),
(117, 1),
(117, 2),
(118, 1),
(118, 2),
(119, 1),
(119, 2),
(120, 1),
(120, 2),
(121, 1),
(121, 2),
(122, 1),
(122, 2),
(123, 1),
(123, 2),
(124, 1),
(124, 2),
(125, 1),
(125, 2),
(126, 1),
(126, 2),
(127, 1),
(127, 2),
(128, 1),
(128, 2),
(129, 1),
(129, 2),
(130, 1),
(130, 2),
(131, 1),
(131, 2),
(132, 1),
(132, 2),
(133, 1),
(133, 2),
(134, 1),
(134, 2),
(135, 1),
(135, 2),
(136, 1),
(136, 2),
(137, 1),
(137, 2),
(138, 1),
(138, 2),
(139, 1),
(139, 2),
(140, 1),
(140, 2),
(141, 1),
(141, 2),
(142, 1),
(142, 2),
(143, 1),
(143, 2),
(144, 1),
(144, 2),
(145, 1),
(145, 2),
(146, 1),
(146, 2),
(147, 1),
(147, 2),
(148, 1),
(148, 2),
(149, 1),
(149, 2),
(150, 1),
(150, 2),
(151, 1),
(151, 2),
(152, 1),
(152, 2),
(153, 1),
(153, 2),
(154, 1),
(154, 2),
(155, 1),
(155, 2),
(156, 1),
(156, 2),
(157, 1),
(157, 2),
(158, 1),
(158, 2),
(159, 1),
(159, 2),
(160, 1),
(160, 2),
(161, 1),
(161, 2),
(162, 1),
(162, 2),
(163, 1),
(163, 2),
(164, 1),
(164, 2),
(165, 1),
(165, 2),
(166, 1),
(166, 2),
(167, 1),
(167, 2),
(168, 1),
(168, 2),
(169, 1),
(169, 2),
(170, 1),
(170, 2),
(171, 1),
(171, 2),
(172, 1),
(172, 2),
(173, 1),
(173, 2),
(174, 1),
(174, 2),
(175, 1),
(175, 2),
(176, 1),
(176, 2),
(177, 1),
(177, 2),
(178, 1),
(178, 2),
(179, 1),
(179, 2),
(180, 1),
(180, 2),
(181, 1),
(181, 2),
(182, 1),
(182, 2),
(183, 1),
(183, 2),
(184, 1),
(184, 2),
(185, 1),
(185, 2),
(186, 1),
(186, 2),
(187, 1),
(187, 2),
(188, 1),
(188, 2),
(189, 1),
(189, 2),
(190, 1),
(190, 2),
(191, 1),
(191, 2),
(192, 1),
(192, 2),
(193, 1),
(193, 2),
(194, 1),
(194, 2),
(195, 1),
(195, 2),
(196, 1),
(196, 2),
(197, 1),
(197, 2),
(198, 1),
(198, 2),
(199, 1),
(199, 2),
(200, 1),
(200, 2),
(201, 1),
(201, 2),
(202, 1),
(202, 2),
(203, 1),
(203, 2),
(204, 1),
(204, 2),
(205, 1),
(205, 2),
(206, 1),
(206, 2),
(207, 1),
(207, 2),
(208, 1),
(208, 2),
(209, 1),
(209, 2),
(210, 1),
(210, 2),
(211, 1),
(211, 2),
(212, 1),
(212, 2),
(213, 1),
(213, 2),
(214, 1),
(214, 2),
(215, 1),
(215, 2),
(216, 1),
(216, 2),
(217, 1),
(217, 2),
(218, 1),
(218, 2),
(219, 1),
(219, 2),
(220, 1),
(220, 2),
(221, 1),
(221, 2),
(222, 1),
(222, 2),
(223, 1),
(223, 2),
(224, 1),
(224, 2),
(225, 1),
(225, 2),
(226, 1),
(226, 2),
(227, 1),
(227, 2),
(228, 1),
(228, 2);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `hostel_category_id` bigint(20) UNSIGNED NOT NULL,
  `hostel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `building_id` bigint(20) UNSIGNED DEFAULT NULL,
  `floor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `room_number` varchar(20) NOT NULL,
  `capacity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `institute_id`, `branch_id`, `hostel_category_id`, `hostel_id`, `building_id`, `floor_id`, `room_number`, `capacity`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 1, NULL, NULL, NULL, 'Room No 5', 5, '2026-09-19 03:57:53', '2026-09-19 03:57:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `room_members`
--

CREATE TABLE `room_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `bed_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','transferred','left') NOT NULL DEFAULT 'active',
  `transfer_reason` varchar(191) DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_heads`
--

CREATE TABLE `salary_heads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('Addition','Deduction') NOT NULL DEFAULT 'Addition',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_heads`
--

INSERT INTO `salary_heads` (`id`, `institute_id`, `branch_id`, `name`, `type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Basic', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Allowance', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'Early Leave Fine', 'Deduction', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'Festival Allowance', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'Welfare Fund', 'Deduction', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'Professional Tax', 'Deduction', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'Conveyance', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, 'Exam Hall Duty', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 1, 1, 'Incentive', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(10, 1, 1, 'Medical', 'Addition', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(12, 6, 6, 'Bonus', 'Addition', '2026-09-16 13:53:30', '2026-09-16 13:53:30'),
(13, 6, 6, 'Commision', 'Addition', '2026-09-16 13:53:43', '2026-09-16 13:53:43'),
(14, 6, 6, 'Efficiency Allowance', 'Addition', '2026-09-16 13:54:21', '2026-09-16 13:54:21'),
(15, 6, 6, 'Absent', 'Deduction', '2026-09-16 13:54:46', '2026-09-16 13:54:46'),
(16, 6, 6, 'Low Performance', 'Deduction', '2026-09-16 13:55:03', '2026-09-16 13:55:03'),
(17, 6, 6, 'Negligence', 'Deduction', '2026-09-16 13:55:16', '2026-09-16 13:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `salary_head_user_payrolls`
--

CREATE TABLE `salary_head_user_payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_payroll_id` bigint(20) UNSIGNED NOT NULL,
  `salary_head_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `student_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_name` varchar(20) NOT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  `class_teacher_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `rank` int(11) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `attendance_time_config_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `institute_id`, `branch_id`, `class_id`, `student_group_id`, `section_name`, `room_no`, `class_teacher_id`, `status`, `rank`, `capacity`, `attendance_time_config_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 3, 1, 1, 'Section A', '12', NULL, 1, NULL, NULL, NULL, '2026-09-16 05:10:43', '2026-09-16 05:11:13', NULL),
(2, 6, 6, 2, NULL, 'Section A', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-16 10:31:14', '2026-09-17 10:00:02', '2026-09-17 10:00:02'),
(3, 6, 6, 2, NULL, 'Section B', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-16 10:31:37', '2026-09-17 09:59:58', '2026-09-17 09:59:58'),
(4, 6, 6, 2, NULL, 'Section C', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-16 10:31:45', '2026-09-17 09:59:54', '2026-09-17 09:59:54'),
(5, 6, 6, 2, 9, 'Section A', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-17 09:59:04', '2026-09-17 09:59:14', '2026-09-17 09:59:14'),
(6, 6, 6, 2, 9, 'Section A', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-17 10:00:20', '2026-09-17 10:00:20', NULL),
(7, 6, 6, 2, 9, 'Section B', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-17 10:00:35', '2026-09-17 10:00:35', NULL),
(8, 6, 6, 2, 9, 'Section C', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-17 10:00:50', '2026-09-17 10:00:50', NULL),
(9, 6, 6, 7, 9, 'Section A', NULL, NULL, 1, NULL, NULL, NULL, '2026-09-17 10:02:59', '2026-09-17 10:02:59', NULL),
(10, 11, 11, 19, 10, 'Section A', '5', NULL, 1, NULL, NULL, NULL, '2026-09-19 16:18:20', '2026-09-19 16:18:20', NULL),
(11, 11, 11, 21, 10, 'Section A', '15', NULL, 1, NULL, NULL, NULL, '2026-09-21 13:01:17', '2026-09-21 13:01:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `value` longtext DEFAULT NULL,
  `payment_info` longtext DEFAULT NULL,
  `sms_info` longtext DEFAULT NULL,
  `type` varchar(191) DEFAULT 'general',
  `mode` varchar(191) DEFAULT NULL,
  `status` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `institute_id`, `branch_id`, `name`, `value`, `payment_info`, `sms_info`, `type`, `mode`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'school_name', 'Your Institute Name', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-14 09:10:59'),
(2, 1, 1, 'site_title', 'Your Institute Name', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-14 09:10:59'),
(3, 1, 1, 'phone', '+123456789', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(4, 1, 1, 'email', 'example@gmail.com', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(5, 1, 1, 'language', 'en', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(6, 1, 1, 'google_map', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(7, 1, 1, 'address', '2/A NewYork, USA', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(8, 1, 1, 'on_google_map', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(9, 1, 1, 'institute_code', '987654', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(10, 1, 1, 'timezone', 'Asia/Dhaka', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(11, 1, 1, 'academic_year', '1', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(12, 1, 1, 'currency_symbol', '$', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(13, 1, 1, 'mail_type', 'mail', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(14, 1, 1, 'logo', 'logo.png', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(15, 1, 1, 'disabled_website', 'no', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(16, 1, 1, 'copyright_text', '&copy; Copyright 2025. All Rights Reserved by Your Institute Name', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-14 09:10:59'),
(17, 1, 1, 'exam_result_phone', NULL, NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-13 17:28:18'),
(18, 1, 1, 'tuition_fee_phone', NULL, NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-13 17:28:18'),
(19, 1, 1, 'facebook_link', 'https://www.facebook.com/', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(20, 1, 1, 'google_plus_link', 'https://www.google.com/', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(21, 1, 1, 'youtube_link', 'https://www.youtube.com/', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(22, 1, 1, 'whats_app_link', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(23, 1, 1, 'twitter_link', 'https://www.twitter.com', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(24, 1, 1, 'eiin_code', '123456', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(25, 1, 1, 'sms_gateway', 'twilio', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(26, 1, 1, 'bulk_sms_api_key', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(27, 1, 1, 'bulk_sms_sender_id', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(28, 1, 1, 'twilio_sid', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(29, 1, 1, 'twilio_token', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(30, 1, 1, 'twilio_from_number', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(31, 1, 1, 'zoom_account_id', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(32, 1, 1, 'zoom_client_key', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(33, 1, 1, 'zoom_client_secret', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(34, 1, 1, 'header_notice', 'Admission start', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-13 17:28:18'),
(35, 1, 1, 'exam_result_status', 'no', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(36, 1, 1, 'admission_display_status', 'no', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(37, 1, 1, 'tc_amount', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(38, 1, 1, 'primary_color', '#016bc3', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-14 09:12:48'),
(39, 1, 1, 'secondary_color', '#0a345b', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-14 09:12:48'),
(40, 1, 1, 'primary_container_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(41, 1, 1, 'dark_primary_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(42, 1, 1, 'dark_secondary_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(43, 1, 1, 'dark_container_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(44, 1, 1, 'text_color', '#000000', NULL, NULL, 'general', NULL, NULL, NULL, '2026-09-14 09:12:48'),
(45, 1, 1, 'dark_text_colo', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(46, 1, 1, 'sidebar_selected_bg_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(47, 1, 1, 'sidebar_selected_text_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(48, 1, 1, 'sidebar_selected_text_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(49, 1, 1, 'guidance', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(50, 1, 1, 'academic_office', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(51, 1, 1, 'tuition_fee', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(52, 1, 1, 'exam_office', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(53, 1, 1, 'website_link', '', NULL, NULL, 'general', NULL, NULL, NULL, NULL),
(54, 1, 1, 'bkash', NULL, '{\"app_key\":\"\",\"app_secret\":\"\",\"username\":\"\",\"password\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(55, 1, 1, 'paystack', NULL, '{\"public_key\":\"\",\"secret_key\":\"\",\"merchant_email\":\"\",\"callback_url\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(56, 1, 1, 'razor_pay', NULL, '{\"api_key\":\"\",\"api_secret\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(57, 1, 1, 'stripe', NULL, '{\"published_key\":\"\",\"api_key\":\"\",\"mode\":\"test\",\"other_config_key\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(58, 1, 1, 'ssl_commerz', NULL, '{\"store_id\":\"\",\"store_password\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(59, 1, 1, 'pvit', NULL, '{\"mc_tel_merchant\":\"\",\"access_token\":\"\",\"mc_merchant_code\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(60, 1, 1, 'paypal', NULL, '{\"client_id\":\"\",\"client_secret\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(61, 1, 1, 'paymob_accept', NULL, '{\"api_key\":\"\",\"integration_id\":\"\",\"iframe_id\":\"\",\"hmac_secret\":\"\",\"supported_country\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(62, 1, 1, 'flutterwave', NULL, '{\"public_key\":\"\",\"secret_key\":\"\",\"encryption_key\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(63, 1, 1, 'senang_pay', NULL, '{\"merchant_id\":\"\",\"secret_key\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(64, 1, 1, 'paytm', NULL, '{\"merchant_id\":\"\",\"merchant_key\":\"\",\"merchant_website_link\":\"\",\"refund_url\":\"\"}', NULL, 'payment_config', 'test', '1', NULL, NULL),
(65, 1, 1, 'app_version', '1.0.0', NULL, NULL, 'general', NULL, NULL, '2026-09-13 17:28:18', '2026-09-13 17:28:18'),
(66, 1, 1, 'app_url', '#', NULL, NULL, 'general', NULL, NULL, '2026-09-13 17:28:18', '2026-09-13 17:28:18'),
(67, 1, 1, 'play_store_link', 'https://play.google.com/store/apps/details?id=com.example.app', NULL, NULL, 'general', NULL, NULL, '2026-09-13 17:28:18', '2026-09-13 17:28:18'),
(68, 1, 1, 'app_store_link', 'https://apps.apple.com/us/app/example-app/id123456789', NULL, NULL, 'general', NULL, NULL, '2026-09-13 17:28:18', '2026-09-13 17:28:18'),
(108, 3, 3, 'school_name', 'Children Academy', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(109, 3, 3, 'site_title', 'Children Academy', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(110, 3, 3, 'phone', '03480233673', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(111, 3, 3, 'email', 'umairkheshgi@gmail.com', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(112, 3, 3, 'language', 'en', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(113, 3, 3, 'google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(114, 3, 3, 'address', 'House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(115, 3, 3, 'on_google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(116, 3, 3, 'institute_code', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(117, 3, 3, 'timezone', 'Asia/Dhaka', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(118, 3, 3, 'academic_year', '1', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(119, 3, 3, 'currency_symbol', '$', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(120, 3, 3, 'mail_type', 'mail', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(121, 3, 3, 'logo', 'logo.png', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(122, 3, 3, 'disabled_website', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(123, 3, 3, 'copyright_text', '&copy; Copyright 2026. All Rights Reserved by Dahidemy', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(124, 3, 3, 'exam_result_phone', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(125, 3, 3, 'tuition_fee_phone', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(126, 3, 3, 'facebook_link', 'https://www.facebook.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(127, 3, 3, 'google_plus_link', 'https://www.google.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(128, 3, 3, 'youtube_link', 'https://www.youtube.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(129, 3, 3, 'whats_app_link', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(130, 3, 3, 'twitter_link', 'https://www.twitter.com', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(131, 3, 3, 'eiin_code', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(132, 3, 3, 'sms_gateway', 'twilio', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(133, 3, 3, 'bulk_sms_api_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(134, 3, 3, 'bulk_sms_sender_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(135, 3, 3, 'twilio_sid', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(136, 3, 3, 'twilio_token', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(137, 3, 3, 'twilio_from_number', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(138, 3, 3, 'zoom_account_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(139, 3, 3, 'zoom_client_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(140, 3, 3, 'zoom_client_secret', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(141, 3, 3, 'header_notice', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(142, 3, 3, 'exam_result_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(143, 3, 3, 'admission_display_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(144, 3, 3, 'tc_amount', '0', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(145, 3, 3, 'app_version', '1.0.0', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(146, 3, 3, 'app_url', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 08:06:53', '2026-09-14 08:06:53'),
(186, 1, 1, 'google_meet_status', '0', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(187, 1, 1, 'google_meet_client_id', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(188, 1, 1, 'google_meet_client_secret', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(189, 1, 1, 'google_meet_project_id', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(190, 1, 1, 'google_meet_redirect_uri', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(191, 1, 1, 'google_meet_api_key', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(192, 1, 1, 'google_meet_service_account_json', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(193, 1, 1, 'google_meet_calendar_id', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(194, 1, 1, 'google_meet_default_duration', '40', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(195, 1, 1, 'google_meet_default_timezone', 'UTC', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(196, 1, 1, 'google_meet_default_permission', 'teacher,student', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:11:40', '2026-09-14 09:11:40'),
(197, 1, 1, 'sidebar_color', '#000000', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:12:48', '2026-09-14 09:12:48'),
(198, 5, 5, 'school_name', 'Pegasus School', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(199, 5, 5, 'site_title', 'Pegasus School', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(200, 5, 5, 'phone', '+923139038053', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(201, 5, 5, 'email', 'zulfiqarkheshgi@gmail.com', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(202, 5, 5, 'language', 'en', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(203, 5, 5, 'google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(204, 5, 5, 'address', '2nd Floor, Khattak Plaza, Nowshera Cantt:, Khyber Pakhtunkhwa', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(205, 5, 5, 'on_google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(206, 5, 5, 'institute_code', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(207, 5, 5, 'timezone', 'Asia/Dhaka', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(208, 5, 5, 'academic_year', '1', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(209, 5, 5, 'currency_symbol', '$', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(210, 5, 5, 'mail_type', 'mail', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(211, 5, 5, 'logo', '2026-09-14-6aa7df2ac4d30.png', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(212, 5, 5, 'disabled_website', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(213, 5, 5, 'copyright_text', '&copy; Copyright 2026. All Rights Reserved by Dahidemy', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(214, 5, 5, 'exam_result_phone', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(215, 5, 5, 'tuition_fee_phone', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(216, 5, 5, 'facebook_link', 'https://www.facebook.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(217, 5, 5, 'google_plus_link', 'https://www.google.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(218, 5, 5, 'youtube_link', 'https://www.youtube.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(219, 5, 5, 'whats_app_link', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(220, 5, 5, 'twitter_link', 'https://www.twitter.com', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(221, 5, 5, 'eiin_code', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(222, 5, 5, 'sms_gateway', 'twilio', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(223, 5, 5, 'bulk_sms_api_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(224, 5, 5, 'bulk_sms_sender_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(225, 5, 5, 'twilio_sid', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(226, 5, 5, 'twilio_token', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(227, 5, 5, 'twilio_from_number', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(228, 5, 5, 'zoom_account_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(229, 5, 5, 'zoom_client_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(230, 5, 5, 'zoom_client_secret', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(231, 5, 5, 'header_notice', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(232, 5, 5, 'exam_result_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(233, 5, 5, 'admission_display_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(234, 5, 5, 'tc_amount', '0', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(235, 5, 5, 'app_version', '1.0.0', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(236, 5, 5, 'app_url', '', NULL, NULL, 'general', NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(237, 3, 3, 'website_theme', 'theme-one', NULL, NULL, 'general', NULL, NULL, '2026-09-14 16:44:56', '2026-09-17 03:07:32'),
(238, 3, 3, 'website_primary_color', '#7c3aed', NULL, NULL, 'general', NULL, NULL, '2026-09-14 16:44:56', '2026-09-14 16:44:56'),
(239, 3, 3, 'website_secondary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-14 16:44:56', '2026-09-14 16:44:56'),
(240, 3, 3, 'website_accent_color', '#ec4899', NULL, NULL, 'general', NULL, NULL, '2026-09-14 16:44:56', '2026-09-14 16:44:56'),
(241, 1, 1, 'website_theme', 'theme-one', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(242, 1, 1, 'website_primary_color', '#7c3aed', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(243, 1, 1, 'website_secondary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(244, 1, 1, 'website_accent_color', '#ec4899', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(253, 5, 5, 'website_theme', 'theme-one', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(254, 5, 5, 'website_primary_color', '#7c3aed', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(255, 5, 5, 'website_secondary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(256, 5, 5, 'website_accent_color', '#ec4899', NULL, NULL, 'general', NULL, NULL, '2026-09-15 05:26:28', '2026-09-15 05:26:28'),
(257, 6, 6, 'school_name', 'Pegasus University', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(258, 6, 6, 'site_title', 'Pegasus University', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(259, 6, 6, 'phone', '+923139038053', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(260, 6, 6, 'email', 'zulfiqarkheshgihome@gmail.com', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(261, 6, 6, 'language', 'en', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(262, 6, 6, 'google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(263, 6, 6, 'address', '2nd Floor, Khattak Plaza, Nowshera Cantt:, Khyber Pakhtunkhwa', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(264, 6, 6, 'on_google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(265, 6, 6, 'institute_code', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(266, 6, 6, 'timezone', 'Asia/Dhaka', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(267, 6, 6, 'academic_year', '3', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-17 09:52:43'),
(268, 6, 6, 'currency_symbol', '$', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(269, 6, 6, 'mail_type', 'mail', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(270, 6, 6, 'logo', 'logo.png', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(271, 6, 6, 'disabled_website', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(272, 6, 6, 'copyright_text', '&copy; Copyright 2026. All Rights Reserved by Pegasus', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(273, 6, 6, 'exam_result_phone', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(274, 6, 6, 'tuition_fee_phone', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(275, 6, 6, 'facebook_link', 'https://www.facebook.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(276, 6, 6, 'google_plus_link', 'https://www.google.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(277, 6, 6, 'youtube_link', 'https://www.youtube.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(278, 6, 6, 'whats_app_link', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(279, 6, 6, 'twitter_link', 'https://www.twitter.com', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(280, 6, 6, 'eiin_code', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(281, 6, 6, 'sms_gateway', 'twilio', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(282, 6, 6, 'bulk_sms_api_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(283, 6, 6, 'bulk_sms_sender_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(284, 6, 6, 'twilio_sid', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(285, 6, 6, 'twilio_token', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(286, 6, 6, 'twilio_from_number', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(287, 6, 6, 'zoom_account_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(288, 6, 6, 'zoom_client_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(289, 6, 6, 'zoom_client_secret', '', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(290, 6, 6, 'header_notice', 'Welcome to house of wisdom.', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(291, 6, 6, 'exam_result_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(292, 6, 6, 'admission_display_status', 'yes', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(293, 6, 6, 'tc_amount', '0', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(294, 6, 6, 'app_version', '1.0.0', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(295, 6, 6, 'app_url', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 08:44:34'),
(296, 6, 6, 'website_theme', 'theme-one', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(297, 6, 6, 'website_primary_color', '#7c3aed', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(298, 6, 6, 'website_secondary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(299, 6, 6, 'website_accent_color', '#ec4899', NULL, NULL, 'general', NULL, NULL, '2026-09-16 08:09:19', '2026-09-16 08:09:19'),
(503, 11, 11, 'school_name', 'Children Academy', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(504, 11, 11, 'site_title', 'Children Academy', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(505, 11, 11, 'phone', '03480233673', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(506, 11, 11, 'email', 'uape00@gmail.com', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(507, 11, 11, 'language', 'en', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(508, 11, 11, 'google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(509, 11, 11, 'address', 'House No: 70-D, Street-5, Block-D, Double Road, ASC Colony, Nowshera', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(510, 11, 11, 'on_google_map', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(511, 11, 11, 'institute_code', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(512, 11, 11, 'timezone', 'Asia/Dhaka', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(513, 11, 11, 'academic_year', '1', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(514, 11, 11, 'currency_symbol', '$', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(515, 11, 11, 'mail_type', 'mail', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(516, 11, 11, 'logo', 'logo.png', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(517, 11, 11, 'disabled_website', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(518, 11, 11, 'copyright_text', '&copy; Copyright 2026. All Rights Reserved by Dahidemy', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(519, 11, 11, 'exam_result_phone', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(520, 11, 11, 'tuition_fee_phone', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(521, 11, 11, 'facebook_link', 'https://www.facebook.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(522, 11, 11, 'google_plus_link', 'https://www.google.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(523, 11, 11, 'youtube_link', 'https://www.youtube.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(524, 11, 11, 'whats_app_link', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(525, 11, 11, 'twitter_link', 'https://www.twitter.com', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(526, 11, 11, 'eiin_code', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(527, 11, 11, 'sms_gateway', 'twilio', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(528, 11, 11, 'bulk_sms_api_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(529, 11, 11, 'bulk_sms_sender_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(530, 11, 11, 'twilio_sid', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(531, 11, 11, 'twilio_token', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(532, 11, 11, 'twilio_from_number', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(533, 11, 11, 'zoom_account_id', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(534, 11, 11, 'zoom_client_key', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(535, 11, 11, 'zoom_client_secret', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(536, 11, 11, 'header_notice', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(537, 11, 11, 'exam_result_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(538, 11, 11, 'admission_display_status', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(539, 11, 11, 'tc_amount', '0', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(540, 11, 11, 'app_version', '1.0.0', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(541, 11, 11, 'app_url', '', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(542, 11, 11, 'website_theme', 'theme-one', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(543, 11, 11, 'website_primary_color', '#7c3aed', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(544, 11, 11, 'website_secondary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(545, 11, 11, 'website_accent_color', '#ec4899', NULL, NULL, 'general', NULL, NULL, '2026-09-17 10:24:24', '2026-09-17 10:24:24'),
(546, 6, 6, 'play_store_link', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 08:44:34', '2026-09-19 08:44:34'),
(547, 6, 6, 'app_store_link', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 08:44:34', '2026-09-19 08:44:34');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `institute_id`, `branch_id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Morning Shift', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(2, 1, 1, 'Day Shift', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 6, 6, 'Morning', '2026-09-16 10:44:51', '2026-09-16 10:44:51', NULL),
(4, 6, 6, 'Evening', '2026-09-16 10:44:59', '2026-09-16 10:44:59', NULL),
(5, 6, 6, 'Night', '2026-09-16 10:45:09', '2026-09-16 10:45:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `short_codes`
--

CREATE TABLE `short_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `short_code_title` varchar(191) NOT NULL,
  `short_code_note` varchar(191) NOT NULL,
  `default_id` int(11) NOT NULL,
  `total_mark` decimal(5,2) NOT NULL,
  `accept_percent` decimal(5,2) NOT NULL,
  `pass_mark` decimal(5,2) NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `short_codes`
--

INSERT INTO `short_codes` (`id`, `institute_id`, `branch_id`, `short_code_title`, `short_code_note`, `default_id`, `total_mark`, `accept_percent`, `pass_mark`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'SC-1', 'Short Code -1', 1, 100.00, 1.00, 0.00, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'SC-2', 'Short Code -2', 2, 100.00, 1.00, 0.00, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'SC-3', 'Short Code -3', 3, 100.00, 1.00, 0.00, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'SC-4', 'Short Code -4', 4, 100.00, 1.00, 0.00, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'SC-5', 'Short Code -5', 5, 100.00, 1.00, 0.00, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'SC-6', 'Short Code -6', 6, 100.00, 1.00, 0.00, 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `signatures`
--

CREATE TABLE `signatures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `place_at` varchar(50) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `image` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `signatures`
--

INSERT INTO `signatures` (`id`, `institute_id`, `branch_id`, `user_id`, `place_at`, `title`, `image`, `created_at`, `updated_at`) VALUES
(1, 6, 6, NULL, 'Front Page', 'Principal', '2026-09-17-6aabdcf409f72.png', '2026-09-17 10:28:36', '2026-09-17 10:28:36');

-- --------------------------------------------------------

--
-- Table structure for table `sms_balances`
--

CREATE TABLE `sms_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `masking_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `non_masking_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_balances`
--

INSERT INTO `sms_balances` (`id`, `institute_id`, `branch_id`, `masking_balance`, `non_masking_balance`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.00, 0.00, '2026-08-13 11:56:14', '2026-08-13 11:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `receiver` varchar(20) NOT NULL,
  `user_type` enum('Teacher','Staff','Accountant','Librarian','Employee','Student','Applicant') NOT NULL DEFAULT 'Teacher',
  `message` text NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_purchases`
--

CREATE TABLE `sms_purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `sms_gateway` varchar(191) NOT NULL,
  `masking_type` varchar(191) NOT NULL,
  `transaction_date` date NOT NULL,
  `no_of_sms` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_templates`
--

CREATE TABLE `sms_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_templates`
--

INSERT INTO `sms_templates` (`id`, `institute_id`, `branch_id`, `name`, `description`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Admission', 'Congratulations!🎓 You`ve been accepted into DEMO. 📚 Get ready for an exciting academic journey ahead. 🌟 Orientation  details will be sent shortly. Welcome to the DEMO family!', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Exam Notice', 'Annual exam will be held on pls collect your admit card', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'Headmaster', 'Dear Parents, This is to inform you that school will be closed tomorrow.', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'Holiday Notice', 'Kindly note that the school will remain closed on 14th & 15th April 16 for the Occasion of Pohela Boishakh', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'Urgent Meeting', 'Dear Teacher, An Urgent Meeting will held on Today at 5.30pm. Please Attend on Just Time. Principal', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'Guardian Assembly', 'Respected Parents Dear Sir, On 01/01/2025 school has organized a parent meeting, I would like to request your presence in the said meeting.', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'Present', 'Your child did not attend today.', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 6, 6, 'Congratulation', 'You are Appointed as Teacher', 1, '2026-09-17 02:28:44', '2026-09-17 02:28:44'),
(9, 6, 6, 'Fee Reminder', 'You are directed to deposit your fee in time', 1, '2026-09-17 02:42:34', '2026-09-17 02:42:34');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `designation` varchar(191) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `religion` varchar(20) DEFAULT NULL,
  `phone` varchar(25) NOT NULL,
  `blood` varchar(5) DEFAULT NULL,
  `sl` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `is_administrator` enum('0','1') NOT NULL DEFAULT '0',
  `access_key` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendances`
--

CREATE TABLE `staff_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `type` varchar(191) DEFAULT NULL,
  `attendance` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_categories`
--

CREATE TABLE `staff_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_categories`
--

INSERT INTO `staff_categories` (`id`, `institute_id`, `branch_id`, `name`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Administration', 10, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(2, 1, 1, 'Teaching Staff', 20, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(3, 1, 1, 'Supporting Staff', 30, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(4, 3, 3, 'Administration', 10, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(5, 3, 3, 'Teaching Staff', 20, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(6, 3, 3, 'Supporting Staff', 30, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(7, 5, 5, 'Administration', 10, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(8, 5, 5, 'Teaching Staff', 20, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(9, 5, 5, 'Supporting Staff', 30, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(10, 6, 6, 'Administration', 10, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(11, 6, 6, 'Teaching Staff', 20, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(12, 6, 6, 'Supporting Staff', 30, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(13, 11, 11, 'Administration', 10, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(14, 11, 11, 'Teaching Staff', 20, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL),
(15, 11, 11, 'Supporting Staff', 30, '2026-09-23 06:08:17', '2026-09-23 06:08:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(20) DEFAULT NULL,
  `student_category_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `phone` varchar(25) NOT NULL,
  `registration_no` varchar(191) DEFAULT NULL,
  `roll_no` varchar(191) DEFAULT NULL,
  `father_name` varchar(50) DEFAULT NULL,
  `mother_name` varchar(50) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `religion` varchar(20) DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `information_sent_to_name` varchar(191) DEFAULT NULL,
  `information_sent_to_relation` varchar(191) DEFAULT NULL,
  `information_sent_to_phone` varchar(25) DEFAULT NULL,
  `information_sent_to_address` varchar(191) DEFAULT NULL,
  `state` varchar(30) DEFAULT NULL,
  `country` varchar(40) DEFAULT NULL,
  `activities` varchar(191) DEFAULT NULL,
  `access_key` varchar(191) DEFAULT NULL,
  `remarks` longtext DEFAULT NULL,
  `nationality` varchar(191) DEFAULT 'Bangladeshi',
  `birth_certificate_no` varchar(100) DEFAULT NULL,
  `nid_no` varchar(100) DEFAULT NULL,
  `ethnic` varchar(191) DEFAULT NULL,
  `date_of_admission` date DEFAULT NULL,
  `application_number` varchar(20) DEFAULT NULL,
  `admission_place` varchar(191) DEFAULT NULL,
  `tc_date` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_assessments`
--

CREATE TABLE `student_assessments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `assessment_item_id` bigint(20) UNSIGNED NOT NULL,
  `score` decimal(6,2) DEFAULT NULL,
  `rating` varchar(80) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_attendances`
--

CREATE TABLE `student_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `period_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `attendance` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_categories`
--

CREATE TABLE `student_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_categories`
--

INSERT INTO `student_categories` (`id`, `institute_id`, `branch_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'General', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Special', '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 6, 6, 'Arts', '2026-09-16 10:56:59', '2026-09-16 10:56:59'),
(4, 6, 6, 'Science', '2026-09-16 10:57:06', '2026-09-16 10:57:06'),
(5, 6, 6, 'Pre Medical', '2026-09-16 10:57:17', '2026-09-16 10:57:17'),
(6, 6, 6, 'Pre Engineering', '2026-09-16 10:57:27', '2026-09-16 10:57:27'),
(7, 6, 6, 'Computer Science', '2026-09-16 10:57:40', '2026-09-16 10:57:40'),
(8, 6, 6, 'Inter Science', '2026-09-16 10:57:49', '2026-09-16 10:57:49'),
(9, 6, 6, 'Primary', '2026-09-17 10:23:10', '2026-09-17 10:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_collections`
--

CREATE TABLE `student_collections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` varchar(191) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year',
  `tc_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `attendance_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `quiz_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `lab_fine` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_payable` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_due` decimal(8,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receive_ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_collection_details`
--

CREATE TABLE `student_collection_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year',
  `student_collection_id` bigint(20) UNSIGNED NOT NULL,
  `fee_head_id` bigint(20) UNSIGNED NOT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total_due` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_payable` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(8,2) NOT NULL DEFAULT 0.00,
  `waiver` decimal(8,2) NOT NULL DEFAULT 0.00,
  `fine_payable` decimal(8,2) NOT NULL DEFAULT 0.00,
  `fee_payable` decimal(8,2) NOT NULL DEFAULT 0.00,
  `fee_and_fine_payable` decimal(8,2) NOT NULL DEFAULT 0.00,
  `fee_and_fine_paid` decimal(8,2) NOT NULL DEFAULT 0.00,
  `previous_due_payable` decimal(8,2) NOT NULL DEFAULT 0.00,
  `previous_due_paid` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_collection_details_sub_heads`
--

CREATE TABLE `student_collection_details_sub_heads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year',
  `student_collection_id` bigint(20) UNSIGNED NOT NULL,
  `student_collection_details_id` bigint(20) UNSIGNED NOT NULL,
  `fee_head_id` bigint(20) UNSIGNED NOT NULL,
  `sub_head_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_devices`
--

CREATE TABLE `student_devices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `device_name` varchar(191) DEFAULT NULL,
  `mac_id` varchar(191) DEFAULT NULL,
  `ip_address` varchar(191) DEFAULT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_groups`
--

CREATE TABLE `student_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_groups`
--

INSERT INTO `student_groups` (`id`, `institute_id`, `branch_id`, `group_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 3, 'Medical Group', '2026-09-16 05:10:57', '2026-09-16 05:10:57', NULL),
(2, 6, 6, 'Arts', '2026-09-16 10:32:02', '2026-09-16 10:32:02', NULL),
(3, 6, 6, 'Science', '2026-09-16 10:32:15', '2026-09-16 10:32:15', NULL),
(4, 6, 6, 'Computer Science', '2026-09-16 10:32:39', '2026-09-16 10:32:39', NULL),
(5, 6, 6, 'Pre Medical', '2026-09-16 10:32:54', '2026-09-16 10:32:54', NULL),
(6, 6, 6, 'Pre Engineering', '2026-09-16 10:33:05', '2026-09-16 10:33:05', NULL),
(7, 6, 6, 'Pre Computer Science', '2026-09-16 10:33:20', '2026-09-16 10:33:20', NULL),
(8, 6, 6, 'Inter Science', '2026-09-16 10:33:31', '2026-09-16 10:33:31', NULL),
(9, 6, 6, 'Primary', '2026-09-17 09:58:35', '2026-09-17 09:58:35', NULL),
(10, 11, 11, 'Science', '2026-09-19 16:18:03', '2026-09-19 16:18:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_migrations`
--

CREATE TABLE `student_migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(191) NOT NULL,
  `transferred_to_school_name` varchar(191) DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `prev_class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `prev_section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `prev_group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `prev_roll` bigint(20) UNSIGNED DEFAULT NULL,
  `new_roll` bigint(20) UNSIGNED DEFAULT NULL,
  `academic_year` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_optional_subjects`
--

CREATE TABLE `student_optional_subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `student_session_id` bigint(20) UNSIGNED NOT NULL,
  `optional_subject_config_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `mark` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_sessions`
--

CREATE TABLE `student_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `roll` varchar(15) NOT NULL,
  `qr_code` varchar(32) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_waiver_configs`
--

CREATE TABLE `student_waiver_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `fee_head_id` bigint(20) UNSIGNED DEFAULT NULL,
  `waiver_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `subject_name` varchar(40) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_short_form` varchar(191) DEFAULT NULL,
  `subject_type` varchar(30) DEFAULT NULL,
  `subject_type_form` varchar(30) DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `sl_no` bigint(20) UNSIGNED DEFAULT 20,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `objective` int(10) UNSIGNED DEFAULT 0,
  `written` int(10) UNSIGNED DEFAULT 0,
  `practical` int(10) UNSIGNED DEFAULT 0,
  `full_mark` int(10) UNSIGNED DEFAULT 0,
  `pass_mark` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `institute_id`, `branch_id`, `subject_name`, `subject_code`, `subject_short_form`, `subject_type`, `subject_type_form`, `class_id`, `sl_no`, `group_id`, `objective`, `written`, `practical`, `full_mark`, `pass_mark`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 6, 6, 'English', '0001', 'Eng', 'Compulsory', 'All', 2, 1, 2, 0, 0, 0, 0, 0, '2026-09-16 10:52:34', '2026-09-16 10:52:34', NULL),
(2, 6, 6, 'Urdu', '0002', 'Urd', 'Compulsory', 'All', 2, 2, 2, 0, 0, 0, 0, 0, '2026-09-16 10:53:16', '2026-09-16 10:53:16', NULL),
(3, 11, 11, 'Physics', 'phy20', 'Phy', 'Compulsory', 'Compulsory', 19, 5, 10, 0, 0, 0, 0, 0, '2026-09-19 16:22:04', '2026-09-19 16:22:04', NULL),
(4, 11, 11, 'Physics', 'phy10', 'phy10', 'Compulsory', 'Elective', 25, 545, 10, 0, 0, 0, 0, 0, '2026-09-23 03:29:47', '2026-09-23 03:29:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subject_configs`
--

CREATE TABLE `subject_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `subject_type` varchar(191) DEFAULT NULL,
  `sr_no` int(11) NOT NULL DEFAULT 0,
  `merge_config_id` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subject_configs`
--

INSERT INTO `subject_configs` (`id`, `institute_id`, `branch_id`, `class_id`, `group_id`, `subject_id`, `subject_type`, `sr_no`, `merge_config_id`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 2, 2, 1, NULL, 0, 0, '2026-09-16 10:53:33', '2026-09-16 10:53:33'),
(2, 6, 6, 2, 2, 2, NULL, 0, 0, '2026-09-16 10:53:33', '2026-09-16 10:53:33'),
(3, 11, 11, 20, 10, 3, 'compulsary', 0, 0, '2026-09-19 16:22:25', '2026-09-19 16:22:43');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `invoice_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`invoice_details`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `institute_id`, `plan_id`, `start_date`, `end_date`, `status`, `invoice_details`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 3, '2026-08-13', '2027-08-13', 'active', '\"{\\\"invoice_no\\\":\\\"INV-0NIFNG\\\",\\\"issued_at\\\":\\\"2026-08-13\\\",\\\"notes\\\":\\\"Auto-generated in seeder\\\"}\"', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 3, 1, '2026-09-14', '2026-09-28', 'active', '{\"invoice_no\":\"INV-8EUGSU\",\"issued_at\":\"2026-09-14\",\"notes\":\"Trail\",\"amount\":0}', '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(5, 5, 1, '2026-09-14', '2026-09-28', 'active', '{\"invoice_no\":\"INV-ZNSFNB\",\"issued_at\":\"2026-09-14\",\"notes\":\"Trail\",\"amount\":0}', '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(6, 6, 1, '2026-09-16', '2026-09-30', 'active', '{\"invoice_no\":\"INV-JQPACL\",\"issued_at\":\"2026-09-16\",\"notes\":\"Trail\",\"amount\":0}', '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(11, 11, 2, '2026-09-17', '2027-09-17', 'active', '{\"invoice_no\":\"INV-NQV8M4\",\"issued_at\":\"2026-09-17\",\"notes\":\"Self-service plan change to Standard\",\"amount\":99}', '2026-09-17 10:24:24', '2026-09-17 10:43:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subscription_items`
--

CREATE TABLE `subscription_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(191) DEFAULT NULL,
  `amount_paid` decimal(8,2) DEFAULT NULL,
  `payment_method` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_items`
--

INSERT INTO `subscription_items` (`id`, `subscription_id`, `reference_no`, `amount_paid`, `payment_method`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'PAY-JUD01CNR', 299.00, 'Manual', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 3, 'PAY-SKVI3EUT', 0.00, 'Manual', '2026-09-14 08:06:53', '2026-09-14 08:06:53', NULL),
(5, 5, 'PAY-HCDWIXN6', 0.00, 'Manual', '2026-09-14 09:48:59', '2026-09-14 09:48:59', NULL),
(6, 6, 'PAY-XQ5BX0GE', 0.00, 'Manual', '2026-09-16 08:09:19', '2026-09-16 08:09:19', NULL),
(11, 11, 'PAY-QQFGDU0F', 0.00, 'Manual', '2026-09-17 10:24:24', '2026-09-17 10:24:24', NULL),
(12, 11, 'PAY-HM4LDVET', 99.00, 'Stripe', '2026-09-17 10:43:39', '2026-09-17 10:43:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subscription_upgrade_requests`
--

CREATE TABLE `subscription_upgrade_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `extra_days` int(11) NOT NULL DEFAULT 0,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(191) DEFAULT NULL,
  `notes` varchar(191) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_upgrade_requests`
--

INSERT INTO `subscription_upgrade_requests` (`id`, `institute_id`, `plan_id`, `extra_days`, `amount_paid`, `payment_method`, `notes`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 11, 2, 365, 99.00, 'online', NULL, 'pending', NULL, NULL, '2026-09-17 10:31:40', '2026-09-17 10:31:40');

-- --------------------------------------------------------

--
-- Table structure for table `syllabus`
--

CREATE TABLE `syllabus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `file` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `syllabus`
--

INSERT INTO `syllabus` (`id`, `institute_id`, `branch_id`, `session_id`, `title`, `description`, `class_id`, `file`, `created_at`, `updated_at`) VALUES
(2, 6, 6, 3, 'Syllabus for Grade 1 English', NULL, 7, '2026-09-18-6aacb922384c6.pdf', '2026-09-18 02:08:02', '2026-09-18 02:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `system_modules`
--

CREATE TABLE `system_modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `version` varchar(191) NOT NULL DEFAULT '1.0.0',
  `description` text DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(191) NOT NULL DEFAULT 'active',
  `dependencies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dependencies`)),
  `minimum_app_version` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_modules`
--

INSERT INTO `system_modules` (`id`, `name`, `slug`, `version`, `description`, `enabled`, `status`, `dependencies`, `minimum_app_version`, `created_at`, `updated_at`) VALUES
(1, 'Academic', 'academic', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(2, 'Accounting', 'accounting', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(3, 'Ai', 'ai', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(4, 'Authentication', 'authentication', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(5, 'Elearning', 'elearning', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(6, 'Examination', 'examination', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(7, 'Finance', 'finance', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(8, 'Frontend', 'frontend', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(9, 'Gateways', 'gateways', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(10, 'Hostel', 'hostel', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(11, 'LayoutCert', 'layoutcert', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(12, 'Library', 'library', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(13, 'ParentModule', 'parentmodule', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(14, 'Payroll', 'payroll', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(15, 'QuestionBank', 'questionbank', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(16, 'SMS', 'sms', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(17, 'Student', 'student', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(18, 'SystemConfiguration', 'systemconfiguration', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(19, 'Teacher', 'teacher', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(20, 'Transport', 'transport', '1.0.0', '', 1, 'active', '[]', NULL, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(21, 'FormBuilder', 'formbuilder', '1.0.0', 'Lets institute admins build custom forms (registration, job application, feedback, etc.) with dynamic fields, publish them, collect submissions, and optionally surface them on the public homepage.', 1, 'active', '[]', NULL, '2026-09-15 05:28:28', '2026-09-15 12:52:10'),
(22, 'EventEnrollment', 'eventenrollment', '1.0.0', 'Public event browsing (upcoming/ongoing/past) with free or paid enrollment, integrated with the existing multi-gateway payment system and applicant notifications.', 1, 'active', '[]', NULL, '2026-09-15 07:47:06', '2026-09-15 12:47:06'),
(23, 'MenuBuilder', 'menubuilder', '1.0.0', 'Lets institute admins fully control their public site\'s navigation menu — add, remove, reorder, and nest items, pointing anywhere (a built-in page, a homepage section, or any custom URL) — without editing code.', 1, 'active', '[]', NULL, '2026-09-15 08:44:37', '2026-09-15 12:52:10'),
(24, 'PopupBroadcaster', 'popupbroadcaster', '1.0.0', 'Lets institute admins broadcast scheduled popups on the public website — exam reminders, admission openings, class start notices, or any announcement with an optional link.', 1, 'active', '[]', NULL, '2026-09-15 10:40:00', '2026-09-15 12:52:10'),
(25, 'ResultPublication', 'resultpublication', '1.0.0', 'Teachers submit exam results (online exam link, or an uploaded physical/scanned mark sheet) tied to class/section/subject/exam; an admin approves or rejects before students and parents can see it, with notifications sent on approval.', 1, 'active', '[]', NULL, '2026-09-15 10:40:00', '2026-09-15 12:52:10'),
(26, 'Blog', 'blog', '1.0.0', 'Institute-managed blog: categories, posts, a public blog listing/detail, and a homepage teaser section.', 1, 'active', '[]', NULL, '2026-09-16 08:02:12', '2026-09-16 08:02:12'),
(27, 'HomepageBuilder', 'homepagebuilder', '1.0.0', 'Lets institute admins decide which sections appear on their homepage and in what order, plus a Director\'s Message content block.', 1, 'active', '[]', NULL, '2026-09-16 08:02:12', '2026-09-16 08:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(191) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'string',
  `group` varchar(191) NOT NULL DEFAULT 'general',
  `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `type`, `group`, `is_encrypted`, `created_at`, `updated_at`) VALUES
(1, 'app_version', '15.1', 'string', 'version', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(2, 'latest_version', '', 'string', 'version', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(3, 'update_server_url', '', 'string', 'general', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(4, 'update_check_enabled', '1', 'boolean', 'general', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(5, 'demo_mode', '0', 'boolean', 'demo', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(6, 'demo_auto_reset', '1', 'boolean', 'demo', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33'),
(7, 'demo_reset_interval', '24', 'integer', 'demo', 0, '2026-08-13 11:53:33', '2026-08-13 11:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `system_updates`
--

CREATE TABLE `system_updates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `from_version` varchar(191) DEFAULT NULL,
  `to_version` varchar(191) DEFAULT NULL,
  `type` varchar(191) NOT NULL DEFAULT 'manual',
  `status` varchar(191) NOT NULL DEFAULT 'pending',
  `package_name` varchar(191) DEFAULT NULL,
  `package_size` bigint(20) UNSIGNED DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `backup_path` varchar(191) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `executed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_update_logs`
--

CREATE TABLE `system_update_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `system_update_id` bigint(20) UNSIGNED NOT NULL,
  `level` varchar(191) NOT NULL DEFAULT 'info',
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `logged_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_banners`
--

CREATE TABLE `s_a_a_s_banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_name` varchar(100) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `button_two_name` varchar(100) DEFAULT NULL,
  `button_two_link` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_banners`
--

INSERT INTO `s_a_a_s_banners` (`id`, `title`, `description`, `button_name`, `button_link`, `button_two_name`, `button_two_link`, `image`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Complete Institute Management System', 'Manage admissions, students, teachers, attendance, exams, accounting, HRM, online classes and more from one powerful platform.', 'Start Free Trial', '/register', 'Book Demo', '/contact', '2026-09-14-6aa787f845380.png', 1, NULL, '2026-08-13 11:56:14', '2026-09-14 03:38:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_benefits`
--

CREATE TABLE `s_a_a_s_benefits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `heading` varchar(191) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `para_title_one` varchar(100) DEFAULT NULL,
  `para_title_two` varchar(100) DEFAULT NULL,
  `para_title_three` varchar(100) DEFAULT NULL,
  `para_title_four` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_benefits`
--

INSERT INTO `s_a_a_s_benefits` (`id`, `heading`, `title`, `description`, `para_title_one`, `para_title_two`, `para_title_three`, `para_title_four`, `image`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Why Choose Us', 'Everything Your Institution Needs', 'Our cloud-based ERP simplifies every academic and administrative operation.', 'Multi School', 'Cloud Based', 'Mobile Friendly', '24/7 Support', '2026-09-14-6aa78b58523ef.png', 1, NULL, '2026-08-13 11:56:14', '2026-09-14 03:51:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_contacts`
--

CREATE TABLE `s_a_a_s_contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `subject` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `isSeen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_contacts`
--

INSERT INTO `s_a_a_s_contacts` (`id`, `name`, `email`, `phone`, `subject`, `message`, `user_id`, `isSeen`, `created_at`, `updated_at`) VALUES
(1, 'Denis Berger', 'denisberger.web@gmail.com', '1201201200', 'Re: Increase traffic to your website', 'Hello there,\r\n\r\nI would like to discuss AI SEO!\r\n\r\nWe help businesses rank higher on Google while also increasing their presence on AI-powered search platforms like ChatGPT, Gemini, Claude, and Perplexity.\r\n\r\nMay I send you a quote & price list?\r\n\r\nCheers,\r\nDenis Berger | Founder & Project Head\"\r\n\r\n\r\nNote: Web platform expertise across Squarespace, Shopify, Wix, WordPress, GoDaddy etc.', NULL, 0, '2026-09-15 07:34:36', '2026-09-15 07:34:36'),
(2, 'Ananya Singh', 'ananya@rocketdigitaltech.com', '89650077', 'Improve Your Search Engine Visibility', 'Hello http://dahidemy.com,\r\n\r\nI hope you’re doing well. I came across your business online and thought you might be interested in improving your visibility and traffic on search engines.\r\n\r\nWe specialize in helping businesses strengthen their online presence through effective SEO strategies.\r\n\r\nOnce you share your target keywords and target market, I’ll send a full proposal.\r\n\r\nWarm regards,\r\nAnanya', NULL, 0, '2026-09-16 08:21:18', '2026-09-16 08:21:18'),
(3, 'Joanna Riggs', 'joannariggs3@gmail.com', '626077946', 'Converting more visitors on dahidemy.com', 'Hi,\r\n\r\nI just visited dahidemy.com and wondered if you\'ve ever considered an impactful video to advertise your business? Our videos can generate impressive results on both your website and across social media.\r\n\r\nOur prices start from just $195 (USD).\r\n\r\nLet me know if you\'re interested in seeing samples of our previous work.\r\n\r\nRegards,\r\nJoanna', NULL, 0, '2026-09-16 11:00:39', '2026-09-16 11:00:39'),
(4, 'Elba Cardone', 'domains@search-dahidemy.com', '3363564509', 'Results for dahidemy.com', 'Hi\r\n\r\nList dahidemy.com in Google\'s Search Index to show up in Google search results!\r\n\r\nList dahidemy.com here:\r\n\r\nsearchindex.pro', NULL, 0, '2026-09-16 15:07:06', '2026-09-16 15:07:06'),
(5, 'Rosabel Edney', 'mapmybiz.mkt@gmail.com', NULL, 'SEO Foundations for Long-Term Growth', 'Hey team dahidemy.com,\r\n\r\nI\'d love to discuss how we can grow your online visibility.\r\n\r\nWe help businesses rank higher on Google while also increasing their presence on AI-powered search platforms like ChatGPT, Gemini, Claude, and Perplexity.\r\n\r\nOur SEO + AEO + GEO strategies help brands get discovered wherever customers search for answers.\r\n\r\nMay I send you a quote & price list?\r\n\r\nCheers,\r\nRosabel Edney | Founder & Project Head', NULL, 0, '2026-09-16 21:24:47', '2026-09-16 21:24:47'),
(6, 'Alisa Taggart', 'domains@search-dahidemy.com', '439148444', 'Results for dahidemy.com', 'Dear Sir/Madam\r\n\r\nSubmit dahidemy.com in Google\'s Search Index and have it be displayed in online search results!\r\n\r\nFeature dahidemy.com today:\r\n\r\ndomainsubmit.pro', NULL, 0, '2026-09-17 15:38:48', '2026-09-17 15:38:48'),
(7, 'Rusty Lassiter', 'domains@search-dahidemy.com', '647406797', 'Results for dahidemy.com', 'Greetings\r\n\r\nRegister dahidemy.com in Google\'s Search Index to be displayed in online search results!\r\n\r\nList dahidemy.com here:\r\n\r\nsearchregister.pro', NULL, 0, '2026-09-18 12:54:01', '2026-09-18 12:54:01');

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_cores`
--

CREATE TABLE `s_a_a_s_cores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_cores`
--

INSERT INTO `s_a_a_s_cores` (`id`, `title`, `description`, `icon`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Student Management', 'Manage students, admissions and academic records.', '2026-09-14-6aa7894b8bc6f.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:42:35', NULL),
(2, 'Attendance', 'Daily attendance with reports.', '2026-09-14-6aa789667b53e.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:43:02', NULL),
(3, 'Exam Management', 'Exam routine, marksheet and result processing.', '2026-09-14-6aa7897796a40.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:43:19', NULL),
(4, 'Accounting', 'Income, expense and complete accounting.', '2026-09-14-6aa78983a6e40.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:43:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_faqs`
--

CREATE TABLE `s_a_a_s_faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` longtext DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_faqs`
--

INSERT INTO `s_a_a_s_faqs` (`id`, `question`, `answer`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'What is this software?', 'It is a complete cloud-based School ERP & Multi-School System platform.', 1, NULL, '2026-08-13 11:56:14', '2026-09-14 03:51:47', NULL),
(2, 'Can I manage multiple schools?', 'Yes. The Super Admin can manage unlimited schools from one dashboard.', 1, NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 'Does it support online admission?', 'Yes. Students can apply online and administrators can review applications.', 1, NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(4, 'Is there a free trial?', 'Yes. You can start with a free trial before purchasing.', 1, NULL, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_feedback`
--

CREATE TABLE `s_a_a_s_feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` longtext NOT NULL,
  `thumbnail_image` varchar(191) DEFAULT NULL,
  `video_url` varchar(191) DEFAULT NULL,
  `note` varchar(191) DEFAULT NULL,
  `ratting` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_feedback`
--

INSERT INTO `s_a_a_s_feedback` (`id`, `name`, `description`, `thumbnail_image`, `video_url`, `note`, `ratting`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'John Alex', 'This software has transformed our school`s administration.', '2026-04-19-69e4e9bc045ed.png', NULL, 'Principal', 5, 1, '2026-08-13 11:56:14', '2026-09-14 03:53:07', NULL),
(2, 'David Smith', 'Easy to use and excellent support.', '2026-04-19-69e4e9bc045ed.png', NULL, 'School Administrator', 5, 1, '2026-08-13 11:56:14', '2026-09-14 03:52:15', NULL),
(3, 'Jack Dawson', 'User friendly, reduce 90% work load', NULL, NULL, 'Teacher', 5, 1, '2026-09-14 03:55:42', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_panels`
--

CREATE TABLE `s_a_a_s_panels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `button_name` varchar(100) DEFAULT NULL,
  `button_link` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_panels`
--

INSERT INTO `s_a_a_s_panels` (`id`, `title`, `description`, `icon`, `button_name`, `button_link`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin Panel', 'Manage all schools from one dashboard.', '2026-04-19-69e4f17a3bdef.png', 'Learn More', '#', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(2, 'School Admin Panel', 'Complete administration for each institute.', '2026-04-19-69e4f20acf466.png', 'Explore', '#', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(3, 'Teacher Panel', 'Attendance, marks, homework and routine.', '2026-04-19-69e4f20acf466.png', 'View', '#', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(4, 'Student Panel', 'Assignments, exams, attendance and results.', '2026-04-19-69e4f20acf466.png', 'View', '#', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_policies`
--

CREATE TABLE `s_a_a_s_policies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('privacy','cookie','terms') NOT NULL COMMENT 'privacy=cookie policy, cookie=cookie policy, terms=terms & conditions',
  `description` longtext NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_settings`
--

CREATE TABLE `s_a_a_s_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` longtext DEFAULT NULL,
  `payment_info` longtext DEFAULT NULL,
  `sms_info` longtext DEFAULT NULL,
  `type` varchar(191) DEFAULT 'general',
  `mode` varchar(191) DEFAULT NULL,
  `status` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_settings`
--

INSERT INTO `s_a_a_s_settings` (`id`, `name`, `value`, `payment_info`, `sms_info`, `type`, `mode`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'site_name', 'Dahidemy', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:01', '2026-09-19 04:38:01', NULL),
(2, 'site_title', 'Dahidemy', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:01', '2026-09-19 04:38:01', NULL),
(3, 'phone', '+12345', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:01', '2026-09-19 04:38:01', NULL),
(4, 'office_phone', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:01', '2026-09-19 04:38:01', NULL),
(5, 'email', 'support@dahidemy.com', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:01', '2026-09-19 04:38:01', NULL),
(6, 'language', 'en', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:01', '2026-09-19 04:38:01', NULL),
(7, 'google_map', 'https://maps.app.goo.gl/', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(8, 'address', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(9, 'currency_symbol', '$', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(10, 'logo', 'logo.png', NULL, NULL, 'general', NULL, NULL, NULL, NULL, NULL),
(11, 'disabled_website', 'no', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(12, 'copyright_text', 'Copyright 2026. All Rights Reserved by Dahidemy', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(13, 'facebook_link', 'https://www.facebook.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(14, 'google_plus_link', 'https://www.google.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(15, 'youtube_link', 'https://www.youtube.com/', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(16, 'whats_app_link', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(17, 'twitter_link', 'https://www.twitter.com', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(18, 'header_notice', 'ABC', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(19, 'app_version', '1.0.0', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(20, 'app_url', 'https://dahidemy.com', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(21, 'play_store_link', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(22, 'app_store_link', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(23, 'primary_color', '#4f46e5', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(24, 'secondary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(25, 'primary_container_color', '#ec4899', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(26, 'dark_primary_color', '#0f172a', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(27, 'dark_secondary_color', '#475569', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(28, 'dark_container_color', '#94a3b8', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(29, 'text_color', '#ffffff', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(30, 'dark_text_colo', '#f8fafc', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(31, 'sidebar_selected_bg_color', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(32, 'sidebar_selected_text_color', NULL, NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(33, 'sidebar_selected_text_color', NULL, NULL, NULL, 'general', NULL, NULL, NULL, NULL, NULL),
(34, 'vercel_project_id', 'prj_8PvmJHMdDZNsnjGEK3AarjW75uO4', NULL, NULL, 'vercel', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL),
(35, 'vercel_token', '8dBrjwsJuTMq3okIbXCoVo8S', NULL, NULL, 'vercel', NULL, NULL, NULL, NULL, NULL),
(36, 'bkash', NULL, '{\"app_key\":\"\",\"app_secret\":\"\",\"username\":\"\",\"password\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:13', NULL),
(37, 'paystack', NULL, '{\"public_key\":\"\",\"secret_key\":\"\",\"merchant_email\":\"\",\"callback_url\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:16', NULL),
(38, 'razor_pay', NULL, '{\"api_key\":\"\",\"api_secret\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:19', NULL),
(39, 'stripe', NULL, '{\"published_key\":\"pk_test_51TwyuX4t4OQ7RpdiO6ym2iTjnob5SMtAYrOEVcS9ykUXZWUlqTLheA12oGIJCfpFU5HM2pd1Sw6brLxO7GaCb4x300hXNUVMwk\",\"api_key\":\"sk_test_51TwyuX4t4OQ7RpdiUXtNPgeeBamImNfIvPLOpwxuEgjmSnxeD9iFv7szXklROsxM3a4YvMoQz7UA9w8UZWoBkhz300ViFDARky\",\"mode\":\"test\",\"other_config_key\":\"test_value\"}', NULL, 'payment_config', 'test', '1', NULL, '2026-09-17 09:21:53', NULL),
(40, 'ssl_commerz', NULL, '{\"store_id\":\"\",\"store_password\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:22', NULL),
(41, 'pvit', NULL, '{\"mc_tel_merchant\":\"\",\"access_token\":\"\",\"mc_merchant_code\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:32', NULL),
(42, 'paypal', NULL, '{\"client_id\":\"\",\"client_secret\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:30', NULL),
(43, 'paymob_accept', NULL, '{\"api_key\":\"\",\"integration_id\":\"\",\"iframe_id\":\"\",\"hmac_secret\":\"\",\"supported_country\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:37', NULL),
(44, 'flutterwave', NULL, '{\"public_key\":\"\",\"secret_key\":\"\",\"encryption_key\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:42', NULL),
(45, 'senang_pay', NULL, '{\"merchant_id\":\"\",\"secret_key\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:46', NULL),
(46, 'paytm', NULL, '{\"merchant_id\":\"\",\"merchant_key\":\"\",\"merchant_website_link\":\"\",\"refund_url\":\"\"}', NULL, 'payment_config', 'test', '0', NULL, '2026-09-17 09:22:00', NULL),
(47, 'dark_text_color', '#f8fafc', NULL, NULL, 'general', NULL, NULL, '2026-09-19 04:38:02', '2026-09-19 04:38:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_solutions`
--

CREATE TABLE `s_a_a_s_solutions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_solutions`
--

INSERT INTO `s_a_a_s_solutions` (`id`, `title`, `description`, `icon`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'School ERP', 'Complete digital school solution.', '2026-09-14-6aa789a7a9aa7.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:44:07', NULL),
(2, 'Online Admission', 'Accept admission applications online.', '2026-09-14-6aa789c98bca1.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:44:41', NULL),
(3, 'Online Examination', 'Conduct online examinations easily.', '2026-09-14-6aa789d63fdf7.png', 1, '2026-08-13 11:56:14', '2026-09-14 03:44:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `s_a_a_s_subscriptions`
--

CREATE TABLE `s_a_a_s_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `s_a_a_s_subscriptions`
--

INSERT INTO `s_a_a_s_subscriptions` (`id`, `email`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'demo@example.com', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL),
(2, 'admin@example.com', '2026-08-13 11:56:14', '2026-08-13 11:56:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(30) NOT NULL,
  `designation` varchar(191) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) NOT NULL,
  `religion` varchar(20) DEFAULT NULL,
  `phone` varchar(25) NOT NULL,
  `blood` varchar(5) DEFAULT NULL,
  `sl` int(11) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `father_name` varchar(30) DEFAULT NULL,
  `mother_name` varchar(40) DEFAULT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `spouse_name` varchar(20) DEFAULT NULL,
  `passport_no` varchar(20) DEFAULT NULL,
  `tin_no` varchar(20) DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `permanent_address` longtext DEFAULT NULL,
  `access_key` varchar(25) DEFAULT NULL,
  `attachment` varchar(191) DEFAULT NULL,
  `training_attachment` varchar(191) DEFAULT NULL,
  `is_administrator` enum('0','1') NOT NULL DEFAULT '0',
  `is_visible_website` enum('0','1') NOT NULL DEFAULT '1',
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_signatures`
--

CREATE TABLE `teacher_signatures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `signature_image` varchar(191) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` longtext NOT NULL,
  `thumbnail_image` varchar(191) DEFAULT NULL,
  `video_url` varchar(191) DEFAULT NULL,
  `note` varchar(191) DEFAULT NULL,
  `ratting` int(11) NOT NULL DEFAULT 0,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `institute_id`, `branch_id`, `name`, `description`, `thumbnail_image`, `video_url`, `note`, `ratting`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Principal', 'Our institute focuses on quality education and student development.', NULL, NULL, 'Leadership feedback', 5, 1, '2026-09-14 08:51:39', NULL, NULL),
(2, 1, 1, 'Vice Principal', 'We maintain discipline and academic excellence for all students.', NULL, NULL, 'Administration review', 5, 1, '2026-09-14 08:52:07', NULL, NULL),
(3, 1, 1, 'Teacher', 'Teaching here is a wonderful experience with motivated students.', NULL, NULL, 'Staff feedback', 4, 1, '2026-09-14 08:53:55', NULL, NULL),
(4, 1, 1, 'Student Parent', 'My child has improved a lot after joining this institute.', NULL, NULL, 'Parent satisfaction', 5, 1, '2026-09-14 08:54:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `description` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_collections`
--

CREATE TABLE `transport_collections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `transport_member_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` varchar(191) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year, month collected for',
  `month` varchar(191) DEFAULT NULL COMMENT 'e.g. 2026-07, the billing month this collection covers',
  `fee_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_payable` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receive_ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_fee_configs`
--

CREATE TABLE `transport_fee_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Academic year',
  `route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stop_id` bigint(20) UNSIGNED DEFAULT NULL,
  `monthly_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fund_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ledger_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_members`
--

CREATE TABLE `transport_members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `member_type` varchar(20) NOT NULL DEFAULT 'student',
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_route_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_stop_id` bigint(20) UNSIGNED NOT NULL,
  `seat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transport_id` varchar(191) DEFAULT NULL,
  `fare_amount` decimal(8,2) DEFAULT 0.00,
  `status` smallint(6) NOT NULL DEFAULT 1 COMMENT '1=Active, 2=InActive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referral_code` varchar(20) DEFAULT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `username` varchar(192) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `image` varchar(192) DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `user_type` varchar(20) DEFAULT NULL,
  `facebook` varchar(192) DEFAULT NULL,
  `twitter` varchar(192) DEFAULT NULL,
  `linkedin` varchar(192) DEFAULT NULL,
  `google_plus` varchar(192) DEFAULT NULL,
  `user_status` enum('0','1') NOT NULL DEFAULT '1',
  `nid` varchar(191) DEFAULT NULL,
  `address` varchar(191) DEFAULT NULL,
  `platform` enum('APP','WEB') DEFAULT NULL,
  `device_info` longtext DEFAULT NULL,
  `last_active_time` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `referral_code`, `institute_id`, `branch_id`, `name`, `username`, `email`, `password`, `phone`, `image`, `role_id`, `email_verified_at`, `status`, `user_type`, `facebook`, `twitter`, `linkedin`, `google_plus`, `user_status`, `nid`, `address`, `platform`, `device_info`, `last_active_time`, `remember_token`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(6, NULL, 5, 5, 'Sardar Zulfiqar Ali Shah', NULL, 'zulfiqarkheshgi@gmail.com', '$2y$12$kWrlAC8JVfyaNysr6F9Bx.z0eFuj.OOmHE0bobP68hRTm6Pdg0.6W', '+923139038053', '2026-09-14-6aa7df2ac873d.png', 1, NULL, 1, 'Admin', NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-14 09:48:59', '2026-09-14 09:48:59'),
(7, 'MG0RQSQD', 6, 6, 'Sardar Zulfiqar Ali Shah', NULL, 'zulfiqarkheshgihome@gmail.com', '$2y$12$3KbyKL/55SNd3kxpwBAMyeRFYWmC46Pu0lsCazXTgmfwRE06ofZCe', '+923139038053', '2026-09-16-6aaa778e4784d.png', 1, NULL, 1, 'Admin', NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 'nT0HZXTyEr7uq2wIAMuago9qotxjmntwWTU9ZxUqUrbolAHFK2oUfEBVAp7W', NULL, NULL, '2026-09-16 08:09:19', '2026-09-19 07:53:21'),
(12, 'F1QAKEGE', 11, 11, 'admin', NULL, 'uape00@gmail.com', '$2y$12$597vFkhK0pJZH5ubtKvqJ.m6QRSSPPvEJot.2z42dG6Nd6Rsf9cgW', '03480233673', NULL, 1, NULL, 1, 'Admin', NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-17 10:24:24', '2026-09-18 05:02:48'),
(14, 'Z7C502J8', NULL, NULL, 'Umair Ali', NULL, 'umairkheshgi@gmail.com', '$2y$12$Ovq0p2J89aexhWF0GXNOZuBcx.wKdlw2kIXXSBqmUniTJtPK2YE.K', '00000000000', NULL, NULL, NULL, 1, 'SAAS Admin', NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-18 08:35:38', '2026-09-18 08:35:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(191) DEFAULT NULL,
  `action` enum('create','update','view','delete','fee_collect','payment','receipt') NOT NULL DEFAULT 'create',
  `detail` varchar(191) DEFAULT NULL,
  `previous_detail` varchar(191) DEFAULT NULL,
  `model` varchar(191) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `url` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_notices`
--

CREATE TABLE `user_notices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `notice_id` int(11) NOT NULL,
  `user_type` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_notices`
--

INSERT INTO `user_notices` (`id`, `institute_id`, `branch_id`, `notice_id`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 6, 6, 1, 'Admin', '2026-09-17 02:26:25', '2026-09-17 02:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_payrolls`
--

CREATE TABLE `user_payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `net_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_advance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_categories`
--

CREATE TABLE `vehicle_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_fitness_certificates`
--

CREATE TABLE `vehicle_fitness_certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bus_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_number` varchar(191) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `document_path` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_fuel_logs`
--

CREATE TABLE `vehicle_fuel_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bus_id` bigint(20) UNSIGNED NOT NULL,
  `fill_date` date NOT NULL,
  `quantity_liters` decimal(8,2) NOT NULL,
  `cost_amount` decimal(10,2) NOT NULL,
  `odometer_reading` int(10) UNSIGNED DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_insurances`
--

CREATE TABLE `vehicle_insurances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bus_id` bigint(20) UNSIGNED NOT NULL,
  `policy_number` varchar(191) DEFAULT NULL,
  `provider` varchar(191) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `premium_amount` decimal(10,2) DEFAULT NULL,
  `document_path` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_maintenance_logs`
--

CREATE TABLE `vehicle_maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bus_id` bigint(20) UNSIGNED NOT NULL,
  `service_date` date NOT NULL,
  `service_type` varchar(191) NOT NULL,
  `cost_amount` decimal(10,2) DEFAULT NULL,
  `vendor` varchar(191) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_tax_infos`
--

CREATE TABLE `vehicle_tax_infos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bus_id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(191) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `document_path` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_types`
--

CREATE TABLE `vehicle_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `status` varchar(191) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waivers`
--

CREATE TABLE `waivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `waiver` varchar(191) NOT NULL,
  `serial` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waivers`
--

INSERT INTO `waivers` (`id`, `institute_id`, `branch_id`, `waiver`, `serial`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Class Waiver', 1, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(2, 1, 1, 'Girl Waiver', 2, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(3, 1, 1, 'Merit Waiver', 3, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(4, 1, 1, 'Poor Waiver', 3, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(5, 1, 1, 'Scout Waiver', 4, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(6, 1, 1, 'BNCC Waiver', 5, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(7, 1, 1, 'Special Waiver', 6, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(8, 1, 1, 'Govt. Waiver', 7, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(9, 1, 1, 'Invention Waiver', 8, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(10, 1, 1, 'Creative Waiver', 9, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(11, 1, 1, 'Other Waiver', 10, '2026-08-13 11:56:14', '2026-08-13 11:56:14'),
(12, 6, 6, 'Brother Concession', NULL, '2026-09-16 12:35:10', '2026-09-16 12:35:10'),
(13, 6, 6, 'Orphan', NULL, '2026-09-16 12:35:17', '2026-09-16 12:35:17'),
(14, 6, 6, 'Talent', NULL, '2026-09-16 12:35:39', '2026-09-16 12:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `whats_app_logs`
--

CREATE TABLE `whats_app_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_type` varchar(191) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(191) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','queued','sent','failed') DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `retry_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whats_app_settings`
--

CREATE TABLE `whats_app_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provider` enum('meta','twilio') NOT NULL,
  `access_token` text DEFAULT NULL,
  `phone_number_id` varchar(191) DEFAULT NULL,
  `business_account_id` varchar(191) DEFAULT NULL,
  `language` varchar(191) NOT NULL DEFAULT 'en',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whats_app_settings`
--

INSERT INTO `whats_app_settings` (`id`, `institute_id`, `branch_id`, `provider`, `access_token`, `phone_number_id`, `business_account_id`, `language`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'meta', 'EAAbwzJcUfkIBRxuNpAGaLLeQZCF0qwXpjMlnpWiwlpn36QTN829CNX0g1EThaAFZA3ZByCgPnAxOUKLliZC75EahvqxO0ZApXhRuW3TFWIiLZCXlSGEEupcPb8RblRDzRbJZB7eTc8oVjRT1m2PLLx5hj8FZCQH6dxDZBaj08wn1ZBlZBIKn4US74H00WWOXBdONFitKyKzsD94XkuSBeaNOCi2ur6SPBEWPohjnjG3koR234HSiCu5M02mZCQNTtZBdypWh2K6xF6vc8xdBK2AOmVwPf', '1165478333321974', '1258886566157901', 'en', 1, '2026-08-13 11:53:33', '2026-08-13 11:53:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `whats_app_templates`
--

CREATE TABLE `whats_app_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `event` varchar(191) NOT NULL,
  `message` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `why_choose_us`
--

CREATE TABLE `why_choose_us` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `why_choose_us`
--

INSERT INTO `why_choose_us` (`id`, `institute_id`, `branch_id`, `title`, `description`, `icon`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Personalized Education', 'We tailor the learning experience to suit each student`s strengths and needs.', '2026-09-14-6aa7d07e9c5d1.png', '2026-09-14 08:46:22', NULL, NULL),
(2, 1, 1, 'Experienced Faculty', 'Our team of highly qualified teachers is dedicated to nurturing your child`s potential.', '2026-09-14-6aa7d096b1658.png', '2026-09-14 08:46:46', NULL, NULL),
(3, 1, 1, 'State-of-the-Art Facilities', 'From smart classrooms to recreational areas, we offer the best resources for students to thrive.', '2026-09-14-6aa7d0b2b13a0.png', '2026-09-14 08:47:14', NULL, NULL),
(4, 1, 1, 'Holistic Development', 'Beyond academics, we focus on sports, arts, and personal growth to prepare students for the future.', '2026-09-14-6aa7d0cb9cebf.png', '2026-09-14 08:47:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_requests`
--

CREATE TABLE `withdrawal_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payout_method` varchar(191) DEFAULT NULL,
  `payout_details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(191) DEFAULT NULL,
  `payment_reference` varchar(191) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_settings`
--

CREATE TABLE `withdrawal_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `scope_type` enum('platform','institute') NOT NULL,
  `scope_id` bigint(20) UNSIGNED DEFAULT NULL,
  `min_amount` decimal(12,2) DEFAULT NULL,
  `max_amount` decimal(12,2) DEFAULT NULL,
  `enabled_methods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`enabled_methods`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawal_settings`
--

INSERT INTO `withdrawal_settings` (`id`, `scope_type`, `scope_id`, `min_amount`, `max_amount`, `enabled_methods`, `created_at`, `updated_at`) VALUES
(1, 'platform', NULL, 50.00, NULL, '[\"bank_transfer\"]', '2026-09-18 14:58:25', '2026-09-18 14:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `zoom_meetings`
--

CREATE TABLE `zoom_meetings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `institute_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `section_id` bigint(20) UNSIGNED DEFAULT NULL,
  `meeting_id` varchar(191) NOT NULL,
  `topic` text NOT NULL,
  `agenda` text DEFAULT NULL,
  `start_time` varchar(191) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `join_url` longtext NOT NULL,
  `start_url` longtext NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`id`),
  ADD KEY `about_us_institute_id_foreign` (`institute_id`),
  ADD KEY `about_us_branch_id_foreign` (`branch_id`),
  ADD KEY `about_us_created_by_foreign` (`created_by`),
  ADD KEY `about_us_title_index` (`title`);

--
-- Indexes for table `absent_fines`
--
ALTER TABLE `absent_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `absent_fines_institute_id_foreign` (`institute_id`),
  ADD KEY `absent_fines_branch_id_foreign` (`branch_id`),
  ADD KEY `absent_fines_class_id_index` (`class_id`);

--
-- Indexes for table `academic_images`
--
ALTER TABLE `academic_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `academic_images_institute_id_foreign` (`institute_id`),
  ADD KEY `academic_images_created_by_foreign` (`created_by`);

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `accounting_categories`
--
ALTER TABLE `accounting_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`name`),
  ADD KEY `accounting_categories_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `accounting_funds`
--
ALTER TABLE `accounting_funds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`name`),
  ADD KEY `accounting_funds_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `accounting_groups`
--
ALTER TABLE `accounting_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`accounting_category_id`,`name`),
  ADD KEY `accounting_groups_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `accounting_ledgers`
--
ALTER TABLE `accounting_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`ledger_name`),
  ADD KEY `accounting_ledgers_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_transactions_institute_id_foreign` (`institute_id`),
  ADD KEY `account_transactions_branch_id_foreign` (`branch_id`),
  ADD KEY `account_transactions_voucher_id_fund_id_index` (`voucher_id`,`fund_id`);

--
-- Indexes for table `account_transaction_details`
--
ALTER TABLE `account_transaction_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_transaction_details_institute_id_foreign` (`institute_id`),
  ADD KEY `account_transaction_details_branch_id_foreign` (`branch_id`),
  ADD KEY `account_transaction_details_account_transactions_id_index` (`account_transactions_id`);

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admission_forms`
--
ALTER TABLE `admission_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_forms_institute_id_class_id_index` (`institute_id`,`class_id`),
  ADD KEY `admission_forms_status_index` (`status`);

--
-- Indexes for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_conversations_branch_id_foreign` (`branch_id`),
  ADD KEY `ai_conversations_user_id_foreign` (`user_id`),
  ADD KEY `ai_conversations_institute_id_branch_id_user_id_feature_index` (`institute_id`,`branch_id`,`user_id`,`feature`);

--
-- Indexes for table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_messages_institute_id_foreign` (`institute_id`),
  ADD KEY `ai_messages_branch_id_foreign` (`branch_id`),
  ADD KEY `ai_messages_ai_conversation_id_foreign` (`ai_conversation_id`);

--
-- Indexes for table `assessment_domains`
--
ALTER TABLE `assessment_domains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_domains_branch_id_foreign` (`branch_id`),
  ADD KEY `assessment_domains_tenant_type_idx` (`institute_id`,`branch_id`,`domain_type`);

--
-- Indexes for table `assessment_items`
--
ALTER TABLE `assessment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assessment_items_institute_id_foreign` (`institute_id`),
  ADD KEY `assessment_items_branch_id_foreign` (`branch_id`),
  ADD KEY `assessment_items_domain_active_idx` (`assessment_domain_id`,`is_active`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignments_institute_id_foreign` (`institute_id`),
  ADD KEY `assignments_branch_id_foreign` (`branch_id`),
  ADD KEY `assignments_class_id_section_id_index` (`class_id`,`section_id`);

--
-- Indexes for table `assignment_submits`
--
ALTER TABLE `assignment_submits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_submits_institute_id_foreign` (`institute_id`),
  ADD KEY `assignment_submits_branch_id_foreign` (`branch_id`),
  ADD KEY `assignment_submits_reviewed_by_foreign` (`reviewed_by`);

--
-- Indexes for table `assign_shifts`
--
ALTER TABLE `assign_shifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assign_shifts_teacher_shift_unique` (`teacher_id`,`shift_id`),
  ADD KEY `assign_shifts_institute_id_foreign` (`institute_id`),
  ADD KEY `assign_shifts_branch_id_foreign` (`branch_id`),
  ADD KEY `assign_shifts_teacher_id_shift_id_index` (`teacher_id`,`shift_id`);

--
-- Indexes for table `assign_subjects`
--
ALTER TABLE `assign_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assign_subjects_subject_id_section_id_unique` (`subject_id`,`section_id`),
  ADD KEY `assign_subjects_institute_id_foreign` (`institute_id`),
  ADD KEY `assign_subjects_branch_id_foreign` (`branch_id`),
  ADD KEY `assign_subjects_teacher_id_foreign` (`teacher_id`),
  ADD KEY `assign_subjects_section_id_foreign` (`section_id`),
  ADD KEY `assign_subjects_subject_id_index` (`subject_id`);

--
-- Indexes for table `attendance_fines`
--
ALTER TABLE `attendance_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_fines_institute_id_foreign` (`institute_id`),
  ADD KEY `attendance_fines_branch_id_foreign` (`branch_id`),
  ADD KEY `attendance_fines_student_id_index` (`student_id`);

--
-- Indexes for table `attendance_waivers`
--
ALTER TABLE `attendance_waivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_waivers_institute_id_foreign` (`institute_id`),
  ADD KEY `attendance_waivers_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `banners_institute_id_foreign` (`institute_id`),
  ADD KEY `banners_branch_id_foreign` (`branch_id`),
  ADD KEY `banners_created_by_foreign` (`created_by`);

--
-- Indexes for table `beds`
--
ALTER TABLE `beds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `beds_room_id_bed_number_unique` (`room_id`,`bed_number`);

--
-- Indexes for table `behaviors`
--
ALTER TABLE `behaviors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `behaviors_institute_id_foreign` (`institute_id`),
  ADD KEY `behaviors_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_categories_institute_slug_unq` (`institute_id`,`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_posts_institute_slug_unq` (`institute_id`,`slug`),
  ADD KEY `blog_posts_inst_status_pub_idx` (`institute_id`,`status`,`published_at`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `books_institute_id_foreign` (`institute_id`),
  ADD KEY `books_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `book_categories`
--
ALTER TABLE `book_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_categories_institute_id_foreign` (`institute_id`),
  ADD KEY `book_categories_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `book_issues`
--
ALTER TABLE `book_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_issues_institute_id_foreign` (`institute_id`),
  ADD KEY `book_issues_branch_id_foreign` (`branch_id`),
  ADD KEY `book_issues_book_id_library_id_user_id_index` (`book_id`,`library_id`,`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branches_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buses_institute_id_foreign` (`institute_id`),
  ADD KEY `buses_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `bus_routes`
--
ALTER TABLE `bus_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bus_routes_institute_id_foreign` (`institute_id`),
  ADD KEY `bus_routes_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `bus_seats`
--
ALTER TABLE `bus_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bus_seats_bus_id_seat_number_unique` (`bus_id`,`seat_number`);

--
-- Indexes for table `bus_stops`
--
ALTER TABLE `bus_stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bus_stops_institute_id_foreign` (`institute_id`),
  ADD KEY `bus_stops_branch_id_foreign` (`branch_id`),
  ADD KEY `bus_stops_route_id_foreign` (`route_id`);

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
-- Indexes for table `certificate_issues`
--
ALTER TABLE `certificate_issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_issues_verification_code_unique` (`verification_code`),
  ADD KEY `certificate_issues_institute_id_foreign` (`institute_id`),
  ADD KEY `certificate_issues_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `certificate_templates_institute_id_foreign` (`institute_id`),
  ADD KEY `certificate_templates_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chapters_institute_id_foreign` (`institute_id`),
  ADD KEY `chapters_course_id_foreign` (`course_id`),
  ADD KEY `chapters_created_by_foreign` (`created_by`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `classes_institute_id_foreign` (`institute_id`),
  ADD KEY `classes_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `class_assigns`
--
ALTER TABLE `class_assigns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_assigns_teacher_class_unique` (`teacher_id`,`class_id`),
  ADD KEY `class_assigns_institute_id_foreign` (`institute_id`),
  ADD KEY `class_assigns_branch_id_foreign` (`branch_id`),
  ADD KEY `class_assigns_teacher_id_class_id_index` (`teacher_id`,`class_id`);

--
-- Indexes for table `class_days`
--
ALTER TABLE `class_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_days_institute_id_foreign` (`institute_id`),
  ADD KEY `class_days_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `class_exams`
--
ALTER TABLE `class_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_exams_institute_id_foreign` (`institute_id`),
  ADD KEY `class_exams_branch_id_foreign` (`branch_id`),
  ADD KEY `class_exams_class_id_foreign` (`class_id`),
  ADD KEY `class_exams_exam_id_foreign` (`exam_id`),
  ADD KEY `class_exams_merit_process_type_id_foreign` (`merit_process_type_id`);

--
-- Indexes for table `class_lessons`
--
ALTER TABLE `class_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_lessons_institute_id_foreign` (`institute_id`),
  ADD KEY `class_lessons_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `class_routines`
--
ALTER TABLE `class_routines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `start_end_teacher_unique` (`day`,`start_time`,`end_time`,`teacher_id`),
  ADD KEY `class_routines_institute_id_foreign` (`institute_id`),
  ADD KEY `class_routines_branch_id_foreign` (`branch_id`),
  ADD KEY `class_routines_class_id_foreign` (`class_id`),
  ADD KEY `class_routines_subject_id_foreign` (`subject_id`),
  ADD KEY `class_routines_section_id_subject_id_teacher_id_index` (`section_id`,`subject_id`,`teacher_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_institute_id_foreign` (`institute_id`),
  ADD KEY `contacts_user_id_isseen_index` (`user_id`,`isSeen`);

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contents_chapter_id_type_type_id_unique` (`chapter_id`,`type`,`type_id`),
  ADD KEY `contents_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `content_visibility`
--
ALTER TABLE `content_visibility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_visibility_institute_id_foreign` (`institute_id`),
  ADD KEY `content_visibility_course_id_foreign` (`course_id`),
  ADD KEY `content_visibility_chapter_id_foreign` (`chapter_id`),
  ADD KEY `content_visibility_created_by_foreign` (`created_by`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_institute_id_foreign` (`institute_id`),
  ADD KEY `courses_branch_id_foreign` (`branch_id`),
  ADD KEY `courses_course_category_id_foreign` (`course_category_id`),
  ADD KEY `courses_course_sub_category_id_foreign` (`course_sub_category_id`),
  ADD KEY `courses_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_categories_institute_id_foreign` (`institute_id`),
  ADD KEY `course_categories_parent_id_foreign` (`parent_id`),
  ADD KEY `course_categories_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_faqs`
--
ALTER TABLE `course_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_faqs_institute_id_foreign` (`institute_id`),
  ADD KEY `course_faqs_course_id_foreign` (`course_id`),
  ADD KEY `course_faqs_created_by_foreign` (`created_by`);

--
-- Indexes for table `course_features`
--
ALTER TABLE `course_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_features_institute_id_foreign` (`institute_id`),
  ADD KEY `course_features_course_id_foreign` (`course_id`),
  ADD KEY `course_features_created_by_foreign` (`created_by`);

--
-- Indexes for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_fields_institute_id_branch_id_module_index` (`institute_id`,`branch_id`,`module`),
  ADD KEY `custom_fields_institute_id_index` (`institute_id`),
  ADD KEY `custom_fields_branch_id_index` (`branch_id`),
  ADD KEY `custom_fields_field_name_index` (`field_name`);

--
-- Indexes for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_field_values_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `custom_field_values_institute_id_index` (`institute_id`),
  ADD KEY `custom_field_values_branch_id_index` (`branch_id`),
  ADD KEY `custom_field_values_custom_field_id_index` (`custom_field_id`);

--
-- Indexes for table `custom_menus`
--
ALTER TABLE `custom_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_menus_institute_id_parent_id_status_sort_order_index` (`institute_id`,`parent_id`,`status`,`sort_order`);

--
-- Indexes for table `demo_restrictions`
--
ALTER TABLE `demo_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demo_restrictions_key_unique` (`key`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`department_name`),
  ADD KEY `departments_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `device_controls`
--
ALTER TABLE `device_controls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_controls_student_id_foreign` (`student_id`);

--
-- Indexes for table `director_messages`
--
ALTER TABLE `director_messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `director_messages_institute_unq` (`institute_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `drivers_institute_id_foreign` (`institute_id`),
  ADD KEY `drivers_branch_id_foreign` (`branch_id`),
  ADD KEY `drivers_assigned_bus_id_foreign` (`assigned_bus_id`);

--
-- Indexes for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_documents_driver_id_foreign` (`driver_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollments_course_id_user_id_unique` (`course_id`,`user_id`),
  ADD KEY `enrollments_institute_id_foreign` (`institute_id`),
  ADD KEY `enrollments_user_id_foreign` (`user_id`),
  ADD KEY `enrollments_payment_request_idx` (`payment_request_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_institute_id_foreign` (`institute_id`),
  ADD KEY `events_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `event_enrollments`
--
ALTER TABLE `event_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_enrollments_ticket_code_unique` (`ticket_code`),
  ADD KEY `event_enrollments_event_id_payment_status_index` (`event_id`,`payment_status`),
  ADD KEY `event_enrollments_payment_request_id_index` (`payment_request_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`exam_code`),
  ADD KEY `exams_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `exam_attendances`
--
ALTER TABLE `exam_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_attendance_unique` (`exam_id`,`subject_id`,`student_id`,`date`),
  ADD KEY `exam_attendances_institute_id_foreign` (`institute_id`),
  ADD KEY `exam_attendances_branch_id_foreign` (`branch_id`),
  ADD KEY `exam_attendances_class_id_section_id_exam_id_index` (`class_id`,`section_id`,`exam_id`);

--
-- Indexes for table `exam_codes`
--
ALTER TABLE `exam_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_codes_institute_id_foreign` (`institute_id`),
  ADD KEY `exam_codes_branch_id_foreign` (`branch_id`),
  ADD KEY `exam_codes_class_model_id_foreign` (`class_model_id`),
  ADD KEY `exam_codes_short_code_id_foreign` (`short_code_id`);

--
-- Indexes for table `exam_grades`
--
ALTER TABLE `exam_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_grades_institute_id_foreign` (`institute_id`),
  ADD KEY `exam_grades_branch_id_foreign` (`branch_id`),
  ADD KEY `exam_grades_class_model_id_foreign` (`class_model_id`),
  ADD KEY `exam_grades_grade_id_foreign` (`grade_id`);

--
-- Indexes for table `exam_marks`
--
ALTER TABLE `exam_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_marks_unique_lookup` (`institute_id`,`branch_id`,`session_id`,`student_id`,`exam_id`,`subject_id`),
  ADD KEY `exam_marks_branch_id_foreign` (`branch_id`),
  ADD KEY `exam_marks_student_id_foreign` (`student_id`),
  ADD KEY `exam_marks_class_id_foreign` (`class_id`),
  ADD KEY `exam_marks_subject_id_foreign` (`subject_id`),
  ADD KEY `exam_marks_exam_id_foreign` (`exam_id`),
  ADD KEY `exam_marks_class_exam_idx` (`institute_id`,`class_id`,`exam_id`),
  ADD KEY `exam_marks_group_id_foreign` (`group_id`);

--
-- Indexes for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_schedules_exam_class_subject_unique` (`exam_id`,`class_id`,`subject_id`),
  ADD KEY `exam_schedules_institute_id_foreign` (`institute_id`),
  ADD KEY `exam_schedules_branch_id_foreign` (`branch_id`),
  ADD KEY `exam_schedules_class_id_subject_id_exam_id_index` (`class_id`,`subject_id`,`exam_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faq_questions`
--
ALTER TABLE `faq_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faq_questions_institute_id_foreign` (`institute_id`),
  ADD KEY `faq_questions_branch_id_foreign` (`branch_id`),
  ADD KEY `faq_questions_created_by_foreign` (`created_by`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fees_institute_id_foreign` (`institute_id`),
  ADD KEY `fees_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `fee_date_configs`
--
ALTER TABLE `fee_date_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_date_configs_institute_id_foreign` (`institute_id`),
  ADD KEY `fee_date_configs_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `fee_heads`
--
ALTER TABLE `fee_heads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`name`),
  ADD KEY `fee_heads_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `fee_maps`
--
ALTER TABLE `fee_maps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_maps_institute_id_foreign` (`institute_id`),
  ADD KEY `fee_maps_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `fee_map_fee_sub_head`
--
ALTER TABLE `fee_map_fee_sub_head`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_map_fund`
--
ALTER TABLE `fee_map_fund`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_sub_heads`
--
ALTER TABLE `fee_sub_heads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`fee_head_id`,`name`),
  ADD KEY `fee_sub_heads_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `form_builder_fields`
--
ALTER TABLE `form_builder_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_builder_fields_form_id_field_key_unique` (`form_id`,`field_key`),
  ADD KEY `form_builder_fields_form_id_index` (`form_id`);

--
-- Indexes for table `form_builder_forms`
--
ALTER TABLE `form_builder_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_builder_forms_institute_id_slug_unique` (`institute_id`,`slug`),
  ADD KEY `form_builder_forms_institute_id_status_show_on_homepage_index` (`institute_id`,`status`,`show_on_homepage`);

--
-- Indexes for table `form_builder_submissions`
--
ALTER TABLE `form_builder_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_builder_submissions_form_id_institute_id_index` (`form_id`,`institute_id`);

--
-- Indexes for table `gamifications`
--
ALTER TABLE `gamifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gamifications_institute_id_foreign` (`institute_id`),
  ADD KEY `gamifications_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `google_meet_meetings`
--
ALTER TABLE `google_meet_meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `google_meet_meetings_institute_id_branch_id_index` (`institute_id`,`branch_id`),
  ADD KEY `google_meet_meetings_class_id_section_id_index` (`class_id`,`section_id`),
  ADD KEY `google_meet_meetings_teacher_id_index` (`teacher_id`),
  ADD KEY `google_meet_meetings_start_date_index` (`start_date`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grades_institute_id_foreign` (`institute_id`),
  ADD KEY `grades_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `grand_final_class_exams`
--
ALTER TABLE `grand_final_class_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grand_final_class_exams_institute_id_foreign` (`institute_id`),
  ADD KEY `grand_final_class_exams_branch_id_foreign` (`branch_id`),
  ADD KEY `grand_final_class_exams_class_id_foreign` (`class_id`),
  ADD KEY `grand_final_class_exams_exam_id_foreign` (`exam_id`);

--
-- Indexes for table `grand_final_settings`
--
ALTER TABLE `grand_final_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `grand_final_settings_unique` (`institute_id`,`branch_id`,`session_id`,`class_id`),
  ADD KEY `grand_final_settings_branch_id_foreign` (`branch_id`),
  ADD KEY `grand_final_settings_class_id_foreign` (`class_id`);

--
-- Indexes for table `helpers`
--
ALTER TABLE `helpers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `homepage_sections_inst_key_unq` (`institute_id`,`section_key`);

--
-- Indexes for table `home_sections`
--
ALTER TABLE `home_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostels_institute_id_foreign` (`institute_id`),
  ADD KEY `hostels_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `hostel_bills`
--
ALTER TABLE `hostel_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_bills_institute_id_foreign` (`institute_id`),
  ADD KEY `hostel_bills_branch_id_foreign` (`branch_id`),
  ADD KEY `hostel_bills_student_id_foreign` (`student_id`);

--
-- Indexes for table `hostel_buildings`
--
ALTER TABLE `hostel_buildings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_buildings_hostel_id_foreign` (`hostel_id`);

--
-- Indexes for table `hostel_categories`
--
ALTER TABLE `hostel_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_categories_institute_id_foreign` (`institute_id`),
  ADD KEY `hostel_categories_branch_id_foreign` (`branch_id`),
  ADD KEY `hostel_categories_hostel_id_foreign` (`hostel_id`);

--
-- Indexes for table `hostel_collections`
--
ALTER TABLE `hostel_collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hostel_collections_invoice_id_unique` (`invoice_id`),
  ADD KEY `hostel_collections_room_member_id_foreign` (`room_member_id`),
  ADD KEY `hostel_collections_student_id_session_id_index` (`student_id`,`session_id`);

--
-- Indexes for table `hostel_collection_details`
--
ALTER TABLE `hostel_collection_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_collection_details_hostel_collection_id_foreign` (`hostel_collection_id`);

--
-- Indexes for table `hostel_deposit_refunds`
--
ALTER TABLE `hostel_deposit_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_deposit_refunds_hostel_collection_detail_id_foreign` (`hostel_collection_detail_id`);

--
-- Indexes for table `hostel_fee_configs`
--
ALTER TABLE `hostel_fee_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_fee_configs_hostel_category_id_foreign` (`hostel_category_id`);

--
-- Indexes for table `hostel_floors`
--
ALTER TABLE `hostel_floors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_floors_building_id_foreign` (`building_id`);

--
-- Indexes for table `hostel_leaves`
--
ALTER TABLE `hostel_leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_leaves_room_member_id_foreign` (`room_member_id`);

--
-- Indexes for table `hostel_members`
--
ALTER TABLE `hostel_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_members_institute_id_foreign` (`institute_id`),
  ADD KEY `hostel_members_branch_id_foreign` (`branch_id`),
  ADD KEY `hostel_members_hostel_category_id_foreign` (`hostel_category_id`),
  ADD KEY `hostel_members_student_id_foreign` (`student_id`);

--
-- Indexes for table `impersonation_tokens`
--
ALTER TABLE `impersonation_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `impersonation_tokens_token_hash_unique` (`token_hash`),
  ADD KEY `impersonation_tokens_institute_id_index` (`institute_id`),
  ADD KEY `impersonation_tokens_impersonator_id_index` (`impersonator_id`);

--
-- Indexes for table `institutes`
--
ALTER TABLE `institutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `institutes_email_unique` (`email`),
  ADD UNIQUE KEY `institutes_referral_code_unique` (`referral_code`),
  ADD UNIQUE KEY `institutes_domain_unique` (`domain`),
  ADD KEY `institutes_owner_id_foreign` (`owner_id`),
  ADD KEY `institutes_assigned_to_foreign` (`assigned_to`),
  ADD KEY `institutes_created_by_foreign` (`created_by`),
  ADD KEY `institutes_updated_by_foreign` (`updated_by`),
  ADD KEY `institutes_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `institute_image_settings`
--
ALTER TABLE `institute_image_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institute_image_settings_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `institute_image_s_a_a_s_settings`
--
ALTER TABLE `institute_image_s_a_a_s_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `institute_referral_settings`
--
ALTER TABLE `institute_referral_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `irs_institute_role_type_unique` (`institute_id`,`referrer_role`,`referral_type`);

--
-- Indexes for table `institute_types`
--
ALTER TABLE `institute_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internal_referrals`
--
ALTER TABLE `internal_referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internal_referrals_institute_id_referrer_user_id_index` (`institute_id`,`referrer_user_id`),
  ADD KEY `internal_referrals_institute_id_referred_user_id_index` (`institute_id`,`referred_user_id`),
  ADD KEY `internal_referrals_referrer_user_id_foreign` (`referrer_user_id`),
  ADD KEY `internal_referrals_referred_user_id_foreign` (`referred_user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_types_institute_id_foreign` (`institute_id`),
  ADD KEY `leave_types_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessons_institute_id_foreign` (`institute_id`),
  ADD KEY `lessons_course_id_foreign` (`course_id`),
  ADD KEY `lessons_chapter_id_foreign` (`chapter_id`),
  ADD KEY `lessons_created_by_foreign` (`created_by`);

--
-- Indexes for table `library_fines`
--
ALTER TABLE `library_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `library_fines_institute_id_foreign` (`institute_id`),
  ADD KEY `library_fines_branch_id_foreign` (`branch_id`),
  ADD KEY `library_fines_book_issue_id_library_id_index` (`book_issue_id`,`library_id`);

--
-- Indexes for table `library_members`
--
ALTER TABLE `library_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `library_members_user_id_unique` (`user_id`),
  ADD KEY `library_members_institute_id_foreign` (`institute_id`),
  ADD KEY `library_members_branch_id_foreign` (`branch_id`),
  ADD KEY `library_members_teacher_id_foreign` (`teacher_id`),
  ADD KEY `library_members_student_id_foreign` (`student_id`),
  ADD KEY `library_members_staff_id_foreign` (`staff_id`),
  ADD KEY `library_members_user_id_library_id_index` (`user_id`,`library_id`);

--
-- Indexes for table `lms_assignments`
--
ALTER TABLE `lms_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lms_assignments_institute_id_foreign` (`institute_id`),
  ADD KEY `lms_assignments_course_id_foreign` (`course_id`),
  ADD KEY `lms_assignments_chapter_id_foreign` (`chapter_id`),
  ADD KEY `lms_assignments_created_by_foreign` (`created_by`);

--
-- Indexes for table `lms_assignment_results`
--
ALTER TABLE `lms_assignment_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lms_assignment_results_institute_id_foreign` (`institute_id`),
  ADD KEY `lms_assignment_results_assignment_id_foreign` (`assignment_id`),
  ADD KEY `lms_assignment_results_user_id_foreign` (`user_id`);

--
-- Indexes for table `lms_class_routines`
--
ALTER TABLE `lms_class_routines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lms_class_routines_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `lms_class_routine_days`
--
ALTER TABLE `lms_class_routine_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lms_class_routine_days_routine_id_foreign` (`routine_id`),
  ADD KEY `lms_class_routine_days_day_id_foreign` (`day_id`);

--
-- Indexes for table `lms_days`
--
ALTER TABLE `lms_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lms_days_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `lms_rooms`
--
ALTER TABLE `lms_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lms_rooms_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `lms_zoom_meetings`
--
ALTER TABLE `lms_zoom_meetings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`meeting_id`),
  ADD KEY `lms_zoom_meetings_course_id_foreign` (`course_id`),
  ADD KEY `lms_zoom_meetings_created_by_foreign` (`created_by`);

--
-- Indexes for table `mark_configs`
--
ALTER TABLE `mark_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mark_configs_institute_id_foreign` (`institute_id`),
  ADD KEY `mark_configs_branch_id_foreign` (`branch_id`),
  ADD KEY `mark_configs_class_id_foreign` (`class_id`),
  ADD KEY `mark_configs_group_id_foreign` (`group_id`),
  ADD KEY `mark_configs_subject_id_foreign` (`subject_id`),
  ADD KEY `mark_configs_exam_id_foreign` (`exam_id`),
  ADD KEY `mark_configs_mark_config_exam_code_id_foreign` (`mark_config_exam_code_id`);

--
-- Indexes for table `mark_config_exam_codes`
--
ALTER TABLE `mark_config_exam_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mark_config_exam_codes_title_subject_id_unique` (`title`,`subject_id`),
  ADD KEY `mark_config_exam_codes_institute_id_foreign` (`institute_id`),
  ADD KEY `mark_config_exam_codes_branch_id_foreign` (`branch_id`),
  ADD KEY `mark_config_exam_codes_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meals_institute_id_foreign` (`institute_id`),
  ADD KEY `meals_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `meal_entries`
--
ALTER TABLE `meal_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_entries_institute_id_foreign` (`institute_id`),
  ADD KEY `meal_entries_branch_id_foreign` (`branch_id`),
  ADD KEY `meal_entries_student_id_foreign` (`student_id`),
  ADD KEY `meal_entries_meal_id_foreign` (`meal_id`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_plans_institute_id_foreign` (`institute_id`),
  ADD KEY `meal_plans_branch_id_foreign` (`branch_id`),
  ADD KEY `meal_plans_student_id_foreign` (`student_id`),
  ADD KEY `meal_plans_meal_id_foreign` (`meal_id`);

--
-- Indexes for table `merit_process_types`
--
ALTER TABLE `merit_process_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merit_process_types_institute_id_foreign` (`institute_id`),
  ADD KEY `merit_process_types_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mobile_app_sections`
--
ALTER TABLE `mobile_app_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mobile_app_sections_institute_id_foreign` (`institute_id`),
  ADD KEY `mobile_app_sections_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notices_institute_id_foreign` (`institute_id`),
  ADD KEY `notices_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `onboardings`
--
ALTER TABLE `onboardings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `onboardings_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `online_exams`
--
ALTER TABLE `online_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `online_exams_branch_id_foreign` (`branch_id`),
  ADD KEY `online_exams_exam_id_foreign` (`exam_id`),
  ADD KEY `online_exams_class_id_foreign` (`class_id`),
  ADD KEY `online_exams_subject_id_foreign` (`subject_id`),
  ADD KEY `online_exams_lookup_idx` (`institute_id`,`exam_id`,`class_id`,`subject_id`);

--
-- Indexes for table `online_exam_attempts`
--
ALTER TABLE `online_exam_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `online_exam_attempts_institute_id_foreign` (`institute_id`),
  ADD KEY `online_exam_attempts_branch_id_foreign` (`branch_id`),
  ADD KEY `online_exam_attempts_student_id_foreign` (`student_id`),
  ADD KEY `online_exam_attempts_lookup_idx` (`online_exam_id`,`student_id`,`status`);

--
-- Indexes for table `online_exam_mark_transfers`
--
ALTER TABLE `online_exam_mark_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `online_exam_mark_transfers_unique` (`online_exam_id`,`student_id`),
  ADD KEY `online_exam_mark_transfers_institute_id_foreign` (`institute_id`),
  ADD KEY `online_exam_mark_transfers_branch_id_foreign` (`branch_id`),
  ADD KEY `online_exam_mark_transfers_student_id_foreign` (`student_id`);

--
-- Indexes for table `online_exam_questions`
--
ALTER TABLE `online_exam_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `online_exam_questions_institute_id_foreign` (`institute_id`),
  ADD KEY `online_exam_questions_branch_id_foreign` (`branch_id`),
  ADD KEY `online_exam_questions_exam_idx` (`online_exam_id`,`is_active`);

--
-- Indexes for table `optional_subject_configs`
--
ALTER TABLE `optional_subject_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `optional_subject_configs_institute_id_foreign` (`institute_id`),
  ADD KEY `optional_subject_configs_branch_id_foreign` (`branch_id`),
  ADD KEY `optional_subject_configs_class_id_foreign` (`class_id`),
  ADD KEY `optional_subject_configs_group_id_foreign` (`group_id`);

--
-- Indexes for table `optional_subject_items`
--
ALTER TABLE `optional_subject_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `optional_subject_items_institute_id_foreign` (`institute_id`),
  ADD KEY `optional_subject_items_branch_id_foreign` (`branch_id`),
  ADD KEY `optional_subject_items_optional_subject_config_id_foreign` (`optional_subject_config_id`),
  ADD KEY `optional_subject_items_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otp_verifications_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `our_histories`
--
ALTER TABLE `our_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `our_histories_institute_id_foreign` (`institute_id`),
  ADD KEY `our_histories_branch_id_foreign` (`branch_id`),
  ADD KEY `our_histories_created_by_foreign` (`created_by`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_page_slug` (`institute_id`,`branch_id`,`slug`),
  ADD KEY `pages_branch_id_foreign` (`branch_id`),
  ADD KEY `pages_author_id_foreign` (`author_id`);

--
-- Indexes for table `parent_models`
--
ALTER TABLE `parent_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_models_institute_id_foreign` (`institute_id`),
  ADD KEY `parent_models_branch_id_foreign` (`branch_id`),
  ADD KEY `parent_models_student_id_foreign` (`student_id`),
  ADD KEY `parent_models_user_id_parent_phone_index` (`user_id`,`parent_phone`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_institute_id_foreign` (`institute_id`),
  ADD KEY `payments_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `payment_histories`
--
ALTER TABLE `payment_histories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_histories_invoice_number_unique` (`invoice_number`),
  ADD UNIQUE KEY `payment_histories_user_id_course_id_date_issued_unique` (`user_id`,`course_id`,`date_issued`),
  ADD KEY `payment_histories_institute_id_foreign` (`institute_id`),
  ADD KEY `payment_histories_course_id_foreign` (`course_id`),
  ADD KEY `payment_histories_payment_method_id_foreign` (`payment_method_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_methods_institute_id_foreign` (`institute_id`),
  ADD KEY `payment_methods_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_requests_institute_id_foreign` (`institute_id`),
  ADD KEY `payment_requests_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `payroll_accounting_mappings`
--
ALTER TABLE `payroll_accounting_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_accounting_mappings_ledger_id_fund_id_index` (`ledger_id`,`fund_id`),
  ADD KEY `payroll_accounting_mappings_institute_id_branch_id_index` (`institute_id`,`branch_id`);

--
-- Indexes for table `payslip_invoices`
--
ALTER TABLE `payslip_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslip_invoices_institute_id_foreign` (`institute_id`),
  ADD KEY `payslip_invoices_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `payslip_salaries`
--
ALTER TABLE `payslip_salaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payslip_salaries_tenant_user_period_unique` (`institute_id`,`branch_id`,`user_id`,`year`,`month`),
  ADD KEY `payslip_salaries_branch_id_foreign` (`branch_id`),
  ADD KEY `payslip_salaries_user_id_index` (`user_id`);

--
-- Indexes for table `payslip_salary_heads`
--
ALTER TABLE `payslip_salary_heads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslip_salary_heads_institute_id_foreign` (`institute_id`),
  ADD KEY `payslip_salary_heads_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `periods`
--
ALTER TABLE `periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`period`),
  ADD KEY `periods_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `phone_books`
--
ALTER TABLE `phone_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_books_institute_id_foreign` (`institute_id`),
  ADD KEY `phone_books_branch_id_foreign` (`branch_id`),
  ADD KEY `phone_books_category_id_index` (`category_id`);

--
-- Indexes for table `phone_book_categories`
--
ALTER TABLE `phone_book_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_book_categories_institute_id_foreign` (`institute_id`),
  ADD KEY `phone_book_categories_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `picklists`
--
ALTER TABLE `picklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `picklists_institute_id_foreign` (`institute_id`),
  ADD KEY `picklists_branch_id_foreign` (`branch_id`),
  ADD KEY `picklists_type_index` (`type`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `policies_institute_id_foreign` (`institute_id`),
  ADD KEY `policies_branch_id_foreign` (`branch_id`),
  ADD KEY `policies_created_by_foreign` (`created_by`);

--
-- Indexes for table `popups`
--
ALTER TABLE `popups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `popups_institute_id_status_start_at_end_at_index` (`institute_id`,`status`,`start_at`,`end_at`);

--
-- Indexes for table `prayers`
--
ALTER TABLE `prayers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prayers_institute_id_foreign` (`institute_id`),
  ADD KEY `prayers_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_question_bank_class_id_foreign` (`question_bank_class_id`),
  ADD KEY `questions_question_bank_group_id_foreign` (`question_bank_group_id`),
  ADD KEY `questions_question_category_id_foreign` (`question_category_id`),
  ADD KEY `questions_question_bank_subject_id_foreign` (`question_bank_subject_id`),
  ADD KEY `questions_question_bank_chapter_id_foreign` (`question_bank_chapter_id`),
  ADD KEY `questions_question_bank_difficulty_level_id_foreign` (`question_bank_difficulty_level_id`),
  ADD KEY `questions_institute_id_foreign` (`institute_id`),
  ADD KEY `questions_created_by_foreign` (`created_by`);

--
-- Indexes for table `question_bank_boards`
--
ALTER TABLE `question_bank_boards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_chapters`
--
ALTER TABLE `question_bank_chapters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_bank_chapters_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `question_bank_classes`
--
ALTER TABLE `question_bank_classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_difficulty_levels`
--
ALTER TABLE `question_bank_difficulty_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_groups`
--
ALTER TABLE `question_bank_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_bank_groups_class_id_foreign` (`class_id`);

--
-- Indexes for table `question_bank_levels`
--
ALTER TABLE `question_bank_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_sessions`
--
ALTER TABLE `question_bank_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_sources`
--
ALTER TABLE `question_bank_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_subjects`
--
ALTER TABLE `question_bank_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_bank_subjects_class_id_foreign` (`class_id`),
  ADD KEY `question_bank_subjects_group_id_foreign` (`group_id`),
  ADD KEY `question_bank_subjects_question_category_id_foreign` (`question_category_id`);

--
-- Indexes for table `question_bank_sub_sources`
--
ALTER TABLE `question_bank_sub_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_bank_sub_sources_source_id_foreign` (`source_id`);

--
-- Indexes for table `question_bank_tags`
--
ALTER TABLE `question_bank_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_tests`
--
ALTER TABLE `question_bank_tests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_topics`
--
ALTER TABLE `question_bank_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_bank_topics_question_bank_chapter_id_foreign` (`question_bank_chapter_id`);

--
-- Indexes for table `question_bank_types`
--
ALTER TABLE `question_bank_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_bank_years`
--
ALTER TABLE `question_bank_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_categories`
--
ALTER TABLE `question_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_categories_institute_id_foreign` (`institute_id`),
  ADD KEY `question_categories_created_by_foreign` (`created_by`);

--
-- Indexes for table `question_level`
--
ALTER TABLE `question_level`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_level_question_id_foreign` (`question_id`),
  ADD KEY `question_level_question_bank_level_id_foreign` (`question_bank_level_id`);

--
-- Indexes for table `question_session`
--
ALTER TABLE `question_session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_session_question_id_foreign` (`question_id`),
  ADD KEY `question_session_question_bank_session_id_foreign` (`question_bank_session_id`);

--
-- Indexes for table `question_source`
--
ALTER TABLE `question_source`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_source_question_id_foreign` (`question_id`),
  ADD KEY `question_source_question_bank_source_id_foreign` (`question_bank_source_id`);

--
-- Indexes for table `question_sub_source`
--
ALTER TABLE `question_sub_source`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_sub_source_question_id_foreign` (`question_id`),
  ADD KEY `question_sub_source_question_bank_sub_source_id_foreign` (`question_bank_sub_source_id`);

--
-- Indexes for table `question_tag`
--
ALTER TABLE `question_tag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_tag_question_id_foreign` (`question_id`),
  ADD KEY `question_tag_question_bank_tag_id_foreign` (`question_bank_tag_id`);

--
-- Indexes for table `question_test`
--
ALTER TABLE `question_test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_test_question_id_foreign` (`question_id`),
  ADD KEY `question_test_question_bank_test_id_foreign` (`question_bank_test_id`);

--
-- Indexes for table `question_topic`
--
ALTER TABLE `question_topic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_topic_question_id_foreign` (`question_id`),
  ADD KEY `question_topic_question_bank_topic_id_foreign` (`question_bank_topic_id`);

--
-- Indexes for table `question_type`
--
ALTER TABLE `question_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_type_question_id_foreign` (`question_id`),
  ADD KEY `question_type_question_bank_type_id_foreign` (`question_bank_type_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizzes_institute_id_foreign` (`institute_id`),
  ADD KEY `quizzes_branch_id_foreign` (`branch_id`),
  ADD KEY `quizzes_created_by_foreign` (`created_by`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_attempts_institute_id_foreign` (`institute_id`),
  ADD KEY `quiz_attempts_branch_id_foreign` (`branch_id`),
  ADD KEY `quiz_attempts_quiz_id_foreign` (`quiz_id`),
  ADD KEY `quiz_attempts_user_id_foreign` (`user_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_results_institute_id_foreign` (`institute_id`),
  ADD KEY `quiz_results_branch_id_foreign` (`branch_id`),
  ADD KEY `quiz_results_quiz_id_foreign` (`quiz_id`),
  ADD KEY `quiz_results_quiz_attempt_id_foreign` (`quiz_attempt_id`);

--
-- Indexes for table `quiz_topics`
--
ALTER TABLE `quiz_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_topics_institute_id_foreign` (`institute_id`),
  ADD KEY `quiz_topics_quiz_id_foreign` (`quiz_id`),
  ADD KEY `quiz_topics_question_bank_subject_id_foreign` (`question_bank_subject_id`),
  ADD KEY `quiz_topics_question_bank_chapter_id_foreign` (`question_bank_chapter_id`),
  ADD KEY `quiz_topics_question_category_id_foreign` (`question_category_id`);

--
-- Indexes for table `ready_to_join_us`
--
ALTER TABLE `ready_to_join_us`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ready_to_join_us_institute_id_foreign` (`institute_id`),
  ADD KEY `ready_to_join_us_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrals_referrer_type_referrer_id_index` (`referrer_type`,`referrer_id`),
  ADD KEY `referrals_referred_institute_id_foreign` (`referred_institute_id`);

--
-- Indexes for table `referral_settings`
--
ALTER TABLE `referral_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_settings_referrer_role_unique` (`referrer_role`);

--
-- Indexes for table `remark_configs`
--
ALTER TABLE `remark_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remark_configs_institute_id_foreign` (`institute_id`),
  ADD KEY `remark_configs_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resources_institute_id_foreign` (`institute_id`),
  ADD KEY `resources_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `result_cards`
--
ALTER TABLE `result_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_cards_institute_id_foreign` (`institute_id`),
  ADD KEY `result_cards_branch_id_foreign` (`branch_id`),
  ADD KEY `result_cards_signature_id_foreign` (`signature_id`),
  ADD KEY `result_cards_teacher_signature_id_foreign` (`teacher_signature_id`);

--
-- Indexes for table `result_card_settings`
--
ALTER TABLE `result_card_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `result_card_settings_unique` (`institute_id`,`branch_id`,`session_id`),
  ADD KEY `result_card_settings_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `result_publications`
--
ALTER TABLE `result_publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_pubs_class_section_status_idx` (`institute_id`,`class_id`,`section_id`,`status`),
  ADD KEY `result_pubs_institute_status_idx` (`institute_id`,`status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_institute_id_branch_id_unique` (`name`,`guard_name`,`institute_id`,`branch_id`),
  ADD KEY `roles_institute_id_foreign` (`institute_id`),
  ADD KEY `roles_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rooms_institute_id_foreign` (`institute_id`),
  ADD KEY `rooms_branch_id_foreign` (`branch_id`),
  ADD KEY `rooms_hostel_category_id_foreign` (`hostel_category_id`);

--
-- Indexes for table `room_members`
--
ALTER TABLE `room_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_members_institute_id_foreign` (`institute_id`),
  ADD KEY `room_members_branch_id_foreign` (`branch_id`),
  ADD KEY `room_members_room_id_foreign` (`room_id`),
  ADD KEY `room_members_student_id_foreign` (`student_id`);

--
-- Indexes for table `salary_heads`
--
ALTER TABLE `salary_heads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_heads_institute_id_foreign` (`institute_id`),
  ADD KEY `salary_heads_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `salary_head_user_payrolls`
--
ALTER TABLE `salary_head_user_payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_head_user_payrolls_institute_id_foreign` (`institute_id`),
  ADD KEY `salary_head_user_payrolls_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sections_institute_id_foreign` (`institute_id`),
  ADD KEY `sections_branch_id_foreign` (`branch_id`),
  ADD KEY `sections_student_group_id_foreign` (`student_group_id`),
  ADD KEY `sections_class_id_student_group_id_index` (`class_id`,`student_group_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_institute_id_foreign` (`institute_id`),
  ADD KEY `settings_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifts_institute_id_foreign` (`institute_id`),
  ADD KEY `shifts_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `short_codes`
--
ALTER TABLE `short_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `short_codes_institute_id_foreign` (`institute_id`),
  ADD KEY `short_codes_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `signatures`
--
ALTER TABLE `signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `signatures_institute_id_foreign` (`institute_id`),
  ADD KEY `signatures_branch_id_foreign` (`branch_id`),
  ADD KEY `signatures_user_id_index` (`user_id`);

--
-- Indexes for table `sms_balances`
--
ALTER TABLE `sms_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_balances_institute_id_foreign` (`institute_id`),
  ADD KEY `sms_balances_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_logs_institute_id_foreign` (`institute_id`),
  ADD KEY `sms_logs_branch_id_foreign` (`branch_id`),
  ADD KEY `sms_logs_user_id_foreign` (`user_id`),
  ADD KEY `sms_logs_staff_id_foreign` (`staff_id`),
  ADD KEY `sms_logs_teacher_id_foreign` (`teacher_id`),
  ADD KEY `sms_logs_student_id_foreign` (`student_id`),
  ADD KEY `sms_logs_receiver_user_type_status_index` (`receiver`,`user_type`,`status`),
  ADD KEY `sms_logs_sender_id_index` (`sender_id`);

--
-- Indexes for table `sms_purchases`
--
ALTER TABLE `sms_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_purchases_institute_id_foreign` (`institute_id`),
  ADD KEY `sms_purchases_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms_templates_institute_id_foreign` (`institute_id`),
  ADD KEY `sms_templates_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staffs_institute_id_foreign` (`institute_id`),
  ADD KEY `staffs_branch_id_foreign` (`branch_id`),
  ADD KEY `staffs_department_id_foreign` (`department_id`),
  ADD KEY `staffs_user_id_department_id_index` (`user_id`,`department_id`);

--
-- Indexes for table `staff_attendances`
--
ALTER TABLE `staff_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_attendance_date` (`user_id`,`date`),
  ADD KEY `staff_attendances_institute_id_foreign` (`institute_id`),
  ADD KEY `staff_attendances_branch_id_foreign` (`branch_id`),
  ADD KEY `staff_attendances_user_id_date_index` (`user_id`,`date`);

--
-- Indexes for table `staff_categories`
--
ALTER TABLE `staff_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_categories_institute_id_branch_id_index` (`institute_id`,`branch_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `students_institute_id_foreign` (`institute_id`),
  ADD KEY `students_branch_id_foreign` (`branch_id`),
  ADD KEY `students_user_id_phone_group_index` (`user_id`,`phone`,`group`),
  ADD KEY `students_phone_index` (`phone`);

--
-- Indexes for table `student_assessments`
--
ALTER TABLE `student_assessments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_assessments_unique` (`institute_id`,`session_id`,`student_id`,`exam_id`,`assessment_item_id`),
  ADD KEY `student_assessments_branch_id_foreign` (`branch_id`),
  ADD KEY `student_assessments_student_id_foreign` (`student_id`),
  ADD KEY `student_assessments_class_id_foreign` (`class_id`),
  ADD KEY `student_assessments_exam_id_foreign` (`exam_id`),
  ADD KEY `student_assessments_assessment_item_id_foreign` (`assessment_item_id`);

--
-- Indexes for table `student_attendances`
--
ALTER TABLE `student_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_unique_attendance` (`student_id`,`class_id`,`section_id`,`period_id`,`date`),
  ADD KEY `student_attendances_institute_id_foreign` (`institute_id`),
  ADD KEY `student_attendances_branch_id_foreign` (`branch_id`),
  ADD KEY `student_attendances_class_id_foreign` (`class_id`),
  ADD KEY `student_attendances_section_id_foreign` (`section_id`),
  ADD KEY `student_attendances_period_id_foreign` (`period_id`),
  ADD KEY `student_attendances_subject_id_foreign` (`subject_id`),
  ADD KEY `student_attendances_date_index` (`date`);

--
-- Indexes for table `student_categories`
--
ALTER TABLE `student_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_categories_institute_id_foreign` (`institute_id`),
  ADD KEY `student_categories_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `student_collections`
--
ALTER TABLE `student_collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_collections_invoice_id_unique` (`invoice_id`),
  ADD KEY `student_collections_institute_id_foreign` (`institute_id`),
  ADD KEY `student_collections_branch_id_foreign` (`branch_id`),
  ADD KEY `student_collections_class_id_session_id_index` (`class_id`,`session_id`);

--
-- Indexes for table `student_collection_details`
--
ALTER TABLE `student_collection_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_collection_details_institute_id_foreign` (`institute_id`),
  ADD KEY `student_collection_details_branch_id_foreign` (`branch_id`),
  ADD KEY `student_collection_details_student_id_index` (`student_id`);

--
-- Indexes for table `student_collection_details_sub_heads`
--
ALTER TABLE `student_collection_details_sub_heads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_collection_details_sub_heads_institute_id_foreign` (`institute_id`),
  ADD KEY `student_collection_details_sub_heads_branch_id_foreign` (`branch_id`),
  ADD KEY `student_collection_details_sub_heads_student_id_index` (`student_id`);

--
-- Indexes for table `student_devices`
--
ALTER TABLE `student_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_devices_student_id_foreign` (`student_id`);

--
-- Indexes for table `student_groups`
--
ALTER TABLE `student_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_groups_institute_id_foreign` (`institute_id`),
  ADD KEY `student_groups_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `student_migrations`
--
ALTER TABLE `student_migrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_migrations_institute_id_foreign` (`institute_id`),
  ADD KEY `student_migrations_branch_id_foreign` (`branch_id`),
  ADD KEY `student_migrations_class_id_section_id_group_id_index` (`class_id`,`section_id`,`group_id`);

--
-- Indexes for table `student_optional_subjects`
--
ALTER TABLE `student_optional_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_optional_subjects_institute_id_foreign` (`institute_id`),
  ADD KEY `student_optional_subjects_branch_id_foreign` (`branch_id`),
  ADD KEY `student_optional_subjects_student_id_foreign` (`student_id`),
  ADD KEY `student_optional_subjects_student_session_id_foreign` (`student_session_id`),
  ADD KEY `student_optional_subjects_optional_subject_config_id_foreign` (`optional_subject_config_id`),
  ADD KEY `student_optional_subjects_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `student_sessions`
--
ALTER TABLE `student_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`session_id`,`student_id`,`class_id`,`section_id`,`roll`),
  ADD KEY `student_sessions_branch_id_foreign` (`branch_id`),
  ADD KEY `student_sessions_student_id_foreign` (`student_id`),
  ADD KEY `student_sessions_class_id_foreign` (`class_id`),
  ADD KEY `student_sessions_section_id_foreign` (`section_id`),
  ADD KEY `student_sessions_group_id_foreign` (`group_id`),
  ADD KEY `student_sessions_session_id_student_id_class_id_section_id_index` (`session_id`,`student_id`,`class_id`,`section_id`),
  ADD KEY `student_sessions_roll_index` (`roll`),
  ADD KEY `student_sessions_qr_code_index` (`qr_code`),
  ADD KEY `student_sessions_context_lookup_idx` (`institute_id`,`branch_id`,`session_id`,`class_id`,`section_id`);

--
-- Indexes for table `student_waiver_configs`
--
ALTER TABLE `student_waiver_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_waiver_configs_institute_id_foreign` (`institute_id`),
  ADD KEY `student_waiver_configs_branch_id_foreign` (`branch_id`),
  ADD KEY `student_waiver_configs_student_id_index` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subjects_institute_id_foreign` (`institute_id`),
  ADD KEY `subjects_branch_id_foreign` (`branch_id`),
  ADD KEY `subjects_class_id_foreign` (`class_id`),
  ADD KEY `subjects_id_class_id_group_id_index` (`id`,`class_id`,`group_id`);

--
-- Indexes for table `subject_configs`
--
ALTER TABLE `subject_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_configs_institute_id_foreign` (`institute_id`),
  ADD KEY `subject_configs_branch_id_foreign` (`branch_id`),
  ADD KEY `subject_configs_subject_id_foreign` (`subject_id`),
  ADD KEY `subject_configs_class_id_group_id_subject_id_index` (`class_id`,`group_id`,`subject_id`),
  ADD KEY `subject_configs_group_id_foreign` (`group_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_institute_id_foreign` (`institute_id`),
  ADD KEY `subscriptions_plan_id_foreign` (`plan_id`);

--
-- Indexes for table `subscription_items`
--
ALTER TABLE `subscription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_items_subscription_id_foreign` (`subscription_id`);

--
-- Indexes for table `subscription_upgrade_requests`
--
ALTER TABLE `subscription_upgrade_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_upgrade_requests_institute_id_foreign` (`institute_id`),
  ADD KEY `subscription_upgrade_requests_plan_id_foreign` (`plan_id`);

--
-- Indexes for table `syllabus`
--
ALTER TABLE `syllabus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `syllabus_institute_id_foreign` (`institute_id`),
  ADD KEY `syllabus_branch_id_foreign` (`branch_id`),
  ADD KEY `syllabus_session_id_class_id_index` (`session_id`,`class_id`);

--
-- Indexes for table `system_modules`
--
ALTER TABLE `system_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_modules_slug_unique` (`slug`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `system_settings_key_unique` (`key`);

--
-- Indexes for table `system_updates`
--
ALTER TABLE `system_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_updates_status_index` (`status`),
  ADD KEY `system_updates_created_at_index` (`created_at`);

--
-- Indexes for table `system_update_logs`
--
ALTER TABLE `system_update_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_update_logs_system_update_id_foreign` (`system_update_id`);

--
-- Indexes for table `s_a_a_s_banners`
--
ALTER TABLE `s_a_a_s_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `s_a_a_s_banners_created_by_foreign` (`created_by`);

--
-- Indexes for table `s_a_a_s_benefits`
--
ALTER TABLE `s_a_a_s_benefits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `s_a_a_s_benefits_created_by_foreign` (`created_by`);

--
-- Indexes for table `s_a_a_s_contacts`
--
ALTER TABLE `s_a_a_s_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `s_a_a_s_contacts_user_id_isseen_index` (`user_id`,`isSeen`);

--
-- Indexes for table `s_a_a_s_cores`
--
ALTER TABLE `s_a_a_s_cores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_a_a_s_faqs`
--
ALTER TABLE `s_a_a_s_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `s_a_a_s_faqs_created_by_foreign` (`created_by`);

--
-- Indexes for table `s_a_a_s_feedback`
--
ALTER TABLE `s_a_a_s_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_a_a_s_panels`
--
ALTER TABLE `s_a_a_s_panels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_a_a_s_policies`
--
ALTER TABLE `s_a_a_s_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `s_a_a_s_policies_created_by_foreign` (`created_by`);

--
-- Indexes for table `s_a_a_s_settings`
--
ALTER TABLE `s_a_a_s_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_a_a_s_solutions`
--
ALTER TABLE `s_a_a_s_solutions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_a_a_s_subscriptions`
--
ALTER TABLE `s_a_a_s_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `s_a_a_s_subscriptions_email_unique` (`email`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teachers_institute_id_foreign` (`institute_id`),
  ADD KEY `teachers_branch_id_foreign` (`branch_id`),
  ADD KEY `teachers_department_id_foreign` (`department_id`),
  ADD KEY `teachers_user_id_department_id_index` (`user_id`,`department_id`);

--
-- Indexes for table `teacher_signatures`
--
ALTER TABLE `teacher_signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_signatures_institute_id_foreign` (`institute_id`),
  ADD KEY `teacher_signatures_branch_id_foreign` (`branch_id`),
  ADD KEY `teacher_signatures_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `testimonials_institute_id_foreign` (`institute_id`),
  ADD KEY `testimonials_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transport_collections`
--
ALTER TABLE `transport_collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transport_collections_invoice_id_unique` (`invoice_id`),
  ADD KEY `transport_collections_transport_member_id_foreign` (`transport_member_id`),
  ADD KEY `transport_collections_student_id_session_id_index` (`student_id`,`session_id`);

--
-- Indexes for table `transport_fee_configs`
--
ALTER TABLE `transport_fee_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transport_fee_configs_route_id_foreign` (`route_id`),
  ADD KEY `transport_fee_configs_stop_id_foreign` (`stop_id`);

--
-- Indexes for table `transport_members`
--
ALTER TABLE `transport_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transport_members_transport_id_unique` (`transport_id`),
  ADD KEY `transport_members_institute_id_foreign` (`institute_id`),
  ADD KEY `transport_members_branch_id_foreign` (`branch_id`),
  ADD KEY `transport_members_student_id_foreign` (`student_id`),
  ADD KEY `transport_members_assigned_route_id_foreign` (`assigned_route_id`),
  ADD KEY `transport_members_assigned_stop_id_foreign` (`assigned_stop_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_referral_code_unique` (`referral_code`),
  ADD KEY `users_phone_index` (`phone`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_logs_institute_id_foreign` (`institute_id`),
  ADD KEY `user_logs_branch_id_foreign` (`branch_id`),
  ADD KEY `user_logs_user_id_index` (`user_id`);

--
-- Indexes for table `user_notices`
--
ALTER TABLE `user_notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_notices_institute_id_foreign` (`institute_id`),
  ADD KEY `user_notices_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `user_payrolls`
--
ALTER TABLE `user_payrolls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_payrolls_user_id_unique` (`user_id`),
  ADD KEY `user_payrolls_institute_id_foreign` (`institute_id`),
  ADD KEY `user_payrolls_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_fitness_certificates`
--
ALTER TABLE `vehicle_fitness_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_fitness_certificates_bus_id_foreign` (`bus_id`);

--
-- Indexes for table `vehicle_fuel_logs`
--
ALTER TABLE `vehicle_fuel_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_fuel_logs_bus_id_foreign` (`bus_id`);

--
-- Indexes for table `vehicle_insurances`
--
ALTER TABLE `vehicle_insurances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_insurances_bus_id_foreign` (`bus_id`);

--
-- Indexes for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_maintenance_logs_bus_id_foreign` (`bus_id`);

--
-- Indexes for table `vehicle_tax_infos`
--
ALTER TABLE `vehicle_tax_infos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_tax_infos_bus_id_foreign` (`bus_id`);

--
-- Indexes for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `waivers`
--
ALTER TABLE `waivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`waiver`),
  ADD KEY `waivers_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `whats_app_logs`
--
ALTER TABLE `whats_app_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whats_app_logs_reference_type_reference_id_index` (`reference_type`,`reference_id`);

--
-- Indexes for table `whats_app_settings`
--
ALTER TABLE `whats_app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `whats_app_templates`
--
ALTER TABLE `whats_app_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `why_choose_us`
--
ALTER TABLE `why_choose_us`
  ADD PRIMARY KEY (`id`),
  ADD KEY `why_choose_us_institute_id_foreign` (`institute_id`),
  ADD KEY `why_choose_us_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `withdrawal_requests_user_id_status_index` (`user_id`,`status`),
  ADD KEY `withdrawal_requests_institute_id_foreign` (`institute_id`);

--
-- Indexes for table `withdrawal_settings`
--
ALTER TABLE `withdrawal_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `withdrawal_settings_scope_type_scope_id_unique` (`scope_type`,`scope_id`);

--
-- Indexes for table `zoom_meetings`
--
ALTER TABLE `zoom_meetings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_combination` (`institute_id`,`branch_id`,`meeting_id`),
  ADD KEY `zoom_meetings_branch_id_foreign` (`branch_id`),
  ADD KEY `zoom_meetings_teacher_id_foreign` (`teacher_id`),
  ADD KEY `zoom_meetings_class_id_foreign` (`class_id`),
  ADD KEY `zoom_meetings_section_id_foreign` (`section_id`),
  ADD KEY `zoom_meetings_created_by_foreign` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us`
--
ALTER TABLE `about_us`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `absent_fines`
--
ALTER TABLE `absent_fines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `academic_images`
--
ALTER TABLE `academic_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `accounting_categories`
--
ALTER TABLE `accounting_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `accounting_funds`
--
ALTER TABLE `accounting_funds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `accounting_groups`
--
ALTER TABLE `accounting_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `accounting_ledgers`
--
ALTER TABLE `accounting_ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `account_transactions`
--
ALTER TABLE `account_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_transaction_details`
--
ALTER TABLE `account_transaction_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admission_forms`
--
ALTER TABLE `admission_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_messages`
--
ALTER TABLE `ai_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessment_domains`
--
ALTER TABLE `assessment_domains`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `assessment_items`
--
ALTER TABLE `assessment_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assignment_submits`
--
ALTER TABLE `assignment_submits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assign_shifts`
--
ALTER TABLE `assign_shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assign_subjects`
--
ALTER TABLE `assign_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_fines`
--
ALTER TABLE `attendance_fines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_waivers`
--
ALTER TABLE `attendance_waivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `beds`
--
ALTER TABLE `beds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `behaviors`
--
ALTER TABLE `behaviors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `book_categories`
--
ALTER TABLE `book_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `book_issues`
--
ALTER TABLE `book_issues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bus_routes`
--
ALTER TABLE `bus_routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bus_seats`
--
ALTER TABLE `bus_seats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bus_stops`
--
ALTER TABLE `bus_stops`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificate_issues`
--
ALTER TABLE `certificate_issues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `class_assigns`
--
ALTER TABLE `class_assigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_days`
--
ALTER TABLE `class_days`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_exams`
--
ALTER TABLE `class_exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_lessons`
--
ALTER TABLE `class_lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_routines`
--
ALTER TABLE `class_routines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `content_visibility`
--
ALTER TABLE `content_visibility`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_faqs`
--
ALTER TABLE `course_faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `course_features`
--
ALTER TABLE `course_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_fields`
--
ALTER TABLE `custom_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_menus`
--
ALTER TABLE `custom_menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `demo_restrictions`
--
ALTER TABLE `demo_restrictions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `device_controls`
--
ALTER TABLE `device_controls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `director_messages`
--
ALTER TABLE `director_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `driver_documents`
--
ALTER TABLE `driver_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `event_enrollments`
--
ALTER TABLE `event_enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exam_attendances`
--
ALTER TABLE `exam_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_codes`
--
ALTER TABLE `exam_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exam_grades`
--
ALTER TABLE `exam_grades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_marks`
--
ALTER TABLE `exam_marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faq_questions`
--
ALTER TABLE `faq_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_date_configs`
--
ALTER TABLE `fee_date_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_heads`
--
ALTER TABLE `fee_heads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `fee_maps`
--
ALTER TABLE `fee_maps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_map_fee_sub_head`
--
ALTER TABLE `fee_map_fee_sub_head`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_map_fund`
--
ALTER TABLE `fee_map_fund`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_sub_heads`
--
ALTER TABLE `fee_sub_heads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `form_builder_fields`
--
ALTER TABLE `form_builder_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `form_builder_forms`
--
ALTER TABLE `form_builder_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `form_builder_submissions`
--
ALTER TABLE `form_builder_submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gamifications`
--
ALTER TABLE `gamifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `google_meet_meetings`
--
ALTER TABLE `google_meet_meetings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `grand_final_class_exams`
--
ALTER TABLE `grand_final_class_exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_final_settings`
--
ALTER TABLE `grand_final_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `helpers`
--
ALTER TABLE `helpers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `homepage_sections`
--
ALTER TABLE `homepage_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `home_sections`
--
ALTER TABLE `home_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hostel_bills`
--
ALTER TABLE `hostel_bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_buildings`
--
ALTER TABLE `hostel_buildings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hostel_categories`
--
ALTER TABLE `hostel_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hostel_collections`
--
ALTER TABLE `hostel_collections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_collection_details`
--
ALTER TABLE `hostel_collection_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_deposit_refunds`
--
ALTER TABLE `hostel_deposit_refunds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_fee_configs`
--
ALTER TABLE `hostel_fee_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_floors`
--
ALTER TABLE `hostel_floors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hostel_leaves`
--
ALTER TABLE `hostel_leaves`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_members`
--
ALTER TABLE `hostel_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `impersonation_tokens`
--
ALTER TABLE `impersonation_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `institutes`
--
ALTER TABLE `institutes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `institute_image_settings`
--
ALTER TABLE `institute_image_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `institute_image_s_a_a_s_settings`
--
ALTER TABLE `institute_image_s_a_a_s_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `institute_referral_settings`
--
ALTER TABLE `institute_referral_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `institute_types`
--
ALTER TABLE `institute_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `internal_referrals`
--
ALTER TABLE `internal_referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `library_fines`
--
ALTER TABLE `library_fines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `library_members`
--
ALTER TABLE `library_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_assignments`
--
ALTER TABLE `lms_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_assignment_results`
--
ALTER TABLE `lms_assignment_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_class_routines`
--
ALTER TABLE `lms_class_routines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_class_routine_days`
--
ALTER TABLE `lms_class_routine_days`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_days`
--
ALTER TABLE `lms_days`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_rooms`
--
ALTER TABLE `lms_rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lms_zoom_meetings`
--
ALTER TABLE `lms_zoom_meetings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mark_configs`
--
ALTER TABLE `mark_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mark_config_exam_codes`
--
ALTER TABLE `mark_config_exam_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_entries`
--
ALTER TABLE `meal_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merit_process_types`
--
ALTER TABLE `merit_process_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=309;

--
-- AUTO_INCREMENT for table `mobile_app_sections`
--
ALTER TABLE `mobile_app_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `onboardings`
--
ALTER TABLE `onboardings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `online_exams`
--
ALTER TABLE `online_exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_exam_attempts`
--
ALTER TABLE `online_exam_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_exam_mark_transfers`
--
ALTER TABLE `online_exam_mark_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_exam_questions`
--
ALTER TABLE `online_exam_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `optional_subject_configs`
--
ALTER TABLE `optional_subject_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `optional_subject_items`
--
ALTER TABLE `optional_subject_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `our_histories`
--
ALTER TABLE `our_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `parent_models`
--
ALTER TABLE `parent_models`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_histories`
--
ALTER TABLE `payment_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_accounting_mappings`
--
ALTER TABLE `payroll_accounting_mappings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payslip_invoices`
--
ALTER TABLE `payslip_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslip_salaries`
--
ALTER TABLE `payslip_salaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslip_salary_heads`
--
ALTER TABLE `payslip_salary_heads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `periods`
--
ALTER TABLE `periods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `phone_books`
--
ALTER TABLE `phone_books`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phone_book_categories`
--
ALTER TABLE `phone_book_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `picklists`
--
ALTER TABLE `picklists`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `popups`
--
ALTER TABLE `popups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prayers`
--
ALTER TABLE `prayers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_boards`
--
ALTER TABLE `question_bank_boards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_chapters`
--
ALTER TABLE `question_bank_chapters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_classes`
--
ALTER TABLE `question_bank_classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_difficulty_levels`
--
ALTER TABLE `question_bank_difficulty_levels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_groups`
--
ALTER TABLE `question_bank_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_levels`
--
ALTER TABLE `question_bank_levels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_sessions`
--
ALTER TABLE `question_bank_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_sources`
--
ALTER TABLE `question_bank_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_subjects`
--
ALTER TABLE `question_bank_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_sub_sources`
--
ALTER TABLE `question_bank_sub_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_tags`
--
ALTER TABLE `question_bank_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_tests`
--
ALTER TABLE `question_bank_tests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_topics`
--
ALTER TABLE `question_bank_topics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_types`
--
ALTER TABLE `question_bank_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_bank_years`
--
ALTER TABLE `question_bank_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_categories`
--
ALTER TABLE `question_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_level`
--
ALTER TABLE `question_level`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_session`
--
ALTER TABLE `question_session`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_source`
--
ALTER TABLE `question_source`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_sub_source`
--
ALTER TABLE `question_sub_source`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_tag`
--
ALTER TABLE `question_tag`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_test`
--
ALTER TABLE `question_test`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_topic`
--
ALTER TABLE `question_topic`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_type`
--
ALTER TABLE `question_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_topics`
--
ALTER TABLE `quiz_topics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ready_to_join_us`
--
ALTER TABLE `ready_to_join_us`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_settings`
--
ALTER TABLE `referral_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remark_configs`
--
ALTER TABLE `remark_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `result_cards`
--
ALTER TABLE `result_cards`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `result_card_settings`
--
ALTER TABLE `result_card_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `result_publications`
--
ALTER TABLE `result_publications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `room_members`
--
ALTER TABLE `room_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_heads`
--
ALTER TABLE `salary_heads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `salary_head_user_payrolls`
--
ALTER TABLE `salary_head_user_payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=548;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `short_codes`
--
ALTER TABLE `short_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `signatures`
--
ALTER TABLE `signatures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sms_balances`
--
ALTER TABLE `sms_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_purchases`
--
ALTER TABLE `sms_purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_templates`
--
ALTER TABLE `sms_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_attendances`
--
ALTER TABLE `staff_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_categories`
--
ALTER TABLE `staff_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_assessments`
--
ALTER TABLE `student_assessments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_attendances`
--
ALTER TABLE `student_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_categories`
--
ALTER TABLE `student_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_collections`
--
ALTER TABLE `student_collections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_collection_details`
--
ALTER TABLE `student_collection_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_collection_details_sub_heads`
--
ALTER TABLE `student_collection_details_sub_heads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_devices`
--
ALTER TABLE `student_devices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_groups`
--
ALTER TABLE `student_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_migrations`
--
ALTER TABLE `student_migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_optional_subjects`
--
ALTER TABLE `student_optional_subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_sessions`
--
ALTER TABLE `student_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_waiver_configs`
--
ALTER TABLE `student_waiver_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subject_configs`
--
ALTER TABLE `subject_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subscription_items`
--
ALTER TABLE `subscription_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subscription_upgrade_requests`
--
ALTER TABLE `subscription_upgrade_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `syllabus`
--
ALTER TABLE `syllabus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `system_modules`
--
ALTER TABLE `system_modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_updates`
--
ALTER TABLE `system_updates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_update_logs`
--
ALTER TABLE `system_update_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `s_a_a_s_banners`
--
ALTER TABLE `s_a_a_s_banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `s_a_a_s_benefits`
--
ALTER TABLE `s_a_a_s_benefits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `s_a_a_s_contacts`
--
ALTER TABLE `s_a_a_s_contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `s_a_a_s_cores`
--
ALTER TABLE `s_a_a_s_cores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `s_a_a_s_faqs`
--
ALTER TABLE `s_a_a_s_faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `s_a_a_s_feedback`
--
ALTER TABLE `s_a_a_s_feedback`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `s_a_a_s_panels`
--
ALTER TABLE `s_a_a_s_panels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `s_a_a_s_policies`
--
ALTER TABLE `s_a_a_s_policies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `s_a_a_s_settings`
--
ALTER TABLE `s_a_a_s_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `s_a_a_s_solutions`
--
ALTER TABLE `s_a_a_s_solutions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `s_a_a_s_subscriptions`
--
ALTER TABLE `s_a_a_s_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_signatures`
--
ALTER TABLE `teacher_signatures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_collections`
--
ALTER TABLE `transport_collections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_fee_configs`
--
ALTER TABLE `transport_fee_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_members`
--
ALTER TABLE `transport_members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_notices`
--
ALTER TABLE `user_notices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_payrolls`
--
ALTER TABLE `user_payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_fitness_certificates`
--
ALTER TABLE `vehicle_fitness_certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_fuel_logs`
--
ALTER TABLE `vehicle_fuel_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_insurances`
--
ALTER TABLE `vehicle_insurances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_tax_infos`
--
ALTER TABLE `vehicle_tax_infos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_types`
--
ALTER TABLE `vehicle_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waivers`
--
ALTER TABLE `waivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `whats_app_logs`
--
ALTER TABLE `whats_app_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whats_app_settings`
--
ALTER TABLE `whats_app_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `whats_app_templates`
--
ALTER TABLE `whats_app_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `why_choose_us`
--
ALTER TABLE `why_choose_us`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdrawal_settings`
--
ALTER TABLE `withdrawal_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zoom_meetings`
--
ALTER TABLE `zoom_meetings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `about_us`
--
ALTER TABLE `about_us`
  ADD CONSTRAINT `about_us_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `about_us_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `about_us_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `absent_fines`
--
ALTER TABLE `absent_fines`
  ADD CONSTRAINT `absent_fines_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absent_fines_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `academic_images`
--
ALTER TABLE `academic_images`
  ADD CONSTRAINT `academic_images_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `academic_images_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `accounting_categories`
--
ALTER TABLE `accounting_categories`
  ADD CONSTRAINT `accounting_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accounting_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `accounting_funds`
--
ALTER TABLE `accounting_funds`
  ADD CONSTRAINT `accounting_funds_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accounting_funds_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `accounting_groups`
--
ALTER TABLE `accounting_groups`
  ADD CONSTRAINT `accounting_groups_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accounting_groups_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `accounting_ledgers`
--
ALTER TABLE `accounting_ledgers`
  ADD CONSTRAINT `accounting_ledgers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accounting_ledgers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD CONSTRAINT `account_transactions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_transactions_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_transaction_details`
--
ALTER TABLE `account_transaction_details`
  ADD CONSTRAINT `account_transaction_details_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_transaction_details_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD CONSTRAINT `ai_conversations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_conversations_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD CONSTRAINT `ai_messages_ai_conversation_id_foreign` FOREIGN KEY (`ai_conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_messages_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_messages_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_domains`
--
ALTER TABLE `assessment_domains`
  ADD CONSTRAINT `assessment_domains_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_domains_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_items`
--
ALTER TABLE `assessment_items`
  ADD CONSTRAINT `assessment_items_assessment_domain_id_foreign` FOREIGN KEY (`assessment_domain_id`) REFERENCES `assessment_domains` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_items_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assessment_items_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submits`
--
ALTER TABLE `assignment_submits`
  ADD CONSTRAINT `assignment_submits_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submits_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submits_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `assign_shifts`
--
ALTER TABLE `assign_shifts`
  ADD CONSTRAINT `assign_shifts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assign_shifts_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assign_subjects`
--
ALTER TABLE `assign_subjects`
  ADD CONSTRAINT `assign_subjects_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assign_subjects_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assign_subjects_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assign_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assign_subjects_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_fines`
--
ALTER TABLE `attendance_fines`
  ADD CONSTRAINT `attendance_fines_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_fines_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_waivers`
--
ALTER TABLE `attendance_waivers`
  ADD CONSTRAINT `attendance_waivers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_waivers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `banners`
--
ALTER TABLE `banners`
  ADD CONSTRAINT `banners_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `banners_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `banners_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `beds`
--
ALTER TABLE `beds`
  ADD CONSTRAINT `beds_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `behaviors`
--
ALTER TABLE `behaviors`
  ADD CONSTRAINT `behaviors_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `behaviors_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `books_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `book_categories`
--
ALTER TABLE `book_categories`
  ADD CONSTRAINT `book_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `book_issues`
--
ALTER TABLE `book_issues`
  ADD CONSTRAINT `book_issues_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_issues_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `buses`
--
ALTER TABLE `buses`
  ADD CONSTRAINT `buses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `buses_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bus_routes`
--
ALTER TABLE `bus_routes`
  ADD CONSTRAINT `bus_routes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bus_routes_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bus_seats`
--
ALTER TABLE `bus_seats`
  ADD CONSTRAINT `bus_seats_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bus_stops`
--
ALTER TABLE `bus_stops`
  ADD CONSTRAINT `bus_stops_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bus_stops_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bus_stops_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `bus_routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_issues`
--
ALTER TABLE `certificate_issues`
  ADD CONSTRAINT `certificate_issues_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificate_issues_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD CONSTRAINT `certificate_templates_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificate_templates_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chapters`
--
ALTER TABLE `chapters`
  ADD CONSTRAINT `chapters_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chapters_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chapters_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_assigns`
--
ALTER TABLE `class_assigns`
  ADD CONSTRAINT `class_assigns_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_assigns_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_days`
--
ALTER TABLE `class_days`
  ADD CONSTRAINT `class_days_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_days_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_exams`
--
ALTER TABLE `class_exams`
  ADD CONSTRAINT `class_exams_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_exams_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_exams_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_exams_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_exams_merit_process_type_id_foreign` FOREIGN KEY (`merit_process_type_id`) REFERENCES `merit_process_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_lessons`
--
ALTER TABLE `class_lessons`
  ADD CONSTRAINT `class_lessons_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_lessons_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_routines`
--
ALTER TABLE `class_routines`
  ADD CONSTRAINT `class_routines_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_routines_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `class_routines_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_routines_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  ADD CONSTRAINT `class_routines_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contents`
--
ALTER TABLE `contents`
  ADD CONSTRAINT `contents_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contents_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_visibility`
--
ALTER TABLE `content_visibility`
  ADD CONSTRAINT `content_visibility_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_visibility_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_visibility_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `content_visibility_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_course_category_id_foreign` FOREIGN KEY (`course_category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_course_sub_category_id_foreign` FOREIGN KEY (`course_sub_category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `courses_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD CONSTRAINT `course_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `course_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `course_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_faqs`
--
ALTER TABLE `course_faqs`
  ADD CONSTRAINT `course_faqs_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_faqs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `course_faqs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_features`
--
ALTER TABLE `course_features`
  ADD CONSTRAINT `course_features_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_features_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `course_features_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  ADD CONSTRAINT `custom_field_values_custom_field_id_foreign` FOREIGN KEY (`custom_field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `departments_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_controls`
--
ALTER TABLE `device_controls`
  ADD CONSTRAINT `device_controls_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_assigned_bus_id_foreign` FOREIGN KEY (`assigned_bus_id`) REFERENCES `buses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `drivers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `drivers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_documents`
--
ALTER TABLE `driver_documents`
  ADD CONSTRAINT `driver_documents_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_attendances`
--
ALTER TABLE `exam_attendances`
  ADD CONSTRAINT `exam_attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_attendances_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_codes`
--
ALTER TABLE `exam_codes`
  ADD CONSTRAINT `exam_codes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_codes_class_model_id_foreign` FOREIGN KEY (`class_model_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `exam_codes_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_codes_short_code_id_foreign` FOREIGN KEY (`short_code_id`) REFERENCES `short_codes` (`id`);

--
-- Constraints for table `exam_grades`
--
ALTER TABLE `exam_grades`
  ADD CONSTRAINT `exam_grades_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_grades_class_model_id_foreign` FOREIGN KEY (`class_model_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `exam_grades_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`),
  ADD CONSTRAINT `exam_grades_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_marks`
--
ALTER TABLE `exam_marks`
  ADD CONSTRAINT `exam_marks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `exam_marks_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_schedules`
--
ALTER TABLE `exam_schedules`
  ADD CONSTRAINT `exam_schedules_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_schedules_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faq_questions`
--
ALTER TABLE `faq_questions`
  ADD CONSTRAINT `faq_questions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faq_questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `faq_questions_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fees_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_date_configs`
--
ALTER TABLE `fee_date_configs`
  ADD CONSTRAINT `fee_date_configs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_date_configs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_heads`
--
ALTER TABLE `fee_heads`
  ADD CONSTRAINT `fee_heads_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_heads_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_maps`
--
ALTER TABLE `fee_maps`
  ADD CONSTRAINT `fee_maps_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_maps_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_sub_heads`
--
ALTER TABLE `fee_sub_heads`
  ADD CONSTRAINT `fee_sub_heads_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_sub_heads_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gamifications`
--
ALTER TABLE `gamifications`
  ADD CONSTRAINT `gamifications_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gamifications_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grand_final_class_exams`
--
ALTER TABLE `grand_final_class_exams`
  ADD CONSTRAINT `grand_final_class_exams_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grand_final_class_exams_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grand_final_class_exams_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grand_final_class_exams_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grand_final_settings`
--
ALTER TABLE `grand_final_settings`
  ADD CONSTRAINT `grand_final_settings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grand_final_settings_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grand_final_settings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostels`
--
ALTER TABLE `hostels`
  ADD CONSTRAINT `hostels_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostels_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_bills`
--
ALTER TABLE `hostel_bills`
  ADD CONSTRAINT `hostel_bills_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_bills_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_bills_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_buildings`
--
ALTER TABLE `hostel_buildings`
  ADD CONSTRAINT `hostel_buildings_hostel_id_foreign` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_categories`
--
ALTER TABLE `hostel_categories`
  ADD CONSTRAINT `hostel_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_categories_hostel_id_foreign` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_collections`
--
ALTER TABLE `hostel_collections`
  ADD CONSTRAINT `hostel_collections_room_member_id_foreign` FOREIGN KEY (`room_member_id`) REFERENCES `room_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_collection_details`
--
ALTER TABLE `hostel_collection_details`
  ADD CONSTRAINT `hostel_collection_details_hostel_collection_id_foreign` FOREIGN KEY (`hostel_collection_id`) REFERENCES `hostel_collections` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_deposit_refunds`
--
ALTER TABLE `hostel_deposit_refunds`
  ADD CONSTRAINT `hostel_deposit_refunds_hostel_collection_detail_id_foreign` FOREIGN KEY (`hostel_collection_detail_id`) REFERENCES `hostel_collection_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_fee_configs`
--
ALTER TABLE `hostel_fee_configs`
  ADD CONSTRAINT `hostel_fee_configs_hostel_category_id_foreign` FOREIGN KEY (`hostel_category_id`) REFERENCES `hostel_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_floors`
--
ALTER TABLE `hostel_floors`
  ADD CONSTRAINT `hostel_floors_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `hostel_buildings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_leaves`
--
ALTER TABLE `hostel_leaves`
  ADD CONSTRAINT `hostel_leaves_room_member_id_foreign` FOREIGN KEY (`room_member_id`) REFERENCES `room_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_members`
--
ALTER TABLE `hostel_members`
  ADD CONSTRAINT `hostel_members_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_members_hostel_category_id_foreign` FOREIGN KEY (`hostel_category_id`) REFERENCES `hostel_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_members_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `institutes`
--
ALTER TABLE `institutes`
  ADD CONSTRAINT `institutes_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `institutes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `institutes_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `institutes_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `institutes_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `institute_image_settings`
--
ALTER TABLE `institute_image_settings`
  ADD CONSTRAINT `institute_image_settings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `institute_referral_settings`
--
ALTER TABLE `institute_referral_settings`
  ADD CONSTRAINT `institute_referral_settings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `internal_referrals`
--
ALTER TABLE `internal_referrals`
  ADD CONSTRAINT `internal_referrals_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_referrals_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_referrals_referrer_user_id_foreign` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD CONSTRAINT `leave_types_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_types_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lessons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lessons_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `library_fines`
--
ALTER TABLE `library_fines`
  ADD CONSTRAINT `library_fines_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `library_fines_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `library_members`
--
ALTER TABLE `library_members`
  ADD CONSTRAINT `library_members_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `library_members_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `library_members_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staffs` (`id`),
  ADD CONSTRAINT `library_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `library_members_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `library_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `lms_assignments`
--
ALTER TABLE `lms_assignments`
  ADD CONSTRAINT `lms_assignments_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_assignments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_assignments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lms_assignments_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lms_assignment_results`
--
ALTER TABLE `lms_assignment_results`
  ADD CONSTRAINT `lms_assignment_results_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_assignment_results_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_assignment_results_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lms_class_routines`
--
ALTER TABLE `lms_class_routines`
  ADD CONSTRAINT `lms_class_routines_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lms_class_routine_days`
--
ALTER TABLE `lms_class_routine_days`
  ADD CONSTRAINT `lms_class_routine_days_day_id_foreign` FOREIGN KEY (`day_id`) REFERENCES `lms_days` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_class_routine_days_routine_id_foreign` FOREIGN KEY (`routine_id`) REFERENCES `lms_class_routines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lms_days`
--
ALTER TABLE `lms_days`
  ADD CONSTRAINT `lms_days_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lms_rooms`
--
ALTER TABLE `lms_rooms`
  ADD CONSTRAINT `lms_rooms_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lms_zoom_meetings`
--
ALTER TABLE `lms_zoom_meetings`
  ADD CONSTRAINT `lms_zoom_meetings_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_zoom_meetings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lms_zoom_meetings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mark_configs`
--
ALTER TABLE `mark_configs`
  ADD CONSTRAINT `mark_configs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_configs_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_configs_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_configs_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_configs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_configs_mark_config_exam_code_id_foreign` FOREIGN KEY (`mark_config_exam_code_id`) REFERENCES `mark_config_exam_codes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_configs_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mark_config_exam_codes`
--
ALTER TABLE `mark_config_exam_codes`
  ADD CONSTRAINT `mark_config_exam_codes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_config_exam_codes_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mark_config_exam_codes_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meals_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_entries`
--
ALTER TABLE `meal_entries`
  ADD CONSTRAINT `meal_entries_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_entries_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_entries_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_entries_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meal_plans_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plans_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plans_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plans_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `merit_process_types`
--
ALTER TABLE `merit_process_types`
  ADD CONSTRAINT `merit_process_types_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `merit_process_types_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mobile_app_sections`
--
ALTER TABLE `mobile_app_sections`
  ADD CONSTRAINT `mobile_app_sections_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mobile_app_sections_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notices`
--
ALTER TABLE `notices`
  ADD CONSTRAINT `notices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notices_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `onboardings`
--
ALTER TABLE `onboardings`
  ADD CONSTRAINT `onboardings_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `online_exams`
--
ALTER TABLE `online_exams`
  ADD CONSTRAINT `online_exams_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exams_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exams_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exams_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exams_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `online_exam_attempts`
--
ALTER TABLE `online_exam_attempts`
  ADD CONSTRAINT `online_exam_attempts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_attempts_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_attempts_online_exam_id_foreign` FOREIGN KEY (`online_exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_attempts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `online_exam_mark_transfers`
--
ALTER TABLE `online_exam_mark_transfers`
  ADD CONSTRAINT `online_exam_mark_transfers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_mark_transfers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_mark_transfers_online_exam_id_foreign` FOREIGN KEY (`online_exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_mark_transfers_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `online_exam_questions`
--
ALTER TABLE `online_exam_questions`
  ADD CONSTRAINT `online_exam_questions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_questions_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `online_exam_questions_online_exam_id_foreign` FOREIGN KEY (`online_exam_id`) REFERENCES `online_exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `optional_subject_configs`
--
ALTER TABLE `optional_subject_configs`
  ADD CONSTRAINT `optional_subject_configs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `optional_subject_configs_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `optional_subject_configs_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `optional_subject_configs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `optional_subject_items`
--
ALTER TABLE `optional_subject_items`
  ADD CONSTRAINT `optional_subject_items_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `optional_subject_items_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `optional_subject_items_optional_subject_config_id_foreign` FOREIGN KEY (`optional_subject_config_id`) REFERENCES `optional_subject_configs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `optional_subject_items_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD CONSTRAINT `otp_verifications_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `our_histories`
--
ALTER TABLE `our_histories`
  ADD CONSTRAINT `our_histories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `our_histories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `our_histories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pages_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pages_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `parent_models`
--
ALTER TABLE `parent_models`
  ADD CONSTRAINT `parent_models_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parent_models_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parent_models_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `parent_models_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_histories`
--
ALTER TABLE `payment_histories`
  ADD CONSTRAINT `payment_histories_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_histories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_histories_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_methods_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_requests`
--
ALTER TABLE `payment_requests`
  ADD CONSTRAINT `payment_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_requests_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_invoices`
--
ALTER TABLE `payslip_invoices`
  ADD CONSTRAINT `payslip_invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslip_invoices_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_salaries`
--
ALTER TABLE `payslip_salaries`
  ADD CONSTRAINT `payslip_salaries_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslip_salaries_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_salary_heads`
--
ALTER TABLE `payslip_salary_heads`
  ADD CONSTRAINT `payslip_salary_heads_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslip_salary_heads_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `periods`
--
ALTER TABLE `periods`
  ADD CONSTRAINT `periods_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `periods_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `phone_books`
--
ALTER TABLE `phone_books`
  ADD CONSTRAINT `phone_books_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phone_books_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `phone_book_categories`
--
ALTER TABLE `phone_book_categories`
  ADD CONSTRAINT `phone_book_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `phone_book_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `picklists`
--
ALTER TABLE `picklists`
  ADD CONSTRAINT `picklists_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `picklists_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `policies`
--
ALTER TABLE `policies`
  ADD CONSTRAINT `policies_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `policies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `policies_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prayers`
--
ALTER TABLE `prayers`
  ADD CONSTRAINT `prayers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prayers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_question_bank_chapter_id_foreign` FOREIGN KEY (`question_bank_chapter_id`) REFERENCES `question_bank_chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_question_bank_class_id_foreign` FOREIGN KEY (`question_bank_class_id`) REFERENCES `question_bank_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_question_bank_difficulty_level_id_foreign` FOREIGN KEY (`question_bank_difficulty_level_id`) REFERENCES `question_bank_difficulty_levels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_question_bank_group_id_foreign` FOREIGN KEY (`question_bank_group_id`) REFERENCES `question_bank_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_question_bank_subject_id_foreign` FOREIGN KEY (`question_bank_subject_id`) REFERENCES `question_bank_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_question_category_id_foreign` FOREIGN KEY (`question_category_id`) REFERENCES `question_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_chapters`
--
ALTER TABLE `question_bank_chapters`
  ADD CONSTRAINT `question_bank_chapters_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `question_bank_subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_groups`
--
ALTER TABLE `question_bank_groups`
  ADD CONSTRAINT `question_bank_groups_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `question_bank_classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_subjects`
--
ALTER TABLE `question_bank_subjects`
  ADD CONSTRAINT `question_bank_subjects_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `question_bank_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_bank_subjects_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `question_bank_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `question_bank_subjects_question_category_id_foreign` FOREIGN KEY (`question_category_id`) REFERENCES `question_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_sub_sources`
--
ALTER TABLE `question_bank_sub_sources`
  ADD CONSTRAINT `question_bank_sub_sources_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `question_bank_sources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_bank_topics`
--
ALTER TABLE `question_bank_topics`
  ADD CONSTRAINT `question_bank_topics_question_bank_chapter_id_foreign` FOREIGN KEY (`question_bank_chapter_id`) REFERENCES `question_bank_chapters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_categories`
--
ALTER TABLE `question_categories`
  ADD CONSTRAINT `question_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `question_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_level`
--
ALTER TABLE `question_level`
  ADD CONSTRAINT `question_level_question_bank_level_id_foreign` FOREIGN KEY (`question_bank_level_id`) REFERENCES `question_bank_levels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_level_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_session`
--
ALTER TABLE `question_session`
  ADD CONSTRAINT `question_session_question_bank_session_id_foreign` FOREIGN KEY (`question_bank_session_id`) REFERENCES `question_bank_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_session_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_source`
--
ALTER TABLE `question_source`
  ADD CONSTRAINT `question_source_question_bank_source_id_foreign` FOREIGN KEY (`question_bank_source_id`) REFERENCES `question_bank_sources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_source_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_sub_source`
--
ALTER TABLE `question_sub_source`
  ADD CONSTRAINT `question_sub_source_question_bank_sub_source_id_foreign` FOREIGN KEY (`question_bank_sub_source_id`) REFERENCES `question_bank_sub_sources` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_sub_source_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_tag`
--
ALTER TABLE `question_tag`
  ADD CONSTRAINT `question_tag_question_bank_tag_id_foreign` FOREIGN KEY (`question_bank_tag_id`) REFERENCES `question_bank_tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_tag_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_test`
--
ALTER TABLE `question_test`
  ADD CONSTRAINT `question_test_question_bank_test_id_foreign` FOREIGN KEY (`question_bank_test_id`) REFERENCES `question_bank_tests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_test_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_topic`
--
ALTER TABLE `question_topic`
  ADD CONSTRAINT `question_topic_question_bank_topic_id_foreign` FOREIGN KEY (`question_bank_topic_id`) REFERENCES `question_bank_topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_topic_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_type`
--
ALTER TABLE `question_type`
  ADD CONSTRAINT `question_type_question_bank_type_id_foreign` FOREIGN KEY (`question_bank_type_id`) REFERENCES `question_bank_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_type_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quizzes_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_quiz_attempt_id_foreign` FOREIGN KEY (`quiz_attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_topics`
--
ALTER TABLE `quiz_topics`
  ADD CONSTRAINT `quiz_topics_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_topics_question_bank_chapter_id_foreign` FOREIGN KEY (`question_bank_chapter_id`) REFERENCES `question_bank_chapters` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_topics_question_bank_subject_id_foreign` FOREIGN KEY (`question_bank_subject_id`) REFERENCES `question_bank_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_topics_question_category_id_foreign` FOREIGN KEY (`question_category_id`) REFERENCES `question_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_topics_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ready_to_join_us`
--
ALTER TABLE `ready_to_join_us`
  ADD CONSTRAINT `ready_to_join_us_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ready_to_join_us_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_referred_institute_id_foreign` FOREIGN KEY (`referred_institute_id`) REFERENCES `institutes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `remark_configs`
--
ALTER TABLE `remark_configs`
  ADD CONSTRAINT `remark_configs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `remark_configs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resources`
--
ALTER TABLE `resources`
  ADD CONSTRAINT `resources_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resources_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `result_cards`
--
ALTER TABLE `result_cards`
  ADD CONSTRAINT `result_cards_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `result_cards_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `result_cards_signature_id_foreign` FOREIGN KEY (`signature_id`) REFERENCES `signatures` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `result_cards_teacher_signature_id_foreign` FOREIGN KEY (`teacher_signature_id`) REFERENCES `signatures` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `result_card_settings`
--
ALTER TABLE `result_card_settings`
  ADD CONSTRAINT `result_card_settings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `result_card_settings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `roles_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rooms_hostel_category_id_foreign` FOREIGN KEY (`hostel_category_id`) REFERENCES `hostel_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rooms_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_members`
--
ALTER TABLE `room_members`
  ADD CONSTRAINT `room_members_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_members_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_members_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_heads`
--
ALTER TABLE `salary_heads`
  ADD CONSTRAINT `salary_heads_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_heads_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_head_user_payrolls`
--
ALTER TABLE `salary_head_user_payrolls`
  ADD CONSTRAINT `salary_head_user_payrolls_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_head_user_payrolls_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sections_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `sections_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sections_student_group_id_foreign` FOREIGN KEY (`student_group_id`) REFERENCES `student_groups` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `settings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shifts_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `short_codes`
--
ALTER TABLE `short_codes`
  ADD CONSTRAINT `short_codes_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `short_codes_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `signatures`
--
ALTER TABLE `signatures`
  ADD CONSTRAINT `signatures_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `signatures_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_balances`
--
ALTER TABLE `sms_balances`
  ADD CONSTRAINT `sms_balances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_balances_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `sms_logs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_logs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_logs_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_logs_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_logs_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_logs_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sms_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sms_purchases`
--
ALTER TABLE `sms_purchases`
  ADD CONSTRAINT `sms_purchases_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_purchases_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD CONSTRAINT `sms_templates_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms_templates_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staffs`
--
ALTER TABLE `staffs`
  ADD CONSTRAINT `staffs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staffs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `staffs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staffs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `staff_attendances`
--
ALTER TABLE `staff_attendances`
  ADD CONSTRAINT `staff_attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_attendances_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `student_assessments`
--
ALTER TABLE `student_assessments`
  ADD CONSTRAINT `student_assessments_assessment_item_id_foreign` FOREIGN KEY (`assessment_item_id`) REFERENCES `assessment_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessments_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessments_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_attendances`
--
ALTER TABLE `student_attendances`
  ADD CONSTRAINT `student_attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendances_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendances_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendances_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendances_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_attendances_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_categories`
--
ALTER TABLE `student_categories`
  ADD CONSTRAINT `student_categories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_categories_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_collections`
--
ALTER TABLE `student_collections`
  ADD CONSTRAINT `student_collections_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_collections_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_collection_details`
--
ALTER TABLE `student_collection_details`
  ADD CONSTRAINT `student_collection_details_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_collection_details_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_collection_details_sub_heads`
--
ALTER TABLE `student_collection_details_sub_heads`
  ADD CONSTRAINT `student_collection_details_sub_heads_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_collection_details_sub_heads_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_devices`
--
ALTER TABLE `student_devices`
  ADD CONSTRAINT `student_devices_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_groups`
--
ALTER TABLE `student_groups`
  ADD CONSTRAINT `student_groups_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_groups_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_migrations`
--
ALTER TABLE `student_migrations`
  ADD CONSTRAINT `student_migrations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_migrations_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_optional_subjects`
--
ALTER TABLE `student_optional_subjects`
  ADD CONSTRAINT `student_optional_subjects_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_optional_subjects_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_optional_subjects_optional_subject_config_id_foreign` FOREIGN KEY (`optional_subject_config_id`) REFERENCES `optional_subject_configs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_optional_subjects_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_optional_subjects_student_session_id_foreign` FOREIGN KEY (`student_session_id`) REFERENCES `student_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_optional_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_sessions`
--
ALTER TABLE `student_sessions`
  ADD CONSTRAINT `student_sessions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_sessions_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_sessions_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`id`),
  ADD CONSTRAINT `student_sessions_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_sessions_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`),
  ADD CONSTRAINT `student_sessions_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `academic_years` (`id`),
  ADD CONSTRAINT `student_sessions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `student_waiver_configs`
--
ALTER TABLE `student_waiver_configs`
  ADD CONSTRAINT `student_waiver_configs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_waiver_configs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subjects_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `subjects_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subject_configs`
--
ALTER TABLE `subject_configs`
  ADD CONSTRAINT `subject_configs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_configs_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_configs_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `student_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_configs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_configs_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_items`
--
ALTER TABLE `subscription_items`
  ADD CONSTRAINT `subscription_items_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_upgrade_requests`
--
ALTER TABLE `subscription_upgrade_requests`
  ADD CONSTRAINT `subscription_upgrade_requests_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_upgrade_requests_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `syllabus`
--
ALTER TABLE `syllabus`
  ADD CONSTRAINT `syllabus_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `syllabus_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_update_logs`
--
ALTER TABLE `system_update_logs`
  ADD CONSTRAINT `system_update_logs_system_update_id_foreign` FOREIGN KEY (`system_update_id`) REFERENCES `system_updates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `s_a_a_s_banners`
--
ALTER TABLE `s_a_a_s_banners`
  ADD CONSTRAINT `s_a_a_s_banners_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `s_a_a_s_benefits`
--
ALTER TABLE `s_a_a_s_benefits`
  ADD CONSTRAINT `s_a_a_s_benefits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `s_a_a_s_faqs`
--
ALTER TABLE `s_a_a_s_faqs`
  ADD CONSTRAINT `s_a_a_s_faqs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `s_a_a_s_policies`
--
ALTER TABLE `s_a_a_s_policies`
  ADD CONSTRAINT `s_a_a_s_policies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `teachers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `teacher_signatures`
--
ALTER TABLE `teacher_signatures`
  ADD CONSTRAINT `teacher_signatures_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_signatures_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_signatures_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `testimonials_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_collections`
--
ALTER TABLE `transport_collections`
  ADD CONSTRAINT `transport_collections_transport_member_id_foreign` FOREIGN KEY (`transport_member_id`) REFERENCES `transport_members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_fee_configs`
--
ALTER TABLE `transport_fee_configs`
  ADD CONSTRAINT `transport_fee_configs_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `bus_routes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transport_fee_configs_stop_id_foreign` FOREIGN KEY (`stop_id`) REFERENCES `bus_stops` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transport_members`
--
ALTER TABLE `transport_members`
  ADD CONSTRAINT `transport_members_assigned_route_id_foreign` FOREIGN KEY (`assigned_route_id`) REFERENCES `bus_routes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_members_assigned_stop_id_foreign` FOREIGN KEY (`assigned_stop_id`) REFERENCES `bus_stops` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_members_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_members_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_logs_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_notices`
--
ALTER TABLE `user_notices`
  ADD CONSTRAINT `user_notices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notices_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payrolls`
--
ALTER TABLE `user_payrolls`
  ADD CONSTRAINT `user_payrolls_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_payrolls_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_fitness_certificates`
--
ALTER TABLE `vehicle_fitness_certificates`
  ADD CONSTRAINT `vehicle_fitness_certificates_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_fuel_logs`
--
ALTER TABLE `vehicle_fuel_logs`
  ADD CONSTRAINT `vehicle_fuel_logs_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_insurances`
--
ALTER TABLE `vehicle_insurances`
  ADD CONSTRAINT `vehicle_insurances_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  ADD CONSTRAINT `vehicle_maintenance_logs_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_tax_infos`
--
ALTER TABLE `vehicle_tax_infos`
  ADD CONSTRAINT `vehicle_tax_infos_bus_id_foreign` FOREIGN KEY (`bus_id`) REFERENCES `buses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waivers`
--
ALTER TABLE `waivers`
  ADD CONSTRAINT `waivers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `waivers_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `why_choose_us`
--
ALTER TABLE `why_choose_us`
  ADD CONSTRAINT `why_choose_us_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `why_choose_us_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD CONSTRAINT `withdrawal_requests_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `withdrawal_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `zoom_meetings`
--
ALTER TABLE `zoom_meetings`
  ADD CONSTRAINT `zoom_meetings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zoom_meetings_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zoom_meetings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zoom_meetings_institute_id_foreign` FOREIGN KEY (`institute_id`) REFERENCES `institutes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zoom_meetings_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `zoom_meetings_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
