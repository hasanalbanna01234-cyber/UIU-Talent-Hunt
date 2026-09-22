-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2026 at 02:38 PM
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
-- Database: `uiu_talent_hunt`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','deleted') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `post_id`, `user_id`, `comment_text`, `created_at`, `status`) VALUES
(1, 9, 1, 'Nice', '2026-09-13 22:04:48', 'active'),
(2, 13, 5, 'Awesome', '2026-09-13 22:25:57', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `competition_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `max_participants` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('upcoming','active','completed','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`competition_id`, `title`, `description`, `event_date`, `event_time`, `venue`, `registration_deadline`, `max_participants`, `status`, `created_at`) VALUES
(1, 'UIU Video Talent Challenge', 'Show your creativity through video and compete with talented students from UIU.', '2026-09-30', '10:00:00', 'UIU Auditorium', '2026-09-25', NULL, 'active', '2026-09-13 00:24:55'),
(2, 'UIU Audio Talent Challenge', 'Showcase your voice, music, or other audio talents and get recognized.', '2026-10-05', '14:00:00', 'UIU Music Room', '2026-09-28', NULL, 'active', '2026-09-13 00:24:55'),
(3, 'UIU Photography Challenge', 'Capture your best moments and share your photography skills with UIU.', '2026-10-15', '09:00:00', 'UIU Campus Grounds', '2026-10-10', NULL, 'upcoming', '2026-09-13 00:24:55'),
(4, 'The Wordsmith Challenge', 'Turn your thoughts into powerful words and let your imagination speak', '2026-10-01', '10:00:00', 'UIU Auditorium', '2026-09-25', NULL, 'active', '2026-09-13 21:25:57'),
(5, 'Story Sparks', 'Write a captivating story inspired by your imagination and creativity.', '2026-10-03', '14:00:00', 'UIU Multipurpose Hall', '2026-09-28', NULL, 'active', '2026-09-13 21:25:57'),
(6, 'Dance Beyond Limits', 'Move with confidence, express yourself, and let your passion take the stage.', '2026-10-20', '09:00:00', 'UIU Campus Grounds', '2026-10-15', NULL, 'upcoming', '2026-09-13 21:25:57');

-- --------------------------------------------------------

--
-- Table structure for table `competition_registrations`
--

CREATE TABLE `competition_registrations` (
  `registration_id` int(10) UNSIGNED NOT NULL,
  `competition_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('registered','cancelled') NOT NULL DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `competition_registrations`
--

INSERT INTO `competition_registrations` (`registration_id`, `competition_id`, `user_id`, `registered_at`, `status`) VALUES
(2, 1, 3, '2026-09-13 22:02:30', 'cancelled'),
(3, 4, 1, '2026-09-13 22:05:11', 'registered'),
(4, 5, 1, '2026-09-13 22:05:13', 'registered'),
(5, 1, 4, '2026-09-13 22:25:11', 'registered'),
(6, 4, 4, '2026-09-13 22:25:12', 'registered'),
(7, 4, 3, '2026-09-13 23:13:24', 'registered'),
(8, 1, 8, '2026-09-16 11:01:15', 'registered'),
(9, 5, 3, '2026-09-16 11:45:59', 'registered');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(10) UNSIGNED NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `department_code`, `created_at`) VALUES
(1, 'Computer Science and Engineering', 'CSE', '2026-09-11 18:09:57'),
(2, 'Electrical and Electronic Engineering', 'EEE', '2026-09-11 18:09:57'),
(3, 'Civil Engineering', 'CE', '2026-09-11 18:09:57'),
(4, 'English', 'ENGLISH', '2026-09-11 18:09:57'),
(5, 'Business Administration', 'BBA', '2026-09-11 18:09:57'),
(6, 'Media Studies and Journalism', 'MEDIA', '2026-09-11 18:09:57'),
(7, 'Pharmacy', 'PHARMACY', '2026-09-11 18:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`like_id`, `post_id`, `user_id`, `created_at`) VALUES
(7, 9, 1, '2026-09-13 22:04:40'),
(8, 12, 4, '2026-09-13 22:25:00'),
(9, 11, 4, '2026-09-13 22:25:02'),
(10, 9, 4, '2026-09-13 22:25:04'),
(11, 13, 5, '2026-09-13 22:25:35'),
(12, 12, 5, '2026-09-13 22:25:40'),
(13, 11, 5, '2026-09-13 22:25:41'),
(14, 9, 5, '2026-09-13 22:25:43'),
(17, 14, 2, '2026-09-14 00:56:58'),
(18, 13, 2, '2026-09-14 00:57:00'),
(19, 10, 2, '2026-09-14 00:57:06'),
(20, 11, 2, '2026-09-14 00:57:08'),
(21, 9, 2, '2026-09-14 00:57:10'),
(22, 19, 3, '2026-09-14 11:32:28'),
(23, 18, 3, '2026-09-14 11:32:35'),
(24, 17, 3, '2026-09-14 11:32:39'),
(25, 16, 3, '2026-09-14 11:32:43'),
(26, 15, 3, '2026-09-14 11:32:44'),
(27, 14, 3, '2026-09-14 11:32:49'),
(28, 12, 3, '2026-09-14 11:33:04'),
(29, 13, 3, '2026-09-14 11:33:05'),
(30, 11, 3, '2026-09-14 11:33:12'),
(31, 19, 6, '2026-09-16 09:58:24'),
(32, 18, 6, '2026-09-16 09:58:27'),
(33, 16, 6, '2026-09-16 09:58:29'),
(34, 17, 6, '2026-09-16 09:58:30'),
(35, 15, 6, '2026-09-16 09:58:32'),
(36, 14, 6, '2026-09-16 09:58:34'),
(37, 13, 6, '2026-09-16 09:58:37'),
(38, 12, 6, '2026-09-16 09:58:38'),
(39, 10, 6, '2026-09-16 09:58:40'),
(40, 11, 6, '2026-09-16 09:58:41'),
(41, 9, 6, '2026-09-16 09:58:43');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `competition_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('competition','event') NOT NULL DEFAULT 'competition',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `competition_id`, `title`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(20, 1, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 0, '2026-09-13 21:56:15'),
(21, 1, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 0, '2026-09-13 21:56:15'),
(22, 1, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 0, '2026-09-13 21:56:15'),
(23, 1, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 0, '2026-09-13 21:56:15'),
(24, 1, 5, 'Story Sparks', 'Registration is now open.', 'competition', 0, '2026-09-13 21:56:15'),
(25, 1, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 0, '2026-09-13 21:56:15'),
(27, 2, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:03'),
(28, 2, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:03'),
(29, 2, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-13 21:57:03'),
(30, 2, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:03'),
(31, 2, 5, 'Story Sparks', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:03'),
(32, 2, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-13 21:57:03'),
(34, 3, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:53'),
(35, 3, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:53'),
(36, 3, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-13 21:57:53'),
(37, 3, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:53'),
(38, 3, 5, 'Story Sparks', 'Registration is now open.', 'competition', 1, '2026-09-13 21:57:53'),
(39, 3, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-13 21:57:53'),
(41, 4, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 0, '2026-09-13 21:58:40'),
(42, 4, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 0, '2026-09-13 21:58:40'),
(43, 4, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 0, '2026-09-13 21:58:40'),
(44, 4, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 0, '2026-09-13 21:58:40'),
(45, 4, 5, 'Story Sparks', 'Registration is now open.', 'competition', 0, '2026-09-13 21:58:40'),
(46, 4, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 0, '2026-09-13 21:58:40'),
(48, 5, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:59:19'),
(49, 5, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:59:19'),
(50, 5, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-13 21:59:19'),
(51, 5, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 1, '2026-09-13 21:59:19'),
(52, 5, 5, 'Story Sparks', 'Registration is now open.', 'competition', 1, '2026-09-13 21:59:19'),
(53, 5, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-13 21:59:19'),
(54, 6, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-14 11:37:02'),
(55, 6, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-14 11:37:02'),
(56, 6, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-14 11:37:02'),
(57, 6, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 1, '2026-09-14 11:37:02'),
(58, 6, 5, 'Story Sparks', 'Registration is now open.', 'competition', 1, '2026-09-14 11:37:02'),
(59, 6, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-14 11:37:02'),
(60, 7, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 0, '2026-09-16 10:48:10'),
(61, 7, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 0, '2026-09-16 10:48:10'),
(62, 7, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 0, '2026-09-16 10:48:10'),
(63, 7, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 0, '2026-09-16 10:48:10'),
(64, 7, 5, 'Story Sparks', 'Registration is now open.', 'competition', 0, '2026-09-16 10:48:10'),
(65, 7, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 0, '2026-09-16 10:48:10'),
(67, 8, 1, 'UIU Video Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-16 10:58:39'),
(68, 8, 2, 'UIU Audio Talent Challenge', 'Registration is now open.', 'competition', 1, '2026-09-16 10:58:39'),
(69, 8, 3, 'UIU Photography Challenge', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-16 10:58:39'),
(70, 8, 4, 'The Wordsmith Challenge', 'Registration is now open.', 'competition', 1, '2026-09-16 10:58:39'),
(71, 8, 5, 'Story Sparks', 'Registration is now open.', 'competition', 1, '2026-09-16 10:58:39'),
(72, 8, 6, 'Dance Beyond Limits', 'New event is coming up. Check it out!', 'competition', 1, '2026-09-16 10:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `talent_type` enum('video','audio','photography','blog') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `status` enum('draft','published','deleted') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `title`, `description`, `content`, `talent_type`, `category`, `created_at`, `updated_at`, `status`) VALUES
(9, 3, 'A Taste of Happiness', 'Good food, good mood, happy heart. 🍽️✨', NULL, 'video', 'Others', '2026-09-13 22:01:57', NULL, 'published'),
(10, 3, 'Wild Life', NULL, NULL, 'photography', 'Wildlife', '2026-09-13 22:03:14', '2026-09-13 23:11:09', 'published'),
(11, 1, 'The Art of the Last-Minute Trip', NULL, 'The train platform was freezing, and the departures board was flickering yellow in the late evening light. Julian had thirty seconds to decide: take the standard local commuter line home, or hop on the express train heading toward the coast.\r\n\r\nHe didn\'t have a hotel reservation. He had a backpack with a laptop, a half-charged phone, and a lightweight jacket.\r\n\r\nAs the doors of the coast-bound express hissed open, he stepped aboard. The carriage was nearly empty, filled only with the low hum of the electric motor and the rhythmic clack of the rails.\r\n\r\nTwo hours later, he stepped off into a quiet seaside town bathed in moonlight. The air smelled of salt and rain. He found a small late-night diner near the harbor, ordered a hot coffee, and opened his map. Taking an unexpected turn didn\'t resolve all his weekday stress, but as he watched the waves roll into the dark dock, it made the rest of the world feel refreshingly small.', 'blog', 'Story', '2026-09-13 22:06:53', NULL, 'published'),
(12, 2, 'Small Try', NULL, NULL, 'audio', 'Singing', '2026-09-13 22:22:24', NULL, 'published'),
(13, 4, 'Tune Into the Moment', 'Press play and let the sound take you somewhere special.', NULL, 'audio', 'Singing', '2026-09-13 22:24:45', NULL, 'published'),
(14, 5, 'Colourful Dreams', 'Some moments means a lot....', NULL, 'photography', 'Nature', '2026-09-13 22:28:29', NULL, 'published'),
(15, 2, 'Voices & Vibes', 'A little sound, a little emotion, a lot of memories.', NULL, 'audio', 'Singing', '2026-09-14 01:03:58', NULL, 'published'),
(16, 2, 'Night Market', NULL, NULL, 'video', 'Others', '2026-09-14 01:11:17', NULL, 'published'),
(17, 1, 'Chasing Sunsets', 'Golden skies and peaceful moments.', NULL, 'photography', 'Nature', '2026-09-14 01:16:38', NULL, 'published'),
(18, 1, 'Bloom & Glow', 'Bloom beautifully, just the way you are. 🌸', NULL, 'photography', 'Nature', '2026-09-14 01:17:46', NULL, 'published'),
(19, 1, 'Shadows and Signals', NULL, 'The server room was silent except for the low, hypnotic hum of cooling fans. Maya pulled up the diagnostic panel on her laptop screen, staring at the raw thermal map of the cloud cluster.\r\nOver the past three weeks, her task was to test an energy-aware algorithm designed to throttle idle CPU cores during off-peak hours. On paper, reducing standby power sounded easy. In practice, missing a sudden spike in network traffic by even a few milliseconds meant dropped requests and angry alerts.\r\nShe initiated the test sequence. Across the rack, the status lights shifted from bright blue to a subdued dim green as the workload dynamically consolidated onto four primary servers. Power draw plummeted by 35% almost instantly.\r\nJust then, an unexpected burst of data hit the system. Maya held her breath, waiting for the cluster to crash or lag. Instead, the dynamic allocation kicked in seamlessly, waking up secondary nodes just fast enough to absorb the load without missing a beat. Maya leaned back in her chair and smiled—the green architecture wasn\'t just theoretical anymore; it actually worked.', 'blog', 'Story', '2026-09-14 01:19:19', NULL, 'published'),
(20, 6, 'A little try', NULL, NULL, 'audio', 'Singing', '2026-09-16 09:49:34', '2026-09-16 11:46:45', 'published'),
(21, 6, 'The Last Light', NULL, 'Every evening, Arin sat by the old window and watched the sunset. It was a small habit, but it made the noisy world feel quiet for a while.\r\nOne evening, the power suddenly went out across the neighborhood. The streets became dark, and people stepped outside with candles and phone flashlights.\r\nArin noticed an elderly neighbor struggling to find her way home. Without thinking twice, he took his small lantern and walked toward her.\r\n“Thank you,” she smiled. “Sometimes, a little light is all someone needs.”\r\nArin looked at his tiny lantern. It wasn\'t bright enough to light the whole street, but it was enough to guide one person.\r\nThat night, he understood something simple: you don\'t need to change the whole world to make a difference. Sometimes, helping one person is enough.', 'blog', 'Story', '2026-09-16 09:53:41', NULL, 'published'),
(22, 3, 'By discovering nature discover yourself', NULL, NULL, 'photography', 'Nature', '2026-09-16 11:44:19', NULL, 'published');

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `media_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `media_type` enum('image','video','audio') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_media`
--

INSERT INTO `post_media` (`media_id`, `post_id`, `media_type`, `file_path`, `file_name`, `mime_type`, `file_size`, `created_at`) VALUES
(9, 9, 'video', 'uploads/videos/videos_6aa6c8f5004910.73841401.mp4', 'Fruit-salad.mp4', 'video/mp4', 3606096, '2026-09-13 22:01:57'),
(10, 10, 'image', 'uploads/photos/photos_6aa6c942356c44.47281472.jpg', 'Animals.jpg', 'image/jpeg', 3296593, '2026-09-13 22:03:14'),
(11, 11, 'image', 'uploads/photos/photos_6aa6ca1d8b7c39.53734556.jpg', 'fancycrave1-train-821500.jpg', 'image/jpeg', 3326974, '2026-09-13 22:06:53'),
(12, 12, 'audio', 'uploads/audio/audio_6aa6cdc02fd3d5.70183563.mp3', 'Teri Nazron Ke Sadke.mp3', 'audio/mpeg', 1574523, '2026-09-13 22:22:24'),
(13, 12, 'image', 'uploads/thumbnails/thumbnails_6aa6cdc0303d46.59629906.jpg', 'post8.jpg', 'image/jpeg', 212997, '2026-09-13 22:22:24'),
(14, 13, 'audio', 'uploads/audio/audio_6aa6ce4de84778.51407485.mp3', 'rata lambiya kaliya ne sami (slowed x reverb)  sammi meri waar full cover viral song 2026 ShivamYt.mp3', 'audio/mpeg', 1224040, '2026-09-13 22:24:45'),
(15, 13, 'image', 'uploads/thumbnails/thumbnails_6aa6ce4de8ef98.26081179.jpg', 'heckimg-tree-7619791.jpg', 'image/jpeg', 3965999, '2026-09-13 22:24:45'),
(16, 14, 'image', 'uploads/photos/photos_6aa6cf2d50ad95.12313210.jpg', 'colourful dreams.jpg', 'image/jpeg', 96154, '2026-09-13 22:28:29'),
(17, 15, 'audio', 'uploads/audio/audio_6aa6f39eef8cd0.58513034.mp3', 'Tumi Bristi Cheyecho Bole _ ( তুমি বৃষ্টি চেয়েছো বলে ) _ Mahtim sakib _ New Lyrical Song 2024.mp3', 'audio/mpeg', 9246439, '2026-09-14 01:03:58'),
(18, 15, 'image', 'uploads/thumbnails/thumbnails_6aa6f39ef08d75.38819623.jpg', 'k_michels-malaysia-10445550.jpg', 'image/jpeg', 1692807, '2026-09-14 01:03:58'),
(19, 16, 'video', 'uploads/videos/videos_6aa6f5555b8692.57950462.mp4', 'Roadside-market.mp4', 'video/mp4', 48173785, '2026-09-14 01:11:17'),
(20, 17, 'image', 'uploads/photos/photos_6aa6f695f337c2.40108258.jpg', 'giani-mountains-100367.jpg', 'image/jpeg', 1263000, '2026-09-14 01:16:38'),
(21, 18, 'image', 'uploads/photos/photos_6aa6f6da3d0621.92636972.jpg', 'kriemer-verbena-7504222.jpg', 'image/jpeg', 1529962, '2026-09-14 01:17:46'),
(22, 19, 'image', 'uploads/photos/photos_6aa6f737137714.04317308.jpg', 'kundennote_com-candle-335965.jpg', 'image/jpeg', 1410527, '2026-09-14 01:19:19'),
(23, 20, 'audio', 'uploads/audio/audio_6aaa11ce9ecdb6.55349241.mp3', 'videoplayback.mp3', 'video/mp4', 4059003, '2026-09-16 09:49:34'),
(24, 20, 'image', 'uploads/thumbnails/thumbnails_6aaa11ce9ff082.78565271.jpg', 'photography_by_sebbi-prayer-book-7842864.jpg', 'image/jpeg', 4569619, '2026-09-16 09:49:34'),
(25, 21, 'image', 'uploads/photos/photos_6aaa12c5805037.41014810.jpg', 'ri_ya-open-book-7637805.jpg', 'image/jpeg', 3610718, '2026-09-16 09:53:41'),
(26, 22, 'image', 'uploads/photos/photos_6aaa2cb3960319.58887587.jpg', 'Tree.jpg', 'image/jpeg', 1779824, '2026-09-16 11:44:19');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('pending','reviewed','resolved') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `post_id`, `user_id`, `reason`, `status`, `created_at`) VALUES
(1, 21, 3, 'Spam', 'pending', '2026-09-20 14:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE `shares` (
  `share_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shares`
--

INSERT INTO `shares` (`share_id`, `post_id`, `user_id`, `created_at`) VALUES
(4, 9, 1, '2026-09-13 22:04:54'),
(5, 13, 5, '2026-09-13 22:25:38'),
(6, 12, 5, '2026-09-13 22:26:06'),
(7, 11, 3, '2026-09-13 23:12:02'),
(8, 14, 3, '2026-09-13 23:23:01'),
(9, 19, 3, '2026-09-14 11:32:32'),
(10, 22, 6, '2026-09-16 11:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `student_id` varchar(30) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `batch` varchar(30) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `student_id`, `full_name`, `email`, `department_id`, `batch`, `bio`, `password_hash`, `profile_image`, `cover_image`, `created_at`, `status`) VALUES
(1, '0112230522', 'Jannatul  Nur', 'jannatulnur@bscse.uiu.ac.bd', 1, NULL, NULL, '$2y$10$7hEu.FTXtO6jRP9undU4rOm47VhlU2.xQKFUj4xVqPTpNgoY2dEea', 'profile_6aa6cca635cfd3.53009259.jpg', NULL, '2026-09-13 21:56:15', 'active'),
(2, '0112230458', 'Rakib Hasan', 'rakib@bscse.uiu.ac.bd', 2, NULL, NULL, '$2y$10$mb5fsn5btdYkk4QsI082Q.98b4qcge6cJ49H5m5A4.DXtFVSkrJ3K', 'profile_6aa6cd4eaee6d8.84666603.jpg', 'profile_6aa6cd4eaf2301.77680526.jpg', '2026-09-13 21:57:03', 'active'),
(3, '0112230999', 'Mitali Rahman', 'mitali@bscse.uiu.ac.bd', 2, NULL, NULL, '$2y$10$xKFRMzdRkB6yZ.B3KMvUx.itF3R9KS18RnJlhRYb0bP2t.qsRDdOG', 'profile_6aa6cd218061c8.29446036.jpg', 'profile_6aa6cd21814f25.16743278.jpg', '2026-09-13 21:57:53', 'active'),
(4, '0112211078', 'Shefali Ibnat', 'ibnatshef@bscse.uiu.ac.bd', 4, NULL, NULL, '$2y$10$dtwkt99NckwC7AIUryHEKuTgyoch6Cx6OCJYt0JroyxzNHW9CzUCe', 'profile_6aa6cdfb38a967.36139258.jpg', 'profile_6aa6cdfb392676.29976320.jpg', '2026-09-13 21:58:40', 'active'),
(5, '0112220899', 'Brishti Hossain', 'hossain@bscse.uiu.ac.bd', 5, NULL, NULL, '$2y$10$mXM/0C/Bc2dRetu3ezPWsudDHci9F5QW6dJLmGR9D94tReGh2kmTu', 'profile_6aa6ced9a8ea20.82987209.jpg', 'profile_6aa6ced9a92a73.38517308.jpg', '2026-09-13 21:59:19', 'active'),
(6, '0112230758', 'Nazifa Tabassum', 'nazifa@bscse.uiu.ac.bd', 1, NULL, NULL, '$2y$10$fpMBHSiYQPC95uhhyzLjm.0tJP6rJGwQa0FEHx.R0e7YgaaY5aC9m', 'profile_6aa788342cabc7.91445541.jpeg', 'profile_6aa788342dee41.74394204.jpg', '2026-09-14 11:37:02', 'active'),
(7, '0112230794', 'Sumaita Binte Hafiz', 'shafiz@bscse.uiu.ac.bd', 1, NULL, NULL, '$2y$10$tslfiLFQYMCKkBmVyj.MIeD8RQ4fxyEwnxlrHn0y5IlzLGAE/mVHS', NULL, NULL, '2026-09-16 10:48:10', 'active'),
(8, '0112230386', 'Afia Anjum', 'aanjum223386@bscse.uiu.ac.bd', 1, NULL, NULL, '$2y$10$7ukaZE.REydWAk0pejNQC.gL1Aq8rw0XyqUEXqpdfSaqfZrRYqXGK', NULL, NULL, '2026-09-16 10:58:39', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_comments_post` (`post_id`),
  ADD KEY `idx_comments_user` (`user_id`),
  ADD KEY `idx_comments_created` (`created_at`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`competition_id`),
  ADD KEY `idx_competitions_date` (`event_date`),
  ADD KEY `idx_competitions_status` (`status`);

--
-- Indexes for table `competition_registrations`
--
ALTER TABLE `competition_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `uq_competition_user` (`competition_id`,`user_id`),
  ADD KEY `idx_registration_competition` (`competition_id`),
  ADD KEY `idx_registration_user` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `uq_department_name` (`department_name`),
  ADD UNIQUE KEY `uq_department_code` (`department_code`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `uq_likes_post_user` (`post_id`,`user_id`),
  ADD KEY `idx_likes_post` (`post_id`),
  ADD KEY `idx_likes_user` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_competition` (`competition_id`),
  ADD KEY `idx_notifications_created` (`created_at`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `idx_posts_user` (`user_id`),
  ADD KEY `idx_posts_created` (`created_at`),
  ADD KEY `idx_posts_status` (`status`),
  ADD KEY `idx_posts_talent_type` (`talent_type`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `idx_post_media_post` (`post_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `uq_reports_post_user` (`post_id`,`user_id`),
  ADD KEY `idx_reports_post` (`post_id`),
  ADD KEY `idx_reports_user` (`user_id`),
  ADD KEY `idx_reports_status` (`status`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`share_id`),
  ADD UNIQUE KEY `uq_shares_post_user` (`post_id`,`user_id`),
  ADD KEY `idx_shares_post` (`post_id`),
  ADD KEY `idx_shares_user` (`user_id`),
  ADD KEY `idx_shares_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_student_id` (`student_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_department` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `competition_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `competition_registrations`
--
ALTER TABLE `competition_registrations`
  MODIFY `registration_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `like_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `media_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shares`
--
ALTER TABLE `shares`
  MODIFY `share_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `competition_registrations`
--
ALTER TABLE `competition_registrations`
  ADD CONSTRAINT `fk_registration_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`competition_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_registration_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`competition_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `post_media`
--
ALTER TABLE `post_media`
  ADD CONSTRAINT `fk_post_media_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reports_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shares`
--
ALTER TABLE `shares`
  ADD CONSTRAINT `fk_shares_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_shares_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
