-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 09:19 PM
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
-- Database: `upgraded`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_orders`
--

CREATE TABLE `account_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(16,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'paid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_order_items`
--

CREATE TABLE `account_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_detail_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(16,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `active_numbers`
--

CREATE TABLE `active_numbers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `number_id` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `server_id` varchar(255) NOT NULL,
  `service_id` varchar(255) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `buy_time` datetime NOT NULL,
  `service_price` varchar(255) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `sms_text` varchar(255) NOT NULL DEFAULT '',
  `active_status` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `image` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `username`, `email`, `image`, `password`, `password_reset_token`, `password_reset_expires`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin', 'admin@gmail.com', NULL, '$2y$12$mpM3Xwz9Q3mvpALfxXHg0uQiNesYxtQY.DnklpWuV97Z/aDwIfCYm', NULL, NULL, NULL, '2026-03-22 19:16:22', '2026-03-22 19:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `admin_email_templates`
--

CREATE TABLE `admin_email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template` text DEFAULT NULL,
  `meaning` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=inactive, 1=active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_email_templates`
--

INSERT INTO `admin_email_templates` (`id`, `key`, `name`, `subject`, `template`, `meaning`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin_password_reset', 'Admin Password Reset', 'Password reset code', '<p><b>Hi {username},</b></p>\r\n                                <p>Your password reset code is: <strong>{reset_code}</strong></p>\r\n                                <p>This code expires in {expires_in}. If you did not request this, please ignore this email.</p>\r\n                                <p>Thanks,</p>\r\n                                <p>Admin</p>', '{\"username\":\"Admin Username\",\"email\":\"Admin Email\",\"reset_code\":\"6-digit reset code\",\"expires_in\":\"Code validity (e.g. 1 hour)\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(2, 'admin_login', 'Admin Login', 'Admin login notification', '<p><b>Hi {username},</b></p><p>Your admin account was used to log in.</p><p>Login time: {login_time}<br>IP: {ip_address}</p><p>If this was not you, please change your password.</p><p>Thanks,</p><p>System</p>', '{\"username\":\"Admin Username\",\"email\":\"Admin Email\",\"login_time\":\"Login date\\/time\",\"ip_address\":\"IP address\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(3, 'user_payment', 'User Payment', 'New user payment', '<p><b>Hi {username},</b></p><p>A user has made a payment.</p><p>User: {user_email}<br>Amount: {currency} {amount}<br>Method: {method}<br>Reference: {reference}</p><p>Thanks,</p><p>System</p>', '{\"username\":\"Admin Username\",\"email\":\"Admin Email\",\"user_email\":\"User email\",\"amount\":\"Amount\",\"currency\":\"Currency\",\"reference\":\"Reference\",\"method\":\"Payment method\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(4, 'user_number_purchase', 'User Number Purchase', 'User purchased a number', '<p><b>Hi {username},</b></p><p>A user has purchased a number.</p><p>User: {user_email}<br>Number: {number}<br>Service: {service_name}<br>Amount: {amount}</p><p>Thanks,</p><p>System</p>', '{\"username\":\"Admin Username\",\"email\":\"Admin Email\",\"user_email\":\"User email\",\"number\":\"Purchased number\",\"service_name\":\"Service name\",\"amount\":\"Amount\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `api_details`
--

CREATE TABLE `api_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `api_name` varchar(255) NOT NULL,
  `api_url` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `provider_type` varchar(20) NOT NULL DEFAULT 'handler',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_details`
--

INSERT INTO `api_details` (`id`, `api_name`, `api_url`, `api_key`, `provider_type`, `created_at`, `updated_at`) VALUES
(1, 'smsbower', 'https://radiumshop.cc/bower_amazon', 'h7Xmn8g6109WdR4FyxEjOVAxAozIJiiZ', 'handler', '2026-03-22 19:16:22', '2026-03-22 19:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('website-name-cache-424f74a6a7ed4d4ed4761507ebcd209a6ef0937b', 'i:1;', 1774210658),
('website-name-cache-424f74a6a7ed4d4ed4761507ebcd209a6ef0937b:timer', 'i:1774210658;', 1774210658);

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
-- Table structure for table `custom_prices`
--

CREATE TABLE `custom_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `discount` varchar(255) NOT NULL,
  `service_id` varchar(255) NOT NULL,
  `server_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_configs`
--

CREATE TABLE `email_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `smtp_host` varchar(255) NOT NULL,
  `smtp_port` varchar(255) NOT NULL,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
  `sent_from` varchar(255) NOT NULL,
  `smtp_encryption` varchar(255) NOT NULL DEFAULT 'tls' COMMENT 'tls, ssl, none',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=inactive, 1=active',
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
-- Table structure for table `gateways`
--

CREATE TABLE `gateways` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `gateway_name` varchar(255) NOT NULL,
  `gateway_image` varchar(255) DEFAULT NULL,
  `gateway_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_parameters`)),
  `gateway_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=manual, 1=auto',
  `user_proof_param` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`user_proof_param`)),
  `rate` decimal(16,8) NOT NULL DEFAULT 1.00000000,
  `charge` decimal(16,8) NOT NULL DEFAULT 0.00000000,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=off, 1=on',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_01_142422_create_admins_table', 1),
