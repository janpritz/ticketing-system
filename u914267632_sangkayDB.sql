-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2026 at 08:36 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u914267632_sangkayDB`
--

-- --------------------------------------------------------

--
-- Table structure for table `analytics_ticket_snapshot`
--

CREATE TABLE `analytics_ticket_snapshot` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('Open','Forwarded','Closed') NOT NULL,
  `assigned_agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `snapshot_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
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
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Enrollment', 'Handles enrollment, registration, and course-related inquiries', '2026-02-26 19:59:59', '2026-02-26 19:59:59'),
(2, 'Finance and Payments', 'Handles tuition fees, payments, refunds, and billing', '2026-02-26 20:00:00', '2026-02-26 20:00:00'),
(3, 'Scholarships', 'Handles scholarship applications and financial aid', '2026-02-26 20:00:00', '2026-02-26 20:00:00'),
(4, 'Academic Concerns', 'Handles grades, transcripts, graduation, and academic issues', '2026-02-26 20:00:00', '2026-02-26 20:00:00'),
(5, 'Exams', 'Handles exam schedules, results, and accommodations', '2026-02-26 20:00:01', '2026-02-26 20:00:01'),
(6, 'Student Services', 'Handles student life, counseling, activities, and support services', '2026-02-26 20:00:01', '2026-02-26 20:00:01'),
(7, 'Library Services', 'Handles library resources, borrowing, and research assistance', '2026-02-26 20:00:01', '2026-02-26 20:00:01'),
(8, 'IT Support', 'Handles technical support, Wi-Fi, software, and hardware issues', '2026-02-26 20:00:01', '2026-02-26 20:00:01'),
(9, 'Graduation', 'Handles commencement, diplomas, and graduation requirements', '2026-02-26 20:00:02', '2026-02-26 20:00:02'),
(10, 'Athletics and Sports', 'Handles sports clubs, PE classes, and athletic events', '2026-02-26 20:00:02', '2026-02-26 20:00:02'),
(11, 'Primary Administrator', 'System administration and overall management', '2026-02-26 20:00:02', '2026-02-26 20:00:02');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `rasa_doc_id` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_changes`
--

CREATE TABLE `document_changes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `action` enum('created','updated','deleted') NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `old_content_hash` varchar(64) DEFAULT NULL,
  `new_content_hash` varchar(64) DEFAULT NULL,
  `change_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `training_required` tinyint(1) NOT NULL DEFAULT 1,
  `training_completed` tinyint(1) NOT NULL DEFAULT 0,
  `training_timestamp` timestamp NULL DEFAULT NULL,
  `model_name` varchar(255) DEFAULT NULL,
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
(4, '2025_09_04_110456_create_personal_access_tokens_table', 1),
(5, '2025_09_04_111633_create_tickets_table', 1),
(6, '2025_09_06_090726_create_ticket_routing_histories_table', 1),
(7, '2025_09_06_120001_add_response_to_tickets_table', 1),
(8, '2025_09_14_084500_update_status_enums_to_rerouted', 1),
(9, '2025_09_14_103200_add_profile_fields_to_users_table', 1),
(10, '2025_10_14_084900_create_roles_table', 1),
(11, '2025_10_14_085000_migrate_roles_to_roles_table', 1),
(12, '2025_10_16_193526_create_push_notifications_table', 1),
(13, '2025_10_17_110000_add_subscription_to_push_notifications_table', 1),
(14, '2025_10_17_123000_create_push_notification_msgs_table', 1),
(15, '2025_10_18_094700_create_categories_table', 1),
(16, '2025_11_11_224105_add_attachments_to_tickets_table', 1),
(17, '2025_11_13_001241_rename_rerouted_to_forwarded_status', 1),
(18, '2025_11_26_151047_add_soft_deletes_to_users_table', 1),
(19, '2025_11_26_161027_create_otps_table', 1),
(20, '2025_12_07_193212_create_analytics_ticket_snapshot_table', 1),
(21, '2025_12_11_173116_create_document_changes_table', 1),
(22, '2025_12_13_131002_create_pinned_announcements_table', 1),
(23, '2025_12_14_175914_add_model_name_to_document_changes_table', 1),
(24, '2025_12_16_163831_create_rasa_models_table', 1),
(25, '2026_01_04_045629_add_category_id_to_users_table', 1),
(26, '2026_01_04_054703_add_category_id_to_tickets_table', 1),
(27, '2026_01_04_110000_backfill_ticket_category_id', 1),
(28, '2026_01_04_130000_drop_legacy_category_from_tickets', 1),
(29, '2026_01_05_054500_add_first_view_fields_to_tickets_table', 1),
(30, '2026_01_05_120000_add_email_notifications_to_users', 1),
(31, '2026_01_05_123000_set_all_email_notifications_false', 1),
(32, '2026_01_07_072500_create_upload_logs_table', 1),
(33, '2026_01_07_122000_create_announcement_roles_table', 1),
(34, '2026_01_07_141200_create_announcements_table', 1),
(35, '2026_01_07_143000_drop_announcement_roles_table', 1),
(36, '2026_01_07_145000_create_documents_table', 1),
(37, '2026_01_07_150500_make_role_id_nullable_on_categories', 1),
(38, '2026_01_08_000000_create_documents_table', 1),
(39, '2026_02_11_172334_create_processed_tickets_table', 1),
(40, '2026_02_11_172431_create_staged_faqs_table', 1),
(41, '2026_02_14_120000_create_departments_table', 1),
(42, '2026_02_14_121000_add_department_id_to_roles_table', 1),
(43, '2026_02_14_122000_add_department_id_to_users_table', 1),
(44, '2026_02_14_123000_create_user_roles_table', 1),
(45, '2026_02_14_124000_add_role_id_to_tickets_table', 1),
(46, '2026_02_14_130000_drop_category_id_from_users_table', 1),
(47, '2026_02_14_131000_drop_department_id_and_role_id_from_users_table', 1),
(48, '2026_02_15_165224_make_password_nullable_in_users_table', 1),
(49, '2026_02_22_060000_add_verification_token_to_users_table', 1),
(50, '2026_02_22_150333_add_department_id_to_user_roles_table', 1),
(51, '2026_02_22_160000_add_is_primary_role_to_user_roles_table', 1),
(52, '2026_02_26_130000_add_is_processed_to_tickets_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `email`, `otp_code`, `expires_at`, `verified_at`, `created_at`, `updated_at`) VALUES
(6, 'acc.sangkaychatbot@gmail.com', '272314', '2026-02-27 15:09:02', NULL, '2026-02-27 14:54:02', '2026-02-27 14:54:02'),
(8, 'academics.johnfritzcabalhin@gmail.com', '265851', '2026-03-01 15:19:24', NULL, '2026-03-01 15:04:24', '2026-03-01 15:04:24');

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
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pinned_announcements`
--

CREATE TABLE `pinned_announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_notifications`
--

CREATE TABLE `push_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscriptions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`subscriptions`)),
  `subscription` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`subscription`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `push_notifications`
