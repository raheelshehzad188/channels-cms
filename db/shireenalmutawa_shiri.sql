-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 28, 2022 at 04:19 PM
-- Server version: 10.3.34-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shireenalmutawa_shiri`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `videoID` int(11) NOT NULL,
  `feature` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `userID`, `create_at`, `name`, `mediaID`, `videoID`, `feature`, `status`, `sort`) VALUES
(1, 1, '2020-04-10 23:33:29', 'knet', 5, 0, 0, 1, 0),
(2, 1, '2020-04-16 16:40:17', 'Album ', 23, 0, 0, 1, 0),
(3, 14, '2020-04-23 09:56:05', 'Single', 32, 0, 0, 1, 0),
(4, 14, '2020-04-23 13:32:44', 'test album ', 37, 38, 0, 1, 0),
(5, 14, '2020-05-02 19:31:47', 'Ocean Drive', 58, 0, 0, 0, 0),
(6, 14, '2020-05-02 20:02:53', 'Davey', 60, 0, 0, 1, 0),
(7, 14, '2020-05-08 21:31:15', 'Bad Sushi', 73, 0, 0, 0, 0),
(8, 14, '2020-05-08 21:41:19', 'Single', 77, 0, 0, 0, 0),
(9, 14, '2020-05-08 21:47:20', 'Futuristic Funk', 78, 0, 1, 0, 0),
(10, 14, '2020-07-07 15:31:44', 'edit hasseb', 384, 0, 0, 1, 0),
(11, 14, '2020-07-07 15:41:04', 'adds', 395, 0, 0, 1, 0),
(12, 14, '2020-09-02 00:07:10', 'Spectra', 580, 0, 0, 0, 0),
(13, 14, '2020-09-05 17:04:54', 'Long Way Home', 638, 0, 0, 0, 0),
(14, 14, '2020-09-05 17:23:26', 'Invisible', 641, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `artist`
--

CREATE TABLE `artist` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `videoID` varchar(100) NOT NULL DEFAULT '0',
  `banID` int(11) NOT NULL,
  `discription` text NOT NULL,
  `status` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `artist`
--

INSERT INTO `artist` (`id`, `userID`, `create_at`, `name`, `mediaID`, `videoID`, `banID`, `discription`, `status`, `sort`) VALUES
(1, 1, '2020-04-10 23:31:08', 'album1', 1, '0', 0, '', 1, 0),
(2, 1, '2020-04-10 23:32:13', 'album2', 2, '0', 0, '', 1, 0),
(3, 1, '2020-04-10 23:32:54', 'knet', 3, '0', 0, '', 1, 0),
(4, 1, '2020-04-15 16:15:59', 'Raheel shehzad1', 22, '0', 0, '', 1, 0),
(5, 14, '2020-04-23 12:12:14', 'MJ Ultra', 33, '424379872', 0, '', 1, 0),
(6, 14, '2020-04-23 12:22:44', 'Gedina', 549, '0', 0, 'On her full-length debut Tell ’Em, Gedina channels her raw passion into a daringly fresh take on R&B-infused pop. “There are so many songs out there that come from a place of loss or despair, but I wanted to create the exact opposite of that,” says the L.A.-based singer/songwriter. “The intention was to inspire, so that anyone who’s having a rough day can put the album on and feel elevated to the highest version of themselves.” Along with showcasing Gedina’s gorgeously soulful vocals—as heard on the 2015 single “First Time, First Love,” an international hit that’s amassed over 1.9 million streams on Spotify—Tell ’Em brings both gritty vitality and indelible melody to a powerful celebration of growth and resilience.\r\nInstagram: @iamgedina', 0, 0),
(7, 14, '2020-04-23 12:24:23', 'Kayo Genesis', 35, '0', 0, 'Instagram: @kayogenesis', 0, 0),
(8, 14, '2020-04-23 12:27:24', 'Fudge & The Frequency', 36, '0', 0, 'Fudge & The Frequency fuses funk, electro, soul, hip-hoop, dubstep, rock, and reggae. A London-based group who has performed to sell-out crowds in London and across Europe, including Barfly, Omeara, Cargo, the legendary Jazz Cafe, the Clapham Grand, Le Bus Palladium, the Borderline,  Kensington Roof Gardens and most recently at the Livewire Festival supporting THE JACKSONS in front of a sell-out 20,000 crowd.\r\nLead singer Fudge is a visionary, a creative force of pure energy and with a sound to match. As an artist, he has turned down approaches from producers of tv\'s X-Factor and The Voice, saying \"these shows are a fantastic platform for signers rather than artists.\"\r\n\"To me, Funk isn\'t a genre, it\'s an attitude, a state of mind.\"\r\nInstagram: @fudgeandthefrequency\r\n', 0, 1),
(9, 14, '2020-04-23 13:46:41', 'mnvbn ', 0, '0', 0, '', 1, 0),
(10, 14, '2020-04-23 13:49:51', 'final test ', 39, '0', 0, '', 1, 0),
(11, 14, '2020-04-30 22:56:14', 'Zay Jonny', 44, '0', 0, '', 1, 0),
(12, 14, '2020-04-30 22:58:35', 'Zay Jonny', 45, '0', 0, '', 0, 0),
(13, 14, '2020-04-30 23:01:20', 'Evrywhr', 551, '0', 0, ' -', 0, 0),
(14, 14, '2020-04-30 23:16:57', 'Brandyn Burnette', 47, '0', 0, '', 0, 0),
(15, 14, '2020-04-30 23:43:37', 'NHYN', 48, '0', 0, '', 0, 0),
(16, 14, '2020-04-30 23:48:08', 'Jae Murphy', 550, '0', 0, ' Instagram: @jaemurphy', 0, 0),
(17, 14, '2020-05-02 19:09:38', 'ERN', 53, '0', 0, '', 1, 0),
(18, 14, '2020-05-02 19:10:24', 'ERN', 548, '0', 0, 'Aaron', 0, 0),
(19, 14, '2020-05-09 14:12:07', 'BOXINLION', 82, '0', 0, '', 1, 0),
(20, 14, '2020-05-09 14:38:31', 'JNAY', 91, '0', 0, 'Jnay is a MOBO Award winning artist from west London, who draws his inspiration from the likes of Prince, Stevie Wonder, Marvin Gaye and Bilal. \r\nHe delivers electrifying tenacious live performances, with his unique vocal range; tastefully smooth and reassuring to sensuous power when needed.\r\nJnay was a finalist on the XFactor season 7 and his Talent has taken him to stages all over the world and back again including London\'s famous Royal Albert Hall alongside artists such as George Benson and the Black Eyed Peas to name a few.\r\nInstagram: @Jnaybaby', 0, 0),
(21, 14, '2020-05-12 01:05:18', 'Tadashi', 123, '424379872', 0, 'LA based musician\r\n                                                                                                         ', 1, 0),
(22, 14, '2020-05-15 05:15:01', 'test', 126, '424379872', 0, '', 1, 0),
(23, 14, '2020-07-07 15:23:30', 'addxc zc', 376, '424379872', 0, 'gfzx', 1, 0),
(24, 14, '2020-07-07 15:29:09', 'edit aalibaba', 382, '0', 0, 'edit', 1, 0),
(25, 14, '2020-07-07 15:38:25', 'raheel add', 387, '0', 0, 'add disc', 1, 0),
(26, 14, '2020-07-07 15:39:30', 'addf', 392, '0', 0, 'fsdff', 1, 0),
(27, 14, '2020-07-07 15:39:46', 'edit lkkl', 393, '0', 0, 'edit klkk', 1, 0),
(28, 14, '2020-07-08 14:51:18', 'Haseeb', 401, '424379872', 0, 'I am disc', 1, 0),
(29, 14, '2020-07-27 00:49:19', 'MJ Ultra', 475, '446653473', 0, 'MJ Ultra is an LA based alternative-R&B artist and songwriter. He’s presently promoting his debut\r\nindependent album, ‘Ocean Drive’, and he’s just completed an extensive European spring and summer tour in ‘18 & ‘19.\r\n\r\nHis music has been featured in multiple feature films and television shows. Most recently, he scored two original songs and music videos for Universal Pictures’ “Bring It On: Worldwide”, A Netflix Original “#RealityHigh”, Justin Timberlake’s “Friends With Benefits”, and Kevin James’ “Here Comes the Boom”. His music was twice featured on Dancing With the Stars and America’s Got Talent. His latest feature collaboration, “Black & White” for Barcelona-based DJ duo, Boxinlion, has amassed more than 50 million streams worldwide.\r\n\r\nHe’s gearing up to release a new single, “Moonlight”, and an accompanying video. His follow up album, “Goddess”, is scheduled for release in late 2020.\r\nInstagram: @mjultra\r\n', 0, 0),
(30, 14, '2020-07-29 16:33:48', 'New test', 494, '424379872', 0, 'jkjhfh', 1, 0),
(31, 14, '2020-08-08 14:02:37', 'The Keymakers', 505, '0', 0, 'The Keymakers are a Los Angeles based duo made up of brothers Rome and Red. Since they formed in 2018, they\'ve combined their diverse musical influences - Rome\'s rich history in Jazz, Soul and R&B, and Red\'s Electronic production background - to bridge the gap between genres, creating their own brand of soulful electronic music.\r\nInstagram: @thekeymakers', 0, 0),
(32, 14, '2020-08-20 03:03:42', 'Jasmine Kara', 552, '0', 0, ' Instagram: @jasminekaras', 0, 0),
(33, 14, '2020-08-20 03:10:40', 'Louie Bello', 554, '0', 718, 'Louie Bello is a Country R&B artist whose Latest single \"Got it Bad\" recently reached 225,000 streams on Spotify . He can be seen in venues from Nashville and Boston to New York and LA. He recently finished his first  European tour playing in Spain, Portugal, London, Ireland, Iceland and Denmark . \r\n\r\n Louie\'s vocal chops grew from as an American a story as one could imagine: as though he were born of Doo-Wop, an adolescent Louie sang harmonies with friends on Boston street corners.  Since then, he has written theme songs for PBS, ESPN,  had a publishing deal with Sony, and had his original songs featured on the television shows such as God Friended Me, Lincoln Heights, Keeping up with the Kardashians, and The Real World to name a few. Louie has taken the stage at many national music festivals such as Milwaukie Mile of Music Fest, Boston Fest, Red Gorilla Festival, SXSW and has recently opened for Country stars Tyler Farr and Eric Paslay.  \r\nInstagram: @louiebello', 0, 0),
(34, 14, '2020-09-01 23:30:03', 'Blue Light Bandits', 578, '0', 725, 'The Blue Light Bandits are an award winning, 4-piece independent groove rock band from Worcester, MA. The BLB sound is inspired by a soulful mix of jazz, neo-soul, pop, and funk music. Since the release of their B.L.B Demo in 2014 and the self titled debut LP in 2017, they have amassed over 2.1 million streams on Spotify. Live BLB shows have become a staple of the Boston, Worcester and New England music scenes with residencies at Lucky\'s Lounge in Boston and Michael\'s Cigar Bar in Worcester as well as frequent appearances at the best breweries, wineries, and festivals in the region. \r\n \r\nDan DeCristofaro (keys and vocals) and Ethan Bates (bass and vocals) are the founding members, singers, and songwriters behind BLB. Dan is a soulful writer, producer, and arranger who has also begun releasing singles as a solo artist with “Give Me Your Love” in May of 2020 and  \"Unbreakable\" in November 2019. Ethan is also an accomplished cellist and performs and writes solo compositions as \"Ethan Bates Orchestra\" where he builds live loops and layers organically for each performance. The current iteration of the BLB lineup features veteran touring and session drummer, Simon Adamsson, and multi-faceted guitarist, Jay Faires, who also rounds out the signature 3-part BLB harmonies.\r\n \r\nAs of January 2020 the band began work on their second full length album set to be released in Fall 2020 that will introduce the next evolution of their songs, sound, and direction. Follow along and stay tuned!\r\nInstagram: @bluelightbandits', 0, 0),
(35, 14, '2020-09-03 15:16:04', 'Banner test update', 623, '424379872', 624, 'Dscription update ', 1, 0),
(36, 14, '2020-09-18 19:15:28', 'Stas', 691, '459563378', 716, 'Futuristic Cyber Punk', 0, 0),
(37, 14, '2020-09-29 12:44:45', 'Raheel Shehzad', 703, '', 704, 'test discription', 1, 9);

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
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `btype` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `userID`, `create_at`, `name`, `mediaID`, `status`, `sort`, `btype`) VALUES
(27, 1, '2021-05-05 03:59:47', 'You are awesome!!', 43, 0, 0, 0),
(28, 1, '2021-11-18 09:28:50', 'Keep working hard', 44, 0, 0, 0),
(29, 1, '2021-11-20 06:15:31', 'Keep up the good work', 55, 0, 0, 1);

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
(1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `fname` varchar(500) NOT NULL,
  `lname` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `upass` varchar(500) NOT NULL,
  `app_type` varchar(500) NOT NULL,
  `token` varchar(500) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `gender` varchar(500) NOT NULL,
  `age` varchar(500) NOT NULL,
  `height` varchar(500) NOT NULL,
  `weight` varchar(500) NOT NULL,
  `dob` date DEFAULT NULL,
  `subscription` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `fname`, `lname`, `email`, `upass`, `app_type`, `token`, `mediaID`, `gender`, `age`, `height`, `weight`, `dob`, `subscription`) VALUES
(1, 'Raheel andriod', 'test', 'raheelshehzad188@gmail.com', 'MTIzNA==', 'android', '{\"userId\":\"307377b2-b388-4277-b3d6-4533b9cc6098\",\"pushToken\":\"cLs03IJqQBWTdC2TgkJgrB:APA91bGTfqei4Um2PKtqdtvWnkNyjeP1pldwKqAP-LxlbWrmlIGPaTLrFJYTycr4ett5rwYMb0SnyaQ1S_tpM4kB_3dW3sOE4I7vZ5m16rhag9JWsl-Ao7TGOxZUNx-VO7YKdQDgAawn\"}', 236, 'male', '', '6\',3\'\'', 'f', '2020-07-30', NULL),
(2, 'Raheel shehzad', '', 'raheel@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(3, 'Raheel shehzad', '', 'raheel@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(4, 'Raheel1', 'Shehzad1', 'raheelshahzad188@gmail.com', 'YWRtaW4=', '', '', 273, 'male', '', '5.4', '13', '1993-07-25', NULL),
(5, 'Raheel1', 'Shehzad1', 'raheelshahzad1881@gmail.com', 'YWRtaW4=', 'andriod', 'token', 210, 'm', '', '5.4', '10', NULL, NULL),
(6, 'Haseeb', 'Shahzad', 'info@zealcg.com', 'MTE=', 'android', '123', 0, '', '', '', '', NULL, NULL),
(7, 'Haseeb', 'Shazad', 'info@zeal.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(8, 'Haseeb', 'Shahzad', 'haseb@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(9, 'davey', 'duarte', 'daveyduarte@gmail.com', 'UG9pYXNkZjEyMTIxMiE=', 'android', '{\"userId\":\"ba22f56f-6af6-4a65-97b8-16978a2f9fd3\",\"pushToken\":\"ekL5MMt-TMWO9PfC_Btdnn:APA91bGICwyUcuTJ3V98yvhvWEVre7aoE5ttHPVogwtXWTQWKM3FJVeumqDfqvdx_4NPJrPNIzna2xPX5GpQmyityYTsSvACcLPNPJVGpIKM_jCZgbmxZUkKgtAjklTnPfE7i2coHueW\"}', 0, '', '', '', '', NULL, NULL),
(10, 'Raheel1', 'Shehzad1234', 'haseeb@haseeb.com', 'YWRtaW4=', 'ios', '{\"userId\":\"6a9b0f50-959b-477a-9549-e89d6d7484f2\",\"pushToken\":\"1d96d97821854d2a00a38f83541f997d3471947ad4149049948b1d997f3e278f\"}', 714, 'male', '', '6\',11\'\'', 'm', '2020-07-24', '2037-08-15 00:00:00'),
(11, 'Raheel', 'Shehzad', 'raheelshahzad18821@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, '2036-06-21 00:00:00'),
(12, 'Raheel', 'Shehzad', 'raheelshahzad188216@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(13, 'Raheel', 'Shehzad', 'raheelshahzad1d881@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(14, 'Marcia', 'Davis', 'mcd112s7@live.com', 'QEBjb2RlQEAxMjM=', '', '', 0, '', '', '', '', NULL, NULL),
(15, 'Ja', 'hjkll', 'has@has.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(16, 'adfa', 'add', 'raheel@raheel.com', 'YWRtaW4=', 'web', 'undefined', 352, '', '', '', '', NULL, '2020-08-01 15:03:55'),
(17, 'a', 'a', 'raheelshedhzad188@gmail.com', 'YWRx', '', '', 0, '', '', '', '', NULL, NULL),
(18, 'Raheel', 'Shehzad', 'raheelshahzad18820@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(19, 'Raheel', 'Shehzad', 'raheelshahzad18829@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(20, 'Raheel', 'Shehzad', 'raheelshahzad18828@gmail.com', 'YWRtaW4=', '', '', 0, '', '', '', '', NULL, NULL),
(23, 'david', 'bowdre', 'djbbo@aol.com', 'VHVyYm8xMDIw', 'ios', '{\"userId\":\"7b61074e-cfe1-4ce5-9899-9f3971ca293c\",\"pushToken\":\"bfd67a91cac0a599cad82fc5ba281d86933c7b36683e0929eeb3bfe185abf811\"}', 677, 'male', '', '6\',4\'\'', '230', '1962-12-03', '2020-08-12 02:13:07'),
(21, 'Justin', 'May', 'dashlife1@gmail.com', 'VHVyYm9maXQxMQ==', 'ios', '{\"userId\":\"4e7e9e20-40c9-4c74-89b1-5e4561489155\",\"pushToken\":\"be9bf666810b4fc04fce06ad643e0526d9261915cf3ad1be11bea238914928d0\"}', 576, 'male', '', '6\',5\'\'', 'f', '1985-09-11', NULL),
(22, 'Turhan', 'Douglas', 'turhan.douglas.td@gmail.com', 'dHVyYm85MjU2OQ==', 'ios', '{\"userId\":\"9302e7a5-fe16-4a5e-92eb-a395e258c3b7\",\"pushToken\":\"c9e576302e81b17105b80eee75023bd767ab8f05bc7b991e6f3beb1b5a997bb3\"}', 672, 'male', '', '6\',2\'\'', '198', '1969-09-25', '2025-09-09 07:05:48'),
(31, 'cheryl', 'douglas', 'cheryldouglas.kw@gmail.com', 'SWx0aHJAMXRm', '', '', 0, '', '', '', '', NULL, '2021-07-19 06:39:27'),
(30, 'Rob', 'Goodrich ', 'robgoodrich@gmail.com', 'Y2FtbWVyMjI=', '', '', 0, '', '', '', '', NULL, NULL),
(24, 'hunter', 'douglas', 'hunter.m.douglas@gmail.com', 'QXNoYXIxMjMh', 'ios', '{\"userId\":\"02b0a08d-71b8-43bb-8f03-a9516064e599\",\"pushToken\":\"53c9bf1af2d16d314c9990f9e825abda11a4c9549a7ac266c9785f11250ecf09\"}', 427, 'male', '', '6\',1\'\'', 'm', '2020-07-26', '2021-07-21 01:26:20'),
(25, 'Donald ', 'Suxho', 'dsuxho@icloud.com', 'c2hhbmUyMDAx', '', '', 0, '', '', '', '', NULL, '2021-07-18 06:21:57'),
(26, 'Whitney', 'Renee', 'hello@gmail.com', 'R3JpZmZleTU1', '', '', 0, '', '', '', '', NULL, NULL),
(27, 'toshi', 'lu', 'toshi@ghostmotorsports.com', 'bHV4NzAwMjQ3', '', '', 0, '', '', '', '', NULL, NULL),
(48, 'cheyenne', 'floyd', 'itscheyennefloyd@gmail.com', 'UnlkZXJrMDc=', '', '', 0, 'female', '', '5\',5\'\'', 'undefined', '1992-10-20', NULL),
(28, 'Cameron', 'Goodrich', 'cameron@6tomidnightmusic.com', 'UGhpc2h5MjI=', 'ios', '{\"userId\":\"a33aef32-e7f0-4229-942e-78ddfb0d5801\",\"pushToken\":\"c401122ae3fdb9ec898c30ebb03abde05664725e982daa6294b4a25612c274cc\"}', 493, 'male', '', '6\',0\'\'', 'm', '1984-06-23', '2022-08-20 20:36:53'),
(29, 'Hannah', 'Douglas', 'hannahdouglas58@yahoo.com', 'UmF5Y2Vkb3VnbGFzMDgxOA==', '', '', 0, '', '', '', '', NULL, NULL),
(32, 'Margaret', 'Bowdre', 'margaret@iqcopy.com', 'cnlkZXJiYWF6Nzg=', '', '', 0, 'female', '', '5\',7\'\'', '167', '2020-07-20', '2020-08-18 03:58:05'),
(33, 'Dawn', 'Lovelace', 'kaseylovelace@gmail.com', 'bG92ZWxhY2UxMjM=', '', '', 0, 'female', '', '5\',3\'\'', 'm', '2020-07-19', NULL),
(34, 'Jason', 'Armstrong', 'jay@walklikeaduck.com', 'cG9zdHJvYWQ=', 'ios', '{\"userId\":\"9bbfc4f0-53e0-4520-88aa-666aad4e9362\",\"pushToken\":\"731f9abf604fffcfe1ac3b4fa8104f4bead7d16d6291f35730daeaa004f7a937\"}', 0, '', '', '', '', NULL, '2020-08-20 02:35:14'),
(36, 'Christine', 'Burkhart', 'burkhart1313@gmail.com', 'UGlyYXRlMTNQaXJhdGU=', '', '', 0, '', '', '', '', NULL, NULL),
(35, 'Lisa', 'Cole', 'misscolebass@gmail.com', 'TWlsbGlvbmFpcmUwMDE=', '', '', 0, '', '', '', '', NULL, NULL),
(37, 'new', 'User', 'new@new.com', 'YWRtaW4=', '', '', 459, '', '', '', '', NULL, '2021-07-25 04:49:44'),
(38, 'Ryan', 'Bowdre', 'ryanbowdre@gmail.com', 'cGFzc3dvcmQ=', 'ios', '{\"userId\":\"9ed5b290-8360-461a-a049-717a5dccad7d\",\"pushToken\":\"451338eb5ff94ac22458238996154e95621443c443f4485fd3b2aa90fe313b92\"}', 498, '', '', '', '', NULL, NULL),
(39, 'test', 'test', 'igigk@7(7(.7(7?', 'cA==', '', '', 0, '', '', '', '', NULL, NULL),
(40, 't', 't', 't', 'dA==', 'ios', '{\"userId\":\"9ed5b290-8360-461a-a049-717a5dccad7d\",\"pushToken\":\"451338eb5ff94ac22458238996154e95621443c443f4485fd3b2aa90fe313b92\"}', 500, '', '', '', '', NULL, '2024-07-31 20:44:38'),
(41, 'igivivi', 'ifucuvig', 'k', 'cG9w', '', '', 499, 'female', '', '7\',2\'\'', 'm', '2020-08-01', '2025-07-31 20:18:29'),
(42, 'Leah', 'Mitchell', 'theleahmitchell@gmail.com', 'QW1lbnRvamVzdXMx', '', '', 0, '', '', '', '', NULL, '2021-08-06 08:13:36'),
(43, 'developer ', 'Test', 'appletest@test.com', 'YWRtaW4=', 'ios', '{\"userId\":\"b153117d-99e2-4045-ac70-55b0a0415986\",\"pushToken\":\"\"}', 727, 'male', '', '6\',1\'\'', '77', '2019-09-10', NULL),
(45, 'R', 'Floyd', 'rkylelynn@gmail.com', 'VHVyYm9maXQyMDIw', '', '', 574, 'female', '', '5\',10\'\'', 'f', '1989-09-08', NULL),
(44, 'victoria', 'douglas', 'victoriamariedouglas@gmail.com', 'TUBkaXNvbjE=', 'ios', '{\"userId\":\"392b039c-2aeb-4004-a8eb-350460611fa7\",\"pushToken\":\"4889e66520c133cfa40357e4e1aeec878e35dad237611305c7e34f5cec347809\"}', 664, 'female', '', '5\',11\'\'', '165', '1983-12-29', NULL),
(46, 'Amir', 'H', 'amir11cool@yahoo.com', 'YW1pcg==', '', '', 0, 'male', '', '6\',3\'\'', 'f', '1970-01-01', NULL),
(47, 'lindsay ', 'endicott', 'lindsayendicott@gmail.com', 'SnVzdGlu', '', '', 0, 'female', '', '5\',8\'\'', 'f', '1993-10-20', NULL),
(49, 'Michael', 'Thompson', 'thompsonmichael77720@yahoo.com', 'U3VwZXJtYW43', '', '', 0, 'male', '', '6\',2\'\'', 'm', '1962-12-12', NULL),
(50, 'A', 'A', 'a@gmail.com', 'MTIzNDU2', '', '', 0, 'male', '', '7\',3\'\'', 'm', '2020-09-02', NULL),
(56, 'joe', 'oas', 'joeoas2001@gmail.com', 'SnVuZ2hvb244OA==', '', '', 0, 'male', '', '5\',6\'\'', 'm', '2020-09-04', NULL),
(51, 'journey', 'manzo', 'journeym13@gmail.com', 'QnJ1aA==', '', '', 0, 'female', '', '5\',7\'\'', 'm', '2003-07-21', NULL),
(52, 'Turhan', 'Douglas ', 'Turhand@outlook.com', 'MXFhejJ3c3g=', 'ios', '{\"userId\":\"ed4ec9bf-eb9a-48b1-a2e6-a27912f35595\",\"pushToken\":\"\"}', 0, '', '', '', '', NULL, NULL),
(53, 'jessica', 'sykora', 'jessicasykora8@gmail.com', 'amVzc2ljYXM=', '', '', 0, 'female', '', '6\',4\'\'', 'm', '1987-12-23', NULL),
(54, 'Nicole', 'Millar', 'nicolemillar1@me.com', 'bG9uZG9uMTk3MQ==', '', '', 0, 'female', '', '5\',2\'\'', '150', '1971-10-20', '2021-09-03 04:35:45'),
(55, 'sambinta', 'menter', 'sambintagueye@yahoo.com', 'R25hZ25hMTc=', '', '', 0, 'male', '', '5\',10\'\'', 'm', '2020-09-03', NULL),
(57, 'ashmita', 'thapa', 'ashmiko123@icloud.com', 'YXNobWl0YTE=', '', '', 0, 'female', '', '5\',1\'\'', 'f', '2001-06-03', NULL),
(58, 'emiliano', 'mendoza', 'bruinfan13@yahoo.com', 'M2YxM0IyNTI1', '', '', 658, 'male', '', '6\',0\'\'', 'f', '1970-01-01', NULL),
(59, 'Elina', 'Xie', 'elina.t.xie@gmail.com', 'U2FuODJtb24=', '', '', 0, 'female', '', '5\',5\'\'', 'f', '2001-11-20', NULL),
(60, 'Turhan', 'Douglas ', 'turhan@joejuice.com', 'MXFhejJ3c3g=', '', '', 659, 'male', '', '6\',0\'\'', 'm', '1995-02-10', NULL),
(61, 'Jadrien', 'Louden-Wiser', 'jadrien8f4l@gmail.com', 'SjEyNTEzNnck', '', '', 0, 'male', '', '6\',3\'\'', 'f', '1995-11-04', NULL),
(62, 'juliane', 'duarte', 'juliane.ds.duarte@gmail.com', 'cG9hc2Rm', '', '', 660, '', '', '', '', NULL, NULL),
(63, 'Vanessa', 'Heavens', 'vanessalheavens@gmail.com', 'QnV0dG9uczEh', '', '', 0, '', '', '', '', NULL, NULL),
(71, 'Ryan', 'Palmer', 'palmerr1979@gmail.com', 'TGFrZXJzMTk3OQ==', '', '', 0, 'male', '', '6\',0\'\'', 'f', '1979-03-26', NULL),
(64, 'Tim', 'Brown', 'brown_tim@smc.edu', 'Q3JlbnNoYXc4Ng==', '', '', 0, 'male', '', '5\',7\'\'', 'f', '1970-01-01', NULL),
(65, 'j', 'l', 'jameswlee99@gmail.com', 'SmFtZXN3bGVlOTlAZ21haWwuY29t', '', '', 0, 'male', '', '5\',10\'\'', 'undefined', '1970-01-01', NULL),
(66, 'Sivhui', 'Thiem', 'Sivhui@gmail.com', 'TXM3MzY2Mzc=', '', '', 0, '', '', '', '', NULL, NULL),
(67, 'robert', 'anderson', 'rico.anderson@gmail.com', 'SmVtaTMyODEy', '', '', 0, 'male', '', '6\',3\'\'', 'm', '1970-01-01', NULL),
(68, 'Joshua', 'Shelly', 'joshshelly@gmail.com', 'NjE4OUNvbG9tYSE=', '', '', 0, 'male', '', '6\',3\'\'', 'undefined', '1982-12-25', NULL),
(69, 'Andre ', 'Saab', 'andre@digcycle.com', 'RGlnY3ljbGUxMjMh', '', '', 0, '', '', '', '', NULL, NULL),
(70, 'Danielle', 'Blum', 'dcstiller_12@yahoo.com', 'YXByMjJpbA==', '', '', 0, '', '', '', '', NULL, NULL),
(72, 'amy', 'herrick', 'aherrick99@gmail.com', 'Ym9zdGl0Y2g1MA==', '', '', 0, '', '', '', '', NULL, NULL),
(73, 'kelly', 'knickerbocker', 'kknickerbocker@ucla.edu', 'S2VsbDIz', '', '', 692, 'female', '', '5\',7\'\'', '140', '1991-07-23', NULL),
(74, 'denise', 'anderson', 'dma1013@icloud.com', 'MTNBcmF2Yms=', '', '', 0, '', '', '', '', NULL, NULL),
(75, 'Guillermo', 'Ortiz', 'ortiz.memo@ymail.com', 'b21hcjEyMTA5Ng==', '', '', 0, 'male', '', '5\',7\'\'', '185', '1996-12-11', NULL),
(76, 'Maud', 'Fraipont', 'sorraiamafalda@gmail.com', 'SWhhVHVyYm9GaXRhIQ==', 'ios', '{\"userId\":\"c3d855d1-80b2-4bc2-b117-dfe0de9c83d1\",\"pushToken\":\"94592ba71231e7714611bb3b9f8b233f6dd377fffb38a827cda22e2c9998bedd\"}', 0, 'female', '', '5\',2\'\'', '137', '1999-12-27', NULL),
(77, 'edward', 'snowden', 'bdulan715@gmail.com', 'QG5hbHVkczIyMTk=', '', '', 0, 'male', '', '6\',3\'\'', '225', '1970-01-01', NULL),
(78, 'Robert', 'Anderson', 'r_anderson24@yahoo.com', 'SW1udXR0eTI0', '', '', 0, 'male', '', '5\',10\'\'', '300', '1970-01-01', NULL),
(79, 'judith', 'carolina', 'oliden.judith@gmail.com', 'Q2Fyb2xpbmE5ODIxIQ==', '', '', 693, 'female', '', '5\',1\'\'', '145', '1999-10-29', NULL),
(80, 'Marie', 'Fraipont', 'marie.fraipont@live.fr', 'TEEyMDIw', '', '', 696, 'female', '', '5\',1\'\'', '121', '1970-01-01', NULL),
(81, 'Kassandra', 'Carrillo', 'kass232017@gmail.com', 'dGhhbmt5b3VMb3JkMjM=', '', '', 0, 'female', '', '5\',1\'\'', '112', '1992-10-23', NULL),
(82, 'karyn', 'anderson', 'kand3@bu.edu', 'QnVkZHlMb3ZlMg==', '', '', 0, 'female', '', '5\',5\'\'', '160', '1970-01-01', NULL),
(83, 'jessica', 'fleming', 'fleming.jessica@gmail.com', 'RXJuaWV0aGViZXJuaWUxIQ==', '', '', 0, '', '', '', '', NULL, NULL),
(84, 'michelle', 'callahan', 'mcallahan2727@yahoo.com', 'Um91dGU5', '', '', 0, 'female', '', '5\',2\'\'', '138', '1970-01-01', NULL),
(85, 'Jilayne', 'Lovejoy', 'jilayne@2020-la.com', 'amJsNzM0Ng==', '', '', 0, 'female', '', '5\',7\'\'', '124', '1970-01-01', NULL),
(86, 'taylor', 'ackerman', 'tayyackerman@aol.com', 'SWxvdmVtYXJ5MQ==', 'ios', '{\"userId\":\"b5d2ff39-9238-4b3f-9244-bb02bd026e9d\",\"pushToken\":\"\"}', 0, '', '', '', '', NULL, NULL),
(87, 'alaina', 'guido', 'alainajh218@aol.com', 'RHJldzc3', '', '', 0, '', '', '', '', NULL, NULL),
(88, 'haseeb', 'testhaseeb', 'testok@test.com', 'YWRtaW4=', '', '', 719, 'male', '', '6\',3\'\'', '78', '2020-09-30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupon`
--