(5, '2025_10_03_221944_create_gateways_table', 1),
(6, '2025_10_06_000000_create_email_configs_table', 1),
(7, '2025_10_06_162200_create_admin_email_templates_table', 1),
(8, '2025_10_06_163758_create_user_email_templates_table', 1),
(9, '2025_10_09_100000_create_settings_table', 1),
(10, '2026_03_04_193454_create_otp_servers_table', 1),
(11, '2026_03_04_193541_create_api_details_table', 1),
(12, '2026_03_04_193604_create_services_table', 1),
(13, '2026_03_04_193739_create_service_icons_table', 1),
(14, '2026_03_04_193858_create_active_numbers_table', 1),
(15, '2026_03_04_193945_create_custom_prices_table', 1),
(16, '2026_03_04_194009_create_top_services_table', 1),
(17, '2026_03_05_100150_create_payments_table', 1),
(18, '2026_03_05_100212_create_transactions_table', 1),
(19, '2026_03_08_182400_create_smm_providers_table', 1),
(20, '2026_03_08_182436_create_smm_categories_table', 1),
(21, '2026_03_08_182502_create_smm_services_table', 1),
(22, '2026_03_08_182533_create_smm_orders_table', 1),
(23, '2026_03_10_100001_create_product_categories_table', 1),
(24, '2026_03_10_100002_create_products_table', 1),
(25, '2026_03_10_100003_create_product_details_table', 1),
(26, '2026_03_10_100004_create_account_orders_table', 1),
(27, '2026_03_10_100005_create_account_order_items_table', 1),
(28, '2026_03_15_100000_create_tutorials_table', 1),
(29, '2026_03_20_220100_create_referral_commissions_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `otp_servers`
--

CREATE TABLE `otp_servers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `server_name` varchar(255) NOT NULL,
  `server_code` varchar(255) NOT NULL,
  `provider_server_id` varchar(255) DEFAULT NULL,
  `api_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_servers`
--

INSERT INTO `otp_servers` (`id`, `server_name`, `server_code`, `provider_server_id`, `api_id`, `status`, `created_at`, `updated_at`) VALUES
(278, 'Paraguay', '87', '278', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(282, 'Canada', '36', '282', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(283, 'United Kingdom', '16', '283', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(284, 'Qatar', '111', '284', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(285, 'Turkey', '62', '285', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(286, 'United States', '12', '286', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(287, 'UAE', '95', '287', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(288, 'Saudi Arabia', '53', '288', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(289, 'Vietnam', '10', '289', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(290, 'Spain', '56', '290', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(291, 'France', '78', '291', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(292, 'Italy', '86', '292', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(293, 'Netherlands', '48', '293', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(294, 'Australia', '175', '294', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(295, 'Austria', '50', '295', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(296, 'Russia', '0', '296', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(297, 'Morocco', '37', '297', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(298, 'Germany', '43', '298', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(299, 'New Zealand', '67', '299', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(300, 'Singapore', '196', '300', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(301, 'Indonesia', '6', '301', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(302, 'India', '22', '302', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(303, 'Korea', '1002', '303', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(304, 'Nigeria', '19', '304', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(305, 'South Africa', '31', '305', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(306, 'Bangladesh', '60', '306', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(307, 'Ukraine', '1', '307', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(308, 'Malaysia', '7', '308', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(309, 'Philippines', '4', '309', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(310, 'Romania', '32', '310', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(311, 'Thailand', '52', '311', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(312, 'Ghana', '38', '312', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(313, 'Portugal', '117', '313', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(314, 'Poland', '15', '314', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(315, 'Peru', '65', '315', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(316, 'Mexico', '54', '316', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(317, 'Greece', '129', '317', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(318, 'Brazil', '73', '318', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(319, 'Argentinas', '39', '319', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(320, 'Norway', '174', '320', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(321, 'Cape Verde', '186', '321', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(322, 'Puerto Rico', '97', '322', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(323, 'Sweden', '46', '323', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(324, 'China', '3', '324', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(325, 'Colombia', '33', '325', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(326, 'Denmark', '172', '326', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(327, 'Monaco', '144', '327', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(328, 'Egypt', '21', '328', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(329, 'Hong Kong', '14', '329', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(330, 'Ireland', '23', '330', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(331, 'Finland', '163', '331', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(332, 'Slovakia', '141', '332', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(333, 'Grenada', '127', '333', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(334, 'Seychelles', '184', '334', 1, '1', '2026-03-22 19:16:22', '2026-03-22 19:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(255) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'NGN',
  `amount` decimal(16,2) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `status` enum('pending','success','failed','cancelled') NOT NULL DEFAULT 'pending',
  `channel` varchar(255) DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(16,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_category_id`, `name`, `price`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Instagram Account - Basic', 5.00, 'Single Instagram account. Login credentials provided after purchase.', NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(2, 1, 'Instagram Account - Aged', 12.00, 'Aged Instagram account with some history.', NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(3, 2, 'TikTok Account', 4.50, 'TikTok account ready to use.', NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(4, 3, 'Facebook Account', 6.00, 'Facebook account with email access.', NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(5, 4, 'Twitter / X Account', 7.00, 'Twitter (X) account credentials.', NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Instagram Accounts', 1, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(2, 'TikTok Accounts', 2, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(3, 'Facebook Accounts', 3, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(4, 'Twitter / X Accounts', 4, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(5, 'YouTube Accounts', 5, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(6, 'Telegram Accounts', 6, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(7, 'Other Accounts', 99, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `product_details`
--

CREATE TABLE `product_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `details` text NOT NULL,
  `is_sold` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_details`
--

INSERT INTO `product_details` (`id`, `product_id`, `details`, `is_sold`, `created_at`, `updated_at`) VALUES
(1, 1, 'Username:demo_ig_1 | Password:DemoPass123', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(2, 1, 'Username:demo_ig_2 | Password:DemoPass456', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(3, 1, 'Username:demo_ig_3 | Password:DemoPass789', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(4, 2, 'Username:aged_ig_1 | Password:AgedPass111', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(5, 2, 'Username:aged_ig_2 | Password:AgedPass222', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(6, 3, 'Username:demo_tt_1 | Password:TikTokPass1', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(7, 3, 'Username:demo_tt_2 | Password:TikTokPass2', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(8, 3, 'Username:demo_tt_3 | Password:TikTokPass3', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(9, 3, 'Username:demo_tt_4 | Password:TikTokPass4', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(10, 4, 'Email:fb_demo1@example.com | Password:FBPass111', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(11, 4, 'Email:fb_demo2@example.com | Password:FBPass222', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(12, 5, 'Username:demo_tw_1 | Password:TwitterPass1', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(13, 5, 'Username:demo_tw_2 | Password:TwitterPass2', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(14, 5, 'Username:demo_tw_3 | Password:TwitterPass3', 0, '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `referral_commissions`
--

CREATE TABLE `referral_commissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `referrer_user_id` bigint(20) UNSIGNED NOT NULL,
  `referred_user_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deposit_amount` decimal(16,2) NOT NULL,
  `commission_percent` decimal(5,2) NOT NULL,
  `commission_amount` decimal(16,2) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `status` enum('paid','cancelled') NOT NULL DEFAULT 'paid',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_id` varchar(255) NOT NULL,
  `server_id` varchar(255) NOT NULL,
  `service_price` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `service_id`, `server_id`, `service_price`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Winzo', 'wnz', '1', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(2, 'Any other ', 'ot', '1', '2', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(3, 'Bharat pe ', 'bhr', '1', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(4, 'Winzo ', 'vs', '2', '15', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(5, 'Any Other ', 'ot', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(6, 'Instagram ', 'idg', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(7, 'Google,YouTube,Gmail ', 'go', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(8, 'Google(3)', 'go_3', '3', '11', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(9, 'Vkontakte', 'vk', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(10, 'Telegram ', 'tg', '3', '24', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(11, 'Telegram (3)', 'tg_3', '3', '30', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(12, 'Amazon ', 'am', '3', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(13, 'Amazon (3)', 'am_3', '3', '13', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(14, 'WhatsApp ', 'wa', '3', '40', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(15, 'WhatsApp (5)', 'wa_5', '3', '35', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(16, 'Open ai', 'dr', '3', '35', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(17, 'Mihuashi', 'yd', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(18, 'Redbus', 'ci', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(19, 'CommunityGaming', 'zx', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(20, 'BeReal', 'ck', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(21, 'Adani', 'bq', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(22, 'Loanflix', 'di', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(23, 'AUBANK', 'du', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(24, 'RummyCulture', 'ec', '3', '6.7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(25, 'Indian oil', 'Fg', '3', '7.3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(26, 'Hirect', 'ks', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(27, 'Medibuddy', 'lg', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(28, 'Cashmine', 'ld', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(29, 'Linode', 'ex', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(30, 'EscapeFromTarkov', 'ah', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(31, 'Stockydodo', 'ali', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(32, 'Mera Gaon', 'alp', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(33, 'ZET:Refer&Earn', 'zra', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(34, 'Hinge Dating ', 'aii', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(35, 'Bookmyplay', 'akg', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(36, 'Fan tv', 'fat', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(37, 'CheQ credit ', 'cec', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(38, 'Vegas Casino', 'vec', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(39, 'Yes bank', 'yeb', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(40, 'Pickright', 'pic', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(41, 'Winds', 'wds', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(42, 'Kreditbee', 'krb', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(43, 'Khatabook', 'adk', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(44, 'Marwadi', 'adg', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(45, 'FitCredit', 'adm', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(46, 'Crickex', 'rix', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(47, 'Rummy Vip', 'ruv', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(48, 'Gromo partner', 'grr', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(49, 'QwikCilver', 'acr', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(50, 'Pantaloons', 'pas', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(51, 'Rush', 'rsh', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(52, 'CollabAct', 'acg', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(53, 'TVS motor', 'tvm', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(54, 'Winter Loan', 'acf', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(55, 'Teenpatti lucky card', 'tlc', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(56, 'JioCinema', 'jic', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(57, 'Ludo Select', 'lul', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(58, 'Nykaa', 'nyk', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(59, 'Gullak', 'gul', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(60, 'Fello', 'fll', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(61, 'Booster', 'boo', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(62, 'PokerBaazi', 'pok', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(63, 'Eatclub', 'etc', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(64, 'Pipiko', 'pip', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(65, 'KaloorSports', 'kal', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(66, 'GamerPe', 'gmp', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(67, '96in', 'nis', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(68, 'GetMega', 'gem', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(69, 'SMARTCOIN', 'smo', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(70, 'GROUPON MALL', 'grm', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(71, 'Mgamer', 'mga', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(72, 'Marvel bet', 'mab', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(73, 'ixigo Train Status Book Ticket', 'ixi', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(74, 'Cashbee', 'cab', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(75, 'Khilladi', 'khd', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(76, 'Open phone', 'opp', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(77, 'Winzo', 'wnz', '3', '17', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(78, 'Winzo (5)', 'wnz_5', '3', '17', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(79, 'Winzo (3)', 'wnz_3', '3', '22', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(80, 'Flipkart ', 'flp', '3', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(81, 'Winzo', 'winzo', '9', '22', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(82, 'Teenpatti Joy', 'teenpattijoy', '8', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(83, 'Airtel ', 'zl', '3', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(84, 'Rainbow Rummy ', 'ray', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(85, 'Paytm', 'ge', '3', '12.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(86, 'Jio mart', 'jmt', '3', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(87, 'Winzo', 'vs', '7', '13.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(88, 'Microsoft ', 'mm', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(89, 'Rummy Ares', 'rms', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(90, 'Joy Rummy', 'jor', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(91, 'Rummy XL', 'rux', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(92, 'Rummy Bloc', 'rmu', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(93, 'Rummy Tour', 'rmo', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(94, 'Deccan Rummy', 'der', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(95, 'Rummy Master', 'rmr', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(96, 'LazyPay', 'bb', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(97, 'MobiKwik', 'fo', '3', '9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(98, 'Swiggy', 'jx', '3', '9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(99, 'Winzo new ', 'winzo', '8', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(100, 'Jiomart', 'jiomart', '8', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(101, 'Airtel ', 'airtel', '8', '11', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(102, 'Navi', 'nav', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(103, 'Truecaller', 'tl', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(104, 'Lazypay ', 'lazypay', '8', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(105, 'Yono games ', 'yog', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(106, 'Yono games ', 'yonogames', '8', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(107, 'Teenpatti Joy', 'ttj', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(108, 'Rummy Nabob', 'rmn', '3', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(109, 'Zupee', 'mi', '3', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(110, 'LazyPay', 'bb', '10', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(111, 'Winzo 95% new', 'vs', '4', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(112, 'TeenPatti refer-earn', 'ter', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(113, 'IRCTC', 'irc', '3', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(114, 'Winzo', 'vs', '12', '17', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(115, 'WhatsApp', 'whatsapp', '8', '25', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(116, 'Any other ', 'other', '8', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(117, 'IRCTC ', 'us', '4', '15', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(118, 'winzo', 'vs', '14', '9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(119, 'Telegram ', 'tg', '19', '25', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(120, 'My11Circle', 'my11circle', '8', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(121, 'My11Circle', 'rlr', '3', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(122, 'Ludo Supreme', 'lus', '3', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(123, 'Winzo', 'vs', '15', '15', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(124, 'Yonorummy', 'yonorummy', '8', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(125, 'Winzo', 'wnz', '16', '9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(126, 'IRCTC ', 'irctc', '8', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(127, 'Truecaller ', 'tl', '20', '3.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(128, 'Microsoft ', 'mm', '20', '3.2', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(129, 'LazyPay ', 'bb', '20', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(130, 'Paytm ', 'ge', '20', '11.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(131, 'Dream 11', 've', '20', '15', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(132, 'Winzo', 'vs', '20', '15', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(133, 'IndianOil', 'fg', '20', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(134, 'Winzo', 'vs', '21', '16', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(135, 'Yono rummy ', 'yog', '20', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(136, 'Fastwin', 'fst', '3', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(137, 'Google ', 'go', '8', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(138, 'Winzo', 'vs', '22', '12.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(139, 'Truecaller ', 'tl', '22', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(140, 'Google ', 'go', '22', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(141, 'IRCTC ', '0183', '26', '6.2', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(142, 'WhatsApp ', '0107', '26', '20', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(143, 'Yonogames ', '2423', '26', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(144, 'Winzo 90% old ', '1551', '26', '7.8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(145, 'Whatsapp', 'wa', '29', '100', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(146, 'Telegram ', '0257', '26', '18', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(147, 'Truecaller ', '0284', '26', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(148, 'LuckyLand Slots', 'acc', '334', '532.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(149, 'WhatsApp', 'wa', '286', '1980.56', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(150, 'Instagram', 'ig', '282', '1130.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(151, 'Bajaj finance ', 'bfs', '46', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(152, 'Winzo', 'vs', '51', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(153, 'Telegram', 'tg', '277', '1300', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(154, 'WhatsApp', 'wa', '70', '1750', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(155, 'Amazon / AWS', 'am', '278', '743.75', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(156, 'Yahoo', 'mb', '286', '645.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(157, 'pgtry', 'rtr', '46', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(158, 'Paypal', '2', '275', '2312.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(159, 'WhatsApp', 'wa', '275', '1586.40', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(160, 'WeChat', 'wb', '286', '665.00', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(161, 'Zomato ', '0945', '26', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(162, 'Telegram', 'tg', '27', '2310.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(163, 'Flipkart ', '0563', '26', '7.9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(164, 'Simpl', '1814', '26', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(165, 'Airtel ', '0893', '26', '11', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(166, 'LuckyLand Slots', 'acc', '283', '673.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(167, 'VKontakte', 'vk', '286', '830.45', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(168, 'TeenPatti live', '2746', '26', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(169, 'LuckyLand Slots', 'acc', '288', '984.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(170, 'LuckyLand Slots', 'acc', '287', '805.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(171, 'LazyPay', 'bb', '4', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(172, 'Cardspatti', '4197', '26', '8.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(173, 'Airtel ', 'zl', '25', '12.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(174, 'LuckyLand Slots', 'acc', '331', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(175, 'LuckyLand Slots', 'acc', '333', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(176, 'Telegram', 'tg', '282', '1267.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(177, 'Telegram', 'tg', '286', '1824.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(178, 'LuckyLand Slots', 'acc', '282', '673.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(179, 'Telegram', '1', '275', '1324.70', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(180, 'Whatsapp', 'wa', '28', '25', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(181, 'Myntra', 'nl', '46', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(182, 'Winzo 88% new', 'vs', '30', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(183, 'LuckyLand Slots', 'acc', '285', '672.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(184, 'Viber', 'vi', '286', '958.80', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(185, 'WhatsApp', '5', '275', '1590.01', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(186, 'anyother', 'ot', '45', '3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(187, 'winzo old', '1551', '31', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(188, 'WhatsApp', 'wa', '27', '1556.92', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(189, 'Winzo', 'vs', '32', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(190, 'IRCTC ', 'irc', '33', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(191, 'Irct', 'us', '34', '100', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(192, 'Winzo 99% new ', 'wnz', '35', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(193, 'dream11', 've', '45', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(194, 'dream11', 've', '4', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(195, 'LuckyLand Slots', 'acc', '332', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(196, 'airtel', 'zl', '45', '3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(197, 'flipkart', 'xt', '45', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(198, 'WhatsApp new', 'wa', '36', '35', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(199, 'Airtel ', '0893', '31', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(200, 'Facebook ', 'fb', '50', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(201, 'Rush', 'kv', '48', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(202, 'Winzo 95% new', 'vs', '49', '8.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(203, 'Irctc', 'us', '50', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(204, 'Winzo old 70%', 'vs', '46', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(205, 'Flipkart old', '0563', '31', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(206, 'Zupee', 'mi', '30', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(207, 'Dream11 ', 've', '30', '9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(208, 'My11 circle ', 'ha', '4', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(209, 'LuckyLand Slots', 'acc', '284', '872.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(210, 'Pgtry', 'rtr', '41', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(211, 'Anyother', 'ot', '37', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(212, 'bajaj', 'agq', '37', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(213, 'Winzo', 'vs', '37', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(214, 'zepto', 'adi', '37', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(215, 'Tataneu ', 'ace', '37', '3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(216, 'Jiomart', 'aay', '37', '3.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(217, 'My11circle', 'ha', '37', '3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(218, 'dream11', 've', '37', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(219, 'DREAM 11', 've', '41', '11', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(220, 'WhatsApp', 'wa', '37', '30', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(221, 'Flipkart', 'xt', '37', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(222, 'olx', 'sn', '37', '2.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(223, 'Amazon', 'am', '37', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(224, 'Google', 'go', '37', '2.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(225, 'Zupee', 'mi', '37', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(226, 'IRCTC', 'us', '37', '4.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(227, 'Winzo', 'vs', '39', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(228, 'ANYOTHER ', 'ot', '38', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(229, 'Rummy circle', 'adj', '37', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(230, 'Any other ', 'ot', '39', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(231, 'Winzo new 90%', 'vs', '40', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(232, 'winzo old 70%', 'vs', '41', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(233, 'Goldsbet', 'sgo', '41', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(234, 'dream11', 've', '25', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(235, 'Bajaj finserv ', 'bfs', '41', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(236, 'Flipkart ', 'xt', '41', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(237, 'Google,youtube,Gmail', 'go', '41', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(238, 'Amazon ', 'am', '41', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(239, 'LuckyLand Slots', 'acc', '330', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(240, 'Winzo', 'wnz', '48', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(241, 'Winzo', 'wnz', '47', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(242, 'Winzo 80% new', 'vs', '25', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(243, 'Jiomart', 'aay', '41', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(244, 'My11Circle', 'ha', '41', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(245, 'Rush', 'kv', '41', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(246, 'Airtel ', 'zl', '41', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(247, 'irctc', 'us', '45', '3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(248, 'Winzo', 'vs', '42', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(249, 'bajaj finserv', 'agq', '45', '3', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(250, 'olx', 'sn', '45', '2.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(251, 'LuckyLand Slots', 'acc', '328', '732.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(252, 'My Jar', 'mjr', '46', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(253, 'Winzo ', 'wnz', '52', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(254, 'Bajaj', 'bfs', '52', '4', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(255, 'Rummy wealth ', 'so', '46', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(256, 'Dream 11', 've', '49', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(257, 'Nykaa', 'nkn', '46', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(258, 'Flipkart ', 'xt', '46', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(259, 'Facebook', 'fb', '46', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(260, 'MobiKwik', 'fo', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(261, 'dream11', 've', '46', '8.9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(262, 'Goldsbet', 'sgo', '60', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(263, 'Goldsbet', 'sgo', '46', '9', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(264, 'Winzo', 'vs', '56', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(265, 'Dream11', 've', '56', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(266, 'winzo', 'wnz', '57', '50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(267, 'winzo', 'vs', '60', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(268, 'kamaai kender', 'eid', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(269, 'Amazon (fastest server)', 'am', '46', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(270, 'Holy rummy', 'mmt', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(271, '567 slots', 'tss', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(272, 'WINZO', 'vs', '58', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(273, 'Winzo', 'wnz', '59', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(274, ' Yono Rummy', 'mrn', '46', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(275, ' Yono VIP', 'nio', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(276, 'Yono Games', 'agr', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(277, 'Yono Arcade', 'deo', '46', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(278, 'any other', 'ot', '62', '6.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(279, 'winzo', 'vs', '61', '7.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(280, 'whatsapp', 'wa', '60', '40', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(281, 'Yono Arcade', 'deo', '60', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(282, 'amit', 'us', '46', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(283, 'rush', 'kv', '60', '5.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(284, 'flipkart', 'xt', '60', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(285, 'Winzo emergency ', 'vs', '62', '12.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(286, 'Amazon (super fast)', 'am', '60', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(287, 'Winzo', 'vs', '63', '6', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(288, 'Winzo', 'vs', '64', '8', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(289, 'Winzo', 'vs', '65', '5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(290, 'Dream11', 've', '65', '7', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(291, 'Dream11 ', 've', '64', '10.5', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(292, 'Paytm', 'ge', '65', '12', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(293, 'Winzo', 'vs', '66', '10', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(294, 'Winzo', 'vs', '67', '20', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(295, 'Tinder', '82', '275', '1150.00', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(296, 'Tiktok', 'lf', '68', '500', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(297, 'Instagram', 'ig', '265', '696.96', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(298, 'WhatsApp', 'wa', '265', '1300', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(299, 'LuckyLand Slots', 'acc', '329', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(300, 'LuckyLand Slots', 'acc', '326', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(301, 'LuckyLand Slots', 'acc', '327', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(302, 'LuckyLand Slots', 'acc', '318', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(303, 'LuckyLand Slots', 'acc', '325', '642.50', '1', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(304, 'LuckyLand Slots', 'acc', '324', '642.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(305, 'LuckyLand Slots', 'acc', '323', '642.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(306, 'WhatsApp', 'wa', '283', '3550.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(307, 'WhatsApp', 'wa', '282', '3965.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(308, 'WhatsApp', 'wa', '287', '1668.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(309, 'WhatsApp', 'wa', '288', '1768.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(310, 'WhatsApp', 'wa', '284', '1768.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(311, 'WhatsApp', 'wa', '285', '1768.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(312, 'WhatsApp', 'wa', '328', '2135.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(313, 'WhatsApp', 'wa', '334', '2135.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(314, 'WhatsApp', 'wa', '333', '2135.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(315, 'WhatsApp', 'wa', '332', '2135.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(316, 'WhatsApp', 'wa', '331', '2517.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(317, 'WhatsApp', 'wa', '330', '8765.35', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(318, 'WhatsApp', 'wa', '329', '6390.56', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(319, 'WhatsApp', 'wa', '326', '2352.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(320, 'WhatsApp', 'wa', '327', '2652.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(321, 'WhatsApp', 'wa', '318', '4832.67', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(322, 'WhatsApp', 'wa', '325', '3695.36', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(323, 'WhatsApp', 'wa', '324', '4950.36', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(324, 'WhatsApp', 'wa', '323', '4027.29', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(325, 'WhatsApp', 'wa', '322', '2847.68', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(326, 'WhatsApp', 'wa', '321', '2147.68', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(327, 'WhatsApp', 'wa', '320', '1968.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(328, 'WhatsApp', 'wa', '319', '2210.95', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(329, 'WhatsApp', 'wa', '317', '3058.64', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(330, 'WhatsApp', 'wa', '316', '2129.32', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(331, 'WhatsApp', 'wa', '315', '2129.32', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(332, 'WhatsApp', 'wa', '314', '6528.04', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(333, 'WhatsApp', 'wa', '300', '97871.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(334, 'WhatsApp', 'wa', '313', '5269.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(335, 'WhatsApp', 'wa', '312', '2256.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(336, 'WhatsApp', 'wa', '311', '3283.61', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(337, 'WhatsApp', 'wa', '310', '3574.22', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(338, 'WhatsApp', 'wa', '309', '2210.95', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(339, 'WhatsApp', 'wa', '308', '4206.14', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(340, 'WhatsApp', 'wa', '307', '5632.67', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(341, 'WhatsApp', 'wa', '306', '2350.55', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(342, 'WhatsApp', 'wa', '305', '2150.55', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(343, 'WhatsApp', 'wa', '304', '2420.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(344, 'WhatsApp', 'wa', '303', '2350.55', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(345, 'WhatsApp', 'wa', '302', '2150.55', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(346, 'WhatsApp', 'wa', '301', '2738.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(347, 'WhatsApp', 'wa', '299', '16530.69', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(348, 'WhatsApp', 'wa', '295', '6564.77', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(349, 'WhatsApp', 'wa', '298', '8650.35', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(350, 'WhatsApp', 'wa', '297', '2915.43', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(351, 'WhatsApp', 'wa', '296', '6798.58', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(352, 'WhatsApp', 'wa', '293', '4548.44', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(353, 'WhatsApp', 'wa', '294', '31319.31', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(354, 'WhatsApp', 'wa', '291', '2368.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(355, 'WhatsApp', 'wa', '292', '6468.20', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(356, 'WhatsApp', 'wa', '290', '15108.97', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(357, 'WhatsApp', 'wa', '289', '2847.68', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(358, 'WhatsApp', 'wa', '278', '2168.23', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(359, 'Facebook', 'fb', '286', '1968.83', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(360, 'Facebook', 'fb', '283', '1584.42', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(361, 'Facebook', 'fb', '282', '1342.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(362, 'Facebook', 'fb', '287', '1251.85', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(363, 'Facebook', 'fb', '288', '1251.85', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(364, 'Facebook', 'fb', '284', '1251.85', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(365, 'Facebook', 'fb', '285', '1251.85', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(366, 'Facebook', 'fb', '328', '1426.94', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(367, 'Facebook', 'fb', '334', '1236.59', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(368, 'Facebook', 'fb', '333', '1236.59', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(369, 'Facebook', 'fb', '332', '1492.33', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(370, 'Facebook', 'fb', '331', '1355.01', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(371, 'Facebook', 'fb', '330', '1459.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(372, 'Facebook', 'fb', '329', '1560.26', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(373, 'Facebook', 'fb', '326', '1317.96', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(374, 'Facebook', 'fb', '327', '1317.96', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(375, 'Facebook', 'fb', '318', '1242.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(376, 'Facebook', 'fb', '325', '942.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(377, 'Facebook', 'fb', '324', '965.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(378, 'Facebook', 'fb', '323', '1255.01', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(379, 'Facebook', 'fb', '322', '955.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(380, 'Facebook', 'fb', '321', '975.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(381, 'Facebook', 'fb', '320', '975.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(382, 'Facebook', 'fb', '319', '1275.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(383, 'Facebook', 'fb', '317', '1584.42', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(384, 'Facebook', 'fb', '316', '1356.40', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(385, 'Facebook', 'fb', '315', '1156.40', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(386, 'Facebook', 'fb', '314', '1456.40', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(387, 'Facebook', 'fb', '300', '4596.75', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(388, 'Facebook', 'fb', '313', '1596.75', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(389, 'Facebook', 'fb', '312', '1242.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(390, 'Facebook', 'fb', '311', '1446.60', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(391, 'Facebook', 'fb', '310', '1246.60', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(392, 'Facebook', 'fb', '309', '1246.60', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(393, 'Facebook', 'fb', '308', '1463.27', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(394, 'Facebook', 'fb', '307', '1923.53', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(395, 'Facebook', 'fb', '306', '1257.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(396, 'Facebook', 'fb', '305', '714.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(397, 'Facebook', 'fb', '304', '810.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(398, 'Facebook', 'fb', '303', '810.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(399, 'Facebook', 'fb', '302', '731.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(400, 'Facebook', 'fb', '301', '831.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(401, 'Facebook', 'fb', '299', '1323.10', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(402, 'Facebook', 'fb', '295', '1947.68', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(403, 'Facebook', 'fb', '298', '1775.03', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(404, 'Facebook', 'fb', '297', '742.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(405, 'Facebook', 'fb', '296', '742.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(406, 'Facebook', 'fb', '293', '742.30', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(407, 'Facebook', 'fb', '294', '4859.20', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(408, 'Facebook', 'fb', '291', '659.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(409, 'Facebook', 'fb', '292', '1011.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(410, 'Facebook', 'fb', '290', '711.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(411, 'Facebook', 'fb', '289', '761.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(412, 'Facebook', 'fb', '278', '761.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(413, 'Telegram', 'tg', '283', '1965.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(414, 'Telegram', 'tg', '287', '1367.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(415, 'Telegram', 'tg', '288', '1167.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(416, 'Telegram', 'tg', '284', '1167.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(417, 'Telegram', 'tg', '285', '1167.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(418, 'Telegram', 'tg', '328', '1167.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(419, 'Telegram', 'tg', '334', '1103.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(420, 'Telegram', 'tg', '333', '1103.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(421, 'Telegram', 'tg', '332', '1153.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(422, 'Telegram', 'tg', '331', '843.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(423, 'Telegram', 'tg', '330', '3179.60', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(424, 'Telegram', 'tg', '329', '2153.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(425, 'Telegram', 'tg', '326', '4248.44', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(426, 'Telegram', 'tg', '327', '1167.16', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(427, 'Telegram', 'tg', '318', '2430.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(428, 'Telegram', 'tg', '325', '1298.28', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(429, 'Telegram', 'tg', '324', '1148.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(430, 'Telegram', 'tg', '323', '5928.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(431, 'Telegram', 'tg', '322', '1228.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(432, 'Telegram', 'tg', '321', '1172.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(433, 'Telegram', 'tg', '320', '1172.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(434, 'Telegram', 'tg', '319', '1172.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(435, 'Telegram', 'tg', '317', '2540.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(436, 'Telegram', 'tg', '316', '1231.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(437, 'Telegram', 'tg', '315', '963.27', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(438, 'Telegram', 'tg', '314', '6754.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(439, 'Telegram', 'tg', '300', '51435.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(440, 'Telegram', 'tg', '313', '3532.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(441, 'Telegram', 'tg', '312', '1830.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(442, 'Telegram', 'tg', '311', '2189.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(443, 'Telegram', 'tg', '310', '1667.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(444, 'Telegram', 'tg', '309', '1826.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(445, 'Telegram', 'tg', '308', '2795.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(446, 'Telegram', 'tg', '307', '10339.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(447, 'Telegram', 'tg', '306', '1339.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(448, 'Telegram', 'tg', '305', '2210.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(449, 'Telegram', 'tg', '304', '2210.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(450, 'Telegram', 'tg', '303', '1100.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(451, 'Telegram', 'tg', '302', '1240.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(452, 'Telegram', 'tg', '301', '1240.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(453, 'Telegram', 'tg', '299', '8523.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(454, 'Telegram', 'tg', '295', '2658.64', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(455, 'Telegram', 'tg', '298', '5943.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(456, 'Telegram', 'tg', '297', '1884.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(457, 'Telegram', 'tg', '296', '7238.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(458, 'Telegram', 'tg', '293', '5317.09', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(459, 'Telegram', 'tg', '294', '1317.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(460, 'Telegram', 'tg', '291', '2826.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(461, 'Telegram', 'tg', '292', '1567.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(462, 'Telegram', 'tg', '290', '2567.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(463, 'Telegram', 'tg', '289', '1489.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(464, 'Telegram', 'tg', '278', '2489.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(465, 'Instagram', 'ig', '283', '1150', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(466, 'Instagram', 'ig', '286', '645.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(467, 'Instagram', 'ig', '282', '1130.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(468, 'Instagram', 'ig', '287', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(469, 'Instagram', 'ig', '288', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(470, 'Instagram', 'ig', '284', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(471, 'Instagram', 'ig', '285', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(472, 'Instagram', 'ig', '328', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(473, 'Instagram', 'ig', '334', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(474, 'Instagram', 'ig', '333', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(475, 'Instagram', 'ig', '332', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(476, 'Instagram', 'ig', '331', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(477, 'Instagram', 'ig', '330', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(478, 'Instagram', 'ig', '329', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(479, 'Instagram', 'ig', '326', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(480, 'Instagram', 'ig', '327', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(481, 'Instagram', 'ig', '318', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(482, 'Instagram', 'ig', '325', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(483, 'Instagram', 'ig', '324', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(484, 'Instagram', 'ig', '323', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(485, 'Instagram', 'ig', '322', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(486, 'Instagram', 'ig', '321', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(487, 'Instagram', 'ig', '320', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(488, 'Instagram', 'ig', '319', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(489, 'Instagram', 'ig', '317', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(490, 'Instagram', 'ig', '316', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(491, 'Instagram', 'ig', '315', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(492, 'Instagram', 'ig', '314', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(493, 'Instagram', 'ig', '300', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(494, 'Instagram', 'ig', '313', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(495, 'Instagram', 'ig', '312', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(496, 'Instagram', 'ig', '311', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(497, 'Instagram', 'ig', '310', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(498, 'Instagram', 'ig', '309', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(499, 'Instagram', 'ig', '308', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(500, 'Instagram', 'ig', '307', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(501, 'Instagram', 'ig', '306', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(502, 'Instagram', 'ig', '305', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(503, 'Instagram', 'ig', '304', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(504, 'Instagram', 'ig', '303', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(505, 'Instagram', 'ig', '302', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(506, 'Instagram', 'ig', '301', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(507, 'Instagram', 'ig', '299', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(508, 'Instagram', 'ig', '295', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(509, 'Instagram', 'ig', '298', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(510, 'Instagram', 'ig', '296', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(511, 'Instagram', 'ig', '297', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(512, 'Instagram', 'ig', '293', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(513, 'Instagram', 'ig', '294', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(514, 'Instagram', 'ig', '291', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(515, 'Instagram', 'ig', '292', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(516, 'Instagram', 'ig', '290', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(517, 'Instagram', 'ig', '289', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(518, 'Instagram', 'ig', '278', '1150.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(519, 'Walmart', 'wr', '286', '645.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(520, 'Walmart', 'wr', '283', '645.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(521, 'Walmart', 'wr', '282', '645.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(522, 'Walmart', 'wr', '287', '1645.00', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(523, 'Adidas', 'an', '283', '980.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(524, 'Adidas', 'an', '286', '980.50', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `service_icons`
--

CREATE TABLE `service_icons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `short_code` varchar(255) NOT NULL,
  `img_url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_icons`
--

INSERT INTO `service_icons` (`id`, `short_code`, `img_url`, `created_at`, `updated_at`) VALUES
(1, 'wnz', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8Br7w9JzkyIag4WOJWizaWpdaY00ib87iwXXpefg9Ug&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(2, 'tg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/2048px-Telegram_logo.svg.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(3, 'mb', 'https://cdn1.iconfinder.com/data/icons/smallicons-logotypes/32/yahoo-512.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(4, 'aa', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQVl5WB68-qQy0ZZ15B8aSvF5vxTLknwfeRP8ANlv4vEA&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(5, 'rsh', 'https://i.pinimg.com/originals/fa/50/b2/fa50b28ca286404796f76aec8d733c6e.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(6, 'adh', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQYjFPNq7k0aoDZz7rITMVMIcsbOeEezN0T286x4AtY1A&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(7, 'vk', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRf6QqI3z35JSsDD4CVqYSYzVxYK6R41u3nk4AJ5Iu4IQ&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(8, 'idg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/2048px-Instagram_logo_2016.svg.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(9, 'go', 'https://cdn-teams-slug.flaticon.com/google.jpg', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(10, 'vs', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8Br7w9JzkyIag4WOJWizaWpdaY00ib87iwXXpefg9Ug&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(11, 'mjr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStKZknYITvQ8B_i-4dCW3F2HxHHpK7TBUqJSnk0Imw7qr21jVcfy_3z_A&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(12, 'go_3', 'https://cdn-teams-slug.flaticon.com/google.jpg', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(13, 'wa', 'https://cdn-icons-png.flaticon.com/512/3670/3670051.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(14, 'dr', 'https://static-00.iconduck.com/assets.00/openai-icon-2021x2048-4rpe5x7n.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(15, 'zx', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRtSvw9JHdU9WrikrFNJ-sCGl_vUJnHurs1pw&usqp=CAU', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(16, 'ci', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTe7Xd5KmoXvaQPmaGejf5Cm7yfSp2USKw3461SQzVKUcj6ZOiQ6vOC8Co&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(17, 'ck', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStPuLN8uUGMtYg_CY9PEpXGCUDJwg1VVHDC39Jb1MzxA&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(18, 'di', 'https://m.media-amazon.com/images/I/61VuVNzAt1L.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(19, 'du', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQjQOQE2Abl67enqKNWQtt---S8bdF8Z7Co5O64g-D-bf_GKsB6b5WxAUw&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(20, 'bb', 'https://cdn-images-1.medium.com/v2/resize:fit:1200/1*CxL6FKrFEX5uiNJXxlTfcw.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(21, 'nav', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQalmZ0xi4zTN4sywfCE5CKccOGOmNY444uDL1P2AQltoQOJK1pYJHfunI&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(22, 'tl', 'https://upload.wikimedia.org/wikipedia/commons/0/02/TrueCaller_Icon.png', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(23, 'dy', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT8nRNfM_vH-KXfVsrYTz7rctEhrRscF1eobm5gzo4zYX8FbFJIJ63JMU4X&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(24, 'rsj', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQyAVT-0uKLYaV1A1bgCekn_3CS0I-R2qUvLFPnntb9XjW1OvwHsDiz9btr&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(25, 'smp', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3Fe48dj2Q2rGCM4reXYibjXEhLn4SPJ97FMFyxHnlMg&s', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(26, 'rmb', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQP3OKGGUwoOEgTp5CWy-AgdKeISQ5n9IVq5Wg13UYdTlwQEk0Ywy6Cg1W-&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(27, 'rmn', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQP3OKGGUwoOEgTp5CWy-AgdKeISQ5n9IVq5Wg13UYdTlwQEk0Ywy6Cg1W-&s=10', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(28, 'ter', 'https://i0.wp.com/teenpattireferearn.com/img/teen-patti-refer-earn-logo.webp?strip=all', '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(29, 'rms', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYcwXEqemY8-2lBiZcZOvE2vi5UK0cCEeTAatsTrVcGA&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(30, 'rme', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ1rTBqukGjMng5opguwOICZoZwQlG0P-UmQBwNbjgxQA&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(31, 'bfs', 'https://companieslogo.com/img/orig/BAJAJFINSV.NS-69a58fe4.png?t=1596838048', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(32, 'cap', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ3lWTTYhuf6mn0nKyC0oS5rO0wIJ072i29d9NkGxhKGDSxXKfCdoMgwwY&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(33, 'fg', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSPG2ArXzy8sC4IC1_qLnNEghPt_Io4nOkIBPSfEo6rRg3AnxRkTLQZ1U7Z&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(34, 'ec', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTcOIMW2ZXQQi56CslWkTppveWFu99H-VlYmEXg7xCfmjuEb6huxyMeJ5RG&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(35, 'ks', 'https://play-lh.googleusercontent.com/mkuNjtUPpNAvk0tI6NcllPqRO2jnGS3W5TJIHJGlCrOUVcaM5ZzVyFBkmUjL4H3Tdg', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(36, 'id', 'https://lh3.googleusercontent.com/7ATZTNY7SHZB7hcP9qdcuJ0zA29g6hN5asl4X-UQXYDev3DFH36quT5ewUyGl7QUKLHJltOgqYsUDiHA830OFodIshxbRuvRjMw', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(37, 'ex', 'https://static-00.iconduck.com/assets.00/linode-icon-427x512-4l4fs2tu.png', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(38, 'ah', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcROSXnkIUmYEGMJIIDrfg80KI_uxsbLA94wVw&usqp=CAU', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(39, 'fo', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_CjocT_DXaqUNDscpPiPZAkY7G0P-v8Y_ZJzbbhCwkQ&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(40, 'tg_3', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/82/Telegram_logo.svg/2048px-Telegram_logo.svg.png', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(41, 'jx', 'https://cdn.iconscout.com/icon/free/png-256/free-swiggy-1613371-1369418.png', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(42, 'adi', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTJ7BGFvKkNKTBzt2lruYxcKn2jBMVgGRvBJ_yquusnWg&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(43, 'mm', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMIUHKeZUSADdjxX7WJVNw3hJk_PbCEcVXtXGcLsdQrQ&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(44, 'mi', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRC97bPKSDRspPU-WdpfssGsoVialyvbyrIXBEo7G8axg&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(45, 'flp', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgyhRhEGap4lug-9rI0fHdKSSMpWntXtnyZ7Cpxwg0DKpKUGSTTGsDDmo&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(46, 'ge', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTkZ3AO2eCD3xqEswfI46evQ-RK1VeaPtWLgoZDpxrUuLMJPVcSx8K47DE&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(47, 'rlr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfyW2345lfHY9bLrpYWBos09Ncr5S9Yn5uIOFf7xXZFw&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(48, 'zl', 'https://play-lh.googleusercontent.com/uFg3zOsnGZkIrswmvXyFYhoF3gC4tv0ovFZv0zisJFQ2DZqJyh9SUGrK6D-Tnn1lGqc', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(49, 've', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxlCt1q-O7ztbzGEKrK8RSB7zOz6EoP7LzJ8noMt-icWVXE3QhfIf9xw8w&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(50, 'rtr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSEtfKODFf6I-vsHrYPJBnu0T_-8GlF8IpdJxy4yncS3Q&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(51, 'nl', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ8RNQjrnnHaVq7_cIdXXeKF_hw36ejOr4U5PHuQkEifXjcvBdCgyd8BIyq&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(52, 'so', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRcxz3sPuRMoByAxOg9_EQuBeEQK6x1DcUbsWCBH9HFwg&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(53, 'yd', 'https://images.crunchbase.com/image/upload/c_pad,f_auto,q_auto:eco,dpr_1/307da4ceb53e3089786b', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(54, 'ot', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSw99DZ1_VpbuJrxXBOvmmCalpqGBUeGKpdEg&usqp=CAU', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(55, 'nkn', 'https://fastsms.su/img/service/nyk.png', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(56, 'xt', 'https://fastsms.su/img/service/flp.png', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(57, 'ig', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/2048px-Instagram_logo_2016.svg.png', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(58, 'am', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSH-_j31mFpg7kzUDjGtzBeuQ-4DQX_7aBAaJFBvbtLXeI358dHp43VEhTZ&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(59, 'wr', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRLD7iDoakCpbyXg1rwqDN8I3rxSHO-4ltYS9hcPKewhg&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(60, 'fb', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMIUHKeZUSADdjxX7WJVNw3hJk_PbCEcVXtXGcLsdQrQ&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(61, 'vi', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_CjocT_DXaqUNDscpPiPZAkY7G0P-v8Y_ZJzbbhCwkQ&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(62, 'an', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQalmZ0xi4zTN4sywfCE5CKccOGOmNY444uDL1P2AQltoQOJK1pYJHfunI&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(63, 'acc', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStPuLN8uUGMtYg_CY9PEpXGCUDJwg1VVHDC39Jb1MzxA&s', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(64, 'lf', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTe7Xd5KmoXvaQPmaGejf5Cm7yfSp2USKw3461SQzVKUcj6ZOiQ6vOC8Co&s=10', '2026-03-22 19:16:23', '2026-03-22 19:16:23'),
(65, '82', 'https://www.flaticon.com/free-icon/tinder_4423669?term=tinder&page=1&position=3&origin=search&related_id=4423669', '2026-03-22 19:16:23', '2026-03-22 19:16:23');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('HamHOT04ZLEQSqjeKi7hHHRngFmaWIXOutydIfi1', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoialp3dGpzUEZzU2dsNnZHeVBnT0d6VHR5bHdUbDhObUR6eWhtU3pUSSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2JhY2tlbmQvdXNlcnMvZGV0YWlscy8xIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9iYWNrZW5kL3VzZXJzIjtzOjU6InJvdXRlIjtzOjE5OiJiYWNrZW5kLnVzZXJzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1774210603);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `website_name` varchar(255) DEFAULT NULL,
  `website_currency` varchar(255) DEFAULT NULL,
  `website_color` varchar(255) NOT NULL DEFAULT '#4c45e0',
  `timezone` varchar(255) NOT NULL DEFAULT 'UTC',
  `copyright_text` varchar(255) DEFAULT NULL,
  `user_registration` tinyint(4) NOT NULL DEFAULT 1,
  `recaptcha` tinyint(4) NOT NULL DEFAULT 0,
  `user_email_notification` tinyint(4) NOT NULL DEFAULT 1,
  `admin_email_notification` tinyint(4) NOT NULL DEFAULT 1,
  `user_payment` tinyint(4) NOT NULL DEFAULT 1,
  `user_login` tinyint(4) NOT NULL DEFAULT 1,
  `buy_number` tinyint(4) NOT NULL DEFAULT 1,
  `boost_social` tinyint(4) NOT NULL DEFAULT 1,
  `social_logs` tinyint(4) NOT NULL DEFAULT 1,
  `referral_enabled` tinyint(4) NOT NULL DEFAULT 0,
  `referral_commission_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `referral_first_deposit_only` tinyint(4) NOT NULL DEFAULT 1,
  `seo_description` text DEFAULT NULL,
  `live_chat_code` text DEFAULT NULL,
  `logo_white` varchar(255) DEFAULT NULL,
  `logo_dark` varchar(255) DEFAULT NULL,
  `favicon_white` varchar(255) DEFAULT NULL,
  `favicon_dark` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `website_name`, `website_currency`, `website_color`, `timezone`, `copyright_text`, `user_registration`, `recaptcha`, `user_email_notification`, `admin_email_notification`, `user_payment`, `user_login`, `buy_number`, `boost_social`, `social_logs`, `referral_enabled`, `referral_commission_percent`, `referral_first_deposit_only`, `seo_description`, `live_chat_code`, `logo_white`, `logo_dark`, `favicon_white`, `favicon_dark`, `created_at`, `updated_at`) VALUES
(1, 'My Website', 'USD', '#4c45e0', 'UTC', '© 2025 My Website All Rights Reserved', 1, 0, 1, 1, 1, 1, 1, 1, 1, 0, 0.00, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-22 19:16:22', '2026-03-22 19:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `smm_categories`
--

CREATE TABLE `smm_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sort` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smm_categories`
--

INSERT INTO `smm_categories` (`id`, `name`, `sort`, `created_at`, `updated_at`) VALUES
(1, 'Instagram', 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(2, 'TikTok', 2, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(3, 'YouTube', 3, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(4, 'Facebook', 4, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(5, 'Twitter / X', 5, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(6, 'Telegram', 6, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(7, 'Spotify', 7, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(8, 'Other', 99, '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `smm_orders`
--

CREATE TABLE `smm_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `smm_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `smm_service_id` bigint(20) UNSIGNED NOT NULL,
  `link` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `charge` decimal(16,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'awaiting',
  `api_provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `api_service_id` varchar(255) DEFAULT NULL,
  `api_order_id` varchar(255) DEFAULT NULL,
  `start_counter` int(10) UNSIGNED DEFAULT NULL,
  `remains` int(10) UNSIGNED DEFAULT NULL,
  `mode` tinyint(1) NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `service_type` varchar(50) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `is_drip_feed` tinyint(1) NOT NULL DEFAULT 0,
  `runs` int(10) UNSIGNED DEFAULT NULL,
  `interval` int(10) UNSIGNED DEFAULT NULL,
  `dripfeed_quantity` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smm_providers`
--

CREATE TABLE `smm_providers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `balance` decimal(16,2) DEFAULT NULL,
  `currency_code` varchar(10) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smm_services`
--

CREATE TABLE `smm_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `smm_category_id` bigint(20) UNSIGNED NOT NULL,
  `api_provider_id` bigint(20) UNSIGNED DEFAULT NULL,
  `api_service_id` varchar(255) DEFAULT NULL,
  `min` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `max` int(10) UNSIGNED NOT NULL DEFAULT 10000,
  `price` decimal(16,4) NOT NULL DEFAULT 0.0000,
  `original_price` decimal(16,4) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'default',
  `add_type` varchar(20) NOT NULL DEFAULT 'manual',
  `dripfeed` tinyint(1) NOT NULL DEFAULT 0,
  `desc` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smm_services`
--

INSERT INTO `smm_services` (`id`, `name`, `smm_category_id`, `api_provider_id`, `api_service_id`, `min`, `max`, `price`, `original_price`, `type`, `add_type`, `dripfeed`, `desc`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Instagram Followers', 1, NULL, NULL, 100, 100000, 1.5000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(2, 'Instagram Likes', 1, NULL, NULL, 50, 50000, 0.8000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(3, 'Instagram Comments', 1, NULL, NULL, 10, 5000, 2.0000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(4, 'TikTok Followers', 2, NULL, NULL, 100, 100000, 1.2000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(5, 'TikTok Likes', 2, NULL, NULL, 50, 50000, 0.5000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(6, 'TikTok Views', 2, NULL, NULL, 1000, 1000000, 0.3000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(7, 'YouTube Views', 3, NULL, NULL, 1000, 500000, 2.5000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(8, 'YouTube Likes', 3, NULL, NULL, 100, 10000, 3.0000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(9, 'YouTube Subscribers', 3, NULL, NULL, 100, 50000, 5.0000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(10, 'Facebook Page Likes', 4, NULL, NULL, 100, 10000, 2.0000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(11, 'Facebook Post Likes', 4, NULL, NULL, 50, 5000, 1.0000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(12, 'Twitter Followers', 5, NULL, NULL, 100, 50000, 2.2000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(13, 'Twitter Likes', 5, NULL, NULL, 50, 10000, 1.5000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(14, 'Telegram Channel Members', 6, NULL, NULL, 100, 50000, 1.8000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(15, 'Spotify Plays', 7, NULL, NULL, 1000, 100000, 4.0000, NULL, 'default', 'manual', 0, NULL, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `top_services`
--

CREATE TABLE `top_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `server_id` varchar(255) NOT NULL,
  `service_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `top_services`
--

INSERT INTO `top_services` (`id`, `server_id`, `service_id`, `status`, `created_at`, `updated_at`) VALUES
(1, '282', 'tg', '1', '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(16,2) NOT NULL,
  `balance_before` decimal(16,2) NOT NULL,
  `balance_after` decimal(16,2) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutorials`
--

CREATE TABLE `tutorials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0=off, 1=on',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tutorials`
--

INSERT INTO `tutorials` (`id`, `title`, `url`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'How to Buy a Number', '#', 1, 1, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(2, 'How to Fund Your Wallet With Paystack', '#', 1, 2, '2026-03-22 19:16:24', '2026-03-22 19:16:24'),
(3, 'How to Fund Your Wallet With Flutterwave', '#', 1, 3, '2026-03-22 19:16:24', '2026-03-22 19:16:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `balance` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_otp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `verification_code` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=active, 0=deactivate',
  `reffered_by` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `api_key` varchar(80) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_email_templates`
--

CREATE TABLE `user_email_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template` text DEFAULT NULL,
  `meaning` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=inactive, 1=active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_email_templates`
--

INSERT INTO `user_email_templates` (`id`, `name`, `subject`, `template`, `meaning`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Welcome Email', 'Welcome to our platform', '<p><b>Hi {fname} {lname},</b></p><p>Welcome! Your account has been created successfully. You can now log in with your email and password.</p><p>Thanks,</p><p>Team</p>', '{\"username\":\"Username\",\"email\":\"Email\",\"fname\":\"First name\",\"lname\":\"Last name\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(2, 'Payment Success', 'Payment received', '<p><b>Hi {username},</b></p><p>Your payment of {currency} {amount} has been received successfully.</p><p>Reference: {reference}<br>Method: {method}</p><p>Thanks,</p><p>Team</p>', '{\"username\":\"Username\",\"email\":\"Email\",\"amount\":\"Payment amount\",\"currency\":\"Currency code\",\"reference\":\"Payment reference\",\"method\":\"Payment method\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(3, 'Number Purchased', 'Number purchased successfully', '<p><b>Hi {username},</b></p><p>You have successfully purchased a number.</p><p>Number: {number}<br>Service: {service_name}<br>Amount: {amount}</p><p>Thanks,</p><p>Team</p>', '{\"username\":\"Username\",\"email\":\"Email\",\"number\":\"Purchased number\",\"service_name\":\"Service name\",\"amount\":\"Amount paid\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(4, 'Payment Submitted', 'Payment proof submitted', '<p><b>Hi {username},</b></p><p>Your payment proof for {currency} {amount} (Reference: {reference}) has been submitted. We will notify you once it is approved.</p><p>Thanks,</p><p>Team</p>', '{\"username\":\"Username\",\"email\":\"Email\",\"amount\":\"Amount\",\"currency\":\"Currency\",\"reference\":\"Reference\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(5, 'Payment Approved', 'Payment approved', '<p><b>Hi {username},</b></p><p>Your bank payment of {currency} {amount} (Reference: {reference}) has been approved. The amount has been added to your wallet.</p><p>Thanks,</p><p>Team</p>', '{\"username\":\"Username\",\"email\":\"Email\",\"amount\":\"Amount\",\"currency\":\"Currency\",\"reference\":\"Reference\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22'),
(6, 'Password Reset', 'Password reset code', '<p><b>Hi {fname},</b></p><p>You requested a password reset. Use the code below to set a new password. This code expires in {expires_in}.</p><p><strong>Reset code: {reset_code}</strong></p><p>If you did not request this, please ignore this email.</p><p>Thanks,</p><p>Team</p>', '{\"fname\":\"First name\",\"reset_code\":\"6-digit reset code\",\"expires_in\":\"Code validity (e.g. 1 hour)\"}', 1, '2026-03-22 19:16:22', '2026-03-22 19:16:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_orders`
--
ALTER TABLE `account_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_orders_user_id_foreign` (`user_id`);

--
-- Indexes for table `account_order_items`
--
ALTER TABLE `account_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_order_items_account_order_id_foreign` (`account_order_id`),
  ADD KEY `account_order_items_product_id_foreign` (`product_id`),
  ADD KEY `account_order_items_product_detail_id_foreign` (`product_detail_id`);

--
-- Indexes for table `active_numbers`
--
ALTER TABLE `active_numbers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active_numbers_user_id_foreign` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_username_unique` (`username`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `admin_email_templates`
--
ALTER TABLE `admin_email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_email_templates_key_unique` (`key`);

--
-- Indexes for table `api_details`
--
ALTER TABLE `api_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `custom_prices`
--
ALTER TABLE `custom_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `custom_prices_user_id_foreign` (`user_id`);

--
-- Indexes for table `email_configs`
--
ALTER TABLE `email_configs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `gateways`
--
ALTER TABLE `gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gateways_gateway_name_unique` (`gateway_name`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_servers`
--
ALTER TABLE `otp_servers`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `payments_reference_unique` (`reference`),
  ADD KEY `payments_user_id_foreign` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_product_category_id_foreign` (`product_category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_details_product_id_foreign` (`product_id`);

--
-- Indexes for table `referral_commissions`
--
ALTER TABLE `referral_commissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_commissions_payment_id_unique` (`payment_id`),
  ADD KEY `referral_commissions_referred_user_id_foreign` (`referred_user_id`),
  ADD KEY `referral_commissions_referrer_user_id_created_at_index` (`referrer_user_id`,`created_at`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_icons`
--
ALTER TABLE `service_icons`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smm_categories`
--
ALTER TABLE `smm_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smm_orders`
--
ALTER TABLE `smm_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `smm_orders_user_id_foreign` (`user_id`),
  ADD KEY `smm_orders_smm_category_id_foreign` (`smm_category_id`),
  ADD KEY `smm_orders_smm_service_id_foreign` (`smm_service_id`),
  ADD KEY `smm_orders_api_provider_id_foreign` (`api_provider_id`);

--
-- Indexes for table `smm_providers`
--
ALTER TABLE `smm_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smm_services`
--
ALTER TABLE `smm_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `smm_services_smm_category_id_foreign` (`smm_category_id`),
  ADD KEY `smm_services_api_provider_id_foreign` (`api_provider_id`);

--
-- Indexes for table `top_services`
--
ALTER TABLE `top_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `tutorials`
--
ALTER TABLE `tutorials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_api_key_unique` (`api_key`);

--
-- Indexes for table `user_email_templates`
--
ALTER TABLE `user_email_templates`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_orders`
--
ALTER TABLE `account_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_order_items`
--
ALTER TABLE `account_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `active_numbers`
--
ALTER TABLE `active_numbers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_email_templates`
--
ALTER TABLE `admin_email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `api_details`
--
ALTER TABLE `api_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `custom_prices`
--
ALTER TABLE `custom_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_configs`
--
ALTER TABLE `email_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gateways`
--
ALTER TABLE `gateways`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `otp_servers`
--
ALTER TABLE `otp_servers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=335;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `referral_commissions`
--
ALTER TABLE `referral_commissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=525;

--
-- AUTO_INCREMENT for table `service_icons`
--
ALTER TABLE `service_icons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `smm_categories`
--
ALTER TABLE `smm_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `smm_orders`
--
ALTER TABLE `smm_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smm_providers`
--
ALTER TABLE `smm_providers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `smm_services`
--
ALTER TABLE `smm_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `top_services`
--
ALTER TABLE `top_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tutorials`
--
ALTER TABLE `tutorials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_email_templates`
--
ALTER TABLE `user_email_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_orders`
--
ALTER TABLE `account_orders`
  ADD CONSTRAINT `account_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `account_order_items`
--
ALTER TABLE `account_order_items`
  ADD CONSTRAINT `account_order_items_account_order_id_foreign` FOREIGN KEY (`account_order_id`) REFERENCES `account_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_order_items_product_detail_id_foreign` FOREIGN KEY (`product_detail_id`) REFERENCES `product_details` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `account_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `active_numbers`
--
ALTER TABLE `active_numbers`
  ADD CONSTRAINT `active_numbers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_prices`
--
ALTER TABLE `custom_prices`
  ADD CONSTRAINT `custom_prices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_product_category_id_foreign` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_details`
--
ALTER TABLE `product_details`
  ADD CONSTRAINT `product_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_commissions`
--
ALTER TABLE `referral_commissions`
  ADD CONSTRAINT `referral_commissions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referral_commissions_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referral_commissions_referrer_user_id_foreign` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `smm_orders`
--
ALTER TABLE `smm_orders`
  ADD CONSTRAINT `smm_orders_api_provider_id_foreign` FOREIGN KEY (`api_provider_id`) REFERENCES `smm_providers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `smm_orders_smm_category_id_foreign` FOREIGN KEY (`smm_category_id`) REFERENCES `smm_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `smm_orders_smm_service_id_foreign` FOREIGN KEY (`smm_service_id`) REFERENCES `smm_services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `smm_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `smm_services`
--
ALTER TABLE `smm_services`
  ADD CONSTRAINT `smm_services_api_provider_id_foreign` FOREIGN KEY (`api_provider_id`) REFERENCES `smm_providers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `smm_services_smm_category_id_foreign` FOREIGN KEY (`smm_category_id`) REFERENCES `smm_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
