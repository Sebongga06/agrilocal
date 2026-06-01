-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2026 at 05:42 PM
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
-- Database: `db_agrilocal`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart`
--

CREATE TABLE `tbl_cart` (
  `crt_id` int(11) NOT NULL,
  `crt_user_id` int(11) NOT NULL,
  `crt_dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `crt_dateUpdated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cart`
--

INSERT INTO `tbl_cart` (`crt_id`, `crt_user_id`, `crt_dateCreated`, `crt_dateUpdated`) VALUES
(1, 1, '2026-04-16 11:08:09', NULL),
(2, 4, '2026-04-18 11:35:57', NULL),
(3, 15, '2026-05-04 16:17:06', NULL),
(4, 17, '2026-05-04 16:43:58', NULL),
(5, 18, '2026-05-05 02:16:44', NULL),
(6, 19, '2026-05-09 13:41:01', NULL),
(7, 22, '2026-05-14 15:05:49', NULL),
(8, 23, '2026-05-14 15:11:51', NULL),
(9, 26, '2026-05-24 13:58:14', NULL),
(10, 27, '2026-05-27 16:33:56', NULL),
(11, 29, '2026-05-28 14:13:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cart_item`
--

CREATE TABLE `tbl_cart_item` (
  `cit_id` int(11) NOT NULL,
  `cit_cart_id` int(11) NOT NULL,
  `cit_product_id` int(11) NOT NULL,
  `cit_quantity` decimal(10,2) NOT NULL,
  `cit_unit_price` decimal(10,2) NOT NULL,
  `cit_special_instructions` text DEFAULT NULL,
  `cit_dateAdded` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cart_item`
--

INSERT INTO `tbl_cart_item` (`cit_id`, `cit_cart_id`, `cit_product_id`, `cit_quantity`, `cit_unit_price`, `cit_special_instructions`, `cit_dateAdded`) VALUES
(47, 3, 4, 6.00, 150.00, NULL, '2026-05-04 16:19:21'),
(48, 3, 9, 3.00, 150.00, NULL, '2026-05-04 16:19:27'),
(49, 3, 3, 1.00, 80.00, NULL, '2026-05-04 16:19:32'),
(75, 7, 13, 10.00, 20.00, NULL, '2026-05-14 15:05:49'),
(76, 8, 12, 21.00, 15.00, NULL, '2026-05-14 15:11:51'),
(77, 8, 10, 2.00, 13.00, NULL, '2026-05-14 15:49:45'),
(83, 10, 16, 1.00, 80.00, NULL, '2026-05-27 16:33:56'),
(84, 2, 16, 2.00, 80.00, NULL, '2026-05-27 16:35:04'),
(88, 11, 14, 19.00, 15.00, NULL, '2026-05-28 14:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_category`
--

CREATE TABLE `tbl_category` (
  `cat_id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_category`
--

INSERT INTO `tbl_category` (`cat_id`, `cat_name`) VALUES
(3, 'Dairy'),
(2, 'Fruits'),
(1, 'Vegetables');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_favorites`
--

CREATE TABLE `tbl_favorites` (
  `fav_id` int(11) NOT NULL,
  `fav_user_id` int(11) NOT NULL,
  `fav_vendor_id` int(11) DEFAULT NULL,
  `fav_product_id` int(11) DEFAULT NULL,
  `fav_dateCreated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_favorites`
--

INSERT INTO `tbl_favorites` (`fav_id`, `fav_user_id`, `fav_vendor_id`, `fav_product_id`, `fav_dateCreated`) VALUES
(7, 4, 1, NULL, '2026-04-23 01:03:04'),
(8, 4, NULL, 6, '2026-04-23 01:11:19'),
(12, 15, NULL, 12, '2026-05-04 16:17:10'),
(13, 15, 1, NULL, '2026-05-04 16:17:56'),
(14, 18, NULL, 13, '2026-05-05 02:21:15'),
(15, 23, NULL, 11, '2026-05-14 16:00:06'),
(16, 23, NULL, 7, '2026-05-14 16:00:07'),
(17, 26, NULL, 15, '2026-05-24 14:01:14');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order`
--

CREATE TABLE `tbl_order` (
  `ord_id` int(11) NOT NULL,
  `ord_user_id` int(11) NOT NULL,
  `ord_vendor_id` int(11) NOT NULL,
  `ord_status` enum('pending','confirmed','ready','picked_up','completed','cancelled') DEFAULT 'pending',
  `ord_total_amount` decimal(10,2) NOT NULL,
  `ord_delivery_method` enum('pickup','delivery') NOT NULL,
  `ord_delivery_address` text DEFAULT NULL,
  `ord_pickup_time_slot` timestamp NULL DEFAULT NULL,
  `ord_payment_status` varchar(50) NOT NULL DEFAULT 'unpaid',
  `ord_payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `ord_payment_reference` varchar(100) DEFAULT NULL,
  `ord_payment_proof` varchar(255) DEFAULT NULL,
  `ord_notes` text DEFAULT NULL,
  `ord_dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `ord_dateUpdated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_order`
--

INSERT INTO `tbl_order` (`ord_id`, `ord_user_id`, `ord_vendor_id`, `ord_status`, `ord_total_amount`, `ord_delivery_method`, `ord_delivery_address`, `ord_pickup_time_slot`, `ord_payment_status`, `ord_payment_method`, `ord_payment_reference`, `ord_payment_proof`, `ord_notes`, `ord_dateCreated`, `ord_dateUpdated`) VALUES
(1, 4, 1, 'ready', 210.00, 'pickup', NULL, '2026-04-22 11:51:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-18 11:51:39', '2026-04-22 18:19:47'),
(2, 4, 2, 'pending', 1000.05, 'delivery', 'Singcang', '2026-04-21 17:06:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-19 17:06:48', NULL),
(3, 4, 15, 'ready', 3000.00, 'delivery', 'Singcang', '2026-04-21 17:06:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-19 17:06:48', '2026-05-28 13:26:46'),
(4, 4, 13, 'pending', 500.00, 'delivery', 'Singcang', '2026-04-21 17:06:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-19 17:06:48', NULL),
(5, 4, 1, 'completed', 240.00, 'delivery', 'Singcang', '2026-04-21 17:06:00', 'paid', 'cash', NULL, NULL, NULL, '2026-04-19 17:06:48', '2026-05-04 16:12:23'),
(6, 4, 13, 'cancelled', 50.00, 'delivery', 'Singcang', '2026-04-21 17:08:00', 'refunded', 'cash', NULL, NULL, NULL, '2026-04-19 17:08:52', '2026-05-04 16:12:23'),
(7, 4, 1, 'ready', 150.00, 'delivery', 'Singcang', '2026-04-21 17:08:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-19 17:08:52', '2026-04-22 18:12:48'),
(8, 4, 2, 'completed', 90.00, 'pickup', NULL, '2026-04-21 17:23:00', 'paid', 'cash', NULL, NULL, NULL, '2026-04-19 17:23:57', '2026-05-04 16:12:23'),
(9, 4, 2, 'pending', 200.00, 'pickup', NULL, '2026-04-10 17:37:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-19 17:37:42', NULL),
(10, 4, 13, 'pending', 50.00, 'pickup', NULL, '2026-04-19 17:39:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-19 17:40:04', NULL),
(11, 4, 2, 'pending', 1080.00, 'pickup', NULL, '2026-04-24 02:55:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-22 02:56:29', NULL),
(12, 4, 2, 'cancelled', 360.00, 'pickup', NULL, '2026-04-22 02:57:00', 'refunded', 'cash', NULL, NULL, NULL, '2026-04-22 02:57:58', '2026-05-04 16:12:23'),
(13, 4, 1, 'cancelled', 270.00, 'pickup', NULL, '2026-04-22 16:17:00', 'refunded', 'cash', NULL, NULL, NULL, '2026-04-22 16:17:16', '2026-05-04 16:12:23'),
(14, 4, 1, 'cancelled', 120.00, 'pickup', NULL, '2026-04-22 16:26:00', 'refunded', 'cash', NULL, NULL, NULL, '2026-04-22 16:26:31', '2026-05-04 16:12:23'),
(15, 4, 13, 'pending', 600.00, 'pickup', NULL, '2026-04-22 18:38:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-22 18:38:24', NULL),
(16, 4, 1, 'ready', 750.00, 'pickup', NULL, '2026-04-22 18:38:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-22 18:38:24', '2026-05-04 16:13:16'),
(17, 4, 2, 'pending', 380.00, 'pickup', NULL, '2026-04-23 14:38:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-23 14:39:00', NULL),
(18, 4, 13, 'pending', 100.00, 'pickup', NULL, '2026-04-23 14:38:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-23 14:39:00', NULL),
(19, 4, 18, 'pending', 13.00, 'pickup', NULL, '2026-04-23 14:38:00', 'pending', 'cash', NULL, NULL, NULL, '2026-04-23 14:39:00', NULL),
(20, 17, 1, 'pending', 795.00, 'delivery', 'San Enrique', '2026-05-04 16:45:00', 'pending', 'cash', NULL, NULL, NULL, '2026-05-04 16:45:51', NULL),
(21, 18, 1, 'pending', 600.00, 'delivery', 'Fortune', '2026-05-05 02:17:00', 'pending', 'cash', NULL, NULL, NULL, '2026-05-05 02:17:59', NULL),
(22, 19, 1, 'pending', 300.00, 'pickup', NULL, '2026-05-12 15:02:00', 'pending_verification', 'maya', '12345', 'uploads/payment_proofs/proof_6a009ee65b9b04.92757014.png', NULL, '2026-05-10 15:06:14', NULL),
(23, 19, 1, 'pending', 423.00, 'delivery', 'Bacolod-Negros Occcidental Economic Highway, Paglaum Village, Mansilingan, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', '2026-05-13 15:29:00', 'pending_verification', 'gcash', '12367', 'uploads/payment_proofs/proof_6a049967587c99.46723906.png', NULL, '2026-05-13 15:31:51', NULL),
(24, 19, 19, 'pending', 95.00, 'delivery', 'Liroville, Taculing, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', '2026-05-14 16:18:00', 'unpaid', 'cash', NULL, NULL, NULL, '2026-05-14 16:18:57', NULL),
(25, 19, 19, 'pending', 2628.00, 'delivery', 'Liroville, Taculing, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', '2026-05-14 16:20:00', 'unpaid', 'cash', NULL, NULL, NULL, '2026-05-14 16:20:39', NULL),
(26, 26, 1, 'pending', 173.00, 'delivery', 'Sum-ag - Abuanan Road, Abuanan, Bago, Negros Occidental, Negros Island Region, 6101, Philippines', '2026-05-24 13:58:00', 'unpaid', 'cash', NULL, NULL, NULL, '2026-05-24 13:58:57', NULL),
(27, 26, 20, 'pending', 163.00, 'delivery', 'Sum-ag - Abuanan Road, Abuanan, Bago, Negros Occidental, Negros Island Region, 6101, Philippines', '2026-05-25 14:01:00', 'unpaid', 'cash', NULL, NULL, NULL, '2026-05-24 14:02:48', NULL),
(28, 26, 18, 'pending', 211.00, 'delivery', 'Liroville, Taculing, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', '2026-05-27 15:55:00', 'unpaid', 'cash', NULL, NULL, NULL, '2026-05-27 15:56:03', NULL),
(29, 1, 22, 'completed', 188.00, 'delivery', 'Liroville, Taculing, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', NULL, 'paid', 'cash', NULL, NULL, NULL, '2026-05-28 08:43:44', '2026-05-28 13:58:40'),
(30, 1, 22, 'completed', 248.00, 'delivery', 'Liroville, Taculing, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', '2026-05-29 14:02:00', 'paid', 'gcash', '12345', 'uploads/payment_proofs/proof_6a184b2370b9c8.28371652.png', NULL, '2026-05-28 14:03:15', '2026-05-28 14:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order_item`
--

CREATE TABLE `tbl_order_item` (
  `oit_id` int(11) NOT NULL,
  `oit_order_id` int(11) NOT NULL,
  `oit_product_id` int(11) NOT NULL,
  `oit_quantity` decimal(10,2) NOT NULL,
  `oit_unit_price` decimal(10,2) NOT NULL,
  `oit_subtotal` decimal(12,2) GENERATED ALWAYS AS (`oit_quantity` * `oit_unit_price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_order_item`
--

INSERT INTO `tbl_order_item` (`oit_id`, `oit_order_id`, `oit_product_id`, `oit_quantity`, `oit_unit_price`) VALUES
(2, 1, 2, 1.00, 90.00),
(3, 2, 5, 5.00, 200.01),
(4, 3, 9, 20.00, 150.00),
(5, 4, 8, 10.00, 50.00),
(6, 5, 6, 1.00, 150.00),
(7, 5, 2, 1.00, 90.00),
(8, 6, 8, 1.00, 50.00),
(9, 7, 6, 1.00, 150.00),
(10, 8, 7, 1.00, 90.00),
(11, 9, 5, 1.00, 200.00),
(12, 10, 8, 1.00, 50.00),
(13, 11, 7, 12.00, 90.00),
(14, 12, 7, 4.00, 90.00),
(15, 13, 2, 3.00, 90.00),
(17, 15, 8, 12.00, 50.00),
(18, 16, 4, 5.00, 150.00),
(19, 17, 7, 2.00, 90.00),
(20, 17, 5, 1.00, 200.00),
(21, 18, 8, 2.00, 50.00),
(22, 19, 10, 1.00, 13.00),
(23, 20, 2, 7.00, 90.00),
(24, 20, 4, 1.00, 150.00),
(25, 20, 12, 1.00, 15.00),
(26, 21, 13, 30.00, 20.00),
(27, 22, 13, 15.00, 20.00),
(28, 23, 12, 20.00, 15.00),
(29, 24, 14, 3.00, 15.00),
(30, 25, 14, 4.00, 15.00),
(31, 26, 13, 1.00, 20.00),
(32, 27, 15, 1.00, 10.00),
(33, 28, 10, 1.00, 13.00),
(34, 29, 17, 14.00, 10.00),
(35, 30, 17, 20.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `pay_id` int(11) NOT NULL,
  `pay_order_id` int(11) NOT NULL,
  `pay_amount` decimal(10,2) NOT NULL,
  `pay_method` varchar(50) NOT NULL,
  `pay_reference` varchar(255) DEFAULT NULL,
  `pay_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `pay_dateCreated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product`
--

CREATE TABLE `tbl_product` (
  `prd_id` int(11) NOT NULL,
  `prd_vendor_id` int(11) NOT NULL,
  `prd_category_id` int(11) NOT NULL,
  `prd_name` varchar(150) NOT NULL,
  `prd_description` text DEFAULT NULL,
  `prd_price` decimal(10,2) NOT NULL,
  `prd_unit` varchar(20) NOT NULL,
  `prd_stock_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `prd_is_available` tinyint(1) NOT NULL DEFAULT 1,
  `prd_is_in_season` tinyint(1) NOT NULL DEFAULT 1,
  `prd_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prd_images`)),
  `prd_dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `prd_dateUpdated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `tbl_product`
--

INSERT INTO `tbl_product` (`prd_id`, `prd_vendor_id`, `prd_category_id`, `prd_name`, `prd_description`, `prd_price`, `prd_unit`, `prd_stock_quantity`, `prd_is_available`, `prd_is_in_season`, `prd_images`, `prd_dateCreated`, `prd_dateUpdated`) VALUES
(2, 1, 1, 'Carrots', 'Fresh stock and daily stock availability', 90.00, 'kg', 100.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69f8acd0040f83.15516914.jpg\"]', '2026-04-16 07:28:40', '2026-05-04 14:27:28'),
(3, 1, 2, 'Banana', 'Ripe bananas are soft, creamy, and have a distinct, slightly sweet, or starchy flavor.', 80.00, 'kg', 100.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69e39f9bd03cb5.34247592.jpg\"]', '2026-04-16 07:28:40', '2026-04-22 18:14:14'),
(4, 1, 1, 'Fresh Spinach', 'Spinach fully washed and cleaned, chopped, sufficiently blanched to ensure adequate stability of color and flavor, properly drained from water, then packed in transparent bag and frozen', 150.00, 'kg', 50.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69e3a0904c7042.03686023.jpg\"]', '2026-04-16 08:05:09', '2026-04-22 18:13:01'),
(5, 2, 2, 'Mini Tomato', 'Fresh Mini Tomato', 200.00, 'kg', 300.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69e3a559be0c46.67223540.jpg\"]', '2026-04-17 18:35:33', '2026-04-22 03:10:35'),
(6, 1, 1, 'Garlic', 'Fresh and big per bundle and kilograms', 150.00, 'kg', 200.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69e3a2e978fdb8.44214466.jpg\"]', '2026-04-18 15:27:37', NULL),
(7, 2, 2, 'Sweet Corn', 'Fresh Corns', 90.00, 'kg', 100.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69e3a60c169053.37192355.jpg\"]', '2026-04-18 15:41:00', NULL),
(8, 13, 2, 'Banana', '', 50.00, 'kg', 100.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69e3b624539f23.16278950.jpg\"]', '2026-04-18 16:49:40', NULL),
(9, 15, 3, 'Fresh Cow Milk', 'Fresh milk from pasture based cow', 150.00, 'liter', 100.00, 1, 0, '[\"assets\\/img\\/products\\/prd_69e4fb4c149985.34800321.jpg\"]', '2026-04-19 15:57:00', NULL),
(10, 18, 1, 'Organic Pechay', 'Freshly farmed', 13.00, 'pc', 49.97, 1, 1, '[\"assets\\/img\\/products\\/prd_69ea2ed3119594.64379227.webp\"]', '2026-04-23 14:38:11', NULL),
(11, 18, 1, 'Carrots', '', 15.00, 'kg', 40.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69ea34515b8985.02809980.jpg\"]', '2026-04-23 15:01:37', NULL),
(12, 1, 1, 'Kamote', '', 15.00, 'kg', 20.00, 1, 1, '[\"assets\\/img\\/products\\/prd_69f8acacd50075.80279963.webp\"]', '2026-05-04 14:26:52', NULL),
(13, 1, 1, 'Cabbage', 'Fresh Cabbage', 20.00, 'kg', 29.99, 1, 1, '[\"assets\\/img\\/products\\/prd_69f952f9df80b6.49228078.jpg\"]', '2026-05-05 02:16:25', NULL),
(14, 19, 1, 'Fresh Pechay', '', 15.00, 'bundle', 19.99, 1, 1, '[\"assets\\/img\\/products\\/prd_6a05f5a9aa9dc1.79267461.webp\"]', '2026-05-14 16:17:45', NULL),
(15, 20, 2, 'Orange', '', 10.00, 'pc', 50.00, 1, 1, '[\"assets\\/img\\/products\\/prd_6a13049814c136.94961099.jpg\"]', '2026-05-24 14:00:56', NULL),
(16, 21, 2, 'Mango', 'Fresh Mango from guimaras', 80.00, 'kg', 100.00, 1, 1, '[\"assets\\/img\\/products\\/prd_6a171ce6932901.73152451.jpg\"]', '2026-05-27 16:33:42', NULL),
(17, 22, 2, 'Fresh Apples', 'Freshly picked apples', 10.00, 'pc', 50.00, 1, 1, '[\"assets\\/img\\/products\\/prd_6a17ff92ae2bc4.72784474.webp\"]', '2026-05-28 08:40:50', '2026-05-28 08:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_review`
--

CREATE TABLE `tbl_review` (
  `rev_id` int(11) NOT NULL,
  `rev_user_id` int(11) NOT NULL,
  `rev_vendor_id` int(11) NOT NULL,
  `rev_product_id` int(11) DEFAULT NULL,
  `rev_order_id` int(11) DEFAULT NULL,
  `rev_rating` smallint(6) NOT NULL,
  `rev_title` varchar(100) DEFAULT NULL,
  `rev_comment` text DEFAULT NULL,
  `rev_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rev_images`)),
  `rev_vendor_reply` text DEFAULT NULL,
  `rev_dateCreated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_review`
--

INSERT INTO `tbl_review` (`rev_id`, `rev_user_id`, `rev_vendor_id`, `rev_product_id`, `rev_order_id`, `rev_rating`, `rev_title`, `rev_comment`, `rev_images`, `rev_vendor_reply`, `rev_dateCreated`) VALUES
(1, 1, 13, NULL, NULL, 5, 'Excellent!', 'Sweet bananas and very fresh.', NULL, NULL, '2026-04-19 16:06:55'),
(2, 1, 13, NULL, NULL, 4, 'Good quality', 'Fresh and affordable fruits.', NULL, NULL, '2026-04-19 16:06:55'),
(3, 1, 13, NULL, NULL, 5, 'Highly recommended', 'Will definitely buy again!', NULL, NULL, '2026-04-19 16:06:55'),
(4, 2, 1, NULL, NULL, 3, 'Excellent!', 'Sweet bananas and very fresh.', NULL, NULL, '2026-04-19 16:17:20'),
(5, 3, 1, NULL, NULL, 4, 'Good quality', 'Fresh and affordable fruits.', NULL, NULL, '2026-04-19 16:17:20'),
(6, 4, 1, NULL, NULL, 4, 'Highly recommended', 'Will definitely buy again!', NULL, NULL, '2026-04-19 16:17:20'),
(7, 4, 1, NULL, NULL, 5, 'Recomment', 'Will definitely buy again!', NULL, NULL, '2026-04-19 16:17:20');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_pass` varchar(255) NOT NULL,
  `user_2fa_secret` varchar(64) DEFAULT NULL,
  `user_2fa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `user_login_count` int(11) NOT NULL DEFAULT 0,
  `user_vendor_pass` varchar(255) DEFAULT NULL,
  `user_fname` varchar(50) NOT NULL,
  `user_lname` varchar(50) NOT NULL,
  `user_phone` varchar(20) DEFAULT NULL,
  `user_address` varchar(255) DEFAULT NULL,
  `user_city` varchar(100) DEFAULT NULL,
  `user_region` varchar(100) DEFAULT NULL,
  `user_role` enum('buyer','vendor') NOT NULL DEFAULT 'buyer',
  `user_profile_pic` text DEFAULT NULL,
  `user_is_active` tinyint(1) DEFAULT 1,
  `user_dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_dateUpdated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `user_lat` decimal(10,7) DEFAULT NULL,
  `user_lng` decimal(10,7) DEFAULT NULL,
  `user_barangay` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `user_email`, `user_pass`, `user_2fa_secret`, `user_2fa_enabled`, `user_login_count`, `user_vendor_pass`, `user_fname`, `user_lname`, `user_phone`, `user_address`, `user_city`, `user_region`, `user_role`, `user_profile_pic`, `user_is_active`, `user_dateCreated`, `user_dateUpdated`, `user_lat`, `user_lng`, `user_barangay`) VALUES
(1, 'buyer@test.com', 'userpass', 'SXURWWFUFF5VS4YS', 0, 7, 'admin', 'Anna', 'Santos', NULL, 'Barangay Cabug', 'Bacolod City', NULL, 'buyer', NULL, 1, '2026-04-16 07:28:40', '2026-05-28 13:56:46', 10.6168903, 123.0246290, NULL),
(2, 'vendor@test.com', 'admin', NULL, 0, 1, NULL, 'Juan', 'Dela Cruz', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-16 07:28:40', '2026-05-28 13:03:51', NULL, NULL, NULL),
(3, 'Babylin@gmail.com', 'admin', NULL, 0, 3, NULL, 'BABYLIN', 'SEBONGGA', '90909090', NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-17 18:31:59', '2026-05-28 12:52:38', NULL, NULL, NULL),
(4, 'Lyn@gmail.com', 'userpass', NULL, 0, 8, 'admin', 'Lyn', '', NULL, 'Taculing, Bacolod City, Negros Occidental', 'Bacolod City', 'Negros Occidental', 'buyer', 'uploads/users/pic_69ea28309c9797.40455404.jpg', 1, '2026-04-18 11:35:39', '2026-05-28 13:32:37', NULL, NULL, 'Taculing'),
(5, 'taala@gmail.com', 'admin', NULL, 0, 16, NULL, 'TA-ALA', 'Farms', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-18 14:49:45', '2026-05-28 12:54:10', NULL, NULL, NULL),
(6, 'jkn@gmail.com', 'admin', NULL, 0, 4, NULL, 'JKN', 'Vendor', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-18 16:35:53', '2026-05-28 12:55:36', NULL, NULL, NULL),
(7, 'mamabeth@gmail.com', 'admin', NULL, 0, 2, NULL, 'Mama Beth', 'Vendor', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-18 16:35:53', '2026-05-28 12:57:22', NULL, NULL, NULL),
(8, 'citydairy@gmail.com', 'admin', NULL, 0, 2, NULL, 'Happy', 'Dairy', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-18 16:35:53', '2026-05-28 13:26:01', NULL, NULL, NULL),
(9, 'negrosfarmer@gmail.com', 'admin', NULL, 0, 2, NULL, 'Orchard', 'Fresh', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-18 16:35:53', '2026-05-28 13:28:17', NULL, NULL, NULL),
(10, 'GreenBlumsPlants@gmail.com', 'admin', NULL, 0, 2, NULL, 'Garden', 'Greens', NULL, NULL, NULL, NULL, 'vendor', NULL, 1, '2026-04-18 16:35:53', '2026-05-28 13:31:18', NULL, NULL, NULL),
(11, 'nina@email.com', 'userpass', NULL, 0, 1, NULL, 'Nina', 'Lopez', NULL, NULL, NULL, NULL, 'buyer', NULL, 1, '2026-04-19 16:15:37', '2026-05-28 13:05:10', NULL, NULL, NULL),
(12, 'carlo@email.com', 'userpass', NULL, 0, 1, NULL, 'Carlo', 'Mendoza', NULL, NULL, NULL, NULL, 'buyer', NULL, 1, '2026-04-19 16:15:37', '2026-05-28 13:36:21', NULL, NULL, NULL),
(13, 'patricia@email.com', 'userpass', NULL, 0, 1, NULL, 'Patricia', 'Revera', NULL, NULL, NULL, NULL, 'buyer', NULL, 1, '2026-04-19 16:15:37', '2026-05-28 13:36:17', NULL, NULL, NULL),
(15, 'raya@gmail.com', 'rayapass', NULL, 0, 4, NULL, 'Raya', '', '1234567890', NULL, NULL, NULL, 'buyer', NULL, 1, '2026-04-23 12:29:08', '2026-05-04 16:37:48', NULL, NULL, NULL),
(16, 'Ryial@gmail.com', 'userpass', '3HDFEEUKDWFWYT6V', 0, 0, NULL, 'Ryial', 'Blance', '1234567890', NULL, NULL, NULL, 'buyer', NULL, 1, '2026-05-04 14:20:12', '2026-05-28 13:36:11', NULL, NULL, NULL),
(17, 'Rylyn@gmail.com', 'userpass', 'OCJTOVQI7YSTCZHS', 1, 0, NULL, 'Rylyn', 'Blanca', '6534232', NULL, NULL, NULL, 'buyer', NULL, 1, '2026-05-04 16:41:25', '2026-05-28 13:36:07', NULL, NULL, NULL),
(18, 'Yhana@gmail.com', 'userpass', 'AXBX425MAD2LYS43', 1, 1, NULL, 'Yhana', '', '90909090', NULL, NULL, NULL, 'buyer', NULL, 1, '2026-05-05 02:12:19', '2026-05-28 13:36:01', NULL, NULL, NULL),
(19, 'sebongga.babylin@gmail.com', 'userpass', 'WZHWAVJOHY4P7CEN', 0, 18, NULL, 'BABYLIN', 'SEBONGGA', '90909090', 'Alijis, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', 'Bacolod City', 'Negros Occidental', 'buyer', NULL, 1, '2026-05-09 13:40:51', '2026-05-28 13:35:57', 10.6367119, 122.9503643, NULL),
(20, 'lin@gmail.com', 'userpass', NULL, 0, 0, NULL, 'Lin', '', '8080', 'Barangay Abuanan', 'Bago City', 'Negros Occidental', 'buyer', NULL, 1, '2026-05-14 14:46:12', '2026-05-28 13:35:51', NULL, NULL, NULL),
(22, 'AM@gmail.com', 'userpass', 'OBMBCSULZYYOFTM7', 0, 1, NULL, 'AM', '', '8080', 'Abuanan', 'Bago City', 'Negros Occidental', 'buyer', NULL, 1, '2026-05-14 15:02:05', '2026-05-28 13:35:44', NULL, NULL, NULL),
(23, 'vicepass', 'userpass', 'WDGWSIGIE3ACHYB2', 0, 0, NULL, 'Vice', '', '900', 'Alijis, Bacolod City, Negros Occidental', 'Bacolod City', 'Negros Occidental', 'buyer', NULL, 1, '2026-05-14 15:10:39', '2026-05-28 13:35:39', NULL, NULL, 'Alijis'),
(24, 'liza@gmail.com', 'userpass', 'SBNBG22N3267K2PG', 0, 3, 'admin', 'Liza', '', '0909', 'Larena', 'San Juan', 'Siquijor', 'buyer', NULL, 1, '2026-05-14 16:14:18', '2026-05-28 13:51:37', NULL, NULL, NULL),
(25, 'mary@gmail.com', 'userpass', 'LQMGOPOMPABWPAWB', 0, 2, 'admin', 'Mary', '', '0991100', 'Brgy. Cabug, Lot 53 Block 10', 'Bacolod City', 'Negros Occidental', 'buyer', NULL, 1, '2026-05-24 13:56:14', '2026-05-28 13:47:01', NULL, NULL, NULL),
(26, 'Maria@gmail.com', 'userpass', 'CMHBOEK64TIZYMHO', 0, 8, NULL, 'Mari', '', '8080', 'Abuanan', 'Bago City', 'Negros Occidental', 'buyer', NULL, 1, '2026-05-24 13:57:56', '2026-05-28 13:46:16', NULL, NULL, NULL),
(27, 'Jose@gmail.com', 'userpass', 'RABWKG5GYPLCD4KM', 0, 2, 'admin', 'Jose', '', '0909', NULL, NULL, NULL, 'vendor', NULL, 1, '2026-05-27 16:31:56', '2026-05-28 13:55:12', NULL, NULL, NULL),
(28, 'Rose@gmail.com', 'admin', 'L4QG6WUDON6YMW4J', 1, 4, 'admin', 'Rose', '', '9080', 'Magsungay, Singcang-Airport, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', NULL, NULL, 'vendor', NULL, 1, '2026-05-28 08:38:07', '2026-05-28 13:58:03', 10.6558914, 122.9281228, NULL),
(29, 'user@gmail.com', 'userpass', 'F4XXZ27ZDIHMZRAK', 0, 0, NULL, 'User', '', '09991', 'Santol, Mandalagan, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', NULL, NULL, 'buyer', NULL, 1, '2026-05-28 14:12:59', '2026-05-28 14:13:34', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_vendor`
--

CREATE TABLE `tbl_vendor` (
  `vnd_id` int(11) NOT NULL,
  `vnd_user_id` int(11) NOT NULL,
  `vnd_farm_name` varchar(100) NOT NULL,
  `vnd_owner_name` varchar(100) DEFAULT NULL,
  `vnd_farm_desc` text DEFAULT NULL,
  `vnd_cover_photo` text DEFAULT NULL,
  `vnd_profile_pic` text DEFAULT NULL,
  `vnd_address` text NOT NULL,
  `vnd_pickup_instructions` text DEFAULT NULL,
  `vnd_delivery_fee` decimal(10,2) DEFAULT 0.00,
  `vnd_rating_avg` decimal(3,2) DEFAULT 0.00,
  `vnd_total_reviews` int(11) DEFAULT 0,
  `vnd_bank_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vnd_bank_details`)),
  `vnd_dateCreated` timestamp NOT NULL DEFAULT current_timestamp(),
  `vnd_dateUpdated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `vnd_lat` decimal(10,7) DEFAULT NULL,
  `vnd_lng` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_vendor`
--

INSERT INTO `tbl_vendor` (`vnd_id`, `vnd_user_id`, `vnd_farm_name`, `vnd_owner_name`, `vnd_farm_desc`, `vnd_cover_photo`, `vnd_profile_pic`, `vnd_address`, `vnd_pickup_instructions`, `vnd_delivery_fee`, `vnd_rating_avg`, `vnd_total_reviews`, `vnd_bank_details`, `vnd_dateCreated`, `vnd_dateUpdated`, `vnd_lat`, `vnd_lng`) VALUES
(1, 5, 'TA-ALA FARMS Inc', 'TA-ALA FARMS Inc', 'Fresh Eggs Farm. Sizes ranges to small-xxl.\r\nFree Range Chicken', 'https://images.pexels.com/photos/36574335/pexels-photo-36574335.jpeg', NULL, 'Cabug, Bacolod, Negros Island Region, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-04-16 07:28:40', '2026-05-13 14:49:52', 10.5989209, 122.9477642),
(2, 3, 'BABYLIN SEBONGGA\'s Farm', 'BABYLIN SEBONGGA\'s Farm', NULL, 'https://images.pexels.com/photos/8232764/pexels-photo-8232764.jpeg', NULL, 'Mandalagan, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-04-17 18:31:59', '2026-05-28 12:47:23', 10.6918303, 122.9661416),
(13, 6, 'JKN Fruit Farm', 'JKN Fruit Farm', 'Fresh tropical fruits directly from the farm.', 'https://images.pexels.com/photos/36967907/pexels-photo-36967907.jpeg', NULL, 'M3V4+2HQ Talisay, Negros Occidental', 'Pickup at main fruit stand. Call before arrival.', 0.00, 0.00, 0, NULL, '2026-04-18 16:36:26', '2026-05-11 15:58:22', 10.7374022, 122.9704928),
(14, 7, 'Mama Beth\'s Eco Farm', 'Mama Beth\'s Eco Farm', 'Organic fruits grown using eco-friendly methods.', 'https://images.pexels.com/photos/35834140/pexels-photo-35834140.jpeg', NULL, 'Alangilan Barangay Hall, Dumaguete South Road, Alangilan, Santa Catalina, Negros Oriental, Negros Island Region, 6220, Philippines', 'Pickup at farm entrance. Ask for staff assistance.', 0.00, 0.00, 0, NULL, '2026-04-18 16:36:26', '2026-05-28 12:58:50', 9.2751187, 122.8784975),
(15, 8, 'Sagay City Dairy Farm', 'Sagay City Dairy Farm', 'Fresh milk, yogurt, and cheese straight from the dairy farm.', 'https://images.pexels.com/photos/33110603/pexels-photo-33110603.jpeg', NULL, 'Sagay, Negros Occidental, Negros Island Region, 6122, Philippines', 'Pickup at dairy station. Refrigerated items available.', 0.00, 0.00, 0, NULL, '2026-04-18 16:36:26', '2026-05-28 13:26:22', 10.8960681, 123.4154617),
(16, 9, 'Negros Farmer', 'Negros Farmer', 'Fresh herbs and greens harvested daily.', 'https://images.pexels.com/photos/7509491/pexels-photo-7509491.jpeg', NULL, 'Singcang-Airport, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', 'Pickup at greenhouse section.', 0.00, 0.00, 0, NULL, '2026-04-18 16:36:26', '2026-05-28 13:29:24', 10.6578534, 122.9331869),
(17, 10, 'Green Blüms Plants', 'Green Blüms Plants', 'Leafy vegetables and healthy greens grown locally.', 'https://images.pexels.com/photos/35417787/pexels-photo-35417787.jpeg', NULL, 'Carlos Hilado Highway, Samfloma, Villamonte, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', 'Pickup at garden stall.', 0.00, 0.00, 0, NULL, '2026-04-18 16:36:26', '2026-05-28 13:31:58', 10.6771386, 122.9730378),
(18, 4, 'Lyn\'s Farm', 'Lyn Original', NULL, 'uploads/vendors/pic_69ea24b68a5b05.81203777.jpg', 'uploads/vendors/pic_69ea2d3c2cafe0.44019520.jpg', 'Taala Egg Farms, Cansilayan, Murcia, Negros Occidental, Negros Island Region, 6129, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-04-23 00:24:27', '2026-05-14 16:08:54', 10.5645889, 123.0238905),
(19, 24, 'Liza\'s Farm', 'Liza\'s Farm', NULL, 'uploads/vendors/pic_6a184930e34200.78220555.jpg', NULL, 'Larena, Siquijor, Negros Island Region, 6226, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-05-14 16:14:18', '2026-05-28 13:54:56', 9.2488946, 123.5910019),
(20, 25, 'Mary\'s Farm', 'Mary\'s Farm', NULL, 'uploads/vendors/pic_6a171530588211.91101258.jpg', NULL, 'Talisay, Negros Occidental, Negros Island Region, 6115, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-05-24 13:56:14', '2026-05-28 13:47:18', 10.7372649, 122.9673325),
(21, 27, 'Jose\'s Farm', 'Jose\'s Farm', NULL, 'uploads/vendors/pic_6a184949743ce5.54176687.jpg', NULL, 'Banago, Bacolod-1, Bacolod, Negros Island Region, 6100, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-05-27 16:31:56', '2026-05-28 13:55:21', 10.7035993, 122.9499534),
(22, 28, 'Rose\'s Farm', 'Rose\'s Farm', NULL, 'uploads/vendors/pic_6a1849660f5409.48557996.jpg', NULL, 'Magsungay, Singcang-Airport, Bacolod-2, Bacolod, Negros Island Region, 6100, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-05-28 08:38:07', '2026-05-28 14:02:26', 10.6558914, 122.9281228),
(23, 1, 'anna santos\'s Farm', 'anna santos\'s Farm', NULL, 'uploads/vendors/pic_6a1849b133dac3.22612814.jpg', NULL, 'Blumentritt, Murcia, Negros Occidental, Negros Island Region, 6129, Philippines', NULL, 0.00, 0.00, 0, NULL, '2026-05-28 13:15:32', '2026-05-28 13:57:05', 10.6168903, 123.0246290);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD PRIMARY KEY (`crt_id`),
  ADD KEY `crt_user_id` (`crt_user_id`);

--
-- Indexes for table `tbl_cart_item`
--
ALTER TABLE `tbl_cart_item`
  ADD PRIMARY KEY (`cit_id`),
  ADD KEY `cit_cart_id` (`cit_cart_id`),
  ADD KEY `cit_product_id` (`cit_product_id`);

--
-- Indexes for table `tbl_category`
--
ALTER TABLE `tbl_category`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `cat_name` (`cat_name`);

--
-- Indexes for table `tbl_favorites`
--
ALTER TABLE `tbl_favorites`
  ADD PRIMARY KEY (`fav_id`),
  ADD KEY `fav_user_id` (`fav_user_id`),
  ADD KEY `fav_vendor_id` (`fav_vendor_id`),
  ADD KEY `fav_product_id` (`fav_product_id`);

--
-- Indexes for table `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD PRIMARY KEY (`ord_id`),
  ADD KEY `ord_user_id` (`ord_user_id`),
  ADD KEY `ord_vendor_id` (`ord_vendor_id`);

--
-- Indexes for table `tbl_order_item`
--
ALTER TABLE `tbl_order_item`
  ADD PRIMARY KEY (`oit_id`),
  ADD KEY `oit_order_id` (`oit_order_id`),
  ADD KEY `oit_product_id` (`oit_product_id`);

--
-- Indexes for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `pay_order_id` (`pay_order_id`);

--
-- Indexes for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD PRIMARY KEY (`prd_id`),
  ADD KEY `prd_vendor_id` (`prd_vendor_id`),
  ADD KEY `prd_category_id` (`prd_category_id`),
  ADD KEY `idx_prd_available` (`prd_is_available`),
  ADD KEY `idx_prd_in_season` (`prd_is_in_season`);

--
-- Indexes for table `tbl_review`
--
ALTER TABLE `tbl_review`
  ADD PRIMARY KEY (`rev_id`),
  ADD KEY `rev_user_id` (`rev_user_id`),
  ADD KEY `rev_vendor_id` (`rev_vendor_id`),
  ADD KEY `rev_product_id` (`rev_product_id`),
  ADD KEY `rev_order_id` (`rev_order_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- Indexes for table `tbl_vendor`
--
ALTER TABLE `tbl_vendor`
  ADD PRIMARY KEY (`vnd_id`),
  ADD KEY `vnd_user_id` (`vnd_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  MODIFY `crt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_cart_item`
--
ALTER TABLE `tbl_cart_item`
  MODIFY `cit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `tbl_category`
--
ALTER TABLE `tbl_category`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_favorites`
--
ALTER TABLE `tbl_favorites`
  MODIFY `fav_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_order`
--
ALTER TABLE `tbl_order`
  MODIFY `ord_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tbl_order_item`
--
ALTER TABLE `tbl_order_item`
  MODIFY `oit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `pay_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_product`
--
ALTER TABLE `tbl_product`
  MODIFY `prd_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_review`
--
ALTER TABLE `tbl_review`
  MODIFY `rev_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tbl_vendor`
--
ALTER TABLE `tbl_vendor`
  MODIFY `vnd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_cart`
--
ALTER TABLE `tbl_cart`
  ADD CONSTRAINT `tbl_cart_ibfk_1` FOREIGN KEY (`crt_user_id`) REFERENCES `tbl_user` (`user_id`);

--
-- Constraints for table `tbl_cart_item`
--
ALTER TABLE `tbl_cart_item`
  ADD CONSTRAINT `tbl_cart_item_ibfk_1` FOREIGN KEY (`cit_cart_id`) REFERENCES `tbl_cart` (`crt_id`),
  ADD CONSTRAINT `tbl_cart_item_ibfk_2` FOREIGN KEY (`cit_product_id`) REFERENCES `tbl_product` (`prd_id`);

--
-- Constraints for table `tbl_favorites`
--
ALTER TABLE `tbl_favorites`
  ADD CONSTRAINT `tbl_favorites_ibfk_1` FOREIGN KEY (`fav_user_id`) REFERENCES `tbl_user` (`user_id`),
  ADD CONSTRAINT `tbl_favorites_ibfk_2` FOREIGN KEY (`fav_vendor_id`) REFERENCES `tbl_vendor` (`vnd_id`),
  ADD CONSTRAINT `tbl_favorites_ibfk_3` FOREIGN KEY (`fav_product_id`) REFERENCES `tbl_product` (`prd_id`);

--
-- Constraints for table `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD CONSTRAINT `tbl_order_ibfk_1` FOREIGN KEY (`ord_user_id`) REFERENCES `tbl_user` (`user_id`),
  ADD CONSTRAINT `tbl_order_ibfk_2` FOREIGN KEY (`ord_vendor_id`) REFERENCES `tbl_vendor` (`vnd_id`);

--
-- Constraints for table `tbl_order_item`
--
ALTER TABLE `tbl_order_item`
  ADD CONSTRAINT `tbl_order_item_ibfk_1` FOREIGN KEY (`oit_order_id`) REFERENCES `tbl_order` (`ord_id`),
  ADD CONSTRAINT `tbl_order_item_ibfk_2` FOREIGN KEY (`oit_product_id`) REFERENCES `tbl_product` (`prd_id`);

--
-- Constraints for table `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD CONSTRAINT `tbl_payment_ibfk_1` FOREIGN KEY (`pay_order_id`) REFERENCES `tbl_order` (`ord_id`);

--
-- Constraints for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD CONSTRAINT `tbl_product_ibfk_1` FOREIGN KEY (`prd_vendor_id`) REFERENCES `tbl_vendor` (`vnd_id`),
  ADD CONSTRAINT `tbl_product_ibfk_2` FOREIGN KEY (`prd_category_id`) REFERENCES `tbl_category` (`cat_id`);

--
-- Constraints for table `tbl_review`
--
ALTER TABLE `tbl_review`
  ADD CONSTRAINT `tbl_review_ibfk_1` FOREIGN KEY (`rev_user_id`) REFERENCES `tbl_user` (`user_id`),
  ADD CONSTRAINT `tbl_review_ibfk_2` FOREIGN KEY (`rev_vendor_id`) REFERENCES `tbl_vendor` (`vnd_id`),
  ADD CONSTRAINT `tbl_review_ibfk_3` FOREIGN KEY (`rev_product_id`) REFERENCES `tbl_product` (`prd_id`),
  ADD CONSTRAINT `tbl_review_ibfk_4` FOREIGN KEY (`rev_order_id`) REFERENCES `tbl_order` (`ord_id`);

--
-- Constraints for table `tbl_vendor`
--
ALTER TABLE `tbl_vendor`
  ADD CONSTRAINT `tbl_vendor_ibfk_1` FOREIGN KEY (`vnd_user_id`) REFERENCES `tbl_user` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