--

INSERT INTO `push_notifications` (`id`, `subscriptions`, `subscription`, `created_at`, `updated_at`) VALUES
(1, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:01:10', '2026-02-26 20:01:10'),
(2, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:01:26', '2026-02-26 20:01:26'),
(3, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:01:32', '2026-02-26 20:01:32'),
(4, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:01:49', '2026-02-26 20:01:49'),
(5, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:04:48', '2026-02-26 20:04:48'),
(6, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:06:12', '2026-02-26 20:06:12'),
(7, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 20:06:51', '2026-02-26 20:06:51'),
(8, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:16:52', '2026-02-26 20:16:52'),
(9, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:26:30', '2026-02-26 20:26:30'),
(10, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:37:10', '2026-02-26 20:37:10'),
(11, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:38:31', '2026-02-26 20:38:31'),
(12, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:51:03', '2026-02-26 20:51:03'),
(13, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:51:53', '2026-02-26 20:51:53'),
(14, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:56:18', '2026-02-26 20:56:18'),
(15, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:57:28', '2026-02-26 20:57:28'),
(16, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:57:54', '2026-02-26 20:57:54'),
(17, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:59:02', '2026-02-26 20:59:02'),
(18, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 20:59:17', '2026-02-26 20:59:17'),
(19, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:07:01', '2026-02-26 21:07:01'),
(20, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:07:51', '2026-02-26 21:07:51'),
(21, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:10:34', '2026-02-26 21:10:34'),
(22, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:28:45', '2026-02-26 21:28:45'),
(23, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:29:35', '2026-02-26 21:29:35'),
(24, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:29:51', '2026-02-26 21:29:51'),
(25, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:30:08', '2026-02-26 21:30:08'),
(26, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:30:20', '2026-02-26 21:30:20'),
(27, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:32:30', '2026-02-26 21:32:30'),
(28, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:32:45', '2026-02-26 21:32:45'),
(29, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:33:33', '2026-02-26 21:33:33'),
(30, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:34:51', '2026-02-26 21:34:51'),
(31, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:44:06', '2026-02-26 21:44:06'),
(32, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:44:13', '2026-02-26 21:44:13'),
(33, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:44:48', '2026-02-26 21:44:48'),
(34, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:49:40', '2026-02-26 21:49:40'),
(35, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:50:17', '2026-02-26 21:50:17'),
(36, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:54:24', '2026-02-26 21:54:24'),
(37, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 21:56:54', '2026-02-26 21:56:54'),
(38, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:01:59', '2026-02-26 22:01:59'),
(39, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:02:38', '2026-02-26 22:02:38'),
(40, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:02:55', '2026-02-26 22:02:55'),
(41, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:07:13', '2026-02-26 22:07:13'),
(42, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:08:29', '2026-02-26 22:08:29'),
(43, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:09:13', '2026-02-26 22:09:13'),
(44, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:12:39', '2026-02-26 22:12:39'),
(45, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:19:48', '2026-02-26 22:19:48'),
(46, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:20:52', '2026-02-26 22:20:52'),
(47, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:21:13', '2026-02-26 22:21:13'),
(48, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 22:32:08', '2026-02-26 22:32:08'),
(49, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:37:00', '2026-02-26 22:37:00'),
(50, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:37:09', '2026-02-26 22:37:09'),
(51, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:37:13', '2026-02-26 22:37:13'),
(52, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:37:17', '2026-02-26 22:37:17'),
(53, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:37:35', '2026-02-26 22:37:35'),
(54, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:45:59', '2026-02-26 22:45:59'),
(55, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:46:26', '2026-02-26 22:46:26'),
(56, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:46:33', '2026-02-26 22:46:33'),
(57, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:46:42', '2026-02-26 22:46:42'),
(58, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:46:49', '2026-02-26 22:46:49'),
(59, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 22:46:52', '2026-02-26 22:46:52'),
(60, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:00:28', '2026-02-26 23:00:28'),
(61, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:00:34', '2026-02-26 23:00:34'),
(62, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:00:38', '2026-02-26 23:00:38'),
(63, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:01:57', '2026-02-26 23:01:57'),
(64, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:02:00', '2026-02-26 23:02:00'),
(65, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:02:06', '2026-02-26 23:02:06'),
(66, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:02:38', '2026-02-26 23:02:38'),
(67, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:02:53', '2026-02-26 23:02:53'),
(68, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:03:41', '2026-02-26 23:03:41'),
(69, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-26 23:19:05', '2026-02-26 23:19:05'),
(70, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:26:10', '2026-02-26 23:26:10'),
(71, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:26:21', '2026-02-26 23:26:21'),
(72, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:35:08', '2026-02-26 23:35:08'),
(73, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:36:40', '2026-02-26 23:36:40'),
(74, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:37:12', '2026-02-26 23:37:12'),
(75, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:37:24', '2026-02-26 23:37:24'),
(76, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:45:26', '2026-02-26 23:45:26'),
(77, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:45:43', '2026-02-26 23:45:43'),
(78, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:45:47', '2026-02-26 23:45:47'),
(79, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-26 23:46:04', '2026-02-26 23:46:04'),
(80, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:01:14', '2026-02-27 00:01:14'),
(81, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:01:35', '2026-02-27 00:01:35'),
(82, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:02:07', '2026-02-27 00:02:07'),
(83, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:02:14', '2026-02-27 00:02:14'),
(84, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:04:14', '2026-02-27 00:04:14'),
(85, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:04:18', '2026-02-27 00:04:18'),
(86, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:11', '2026-02-27 00:06:11'),
(87, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:13', '2026-02-27 00:06:13'),
(88, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:15', '2026-02-27 00:06:15'),
(89, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:29', '2026-02-27 00:06:29'),
(90, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:31', '2026-02-27 00:06:31'),
(91, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:33', '2026-02-27 00:06:33'),
(92, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:06:35', '2026-02-27 00:06:35'),
(93, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:09:32', '2026-02-27 00:09:32'),
(94, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 00:09:43', '2026-02-27 00:09:43'),
(95, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:14:36', '2026-02-27 00:14:36'),
(96, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:14:38', '2026-02-27 00:14:38'),
(97, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:14:43', '2026-02-27 00:14:43'),
(98, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:15:18', '2026-02-27 00:15:18'),
(99, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:15:33', '2026-02-27 00:15:33'),
(100, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:16:04', '2026-02-27 00:16:04'),
(101, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:16:11', '2026-02-27 00:16:11'),
(102, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:16:38', '2026-02-27 00:16:38'),
(103, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:17:16', '2026-02-27 00:17:16'),
(104, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:17:24', '2026-02-27 00:17:24'),
(105, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/deVSKLR4PVg:APA91bFIxRfxL_e-WeQ_TnPXqXcqoj2pfuKRg3koaLOkmb8f_zsp7sP4w1I7OEKkty4eDWShpYETfKwMaq6A9ixKrnlHrkVbqommi6PhyMCEuqt4UoTDV20LtXRF82yLAP7q_FZ6WCMv\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BNuNyysXN9O7-z_Ykvpxe0Cid4KCc3aOtBanWWmS9QQZxrNT2ugLANxRf4yLwOKwPWhUi9PNmNGkvZ_Ymx1mH3Y\",\"auth\":\"9lh5KscdE9HBQD5LjJgqCw\"}}', NULL, '2026-02-27 00:17:27', '2026-02-27 00:17:27'),
(106, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:18:45', '2026-02-27 00:18:45'),
(107, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:18:52', '2026-02-27 00:18:52'),
(108, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:18:57', '2026-02-27 00:18:57'),
(109, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:19:00', '2026-02-27 00:19:00'),
(110, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:19:03', '2026-02-27 00:19:03');
INSERT INTO `push_notifications` (`id`, `subscriptions`, `subscription`, `created_at`, `updated_at`) VALUES
(111, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:19:10', '2026-02-27 00:19:10'),
(112, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:19:24', '2026-02-27 00:19:24'),
(113, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:19:26', '2026-02-27 00:19:26'),
(114, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:19:35', '2026-02-27 00:19:35'),
(115, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:20:03', '2026-02-27 00:20:03'),
(116, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:20:17', '2026-02-27 00:20:17'),
(117, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:20:27', '2026-02-27 00:20:27'),
(118, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:20:43', '2026-02-27 00:20:43'),
(119, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/em5sBXijymg:APA91bEhATizGh6qo7VK0VoLeVkXzr4wxOgs8yIBF7F5t8oebRa1DhaQGrkhQ0EzwRPjgoNR7qVjiJXH0JzpmOP2G3mULPOiT8F8KWEf1w6RG_iIRPSukp5gFlj_f4rADWUYMpAsg8az\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BAeFOBAAWZxiGrb88YDdqWJ9ZZdDJgdn6X4EQm90o2qCuwk5qYRlQlIr_iSZtLIQm7X4rl2j2VPGXT06h5eMeLI\",\"auth\":\"iqGYhnU2XB6xt49YkZ83mg\"}}', NULL, '2026-02-27 00:21:15', '2026-02-27 00:21:15'),
(120, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:37:57', '2026-02-27 14:37:57'),
(121, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:39:50', '2026-02-27 14:39:50'),
(122, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:39:53', '2026-02-27 14:39:53'),
(123, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:06', '2026-02-27 14:40:06'),
(124, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:10', '2026-02-27 14:40:10'),
(125, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:14', '2026-02-27 14:40:14'),
(126, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:17', '2026-02-27 14:40:17'),
(127, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:21', '2026-02-27 14:40:21'),
(128, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:24', '2026-02-27 14:40:24'),
(129, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:30', '2026-02-27 14:40:30'),
(130, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:49', '2026-02-27 14:40:49'),
(131, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:40:59', '2026-02-27 14:40:59'),
(132, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:42:32', '2026-02-27 14:42:32'),
(133, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:42:47', '2026-02-27 14:42:47'),
(134, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:42:50', '2026-02-27 14:42:50'),
(135, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:42:54', '2026-02-27 14:42:54'),
(136, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:42:59', '2026-02-27 14:42:59'),
(137, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:43:20', '2026-02-27 14:43:20'),
(138, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:43:52', '2026-02-27 14:43:52'),
(139, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:44:01', '2026-02-27 14:44:01'),
(140, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:44:11', '2026-02-27 14:44:11'),
(141, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:44:18', '2026-02-27 14:44:18'),
(142, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:45:18', '2026-02-27 14:45:18'),
(143, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:48:14', '2026-02-27 14:48:14'),
(144, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:48:36', '2026-02-27 14:48:36'),
(145, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:48:50', '2026-02-27 14:48:50'),
(146, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:49:57', '2026-02-27 14:49:57'),
(147, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:53:45', '2026-02-27 14:53:45'),
(148, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:58:17', '2026-02-27 14:58:17'),
(149, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:58:26', '2026-02-27 14:58:26'),
(150, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 14:58:37', '2026-02-27 14:58:37'),
(151, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 15:00:01', '2026-02-27 15:00:01'),
(152, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 15:00:22', '2026-02-27 15:00:22'),
(153, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 15:00:35', '2026-02-27 15:00:35'),
(154, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/eFK2A6i0lY4:APA91bGIZcmJkangOisF2RD2jGyZmgonSoeI7O5cySM_R1oHyzXVqIOGOVK4YnyndeoRwVbBaERW6cvbvN7LB2YikKikNzHUxqwGAAUNo1Bfb-OMc1qGnT-Itv5-PnebGsimuXJI74E_\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCN6b8vAOJV9KyJSouTJjV2O5tg01k0QLTOewsi9-FfDJLCtOsGLZkJyFwwnOrfmQdMsHN5UJaqTZWBJ9dQYCus\",\"auth\":\"ik39d1NRznUD_XHPRvcuRg\"}}', NULL, '2026-02-27 15:09:17', '2026-02-27 15:09:17'),
(155, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/cY3PReYFF8w:APA91bH6WHwuXaa8KQbYVYe5NgzclS-28Z3Hst58LdORxQ2sCf0c8G1BL5noJrJleCwoBiiwBoGRGqQNsT60Jx6xjL2N_jD0jUWKx3NhNWfvPeBhCqZOCnEorRbWdHy4qsuWz_qBum98\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCqAPEHTUdB_MFZYiDhWQXlTTvvAwhlp47jJc3csP8y_U2SRft0mZmyti2uu4j4YKFS3sOlYhZENGCnuSq4AV0I\",\"auth\":\"9I_dx5_uhQbPDbDh4IcllA\"}}', NULL, '2026-03-01 15:00:17', '2026-03-01 15:00:17'),
(156, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/cY3PReYFF8w:APA91bH6WHwuXaa8KQbYVYe5NgzclS-28Z3Hst58LdORxQ2sCf0c8G1BL5noJrJleCwoBiiwBoGRGqQNsT60Jx6xjL2N_jD0jUWKx3NhNWfvPeBhCqZOCnEorRbWdHy4qsuWz_qBum98\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCqAPEHTUdB_MFZYiDhWQXlTTvvAwhlp47jJc3csP8y_U2SRft0mZmyti2uu4j4YKFS3sOlYhZENGCnuSq4AV0I\",\"auth\":\"9I_dx5_uhQbPDbDh4IcllA\"}}', NULL, '2026-03-01 15:02:49', '2026-03-01 15:02:49'),
(157, '{\"endpoint\":\"https:\\/\\/fcm.googleapis.com\\/fcm\\/send\\/cY3PReYFF8w:APA91bH6WHwuXaa8KQbYVYe5NgzclS-28Z3Hst58LdORxQ2sCf0c8G1BL5noJrJleCwoBiiwBoGRGqQNsT60Jx6xjL2N_jD0jUWKx3NhNWfvPeBhCqZOCnEorRbWdHy4qsuWz_qBum98\",\"expirationTime\":null,\"keys\":{\"p256dh\":\"BCqAPEHTUdB_MFZYiDhWQXlTTvvAwhlp47jJc3csP8y_U2SRft0mZmyti2uu4j4YKFS3sOlYhZENGCnuSq4AV0I\",\"auth\":\"9I_dx5_uhQbPDbDh4IcllA\"}}', NULL, '2026-03-01 15:04:21', '2026-03-01 15:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `push_notification_msgs`
--

CREATE TABLE `push_notification_msgs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rasa_models`
--

CREATE TABLE `rasa_models` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model_name` varchar(255) NOT NULL,
  `size` bigint(20) DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`, `department_id`) VALUES
(1, 'Primary Administrator', 'System administrator with full access', '2026-02-26 20:00:03', '2026-02-26 20:00:03', 11),
(2, 'Enrollment', NULL, '2026-02-26 20:00:04', '2026-02-26 20:00:04', 1),
(3, 'Finance and Payments', NULL, '2026-02-26 20:00:05', '2026-02-26 20:00:05', 2),
(4, 'Scholarships', NULL, '2026-02-26 20:00:05', '2026-02-26 20:00:05', 3),
(5, 'Academic Concerns', NULL, '2026-02-26 20:00:05', '2026-02-26 20:00:05', 4),
(6, 'Exams', NULL, '2026-02-26 20:00:06', '2026-02-26 20:00:06', 5),
(7, 'Student Services', NULL, '2026-02-26 20:00:06', '2026-02-26 20:00:06', 6),
(8, 'Library Services', NULL, '2026-02-26 20:00:07', '2026-02-26 20:00:07', 7),
(9, 'IT Support', NULL, '2026-02-26 20:00:07', '2026-02-26 20:00:07', 8),
(10, 'Graduation', NULL, '2026-02-26 20:00:08', '2026-02-26 20:00:08', 9),
(11, 'Athletics and Sports', NULL, '2026-02-26 20:00:08', '2026-02-26 20:00:08', 10),
(12, 'Student Affairs', NULL, '2026-02-26 20:00:44', '2026-02-26 20:00:44', NULL);

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
('199gumygLLcLY3SVdeqI61wE8SprqqJL9hkOLemF', NULL, '58.69.206.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVXRidUhmendTR1R3SDZZVWhyTnJDRm5SbTNMUDUxVWlzYU5aVExSNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772187933),
('3ZU0xstFOt8sAZZPWDn1DzxzExo9HpxwWENpjMX3', NULL, '2602:80d:1000::40', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTGZTeTZDTnFpTUwySlR3QktEM1NLb0R5NXJLWUhPN29WNWlJOVpxaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHBzOi8vd3d3LmFjY3NhbmdrYXljaGF0Ym90LmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1772285285),
('6yDOSiABi1e63RTvBjG0S4GQwrLVMBWvkCkssBSW', NULL, '198.244.240.97', 'Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZXZuWDM5U3VveVB0SmJZaWNCeXU2SmtndzdNUG5JSDZ3M1RCVkRmaSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772286682),
('7CT3ooQpCRJkr27bLUC1NVZOWtr1GORGGHMUuDNp', NULL, '2a02:4780:5d:c0de::10', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoid3hBdjlUS2xnc1lGTFJ1V0JkS1ZaZVF3U1JwcDNVTURHMnBVb2c0ZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772268774),
('8HmxqRLCXyiCiHa1FlANkVz2mKjmHMw8pnj9tIfb', NULL, '98.81.237.205', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/536.26.17 (KHTML like Gecko) Version/6.0.2 Safari/536.26.17', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFU0UEFaR0pvcXJ2NnIxUUNUOE9IcEplVnVETm04VDh3WGNJRzR4aCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772194448),
('8i1kxq8lSup1oEbJyaElTMIc8uO2BbQSrCiIVJZV', NULL, '93.158.91.239', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRUdleGdNemRiMTJaT3pzVEdwRFhlR2VtYlo4Y1lmdnFjMGR5enRYRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772206311),
('8jONlQZbmmiXOjsNHNnB8796cMmVOxcuw9ZwEtNg', NULL, '93.158.91.242', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU28zVjlJUXludVZLRlFjSDN4aEFKVEg2OHd0YXpEWlRwV1JWdDgyciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2Fib3V0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772206314),
('9hBSNTqtjVx0Lc8AXZXfDxJMs3XRa6ynRZ2wAGvn', NULL, '2001:4ba0:cafe:b2c::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36 Edg/91.0.864.54', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHE3NDFyMlNnQVFJVFl0SGR5QllJbEFuUVppSmRRdDgyd3liQWdWeCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772299711),
('d1MWCz7j7KSRDHqbwBmvuX03fzNV2yLW904qk2Bx', NULL, '113.77.83.53', 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR1pSZGdOWk5PcXB1dmx2MnprbkwwejRaSjhhYlVpQlhhbXY3d3ZEViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772274520),
('dEbU2k1gm7KqstQRXEVSgmOYTdYkeJ7zezVyte6o', NULL, '2a02:4780:5d:c0de::10', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieHNnbnBIdmwxelhpbzdqeGwwdXo4cm4xaWF6bmtjbTkxTkkzaGVMVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772183419),
('dF4Iu8uXNXU8wInH6YuZ1py7VfWuI75CZGnTRjzJ', NULL, '98.81.237.205', 'Mozilla/5.0 (Linux; Android 4.4.2; SAMSUNG-SM-G900A Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYnQ2aVVUcVhMQU94RThzanZPaVBuc2VPcnBIa1NsZE44c3E3WE5LViI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772194449),
('dNwV3LTYirSRuKPCGQVYL5ZeY2YmVT5K0FmTl95h', NULL, '2602:80d:1000::40', 'Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibXVUWTRtdHhRWEdQTzNURlNhaEtQc2FwVTN5NWtUWUlaWmV6VkZoZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vd3d3LmFjY3NhbmdrYXljaGF0Ym90LmNvbS9mYXFzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772285289),
('dSkC5J6Si5a5MmXvgcrZQoXTMcnH39clvwLmek5c', NULL, '98.81.237.205', 'Mozilla/5.0 (Linux; Android 11; Mi A3 Build/RKQ1.200903.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/101.0.4951.41 YaBrowser/22.1.0.194 (lite) Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMUd2eEJzRWRxcUN0SGZqWlZqVzJqdkZRdklMenVGbkl5c0xYaEFXQSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL3N3LmpzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772194454),
('ECV0Gmozoy9VpobKbPjCUm7qKpCzZZWnIyCUuF66', NULL, '124.104.143.95', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVkNUcGVYMTFyYnhQQUVjYnJCSXI3TDVESzB6Z3ZsYmRGRG5YVWs2SCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL3N3LmpzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772187587),
('exPbnVSYSzd0OfWT3h6NWIP6aDLQajzpxWrDIoJJ', NULL, '3.221.83.235', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTGlPVHB0OTZ0UnlEb0x2cTJEdVAzcjBtb2FBaDVyZmFMbmNPZEVKMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772227219),
('Fil7C2wLXvD3sVK3f20SeOCLponjqprf1OC3qzRk', NULL, '178.73.226.22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieEVKV0NnNjhNaTlnS3VJWm9HVnB6R3k3eFE5R3AxVUtwa0hnOEZqUiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2NvbnRhY3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772229600),
('gdXNaM9VKm1W3MZPN57W0wbzbAuJ4RAM55tavkhz', NULL, '98.92.68.248', 'okhttp/5.3.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlFZN3Jmd2R1UEdqbXJHbGdPb2drMlY4TWZYdUdIdVY3S0o2WFppMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772207916),
('gk1S25lPHBL8NPn2UHVECzGoLLQTa25MeBvbaJTR', NULL, '58.69.206.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNzIyaDI3akZPUEJzWG01OEdsaG04Y3pzY3RhU0FNRU9XYjRKQUdXaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772187932),
('JAuTEMmOt3rCP2QCbRuMoi0G6Cj21tqNzrDxqleW', NULL, '151.252.27.109', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieGxLZG1aQlNOWEtGSHBGckdwYmJSMU5yU3J5bUdXQXRBdEJGc3c4QyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772229596),
('jqSX99z8aYvhqn4fzWr4QQtHEPhz3W3JMW3NZylL', 1, '124.104.143.95', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRVZTcXhiZWdCRFR4VGJOTGhVbnlYWXk3dVZFMHg1UHkxanBzNnduSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL3N3LmpzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1772348799),
('lggpjtK7bMzZrZcxA1mto2tJVWDr3dMHwaLPsJhH', NULL, '98.92.68.248', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/138.0.7204.23 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSUYxU2pLRjdmcDRMNllvaGMyNDVabGg5VkFxaHY3Yjd5WGJnYjlWRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL3N3LmpzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772207981),
('N40tKOSRhfMihGCi5HM5H9crM30hEvSOymaB2uyJ', NULL, '98.92.68.248', 'okhttp/5.3.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMk1HNkxQdENoYVdRVThjVXhjdlJHZXVXVElBVVdweDVmMDBWQkNvcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772207916),
('njyyYDXjOBep7TLk9aTqzofaKWxo4Sohvs7DiD4S', NULL, '93.158.100.184', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic3VFallFRmFDZm1XR25KMTRzb2JIOWM1TWdLQmtDTTVoeHl4SVVBdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772229597),
('QhTeWs2wpnNJSx2EMChiwAAOGOM52L2ZbCQG6BEr', NULL, '115.147.13.50', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU3VXdHN2YjlxbXB4TEJWWk4zd2FvaHBkRkVRNUs0QTcwMklPNmxhUyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL3N3LmpzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772248489),
('RoFmBhi43ETIjmBKL4TKRI4eaXIYimM8FyO7mTHr', NULL, '2a02:4780:5d:c0de::10', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidFU4M0RQdnhLU2todlRZSUI5d0Zpb0tEbEQyTHI0Y0RDTWRwVFIwVyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772343624),
('tEPTVBAS5aKBHfVHTOlf6TQRteVTwJIun4JOMsMa', NULL, '98.81.237.205', 'Mozilla/5.0 (Linux; Android 10; TECNO KE5 Build/QP1A.190711.020) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.99 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkFsTTBFT3ZyUkM4eVdCaG9YRnM4QlRKT0NYMGdsenBiZlZWc2hrbSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772194449),
('UaXvbw210RhMSoTxALZtwycAaYFS0rX3jPHuXI2G', NULL, '3.221.83.235', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaE5wT2FFNGp0bVNzakdrelBTd0JaUDJmcEEyMVBBcFJWVFFJeWZLcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772227218),
('uFknR09736FrikkdAqjifxMWIQBMyCaqjaa6Nun1', NULL, '2a02:4780:5d:c0de::10', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRVhuTVhkMUExR2lZaTdhNlA0R0V6REpTcHZUNVg4RnlMbldNOXZSciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772268774),
('uqMaw4OnBEgXCl0ejyT83pC46pttDLtFqxOix7vR', NULL, '98.81.237.205', 'Mozilla/5.0 (Linux; Android 4.4.2; SAMSUNG-SM-G900A Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.94 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicVBCVURkallxWjJaT1lsMmFUdFRUS2V6N3lCN09KMjhRam1CSVR5ZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772194448),
('vLBkSxpbHXsM1Infmv7sq59r13GSebHxIC5qCroD', NULL, '2001:4ba0:cafe:b2c::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36 Edg/91.0.864.54', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSm5KdmZjbjc3TFFLWGZ5dmxQdUhobXFKaTN3RG1WbkFBY2NhR29TWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772299710),
('vLzN5GTiT9sw5uVVH8TXyOv46vUPjkWQ5SYGxTch', NULL, '39.90.97.85', 'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV2NsN0VRNFYwTDRNWmZlWk9XTHFSWGprZ2RzeWs2QkZYYnhXNjRjWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vd3d3LmFjY3NhbmdrYXljaGF0Ym90LmNvbS9mYXFzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772227330),
('w6ZJfn9RqF2fTNz2DfNmI8gWux4qWqLaLSE8eMGG', NULL, '2a02:4780:5d:c0de::10', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT1o5dVMzWDN3dWlua3hRY05YZzVnRnRwS1dlT3NraU9iWFZGZDRoQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772343624),
('WBzEYifBeBfKq6Mmc24AUd2XjTxpGsns0P1Qa1ah', NULL, '2a02:4780:5d:c0de::10', 'Go-http-client/2.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFN3QnE1NVptNkEyZk9SaGhlZm9CMFJpNkNwT25MaElXblZQTUlQOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2ZhcXMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772183419),
('WIkAE1JN7iQjmoCAMN8MGej9UZG5wbM5TXZIlRQc', NULL, '115.147.13.50', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2tVTnpoYnJlMmVndmNvTm9oSVNNdkdXVjI5M3oxZURTTjJ1UTZWcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL3N3LmpzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772354113),
('xEfk7ttPAA3RcJm6vlykx3hLzdzoLxvsYqpiBibn', NULL, '93.158.92.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiem9MYkpFMzhwR1VObW9DOHRaeU1nQXJ2SGd3WW1URFN4OWc5a0ZDaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772206310),
('XRRlZLhyKdAxFOAobLFFSmH9TI69BVwRJMdOobJH', NULL, '5.198.254.244', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNVFxeHBqREFmSVpuQTRCdHhhZk5yTnFmTWdhdlJPN3UxbWhEcFljSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2Fib3V0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772229600),
('XYONqfTGiAcOmLA72B7s7gQCktfS4HJAwHmeRrHx', NULL, '93.158.91.242', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.3', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT0lSVlRmU3d5VGxoR3dwbmtlbHBiUFJkOVJreGl4ZW51RDhodmxPOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vYWNjc2FuZ2theWNoYXRib3QuY29tL2NvbnRhY3QiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1772206313);

-- --------------------------------------------------------

--
-- Table structure for table `staged_faqs`
--

CREATE TABLE `staged_faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `general_topic` varchar(255) NOT NULL,
  `semantic_key` varchar(255) NOT NULL,
  `suggested_q` text NOT NULL,
  `suggested_a` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staged_faqs`
--

INSERT INTO `staged_faqs` (`id`, `ticket_id`, `general_topic`, `semantic_key`, `suggested_q`, `suggested_a`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Enrollment', 'enrollment_process', 'How do I enroll for the next semester?', 'You can enroll through the student portal during the enrollment period. Visit the enrollment section and follow the guided steps.', '', '2026-02-26 21:09:47', '2026-02-26 21:28:54'),
(2, 1, 'Enrollment', 'enrollment_requirements', 'What are the requirements for enrollment?', 'You need your student ID, previous grades, and proof of payment for the enrollment fee.', '', '2026-02-26 21:09:48', '2026-02-26 21:09:48'),
(3, 2, 'Payments', 'tuition_payment', 'Where can I pay my tuition fees?', 'Tuition fees can be paid at the finance office or online through the payment portal using credit/debit card.', '', '2026-02-26 21:09:48', '2026-02-26 21:09:48'),
(4, 3, 'Scholarships', 'new_student_scholarships', 'What scholarships are available for new students?', 'We offer merit-based scholarships for high achievers and need-based scholarships for students with financial difficulties.', '', '2026-02-26 21:09:48', '2026-02-26 21:09:48'),
(5, 3, 'Scholarships', 'scholarship_requirements', 'What are the requirements for scholarships?', 'Requirements vary by scholarship. Generally, you need a good academic record and meet the specific criteria for each scholarship program.', '', '2026-02-26 21:09:48', '2026-02-26 21:09:48'),
(6, 4, 'Academics', 'drop_subject', 'How can I drop a subject?', 'You can drop a subject within the first two weeks of the semester without penalty. After that period, grades may apply.', '', '2026-02-26 21:09:49', '2026-02-26 21:09:49'),
(7, 5, 'Exams', 'final_exams_schedule', 'When are the final exams scheduled?', 'Final exams are scheduled during the last week of the semester. Check the academic calendar for specific dates.', '', '2026-02-26 21:09:49', '2026-02-26 21:09:49'),
(8, 6, 'IT Support', 'email_password_reset', 'How do I reset my student email password?', 'You can reset your password through the IT portal at it-support.edu/reset or contact IT support at it-helpdesk@example.edu.', '', '2026-02-26 21:09:50', '2026-02-26 21:09:50'),
(9, 1, 'Enrollment', 'enroll-semester', 'How can I enroll for the next semester?', 'You can enroll through the student portal during the enrollment period.', 'approved', '2026-02-26 21:49:05', '2026-02-26 22:20:32'),
(10, 2, 'Tuition Fees', 'pay-fees', 'Where can I pay my tuition fees?', 'Tuition fees can be paid at the finance office or online through the payment portal.', 'approved', '2026-02-26 21:49:05', '2026-02-27 15:09:02'),
(11, 3, 'Scholarships', 'avail-scholarships', 'What scholarships are available for new students?', 'We offer merit-based and need-based scholarships for eligible students.', 'approved', '2026-02-26 21:49:06', '2026-02-26 23:26:14'),
(12, 4, 'Course Management', 'drop-subject', 'How can I drop a subject?', 'You can drop a subject within the first two weeks of the semester without penalty.', 'approved', '2026-02-26 21:49:06', '2026-02-27 00:09:34'),
(13, 5, 'Exams', 'final-exams', 'When are the final exams scheduled?', 'Final exams are scheduled during the last week of the semester.', 'approved', '2026-02-26 21:49:07', '2026-02-27 00:09:39'),
(14, 6, 'IT Support', 'reset-password', 'How do I reset my student email password?', 'You can reset your password through the IT portal or contact IT support.', 'approved', '2026-02-26 21:49:07', '2026-02-27 00:09:37');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `question` text NOT NULL,
  `response` text DEFAULT NULL,
  `recepient_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('Open','Forwarded','Closed') NOT NULL DEFAULT 'Open',
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_closed` timestamp NULL DEFAULT NULL,
  `first_viewed_at` timestamp NULL DEFAULT NULL,
  `first_viewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `is_processed` tinyint(1) NOT NULL DEFAULT 0,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `category_id`, `question`, `response`, `recepient_id`, `email`, `status`, `staff_id`, `date_created`, `date_closed`, `first_viewed_at`, `first_viewed_by`, `created_at`, `updated_at`, `attachments`, `is_processed`, `role_id`) VALUES
(1, NULL, 'How do I enroll for the next semester?', 'You can enroll through the student portal during the enrollment period.', '2', 'student1@example.com', 'Closed', 2, '2026-02-26 21:09:47', '2026-02-26 21:09:47', NULL, NULL, '2026-02-26 21:09:47', '2026-02-26 21:49:05', NULL, 1, 2),
(2, NULL, 'Where can I pay my tuition fees?', 'Tuition fees can be paid at the finance office or online through the payment portal.', '3', 'student2@example.com', 'Closed', 2, '2026-02-26 21:09:48', '2026-02-26 21:09:48', NULL, NULL, '2026-02-26 21:09:48', '2026-02-26 21:49:05', NULL, 1, 3),
(3, NULL, 'What scholarships are available for new students?', 'We offer merit-based and need-based scholarships for eligible students.', '4', 'student3@example.com', 'Closed', 2, '2026-02-26 21:09:48', '2026-02-26 21:09:48', '2026-02-26 23:25:33', 1, '2026-02-26 21:09:48', '2026-02-26 23:25:33', NULL, 1, 4),
(4, NULL, 'How can I drop a subject?', 'You can drop a subject within the first two weeks of the semester without penalty.', '5', 'student4@example.com', 'Closed', 2, '2026-02-26 21:09:49', '2026-02-26 21:09:49', '2026-02-26 23:25:12', 1, '2026-02-26 21:09:49', '2026-02-26 23:25:12', NULL, 1, 5),
(5, NULL, 'When are the final exams scheduled?', 'Final exams are scheduled during the last week of the semester.', '6', 'student5@example.com', 'Closed', 2, '2026-02-26 21:09:49', '2026-02-26 21:09:49', NULL, NULL, '2026-02-26 21:09:49', '2026-02-26 21:49:07', NULL, 1, 6),
(6, NULL, 'How do I reset my student email password?', 'You can reset your password through the IT portal or contact IT support.', '9', 'student6@example.com', 'Closed', 2, '2026-02-26 21:09:49', '2026-02-26 21:09:49', '2026-02-26 23:25:38', 1, '2026-02-26 21:09:49', '2026-02-26 23:25:38', NULL, 1, 9),
(7, NULL, 'test', NULL, '12388jf', 'academics.johnfritzcabalhin@gmail.com', 'Forwarded', 4, '2026-02-26 23:38:26', NULL, '2026-02-26 23:45:48', 1, '2026-02-26 23:38:26', '2026-02-26 23:49:19', '[]', 0, NULL),
(8, NULL, 'test enrollment', NULL, '12388jfFF', 'academics.johnfritzcabalhin@gmail.com', 'Open', 7, '2026-02-27 00:02:06', NULL, '2026-02-27 00:02:16', 1, '2026-02-27 00:02:06', '2026-02-27 00:02:16', '[]', 0, 2),
(9, NULL, 'Test academic concern', NULL, 'jalenloyale@gmail.com', 'jalenloyale@gmail.com', 'Open', 5, '2026-02-27 00:21:11', NULL, NULL, NULL, '2026-02-27 00:21:11', '2026-02-27 00:21:11', '[]', 0, 5),
(10, NULL, 'is there scholarship if varsity player?', 'yes just inquire to the sports and academics office for assistance.', '1234', 'jalenloyale@gmail.com', 'Closed', 12, '2026-02-27 14:57:39', '2026-02-27 15:02:49', '2026-02-27 15:01:28', 1, '2026-02-27 14:57:39', '2026-02-27 15:02:49', '[]', 0, 11);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_routing_histories`
--

CREATE TABLE `ticket_routing_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Open','Forwarded','Closed') NOT NULL,
  `routed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_routing_histories`
--

INSERT INTO `ticket_routing_histories` (`id`, `ticket_id`, `staff_id`, `status`, `routed_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 7, 4, 'Forwarded', '2026-02-26 23:49:19', 'Forwarded by admin to user: Ana Reyes', '2026-02-26 23:49:19', '2026-02-26 23:49:19'),
(2, 8, 7, 'Open', '2026-02-27 00:02:06', 'Ticket created and assigned to staff 7', '2026-02-27 00:02:06', '2026-02-27 00:02:06'),
(3, 9, 5, 'Open', '2026-02-27 00:21:11', 'Ticket created and assigned to staff 5', '2026-02-27 00:21:11', '2026-02-27 00:21:11'),
(4, 10, 12, 'Open', '2026-02-27 14:57:39', 'Ticket created and assigned to staff 12', '2026-02-27 14:57:39', '2026-02-27 14:57:39'),
(5, 10, 1, 'Closed', '2026-02-27 15:02:49', 'Admin responded via UI', '2026-02-27 15:02:49', '2026-02-27 15:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `upload_logs`
--

CREATE TABLE `upload_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT NULL,
  `server_recieved_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `verification_token`, `email_verified_at`, `password`, `profile_photo`, `email_notifications`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Primary Administrator', 'acc.sangkaychatbot@gmail.com', NULL, NULL, '$2y$12$npNzIFbp88vHVqRwAqjN8uZAZFPUDQoQrU.f3ZSILWKEC1UVk8HOy', NULL, 0, '3ALBlZp8ow1YKFiVtKw4GCJXc4PYHPotwqizX9BvI24ZNLPuvSZLYtCOCs7x', '2026-02-26 20:00:16', '2026-02-26 20:00:16', NULL),
(2, 'Maria Santos', 'maria.santos@example.com', NULL, NULL, '$2y$12$EaJEHL/zDtpQm4y2OelYAenELU3EVb7r.u4aaylrci.LJPmsHZy2q', NULL, 0, NULL, '2026-02-26 20:00:19', '2026-02-26 20:00:19', NULL),
(3, 'Juan Dela Cruz', 'juan.delacruz@example.com', NULL, NULL, '$2y$12$LiN9Z6XpnzIOf44v4OSGwu1RS1fpb2g0Onvd6USYJXqCEkYayujDq', NULL, 0, NULL, '2026-02-26 20:00:22', '2026-02-26 20:00:22', NULL),
(4, 'Ana Reyes', 'ana.reyes@example.com', NULL, NULL, '$2y$12$82Dx5uh.cdyrKgSrNTMsm.PKSgnLq1G/GgH10F/fuPipeGM2nbYmm', NULL, 0, NULL, '2026-02-26 20:00:26', '2026-02-26 20:00:26', NULL),
(5, 'Pedro Gonzales', 'pedro.gonzales@example.com', NULL, NULL, '$2y$12$B6dl1njohGDPXi0pnZyG.Ok7o06p25mo9y2I3dFdgfpleIRqMa.sq', NULL, 0, 'WeO3vyTd7EZf7bCptlWSOZHBs6GNT5yQmV1Skum3HNel41zeHwGJ35AkdjVF', '2026-02-26 20:00:29', '2026-02-26 20:00:29', NULL),
(6, 'Luisa Mendoza', 'luisa.mendoza@example.com', NULL, NULL, '$2y$12$JHC/4/EFcogBLz6806cbN.M1AAWx7BsRElJ3/LgnaQ5PTMttr.9c6', NULL, 0, NULL, '2026-02-26 20:00:33', '2026-02-26 20:00:33', NULL),
(7, 'Carlos Bautista', 'carlos.bautista@example.com', NULL, NULL, '$2y$12$VBGpOvAOCoaGj7SMGcXnhujGK1JIvw.LDHvYly0csSyx67BOMI9Om', NULL, 0, NULL, '2026-02-26 20:00:36', '2026-02-26 20:00:36', NULL),
(8, 'Rosa Lim', 'rosa.lim@example.com', NULL, NULL, '$2y$12$PmduZfeex2Rg3321bUGZ.u04qCS3mKPGo2OdmN5oiyFqIH4HtbSm.', NULL, 0, NULL, '2026-02-26 20:00:40', '2026-02-26 20:00:40', NULL),
(9, 'Jose Santos', 'jose.santos@example.com', NULL, NULL, '$2y$12$EqHYb89hvy.dpr///HY8lOQ.60UhKSr3LyV81JbA7.6U6Hlz3hUQ6', NULL, 0, NULL, '2026-02-26 20:00:43', '2026-02-26 20:00:43', NULL),
(10, 'Carmen Torres', 'carmen.torres@example.com', NULL, NULL, '$2y$12$DKfqc2jM8VKa/VVWYe9W8u5F6eCucK72oJQzlNRwsfKTilP5k.Zwu', NULL, 0, NULL, '2026-02-26 20:00:47', '2026-02-26 20:00:47', NULL),
(11, 'Ramon Cruz', 'ramon.cruz@example.com', NULL, NULL, '$2y$12$Z/JR34gULgagro.bL8qh4uIBayfAMuzSJ21sv4n8jZsmJNzT6.yni', NULL, 0, NULL, '2026-02-26 20:00:50', '2026-02-26 20:00:50', NULL),
(12, 'Mariano Reyes', 'mariano.reyes@example.com', NULL, NULL, '$2y$12$olDpARlPDthR4rliRdLQrOF3HWJ/XOUnI58l4e6ybmnnFI0MT0UcW', NULL, 0, NULL, '2026-02-26 20:00:53', '2026-02-26 20:00:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_primary_role` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `department_id`, `is_primary_role`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 1, 1, '2026-02-26 20:00:19', '2026-02-26 20:00:19'),
(2, 3, 3, 2, 1, '2026-02-26 20:00:22', '2026-02-26 20:00:22'),
(4, 5, 5, 4, 1, '2026-02-26 20:00:29', '2026-02-26 20:00:29'),
(5, 6, 6, 5, 1, '2026-02-26 20:00:33', '2026-02-26 20:00:33'),
(7, 8, 8, 7, 1, '2026-02-26 20:00:40', '2026-02-26 20:00:40'),
(8, 9, 9, 8, 1, '2026-02-26 20:00:43', '2026-02-26 20:00:43'),
(9, 11, 10, 9, 1, '2026-02-26 20:00:50', '2026-02-26 20:00:50'),
(10, 12, 11, 10, 1, '2026-02-26 20:00:53', '2026-02-26 20:00:53'),
(11, 1, 1, 11, 1, '2026-02-26 20:46:55', '2026-02-26 20:46:55'),
(12, 4, 4, 3, 1, '2026-02-26 20:55:55', '2026-02-26 20:55:55'),
(13, 4, 12, 3, 0, '2026-02-26 20:55:55', '2026-02-26 20:55:55'),
(14, 4, 2, 3, 0, '2026-02-26 20:55:55', '2026-02-26 20:55:55'),
(15, 7, 7, 6, 1, '2026-02-26 22:37:33', '2026-02-26 22:37:33'),
(16, 7, 2, 6, 0, '2026-02-26 22:37:33', '2026-02-26 22:37:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics_ticket_snapshot`
--
ALTER TABLE `analytics_ticket_snapshot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `analytics_ticket_snapshot_assigned_agent_id_foreign` (`assigned_agent_id`),
  ADD KEY `analytics_ticket_snapshot_snapshot_date_status_index` (`snapshot_date`,`status`),
  ADD KEY `analytics_ticket_snapshot_ticket_id_index` (`ticket_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcements_title_unique` (`title`),
  ADD KEY `announcements_role_id_index` (`role_id`),
  ADD KEY `announcements_created_by_index` (`created_by`);

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_role_id_name_unique` (`role_id`,`name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_name_unique` (`name`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documents_file_name_created_by_unique` (`file_name`,`created_by`),
  ADD KEY `documents_file_name_index` (`file_name`),
  ADD KEY `documents_role_id_index` (`role_id`),
  ADD KEY `documents_created_by_index` (`created_by`);

--
-- Indexes for table `document_changes`
--
ALTER TABLE `document_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_changes_file_name_index` (`file_name`),
  ADD KEY `document_changes_training_required_index` (`training_required`),
  ADD KEY `document_changes_change_timestamp_index` (`change_timestamp`),
  ADD KEY `document_changes_model_name_index` (`model_name`);

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
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otps_email_expires_at_index` (`email`,`expires_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `pinned_announcements`
--
ALTER TABLE `pinned_announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pinned_announcements_announcement_id_unique` (`announcement_id`);

--
-- Indexes for table `push_notifications`
--
ALTER TABLE `push_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_notification_msgs`
--
ALTER TABLE `push_notification_msgs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rasa_models`
--
ALTER TABLE `rasa_models`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rasa_models_model_name_unique` (`model_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `roles_department_id_foreign` (`department_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staged_faqs`
--
ALTER TABLE `staged_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staged_faqs_semantic_key_index` (`semantic_key`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_staff_id_foreign` (`staff_id`),
  ADD KEY `tickets_category_id_foreign` (`category_id`),
  ADD KEY `tickets_role_id_foreign` (`role_id`);

--
-- Indexes for table `ticket_routing_histories`
--
ALTER TABLE `ticket_routing_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_routing_histories_ticket_id_foreign` (`ticket_id`),
  ADD KEY `ticket_routing_histories_staff_id_foreign` (`staff_id`);

--
-- Indexes for table `upload_logs`
--
ALTER TABLE `upload_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `upload_logs_staff_id_foreign` (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_verification_token_unique` (`verification_token`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`),
  ADD KEY `user_roles_department_id_foreign` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_ticket_snapshot`
--
ALTER TABLE `analytics_ticket_snapshot`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_changes`
--
ALTER TABLE `document_changes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pinned_announcements`
--
ALTER TABLE `pinned_announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `push_notifications`
--
ALTER TABLE `push_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `push_notification_msgs`
--
ALTER TABLE `push_notification_msgs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rasa_models`
--
ALTER TABLE `rasa_models`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staged_faqs`
--
ALTER TABLE `staged_faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ticket_routing_histories`
--
ALTER TABLE `ticket_routing_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `upload_logs`
--
ALTER TABLE `upload_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analytics_ticket_snapshot`
--
ALTER TABLE `analytics_ticket_snapshot`
  ADD CONSTRAINT `analytics_ticket_snapshot_assigned_agent_id_foreign` FOREIGN KEY (`assigned_agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `analytics_ticket_snapshot_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tickets_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ticket_routing_histories`
--
ALTER TABLE `ticket_routing_histories`
  ADD CONSTRAINT `ticket_routing_histories_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ticket_routing_histories_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `upload_logs`
--
ALTER TABLE `upload_logs`
  ADD CONSTRAINT `upload_logs_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
