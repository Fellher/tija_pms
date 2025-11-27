-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 17, 2025 at 03:16 AM
-- Server version: 11.4.8-MariaDB-cll-lve-log
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `skmcibhb_pms_live`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_calculate_expense_totals`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_calculate_expense_totals` (IN `p_employee_id` INT, IN `p_date_from` DATE, IN `p_date_to` DATE, OUT `p_total_amount` DECIMAL(12,2), OUT `p_total_reimbursement` DECIMAL(12,2), OUT `p_total_tax` DECIMAL(10,2), OUT `p_expense_count` INT)   BEGIN
    SELECT
        COALESCE(SUM(amount), 0),
        COALESCE(SUM(reimbursementAmount), 0),
        COALESCE(SUM(taxAmount), 0),
        COUNT(*)
    INTO
        p_total_amount,
        p_total_reimbursement,
        p_total_tax,
        p_expense_count
    FROM tija_expense
    WHERE employeeID = p_employee_id
    AND expenseDate BETWEEN p_date_from AND p_date_to
    AND isDeleted = 'N';
END$$

DROP PROCEDURE IF EXISTS `sp_generate_expense_number`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generate_expense_number` (IN `p_expense_date` DATE, OUT `p_expense_number` VARCHAR(50))   BEGIN
    DECLARE v_year VARCHAR(4);
    DECLARE v_month VARCHAR(2);
    DECLARE v_count INT DEFAULT 0;

    SET v_year = YEAR(p_expense_date);
    SET v_month = LPAD(MONTH(p_expense_date), 2, '0');

    SELECT COUNT(*) + 1 INTO v_count
    FROM tija_expense
    WHERE YEAR(expenseDate) = YEAR(p_expense_date)
    AND MONTH(expenseDate) = MONTH(p_expense_date);

    SET p_expense_number = CONCAT('EXP-', v_year, v_month, '-', LPAD(v_count, 4, '0'));
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `login_sessions`
--

DROP TABLE IF EXISTS `login_sessions`;
CREATE TABLE `login_sessions` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SessIDStr` varchar(255) NOT NULL,
  `CheckStr` varchar(255) NOT NULL,
  `PersonID` int(10) UNSIGNED DEFAULT NULL,
  `LoginTime` datetime DEFAULT NULL,
  `LastActionTime` datetime DEFAULT NULL,
  `LogoutTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `login_sessions`
--

INSERT INTO `login_sessions` (`ID`, `SessIDStr`, `CheckStr`, `PersonID`, `LoginTime`, `LastActionTime`, `LogoutTime`) VALUES
(1, 'e90a6be4ae3c78ccd949bc7503b51182cd80efd738afc27e2331276b87e95007', '1c38011b4ab7861702f0eb32c3c13d55', 1, '2025-11-01 14:32:54', '2025-11-02 14:07:48', '2025-11-02 14:26:19'),
(2, 'b75f9b266092cf3bedb39a0cb7a05f1105b9c231c04c869455b15c557c1b003b', '33e7171b2a83ed653d95f477ea5e4172', 3, '2025-11-02 10:14:35', '2025-11-02 16:55:48', '2025-11-02 17:00:08'),
(3, '4e01df4a20ccf9b4f724918002e8fdd665230fbb2948773fc271e69f7113130f', '2e3d5fb03c9e0d147e570edd5f95f305', 1, '2025-11-02 14:26:19', '2025-11-02 14:26:23', '2025-11-02 14:32:56'),
(4, '78d6d158c10a2d198d17bc0cb6011b615f6c5aee2d0a096672860df337da2918', 'c68f028c24fc668a1e717fa5e3fbfd48', 1, '2025-11-02 14:32:56', '2025-11-02 16:55:48', '2025-11-03 08:37:00'),
(5, 'd3d98c795b590731cc148271f01dfaa8cd5b93414599019de15cf89917f79083', 'b374aaea56741c3bd84a3dc286f14071', 3, '2025-11-02 17:00:08', '2025-11-03 08:36:41', '2025-11-03 08:36:41'),
(6, 'da6581063f931bafb4e7bca56a0d385403559ad0a9369c7fbad084be87e57b1e', '3e140d23b6442f82bfd50bfbe05a5e6b', 1, '2025-11-03 08:37:00', '2025-11-03 08:38:02', '2025-11-03 08:38:02'),
(7, '1cd2dff86d5568d1ae31b93aaf4a4485cd2ef574f4d1712d776b2a0eb89793df', '75867597b7850beda8e42971bb976df3', 4, '2025-11-03 08:38:10', '2025-11-03 08:39:37', '2025-11-03 08:48:12'),
(8, '34f7ba32c4420bdaa3a7d2bc809eb218f8709409e1cdecf6df87e0f9767bf382', '0ded3cb35cb6fa16c47dc74c68138c97', 4, '2025-11-03 08:48:12', '2025-11-03 10:38:38', '2025-11-03 10:38:38'),
(9, 'e66d6e46cb238e3974e7ec9fe4f89ea4ec70c92c436b4669e08bdd1a8f960e42', '934581cfc4b5f82e662e2c49f5ce4251', 1, '2025-11-03 10:38:50', '2025-11-03 10:47:37', '2025-11-03 10:49:49'),
(10, '74b2c02fbf20f82fa0709dc8f84e69d9d7c9092f4dff3da145bfbd2c82e3223d', 'd7bb41e4475f14a9407950276dfc30e4', 1, '2025-11-03 10:49:49', '2025-11-03 13:06:45', '2025-11-03 13:07:29'),
(11, '788952beb3d615a9bb93a2a4638034f8d621cb67a95f23b85693ac17d433ecf5', '55ba5ef611ba64b62fa3dac90541e13d', 4, '2025-11-03 10:50:30', '2025-11-03 10:55:09', '2025-11-03 16:14:37'),
(12, '46592b66df207ad520429fbf7ed3676e08e718108c422fb9eb8709e7465edf0b', '7b4928c782aab496de9fcd25e7240655', 1, '2025-11-03 13:07:29', '2025-11-03 13:10:35', '2025-11-03 13:10:35'),
(13, 'edb28ae76728109f8288915114f49931889d30257df983a72d3072dd889b6a11', '5a6f33061d25c73e96bd643c884a87f0', 1, '2025-11-03 13:10:55', '2025-11-03 13:35:34', '2025-11-03 13:35:34'),
(14, 'fc80949f1afa768d0d4c79af9b3fb9aec39f9d78cd87904a0d88b31d4bc17e75', '470538a3e9f0729d460beff966754892', 49, '2025-11-03 13:26:07', '2025-11-03 13:36:17', '2025-11-03 13:36:17'),
(15, '28e1d70b7d6a5f4501679c14d94f5c0c9b526d0445b0761a6a32c63080a932b1', '9446a0536bd96a7099102e0f60a4388a', 1, '2025-11-03 13:35:39', '2025-11-04 13:23:54', '2025-11-05 10:58:39'),
(16, '83e5729bb6c32587ed8711b9d63f8bb1ce5a0a83f4f6b2a46a7c08f6bc8e1237', '4b5552d1ab02e2bf5e22152fbf728d7e', 49, '2025-11-03 13:41:48', '2025-11-03 13:52:31', '2025-11-03 13:52:31'),
(17, '780623fe8e888782076e7742fa9e45e559983ed3403be6d17b9e33ee3e6bed8f', 'c08294d43b4e1d98082f8743b10b5487', 49, '2025-11-03 13:52:42', '2025-11-03 13:54:43', '2025-11-03 13:54:43'),
(18, '5ce7ced8fbb5e7d0abea3ea6b39e9db91e512899bc07f768272f52038edd121d', '37142ac6e74823d52b50d8700cc5af54', 49, '2025-11-03 13:54:56', '2025-11-03 13:55:03', '2025-11-03 13:55:03'),
(19, '7e364855f97ad105e3cb3e3ee1e07778ccb6717abbf56548d7a35957906cced8', '952fd1585d06a911f66a9317ffdf6572', 49, '2025-11-03 13:55:38', '2025-11-03 13:59:07', '2025-11-03 13:59:07'),
(20, '639e9394444c85638b03a3251b45e435a9df9e0063a76fa82c74bec606c8a865', '54cfdbe1aa62151d595c866f7c8b6a14', 49, '2025-11-03 13:59:22', '2025-11-04 13:24:54', NULL),
(21, 'e5dae40a738a4cc62310334fd032f8ba19b72759edd33a442547b09f05036353', 'e486ee7b352a1a26cb29337001d2231a', 4, '2025-11-03 16:14:37', '2025-11-04 13:23:54', '2025-11-05 13:35:12'),
(22, '618d63c95a26e9ae81056f3621cf1e81bc011cdd8149e26be91dc1fb6943cca6', 'a9e231d4638df91f9b66619bbab744b8', 1, '2025-11-05 10:58:39', '2025-11-05 15:09:23', '2025-11-05 13:34:35'),
(23, 'f059c459ce86ebc8b0af51a7b5ce5e422f156b924a97cae5ae3db26d00370714', '576aa9dd4408aebb45ec452375c0d576', 1, '2025-11-05 13:34:35', '2025-11-05 13:34:58', '2025-11-05 13:34:58'),
(24, '433fdda53ffcefc9026eba45c0ccbf67e36d23ff0be6613afd158e8ad617a9a2', '9b02f0f080501504f8fc427c052edef2', 4, '2025-11-05 13:35:12', '2025-11-05 13:36:02', '2025-11-05 13:36:02'),
(25, '7f27c3173674b93efa0e43f7ff5bdba9a3b0f6d9d677fd7bbc4c4c1fe7a3c605', '8146cbae71ab059d302c94505c1d5f28', 4, '2025-11-05 13:38:06', '2025-11-05 15:08:23', '2025-11-05 15:17:50'),
(26, '0dd35ccc4ec89f4ac6824f2f524e8e56582e51cc0b3d68a6cfae04f3e14c9ac3', 'f3b498629e61c66f1c0bf22316788542', 4, '2025-11-05 15:17:50', '2025-11-05 15:20:47', '2025-11-05 15:20:47'),
(27, '57a960278db2f69efbc477fb3d4572eacc052cc497e2d998cf2404f97684f2c4', 'ff3fa3098c1f86ce0e6ba381d0b7fd46', 1, '2025-11-05 15:20:56', '2025-11-05 15:31:36', '2025-11-05 15:31:36'),
(28, '158db40bca13d72cb9fd4b23ab7000ebf8d5f3833aeccd6d15c742fecdc74045', '5178a819120ab9137441babf29e10a2f', 4, '2025-11-05 15:31:54', '2025-11-06 06:56:52', '2025-11-05 17:23:42'),
(29, 'c0dbe38cec381873f5d5bbb55cd0aa79944bdcf2505492c0cf7dabc61ecd0ea5', '06a050de7ae7e3cd0c15631ef1767e76', 4, '2025-11-05 17:23:42', '2025-11-06 06:55:52', '2025-11-06 08:54:37'),
(30, '098abc4949531d05d359efcaf082bc0a07305f730623955a7335aebe487a72f2', '946247395b546b1e0f11143de0ef1ea8', 4, '2025-11-06 08:54:37', '2025-11-06 14:11:25', '2025-11-06 14:11:25'),
(31, 'e5a2a3a19693a355a147e2b202073aa7cf67be002818d36abac4da6c05b647c8', 'a22df22ae32570cc51271353ab245cb9', 3, '2025-11-06 13:55:31', '2025-11-07 11:56:52', '2025-11-07 12:08:11'),
(32, '8574ebc3455d8ea6b63ed2eb6e83dc0142ffe7cec201a08fe0bda57e9e6f1fae', 'a96c8f2efc0148dec6fb02ab95456330', 1, '2025-11-06 14:11:38', '2025-11-06 17:08:43', '2025-11-06 17:08:43'),
(33, '5afa98a7c791696054216ce8b2c1ce89048bf72539b8f812f823a4536cbe7dc7', '23172880c75cd2569390e9dbfa345548', 1, '2025-11-07 12:07:42', '2025-11-09 18:11:23', '2025-11-10 05:46:47'),
(34, '1be45832189f6097a42bb0cc8b34720ea780953fac66d3b2672abccffdab2ced', '2ecdce4e0a8f6365e11159be0496afff', 3, '2025-11-07 12:08:11', '2025-11-09 17:26:35', '2025-11-09 18:28:49'),
(35, '012004ee3d4ad45536262955823e3b906288b08b63fc7f648a63e5b6357ae0f1', '9b9af8fc2e12690979bb4b0667572757', 4, '2025-11-07 12:09:10', '2025-11-10 05:06:33', '2025-11-10 06:46:39'),
(36, '8c4fa324155bb42733973ae082f3a52e62feb889d75d96d12f3e480b63fa2f2f', '373d7e8ee126564b4b28f65078d9e594', 3, '2025-11-09 18:28:49', '2025-11-09 19:50:30', NULL),
(37, '1249faa35b7675790cbe57ee94c3764abc0bf34eb95ed646d68cb19e34bed4f5', '13f83961be92ac5775ccbec9319cfb66', 1, '2025-11-10 05:46:47', '2025-11-10 06:46:23', '2025-11-10 06:46:23'),
(38, '8020124c0d2d15b891637fe5aaefe84bbd4e2b293aa6e722781d887edb6b9eeb', 'f71c9cd3fdc9b54f32cc9b1c0f741e65', 4, '2025-11-10 06:46:39', '2025-11-10 06:47:26', '2025-11-11 04:51:12'),
(39, '6a9cef3de41a7ad174178904ce43cbded58b29bb1bc8bebd31cfb0746f34eef0', '58a5d3ba873029a126f90840f221988d', 31, '2025-11-10 07:26:24', '2025-11-10 08:10:31', '2025-11-11 06:09:03'),
(40, '77e01f550452ca55865c7702c2dc717aa001843e585b28db425ad21a729c056c', '2f50c262c9e9c6745557f5c8490bef66', 4, '2025-11-11 04:51:12', '2025-11-11 05:49:54', NULL),
(41, 'a1bb7a17736657f3c9a80efffc62d462600f93d1b2c82c11ff016449c23cdf4b', '073a87eee8ec52dfa67fe6c5403503f1', 32, '2025-11-11 05:19:55', '2025-11-11 06:02:00', NULL),
(42, 'faccb20d69807b3c93a7dc9a464251d5bbb8f9316ed2106a73fb1586025ccd69', '9411080c303ffd11beb82a1efd1295b5', 25, '2025-11-11 05:39:56', '2025-11-11 07:38:33', NULL),
(43, '558d89a02da7a01abe1f84ff5f9d89613bdcfbbb7e3ee1780bfcfadfb70a05ce', '7c7def9c465801bfacd72032eaf092d6', 48, '2025-11-11 05:44:47', '2025-11-11 07:15:25', '2025-11-13 11:37:09'),
(44, '548bc47c0f4afb3bb253a13e825dca44983cf66cb6c7fc6f0693994e2d5d2d1e', '3e37229b1c8b574128f3c7297fbff06d', 39, '2025-11-11 05:45:39', '2025-11-11 05:45:40', NULL),
(45, '0c02f1b1a376b85371f9c77f5eeef74c4747d7bf82dfe7d8729dd3eeb3709e73', '85923b517ed6f21df13913f0aa961842', 18, '2025-11-11 05:47:14', '2025-11-11 06:55:46', NULL),
(46, '8daeaf2135ae6597755456ae3f6d8c87efd5642bddd843a4006fa809e09e5b98', '3578f51d63420b5f15489643f6f55b94', 1, '2025-11-11 05:58:10', '2025-11-11 09:40:08', NULL),
(47, '1255df1f74ec079b433c7133d2fd2d3f419218bc7757b92b22d72d06f038d1ba', '49d4fba0abbc42cb6077e57588b4e3ec', 24, '2025-11-11 06:01:37', '2025-11-11 06:01:38', NULL),
(48, '43c65b3825e9fe84789933f7a9e4d81c4ec909149f2d2c93d2eb34e89a799bf1', 'f386bf42050df25742739f56ee67f02f', 31, '2025-11-11 06:09:03', '2025-11-11 07:01:29', '2025-11-13 14:33:33'),
(49, '448b7f59bfd82ad37a51bb43e4741e6ba54397a4e1f0c4fb4a183c07edb8f13e', '1ce1591ff676f00611180c54f21e668b', 47, '2025-11-11 06:47:04', '2025-11-11 08:38:27', '2025-11-17 07:49:04'),
(50, '63a0b12c27d128bfba19122f713068d99ce5081caaa5c6160bf654c2d9fe02e6', '29dc036f532985af09a2b52b173be0d1', 6, '2025-11-11 07:08:25', '2025-11-11 07:16:47', '2025-11-11 07:16:47'),
(51, '5b146c0c0fd24818c7c50b9ea2d2a61d6d57b644f3336e9f65815b7ffc6cc556', 'dfd48acfe09cb0ff1d78bbbbc3707ace', 28, '2025-11-11 14:11:47', '2025-11-11 14:26:53', NULL),
(52, '782ecee81876241beb954148ff748417dd55ebd1c1581b8044f43b0be38ef185', '0d3a99bb9e02a6102eca6e8ff007b4b6', 48, '2025-11-13 11:37:09', '2025-11-13 12:49:50', '2025-11-17 06:26:49'),
(53, '3fd63dbccede2844a29e9d3114eaa5f93e215d3f1d9b1da541430fc8203bc4f0', '0a2c4e57bf838484985ec347f3f091ad', 5, '2025-11-13 14:03:31', '2025-11-13 14:05:01', NULL),
(54, 'e1270c16936bdbf1d439b8638e788cbc5d6e3131888e3da686bb5523bbe9f748', 'ee5343370aac246fdfca4e87c5b8e997', 31, '2025-11-13 14:33:33', '2025-11-13 15:07:35', NULL),
(55, '4dfee9569bce8a296a409b87e8d2df8f95b1faec0f0e84c1e6008b65f3e77bc4', '9e8e95bf14d54aede4f3553eadf70cdf', 6, '2025-11-13 14:34:00', '2025-11-13 14:39:20', '2025-11-13 14:39:20'),
(56, '0246a74c1efbc5b8bae54e0beda2f5e184f2f08aec5b16ea1b3fc9e9b21cf844', '8a380c9c5ffbeee8113c45efcbd11721', 30, '2025-11-13 15:08:32', '2025-11-13 15:17:43', '2025-11-14 14:35:33'),
(57, '3eb0e474530ef90e0b405dd29986a1338c9bde078a9f4f6202be1fce09fcaf1d', '92a579d474a9844da67e1bba04c101b6', 30, '2025-11-14 14:35:33', '2025-11-14 14:37:04', NULL),
(58, '487b95d0c6d577ca1beba17aeb76a22eecdad3cdc413b458623a6cc1c69089d8', '5d23174a05d7f02fea674dcdd208fdd1', 48, '2025-11-17 06:26:49', '2025-11-17 06:36:37', NULL),
(59, '30e848cf92f2244bb3bc4a063aece78575e20a3dc97aa0a16549c86bb4535d62', '5283885e063348b0360ee2fe7a230034', 12, '2025-11-17 06:36:33', '2025-11-17 06:48:37', NULL),
(60, '990351b7de543a47549f9cc053fd6906808eccd316b3a801cfe6a10ad95a3d8f', 'b8802a673ab9037a5ceb4f1b260b346e', 50, '2025-11-17 07:46:28', '2025-11-17 08:16:26', NULL),
(61, 'b5f3605bb39452ba064d38cbef7b739eb9cb3c71e6a5d2f60f98c5ecd6a6e9c2', 'd12586c1a55fbccd510a3ec771d36efa', 47, '2025-11-17 07:49:04', '2025-11-17 08:15:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE `people` (
  `ID` int(10) UNSIGNED NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `FirstName` varchar(128) NOT NULL,
  `Surname` varchar(128) DEFAULT NULL,
  `OtherNames` varchar(128) DEFAULT NULL,
  `userInitials` varchar(3) NOT NULL,
  `Email` varchar(128) NOT NULL,
  `profile_image` varchar(256) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `NeedsToChangePassword` enum('y','n') DEFAULT 'n',
  `Valid` enum('y','n') DEFAULT 'n',
  `active` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdateByID` int(11) DEFAULT NULL,
  `isEmployee` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`ID`, `DateAdded`, `FirstName`, `Surname`, `OtherNames`, `userInitials`, `Email`, `profile_image`, `Password`, `NeedsToChangePassword`, `Valid`, `active`, `LastUpdateByID`, `isEmployee`) VALUES
