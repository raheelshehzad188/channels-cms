-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2020 at 03:33 PM
-- Server version: 10.4.8-MariaDB
-- PHP Version: 7.2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `knet`
--

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `authorID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `author`
--

INSERT INTO `author` (`authorID`, `userID`, `create_at`, `name`, `status`) VALUES
(1, 1, '2019-10-11 02:37:03', 'cgvbv', 0);

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `bookID` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `title` varchar(200) NOT NULL,
  `authorID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  `isbnNO` varchar(100) NOT NULL,
  `discription` text NOT NULL,
  `coverImg` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`bookID`, `uid`, `create_at`, `title`, `authorID`, `typeID`, `isbnNO`, `discription`, `coverImg`, `status`) VALUES
(2, 1, '2019-10-11 02:37:03', 'Edit', 1, 0, 'cgfxfv', 'Updated', 14, 0),
(3, 1, '2019-10-11 02:37:03', 'xfvfd', 1, 1, 'cgfxfv', 'cxzcxxc', 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `book_to_genre`
--

CREATE TABLE `book_to_genre` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `book_to_genre`
--

INSERT INTO `book_to_genre` (`id`, `book_id`, `genre_id`) VALUES
(14, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `book_to_lang`
--

CREATE TABLE `book_to_lang` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `list_id` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `book_to_lang`
--

INSERT INTO `book_to_lang` (`id`, `book_id`, `list_id`) VALUES
(8, 2, 'ace');

-- --------------------------------------------------------

--
-- Table structure for table `book_to_tag`
--

CREATE TABLE `book_to_tag` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `book_to_tag`
--

INSERT INTO `book_to_tag` (`id`, `book_id`, `tag_id`) VALUES
(4, 2, 1),
(5, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genreID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genreID`, `userID`, `create_at`, `name`, `status`) VALUES
(1, 1, '2019-10-08 18:40:48', 'genere', 0),
(2, 1, '2019-10-08 18:41:40', 'tuytyu', 0);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `groupID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `groupImage` int(11) NOT NULL,
  `description` text NOT NULL,
  `grouptypeID` int(11) NOT NULL,
  `langID` varchar(100) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`groupID`, `userID`, `create_at`, `name`, `groupImage`, `description`, `grouptypeID`, `langID`, `status`) VALUES
(1, 1, '2019-10-12 23:07:03', 'gROUP nAME', 24, '                                                                                                                                                                                                                                                                                                      fghdgffgdf                                                                                                                                                                                                                                                                                        ', 1, 'ain', 0);

-- --------------------------------------------------------

--
-- Table structure for table `group_type`
--

CREATE TABLE `group_type` (
  `gtID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `group_type`
--

INSERT INTO `group_type` (`gtID`, `userID`, `create_at`, `name`, `type`, `status`) VALUES
(1, 1, '2019-10-12 02:45:19', 'Primery', 0, 1),
(2, 1, '2019-10-12 02:45:28', 'Secendery', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `linkID` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `request_data` text NOT NULL,
  `amount` double NOT NULL,
  `step` int(11) NOT NULL,
  `creat_at` datetime NOT NULL DEFAULT current_timestamp(),
  `fat_response` text NOT NULL,
  `payment_link` int(11) NOT NULL,
  `trans_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`) VALUES
(1, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 0, '2020-01-23 19:01:20', '', 0, 0),
(2, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 1, '2020-01-23 19:02:23', '', 0, 0),
(3, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 2, '2020-01-23 19:09:28', 'a:7:{s:2:\"Id\";i:100632;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/PqHZr\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063235&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 0),
(4, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 2, '2020-01-23 19:10:25', 'a:7:{s:2:\"Id\";i:100634;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/duuyF\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063435&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 100634),
(5, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-23 19:12:36', 'a:7:{s:2:\"Id\";i:100637;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/0J55h\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210063735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 100637),
(6, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-23 19:15:16', 'a:7:{s:2:\"Id\";i:100640;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/7d4JQ\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064034&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 100640),
(7, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 2, '2020-01-23 19:16:41', 'a:7:{s:2:\"Id\";i:100641;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/N4mLr\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064135&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 0),
(8, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-23 19:17:06', 'a:7:{s:2:\"Id\";i:100643;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/c6lA9\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064335&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 100643),
(9, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-23 19:17:36', 'a:7:{s:2:\"Id\";i:100645;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/Zpndq\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 100645),
(10, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-23 19:21:21', 'a:7:{s:2:\"Id\";i:100647;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/07ULW\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210064735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 0, 100647);

-- --------------------------------------------------------

--
-- Table structure for table `list`
--

CREATE TABLE `list` (
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `list`
--

INSERT INTO `list` (`id`, `value`) VALUES
('aa', 'Afar'),
('ab', 'Abkhazian'),
('ace', 'Achinese'),
('ach', 'Acoli'),
('ada', 'Adangme'),
('ady', 'Adyghe'),
('ae', 'Avestan'),
('aeb', 'Tunisian Arabic'),
('af', 'Afrikaans'),
('afh', 'Afrihili'),
('agq', 'Aghem'),
('ain', 'Ainu'),
('ak', 'Akan'),
('akk', 'Akkadian'),
('akz', 'Alabama'),
('ale', 'Aleut'),
('aln', 'Gheg Albanian'),
('alt', 'Southern Altai'),
('am', 'Amarik'),
('an', 'Aragonese'),
('ang', 'Old English'),
('anp', 'Angika'),
('ar', 'Arabik'),
('ar_001', 'Modern Standard Arabic'),
('arc', 'Aramaic'),
('arn', 'Mapuche'),
('aro', 'Araona'),
('arp', 'Arapaho'),
('arq', 'Algerian Arabic'),
('arw', 'Arawak'),
('ary', 'Moroccan Arabic'),
('arz', 'Egyptian Arabic'),
('as', 'Assamese'),
('asa', 'Asu'),
('ase', 'American Sign Language'),
('ast', 'Asturian'),
('av', 'Avaric'),
('avk', 'Kotava'),
('awa', 'Awadhi'),
('ay', 'Aymara'),
('az', 'Azerbaijani'),
('azb', 'South Azerbaijani'),
('ba', 'Bashkir'),
('bal', 'Baluchi'),
('ban', 'Balinese'),
('bar', 'Bavarian'),
('bas', 'Basaa'),
('bax', 'Bamun'),
('bbc', 'Batak Toba'),
('bbj', 'Ghomala'),
('be', 'Belarus kasa'),
('bej', 'Beja'),
('bem', 'Bemba'),
('bew', 'Betawi'),
('bez', 'Bena'),
('bfd', 'Bafut'),
('bfq', 'Badaga'),
('bg', 'Bɔlgeria kasa'),
('bho', 'Bhojpuri'),
('bi', 'Bislama'),
('bik', 'Bikol'),
('bin', 'Bini'),
('bjn', 'Banjar'),
('bkm', 'Kom'),
('bla', 'Siksika'),
('bm', 'Bambara'),
('bn', 'Bengali kasa'),
('bo', 'Tibetan'),
('bpy', 'Bishnupriya'),
('bqi', 'Bakhtiari'),
('br', 'Breton'),
('bra', 'Braj'),
('brh', 'Brahui'),
('brx', 'Bodo'),
('bs', 'Bosnian'),
('bss', 'Akoose'),
('bua', 'Buriat'),
('bug', 'Buginese'),
('bum', 'Bulu'),
('byn', 'Blin'),
('byv', 'Medumba'),
('ca', 'Catalan'),
('cad', 'Caddo'),
('car', 'Carib'),
('cay', 'Cayuga'),
('cch', 'Atsam'),
('ce', 'Chechen'),
('ceb', 'Cebuano'),
('cgg', 'Chiga'),
('ch', 'Chamorro'),
('chb', 'Chibcha'),
('chg', 'Chagatai'),
('chk', 'Chuukese'),
('chm', 'Mari'),
('chn', 'Chinook Jargon'),
('cho', 'Choctaw'),
('chp', 'Chipewyan'),
('chr', 'Cherokee'),
('chy', 'Cheyenne'),
('ckb', 'Central Kurdish'),
('co', 'Corsican'),
('cop', 'Coptic'),
('cps', 'Capiznon'),
('cr', 'Cree'),
('crh', 'Crimean Turkish'),
('cs', 'Kyɛk kasa'),
('csb', 'Kashubian'),
('cu', 'Church Slavic'),
('cv', 'Chuvash'),
('cy', 'Welsh'),
('da', 'Danish'),
('dak', 'Dakota'),
('dar', 'Dargwa'),
('dav', 'Taita'),
('de', 'Gyaaman'),
('de_AT', 'Austrian German'),
('de_CH', 'Swiss High German'),
('del', 'Delaware'),
('den', 'Slave'),
('dgr', 'Dogrib'),
('din', 'Dinka'),
('dje', 'Zarma'),
('doi', 'Dogri'),
('dsb', 'Lower Sorbian'),
('dtp', 'Central Dusun'),
('dua', 'Duala'),
('dum', 'Middle Dutch'),
('dv', 'Divehi'),
('dyo', 'Jola-Fonyi'),
('dyu', 'Dyula'),
('dz', 'Dzongkha'),
('dzg', 'Dazaga'),
('ebu', 'Embu'),
('ee', 'Ewe'),
('efi', 'Efik'),
('egl', 'Emilian'),
('egy', 'Ancient Egyptian'),
('eka', 'Ekajuk'),
('el', 'Greek kasa'),
('elx', 'Elamite'),
('en', 'Borɔfo'),
('en_AU', 'Australian English'),
('en_CA', 'Canadian English'),
('en_GB', 'British English'),
('en_US', 'American English'),
('enm', 'Middle English'),
('eo', 'Esperanto'),
('es', 'Spain kasa'),
('es_419', 'Latin American Spanish'),
('es_ES', 'European Spanish'),
('es_MX', 'Mexican Spanish'),
('esu', 'Central Yupik'),
('et', 'Estonian'),
('eu', 'Basque'),
('ewo', 'Ewondo'),
('ext', 'Extremaduran'),
('fa', 'Pɛɛhyia kasa'),
('fan', 'Fang'),
('fat', 'Fanti'),
('ff', 'Fulah'),
('fi', 'Finnish'),
('fil', 'Filipino'),
('fit', 'Tornedalen Finnish'),
('fj', 'Fijian'),
('fo', 'Faroese'),
('fon', 'Fon'),
('fr', 'Frɛnkye'),
('fr_CA', 'Canadian French'),
('fr_CH', 'Swiss French'),
('frc', 'Cajun French'),
('frm', 'Middle French'),
('fro', 'Old French'),
('frp', 'Arpitan'),
('frr', 'Northern Frisian'),
('frs', 'Eastern Frisian'),
('fur', 'Friulian'),
('fy', 'Western Frisian'),
('ga', 'Irish'),
('gaa', 'Ga'),
('gag', 'Gagauz'),
('gan', 'Gan Chinese'),
('gay', 'Gayo'),
('gba', 'Gbaya'),
('gbz', 'Zoroastrian Dari'),
('gd', 'Scottish Gaelic'),
('gez', 'Geez'),
('gil', 'Gilbertese'),
('gl', 'Galician'),
('glk', 'Gilaki'),
('gmh', 'Middle High German'),
('gn', 'Guarani'),
('goh', 'Old High German'),
('gom', 'Goan Konkani'),
('gon', 'Gondi'),
('gor', 'Gorontalo'),
('got', 'Gothic'),
('grb', 'Grebo'),
('grc', 'Ancient Greek'),
('gsw', 'Swiss German'),
('gu', 'Gujarati'),
('guc', 'Wayuu'),
('gur', 'Frafra'),
('guz', 'Gusii'),
('gv', 'Manx'),
('gwi', 'Gwichʼin'),
('ha', 'Hausa'),
('hai', 'Haida'),
('hak', 'Hakka Chinese'),
('haw', 'Hawaiian'),
('he', 'Hebrew'),
('hi', 'Hindi'),
('hif', 'Fiji Hindi'),
('hil', 'Hiligaynon'),
('hit', 'Hittite'),
('hmn', 'Hmong'),
('ho', 'Hiri Motu'),
('hr', 'Croatian'),
('hsb', 'Upper Sorbian'),
('hsn', 'Xiang Chinese'),
('ht', 'Haitian'),
('hu', 'Hangri kasa'),
('hup', 'Hupa'),
('hy', 'Armenian'),
('hz', 'Herero'),
('ia', 'Interlingua'),
('iba', 'Iban'),
('ibb', 'Ibibio'),
('id', 'Indonihyia kasa'),
('ie', 'Interlingue'),
('ig', 'Igbo'),
('ii', 'Sichuan Yi'),
('ik', 'Inupiaq'),
('ilo', 'Iloko'),
('inh', 'Ingush'),
('io', 'Ido'),
('is', 'Icelandic'),
('it', 'Italy kasa'),
('iu', 'Inuktitut'),
('izh', 'Ingrian'),
('ja', 'Gyapan kasa'),
('jam', 'Jamaican Creole English'),
('jbo', 'Lojban'),
('jgo', 'Ngomba'),
('jmc', 'Machame'),
('jpr', 'Judeo-Persian'),
('jrb', 'Judeo-Arabic'),
('jut', 'Jutish'),
('jv', 'Gyabanis kasa'),
('ka', 'Georgian'),
('kaa', 'Kara-Kalpak'),
('kab', 'Kabyle'),
('kac', 'Kachin'),
('kaj', 'Jju'),
('kam', 'Kamba'),
('kaw', 'Kawi'),
('kbd', 'Kabardian'),
('kbl', 'Kanembu'),
('kcg', 'Tyap'),
('kde', 'Makonde'),
('kea', 'Kabuverdianu'),
('ken', 'Kenyang'),
('kfo', 'Koro'),
('kg', 'Kongo'),
('kgp', 'Kaingang'),
('kha', 'Khasi'),
('kho', 'Khotanese'),
('khq', 'Koyra Chiini'),
('khw', 'Khowar'),
('ki', 'Kikuyu'),
('kiu', 'Kirmanjki'),
('kj', 'Kuanyama'),
('kk', 'Kazakh'),
('kkj', 'Kako'),
('kl', 'Kalaallisut'),
('kln', 'Kalenjin'),
('km', 'Kambodia kasa'),
('kmb', 'Kimbundu'),
('kn', 'Kannada'),
('ko', 'Korea kasa'),
('koi', 'Komi-Permyak'),
('kok', 'Konkani'),
('kos', 'Kosraean'),
('kpe', 'Kpelle'),
('kr', 'Kanuri'),
('krc', 'Karachay-Balkar'),
('kri', 'Krio'),
('krj', 'Kinaray-a'),
('krl', 'Karelian'),
('kru', 'Kurukh'),
('ks', 'Kashmiri'),
('ksb', 'Shambala'),
('ksf', 'Bafia'),
('ksh', 'Colognian'),
('ku', 'Kurdish'),
('kum', 'Kumyk'),
('kut', 'Kutenai'),
('kv', 'Komi'),
('kw', 'Cornish'),
('ky', 'Kyrgyz'),
('la', 'Latin'),
('lad', 'Ladino'),
('lag', 'Langi'),
('lah', 'Lahnda'),
('lam', 'Lamba'),
('lb', 'Luxembourgish'),
('lez', 'Lezghian'),
('lfn', 'Lingua Franca Nova'),
('lg', 'Ganda'),
('li', 'Limburgish'),
('lij', 'Ligurian'),
('liv', 'Livonian'),
('lkt', 'Lakota'),
('lmo', 'Lombard'),
('ln', 'Lingala'),
('lo', 'Lao'),
('lol', 'Mongo'),
('loz', 'Lozi'),
('lt', 'Lithuanian'),
('ltg', 'Latgalian'),
('lu', 'Luba-Katanga'),
('lua', 'Luba-Lulua'),
('lui', 'Luiseno'),
('lun', 'Lunda'),
('luo', 'Luo'),
('lus', 'Mizo'),
('luy', 'Luyia'),
('lv', 'Latvian'),
('lzh', 'Literary Chinese'),
('lzz', 'Laz'),
('mad', 'Madurese'),
('maf', 'Mafa'),
('mag', 'Magahi'),
('mai', 'Maithili'),
('mak', 'Makasar'),
('man', 'Mandingo'),
('mas', 'Masai'),
('mde', 'Maba'),
('mdf', 'Moksha'),
('mdr', 'Mandar'),
('men', 'Mende'),
('mer', 'Meru'),
('mfe', 'Morisyen'),
('mg', 'Malagasy'),
('mga', 'Middle Irish'),
('mgh', 'Makhuwa-Meetto'),
('mgo', 'Metaʼ'),
('mh', 'Marshallese'),
('mi', 'Maori'),
('mic', 'Micmac'),
('min', 'Minangkabau'),
('mk', 'Macedonian'),
('ml', 'Malayalam'),
('mn', 'Mongolian'),
('mnc', 'Manchu'),
('mni', 'Manipuri'),
('moh', 'Mohawk'),
('mos', 'Mossi'),
('mr', 'Marathi'),
('mrj', 'Western Mari'),
('ms', 'Malay kasa'),
('mt', 'Maltese'),
('mua', 'Mundang'),
('mul', 'Multiple Languages'),
('mus', 'Creek'),
('mwl', 'Mirandese'),
('mwr', 'Marwari'),
('mwv', 'Mentawai'),
('my', 'Bɛɛmis kasa'),
('mye', 'Myene'),
('myv', 'Erzya'),
('mzn', 'Mazanderani'),
('na', 'Nauru'),
('nan', 'Min Nan Chinese'),
('nap', 'Neapolitan'),
('naq', 'Nama'),
('nb', 'Norwegian Bokmål'),
('nd', 'North Ndebele'),
('nds', 'Low German'),
('ne', 'Nɛpal kasa'),
('new', 'Newari'),
('ng', 'Ndonga'),
('nia', 'Nias'),
('niu', 'Niuean'),
('njo', 'Ao Naga'),
('nl', 'Dɛɛkye'),
('nl_BE', 'Flemish'),
('nmg', 'Kwasio'),
('nn', 'Norwegian Nynorsk'),
('nnh', 'Ngiemboon'),
('no', 'Norwegian'),
('nog', 'Nogai'),
('non', 'Old Norse'),
('nov', 'Novial'),
('nqo', 'NʼKo'),
('nr', 'South Ndebele'),
('nso', 'Northern Sotho'),
('nus', 'Nuer'),
('nv', 'Navajo'),
('nwc', 'Classical Newari'),
('ny', 'Nyanja'),
('nym', 'Nyamwezi'),
('nyn', 'Nyankole'),
('nyo', 'Nyoro'),
('nzi', 'Nzima'),
('oc', 'Occitan'),
('oj', 'Ojibwa'),
('om', 'Oromo'),
('or', 'Oriya'),
('os', 'Ossetic'),
('osa', 'Osage'),
('ota', 'Ottoman Turkish'),
('pa', 'Pungyabi kasa'),
('pag', 'Pangasinan'),
('pal', 'Pahlavi'),
('pam', 'Pampanga'),
('pap', 'Papiamento'),
('pau', 'Palauan'),
('pcd', 'Picard'),
('pdc', 'Pennsylvania German'),
('pdt', 'Plautdietsch'),
('peo', 'Old Persian'),
('pfl', 'Palatine German'),
('phn', 'Phoenician'),
('pi', 'Pali'),
('pl', 'Pɔland kasa'),
('pms', 'Piedmontese'),
('pnt', 'Pontic'),
('pon', 'Pohnpeian'),
('prg', 'Prussian'),
('pro', 'Old Provençal'),
('ps', 'Pashto'),
('pt', 'Pɔɔtugal kasa'),
('pt_BR', 'Brazilian Portuguese'),
('pt_PT', 'European Portuguese'),
('qu', 'Quechua'),
('quc', 'Kʼicheʼ'),
('qug', 'Chimborazo Highland Quichua'),
('raj', 'Rajasthani'),
('rap', 'Rapanui'),
('rar', 'Rarotongan'),
('rgn', 'Romagnol'),
('rif', 'Riffian'),
('rm', 'Romansh'),
('rn', 'Rundi'),
('ro', 'Romenia kasa'),
('ro_MD', 'Moldavian'),
('rof', 'Rombo'),
('rom', 'Romany'),
('root', 'Root'),
('rtm', 'Rotuman'),
('ru', 'Rahyia kasa'),
('rue', 'Rusyn'),
('rug', 'Roviana'),
('rup', 'Aromanian'),
('rw', 'Rewanda kasa'),
('rwk', 'Rwa'),
('sa', 'Sanskrit'),
('sad', 'Sandawe'),
('sah', 'Sakha'),
('sam', 'Samaritan Aramaic'),
('saq', 'Samburu'),
('sas', 'Sasak'),
('sat', 'Santali'),
('saz', 'Saurashtra'),
('sba', 'Ngambay'),
('sbp', 'Sangu'),
('sc', 'Sardinian'),
('scn', 'Sicilian'),
('sco', 'Scots'),
('sd', 'Sindhi'),
('sdc', 'Sassarese Sardinian'),
('se', 'Northern Sami'),
('see', 'Seneca'),
('seh', 'Sena'),
('sei', 'Seri'),
('sel', 'Selkup'),
('ses', 'Koyraboro Senni'),
('sg', 'Sango'),
('sga', 'Old Irish'),
('sgs', 'Samogitian'),
('sh', 'Serbo-Croatian'),
('shi', 'Tachelhit'),
('shn', 'Shan'),
('shu', 'Chadian Arabic'),
('si', 'Sinhala'),
('sid', 'Sidamo'),
('sk', 'Slovak'),
('sl', 'Slovenian'),
('sli', 'Lower Silesian'),
('sly', 'Selayar'),
('sm', 'Samoan'),
('sma', 'Southern Sami'),
('smj', 'Lule Sami'),
('smn', 'Inari Sami'),
('sms', 'Skolt Sami'),
('sn', 'Shona'),
('snk', 'Soninke'),
('so', 'Somalia kasa'),
('sog', 'Sogdien'),
('sq', 'Albanian'),
('sr', 'Serbian'),
('srn', 'Sranan Tongo'),
('srr', 'Serer'),
('ss', 'Swati'),
('ssy', 'Saho'),
('st', 'Southern Sotho'),
('stq', 'Saterland Frisian'),
('su', 'Sundanese'),
('suk', 'Sukuma'),
('sus', 'Susu'),
('sux', 'Sumerian'),
('sv', 'Sweden kasa'),
('sw', 'Swahili'),
('swb', 'Comorian'),
('swc', 'Congo Swahili'),
('syc', 'Classical Syriac'),
('syr', 'Syriac'),
('szl', 'Silesian'),
('ta', 'Tamil kasa'),
('tcy', 'Tulu'),
('te', 'Telugu'),
('tem', 'Timne'),
('teo', 'Teso'),
('ter', 'Tereno'),
('tet', 'Tetum'),
('tg', 'Tajik'),
('th', 'Taeland kasa'),
('ti', 'Tigrinya'),
('tig', 'Tigre'),
('tiv', 'Tiv'),
('tk', 'Turkmen'),
('tkl', 'Tokelau'),
('tkr', 'Tsakhur'),
('tl', 'Tagalog'),
('tlh', 'Klingon'),
('tli', 'Tlingit'),
('tly', 'Talysh'),
('tmh', 'Tamashek'),
('tn', 'Tswana'),
('to', 'Tongan'),
('tog', 'Nyasa Tonga'),
('tpi', 'Tok Pisin'),
('tr', 'Tɛɛki kasa'),
('tru', 'Turoyo'),
('trv', 'Taroko'),
('ts', 'Tsonga'),
('tsd', 'Tsakonian'),
('tsi', 'Tsimshian'),
('tt', 'Tatar'),
('ttt', 'Muslim Tat'),
('tum', 'Tumbuka'),
('tvl', 'Tuvalu'),
('tw', 'Twi'),
('twq', 'Tasawaq'),
('ty', 'Tahitian'),
('tyv', 'Tuvinian'),
('tzm', 'Central Atlas Tamazight'),
('udm', 'Udmurt'),
('ug', 'Uyghur'),
('uga', 'Ugaritic'),
('uk', 'Ukren kasa'),
('umb', 'Umbundu'),
('und', 'Unknown Language'),
('ur', 'Urdu kasa'),
('uz', 'Uzbek'),
('vai', 'Vai'),
('ve', 'Venda'),
('vec', 'Venetian'),
('vep', 'Veps'),
('vi', 'Viɛtnam kasa'),
('vls', 'West Flemish'),
('vmf', 'Main-Franconian'),
('vo', 'Volapük'),
('vot', 'Votic'),
('vro', 'Võro'),
('vun', 'Vunjo'),
('wa', 'Walloon'),
('wae', 'Walser'),
('wal', 'Wolaytta'),
('war', 'Waray'),
('was', 'Washo'),
('wbp', 'Warlpiri'),
('wo', 'Wolof'),
('wuu', 'Wu Chinese'),
('xal', 'Kalmyk'),
('xh', 'Xhosa'),
('xmf', 'Mingrelian'),
('xog', 'Soga'),
('yao', 'Yao'),
('yap', 'Yapese'),
('yav', 'Yangben'),
('ybb', 'Yemba'),
('yi', 'Yiddish'),
('yo', 'Yoruba'),
('yrl', 'Nheengatu'),
('yue', 'Cantonese'),
('za', 'Zhuang'),
('zap', 'Zapotec'),
('zbl', 'Blissymbols'),
('zea', 'Zeelandic'),
('zen', 'Zenaga'),
('zgh', 'Standard Moroccan Tamazight'),
('zh', 'Kyaena kasa'),
('zh_Hans', 'Simplified Chinese'),
('zh_Hant', 'Traditional Chinese'),
('zu', 'Zulu'),
('zun', 'Zuni'),
('zxx', 'No linguistic content'),
('zza', 'Zaza');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `mediaID` int(11) NOT NULL,
  `public_id` varchar(5000) NOT NULL,
  `url` text NOT NULL,
  `cloudinary` int(11) NOT NULL DEFAULT 0,
  `localPath` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`mediaID`, `public_id`, `url`, `cloudinary`, `localPath`) VALUES
(1, 'zkrwefsaz0rfeflsfuzt', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570742880/zkrwefsaz0rfeflsfuzt.jpg', 1, ''),
(2, 'hm7cxwwmzfhmqlgbcsmi', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570742972/hm7cxwwmzfhmqlgbcsmi.jpg', 1, ''),
(3, 'zgbsvsti1jcnbpbsrlwx', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743044/zgbsvsti1jcnbpbsrlwx.jpg', 1, ''),
(4, 'ft0xog3sml38rfkt4xi5', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743091/ft0xog3sml38rfkt4xi5.jpg', 1, ''),
(5, 'akjxxf1vkacmxetrnvcm', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743119/akjxxf1vkacmxetrnvcm.jpg', 1, ''),
(6, 'kyihiwjwg0vh1nygau1p', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743149/kyihiwjwg0vh1nygau1p.jpg', 1, ''),
(7, 'pkylg47nnvjqbvsnuut6', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743189/pkylg47nnvjqbvsnuut6.jpg', 1, ''),
(8, 'pwvgqm6q7troadshdx2t', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743228/pwvgqm6q7troadshdx2t.jpg', 1, ''),
(9, 'bclx1a4xj2emlyvijh5x', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743243/bclx1a4xj2emlyvijh5x.jpg', 1, ''),
(10, 'znjctfrfkmcqu3ulcuri', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743328/znjctfrfkmcqu3ulcuri.jpg', 1, ''),
(11, 'vjpcckinmc8zjjolcox4', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743402/vjpcckinmc8zjjolcox4.jpg', 1, ''),
(12, 'dzte3nhggusfzzuivjn9', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570743424/dzte3nhggusfzzuivjn9.jpg', 1, ''),
(13, 'qde57kxvvtzy12is2mt9', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570826515/qde57kxvvtzy12is2mt9.jpg', 1, ''),
(14, 'kbb2egou7epkxkbbnuoi', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570828838/kbb2egou7epkxkbbnuoi.jpg', 1, ''),
(15, 'zlm4pg48odmaumu7zsrb', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570903416/zlm4pg48odmaumu7zsrb.jpg', 1, 'C:\\xampp\\htdocs\\books/uploads/2921f560-b2e3-4281-a45a-c05a2c18b08b-640-636461695623138950.jpeg'),
(16, 'fryl75x77rumhdqlhion', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570903592/fryl75x77rumhdqlhion.jpg', 1, 'C:\\xampp\\htdocs\\books/uploads/blog-1.jpg'),
(17, 'muuisjj4p9kht1iqrvt4', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570903623/muuisjj4p9kht1iqrvt4.jpg', 1, 'C:\\xampp\\htdocs\\books/uploads/blog-1.jpg'),
(18, 'ficpbq6modcctw3pietm', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570913802/ficpbq6modcctw3pietm.png', 1, 'C:\\xampp\\htdocs\\books/uploads/apple-touch-icon-114x114-precomposed.png'),
(19, 'yfaufbjixblsyl93zlrf', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570913825/yfaufbjixblsyl93zlrf.png', 1, 'C:\\xampp\\htdocs\\books/uploads/apple-touch-icon-114x114-precomposed.png'),
(20, 'ony5imellllvj1fw61vk', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570913835/ony5imellllvj1fw61vk.png', 1, 'C:\\xampp\\htdocs\\books/uploads/apple-touch-icon-114x114-precomposed.png'),
(21, 'ojlqzgmge4tdla5le7un', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570913887/ojlqzgmge4tdla5le7un.png', 1, 'C:\\xampp\\htdocs\\books/uploads/apple-touch-icon-114x114-precomposed.png'),
(22, 'txk9wfsxpk10mye3sozy', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570913903/txk9wfsxpk10mye3sozy.png', 1, 'C:\\xampp\\htdocs\\books/uploads/apple-touch-icon-114x114-precomposed.png'),
(23, 'w9kd1hauxz7ghdyjn8r9', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570914048/w9kd1hauxz7ghdyjn8r9.jpg', 1, 'C:\\xampp\\htdocs\\books/uploads/2921f560-b2e3-4281-a45a-c05a2c18b08b-640-636461695623138950.jpeg'),
(24, 'y6kd7wp5qlknuq0tlhdk', 'http://res.cloudinary.com/golfcartsforsale-raheel/image/upload/v1570914062/y6kd7wp5qlknuq0tlhdk.jpg', 1, 'C:\\xampp\\htdocs\\books/uploads/2921f560-b2e3-4281-a45a-c05a2c18b08b-640-636461695623138950.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `roleID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`roleID`, `name`) VALUES
(1, 'admininstrator'),
(2, 'user');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tagID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `type` varchar(500) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tagID`, `userID`, `create_at`, `name`, `type`, `status`) VALUES
(1, 1, '2020-01-19 18:55:24', 'My fathoorah', 'all', 0),
(2, 1, '2020-01-19 18:56:11', 'knet', 'kn', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `first_name` varchar(500) NOT NULL,
  `last_name` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `uname` varchar(500) NOT NULL,
  `upass` varchar(500) NOT NULL,
  `roleID` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(50) NOT NULL,
  `token` varchar(1000) NOT NULL,
  `api_status` int(11) NOT NULL DEFAULT 1 COMMENT '1 i= sandbox2:live ',
  `gate_way_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `first_name`, `last_name`, `email`, `uname`, `upass`, `roleID`, `status`, `create_at`, `ip`, `token`, `api_status`, `gate_way_ID`) VALUES
(1, 'Raheel', 'Shehzad', 'info@channelsmedia.com', 'raheel.shehzad5', '21232f297a57a5a743894a0e4a801fc3', 1, 1, '2019-10-01 23:07:31', '::1', '', 1, 0),
(10, 'Raheel', 'Shehzad', 'raheelshehzad188@gmail.com', 'raheel.dev', '21232f297a57a5a743894a0e4a801fc3', 2, 1, '2020-01-19 16:03:10', '::1', '67c89a4eeda4213680473ec271eafa7f', 1, 0),
(11, 'Marcia', 'C Davis', 'mcd1127@live.com', 'root', '21232f297a57a5a743894a0e4a801fc3', 2, 1, '2020-01-21 22:45:40', '::1', '23884682b76899cbaaf376f2c340eeaa', 1, 2),
(12, 'Mazaad', 'Bazar', 'info@mazad.com', 'mazaad', '21232f297a57a5a743894a0e4a801fc3', 2, 1, '2020-01-23 18:39:04', '::1', '7f00ac303caa92bd9b447d14e3840c90', 1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`authorID`),
  ADD KEY `uid` (`userID`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`bookID`),
  ADD KEY `uid` (`uid`),
  ADD KEY `authorID` (`authorID`);

--
-- Indexes for table `book_to_genre`
--
ALTER TABLE `book_to_genre`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_to_lang`
--
ALTER TABLE `book_to_lang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_to_tag`
--
ALTER TABLE `book_to_tag`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genreID`),
  ADD KEY `uid` (`userID`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`groupID`),
  ADD KEY `uid` (`userID`);

--
-- Indexes for table `group_type`
--
ALTER TABLE `group_type`
  ADD PRIMARY KEY (`gtID`),
  ADD KEY `uid` (`userID`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`linkID`);

--
-- Indexes for table `list`
--
ALTER TABLE `list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`mediaID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`roleID`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tagID`),
  ADD KEY `uid` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `roleID` (`roleID`),
  ADD KEY `roleID_2` (`roleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `authorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `bookID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `book_to_genre`
--
ALTER TABLE `book_to_genre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `book_to_lang`
--
ALTER TABLE `book_to_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `book_to_tag`
--
ALTER TABLE `book_to_tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genreID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `groupID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `group_type`
--
ALTER TABLE `group_type`
  MODIFY `gtID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `linkID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `mediaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tagID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `authorID` FOREIGN KEY (`authorID`) REFERENCES `author` (`authorID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `userID` FOREIGN KEY (`uid`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `User Id` FOREIGN KEY (`userID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `Roles` FOREIGN KEY (`roleID`) REFERENCES `roles` (`roleID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
