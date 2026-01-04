-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 04, 2026 lúc 07:28 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `colors_configuration`
--

CREATE TABLE `colors_configuration` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `configuration_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `colors_configuration`
--

INSERT INTO `colors_configuration` (`id`, `product_id`, `color_name`, `configuration_name`, `quantity`) VALUES
(5, 8, 'Trắng', '16GB', 96),
(6, 10, 'Đen', '8GB', 100),
(7, 10, 'Đen', '16GB', 150),
(8, 1, 'Trắng', '8GB', 96),
(9, 1, 'Trắng', '16GB', 143),
(10, 2, 'Đen', '8GB', 50),
(11, 2, 'Đen', '16GB', 78),
(12, 3, 'Đen', '8GB', 200),
(13, 3, 'Đen', '16GB', 199),
(14, 4, 'Đen', '8GB', 99),
(15, 4, 'Đen', '16GB', 99),
(16, 5, 'Đen', '8GB', 99),
(17, 5, 'Đen', '16GB', 86),
(18, 6, 'Đen', '8GB', 200),
(19, 6, 'Đen', '16GB', 298),
(20, 7, 'Đen', '8GB', 199);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `combo`
--

CREATE TABLE `combo` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` bigint(20) NOT NULL,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `combodetails`
--

CREATE TABLE `combodetails` (
  `id` bigint(20) NOT NULL,
  `combo_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comment`
--

CREATE TABLE `comment` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `image`
--

CREATE TABLE `image` (
  `id` bigint(20) NOT NULL,
  `path` varchar(500) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `sort_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `image`
--

INSERT INTO `image` (`id`, `path`, `product_id`, `sort_order`) VALUES
(1, 'uploads/1716967485_2910_2c4286fc14c5e733507849d1e2be8ca4.jpg', 1, 1),
(2, 'uploads/1716967485_2910_8d3b42a1ebd768abf173cd9a34336a18.jpg', 1, 2),
(3, 'uploads/1716967485_2910_53668ceb0bc3437c0f1d6237e384c6b2.jpg', 1, 3),
(4, 'uploads/1716967485_2910_aeeab91ee9f8f4570ed022ed81ea7769.jpg', 1, 4),
(5, 'uploads/1716967485_2910_b822d258243d8c8073067c02e7e44dc7.jpg', 1, 5),
(6, 'uploads/1722820400_2910_d6d2442a8264e20a8f4a42b0774a1bf2.jpg', 1, 6),
(7, 'uploads/1722496360_2975_1ccc6e3b0137948e0068ca7504095cf9.jpg', 2, 1),
(8, 'uploads/1722496360_2975_41bc28feb60774d22f9fcce81920c8cf.jpg', 2, 2),
(9, 'uploads/1722496360_2975_92b522bbe23c089ae1d89fd77a5174fe.jpg', 2, 3),
(10, 'uploads/1722496360_2975_f0c9aa2d646612bd73875b1562abd245.jpg', 2, 4),
(11, 'uploads/x0t3e052-1435-dell-inspiron-3535-ryzen-7-7730u-ram-16gb-ssd-512gb-15-6-fhd-touch-new.jpg', 2, 5),
(12, 'uploads/1730191360_3107_09c167400ee61fbe3f38899dab6db16b.jpg', 3, 1),
(13, 'uploads/1730191360_3107_bd7b7f42a83073251e2acaea0ffef0c7.jpg', 3, 2),
(14, 'uploads/1730191360_3107_d2ebfef15dc4e741d1eb64802003063d.jpg', 3, 3),
(15, 'uploads/1730191360_3107_e53e64b188598ad5c028e1daa78f5519.jpg', 3, 4),
(16, 'uploads/gawbulrj-1533-msi-cyborg-15-a13uc-861vn-core-i5-13420h-16gb-512gb-rtx-3050-4gb-15-6-inch-fhd-new.jpg', 3, 5),
(17, 'uploads/1698229938_2320_7fbd29d9e34860e9650058d4de333ef5.jpg', 4, 1),
(18, 'uploads/1698229938_2320_77a9d09aa9e9eadf5bcb26bdceb1c4ab.jpg', 4, 2),
(19, 'uploads/1698229938_2320_aae936497b2fa7eed76e699f5b392834.jpg', 4, 3),
(20, 'uploads/1698229938_2320_deca4ffff48ce78ed9830bde1691065f.jpg', 4, 4),
(21, 'uploads/1698229938_2320_ec7af73ad448370e7c6dabb0b2c6ceaf.jpg', 4, 5),
(22, 'uploads/og6zjhin-985-msi-gaming-thin-gf63-12ve-454vn-core-i5-12450h-16gb-512gb-15-6-fhd-rtx-4050-6gb-win-11-new.png', 4, 6),
(23, 'uploads/1723790945_2802_7ef79546de73d18763b847f2752a8ac1.jpg', 5, 1),
(24, 'uploads/1723790945_2802_633e3cfec53e99ff00fbd65cd98f7f14.jpg', 5, 2),
(25, 'uploads/1723790945_2802_1797cc48db0fa96018cef7d6214b70bd.jpg', 5, 3),
(26, 'uploads/1723790945_2802_98418c3e08b2a2edaa98a82dd5513ece.jpg', 5, 4),
(27, 'uploads/1723790945_2802_bb3cd1778c35cef1ec1468332f87b5a3.jpg', 5, 5),
(28, 'uploads/paa9puqb-1346-asus-zenbook-14-oled-q425ma-u71tb-intel-core-ultra-7-155h-16gb-1tb-14-fhd-intel-graphics-new.jpg', 5, 6),
(29, 'uploads/1700732794_2463_2f14b63c1f6a3790c8d71b3f38501349.jpg', 6, 1),
(30, 'uploads/1700732794_2463_5c6b4b18c6a2b61914c2b0d9acd8667c.jpg', 6, 2),
(31, 'uploads/1700732794_2463_82ec5ce8e9323c49c6ffd5bfafc87b7b.jpg', 6, 3),
(32, 'uploads/1700732794_2463_91009daadd30a43f09745d6d075e4cc7.jpg', 6, 4),
(33, 'uploads/1700732795_2463_307410f4088c6595e84e2869983b0f2a.jpg', 6, 5),
(34, 'uploads/1700732795_2463_b726ba5a188ef3c32b21f61213f2aa27.jpg', 6, 6),
(35, 'uploads/wv9654a0-1080-asus-tuf-gaming-f15-fx507zc4-hn074w-core-i5-12500h-ram-8gb-ssd-512gb-rtx-3050-4gb-15-6inch-fhd-new.jpg', 6, 7),
(36, 'uploads/9yqwvnsb-1092-macbook-air-m2-13inch-8gb-256gb-new-cpo.png', 7, 1),
(37, 'uploads/1700908071_2476_8d457a0d75a9f04b03b388dc07f75dba.jpg', 7, 2),
(38, 'uploads/1700908072_2476_466fa39a0a9d0742f3a43ded2b998a40.jpg', 7, 3),
(39, 'uploads/1700908072_2476_b3942ea4baba2547bf622377ca7f4c61.jpg', 7, 4),
(40, 'uploads/1700908072_2476_dcf1682fb2bc5951be34042abd5d4af4.jpg', 7, 5),
(41, 'uploads/1700908072_2476_fb7193debc079a2d8b34340588137aa4.jpg', 7, 6),
(42, 'uploads/1703240540_745_0a0fc4c1c97216790926b12864143253.png', 8, 1),
(43, 'uploads/1703240540_745_8dcbe45691a78b5e3dd0b2c9af0d540c.png', 8, 2),
(44, 'uploads/1703240540_745_9e62976937af03a6054752127c1bd9d6.png', 8, 3),
(45, 'uploads/1703240540_745_19e91bb551c502377f3ee1197b1c4351.png', 8, 4),
(46, 'uploads/1703240540_745_b65b1f50a917c99b70201350d7e75dde.png', 8, 5),
(47, 'uploads/1703240540_745_f2a8fc7c43492908effd1ee451f1a3f9.png', 8, 6),
(48, 'uploads/1703240540_745_f98d7be5f2930bc4aef3d0721ad86d43.png', 8, 7),
(49, 'uploads/vxmskkqs-141-macbook-pro-13-m1-16gb-256gb-like-new.jpg', 8, 8),
(50, 'uploads/e1404fa-1.png', 10, 1),
(51, 'uploads/e1404fa-2.png', 10, 2),
(52, 'uploads/e1404fa-3.png', 10, 3),
(53, 'uploads/e1404fa-4.png', 10, 4),
(54, 'uploads/e1404fa-5.png', 10, 5),
(55, 'uploads/e1404fa-6.png', 10, 6),
(56, 'uploads/e1404fa-7.png', 10, 7),
(57, 'uploads/e1404fa-8.png', 10, 8);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order`
--

CREATE TABLE `order` (
  `id` bigint(20) NOT NULL,
  `customer_id` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order`
--

INSERT INTO `order` (`id`, `customer_id`, `datetime`) VALUES
(14, 2, '2024-12-27 18:18:45'),
(15, 2, '2024-12-27 18:31:33'),
(16, 4, '2024-12-27 18:34:23'),
(17, 4, '2024-12-27 18:39:53'),
(18, 3, '2024-12-27 18:51:35'),
(19, 2, '2024-12-27 18:59:27'),
(20, 4, '2024-12-27 19:00:17'),
(21, 4, '2024-12-28 12:48:49'),
(22, 2, '2024-12-28 15:42:35'),
(23, 2, '2024-12-30 04:14:49'),
(24, 8, '2025-12-30 03:27:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_details`
--

CREATE TABLE `order_details` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` bigint(20) NOT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `color_name` text DEFAULT NULL,
  `configuration_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`, `status`, `color_name`, `configuration_name`) VALUES
(27, 14, 1, 1, 15000000, 'paid', 'Trắng', '8GB'),
(28, 15, 1, 1, 15000000, 'paid', 'Trắng', '16GB'),
(29, 16, 8, 1, 10800000, 'paid', 'Trắng', '16GB'),
(30, 17, 1, 1, 15000000, 'paid', 'Trắng', '16GB'),
(31, 17, 1, 1, 15000000, 'paid', 'Trắng', '8GB'),
(32, 18, 1, 1, 15000000, 'paid', 'Trắng', '16GB'),
(33, 19, 1, 1, 15000000, 'pending', 'Trắng', '16GB'),
(34, 19, 2, 1, 16000000, 'pending', 'Đen', '8GB'),
(35, 19, 4, 1, 19000000, 'paid', 'Đen', '8GB'),
(36, 19, 5, 1, 21000000, 'paid', 'Đen', '16GB'),
(37, 20, 8, 1, 10800000, 'paid', 'Trắng', '16GB'),
(38, 20, 7, 1, 20000000, 'paid', 'Đen', '8GB'),
(39, 21, 3, 1, 19000000, 'pending', 'Đen', '16GB'),
(40, 22, 1, 1, 15000000, 'paid', 'Trắng', '8GB'),
(41, 23, 8, 2, 10800000, 'paid', 'Trắng', '16GB'),
(42, 24, 2, 2, 16000000, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `price`
--

CREATE TABLE `price` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `price` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL,
  `temporary_price` bigint(20) DEFAULT NULL COMMENT 'Giá tạm thời trong thời gian khuyến mãi',
  `discount_start` datetime DEFAULT NULL COMMENT 'Thời gian bắt đầu giảm giá',
  `discount_end` datetime DEFAULT NULL COMMENT 'Thời gian kết thúc giảm giá'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `price`
--

INSERT INTO `price` (`id`, `product_id`, `price`, `datetime`, `temporary_price`, `discount_start`, `discount_end`) VALUES
(1, 1, 15000000, '2024-12-22 17:43:06', 13500000, '2024-12-22 11:59:06', '2024-12-22 12:59:06'),
(2, 2, 16000000, '2024-12-22 17:46:10', NULL, NULL, NULL),
(3, 3, 19000000, '2024-12-22 17:48:06', NULL, NULL, NULL),
(4, 4, 19000000, '2024-12-22 17:49:54', 13300000, '2024-12-22 11:59:18', '2024-12-23 11:59:18'),
(5, 5, 21000000, '2024-12-22 17:52:07', NULL, NULL, NULL),
(6, 6, 25000000, '2024-12-22 17:53:38', NULL, NULL, NULL),
(7, 7, 20000000, '2024-12-22 17:55:31', NULL, NULL, NULL),
(8, 8, 12000000, '2024-12-27 21:28:02', 10800000, '2024-12-22 11:59:34', '2025-01-22 11:59:34'),
(9, 10, 12500000, '2024-12-25 11:21:51', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product`
--

CREATE TABLE `product` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `unit_id` bigint(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product`
--

INSERT INTO `product` (`id`, `name`, `description`, `unit_id`) VALUES
(1, 'Dell Inspiron 14 7440 2in1 2024', 'Bộ vi xử lý	Intel Core 5 120U, 10 nhân (2P + 8E) / 12 luồng, P-core 1.4 / 5.0GHz, E-core 900MHz / 3.8GHz, 12MB\r\nBộ nhớ trong (RAM)	8GB DDR5 5200MHz\r\nỔ cứng	512GB M.2 PCIe NVMe SSD\r\nCard màn hình	Intel Iris Xe Graphics\r\nMàn hình	14.0inch FHD+ (1920 x 1200) 60Hz,250 nits\r\nKết nối	\r\n2 x USB 3.2 Gen 1 ports\r\n\r\n2 x USB 3.2 Gen 2 (3.1 Gen 2) Type-C ports quantity\r\n\r\n1 x HDMI 1.4\r\n\r\nPin	4Cell \r\nPower Adapter	65 watts\r\nTrọng lượng	1,75 kg, 314 x 226,2 x 15,9 mm\r\nVỏ	Color: Ice Blue', 1),
(2, 'Dell Inspiron 3535', ' CPU	AMD Ryzen™ 7 – 7730U (Up to 4.5GHz, 20 MB total cache, 8 Cores, 16 Threads)\r\n Ram	16GB RAM DDR4 Bus 3200MHz\r\n Ổ cứng	512GB SSD M2 NVMe PCIe\r\n Card màn hình:	AMD Radeon™ Graphics\r\n Màn hình	15.6″ FHD (1920 x 1080) WVA, Multi-Touch, Anti-Glare, 250 nit, Narrow Border, LED-Backlit\r\n Cổng giao tiếp	\r\nUSB-A\r\nUSB-C\r\nHDMI\r\njack 3.5mm\r\nSD card reader\r\n Kết nối không dây 	\r\nBluetooth 5.2\r\nWi-Fi 6E (802.11ax)\r\n Webcam	HD webcam\r\n Hệ điều hành	Windows 11\r\n Trọng lượng	1.63 kg\r\n Kích thước	358 x 235 x ', 1),
(3, 'MSI Cyborg 15 A13UC 861VN', 'CPU\r\n\r\nIntel Core i5-13420H (3.4GHz~4.6GHz, 8 Cores 12 Threads )\r\n\r\nRAM\r\n\r\n16GB (2 x 8GB) DDR5 5200MHz (2x SO-DIMM socket, up to 64GB SDRAM)\r\n\r\nỔ cứng\r\n\r\n512GB NVMe PCIe SSD Gen4x4 (1 slot)\r\n\r\nVGA\r\n\r\nNVIDIA® GeForce RTX™ 3050 Laptop GPU, 4GB GDDR6 Up to 1172.5MHz Boost Clock 45W Maximum Graphics Power with Dynamic Boost.\r\n\r\nMàn hình\r\n\r\n15.6\" FHD (1920x1080), 144Hz, IPS-Level, 45% NTSC, 65% sRGB\r\n\r\nCổng giao tiếp\r\n\r\n1x Type-C (USB3.2 Gen1 / DP)\r\n\r\n2x Type-A USB3.2 Gen1 1x HDMI™ 2.1 (4K @ 60Hz)\r\n\r', 1),
(4, 'MSI Gaming Thin GF63 12VE-454VN', 'Màn hình	15.6 inch, 1920 x 1080 Pixels, IPS, 144, IPS FHD\r\nCPU	Intel, Core i5, 12450H\r\nRAM	16 GB, DDR4, 3200 MHz\r\nỔ cứng	SSD 512 GB\r\nĐồ họa	NVIDIA GeForce RTX 4050 6GB GDDR6; Intel Iris Xe Graphics\r\nHệ điều hành	Windows 11 Home\r\nTrọng lượng	1.86 kg\r\nKích thước	359 x 254 x 21.7 mm\r\nXuất xứ	Trung Quốc\r\nNăm ra mắt	2023\r\n', 1),
(5, 'Asus Zenbook 14 OLED Q425MA-U71TB', 'CPU	Intel® Core™ Ultra 7 Processor 155H 1.4 GHz (24MB Cache, up to 4.8 GHz, 16 cores, 22 Threads); Intel® AI Boost NPU\r\nRAM	16GB LPDDR5X 7467MHz\r\nỔ cứng	1TB M.2 PCIe Gen 4 NVMe SSD\r\nCard VGA	Intel® Arc™ Graphics\r\nMàn hình	\r\n14.0-inch, FHD (1920 x 1200) OLED 16:10 aspect ratio, 0.2ms response time, 60Hz refresh rate, 500nits HDR peak brightness, 100% DCI-P3 color gamut, 1,000,000:1, 1.07 billion colors, Glossy display, 70% less harmful blue light, SGS Eye Care Display, Touch screen,\r\n\r\nCamera	FHD', 1),
(6, 'Asus TUF Gaming F15 FX507ZC4-HN074W ', 'Công nghệ CPU\r\n\r\nIntel® Core™ i5-12500H\r\n\r\nBộ nhớ trong (RAM)\r\n\r\nRAM\r\n\r\n8GB \r\n\r\nLoại RAM\r\n\r\nDDR4\r\n\r\nTốc độ Bus RAM\r\n\r\n3200Mhz\r\n\r\nSố khe cắm\r\n\r\n2 khe\r\n\r\nHỗ trợ RAM tối đa\r\n\r\nNâng cấp tối đa 32GB\r\n\r\nỔ cứng \r\n\r\nDung lượng\r\n\r\n512GB PCIe® 3.0 NVMe™ M.2 SSD\r\n\r\nMàn hình\r\n\r\nKích thước màn hình\r\n\r\n15.6-inch  \r\n\r\nĐộ phân giải\r\n\r\nFHD (1920 x 1080)\r\n\r\nTần số quét\r\n\r\n144Hz\r\n\r\nCông nghệ màn hình\r\n\r\n 16:9, 144Hz, Value IPS-level, NTSC 45%, SRGB 62.5%, anti-glare display\r\n\r\nĐồ Họa (VGA) \r\n\r\nCard màn hình\r\n\r\n NV', 1),
(7, 'Macbook Air M2', 'CPU	8 nhân GPU, 16 nhân Neural Engine\r\nRAM	8GB\r\nSSD	256GB \r\nScreen	13.6 inches\r\nCông nghệ màn hình	Liquid Retina Display\r\nCổng kết nối	\r\n2 x Thunderbolt 3\r\nJack tai nghe 3.5 mm\r\nMagSafe 3\r\n\r\nThời lượng pin	52,6 Wh\r\nHệ điều hành\r\n\r\nMacOS\r\nTrọng lượng\r\n\r\n1.27 kg\r\nTình trạng\r\n\r\nNew - CPO ', 1),
(8, 'MacBook Pro M1', 'Màn hình	13.3 inch, 2560 x 1600 Pixels, IPS, IPS LCD LED Backlit, True Tone\r\nCPU	Apple, M1\r\nỔ cứng	SSD 256 GB\r\nĐồ họa	Apple M1 GPU 8 nhân\r\nHệ điều hành	macOS 12\r\nTrọng lượng	1.4 kg\r\nKích thước	304.1 x 212.4 x 15.6 mm', 1),
(10, 'Laptop ASUS VivoBook Go 14 E1404FA-NK177W', 'Kết nối & Tương thích\r\nCổng giao tiếp\r\nLPDDR5\r\nBộ vi xử lý\r\nSố nhân\r\n4\r\nSố luồng\r\n8\r\nBộ nhớ đệm\r\n4MB\r\nCông nghệ CPU\r\nAMD Ryzen 5\r\nXung nhịp tối đa\r\n4.3 GHz\r\nSố hiệu CPU\r\nR5 - 7520U\r\nXung nhịp cơ bản\r\n2.8 GHz\r\nĐồ họa và Âm thanh\r\nCard on-board\r\nAMD Radeon Graphics\r\nCard đồ hoạ rời\r\nGPU tích hợp\r\nCông nghệ âm thanh\r\nSonicMaster\r\nLoa tích hợp\r\nMicrô mảng tích hợp\r\nBộ nhớ RAM, Ổ cứng\r\nKhả năng nâng cấp ổ cứng\r\nNâng cấp bộ nhớ mặc định lên dung lượng cao hơn.\r\nRAM\r\n16GB\r\nỔ cứng mặc định\r\n512GB SSD\r\nT', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `receipt`
--

CREATE TABLE `receipt` (
  `id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `receipt`
--

INSERT INTO `receipt` (`id`, `supplier_id`, `datetime`) VALUES
(1, 1, '2024-12-12 00:00:00'),
(2, 1, '2024-02-11 00:00:00'),
(3, 2, '2023-12-11 00:00:00'),
(4, 2, '2024-04-12 00:00:00'),
(5, 3, '2024-07-05 00:00:00'),
(6, 3, '2024-12-22 00:00:00'),
(7, 4, '2024-01-14 00:00:00'),
(8, 4, '2024-01-01 00:00:00'),
(9, 3, '2022-12-22 00:00:00'),
(10, 2, '2022-02-22 00:00:00'),
(11, 1, '0000-00-00 00:00:00'),
(12, 1, '2022-02-02 00:00:00'),
(13, 1, '2022-02-22 00:00:00'),
(14, 1, '2022-11-11 00:00:00'),
(15, 1, '2022-11-11 00:00:00'),
(16, 1, '2022-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `receipt_details`
--

CREATE TABLE `receipt_details` (
  `id` bigint(20) NOT NULL,
  `receipt_id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `price` bigint(20) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `receipt_details`
--

INSERT INTO `receipt_details` (`id`, `receipt_id`, `product_id`, `price`, `quantity`) VALUES
(1, 1, 1, 13000000, 239),
(2, 2, 2, 13000000, 128),
(3, 3, 3, 16000000, 399),
(4, 4, 4, 15000000, 198),
(5, 5, 5, 17000000, 185),
(6, 6, 6, 19000000, 498),
(7, 7, 7, 17000000, 199),
(8, 8, 8, 10000000, 96),
(9, 9, 10, 10500000, 250);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review_likes`
--

CREATE TABLE `review_likes` (
  `review_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review_replies`
--

CREATE TABLE `review_replies` (
  `id` bigint(20) NOT NULL,
  `review_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role`
--

CREATE TABLE `role` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `role`
--

INSERT INTO `role` (`id`, `name`, `description`) VALUES
(1, 'admin', 'admin'),
(2, 'customer', 'customer');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `supplier`
--

CREATE TABLE `supplier` (
  `id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `supplier`
--

INSERT INTO `supplier` (`id`, `name`, `description`) VALUES
(1, 'Dell', 'Cung cấp laptop Dell'),
(2, 'MSI', 'Cung Cấp Laptop MSI'),
(3, 'ASUS', 'Cung Cấp Laptop ASUS'),
(4, 'MAC', 'Cung cấp Macbook');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `unit`
--

CREATE TABLE `unit` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `unit`
--

INSERT INTO `unit` (`id`, `name`, `description`) VALUES
(1, 'Máy', 'Máy');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `id` bigint(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(500) NOT NULL,
  `familyname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `familyname`, `firstname`, `phone`, `email`) VALUES
(1, 'admin', '$2y$10$vkPDNzKRPXEcJB/ACWFN1.mAV5ph6W00P2CAFy6T5lX9kCzS4Ep4K', 'admin', '123', '0835412193', 'admin123@gmail.com'),
(2, 'Quang2202', '$2y$10$T.I/qeTiBlemFKW7QGkmP.Mlgul1OBOrk723BdYRoeW6wmtHb5y5a', 'Võ', 'Quang', '0835412192', 'vodangquang22022003@gmail.com'),
(3, 'VTK', '$2y$10$ehGGS5jIebLXZ9ihH4AQE.nmEegabIZjue/GCX4pNwdysXWt/3NqG', 'cus', 'tomer', '0835412192', 'VTK@gmail.com'),
(4, 'VNT', '$2y$10$1cU5quNxfItauFQYQ6CeYuQ/A9FmyWJ8TdpMAAN1d4YMoJfNuIXZa', 'Võ', 'Trường Không', '0835412176', 'VNT@gmail.com'),
(6, 'customer', '$2y$10$xmFGgVdVkyZ2vlmFHeW5fue.ZVNHMz7CJxFBZhz29U3SSCzp6wUSm', 'cus', 'tomer', '0835412777', 'century0801gh90@gmail.com'),
(7, 'customer123', '$2y$10$kLo.u8PVFg8YYTg2OgCR5OPolkO20phB2m4Qt29vuENmgHam0IE22', 'cus', 'tomer', '0835412144', 'customer123@gmail.com'),
(8, 'daiduc', '$2y$10$OKwiSJZ8u3c2/Mp.Z2QnoOlSAjcCLx583HlxWOzzQHFwdok3bSuLG', 'Trần', 'Đức', '0393340406', 'daiducka123@gmail.com');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_role`
--

CREATE TABLE `user_role` (
  `id` bigint(20) NOT NULL,
  `role_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user_role`
--

INSERT INTO `user_role` (`id`, `role_id`, `user_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 2, 3),
(4, 2, 4),
(5, 2, 6),
(6, 2, 7),
(7, 2, 8);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `colors_configuration`
--
ALTER TABLE `colors_configuration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `combo`
--
ALTER TABLE `combo`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `combodetails`
--
ALTER TABLE `combodetails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `combo_id` (`combo_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Chỉ mục cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `price`
--
ALTER TABLE `price`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Chỉ mục cho bảng `receipt`
--
ALTER TABLE `receipt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Chỉ mục cho bảng `receipt_details`
--
ALTER TABLE `receipt_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `receipt_id` (`receipt_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `review_likes`
--
ALTER TABLE `review_likes`
  ADD PRIMARY KEY (`review_id`,`user_id`),
  ADD KEY `idx_review_id` (`review_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `review_replies`
--
ALTER TABLE `review_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_review_id` (`review_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `colors_configuration`
--
ALTER TABLE `colors_configuration`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `combo`
--
ALTER TABLE `combo`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `combodetails`
--
ALTER TABLE `combodetails`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `comment`
--
ALTER TABLE `comment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `image`
--
ALTER TABLE `image`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT cho bảng `order`
--
ALTER TABLE `order`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT cho bảng `price`
--
ALTER TABLE `price`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `product`
--
ALTER TABLE `product`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `receipt`
--
ALTER TABLE `receipt`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `receipt_details`
--
ALTER TABLE `receipt_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `review_replies`
--
ALTER TABLE `review_replies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `role`
--
ALTER TABLE `role`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `unit`
--
ALTER TABLE `unit`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `user_role`
--
ALTER TABLE `user_role`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `colors_configuration`
--
ALTER TABLE `colors_configuration`
  ADD CONSTRAINT `colors_configuration_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Các ràng buộc cho bảng `combodetails`
--
ALTER TABLE `combodetails`
  ADD CONSTRAINT `combodetails_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `combo` (`id`),
  ADD CONSTRAINT `combodetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Các ràng buộc cho bảng `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Các ràng buộc cho bảng `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `image_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `user` (`id`);

--
-- Các ràng buộc cho bảng `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Các ràng buộc cho bảng `price`
--
ALTER TABLE `price`
  ADD CONSTRAINT `price_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `receipt`
--
ALTER TABLE `receipt`
  ADD CONSTRAINT `receipt_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`);

--
-- Các ràng buộc cho bảng `receipt_details`
--
ALTER TABLE `receipt_details`
  ADD CONSTRAINT `receipt_details_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `receipt` (`id`),
  ADD CONSTRAINT `receipt_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `review_likes`
--
ALTER TABLE `review_likes`
  ADD CONSTRAINT `review_likes_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `review_replies`
--
ALTER TABLE `review_replies`
  ADD CONSTRAINT `review_replies_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
