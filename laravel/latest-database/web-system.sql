-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2026 at 01:55 PM
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
-- Database: `web-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activity_name` varchar(255) DEFAULT NULL,
  `activity_type` varchar(255) DEFAULT NULL,
  `short_description` longtext DEFAULT NULL,
  `main_description` longtext DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `projectID` varchar(255) DEFAULT NULL,
  `clientID` varchar(255) DEFAULT NULL,
  `assign_by` varchar(255) DEFAULT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `comments` longtext DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `sub_status` varchar(255) DEFAULT NULL,
  `request_accept_extension` varchar(255) DEFAULT NULL,
  `complete_activity` varchar(255) DEFAULT NULL,
  `discard_activity` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `activity_name`, `activity_type`, `short_description`, `main_description`, `created_by`, `projectID`, `clientID`, `assign_by`, `due_date`, `comments`, `status`, `sub_status`, `request_accept_extension`, `complete_activity`, `discard_activity`, `created_at`, `updated_at`) VALUES
(14, 'Test Activity 1', 'Testing', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '<p><strong style=\"color: rgb(0, 0, 0);\">Lorem Ipsum</strong><span style=\"color: rgb(0, 0, 0);\">&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</span></p>', '1', NULL, '3', '3', '2025-10-21 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '5', NULL, NULL, NULL, '2025-09-05 08:12:21', '2025-10-14 02:36:11'),
(15, 'Test Activity 2', 'Testing', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>', '1', '1', '1', '4,5', '2025-09-29 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '5', NULL, NULL, NULL, '2025-09-05 08:14:06', '2026-01-13 08:16:45'),
(16, 'Test Activity 3', 'Testing', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '1', '1', '4,5', '2025-10-14 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '2', '5', NULL, NULL, NULL, '2025-09-29 07:34:48', '2025-09-29 07:34:48'),
(17, 'Test Activity 4', 'Testing', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.', '1', NULL, NULL, NULL, '2025-10-21 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '1', '5', NULL, NULL, NULL, '2025-10-06 07:16:46', '2025-10-06 07:16:46'),
(18, 'Test Activity 14-10', 'Testing', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '1', '2', '4', '2025-10-28 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '5', NULL, NULL, NULL, '2025-10-14 01:46:17', '2025-10-14 01:46:17'),
(20, 'Test Activity dfgdvxcvcxv', 'Testing', NULL, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '1', '2', '3', '5', '2026-01-30 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '2', '5', NULL, NULL, NULL, '2026-01-27 06:14:33', '2026-01-27 06:14:33'),
(21, 'Test Activity AABB', 'Testing', NULL, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '1', NULL, '3', '4', '2026-01-30 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '2', '5', NULL, NULL, NULL, '2026-01-27 06:30:02', '2026-01-27 06:30:02'),
(23, 'Lorem Ipsume 28-Jan', 'What is Lorem Ipsum?', NULL, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '1', '5', '3', '5', '2026-01-30 18:30:00', 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '2', '3', NULL, NULL, NULL, '2026-01-28 05:58:33', '2026-01-28 07:44:16'),
(24, 'Where does it come from?', 'Lorem Ipsum', NULL, 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.\r\n\r\nThe standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.', '1', '5', '3', '4', '2026-01-30 18:30:00', 'This book is a treatise on the theory of ethics, very popular during the Renaissance.', '4', '1', NULL, NULL, NULL, '2026-01-28 07:12:36', '2026-01-28 07:29:55'),
(25, 'Test Activity From Project', 'Lorem Ipsum', NULL, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nLorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', '1', '5', '3', '5', '2026-01-30 18:30:00', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.', '1', '5', NULL, NULL, NULL, '2026-01-28 07:59:56', '2026-01-28 07:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `activities_attachment`
--

CREATE TABLE `activities_attachment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activityID` bigint(20) UNSIGNED NOT NULL,
  `attachment_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities_attachment`
--

INSERT INTO `activities_attachment` (`id`, `activityID`, `attachment_file`, `created_at`, `updated_at`) VALUES
(21, 14, '1757079739-68bae8bb5ccd8.jpg', '2025-09-05 08:12:21', '2025-09-05 08:12:21'),
(22, 15, '1757079844-68bae9242e72d.jpg', '2025-09-05 08:14:06', '2025-09-05 08:14:06'),
(23, 15, '1757079844-68bae9244be4d.jpg', '2025-09-05 08:14:06', '2025-09-05 08:14:06'),
(24, 17, '1759754742-68e3b9f6a494c.jpg', '2025-10-06 07:16:46', '2025-10-06 07:16:46'),
(25, 18, '1760426173-68edf8bdae811.jpg', '2025-10-14 01:46:17', '2025-10-14 01:46:17'),
(26, 18, '1760426173-68edf8bdae75e.jpg', '2025-10-14 01:46:17', '2025-10-14 01:46:17'),
(27, 20, '1769514267-6978a51b7093e.jpg', '2026-01-27 06:14:33', '2026-01-27 06:14:33'),
(28, 21, '1769515201-6978a8c1624d5.jpg', '2026-01-27 06:30:02', '2026-01-27 06:30:02'),
(30, 23, '1769599708-6979f2dc55afc.jpg', '2026-01-28 05:58:33', '2026-01-28 05:58:33'),
(31, 24, '1769604152-697a043802ea9.jpg', '2026-01-28 07:12:36', '2026-01-28 07:12:36'),
(32, 24, '1769604152-697a04381f58e.jpg', '2026-01-28 07:12:36', '2026-01-28 07:12:36'),
(33, 24, '1769604152-697a043872543.jpg', '2026-01-28 07:12:36', '2026-01-28 07:12:36'),
(34, 25, '1769606994-697a0f52b216f.jpg', '2026-01-28 07:59:56', '2026-01-28 07:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `activity_comment`
--

CREATE TABLE `activity_comment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activityID` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `act_comment` longtext NOT NULL,
  `commentTypes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_comment`
--

INSERT INTO `activity_comment` (`id`, `activityID`, `user_id`, `act_comment`, `commentTypes`, `created_at`, `updated_at`) VALUES
(2, 14, 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '3', '2025-09-09 07:56:22', '2025-09-09 07:56:22'),
(4, 14, 8, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Please check attached doc and images', '1', '2025-09-10 05:18:45', '2025-09-10 05:18:45'),
(5, 14, 8, 'unknown printer took a galley of type and scrambled it to make a type specimen book.', '4', '2025-09-10 05:19:42', '2025-09-10 05:19:42'),
(7, 15, 8, 'New Comment for my side', '2', '2025-09-10 08:14:52', '2025-09-10 08:14:52'),
(11, 14, 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '2025-09-29 06:58:11', '2025-09-29 06:58:11'),
(13, 14, 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Please check the attached doc and images', '4', '2025-10-07 01:16:04', '2025-10-07 01:16:04'),
(14, 17, 1, 'Test Comment', '2', '2025-10-14 02:24:31', '2025-10-14 02:24:31'),
(15, 17, 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '3', '2025-10-14 02:24:58', '2025-10-14 02:24:58'),
(16, 15, 1, 'Testing for review', '3', '2025-11-12 07:42:58', '2025-11-12 07:42:58'),
(17, 15, 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '1', '2025-11-19 07:04:07', '2025-11-19 07:04:07'),
(18, 18, 1, 'sdfcxz', '2', '2025-11-25 03:59:25', '2025-11-25 03:59:25'),
(19, 15, 1, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '3', '2026-01-13 08:16:45', '2026-01-13 08:16:45'),
(20, 24, 1, 'I have reviewed the activities', '3', '2026-01-28 07:25:30', '2026-01-28 07:25:30'),
(21, 24, 1, 'This activity is completed.', '1', '2026-01-28 07:29:55', '2026-01-28 07:29:55'),
(23, 23, 1, 'This activity is a review.', '3', '2026-01-28 07:44:16', '2026-01-28 07:44:16');

-- --------------------------------------------------------

--
-- Table structure for table `activity_comment_shared_documnet`
--

CREATE TABLE `activity_comment_shared_documnet` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `activityID` bigint(20) UNSIGNED NOT NULL,
  `activityCommentID` bigint(20) UNSIGNED NOT NULL,
  `attachment_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Error reading data for table web-system.activity_comment_shared_documnet: #2006 - MySQL server has gone away
<div class="alert alert-danger" role="alert"><h1>Error</h1><p><strong>SQL query:</strong>  <a href="#" class="copyQueryBtn" data-text="SET SQL_QUOTE_SHOW_CREATE = 1">Copy</a>
<a href="index.php?route=/database/sql&sql_query=SET+SQL_QUOTE_SHOW_CREATE+%3D+1&show_query=1&db=web-system"><span class="text-nowrap"><img src="themes/dot.gif" title="Edit" alt="Edit" class="icon ic_b_edit">&nbsp;Edit</span></a>    </p>
<p>
<code class="sql"><pre>
SET SQL_QUOTE_SHOW_CREATE = 1
</pre></code>
</p>
<p>
    <strong>MySQL said: </strong><a href="./url.php?url=https%3A%2F%2Fdev.mysql.com%2Fdoc%2Frefman%2F8.0%2Fen%2Fserver-error-reference.html" target="mysql_doc"><img src="themes/dot.gif" title="Documentation" alt="Documentation" class="icon ic_b_help"></a>
</p>
<code>#2006 - MySQL server has gone away</code><br></div>