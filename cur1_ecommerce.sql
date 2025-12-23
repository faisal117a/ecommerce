-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 10:52 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cur1_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('billing','shipping','both') DEFAULT 'both',
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'United States',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Men', 'men', 'Men\'s clothing and accessories', '2025-12-08 19:15:15'),
(2, 'Women', 'women', 'Women\'s clothing and accessories', '2025-12-08 19:15:15'),
(3, 'Unisex', 'unisex', 'Unisex clothing and accessories', '2025-12-08 19:15:15'),
(4, 'Kids', 'kids', '', '2025-12-09 09:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 'Faisal Hassan', 'faisal117a@gmail.com', 'Quote and Invoice for Google Workspace Email Accounts', 'we need price', 'read', NULL, '2025-12-10 04:24:24', '2025-12-10 04:24:36');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_purchase`, `usage_limit`, `used_count`, `expiry_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'S20', 'fixed', 100.00, 0.00, NULL, 0, NULL, 'active', '2025-12-09 15:04:42', '2025-12-09 15:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cash_on_delivery',
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `billing_address`, `phone`, `payment_method`, `coupon_code`, `discount_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 184.97, 'delivered', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', NULL, '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 08:56:48', '2025-12-09 09:04:07'),
(2, NULL, 139.97, 'cancelled', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 11:26:53', '2025-12-09 11:27:46'),
(3, NULL, 169.97, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 11:28:57', '2025-12-09 11:28:57'),
(4, NULL, 99.98, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 12:48:20', '2025-12-09 12:48:20'),
(5, NULL, 104.98, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 12:59:40', '2025-12-09 12:59:40'),
(6, NULL, 154.97, 'pending', 'Gujrat, Gujrat, Punjab 50700', 'Gujrat, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 14:12:39', '2025-12-09 14:12:39'),
(7, NULL, 49.99, 'pending', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', '0561808255', 'cash_on_delivery', NULL, 0.00, '2025-12-09 15:04:59', '2025-12-09 15:04:59'),
(8, NULL, 39.99, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 15:36:17', '2025-12-09 15:36:17'),
(9, NULL, 74.98, 'pending', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', '0561808255', 'cash_on_delivery', NULL, 0.00, '2025-12-09 15:58:38', '2025-12-09 15:58:38'),
(10, 6, 49.99, 'pending', 'Gujrat, Gujrat, Punjab 50700', 'Gujrat, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 16:01:25', '2025-12-09 16:01:25'),
(11, 8, 49.99, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 16:06:03', '2025-12-09 16:06:03'),
(12, 9, 49.99, 'pending', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', '0561808255', 'cash_on_delivery', NULL, 0.00, '2025-12-09 16:11:37', '2025-12-09 16:11:37'),
(13, 10, 49.99, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 17:30:54', '2025-12-09 17:30:54'),
(14, 12, 49.99, 'pending', 'Gujrat, Gujrat, Punjab 50700', 'Gujrat, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 18:20:09', '2025-12-09 18:20:09'),
(15, 14, 44.99, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 18:29:38', '2025-12-09 18:29:38'),
(16, 15, 49.99, 'pending', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '1st Floor Office #2, Dhakar Plaza, Rehman Shaheed Road, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 18:37:27', '2025-12-09 18:37:27'),
(17, 15, 44.99, 'pending', 'Gujrat, Gujrat, Punjab 50700', 'Gujrat, Gujrat, Punjab 50700', '03129844198', 'cash_on_delivery', NULL, 0.00, '2025-12-09 18:38:00', '2025-12-09 18:38:00'),
(18, 16, 49.99, 'pending', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', 'Block A33 Industrial Area Dibba, Gujrat, Al Fujairah 50700', '0561808255', 'cash_on_delivery', NULL, 0.00, '2025-12-09 19:51:51', '2025-12-09 19:51:51');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 2, 2, 59.99),
(2, 1, 17, 1, 64.99),
(3, 2, 2, 1, 59.99),
(4, 2, 4, 2, 39.99),
(5, 3, 2, 1, 59.99),
(6, 3, 13, 2, 54.99),
(7, 4, 3, 2, 49.99),
(8, 5, 13, 1, 54.99),
(9, 5, 3, 1, 49.99),
(10, 6, 3, 1, 49.99),
(11, 6, 14, 1, 44.99),
(12, 6, 2, 1, 59.99),
(13, 7, 3, 1, 49.99),
(14, 8, 4, 1, 39.99),
(15, 9, 1, 1, 24.99),
(16, 9, 3, 1, 49.99),
(17, 10, 3, 1, 49.99),
(18, 11, 3, 1, 49.99),
(19, 12, 3, 1, 49.99),
(20, 13, 3, 1, 49.99),
(21, 14, 3, 1, 49.99),
(22, 15, 23, 1, 44.99),
(23, 16, 3, 1, 49.99),
(24, 17, 23, 1, 44.99),
(25, 18, 3, 1, 49.99);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `notes`, `created_at`) VALUES
(1, 2, 'cancelled', 'Status changed from Pending to Cancelled', '2025-12-09 11:27:46');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `featured` tinyint(1) DEFAULT 0,
  `badge` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `category_id`, `image_url`, `stock_quantity`, `status`, `featured`, `badge`, `created_at`, `updated_at`) VALUES
(1, 'Classic White T-Shirt', 'classic-white-tshirt', 'Premium cotton t-shirt with modern fit. Perfect for everyday wear.', 24.99, 1, 'assets/uploads/men/classic-white-tshirt.jpg', 50, 'active', 0, 'Best Seller', '2025-12-09 08:05:15', '2025-12-09 08:05:54'),
(2, 'Slim Fit Denim Jeans', 'slim-fit-denim-jeans', 'Comfortable slim-fit jeans with stretch. Classic blue wash.', 59.99, 1, 'assets/uploads/men/slim-fit-denim-jeans.jpg', 35, 'active', 0, 'New', '2025-12-09 08:05:15', '2025-12-09 08:05:55'),
(3, 'Casual Hoodie', 'casual-hoodie', 'Soft cotton hoodie with drawstring hood. Perfect for casual outings.', 49.99, 1, 'assets/uploads/men/casual-hoodie.jpg', 40, 'active', 0, 'Hot', '2025-12-09 08:05:15', '2025-12-09 08:05:56'),
(4, 'Formal Dress Shirt', 'formal-dress-shirt', 'Crisp white dress shirt. Ideal for business and formal occasions.', 39.99, 1, 'assets/uploads/men/formal-dress-shirt.jpg', 30, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:05:58'),
(5, 'Chino Pants', 'chino-pants', 'Versatile chino pants in classic khaki. Smart casual essential.', 54.99, 1, 'assets/uploads/men/chino-pants.jpg', 25, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:05:59'),
(6, 'Polo Shirt', 'polo-shirt', 'Classic polo shirt in navy blue. Breathable cotton blend.', 34.99, 1, 'assets/uploads/men/polo-shirt.jpg', 45, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:05:59'),
(7, 'Leather Jacket', 'leather-jacket', 'Genuine leather jacket with quilted lining. Timeless style.', 129.99, 1, 'assets/uploads/men/leather-jacket.jpg', 15, 'active', 0, 'Limited', '2025-12-09 08:05:15', '2025-12-09 08:06:01'),
(8, 'Cargo Shorts', 'cargo-shorts', 'Comfortable cargo shorts with multiple pockets. Perfect for summer.', 44.99, 1, 'assets/uploads/men/cargo-shorts.jpg', 30, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:02'),
(9, 'Sweater', 'mens-sweater', 'Warm knit sweater in charcoal gray. Cozy and stylish.', 64.99, 1, 'assets/uploads/men/mens-sweater.jpg', 20, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:03'),
(10, 'Track Pants', 'track-pants', 'Comfortable athletic track pants. Great for workouts or casual wear.', 49.99, 1, 'assets/uploads/men/product_6937e3c2c3c41_1765270466.jpg', 35, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:54:26'),
(11, 'Floral Summer Dress', 'floral-summer-dress', 'Beautiful floral print dress. Light and airy for summer days.', 69.99, 2, 'assets/uploads/women/floral-summer-dress.jpg', 40, 'active', 0, 'Trending', '2025-12-09 08:05:15', '2025-12-09 08:06:06'),
(12, 'High-Waist Skinny Jeans', 'high-waist-skinny-jeans', 'Flattering high-waist skinny jeans. Perfect fit and comfort.', 64.99, 2, 'assets/uploads/women/high-waist-skinny-jeans.jpg', 30, 'active', 0, 'Best Seller', '2025-12-09 08:05:15', '2025-12-09 08:06:08'),
(13, 'Oversized Sweater', 'oversized-sweater', 'Cozy oversized sweater in soft beige. Perfect for layering.', 54.99, 2, 'assets/uploads/women/oversized-sweater.jpg', 25, 'active', 0, 'New', '2025-12-09 08:05:15', '2025-12-09 08:06:09'),
(14, 'Blouse', 'blouse', 'Elegant blouse in white. Versatile for office or casual wear.', 44.99, 2, 'assets/uploads/women/product_6937e3d5da4b5_1765270485.jpg', 35, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:54:45'),
(15, 'Midi Skirt', 'midi-skirt', 'Classic midi skirt in black. Timeless and elegant.', 49.99, 2, 'assets/uploads/women/product_6937e3e3ed625_1765270499.jpg', 28, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:54:59'),
(16, 'Cardigan', 'womens-cardigan', 'Soft cardigan in pastel pink. Perfect for layering.', 59.99, 2, 'assets/uploads/women/womens-cardigan.jpg', 22, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:14'),
(17, 'Wide Leg Pants', 'wide-leg-pants', 'Comfortable wide-leg pants in navy. Modern and stylish.', 64.99, 2, 'assets/uploads/women/product_6937e3f139a4d_1765270513.jpg', 20, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:55:13'),
(18, 'Crop Top', 'crop-top', 'Trendy crop top in white. Perfect for summer styling.', 29.99, 2, 'assets/uploads/women/product_6937e3f94d040_1765270521.jpg', 40, 'active', 0, 'Hot', '2025-12-09 08:05:15', '2025-12-09 08:55:21'),
(19, 'Maxi Dress', 'maxi-dress', 'Elegant maxi dress in floral print. Perfect for special occasions.', 79.99, 2, 'assets/uploads/women/maxi-dress.jpg', 18, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:19'),
(20, 'Denim Jacket', 'womens-denim-jacket', 'Classic denim jacket. Versatile layering piece.', 69.99, 2, 'assets/uploads/women/womens-denim-jacket.jpg', 25, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:21'),
(21, 'Unisex Graphic Tee', 'unisex-graphic-tee', 'Cool graphic t-shirt. Unisex fit for everyone.', 29.99, 3, 'assets/uploads/unisex/unisex-graphic-tee.jpg', 50, 'active', 0, 'Limited', '2025-12-09 08:05:15', '2025-12-09 08:06:22'),
(22, 'Minimalist Hoodie', 'minimalist-hoodie', 'Simple and clean hoodie design. Unisex sizing.', 54.99, 3, 'assets/uploads/unisex/minimalist-hoodie.jpg', 40, 'active', 0, 'New', '2025-12-09 08:05:15', '2025-12-09 08:06:23'),
(23, 'Crew Neck Sweatshirt', 'crew-neck-sweatshirt', 'Comfortable crew neck sweatshirt. Perfect for casual days.', 44.99, 3, 'assets/uploads/unisex/crew-neck-sweatshirt.jpg', 45, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:25'),
(24, 'Baseball Cap', 'baseball-cap', 'Classic baseball cap. Adjustable fit for all.', 24.99, 3, 'assets/uploads/unisex/baseball-cap.jpg', 60, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:27'),
(25, 'Joggers', 'joggers', 'Comfortable jogger pants. Perfect for active or casual wear.', 49.99, 3, 'assets/uploads/unisex/joggers.jpg', 35, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:28'),
(26, 'Zip-Up Hoodie', 'zip-up-hoodie', 'Versatile zip-up hoodie. Easy to layer.', 59.99, 3, 'assets/uploads/unisex/zip-up-hoodie.jpg', 30, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:30'),
(27, 'Long Sleeve Tee', 'long-sleeve-tee', 'Basic long sleeve t-shirt. Essential wardrobe piece.', 34.99, 3, 'assets/uploads/unisex/long-sleeve-tee.jpg', 50, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:31'),
(28, 'Beanie', 'beanie', 'Warm knit beanie. One size fits all.', 19.99, 3, 'assets/uploads/unisex/beanie.jpg', 70, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:34'),
(29, 'Oversized T-Shirt', 'oversized-tshirt', 'Comfortable oversized t-shirt. Relaxed fit.', 27.99, 3, 'assets/uploads/unisex/oversized-tshirt.jpg', 55, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:35'),
(30, 'Fleece Jacket', 'fleece-jacket', 'Warm fleece jacket. Perfect for cool weather.', 64.99, 3, 'assets/uploads/unisex/fleece-jacket.jpg', 25, 'active', 0, NULL, '2025-12-09 08:05:15', '2025-12-09 08:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_type` varchar(50) NOT NULL,
  `variant_value` varchar(100) NOT NULL,
  `price_modifier` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `sku` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Cur1 Fashion', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(2, 'site_email', 'info@cur1.com', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(3, 'smtp_host', '', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(4, 'smtp_port', '587', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(5, 'smtp_username', '', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(6, 'smtp_password', '', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(7, 'smtp_encryption', 'tls', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(8, 'smtp_from_email', '', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(9, 'smtp_from_name', 'Cur1 Fashion', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(10, 'header_title', 'Discover Your Everyday Style', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(11, 'header_description', 'Premium quality men &amp; women clothing with modern designs.', 'text', '2025-12-09 11:22:24', '2025-12-09 14:22:28'),
(12, 'header_image', 'assets/uploads/header_1765290148.jpg', 'text', '2025-12-09 11:22:24', '2025-12-09 14:22:28'),
(13, 'logo_path', '', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(14, 'homepage_category_1', '1', 'text', '2025-12-09 11:22:24', '2025-12-09 11:25:01'),
(15, 'homepage_category_2', '2', 'text', '2025-12-09 11:22:24', '2025-12-09 11:25:01'),
(16, 'meta_title', 'Cur1 Fashion Store', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(17, 'meta_description', 'Premium quality men & women clothing with modern designs.', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24'),
(18, 'meta_keywords', 'fashion, clothing, men, women', 'text', '2025-12-09 11:22:24', '2025-12-09 11:22:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `name`, `created_at`) VALUES
(1, 'admin@ecommerce.com', '$2y$10$9RDCuzEH0HuorHk5y6v88.d0t7F4x7jqg1IYr6xntLys/GIyP9zR6', 'admin', 'Admin User', '2025-12-08 19:15:15'),
(6, 'many@yahoo.com', '$2y$10$7msKTOjAsjs5bKQ35WnCa.B8xGA6jKIxbh6fzOHm5f1O1/vXUp/Uu', 'customer', 'ManyMic', '2025-12-09 16:00:32'),
(8, 'manymore@yahoo.com', '$2y$10$dSoLuh4C3YO8UUpQSBY5vO/fxbXgSP55aez11ZenouF8UNA0gCGSW', 'customer', 'Faisal Hassan', '2025-12-09 16:05:16'),
(9, 'many111@yahoo.com', '$2y$10$6XrDU.O1rMka9WUFYFiYy.ypGz1g17I18VjL1t2RakbWOV41/xgwG', 'customer', 'Shahzad', '2025-12-09 16:11:07'),
(10, 'mansssy@yahoo.com', '$2y$10$f12jHfFb/C9EXAbSotnzG.nibavore3rn1V4c6xLMOvenMVTF5f7u', 'customer', 'Faisal Hassan', '2025-12-09 17:30:12'),
(11, 'manyaads@yahoo.com', '$2y$10$BLoQIWEF5qoOXbs03jqjaOk7/GHi2IjUHWfE6w7w/t1ekE3abJg/e', 'customer', 'Faisal Hassan', '2025-12-09 17:45:15'),
(12, 'ase@yahoo.com', '$2y$10$lyyJTcyYsUTGer5dOe59UOzqlnMn1JTfhZHpAOD81FaTkdgqbEney', 'customer', 'Ayan Hassan', '2025-12-09 18:17:40'),
(13, 'manggy@yahoo.com', '$2y$10$RotN1PgflbyEUIe3rh5UFOd.lTGJpsaUlvXNFg5DKy24LtlOmTo3W', 'customer', 'Faisal Hassan', '2025-12-09 18:20:40'),
(14, 'mdddany@yahoo.com', '$2y$10$LZpZz/2poGYHOdtvIqzVpeC6KKBEnMmlautdebns2z3SOlLvug91O', 'customer', 'Faisal Hassan', '2025-12-09 18:29:38'),
(15, 'aseeee@yahoo.com', '$2y$10$HoJf1kODZ0NGgXpaVvOAKOoZqtZRqhklZuSWJc/mNRNSle1QND0PG', 'customer', 'Faisal Hassan', '2025-12-09 18:37:27'),
(16, 'aamany@yahoo.com', '$2y$10$UfEBhQSOefjTqY9F23jig.BwJHPIMfRFNu4Lh3YHoa.KiGTNbgxci', 'customer', 'Saqib Shahzad', '2025-12-09 19:51:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_type` (`variant_type`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