CREATE TABLE `coupon` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `code` varchar(200) NOT NULL,
  `perc` varchar(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `coupon`
--

INSERT INTO `coupon` (`id`, `userID`, `create_at`, `code`, `perc`, `date`, `status`) VALUES
(1, 14, '2020-06-27 15:02:58', 'turbo15', '15', '2020-07-25', 0),
(2, 14, '2020-07-07 15:43:01', '312', '312', '2001-02-18', 1),
(3, 14, '2020-07-07 15:47:13', 'ssa', 'ewsa', '2020-07-06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `coupon_item`
--

CREATE TABLE `coupon_item` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `code` varchar(200) NOT NULL,
  `perc` varchar(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `status` int(11) NOT NULL,
  `is_used` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `price` double NOT NULL,
  `name` varchar(500) NOT NULL,
  `detail` text NOT NULL,
  `badge` int(11) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `cover` text NOT NULL,
  `userID` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `banner` int(11) NOT NULL DEFAULT 0,
  `cID` int(11) NOT NULL DEFAULT 0,
  `mid` int(11) NOT NULL DEFAULT 0,
  `credit` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `price`, `name`, `detail`, `badge`, `mediaID`, `cover`, `userID`, `status`, `banner`, `cID`, `mid`, `credit`) VALUES
(1, 0, 'sdsa', '', 27, 0, '', 1, 1, 0, 0, 0, 0),
(2, 0, 'course ', 'test', 27, 12, '', 1, 1, 0, 0, 0, 0),
(3, 200, 'Basic HTML and HTML5 test', 'HTML is a markup language that uses a special syntax or notation to describe the structure of a webpage to the browser.', 27, 12, '', 1, 0, 0, 0, 0, 5),
(4, 65, 'Kubernetes', 'Kubernetes is an open-source container-orchestration system for automating computer application deployment, scaling, and management.', 27, 14, '', 1, 1, 0, 0, 0, 0),
(5, 25, 'kubernetes', 'dummy ', 27, 0, '', 1, 0, 0, 0, 0, 0),
(6, 35, 'docker ', 'docker dum ', 27, 34, 'https://www.youtube.com/watch?v=qOcSQNlBWSg', 1, 0, 35, 0, 0, 0),
(7, 19, 'Stanford Introduction to Food and Health', 'Around the world, we find ourselves facing global epidemics of obesity, Type 2 Diabetes and other predominantly diet-related diseases. To address these public health crises, we urgently need to explore innovative strategies for promoting healthful eating. There is strong evidence that global increases in the consumption of heavily processed foods, coupled with cultural shifts away from the preparation of food in the home, have contributed to high rates of preventable, chronic disease. In this course, learners will be given the information and practical skills they need to begin optimizing the way they eat. This course will shift the focus away from reductionist discussions about nutrients and move, instead, towards practical discussions about real food and the environment in which we consume it. By the end of this course, learners should have the tools they need to distinguish between foods that will support their health and those that threaten it. In addition, we will present a compelling rationale for a return to simple home cooking, an integral part of our efforts to live longer, healthier lives.', 28, 45, '', 1, 0, 46, 0, 0, 300);

-- --------------------------------------------------------

--
-- Table structure for table `dances`
--

CREATE TABLE `dances` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `vID` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dances`
--

INSERT INTO `dances` (`id`, `userID`, `create_at`, `name`, `vID`, `status`) VALUES
(1, 14, '2020-07-23 14:59:54', '', 8, 1),
(2, 14, '2020-07-24 16:22:15', '', 46, 1),
(3, 14, '2020-07-25 14:16:17', '', 46, 0);

-- --------------------------------------------------------

--
-- Table structure for table `dance_items`
--

CREATE TABLE `dance_items` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `dID` int(11) NOT NULL,
  `vID` varchar(500) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dance_items`
--

INSERT INTO `dance_items` (`id`, `userID`, `create_at`, `name`, `dID`, `vID`, `sort`, `status`) VALUES
(1, 0, '2020-07-23 14:59:54', 'v ideo', 1, '439161820', 0, 0),
(2, 0, '2020-07-23 14:59:54', '2nd', 1, '439161820', 0, 0),
(3, 0, '2020-07-23 14:59:54', '3rd', 1, '439161820', 0, 0),
(270, 0, '2020-07-28 23:17:00', 'Intro 1', 3, '441441852', 1, 0),
(271, 0, '2020-07-28 23:17:00', ' Intro 2', 3, '441441774', 2, 0),
(272, 0, '2020-07-28 23:17:00', 'Stretch 1', 3, '441441707', 3, 0),
(273, 0, '2020-07-28 23:17:00', 'Stretch 2', 3, '441441275', 4, 0),
(274, 0, '2020-07-28 23:17:00', 'Step 1 Bottom', 3, '441440775', 5, 0),
(275, 0, '2020-07-28 23:17:00', 'Step 1 Top', 3, '441440376', 6, 0),
(276, 0, '2020-07-28 23:17:00', 'Step 1 Wide', 3, '441439975', 7, 0),
(277, 0, '2020-07-28 23:17:00', 'Step 1 Performance', 3, '441439884', 8, 0),
(278, 0, '2020-07-28 23:17:00', 'Step 2 Bottom', 3, '441439404', 9, 0),
(279, 0, '2020-07-28 23:17:00', 'Step 2 Top', 3, '441439404', 10, 0),
(280, 0, '2020-07-28 23:17:00', 'Step 2 Wide', 3, '441438531', 11, 0),
(281, 0, '2020-07-28 23:17:00', 'Step 2 Performance', 3, '441438118', 12, 0),
(282, 0, '2020-07-28 23:17:00', 'Step 3 Bottom', 3, '441437333', 13, 0),
(283, 0, '2020-07-28 23:17:00', 'Step 3 Top', 3, '441436947', 14, 0),
(284, 0, '2020-07-28 23:17:00', 'Step 3 Wide', 3, '441436550', 15, 0),
(285, 0, '2020-07-28 23:17:00', 'Step 3 Performance', 3, '441436412', 16, 0),
(286, 0, '2020-07-28 23:17:00', 'Step 4 Bottom', 3, '441436146', 17, 0),
(287, 0, '2020-07-28 23:17:00', 'Step 4 Top', 3, '441435884', 18, 0),
(288, 0, '2020-07-28 23:17:00', 'Step 4 Wide', 3, '441435575', 19, 0),
(289, 0, '2020-07-28 23:17:00', 'Step 4 Performance', 3, '441435401', 20, 0),
(290, 0, '2020-07-28 23:17:00', 'Step 5 Bottom', 3, '441434977', 21, 0),
(291, 0, '2020-07-28 23:17:00', 'Step 5 Top', 3, '441434544', 22, 0),
(292, 0, '2020-07-28 23:17:00', 'Step 5 Wide', 3, '441433987', 23, 0),
(293, 0, '2020-07-28 23:17:00', 'Step 5 Performance', 3, '441433526', 24, 0),
(294, 0, '2020-07-28 23:17:00', 'Step 6 Bottom', 3, '441432640', 25, 0),
(295, 0, '2020-07-28 23:17:00', 'Step 6 Top', 3, '441431663', 26, 0),
(296, 0, '2020-07-28 23:17:00', 'Step 6 Wide', 3, '441430794', 27, 0),
(297, 0, '2020-07-28 23:17:00', 'Step 6 Performance', 3, '441424687', 28, 0);

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
  `payment_link` varchar(500) NOT NULL,
  `trans_id` int(11) NOT NULL,
  `is_paid` int(11) NOT NULL DEFAULT 0,
  `eurl` varchar(500) NOT NULL,
  `surl` varchar(500) NOT NULL,
  `payment_response` text NOT NULL,
  `paymentId` varchar(100) NOT NULL,
  `is_sandbox` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`, `is_paid`, `eurl`, `surl`, `payment_response`, `paymentId`, `is_sandbox`, `status`) VALUES
(1, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-25 17:17:27', 'a:7:{s:2:\"Id\";i:101233;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/nsgVP\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123334&paymentGatewayId=6', 101233, 0, '', '', '', '', 0, 0),
(2, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 10, 3, '2020-01-25 17:17:40', 'a:7:{s:2:\"Id\";i:101234;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/fnisn\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123434&paymentGatewayId=6', 101234, 0, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', '', '', 0, 0),
(3, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 10, 3, '2020-01-25 17:32:57', 'a:7:{s:2:\"Id\";i:101235;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/Aq9ng\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123535&paymentGatewayId=6', 101235, 0, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', '', '', 0, 0),
(4, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 10, 3, '2020-01-25 17:54:01', 'a:7:{s:2:\"Id\";i:101237;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/ytimu\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123735&paymentGatewayId=6', 101237, 0, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', '', '', 1, 0),
(5, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 10, 4, '2020-01-25 17:54:02', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210123835\";s:16:\"InvoiceReference\";s:10:\"2020010609\";s:11:\"CreatedDate\";s:16:\"January 25, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:4:\"Name\";s:14:\"CustomerMobile\";s:14:\"+9651234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:15:\"TransactionDate\";s:10:\"25/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051012386543856\";s:7:\"TrackId\";s:16:\"25-01-2020_65438\";s:13:\"TransactionId\";s:17:\"06051012386543856\";s:9:\"PaymentId\";s:17:\"06051012386543856\";s:15:\"AuthorizationId\";s:17:\"06051012386543856\";s:7:\"OrderId\";s:6:\"101238\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:9:\"Product01\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123835&paymentGatewayId=6', 101238, 1, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', '', '06051012386543856', 1, 1),
(6, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 10, 4, '2020-01-25 17:55:56', 'a:7:{s:2:\"Id\";i:101239;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/zWnf8\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210123935&paymentGatewayId=6', 101239, 1, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210123935\";s:16:\"InvoiceReference\";s:10:\"2020010610\";s:11:\"CreatedDate\";s:16:\"January 25, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:4:\"Name\";s:14:\"CustomerMobile\";s:14:\"+9651234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:15:\"TransactionDate\";s:10:\"25/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051012396543957\";s:7:\"TrackId\";s:16:\"25-01-2020_65439\";s:13:\"TransactionId\";s:17:\"06051012396543957\";s:9:\"PaymentId\";s:17:\"06051012396543957\";s:15:\"AuthorizationId\";s:17:\"06051012396543957\";s:7:\"OrderId\";s:6:\"101239\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:9:\"Product01\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051012396543957', 1, 0),
(7, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 4, '2020-01-25 18:39:40', 'a:7:{s:2:\"Id\";i:101246;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/Jida6\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210124635&paymentGatewayId=6', 101246, 1, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210124635\";s:16:\"InvoiceReference\";s:10:\"2020010617\";s:11:\"CreatedDate\";s:16:\"January 25, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:4:\"Name\";s:14:\"CustomerMobile\";s:14:\"+9651234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:15:\"TransactionDate\";s:10:\"25/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051012466544056\";s:7:\"TrackId\";s:16:\"25-01-2020_65440\";s:13:\"TransactionId\";s:17:\"06051012466544056\";s:9:\"PaymentId\";s:17:\"06051012466544056\";s:15:\"AuthorizationId\";s:17:\"06051012466544056\";s:7:\"OrderId\";s:6:\"101246\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:9:\"Product01\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051012466544056', 1, 0),
(8, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 3, '2020-01-25 19:22:11', 'a:7:{s:2:\"Id\";i:101253;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/n7UNq\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125335&paymentGatewayId=6', 101253, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(9, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 3, '2020-01-25 19:28:59', 'a:7:{s:2:\"Id\";i:101254;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/dfAct\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210125435&paymentGatewayId=6', 101254, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(10, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 2, '2020-01-25 19:50:05', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(11, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 2, '2020-01-25 20:06:08', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(12, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 2, '2020-01-25 20:06:22', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(13, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 2, '2020-01-25 20:07:35', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(14, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 10, 2, '2020-01-25 20:09:27', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(15, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-25 20:30:37', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(16, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-25 20:45:01', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(17, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-25 20:45:09', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(18, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-25 20:46:03', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(19, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 7, 2, '2020-01-26 11:50:49', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', '', '', 1, 0);
INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`, `is_paid`, `eurl`, `surl`, `payment_response`, `paymentId`, `is_sandbox`, `status`) VALUES
(20, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:52:55', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(21, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:53:41', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(22, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:54:15', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(23, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:54:20', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(24, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:54:51', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(25, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-26 11:56:04', 'a:7:{s:2:\"Id\";i:101385;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/D5YEd\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138535&paymentGatewayId=6', 101385, 0, '', '', '', '', 1, 0),
(26, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-26 11:56:10', 'a:7:{s:2:\"Id\";i:101386;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/i5XNf\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210138635&paymentGatewayId=6', 101386, 0, '', '', '', '', 1, 0),
(27, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:56:36', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(28, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:58:44', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(29, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 11:59:42', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(30, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:00:45', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(31, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:01:34', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(32, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:01:47', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(33, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:21:29', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(34, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:22:59', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(35, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-26 12:23:10', 'a:7:{s:2:\"Id\";i:101398;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/ZVFGI\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210139836&paymentGatewayId=6', 101398, 0, '', '', '', '', 1, 0),
(36, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:23:24', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(37, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:1:\" \";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:29:04', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:12:\"CustomerName\";s:5:\"Error\";s:37:\"The field Customer Name is mandatory.\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(38, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:31:28', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:2:{i:0;a:2:{s:4:\"Name\";s:14:\"CustomerMobile\";s:5:\"Error\";s:39:\"The field Customer Mobile is mandatory.\";}i:1;a:2:{s:4:\"Name\";s:0:\"\";s:5:\"Error\";s:56:\"You are trying to send products not belogs to the vendor\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(39, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:0:\"\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:32:14', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:2:{i:0;a:2:{s:4:\"Name\";s:14:\"CustomerMobile\";s:5:\"Error\";s:39:\"The field Customer Mobile is mandatory.\";}i:1;a:2:{s:4:\"Name\";s:0:\"\";s:5:\"Error\";s:56:\"You are trying to send products not belogs to the vendor\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(40, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";s:2:\"18\";s:11:\"ProductName\";s:4:\"Bids\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 2, '2020-01-26 12:33:28', 'a:4:{s:2:\"Id\";i:0;s:9:\"IsSuccess\";b:0;s:7:\"Message\";s:11:\"Bad Request\";s:12:\"FieldsErrors\";a:1:{i:0;a:2:{s:4:\"Name\";s:0:\"\";s:5:\"Error\";s:56:\"You are trying to send products not belogs to the vendor\";}}}', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(41, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:4:\"Name\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\"+965\";s:14:\"CustomerMobile\";s:10:\"1234567890\";s:13:\"CustomerEmail\";s:14:\"test@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Product01\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:0:\"\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:0:\"\";}', 10, 3, '2020-01-26 12:33:51', 'a:7:{s:2:\"Id\";i:101403;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/j9CEk\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140334&paymentGatewayId=6', 101403, 0, '', '', '', '', 1, 0),
(42, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Bids ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 3, '2020-01-26 12:34:26', 'a:7:{s:2:\"Id\";i:101404;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/Fyjb3\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140434&paymentGatewayId=6', 101404, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(43, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Bids ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 3, '2020-01-26 12:36:04', 'a:7:{s:2:\"Id\";i:101405;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/HUyjT\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140534&paymentGatewayId=6', 101405, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0);
INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`, `is_paid`, `eurl`, `surl`, `payment_response`, `paymentId`, `is_sandbox`, `status`) VALUES
(44, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Bids ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:46:\"http://channelsmedia.net/Mazaad-Market/buybids\";}', 7, 3, '2020-01-26 12:36:35', 'a:7:{s:2:\"Id\";i:101406;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/yQn5X\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140635&paymentGatewayId=6', 101406, 0, 'http://channelsmedia.net/Mazaad-Market/buybids', 'http://channelsmedia.net/Mazaad-Market/buybids', '', '', 1, 0),
(45, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Bids ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:33:\"http://localhost/dashdeal/buybids\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:33:\"http://localhost/dashdeal/buybids\";}', 7, 4, '2020-01-26 12:37:14', 'a:7:{s:2:\"Id\";i:101407;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/OeCHT\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140735&paymentGatewayId=6', 101407, 1, 'http://localhost/dashdeal/buybids', 'http://localhost/dashdeal/buybids', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210140735\";s:16:\"InvoiceReference\";s:10:\"2020010741\";s:11:\"CreatedDate\";s:16:\"January 26, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:7;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"26/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051014076557256\";s:7:\"TrackId\";s:16:\"26-01-2020_65572\";s:13:\"TransactionId\";s:17:\"06051014076557256\";s:9:\"PaymentId\";s:17:\"06051014076557256\";s:15:\"AuthorizationId\";s:17:\"06051014076557256\";s:7:\"OrderId\";s:6:\"101407\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:9:\"Bids ID18\";s:9:\"UnitPrice\";s:5:\"7.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:5:\"7.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:5:\"7.000\";s:15:\"TransationValue\";s:5:\"7.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:5:\"7.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:8:\"7.000 KD\";}', '06051014076557256', 1, 0),
(46, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Bids ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:50:\"http://localhost/dashdeal/payment_response/pack/32\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:50:\"http://localhost/dashdeal/payment_response/pack/32\";}', 7, 4, '2020-01-26 12:39:47', 'a:7:{s:2:\"Id\";i:101408;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/nnOwh\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210140835&paymentGatewayId=6', 101408, 1, 'http://localhost/dashdeal/payment_response/pack/32', 'http://localhost/dashdeal/payment_response/pack/32', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210140835\";s:16:\"InvoiceReference\";s:10:\"2020010742\";s:11:\"CreatedDate\";s:16:\"January 26, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:7;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"26/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051014086557356\";s:7:\"TrackId\";s:16:\"26-01-2020_65573\";s:13:\"TransactionId\";s:17:\"06051014086557356\";s:9:\"PaymentId\";s:17:\"06051014086557356\";s:15:\"AuthorizationId\";s:17:\"06051014086557356\";s:7:\"OrderId\";s:6:\"101408\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:9:\"Bids ID18\";s:9:\"UnitPrice\";s:5:\"7.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:5:\"7.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:5:\"7.000\";s:15:\"TransationValue\";s:5:\"7.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:5:\"7.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:8:\"7.000 KD\";}', '06051014086557356', 1, 0),
(47, 12, 'a:20:{s:12:\"InvoiceValue\";i:7;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:9:\"Bids ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:7;}}s:11:\"CallBackUrl\";s:49:\"http://localhost/dashdeal/payment_response/pack/1\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:49:\"http://localhost/dashdeal/payment_response/pack/1\";}', 7, 4, '2020-01-26 12:50:32', 'a:7:{s:2:\"Id\";i:101410;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/ZZQPW\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210141034&paymentGatewayId=6', 101410, 1, 'http://localhost/dashdeal/payment_response/pack/1', 'http://localhost/dashdeal/payment_response/pack/1', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210141034\";s:16:\"InvoiceReference\";s:10:\"2020010744\";s:11:\"CreatedDate\";s:16:\"January 26, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:7;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"26/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051014106557556\";s:7:\"TrackId\";s:16:\"26-01-2020_65575\";s:13:\"TransactionId\";s:17:\"06051014106557556\";s:9:\"PaymentId\";s:17:\"06051014106557556\";s:15:\"AuthorizationId\";s:17:\"06051014106557556\";s:7:\"OrderId\";s:6:\"101410\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:9:\"Bids ID18\";s:9:\"UnitPrice\";s:5:\"7.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:5:\"7.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:5:\"7.000\";s:15:\"TransationValue\";s:5:\"7.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:5:\"7.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:8:\"7.000 KD\";}', '06051014106557556', 1, 0),
(48, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/11\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/11\";}', 10, 2, '2020-01-26 15:40:33', 'N;', '', 0, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/11', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/11', '', '', 1, 0),
(49, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/12\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/12\";}', 10, 3, '2020-01-26 15:41:01', 'a:7:{s:2:\"Id\";i:101461;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/AQny5\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146135&paymentGatewayId=6', 101461, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/12', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/12', '', '', 1, 0),
(50, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/13\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/13\";}', 10, 4, '2020-01-26 15:41:01', 'a:7:{s:2:\"Id\";i:101462;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/RRM9D\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210146235&paymentGatewayId=6', 101462, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/13', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/13', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210146235\";s:16:\"InvoiceReference\";s:10:\"2020010785\";s:11:\"CreatedDate\";s:16:\"January 26, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"26/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051014626561756\";s:7:\"TrackId\";s:16:\"26-01-2020_65617\";s:13:\"TransactionId\";s:17:\"06051014626561756\";s:9:\"PaymentId\";s:17:\"06051014626561756\";s:15:\"AuthorizationId\";s:17:\"06051014626561756\";s:7:\"OrderId\";s:6:\"101462\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051014626561756', 1, 0),
(51, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/14\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/14\";}', 10, 3, '2020-01-26 16:53:48', 'a:7:{s:2:\"Id\";i:101478;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/49VJW\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210147835&paymentGatewayId=6', 101478, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/14', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/14', '', '', 1, 0),
(52, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/16\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/16\";}', 10, 4, '2020-01-26 17:03:47', 'a:7:{s:2:\"Id\";i:101481;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/ja4Ah\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148135&paymentGatewayId=6', 101481, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/16', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/16', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210148135\";s:16:\"InvoiceReference\";s:10:\"2020010802\";s:11:\"CreatedDate\";s:16:\"January 26, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"26/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051014816562957\";s:7:\"TrackId\";s:16:\"26-01-2020_65629\";s:13:\"TransactionId\";s:17:\"06051014816562957\";s:9:\"PaymentId\";s:17:\"06051014816562957\";s:15:\"AuthorizationId\";s:17:\"06051014816562957\";s:7:\"OrderId\";s:6:\"101481\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051014816562957', 1, 0),
(53, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/1\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/1\";}', 10, 4, '2020-01-26 17:15:25', 'a:7:{s:2:\"Id\";i:101484;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/H3N4D\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210148435&paymentGatewayId=6', 101484, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/1', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/1', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210148435\";s:16:\"InvoiceReference\";s:10:\"2020010804\";s:11:\"CreatedDate\";s:16:\"January 26, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"26/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051014846563256\";s:7:\"TrackId\";s:16:\"26-01-2020_65632\";s:13:\"TransactionId\";s:17:\"06051014846563256\";s:9:\"PaymentId\";s:17:\"06051014846563256\";s:15:\"AuthorizationId\";s:17:\"06051014846563256\";s:7:\"OrderId\";s:6:\"101484\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051014846563256', 1, 0),
(54, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"viralappstudio@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/2\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/2\";}', 10, 3, '2020-01-28 09:42:51', 'a:7:{s:2:\"Id\";i:102296;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/6ztAL\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229635&paymentGatewayId=6', 102296, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/2', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/2', '', '', 1, 0);
INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`, `is_paid`, `eurl`, `surl`, `payment_response`, `paymentId`, `is_sandbox`, `status`) VALUES
(55, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"viralappstudio@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/3\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/3\";}', 10, 3, '2020-01-28 09:42:52', 'a:7:{s:2:\"Id\";i:102297;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/44sXh\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210229735&paymentGatewayId=6', 102297, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/3', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/3', '', '', 1, 0),
(56, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"viralappstudio@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/4\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/4\";}', 10, 3, '2020-01-28 09:53:44', 'a:7:{s:2:\"Id\";i:102308;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/MIuvT\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210230835&paymentGatewayId=6', 102308, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/4', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/4', '', '', 1, 0),
(57, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/5\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/5\";}', 10, 4, '2020-01-28 13:08:19', 'a:7:{s:2:\"Id\";i:102477;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/quS3E\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210247735&paymentGatewayId=6', 102477, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/5', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/5', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210247735\";s:16:\"InvoiceReference\";s:10:\"2020011511\";s:11:\"CreatedDate\";s:16:\"January 28, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"28/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051024776639657\";s:7:\"TrackId\";s:16:\"28-01-2020_66396\";s:13:\"TransactionId\";s:17:\"06051024776639657\";s:9:\"PaymentId\";s:17:\"06051024776639657\";s:15:\"AuthorizationId\";s:17:\"06051024776639657\";s:7:\"OrderId\";s:6:\"102477\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051024776639657', 1, 0),
(58, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/7\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/7\";}', 10, 3, '2020-01-28 14:54:06', 'a:7:{s:2:\"Id\";i:102555;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/VgVUS\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210255535&paymentGatewayId=6', 102555, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/7', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/7', '', '', 1, 0),
(59, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/8\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:62:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/8\";}', 1002, 4, '2020-01-28 15:06:52', 'a:7:{s:2:\"Id\";i:102565;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/2FW0I\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256535&paymentGatewayId=6', 102565, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/8', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/8', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210256535\";s:16:\"InvoiceReference\";s:10:\"2020011586\";s:11:\"CreatedDate\";s:16:\"January 28, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:1002;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"28/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051025656648057\";s:7:\"TrackId\";s:16:\"28-01-2020_66480\";s:13:\"TransactionId\";s:17:\"06051025656648057\";s:9:\"PaymentId\";s:17:\"06051025656648057\";s:15:\"AuthorizationId\";s:17:\"06051025656648057\";s:7:\"OrderId\";s:6:\"102565\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:9:\"UnitPrice\";s:9:\"1,002.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:9:\"1,002.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:136:\"Missing parameter. If you provide sub-merchant details, you must provide order.subMerchant.identifier and order.subMerchant.tradingName.\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:9:\"1,002.000\";s:15:\"TransationValue\";s:9:\"1,002.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:9:\"1,002.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:12:\"1,002.000 KD\";}', '06051025656648057', 1, 0),
(60, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:65:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/9\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:65:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/9\";}', 1002, 4, '2020-01-28 15:11:11', 'a:7:{s:2:\"Id\";i:102567;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:21:\"http://t.myf.la/uXu5V\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210256735&paymentGatewayId=6', 102567, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/9', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/9', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210256735\";s:16:\"InvoiceReference\";s:10:\"2020011588\";s:11:\"CreatedDate\";s:16:\"January 28, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:1002;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"28/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051025676648257\";s:7:\"TrackId\";s:16:\"28-01-2020_66482\";s:13:\"TransactionId\";s:17:\"06051025676648257\";s:9:\"PaymentId\";s:17:\"06051025676648257\";s:15:\"AuthorizationId\";s:17:\"06051025676648257\";s:7:\"OrderId\";s:6:\"102567\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:9:\"UnitPrice\";s:9:\"1,002.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:9:\"1,002.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:136:\"Missing parameter. If you provide sub-merchant details, you must provide order.subMerchant.identifier and order.subMerchant.tradingName.\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:9:\"1,002.000\";s:15:\"TransationValue\";s:9:\"1,002.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:9:\"1,002.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:12:\"1,002.000 KD\";}', '06051025676648257', 1, 0),
(61, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/10\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/10\";}', 1002, 4, '2020-01-29 15:02:20', 'a:7:{s:2:\"Id\";i:102952;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210295235\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210295235&paymentGatewayId=6', 102952, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/10', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/10', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210295235\";s:16:\"InvoiceReference\";s:10:\"2020011886\";s:11:\"CreatedDate\";s:16:\"January 29, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:1002;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"29/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051029526678958\";s:7:\"TrackId\";s:16:\"29-01-2020_66789\";s:13:\"TransactionId\";s:17:\"06051029526678958\";s:9:\"PaymentId\";s:17:\"06051029526678958\";s:15:\"AuthorizationId\";s:17:\"06051029526678958\";s:7:\"OrderId\";s:6:\"102952\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:9:\"UnitPrice\";s:9:\"1,002.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:9:\"1,002.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:9:\"1,002.000\";s:15:\"TransationValue\";s:9:\"1,002.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:9:\"1,002.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:12:\"1,002.000 KD\";}', '06051029526678958', 1, 0),
(62, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/11\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/11\";}', 1002, 3, '2020-01-30 13:22:08', 'a:7:{s:2:\"Id\";i:103398;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210339836\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339836&paymentGatewayId=6', 103398, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/11', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/11', '', '', 1, 0),
(63, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/12\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/12\";}', 1002, 3, '2020-01-30 13:22:30', 'a:7:{s:2:\"Id\";i:103399;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210339936\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210339936&paymentGatewayId=6', 103399, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/12', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/12', '', '', 1, 0),
(64, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/13\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/13\";}', 1002, 3, '2020-01-30 13:22:53', 'a:7:{s:2:\"Id\";i:103400;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210340034\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340034&paymentGatewayId=6', 103400, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/13', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/13', '', '', 1, 0);
INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`, `is_paid`, `eurl`, `surl`, `payment_response`, `paymentId`, `is_sandbox`, `status`) VALUES
(65, 12, 'a:20:{s:12:\"InvoiceValue\";i:1002;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:1002;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/14\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/14\";}', 1002, 4, '2020-01-30 13:37:40', 'a:7:{s:2:\"Id\";i:103407;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210340735\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210340735&paymentGatewayId=6', 103407, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/14', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/14', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210340735\";s:16:\"InvoiceReference\";s:10:\"2020012240\";s:11:\"CreatedDate\";s:16:\"January 30, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:1002;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"30/01/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051034076713556\";s:7:\"TrackId\";s:16:\"30-01-2020_67135\";s:13:\"TransactionId\";s:17:\"06051034076713556\";s:9:\"PaymentId\";s:17:\"06051034076713556\";s:15:\"AuthorizationId\";s:17:\"06051034076713556\";s:7:\"OrderId\";s:6:\"103407\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:42:\"Bedford Jacquard Duvet Cover  Product ID49\";s:9:\"UnitPrice\";s:9:\"1,002.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:9:\"1,002.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:9:\"1,002.000\";s:15:\"TransationValue\";s:9:\"1,002.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:9:\"1,002.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:12:\"1,002.000 KD\";}', '06051034076713556', 1, 0),
(66, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/22\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/22\";}', 31, 3, '2020-01-30 14:37:18', 'a:7:{s:2:\"Id\";i:103454;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210345435\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345435&paymentGatewayId=6', 103454, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/22', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/22', '', '', 1, 0),
(67, 12, 'a:20:{s:12:\"InvoiceValue\";i:129;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:129;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/23\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/23\";}', 129, 3, '2020-01-30 14:38:06', 'a:7:{s:2:\"Id\";i:103455;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210345535\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210345535&paymentGatewayId=6', 103455, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/23', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/23', '', '', 1, 0),
(68, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/31\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/31\";}', 10, 3, '2020-01-30 14:41:59', 'a:7:{s:2:\"Id\";i:103460;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210346035\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346035&paymentGatewayId=6', 103460, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/31', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/31', '', '', 1, 0),
(69, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580391995;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/38\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/38\";}', 31, 3, '2020-01-30 14:46:36', 'a:7:{s:2:\"Id\";i:103466;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210346635\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346635&paymentGatewayId=6', 103466, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/38', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/38', '', '', 1, 0),
(70, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:25:\"hafizshazim1990@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/39\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/39\";}', 10, 3, '2020-01-30 14:46:37', 'a:7:{s:2:\"Id\";i:103467;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210346735\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346735&paymentGatewayId=6', 103467, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/39', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/39', '', '', 1, 0),
(71, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:25:\"hafizshazim1990@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/40\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/40\";}', 10, 3, '2020-01-30 14:46:38', 'a:7:{s:2:\"Id\";i:103468;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210346836\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210346836&paymentGatewayId=6', 103468, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/40', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/40', '', '', 1, 0),
(72, 12, 'a:20:{s:12:\"InvoiceValue\";i:60;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580392384;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"COTTON CHINO BELTED SHIRTDRESS  Product ID73\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:60;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/42\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/42\";}', 60, 3, '2020-01-30 14:53:05', 'a:7:{s:2:\"Id\";i:103478;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210347836\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210347836&paymentGatewayId=6', 103478, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/42', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/42', '', '', 1, 0),
(73, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580392819;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/44\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/44\";}', 31, 3, '2020-01-30 15:00:20', 'a:7:{s:2:\"Id\";i:103484;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210348435\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210348435&paymentGatewayId=6', 103484, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/44', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/44', '', '', 1, 0),
(74, 12, 'a:20:{s:12:\"InvoiceValue\";i:129;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580634280;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:129;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/45\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/45\";}', 129, 3, '2020-02-02 10:04:41', 'a:7:{s:2:\"Id\";i:103988;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210398836\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210398836&paymentGatewayId=6', 103988, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/45', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/45', '', '', 1, 0),
(75, 12, 'a:20:{s:12:\"InvoiceValue\";i:129;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580639932;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:129;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/46\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/46\";}', 129, 4, '2020-02-02 11:38:53', 'a:7:{s:2:\"Id\";i:104031;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210403134\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403134&paymentGatewayId=6', 104031, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/46', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/46', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210403134\";s:16:\"InvoiceReference\";s:10:\"2020012841\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:129;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040316761155\";s:7:\"TrackId\";s:16:\"02-02-2020_67611\";s:13:\"TransactionId\";s:17:\"06051040316761155\";s:9:\"PaymentId\";s:17:\"06051040316761155\";s:15:\"AuthorizationId\";s:17:\"06051040316761155\";s:7:\"OrderId\";s:6:\"104031\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:9:\"UnitPrice\";s:7:\"129.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:7:\"129.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:7:\"129.000\";s:15:\"TransationValue\";s:7:\"129.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:7:\"129.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:10:\"129.000 KD\";}', '06051040316761155', 1, 0);
INSERT INTO `links` (`linkID`, `uid`, `request_data`, `amount`, `step`, `creat_at`, `fat_response`, `payment_link`, `trans_id`, `is_paid`, `eurl`, `surl`, `payment_response`, `paymentId`, `is_sandbox`, `status`) VALUES
(76, 12, 'a:20:{s:12:\"InvoiceValue\";i:129;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580640095;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:129;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/47\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/47\";}', 129, 3, '2020-02-02 11:41:36', 'a:7:{s:2:\"Id\";i:104033;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210403334\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403334&paymentGatewayId=6', 104033, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/47', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/47', '', '', 1, 0),
(77, 12, 'a:20:{s:12:\"InvoiceValue\";i:60;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580640210;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"COTTON CHINO BELTED SHIRTDRESS  Product ID73\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:60;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/48\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/48\";}', 60, 4, '2020-02-02 11:43:30', 'a:7:{s:2:\"Id\";i:104034;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210403435\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403435&paymentGatewayId=6', 104034, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/48', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/48', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210403435\";s:16:\"InvoiceReference\";s:10:\"2020012844\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:60;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040346761456\";s:7:\"TrackId\";s:16:\"02-02-2020_67614\";s:13:\"TransactionId\";s:17:\"06051040346761456\";s:9:\"PaymentId\";s:17:\"06051040346761456\";s:15:\"AuthorizationId\";s:17:\"06051040346761456\";s:7:\"OrderId\";s:6:\"104034\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:44:\"COTTON CHINO BELTED SHIRTDRESS  Product ID73\";s:9:\"UnitPrice\";s:6:\"60.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"60.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"60.000\";s:15:\"TransationValue\";s:6:\"60.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"60.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"60.000 KD\";}', '06051040346761456', 1, 0),
(78, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:15:\"info@zealcg.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/49\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/49\";}', 10, 4, '2020-02-02 11:49:17', 'a:7:{s:2:\"Id\";i:104036;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210403635\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403635&paymentGatewayId=6', 104036, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/49', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/49', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210403635\";s:16:\"InvoiceReference\";s:10:\"2020012846\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:15:\"info@zealcg.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040366761556\";s:7:\"TrackId\";s:16:\"02-02-2020_67615\";s:13:\"TransactionId\";s:17:\"06051040366761556\";s:9:\"PaymentId\";s:17:\"06051040366761556\";s:15:\"AuthorizationId\";s:17:\"06051040366761556\";s:7:\"OrderId\";s:6:\"104036\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051040366761556', 1, 0),
(79, 12, 'a:20:{s:12:\"InvoiceValue\";i:100;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580640772;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:15:\"info@zealcg.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:37:\"RUFFLED COTTON CARDIGAN  Product ID81\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:100;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/50\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/50\";}', 100, 4, '2020-02-02 11:52:52', 'a:7:{s:2:\"Id\";i:104037;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.MyFatoorah.com/ie/0106210403735\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.MyFatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210403735&paymentGatewayId=6', 104037, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/50', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/50', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210403735\";s:16:\"InvoiceReference\";s:10:\"2020012847\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:100;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:15:\"info@zealcg.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040376761757\";s:7:\"TrackId\";s:16:\"02-02-2020_67617\";s:13:\"TransactionId\";s:17:\"06051040376761757\";s:9:\"PaymentId\";s:17:\"06051040376761757\";s:15:\"AuthorizationId\";s:17:\"06051040376761757\";s:7:\"OrderId\";s:6:\"104037\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:37:\"RUFFLED COTTON CARDIGAN  Product ID81\";s:9:\"UnitPrice\";s:7:\"100.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:7:\"100.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:7:\"100.000\";s:15:\"TransationValue\";s:7:\"100.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:7:\"100.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:10:\"100.000 KD\";}', '06051040376761757', 1, 0),
(80, 12, 'a:20:{s:12:\"InvoiceValue\";i:129;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580641934;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:129;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/51\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/51\";}', 129, 4, '2020-02-02 12:12:14', 'a:7:{s:2:\"Id\";i:104040;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210404034\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404034&paymentGatewayId=6', 104040, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/51', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/51', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210404034\";s:16:\"InvoiceReference\";s:10:\"2020012850\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:129;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040406761956\";s:7:\"TrackId\";s:16:\"02-02-2020_67619\";s:13:\"TransactionId\";s:17:\"06051040406761956\";s:9:\"PaymentId\";s:17:\"06051040406761956\";s:15:\"AuthorizationId\";s:17:\"06051040406761956\";s:7:\"OrderId\";s:6:\"104040\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:44:\"PATCHWORK CANVAS BIG PONY TOTE  Product ID35\";s:9:\"UnitPrice\";s:7:\"129.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:7:\"129.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:7:\"129.000\";s:15:\"TransationValue\";s:7:\"129.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:7:\"129.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:10:\"129.000 KD\";}', '06051040406761956', 1, 1),
(81, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580642864;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/52\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/52\";}', 31, 3, '2020-02-02 12:27:45', 'a:7:{s:2:\"Id\";i:104045;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210404535\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404535&paymentGatewayId=6', 104045, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/52', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/52', '', '', 1, 1),
(82, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580642883;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/53\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/53\";}', 31, 3, '2020-02-02 12:28:03', 'a:7:{s:2:\"Id\";i:104046;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210404635\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404635&paymentGatewayId=6', 104046, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/53', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/53', '', '', 1, 1),
(83, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580642886;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/54\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/54\";}', 31, 3, '2020-02-02 12:28:06', 'a:7:{s:2:\"Id\";i:104047;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210404735\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404735&paymentGatewayId=6', 104047, 0, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/54', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/54', '', '', 1, 1),
(84, 12, 'a:20:{s:12:\"InvoiceValue\";i:31;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";i:1580642887;s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:31;}}s:11:\"CallBackUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/55\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:66:\"http://channelsmedia.net/Mazaad-Market/payment_response/product/55\";}', 31, 4, '2020-02-02 12:28:08', 'a:7:{s:2:\"Id\";i:104048;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210404835\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210404835&paymentGatewayId=6', 104048, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/product/55', 'http://channelsmedia.net/Mazaad-Market/payment_response/product/55', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210404835\";s:16:\"InvoiceReference\";s:10:\"2020012855\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:31;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:24:\"itzsolution786@gmail.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040486762557\";s:7:\"TrackId\";s:16:\"02-02-2020_67625\";s:13:\"TransactionId\";s:17:\"06051040486762557\";s:9:\"PaymentId\";s:17:\"06051040486762557\";s:15:\"AuthorizationId\";s:17:\"06051040486762557\";s:7:\"OrderId\";s:6:\"104048\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:26:\"BANKS SANDAL  Product ID71\";s:9:\"UnitPrice\";s:6:\"31.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"31.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"31.000\";s:15:\"TransationValue\";s:6:\"31.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"31.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"31.000 KD\";}', '06051040486762557', 1, 1),
(85, 12, 'a:20:{s:12:\"InvoiceValue\";i:10;s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:13:\"CustomerBlock\";s:5:\"Block\";s:14:\"CustomerStreet\";s:6:\"Street\";s:23:\"CustomerHouseBuildingNo\";s:11:\"Building no\";s:15:\"CustomerCivilId\";s:12:\"123456789124\";s:15:\"CustomerAddress\";s:15:\"Payment Address\";s:17:\"CustomerReference\";s:6:\"\'.$t.\'\";s:23:\"DisplayCurrencyIsoAlpha\";s:3:\"KWD\";s:13:\"CountryCodeId\";s:4:\" 965\";s:14:\"CustomerMobile\";s:9:\"123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:17:\"DisplayCurrencyId\";i:3;s:17:\"SendInvoiceOption\";i:1;s:18:\"InvoiceItemsCreate\";a:1:{i:0;a:4:{s:9:\"ProductId\";N;s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:8:\"Quantity\";i:1;s:9:\"UnitPrice\";i:10;}}s:11:\"CallBackUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/56\";s:8:\"Language\";i:2;s:10:\"ExpireDate\";s:24:\"2022-12-31T13:30:17.812Z\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:8:\"ErrorUrl\";s:63:\"http://channelsmedia.net/Mazaad-Market/payment_response/pack/56\";}', 10, 4, '2020-02-02 13:51:45', 'a:7:{s:2:\"Id\";i:104076;s:9:\"IsSuccess\";b:1;s:7:\"Message\";s:28:\"Record Created Successfully!\";s:11:\"RedirectUrl\";s:44:\"https://demo.myfatoorah.com/ie/0106210407635\";s:12:\"FieldsErrors\";N;s:14:\"PaymentMethods\";a:10:{i:0;a:3:{s:17:\"PaymentMethodName\";s:4:\"AMEX\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=3\";s:17:\"PaymentMethodCode\";s:2:\"ae\";}i:1;a:3:{s:17:\"PaymentMethodName\";s:5:\"Sadad\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=4\";s:17:\"PaymentMethodCode\";s:1:\"s\";}i:2;a:3:{s:17:\"PaymentMethodName\";s:7:\"Benefit\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=5\";s:17:\"PaymentMethodCode\";s:1:\"b\";}i:3;a:3:{s:17:\"PaymentMethodName\";s:11:\"VISA/MASTER\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=6\";s:17:\"PaymentMethodCode\";s:2:\"vm\";}i:4;a:3:{s:17:\"PaymentMethodName\";s:17:\"Qatar Debit Cards\";s:16:\"PaymentMethodUrl\";s:94:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=8\";s:17:\"PaymentMethodCode\";s:2:\"np\";}i:5;a:3:{s:17:\"PaymentMethodName\";s:4:\"MADA\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=10\";s:17:\"PaymentMethodCode\";s:2:\"md\";}i:6;a:3:{s:17:\"PaymentMethodName\";s:4:\"KNET\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=20\";s:17:\"PaymentMethodCode\";s:2:\"kn\";}i:7;a:3:{s:17:\"PaymentMethodName\";s:9:\"Apple Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=21\";s:17:\"PaymentMethodCode\";s:2:\"ap\";}i:8;a:3:{s:17:\"PaymentMethodName\";s:7:\"STC Pay\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=27\";s:17:\"PaymentMethodCode\";s:3:\"stc\";}i:9;a:3:{s:17:\"PaymentMethodName\";s:15:\"UAE Debit Cards\";s:16:\"PaymentMethodUrl\";s:95:\"https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=28\";s:17:\"PaymentMethodCode\";s:5:\"uaecc\";}}s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";}', 'https://demo.myfatoorah.com/En/PayInvoice/Checkout?invoiceKey=0106210407635&paymentGatewayId=6', 104076, 1, 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/56', 'http://channelsmedia.net/Mazaad-Market/payment_response/pack/56', 'a:28:{s:9:\"InvoiceId\";s:13:\"0106210407635\";s:16:\"InvoiceReference\";s:10:\"2020012869\";s:11:\"CreatedDate\";s:16:\"February 2, 2020\";s:10:\"ExpireDate\";s:17:\"December 31, 2022\";s:12:\"InvoiceValue\";d:10;s:8:\"Comments\";s:0:\"\";s:12:\"CustomerName\";s:15:\"Mazaad customer\";s:14:\"CustomerMobile\";s:13:\"+965123456789\";s:13:\"CustomerEmail\";s:26:\"raheelshehzad188@gmail.com\";s:15:\"TransactionDate\";s:10:\"02/02/2020\";s:14:\"PaymentGateway\";s:11:\"VISA/MASTER\";s:11:\"ReferenceId\";s:17:\"06051040766764457\";s:7:\"TrackId\";s:16:\"02-02-2020_67644\";s:13:\"TransactionId\";s:17:\"06051040766764457\";s:9:\"PaymentId\";s:17:\"06051040766764457\";s:15:\"AuthorizationId\";s:17:\"06051040766764457\";s:7:\"OrderId\";s:6:\"104076\";s:12:\"InvoiceItems\";a:1:{i:0;a:4:{s:11:\"ProductName\";s:24:\"Basic  Bids Package ID18\";s:9:\"UnitPrice\";s:6:\"10.000\";s:8:\"Quantity\";s:1:\"1\";s:14:\"ExtendedAmount\";s:6:\"10.000\";}}s:17:\"TransactionStatus\";i:3;s:5:\"Error\";s:8:\"DECLINED\";s:12:\"PaidCurrency\";s:2:\"KD\";s:17:\"PaidCurrencyValue\";s:6:\"10.000\";s:15:\"TransationValue\";s:6:\"10.000\";s:21:\"CustomerServiceCharge\";s:5:\"0.000\";s:8:\"DueValue\";s:6:\"10.000\";s:8:\"Currency\";s:2:\"KD\";s:15:\"ApiCustomFileds\";s:27:\"weight=10,size=L,lenght=170\";s:19:\"InvoiceDisplayValue\";s:9:\"10.000 KD\";}', '06051040766764457', 1, 1);

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
  `localPath` varchar(500) NOT NULL,
  `cloud_id` varchar(90) NOT NULL,
  `cloud_url` varchar(90) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`mediaID`, `public_id`, `url`, `cloudinary`, `localPath`, `cloud_id`, `cloud_url`) VALUES
(1, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(2, '', '', 1, '/uploads/a912cadd-2987-4ae0-9805-98ae5deeb5ed.jpg', '', ''),
(3, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(4, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(5, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(6, '', '', 1, '/uploads/a912cadd-2987-4ae0-9805-98ae5deeb5ed.jpg', '', ''),
(7, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(8, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(9, '', '', 1, '/uploads/127254049_1874918405994166_6023938490453180063_o.jpg', '', ''),
(10, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(11, '', '', 1, '/uploads/20799077_891687167650633_8596979758274812040_n.jpg', '', ''),
(12, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(13, '', '', 1, '/uploads/sample.png', '', ''),
(14, '', '', 1, '/uploads/1200px-Kubernetes_logo_without_workmark.svg.png', '', ''),
(15, '', '', 1, '/uploads/1200px-Kubernetes_logo_without_workmark.svg.png', '', ''),
(16, '', '', 1, '/uploads/1200px-Kubernetes_logo_without_workmark.svg.png', '', ''),
(17, '', '', 1, '/uploads/docker.png', '', ''),
(18, '', '', 1, '/uploads/Desert.jpg', '', ''),
(19, '', '', 1, '/uploads/Desert.jpg', '', ''),
(20, '', '', 1, '/uploads/Desert.jpg', '', ''),
(21, '', '', 1, '/uploads/Desert.jpg', '', ''),
(22, '', '', 1, '/uploads/Desert.jpg', '', ''),
(23, '', '', 1, '/uploads/Desert.jpg', '', ''),
(24, '', '', 1, '/uploads/Desert.jpg', '', ''),
(25, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(26, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(27, '', '', 1, '/uploads/Desert.jpg', '', ''),
(28, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(29, '', '', 1, '/uploads/Desert.jpg', '', ''),
(30, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(31, '', '', 1, '/uploads/Desert.jpg', '', ''),
(32, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(33, '', '', 1, '/uploads/Penguins.jpg', '', ''),
(34, '', '', 1, '/uploads/Penguins.jpg', '', ''),
(35, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(36, '', '', 1, '/uploads/Tulips.jpg', '', ''),
(37, '', '', 1, '/uploads/jquer.png', '', ''),
(38, '', '', 1, '/uploads/Doctype.png', '', ''),
(39, '', '', 1, '/uploads/cameras.png', '', ''),
(40, '', '', 1, '/uploads/Screenshot_1.png', '', ''),
(41, '', '', 1, '/uploads/Screenshot_1.png', '', ''),
(42, '', '', 1, '/uploads/Screenshot_1.png', '', ''),
(43, '', '', 1, '/uploads/Screenshot_2.png', '', ''),
(44, '', '', 1, '/uploads/download.jpg', '', ''),
(45, '', '', 1, '/uploads/nutrition.png', '', ''),
(46, '', '', 1, '/uploads/download (1).jpg', '', ''),
(47, '', '', 1, '/uploads/t-t-18618-types-of-nutrients-pyramid-poster_ver_2.jpg', '', ''),
(48, '', '', 1, '/uploads/cover-1.jpg', '', ''),
(49, '', '', 1, '/uploads/Healthy-Lifestyle.jpg', '', ''),
(50, '', '', 1, '/uploads/0.jpg', '', ''),
(51, '', '', 1, '/uploads/obesity-in-america-1-051012.jpg', '', ''),
(52, '', '', 1, '/uploads/0 (1).jpg', '', ''),
(53, '', '', 1, '/uploads/0 (2).jpg', '', ''),
(54, '', '', 1, '/uploads/istockphoto-1246960593-612x612.jpg', '', ''),
(55, '', '', 1, '/uploads/360_F_315929987_auQ9HWs169bhJqFCYgYge3qr5A6ISgDt.jpg', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE `module` (
  `id` int(11) NOT NULL,
  `cID` int(11) NOT NULL,
  `name` varchar(500) NOT NULL,
  `credits` int(11) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `detail` text NOT NULL,
  `badge` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `module`
--

INSERT INTO `module` (`id`, `cID`, `name`, `credits`, `mediaID`, `status`, `detail`, `badge`) VALUES
(1, 6, 'sdsa', 10, 37, 0, 'test discription', 27),
(2, 3, 'Declare the Doctype of an HTML Document', 10, 38, 0, '', 0),
(3, 7, 'Background on Food & Nutrients', 100, 47, 0, 'In this section we will examine the social and cultural shifts that have contributed to our modern epidemics of overweight and obesity. We will briefly review the nutrients found in foods, their different functions in the human body and how we can support our own health by choosing wisely from the foods within each category.', 27),
(4, 7, 'Contemporary Trends in Eating', 100, 48, 0, 'In this section, we will explore the ways in which highly processed foods differ from real, whole food and the implications of food processing on our health. We’ll also look at how our consumption of sugar has changed in recent decades and explore sensible solutions for people who wish to start eating better. We will also meet Kevin, a middle-aged pre-diabetic man, and find out how a step-wise approach to behavior change helped him change for the better.', 27),
(5, 7, 'Future Directions in Health', 100, 49, 0, 'This section focuses on sustainable solutions to the challenge of choosing healthier foods more frequently. Michael Pollan explains his mantra and how we can use it to make better food choices. We also begin to explore practical tips for preparing foods that will support our health and enjoyment.', 27);

-- --------------------------------------------------------

--
-- Table structure for table `music`
--

CREATE TABLE `music` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `title` text NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `audioID` int(11) NOT NULL,
  `discription` text NOT NULL,
  `albumID` int(11) NOT NULL,
  `artistID` int(11) NOT NULL,
  `catID` int(11) NOT NULL,
  `album_img` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `music`
--

INSERT INTO `music` (`id`, `userID`, `create_at`, `title`, `mediaID`, `audioID`, `discription`, `albumID`, `artistID`, `catID`, `album_img`, `status`, `sort`) VALUES
(1, 1, '2020-04-11 15:37:11', 'knet', 6, 0, '', 0, 0, 0, 0, 1, 0),
(2, 1, '2020-04-12 16:56:13', 'video1', 10, 11, 'hggdfgsdfdf', 1, 2, 0, 0, 1, 0),
(3, 1, '2020-04-12 17:20:13', ' vcz', 12, 13, 'ghdfgs', 1, 2, 0, 0, 1, 0),
(4, 1, '2020-04-12 17:32:53', 'Musa', 14, 15, 'Musa viseo test', 1, 2, 0, 0, 1, 0),
(5, 1, '2020-04-13 15:08:59', 'I am titole', 16, 17, 'i am disc', 1, 2, 0, 0, 1, 0),
(6, 1, '2020-04-15 15:58:48', 'para no 1', 19, 20, 'uyuyryryrty', 1, 2, 0, 0, 1, 0),
(7, 1, '2020-04-16 16:47:52', 'song1', 24, 25, '', 2, 1, 2, 0, 1, 0),
(8, 14, '2020-04-30 23:54:51', 'Angel', 478, 156, '', 8, 16, 9, 0, 0, 0),
(9, 14, '2020-05-01 00:01:29', 'Get It Up', 166, 167, '', 8, 12, 7, 0, 0, 0),
(10, 14, '2020-05-01 00:06:42', 'OVRTME (Ft. Rocco808)', 479, 215, '', 8, 15, 11, 0, 0, 0),
(11, 14, '2020-05-02 19:13:51', 'Save That (Ft. Kayo Genesis)', 183, 184, '', 8, 13, 9, 0, 0, 0),
(12, 14, '2020-05-02 19:27:56', 'Hands Down (Ft. Kayo Genesis)', 168, 169, '', 8, 14, 11, 0, 0, 0),
(13, 14, '2020-05-02 19:30:19', 'Follow', 547, 160, '', 8, 18, 11, 0, 0, 0),
(14, 14, '2020-05-02 19:34:11', 'Cave (Ft. Joe Con)', 147, 148, '', 5, 5, 9, 0, 0, 0),
(15, 14, '2020-05-02 21:27:42', 'Through The Night', 195, 196, '', 5, 5, 9, 0, 0, 0),
(16, 14, '2020-05-08 21:19:43', 'Edible', 153, 154, '', 7, 7, 10, 0, 0, 0),
(17, 14, '2020-05-08 21:34:37', '24 Hours', 0, 0, '', 7, 8, 11, 0, 1, 0),
(18, 14, '2020-05-08 21:35:31', '24 Hours', 476, 0, '', 9, 8, 11, 0, 0, 0),
(19, 14, '2020-05-08 21:49:01', 'Footprints on the Moon', 161, 162, '', 9, 8, 11, 0, 0, 0),
(20, 14, '2020-05-08 21:49:24', 'Planet Radio', 181, 182, '', 9, 8, 11, 1, 0, 0),
(21, 14, '2020-05-08 21:54:02', 'Paradise', 179, 180, '', 5, 5, 9, 1, 0, 0),
(22, 14, '2020-05-08 21:56:31', 'Start to Close In (Ft MJ Ultra)', 191, 192, '', 8, 6, 5, 0, 0, 0),
(23, 14, '2020-05-08 22:17:25', 'BOXINLION (Ft MJ Ultra)', 81, 0, '', 8, 5, 4, 0, 1, 0),
(24, 14, '2020-05-09 14:13:51', 'Black and White', 145, 146, '', 8, 29, 4, 0, 0, 0),
(25, 14, '2020-05-09 14:18:30', 'LA \'09', 172, 173, '', 8, 5, 10, 0, 0, 0),
(26, 14, '2020-05-09 14:23:13', 'Flow', 487, 158, '', 8, 7, 11, 0, 0, 0),
(27, 14, '2020-05-09 14:25:31', 'Shine', 86, 0, '', 8, 5, 5, 0, 1, 0),
(28, 14, '2020-05-09 14:31:25', 'Shine', 488, 186, '', 8, 29, 5, 0, 0, 0),
(29, 14, '2020-05-09 14:32:22', 'Take It From Me (Ft. Maxwell)', 490, 194, '', 8, 29, 7, 0, 0, 0),
(30, 14, '2020-05-09 14:42:39', 'Don\'t Give Up', 0, 0, '', 8, 20, 11, 0, 1, 0),
(31, 14, '2020-05-09 14:43:41', 'Don\'t Give Up', 480, 152, '', 8, 20, 11, 0, 0, 0),
(32, 14, '2020-05-09 14:46:27', 'Like Yesterday', 489, 175, '', 8, 29, 9, 0, 0, 0),
(33, 14, '2020-05-09 14:55:24', 'Dear Lord', 149, 150, '', 8, 29, 11, 0, 0, 0),
(34, 14, '2020-05-09 15:09:26', 'Handsy', 545, 171, '', 8, 18, 4, 0, 0, 0),
(35, 14, '2020-05-09 15:12:33', 'Never About You (Ft. Maxwell)', 477, 176, '', 8, 29, 9, 0, 0, 0),
(36, 14, '2020-05-09 15:26:21', 'Splendid (Ft. ERN)', 573, 190, '', 8, 29, 11, 0, 0, 0),
(37, 14, '2020-05-09 15:27:20', 'Splash on Me', 187, 188, '', 8, 5, 9, 0, 0, 0),
(38, 14, '2020-05-09 15:43:24', 'Fracture', 546, 165, '', 8, 18, 4, 0, 0, 0),
(39, 14, '2020-05-09 17:11:39', 'shri test', 104, 0, '', 9, 14, 10, 0, 1, 0),
(40, 14, '2020-05-09 17:45:45', '5 test', 0, 0, '', 7, 5, 11, 1, 1, 0),
(41, 14, '2020-05-09 17:50:54', '5 test1', 115, 116, '', 9, 20, 11, 1, 1, 0),
(42, 14, '2020-05-09 17:53:46', 'para no 1', 117, 118, '', 9, 13, 9, 1, 1, 0),
(43, 14, '2020-05-11 23:48:18', 'Footprints on the Moon (DJ Halo Remix)', 120, 121, '', 9, 8, 11, 0, 0, 0),
(44, 14, '2020-05-15 17:14:09', 'Black and White (feat.MJ Ultra)', 0, 129, '', 9, 22, 11, 0, 1, 0),
(45, 14, '2020-05-15 17:14:46', 'MJ Ultra - Like Yesterday', 0, 0, '', 9, 22, 11, 0, 1, 0),
(46, 14, '2020-05-15 17:16:16', 'MJ Ultra - Through the Night', 0, 130, '', 9, 22, 11, 0, 1, 0),
(47, 14, '2020-05-15 17:19:19', 'LA', 0, 131, '', 9, 22, 11, 0, 1, 0),
(48, 14, '2020-05-15 17:20:00', 'Cave (feat Joe Con)', 0, 132, '', 9, 22, 11, 0, 1, 0),
(49, 14, '2020-05-15 17:22:42', 'Shine', 0, 133, '', 9, 22, 11, 0, 1, 0),
(50, 14, '2020-05-15 17:24:02', 'Splash', 0, 134, '', 9, 22, 11, 0, 1, 0),
(51, 14, '2020-05-15 17:26:25', 'Fudge - Planet', 0, 135, '', 9, 22, 11, 0, 1, 0),
(52, 14, '2020-05-15 17:27:28', 'Take It From Me (feat Maxwell D)', 0, 136, '', 9, 22, 11, 0, 1, 0),
(53, 14, '2020-05-15 17:28:15', 'Never About You (feat. Maxwell D)', 0, 137, '', 9, 22, 11, 0, 1, 0),
(54, 14, '2020-05-15 17:29:41', 'ERN & MJ Ultra - Splendid', 0, 138, '', 9, 22, 11, 0, 1, 0),
(55, 14, '2020-05-15 17:31:04', 'Follow', 0, 139, '', 9, 22, 11, 0, 1, 0),
(56, 14, '2020-05-15 17:32:18', 'Fracture', 0, 140, '', 9, 22, 11, 0, 1, 0),
(57, 14, '2020-05-15 17:33:14', 'Handsy', 0, 141, '', 9, 22, 11, 0, 1, 0),
(58, 14, '2020-05-15 17:34:19', 'Don\'t give up', 0, 142, '', 9, 22, 11, 0, 1, 0),
(59, 14, '2020-05-15 17:37:08', 'FOOTPRINTS ON THE MOON', 0, 143, '', 9, 22, 11, 0, 1, 0),
(60, 14, '2020-06-01 11:10:15', 'edit del testing  remove', 222, 0, '', 7, 7, 10, 0, 1, 0),
(61, 14, '2020-07-07 15:35:32', 'edit', 388, 386, '', 5, 7, 9, 0, 1, 0),
(62, 14, '2020-07-07 15:42:51', 'addd', 398, 397, '', 7, 18, 5, 1, 1, 0),
(63, 14, '2020-07-27 01:43:31', 'Concrete Runway', 481, 482, '', 8, 13, 11, 0, 0, 0),
(64, 14, '2020-08-05 23:53:41', 'Let It Out', 611, 501, '', 7, 7, 7, 0, 0, 0),
(65, 14, '2020-08-06 00:49:42', 'Feels Good', 502, 503, '', 8, 13, 9, 0, 0, 0),
(66, 14, '2020-08-21 18:38:48', 'Dirt Road', 566, 555, '', 8, 33, 14, 0, 0, 0),
(67, 14, '2020-08-23 15:36:21', 'Got it Bad', 568, 563, '', 8, 33, 14, 0, 0, 0),
(68, 14, '2020-08-23 15:37:21', 'Disapointed', 570, 565, '', 8, 33, 14, 0, 0, 0),
(69, 14, '2020-09-02 00:13:59', 'California in the Sun', 586, 581, '', 12, 31, 11, 0, 0, 0),
(70, 14, '2020-09-02 00:28:22', 'Burning Me Up', 584, 585, '', 8, 31, 11, 0, 0, 0),
(71, 14, '2020-09-02 00:30:29', 'Dark Out', 587, 588, '', 12, 31, 11, 0, 0, 0),
(72, 14, '2020-09-02 00:33:03', 'Sway', 589, 590, '', 8, 31, 11, 0, 0, 0),
(73, 14, '2020-09-02 00:36:20', 'What You Wanted', 591, 592, '', 12, 31, 11, 0, 0, 0),
(74, 14, '2020-09-02 00:45:53', 'Feelin Myself', 593, 594, '', 8, 31, 11, 0, 0, 0),
(75, 14, '2020-09-02 00:47:44', 'Tell Me Something', 595, 596, '', 8, 31, 11, 0, 0, 0),
(76, 14, '2020-09-02 00:49:45', 'Flipside', 597, 598, '', 8, 31, 11, 0, 0, 0),
(77, 14, '2020-09-02 00:50:46', 'I Got You', 599, 600, '', 8, 31, 11, 0, 0, 0),
(78, 14, '2020-09-02 00:52:30', 'On Everything', 601, 602, '', 8, 31, 11, 0, 0, 0),
(79, 14, '2020-09-02 00:53:44', 'Coleslaw', 603, 604, '', 8, 31, 11, 0, 0, 0),
(80, 14, '2020-09-02 00:54:48', 'Carried Away', 605, 606, '', 8, 31, 11, 0, 0, 0),
(81, 14, '2020-09-02 00:56:26', 'Down', 607, 608, '', 8, 31, 11, 0, 0, 0),
(82, 14, '2020-09-02 00:57:25', 'Good For You', 609, 610, '', 8, 31, 11, 0, 0, 0),
(83, 14, '2020-09-05 17:13:30', 'Take the Money', 636, 635, '', 13, 33, 5, 1, 0, 0),
(84, 14, '2020-09-05 17:20:52', 'Long Way Home', 639, 640, '', 13, 33, 9, 0, 0, 0),
(85, 14, '2020-09-05 17:29:07', 'Beautiful', 642, 643, '', 14, 33, 9, 0, 0, 0),
(86, 14, '2020-09-05 17:51:14', 'Dirty White Chucks', 644, 645, '', 14, 33, 14, 0, 0, 0),
(87, 14, '2020-09-05 17:53:55', 'Invisible', 646, 647, '', 14, 33, 14, 0, 0, 0),
(88, 14, '2020-09-05 18:02:15', 'My City', 648, 649, '', 14, 33, 14, 0, 0, 0),
(89, 14, '2020-09-05 18:03:45', 'Tennessee', 650, 651, '', 14, 33, 14, 0, 0, 0),
(90, 14, '2020-09-05 18:06:17', 'Dad', 678, 653, '', 8, 33, 14, 0, 0, 0),
(91, 14, '2020-09-05 18:08:20', 'Hello', 679, 655, '', 8, 33, 14, 0, 0, 0),
(92, 14, '2020-09-05 18:09:44', 'Mistake', 680, 657, '', 8, 33, 14, 0, 0, 0),
(93, 14, '2020-09-18 17:02:45', 'What You Started', 681, 682, '', 8, 34, 5, 0, 0, 0),
(94, 14, '2020-09-18 17:06:45', 'Homegrown-Til The Day I Die', 684, 685, '', 8, 34, 5, 0, 0, 0),
(95, 14, '2020-09-18 17:11:07', 'Highest [Intro]', 687, 688, '', 8, 34, 5, 0, 0, 0),
(96, 14, '2020-09-18 17:12:25', 'Stay', 689, 690, '', 8, 34, 5, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `music_generes`
--

CREATE TABLE `music_generes` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `music_generes`
--

INSERT INTO `music_generes` (`id`, `userID`, `create_at`, `name`, `mediaID`, `status`) VALUES
(1, 1, '2020-04-11 17:22:19', 'hfhgfh', 7, 1),
(2, 1, '2020-04-12 16:43:27', 'workout', 9, 1),
(3, 1, '2020-04-14 16:42:57', 'Hip hop', 18, 1),
(4, 14, '2020-04-23 12:16:33', 'EDM', 290, 0),
(5, 14, '2020-04-23 12:16:55', 'POP', 572, 0),
(6, 14, '2020-04-23 12:17:23', 'R&B', 0, 1),
(7, 14, '2020-04-23 12:17:33', 'HIP HOP', 284, 0),
(8, 14, '2020-04-23 12:17:37', 'RAP', 0, 1),
(9, 14, '2020-04-30 22:30:55', 'R & B', 287, 0),
(10, 14, '2020-04-30 22:31:00', 'RAP', 286, 0),
(11, 14, '2020-05-02 19:18:12', 'DANCE', 325, 0),
(12, 14, '2020-07-07 15:22:11', 'addxd', 372, 1),
(13, 14, '2020-07-07 15:26:39', 'edit ali', 379, 1),
(14, 14, '2020-08-21 18:45:44', 'COUNTRY', 556, 0),
(15, 14, '2020-08-21 18:48:12', 'HOUSE', 558, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `title` text NOT NULL,
  `msg` text NOT NULL,
  `page` varchar(500) NOT NULL,
  `data` text NOT NULL,
  `mediaID` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `userID`, `create_at`, `title`, `msg`, `page`, `data`, `mediaID`, `status`) VALUES
(1, 37, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(2, 36, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(3, 35, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(4, 34, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(5, 33, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(6, 32, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(7, 31, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(8, 30, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(9, 29, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(10, 28, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(11, 27, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(12, 26, '2020-07-29 17:55:57', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(13, 25, '2020-07-29 17:55:58', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(14, 24, '2020-07-29 17:55:58', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(15, 23, '2020-07-29 17:55:58', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(16, 22, '2020-07-29 17:56:00', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(17, 21, '2020-07-29 17:56:01', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(18, 20, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(19, 19, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(20, 18, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(21, 17, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(22, 16, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(23, 15, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(24, 14, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(25, 13, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(26, 12, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(27, 11, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(28, 10, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(29, 9, '2020-07-29 17:56:02', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 1),
(30, 8, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(31, 7, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(32, 6, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(33, 5, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(34, 4, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(35, 3, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(36, 2, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(37, 1, '2020-07-29 17:56:03', 'I am First Qirat', '                        ok                                                                                                            ', 'notification', '[]', 0, 0),
(38, 41, '2020-08-01 13:30:43', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(39, 40, '2020-08-01 13:30:43', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(40, 39, '2020-08-01 13:30:43', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(41, 38, '2020-08-01 13:30:43', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(42, 37, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(43, 36, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(44, 35, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(45, 34, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(46, 33, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(47, 32, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(48, 31, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(49, 30, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(50, 29, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(51, 28, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(52, 27, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(53, 26, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(54, 25, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(55, 24, '2020-08-01 13:30:44', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(56, 23, '2020-08-01 13:30:45', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(57, 22, '2020-08-01 13:30:45', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(58, 21, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(59, 20, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(60, 19, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(61, 18, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(62, 17, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(63, 16, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(64, 15, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(65, 14, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(66, 13, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(67, 12, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(68, 11, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(69, 10, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 1),
(70, 9, '2020-08-01 13:30:46', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(71, 8, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(72, 7, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(73, 6, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(74, 5, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(75, 4, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(76, 3, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(77, 2, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(78, 1, '2020-08-01 13:30:47', 'Test notification', 'this is a test                                                                                                                                    ', 'notification', '[]', 0, 0),
(79, 41, '2020-08-01 13:31:08', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(80, 40, '2020-08-01 13:31:08', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(81, 39, '2020-08-01 13:31:08', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(82, 38, '2020-08-01 13:31:08', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(83, 37, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(84, 36, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(85, 35, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(86, 34, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(87, 33, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(88, 32, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(89, 31, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(90, 30, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(91, 29, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(92, 28, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(93, 27, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(94, 26, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(95, 25, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(96, 24, '2020-08-01 13:31:09', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(97, 23, '2020-08-01 13:31:10', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(98, 22, '2020-08-01 13:31:10', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(99, 21, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(100, 20, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(101, 19, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(102, 18, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(103, 17, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(104, 16, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(105, 15, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(106, 14, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(107, 13, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(108, 12, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(109, 11, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(110, 10, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 1),
(111, 9, '2020-08-01 13:31:11', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(112, 8, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(113, 7, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(114, 6, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(115, 5, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(116, 4, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(117, 3, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(118, 2, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(119, 1, '2020-08-01 13:31:12', 'test 2', 'testttt                                                                                                                                    ', 'notification', '[]', 0, 0),
(120, 41, '2020-08-01 13:31:35', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(121, 40, '2020-08-01 13:31:35', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(122, 39, '2020-08-01 13:31:35', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(123, 38, '2020-08-01 13:31:35', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(124, 37, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(125, 36, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(126, 35, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(127, 34, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(128, 33, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(129, 32, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(130, 31, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(131, 30, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(132, 29, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(133, 28, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(134, 27, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(135, 26, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(136, 25, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(137, 24, '2020-08-01 13:31:36', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(138, 23, '2020-08-01 13:31:37', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(139, 22, '2020-08-01 13:31:37', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(140, 21, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(141, 20, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(142, 19, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(143, 18, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(144, 17, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(145, 16, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(146, 15, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(147, 14, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(148, 13, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(149, 12, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(150, 11, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(151, 10, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 1),
(152, 9, '2020-08-01 13:31:38', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(153, 8, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(154, 7, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(155, 6, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(156, 5, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(157, 4, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(158, 3, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(159, 2, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(160, 1, '2020-08-01 13:31:39', 'testttt', '                                                                                                                                    teststsetset', 'notification', '[]', 0, 0),
(161, 41, '2020-08-01 13:32:42', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(162, 40, '2020-08-01 13:32:42', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(163, 39, '2020-08-01 13:32:43', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(164, 38, '2020-08-01 13:32:43', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(165, 37, '2020-08-01 13:32:43', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(166, 36, '2020-08-01 13:32:43', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(167, 35, '2020-08-01 13:32:43', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(168, 34, '2020-08-01 13:32:43', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(169, 33, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(170, 32, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(171, 31, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(172, 30, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(173, 29, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(174, 28, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(175, 27, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(176, 26, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(177, 25, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(178, 24, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(179, 23, '2020-08-01 13:32:44', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(180, 22, '2020-08-01 13:32:45', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(181, 21, '2020-08-01 13:32:45', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(182, 20, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(183, 19, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(184, 18, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(185, 17, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(186, 16, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(187, 15, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(188, 14, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(189, 13, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(190, 12, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(191, 11, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(192, 10, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 1),
(193, 9, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(194, 8, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(195, 7, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(196, 6, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(197, 5, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(198, 4, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(199, 3, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(200, 2, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(201, 1, '2020-08-01 13:32:46', 'New Live Stream - Dig Cycle!', 'Join now!', 'notification', '[]', 0, 0),
(202, 41, '2020-08-01 13:33:49', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(203, 40, '2020-08-01 13:33:50', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(204, 39, '2020-08-01 13:33:51', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(205, 38, '2020-08-01 13:33:51', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(206, 37, '2020-08-01 13:33:51', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(207, 36, '2020-08-01 13:33:51', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(208, 35, '2020-08-01 13:33:51', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(209, 34, '2020-08-01 13:33:51', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(210, 33, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(211, 32, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(212, 31, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(213, 30, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(214, 29, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(215, 28, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(216, 27, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(217, 26, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(218, 25, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(219, 24, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(220, 23, '2020-08-01 13:33:52', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(221, 22, '2020-08-01 13:33:53', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(222, 21, '2020-08-01 13:33:53', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(223, 20, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(224, 19, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(225, 18, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(226, 17, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(227, 16, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(228, 15, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(229, 14, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(230, 13, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(231, 12, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(232, 11, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(233, 10, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 1),
(234, 9, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(235, 8, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(236, 7, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(237, 6, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(238, 5, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(239, 4, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(240, 3, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(241, 2, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(242, 1, '2020-08-01 13:33:54', 'Test notification', '                                                                                                                                    taerasdfasdf', 'notification', '[]', 0, 0),
(243, 44, '2020-08-31 04:05:44', 'Thanks for Downloading TurboFit App', 'Up your fitness game...and get fit doing it, your one step closer to your goals. Upgrade today to get access to more workout videos and music.                                                                                                                            ', 'notification', '[]', 0, 1),
(244, 65, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(245, 64, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(246, 63, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0);
INSERT INTO `notification` (`id`, `userID`, `create_at`, `title`, `msg`, `page`, `data`, `mediaID`, `status`) VALUES
(247, 62, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(248, 61, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(249, 60, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(250, 59, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(251, 58, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(252, 57, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(253, 56, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(254, 55, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(255, 54, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(256, 53, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(257, 52, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(258, 51, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(259, 50, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(260, 49, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(261, 48, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(262, 47, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(263, 46, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(264, 45, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(265, 44, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(266, 43, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(267, 42, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(268, 41, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(269, 40, '2020-09-11 16:26:17', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(270, 39, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(271, 38, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(272, 37, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(273, 36, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(274, 35, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(275, 34, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(276, 33, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(277, 32, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(278, 31, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(279, 30, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(280, 29, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(281, 28, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(282, 27, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(283, 26, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(284, 25, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(285, 24, '2020-09-11 16:26:18', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(286, 23, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(287, 22, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(288, 21, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(289, 20, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(290, 19, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(291, 18, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(292, 17, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(293, 16, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(294, 15, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(295, 14, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(296, 13, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(297, 12, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(298, 11, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(299, 10, '2020-09-11 16:26:19', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 1),
(300, 9, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(301, 8, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(302, 7, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(303, 6, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(304, 5, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(305, 4, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(306, 3, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(307, 2, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(308, 1, '2020-09-11 16:26:20', 'i am notification ', '                                                                                                                                    i am notification', 'notification', '[]', 0, 0),
(309, 65, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(310, 64, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(311, 63, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(312, 62, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(313, 61, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(314, 60, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(315, 59, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(316, 58, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(317, 57, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(318, 56, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(319, 55, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(320, 54, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(321, 53, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(322, 52, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(323, 51, '2020-09-11 16:28:06', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(324, 50, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(325, 49, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(326, 48, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(327, 47, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(328, 46, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(329, 45, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(330, 44, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(331, 43, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(332, 42, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(333, 41, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(334, 40, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(335, 39, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(336, 38, '2020-09-11 16:28:07', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(337, 37, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(338, 36, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(339, 35, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(340, 34, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(341, 33, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(342, 32, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(343, 31, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(344, 30, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(345, 29, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(346, 28, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(347, 27, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(348, 26, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(349, 25, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(350, 24, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(351, 23, '2020-09-11 16:28:08', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(352, 22, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(353, 21, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(354, 20, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(355, 19, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(356, 18, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(357, 17, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(358, 16, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(359, 15, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(360, 14, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(361, 13, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(362, 12, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(363, 11, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(364, 10, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 1),
(365, 9, '2020-09-11 16:28:09', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(366, 8, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(367, 7, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(368, 6, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(369, 5, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(370, 4, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(371, 3, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(372, 2, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0),
(373, 1, '2020-09-11 16:28:10', 'testing', '                                                                                                                                    i am notifica', 'notification', '[]', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `track` int(11) NOT NULL,
  `cid` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 0,
  `date_time` datetime NOT NULL DEFAULT current_timestamp(),
  `pstatus` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `myfatoorah_response` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `track`, `cid`, `total`, `date_time`, `pstatus`, `status`, `myfatoorah_response`) VALUES
(1, 1636220150, 1, 400, '2021-11-06 22:35:50', 1, 0, '[]');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `data` text NOT NULL,
  `cid` int(11) NOT NULL DEFAULT 0,
  `create_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `uid`, `sid`, `data`, `cid`, `create_at`) VALUES
(1, 10, 1, '0', 0, '2020-06-24 15:52:47'),
(2, 10, 1, 'data', 0, '2020-06-24 15:54:02'),
(3, 10, 1, 'data', 1, '2020-06-24 15:54:11'),
(4, 10, 1, 'data', 1, '2020-06-24 16:33:33'),
(5, 10, 1, 'data', 1, '2020-06-24 16:36:34'),
(6, 11, 1, 'data', 1, '2020-06-24 16:36:48'),
(7, 11, 1, 'data', 1, '2020-06-24 16:37:00'),
(8, 11, 1, 'data', 1, '2020-06-24 16:37:29'),
(9, 11, 1, 'data', 1, '2020-06-24 16:37:58'),
(10, 11, 1, 'data', 1, '2020-06-24 16:40:49'),
(11, 11, 1, 'data', 1, '2020-06-24 17:11:27'),
(12, 11, 1, 'data', 1, '2020-06-24 17:12:10'),
(13, 11, 1, 'data', 1, '2020-06-24 17:12:13'),
(14, 10, 2, 'PAYID-L3Z6HJY00T52913TF030481D', 1, '2020-06-24 17:37:17'),
(15, 10, 2, 'PAYID-L3Z6JCQ79L55944F0392160U', 1, '2020-06-24 17:41:07'),
(16, 16, 1, 'PAYID-L3642IY3V355891VH061114P', 0, '2020-07-02 06:03:55'),
(17, 10, 2, 'undefined', 0, '2020-07-11 18:01:14'),
(18, 22, 2, 'undefined', 0, '2020-07-11 22:05:49'),
(19, 10, 2, 'undefined', 0, '2020-07-12 07:40:30'),
(20, 10, 2, 'undefined', 0, '2020-07-12 14:56:54'),
(21, 10, 2, 'undefined', 0, '2020-07-12 14:57:16'),
(22, 23, 1, 'undefined', 0, '2020-07-12 17:13:07'),
(23, 22, 1, 'undefined', 0, '2020-07-17 21:18:59'),
(24, 25, 2, 'undefined', 0, '2020-07-17 21:21:57'),
(25, 22, 2, 'undefined', 0, '2020-07-17 21:39:00'),
(26, 10, 2, '89.99', 0, '2020-07-18 12:43:03'),
(27, 32, 1, '12.99', 0, '2020-07-18 18:58:05'),
(28, 31, 2, '89.99', 0, '2020-07-18 21:39:27'),
(29, 22, 1, '12.99', 0, '2020-07-19 03:46:29'),
(30, 22, 2, '89.99', 0, '2020-07-20 04:02:27'),
(31, 24, 2, '89.99', 0, '2020-07-20 16:26:20'),
(32, 22, 2, '89.99', 0, '2020-07-20 16:32:18'),
(33, 34, 1, '12.99', 0, '2020-07-20 17:35:14'),
(34, 22, 2, '89.99', 0, '2020-07-20 20:52:56'),
(35, 28, 1, '12.99', 0, '2020-07-21 11:36:53'),
(36, 28, 2, '89.99', 0, '2020-07-21 12:46:28'),
(37, 28, 2, '89.99', 0, '2020-07-21 12:49:46'),
(38, 37, 2, '89.99', 0, '2020-07-24 19:49:44'),
(39, 41, 2, '89.99', 0, '2020-08-01 11:18:29'),
(40, 41, 2, '89.99', 0, '2020-08-01 11:18:52'),
(41, 41, 2, '89.99', 0, '2020-08-01 11:21:41'),
(42, 41, 2, '89.99', 0, '2020-08-01 11:22:15'),
(43, 41, 2, '89.99', 0, '2020-08-01 11:22:27'),
(44, 40, 2, '89.99', 0, '2020-08-01 11:44:38'),
(45, 40, 2, '89.99', 0, '2020-08-01 13:13:22'),
(46, 40, 2, '89.99', 0, '2020-08-01 13:19:12'),
(47, 40, 2, '89.99', 0, '2020-08-01 13:23:36'),
(48, 42, 2, '89.99', 0, '2020-08-05 23:13:36'),
(49, 10, 1, 'Rs 2,000', 0, '2020-08-26 15:34:13'),
(50, 10, 1, 'Rs 2,000', 0, '2020-08-26 15:40:43'),
(51, 10, 1, 'Rs 2,000', 0, '2020-08-26 15:46:04'),
(52, 10, 1, 'Rs 2,000', 0, '2020-08-26 15:58:37'),
(53, 10, 2, 'Rs 13,900', 0, '2020-08-26 15:59:14'),
(54, 54, 2, '$89.99', 0, '2020-09-02 19:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `ptype` varchar(200) NOT NULL,
  `order_id` int(11) NOT NULL,
  `process_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`id`, `pid`, `qty`, `ptype`, `order_id`, `process_status`) VALUES
(1, 3, 1, 'course', 1, 1),
(2, 3, 1, 'course', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `userID` int(11) NOT NULL,
  `quiz` int(11) NOT NULL,
  `option` text NOT NULL,
  `ans` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`id`, `name`, `userID`, `quiz`, `option`, `ans`, `status`) VALUES
(1, 'gfxgfs', 1, 1, 'ereae', 'aasda', 0);

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `btype` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `name`, `btype`, `status`, `userID`) VALUES
(1, 'test', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) DEFAULT NULL,
  `vid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `stemp` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `vid`, `uid`, `stemp`) VALUES
(NULL, 40, 22, '0000-00-00 00:00:00'),
(NULL, 40, 22, '0000-00-00 00:00:00'),
(NULL, 40, 22, '0000-00-00 00:00:00'),
(NULL, 40, 22, '0000-00-00 00:00:00'),
(NULL, 40, 22, '0000-00-00 00:00:00'),
(NULL, 40, 22, '0000-00-00 00:00:00'),
(NULL, 25, 22, '0000-00-00 00:00:00'),
(NULL, 15, 22, '0000-00-00 00:00:00'),
(NULL, 31, 22, '0000-00-00 00:00:00'),
(NULL, 24, 22, '0000-00-00 00:00:00'),
(NULL, 20, 24, '0000-00-00 00:00:00'),
(NULL, 34, 22, '0000-00-00 00:00:00'),
(NULL, 40, 40, '2020-01-08 20:41:00'),
(NULL, 46, 44, '0000-00-00 00:00:00');

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
(2, 'users');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tagID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `name` varchar(200) NOT NULL,
  `imgID` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tagID`, `userID`, `create_at`, `name`, `imgID`, `status`) VALUES
(1, 1, '2020-03-07 22:11:26', 'knet', 26, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `first_name` varchar(500) NOT NULL,
  `last_name` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `uname` varchar(500) DEFAULT NULL,
  `upass` varchar(500) NOT NULL,
  `roleID` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(50) NOT NULL,
  `token` varchar(1000) NOT NULL,
  `api_status` int(11) NOT NULL DEFAULT 1 COMMENT '1 i= sandbox2:live ',
  `call_back` varchar(500) NOT NULL,
  `permmision` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `first_name`, `last_name`, `email`, `uname`, `upass`, `roleID`, `status`, `create_at`, `ip`, `token`, `api_status`, `call_back`, `permmision`) VALUES
(1, 'Raheel', 'Shehzad', 'info@channelsmedia.com', 'raheel.shehzad5', '21232f297a57a5a743894a0e4a801fc3', 1, 0, '2019-10-01 23:07:31', '119.160.58.198', '', 1, '', ''),
(14, 'Admin', '', 'admin@admin.com', NULL, '21232f297a57a5a743894a0e4a801fc3', 1, 1, '2020-03-06 22:05:13', '::1', '', 1, '', '[\"product\",\"cateory\"]'),
(15, 'Raheel', 'Shehzad', 'raheelshehzad188@gmail.com', 'raheelshehzad188@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 2, 0, '2020-12-21 06:14:57', '39.34.247.25', '154e9fe811a8922dc7dba4ac14553ac9', 1, '', ''),
(16, '', '', 'demo@android.com', 'demo@android.com', '21232f297a57a5a743894a0e4a801fc3', 2, 1, '2021-03-15 04:14:04', '39.34.216.8', 'c288e4c9c451672dc71dd2a891a1de21', 1, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tagID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

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