(1, '2023-03-12 16:56:54', 'System', 'Administrator', NULL, '', 'support@sbsl.co.ke', 'employee_profile/1756735549_4.jpg', '$6$rounds=1024$1063359921$mAbT9hkQ9Eazp16ULeuWdqSIxiyY5cR6zzo0.EwofatNwZybPCuODvERRpTuDowDH9DOOLDTb7/CZjkYCNAla.', 'n', 'y', 'N', NULL, 'N'),
(2, '2025-07-15 17:14:29', 'Felix', 'Mauncho', '(admin SKM Example)', '', 'felix.mauncho@example.com', NULL, '$6$rounds=1024$238218335$8mVukyfrKbFXHoISvlNxB6DHbW31amjW1pzm2iMUOD0Y8oRV1pIbkOgZe8xRt1CMcMYPeGHzOB.fRbLM4ylrI1', 'n', 'y', 'N', NULL, 'Y'),
(3, '2025-07-15 18:44:19', 'Hillary', 'Oonge', 'Joseph Orwoba', 'HO', 'hoonge@skm.co.ke', NULL, '$6$rounds=1024$1591503764$eUj6v.CFTcDahAq2hs01fPzKPtTpTNKPUw.VY.Z2EmjhcQxqf457lbPBt7yE19WCRS0hurw3F7uIxEnoGv50E1', 'y', 'n', 'N', NULL, 'Y'),
(4, '2025-07-15 18:46:16', 'Felix', 'Mauncho', 'Nyandega', 'FM', 'felix.mauncho@skm.co.ke', 'employee_profile/1756735549_4.jpg', '$6$rounds=1024$608157907$Ruja/D/eZglH5Dp1VRl1WtGWzvB4e6T/FXUGufcZaQHuPdnrRezh/NHDrXC0o8LsUp1y3EPmDac.UZDNCIb3f/', 'y', 'n', 'N', NULL, 'Y'),
(5, '2025-07-15 18:52:11', 'Mkandawiro', 'Erisiana', NULL, 'EM', 'emkandawiro@skm.co.ke', NULL, '$6$rounds=1024$916847481$X5RWuBu5u.PTf9/FrukoHvp7BsYVKabSOLy0DkR6eSBYkq1HtdY/Tb9fzQdWBJBQJfA3aJRlexSmMwVvMucNZ0', 'n', 'y', 'N', NULL, 'Y'),
(6, '2025-07-15 18:55:18', 'George', 'Onditi', 'Agembe', 'GO', 'gonditi@skm.co.ke', 'employee_profile/1756192514_Logos12.png', '$6$rounds=1024$2122751286$TU7fyYtUfELQug0BhXNzp5oyXjlJx/1HlbCdcyo7UrKabQPW7XG3DPkW4xXsWsrkTB9.JMqKGFbeGNVwjpPPi/', 'n', 'y', 'N', NULL, 'Y'),
(7, '2025-07-21 06:40:10', 'Phoebian', 'Moindi', 'Moraa', 'PM', 'pmoindi@skm.co.ke', NULL, '$6$rounds=1024$1262249528$H5KaFqgPmWgtRPB0KcZVcrkCrFmUOjW3F27InVNRYoyzKFrSTp/koofHTnTkA4R8/pEgn7530GoFYHJR6h1S0/', 'y', 'n', 'N', NULL, 'Y'),
(8, '2025-07-21 06:49:06', 'Caleb', 'Mokaya', 'Karaya', 'KM', 'kmokaya@skm.co.ke', NULL, '$6$rounds=1024$749741764$YqaoGGOMo74.AiEsNW8XM7mbZZwCN3vHoR2TuP1h2zzu4EQUUoWPFAgmxrDmPC7Uc6Pi9w7z5c7o2pmoiaktX/', 'y', 'n', 'N', NULL, 'Y'),
(9, '2025-07-21 06:50:47', 'Gerhard', 'Uduny', 'James', 'GU', 'uduny@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(10, '2025-07-21 06:56:06', 'George', 'Njeri', 'Gitau', 'GG', 'ggitau@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(11, '2025-07-21 06:57:25', 'Newton', 'Kangethe', NULL, 'NK', 'nkangethe@skm.co.ke', NULL, '$6$rounds=1024$1064726531$NzXaRhg2lXPzaoie1inyn8eBT95J.2tvjQKXOgIJIj5McAMgLUMjJuKUNWV6AMwuNh7XIHlOYAfCS1b8C0SXc/', 'y', 'n', 'N', NULL, 'Y'),
(12, '2025-07-21 07:50:28', 'Tonui', 'Faith', 'Chepkirui', 'FT', 'ftonui@skm.co.ke', NULL, '$6$rounds=1024$943863675$ekGbB.6odBg80x59rJ1lWVGjRA1pYQYYUIIaDGUz2k00JxoA5R97t1TEfek04trYFt0mXq/.3rIfaNnu6rU/l0', 'n', 'y', 'N', NULL, 'Y'),
(13, '2025-07-21 07:55:53', 'Francis,', 'Musyoka', 'Jimmy', 'JM', 'jmusyoka@skm.co.ke', NULL, '$6$rounds=1024$1319157296$QlitZEWCeW1bBTcFOLOlx/fE7Y8D9U5BzXGlEZTflF7.7NzNM4sUwOnsPL8mrfjbG7e6LMbscgNqYMqR4zV0x1', 'y', 'n', 'N', NULL, 'Y'),
(14, '2025-07-21 08:01:11', 'Fred', 'Gitonga', 'Maithethia', 'FG', 'fgitonga@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(15, '2025-07-21 08:02:44', 'Kelvin', 'Mule', 'Musyoka', 'KMM', 'kmule@skm.co.ke', NULL, '$6$rounds=1024$1891127954$oXkwt6zNljdHo/DUZR23JCwDLNCllqpeAacNRk7ZeQgly3tMbwEEGAfkUBAsIxIcsmKoeArSyNTfxUDHpFvQG.', 'y', 'n', 'N', NULL, 'Y'),
(16, '2025-07-21 08:04:53', 'Kevin', 'Okenye', 'Nyabuto', 'KO', 'kokenye@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(17, '2025-07-21 08:05:58', 'Beatrice', 'Orawo', NULL, 'BO', 'borawo@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(18, '2025-07-21 08:08:26', 'Mercy', 'Kahenya,', 'Wanjiku', 'MK', 'mkahenya@skm.co.ke', NULL, '$6$rounds=1024$495934766$.oLGeIBa.1RDHoffJEQ6eMkeXarY1Dd6rewtGoBLcva3HcXzoHCXi.JqThuW4QT0QXeByZ/tXr/ewxHZEAyYw1', 'n', 'y', 'N', NULL, 'Y'),
(19, '2025-07-21 08:11:52', 'Julia', 'Sikulu', 'Khaindi', 'JS', 'jsikulu@skm.co.ke', NULL, '$6$rounds=1024$576734188$mwB1uUayhuUk3kchSMSxyMr8IrTONCPKblS.j0EEfRKgD.16oUlz9hWXBGPXe64qQG/i9vtBWP8Zb4.Rap89L.', 'y', 'n', 'N', NULL, 'Y'),
(20, '2025-07-21 08:17:05', 'Felicia', 'Kwamboka,', 'Alexina', 'FK', 'kfelicia@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(21, '2025-07-21 08:23:45', 'Victor', 'Mayogi', 'Nyambane', 'VM', 'vmayogi@skm.co.ke', NULL, '$6$rounds=1024$432774450$aNxhhrt24n/tW.6498uzZuLBywvYgbo3Hl3kfGKW8rQH8UhDQwUbkgEkj7QBQVw.uHMIvWURTcuTE6/R6f1ma/', 'y', 'n', 'N', NULL, 'Y'),
(22, '2025-07-21 08:33:54', 'Wasike', 'Quincy', NULL, 'WQ', 'qwasike@skm.co.ke', NULL, '$6$rounds=1024$515878554$RfKtrFWcB2Dqn.RBMpEZcLg61m0D5ffVayfznsJxdmu13FU7HXfmD6SHXy6zLl0OdEHLsOqfs7ImFPk7fSzay0', 'y', 'n', 'N', NULL, 'Y'),
(23, '2025-07-21 08:35:33', 'Christopher', 'Njagi', 'Muriuki', 'CN', 'cmuriuki@skm.co.ke', NULL, '$6$rounds=1024$1385875226$eI/y4H1xTZ.dytAc39YyDYkckNCzEHUbPNKDUcRe49qCojt.FIZToyQplGoo9HssZbiYxPnCVA6uXkqLknivW/', 'y', 'n', 'N', NULL, 'Y'),
(24, '2025-07-21 08:43:39', 'Dorcah', 'Otara', 'Kemunto', 'DO', 'dotara@skm.co.ke', NULL, '$6$rounds=1024$115148683$MSeIfnR453gXDE9NqW5wdyxZ8s8FD/odrU7eCYnXecjmBB8y0EOFp/L9V/OjfLW3/.K//M4QWcQIOK9QF4uRT.', 'n', 'y', 'N', NULL, 'Y'),
(25, '2025-07-21 08:45:03', 'Faith', 'Omondi', 'Anyango', 'FO', 'fomondi@skm.co.ke', NULL, '$6$rounds=1024$1345267685$wlFJwJ7PzO45ZJjAMQ6xzR7J8YubDl.EZoTGKIVnOxTel7fJjpkDg1zlFmY288W8dZBFgVz.aUZ8jpkDrUDwo0', 'n', 'y', 'N', NULL, 'Y'),
(26, '2025-07-21 08:46:21', 'Anthony', 'Njoroge', 'Nyoro', 'SN', 'anyoro@skm.co.ke', NULL, '$6$rounds=1024$1996760329$um6vJsJgg8pkqKVxKmRr8ZxilQZmV0Mxsq4pWPUkdA2W9HYUq4DAdSxiWtmE2z9kSeLpidkPJ2o/SHLL3PCae.', 'y', 'n', 'N', NULL, 'Y'),
(27, '2025-07-21 08:47:27', 'Douglas', 'Mochama', 'Nyabuto', 'DM', 'dnyabuto@skm.co.ke', NULL, '$6$rounds=1024$238218335$8mVukyfrKbFXHoISvlNxB6DHbW31amjW1pzm2iMUOD0Y8oRV1pIbkOgZe8xRt1CMcMYPeGHzOB.fRbLM4ylrI1', 'y', 'n', 'N', NULL, 'Y'),
(28, '2025-07-21 08:48:48', 'Timothy', 'Kiswii', 'Kyalo', 'TK', 'tkiswii@skm.co.ke', NULL, '$6$rounds=1024$1947990945$USSKbQrXUn39gwe0T4zKknm2MIioZYTc5DRIOMYs/5A1dEIuhu76GwOAqCncfhSUwbORfL2iQ6Cofi.iLmx8L/', 'n', 'y', 'N', NULL, 'Y'),
(29, '2025-07-21 08:50:00', 'Briton', 'Atuti', 'Kabungo', 'BA', 'batuti@skm.co.ke', NULL, '$6$rounds=1024$1292865620$7V1eebUC6pP8hP2A0lF/leNJsGv.WKZhe2icbRAKF9Z6.o7L6wCD/4Ol2RvTSPFHlG.mQ8Ia5dtdGDIlYgmgK/', 'y', 'n', 'N', NULL, 'Y'),
(30, '2025-07-21 08:52:05', 'Bridget', 'Mungai,', 'Wanja', 'BM', 'bmungai@skm.co.ke', NULL, '$6$rounds=1024$607199333$Gd01m7.CWQsXImHjihzdefzcKSDExJpulhDRJPLGwR0chrhcn6KVqLXccg2WFux6Ut4yat3j4zn8rPCIF9wMn0', 'n', 'y', 'N', NULL, 'Y'),
(31, '2025-07-23 05:10:54', 'Cynthia', 'Muiruri', 'Wambui', 'CM', 'cmuiruri@skm.co.ke', NULL, '$6$rounds=1024$1755759687$ycaamWit4ZQwvgBOG47/KJMYl7znGc.yv3WkqKnrO4Lhez0X72.PtS4DGdXhLW4bgKpDUUM/onRDipwIEH7g11', 'n', 'y', 'N', NULL, 'Y'),
(32, '2025-07-23 05:28:05', 'Bernard', 'Kamau', 'Gichango', 'GB', 'bkamau@skm.co.ke', NULL, '$6$rounds=1024$828001665$cRetc0e1uXDwtgse6/5Tnh6qf/apbjYSR/2EAx0sVeBJN.QQs8wMcTg87tdyTOmJ1B8cv7MxCJdhASqEh8T6R.', 'y', 'n', 'N', NULL, 'Y'),
(33, '2025-07-28 03:58:46', 'Jacob', 'Kumenda', NULL, 'JK', 'Jkumenda@skm.co.ke', NULL, '$6$rounds=1024$2006069152$Jvmm4hDfxtKfHyv18tD9L0Om9Y5diwD/4YSbwPYs2AGbPkPGPzogMDR3mQzjZrzTTxo.NLNPyUepA1zbdbUZ10', 'y', 'n', 'N', NULL, 'Y'),
(34, '2025-07-28 04:00:41', 'Diana', 'Wanjiku', 'Kaguongo', 'DW', 'dkaguongo@skm.co.ke', NULL, '$6$rounds=1024$1718486302$gDvUY/8guvy9S8GMWr4/wg2o4IqcyUb.11ahACB1wY8RTZCdtInMm6LYjY3MvVWGkNyABmPf/MH.MqJH3opHv/', 'y', 'n', 'N', NULL, 'Y'),
(35, '2025-07-28 04:14:25', 'Albert', 'Otieno', 'Owino', 'AO', 'Aotieno@skm.co.ke', NULL, '$6$rounds=1024$1954727104$uEaOO0GMD6Hk01nkmfiyJ4ZpQg45RPHahOk.kmnj/raqIByj6QeVRlazB3/9D9BpPFb3OqMdEwAYzWDMr04v3/', 'y', 'n', 'N', NULL, 'Y'),
(36, '2025-07-28 04:16:38', 'Moureen', 'Njuru', NULL, 'MN', 'mnjuru@skm.co.ke', NULL, '$6$rounds=1024$1241663987$OqbOWF1jaez.OK9Kr4AmWbyS7eARRPLTptNOxbBbr3sL8oV6Opbpa9lEo6H8sDVZuo/vZrZUEfVJM84w86pE21', 'y', 'n', 'N', NULL, 'Y'),
(37, '2025-07-28 04:24:06', 'Wilson', 'Okello', NULL, 'WO', 'wokello@skm.co.ke', NULL, '$6$rounds=1024$152427703$KMw5hCYoCqrLwqv9qiv6TaEwmlQPx1.KTsjfxZ5bBTdFoV26aypq78MBvaWVH89G0BtE4E5nJUBf76zS.1dBT0', 'y', 'n', 'N', NULL, 'Y'),
(38, '2025-07-28 04:26:56', 'Magdalene', 'Mutethya', 'Wayua', 'MW', 'mwayua@skm.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(39, '2025-07-28 04:28:10', 'Stellah', 'Chemtai', 'Omuse', 'SO', 'somuse@skm.co.ke', NULL, '$6$rounds=1024$568121781$g.daVThzLmd3xyEFbcN0kHWnZT56fnrftwgSDv4NcY/qIurkJXXlJZIdfOIqaam9jZY/EQF7GVLRSZHr9pKJn.', 'n', 'y', 'N', NULL, 'Y'),
(40, '2025-07-28 05:00:52', 'Denis', 'Nyaga', NULL, 'DN', 'dnyaga@pms.skm.co.ke', NULL, '$6$rounds=1024$1359370649$ca./A3PysPiTp9GpcxINXIQipLEuaX5NBVb6l1fZHcUgXVvEqVR./OKl6PwARpqNKSfOXvM00CDPNDJDfEgNB1', 'y', 'n', 'N', NULL, 'Y'),
(41, '2025-07-28 06:06:03', 'MaryAnn', 'Nyawira', NULL, 'MN', 'mnyawira@pms.skm.co.ke', NULL, '$6$rounds=1024$1259842995$KyA7hYJTlDKyFFekMLt7pocRPyGbHq82P36cdC4aZRxBdkgZelvFz.pO79uyKNfLo5QACnbkEalmgjjrO2s8s1', 'y', 'n', 'N', NULL, 'Y'),
(42, '2025-07-28 06:07:04', 'Lucy', 'Mukami', NULL, 'LM', 'lmukami@pms.skm.co.ke', NULL, '$6$rounds=1024$1001520798$AS6m4b/vJ8aAuFpArAW1io2hVG3RuKywpXALizq3JDiDimIt9eP5qHa7oXUpOl2nSwWBRwZlEjCplie2cHoYo1', 'y', 'n', 'N', NULL, 'Y'),
(43, '2025-07-28 06:08:06', 'Moses', 'Omondi', NULL, 'MO', 'momondi@pms.skm.co.ke', NULL, '$6$rounds=1024$913578741$b2WjDKwT45/TlQKk1F3uii6p0829A5mev87tsDYwK.GDa4h9Kh4xSUtum7jbMyfHlm/NXxZNKsGGJIMlP.9IM1', 'y', 'n', 'N', NULL, 'Y'),
(44, '2025-07-28 06:10:07', 'Tracy', 'Velma', NULL, 'TV', 'tvelma@pms.skm.co.ke', NULL, '$6$rounds=1024$708439014$JY2yazFlisN/tIUr5xn.5DfnPv3sDSvQ4SNiJEd5z4ERWH/O6O8NyiCnBohZ5xnwfissWMyTuoh3.oCY5X1ZY.', 'y', 'n', 'N', NULL, 'Y'),
(45, '2025-07-28 06:12:51', 'Celestine', 'Wamunga', NULL, 'CW', 'cwamunga@pms.skm.co.ke', NULL, '$6$rounds=1024$240797578$Oc3FXMNglci7bLpduvpoSurBMbAEvuhr5u6e/QigMLJUSdfmn4/ohrJHnHvV4SnjROz1hVeW84NMohUed6dem.', 'y', 'n', 'N', NULL, 'Y'),
(46, '2025-07-28 06:14:24', 'Collins', 'Jairus', NULL, 'CJ', 'cjairus@pms.skm.co.ke', NULL, '$6$rounds=1024$40684327$3bUY4ow1D7zgd7/ENjDV1wwF/jXyuMa4pWK6McG1XwV3LIroG3hfB9onUY6mD4ACOGWW36kf2SnFKgtrpmW4y1', 'y', 'n', 'N', NULL, 'Y'),
(47, '2025-08-13 09:46:24', 'Bright', 'Semei', NULL, 'BS', 'bsemei@skm.co.ke', NULL, '$6$rounds=1024$1239037446$7rVzGwZOffVw8zJfMHXUF9/5arfpiBiJlVRDUxGluCPdxHvR/5Ea0DAKaJbyN0lR/4ZmeErqFMZrtGFdvDvoV/', 'n', 'y', 'N', NULL, 'Y'),
(48, '2025-10-13 17:30:55', 'Julius', 'Macharia', NULL, 'JM', 'julius.macharia@skm.co.ke', NULL, '$6$rounds=1024$577101311$IJMBAT9LGhlYh9tZ7u8D.tmCZXIS2Fd3YriJZig/.IoVA2eiyY6PYmcPy7tcQ0z6uoDC4vZyimgdoGTFk.o8i.', 'n', 'y', 'N', NULL, 'Y'),
(49, '2025-10-13 18:22:02', 'John', 'Doe', NULL, 'JD', 'felixmauncho@gmail.com', 'employee_profile/1760522331_8.jpg', '$6$rounds=1024$73490082$5apwfN1jjw6QOHeCO/Omsv7iL9g4cKWy4f3vO.EspWTH/SsjkMBcP4b26WX1lV5hBK/DW0G5Ydz31E.rJPhJs.', 'n', 'y', 'N', NULL, 'N'),
(50, '2025-10-29 05:31:10', 'Euphemia', 'Okioga', '', 'EO', 'eokioga@skm.co.ke', NULL, '$6$rounds=1024$1597015477$Gz2cmPdsEZc.RLg9g0uENYDAYIoLouttEzQNat0Pr8lEaLSuEPlXbmgXyE/MqDuoz94J6XyE15xDvuRh.uKLH1', 'n', 'y', 'N', NULL, 'Y'),
(53, '2025-10-25 13:25:53', 'Justus', 'Kangethe', 'Kinyua', '', 'knagethe@example.com', NULL, '$2y$10$HwwAWoJNPD/Wt6F9WQIVB.7ZAUWrsen/dmIwWI4OD9A0RID4saT9y', 'n', 'n', 'N', NULL, 'Y'),
(55, '2025-10-26 13:05:29', 'Brian', 'Nyingesa', 'Julius', 'BN', 'brian@example.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(56, '2025-10-26 13:30:47', 'Felix', 'Mauncho', 'Nyandega', 'FM', 'felix.mauncho@webforte.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(57, '2025-10-26 19:42:27', 'Brian', 'Nyongesa', '', 'BM', 'brian@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(58, '2025-10-26 19:48:32', 'Felix', 'Mauncho', 'Nyandega', 'FM', 'felix.mauncho@sbsl.co.ke', NULL, '$6$rounds=1024$1591503764$eUj6v.CFTcDahAq2hs01fPzKPtTpTNKPUw.VY.Z2EmjhcQxqf457lbPBt7yE19WCRS0hurw3F7uIxEnoGv50E1', 'n', 'y', 'N', NULL, 'Y'),
(59, '2025-10-27 11:42:38', 'Charles', 'Owino', '', '', 'charles@example.com', NULL, NULL, 'n', 'n', 'N', NULL, 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `registration_tokens`
--

DROP TABLE IF EXISTS `registration_tokens`;
CREATE TABLE `registration_tokens` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PersonID` int(10) UNSIGNED DEFAULT NULL,
  `DateAdded` datetime NOT NULL,
  `Token1` varchar(128) NOT NULL,
  `Token2` varchar(128) NOT NULL,
  `PasswordSet` enum('y','n') NOT NULL DEFAULT 'n',
  `DatePasswordSet` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `registration_tokens`
--

INSERT INTO `registration_tokens` (`ID`, `PersonID`, `DateAdded`, `Token1`, `Token2`, `PasswordSet`, `DatePasswordSet`) VALUES
(1, 2, '2025-07-15 17:14:29', '388fa7882c4c50fa4d4333d8c0ab00b599e90bed33ba8a255c542435f3557b23', 'e75ad165c70544415b70b3129f9b76232b42bdff95d325ffd4b794c85cc7189a', 'y', '2025-07-15 17:17:06'),
(2, 3, '2025-07-15 18:44:19', '4be5bc04e7e6de5f3b5eaf2aad28adadb7b7d0a65b000fc94340ecc9f751727a', '65dc864889a0a32f607707c7077b905026275cfcf9ef4f66beca8eb69bd4f71e', 'n', '2025-07-23 02:52:12'),
(3, 4, '2025-07-15 18:46:16', '21873e126c42f13deafd06eefdd29bc9e4a61866ec84e80ff1eadcd4316f6e54', '9a454e1f3c27f20de60294e8bac00ce9aec0205795366291603eb6fcea9c70e1', 'n', '2025-10-15 12:36:06'),
(4, 5, '2025-07-15 18:52:11', 'dbb6c77ecb054a73e8498d1643140fa9790de0e1ffbbad048205f0142f893ff7', 'a9480edbc5373002cb06ba8332b20bffe86ae7be273ffa64117c6c9916515d3c', 'y', '2025-11-13 09:03:31'),
(5, 6, '2025-07-15 18:55:18', 'a76e6810ddf16636720fa9f0dd352649fb8b13c5fb0e7e20754855dababfa18c', '17e15c6be98c8b3bd54356961f51bf48c390ae55153c0862d3f7aaaab085b994', 'y', '2025-11-11 02:08:25'),
(6, 7, '2025-07-21 06:40:10', '52f09666b321649a28488761ecb153a4de51a0c3b2a79906027b3be89e5b6b7c', '43d0cd3d148bce6938acf070f8383c9902334cd1ce79e0adcf03474f97eab544', 'n', '2025-07-21 09:38:17'),
(7, 8, '2025-07-21 06:49:06', '182211db46904ccb8e1d6e8aad64ce5d70ab0fba0cf6b47df1227f9c71925b58', 'f108f7acf19195feff30884b9101acdff157b68fed6ca49a792e80d9518bba54', 'n', '2025-08-13 07:26:31'),
(8, 9, '2025-07-21 06:50:47', '4d8b0bccc58f1ad0831f4916599c6e8b2a14b2b4191e2563c4e54beac4ebb558', 'e4a8f918f1eb9bbf86d00297b06cd20ed014231fb4f893aa8c53537aea4cb86b', 'n', NULL),
(9, 10, '2025-07-21 06:56:06', '05438f5a8a82e40e024f6c026a6e535afdfbc89b492532e893d5a2c08dcc80d2', '60f05d751ae422d38ff5b9b147be921ef9ff592db3e207e3b861b972d44a1bd7', 'n', NULL),
(10, 11, '2025-07-21 06:57:25', '54fcac6bf6dcf7a8f015ffcafee30261fdd55212a66822ad4d4a2ab4c37ff5c4', '5ed82a757837de78963652701c439c1f516f0fd40895748f7f27a265be3be629', 'n', '2025-07-24 04:04:31'),
(11, 12, '2025-07-21 07:50:28', '93f85266b82c2228dcdefb546e7540a9ab8953b914b45dac88f9a1b5989060cc', '5f8301d4f9ccb7196f4622750477525dc49657cd58f96510d8cc7011cd7fa5f2', 'y', '2025-11-17 01:36:33'),
(12, 13, '2025-07-21 07:55:53', 'd737c8bb39d641d58f6d9a8308eb773d5a34def52d37f65e55a4fc82e50090c5', 'af80699e1c54f40881b9da6aa8343485ad75cea44c93dce4b4c6a90084d8ed98', 'n', '2025-08-13 02:28:29'),
(13, 14, '2025-07-21 08:01:11', '2eb6b7e3f9a2c400889e8e9b8aeac20d23738cbdf9bbd2546fcff71c22ac7105', '245bd21af437094fef45b861c20740ba427c696280f158780a60d4bf235e0360', 'n', NULL),
(14, 15, '2025-07-21 08:02:44', '0b14f8e87264418d70b40453db0bc73c12f2d7addfab874a0c078115c8f0f1c0', '6ad5cd2a597bcaea8b42aeaf142a22596a8e4339467ea99aa721fb2822e66590', 'n', '2025-07-23 02:50:12'),
(15, 16, '2025-07-21 08:04:53', '46d268a09afd34c3245af9de7dbbfe3d14ea9117b8a7fadf589298ced2a1c97c', 'ca03c45a4a98e17cd9bb6be9d9c44e06fd6e79c8458e7a8283c23265284f41c0', 'n', NULL),
(16, 17, '2025-07-21 08:05:58', '791c2c20a2361c31e22363239b295bcad9924fdc8d6bdc40304ebfa5341de240', '84a816ddf68bf10c5b33b272e14e7f7d9193fa6133b9ba79915330b3cc5ef436', 'n', NULL),
(17, 18, '2025-07-21 08:08:26', '08891ac16fa495c4219ce02a0db7bdceb99529298654169536071980ebac55a9', '3df52e488c9a80313dbed6a52302be62cb57398c77faa2e96dff3870ff0f24d5', 'y', '2025-11-11 00:47:14'),
(18, 19, '2025-07-21 08:11:52', 'c90fce60a1823845d52bef72529a86e4a76fb1da0106b70c8e2e59c56c40c913', '05093da37d8d8ff6aaa2dc7be23f5215522eeb0ccea8a61725bd22f1b927aec9', 'n', '2025-07-21 09:00:39'),
(19, 20, '2025-07-21 08:17:05', 'befc54f4935b903c9bc85de2f67357f46dab11f3f45094b0b043cb4f5eed771b', 'bcaed75db7b35bb8105b2ff6c08a83a7fdab003881086ddebb64f68b72fb842f', 'n', NULL),
(20, 21, '2025-07-21 08:23:45', '6b1e1c12c3f36dafd0ba4f4637726f1d7ee6ed776b026bd545204984eb9a338e', '20056c65802935edabbbcc538c99f9f3e7e96407b39638be8e9870e674423621', 'n', '2025-07-21 09:28:04'),
(21, 22, '2025-07-21 08:33:54', 'bd8af902e03bab69d536b733ca96143d0eda7d7b4f4c73a7e86914229ecd82fe', 'eb1069e22d47716cb43e21bb9733850a56c12414c7b9970157356b9174458c16', 'n', '2025-07-21 09:20:51'),
(22, 23, '2025-07-21 08:35:33', '32dce8ebdd3768323b9101797477ef844f15d67538b57fc17a1ea4e23a21fb6e', 'c1f5a216d80d76cd6238e5cca6b32da49ebf5f2cff7e63788ef1f3cc68d22086', 'n', '2025-07-21 09:54:35'),
(23, 24, '2025-07-21 08:43:39', 'b39063349587fcff1fb1e1b95831f64dce0526242c84cc45c203c2eaa16ef6c1', 'fd814a6bef2572d57c99dc7d7003f1302def580cffbc3ee17b43c846e7016f28', 'y', '2025-11-11 01:01:37'),
(24, 25, '2025-07-21 08:45:03', '4422cffc8931e494e3b60533485f7a66a49dc969ac6c5e2719979198205230f5', '14f9f4ff6ce9e5535debf7b4611cb61fb748126173d6fc543853becedfe8e89c', 'y', '2025-11-11 00:39:56'),
(25, 26, '2025-07-21 08:46:21', '217576caf1ae8e32ad172a973a4549801234409ccd482175abfa9f15312d4e8e', '606c2a0d3e987624a940ecc4812dccf7aa59b3c679b77d6ed0d542667a09877e', 'n', '2025-08-04 01:55:56'),
(26, 27, '2025-07-21 08:47:27', '0160d65de887245688e67b1b5c24e530c698ec50a68e6b52bea7a912b4548fe9', '1c5ad713f094288b4babf568e4da2669493f71f0d8bac2c47903ef6d538490ec', 'n', '2025-07-22 04:06:06'),
(27, 28, '2025-07-21 08:48:48', '55a1580f6b17ad645e49fa98fbfa32aff663b7637374cc9b1e46572cc4b30b3f', '455bcc7841f9f1c2e75b019e3dd10ce766c83301e0e1531fe2fbcc702085f30b', 'y', '2025-11-11 09:11:47'),
(28, 29, '2025-07-21 08:50:00', 'bdfb7bdb09966ad7ec6520fdf132a5e19c12bdc7e48bda184b3bd90b8ff16ac6', 'c9d4ac6e40613ffe3ede5f6ac7681fedbed8a0b3da2fe7d65c556666ca01d65b', 'n', '2025-07-21 08:57:01'),
(29, 30, '2025-07-21 08:52:05', '1f6be8bcc2aa46fa5f660869f06c5f16c72e4d660d1fdeca7332b1f19a0badac', '72a9d1b69b6d3a8765f86b5f7f1c3b8b8665b447bb0419f748710f1ee4af3c4f', 'y', '2025-11-13 10:08:32'),
(30, 31, '2025-07-23 05:10:54', '8dd0625a02bdbb79a8ae295ab27416ef124a0965e061e4e85ad92b77b3f87761', '67c086e0ef61d4e7c6e636f0f853f46778f20be878a9711b887b872c8b1199cd', 'y', '2025-11-11 01:09:03'),
(31, 32, '2025-07-23 05:28:05', 'a7db949b6e61f8a1b772dd56d38e27062b3181fca68ee028dfcf243b90a67cb8', '89f7866856be8d9277d32a4af07808547f1504f7abff9ba211ef3f9f27adcace', 'n', '2025-11-11 00:19:55'),
(32, 33, '2025-07-28 03:58:46', '15f83c9165dd69d8ac90c911a5a9b2e665af354e9e2a561acc5c7e9b33d6cc9b', '7d7a60e57fa5f97672113f45fe64be122babc178adad7cce447ea376f13fc866', 'n', '2025-07-28 05:45:10'),
(33, 34, '2025-07-28 04:00:41', '0cfe7e956d0c35f2a318004feef5fa14b5fcd64047580f068147dd47dfd7c433', '912454b6d864241d76f3a33bcd5753455be6e06957cfb09715b998d4859d7e5d', 'n', '2025-08-04 01:42:42'),
(34, 35, '2025-07-28 04:14:25', '7710c49d3c512c775acbee44187307c7442328676e51c06d90dff3c15a1fa8ab', 'b945704ab60680f9d181dbfc175d9a9e94661636186083206bdd1b717536dfad', 'n', '2025-08-13 02:25:36'),
(35, 36, '2025-07-28 04:16:38', 'c84ab2b1a0b52a7c9338dba59592c3aba7faa6736075150ee5a7f0c03152eb84', '2c884e764d4aefa92dd98fb286b8489c4fb799701d53ff90f9330d465e1e9116', 'n', '2025-08-04 01:45:14'),
(36, 37, '2025-07-28 04:24:06', 'fad8ec3c94f5fbdbc869b8f24ea41842201139a1935dffc56025d4992f79b77c', '539e2c47ce252dea805198fea8fbaa87e15cc02ac14bc7435cf2195e957110db', 'n', '2025-07-28 04:34:14'),
(37, 38, '2025-07-28 04:26:56', '326f09b02cb6431fa8cbf5e804c316a5e81f42df36ae253865c3e33351e01ab2', '511acf7ebd3d5ff35d70131e4f40d30dcdb8bff200a9c449441d3edcda7e357d', 'n', NULL),
(38, 39, '2025-07-28 04:28:10', '7b9d2d0cb2e76ddf752ba80e2797b8ef979b96fa5488c951f769b94cfce3da1f', '07d221d90ce604d58543a3dd82b916d28602d97925bf55e677615303af464736', 'y', '2025-11-11 00:45:39'),
(39, 40, '2025-07-28 05:00:52', '0a5b6dcf91a4542434df2f07a63fa54bd28d589e713573e7224b1bc47c29dab2', 'f1ff2789713e408136387e223405fec2857a3a4a9884f68a7fe7b2d84e13a973', 'n', '2025-07-28 09:34:41'),
(40, 41, '2025-07-28 06:06:03', 'bd98678e7af5805db9b5d0295b8e5d964561d9b756a679540a6f6e243a2fbaf8', 'e37840e25affb6f94a41d418c84a8d50ee28cd57e454970e88031f1ec607e4a8', 'n', '2025-08-26 03:27:48'),
(41, 42, '2025-07-28 06:07:04', '0b7b5aa31c2a0786d2de7c6cf9842dc3aefa58150b5114bafd2157b4b86ed1ef', '018ee3af7d6a15f6e92f578ecb95cf12f240990f2d008798aba5e099488becf4', 'n', '2025-07-28 09:44:19'),
(42, 43, '2025-07-28 06:08:06', '2b8256df34bf62f890077e87ec4eecb7e49bd10757a8ea1a5e343fc3b7df2a03', '5d4dcad4bede755576f2f8b73087723209bab9f6205e5f0ca4b88f2e9ca53ccb', 'n', '2025-08-13 09:42:47'),
(43, 44, '2025-07-28 06:10:07', '1735f5b707e021c807867b52064e62c6a5eb75fdb4069e040f323fc1892ece2d', 'a9e44c0afa5c08f081ee557f5a6804ac0403dc4593e7a2491747ae45f9e97813', 'n', '2025-07-28 09:56:05'),
(44, 45, '2025-07-28 06:12:51', '09e952dcb6bfe2e9427448518fcbb2d7ef22711ff559571d8d883eda3a8afb13', 'fb71ea53f7b52b2acc008ec3e0172c98d40b29293b4afeb7679e34741aa30176', 'n', '2025-08-01 02:39:27'),
(45, 46, '2025-07-28 06:14:24', '1ad62fe82dceda397663f76ef7de4f435a549807d5d595fd6ec8dd1b96e0ac92', '664d1706db6a935fbac50a75f4036a1f9ead871b2a00e3e9bb974915240042a9', 'n', '2025-08-06 09:41:58'),
(46, 47, '2025-08-13 09:46:24', '30c1ba139694f5f8c198d53581fed8e6cdd0c7f4eb3706380e74b760a3e94b0b', '052afe73fe62467b2190646fb77ead30dd2c3823ad5cb686e56fab51d84b9fc9', 'y', '2025-11-11 01:47:04'),
(47, 48, '2025-10-13 17:30:55', '3c1056161d9a62e9f065abc8509c21ca9a8eb6c0b242b4ebc941e354cd701eae', '18c4e6de8395c3522781c310feb800e1a6f23c15e9de0fce69047a5de3d08c87', 'y', '2025-11-11 00:44:47'),
(48, 49, '2025-10-13 18:22:02', '7fca3f2a512f8dcdb2f52b1cb7718d364fe0ceb197344a873f33b1dd2bc5508d', 'c64c7ee45052cd5d4e8e53714eb9623f35cb4fa0412c5a0a116f92c4f8787a78', 'y', '2025-11-03 16:26:07'),
(50, 55, '2025-10-26 13:05:29', '91ffe892bae613706c4780f97af4b53d7f8c9f5b584ed9d0f44d2ccb4cc61985', 'b15cc763a35790d738ddb2533a6b89cbcaf3a5decde639a805a53301e810473d', 'n', NULL),
(51, 56, '2025-10-26 13:30:47', 'cca8ce780912a5f092d2287d91948b22fa5f5e5843974fb9505ad5e8c9f33fab', '10e2741a1b229abfba83f683d87498503f2875bc972055c6d9c8d9adcafe4141', 'n', NULL),
(52, 57, '2025-10-26 19:42:27', '1cc9b346eef38c2b11a65f12325a6a8d71f0a21c20c981a739b1b32d189a01fc', 'a6d6a3ba74de5c111b41702b2a75d2157fad3a389dbad2b18c022cf64ef6f42b', 'n', NULL),
(53, 58, '2025-10-26 19:48:32', 'b718e5a7684f75195f266482444128084fa441852fed8a8c3808a8d4f6027de6', '22124c4b9913715bf0435700dbf1037c46c8ca0b42053dd6c29d8576767b872a', 'y', '2025-10-27 07:38:37'),
(54, 59, '2025-10-27 11:42:38', '695f74abcbd9cf1e2a948680f7bdf8cff70ccace533d80f37056c20f3d3b8f22', 'f4df9d100b8cc40ea253e771783beed432d5634c325324fda4b8a7945d6ed963', 'n', NULL),
(55, 50, '2025-11-11 00:36:06', '286fb4299ecfc6f09720cac087e15942fe1b33b4fe822dbd8888b03b6959796c', '1f87ad9794035959b76bff4d2a658b00771ea990a253ada94bb077307680f8de', 'y', '2025-11-17 02:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `tija_clients`
--

DROP TABLE IF EXISTS `tija_clients`;
CREATE TABLE `tija_clients` (
  `clientID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `clientCode` varchar(20) NOT NULL,
  `clientName` varchar(256) NOT NULL,
  `clientDescription` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `clientIndustryID` int(11) DEFAULT NULL,
  `clientSectorID` int(11) DEFAULT NULL,
  `clientLevelID` int(11) NOT NULL DEFAULT 1,
  `clientPin` int(11) DEFAULT NULL,
  `vatNumber` varchar(120) DEFAULT NULL,
  `accountOwnerID` int(11) NOT NULL,
  `isClient` enum('Y','N') NOT NULL DEFAULT 'N',
  `inhouse` enum('Y','N') NOT NULL DEFAULT 'N',
  `countryID` int(11) DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `clientStatus` enum('active','inactive') NOT NULL DEFAULT 'active',
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_clients`
--

INSERT INTO `tija_clients` (`clientID`, `DateAdded`, `clientCode`, `clientName`, `clientDescription`, `orgDataID`, `entityID`, `clientIndustryID`, `clientSectorID`, `clientLevelID`, `clientPin`, `vatNumber`, `accountOwnerID`, `isClient`, `inhouse`, `countryID`, `city`, `clientStatus`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 17:10:47', 'SAL-213431', 'SKM Africa LLP', NULL, 1, 1, 42, NULL, 1, 0, NULL, 1, 'N', 'Y', NULL, NULL, 'active', 1, '2025-07-15 17:10:47', 'N', 'N'),
(2, '2025-07-15 19:12:53', 'SBSL-078675', 'Strategic Business solutions Limited', '<p>SBSL Is a finatial and Human Resouece tech Giant</p>', 1, 1, 35, 8, 1, NULL, '23434543', 4, 'N', 'N', NULL, NULL, 'active', 2, '2025-07-15 19:21:03', 'N', 'N'),
(3, '2025-07-18 09:08:11', 'K-015427', 'KCB', NULL, 1, 1, 1, 1, 1, NULL, 'Y678987654I', 3, 'N', 'N', NULL, NULL, 'active', 4, '2025-07-18 09:08:44', 'N', 'N'),
(4, '2025-07-18 09:15:00', 'E-243010', 'Equity', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 5, 'N', 'N', 25, 'Nairobi', 'active', 4, '2025-07-18 09:15:00', 'N', 'N'),
(5, '2025-07-23 02:54:24', 'F&-196136', 'Food &Us', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 7, 'N', 'N', NULL, NULL, 'active', 23, '2025-07-23 09:54:24', 'N', 'N'),
(6, '2025-07-23 03:02:37', 'ICPAAC-273147', 'IGAD Climate Predictions and Application Centre', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 11, 'N', 'N', 25, 'Nairobi', 'active', 3, '2025-07-23 10:02:37', 'N', 'N'),
(7, '2025-07-23 03:27:12', 'HIL-671863', 'Heaves International limited', NULL, 1, 1, NULL, NULL, 1, NULL, 'NA', 8, 'N', 'N', NULL, NULL, 'active', 21, '2025-07-23 10:27:12', 'N', 'N'),
(8, '2025-07-23 03:38:08', 'KPA-205193', 'Kenya Ports Authority', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', 25, 'Nairobi', 'active', 3, '2025-07-23 10:38:08', 'N', 'N'),
(9, '2025-07-23 04:08:11', 'TCF-623506', 'Tsavo Skywalk Executive Apartments Limited', NULL, 1, 1, 48, 11, 1, NULL, 'P051885429T', 8, 'N', 'N', NULL, NULL, 'active', 22, '2025-07-24 12:41:16', 'N', 'N'),
(10, '2025-07-23 04:09:03', 'TCF-952590', 'Tsavo City Foundation', NULL, 1, 1, NULL, NULL, 1, NULL, 'P051932058L', 8, 'N', 'N', NULL, NULL, 'active', 22, '2025-07-23 11:09:03', 'N', 'N'),
(11, '2025-07-23 04:37:21', 'MLL-810850', 'Maven Luxury Limited', NULL, 1, 1, NULL, NULL, 1, NULL, '12345', 3, 'N', 'N', NULL, NULL, 'active', 15, '2025-07-23 11:37:21', 'N', 'N'),
(12, '2025-07-23 04:37:33', 'W-018978', 'WOHED', NULL, 1, 1, NULL, NULL, 1, NULL, '123456789', 11, 'N', 'N', NULL, NULL, 'active', 18, '2025-07-23 11:37:33', 'N', 'N'),
(13, '2025-07-23 04:37:41', 'K-430263', 'KIPRO', NULL, 1, 1, NULL, NULL, 1, NULL, '12345', 8, 'N', 'N', NULL, NULL, 'active', 23, '2025-07-23 11:37:41', 'N', 'N'),
(14, '2025-07-24 12:27:29', 'D-309727', 'DMW', NULL, 1, 1, 2, 1, 1, NULL, NULL, 15, 'N', 'N', NULL, NULL, 'active', 15, '2025-07-24 12:27:29', 'N', 'N'),
(15, '2025-07-24 05:30:54', 'MGTAKL-094510', 'Mr. Green Trading Africa Kenya Limited	', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 9, 'N', 'N', NULL, NULL, 'active', 15, '2025-07-24 12:30:54', 'N', 'N'),
(16, '2025-07-24 12:50:12', 'DDCL-727514', 'Dell Development Company Ltd', NULL, 1, 1, 48, 11, 1, NULL, 'P051950577R', 10, 'N', 'N', 25, 'Nairobi', 'active', 22, '2025-07-24 13:14:42', 'N', 'N'),
(17, '2025-07-24 06:02:52', 'YL-846392', 'Yegomobility Limited	', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 8, 'N', 'N', NULL, NULL, 'active', 15, '2025-07-24 13:02:52', 'N', 'N'),
(18, '2025-07-24 06:21:17', 'KRGC-739396', 'Kenya Railways Golf Club', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-07-24 13:21:17', 'N', 'N'),
(19, '2025-07-25 12:15:55', 'RSPTL-789452', 'Royal Suburb Phase Three Limited', NULL, 1, 1, 48, 11, 1, NULL, NULL, 22, 'N', 'N', 25, 'Nairobi', 'active', 22, '2025-07-25 12:15:55', 'N', 'N'),
(20, '2025-08-06 04:00:35', 'MCL-515203', 'Moran capital Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 15, '2025-08-06 11:00:35', 'N', 'N'),
(21, '2025-08-06 04:05:08', 'AAMC-039023', 'Accretive Africa Management Consulting', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:05:08', 'N', 'N'),
(22, '2025-08-06 04:06:35', 'AAAL-989397', 'Alternative Ad Agency Limited', NULL, 1, 1, NULL, NULL, 1, NULL, 'P051576610C', 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-26 11:37:44', 'N', 'N'),
(23, '2025-08-06 04:07:11', 'F&UL-275272', 'Food & Us Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:07:11', 'N', 'N'),
(24, '2025-08-06 04:07:46', 'LI-019825', 'Lesaffre International', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:07:46', 'N', 'N'),
(25, '2025-08-06 04:08:13', 'MLL-590612', 'Maven Luxury Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:08:13', 'N', 'N'),
(26, '2025-08-06 04:08:48', 'MGTAKL-387568', 'Mr Green Trading Africa Kenya Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:08:48', 'N', 'N'),
(27, '2025-08-06 04:09:08', 'TND(-484652', 'The Netherlands Development (FMO)', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:09:08', 'N', 'N'),
(28, '2025-08-06 04:09:32', 'TJKL-231423', 'Triple Jump Kenya Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:09:32', 'N', 'N'),
(29, '2025-08-06 04:09:54', 'YML-827173', 'Yego Mobility Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:09:54', 'N', 'N'),
(30, '2025-08-06 04:10:13', 'I-982639', 'Intelegant', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:10:13', 'N', 'N'),
(31, '2025-08-06 04:10:32', 'FF-428952', 'Fistula Foundation', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:10:32', 'N', 'N'),
(32, '2025-08-06 04:10:51', 'JIL-290194', 'Jireh Innovations Ltd', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:10:51', 'N', 'N'),
(33, '2025-08-06 04:11:10', 'MCIK-054747', 'Methodist Church in Kenya', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:11:10', 'N', 'N'),
(34, '2025-08-06 04:11:44', 'H-786412', 'HEVA', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:11:44', 'N', 'N'),
(35, '2025-08-06 04:13:47', 'NCL-402370', 'Nirvana Credit Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:13:47', 'N', 'N'),
(36, '2025-08-06 04:14:08', 'NBL-216912', 'Nirvana Brokers Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:14:08', 'N', 'N'),
(37, '2025-08-06 04:14:36', 'SL-241472', 'SKM LLP', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:14:36', 'N', 'N'),
(38, '2025-08-06 04:16:25', 'HJW-724912', 'Hakan John Wilson', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:16:25', 'N', 'N'),
(39, '2025-08-06 04:16:52', 'A-879204', 'Africon', NULL, 1, 1, 21, 5, 1, NULL, 'P051630180A', 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-26 10:30:17', 'N', 'N'),
(40, '2025-08-06 04:17:18', 'A-161727', 'Agrichamp', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:17:18', 'N', 'N'),
(41, '2025-08-06 04:17:42', 'YML-987379', 'Yego Mobility Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:17:42', 'N', 'N'),
(42, '2025-08-06 04:18:06', 'FEL-945469', 'FJM Enterprises Ltd', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:18:06', 'N', 'N'),
(43, '2025-08-06 04:18:34', 'DA-407592', 'David Abwoga', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:18:34', 'N', 'N'),
(44, '2025-08-06 04:19:07', 'SA-969742', 'SKM Advisory', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:19:07', 'N', 'N'),
(45, '2025-08-06 04:19:29', 'EEL-062169', 'Elgon Events Ltd', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:19:29', 'N', 'N'),
(46, '2025-08-06 04:21:26', 'PUM-416161', 'Pick up Mtaani', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:21:26', 'N', 'N'),
(47, '2025-08-06 04:21:46', 'DAM-674367', 'DWM Asset Management', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:21:46', 'N', 'N'),
(48, '2025-08-06 04:22:12', 'MLD-145324', 'Maven Luxury Dubai', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:22:12', 'N', 'N'),
(49, '2025-08-06 04:22:31', 'SE-305464', 'Sammy Ellouze', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:22:31', 'N', 'N'),
(50, '2025-08-06 04:24:04', 'SL-407638', 'Selu Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:24:04', 'N', 'N'),
(51, '2025-08-06 04:24:37', 'SGFL-902132', 'Selu Galana Farm Ltd', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 25, '2025-08-06 11:24:37', 'N', 'N'),
(52, '2025-08-12 04:51:50', 'BHN-835765', 'Baraka Health Net', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 5, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 11:51:50', 'N', 'N'),
(53, '2025-08-12 05:08:07', 'SKI-473090', 'SOS Kinderdorf International', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 6, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:08:07', 'N', 'N'),
(54, '2025-08-12 05:11:10', 'TIOSA-286450', 'The Institute of Social Accountability', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:11:10', 'N', 'N'),
(55, '2025-08-12 05:16:51', 'KRCL-841464', 'Kenya Reinsurance Corporation Ltd', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:16:51', 'N', 'N'),
(56, '2025-08-12 05:25:33', 'SICSL-735938', 'SIC Investment Cooperative Society Ltd', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:25:33', 'N', 'N'),
(57, '2025-08-12 05:28:07', 'KS-818469', 'Kenversity Sacco', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:28:07', 'N', 'N'),
(58, '2025-08-12 05:43:02', 'EN-956392', 'Equality Now', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:43:01', 'N', 'N'),
(59, '2025-08-12 05:46:14', 'MESPT(-260568', 'Micro Enterprises Support Programme Trust (MESPT)', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 31, 'N', 'N', 25, 'Nairobi', 'active', 31, '2025-08-12 12:46:14', 'N', 'N'),
(60, '2025-08-25 01:12:23', 'BIAF-412131', 'Bridge International Academies Foundation', '<p>SCHOOLS IN KENYA, UGANDA, NIGERIA &amp; INDIA</p>', 1, 1, NULL, NULL, 1, NULL, 'P052300160M', 36, 'N', 'N', NULL, NULL, 'active', 36, '2025-08-25 08:30:59', 'N', 'N'),
(61, '2025-08-26 02:12:45', 'F&-153819', 'Food &Us', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 7, 'N', 'N', NULL, NULL, 'active', 23, '2025-08-26 09:12:45', 'N', 'N'),
(62, '2025-08-26 02:13:44', 'H-691417', 'HEVA', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 23, '2025-08-26 09:13:44', 'N', 'N'),
(63, '2025-08-26 03:07:19', 'H-216518', 'HEVA', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 3, 'N', 'N', NULL, NULL, 'active', 23, '2025-08-26 10:07:19', 'N', 'N'),
(64, '2025-08-26 03:15:52', 'AEAL-364746', 'AARO East Africa Limited', '<p>9iuygfdsa</p>', 1, 1, 2, 1, 1, NULL, 'I4764655288Y', 8, 'N', 'N', NULL, NULL, 'active', 4, '2025-08-26 10:30:14', 'N', 'N'),
(65, '2025-08-26 03:20:15', 'R-480715', 'rtceytyfy', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 4, 'N', 'N', 25, 'Nairobi', 'active', 4, '2025-08-26 10:20:15', 'N', 'N'),
(66, '2025-08-26 08:49:21', 'EBKL-657471', 'Equity Bank Kenya Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 7, 'N', 'N', NULL, NULL, 'active', 19, '2025-08-26 15:49:21', 'N', 'N'),
(67, '2025-08-26 08:54:38', 'PPKL-929874', 'Paystack Payments Kenya Limited', NULL, 1, 1, NULL, NULL, 1, NULL, NULL, 7, 'N', 'N', NULL, NULL, 'active', 19, '2025-08-26 15:54:38', 'N', 'N'),
(68, '2025-08-27 08:41:13', 'LPP-323863', 'LONGHORN PUBLISHERS PLC', NULL, 1, 1, NULL, NULL, 1, NULL, 'P000593793I', 8, 'N', 'N', NULL, NULL, 'active', 21, '2025-08-27 15:41:13', 'N', 'N'),
(69, '2025-09-02 12:22:10', 'ACA-125746', 'Acre Consulting Africa', NULL, 1, 1, NULL, NULL, 1, NULL, 'I4764655288Y', 4, 'N', 'N', NULL, NULL, 'active', 4, '2025-09-02 12:22:10', 'N', 'N'),
(70, '2025-09-02 12:24:24', 'RACL-741928', 'Rose Avenue Consulting Limited', NULL, 1, 1, NULL, NULL, 1, NULL, 'I4764655288Y', 4, 'N', 'N', NULL, NULL, 'active', 4, '2025-09-02 12:24:24', 'N', 'N'),
(71, '2025-10-25 11:43:08', 'NCL-075423', 'Nirvana Credit Limited', NULL, 1, 2, 11, NULL, 1, 0, NULL, 1, 'N', 'Y', NULL, NULL, 'active', 1, '2025-10-25 11:43:08', 'N', 'N'),
(72, '2025-10-26 19:34:08', 'SK-490938', 'SBSL Kenya', NULL, 3, 11, 24, NULL, 1, 0, NULL, 1, 'N', 'Y', NULL, NULL, 'active', 1, '2025-10-26 19:34:07', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_addresses`
--

DROP TABLE IF EXISTS `tija_client_addresses`;
CREATE TABLE `tija_client_addresses` (
  `clientAddressID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `clientID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `address` text NOT NULL,
  `postalCode` varchar(20) DEFAULT NULL,
  `clientEmail` int(11) DEFAULT NULL,
  `City` varchar(120) NOT NULL,
  `countryID` int(11) NOT NULL,
  `addressType` enum('officeAddress','postalAddress') NOT NULL,
  `billingAddress` enum('Y','N') NOT NULL DEFAULT 'N',
  `headquarters` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_client_addresses`
--

INSERT INTO `tija_client_addresses` (`clientAddressID`, `DateAdded`, `clientID`, `orgDataID`, `entityID`, `address`, `postalCode`, `clientEmail`, `City`, `countryID`, `addressType`, `billingAddress`, `headquarters`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 19:12:53', 2, 1, 1, '<p>5th Floor Rainbow Tower, Muthithi Road, Westlands</p>', '00100', NULL, 'Nairobi', 25, 'officeAddress', 'Y', 'Y', 2, '2025-07-15 19:12:53', 'N', 'N'),
(2, '2025-07-18 09:08:11', 3, 1, 1, '<p>dsdkfas d</p>', '00100', NULL, 'Nairobi', 25, 'officeAddress', 'Y', 'Y', 4, '2025-07-18 09:08:11', 'N', 'N'),
(3, '2025-07-23 02:54:24', 5, 1, 1, '<p>External Audit services&nbsp;</p>', 'Waiyaki way', NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 23, '2025-07-23 09:54:24', 'N', 'N'),
(4, '2025-07-23 03:27:12', 7, 1, 1, '<p>P.O BOX 14438 G.P.O NAIROBI</p>', ' 14438 G', NULL, '00100', 25, 'postalAddress', 'N', 'N', 21, '2025-07-23 10:27:12', 'N', 'N'),
(5, '2025-07-23 04:09:03', 10, 1, 1, '<p>Coral Bells, Thindigua, Off Kiambu Road</p>', NULL, NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 22, '2025-07-23 11:09:03', 'N', 'N'),
(6, '2025-07-23 04:37:41', 13, 1, 1, '<p>KIPRO CENTER</p>', 'Waiyaki way', NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 23, '2025-07-23 11:37:41', 'N', 'N'),
(7, '2025-07-24 12:50:12', 16, 1, 1, 'Kisauni Rd, Nairobi West, Nairobi Kenya.', NULL, NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 22, '2025-07-24 12:50:12', 'N', 'N'),
(8, '2025-07-25 12:15:55', 19, 1, 1, 'Coral Bells, Thindigua off Kiambu Rd', NULL, NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 22, '2025-07-25 12:15:55', 'N', 'N'),
(9, '2025-08-25 08:28:00', 60, 1, 1, '<p>P.O BOX 78105 VIWANDANI<br>TELEPHONE: +2547321600000, EMAIL:<br>LEGAL@BRIDGE.AC.KE<br>COUNTY: NAIROBI, DISTRICT: NAIROBI EAST DISTRICT ,<br>LOCALITY: NAIROBI EAST<br>STREET: MOMBASA ROAD, BUILDING: TULIP HOUSE</p>', '78105', NULL, 'NAIROBI', 25, 'officeAddress', 'N', 'N', 36, '2025-08-25 08:28:00', 'N', 'N'),
(10, '2025-08-26 10:07:43', 39, 1, 1, '<p>P. O. Box : 856</p>', '00606', NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 25, '2025-08-26 11:23:25', 'N', 'N'),
(11, '2025-08-26 03:15:52', 64, 1, 1, '<p>Koulukatu 1</p>', '00100', NULL, 'Nairobi', 25, 'officeAddress', 'Y', 'Y', 4, '2025-08-28 19:06:39', 'N', 'N'),
(12, '2025-08-26 10:32:19', 64, 1, 1, '<p>Lindhagensgatan 94</p>', '00100', NULL, 'MNVMNB,J', 15, 'officeAddress', 'N', 'N', 4, '2025-08-28 19:05:52', 'N', 'N'),
(13, '2025-08-26 08:54:38', 67, 1, 1, '<p>Lower Kabete Road Westlands</p>', NULL, NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 19, '2025-08-26 15:54:38', 'N', 'N'),
(14, '2025-08-27 08:41:13', 68, 1, 1, '<p>Funzi Road,<br>Industrial Area,<br>Nairobi, Kenya</p>', NULL, NULL, 'Nairobi', 25, 'officeAddress', 'N', 'N', 21, '2025-08-27 15:41:13', 'N', 'N'),
(15, '2025-08-28 19:00:13', 64, 1, 1, '<p>Morocco Subsidiary&nbsp;<br>Marrakech Street</p>', '00100', NULL, 'Marrakech', 34, 'officeAddress', 'N', 'N', 4, '2025-08-28 19:00:13', 'N', 'N'),
(16, '2025-09-02 12:22:10', 69, 1, 1, '<p>International Life house&nbsp;<br>Mama Ngina street<br>Nairobi&nbsp;</p>', '00100', NULL, 'Nairobi', 25, 'officeAddress', 'Y', 'Y', 4, '2025-09-02 12:22:10', 'N', 'N'),
(17, '2025-09-02 12:24:24', 70, 1, 1, '<p>Blue Violet House&nbsp;<br>off Kilimano Road&nbsp;</p>', '00100', NULL, 'Nairobi', 25, 'officeAddress', 'Y', 'Y', 4, '2025-09-02 12:24:24', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_contacts`
--

DROP TABLE IF EXISTS `tija_client_contacts`;
CREATE TABLE `tija_client_contacts` (
  `clientContactID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `contactTypeID` int(11) DEFAULT NULL,
  `contactName` varchar(255) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `salutationID` int(11) DEFAULT NULL,
  `contactEmail` varchar(256) DEFAULT NULL,
  `contactPhone` varchar(22) DEFAULT NULL,
  `clientAddressID` int(11) DEFAULT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_client_contacts`
--

INSERT INTO `tija_client_contacts` (`clientContactID`, `DateAdded`, `userID`, `clientID`, `contactTypeID`, `contactName`, `title`, `salutationID`, `contactEmail`, `contactPhone`, `clientAddressID`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 19:23:32', 2, 2, 1, 'Dennis  Wabukala', 'Head of Project ', 1, 'dennis@sbsl.co.ke', '+254785659652', 1, 2, '2025-07-15 19:23:32', 'N', 'N'),
(2, '2025-07-18 09:11:35', 4, 3, 1, 'Brian Nyongesa', 'ceo', 1, 'brown@sbsl.co.ke', '+2540258654', 2, 4, '2025-07-18 09:11:35', 'N', 'N'),
(3, '2025-07-23 10:07:22', 3, 6, NULL, 'xx', 'xx', 1, 'xx', 'xx', NULL, 3, '2025-07-23 10:07:22', 'N', 'N'),
(4, '2025-07-23 03:45:39', 21, 7, 1, 'Peter Peter', 'Accontant', 1, 'peter@heavesinternational.com', NULL, 4, 21, '2025-07-23 10:45:39', 'N', 'N'),
(5, '2025-08-26 03:33:59', 4, 64, 1, 'Martha Jordan', 'Sales Director', 2, 'info@webfortekenya.com', '+60722540168', 12, 4, '2025-08-28 19:10:12', 'N', 'N'),
(6, '2025-08-28 06:46:37', 21, 68, NULL, 'Alfred Muia', 'Accountant', 1, 'amuia@longhornpublishers.com', '0723416187', NULL, 21, '2025-08-28 06:46:37', 'N', 'N'),
(7, '2025-08-28 19:02:26', 4, 64, 1, 'Mauncho Okemwa', 'Marketing Management', 1, 'info@webfortekenya.com', '+60722540168', 15, 4, '2025-08-28 19:02:26', 'N', 'N'),
(8, '2025-08-28 19:09:18', 4, 64, 2, 'James Mathews', 'Chief Executive Officer', 1, 'james@example.vo.ke', '+254722540169', 11, 4, '2025-08-28 19:09:18', 'N', 'N'),
(9, '2025-08-28 19:13:50', 4, 64, 4, 'Jules  Marcos', 'Operations Director', 2, 'marcos@example.com', '+345245435', 11, 4, '2025-08-28 19:13:50', 'N', 'N'),
(10, '2025-10-11 12:34:51', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:34:51', 'N', 'N'),
(11, '2025-10-11 12:35:14', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:35:14', 'N', 'N'),
(12, '2025-10-11 12:35:32', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:35:32', 'N', 'N'),
(13, '2025-10-11 12:36:03', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:36:03', 'N', 'N'),
(14, '2025-10-11 12:36:09', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:36:09', 'N', 'N'),
(15, '2025-10-11 12:36:22', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:36:22', 'N', 'N'),
(16, '2025-10-11 12:36:47', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:36:47', 'N', 'N'),
(17, '2025-10-11 12:37:50', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:37:50', 'N', 'N'),
(18, '2025-10-11 12:40:13', 0, 52, 2, 'Kelvin Simiyu', 'Head of projects', NULL, 'kelvin@example.com', '+23434534232', NULL, 4, '2025-10-11 12:40:13', 'N', 'N'),
(19, '2025-10-11 12:41:40', 0, 53, 2, 'Mauncho Nyandega', 'Head of training', NULL, 'mauncho@example.com', '+2547589658', NULL, 4, '2025-10-11 12:41:40', 'N', 'N'),
(20, '2025-10-11 13:04:47', 0, 53, 2, 'Mauncho Nyandega', 'Head of training', NULL, 'mauncho@example.com', '+2547589658', NULL, 4, '2025-10-11 13:04:47', 'N', 'N'),
(21, '2025-10-11 13:05:36', 0, 54, 2, 'Mauncho Example', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:05:36', 'N', 'N'),
(22, '2025-10-11 13:06:04', 0, 54, 2, 'Mauncho Example', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:06:04', 'N', 'N'),
(23, '2025-10-11 13:09:51', 0, 54, 2, 'Mauncho Example', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:09:51', 'N', 'N'),
(24, '2025-10-11 13:11:54', 0, 55, 2, 'Felix Mauncho', 'Head of projects', NULL, 'info@sbsl.co.ke', '0722540169', NULL, 4, '2025-10-11 13:11:54', 'N', 'N'),
(25, '2025-10-11 13:15:49', 0, 56, 3, 'Mauncho Doe', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:15:49', 'N', 'N'),
(26, '2025-10-11 13:17:01', 0, 56, 3, 'Mauncho Doe', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:17:01', 'N', 'N'),
(27, '2025-10-11 13:21:16', 0, 56, 3, 'Mauncho Doe', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:21:16', 'N', 'N'),
(28, '2025-10-11 13:21:43', 0, 56, 3, 'Mauncho Doe', 'Head of projects', NULL, 'info@webfortekenya.com', '722540168', NULL, 4, '2025-10-11 13:21:43', 'N', 'N'),
(29, '2025-10-11 13:22:14', 0, 57, 1, 'John Doe', 'Head of projects', NULL, 'johndoe@example.com', '0722540169', NULL, 4, '2025-10-11 13:22:14', 'N', 'N'),
(30, '2025-10-11 13:26:45', 0, 57, 1, 'John Doe', 'Head of projects', NULL, 'johndoe@example.com', '0722540169', NULL, 4, '2025-10-11 13:26:45', 'N', 'N'),
(31, '2025-10-11 13:27:22', 0, 26, 4, 'James Mathews', 'Head of projects', NULL, 'james@example.vo.ke', '0722540169', NULL, 4, '2025-10-11 13:27:22', 'N', 'N'),
(32, '2025-10-11 13:32:56', 0, 58, 2, 'John Doe', 'Head of projects', NULL, 'johndoe@example.com', '0722540169', NULL, 4, '2025-10-11 13:32:56', 'N', 'N'),
(33, '2025-10-13 10:09:59', 0, 59, NULL, 'Mauncho', 'Procurement', NULL, 'info@webfortekenya.com', '+60722540168', NULL, 4, '2025-10-13 10:09:59', 'N', 'N'),
(34, '2025-10-13 10:56:43', 0, 69, 2, 'John Doe', 'Head of projects', NULL, 'johndoe@example.com', '0722540169', NULL, 4, '2025-10-13 10:56:43', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_documents`
--

DROP TABLE IF EXISTS `tija_client_documents`;
CREATE TABLE `tija_client_documents` (
  `clientDocumentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `clientDocumentName` varchar(255) NOT NULL,
  `clientDocumentDescription` text NOT NULL,
  `documentTypeID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `clientDocumentFile` varchar(256) NOT NULL,
  `documentFileName` varchar(255) NOT NULL,
  `documentFileSize` int(11) NOT NULL,
  `documentFileType` varchar(255) NOT NULL,
  `documentFilePath` varchar(255) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_client_documents`
--

INSERT INTO `tija_client_documents` (`clientDocumentID`, `DateAdded`, `clientDocumentName`, `clientDocumentDescription`, `documentTypeID`, `clientID`, `clientDocumentFile`, `documentFileName`, `documentFileSize`, `documentFileType`, `documentFilePath`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 19:17:55', 'Registration Certificate', 'Company Registration Document For SBSL', 1, 2, 'client_documents/1752596275_Westlands._SKM__Revision_8.pdf', '1752596275_Westlands._SKM__Revision_8.pdf', 297556, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-15 19:17:54', 2, 'N', 'N'),
(2, '2025-07-18 09:10:07', 'Registration Certificate', 'rgedjfkgkgjj,', 2, 3, 'client_documents/1752819007_Policy_on_Minimum_Information_Security_Requirements_for_Vendors_28.04.2025.pdf', '1752819007_Policy_on_Minimum_Information_Security_Requirements_for_Vendors_28.04.2025.pdf', 227338, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-18 09:10:07', 4, 'N', 'N'),
(3, '2025-07-23 03:40:32', 'Certificate of incorporation', 'Certificate of incorporation', 3, 7, 'client_documents/1753256432_Certificate_of_Incorporation.pdf', '1753256432_Certificate_of_Incorporation.pdf', 282515, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-23 10:40:32', 21, 'N', 'N'),
(4, '2025-07-23 03:41:14', 'CR12', 'CR12', 3, 7, 'client_documents/1753256474_Heaves_CR12.pdf', '1753256474_Heaves_CR12.pdf', 48713, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-23 10:41:14', 21, 'N', 'N'),
(5, '2025-07-23 03:42:27', 'Co Profile', 'Profile', 3, 7, 'client_documents/1753256547_Heaves_International_profile.pdf', '1753256547_Heaves_International_profile.pdf', 1962109, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-23 10:42:27', 21, 'N', 'N'),
(6, '2025-07-23 03:43:14', 'PIN cert', 'PIN certificate', 3, 7, 'client_documents/1753256594_HEAVES_PIN.pdf', '1753256594_HEAVES_PIN.pdf', 17289, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-23 10:43:14', 21, 'N', 'N'),
(7, '2025-07-24 06:10:08', 'KRA PIN', 'KRA PIN CERTIFICATE', 1, 16, 'client_documents/1753351808_03_-_PIN_Certificate_-_Dell.pdf', '1753351808_03_-_PIN_Certificate_-_Dell.pdf', 17293, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-24 13:10:08', 22, 'N', 'N'),
(8, '2025-07-24 06:11:36', 'Certificate of Incorporation', 'Certificate of Incorporation', 3, 16, 'client_documents/1753351896_02_-_Certificate_of_Incorporation.pdf', '1753351896_02_-_Certificate_of_Incorporation.pdf', 52935, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-24 13:11:36', 22, 'N', 'N'),
(9, '2025-07-24 06:12:55', 'CR12', 'CR12', 3, 16, 'client_documents/1753351975_06_-_CR12_Certificate.pdf', '1753351975_06_-_CR12_Certificate.pdf', 94879, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-07-24 13:12:55', 22, 'N', 'N'),
(10, '2025-08-13 02:34:30', 'Pin certificate', 'Pin Certificate', 1, 32, 'client_documents/1755066870_KRA_Pin_Certificate_Jireh_Corporate__8_Aug_24_.pdf', '1755066870_KRA_Pin_Certificate_Jireh_Corporate__8_Aug_24_.pdf', 16907, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-13 09:34:30', 13, 'N', 'N'),
(11, '2025-08-25 01:14:44', 'CR 12', 'CR 12', 1, 60, 'client_documents/1756098884_BIAF_Company_Search_CR12_November_9_2024.pdf', '1756098884_BIAF_Company_Search_CR12_November_9_2024.pdf', 92755, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-25 08:14:44', 36, 'N', 'N'),
(12, '2025-08-26 02:05:19', 'Engagement letter', 'it states Scope of Services, Fee arrangements, Standard terms', 4, 39, 'client_documents/1756188319_1.Africon_Consulting_EL_Signed.pdf', '1756188319_1.Africon_Consulting_EL_Signed.pdf', 583712, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-26 09:05:19', 25, 'N', 'N'),
(13, '2025-08-26 02:17:25', 'KRA PIN', 'KRA PIN', 3, 39, 'client_documents/1756189045_Africon_KRA_PIN.pdf', '1756189045_Africon_KRA_PIN.pdf', 17126, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-26 09:17:25', 25, 'N', 'N'),
(14, '2025-08-26 02:28:32', 'Africon Certificate of Incorporation', 'Certificate of incoporation', 3, 39, 'client_documents/1756189712_Africon_Kenya_Certificate_of_Incorporation.pdf', '1756189712_Africon_Kenya_Certificate_of_Incorporation.pdf', 238871, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-26 09:28:32', 25, 'N', 'N'),
(15, '2025-08-26 02:31:27', 'CR12', 'Africon CR12', 3, 39, 'client_documents/1756189887_Africon_Kenya_CR12.pdf', '1756189887_Africon_Kenya_CR12.pdf', 77883, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-26 09:31:27', 25, 'N', 'N'),
(16, '2025-08-26 03:17:56', 'KRA PIN Certificate', 'nhjkhgjkgl', 1, 64, 'client_documents/1756192676_tax_com_requirements.docx', '1756192676_tax_com_requirements.docx', 5154690, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-26 10:17:56', 4, 'N', 'N'),
(17, '2025-08-26 04:49:42', 'Engagement letter', 'Alternative EL', 4, 22, 'client_documents/1756198182_10.Alternative_Ad_Agency_Limited_-_Addendum_EL_For_Provision_of_BKPS_VAT_WHT_Statutories_and_Staff_secondment.pdf', '1756198182_10.Alternative_Ad_Agency_Limited_-_Addendum_EL_For_Provision_of_BKPS_VAT_WHT_Statutories_and_Staff_secondment.pdf', 120547, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-08-26 11:49:42', 25, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_contact_relationships`
--

DROP TABLE IF EXISTS `tija_contact_relationships`;
CREATE TABLE `tija_contact_relationships` (
  `relationshipID` int(11) NOT NULL,
  `relationshipName` varchar(100) NOT NULL,
  `relationshipCode` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `sortOrder` int(11) DEFAULT 0,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_contact_relationships`
--

INSERT INTO `tija_contact_relationships` (`relationshipID`, `relationshipName`, `relationshipCode`, `description`, `sortOrder`, `Suspended`) VALUES
(1, 'Spouse', 'SPOUSE', NULL, 1, 'N'),
(2, 'Parent', 'PARENT', NULL, 2, 'N'),
(3, 'Sibling', 'SIBLING', NULL, 3, 'N'),
(4, 'Child', 'CHILD', NULL, 4, 'N'),
(5, 'Partner', 'PARTNER', NULL, 5, 'N'),
(6, 'Friend', 'FRIEND', NULL, 6, 'N'),
(7, 'Colleague', 'COLLEAGUE', NULL, 7, 'N'),
(8, 'Guardian', 'GUARDIAN', NULL, 8, 'N'),
(9, 'Relative', 'RELATIVE', NULL, 9, 'N'),
(10, 'Other', 'OTHER', NULL, 10, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entities`
--

DROP TABLE IF EXISTS `tija_entities`;
CREATE TABLE `tija_entities` (
  `entityID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `entityName` varchar(255) NOT NULL,
  `entityDescription` text DEFAULT NULL,
  `entityTypeID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityParentID` int(11) NOT NULL,
  `industrySectorID` int(11) NOT NULL,
  `registrationNumber` varchar(60) NOT NULL,
  `entityPIN` varchar(60) NOT NULL,
  `entityCity` varchar(120) NOT NULL,
  `entityCountry` varchar(180) NOT NULL,
  `entityPhoneNumber` int(11) NOT NULL,
  `entityEmail` varchar(256) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_entities`
--

INSERT INTO `tija_entities` (`entityID`, `DateAdded`, `entityName`, `entityDescription`, `entityTypeID`, `orgDataID`, `entityParentID`, `industrySectorID`, `registrationNumber`, `entityPIN`, `entityCity`, `entityCountry`, `entityPhoneNumber`, `entityEmail`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 17:10:47', 'SKM Africa LLP', 'Demo Entity', 1, 1, 0, 42, 'LLP -4EL1PY', 'P051661058D', 'Nairobi', '25', 2147483647, 'info@skm.co.ke', '2025-07-15 17:10:47', 1, 'N', 'N'),
(2, '2025-10-25 11:43:08', 'Nirvana Credit Limited', 'Demo Company Subcidiary', 1, 1, 1, 11, '234R65723', 'P051661043D', 'Nairobi', '25', 700039147, 'info@demoexample.co.ke', '2025-10-25 11:43:08', 1, 'N', 'N'),
(3, '2025-10-25 11:21:32', 'SBSL Kenya', NULL, 1, 4, 0, 0, '34567hdyjsoiksvdsdf', '', 'Nairobi', '25', 2147483647, 'felixmauncho@gmail.com', '2025-10-25 14:21:32', 1, 'N', 'N'),
(4, '2025-10-25 11:21:32', 'SBSL Uganda', NULL, 1, 4, 0, 0, '34567hdyvjsoikdfsdsdf', '', 'Nairobi', '25', 2147483647, 'felixmauncho@gmail.com', '2025-10-25 14:21:32', 1, 'N', 'N'),
(5, '2025-10-25 13:16:36', 'Webforte Dev', NULL, 1, 5, 0, 0, '34567hdsyjsoik', '', 'Nairobi', '25', 2147483647, 'info@webfortekenya.com', '2025-10-25 16:16:36', 1, 'N', 'N'),
(6, '2025-10-25 13:18:37', 'Webforte Dev', NULL, 1, 6, 0, 0, '3456k,7hdsyjsoik', '', 'Nairobi', '25', 2147483647, 'info@webfortekenya.com', '2025-10-25 16:18:37', 1, 'N', 'N'),
(7, '2025-10-25 13:19:42', 'Webforte Dev', NULL, 1, 7, 0, 0, '3456k,7hdGsyjsoik', '', 'Nairobi', '25', 2147483647, 'info@webfortekenya.com', '2025-10-25 16:19:42', 1, 'N', 'N'),
(8, '2025-10-25 13:23:30', 'Webforte Dev', NULL, 1, 8, 0, 0, '3456k,7hdGsygjsoik', '', 'Nairobi', '25', 2147483647, 'info@webfortekenya.com', '2025-10-25 16:23:30', 0, 'N', 'N'),
(9, '2025-10-25 13:24:31', 'Webforte Dev', NULL, 1, 9, 0, 0, '3456k,7hdGsagjsoik', '', 'Nairobi', '25', 2147483647, 'info@webfortekenya.com', '2025-10-25 16:24:31', 0, 'N', 'N'),
(10, '2025-10-25 13:25:53', 'Webforte Dev', 'The parent entity for webforte group', 1, 10, 0, 24, '3456k,s7hdGsagjsoik', 'P051661058D', 'Nairobi', '25', 2147483647, 'info@webfortekenya.com', '2025-10-25 16:25:53', 0, 'N', 'N'),
(11, '2025-10-26 19:34:07', 'SBSL Kenya', 'The Kenya Company for SBSL', 1, 3, 0, 24, 'LLP -4EL1PY-SBSL', 'P051661058D-SBSL', 'Nairobi', '25', 722540169, 'info@sbsl.co.ke', '2025-10-26 19:34:07', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_organisation_data`
--

DROP TABLE IF EXISTS `tija_organisation_data`;
CREATE TABLE `tija_organisation_data` (
  `orgDataID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgLogo` varchar(256) DEFAULT NULL,
  `orgName` varchar(255) NOT NULL,
  `industrySectorID` int(11) DEFAULT NULL,
  `numberOfEmployees` int(11) NOT NULL,
  `registrationNumber` varchar(30) NOT NULL,
  `orgPIN` varchar(80) NOT NULL,
  `costCenterEnabled` enum('Y','N') NOT NULL DEFAULT 'N',
  `orgAddress` varchar(30) NOT NULL,
  `orgPostalCode` varchar(30) DEFAULT NULL,
  `orgCity` varchar(128) NOT NULL,
  `countryID` int(11) NOT NULL,
  `orgPhoneNumber1` varchar(30) NOT NULL,
  `orgPhoneNUmber2` varchar(30) DEFAULT NULL,
  `orgEmail` varchar(255) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_organisation_data`
--

INSERT INTO `tija_organisation_data` (`orgDataID`, `DateAdded`, `orgLogo`, `orgName`, `industrySectorID`, `numberOfEmployees`, `registrationNumber`, `orgPIN`, `costCenterEnabled`, `orgAddress`, `orgPostalCode`, `orgCity`, `countryID`, `orgPhoneNumber1`, `orgPhoneNUmber2`, `orgEmail`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 13:52:03', '', 'SKM Africa LLP', 42, 44, 'LLP -4EL1PY', 'P051661058D', 'Y', '22222', '00100', 'Nairobi', 25, '722540168', '+254700039147', 'support@example.co.ke', '2025-11-10 09:03:25', 1, 'N', 'N'),
(2, '2025-10-25 11:18:05', NULL, 'Strategic Business Solutions Limited', 12, 23, '234R65723sd', 'P051661058Dsd', 'Y', 'Rainbow Towers\r\nP. O. BOX 2021', '00100', 'Nairobi', 25, '+254722540169', NULL, 'felixmauncho@gmail.com', '2025-10-25 14:18:05', 1, 'N', 'N'),
(3, '2025-10-25 11:20:02', NULL, 'Strategic Business Solutions Limited', 12, 23, '234R65723sddfsds', 'P051661058Dsdsds', 'Y', 'Rainbow Towers\r\nP. O. BOX 2021', '00100', 'Nairobi', 25, '+254722540169', NULL, 'felixmauncho@gmail.com', '2025-10-25 14:20:02', 1, 'N', 'N'),
(10, '2025-10-25 13:25:53', NULL, 'WQebforte Kenya Limited', 24, 23, 'LLPs -4EL1PsgKPYds', 'P05166105g6s8N7P8aK', 'Y', 'Rainbow Towers\r\nP. O. BOX 2021', '00100', 'Nairobi', 25, '+254722540169', NULL, 'info@webfortekenya.com', '2025-10-25 16:25:53', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_charts`
--

DROP TABLE IF EXISTS `tija_org_charts`;
CREATE TABLE `tija_org_charts` (
  `orgChartID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgChartName` varchar(256) NOT NULL,
  `orgChartDescription` int(11) NOT NULL COMMENT 'Description of the organizational chart',
  `chartType` varchar(50) DEFAULT 'hierarchical' COMMENT 'Type: hierarchical, matrix, flat, divisional',
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `effectiveDate` date DEFAULT NULL COMMENT 'Date when this org chart becomes effective',
  `isCurrent` enum('Y','N') DEFAULT 'N' COMMENT 'Is this the current active organizational chart'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_org_charts`
--

INSERT INTO `tija_org_charts` (`orgChartID`, `DateAdded`, `orgChartName`, `orgChartDescription`, `chartType`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`, `effectiveDate`, `isCurrent`) VALUES
(1, '2025-07-15 18:56:38', 'SKM Africa LLP Kenya', 0, 'hierarchical', 1, 1, '2025-07-15 18:56:38', 2, 'N', 'N', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_chart_position_assignments`
--

DROP TABLE IF EXISTS `tija_org_chart_position_assignments`;
CREATE TABLE `tija_org_chart_position_assignments` (
  `positionAssignmentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `orgChartID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `positionID` int(11) NOT NULL,
  `positionTypeID` int(11) DEFAULT NULL,
  `positionTitle` varchar(255) NOT NULL,
  `positionDescription` text DEFAULT NULL,
  `positionParentID` int(11) NOT NULL,
  `positionOrder` int(11) DEFAULT NULL,
  `positionLevel` varchar(120) DEFAULT NULL,
  `positionCode` varchar(120) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_org_chart_position_assignments`
--

INSERT INTO `tija_org_chart_position_assignments` (`positionAssignmentID`, `DateAdded`, `orgDataID`, `orgChartID`, `entityID`, `positionID`, `positionTypeID`, `positionTitle`, `positionDescription`, `positionParentID`, `positionOrder`, `positionLevel`, `positionCode`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 18:56:51', 1, 1, 1, 55, NULL, 'Partner', 'A partner in a consulting firm is a senior leader responsible for driving the firm\'s growth and success. They develop and execute business strategies, manage client relationships, and oversee the delivery of high-quality consulting projects. Partners are instrumental in generating new business opportunities and expanding existing client relationships. They lead and mentor consulting staff, manage project finances, and represent the firm at industry events, contributing to its thought leadership and reputation.', 0, NULL, NULL, NULL, '2025-07-15 18:56:51', 2, 'N', 'N'),
(2, '2025-07-15 18:57:05', 1, 1, 1, 56, NULL, 'Director', 'A director in a consulting firm plays a pivotal role in shaping the firm\'s strategic direction and ensuring the successful delivery of client projects. They manage client relationships, oversee multiple projects, and ensure that consulting teams deliver high-quality solutions. Their responsibilities also include identifying new business opportunities, mentoring staff, and managing project budgets. Directors represent the firm at industry events, contributing to its reputation and thought leadership.', 1, NULL, NULL, NULL, '2025-07-15 18:57:05', 2, 'N', 'N'),
(3, '2025-07-15 18:57:34', 1, 1, 1, 64, NULL, 'Senior Manager', 'A senior manager in a consulting firm plays a key role in overseeing complex projects and ensuring their successful delivery. They manage project teams, coordinate tasks, and maintain high-quality standards. Senior managers build and maintain strong client relationships, addressing their needs and concerns promptly. Their responsibilities include identifying opportunities for business growth, mentoring junior staff, and managing project budgets. Senior managers represent the firm in client meetings and industry events, contributing to its professional reputation and thought leadership.', 2, NULL, NULL, NULL, '2025-07-15 18:57:34', 2, 'N', 'N'),
(4, '2025-07-15 18:57:44', 1, 1, 1, 54, NULL, 'Manager', 'A manager in a consulting firm is responsible for overseeing day-to-day operations and ensuring the successful execution of client projects. They manage project teams, coordinate tasks, and ensure that deliverables meet the firm\'s standards of quality. Managers also maintain client relationships, addressing their needs and concerns promptly. Their role includes identifying opportunities for process improvements, mentoring junior staff, and managing project budgets. Managers often represent the firm in client meetings and industry events, contributing to its professional reputation', 3, NULL, NULL, NULL, '2025-09-19 14:45:25', 4, 'N', 'N'),
(5, '2025-07-15 18:58:06', 1, 1, 1, 57, NULL, 'Assistant Manager', 'An assistant manager in a consulting firm supports the manager in overseeing daily operations and ensuring the smooth execution of client projects. They assist in coordinating project tasks, managing teams, and maintaining quality standards. Assistant managers help address client needs and concerns, contributing to strong client relationships. Their role includes supporting process improvements, mentoring junior staff, and assisting with project budgeting. They often participate in client meetings and industry events, representing the firm and contributing to its professional reputation', 4, NULL, NULL, NULL, '2025-07-15 18:58:06', 2, 'N', 'N'),
(6, '2025-07-15 18:58:37', 1, 1, 1, 58, NULL, 'Senior Associate', 'Art directors typically oversee the work of other designers and artists who produce images for television, film, live performances, advertisements, or video games. They determine the overall style in which a message is communicated visually to its audience.', 5, NULL, NULL, NULL, '2025-07-15 18:58:37', 2, 'N', 'N'),
(7, '2025-07-15 18:59:38', 1, 1, 1, 59, NULL, 'Associate', 'An associate in a consulting firm is responsible for supporting project teams in delivering high-quality consulting services. They conduct research, analyze data, and assist in developing solutions tailored to client needs. Associates work closely with senior team members to ensure that project deliverables meet the firm\'s standards. They also help maintain client relationships by addressing client inquiries and providing timely updates. Associates contribute to business development efforts by identifying potential opportunities and supporting proposal development. Their role involves continuous learning and professional development to enhance their consulting skills.', 6, NULL, NULL, NULL, '2025-07-15 18:59:38', 2, 'N', 'N'),
(8, '2025-07-15 18:59:53', 1, 1, 1, 61, NULL, 'Intern', 'An intern in a consulting firm supports project teams by conducting research, analyzing data, and assisting in the development of client solutions. They work closely with associates and senior team members to learn about consulting practices and contribute to project deliverables. Interns help with administrative tasks, prepare reports, and participate in client meetings. Their role involves gaining practical experience and developing skills that are essential for a career in consulting.', 7, NULL, NULL, NULL, '2025-07-15 18:59:53', 2, 'N', 'N'),
(9, '2025-09-19 14:17:46', 1, 1, 1, 54, NULL, 'Manager', 'A manager in a consulting firm is responsible for overseeing day-to-day operations and ensuring the successful execution of client projects. They manage project teams, coordinate tasks, and ensure that deliverables meet the firm\'s standards of quality. Managers also maintain client relationships, addressing their needs and concerns promptly. Their role includes identifying opportunities for process improvements, mentoring junior staff, and managing project budgets. Managers often represent the firm in client meetings and industry events, contributing to its professional reputation', 18, NULL, NULL, NULL, '2025-09-19 14:17:46', 4, 'N', 'N'),
(10, '2025-09-19 14:18:16', 1, 1, 1, 57, NULL, 'Assistant Manager', 'An assistant manager in a consulting firm supports the manager in overseeing daily operations and ensuring the smooth execution of client projects. They assist in coordinating project tasks, managing teams, and maintaining quality standards. Assistant managers help address client needs and concerns, contributing to strong client relationships. Their role includes supporting process improvements, mentoring junior staff, and assisting with project budgeting. They often participate in client meetings and industry events, representing the firm and contributing to its professional reputation', 12, NULL, NULL, NULL, '2025-09-19 14:18:16', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_projects`
--

DROP TABLE IF EXISTS `tija_projects`;
CREATE TABLE `tija_projects` (
  `projectID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `DateLastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `projectCode` varchar(30) NOT NULL,
  `projectName` varchar(255) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `caseID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `projectStart` date NOT NULL,
  `projectClose` date DEFAULT NULL,
  `projectDeadline` date DEFAULT NULL,
  `projectOwnerID` int(11) DEFAULT NULL,
  `projectManagersIDs` varchar(256) DEFAULT NULL,
  `billable` enum('Y','N') DEFAULT 'N',
  `billingRateID` int(11) DEFAULT NULL,
  `billableRateValue` decimal(10,2) NOT NULL DEFAULT 4000.00,
  `roundingoff` varchar(255) DEFAULT NULL,
  `roundingInterval` int(11) DEFAULT NULL,
  `businessUnitID` int(11) NOT NULL,
  `projectValue` decimal(10,2) DEFAULT NULL,
  `approval` enum('Y','N') NOT NULL DEFAULT 'N',
  `projectStatus` enum('open','closed','inactive') NOT NULL DEFAULT 'open',
  `salesCaseID` int(11) DEFAULT NULL,
  `projectTypeID` int(11) NOT NULL DEFAULT 1,
  `orderDate` date DEFAULT NULL,
  `projectType` enum('inhouse','recurrent','client') NOT NULL DEFAULT 'client',
  `allocatedWorkHours` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_projects`
--

INSERT INTO `tija_projects` (`projectID`, `DateAdded`, `DateLastUpdated`, `projectCode`, `projectName`, `orgDataID`, `caseID`, `entityID`, `clientID`, `projectStart`, `projectClose`, `projectDeadline`, `projectOwnerID`, `projectManagersIDs`, `billable`, `billingRateID`, `billableRateValue`, `roundingoff`, `roundingInterval`, `businessUnitID`, `projectValue`, `approval`, `projectStatus`, `salesCaseID`, `projectTypeID`, `orderDate`, `projectType`, `allocatedWorkHours`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-16 14:37:20', '2025-07-16 14:37:20', '_DUK6L', 'Regea Implementation', 1, 0, 1, 2, '2025-07-16', '2025-09-25', NULL, 4, NULL, 'N', 2, 4000.00, 'round_up', 15, 2, 2000000.00, 'N', '', NULL, 1, '2025-07-16', 'client', NULL, '2025-07-24 12:06:04', 4, 'N', 'N'),
(2, '2025-07-16 16:00:38', '2025-07-16 16:00:38', '_YA23P', 'Regea Implementation', 1, 0, 1, 1, '2025-07-16', '2025-08-13', NULL, 3, NULL, 'N', 1, 4000.00, 'no_rounding', NULL, 2, 4000000.00, 'N', '', NULL, 2, '2025-07-16', 'client', NULL, '2025-07-16 16:00:38', 3, 'N', 'N'),
(3, '2025-07-18 09:29:03', '2025-07-18 09:29:03', '_QOMKZ', 'Comparative Worth Index Implementation', 1, 0, 1, 4, '2025-07-18', '2025-08-31', NULL, 4, NULL, 'N', 2, 4000.00, 'round_up', 30, 3, 2000000.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-07-18 09:29:03', 4, 'N', 'N'),
(4, '2025-07-23 10:02:38', '2025-07-23 03:02:38', '_6YRRE', 'Triple Jump Kenya Limited', 1, 0, 1, 1, '2025-01-01', '2025-12-31', NULL, 15, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 3, 60000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-08-06 16:48:21', 24, 'N', 'N'),
(5, '2025-07-23 10:04:47', '2025-07-23 03:04:47', '_TW464', 'External audit', 1, 0, 1, 0, '2025-07-23', '2025-07-24', NULL, 18, NULL, 'N', 2, 4000.00, 'round_up', NULL, 0, 55000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 10:04:47', 18, 'N', 'N'),
(6, '2025-07-23 10:07:49', '2025-07-23 03:07:49', '_IL0CJ', 'Insurance Audit', 1, 0, 1, 0, '2025-07-23', '2025-08-06', NULL, 7, NULL, 'N', 3, 4000.00, 'round_up', 5, 0, 1.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 10:07:49', 23, 'N', 'N'),
(7, '2025-07-23 10:21:59', '2025-07-23 03:21:59', '_UFT0E', 'External Audit', 1, 0, 1, 5, '2025-08-01', '2025-08-29', NULL, 7, NULL, 'N', 3, 4000.00, 'round_up', 5, 6, 200.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 10:21:59', 23, 'N', 'N'),
(8, '2025-07-23 10:28:36', '2025-07-23 03:28:36', '_11QFK', 'Income Tax Exemption', 1, 0, 1, 0, '2024-11-01', '2025-07-31', NULL, 8, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 1, 500000.00, 'N', '', NULL, 1, '2024-10-01', 'client', NULL, '2025-07-23 10:28:36', 22, 'N', 'N'),
(9, '2025-07-23 10:30:09', '2025-07-23 03:30:09', '_ZPFVA', 'External Audit', 1, 0, 1, 5, '2025-08-01', '2025-08-29', NULL, 23, NULL, 'N', 2, 4000.00, 'round_up', 5, 5, 200.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 10:30:09', 23, 'N', 'N'),
(10, '2025-07-23 10:31:46', '2025-07-23 03:31:46', '_1WU7Q', 'Outsourced Accounting, bookkeeping and payroll services', 1, 0, 1, 0, '2022-01-01', '2025-12-31', NULL, 8, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 600000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 10:31:46', 15, 'N', 'N'),
(11, '2025-07-23 10:36:33', '2025-07-23 03:36:33', '_370SA', 'Insurance Audit', 1, 0, 1, 0, '2025-07-23', '2025-08-06', NULL, 7, NULL, 'N', 2, 4000.00, 'round_up', 5, 4, 200.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 10:54:44', 23, 'N', 'N'),
(12, '2025-07-23 10:37:11', '2025-07-23 03:37:11', '_UPGQ7', 'ACCOUNTING AND PAYROLL', 1, 0, 1, 12, '2025-01-01', '2025-12-31', NULL, 8, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 600000.00, 'N', '', NULL, 1, '2025-07-22', 'client', NULL, '2025-07-23 12:30:36', 15, 'N', 'N'),
(13, '2025-07-23 10:47:48', '2025-07-23 03:47:48', '_3C67D', 'Income Tax Exemption', 1, 0, 1, 10, '2025-06-01', '2025-07-21', NULL, 22, NULL, 'N', 2, 4000.00, 'round_up', NULL, 1, 500000.00, 'N', '', NULL, 1, '2025-07-22', 'client', NULL, '2025-07-23 11:16:23', 22, 'N', 'N'),
(14, '2025-07-23 11:03:22', '2025-07-23 04:03:22', '_42LT6', 'External Audit', 1, 0, 1, 0, '2025-08-01', '2025-08-28', NULL, 18, NULL, 'N', 3, 4000.00, 'round_up', 5, 5, 200.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 11:03:22', 23, 'N', 'N'),
(15, '2025-07-23 11:08:41', '2025-07-23 04:08:41', '_59ONZ', 'External Audit', 1, 0, 1, 0, '2025-07-23', '2025-07-30', NULL, 9, NULL, 'N', 2, 4000.00, 'round_up', 5, 5, 200.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 11:08:41', 23, 'N', 'N'),
(16, '2025-07-23 11:18:36', '2025-07-23 04:18:36', '_WM732', 'VAT compliance and Payroll services', 1, 0, 1, 5, '2025-01-01', '2025-12-31', NULL, 15, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 3, 210000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-24 12:43:15', 15, 'N', 'N'),
(17, '2025-07-23 11:39:39', '2025-07-23 04:39:39', '_SPC33', 'External audit', 1, 0, 1, 12, '2025-07-01', '2025-08-02', NULL, 23, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 4, 55000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 11:39:39', 18, 'N', 'N'),
(18, '2025-07-23 11:40:05', '2025-07-23 04:40:05', '_Z8E2F', 'Maven Luxury Limited', 1, 0, 1, 11, '2025-07-23', '2025-07-31', NULL, 15, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 3, 500000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-23 11:40:05', 15, 'N', 'N'),
(19, '2025-07-24 12:33:04', '2025-07-24 05:33:04', '_UEVZW', 'WHT and VAT compliance services', 1, 0, 1, 15, '2025-01-01', '2025-12-31', NULL, 15, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 3, 720000.00, 'N', '', NULL, 1, '2025-07-24', 'client', NULL, '2025-07-24 12:33:04', 15, 'N', 'N'),
(20, '2025-07-25 11:06:57', '2025-07-25 04:06:57', '_9RVXB', 'Income Tax Rebate', 1, 0, 1, 16, '2025-07-23', '2025-09-30', NULL, 22, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 1, 1000000.00, 'N', '', NULL, 1, '2025-07-23', 'client', NULL, '2025-07-25 11:06:57', 22, 'N', 'N'),
(21, '2025-07-25 12:15:55', '2025-07-25 05:15:55', '_OOO3G', 'Income Tax Rebate', 1, 0, 1, 19, '2025-07-21', '2025-08-31', NULL, 22, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 1, 2000000.00, 'N', '', NULL, 1, '2025-07-25', 'client', NULL, '2025-07-25 12:15:55', 22, 'N', 'N'),
(22, '2025-08-06 11:29:08', '2025-08-06 04:29:08', '_SSFGK', 'VAT WHT and payroll services', 1, 0, 1, 23, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 17500.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 11:55:37', 25, 'N', 'N'),
(23, '2025-08-06 11:53:18', '2025-08-06 04:53:18', '_5G7M7', 'TEST', 1, 0, 1, 2, '2025-08-06', '2025-12-31', NULL, 25, NULL, 'N', 2, 4000.00, 'round_to_the_nearest', 10, 4, 17.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:01:00', 25, 'N', 'N'),
(24, '2025-08-06 12:02:12', '2025-08-06 05:02:12', '_MGT2T', 'Nil Filing Vat Paye', 1, 0, 1, 21, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 5000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:02:12', 25, 'N', 'N'),
(25, '2025-08-06 12:09:18', '2025-08-06 05:09:18', '_U4WBI', 'Shadow payroll services', 1, 0, 1, 24, '2025-08-01', '2025-12-31', NULL, 15, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 22800.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:09:18', 25, 'N', 'N'),
(26, '2025-08-06 12:18:08', '2025-08-06 05:18:08', '_0GXLN', 'Book keeping and payroll services', 1, 0, 1, 11, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 58000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:18:08', 25, 'N', 'N'),
(27, '2025-08-06 12:21:06', '2025-08-06 05:21:06', '_M1RX4', 'Book keeping and payroll services', 1, 0, 1, 20, '2025-08-01', '2025-12-31', NULL, 25, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 32000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:21:06', 25, 'N', 'N'),
(28, '2025-08-06 12:24:11', '2025-08-06 05:24:11', '_6IDDG', 'VAT, WHT and Payroll', 1, 0, 1, 15, '2025-08-01', '2025-12-31', NULL, 26, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 75000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:24:11', 25, 'N', 'N'),
(29, '2025-08-06 12:28:17', '2025-08-06 05:28:17', '_WCE39', 'Payroll services', 1, 0, 1, 27, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 45000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:28:17', 25, 'N', 'N'),
(30, '2025-08-06 12:51:10', '2025-08-06 05:51:10', '_IOP2V', 'Bookeeping', 1, 0, 1, 28, '2025-08-01', '2025-12-31', NULL, 15, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 60000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:51:10', 25, 'N', 'N'),
(31, '2025-08-06 12:54:04', '2025-08-06 05:54:04', '_SSJIT', 'Book keeping and payroll services', 1, 0, 1, 29, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 40000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:54:04', 25, 'N', 'N'),
(32, '2025-08-06 12:58:17', '2025-08-06 05:58:17', '_Z18QS', 'Bookkeeping And Payroll', 1, 0, 1, 30, '2025-08-01', '2025-12-31', NULL, 26, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 30000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 12:58:17', 25, 'N', 'N'),
(33, '2025-08-06 13:05:17', '2025-08-06 06:05:17', '_KQ23M', 'Payroll services', 1, 0, 1, 31, '2025-08-01', '2025-12-31', NULL, 13, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 14520.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 13:05:17', 25, 'N', 'N'),
(34, '2025-08-06 13:10:21', '2025-08-06 06:10:22', '_HP4VL', 'Payroll services', 1, 0, 1, 39, '2025-08-01', '2025-12-31', NULL, 26, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 29670.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:31:42', 25, 'N', 'N'),
(35, '2025-08-06 13:15:52', '2025-08-06 06:15:52', '_RA7YD', 'Bookeeping', 1, 0, 1, 32, '2025-08-01', '2025-12-31', NULL, 24, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 20000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 13:15:52', 25, 'N', 'N'),
(36, '2025-08-06 13:20:23', '2025-08-06 06:20:23', '_TDG4E', 'Finance Function', 1, 0, 1, 33, '2025-08-01', '2025-12-31', NULL, 13, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 244010.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 13:20:23', 25, 'N', 'N'),
(37, '2025-08-06 14:38:31', '2025-08-06 07:38:31', '_SWTB0', 'Expense Verification', 1, 0, 1, 34, '2025-08-01', '2025-12-31', NULL, 13, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 550000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 14:38:31', 25, 'N', 'N'),
(38, '2025-08-06 15:04:05', '2025-08-06 08:04:05', '_HLL1S', 'Hr Consultancy,Bookkeeping,Payroll & Wht', 1, 0, 1, 35, '2025-08-01', '2025-12-31', NULL, 13, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 35000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 15:04:05', 25, 'N', 'N'),
(39, '2025-08-06 15:17:46', '2025-08-06 08:17:46', '_7TP8A', 'Hr Consultancy,Bookkeeping,Payroll & Wht', 1, 0, 1, 36, '2025-08-01', '2025-12-31', NULL, 15, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 52000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 15:17:46', 25, 'N', 'N'),
(40, '2025-08-06 15:23:35', '2025-08-06 08:23:35', '_4RU9B', 'Payroll services', 1, 0, 1, 38, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 11600.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 15:23:35', 25, 'N', 'N'),
(41, '2025-08-06 15:31:44', '2025-08-06 08:31:44', '_X0W61', 'Bookkeeping And Payroll Services', 1, 0, 1, 40, '2025-08-01', '2025-12-31', NULL, 15, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 35000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 15:31:44', 25, 'N', 'N'),
(42, '2025-08-06 15:39:30', '2025-08-06 08:39:30', '_JWCG9', 'MRI', 1, 0, 1, 43, '2025-08-01', '2025-12-31', NULL, 27, NULL, 'N', 3, 4000.00, 'round_up', 10, 3, 13600.00, 'N', '', NULL, 1, '0000-00-00', 'client', NULL, '2025-09-20 15:16:16', 27, 'N', 'N'),
(43, '2025-08-06 15:53:32', '2025-08-06 08:53:32', '_UO39B', 'MCOS', 1, 0, 1, 45, '2025-08-01', '2025-12-31', NULL, 26, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 34800.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 15:53:32', 25, 'N', 'N'),
(44, '2025-08-06 15:57:19', '2025-08-06 08:57:19', '_VFUBY', 'MCOS', 1, 0, 1, 46, '2025-08-01', '2025-12-31', NULL, 13, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 23000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:30:09', 25, 'N', 'N'),
(45, '2025-08-06 16:01:31', '2025-08-06 09:01:31', '_97CT6', 'Payroll Services', 1, 0, 1, 47, '2025-08-01', '2025-12-31', NULL, 26, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 32000.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:29:33', 25, 'N', 'N'),
(46, '2025-08-06 16:08:06', '2025-08-06 09:08:06', '_09CNK', 'Bookkeeping', 1, 0, 1, 48, '2025-08-01', '2025-12-31', NULL, 24, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 38760.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:08:06', 25, 'N', 'N'),
(47, '2025-08-06 16:11:41', '2025-08-06 09:11:41', '_AA25M', 'Bookkeeping', 1, 0, 1, 50, '2025-08-01', '2025-12-31', NULL, 35, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 139200.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:11:41', 25, 'N', 'N'),
(48, '2025-08-06 16:22:06', '2025-08-06 09:22:06', '_EKX1F', 'Bookkeeping', 1, 0, 1, 51, '2025-08-01', '2025-12-31', NULL, 35, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 52200.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:22:06', 25, 'N', 'N'),
(49, '2025-08-06 16:25:56', '2025-08-06 09:25:56', '_CITDZ', 'Payroll services', 1, 0, 1, 49, '2025-08-01', '2025-12-31', NULL, 26, NULL, 'N', 3, 4000.00, 'no_rounding', NULL, 3, 149890.00, 'N', '', NULL, 1, '2025-08-06', 'client', NULL, '2025-08-06 16:28:46', 25, 'N', 'N'),
(50, '2025-08-27 15:43:44', '2025-08-27 08:43:44', '_UZSGK', '2025 Tax Computation', 1, 0, 1, 68, '2025-09-23', '2025-11-01', NULL, 8, NULL, 'N', 3, 4000.00, 'round_up', 60, 1, 700000.00, 'N', '', NULL, 1, '2025-08-27', 'client', NULL, '2025-09-23 17:00:01', 4, 'N', 'N'),
(51, '2025-08-28 11:33:31', '2025-08-28 04:33:31', '_0PUXT', 'Reconcilliation support', 1, 0, 1, 2, '2025-08-28', '2025-12-31', NULL, 4, NULL, 'N', 2, 4000.00, 'round_to_the_nearest', 60, 2, 2000000.00, 'N', '', NULL, 1, '2025-08-28', 'client', NULL, '2025-11-03 12:22:06', 4, 'N', 'N'),
(52, '2025-09-04 09:03:50', '2025-09-04 02:03:50', '_AWQTQ', 'Bridge International Academies Foundation', 1, 0, 1, 60, '2025-07-02', '2027-07-23', NULL, 36, NULL, 'N', 2, 4000.00, 'round_up', 5, 3, 0.00, 'N', '', NULL, 1, '2025-07-01', 'client', NULL, '2025-09-22 15:18:20', 4, 'N', 'N'),
(53, '2025-09-23 14:55:04', '2025-09-23 14:55:04', '_GCEPP', 'Tija PMS Automation tyikki', 1, 0, 1, 64, '2025-09-23', '2025-11-26', NULL, 4, NULL, 'N', 2, 4000.00, 'round_up', 10, 2, 2000000.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-09-23 14:55:04', 4, 'N', 'N'),
(54, '2025-10-13 15:31:19', '2025-10-13 15:31:19', '_DRIRF', 'Project Audit', 1, 0, 1, 6, '2025-10-13', '2025-11-28', NULL, 4, NULL, 'N', 2, 4000.00, 'no_rounding', NULL, 2, 4000000.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-10-13 15:31:19', 4, 'N', 'N'),
(55, '2025-10-29 07:57:36', '2025-10-29 07:57:36', '_OONXH', 'Tija Training Project', 1, 0, 1, 1, '2025-10-29', '2025-11-14', NULL, 4, NULL, 'N', 1, 4000.00, 'no_rounding', NULL, 2, 2000000.00, 'N', '', NULL, 2, '2025-10-29', 'client', NULL, '2025-10-29 07:57:36', 4, 'N', 'N'),
(56, '2025-11-04 12:43:15', '2025-11-04 12:43:15', 'SCH_VDWGA', 'School Management System', 1, 0, 1, 69, '2025-11-04', '2025-11-13', '2025-11-13', 49, NULL, 'N', 3, 4000.00, 'round_down', 1, 4, 34232.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-11-04 12:43:15', 49, 'N', 'N'),
(57, '2025-11-04 12:52:03', '2025-11-04 12:52:03', 'MAN_MEEZG', 'Management Tax system', 1, 0, 1, 64, '2025-11-04', '2025-11-28', '2025-11-28', 49, NULL, 'N', 2, 4000.00, 'round_to_the_nearest', 15, 2, 75467456.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-11-04 12:52:03', 49, 'N', 'N'),
(58, '2025-11-04 12:56:30', '2025-11-04 12:56:30', 'MAN_52KJ1', 'Management Tax system for SKK', 1, 0, 1, 39, '2025-11-04', '2025-11-28', '2025-11-28', 49, NULL, 'N', 2, 4000.00, 'round_up', 15, 2, 75467456.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-11-04 12:56:30', 49, 'N', 'N'),
(59, '2025-11-04 12:59:13', '2025-11-04 12:59:13', 'MAN_JN6YO', 'Management Tija System', 1, 0, 1, 22, '2025-11-04', '2025-11-21', '2025-11-21', 49, NULL, 'N', 2, 4000.00, 'round_to_the_nearest', 15, 2, 75467456.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-11-04 12:59:13', 49, 'N', 'N'),
(60, '2025-11-04 13:53:01', '2025-11-04 13:53:01', 'MAN_TTCL7', 'Management Tax system for SKK', 1, 0, 1, 69, '2025-11-04', '2025-11-28', '2025-11-28', 49, NULL, 'N', 3, 4000.00, 'round_to_the_nearest', 15, 3, 436532.00, 'N', '', NULL, 1, NULL, 'client', NULL, '2025-11-04 13:53:01', 49, 'N', 'N'),
(61, '2025-11-11 09:49:52', '2025-11-11 01:49:52', '_BZ9CS', 'October Management Report', 1, 0, 1, 11, '2025-11-10', '2025-11-13', NULL, 47, NULL, 'N', 2, 4000.00, 'round_to_the_nearest', NULL, 3, 100000.00, 'N', '', NULL, 1, '2025-11-10', 'client', NULL, '2025-11-11 09:49:52', 47, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_assignments`
--

DROP TABLE IF EXISTS `tija_project_assignments`;
CREATE TABLE `tija_project_assignments` (
  `assignmentID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL COMMENT 'Project ID',
  `employeeID` int(11) NOT NULL COMMENT 'Employee ID',
  `roleID` int(11) DEFAULT NULL COMMENT 'Role in the project',
  `startDate` date DEFAULT NULL COMMENT 'Assignment start date',
  `endDate` date DEFAULT NULL COMMENT 'Assignment end date',
  `allocationPercentage` decimal(5,2) DEFAULT 100.00 COMMENT 'Percentage allocation to project',
  `isActive` enum('Y','N') NOT NULL DEFAULT 'Y',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee assignments to projects';

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_phases`
--

DROP TABLE IF EXISTS `tija_project_phases`;
CREATE TABLE `tija_project_phases` (
  `projectPhaseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectID` int(11) NOT NULL,
  `projectPhaseName` varchar(180) NOT NULL,
  `phaseStartDate` date DEFAULT NULL,
  `phaseEndDate` date DEFAULT NULL,
  `phaseWorkHrs` decimal(10,2) DEFAULT NULL,
  `phaseWeighting` decimal(10,2) DEFAULT NULL,
  `billingMilestone` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_project_phases`
--

INSERT INTO `tija_project_phases` (`projectPhaseID`, `DateAdded`, `projectID`, `projectPhaseName`, `phaseStartDate`, `phaseEndDate`, `phaseWorkHrs`, `phaseWeighting`, `billingMilestone`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-16 14:53:50', 1, 'Inception/Initializing', '2025-07-16', '2025-07-31', 20.00, NULL, '', '2025-07-16 14:53:50', 0, 'N', 'N'),
(2, '2025-07-18 09:33:13', 3, 'Phase 1: Planning', '2025-07-18', '2025-07-24', 60.00, NULL, 'Y', '2025-07-18 09:33:13', 0, 'N', 'N'),
(3, '2025-07-23 03:12:26', 5, 'Preparation of financial statements', '2025-07-23', NULL, 48.00, NULL, 'N', '2025-07-23 03:12:26', 0, 'N', 'N'),
(4, '2025-07-23 03:13:47', 6, 'planning', '2025-07-23', NULL, 12.00, NULL, 'N', '2025-07-23 03:13:47', 0, 'N', 'N'),
(5, '2025-07-23 03:15:54', 6, 'Field work', '2025-07-28', NULL, 48.00, NULL, 'N', '2025-07-23 03:15:54', 0, 'N', 'N'),
(6, '2025-07-23 03:18:34', 4, 'Bookeeping - Monthly', '2025-07-01', NULL, 8.00, NULL, 'Y', '2025-07-23 03:18:34', 0, 'N', 'N'),
(7, '2025-07-23 03:26:29', 7, 'planning', '2025-08-01', NULL, 24.00, NULL, 'N', '2025-07-23 03:26:29', 0, 'N', 'N'),
(8, '2025-07-23 03:41:34', 12, 'Bookkeeping & Payroll', '2025-07-01', NULL, 50.00, NULL, '', '2025-07-23 03:41:34', 0, 'N', 'N'),
(9, '2025-07-23 03:47:46', 9, 'Planning Phase', '2025-08-01', NULL, 24.00, NULL, 'N', '2025-07-23 03:47:46', 0, 'N', 'N'),
(10, '2025-07-23 03:50:16', 9, 'Field work', '2025-08-11', NULL, 48.00, NULL, 'N', '2025-07-23 03:50:16', 0, 'N', 'N'),
(11, '2025-07-23 04:20:59', 13, 'Exemption Application', '2025-07-21', NULL, 20.00, NULL, 'N', '2025-07-23 04:20:59', 0, 'N', 'N'),
(12, '2025-07-23 04:22:04', 13, 'Follow Up for Approval', '2025-07-01', NULL, 30.00, NULL, 'N', '2025-07-23 04:22:04', 0, 'N', 'N'),
(13, '2025-07-23 04:23:47', 16, 'Audit', '2025-07-23', NULL, 30.00, NULL, 'N', '2025-07-23 04:23:47', 0, 'N', 'N'),
(14, '2025-07-23 04:42:46', 17, 'PLANNING PHASE', '2025-07-24', NULL, 24.00, NULL, 'N', '2025-07-23 04:42:46', 0, 'N', 'N'),
(15, '2025-07-24 05:35:30', 19, 'WHT', '2025-07-01', NULL, 20.00, NULL, 'N', '2025-07-24 05:35:30', 0, 'N', 'N'),
(16, '2025-07-24 05:36:53', 19, 'VAT filing', '2025-07-01', '2025-07-31', 16.00, NULL, 'Y', '2025-07-24 05:36:53', 0, 'N', 'N'),
(17, '2025-07-25 04:13:43', 20, 'Application Preparation', '2025-07-25', NULL, 30.00, NULL, 'N', '2025-07-25 04:13:43', 0, 'N', 'N'),
(18, '2025-07-25 05:18:15', 21, 'Application Submission', '2025-07-23', NULL, 30.00, NULL, 'N', '2025-07-25 05:18:15', 0, 'N', 'N'),
(19, '2025-07-25 05:19:04', 21, 'Follow Up for Approval', '2025-07-24', NULL, 20.00, NULL, 'N', '2025-07-25 05:19:04', 0, 'N', 'N'),
(20, '2025-07-30 07:59:42', 14, 'FIELDWORK', '2025-07-31', NULL, 48.00, NULL, 'N', '2025-07-30 07:59:42', 0, 'N', 'N'),
(21, '2025-08-28 04:39:17', 51, 'Inception/Initializing', '2025-08-28', '2025-11-20', 20.00, 10.00, 'N', '2025-11-03 12:22:14', 4, 'N', 'N'),
(22, '2025-08-28 04:42:16', 51, 'CyberSource', '2025-09-09', '2025-11-20', 20.00, NULL, 'N', '2025-11-03 12:22:25', 4, 'N', 'N'),
(23, '2025-09-02 15:29:41', 53, 'Inception/Initializing', '2025-09-02', NULL, 20.00, NULL, 'N', '2025-09-02 15:29:41', 0, 'N', 'N'),
(24, '2025-09-20 17:17:50', 31, 'Planning', '2025-09-20', '2025-09-30', 20.00, 40.00, 'Y', '2025-09-20 17:17:50', 27, 'N', 'N'),
(25, '2025-09-20 18:24:47', 31, 'Project Scoping', '2025-09-30', '2025-10-07', 10.00, 10.00, 'Y', '2025-09-20 18:24:47', 27, 'N', 'N'),
(26, '2025-09-21 18:06:59', 31, 'Design', '2025-09-21', '2025-09-23', 20.00, 40.00, 'Y', '2025-09-21 18:06:59', 27, 'N', 'N'),
(27, '2025-09-21 19:10:16', 49, 'Phase One', '2025-09-21', '2025-09-30', 20.00, 10.00, 'Y', '2025-09-21 19:10:16', 4, 'N', 'N'),
(28, '2025-09-21 19:26:15', 49, 'Inception/Initializing', '2025-09-30', '2025-11-12', 10.00, 10.00, 'Y', '2025-09-21 19:26:15', 4, 'N', 'N'),
(71, '2025-09-26 18:43:33', 50, 'Backend Design', '2025-09-24', '2025-10-15', 20.00, 10.00, 'Y', '2025-09-26 18:43:33', 4, 'N', 'N'),
(70, '2025-09-26 14:16:04', 53, 'Project Scope', '2025-10-14', '2025-11-21', 20.00, 20.00, 'Y', '2025-11-03 12:08:14', 4, 'N', 'N'),
(69, '2025-09-23 17:15:06', 50, 'Inception/Initializing', '2025-09-23', '2025-09-26', 20.00, 10.00, 'Y', '2025-09-23 17:15:06', 4, 'N', 'N'),
(68, '2025-09-22 19:05:28', 52, 'Project handover', '2025-09-30', '2025-10-01', 20.00, 20.00, 'Y', '2025-09-23 12:26:54', 4, 'N', 'N'),
(67, '2025-09-22 16:26:47', 52, 'Phase two Development', '2025-09-22', '2025-09-26', 10.00, 10.00, 'Y', '2025-09-22 16:26:47', 4, 'N', 'N'),
(66, '2025-09-22 16:26:45', 52, 'Phase two Development', '2025-09-22', '2025-09-26', 10.00, 10.00, 'Y', '2025-09-22 16:26:45', 4, 'N', 'N'),
(65, '2025-09-22 16:09:29', 52, 'Design', '2025-09-22', '2025-09-26', 10.00, 10.00, 'Y', '2025-09-22 16:09:29', 4, 'N', 'N'),
(64, '2025-09-22 15:46:14', 52, 'Planning', '2025-09-22', '2025-10-02', 30.00, 10.00, 'Y', '2025-09-22 15:46:14', 4, 'N', 'N'),
(63, '2025-09-22 15:45:01', 52, 'Project Scoping', '2025-09-23', '2025-09-30', 80.00, 40.00, 'Y', '2025-09-22 15:45:01', 4, 'N', 'N'),
(72, '2025-11-04 12:59:13', 59, 'Phase 1', '2025-11-04', '2025-11-12', NULL, NULL, 'N', '2025-11-04 12:59:13', 49, 'N', 'N'),
(73, '2025-11-04 12:59:13', 59, 'Phase 2', '2025-11-12', '2025-11-21', NULL, NULL, 'N', '2025-11-04 12:59:13', 49, 'N', 'N'),
(74, '2025-11-04 14:24:41', 60, 'Sprint Planning', '2025-11-04', '2025-11-06', NULL, NULL, 'N', '2025-11-04 14:24:41', 49, 'N', 'N'),
(75, '2025-11-04 14:24:41', 60, 'Development', '2025-11-06', '2025-11-23', NULL, NULL, 'N', '2025-11-04 14:24:41', 49, 'N', 'N'),
(76, '2025-11-04 14:24:41', 60, 'Review', '2025-11-23', '2025-11-25', NULL, NULL, 'N', '2025-11-04 14:24:41', 49, 'N', 'N'),
(77, '2025-11-04 14:24:41', 60, 'Retrospective', '2025-11-25', '2025-11-28', NULL, NULL, 'N', '2025-11-04 14:24:41', 49, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_roles`
--

DROP TABLE IF EXISTS `tija_project_roles`;
CREATE TABLE `tija_project_roles` (
  `roleID` int(11) NOT NULL,
  `roleName` varchar(255) NOT NULL COMMENT 'Name of the role',
  `roleDescription` text DEFAULT NULL COMMENT 'Description of the role',
  `roleCategory` varchar(100) DEFAULT NULL COMMENT 'Category of role (Technical, Management, etc.)',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available roles for project assignments';

--
-- Dumping data for table `tija_project_roles`
--

INSERT INTO `tija_project_roles` (`roleID`, `roleName`, `roleDescription`, `roleCategory`, `DateAdded`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, 'Project Manager', 'Overall project management and coordination', 'Management', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(2, 'Team Lead', 'Technical team leadership and guidance', 'Technical', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(3, 'Senior Developer', 'Senior software development role', 'Technical', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(4, 'Developer', 'Software development role', 'Technical', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(5, 'Business Analyst', 'Business requirements analysis', 'Analysis', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(6, 'Quality Assurance', 'Testing and quality assurance', 'Quality', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(7, 'UI/UX Designer', 'User interface and experience design', 'Design', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(8, 'DevOps Engineer', 'Infrastructure and deployment management', 'Technical', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(9, 'Data Analyst', 'Data analysis and reporting', 'Analysis', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(10, 'Consultant', 'External consulting role', 'Consulting', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(11, 'Project Manager', 'Overall project management and coordination', 'Management', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(12, 'Team Lead', 'Technical team leadership and guidance', 'Technical', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(13, 'Senior Developer', 'Senior software development role', 'Technical', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(14, 'Developer', 'Software development role', 'Technical', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(15, 'Business Analyst', 'Business requirements analysis', 'Analysis', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(16, 'Quality Assurance', 'Testing and quality assurance', 'Quality', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(17, 'UI/UX Designer', 'User interface and experience design', 'Design', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(18, 'DevOps Engineer', 'Infrastructure and deployment management', 'Technical', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(19, 'Data Analyst', 'Data analysis and reporting', 'Analysis', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N'),
(20, 'Consultant', 'External consulting role', 'Consulting', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_tasks`
--

DROP TABLE IF EXISTS `tija_project_tasks`;
CREATE TABLE `tija_project_tasks` (
  `projectTaskID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `DateLastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `projectTaskCode` varchar(30) NOT NULL,
  `projectTaskName` varchar(256) NOT NULL,
  `taskStart` date NOT NULL,
  `taskDeadline` date DEFAULT NULL,
  `projectID` int(11) DEFAULT NULL,
  `projectPhaseID` int(11) DEFAULT NULL,
  `billableTaskrate` varchar(20) DEFAULT NULL,
  `taskStatusID` int(11) DEFAULT NULL,
  `projectTaskTypeID` int(11) NOT NULL DEFAULT 1,
  `status` varchar(120) NOT NULL DEFAULT 'active',
  `progress` int(11) DEFAULT NULL,
  `taskDescription` text DEFAULT NULL,
  `hoursAllocated` decimal(10,2) DEFAULT NULL,
  `assigneeID` int(11) NOT NULL,
  `taskWeighting` decimal(10,2) DEFAULT NULL,
  `needsDocuments` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_project_tasks`
--

INSERT INTO `tija_project_tasks` (`projectTaskID`, `DateAdded`, `DateLastUpdated`, `projectTaskCode`, `projectTaskName`, `taskStart`, `taskDeadline`, `projectID`, `projectPhaseID`, `billableTaskrate`, `taskStatusID`, `projectTaskTypeID`, `status`, `progress`, `taskDescription`, `hoursAllocated`, `assigneeID`, `taskWeighting`, `needsDocuments`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-16 14:53:50', '2025-07-16 14:53:50', 'Q-6297864238', 'System Setup Needs analysis', '2025-07-16', '2025-07-22', 1, 1, NULL, NULL, 1, 'active', NULL, '<p>Needs Analysis</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(2, '2025-07-16 14:54:50', '2025-07-16 14:54:50', '6-7036063478', 'Create inception Report', '2025-07-16', '2025-07-18', 1, 1, NULL, NULL, 1, 'active', NULL, '<p>Inception Report</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(3, '2025-07-18 09:33:13', '2025-07-18 09:33:13', 'C-9707393943', 'dfdsghj', '2025-07-18', '2025-07-24', 3, 2, NULL, 4, 1, '4', NULL, '<p>htetryryktuk</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(4, '2025-07-18 09:34:23', '2025-07-18 09:34:23', 'Y-7269724301', 'dfdsghj', '2025-07-22', '2025-07-24', 3, 2, NULL, NULL, 1, 'active', NULL, '<p>ghjgrjg</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(5, '2025-07-23 03:13:47', '2025-07-23 03:13:47', 'K-5615635010', 'Meeting with client', '2025-07-23', '2025-07-25', 6, 4, NULL, NULL, 1, 'active', NULL, '<p>Plan how to conduct the audit and share details required to the client</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(6, '2025-07-23 03:18:34', '2025-07-23 03:18:34', 'Z-0525310710', 'AP DUTIES - WEEKLY', '2025-07-01', '2025-07-31', 4, 6, NULL, NULL, 1, 'active', NULL, '<p>Sheduling payment for the week</p>\r\n<p>Booking Payments in AP</p>\r\n<p>Closing AP weekly</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(7, '2025-07-23 03:21:26', '2025-07-23 03:21:26', '9-9518976343', 'Tresaury', '2025-07-01', '2025-07-31', 4, 6, NULL, NULL, 1, 'active', NULL, '<p>Processing payments at bank</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(8, '2025-07-23 03:26:29', '2025-07-23 03:26:29', 'F-2438039450', 'Meeting with client', '2025-08-01', '2025-08-08', 7, 7, NULL, 4, 1, '4', NULL, '<p>Planning phase prepare details requred and sampling and share with client.&nbsp;</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(9, '2025-07-23 03:41:34', '2025-07-23 03:41:34', 'P-0394540478', 'Bookkeeping Monthly', '2025-07-01', '2025-07-31', 12, 8, NULL, NULL, 1, 'active', NULL, NULL, NULL, 0, NULL, 'N', 'N', 'N'),
(10, '2025-07-23 03:47:46', '2025-07-23 03:47:46', 'F-1412104196', 'Meeting with client', '2025-08-01', '2025-08-08', 9, 9, NULL, NULL, 1, 'active', NULL, '<p>Preparing details required and sampling</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(11, '2025-07-23 03:50:16', '2025-07-23 03:50:16', 'K-3525382134', 'invoice  confirming', '2025-08-11', '2025-08-18', 9, 10, NULL, NULL, 1, 'active', NULL, '<p>confirm support document&nbsp;</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(12, '2025-07-23 03:54:41', '2025-07-23 03:54:41', 'Y-8625130138', 'Payroll - Run payroll for the month', '2025-07-20', '2025-07-31', 12, 8, NULL, NULL, 1, 'active', NULL, NULL, NULL, 0, NULL, 'N', 'N', 'N'),
(13, '2025-07-23 04:20:59', '2025-07-23 04:20:59', '4-9731782958', 'Preparation of application documents', '2025-07-21', '2025-07-24', 13, 11, NULL, 4, 1, '4', NULL, '<p>Preparation and printing of application</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(14, '2025-07-23 04:22:04', '2025-07-23 04:22:04', 'I-2737563504', 'Follow up with KRA for approval of the exemption', '2025-07-01', '2025-07-31', 13, 12, NULL, NULL, 1, 'active', NULL, '<p>Following up with KRA for approval</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(15, '2025-07-23 04:23:47', '2025-07-23 04:23:47', 'Q-7412414719', 'Visit client - Audit planning', '2025-07-23', '2025-07-25', 16, 13, NULL, NULL, 1, 'active', NULL, NULL, NULL, 0, NULL, 'N', 'N', 'N'),
(16, '2025-07-23 04:42:46', '2025-07-23 04:42:46', 'X-5140547361', 'Meeting with client', '2025-07-24', '2025-07-24', 17, 14, NULL, 4, 1, '4', NULL, '<p>Visit Client to start the audit</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(17, '2025-07-24 05:04:47', '2025-07-24 05:04:47', 'Z-7380593920', 'Card Accounts - WS 1', '2025-08-07', '2025-08-08', 1, 1, NULL, NULL, 1, 'active', NULL, '<p>ssdfga\\d</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(18, '2025-07-24 05:06:28', '2025-07-24 05:06:28', 'Y-5163101697', 'Card Accounts - WS 1', '2025-07-24', '2025-07-30', 1, 1, NULL, NULL, 1, 'active', NULL, NULL, NULL, 0, NULL, 'N', 'N', 'N'),
(19, '2025-07-24 05:35:30', '2025-07-24 05:35:30', 'C-5154067053', 'WHT filing and payments', '2025-07-01', '2025-07-31', 19, 15, NULL, NULL, 1, 'active', NULL, '<p>Weekly</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(20, '2025-07-24 05:36:53', '2025-07-24 12:57:51', 'B-7293654296', 'VAT schedule and Filing', '2025-01-01', '2025-07-31', 19, 16, NULL, NULL, 1, 'active', NULL, NULL, NULL, 0, NULL, 'N', 'N', 'N'),
(21, '2025-07-30 08:04:19', '2025-07-30 08:04:19', 'K-5718048042', 'working papers', '2025-07-30', '2025-07-31', 14, 20, NULL, NULL, 1, 'active', NULL, '<p>prepare workingpaperrs&nbsp;</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(22, '2025-08-28 04:39:17', '2025-09-24 18:02:12', 'R-9836837386', 'System Setup Needs analysis', '2025-08-28', '2025-09-16', 51, 21, NULL, NULL, 1, '2', NULL, '<p>New task loaded</p>', 12.00, 0, 10.00, 'N', 'N', 'N'),
(23, '2025-08-28 04:40:53', '2025-08-28 04:40:53', 'T-7291307930', 'Card Accounts - WS 1', '2025-09-02', '2025-09-26', 51, 21, NULL, NULL, 1, 'active', NULL, '<p>fl;\';lkjh</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(24, '2025-08-28 04:42:16', '2025-11-03 12:22:43', 'B-4306913738', 'Card Accounts - WS 1', '2025-11-03', '2025-11-20', 51, 22, NULL, NULL, 1, '2', NULL, '<p>rhtjyukli;o\'p</p>', 10.00, 0, 12.00, 'N', 'N', 'N'),
(25, '2025-09-02 15:29:41', '2025-09-02 15:29:41', 'Z-4758454895', 'System Setup Needs analysis', '2025-09-02', '2025-09-11', 53, 23, NULL, NULL, 1, 'active', NULL, '<p>zgnfdgsn sg sgh sg</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(29, '2025-09-20 19:20:16', '2025-09-20 19:20:16', 'V-8490908394', 'Card Accounts - WS 1', '2025-09-20', '2025-09-30', 31, 0, NULL, NULL, 1, 'active', NULL, '<p>fdg df ghdsg dfg</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(30, '2025-09-21 20:01:58', '2025-09-21 20:01:58', 'N-1464721064', 'System Setup Needs analysis', '2025-09-21', '2025-09-24', 49, 27, NULL, NULL, 1, 'active', NULL, '<p>xz\\xc\\</p>', NULL, 0, NULL, 'N', 'N', 'N'),
(31, '2025-09-26 16:27:55', '2025-09-26 16:27:55', 'A-0309792074', 'System Setup Needs analysis', '2025-09-26', '2025-09-30', 50, 69, NULL, NULL, 1, '1', NULL, ' dsafdsfadsfasdf', 10.00, 0, 20.00, 'N', 'N', 'N'),
(32, '2025-09-26 18:01:11', '2025-09-26 18:01:11', 'G-5481367824', 'Project Design', '2025-09-26', '2025-09-30', 50, 69, NULL, NULL, 1, 'active', NULL, ' fcgdfgdfsg', 10.00, 0, 10.00, 'N', 'N', 'N'),
(33, '2025-09-27 13:56:45', '2025-09-27 13:56:45', '5-7014847540', 'Determine Project Scope', '2025-09-24', '2025-09-26', 50, 69, NULL, NULL, 1, 'active', NULL, ' cvxcv cxv xczvx', 10.00, 0, 10.00, 'N', 'N', 'N'),
(34, '2025-09-27 13:59:15', '2025-09-27 14:24:23', 'E-6714673041', 'System Setup Needs analysis', '2025-09-25', '2025-10-08', 50, 71, NULL, NULL, 1, 'active', NULL, '<p><strong>dsd fgsdh sdhf</strong></p>', 10.00, 0, 20.00, 'N', 'N', 'N'),
(35, '2025-09-29 09:52:29', '2025-09-29 09:53:20', 'H-8179258754', 'Card Accounts - WS 1', '2025-10-15', '2025-10-17', 53, 70, NULL, NULL, 1, 'active', NULL, '<p>fgdf ghdfg dfgd</p>', 10.00, 0, 10.00, 'N', 'N', 'N'),
(36, '2025-11-03 12:07:18', '2025-11-03 12:07:18', 'P-3239754684', 'Card Accounts - WS 1', '2025-11-03', '2025-11-06', 53, 23, NULL, NULL, 1, 'active', NULL, ' asdf dsa fsdaf ', 10.00, 0, 10.00, 'N', 'N', 'N'),
(37, '2025-11-03 12:07:50', '2025-11-03 12:07:50', 'F-8389363605', 'System Setup Needs analysis', '2025-11-05', '2025-11-07', 53, 23, NULL, NULL, 1, 'active', NULL, ' fsd sdfg sdfg sdf', 10.00, 0, 10.00, 'N', 'N', 'N'),
(38, '2025-11-03 12:08:53', '2025-11-03 12:08:53', 'F-1710710890', 'New Task', '2025-11-03', '2025-11-14', 53, 70, NULL, NULL, 1, 'active', NULL, ' adsfdsafsd', 10.00, 0, 10.00, 'N', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_units`
--

DROP TABLE IF EXISTS `tija_units`;
CREATE TABLE `tija_units` (
  `unitID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `unitCode` varchar(256) DEFAULT NULL,
  `orgDataID` varchar(120) NOT NULL,
  `entityID` int(11) NOT NULL,
  `unitName` varchar(256) NOT NULL,
  `unitTypeID` int(11) NOT NULL,
  `headOfUnitID` int(11) NOT NULL,
  `parentUnitID` int(11) NOT NULL,
  `unitDescription` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_units`
--

INSERT INTO `tija_units` (`unitID`, `DateAdded`, `unitCode`, `orgDataID`, `entityID`, `unitName`, `unitTypeID`, `headOfUnitID`, `parentUnitID`, `unitDescription`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 18:34:04', 'TAX_K9RNU', '1', 1, 'Tax', 1, 9, 0, 'Tax department&nbsp;', '2025-09-19 13:11:58', 'N', 'N'),
(2, '2025-07-15 18:38:02', 'MCO_8RJBR', '1', 1, 'MCOS', 1, 3, 0, 'Management Consulting Services Department&nbsp;', '2025-09-19 12:13:29', 'N', 'N'),
(3, '2025-07-15 18:40:40', 'IMM_V4DUW', '1', 1, 'Immigration', 1, 7, 0, 'Immigration Depertment', '2025-09-19 11:45:53', 'N', 'N'),
(4, '2025-07-15 18:41:08', 'AUD_QNHGP', '1', 1, 'Audit', 1, 11, 0, 'Audit Department', '2025-09-19 13:11:47', 'N', 'N'),
(5, '2025-07-15 18:41:50', 'MKC_YFF0E', '1', 1, 'MKC', 1, 3, 0, 'Marketing, Knowledge and Communication Section', '2025-09-19 12:13:40', 'N', 'N'),
(20, '2025-10-26 19:35:07', 'A-962946', '3', 11, 'Administration', 1, 57, 0, 'The Administration Department manages the administrative needs of the company', '2025-10-26 19:56:59', 'N', 'N'),
(19, '2025-10-25 20:02:48', 'I&I-325020', '10', 10, 'ICT & Innovations', 1, 56, 0, 'INfomateion Technology, Development And innovations', '2025-10-26 15:32:20', 'N', 'N'),
(18, '2025-10-25 20:02:03', 'P-563963', '10', 10, 'Projects', 1, 0, 0, 'Projects Department', '2025-10-25 20:02:03', 'N', 'N'),
(17, '2025-10-25 20:01:44', 'O-904905', '10', 10, 'Operations & Administration', 1, 55, 0, 'Operations & Administration Department', '2025-10-26 15:38:33', 'N', 'N'),
(16, '2025-10-25 19:29:29', 'M-835892', '10', 10, 'MKC', 1, 0, 0, 'The Marketing Knowledge and Communication', '2025-10-25 19:29:29', 'N', 'N'),
(21, '2025-10-26 19:35:47', 'P-578637', '3', 11, 'Projects', 1, 0, 0, 'The Projects Department in in charge of project management', '2025-10-26 19:35:47', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_user_unit_assignments`
--

DROP TABLE IF EXISTS `tija_user_unit_assignments`;
CREATE TABLE `tija_user_unit_assignments` (
  `unitAssignmentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `unitID` int(11) NOT NULL,
  `unitTypeID` int(11) NOT NULL,
  `assignmentStartDate` date NOT NULL,
  `assignmentEndDate` date DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_user_unit_assignments`
--

INSERT INTO `tija_user_unit_assignments` (`unitAssignmentID`, `DateAdded`, `orgDataID`, `entityID`, `userID`, `unitID`, `unitTypeID`, `assignmentStartDate`, `assignmentEndDate`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-15 18:44:19', 1, 1, 3, 2, 1, '0000-00-00', NULL, '2025-07-15 18:44:19', 2, 'N', 'N'),
(2, '2025-07-15 18:52:11', 1, 1, 5, 5, 1, '0000-00-00', NULL, '2025-07-15 18:52:11', 5, 'N', 'N'),
(3, '2025-07-15 18:52:11', 1, 1, 5, 5, 2, '0000-00-00', NULL, '2025-07-15 18:52:11', 2, 'N', 'N'),
(4, '2025-07-15 18:55:18', 1, 1, 6, 5, 1, '0000-00-00', NULL, '2025-07-15 18:55:18', 6, 'N', 'N'),
(5, '2025-07-15 18:55:18', 1, 1, 6, 5, 2, '0000-00-00', NULL, '2025-07-15 18:55:18', 2, 'N', 'N'),
(6, '2025-07-21 06:40:10', 1, 1, 7, 3, 1, '0000-00-00', NULL, '2025-07-21 06:40:10', 2, 'N', 'N'),
(7, '2025-07-21 06:49:06', 1, 1, 8, 1, 1, '0000-00-00', NULL, '2025-07-21 06:49:06', 2, 'N', 'N'),
(8, '2025-07-21 06:50:47', 1, 1, 9, 1, 1, '0000-00-00', NULL, '2025-07-21 06:50:47', 2, 'N', 'N'),
(9, '2025-07-21 06:56:06', 1, 1, 10, 1, 1, '0000-00-00', NULL, '2025-07-21 06:56:06', 2, 'N', 'N'),
(10, '2025-07-21 06:57:25', 1, 1, 11, 4, 1, '0000-00-00', NULL, '2025-07-21 06:57:25', 2, 'N', 'N'),
(11, '2025-07-21 07:50:28', 1, 1, 12, 1, 1, '0000-00-00', NULL, '2025-07-21 07:50:28', 2, 'N', 'N'),
(12, '2025-07-21 07:55:53', 1, 1, 13, 2, 1, '0000-00-00', NULL, '2025-07-21 07:55:53', 2, 'N', 'N'),
(13, '2025-07-21 08:01:11', 1, 1, 14, 1, 1, '0000-00-00', NULL, '2025-07-21 08:01:11', 2, 'N', 'N'),
(14, '2025-07-21 08:02:44', 1, 1, 15, 2, 1, '0000-00-00', NULL, '2025-07-21 08:02:44', 2, 'N', 'N'),
(15, '2025-07-21 08:04:53', 1, 1, 16, 1, 1, '0000-00-00', NULL, '2025-07-21 08:04:53', 2, 'N', 'N'),
(16, '2025-07-21 08:05:58', 1, 1, 17, 2, 1, '0000-00-00', NULL, '2025-07-21 08:05:58', 2, 'N', 'N'),
(17, '2025-07-21 08:08:26', 1, 1, 18, 4, 1, '0000-00-00', NULL, '2025-07-21 08:08:26', 2, 'N', 'N'),
(18, '2025-07-21 08:11:52', 1, 1, 19, 3, 1, '0000-00-00', NULL, '2025-07-21 08:11:52', 2, 'N', 'N'),
(19, '2025-07-21 08:17:05', 1, 1, 20, 1, 1, '0000-00-00', NULL, '2025-07-21 08:17:05', 2, 'N', 'N'),
(20, '2025-07-21 08:23:45', 1, 1, 21, 1, 1, '0000-00-00', NULL, '2025-07-21 08:23:45', 2, 'N', 'N'),
(21, '2025-07-21 08:33:54', 1, 1, 22, 1, 1, '0000-00-00', NULL, '2025-07-21 08:33:54', 2, 'N', 'N'),
(22, '2025-07-21 08:35:33', 1, 1, 23, 4, 1, '0000-00-00', NULL, '2025-07-21 08:35:33', 2, 'N', 'N'),
(23, '2025-07-21 08:43:39', 1, 1, 24, 2, 1, '0000-00-00', NULL, '2025-07-21 08:43:39', 2, 'N', 'N'),
(24, '2025-07-21 08:45:03', 1, 1, 25, 2, 1, '0000-00-00', NULL, '2025-07-21 08:45:03', 2, 'N', 'N'),
(25, '2025-07-21 08:46:21', 1, 1, 26, 2, 1, '0000-00-00', NULL, '2025-07-21 08:46:21', 2, 'N', 'N'),
(26, '2025-07-21 08:47:27', 1, 1, 27, 2, 1, '0000-00-00', NULL, '2025-07-21 08:47:27', 2, 'N', 'N'),
(27, '2025-07-21 08:48:48', 1, 1, 28, 3, 1, '0000-00-00', NULL, '2025-07-21 08:48:48', 2, 'N', 'N'),
(28, '2025-07-21 08:50:00', 1, 1, 29, 1, 1, '0000-00-00', NULL, '2025-07-21 08:50:00', 2, 'N', 'N'),
(29, '2025-07-21 08:52:05', 1, 1, 30, 1, 1, '0000-00-00', NULL, '2025-07-21 08:52:05', 2, 'N', 'N'),
(30, '2025-07-22 03:06:33', 1, 1, 29, 5, 2, '0000-00-00', '0000-00-00', '2025-07-22 03:06:33', 29, 'N', 'N'),
(31, '2025-07-22 03:08:01', 1, 1, 23, 5, 2, '0000-00-00', '0000-00-00', '2025-07-22 03:08:01', 23, 'N', 'N'),
(32, '2025-07-23 05:10:54', 1, 1, 31, 5, 2, '2025-07-14', '2026-07-14', '2025-07-23 05:10:54', 2, 'N', 'N'),
(33, '2025-07-23 05:28:05', 1, 1, 32, 1, 1, '0000-00-00', NULL, '2025-07-23 05:28:05', 2, 'N', 'N'),
(34, '2025-07-28 03:58:46', 1, 1, 33, 4, 1, '0000-00-00', NULL, '2025-07-28 03:58:46', 2, 'N', 'N'),
(35, '2025-07-28 04:00:41', 1, 1, 34, 2, 1, '0000-00-00', NULL, '2025-07-28 04:00:41', 2, 'N', 'N'),
(36, '2025-07-28 04:14:25', 1, 1, 35, 2, 1, '0000-00-00', NULL, '2025-07-28 04:14:25', 2, 'N', 'N'),
(37, '2025-07-28 04:16:38', 1, 1, 36, 2, 1, '0000-00-00', NULL, '2025-07-28 04:16:38', 2, 'N', 'N'),
(38, '2025-07-28 04:24:06', 1, 1, 37, 1, 1, '0000-00-00', NULL, '2025-07-28 04:24:06', 2, 'N', 'N'),
(39, '2025-07-28 04:26:56', 1, 1, 38, 2, 1, '0000-00-00', NULL, '2025-07-28 04:26:56', 2, 'N', 'N'),
(40, '2025-07-28 04:28:10', 1, 1, 39, 4, 1, '0000-00-00', NULL, '2025-07-28 04:28:10', 2, 'N', 'N'),
(41, '2025-07-28 05:00:52', 1, 1, 40, 5, 1, '0000-00-00', NULL, '2025-07-28 05:00:52', 40, 'N', 'N'),
(42, '2025-07-28 05:00:52', 1, 1, 40, 5, 2, '0000-00-00', NULL, '2025-07-28 05:00:52', 2, 'N', 'N'),
(43, '2025-07-28 06:06:03', 1, 1, 41, 2, 1, '0000-00-00', NULL, '2025-07-28 06:06:03', 2, 'N', 'N'),
(44, '2025-07-28 06:07:04', 1, 1, 42, 3, 1, '0000-00-00', NULL, '2025-07-28 06:07:04', 2, 'N', 'N'),
(45, '2025-07-28 06:08:06', 1, 1, 43, 1, 1, '0000-00-00', NULL, '2025-07-28 06:08:06', 2, 'N', 'N'),
(46, '2025-07-28 06:10:07', 1, 1, 44, 1, 1, '0000-00-00', NULL, '2025-07-28 06:10:07', 2, 'N', 'N'),
(47, '2025-07-28 06:12:51', 1, 1, 45, 2, 1, '0000-00-00', NULL, '2025-07-28 06:12:51', 2, 'N', 'N'),
(48, '2025-07-28 06:14:24', 1, 1, 46, 2, 1, '0000-00-00', NULL, '2025-07-28 06:14:24', 2, 'N', 'N'),
(49, '2025-08-13 09:46:24', 1, 1, 47, 2, 1, '0000-00-00', NULL, '2025-08-13 09:46:24', 2, 'N', 'N'),
(50, '2025-08-25 09:15:34', 1, 1, 31, 5, 1, '0000-00-00', '0000-00-00', '2025-08-25 09:15:34', 31, 'N', 'N'),
(51, '2025-09-02 11:19:25', 1, 1, 4, 2, 1, '0000-00-00', '0000-00-00', '2025-09-02 11:19:25', 4, 'N', 'N'),
(52, '2025-10-13 17:30:55', 1, 1, 48, 2, 1, '0000-00-00', NULL, '2025-10-13 17:30:55', 4, 'N', 'N'),
(53, '2025-10-13 18:22:02', 1, 1, 49, 2, 1, '0000-00-00', NULL, '2025-10-13 18:22:02', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

DROP TABLE IF EXISTS `user_details`;
CREATE TABLE `user_details` (
  `ID` int(11) NOT NULL,
  `UID` varchar(256) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL DEFAULT 1,
  `entityID` int(11) NOT NULL,
  `prefixID` varchar(10) DEFAULT NULL,
  `phoneNo` varchar(40) DEFAULT NULL,
  `payrollNo` varchar(20) DEFAULT NULL,
  `PIN` varchar(30) DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `businessUnitID` int(11) DEFAULT NULL,
  `supervisorID` int(11) DEFAULT NULL,
  `supervisingJobTitleID` int(11) DEFAULT NULL,
  `workTypeID` int(11) DEFAULT NULL,
  `jobTitleID` int(11) DEFAULT NULL,
  `departmentID` int(11) DEFAULT NULL,
  `costPerHour` int(11) DEFAULT NULL,
  `jobCategoryID` int(11) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `jobBandID` int(11) DEFAULT NULL,
  `employmentStatusID` int(11) DEFAULT NULL,
  `dailyHours` int(11) DEFAULT NULL,
  `weekWorkDays` varchar(256) DEFAULT NULL,
  `overtimeAllowed` enum('Y','N') DEFAULT NULL,
  `workHourRoundingID` int(11) DEFAULT NULL,
  `payGradeID` int(11) DEFAULT NULL,
  `nationalID` varchar(23) DEFAULT NULL,
  `nhifNumber` varchar(22) DEFAULT NULL,
  `nssfNumber` varchar(22) DEFAULT NULL,
  `basicSalary` float(16,2) DEFAULT NULL,
  `bonusEligible` enum('Y','N') DEFAULT 'N' COMMENT 'Eligible for performance bonuses',
  `commissionEligible` enum('Y','N') DEFAULT 'N' COMMENT 'Eligible for sales commission',
  `commissionRate` decimal(5,2) DEFAULT 0.00 COMMENT 'Commission percentage (0-100)',
  `SetUpProfile` enum('y','n') NOT NULL DEFAULT 'n',
  `profileImageFile` varchar(256) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `contractStartDate` varchar(234) DEFAULT NULL,
  `contractEndDate` varchar(234) DEFAULT NULL,
  `employmentStartDate` date DEFAULT NULL,
  `employmentEndDate` date DEFAULT NULL,
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`ID`, `UID`, `DateAdded`, `orgDataID`, `entityID`, `prefixID`, `phoneNo`, `payrollNo`, `PIN`, `dateOfBirth`, `gender`, `businessUnitID`, `supervisorID`, `supervisingJobTitleID`, `workTypeID`, `jobTitleID`, `departmentID`, `costPerHour`, `jobCategoryID`, `salary`, `jobBandID`, `employmentStatusID`, `dailyHours`, `weekWorkDays`, `overtimeAllowed`, `workHourRoundingID`, `payGradeID`, `nationalID`, `nhifNumber`, `nssfNumber`, `basicSalary`, `bonusEligible`, `commissionEligible`, `commissionRate`, `SetUpProfile`, `profileImageFile`, `Lapsed`, `Suspended`, `contractStartDate`, `contractEndDate`, `employmentStartDate`, `employmentEndDate`, `LastUpdatedByID`, `LastUpdate`) VALUES
(3, 'dbcd10ef7aae2b7b411c8e5901bfe7154eb2543a21abe43d466545208a3510e4', '2025-07-15 18:44:19', 1, 1, '1', '+254727118057', 'SKMO29', '', '0000-00-00', 'male', NULL, 9, NULL, NULL, 55, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, NULL, 30, NULL, '', '', '', 500000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 2, '2025-07-15 18:44:19'),
(4, '561ef2f4eb623a9dae283a0dfa6d7557fae7c25ff7f2a7d17b0a905cd30c7b1d', '2025-07-15 18:46:16', 1, 1, NULL, '+254722540269', 'SKMO98', NULL, '1991-08-14', 'male', NULL, 3, NULL, NULL, 19, NULL, 1905, NULL, NULL, NULL, 1, 8, '5', 'Y', 3, 5, NULL, NULL, NULL, 320000.00, 'Y', 'Y', 10.00, 'n', 'employee_profile/1756735549_4.jpg', 'N', 'N', '2025-01-01', '2028-12-29', '2025-01-01', NULL, 4, '2025-07-15 18:46:16'),
(5, '9c6ad48a79ff0eee5873c81860fc2c163a5f0179948cba608ac72416aa75e7f8', '2025-07-15 18:52:11', 1, 1, '3', '254740111049', 'SKM025', 'A008450601D', '1994-04-24', 'female', NULL, 3, NULL, NULL, 58, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '31444580', 'CR9891584474581-7', '2042818258', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 5, '2025-07-15 18:52:11'),
(6, '2031e5211127842a238462d428920b5ab6968a404fcc07e748e9882086962fe7', '2025-07-15 18:55:18', 1, 1, '1', '0768434123', 'SKM033', 'A014156377W', '2001-01-12', 'male', NULL, 3, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '38366541', 'CR1195252096699-9', '2053378524', NULL, 'N', 'N', 0.00, 'n', 'employee_profile/1756192514_Logos12.png', 'N', 'N', NULL, NULL, NULL, NULL, 6, '2025-07-15 18:55:18'),
(7, '4445f17c647dac74a8e72aed1208caeae55263e7d8922524eb522770de60ed69', '2025-07-21 06:40:10', 1, 1, '2', '', 'SKM001', '', '0000-00-00', 'female', NULL, 9, NULL, NULL, 55, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 2, '2025-07-21 06:40:10'),
(8, '6d99c3687ac109be42153092d6109e55ed1f437d36156750b957af7b159351cc', '2025-07-21 06:49:06', 1, 1, '1', '', 'SKM004', '', '0000-00-00', 'male', NULL, 9, NULL, NULL, 55, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 2, '2025-07-21 06:49:06'),
(9, '4b88190e677f87a81702da809eafa53438bfad7281743a58f839eec9f74ebac7', '2025-07-21 06:50:47', 1, 1, '1', '', 'SKM006', '', '0000-00-00', 'male', NULL, 8, NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 2, '2025-07-21 06:50:47'),
(10, 'f179519e7f05fefc872d29a4c2f39c7a8bb8a4e05b3920c52500dabdd5faf470', '2025-07-21 06:56:06', 1, 1, '1', '', 'SKM022', '', '0000-00-00', 'male', NULL, 9, NULL, NULL, 55, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 06:56:06'),
(11, '339d6811f88c1a6694347a28bb99f39ac4a0a1458eb398b5c6ad96a4ca36e3a3', '2025-07-21 06:57:25', 1, 1, '1', '', '', '', '0000-00-00', 'male', NULL, 9, NULL, NULL, 56, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 06:57:25'),
(12, '2b1f8bb51b4214638483f738069e79a35691871e0a107f63d5b4813423de9477', '2025-07-21 07:50:28', 1, 1, '3', '', 'SKM039', '', '0000-00-00', 'female', NULL, 10, NULL, NULL, 64, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '', '', '', 320000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 2, '2025-07-21 07:50:28'),
(13, '551ecf5e89d349c21c05bf6f26b83554abf0af14804f02dda71c687c7bbd9747', '2025-07-21 07:55:53', 1, 1, '', '', 'SKM036', '', '0000-00-00', 'male', NULL, 3, NULL, NULL, 54, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '', '', '', 230000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 2, '2025-07-21 07:55:53'),
(14, '1e4c7589fc88b50481d20a51e4f22b6ffb908fadb72ee885d9c6ca19416443e0', '2025-07-21 08:01:11', 1, 1, '1', '', 'SKM034', '', '0000-00-00', 'male', NULL, 9, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 150000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:01:11'),
(15, 'b14457369c100de1c96b9dfb4a10facc6b51cb21717199ee4752804e0539bc91', '2025-07-21 08:02:44', 1, 1, '1', '', 'SKM018', '', '0000-00-00', 'male', NULL, 3, NULL, NULL, 57, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 150000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:02:44'),
(16, 'bcbec5daf078bc05e2a12277b774e689c198480495521bd20334e9994a539bc1', '2025-07-21 08:04:53', 1, 1, '1', '', 'SKM040', '', '0000-00-00', 'male', NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 150000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:04:53'),
(17, '616e902d3de4e0909a9ced4b7129a1612d685876732ddf374cddcb161b4ada27', '2025-07-21 08:05:58', 1, 1, '3', '', '', '', '0000-00-00', 'female', NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 150000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:05:58'),
(18, '641644e4babe2cfd9a0099236e033f8bbe2ebe3afd665e759a12294426c01a32', '2025-07-21 08:08:26', 1, 1, '3', '', 'SKM024', '', '0000-00-00', 'female', NULL, 11, NULL, NULL, 57, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, NULL, 0, NULL, '', '', '', 200000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:08:26'),
(19, '58fcfa1b0f5c267ce5f16759b79db06f73bb10382417e467c0fe55a41a27471c', '2025-07-21 08:11:52', 1, 1, '3', '0711637294', 'SKM005', 'A003438569J', '1975-06-30', 'female', NULL, 7, NULL, NULL, 58, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '20541226', '573993815', 'CR4350819246006-7', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 19, '2025-07-21 08:11:52'),
(20, '7386f8d2123b813f9d3708920a17d0d80f6fb9e65b51b11b27ca11aa5f3cc6e8', '2025-07-21 08:17:05', 1, 1, '3', '', 'SKM014', '', '0000-00-00', 'female', NULL, 12, NULL, NULL, 58, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, NULL, 0, NULL, '', '', '', 130000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:17:05'),
(21, 'd512733897ff9060d55dbf5a96181eb802d55b2292c9cfde971ead0b306780fe', '2025-07-21 08:23:45', 1, 1, '1', '0738560095', 'SKM002', 'A003550482T', '1981-05-06', 'male', NULL, 9, NULL, NULL, 58, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '22009887', 'CR9496628364440-3', '680562923', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 21, '2025-07-21 08:23:45'),
(22, 'efd90564f0bd3a6a7cf4c1f434d18bd2de8eb5e526eca0b7fcaea441d12b65a6', '2025-07-21 08:33:54', 1, 1, '1', '', 'SKM031', '', '0000-00-00', 'male', NULL, 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '', '', '', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 22, '2025-07-21 08:33:54'),
(23, '253b2426060eac458fed1c11f71e2b51d7b4317dfa6463c68c3f01439821e68c', '2025-07-21 08:35:33', 1, 1, '1', '0791043952', 'SKM032', '', '1999-09-05', 'male', NULL, 11, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '36854354', '20700945', '2042951721', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 23, '2025-07-21 08:35:33'),
(24, '55fb97d325df9524d45c40a76faa698675a888a6c4e3496bc4b6b11f328a6f3d', '2025-07-21 08:43:39', 1, 1, '3', '', 'SKM037', '', '0000-00-00', 'female', NULL, 13, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, NULL, 0, NULL, '', '', '', 70000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:43:39'),
(25, '8778c194ea50b5dc4dcc4edc07aef09bb7576ef68b6d23fd8ecc5b7a1183cdc4', '2025-07-21 08:45:03', 1, 1, '4', '0702401761', 'SKM038', '', '2007-08-08', 'female', NULL, 13, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '35940382', 'CR9838459680349-0', '203624395', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 25, '2025-07-21 08:45:03'),
(26, '82bcd03beff39d72cab261848cbade588ae57605dcc55261c47131107265c22c', '2025-07-21 08:46:21', 1, 1, '1', '', 'SKM017', '', '0000-00-00', 'male', NULL, 4, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, NULL, NULL, NULL, '', '', '', 90000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', NULL, 4, '2025-07-21 08:46:21'),
(27, '4bd3615b74448d9ca948234ca41926e553e2039b928b0e557520c129fd4be34e', '2025-07-21 08:47:27', 1, 1, '1', '', 'SKM011', '', '0000-00-00', 'male', NULL, 17, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 0, 8, NULL, NULL, 0, NULL, '', '', '', 90000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:47:27'),
(28, '8d185c1fb9262aac4c15548b91fb1a305b0af9d1855c7b84853e434be8055d4f', '2025-07-21 08:48:48', 1, 1, '1', '', 'SKM021', '', '0000-00-00', 'male', NULL, 7, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, NULL, 0, NULL, '', '', '', 90000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-21 08:48:48'),
(29, 'fc964de5590deca92520ae1465f6e9feb6379be8004dd9d8ec548346d8282fa9', '2025-07-21 08:50:00', 1, 1, '1', '0712073052', 'SKM025', '', '2000-01-08', 'male', NULL, 9, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '36943452', 'CR9000615274480-4', '2041516842', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 29, '2025-07-21 08:50:00'),
(30, '4345987acfd033ade279f88e4a1d4195b3d48c3ead80cc226fe98d7b1d91dfa7', '2025-07-21 08:52:05', 1, 1, '3', '254 702729213', 'SKM041', 'A016774043W', '2001-01-18', 'female', NULL, 12, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '38242434', 'CR7065340585063-3', '2056632534', NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 30, '2025-07-21 08:52:05'),
(31, '8f5442bd332be58ecae06841a1361e45ee8421adaf270109ce93c8761e92a62b', '2025-07-23 05:10:54', 1, 1, '4', '+254700868488', 'SKM051', 'A015449402R', '2002-04-22', 'female', NULL, 3, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', NULL, NULL, NULL, '39293222', 'CR1776876726224-7', '204983119X', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 31, '2025-07-23 05:10:54'),
(32, 'ef7b3d8042d6a55cf5d8cba2d19d8860145f0caa5c28ac43644a76cfdaa3e904', '2025-07-23 05:28:05', 1, 1, '1', '0729340321', '', '', '1991-08-28', 'male', NULL, 9, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-23 05:28:05'),
(33, 'b234d382586e182f32b32fe904f37d7eefdfd54a30b3f1f51059d43e3771b7ac', '2025-07-28 03:58:46', 1, 1, '1', '', '', '', '0000-00-00', 'male', NULL, 11, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 03:58:46'),
(34, '54047e994dda91020eabc249c9c7c4c8abd2053eee2e3f6464b959ae10fa0dd7', '2025-07-28 04:00:41', 1, 1, '3', '', '', '', '0000-00-00', 'female', NULL, 3, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 04:00:41'),
(35, '3ccb63dcf53ea2ff2f39cbfa196cf493578e1e6ea7beeeb15514e6f51cb805fe', '2025-07-28 04:14:25', 1, 1, '', '254713820589', 'SKM048', 'A006273818N', '1996-12-12', 'male', NULL, 3, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '28871578', 'CR4724472217306-3', '2005679378', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 35, '2025-07-28 04:14:25'),
(36, '5f207281086178188a8e11bbf455f47ff9b1d3ffad41845833d45a47fc377bc3', '2025-07-28 04:16:38', 1, 1, '3', '254 728 753 590', 'N', '', '1989-02-23', 'female', NULL, 3, NULL, NULL, 57, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '27309818', '3439824', '383093821', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 36, '2025-07-28 04:16:38'),
(37, 'b65df87d16a43face357de199615283540ef6b6f7d6d19ea7f1e152acd4d182b', '2025-07-28 04:24:06', 1, 1, '1', '254710471237', 'SKM045', 'A011254267P', '1999-05-30', 'male', NULL, 29, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '36573089', 'CR2676068408059-5', '2042367326', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 37, '2025-07-28 04:24:06'),
(38, '0be4d36b85beb4ba69fa97cbc07ea6c75a66c4b57781b1c9ba36c8099457cda1', '2025-07-28 04:26:56', 1, 1, '3', '', '', '', '0000-00-00', 'female', NULL, 24, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 04:26:56'),
(39, '2f228fb2baef39dac9f4dc6dbd78dcb353c2edeb8a76ab23695dc11bd90cfb5a', '2025-07-28 04:28:10', 1, 1, '3', '0768801783/0736137736', 'SKM047', 'A014290083M', '2000-06-24', 'female', NULL, 0, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '38070473', 'CR3204678770926-0', '2056620032', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 39, '2025-07-28 04:28:10'),
(40, 'ac7c0b3140045507e8c4f3d5d4ac2a884bc181a00ebca4a53969c3373b119e9f', '2025-07-28 05:00:52', 1, 1, '1', '254704951584', 'N/A', 'A016986565U', '2001-05-14', 'male', NULL, 6, NULL, NULL, 61, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, '38955962', '2312647254998-0', '2059489057', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 40, '2025-07-28 05:00:52'),
(41, 'ed51ae390e7f26b9e9f1ce47bee05ef7ce1d32878f6753efedbac39dea30f0cb', '2025-07-28 06:06:03', 1, 1, '3', '', '', '', '0000-00-00', 'female', NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 06:06:03'),
(42, '29652146de422fe830cb02a2054af4bab23505d5b3803aaefb2def2fc5445c6b', '2025-07-28 06:07:04', 1, 1, '3', '', '', '', '1996-11-14', 'female', NULL, 19, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 42, '2025-07-28 06:07:04'),
(43, 'b5d6dc8cbd0c736a23fefa23ff443b8238f63b51d159ff4e21cfdc9d5bd70a0a', '2025-07-28 06:08:06', 1, 1, '1', '254769528702', 'INT019', 'A016576969T', '2002-06-30', 'male', NULL, 30, NULL, NULL, 61, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, '39417094', 'CR6799179787245-9', '2046163271', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 43, '2025-07-28 06:08:06'),
(44, 'd743383551053dafd1de68c1beb98f2b12b5d89ef12de7156733ba07d1cd4a89', '2025-07-28 06:10:07', 1, 1, '3', '', '', '', '0000-00-00', '', NULL, 29, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 06:10:07'),
(45, '53a61a5ae51fa002fb6ef0258a2662decf45145aeaaca07e631de90d4ba2cf39', '2025-07-28 06:12:51', 1, 1, '3', '', '', '', '0000-00-00', 'female', NULL, 34, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 06:12:51'),
(46, 'a86b36701c3770a7ac1b1d7fcc0a469f123210de8c9f936284ab396367b94fee', '2025-07-28 06:14:24', 1, 1, '1', '', '', '', '0000-00-00', 'male', NULL, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 2, 0, NULL, NULL, 0, NULL, '', '', '', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '0000-00-00', '0000-00-00', 2, '2025-07-28 06:14:24'),
(47, '0fce19dec1ecac6db33af28ffb8b6bb447528634cf6c60a44aebf1f8c279c96e', '2025-08-13 09:46:24', 1, 1, '1', '254723793954', 'SKM052', 'A013679056K.', '2001-01-24', 'male', NULL, 0, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, '38081511', 'CR7328568754999', '2041518696', 0.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 47, '2025-08-13 09:46:24'),
(48, 'd686708be50ef489a883f79c8ef6b9648201f6c08e0b306a0f36bd568735d376', '2025-10-13 17:30:55', 1, 1, '1', '0728977327', 'SKM008', 'A005447909A', '1988-11-11', 'male', NULL, 9, NULL, NULL, 55, NULL, 1488, NULL, NULL, NULL, 1, 8, '5', NULL, 0, 5, '26081398', 'CR0356108917583', '883344912', 250000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2020-06-01', '0000-00-00', 48, '2025-10-13 17:30:55'),
(49, '123ee010c931b3fafeb9c38cee291d46b7b2881e941c2cd5f08f509ccb654d62', '2025-10-13 18:22:02', 1, 1, '1', '722540168', 'asdas', 'A004654098I', '2007-05-25', 'male', NULL, 8, NULL, NULL, 54, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, NULL, 0, NULL, '2343456543', 'Y78u9474', '2343654', 0.00, 'N', 'N', 0.00, 'n', 'employee_profile/1760522331_8.jpg', 'N', 'N', NULL, NULL, NULL, NULL, 49, '2025-10-13 18:22:02'),
(50, '9f9b7f1ec6d9db133842d0cfacb5277f53d5b4dbe7cc67e692744b9a7a24474f', '2025-10-29 05:31:10', 1, 1, '3', '+254', NULL, NULL, NULL, '', NULL, 0, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-10-29 05:31:10'),
(55, '232902066fde48546ee88d9b0f4a3b12add6fc86fe815a6983916f34a7e15d92', '2025-10-26 13:05:29', 10, 10, '1', '+60722540168', 'SBSL-001', 'A004654098K', '1996-04-14', '', NULL, NULL, NULL, NULL, 14, NULL, NULL, NULL, 500000.00, NULL, 1, 8, '5', 'N', 1, NULL, '2343456543', '23503042', '234365454yNSSF', 500000.00, 'Y', 'N', 0.00, 'n', NULL, 'N', 'N', '2023-01-02', NULL, '2023-01-02', NULL, 1, '2025-10-26 13:05:29'),
(56, '443cf2964f4a7407f56fc7675cab6fa5c1c2c0dc19776947de63cd465043fb7a', '2025-10-26 13:30:47', 10, 10, '1', '+254722540169', 'SBSL-002', 'A004654098I', '1983-06-01', '', NULL, 55, NULL, NULL, 19, NULL, NULL, NULL, 300000.00, NULL, 1, 8, '5', 'N', NULL, NULL, '2343456543', 'Y78u94743', '23436543', 300000.00, 'Y', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-10-26 13:30:47'),
(57, 'd5ff905e8136e00ce08f91e5d1e451368e522be69237b665446002929c7b1c0e', '2025-10-26 19:42:27', 3, 11, '1', '+60722540168', NULL, NULL, NULL, '', NULL, 0, NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-10-26 19:42:27'),
(58, '8132434aa15fe78b8da09e21ece784682a4bfdd3e4304b39983e02a62a825ce2', '2025-10-26 19:48:32', 3, 11, '1', '+60722540168', 'SKM002', NULL, NULL, '', NULL, 57, NULL, NULL, 19, NULL, NULL, NULL, NULL, NULL, 1, 8, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-10-26 19:48:32'),
(59, 'd4ce75f7184a1366d73798725f6f8af9e5254ef9a1de5ae21754eba8e9c826f7', '2025-10-27 11:42:38', 3, 1, '1', '+60722540168', NULL, NULL, NULL, '', NULL, 0, NULL, NULL, 16, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-10-27', NULL, 4, '2025-10-27 11:42:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `login_sessions`
--
ALTER TABLE `login_sessions`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `SessIDStr` (`SessIDStr`);

--
-- Indexes for table `people`
--
-- Note: PRIMARY KEY and UNIQUE KEY for Email are already defined in CREATE TABLE statement above
-- ALTER TABLE `people`
--   ADD PRIMARY KEY (`ID`),
--   ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `registration_tokens`
--
ALTER TABLE `registration_tokens`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Token1` (`Token1`),
  ADD UNIQUE KEY `Token2` (`Token2`);

--
-- Indexes for table `tija_clients`
--
ALTER TABLE `tija_clients`
  ADD PRIMARY KEY (`clientID`);

--
-- Indexes for table `tija_client_addresses`
--
ALTER TABLE `tija_client_addresses`
  ADD PRIMARY KEY (`clientAddressID`);

--
-- Indexes for table `tija_client_contacts`
--
ALTER TABLE `tija_client_contacts`
  ADD PRIMARY KEY (`clientContactID`);

--
-- Indexes for table `tija_client_documents`
--
ALTER TABLE `tija_client_documents`
  ADD PRIMARY KEY (`clientDocumentID`);

--
-- Indexes for table `tija_contact_relationships`
--
ALTER TABLE `tija_contact_relationships`
  ADD PRIMARY KEY (`relationshipID`),
  ADD UNIQUE KEY `relationshipCode` (`relationshipCode`);

--
-- Indexes for table `tija_entities`
--
ALTER TABLE `tija_entities`
  ADD PRIMARY KEY (`entityID`);

--
-- Indexes for table `tija_organisation_data`
--
ALTER TABLE `tija_organisation_data`
  ADD PRIMARY KEY (`orgDataID`),
  ADD UNIQUE KEY `orgPIN` (`orgPIN`);

--
-- Indexes for table `tija_org_charts`
--
ALTER TABLE `tija_org_charts`
  ADD PRIMARY KEY (`orgChartID`);

--
-- Indexes for table `tija_org_chart_position_assignments`
--
ALTER TABLE `tija_org_chart_position_assignments`
  ADD PRIMARY KEY (`positionAssignmentID`);

--
-- Indexes for table `tija_projects`
--
ALTER TABLE `tija_projects`
  ADD PRIMARY KEY (`projectID`);

--
-- Indexes for table `tija_project_assignments`
--
ALTER TABLE `tija_project_assignments`
  ADD PRIMARY KEY (`assignmentID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_role` (`roleID`),
  ADD KEY `idx_active` (`isActive`);

--
-- Indexes for table `tija_project_phases`
--
ALTER TABLE `tija_project_phases`
  ADD PRIMARY KEY (`projectPhaseID`);

--
-- Indexes for table `tija_project_roles`
--
ALTER TABLE `tija_project_roles`
  ADD PRIMARY KEY (`roleID`),
  ADD KEY `idx_role_name` (`roleName`),
  ADD KEY `idx_role_category` (`roleCategory`);

--
-- Indexes for table `tija_project_tasks`
--
ALTER TABLE `tija_project_tasks`
  ADD PRIMARY KEY (`projectTaskID`),
  ADD UNIQUE KEY `projectTaskCode` (`projectTaskCode`);

--
-- Indexes for table `tija_units`
--
ALTER TABLE `tija_units`
  ADD PRIMARY KEY (`unitID`),
  ADD UNIQUE KEY `UID` (`unitCode`);

--
-- Indexes for table `tija_user_unit_assignments`
--
ALTER TABLE `tija_user_unit_assignments`
  ADD PRIMARY KEY (`unitAssignmentID`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD UNIQUE KEY `UID` (`UID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_sessions`
--
ALTER TABLE `login_sessions`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `registration_tokens`
--
ALTER TABLE `registration_tokens`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tija_clients`
--
ALTER TABLE `tija_clients`
  MODIFY `clientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tija_client_addresses`
--
ALTER TABLE `tija_client_addresses`
  MODIFY `clientAddressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tija_client_contacts`
--
ALTER TABLE `tija_client_contacts`
  MODIFY `clientContactID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `tija_client_documents`
--
ALTER TABLE `tija_client_documents`
  MODIFY `clientDocumentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `tija_contact_relationships`
--
ALTER TABLE `tija_contact_relationships`
  MODIFY `relationshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_entities`
--
ALTER TABLE `tija_entities`
  MODIFY `entityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tija_organisation_data`
--
ALTER TABLE `tija_organisation_data`
  MODIFY `orgDataID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_org_charts`
--
ALTER TABLE `tija_org_charts`
  MODIFY `orgChartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_org_chart_position_assignments`
--
ALTER TABLE `tija_org_chart_position_assignments`
  MODIFY `positionAssignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_projects`
--
ALTER TABLE `tija_projects`
  MODIFY `projectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `tija_project_assignments`
--
ALTER TABLE `tija_project_assignments`
  MODIFY `assignmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_phases`
--
ALTER TABLE `tija_project_phases`
  MODIFY `projectPhaseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `tija_project_roles`
--
ALTER TABLE `tija_project_roles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tija_project_tasks`
--
ALTER TABLE `tija_project_tasks`
  MODIFY `projectTaskID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `tija_units`
--
ALTER TABLE `tija_units`
  MODIFY `unitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tija_user_unit_assignments`
--
ALTER TABLE `tija_user_unit_assignments`
  MODIFY `unitAssignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
COMMIT;
