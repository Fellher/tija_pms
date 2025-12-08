-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 06, 2025 at 09:08 AM
-- Server version: 11.4.8-MariaDB-cll-lve-log
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sbsl_pms`
--

DELIMITER $$
--
-- Procedures
--
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
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `ID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`ID`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `african_countries`
--

CREATE TABLE `african_countries` (
  `countryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `countryName` varchar(100) NOT NULL,
  `countryCode` char(2) NOT NULL,
  `countryISO3Code` char(3) NOT NULL,
  `phoneCode` varchar(5) DEFAULT NULL,
  `countryCapital` varchar(100) DEFAULT NULL,
  `region` varchar(50) DEFAULT 'Africa',
  `subregion` varchar(50) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `african_countries`
--

INSERT INTO `african_countries` (`countryID`, `DateAdded`, `countryName`, `countryCode`, `countryISO3Code`, `phoneCode`, `countryCapital`, `region`, `subregion`, `isActive`, `created_at`, `updated_at`) VALUES
(1, '2025-02-01 22:26:38', 'Algeria', 'DZ', 'DZA', '213', 'Algiers', 'Africa', 'Northern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(2, '2025-02-01 22:26:38', 'Angola', 'AO', 'AGO', '244', 'Luanda', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(3, '2025-02-01 22:26:38', 'Benin', 'BJ', 'BEN', '229', 'Porto-Novo', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(4, '2025-02-01 22:26:38', 'Botswana', 'BW', 'BWA', '267', 'Gaborone', 'Africa', 'Southern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(5, '2025-02-01 22:26:38', 'Burkina Faso', 'BF', 'BFA', '226', 'Ouagadougou', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(6, '2025-02-01 22:26:38', 'Burundi', 'BI', 'BDI', '257', 'Gitega', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(7, '2025-02-01 22:26:38', 'Cameroon', 'CM', 'CMR', '237', 'Yaoundé', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(8, '2025-02-01 22:26:38', 'Cape Verde', 'CV', 'CPV', '238', 'Praia', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(9, '2025-02-01 22:26:38', 'Central African Republic', 'CF', 'CAF', '236', 'Bangui', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(10, '2025-02-01 22:26:38', 'Chad', 'TD', 'TCD', '235', 'N\'Djamena', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(11, '2025-02-01 22:26:38', 'Comoros', 'KM', 'COM', '269', 'Moroni', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(12, '2025-02-01 22:26:38', 'Congo', 'CG', 'COG', '242', 'Brazzaville', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(13, '2025-02-01 22:26:38', 'Democratic Republic of the Congo', 'CD', 'COD', '243', 'Kinshasa', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(14, '2025-02-01 22:26:38', 'Djibouti', 'DJ', 'DJI', '253', 'Djibouti', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(15, '2025-02-01 22:26:38', 'Egypt', 'EG', 'EGY', '20', 'Cairo', 'Africa', 'Northern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(16, '2025-02-01 22:26:38', 'Equatorial Guinea', 'GQ', 'GNQ', '240', 'Malabo', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(17, '2025-02-01 22:26:38', 'Eritrea', 'ER', 'ERI', '291', 'Asmara', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(18, '2025-02-01 22:26:38', 'Ethiopia', 'ET', 'ETH', '251', 'Addis Ababa', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(19, '2025-02-01 22:26:38', 'Gabon', 'GA', 'GAB', '241', 'Libreville', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(20, '2025-02-01 22:26:38', 'Gambia', 'GM', 'GMB', '220', 'Banjul', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(21, '2025-02-01 22:26:38', 'Ghana', 'GH', 'GHA', '233', 'Accra', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(22, '2025-02-01 22:26:38', 'Guinea', 'GN', 'GIN', '224', 'Conakry', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(23, '2025-02-01 22:26:38', 'Guinea-Bissau', 'GW', 'GNB', '245', 'Bissau', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(24, '2025-02-01 22:26:38', 'Ivory Coast', 'CI', 'CIV', '225', 'Yamoussoukro', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(25, '2025-02-01 22:26:38', 'Kenya', 'KE', 'KEN', '254', 'Nairobi', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(26, '2025-02-01 22:26:38', 'Lesotho', 'LS', 'LSO', '266', 'Maseru', 'Africa', 'Southern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(27, '2025-02-01 22:26:38', 'Liberia', 'LR', 'LBR', '231', 'Monrovia', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(28, '2025-02-01 22:26:38', 'Libya', 'LY', 'LBY', '218', 'Tripoli', 'Africa', 'Northern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(29, '2025-02-01 22:26:38', 'Madagascar', 'MG', 'MDG', '261', 'Antananarivo', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(30, '2025-02-01 22:26:38', 'Malawi', 'MW', 'MWI', '265', 'Lilongwe', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(31, '2025-02-01 22:26:38', 'Mali', 'ML', 'MLI', '223', 'Bamako', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(32, '2025-02-01 22:26:38', 'Mauritania', 'MR', 'MRT', '222', 'Nouakchott', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(33, '2025-02-01 22:26:38', 'Mauritius', 'MU', 'MUS', '230', 'Port Louis', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(34, '2025-02-01 22:26:38', 'Morocco', 'MA', 'MAR', '212', 'Rabat', 'Africa', 'Northern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(35, '2025-02-01 22:26:38', 'Mozambique', 'MZ', 'MOZ', '258', 'Maputo', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(36, '2025-02-01 22:26:38', 'Namibia', 'NA', 'NAM', '264', 'Windhoek', 'Africa', 'Southern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(37, '2025-02-01 22:26:38', 'Niger', 'NE', 'NER', '227', 'Niamey', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(38, '2025-02-01 22:26:38', 'Nigeria', 'NG', 'NGA', '234', 'Abuja', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(39, '2025-02-01 22:26:38', 'Rwanda', 'RW', 'RWA', '250', 'Kigali', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(40, '2025-02-01 22:26:38', 'Sao Tome and Principe', 'ST', 'STP', '239', 'São Tomé', 'Africa', 'Central Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(41, '2025-02-01 22:26:38', 'Senegal', 'SN', 'SEN', '221', 'Dakar', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(42, '2025-02-01 22:26:38', 'Seychelles', 'SC', 'SYC', '248', 'Victoria', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(43, '2025-02-01 22:26:38', 'Sierra Leone', 'SL', 'SLE', '232', 'Freetown', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(44, '2025-02-01 22:26:38', 'Somalia', 'SO', 'SOM', '252', 'Mogadishu', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(45, '2025-02-01 22:26:38', 'South Africa', 'ZA', 'ZAF', '27', 'Pretoria', 'Africa', 'Southern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(46, '2025-02-01 22:26:38', 'South Sudan', 'SS', 'SSD', '211', 'Juba', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(47, '2025-02-01 22:26:38', 'Sudan', 'SD', 'SDN', '249', 'Khartoum', 'Africa', 'Northern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(48, '2025-02-01 22:26:38', 'Swaziland', 'SZ', 'SWZ', '268', 'Mbabane', 'Africa', 'Southern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(49, '2025-02-01 22:26:38', 'Tanzania', 'TZ', 'TZA', '255', 'Dodoma', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(50, '2025-02-01 22:26:38', 'Togo', 'TG', 'TGO', '228', 'Lomé', 'Africa', 'Western Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(51, '2025-02-01 22:26:38', 'Tunisia', 'TN', 'TUN', '216', 'Tunis', 'Africa', 'Northern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(52, '2025-02-01 22:26:38', 'Uganda', 'UG', 'UGA', '256', 'Kampala', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(53, '2025-02-01 22:26:38', 'Zambia', 'ZM', 'ZMB', '260', 'Lusaka', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47'),
(54, '2025-02-01 22:26:38', 'Zimbabwe', 'ZW', 'ZWE', '263', 'Harare', 'Africa', 'Eastern Africa', 1, '2025-02-01 19:19:47', '2025-02-01 19:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `client_relationship_assignments`
--

CREATE TABLE `client_relationship_assignments` (
  `clientRelationshipID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `clientID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `clientRelationshipType` enum('clientLiaisonPartner','engagementPartner','manager','AssociateSeniorAssociate','associateIntern') NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `client_relationship_assignments`
--

INSERT INTO `client_relationship_assignments` (`clientRelationshipID`, `DateAdded`, `clientID`, `employeeID`, `clientRelationshipType`, `startDate`, `endDate`, `notes`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-02 13:19:31', 1, 3, 'engagementPartner', '0000-00-00', NULL, NULL, '2025-11-02 13:19:31', 3, 'N', 'N'),
(2, '2025-11-27 06:44:11', 1, 22, 'engagementPartner', '0000-00-00', NULL, NULL, '2025-11-27 14:44:11', 22, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE `currency` (
  `currencyID` int(11) NOT NULL,
  `NAME` varchar(20) DEFAULT NULL,
  `CODE` varchar(3) DEFAULT NULL,
  `symbol` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`currencyID`, `NAME`, `CODE`, `symbol`) VALUES
(1, 'Leke', 'ALL', 'Lek'),
(2, 'Dollars', 'USD', '$'),
(3, 'Afghanis', 'AFN', '؋'),
(4, 'Pesos', 'ARS', '$'),
(5, 'Guilders', 'AWG', 'ƒ'),
(6, 'Dollars', 'AUD', '$'),
(7, 'New Manats', 'AZN', 'ман'),
(8, 'Dollars', 'BSD', '$'),
(9, 'Dollars', 'BBD', '$'),
(10, 'Rubles', 'BYR', 'p.'),
(11, 'Euro', 'EUR', '€'),
(12, 'Dollars', 'BZD', 'BZ$'),
(13, 'Dollars', 'BMD', '$'),
(14, 'Bolivianos', 'BOB', '$b'),
(15, 'Convertible Marka', 'BAM', 'KM'),
(16, 'Pula', 'BWP', 'P'),
(17, 'Leva', 'BGN', 'лв'),
(18, 'Reais', 'BRL', 'R$'),
(19, 'Pounds', 'GBP', '£'),
(20, 'Dollars', 'BND', '$'),
(21, 'Riels', 'KHR', '៛'),
(22, 'Dollars', 'CAD', '$'),
(23, 'Dollars', 'KYD', '$'),
(24, 'Pesos', 'CLP', '$'),
(25, 'Yuan Renminbi', 'CNY', '¥'),
(26, 'Pesos', 'COP', '$'),
(27, 'Colón', 'CRC', '₡'),
(28, 'Kuna', 'HRK', 'kn'),
(29, 'Pesos', 'CUP', '₱'),
(30, 'Koruny', 'CZK', 'Kč'),
(31, 'Kroner', 'DKK', 'kr'),
(32, 'Pesos', 'DOP', 'RD$'),
(33, 'Dollars', 'XCD', '$'),
(34, 'Pounds', 'EGP', '£'),
(35, 'Colones', 'SVC', '$'),
(36, 'Pounds', 'FKP', '£'),
(37, 'Dollars', 'FJD', '$'),
(38, 'Cedis', 'GHC', '¢'),
(39, 'Pounds', 'GIP', '£'),
(40, 'Quetzales', 'GTQ', 'Q'),
(41, 'Pounds', 'GGP', '£'),
(42, 'Dollars', 'GYD', '$'),
(43, 'Lempiras', 'HNL', 'L'),
(44, 'Dollars', 'HKD', '$'),
(45, 'Forint', 'HUF', 'Ft'),
(46, 'Kronur', 'ISK', 'kr'),
(47, 'Rupees', 'INR', 'Rp'),
(48, 'Rupiahs', 'IDR', 'Rp'),
(49, 'Rials', 'IRR', '﷼'),
(50, 'Pounds', 'IMP', '£'),
(51, 'New Shekels', 'ILS', '₪'),
(52, 'Dollars', 'JMD', 'J$'),
(53, 'Yen', 'JPY', '¥'),
(54, 'Pounds', 'JEP', '£'),
(55, 'Tenge', 'KZT', 'лв'),
(56, 'Won', 'KPW', '₩'),
(57, 'Won', 'KRW', '₩'),
(58, 'Soms', 'KGS', 'лв'),
(59, 'Kips', 'LAK', '₭'),
(60, 'Lati', 'LVL', 'Ls'),
(61, 'Pounds', 'LBP', '£'),
(62, 'Dollars', 'LRD', '$'),
(63, 'Switzerland Francs', 'CHF', 'CHF'),
(64, 'Litai', 'LTL', 'Lt'),
(65, 'Denars', 'MKD', 'ден'),
(66, 'Ringgits', 'MYR', 'RM'),
(67, 'Rupees', 'MUR', '₨'),
(68, 'Pesos', 'MXN', '$'),
(69, 'Tugriks', 'MNT', '₮'),
(70, 'Meticais', 'MZN', 'MT'),
(71, 'Dollars', 'NAD', '$'),
(72, 'Rupees', 'NPR', '₨'),
(73, 'Guilders', 'ANG', 'ƒ'),
(74, 'Dollars', 'NZD', '$'),
(75, 'Cordobas', 'NIO', 'C$'),
(76, 'Nairas', 'NGN', '₦'),
(77, 'Krone', 'NOK', 'kr'),
(78, 'Rials', 'OMR', '﷼'),
(79, 'Rupees', 'PKR', '₨'),
(80, 'Balboa', 'PAB', 'B/.'),
(81, 'Guarani', 'PYG', 'Gs'),
(82, 'Nuevos Soles', 'PEN', 'S/.'),
(83, 'Pesos', 'PHP', 'Php'),
(84, 'Zlotych', 'PLN', 'zł'),
(85, 'Rials', 'QAR', '﷼'),
(86, 'New Lei', 'RON', 'lei'),
(87, 'Rubles', 'RUB', 'руб'),
(88, 'Pounds', 'SHP', '£'),
(89, 'Riyals', 'SAR', '﷼'),
(90, 'Dinars', 'RSD', 'Дин.'),
(91, 'Rupees', 'SCR', '₨'),
(92, 'Dollars', 'SGD', '$'),
(93, 'Dollars', 'SBD', '$'),
(94, 'Shillings', 'SOS', 'S'),
(95, 'Rand', 'ZAR', 'R'),
(96, 'Rupees', 'LKR', '₨'),
(97, 'Kronor', 'SEK', 'kr'),
(98, 'Dollars', 'SRD', '$'),
(99, 'Pounds', 'SYP', '£'),
(100, 'New Dollars', 'TWD', 'NT$'),
(101, 'Baht', 'THB', '฿'),
(102, 'Dollars', 'TTD', 'TT$'),
(103, 'Lira', 'TRY', '₺'),
(104, 'Liras', 'TRL', '£'),
(105, 'Dollars', 'TVD', '$'),
(106, 'Hryvnia', 'UAH', '₴'),
(107, 'Pesos', 'UYU', '$U'),
(108, 'Sums', 'UZS', 'лв'),
(109, 'Bolivares Fuertes', 'VEF', 'Bs'),
(110, 'Dong', 'VND', '₫'),
(111, 'Rials', 'YER', '﷼'),
(112, 'Zimbabwe Dollars', 'ZWD', 'Z$'),
(113, 'Rupees', 'INR', '₹');

-- --------------------------------------------------------

--
-- Table structure for table `industry_sectors`
--

CREATE TABLE `industry_sectors` (
  `industrySectorID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `industryTitle` varchar(180) NOT NULL,
  `industryCategory` varchar(180) NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `industry_sectors`
--

INSERT INTO `industry_sectors` (`industrySectorID`, `DateAdded`, `industryTitle`, `industryCategory`, `Suspended`) VALUES
(1, '2021-03-06 15:42:46', 'Accounting', '', 'N'),
(2, '2021-03-06 15:42:46', 'Airlines/Aviation', '', 'N'),
(3, '2021-03-06 15:42:46', 'Alternative Dispute Resolution', '', 'N'),
(4, '2021-03-06 15:42:46', 'Alternative Medicine', '', 'N'),
(5, '2021-03-06 15:42:46', 'Animation', '', 'N'),
(6, '2021-03-06 15:42:46', 'Apparel/Fashion', '', 'N'),
(7, '2021-03-06 15:42:46', 'Architecture/Planning', '', 'N'),
(8, '2021-03-06 15:42:46', 'Arts/Crafts', '', 'N'),
(9, '2021-03-06 15:42:46', 'Automotive', '', 'N'),
(10, '2021-03-06 15:42:46', 'Aviation/Aerospace', '', 'N'),
(11, '2021-03-06 15:42:46', 'Banking/Mortgage', '', 'N'),
(12, '2021-03-06 15:42:46', 'Biotechnology/Greentech', '', 'N'),
(13, '2021-03-06 15:42:46', 'Broadcast Media', '', 'N'),
(14, '2021-03-06 15:42:46', 'Building Materials', '', 'N'),
(15, '2021-03-06 15:42:46', 'Business Supplies/Equipment', '', 'N'),
(16, '2021-03-06 15:42:46', 'Capital Markets/Hedge Fund/Private Equit', '', 'N'),
(17, '2021-03-06 15:42:46', 'Chemicals', '', 'N'),
(18, '2021-03-06 15:42:46', 'Civic/Social Organization', '', 'N'),
(19, '2021-03-06 15:42:46', 'Civil Engineering', '', 'N'),
(20, '2021-03-06 15:42:46', 'Commercial Real Estate', '', 'N'),
(21, '2021-03-06 15:42:46', 'Computer Games', '', 'N'),
(22, '2021-03-06 15:42:46', 'Computer Hardware', '', 'N'),
(23, '2021-03-06 15:42:46', 'Computer Networking', '', 'N'),
(24, '2021-03-06 15:42:46', 'Computer Software/Engineering', '', 'N'),
(25, '2021-03-06 15:42:46', 'Computer/Network Security', '', 'N'),
(26, '2021-03-06 15:42:46', 'Construction', '', 'N'),
(27, '2021-03-06 15:42:46', 'Consumer Electronics', '', 'N'),
(28, '2021-03-06 15:42:46', 'Consumer Goods', '', 'N'),
(29, '2021-03-06 15:42:46', 'Consumer Services', '', 'N'),
(30, '2021-03-06 15:42:46', 'Cosmetics', '', 'N'),
(31, '2021-03-06 15:42:46', 'Dairy', '', 'N'),
(32, '2021-03-06 15:42:46', 'Defense/Space', '', 'N'),
(33, '2021-03-06 15:42:46', 'Design', '', 'N'),
(34, '2021-03-06 15:42:46', 'E-Learning', '', 'N'),
(35, '2021-03-06 15:42:46', 'Education Management', '', 'N'),
(36, '2021-03-06 15:42:46', 'Electrical/Electronic Manufacturing', '', 'N'),
(37, '2021-03-06 15:42:46', 'Entertainment/Movie Production', '', 'N'),
(38, '2021-03-06 15:42:46', 'Environmental Services', '', 'N'),
(39, '2021-03-06 15:42:46', 'Events Service', '', 'N'),
(40, '2021-03-06 15:42:46', 'Executive Office', '', 'N'),
(41, '2021-03-06 15:42:46', 'Farming', '', 'N'),
(42, '2021-03-06 15:42:46', 'Financial Services', '', 'N'),
(43, '2021-03-06 15:42:46', 'Fine Art', '', 'N'),
(44, '2021-03-06 15:42:46', 'Fishery', '', 'N'),
(45, '2021-03-06 15:42:46', 'Food Production', '', 'N'),
(46, '2021-03-06 15:42:46', 'Food/Beverages', '', 'N'),
(47, '2021-03-06 15:42:46', 'Fundraising', '', 'N'),
(48, '2021-03-06 15:42:46', 'Furniture', '', 'N'),
(49, '2021-03-06 15:42:46', 'Gambling/Casinos', '', 'N'),
(50, '2021-03-06 15:42:46', 'Glass/Ceramics/Concrete', '', 'N'),
(51, '2021-03-06 15:42:46', 'Government Administration', '', 'N'),
(52, '2021-03-06 15:42:46', 'Government Relations', '', 'N'),
(53, '2021-03-06 15:42:46', 'Graphic Design/Web Design', '', 'N'),
(54, '2021-03-06 15:42:46', 'Health/Fitness', '', 'N'),
(55, '2021-03-06 15:42:46', 'Higher Education/Acadamia', '', 'N'),
(56, '2021-03-06 15:42:46', 'Hospital/Health Care', '', 'N'),
(57, '2021-03-06 15:42:46', 'Hospitality', '', 'N'),
(58, '2021-03-06 15:42:46', 'Human Resources/HR', '', 'N'),
(59, '2021-03-06 15:42:46', 'Import/Export', '', 'N'),
(60, '2021-03-06 15:42:46', 'Individual/Family Services', '', 'N'),
(61, '2021-03-06 15:42:46', 'Industrial Automation', '', 'N'),
(62, '2021-03-06 15:42:46', 'Information Services', '', 'N'),
(63, '2021-03-06 15:42:46', 'Information Technology/IT', '', 'N'),
(64, '2021-03-06 15:42:46', 'Insurance', '', 'N'),
(65, '2021-03-06 15:42:46', 'International Affairs', '', 'N'),
(66, '2021-03-06 15:42:46', 'International Trade/Development', '', 'N'),
(67, '2021-03-06 15:42:46', 'Internet', '', 'N'),
(68, '2021-03-06 15:42:46', 'Investment Banking/Venture', '', 'N'),
(69, '2021-03-06 15:42:46', 'Investment Management/Hedge Fund/Private Equity', '', 'N'),
(70, '2021-03-06 15:42:46', 'Judiciary', '', 'N'),
(71, '2021-03-06 15:42:46', 'Law Enforcement', '', 'N'),
(72, '2021-03-06 15:42:46', 'Law Practice/Law Firms', '', 'N'),
(73, '2021-03-06 15:42:46', 'Legal Services', '', 'N'),
(74, '2021-03-06 15:42:46', 'Legislative Office', '', 'N'),
(75, '2021-03-06 15:42:46', 'Leisure/Travel', '', 'N'),
(76, '2021-03-06 15:42:46', 'Library', '', 'N'),
(77, '2021-03-06 15:42:46', 'Logistics/Procurement', '', 'N'),
(78, '2021-03-06 15:42:46', 'Luxury Goods/Jewelry', '', 'N'),
(79, '2021-03-06 15:42:46', 'Machinery', '', 'N'),
(80, '2021-03-06 15:42:46', 'Management Consulting', '', 'N'),
(81, '2021-03-06 15:42:46', 'Maritime', '', 'N'),
(82, '2021-03-06 15:42:46', 'Market Research', '', 'N'),
(83, '2021-03-06 15:42:46', 'Marketing/Advertising/Sales', '', 'N'),
(84, '2021-03-06 15:42:46', 'Mechanical or Industrial Engineering', '', 'N'),
(85, '2021-03-06 15:42:46', 'Media Production', '', 'N'),
(86, '2021-03-06 15:42:46', 'Medical Equipment', '', 'N'),
(87, '2021-03-06 15:42:46', 'Medical Practice', '', 'N'),
(88, '2021-03-06 15:42:46', 'Mental Health Care', '', 'N'),
(89, '2021-03-06 15:42:46', 'Military Industry', '', 'N'),
(90, '2021-03-06 15:42:46', 'Mining/Metals', '', 'N'),
(91, '2021-03-06 15:42:46', 'Motion Pictures/Film', '', 'N'),
(92, '2021-03-06 15:42:46', 'Museums/Institutions', '', 'N'),
(93, '2021-03-06 15:42:46', 'Music', '', 'N'),
(94, '2021-03-06 15:42:46', 'Nanotechnology', '', 'N'),
(95, '2021-03-06 15:42:46', 'Newspapers/Journalism', '', 'N'),
(96, '2021-03-06 15:42:46', 'Non-Profit/Volunteering', '', 'N'),
(97, '2021-03-06 15:42:46', 'Oil/Energy/Solar/Greentech', '', 'N'),
(98, '2021-03-06 15:42:46', 'Online Publishing', '', 'N'),
(99, '2021-03-06 15:42:46', 'Other Industry', '', 'N'),
(100, '2021-03-06 15:42:46', 'Outsourcing/Offshoring', '', 'N'),
(101, '2021-03-06 15:42:46', 'Package/Freight Delivery', '', 'N'),
(102, '2021-03-06 15:42:46', 'Packaging/Containers', '', 'N'),
(103, '2021-03-06 15:42:46', 'Paper/Forest Products', '', 'N'),
(104, '2021-03-06 15:42:46', 'Performing Arts', '', 'N'),
(105, '2021-03-06 15:42:46', 'Pharmaceuticals', '', 'N'),
(106, '2021-03-06 15:42:46', 'Philanthropy', '', 'N'),
(107, '2021-03-06 15:42:46', 'Photography', '', 'N'),
(108, '2021-03-06 15:42:46', 'Plastics', '', 'N'),
(109, '2021-03-06 15:42:46', 'Political Organization', '', 'N'),
(110, '2021-03-06 15:42:46', 'Primary/Secondary Education', '', 'N'),
(112, '2021-03-06 15:42:46', 'Printing', '', 'N'),
(113, '2021-03-06 15:42:46', 'Professional Training', '', 'N'),
(114, '2021-03-06 15:42:46', 'Program Development', '', 'N'),
(115, '2021-03-06 15:42:46', 'Public Relations/PR', '', 'N'),
(116, '2021-03-06 15:42:46', 'Public Safety', '', 'N'),
(117, '2021-03-06 15:42:46', 'Publishing Industry', '', 'N'),
(118, '2021-03-06 15:42:46', 'Railroad Manufacture', '', 'N'),
(119, '2021-03-06 15:42:46', 'Ranching', '', 'N'),
(120, '2021-03-06 15:42:46', 'Real Estate/Mortgage', '', 'N'),
(121, '2021-03-06 15:42:46', 'Recreational Facilities/Services', '', 'N'),
(122, '2021-03-06 15:42:46', 'Religious Institutions', '', 'N'),
(123, '2021-03-06 15:42:46', 'Renewables/Environment', '', 'N'),
(124, '2021-03-06 15:42:46', 'Research Industry', '', 'N'),
(125, '2021-03-06 15:42:46', 'Restaurants', '', 'N'),
(126, '2021-03-06 15:42:46', 'Retail Industry', '', 'N'),
(127, '2021-03-06 15:42:46', 'Security/Investigations', '', 'N'),
(128, '2021-03-06 15:42:46', 'Semiconductors', '', 'N'),
(129, '2021-03-06 15:42:46', 'Shipbuilding', '', 'N'),
(130, '2021-03-06 15:42:46', '>Sporting Goods', '', 'N'),
(131, '2021-03-06 15:42:46', 'Sports', '', 'N'),
(132, '2021-03-06 15:42:46', 'Staffing/Recruiting', '', 'N'),
(133, '2021-03-06 15:42:46', 'Supermarkets', '', 'N'),
(134, '2021-03-06 15:42:46', 'Telecommunications', '', 'N'),
(135, '2021-03-06 15:42:46', 'Textiles', '', 'N'),
(136, '2021-03-06 15:42:46', 'Think Tanks', '', 'N'),
(137, '2021-03-06 15:42:46', 'Tobacco', '', 'N'),
(138, '2021-03-06 15:42:46', 'Translation/Localization', '', 'N'),
(139, '2021-03-06 15:42:46', 'Transportation', '', 'N'),
(140, '2021-03-06 15:42:46', 'Utilities', '', 'N'),
(141, '2021-03-06 15:42:46', 'Venture Capital/VC', '', 'N'),
(142, '2021-03-06 15:42:46', 'Veterinary', '', 'N'),
(143, '2021-03-06 15:42:46', 'Warehousing', '', 'N'),
(144, '2021-03-06 15:42:46', 'Wholesale', '', 'N'),
(145, '2021-03-06 15:42:46', 'Wine/Spirits', '', 'N'),
(146, '2021-03-06 15:42:46', 'Wireless', '', 'N'),
(147, '2021-03-06 15:42:46', 'Writing/Editing', '', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `login_sessions`
--

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
(1, '72c53756dd7098c067c7d7f4a467827f5a11f62acfd7dc07d1e3bed077ef4ec0', '0ca27ab01ebec234a96676d368f3396a', 1, '2025-11-21 06:52:15', '2025-11-21 12:58:05', NULL),
(2, '61d681f88ae05fbc3a2ff8eb2f6653805290861c7b2fc85cf0608279e3ae5f54', '5054fabac77c345d64ad9fa55c1f0549', 4, '2025-11-21 11:26:45', '2025-11-21 12:58:05', '2025-11-21 13:06:25'),
(3, '23fe21eca55f5f83181a1d88c23e916cdf6055531b9a59d9002642f86db9d414', '228a853d81649a3d190b9a7a0ebf03a2', 4, '2025-11-21 13:06:25', '2025-11-22 07:16:01', '2025-11-22 07:28:31'),
(4, 'c44ee8cb1c88df52bace8d1563f3239fe1980464e40a8bba937a1a0b26b8a8c6', 'e3c833070f140dd4428b7e37ffef1197', 4, '2025-11-22 07:28:31', '2025-11-22 11:41:21', '2025-11-22 11:57:42'),
(5, '56f7a2a087d9e9c03c0c67d6060147b92b4dec9be3410a1b5e790d36ab0a2c64', '70583630627719b351df2f425f469ac7', 4, '2025-11-22 11:57:42', '2025-11-22 15:45:12', '2025-11-22 15:45:12'),
(6, 'f681b4040ae2f43dfda6e79180685305e4430992f2f99e3665f4484e279d370a', '9c248469df0e997cc6776161bcad439b', 4, '2025-11-22 15:45:30', '2025-11-24 11:11:21', '2025-11-24 11:22:27'),
(7, '5722ffcf481e1af5137252b12633a77603bcc4b239e77bea725842fd52d69a3b', '9484c6bb6909448070341786d9e449e9', 4, '2025-11-24 11:22:27', '2025-11-24 11:22:33', '2025-11-24 11:22:33'),
(8, 'bde8d8e72ae142164f7cd7ab0d45ea13893d8c5456d8e7f38c9df61d62b13bb8', '1ae9c58f7900187b211944cc8be5feae', 4, '2025-11-24 11:22:36', '2025-11-24 16:44:54', '2025-11-24 16:57:40'),
(9, '3bebd0d3329eb48044f451c2e8f2228ccfa6e4a55735261490592202370e160d', 'e61a0769f650ebc69d907ad644d68899', 24, '2025-11-24 14:43:21', '2025-11-24 16:43:53', '2025-11-25 10:22:46'),
(10, '24ef50ec29a0569e323be2333a4eca20bd99a209984165f1d89b1e2cc5bee730', '63528bc7728b2e2f8ac71396dce8ec99', 23, '2025-11-24 16:43:57', '2025-11-24 16:45:28', '2025-11-24 17:08:41'),
(11, '59f9cb80e85703bc7530ed985d58fe40ae1b994c9e077e7baea1aea303b5905d', '333d3067cfd69e62268d4ec154cb27fe', 4, '2025-11-24 16:57:40', '2025-11-25 05:08:09', '2025-11-25 06:46:04'),
(12, '62749a2c2bdd33317b5fe26271013d9b19776ea70dea69e82f2fc37668cd5766', 'ba8437e33f4525295a943ed5a3a42526', 23, '2025-11-24 17:08:41', '2025-11-25 05:07:57', '2025-11-24 18:46:23'),
(13, '058d79c39c48dbadac3690bb1817286f21bfdfdb034ea4653d889cd31f4076dd', '46e36ef46093a3b594c31aae69f72b22', 23, '2025-11-24 18:46:23', '2025-11-25 05:07:09', '2025-12-01 08:22:39'),
(14, '531b41c7b682ddeb85f40a4f41b4d0ab80b55c9ce591d26d754c9444e8939102', '6472825a448c68812a8a8f93443f22c9', 4, '2025-11-25 06:46:04', '2025-11-25 06:52:49', '2025-11-25 07:50:51'),
(15, 'a1d4b3b48bc7562b5c153e5b4b0eaacec6fac0af468d4b9f560d9d4fb1567207', '71856ffaf423106bb4c9d49ce18f0fc1', 4, '2025-11-25 07:50:51', '2025-11-25 08:10:56', '2025-11-25 10:21:39'),
(16, 'e34bdc4d04d75a076898f566d225b0cd4c99754f81ee97502c759cc37bcbffc8', '70745193cb107d18011560ec81d4b965', 13, '2025-11-25 08:29:06', '2025-11-25 12:09:01', NULL),
(17, '404c5919c26e6b74e3200daedf17775851d95629fb479b561df79a105613c544', '59a6e19864329c59f3321b54282f16dd', 16, '2025-11-25 08:37:14', '2025-11-25 08:52:12', NULL),
(18, 'ced2a744dfe46c3c6e164f5c301f53d5f2c91c07ba099fb56fbc7fe82aea901e', 'ed5cb6834c337eb9b0a77d28154ed716', 3, '2025-11-25 10:01:24', '2025-11-25 10:21:05', NULL),
(19, 'b014fcb54e846a809ecc9277465873ef01418c7c9c1f10e5a2b18e79edd63f86', '894b8e667be38f581f9f760931ef295e', 4, '2025-11-25 10:21:39', '2025-11-25 10:25:06', '2025-11-25 10:40:35'),
(20, '46ea0e07500d69639d6d1f8d768c0e6446c5e367b3f2823bcbf50ad3566d8594', 'bc5c5f76604e16446d45770c3bd9f152', 24, '2025-11-25 10:22:46', '2025-11-25 13:49:13', '2025-12-01 08:18:39'),
(21, '94a44b0e288a0f1d01148d8a8712aa1e5a1d9ac807381704fe91389ba82b131d', '42b3cae9c2af23fb94688e4800d8c789', 4, '2025-11-25 10:40:35', '2025-11-25 13:49:13', '2025-11-25 11:33:32'),
(22, 'b8cbd28960bd17c56220b76421f9b7b7434a7f00db649d4b039d4c21bd34a48b', '7324b319e8bf8ce4e18fd6a9bb2315f0', 4, '2025-11-25 11:33:32', '2025-11-25 11:38:04', '2025-11-26 05:11:52'),
(23, 'a9143ac78a1d097422f552d834fa63e5e3e58b6369ad07d4b0a7ccae3459cabf', '618f56ba4a4d485c9050b3635386d060', 4, '2025-11-26 05:11:52', '2025-11-26 09:12:26', '2025-11-27 08:11:10'),
(24, '76b712a431fc7176a6fa44ed62fab968d50b84bdf6c55ffd31641959756b1623', '87de872de2b093e7794fb8aad1ae6613', 6, '2025-11-26 09:13:24', '2025-11-26 09:38:09', NULL),
(25, 'a5e557372ae66f68e045feea1a29dc8bfc2d8d0aae65bc6603f15d67076a1e79', '2ae42b85704ca6f44734b4bc3ab1e069', 9, '2025-11-27 06:28:26', '2025-11-27 07:56:31', NULL),
(26, '32b9e33161f28bbf21d616cdd94b2322e4c3abe2b8c899cf11621284bb11f076', '5ad29ae8478f17d020b75ded37a42c7e', 4, '2025-11-27 08:11:10', '2025-11-27 10:14:16', '2025-11-30 13:52:35'),
(27, '04d0c8c44d56a0e6c2aa40a4dd75b3558c8cd05502788d8da6d398828d5f46d1', '09dabee3bf0e888dda28448ba492893c', 22, '2025-11-27 09:49:01', '2025-11-27 09:55:04', '2025-11-27 11:41:05'),
(28, '1339bbdd102d812dfc8224c95b05bb55805a341e66e93ed4cb25f106d054fb1c', 'cc896c15a54163189b97c0845f5df37d', 22, '2025-11-27 11:41:05', '2025-11-27 13:15:51', NULL),
(29, 'f19f1e612d3e1c191ccd2f3da89ebc573951c8307473fabc084e32fd5cdaf98b', '1a01a203f2934296b7cb092222f4b132', 15, '2025-11-29 08:03:35', '2025-11-29 08:27:38', NULL),
(30, 'cea12474d441200612c91ce1fd1a3766ffbe9980d368bd7d690e5ff90058f25e', '59af5b28f662c333d3bb23f21bdbd0dc', 4, '2025-11-30 13:52:35', '2025-11-30 14:45:55', '2025-12-01 08:15:13'),
(31, '53c037a4386265d239fcda6bf4d422a9bbfb2af507162917f52fa21f9d30bb22', '5042cb64effc990709f2414358b3395d', 14, '2025-12-01 06:15:53', '2025-12-01 07:31:31', NULL),
(32, '64b657f7dc10e268789e37751c2391a9148ef9e020b404ec2cbd11298c9c0c21', '79cb9cce80d88851ca1b5412bebbeae8', 2, '2025-12-01 06:44:58', '2025-12-01 06:51:11', NULL),
(33, '477211f937e6a5522748c1e44b5916ea9913e4a516e3f2ad5ff0ace665824c11', '343e814ca99b38e0d8aa4998d5d1624a', 4, '2025-12-01 08:15:13', '2025-12-01 10:13:38', '2025-12-01 10:17:24'),
(34, '90bfb4d1ebf5d2f0f1c4fb4b9ebfc520c31f26080cd0000f058d9de9e4a8f121', 'cf7b815115063f9f1530ab99ac0e83c5', 24, '2025-12-01 08:18:39', '2025-12-01 10:10:58', '2025-12-01 10:10:58'),
(35, '5a6436c7b24933a5f67030fd6ec2e3a719e93f47182d7a84f244041903b3f495', '27ba922efc11a4a6b22294d05a3d1ae7', 23, '2025-12-01 08:22:39', '2025-12-01 10:13:38', '2025-12-01 10:18:22'),
(36, 'c8cf1b1eb503c0ab1c1f98ecab204639f0a1923a96ad51e3cce49f40ea22844b', 'c7add5a84287c298916aa245f059e392', 4, '2025-12-01 10:17:24', '2025-12-01 11:22:31', '2025-12-01 11:36:42'),
(37, 'c945128081c4bbc762a3d7b7e57e3eb58953c42ab0db2e3a990a12e2e3dd07ec', '22ea36c94b8aa901772909238828f9be', 23, '2025-12-01 10:18:22', '2025-12-01 11:24:02', '2025-12-01 11:37:45'),
(38, '202a62ef6a8b0af2e29c12f775e1e771c60acdc489d8fbb39ae1b189674c6e04', '79c8a91ef46bd990d9dfb2f65f91c1a6', 24, '2025-12-01 10:19:23', '2025-12-01 11:23:31', NULL),
(39, 'a527c4c214c75f2a414bd4caca1089a7421f691ca44ec7cf2cadc9924dc3ba72', 'c68ceae6d08204070fbd86eb723affca', 4, '2025-12-01 11:36:42', '2025-12-01 11:37:34', '2025-12-01 11:37:34'),
(40, '4c7c1471197fa46ea87872002a8bf0236a395cd025086354810b6d61fd02d6b2', '67803722b53f633e4022f99370d1f5b4', 23, '2025-12-01 11:37:45', '2025-12-01 15:38:32', NULL),
(41, '29a5c6a67d1fe2ead08880c158c0e768aa58c75b555b26e05e260c6116c30d93', '32813e86dbe5928785a11c065dbff583', 4, '2025-12-01 13:50:40', '2025-12-01 13:55:16', '2025-12-06 13:45:56'),
(42, '7e6ef98e5395eb80b198f283249116ed8d8457bdb087e74e3148120d9cd7962a', '7a3e56c1bff2bde4f8b456bc2ad7a228', 4, '2025-12-06 13:45:56', '2025-12-06 14:08:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

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
  `isEmployee` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`ID`, `DateAdded`, `FirstName`, `Surname`, `OtherNames`, `userInitials`, `Email`, `profile_image`, `Password`, `NeedsToChangePassword`, `Valid`, `active`, `LastUpdateByID`, `isEmployee`) VALUES
(1, '2023-03-12 16:56:54', 'System', 'Administrator', NULL, '', 'support@sbsl.co.ke', 'employee_profile/1756735549_4.jpg', '$6$rounds=1024$1063359921$mAbT9hkQ9Eazp16ULeuWdqSIxiyY5cR6zzo0.EwofatNwZybPCuODvERRpTuDowDH9DOOLDTb7/CZjkYCNAla.', 'n', 'y', 'N', NULL, 'N'),
(2, '2025-11-21 09:59:36', 'Brian', 'Nyongesa', 'Julius', '', 'brian@sbsl.co.ke', NULL, '$6$rounds=1024$3000301$KriahSGMq7nrCEVWRP72FloSVgR/PMxFvS8BCrHJgnLPL3nMcrHGJDRefqwZm9wFjhva0KxPa855vFgOwQWlS1', 'n', 'y', 'N', NULL, 'Y'),
(3, '2025-11-21 10:01:12', 'Dennis', 'Wabukala', '', 'DW', 'dennis@sbsl.co.ke', NULL, '$6$rounds=1024$1943475369$W77hb5/DaO1ypVOFQTx7MYABH7/.T8FKWmqX.fWFPxJMyBiZTY642aIH4B/QaJU3kziHWq80/vA/NpwxhWaEt1', 'n', 'y', 'N', NULL, 'Y'),
(4, '2025-11-21 11:21:15', 'Felix', 'Mauncho', '', 'FM', 'felix.mauncho@sbsl.co.ke', NULL, '$6$rounds=1024$1780660677$O6N.9LBiVhheXpSS6CYWYnZkiNpfDzPUV1FP1XremJSS7fV1pATViK0Iu.x3pBYHXUZnG2Gj66mbCqrC6kb1n1', 'n', 'y', 'N', NULL, 'Y'),
(5, '2025-11-21 11:23:46', 'Brown', 'Ndiewo', '', 'SBS', 'brown@sbsl.co.ke', NULL, '$6$rounds=1024$241555554$n15.79n9wW2JfpgPPrOhtLDXWfIajA23n7onUM/C34banzljm/meAcmKGXo0Fkls/k6XW5VCgLYydwbBN0GmC.', 'n', 'y', 'N', NULL, 'Y'),
(6, '2025-11-21 11:25:20', 'Marleeen', 'Kwamboka', '', 'MK', 'marleen.kwamboka@sbsl.co.ke', NULL, '$6$rounds=1024$881121838$QyxOBG9RAwa/ZVqqe.s.S915A2wFoIw7lmdw6Cb.iponUImrmc039MmX5bMKnp1uDdZaM7VGuEojklvokLRDT.', 'n', 'y', 'N', NULL, 'Y'),
(7, '2025-11-21 11:30:05', 'Amos', 'Kiritu', '', 'AK', 'amos.kiritu@sbsl.co.ke', NULL, '$6$rounds=1024$1361192382$uSiufShuHgJ6O.YP4LeIaPPRJ.KIIyDiBjfdU87FFL6/rmlqeX.ZrIKAmdGRADjzb355doPXBZBNAYM5Syjah0', 'n', 'y', 'N', NULL, 'Y'),
(8, '2025-11-21 11:31:54', 'Edwin', 'Masai', '', 'EM', 'edwin.masai@sbsl.co.ke', NULL, '$6$rounds=1024$149459685$aJQb8dMAcXndYw4qyWNPojSRsB4Di02J.doNHAD0EPbKsgvywfRoGeHBG9kfjgTf2PSZ.abiTppy1IguBwy2H0', 'n', 'y', 'N', NULL, 'Y'),
(9, '2025-11-21 11:42:45', 'Francis', 'Lelei', '', 'FL', 'francis.lelei@sbsl.co.ke', NULL, '$6$rounds=1024$735690526$Gh5zyJuWqg9IxfouNdpjRI6Qt./pNGo6Q.lhf7CsL1d.IGCOKxzygSbcZ2eS13lqjMt2YgL1Lqdhh0.aADkST/', 'n', 'y', 'N', NULL, 'Y'),
(10, '2025-11-21 11:45:25', 'Eddah', 'Jelimo', '', 'EJ', 'eddah.jelimo@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(11, '2025-11-21 11:49:53', 'Brenda', 'Wambua', '', 'BW', 'brenda.wambua@sbsl.co.ke', NULL, '$6$rounds=1024$1609797041$T64AO.pzc5AwbA/1lEy8nA6UrnmOA8uGuhIc6OBPqP6B8rF64cCBcrYPvEPqNScMWimY9oK9BKOzxN8LgilO51', 'n', 'y', 'N', NULL, 'Y'),
(12, '2025-11-21 11:51:24', 'Emmanuel', 'Kelechi', '', 'EK', 'irene.muthoni@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(13, '2025-11-21 11:56:24', 'Anita', 'Wanjiru', '', '', 'anita.wanjiru@sbsl.co.ke', 'employee_profile/1764008119_Screenshot_2025-07-01_232448.png', '$6$rounds=1024$47088302$LmweN.wdzwsnjYyF9.hUyOMEL.cPIVHRgtUzYWVdb.QyI8ofey4zX7ZAuObrbnSJZZmi/SqQxGZf.LPi4DwW10', 'n', 'y', 'N', NULL, 'Y'),
(14, '2025-11-21 12:00:07', 'Kibwana', 'Jerumani', '', 'JK', 'jerumani.kibwana@sbsl.co.ke', 'employee_profile/1763968287_Germans_passport_photo.png', '$6$rounds=1024$342429346$rxkmJQnkjnOu8oSM2giKD8AisFhVdbt2EFWBqBwGRTYpGgh0yGetMcT9YX875BxBShDImQ4cvLTwMA/u3n0DI0', 'n', 'y', 'N', NULL, 'Y'),
(15, '2025-11-21 12:05:10', 'Bryson', 'Yida', '', 'BY', 'bryson@sbsl.co.ke', 'employee_profile/1764404376_avatar.jpg', '$6$rounds=1024$740770009$WPnhQQI9WlZS1IA9Lr79vazLBlYTd8SOPYkE5WoK4MBzvBDsUJRNHEZYo7o5BoODnUO95Nnp0votleWXC4kcl/', 'n', 'y', 'N', NULL, 'Y'),
(16, '2025-11-21 12:10:29', 'Timothy', 'Oduor', '', 'TO', 'timothy.oduor@sbsl.co.ke', NULL, '$6$rounds=1024$1675686638$s74l31eFgl29pJ.vNFb0wuBbQihwyw6/dBbe173dtLNyyAg8ZOayz9UKyb5U5Pbs5VUt8.qWvyLL56SMyZ56h/', 'n', 'y', 'N', NULL, 'Y'),
(17, '2025-11-21 12:16:15', 'Luther', 'Icami', '', 'LI', 'luther.Icami@sbsl.co.ke', NULL, '$6$rounds=1024$1592285428$jRd1h.n5CSVqF0B/a16dZ.Ch8pgNAB7bAA5saKKsWt/FKYhJtyCl0AzbzTH7Y3fZA.NHrSSpARjF1RKDIPFsm/', 'n', 'y', 'N', NULL, 'Y'),
(18, '2025-11-21 12:18:51', 'Joseph', 'Nzeli', '', 'JN', 'joseph.nzeli@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(19, '2025-11-21 12:23:27', 'Hobson', 'Mokaya', 'Atuti', 'HM', 'hobson.mokaya@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(20, '2025-11-21 12:30:30', 'Mercy', 'Morema', '', '', 'Mercy.morema@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(21, '2025-11-21 12:32:33', 'Dan', 'Birenge', '', 'DB', 'dan.birenge@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(22, '2025-11-22 10:54:36', 'Ian', 'Simba', '', 'IS', 'ian.simba@sbsl.co.ke', NULL, '$6$rounds=1024$1954996495$IyXaeelQrWQICxH3REk8.1JQgex6vRFmI3B4KncYIjIdYhTxP1srLPdUSoBk1JpAdml6A1bTFF6.Q.GjU7zZD1', 'n', 'y', 'N', NULL, 'Y'),
(23, '2025-11-24 01:45:13', 'Test', 'User', '', 'TU', 'felix.mauncho@skm.co.ke', NULL, '$6$rounds=1024$48493910$0mVQ1dOWwsW6xa2QkBJ1Sxa1rhXGTwL5fiEw7sCKzMDnxzIQYmXPqvifvi1qBfS.sYpRMA4vX7FacLXFXSlNm.', 'n', 'y', 'N', NULL, 'Y'),
(24, '2025-11-24 01:57:38', 'John', 'Doe', '', 'JD', 'felixmauncho@gmail.com', NULL, '$6$rounds=1024$415444455$a0A62LhhKDd/UKTGhjQJhoAxvV04Iumhn3CaCHcDQ9rJ4ip687QDMu/mOS1uMBbMGCo3NoY8MVzP9S.x7qbMl1', 'n', 'y', 'N', NULL, 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `registration_tokens`
--

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
(1, 2, '2025-11-21 09:59:36', '6db4f28b4806bc4a7c051f88f09aadfbbc30175bc579b3b98afb2592d66f5bd3', '4b28f4cfa1b5c389740a5cd58d2d121b31218a53a9bc553ec8443ff6e9c45696', 'n', NULL),
(2, 3, '2025-11-21 10:01:12', 'd37c4988a785a3da6b11e1f8ed624164e3adc72e873eba09f8f250ba9d38be40', 'd1017378f5d9e2179d9c36bd65e62b473e6964f8bfe6c933f8d9ae6c07a515aa', 'n', NULL),
(3, 4, '2025-11-21 11:21:15', '28510d9efe961ff51999fb805b771e5ba9b3f131969cbde95352386e8807cddc', '95a68b8c9799dda5c1647e2d09ede038dc97a6d87bf2815f13c6b8c7f4d47c80', 'y', '2025-11-24 14:22:27'),
(4, 5, '2025-11-21 11:23:46', '21198b1a88d9c8e65ce319edf5c8c3d43575555d1995bc5177b86019d2c2f535', 'c97d6c4194807613377ff0d9890abde0a4709fbe66d4bbda3bc29760352c885e', 'n', NULL),
(5, 6, '2025-11-21 11:25:20', '1857e9e5f23c7dfe69f8f33b0d3e33825255527d389d8b04b1039c3f8b8c9d2f', 'e83abc7c6ab5a793d8b37832468c04d0914b7e5c249aacd8edb2d1f358eba1f9', 'y', '2025-11-26 04:13:24'),
(6, 7, '2025-11-21 11:30:05', '9df9ae4d3ada30ef24424dd9354c08f600b49accb0fc29f1d59ae8643bd9ef12', '21176ca0021b32ccd77a6fac80128eb846182bda3c402f0b8cb12145947128a6', 'n', NULL),
(7, 8, '2025-11-21 11:31:54', 'b4b9f865799cd395c7f32553bd2d4797194ef10b61180254769c52e45a2fc178', '0c0da7535dd2f89433661aea33e89365f8f24a61da9d031e804899bb2f78fa57', 'n', NULL),
(8, 9, '2025-11-21 11:42:45', '66a11cbb9cacd28d13d37fb643a6f5e7317468f1ef36e200b72e0007d95ab9cc', '8e76f5d4951163611d38866999d4d2f575ed8969e403219bd7e5af1fd3085b81', 'n', NULL),
(9, 10, '2025-11-21 11:45:25', '0980df75f616d20a05aad123fd3585a43a9af81058773d542617cf4f7749c791', 'c3e19b1764cae39da8edd2e187e43d8ffa92ffc181d3774cb62e9d90da8cc79a', 'n', NULL),
(10, 11, '2025-11-21 11:49:53', 'b96843918ce3b155c093e2ec055cd7a0aa2ee5b6f0db57b822a9525e949ec6f0', 'c68f17f7a1b3268b7a6419081c2e7c61df9e3ac0b4c5a28042164f3550392062', 'n', NULL),
(11, 12, '2025-11-21 11:51:24', '9172b7414a7f9cd5644f22bf1db61b086ee70295728b83cba538b703f6bbbf69', 'f4059ee0504682594bf6065c2d7fc5dfc33efd258b31031dd8c584fbd6fe9d00', 'n', NULL),
(12, 13, '2025-11-21 11:56:24', '3c5b8191ced9e0613083b29af3844f20079b5715ffb26b569ac8f65cec2e074c', 'b3450b4b9adcd4800f1e86bbc6e33fdbbffacbeee3ad092263ead219ac5116ea', 'n', NULL),
(13, 14, '2025-11-21 12:00:07', '4b441b06f8b6ba498d8b0995dc08e8a3393ebb8f2f4e9dfda4aeada824209659', '885016eca53bb1e63cce4bcec62a30cc44bc3c62edb5676430c7595c68b7495b', 'n', NULL),
(14, 15, '2025-11-21 12:05:10', '34f941b58a2097eb34e247bf33927a94edec940abb9dc39a1e97f5b46597be72', 'fccacdb091f9c371c66c1ca26f91075b9cd2a9fb98c8cd123b9613c73f7b965b', 'y', '2025-11-29 03:03:35'),
(15, 16, '2025-11-21 12:10:29', 'b9e58342ba50c1719f07e81402899af7598282eb3dce1bc515b1572bb5f245d3', '5fb006787eaa52bde44e73d6df60d512f2baee17cc39cd2d1a41bf6615b45299', 'y', '2025-11-25 03:37:14'),
(16, 17, '2025-11-21 12:16:15', 'aacc280c203d2c140da40644df8d178d87443a9efb218681fc94ba3c8454bcf4', '7d8b46d8137b84a298fd683d044423cc9c0e4fa1a9a2b92e1260f8d6c3ddb8ac', 'n', NULL),
(17, 18, '2025-11-21 12:18:51', 'bcbeabb0ab5bbb41c919e24ea5c2c5fc30cbcf098a5dffaaf9a3e762dbf59a57', '0794013c9165a4b704c3ba32feb6d54516797a9d970a1a9279061174bc8bf081', 'n', NULL),
(18, 19, '2025-11-21 12:23:27', '29d45b5385a11b22e47422eb1d8a1509fae7f51716f68764ddbb2b5966a9eae5', 'f4738302f802ff5bfeec5be9b80ce98f7d22a99874d8d20e3bec9c9f1779128b', 'n', NULL),
(19, 20, '2025-11-21 12:30:30', 'ee88b17b5ace3a5927d66960f848f3b9d778106289548df5b6df6dce95935da4', '9523376425e22a67874930daf469781d36958fa8c7bd9dc0b10ef9131b1d4cd0', 'n', NULL),
(20, 21, '2025-11-21 12:32:33', '4e7bd732a3b300282876b453f44d21cad46baf7cb4df43364325b772371cfb4f', '769227dab48acee67796ce52e73e5cec439ad58678cc082ae22dc132d836f333', 'n', NULL),
(21, 22, '2025-11-24 10:10:16', '02040f0b58f291ed47d996946891a9fa5af15dc58741993ed4be4f40320b02a8', '57478593b1d4a10893c421f781e05aba9b18911b5f41189b119f8614e4e4bbb6', 'n', NULL),
(22, 23, '2025-11-24 17:19:54', '1d4db7b3fb1a881cd3967e8226a10a368ee4f9aa60926de43c9e119e56f81bc9', 'fc6a50dab398ceac8872b941aafe8404613c08e43230eea64a6503b07e2fae4b', 'y', '2025-12-01 03:22:39'),
(23, 24, '2025-11-24 17:21:00', '4e9bbbf491e732b3ae476200637fd66a444bb4eaf63dcc2c1c432594bc3d0728', 'af793b0045ae5cca49d0fff00dfc65a317142593e9d8d2da67beefdf358924ce', 'y', '2025-11-24 17:43:21');

-- --------------------------------------------------------

--
-- Table structure for table `tija_absence_data`
--

CREATE TABLE `tija_absence_data` (
  `absenceID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `absenceName` varchar(256) NOT NULL,
  `absenceTypeID` int(11) NOT NULL,
  `projectID` text DEFAULT NULL COMMENT 'Affected Project',
  `absenceDate` date NOT NULL,
  `startTime` varchar(20) NOT NULL,
  `endTime` varchar(20) NOT NULL,
  `allday` enum('Y','N') NOT NULL DEFAULT 'N',
  `absenceHrs` time NOT NULL,
  `functionID` int(11) DEFAULT NULL,
  `absenceDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_absence_type`
--

CREATE TABLE `tija_absence_type` (
  `absenceTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `absenceTypeName` varchar(180) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_absence_type`
--

INSERT INTO `tija_absence_type` (`absenceTypeID`, `DateAdded`, `absenceTypeName`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-16 15:52:12', 'Sick Off', 'N', 'N'),
(2, '2025-07-16 15:52:12', 'Personal Emergency', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_activities`
--

CREATE TABLE `tija_activities` (
  `activityID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `activityName` varchar(255) NOT NULL,
  `activityDescription` text DEFAULT NULL,
  `activityCategoryID` int(11) NOT NULL,
  `activityTypeID` int(11) NOT NULL,
  `activitySegment` enum('sales','project','task','activity','businessDevelopment') DEFAULT NULL,
  `durationType` varchar(120) NOT NULL,
  `activityDate` date NOT NULL,
  `activityStartTime` time DEFAULT NULL,
  `activityDurationEndTime` time DEFAULT NULL,
  `activityDurationEndDate` date DEFAULT NULL,
  `recurring` varchar(120) DEFAULT NULL,
  `recurrenceType` varchar(254) DEFAULT NULL,
  `recurringInterval` int(11) DEFAULT NULL,
  `recurringIntervalUnit` varchar(120) DEFAULT NULL,
  `weekRecurringDays` text DEFAULT NULL,
  `monthRepeatOnDays` varchar(120) DEFAULT NULL,
  `monthlyRepeatingDay` int(11) DEFAULT NULL,
  `customFrequencyOrdinal` varchar(120) DEFAULT NULL,
  `customFrequencyDayValue` varchar(120) NOT NULL,
  `recurrenceEndType` varchar(120) DEFAULT NULL,
  `numberOfOccurrencesToEnd` int(11) DEFAULT NULL,
  `recurringEndDate` date DEFAULT NULL,
  `salesCaseID` int(11) DEFAULT NULL,
  `projectID` int(11) DEFAULT NULL,
  `projectPhaseID` int(11) DEFAULT NULL,
  `projectTaskID` int(11) DEFAULT NULL,
  `activityStatus` enum('notStarted','inProgress','inReview','completed','needsAttention','stalled') NOT NULL DEFAULT 'notStarted',
  `activityStatusID` int(11) NOT NULL DEFAULT 1,
  `activityPriority` varchar(120) NOT NULL,
  `activityOwnerID` int(11) NOT NULL,
  `activityParticipants` text DEFAULT NULL,
  `activityNotesID` int(11) DEFAULT NULL,
  `activityLocation` text DEFAULT NULL,
  `assignedByID` int(11) NOT NULL,
  `workSegmentID` int(11) NOT NULL DEFAULT 3,
  `activityCompleted` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_activities`
--

INSERT INTO `tija_activities` (`activityID`, `DateAdded`, `orgDataID`, `entityID`, `clientID`, `activityName`, `activityDescription`, `activityCategoryID`, `activityTypeID`, `activitySegment`, `durationType`, `activityDate`, `activityStartTime`, `activityDurationEndTime`, `activityDurationEndDate`, `recurring`, `recurrenceType`, `recurringInterval`, `recurringIntervalUnit`, `weekRecurringDays`, `monthRepeatOnDays`, `monthlyRepeatingDay`, `customFrequencyOrdinal`, `customFrequencyDayValue`, `recurrenceEndType`, `numberOfOccurrencesToEnd`, `recurringEndDate`, `salesCaseID`, `projectID`, `projectPhaseID`, `projectTaskID`, `activityStatus`, `activityStatusID`, `activityPriority`, `activityOwnerID`, `activityParticipants`, `activityNotesID`, `activityLocation`, `assignedByID`, `workSegmentID`, `activityCompleted`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-27 07:12:32', 1, 1, 1, 'Call Test Company', '<p>Make a call.</p>', 1, 1, 'sales', 'single', '2025-11-27', '14:52:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'notStarted', 1, 'high', 22, NULL, NULL, NULL, 22, 3, 'N', '2025-11-27 12:12:32', 22, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_categories`
--

CREATE TABLE `tija_activity_categories` (
  `activityCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `activityCategoryName` varchar(254) NOT NULL,
  `iconlink` varchar(255) DEFAULT NULL,
  `activityCategoryDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_activity_categories`
--

INSERT INTO `tija_activity_categories` (`activityCategoryID`, `DateAdded`, `activityCategoryName`, `iconlink`, `activityCategoryDescription`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-22 20:46:11', 'To-Do', 'ri-edit-box-line', 'Activity/activities that one is scheduling to be done&nbsp;', '2025-04-15 13:29:04', 37, 'N', 'N'),
(2, '2025-02-22 20:52:03', 'Calendar entry', 'ri-calendar-2', 'A calendar schedule', '2025-04-15 13:29:59', 37, 'N', 'N'),
(3, '2025-02-22 20:52:49', 'Project Task', 'ri-edit-box-line', 'Project Task &nbsp; activity', '2025-04-15 13:30:22', 37, 'N', 'N'),
(4, '2025-02-22 20:53:23', 'Private', 'ti ti-user', 'Private Activity', '2025-04-15 13:30:28', 37, 'N', 'N'),
(5, '2025-02-22 20:53:52', 'Absence', 'ti ti-user-off', 'Absence from activity', '2025-04-15 13:31:32', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_log`
--

CREATE TABLE `tija_activity_log` (
  `activityLogID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `objectType` varchar(255) NOT NULL,
  `objectID` int(11) NOT NULL,
  `objectName` varchar(255) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_participant_assignment`
--

CREATE TABLE `tija_activity_participant_assignment` (
  `activityParticipantID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `activityID` int(11) NOT NULL,
  `participantUserID` int(11) NOT NULL,
  `activityOwnerID` int(11) NOT NULL,
  `recurring` enum('Y','N') DEFAULT NULL,
  `recurringInterval` int(11) DEFAULT NULL,
  `recurringIntervalUnit` varchar(180) DEFAULT NULL,
  `activityStartDate` date NOT NULL,
  `activityEndDate` date DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `CreatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_activity_participant_assignment`
--

INSERT INTO `tija_activity_participant_assignment` (`activityParticipantID`, `DateAdded`, `activityID`, `participantUserID`, `activityOwnerID`, `recurring`, `recurringInterval`, `recurringIntervalUnit`, `activityStartDate`, `activityEndDate`, `LastUpdate`, `LastUpdateByID`, `CreatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-27 07:12:32', 1, 0, 22, 'N', 0, '', '2025-11-27', '2025-11-27', '2025-11-27 12:12:32', 22, 22, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_status`
--

CREATE TABLE `tija_activity_status` (
  `activityStatusID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `activityStatusName` varchar(255) NOT NULL,
  `activityStatusDescription` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_activity_status`
--

INSERT INTO `tija_activity_status` (`activityStatusID`, `DateAdded`, `activityStatusName`, `activityStatusDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-04-19 21:15:52', 'Not Started', 'Activity has not been initiated.', '2025-04-19 18:15:52', 37, 'N', 'N'),
(2, '2025-04-20 19:34:06', 'In Progress', 'Activity is currently being worked on.', '2025-04-20 16:34:06', 37, 'N', 'N'),
(3, '2025-04-20 19:34:24', 'In Review', 'Activity is under review.', '2025-04-20 16:34:24', 37, 'N', 'N'),
(4, '2025-04-20 19:34:39', 'Completed', 'Activity has been completed.', '2025-04-20 16:34:39', 37, 'N', 'N'),
(5, '2025-04-20 19:35:04', 'Needs Attention', 'Activity requires attention.', '2025-04-20 16:35:04', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_types`
--

CREATE TABLE `tija_activity_types` (
  `activityTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `activityTypeName` varchar(256) NOT NULL,
  `activityTypeDescription` text NOT NULL,
  `iconlink` varchar(256) NOT NULL,
  `activityCategoryID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_activity_types`
--

INSERT INTO `tija_activity_types` (`activityTypeID`, `DateAdded`, `activityTypeName`, `activityTypeDescription`, `iconlink`, `activityCategoryID`, `LastUpdate`, `LastUpdatedByID`, `Suspended`, `Lapsed`) VALUES
(1, '2021-09-04 13:34:14', 'Call', 'telephone call to client', 'ti ti-phone-call', 1, '2025-04-15 12:41:45', 37, 'N', 'N'),
(3, '2021-09-04 13:34:48', 'Deadline', 'Deadline activity type', 'ti ti-clock', 1, '2025-04-15 12:50:55', 37, 'N', 'N'),
(4, '2021-09-04 13:34:48', 'Email', 'Schedule an email to client', 'ti ti-mail', 1, '2025-04-15 12:49:47', 37, 'N', 'N'),
(5, '2021-09-04 13:34:48', 'To-Do', 'Schedule a todo Task', 'ri-edit-box-line', 1, '2025-04-15 12:52:38', 37, 'N', 'N'),
(6, '2025-04-14 17:50:01', 'meeting', 'Meeting', 'ti ti-calendar-stats', 2, '2025-04-15 13:13:46', 37, 'N', 'N'),
(7, '2025-04-15 13:19:55', 'Private', 'Private Activity', 'ti ti-user', 4, '2025-04-15 13:19:55', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_adhoc_tasks`
--

CREATE TABLE `tija_adhoc_tasks` (
  `adhocTaskID` int(11) NOT NULL,
  `DateAdded` int(11) NOT NULL,
  `adhoctaskTitle` varchar(255) NOT NULL,
  `adhocTaskDescription` text NOT NULL,
  `workSegmentID` int(11) NOT NULL,
  `segmentActivityTaskID` int(11) NOT NULL,
  `businessUnitID` int(11) NOT NULL,
  `workTypeID` int(11) NOT NULL,
  `approverUserID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_administrators`
--

CREATE TABLE `tija_administrators` (
  `adminID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `adminTypeID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `unitTypeID` int(11) DEFAULT NULL,
  `unitID` int(11) DEFAULT NULL,
  `isEmployee` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_administrators`
--

INSERT INTO `tija_administrators` (`adminID`, `DateAdded`, `userID`, `adminTypeID`, `orgDataID`, `entityID`, `unitTypeID`, `unitID`, `isEmployee`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 14:19:31', 4, 1, 1, 0, 0, 0, 'Y', '2025-11-21 14:19:31', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_admin_types`
--

CREATE TABLE `tija_admin_types` (
  `adminTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `adminTypeName` varchar(256) NOT NULL,
  `adminCode` varchar(80) NOT NULL,
  `adminTypeDescription` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_admin_types`
--

INSERT INTO `tija_admin_types` (`adminTypeID`, `DateAdded`, `adminTypeName`, `adminCode`, `adminTypeDescription`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2023-03-10 21:42:49', 'Super Admin', 'SUPER', '', 0, '2023-03-10 21:42:49', 'N', 'N'),
(2, '2023-03-10 21:42:49', 'Tenant Admin', 'TENANT', '', 0, '2023-03-10 21:42:49', 'N', 'N'),
(3, '2023-03-10 21:57:37', 'Entity Admin', 'ENTITY', '', 0, '2023-03-10 21:57:37', 'N', 'N'),
(4, '2023-03-10 21:57:37', 'Unit Admin', 'UNIT', '', 0, '2023-03-10 21:57:37', 'N', 'N'),
(5, '2023-03-10 21:57:37', 'Team Admin', 'TEAM', '', 0, '2023-03-10 21:57:37', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_advance_tax`
--

CREATE TABLE `tija_advance_tax` (
  `advanceTaxID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `fiscalYear` int(11) NOT NULL,
  `advanceTax` float(22,2) NOT NULL,
  `advanceTaxDescription` text DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_assigned_project_tasks`
--

CREATE TABLE `tija_assigned_project_tasks` (
  `assignmentTaskID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `projectID` int(11) DEFAULT NULL,
  `projectTaskID` int(11) NOT NULL,
  `projectTeamMemberID` int(11) NOT NULL,
  `assignmentStatus` enum('accepted','rejected','edit-request','assigned','pending','suspended') DEFAULT 'assigned',
  `notes` text DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_benefit_types`
--

CREATE TABLE `tija_benefit_types` (
  `benefitTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `benefitName` varchar(255) NOT NULL,
  `benefitCode` varchar(50) NOT NULL,
  `benefitCategory` enum('insurance','pension','allowance','wellness','other') NOT NULL DEFAULT 'insurance',
  `description` text DEFAULT NULL,
  `providerName` varchar(255) DEFAULT NULL,
  `providerContact` varchar(255) DEFAULT NULL,
  `employerContribution` decimal(10,2) DEFAULT 0.00,
  `employeeContribution` decimal(10,2) DEFAULT 0.00,
  `contributionType` enum('fixed','percentage') DEFAULT 'fixed',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `sortOrder` int(11) DEFAULT 0,
  `createdBy` int(11) DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_benefit_types`
--

INSERT INTO `tija_benefit_types` (`benefitTypeID`, `DateAdded`, `benefitName`, `benefitCode`, `benefitCategory`, `description`, `providerName`, `providerContact`, `employerContribution`, `employeeContribution`, `contributionType`, `isActive`, `sortOrder`, `createdBy`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-21 07:51:39', 'Medical Insurance', 'MED_INS', 'insurance', 'Comprehensive medical coverage', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(2, '2025-10-21 07:51:39', 'Life Insurance', 'LIFE_INS', 'insurance', 'Life insurance coverage', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(3, '2025-10-21 07:51:39', 'Pension Plan', 'PENSION', 'pension', 'Retirement pension plan', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(4, '2025-10-21 07:51:39', 'NSSF Contribution', 'NSSF', 'pension', 'National Social Security Fund', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(5, '2025-10-21 07:51:39', 'NHIF Contribution', 'NHIF', 'insurance', 'National Hospital Insurance Fund', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(6, '2025-10-21 07:51:39', 'Dental Cover', 'DENTAL', 'insurance', 'Dental insurance', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(7, '2025-10-21 07:51:39', 'Group Personal Accident', 'GPA', 'insurance', 'Group personal accident cover', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(8, '2025-10-21 07:51:39', 'Education Allowance', 'EDU_ALLOW', 'allowance', 'Education assistance for children', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(9, '2025-10-21 07:51:39', 'Housing Allowance', 'HOUSE_ALLOW', 'allowance', 'Housing support', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N'),
(10, '2025-10-21 07:51:39', 'Gym Membership', 'GYM', 'wellness', 'Wellness and fitness benefit', NULL, NULL, 0.00, 0.00, 'fixed', 'Y', 0, NULL, NULL, '2025-10-21 07:51:39', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_billing_rate`
--

CREATE TABLE `tija_billing_rate` (
  `billingRateID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `billingRate` varchar(120) NOT NULL,
  `billingRateDescription` text NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_billing_rate`
--

INSERT INTO `tija_billing_rate` (`billingRateID`, `DateAdded`, `billingRate`, `billingRateDescription`, `Lapsed`, `Suspended`) VALUES
(1, '2021-07-27 11:55:53', 'Not Billable', 'Not Billable', 'N', 'N'),
(2, '2021-07-27 11:55:53', 'Billable: Project Based Rate', 'Billable amount= ( Project Billable Rate * hours) + Expenses', 'N', 'N'),
(3, '2021-07-27 11:55:53', 'Billable: Task Based Rate', 'Billable amount= ( Task Billable Rate * hours) + Expenses', 'N', 'N'),
(4, '2021-07-27 11:55:53', 'Billable: Member Based Rate', 'Billable amount= (  Member Based Rate * hours) + Expenses', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_billing_rates`
--

CREATE TABLE `tija_billing_rates` (
  `billingRateID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `workTypeID` int(11) NOT NULL,
  `billingRateName` varchar(256) DEFAULT NULL,
  `billingRateDescription` text DEFAULT NULL,
  `workCategory` enum('sales','project','administartive') DEFAULT NULL,
  `doneByID` int(11) NOT NULL,
  `hourlyRate` decimal(10,2) NOT NULL,
  `billingRateTypeID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `bill` enum('Y','N') NOT NULL DEFAULT 'Y',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_billing_rates`
--

INSERT INTO `tija_billing_rates` (`billingRateID`, `DateAdded`, `workTypeID`, `billingRateName`, `billingRateDescription`, `workCategory`, `doneByID`, `hourlyRate`, `billingRateTypeID`, `entityID`, `projectID`, `bill`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-18 09:35:15', 1, 'hjfjyf', NULL, NULL, 4, 2000.00, 1, 1, 3, 'Y', '2025-07-18 09:35:15', 4, 'N', 'N'),
(2, '2025-07-23 04:27:49', 1, 'mobilization', NULL, NULL, 15, 500.00, 3, 1, 16, 'Y', '2025-07-23 11:27:49', 15, 'N', 'N'),
(3, '2025-09-02 15:30:34', 1, 'Project Default Billing', NULL, NULL, 4, 2000.00, 1, 1, 53, 'Y', '2025-09-02 15:30:34', 4, 'N', 'N'),
(4, '2025-09-27 14:33:35', 1, 'Project Default Billing', NULL, NULL, 4, 2000.00, 1, 1, 50, 'Y', '2025-09-27 14:33:35', 4, 'N', 'N'),
(5, '2025-09-27 14:34:00', 3, 'Project Default Billing', NULL, NULL, 4, 1000.00, 1, 1, 50, 'Y', '2025-09-27 14:34:00', 4, 'N', 'N'),
(6, '2025-09-27 14:35:08', 5, 'internal/Administrative work', NULL, NULL, 4, 2000.00, 1, 1, 50, 'Y', '2025-09-27 14:35:08', 4, 'N', 'N'),
(7, '2025-11-15 18:03:21', 1, 'Project Default Billing', NULL, NULL, 4, 2000.00, 1, 1, 74, 'Y', '2025-11-15 18:03:20', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_billing_rate_types`
--

CREATE TABLE `tija_billing_rate_types` (
  `billingRateTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `billingRateTypeName` varchar(255) NOT NULL,
  `billingRateTypeDescription` text DEFAULT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_billing_rate_types`
--

INSERT INTO `tija_billing_rate_types` (`billingRateTypeID`, `DateAdded`, `billingRateTypeName`, `billingRateTypeDescription`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-07 20:28:18', 'Work Hour Rates', '<p>Work hour rates for work types in a project</p>\r\n<p>&nbsp;</p>', 0, '2025-03-07 20:28:18', 'N', 'N'),
(2, '2025-03-07 20:28:39', 'Travel Rates', '<p>Travel&nbsp; Rates</p>', 0, '2025-03-07 20:28:39', 'N', 'N'),
(3, '2025-03-07 20:28:58', 'Product Rates', '<p>Product Rates</p>', 0, '2025-03-07 20:28:58', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_bradford_factor`
--

CREATE TABLE `tija_bradford_factor` (
  `bradfordFactorID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `bradfordFactorName` varchar(255) NOT NULL,
  `bradfordFactorValue` decimal(4,2) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_bradford_factor`
--

INSERT INTO `tija_bradford_factor` (`bradfordFactorID`, `DateAdded`, `bradfordFactorName`, `bradfordFactorValue`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-16 17:37:26', 'Minor', 50.00, '2025-03-16 17:37:26', 11, 'N', 'N'),
(2, '2025-03-16 17:37:58', 'Warning', 75.00, '2025-03-16 17:37:58', 11, 'N', 'N'),
(3, '2025-03-16 17:38:09', 'Major', 99.99, '2025-03-16 17:38:09', 11, 'N', 'N'),
(5, '2025-03-16 17:49:20', 'Negligible', 10.00, '2025-03-16 17:50:09', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_business_units`
--

CREATE TABLE `tija_business_units` (
  `businessUnitID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `businessUnitName` varchar(180) NOT NULL,
  `businessUnitDescription` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `unitTypeID` int(11) DEFAULT NULL,
  `categoryID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_business_units`
--

INSERT INTO `tija_business_units` (`businessUnitID`, `DateAdded`, `businessUnitName`, `businessUnitDescription`, `orgDataID`, `entityID`, `unitTypeID`, `categoryID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 12:35:59', 'Human Resource Advisory', 'HR Consultancy & Technology Enablement', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(2, '2025-11-21 12:36:44', 'Reconciliation Advisory', 'Reconciliation Product line advisory & Implementation', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(3, '2025-11-21 12:37:40', 'Risk & Compliance Advisory', 'Risk and Compliance Automation Advisory', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(4, '2025-11-21 12:39:14', 'Reporting Advisory', 'Group reporting & Quick console', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_business_unit_categories`
--

CREATE TABLE `tija_business_unit_categories` (
  `categoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `categoryName` varchar(255) NOT NULL,
  `categoryCode` varchar(50) DEFAULT NULL,
  `categoryDescription` text DEFAULT NULL,
  `categoryOrder` int(11) DEFAULT 1,
  `iconClass` varchar(100) DEFAULT NULL COMMENT 'Font Awesome icon class',
  `colorCode` varchar(20) DEFAULT NULL COMMENT 'Color for UI display',
  `isActive` enum('Y','N') NOT NULL DEFAULT 'Y',
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Business unit categories for classification';

--
-- Dumping data for table `tija_business_unit_categories`
--

INSERT INTO `tija_business_unit_categories` (`categoryID`, `DateAdded`, `categoryName`, `categoryCode`, `categoryDescription`, `categoryOrder`, `iconClass`, `colorCode`, `isActive`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-25 20:19:47', 'Cost Center', 'cost_center', 'Units that incur costs but do not directly generate revenue', 1, 'fa-money-bill-wave', '#dc3545', 'Y', NULL, NULL, 'N', 'N'),
(2, '2025-10-25 20:19:47', 'Profit Center', 'profit_center', 'Units that generate revenue and are responsible for profit', 2, 'fa-chart-line', '#28a745', 'Y', NULL, NULL, 'N', 'N'),
(3, '2025-10-25 20:19:47', 'Product Line', 'product_line', 'Units organized around specific products or product families', 3, 'fa-box-open', '#007bff', 'Y', NULL, NULL, 'N', 'N'),
(4, '2025-10-25 20:19:47', 'Project', 'project', 'Temporary units created for specific projects or initiatives', 4, 'fa-project-diagram', '#6f42c1', 'Y', NULL, NULL, 'N', 'N'),
(5, '2025-10-25 20:19:47', 'Service Line', 'service_line', 'Units organized around specific service offerings', 5, 'fa-concierge-bell', '#fd7e14', 'Y', NULL, NULL, 'N', 'N'),
(6, '2025-10-25 20:19:47', 'Revenue Center', 'revenue_center', 'Units focused on generating revenue through sales', 6, 'fa-dollar-sign', '#20c997', 'Y', NULL, NULL, 'N', 'N'),
(7, '2025-10-25 20:19:47', 'Investment Center', 'investment_center', 'Units with control over revenue, costs, and investment decisions', 7, 'fa-landmark', '#6610f2', 'Y', NULL, NULL, 'N', 'N'),
(8, '2025-10-25 20:19:47', 'Support Unit', 'support_unit', 'Units providing support services to other business units', 8, 'fa-hands-helping', '#17a2b8', 'Y', NULL, NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_cases`
--

CREATE TABLE `tija_cases` (
  `caseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `caseName` varchar(256) NOT NULL,
  `caseOwner` int(11) NOT NULL,
  `caseType` varchar(80) NOT NULL,
  `clientID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `saleID` int(11) DEFAULT NULL,
  `projectID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_clients`
--

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
(1, '2025-11-27 06:44:11', 'TC-870697', 'Test Company', '<p>Test Company</p>\r\n<p>&nbsp;</p>', 1, 1, NULL, NULL, 1, NULL, 'K1234567', 22, 'N', 'N', NULL, NULL, 'active', 22, '2025-11-27 14:51:23', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_addresses`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_contacts`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_documents`
--

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
(1, '2025-11-27 06:51:09', 'Test Proposal', 'Test', 5, 1, 'client_documents/1764244269_Quick_Consols_Write-up_SBSL.pdf', '1764244269_Quick_Consols_Write-up_SBSL.pdf', 903768, 'application/pdf', '../../../data/uploaded_files/client_documents/', 0, 0, '2025-11-27 14:51:09', 22, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_levels`
--

CREATE TABLE `tija_client_levels` (
  `clientLevelID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `clientLevelName` varchar(255) NOT NULL,
  `clientLevelDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL DEFAULT 37,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_client_levels`
--

INSERT INTO `tija_client_levels` (`clientLevelID`, `DateAdded`, `clientLevelName`, `clientLevelDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-09 17:51:36', 'Customer', 'An individual or organization that has purchased products or services. They have an existing transactional history and are active users or consumers of your offerings.', '2025-05-09 17:51:36', 37, 'N', 'N'),
(2, '2025-05-09 17:51:36', 'Partner', 'An individual or organization that collaborates with your business in a strategic relationship to achieve mutual goals. This can include resellers, affiliates, technology partners, service delivery partners, or joint venture associates.', '2025-05-09 17:51:36', 37, 'N', 'N'),
(3, '2025-05-09 17:51:36', 'Prospect', 'An individual or organization that has shown interest in your products or services and has the potential to become a customer. They are typically in the sales pipeline but have not yet made a purchase.', '2025-05-09 17:51:36', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_relationship_types`
--

CREATE TABLE `tija_client_relationship_types` (
  `clientRelationshipTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `clientRelationshipType` varchar(255) NOT NULL,
  `clientRelationshipTypeDescription` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_contact_relationships`
--

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
-- Table structure for table `tija_contact_types`
--

CREATE TABLE `tija_contact_types` (
  `contactTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `contactType` varchar(120) NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_contact_types`
--

INSERT INTO `tija_contact_types` (`contactTypeID`, `DateAdded`, `contactType`, `LastUpdateByID`, `LastUpdate`, `Suspended`, `Lapsed`) VALUES
(1, '2021-07-09 19:16:44', 'Case Contact', 0, '2025-02-19 18:18:07', 'N', 'N'),
(2, '2021-07-09 19:16:44', 'Project Contact', 0, '2025-02-19 18:18:07', 'N', 'N'),
(3, '2021-07-09 19:16:44', 'Sales Contact', 0, '2025-02-19 18:18:07', 'N', 'N'),
(4, '2021-07-09 19:16:44', 'Project Manager', 0, '2025-02-19 18:18:07', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_delegation_assignments`
--

CREATE TABLE `tija_delegation_assignments` (
  `delegationID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `delegatorID` int(11) NOT NULL COMMENT 'Person delegating authority',
  `delegateID` int(11) NOT NULL COMMENT 'Person receiving authority',
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `delegationType` enum('Full','Partial','Specific') DEFAULT 'Partial',
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `approvalScope` text DEFAULT NULL COMMENT 'What can be approved',
  `financialLimit` decimal(15,2) DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `approvedBy` int(11) DEFAULT NULL,
  `approvedDate` datetime DEFAULT NULL,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Temporary delegation of authority';

-- --------------------------------------------------------

--
-- Table structure for table `tija_document_types`
--

CREATE TABLE `tija_document_types` (
  `documentTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `documentTypeName` varchar(255) NOT NULL,
  `DocumentTypeDescription` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_document_types`
--

INSERT INTO `tija_document_types` (`documentTypeID`, `DateAdded`, `documentTypeName`, `DocumentTypeDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-06-19 16:32:38', 'Statutory Documents', 'These are the official records that a business is legally obligated to create, maintain, and in many cases, file with governmental authorities.', '2025-06-19 16:32:38', 37, 'N', 'N'),
(2, '2025-07-18 09:10:07', 'fdetey', 'gfhgfjh', '2025-07-18 09:10:07', 4, 'N', 'N'),
(3, '2025-07-23 03:40:32', 'KYC', 'Certificate of incorporation', '2025-07-23 10:40:32', 21, 'N', 'N'),
(4, '2025-08-26 02:05:19', 'Project Document', 'Documents that relate to spesific projects/assignments', '2025-08-26 09:05:19', 25, 'N', 'N'),
(5, '2025-11-27 06:51:09', 'Proposal', 'Financial Proposal', '2025-11-27 14:51:09', 22, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_addresses`
--

CREATE TABLE `tija_employee_addresses` (
  `addressID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `addressType` enum('home','work','postal','permanent','temporary') NOT NULL,
  `addressLine1` text DEFAULT NULL,
  `addressLine2` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `county` varchar(100) DEFAULT NULL COMMENT 'County/State/Province',
  `state` varchar(100) DEFAULT NULL,
  `postalCode` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Kenya',
  `landmark` varchar(255) DEFAULT NULL COMMENT 'Nearby landmark for directions',
  `validFrom` date DEFAULT NULL COMMENT 'Address valid from date',
  `validTo` date DEFAULT NULL COMMENT 'Address valid until date',
  `notes` text DEFAULT NULL COMMENT 'Additional notes about this address',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee addresses - current, permanent, postal';

--
-- Dumping data for table `tija_employee_addresses`
--

INSERT INTO `tija_employee_addresses` (`addressID`, `employeeID`, `addressType`, `addressLine1`, `addressLine2`, `city`, `county`, `state`, `postalCode`, `country`, `landmark`, `validFrom`, `validTo`, `notes`, `isPrimary`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, 13, 'work', '3066-00506', 'Rainbow tower, 5th floor', 'westlands', 'nairobi', NULL, '3066-00506', 'Kenya', 'next to Romo house', NULL, NULL, '', 'N', 13, '2025-11-25 08:52:26', 13, '2025-11-25 08:52:26', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_allowances`
--

CREATE TABLE `tija_employee_allowances` (
  `allowanceID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL,
  `housingAllowance` decimal(15,2) DEFAULT 0.00,
  `transportAllowance` decimal(15,2) DEFAULT 0.00,
  `medicalAllowance` decimal(15,2) DEFAULT 0.00,
  `communicationAllowance` decimal(15,2) DEFAULT 0.00,
  `mealAllowance` decimal(15,2) DEFAULT 0.00,
  `otherAllowances` decimal(15,2) DEFAULT 0.00,
  `allowanceNotes` text DEFAULT NULL,
  `bonusEligible` enum('Y','N') DEFAULT 'N',
  `overtimeEligible` enum('Y','N') DEFAULT 'N',
  `overtimeRate` decimal(5,2) DEFAULT 1.50,
  `commissionEligible` enum('Y','N') DEFAULT 'N',
  `commissionRate` decimal(5,2) DEFAULT 0.00,
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_bank_accounts`
--

CREATE TABLE `tija_employee_bank_accounts` (
  `bankAccountID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(11) NOT NULL,
  `bankName` varchar(255) NOT NULL,
  `bankCode` varchar(50) DEFAULT NULL,
  `branchName` varchar(255) DEFAULT NULL,
  `branchCode` varchar(50) DEFAULT NULL,
  `accountNumber` varchar(100) NOT NULL,
  `accountName` varchar(255) NOT NULL,
  `accountType` enum('savings','checking','current','salary') DEFAULT 'salary',
  `currency` varchar(10) DEFAULT 'KES',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `allocationPercentage` decimal(5,2) DEFAULT 100.00,
  `swiftCode` varchar(50) DEFAULT NULL,
  `iban` varchar(100) DEFAULT NULL,
  `sortCode` varchar(50) DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `effectiveDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `isVerified` enum('Y','N') DEFAULT 'N',
  `verifiedDate` date DEFAULT NULL,
  `verifiedBy` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_employee_bank_accounts`
--

INSERT INTO `tija_employee_bank_accounts` (`bankAccountID`, `DateAdded`, `employeeID`, `bankName`, `bankCode`, `branchName`, `branchCode`, `accountNumber`, `accountName`, `accountType`, `currency`, `isPrimary`, `allocationPercentage`, `swiftCode`, `iban`, `sortCode`, `isActive`, `effectiveDate`, `endDate`, `isVerified`, `verifiedDate`, `verifiedBy`, `notes`, `createdBy`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-25 04:49:05', 13, 'Standard Chartered', '', 'Westlands Branch', '', 'n/a', 'N/A', 'salary', 'KES', 'N', 100.00, '', '', '', 'N', NULL, NULL, 'N', NULL, NULL, '', 13, 13, '2025-11-25 04:49:37', 'N', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_bank_details`
--

CREATE TABLE `tija_employee_bank_details` (
  `bankDetailID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `bankName` varchar(255) NOT NULL,
  `bankCode` varchar(50) DEFAULT NULL,
  `branchName` varchar(255) DEFAULT NULL,
  `branchCode` varchar(50) DEFAULT NULL,
  `accountNumber` varchar(100) NOT NULL,
  `accountName` varchar(255) NOT NULL,
  `accountType` enum('savings','current','fixed_deposit','other') DEFAULT 'savings',
  `swiftCode` varchar(50) DEFAULT NULL,
  `iban` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `isPrimary` enum('Y','N') DEFAULT 'Y',
  `isActiveForSalary` enum('Y','N') DEFAULT 'Y',
  `salaryAllocationPercentage` decimal(5,2) DEFAULT 100.00,
  `sortOrder` int(11) DEFAULT 0,
  `verificationStatus` enum('pending','verified','failed') DEFAULT 'pending',
  `verifiedBy` int(11) DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bank account details for salary deposits';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_benefits`
--

CREATE TABLE `tija_employee_benefits` (
  `benefitID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `benefitTypeID` int(11) NOT NULL,
  `benefitType` varchar(100) NOT NULL COMMENT 'Medical, Life, Pension, etc.',
  `benefitName` varchar(255) NOT NULL,
  `providerName` varchar(255) DEFAULT NULL,
  `policyNumber` varchar(100) DEFAULT NULL,
  `membershipNumber` varchar(100) DEFAULT NULL,
  `coverageAmount` decimal(15,2) DEFAULT NULL,
  `employeeContribution` decimal(15,2) DEFAULT 0.00,
  `employerContribution` decimal(15,2) DEFAULT 0.00,
  `totalContribution` decimal(15,2) GENERATED ALWAYS AS (`employeeContribution` + `employerContribution`) STORED,
  `contributionFrequency` enum('monthly','quarterly','annually') DEFAULT 'monthly',
  `coverageStartDate` date NOT NULL,
  `coverageEndDate` date DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `beneficiaries` text DEFAULT NULL COMMENT 'JSON array of beneficiaries',
  `attachmentPath` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N',
  `enrollmentDate` date NOT NULL,
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `coverageLevel` enum('individual','spouse','family','children') DEFAULT 'individual',
  `memberNumber` varchar(100) DEFAULT NULL,
  `totalPremium` decimal(10,2) DEFAULT 0.00,
  `dependentsCovered` int(11) DEFAULT 0,
  `dependentIDs` text DEFAULT NULL,
  `providerContact` varchar(255) DEFAULT NULL,
  `providerPolicyNumber` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee benefits enrollment and coverage';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_certifications`
--

CREATE TABLE `tija_employee_certifications` (
  `certificationID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `certificationName` varchar(255) NOT NULL,
  `issuingOrganization` varchar(255) NOT NULL,
  `certificationNumber` varchar(100) DEFAULT NULL,
  `issueDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `doesNotExpire` enum('Y','N') DEFAULT 'N',
  `verificationURL` varchar(500) DEFAULT NULL,
  `credentialID` varchar(100) DEFAULT NULL,
  `credentialURL` varchar(255) DEFAULT NULL,
  `attachmentPath` varchar(255) DEFAULT NULL,
  `verificationStatus` enum('pending','verified','failed','not_required') DEFAULT 'pending',
  `verifiedBy` int(11) DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Professional certifications';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_dependants`
--

CREATE TABLE `tija_employee_dependants` (
  `dependantID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `fullName` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL COMMENT 'Child, Spouse, Parent, etc.',
  `dateOfBirth` date NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `nationalID` varchar(50) DEFAULT NULL,
  `birthCertificateNumber` varchar(50) DEFAULT NULL,
  `isStudent` enum('Y','N') DEFAULT 'N',
  `isDisabled` enum('Y','N') DEFAULT 'N',
  `isDependentForTax` enum('Y','N') DEFAULT 'N',
  `schoolName` varchar(255) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `studentID` varchar(100) DEFAULT NULL,
  `bloodType` varchar(10) DEFAULT NULL,
  `hasDisability` enum('Y','N') DEFAULT 'N',
  `disabilityDetails` text DEFAULT NULL,
  `medicalConditions` text DEFAULT NULL,
  `insuranceMemberNumber` varchar(100) DEFAULT NULL,
  `isBeneficiary` enum('Y','N') DEFAULT 'Y' COMMENT 'Eligible for benefits',
  `benefitStartDate` date DEFAULT NULL,
  `benefitEndDate` date DEFAULT NULL,
  `allocationPercentage` decimal(5,2) DEFAULT 0.00,
  `phoneNumber` varchar(50) DEFAULT NULL,
  `emailAddress` varchar(255) DEFAULT NULL,
  `photoPath` varchar(500) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dependants for insurance and benefits';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_education`
--

CREATE TABLE `tija_employee_education` (
  `educationID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `institutionName` varchar(255) NOT NULL,
  `institutionType` enum('high_school','college','university','technical','other') DEFAULT 'university',
  `institutionCountry` varchar(100) DEFAULT 'Kenya',
  `qualificationLevel` enum('high_school','diploma','degree','masters','phd','certificate','other') NOT NULL,
  `qualificationTitle` varchar(255) NOT NULL,
  `educationLevel` varchar(100) NOT NULL COMMENT 'Primary, Secondary, Diploma, Degree, Masters, PhD, etc.',
  `fieldOfStudy` varchar(255) DEFAULT NULL,
  `degreeTitle` varchar(255) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `isCompleted` enum('Y','N') DEFAULT 'Y',
  `certificateNumber` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `attachmentPath` varchar(255) DEFAULT NULL,
  `verificationStatus` enum('pending','verified','failed','not_required') DEFAULT 'pending',
  `verifiedBy` int(11) DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `sortOrder` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Educational qualifications';

--
-- Dumping data for table `tija_employee_education`
--

INSERT INTO `tija_employee_education` (`educationID`, `DateAdded`, `employeeID`, `institutionName`, `institutionType`, `institutionCountry`, `qualificationLevel`, `qualificationTitle`, `educationLevel`, `fieldOfStudy`, `degreeTitle`, `grade`, `startDate`, `completionDate`, `isCompleted`, `certificateNumber`, `location`, `country`, `attachmentPath`, `verificationStatus`, `verifiedBy`, `verificationDate`, `sortOrder`, `notes`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-25 04:01:59', 13, 'Catholic University of Eastern Africa', 'university', 'Kenya', 'diploma', 'Diploma In International Relations', '', 'arts and social sciences', NULL, 'N/A', '2023-05-08', NULL, 'N', '', NULL, NULL, NULL, 'pending', NULL, NULL, 0, '', 13, '2025-11-25 09:01:59', 13, '2025-11-25 09:01:59', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_emergency_contacts`
--

CREATE TABLE `tija_employee_emergency_contacts` (
  `emergencyContactID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `contactName` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL COMMENT 'Spouse, Parent, Sibling, Friend, etc.',
  `primaryPhoneNumber` varchar(50) NOT NULL,
  `secondaryPhoneNumber` varchar(50) DEFAULT NULL,
  `workPhoneNumber` varchar(20) DEFAULT NULL COMMENT 'Work phone number',
  `emailAddress` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `county` varchar(100) DEFAULT NULL COMMENT 'County/State',
  `postalCode` varchar(20) DEFAULT NULL COMMENT 'Postal code',
  `country` varchar(100) DEFAULT 'Kenya',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `contactPriority` enum('primary','secondary','tertiary') DEFAULT 'secondary' COMMENT 'Priority level',
  `sortOrder` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL COMMENT 'Occupation of emergency contact',
  `employer` varchar(200) DEFAULT NULL COMMENT 'Employer of emergency contact',
  `nationalID` varchar(50) DEFAULT NULL COMMENT 'National ID/Passport',
  `bloodType` varchar(10) DEFAULT NULL COMMENT 'Blood type',
  `medicalConditions` text DEFAULT NULL COMMENT 'Medical conditions',
  `authorizedToCollectSalary` enum('Y','N') DEFAULT 'N' COMMENT 'Can collect salary',
  `authorizedForMedicalDecisions` enum('Y','N') DEFAULT 'N' COMMENT 'Can make medical decisions',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Emergency contact persons';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_extended_personal`
--

CREATE TABLE `tija_employee_extended_personal` (
  `extendedPersonalID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `middleName` varchar(100) DEFAULT NULL,
  `maidenName` varchar(100) DEFAULT NULL,
  `maritalStatus` enum('single','married','divorced','widowed','separated') DEFAULT NULL,
  `nationality` varchar(100) DEFAULT 'Kenyan',
  `passportNumber` varchar(50) DEFAULT NULL,
  `passportIssueDate` date DEFAULT NULL,
  `passportExpiryDate` date DEFAULT NULL,
  `bloodGroup` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `ethnicity` varchar(50) DEFAULT NULL,
  `languagesSpoken` text DEFAULT NULL COMMENT 'JSON array of languages',
  `disabilities` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_employee_extended_personal`
--

INSERT INTO `tija_employee_extended_personal` (`extendedPersonalID`, `employeeID`, `middleName`, `maidenName`, `maritalStatus`, `nationality`, `passportNumber`, `passportIssueDate`, `passportExpiryDate`, `bloodGroup`, `religion`, `ethnicity`, `languagesSpoken`, `disabilities`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Suspended`) VALUES
(1, 13, NULL, NULL, 'single', 'Kenyan', NULL, NULL, NULL, NULL, 'Christian', 'kikuyu', 'english, swahili', 'N/A', 13, '2025-11-25 08:42:56', 13, '2025-11-25 08:42:56', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_job_history`
--

CREATE TABLE `tija_employee_job_history` (
  `jobHistoryID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL,
  `jobTitleID` int(11) DEFAULT NULL,
  `departmentID` int(11) DEFAULT NULL,
  `businessUnitID` int(11) DEFAULT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'N',
  `responsibilities` text DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `changeReason` varchar(255) DEFAULT NULL,
  `salaryAtTime` decimal(15,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_licenses`
--

CREATE TABLE `tija_employee_licenses` (
  `licenseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `licenseType` varchar(100) NOT NULL COMMENT 'Driving License, Professional License, etc.',
  `licenseName` varchar(255) NOT NULL,
  `licenseNumber` varchar(100) NOT NULL,
  `licenseCategory` varchar(100) DEFAULT NULL,
  `issuingAuthority` varchar(255) DEFAULT NULL,
  `issuingCountry` varchar(100) DEFAULT 'Kenya',
  `issueDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `doesNotExpire` enum('Y','N') DEFAULT 'N',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `restrictions` text DEFAULT NULL,
  `attachmentPath` varchar(255) DEFAULT NULL,
  `verificationStatus` enum('pending','verified','failed','not_required') DEFAULT 'pending',
  `verifiedBy` int(11) DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Professional licenses';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_next_of_kin`
--

CREATE TABLE `tija_employee_next_of_kin` (
  `nextOfKinID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `fullName` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `nationalID` varchar(50) DEFAULT NULL,
  `phoneNumber` varchar(50) NOT NULL,
  `alternativePhone` varchar(20) DEFAULT NULL,
  `emailAddress` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `county` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Kenya',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `allocationPercentage` decimal(5,2) DEFAULT 100.00 COMMENT 'Percentage of benefits',
  `sortOrder` int(11) DEFAULT 0,
  `occupation` varchar(150) DEFAULT NULL,
  `employer` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Next of kin for benefits and insurance';

--
-- Dumping data for table `tija_employee_next_of_kin`
--

INSERT INTO `tija_employee_next_of_kin` (`nextOfKinID`, `DateAdded`, `employeeID`, `fullName`, `relationship`, `dateOfBirth`, `gender`, `nationalID`, `phoneNumber`, `alternativePhone`, `emailAddress`, `address`, `city`, `county`, `country`, `isPrimary`, `allocationPercentage`, `sortOrder`, `occupation`, `employer`, `notes`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-13 17:22:26', 31, 'Felix Nyandega MAuncho', 'Parent', '2025-11-04', 'male', '2343456543', '0722540169', '0722540169', 'felixmauncho@gmail.com', 'Rainbow Towers\r\nP. O. BOX 20212 00100', 'Nairobi', 'Nairobi', 'Kenya', 'N', 20.00, 0, 'Communication Director', 'The University Of Nairobi', 'reer yer ert rtwy rw', 31, '2025-11-13 14:22:26', 31, '2025-11-13 14:22:31', 'N', 'Y'),
(2, '2025-11-13 17:56:37', 31, 'asfgsagasfdg', 'Parent', '2025-10-27', 'female', '23595758', '0722540169', '0722540169', 'johndoe@example.com', '2012002\r\nsuite 255, longhorn House', 'Nairobi', 'Nairobi', 'Kenya', 'N', 23.00, 0, 'Professor', 'The University Of Nairobi', 'sfadg fag asfdg a', 31, '2025-11-13 14:56:37', 31, '2025-11-13 14:56:37', 'N', 'N'),
(3, '2025-11-25 03:56:47', 13, 'Winnie Njenga', 'Parent', '1985-10-24', 'female', '', '0725930042', '', 'wnnjay@gmail.com', 'N/A', 'Nairobi', 'nairobi', 'Kenya', 'Y', 0.00, 0, 'N/A', 'N/A', '', 13, '2025-11-25 08:56:47', 13, '2025-11-25 08:56:47', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_salary_components`
--

CREATE TABLE `tija_employee_salary_components` (
  `employeeComponentID` int(10) UNSIGNED NOT NULL,
  `DateAdded` timestamp NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL,
  `salaryComponentID` int(10) UNSIGNED NOT NULL,
  `componentValue` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Overrides default value',
  `valueType` enum('fixed','percentage','formula') NOT NULL COMMENT 'How this value is applied',
  `applyTo` enum('basic_salary','gross_salary','taxable_income','net_salary') DEFAULT 'basic_salary',
  `effectiveDate` date NOT NULL COMMENT 'When this assignment starts',
  `endDate` date DEFAULT NULL COMMENT 'When this assignment ends',
  `isCurrent` enum('Y','N') DEFAULT 'Y' COMMENT 'Is this the current assignment?',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Is this component active for the employee?',
  `frequency` enum('every_payroll','monthly','bi-weekly','weekly','one-time') DEFAULT 'every_payroll',
  `oneTimePayrollDate` date DEFAULT NULL COMMENT 'For one-time components',
  `notes` text DEFAULT NULL COMMENT 'Reason for assignment or special notes',
  `assignedBy` int(10) UNSIGNED DEFAULT NULL COMMENT 'Who assigned this component',
  `assignedAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(10) UNSIGNED DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_salary_history`
--

CREATE TABLE `tija_employee_salary_history` (
  `salaryHistoryID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL,
  `oldBasicSalary` decimal(15,2) DEFAULT 0.00,
  `newBasicSalary` decimal(15,2) NOT NULL,
  `oldGrossSalary` decimal(15,2) DEFAULT 0.00,
  `newGrossSalary` decimal(15,2) NOT NULL,
  `changePercentage` decimal(5,2) DEFAULT 0.00,
  `changeReason` varchar(255) DEFAULT NULL,
  `effectiveDate` date NOT NULL,
  `approvedBy` int(11) DEFAULT NULL,
  `approvalDate` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_skills`
--

CREATE TABLE `tija_employee_skills` (
  `skillID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `skillName` varchar(255) NOT NULL,
  `skillCategory` varchar(100) DEFAULT NULL COMMENT 'Technical, Soft, Language, etc.',
  `proficiencyLevel` enum('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
  `yearsOfExperience` decimal(4,1) DEFAULT NULL,
  `lastUsed` date DEFAULT NULL,
  `isCertified` enum('Y','N') DEFAULT 'N',
  `certificationName` varchar(255) DEFAULT NULL,
  `lastUsedDate` date DEFAULT NULL,
  `certificationDate` date DEFAULT NULL,
  `certificationExpiry` date DEFAULT NULL,
  `endorsedBy` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Professional skills and competencies';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_subordinates`
--

CREATE TABLE `tija_employee_subordinates` (
  `subordinateMappingID` int(11) NOT NULL,
  `supervisorID` int(10) UNSIGNED NOT NULL,
  `subordinateID` int(10) UNSIGNED NOT NULL,
  `reportingType` enum('direct','functional','dotted_line') DEFAULT 'direct',
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_supervisors`
--

CREATE TABLE `tija_employee_supervisors` (
  `supervisorMappingID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL,
  `supervisorID` int(10) UNSIGNED NOT NULL,
  `supervisorType` enum('direct','functional','dotted_line') DEFAULT 'direct',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_supervisor_relationships`
--

CREATE TABLE `tija_employee_supervisor_relationships` (
  `relationshipID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(11) NOT NULL COMMENT 'Employee who reports to supervisor',
  `supervisorID` int(11) NOT NULL COMMENT 'The supervisor',
  `relationshipType` enum('direct','indirect','dotted-line','functional','matrix') NOT NULL DEFAULT 'direct',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `percentage` decimal(5,2) DEFAULT 100.00,
  `effectiveDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `scope` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `projectID` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_employee_supervisor_relationships`
--

INSERT INTO `tija_employee_supervisor_relationships` (`relationshipID`, `DateAdded`, `employeeID`, `supervisorID`, `relationshipType`, `isPrimary`, `percentage`, `effectiveDate`, `endDate`, `isActive`, `scope`, `department`, `projectID`, `notes`, `createdBy`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-24 17:25:07', 24, 4, 'direct', 'N', 100.00, NULL, NULL, 'N', 'Administrative', 'Administrative', NULL, '', 4, 4, '2025-12-01 03:17:58', 'N', 'Y'),
(2, '2025-12-01 03:17:46', 24, 23, 'direct', 'Y', 100.00, NULL, NULL, 'Y', '', '', NULL, '', 4, 4, '2025-12-01 03:17:46', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_work_experience`
--

CREATE TABLE `tija_employee_work_experience` (
  `workExperienceID` int(11) NOT NULL,
  `employeeID` int(10) UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `companyName` varchar(255) NOT NULL,
  `companyIndustry` varchar(100) DEFAULT NULL,
  `companyLocation` varchar(255) DEFAULT NULL,
  `jobTitle` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `employmentType` varchar(50) DEFAULT NULL COMMENT 'Full-time, Part-time, Contract, etc.',
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'N',
  `isCurrentEmployer` enum('Y','N') DEFAULT 'N',
  `responsibilities` text DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `reasonForLeaving` varchar(255) DEFAULT NULL,
  `supervisorName` varchar(255) DEFAULT NULL,
  `supervisorContact` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `canContact` enum('Y','N') DEFAULT 'Y',
  `location` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `monthlyGrossSalary` decimal(15,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `sortOrder` int(11) DEFAULT 0,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT current_timestamp(),
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Previous employment history';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employment_status`
--

CREATE TABLE `tija_employment_status` (
  `employmentStatusID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employmentStatusTitle` varchar(255) NOT NULL,
  `employmentStatusDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_employment_status`
--

INSERT INTO `tija_employment_status` (`employmentStatusID`, `DateAdded`, `employmentStatusTitle`, `employmentStatusDescription`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2025-07-10 18:13:23', 'Full-Time Contract', 'Full time contract Employee', 1, '2025-07-10 18:13:23', 'N', 'N'),
(2, '2025-07-10 18:13:49', 'Internship', 'Employee on internship.', 1, '2025-07-10 18:13:49', 'N', 'N'),
(3, '2025-10-25 21:19:26', 'Permanent', 'Permanent employee', 1, '2025-10-25 21:19:26', 'N', 'N'),
(4, '2025-10-25 21:19:26', 'Contract', 'Contract employee', 1, '2025-10-25 21:19:26', 'N', 'N'),
(5, '2025-10-25 21:19:26', 'Part-Time', 'Part-time employee', 1, '2025-10-25 21:19:26', 'N', 'N'),
(6, '2025-10-25 21:19:26', 'Consultant', 'Consultant', 1, '2025-10-25 21:19:26', 'N', 'N'),
(7, '2025-10-25 21:19:26', 'Temporary', 'Temporary employee', 1, '2025-10-25 21:19:26', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entities`
--

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
(1, '2025-11-21 06:58:17', 'SBSL Kenya', NULL, 1, 1, 0, 0, '98309', '', 'Nairobi', '25', 254, 'info@sbsl.co.ke', '2025-11-21 09:58:17', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entity_hr_assignments`
--

CREATE TABLE `tija_entity_hr_assignments` (
  `assignmentID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `roleType` enum('primary','substitute') NOT NULL DEFAULT 'primary',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('N','Y') NOT NULL DEFAULT 'N',
  `Suspended` enum('N','Y') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_entity_hr_assignments`
--

INSERT INTO `tija_entity_hr_assignments` (`assignmentID`, `entityID`, `userID`, `roleType`, `DateAdded`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(6, 1, 4, 'primary', '2025-12-01 10:23:28', '2025-12-01 10:23:28', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entity_role_types`
--

CREATE TABLE `tija_entity_role_types` (
  `roleTypeID` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `roleTypeName` varchar(100) NOT NULL COMMENT 'Display name (e.g., Executive, Management)',
  `roleTypeCode` varchar(20) NOT NULL COMMENT 'Short code (e.g., EXEC, MGT)',
  `roleTypeDescription` text DEFAULT NULL COMMENT 'Description of the role type',
  `displayOrder` int(11) DEFAULT 0 COMMENT 'Order for display in dropdowns',
  `colorCode` varchar(7) DEFAULT '#667eea' COMMENT 'Hex color code for badges',
  `iconClass` varchar(50) DEFAULT 'fa-user-tie' COMMENT 'FontAwesome icon class',
  `isDefault` enum('Y','N') DEFAULT 'N' COMMENT 'Is this a default/system role type',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Is this role type active',
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role types for organizational roles';

--
-- Dumping data for table `tija_entity_role_types`
--

INSERT INTO `tija_entity_role_types` (`roleTypeID`, `DateAdded`, `roleTypeName`, `roleTypeCode`, `roleTypeDescription`, `displayOrder`, `colorCode`, `iconClass`, `isDefault`, `isActive`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-14 12:41:34', 'Executive', 'EXEC', 'C-Level, Top Leadership', 1, '#dc3545', 'fa-crown', 'Y', 'Y', '2025-11-14 09:41:34', NULL, 'N', 'N'),
(2, '2025-11-14 12:41:34', 'Management', 'MGT', 'Directors, Managers', 2, '#ffc107', 'fa-user-tie', 'Y', 'Y', '2025-11-14 09:41:34', NULL, 'N', 'N'),
(3, '2025-11-14 12:41:34', 'Supervisory', 'SUPV', 'Team Leads, Supervisors', 3, '#17a2b8', 'fa-user-shield', 'Y', 'Y', '2025-11-14 09:41:34', NULL, 'N', 'N'),
(4, '2025-11-14 12:41:34', 'Operational', 'OPR', 'Officers, Staff (Default)', 4, '#28a745', 'fa-user', 'Y', 'Y', '2025-11-14 09:41:34', NULL, 'N', 'N'),
(5, '2025-11-14 12:41:34', 'Support', 'SUPP', 'Administrative, Assistants', 5, '#6c757d', 'fa-user-cog', 'Y', 'Y', '2025-11-14 09:41:34', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entity_types`
--

CREATE TABLE `tija_entity_types` (
  `entityTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `entityTypeTitle` varchar(255) NOT NULL,
  `entityTypeDescription` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_entity_types`
--

INSERT INTO `tija_entity_types` (`entityTypeID`, `DateAdded`, `entityTypeTitle`, `entityTypeDescription`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-01 22:43:32', 'company', 'company', '2025-02-01 22:43:32', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense`
--

CREATE TABLE `tija_expense` (
  `expenseID` int(11) NOT NULL COMMENT 'Unique expense identifier',
  `expenseNumber` varchar(50) NOT NULL COMMENT 'Unique expense reference number (e.g., EXP-202412-0001)',
  `expenseCode` varchar(20) DEFAULT NULL COMMENT 'Short expense code for quick reference',
  `employeeID` int(11) NOT NULL COMMENT 'ID of employee who incurred the expense',
  `employeeCode` varchar(20) DEFAULT NULL COMMENT 'Employee code for quick reference',
  `expenseTypeID` int(11) NOT NULL COMMENT 'Reference to expense type (travel, meals, etc.)',
  `expenseCategoryID` int(11) NOT NULL COMMENT 'Reference to expense category',
  `expenseStatusID` int(11) NOT NULL DEFAULT 1 COMMENT 'Current status of the expense',
  `projectID` int(11) DEFAULT NULL COMMENT 'Associated project ID if applicable',
  `clientID` int(11) DEFAULT NULL COMMENT 'Associated client ID if applicable',
  `salesCaseID` int(11) DEFAULT NULL COMMENT 'Associated sales case ID if applicable',
  `departmentID` int(11) DEFAULT NULL COMMENT 'Department ID for expense allocation',
  `expenseDate` date NOT NULL COMMENT 'Date when expense was incurred',
  `submissionDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Date when expense was submitted',
  `description` text NOT NULL COMMENT 'Detailed description of the expense',
  `shortDescription` varchar(255) DEFAULT NULL COMMENT 'Brief description for quick reference',
  `amount` decimal(12,2) NOT NULL COMMENT 'Expense amount (supports up to 999,999,999.99)',
  `currency` varchar(3) NOT NULL DEFAULT 'KES' COMMENT 'Currency code (ISO 4217)',
  `exchangeRate` decimal(10,6) DEFAULT 1.000000 COMMENT 'Exchange rate if different from base currency',
  `baseAmount` decimal(12,2) DEFAULT NULL COMMENT 'Amount converted to base currency',
  `taxAmount` decimal(10,2) DEFAULT 0.00 COMMENT 'Tax amount included in expense',
  `taxRate` decimal(5,2) DEFAULT 0.00 COMMENT 'Tax rate percentage',
  `netAmount` decimal(12,2) DEFAULT NULL COMMENT 'Net amount after tax deductions',
  `receiptRequired` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether receipt is mandatory',
  `receiptAttached` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether receipt is attached',
  `receiptPath` varchar(500) DEFAULT NULL COMMENT 'File path to receipt attachment',
  `receiptFileName` varchar(255) DEFAULT NULL COMMENT 'Original receipt filename',
  `receiptFileSize` int(11) DEFAULT NULL COMMENT 'Receipt file size in bytes',
  `receiptMimeType` varchar(100) DEFAULT NULL COMMENT 'Receipt file MIME type',
  `approvalRequired` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether approval is required',
  `approvalLevel` int(11) DEFAULT 1 COMMENT 'Required approval level',
  `approvedBy` int(11) DEFAULT NULL COMMENT 'ID of person who approved',
  `approvalDate` datetime DEFAULT NULL COMMENT 'Date of approval',
  `approvalNotes` text DEFAULT NULL COMMENT 'Notes from approver',
  `approvalDeadline` datetime DEFAULT NULL COMMENT 'Approval deadline',
  `rejectedBy` int(11) DEFAULT NULL COMMENT 'ID of person who rejected',
  `rejectionDate` datetime DEFAULT NULL COMMENT 'Date of rejection',
  `rejectionReason` text DEFAULT NULL COMMENT 'Reason for rejection',
  `rejectionCode` varchar(20) DEFAULT NULL COMMENT 'Standardized rejection code',
  `paymentMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD','MOBILE_MONEY') DEFAULT 'BANK_TRANSFER' COMMENT 'Method of payment',
  `paymentDate` datetime DEFAULT NULL COMMENT 'Date of payment',
  `paymentReference` varchar(100) DEFAULT NULL COMMENT 'Payment reference number',
  `paidBy` int(11) DEFAULT NULL COMMENT 'ID of person who processed payment',
  `paymentNotes` text DEFAULT NULL COMMENT 'Payment processing notes',
  `paymentDeadline` datetime DEFAULT NULL COMMENT 'Payment deadline',
  `reimbursementAmount` decimal(12,2) DEFAULT NULL COMMENT 'Amount to be reimbursed',
  `reimbursementRate` decimal(5,2) DEFAULT 100.00 COMMENT 'Percentage of expense to reimburse',
  `reimbursementMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD','MOBILE_MONEY') DEFAULT 'BANK_TRANSFER' COMMENT 'Method of reimbursement',
  `reimbursementDate` datetime DEFAULT NULL COMMENT 'Date of reimbursement',
  `reimbursementReference` varchar(100) DEFAULT NULL COMMENT 'Reimbursement reference',
  `budgetCode` varchar(50) DEFAULT NULL COMMENT 'Budget code for expense allocation',
  `costCenter` varchar(50) DEFAULT NULL COMMENT 'Cost center for expense tracking',
  `budgetYear` year(4) DEFAULT NULL COMMENT 'Budget year',
  `budgetMonth` tinyint(4) DEFAULT NULL COMMENT 'Budget month (1-12)',
  `location` varchar(255) DEFAULT NULL COMMENT 'Location where expense was incurred',
  `vendor` varchar(255) DEFAULT NULL COMMENT 'Vendor or merchant name',
  `vendorCode` varchar(50) DEFAULT NULL COMMENT 'Vendor code for tracking',
  `invoiceNumber` varchar(100) DEFAULT NULL COMMENT 'Invoice number if applicable',
  `invoiceDate` date DEFAULT NULL COMMENT 'Invoice date if applicable',
  `isRecurring` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this is a recurring expense',
  `recurringFrequency` enum('DAILY','WEEKLY','MONTHLY','QUARTERLY','YEARLY') DEFAULT NULL COMMENT 'Frequency of recurring expense',
  `isBillable` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expense can be billed to client',
  `isTaxDeductible` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expense is tax deductible',
  `requiresJustification` enum('Y','N') DEFAULT 'N' COMMENT 'Whether detailed justification is required',
  `isUrgent` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expense requires urgent processing',
  `orgDataID` int(11) NOT NULL COMMENT 'Organization data ID',
  `entityID` int(11) NOT NULL COMMENT 'Entity ID for multi-tenant support',
  `createdBy` int(11) NOT NULL COMMENT 'ID of user who created the record',
  `createdDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Record creation timestamp',
  `lastUpdatedBy` int(11) DEFAULT NULL COMMENT 'ID of user who last updated the record',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Last update timestamp',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether record is suspended',
  `isDeleted` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Soft delete flag',
  `deletedBy` int(11) DEFAULT NULL COMMENT 'ID of user who deleted the record',
  `deletedDate` datetime DEFAULT NULL COMMENT 'Soft delete timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comprehensive expense management table with full audit trail and workflow support';

--
-- Triggers `tija_expense`
--
DELIMITER $$
CREATE TRIGGER `tr_expense_calculate_net_amount` BEFORE INSERT ON `tija_expense` FOR EACH ROW BEGIN
    -- Calculate net amount (amount - tax)
    IF NEW.taxAmount IS NOT NULL AND NEW.taxAmount > 0 THEN
        SET NEW.netAmount = NEW.amount - NEW.taxAmount;
    ELSE
        SET NEW.netAmount = NEW.amount;
    END IF;

    -- Calculate base amount if exchange rate is provided
    IF NEW.exchangeRate IS NOT NULL AND NEW.exchangeRate != 1.000000 THEN
        SET NEW.baseAmount = NEW.amount * NEW.exchangeRate;
    ELSE
        SET NEW.baseAmount = NEW.amount;
    END IF;

    -- Set reimbursement amount if not provided
    IF NEW.reimbursementAmount IS NULL THEN
        SET NEW.reimbursementAmount = NEW.netAmount * (NEW.reimbursementRate / 100);
    END IF;

    -- Generate expense number if not provided
    IF NEW.expenseNumber IS NULL OR NEW.expenseNumber = '' THEN
        SET NEW.expenseNumber = CONCAT('EXP-', YEAR(NEW.expenseDate), LPAD(MONTH(NEW.expenseDate), 2, '0'), '-', LPAD((SELECT COUNT(*) + 1 FROM tija_expense WHERE YEAR(expenseDate) = YEAR(NEW.expenseDate) AND MONTH(expenseDate) = MONTH(NEW.expenseDate)), 4, '0'));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_expense_update_net_amount` BEFORE UPDATE ON `tija_expense` FOR EACH ROW BEGIN
    -- Recalculate net amount if amount or tax changes
    IF OLD.amount != NEW.amount OR OLD.taxAmount != NEW.taxAmount THEN
        IF NEW.taxAmount IS NOT NULL AND NEW.taxAmount > 0 THEN
            SET NEW.netAmount = NEW.amount - NEW.taxAmount;
        ELSE
            SET NEW.netAmount = NEW.amount;
        END IF;
    END IF;

    -- Recalculate base amount if amount or exchange rate changes
    IF OLD.amount != NEW.amount OR OLD.exchangeRate != NEW.exchangeRate THEN
        IF NEW.exchangeRate IS NOT NULL AND NEW.exchangeRate != 1.000000 THEN
            SET NEW.baseAmount = NEW.amount * NEW.exchangeRate;
        ELSE
            SET NEW.baseAmount = NEW.amount;
        END IF;
    END IF;

    -- Update reimbursement amount if reimbursement rate changes
    IF OLD.reimbursementRate != NEW.reimbursementRate THEN
        SET NEW.reimbursementAmount = NEW.netAmount * (NEW.reimbursementRate / 100);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_expense_validate_data` BEFORE INSERT ON `tija_expense` FOR EACH ROW BEGIN
    -- Validate amount is positive
    IF NEW.amount <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Expense amount must be greater than zero';
    END IF;

    -- Validate expense date is not in the future
    IF NEW.expenseDate > CURDATE() THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Expense date cannot be in the future';
    END IF;

    -- Validate tax amount is not negative
    IF NEW.taxAmount < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tax amount cannot be negative';
    END IF;

    -- Validate reimbursement rate is between 0 and 100
    IF NEW.reimbursementRate < 0 OR NEW.reimbursementRate > 100 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Reimbursement rate must be between 0 and 100';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expenses`
--

CREATE TABLE `tija_expenses` (
  `expenseID` int(11) NOT NULL,
  `expenseNumber` varchar(50) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `expenseTypeID` int(11) NOT NULL,
  `expenseCategoryID` int(11) NOT NULL,
  `expenseStatusID` int(11) NOT NULL DEFAULT 1,
  `projectID` int(11) DEFAULT NULL,
  `clientID` int(11) DEFAULT NULL,
  `salesCaseID` int(11) DEFAULT NULL,
  `expenseDate` date NOT NULL,
  `submissionDate` datetime NOT NULL DEFAULT current_timestamp(),
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'KES',
  `receiptRequired` enum('Y','N') DEFAULT 'Y',
  `receiptAttached` enum('Y','N') DEFAULT 'N',
  `receiptPath` varchar(255) DEFAULT NULL,
  `approvalRequired` enum('Y','N') DEFAULT 'Y',
  `approvedBy` int(11) DEFAULT NULL,
  `approvalDate` datetime DEFAULT NULL,
  `approvalNotes` text DEFAULT NULL,
  `rejectedBy` int(11) DEFAULT NULL,
  `rejectionDate` datetime DEFAULT NULL,
  `rejectionReason` text DEFAULT NULL,
  `paymentMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH') DEFAULT 'BANK_TRANSFER',
  `paymentDate` datetime DEFAULT NULL,
  `paymentReference` varchar(100) DEFAULT NULL,
  `paidBy` int(11) DEFAULT NULL,
  `paymentNotes` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdDate` datetime NOT NULL DEFAULT current_timestamp(),
  `lastUpdatedBy` int(11) DEFAULT NULL,
  `lastUpdated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_approvals`
--

CREATE TABLE `tija_expense_approvals` (
  `approvalID` int(11) NOT NULL,
  `expenseID` int(11) NOT NULL,
  `approverID` int(11) NOT NULL,
  `approvalLevel` int(11) NOT NULL DEFAULT 1,
  `approvalStatus` enum('PENDING','APPROVED','REJECTED','DELEGATED') DEFAULT 'PENDING',
  `approvalDate` datetime DEFAULT NULL,
  `approvalNotes` text DEFAULT NULL,
  `delegatedTo` int(11) DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdDate` datetime NOT NULL DEFAULT current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_attachments`
--

CREATE TABLE `tija_expense_attachments` (
  `attachmentID` int(11) NOT NULL,
  `expenseID` int(11) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `filePath` varchar(500) NOT NULL,
  `fileSize` int(11) DEFAULT NULL,
  `fileType` varchar(50) DEFAULT NULL,
  `uploadedBy` int(11) NOT NULL,
  `uploadDate` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_categories`
--

CREATE TABLE `tija_expense_categories` (
  `expenseCategoryID` int(11) NOT NULL COMMENT 'Unique identifier for expense category',
  `categoryName` varchar(100) NOT NULL COMMENT 'Display name of the expense category',
  `categoryDescription` text DEFAULT NULL COMMENT 'Detailed description of the category and its purpose',
  `categoryCode` varchar(20) NOT NULL COMMENT 'Short code for the category (e.g., TRAVEL, MEALS)',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether the category is currently active and available for use',
  `requiresReceipt` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether receipts are mandatory for expenses in this category',
  `maxAmount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum allowed amount for expenses in this category (NULL = no limit)',
  `minAmount` decimal(10,2) DEFAULT NULL COMMENT 'Minimum amount for expenses in this category (NULL = no minimum)',
  `requiresApproval` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses in this category require approval',
  `approvalLevel` int(11) DEFAULT 1 COMMENT 'Required approval level (1=Manager, 2=Director, etc.)',
  `autoApproveLimit` decimal(10,2) DEFAULT NULL COMMENT 'Amount below which expenses are auto-approved (NULL = manual approval always required)',
  `hasBudgetLimit` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this category has a budget limit',
  `monthlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Monthly budget limit for this category',
  `yearlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Yearly budget limit for this category',
  `budgetPeriod` enum('MONTHLY','QUARTERLY','YEARLY') DEFAULT 'MONTHLY' COMMENT 'Budget period for tracking',
  `parentCategoryID` int(11) DEFAULT NULL COMMENT 'Parent category ID for hierarchical organization',
  `categoryLevel` int(11) DEFAULT 1 COMMENT 'Level in category hierarchy (1=top level)',
  `sortOrder` int(11) DEFAULT 0 COMMENT 'Display order for category listing',
  `isTaxable` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses in this category are subject to tax',
  `taxRate` decimal(5,2) DEFAULT NULL COMMENT 'Tax rate percentage for this category (NULL = use default)',
  `taxInclusive` enum('Y','N') DEFAULT 'N' COMMENT 'Whether amounts include tax (Y) or are tax-exclusive (N)',
  `reimbursementRate` decimal(5,2) DEFAULT 100.00 COMMENT 'Percentage of expense amount that can be reimbursed (100 = full reimbursement)',
  `reimbursementMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD') DEFAULT 'BANK_TRANSFER' COMMENT 'Default reimbursement method for this category',
  `requiresJustification` enum('Y','N') DEFAULT 'N' COMMENT 'Whether detailed justification is required for expenses in this category',
  `requiresProjectLink` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses must be linked to a project',
  `requiresClientLink` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses must be linked to a client',
  `requiresSalesCaseLink` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses must be linked to a sales case',
  `notifyOnSubmission` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify approvers when expenses are submitted in this category',
  `notifyOnApproval` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify employee when expenses are approved',
  `notifyOnRejection` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify employee when expenses are rejected',
  `orgDataID` int(11) NOT NULL COMMENT 'Organization data identifier',
  `entityID` int(11) NOT NULL COMMENT 'Entity identifier within organization',
  `createdBy` int(11) NOT NULL COMMENT 'User ID who created this category',
  `createdDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Date and time when category was created',
  `lastUpdatedBy` int(11) DEFAULT NULL COMMENT 'User ID who last updated this category',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Date and time when category was last updated',
  `Suspended` enum('Y','N') DEFAULT 'N' COMMENT 'Whether the category is suspended/deleted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Expense categories for organizing and managing different types of business expenses';

--
-- Dumping data for table `tija_expense_categories`
--

INSERT INTO `tija_expense_categories` (`expenseCategoryID`, `categoryName`, `categoryDescription`, `categoryCode`, `isActive`, `requiresReceipt`, `maxAmount`, `minAmount`, `requiresApproval`, `approvalLevel`, `autoApproveLimit`, `hasBudgetLimit`, `monthlyBudgetLimit`, `yearlyBudgetLimit`, `budgetPeriod`, `parentCategoryID`, `categoryLevel`, `sortOrder`, `isTaxable`, `taxRate`, `taxInclusive`, `reimbursementRate`, `reimbursementMethod`, `requiresJustification`, `requiresProjectLink`, `requiresClientLink`, `requiresSalesCaseLink`, `notifyOnSubmission`, `notifyOnApproval`, `notifyOnRejection`, `orgDataID`, `entityID`, `createdBy`, `createdDate`, `lastUpdatedBy`, `lastUpdated`, `Suspended`) VALUES
(1, 'Travel', 'Business travel expenses including flights, accommodation, meals, and local transport', 'TRAVEL', 'Y', 'Y', 100000.00, 100.00, 'Y', 2, 5000.00, 'Y', 50000.00, 500000.00, 'MONTHLY', NULL, 1, 1, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(2, 'Meals & Entertainment', 'Client meetings, business meals, entertainment expenses, and hospitality', 'MEALS', 'Y', 'Y', 15000.00, 50.00, 'Y', 1, 2000.00, 'Y', 20000.00, 200000.00, 'MONTHLY', NULL, 1, 2, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(3, 'Office Supplies', 'Stationery, office equipment, supplies, and consumables', 'OFFICE', 'Y', 'Y', 10000.00, 10.00, 'Y', 1, 1000.00, 'Y', 15000.00, 150000.00, 'MONTHLY', NULL, 1, 3, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(4, 'Communication', 'Phone bills, internet, mobile data, and communication expenses', 'COMM', 'Y', 'Y', 5000.00, 50.00, 'Y', 1, 500.00, 'Y', 10000.00, 100000.00, 'MONTHLY', NULL, 1, 4, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(5, 'Transportation', 'Local transport, fuel, parking fees, and vehicle expenses', 'TRANS', 'Y', 'Y', 3000.00, 20.00, 'Y', 1, 500.00, 'Y', 8000.00, 80000.00, 'MONTHLY', NULL, 1, 5, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(6, 'Training & Development', 'Courses, conferences, professional development, and educational expenses', 'TRAINING', 'Y', 'Y', 50000.00, 100.00, 'Y', 2, 5000.00, 'Y', 30000.00, 300000.00, 'MONTHLY', NULL, 1, 6, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(7, 'Marketing', 'Marketing materials, advertising, promotional items, and brand expenses', 'MARKETING', 'Y', 'Y', 25000.00, 100.00, 'Y', 2, 2000.00, 'Y', 20000.00, 200000.00, 'MONTHLY', NULL, 1, 7, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(8, 'Equipment', 'IT equipment, tools, machinery, and capital expenses', 'EQUIPMENT', 'Y', 'Y', 200000.00, 500.00, 'Y', 3, 10000.00, 'Y', 50000.00, 500000.00, 'MONTHLY', NULL, 1, 8, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(9, 'Utilities', 'Electricity, water, office utilities, and facility expenses', 'UTILITIES', 'Y', 'Y', 30000.00, 100.00, 'Y', 1, 2000.00, 'Y', 25000.00, 250000.00, 'MONTHLY', NULL, 1, 9, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N'),
(10, 'Miscellaneous', 'Other business expenses not covered by specific categories', 'MISC', 'Y', 'Y', 5000.00, 10.00, 'Y', 1, 500.00, 'Y', 10000.00, 100000.00, 'MONTHLY', NULL, 1, 10, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_status`
--

CREATE TABLE `tija_expense_status` (
  `expenseStatusID` int(11) NOT NULL COMMENT 'Unique identifier for expense status',
  `statusName` varchar(50) NOT NULL COMMENT 'Display name of the expense status',
  `statusDescription` text DEFAULT NULL COMMENT 'Detailed description of the status and its meaning',
  `statusCode` varchar(20) NOT NULL COMMENT 'Short code for the status (e.g., DRAFT, SUBMITTED, APPROVED)',
  `statusColor` varchar(7) DEFAULT '#6c757d' COMMENT 'Hex color code for status display (e.g., #28a745 for green)',
  `statusIcon` varchar(50) DEFAULT NULL COMMENT 'Icon class or name for status display',
  `statusPriority` int(11) DEFAULT 0 COMMENT 'Priority level for status ordering (higher = more important)',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether the status is currently active and available for use',
  `isInitialStatus` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this is the initial status for new expenses',
  `isFinalStatus` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this is a final status (no further transitions allowed)',
  `requiresAction` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this status requires user action to proceed',
  `isApprovalStatus` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this status represents an approval state',
  `isRejectionStatus` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this status represents a rejection state',
  `isPendingStatus` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this status represents a pending state',
  `isPaidStatus` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this status represents a paid state',
  `allowedTransitions` text DEFAULT NULL COMMENT 'JSON array of status IDs that can transition from this status',
  `blockedTransitions` text DEFAULT NULL COMMENT 'JSON array of status IDs that cannot transition from this status',
  `autoTransitionAfter` int(11) DEFAULT NULL COMMENT 'Days after which status auto-transitions (NULL = no auto-transition)',
  `autoTransitionTo` int(11) DEFAULT NULL COMMENT 'Status ID to auto-transition to after specified days',
  `notifyEmployee` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify employee when expense reaches this status',
  `notifyApprover` enum('Y','N') DEFAULT 'N' COMMENT 'Whether to notify approver when expense reaches this status',
  `notifyFinance` enum('Y','N') DEFAULT 'N' COMMENT 'Whether to notify finance team when expense reaches this status',
  `notifyManager` enum('Y','N') DEFAULT 'N' COMMENT 'Whether to notify manager when expense reaches this status',
  `emailTemplate` varchar(100) DEFAULT NULL COMMENT 'Email template name for notifications',
  `smsTemplate` varchar(100) DEFAULT NULL COMMENT 'SMS template name for notifications',
  `notificationSubject` varchar(200) DEFAULT NULL COMMENT 'Default notification subject line',
  `allowsEditing` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses in this status can be edited',
  `allowsDeletion` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses in this status can be deleted',
  `allowsAttachment` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether attachments can be added in this status',
  `requiresComment` enum('Y','N') DEFAULT 'N' COMMENT 'Whether a comment is required when transitioning to this status',
  `showInDashboard` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to show expenses with this status in dashboard',
  `showInReports` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to include expenses with this status in reports',
  `showInKanban` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to show expenses with this status in kanban board',
  `kanbanColumnTitle` varchar(50) DEFAULT NULL COMMENT 'Custom title for kanban column (NULL = use statusName)',
  `orgDataID` int(11) NOT NULL COMMENT 'Organization data identifier',
  `entityID` int(11) NOT NULL COMMENT 'Entity identifier within organization',
  `createdBy` int(11) NOT NULL COMMENT 'User ID who created this status',
  `createdDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Date and time when status was created',
  `lastUpdatedBy` int(11) DEFAULT NULL COMMENT 'User ID who last updated this status',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Date and time when status was last updated',
  `Suspended` enum('Y','N') DEFAULT 'N' COMMENT 'Whether the status is suspended/deleted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Expense status definitions for managing expense workflow states';

--
-- Dumping data for table `tija_expense_status`
--

INSERT INTO `tija_expense_status` (`expenseStatusID`, `statusName`, `statusDescription`, `statusCode`, `statusColor`, `statusIcon`, `statusPriority`, `isActive`, `isInitialStatus`, `isFinalStatus`, `requiresAction`, `isApprovalStatus`, `isRejectionStatus`, `isPendingStatus`, `isPaidStatus`, `allowedTransitions`, `blockedTransitions`, `autoTransitionAfter`, `autoTransitionTo`, `notifyEmployee`, `notifyApprover`, `notifyFinance`, `notifyManager`, `emailTemplate`, `smsTemplate`, `notificationSubject`, `allowsEditing`, `allowsDeletion`, `allowsAttachment`, `requiresComment`, `showInDashboard`, `showInReports`, `showInKanban`, `kanbanColumnTitle`, `orgDataID`, `entityID`, `createdBy`, `createdDate`, `lastUpdatedBy`, `lastUpdated`, `Suspended`) VALUES
(1, 'Draft', 'Expense is being prepared and not yet submitted for approval', 'DRAFT', '#6c757d', 'ri-draft-line', 1, 'Y', 'Y', 'N', 'N', 'N', 'N', 'N', 'N', '[2,5]', NULL, NULL, NULL, 'N', 'N', 'N', 'N', 'expense_draft', NULL, 'Expense Draft Created', 'Y', 'Y', 'Y', 'N', 'Y', 'N', 'Y', 'Draft', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(2, 'Submitted', 'Expense has been submitted and is awaiting review', 'SUBMITTED', '#ffc107', 'ri-send-plane-line', 2, 'Y', 'N', 'N', 'Y', 'N', 'N', 'Y', 'N', '[3,4,5]', NULL, NULL, NULL, 'Y', 'Y', 'N', 'N', 'expense_submitted', NULL, 'Expense Submitted for Approval', 'N', 'N', 'Y', 'N', 'Y', 'Y', 'Y', 'Submitted', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(3, 'Under Review', 'Expense is being reviewed by approver', 'UNDER_REVIEW', '#17a2b8', 'ri-eye-line', 3, 'Y', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'N', '[4,5,6]', NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', 'expense_under_review', NULL, 'Expense Under Review', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Under Review', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(4, 'Approved', 'Expense has been approved and is ready for payment', 'APPROVED', '#28a745', 'ri-check-line', 4, 'Y', 'N', 'N', 'N', 'Y', 'N', 'N', 'N', '[6,7]', NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', 'expense_approved', NULL, 'Expense Approved', 'N', 'N', 'Y', 'N', 'Y', 'Y', 'Y', 'Approved', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(5, 'Rejected', 'Expense has been rejected and requires revision', 'REJECTED', '#dc3545', 'ri-close-line', 5, 'Y', 'N', 'N', 'Y', 'N', 'Y', 'N', 'N', '[1,2]', NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', 'expense_rejected', NULL, 'Expense Rejected', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Rejected', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(6, 'Paid', 'Expense has been paid and reimbursement completed', 'PAID', '#20c997', 'ri-money-dollar-circle-line', 6, 'Y', 'N', 'Y', 'N', 'N', 'N', 'N', 'Y', '[]', NULL, NULL, NULL, 'Y', 'N', 'N', 'N', 'expense_paid', NULL, 'Expense Paid', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Paid', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(7, 'Overdue', 'Payment is overdue and requires immediate attention', 'OVERDUE', '#fd7e14', 'ri-alert-line', 7, 'Y', 'N', 'N', 'Y', 'N', 'N', 'Y', 'N', '[6,8]', NULL, NULL, NULL, 'Y', 'N', 'Y', 'Y', 'expense_overdue', NULL, 'Expense Payment Overdue', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Overdue', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(8, 'Cancelled', 'Expense has been cancelled and will not be processed', 'CANCELLED', '#6c757d', 'ri-close-circle-line', 8, 'Y', 'N', 'Y', 'N', 'N', 'N', 'N', 'N', '[]', NULL, NULL, NULL, 'Y', 'N', 'N', 'N', 'expense_cancelled', NULL, 'Expense Cancelled', 'N', 'N', 'N', 'N', 'N', 'N', 'N', 'Cancelled', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(9, 'On Hold', 'Expense processing is temporarily suspended', 'ON_HOLD', '#ffc107', 'ri-pause-circle-line', 9, 'Y', 'N', 'N', 'Y', 'N', 'N', 'Y', 'N', '[3,4,5,8]', NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', 'expense_on_hold', NULL, 'Expense On Hold', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'On Hold', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N'),
(10, 'Under Dispute', 'Expense is under dispute and requires resolution', 'UNDER_DISPUTE', '#dc3545', 'ri-question-line', 10, 'Y', 'N', 'N', 'Y', 'N', 'N', 'Y', 'N', '[4,5,8]', NULL, NULL, NULL, 'Y', 'N', 'Y', 'Y', 'expense_dispute', NULL, 'Expense Under Dispute', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Under Dispute', 1, 1, 1, '2025-09-17 18:18:54', NULL, NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_types`
--

CREATE TABLE `tija_expense_types` (
  `expenseTypeID` int(11) NOT NULL,
  `typeName` varchar(100) NOT NULL COMMENT 'Display name of the expense type',
  `typeDescription` text DEFAULT NULL COMMENT 'Detailed description of the expense type',
  `typeCode` varchar(20) NOT NULL COMMENT 'Short code for the expense type (e.g., REIMB)',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether the expense type is currently active and available for use',
  `isReimbursable` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses of this type are reimbursable',
  `isPettyCash` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this is a petty cash expense type',
  `requiresReceipt` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether receipts are mandatory for expenses of this type',
  `maxAmount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum allowed amount for expenses of this type (NULL = no limit)',
  `minAmount` decimal(10,2) DEFAULT NULL COMMENT 'Minimum amount for expenses of this type (NULL = no minimum)',
  `requiresApproval` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses of this type require approval',
  `approvalLimit` decimal(10,2) DEFAULT NULL COMMENT 'Amount above which approval is required',
  `approvalLevel` int(11) DEFAULT 1 COMMENT 'Required approval level (1=Manager, 2=Director, etc.)',
  `autoApproveLimit` decimal(10,2) DEFAULT NULL COMMENT 'Amount below which expenses are auto-approved (NULL = manual approval always required)',
  `hasBudgetLimit` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this expense type has a budget limit',
  `monthlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Monthly budget limit for this expense type',
  `yearlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Yearly budget limit for this expense type',
  `budgetPeriod` enum('MONTHLY','QUARTERLY','YEARLY') DEFAULT 'MONTHLY' COMMENT 'Budget period for tracking',
  `parentTypeID` int(11) DEFAULT NULL COMMENT 'Parent expense type ID for hierarchical organization',
  `typeLevel` int(11) DEFAULT 1 COMMENT 'Level in expense type hierarchy (1=top level)',
  `sortOrder` int(11) DEFAULT 0 COMMENT 'Display order for expense type listing',
  `isTaxable` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses of this type are subject to tax',
  `taxRate` decimal(5,2) DEFAULT NULL COMMENT 'Tax rate percentage for this expense type (NULL = use default)',
  `taxInclusive` enum('Y','N') DEFAULT 'N' COMMENT 'Whether amounts include tax (Y) or are tax-exclusive (N)',
  `reimbursementRate` decimal(5,2) DEFAULT 100.00 COMMENT 'Percentage of expense amount that can be reimbursed (100 = full reimbursement)',
  `reimbursementMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD') DEFAULT 'BANK_TRANSFER' COMMENT 'Default reimbursement method for this expense type',
  `requiresJustification` enum('Y','N') DEFAULT 'N' COMMENT 'Whether detailed justification is required for expenses of this type',
  `requiresProjectLink` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses must be linked to a project',
  `requiresClientLink` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses must be linked to a client',
  `requiresSalesCaseLink` enum('Y','N') DEFAULT 'N' COMMENT 'Whether expenses must be linked to a sales case',
  `notifyOnSubmission` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify approvers when expenses are submitted of this type',
  `notifyOnApproval` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify employees when expenses are approved',
  `notifyOnRejection` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify employees when expenses are rejected',
  `notifyOnPayment` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether to notify employees when expenses are paid',
  `defaultCurrency` varchar(3) DEFAULT 'KES' COMMENT 'Default currency for expenses of this type',
  `expenseValidityDays` int(11) DEFAULT 30 COMMENT 'Number of days after expense date that submission is valid',
  `submissionDeadlineDays` int(11) DEFAULT 7 COMMENT 'Number of days after expense date to submit for reimbursement',
  `approvalDeadlineDays` int(11) DEFAULT 3 COMMENT 'Number of days for approval deadline',
  `paymentDeadlineDays` int(11) DEFAULT 7 COMMENT 'Number of days for payment after approval',
  `orgDataID` int(11) NOT NULL COMMENT 'Organization data ID',
  `entityID` int(11) NOT NULL COMMENT 'Entity ID',
  `createdBy` int(10) UNSIGNED NOT NULL COMMENT 'ID of user who created this record',
  `createdDate` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Timestamp when record was created',
  `lastUpdatedBy` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID of user who last updated this record',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'Timestamp of last update',
  `Suspended` enum('Y','N') DEFAULT 'N' COMMENT 'Whether the expense type is suspended'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Comprehensive expense types table with advanced configuration options';

--
-- Dumping data for table `tija_expense_types`
--

INSERT INTO `tija_expense_types` (`expenseTypeID`, `typeName`, `typeDescription`, `typeCode`, `isActive`, `isReimbursable`, `isPettyCash`, `requiresReceipt`, `maxAmount`, `minAmount`, `requiresApproval`, `approvalLimit`, `approvalLevel`, `autoApproveLimit`, `hasBudgetLimit`, `monthlyBudgetLimit`, `yearlyBudgetLimit`, `budgetPeriod`, `parentTypeID`, `typeLevel`, `sortOrder`, `isTaxable`, `taxRate`, `taxInclusive`, `reimbursementRate`, `reimbursementMethod`, `requiresJustification`, `requiresProjectLink`, `requiresClientLink`, `requiresSalesCaseLink`, `notifyOnSubmission`, `notifyOnApproval`, `notifyOnRejection`, `notifyOnPayment`, `defaultCurrency`, `expenseValidityDays`, `submissionDeadlineDays`, `approvalDeadlineDays`, `paymentDeadlineDays`, `orgDataID`, `entityID`, `createdBy`, `createdDate`, `lastUpdatedBy`, `lastUpdated`, `Suspended`) VALUES
(1, 'Reimbursable Expense', 'General reimbursable business expenses', 'REIMB', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 50000.00, 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 1, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(2, 'Petty Cash Expense', 'Small cash expenses handled through petty cash', 'PETTY', 'Y', 'N', 'Y', 'Y', NULL, NULL, 'N', 5000.00, 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 2, 'Y', NULL, 'N', 100.00, 'CASH', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(3, 'Project Expense', 'Expenses directly related to specific projects', 'PROJECT', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 100000.00, 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 3, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'Y', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(4, 'Sales Expense', 'Expenses related to sales activities and client acquisition', 'SALES', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 75000.00, 2, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 4, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'Y', 'Y', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(5, 'Company Expense', 'General company operational expenses', 'COMPANY', 'Y', 'N', 'N', 'Y', NULL, NULL, 'Y', 200000.00, 2, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 5, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(6, 'Travel Expense', 'Business travel and transportation expenses', 'TRAVEL', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 50000.00, 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 6, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(7, 'Meal Expense', 'Business meals and entertainment expenses', 'MEALS', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 15000.00, 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 7, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(8, 'Office Supplies', 'Office supplies and equipment expenses', 'OFFICE', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 10000.00, 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 8, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(9, 'Training Expense', 'Professional development and training expenses', 'TRAINING', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 25000.00, 2, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 9, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N'),
(10, 'Marketing Expense', 'Marketing and advertising expenses', 'MARKETING', 'Y', 'Y', 'N', 'Y', NULL, NULL, 'Y', 30000.00, 2, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 10, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 'Y', 'KES', 30, 7, 3, 7, 1, 1, 1, '2025-09-17 18:34:53', NULL, NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_financial_statements`
--

CREATE TABLE `tija_financial_statements` (
  `financialStatementID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `financialStatementTypeID` int(11) NOT NULL,
  `fiscalYear` int(11) NOT NULL,
  `fiscalPeriod` varchar(40) NOT NULL,
  `periodStartDate` date DEFAULT NULL,
  `periodEndDate` date DEFAULT NULL,
  `statementTypeNode` varchar(255) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL,
  `Suspended` enum('Y','N') NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `financialStatementTypeName` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_financial_statements_types`
--

CREATE TABLE `tija_financial_statements_types` (
  `financialStatementTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `financialStatementTypeName` varchar(255) NOT NULL,
  `financialStatementTypeDescription` text NOT NULL,
  `statementTypeNode` varchar(120) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_financial_statements_types`
--

INSERT INTO `tija_financial_statements_types` (`financialStatementTypeID`, `DateAdded`, `financialStatementTypeName`, `financialStatementTypeDescription`, `statementTypeNode`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-01-17 13:54:47', 'Balance Sheet', '<p>Statement of financial Position. This statement provides a snapshot of a company\'s assets, liabilities, and shareholders\' equity at a specific point in time. It helps in understanding what the company owns and owes.</p>', 'BalanceSheet', '2025-01-17 13:54:47', 'N', 'N'),
(2, '2025-01-17 14:17:54', 'Income Statement', '<p>A financial statement that shows the company\'s revenues, expenses, and profits over a specific period. This statement helps in assessing the company\'s financial performance.</p>', 'Incomestatement', '2025-01-17 14:17:54', 'N', 'N'),
(3, '2025-01-17 14:20:17', 'Cash Flow Statement:', '<p>This Statement tracks the flow of cash in and out of the business and is divided into three sections operating activities, investing activities, and financing activities. It helps in understanding how the company generates and uses its cash.</p>', 'CashFlowStatement', '2025-01-17 14:20:17', 'N', 'N'),
(4, '2025-01-17 14:20:38', 'Statement of Changes in Equity', '<p>This statement shows the changes in the company\'s equity over a specific period. It includes details about retained earnings, dividends paid, and other changes in equity.</p>', 'StatementOfChangesInEquity', '2025-01-17 14:20:38', 'N', 'N'),
(5, '2025-01-18 16:39:26', 'Statement of Investment Allowance', '<p>Statement of Investment Allowance is a formal document or declaration used in accounting and taxation to claim a specific tax benefit provided by governments to encourage investments in eligible assets, such as machinery, equipment, or infrastructure. It outlines the details of the investment made and forms part of the records submitted for tax purposes.</p>', 'StatementofInvestmentAllowance', '2025-01-18 16:39:26', 'N', 'N'),
(7, '2025-01-20 13:38:34', 'Trial Balance', '<p>A financial report that lists all the general ledger accounts of a business and their respective balances at a specific point in time. It serves as a tool to ensure the accuracy of the bookkeeping process by verifying that the total of&nbsp;<strong>debit balances</strong> equals the total of <strong>credit balances</strong>.</p>', 'TrialBalance', '2025-01-20 13:38:34', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_financial_statement_accounts`
--

CREATE TABLE `tija_financial_statement_accounts` (
  `financialStatementAccountID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `accountNode` varchar(256) NOT NULL,
  `accountName` varchar(256) NOT NULL,
  `parentAccountID` int(11) NOT NULL,
  `accountCode` varchar(120) NOT NULL,
  `accountDescription` text DEFAULT NULL,
  `accountType` enum('debit','credit') NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_financial_statement_data`
--

CREATE TABLE `tija_financial_statement_data` (
  `financialStatementDataID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `financialStatementID` int(11) NOT NULL,
  `financialStatementTypeID` int(11) NOT NULL,
  `accountNode` varchar(255) NOT NULL,
  `accountName` varchar(255) NOT NULL,
  `accountCode` varchar(250) NOT NULL,
  `accountDescription` text DEFAULT NULL,
  `accountType` varchar(120) NOT NULL,
  `accountCategory` varchar(256) NOT NULL,
  `debitValue` decimal(20,2) DEFAULT NULL,
  `creditValue` decimal(20,2) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_global_holidays`
--

CREATE TABLE `tija_global_holidays` (
  `holidayID` int(11) NOT NULL,
  `holidayName` varchar(255) NOT NULL COMMENT 'Name of the holiday',
  `holidayDate` date NOT NULL COMMENT 'Date of the holiday',
  `jurisdiction` varchar(100) NOT NULL COMMENT 'Country, state, or "Global"',
  `holidayType` enum('Public','Religious','Cultural','Company','Regional') NOT NULL DEFAULT 'Public',
  `description` text DEFAULT NULL COMMENT 'Description of the holiday',
  `recurring` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether holiday recurs annually',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global holidays for different jurisdictions';

--
-- Dumping data for table `tija_global_holidays`
--

INSERT INTO `tija_global_holidays` (`holidayID`, `holidayName`, `holidayDate`, `jurisdiction`, `holidayType`, `description`, `recurring`, `DateAdded`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, 'New Year\'s Day', '2025-01-01', 'Global', 'Public', 'New Year\'s Day celebration', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(2, 'Good Friday', '2025-04-18', 'Global', 'Religious', 'Good Friday - Christian holiday', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(3, 'Easter Monday', '2025-04-21', 'Global', 'Religious', 'Easter Monday - Christian holiday', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(4, 'Labour Day', '2025-05-01', 'Global', 'Public', 'International Workers\' Day', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(5, 'Christmas Day', '2025-12-25', 'Global', 'Religious', 'Christmas Day celebration', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(6, 'Boxing Day', '2025-12-26', 'Global', 'Public', 'Boxing Day', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(7, 'Madaraka Day', '2025-06-01', 'Kenya', 'Public', 'Madaraka Day - Kenya\'s self-rule day', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(8, 'Eid al-Fitr', '2025-03-30', 'Kenya', 'Religious', 'End of Ramadan', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(9, 'Eid al-Adha', '2025-06-07', 'Kenya', 'Religious', 'Feast of Sacrifice', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(10, 'Mashujaa Day', '2025-10-20', 'Kenya', 'Public', 'Heroes\' Day', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(11, 'Jamhuri Day', '2025-12-12', 'Kenya', 'Public', 'Independence Day', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N'),
(12, 'New Year\'s Day', '2025-01-01', 'Global', 'Public', 'New Year\'s Day celebration', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(13, 'Good Friday', '2025-04-18', 'Global', 'Religious', 'Good Friday - Christian holiday', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(14, 'Easter Monday', '2025-04-21', 'Global', 'Religious', 'Easter Monday - Christian holiday', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(15, 'Labour Day', '2025-05-01', 'Global', 'Public', 'International Workers\' Day', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(16, 'Christmas Day', '2025-12-25', 'Global', 'Religious', 'Christmas Day celebration', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(17, 'Boxing Day', '2025-12-26', 'Global', 'Public', 'Boxing Day', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(18, 'Madaraka Day', '2025-06-01', 'Kenya', 'Public', 'Madaraka Day - Kenya\'s self-rule day', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(19, 'Eid al-Fitr', '2025-03-30', 'Kenya', 'Religious', 'End of Ramadan', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(20, 'Eid al-Adha', '2025-06-07', 'Kenya', 'Religious', 'Feast of Sacrifice', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(21, 'Mashujaa Day', '2025-10-20', 'Kenya', 'Public', 'Heroes\' Day', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N'),
(22, 'Jamhuri Day', '2025-12-12', 'Kenya', 'Public', 'Independence Day', 'Y', '2025-09-27 20:00:49', NULL, NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_holidays`
--

CREATE TABLE `tija_holidays` (
  `holidayID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `holidayName` varchar(256) NOT NULL,
  `holidayDate` date NOT NULL,
  `holidayType` enum('half_day','full_day') NOT NULL,
  `countryID` int(11) NOT NULL,
  `repeatsAnnually` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `jurisdictionLevel` varchar(20) DEFAULT 'country' COMMENT 'global, country, region, city, entity',
  `regionID` varchar(100) DEFAULT NULL COMMENT 'Region/State identifier',
  `cityID` varchar(100) DEFAULT NULL COMMENT 'City identifier',
  `entitySpecific` text DEFAULT NULL COMMENT 'Comma-separated entity IDs',
  `excludeBusinessUnits` text DEFAULT NULL COMMENT 'Comma-separated business unit IDs to exclude',
  `affectsLeaveBalance` char(1) DEFAULT 'Y' COMMENT 'Whether holiday affects leave calculations',
  `holidayNotes` text DEFAULT NULL COMMENT 'Additional notes or observance details',
  `CreatedByID` int(11) DEFAULT NULL COMMENT 'User ID who created the holiday',
  `applyToEmploymentTypes` varchar(500) DEFAULT 'all' COMMENT 'Comma-separated employment types',
  `CreateDate` datetime DEFAULT NULL COMMENT 'Creation timestamp',
  `generatedFrom` int(11) DEFAULT NULL COMMENT 'Source holiday ID if auto-generated'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_holidays`
--

INSERT INTO `tija_holidays` (`holidayID`, `DateAdded`, `holidayName`, `holidayDate`, `holidayType`, `countryID`, `repeatsAnnually`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `jurisdictionLevel`, `regionID`, `cityID`, `entitySpecific`, `excludeBusinessUnits`, `affectsLeaveBalance`, `holidayNotes`, `CreatedByID`, `applyToEmploymentTypes`, `CreateDate`, `generatedFrom`) VALUES
(1, '2025-03-16 16:44:11', 'New Years Day', '2025-01-01', 'full_day', 25, 'Y', '2025-03-16 16:44:11', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(2, '2025-03-16 16:47:15', 'Good Friday', '2025-04-10', 'full_day', 25, 'N', '2025-03-16 16:47:15', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(3, '2025-03-16 16:56:31', 'Easter Monday', '2025-04-21', 'full_day', 25, 'N', '2025-03-16 16:56:31', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(4, '2025-03-16 16:56:54', 'Labour Day', '2025-05-01', 'full_day', 25, 'Y', '2025-03-16 16:56:54', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(5, '2025-03-16 16:57:12', 'Madaraka Day', '2025-06-01', 'full_day', 25, 'Y', '2025-03-16 16:57:12', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(6, '2025-03-16 16:57:32', 'Mazingira Day', '2025-09-10', 'full_day', 25, 'Y', '2025-03-16 16:57:32', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(7, '2025-03-16 16:57:50', 'Mashujaa Day', '2025-10-20', 'full_day', 25, 'Y', '2025-03-16 16:57:50', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(8, '2025-03-16 16:58:08', 'Jamhuri Day', '2025-12-12', 'full_day', 25, 'Y', '2025-03-16 16:58:08', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(9, '2025-03-16 16:58:25', 'Christmas Day', '2025-12-25', 'full_day', 25, 'Y', '2025-03-16 16:58:25', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL),
(10, '2025-03-16 16:58:59', 'Boxing Day', '2025-12-26', 'full_day', 25, 'Y', '2025-03-16 16:58:59', 0, 'N', 'N', 'country', NULL, NULL, NULL, NULL, 'Y', NULL, NULL, 'all', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_holiday_audit_log`
--

CREATE TABLE `tija_holiday_audit_log` (
  `auditID` int(11) NOT NULL,
  `holidayID` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'created, updated, deleted, generated',
  `performedByID` int(11) NOT NULL,
  `performedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `changeDetails` text DEFAULT NULL COMMENT 'JSON of what changed',
  `ipAddress` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_industries`
--

CREATE TABLE `tija_industries` (
  `industryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `industryName` varchar(255) NOT NULL,
  `industryDescription` text NOT NULL,
  `sectorID` int(11) NOT NULL,
  `LastUpdateByID` int(11) NOT NULL DEFAULT 37,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_industries`
--

INSERT INTO `tija_industries` (`industryID`, `DateAdded`, `industryName`, `industryDescription`, `sectorID`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-09 17:38:54', 'Oil, Gas & Consumable Fuels', 'Companies involved in the exploration, production, refining, marketing, and storage of oil, gas, coal, and consumable fuels.', 1, 37, '2025-05-09 17:38:54', 'N', 'N'),
(2, '2025-05-09 17:38:54', 'Energy Equipment & Services', 'Companies that manufacture and provide equipment and services for the energy industry, such as drilling, well construction, and energy infrastructure.', 1, 37, '2025-05-09 17:38:54', 'N', 'N'),
(3, '2025-05-09 17:38:54', 'Chemicals', 'Companies that manufacture a wide range of chemicals, including basic chemicals, specialty chemicals, fertilizers, and agricultural chemicals.', 2, 37, '2025-05-09 17:38:54', 'N', 'N'),
(4, '2025-05-09 17:38:54', 'Metals & Mining', 'Companies engaged in the exploration, extraction, and processing of metals (e.g., steel, aluminum, gold) and other mined products.', 2, 37, '2025-05-09 17:38:54', 'N', 'N'),
(5, '2025-05-09 17:38:54', 'Construction Materials', 'Companies that produce materials used in construction, such as cement, aggregates, and bricks.', 2, 37, '2025-05-09 17:38:54', 'N', 'N'),
(6, '2025-05-09 17:38:54', 'Containers & Packaging', 'Companies that manufacture various types of containers and packaging materials, including paper, plastic, glass, and metal packaging.', 2, 37, '2025-05-09 17:38:54', 'N', 'N'),
(7, '2025-05-09 17:38:54', 'Paper & Forest Products', 'Companies involved in the manufacturing of paper and forest products, including pulp, paperboard, and lumber.', 2, 37, '2025-05-09 17:38:54', 'N', 'N'),
(8, '2025-05-09 17:38:54', 'Aerospace & Defense', 'Companies that manufacture aerospace and defense products, including aircraft, defense electronics, and weapons systems.', 3, 37, '2025-05-09 17:38:54', 'N', 'N'),
(9, '2025-05-09 17:38:54', 'Building Products', 'Companies that manufacture products used in the construction and renovation of buildings, such as plumbing fixtures, HVAC systems, and cabinetry.', 3, 37, '2025-05-09 17:38:54', 'N', 'N'),
(10, '2025-05-09 17:38:54', 'Machinery', 'Companies that manufacture industrial machinery and equipment, including construction machinery, agricultural machinery, and engines.', 3, 37, '2025-05-09 17:38:54', 'N', 'N'),
(11, '2025-05-09 17:38:54', 'Commercial & Professional Services', 'Companies that provide a variety of business-to-business services such as consulting, employment services, environmental services, and office services.', 3, 37, '2025-05-09 17:38:54', 'N', 'N'),
(12, '2025-05-09 17:38:54', 'Transportation', 'Companies involved in moving people and goods, including airlines, railroads, trucking, shipping, and logistics services.', 3, 37, '2025-05-09 17:38:54', 'N', 'N'),
(13, '2025-05-09 17:38:54', 'Electrical Equipment', 'Companies that manufacture electrical components and equipment, including power generation equipment, automation products, and electrical wiring.', 3, 37, '2025-05-09 17:38:54', 'N', 'N'),
(14, '2025-05-09 17:38:54', 'Automobiles & Components', 'Companies that design, manufacture, and sell automobiles, motorcycles, and their related parts and components.', 4, 37, '2025-05-09 17:38:54', 'N', 'N'),
(15, '2025-05-09 17:38:54', 'Hotels, Restaurants & Leisure', 'Companies that own and operate hotels, resorts, restaurants, casinos, cruise lines, and other leisure facilities.', 4, 37, '2025-05-09 17:38:54', 'N', 'N'),
(16, '2025-05-09 17:38:54', 'Media', 'Companies involved in the production and distribution of media content, including television, radio, film, publishing, and advertising (often overlaps with Communication Services).', 4, 37, '2025-05-09 17:38:54', 'N', 'N'),
(17, '2025-05-09 17:38:54', 'Specialty Retail', 'Retailers that focus on specific product categories, such as apparel, electronics, home improvement, and luxury goods.', 4, 37, '2025-05-09 17:38:54', 'N', 'N'),
(18, '2025-05-09 17:38:54', 'Household Durables', 'Companies that manufacture durable goods for the home, such as appliances, furniture, and home furnishings.', 4, 37, '2025-05-09 17:38:54', 'N', 'N'),
(19, '2025-05-09 17:38:54', 'Textiles, Apparel & Luxury Goods', 'Companies that design, manufacture, and sell textiles, apparel, footwear, and luxury items.', 4, 37, '2025-05-09 17:38:54', 'N', 'N'),
(20, '2025-05-09 17:38:54', 'Food & Staples Retailing', 'Retailers primarily engaged in selling food, beverages, and other essential household items, including supermarkets and convenience stores.', 5, 37, '2025-05-09 17:38:54', 'N', 'N'),
(21, '2025-05-09 17:38:54', 'Food Products', 'Companies that manufacture and process packaged foods, meats, and agricultural products.', 5, 37, '2025-05-09 17:38:54', 'N', 'N'),
(22, '2025-05-09 17:38:54', 'Beverages', 'Companies that produce and distribute alcoholic and non-alcoholic beverages.', 5, 37, '2025-05-09 17:38:54', 'N', 'N'),
(23, '2025-05-09 17:38:54', 'Tobacco', 'Companies that manufacture and sell tobacco products.', 5, 37, '2025-05-09 17:38:54', 'N', 'N'),
(24, '2025-05-09 17:38:54', 'Household & Personal Products', 'Companies that manufacture non-durable household products (e.g., cleaning supplies) and personal care items (e.g., cosmetics, toiletries).', 5, 37, '2025-05-09 17:38:54', 'N', 'N'),
(25, '2025-05-09 17:38:54', 'Pharmaceuticals', 'Companies that discover, develop, manufacture, and market prescription and over-the-counter drugs.', 6, 37, '2025-05-09 17:38:54', 'N', 'N'),
(26, '2025-05-09 17:38:54', 'Biotechnology', 'Companies focused on research, development, and commercialization of products based on biological sciences, including genetic engineering.', 6, 37, '2025-05-09 17:38:54', 'N', 'N'),
(27, '2025-05-09 17:38:54', 'Health Care Equipment & Supplies', 'Companies that manufacture medical devices, instruments, diagnostic equipment, and medical supplies.', 6, 37, '2025-05-09 17:38:54', 'N', 'N'),
(28, '2025-05-09 17:38:54', 'Health Care Providers & Services', 'Companies that own and operate health care facilities (e.g., hospitals, clinics) and provide health care services, including managed care.', 6, 37, '2025-05-09 17:38:54', 'N', 'N'),
(29, '2025-05-09 17:38:54', 'Life Sciences Tools & Services', 'Companies that provide analytical tools, instruments, consumables, and services to the life sciences industry.', 6, 37, '2025-05-09 17:38:54', 'N', 'N'),
(30, '2025-05-09 17:38:54', 'Banks', 'Commercial banks that provide a range of financial services, including deposits, loans, and payment processing.', 7, 37, '2025-05-09 17:38:54', 'N', 'N'),
(31, '2025-05-09 17:38:54', 'Insurance', 'Companies that offer various types of insurance, including life, health, property & casualty, and reinsurance.', 7, 37, '2025-05-09 17:38:54', 'N', 'N'),
(32, '2025-05-09 17:38:54', 'Capital Markets', 'Companies involved in investment banking, brokerage services, asset management, and custody services. Also includes financial exchanges and data providers.', 7, 37, '2025-05-09 17:38:54', 'N', 'N'),
(33, '2025-05-09 17:38:54', 'Diversified Financial Services', 'Financial institutions with diversified operations across multiple financial service areas.', 7, 37, '2025-05-09 17:38:54', 'N', 'N'),
(34, '2025-05-09 17:38:54', 'Mortgage REITs', 'Real Estate Investment Trusts that primarily invest in mortgage-backed securities and other mortgage-related assets.', 7, 37, '2025-05-09 17:38:54', 'N', 'N'),
(35, '2025-05-09 17:38:54', 'Software', 'Companies that develop and market application software, systems software, and internet software & services.', 8, 37, '2025-05-09 17:38:54', 'N', 'N'),
(36, '2025-05-09 17:38:54', 'IT Services', 'Companies that provide information technology consulting, outsourcing, data processing, and other IT services.', 8, 37, '2025-05-09 17:38:54', 'N', 'N'),
(37, '2025-05-09 17:38:54', 'Technology Hardware, Storage & Peripherals', 'Companies that manufacture computers, communication equipment, and related hardware and peripherals.', 8, 37, '2025-05-09 17:38:54', 'N', 'N'),
(38, '2025-05-09 17:38:54', 'Semiconductors & Semiconductor Equipment', 'Companies that design, manufacture, and sell semiconductors and related equipment used in their production.', 8, 37, '2025-05-09 17:38:54', 'N', 'N'),
(39, '2025-05-09 17:38:54', 'Telecommunication Services', 'Companies that provide wired and wireless telecommunication services, including mobile, fixed-line, and internet services.', 9, 37, '2025-05-09 17:38:54', 'N', 'N'),
(40, '2025-05-09 17:38:54', 'Media & Entertainment', 'Companies involved in the creation and distribution of entertainment content, including movies, television programming, music, and interactive entertainment (this can overlap with Consumer Discretionary\'s Media). This GICS sector consolidates many of these.', 9, 37, '2025-05-09 17:38:54', 'N', 'N'),
(41, '2025-05-09 17:38:54', 'Interactive Media & Services', 'Companies that operate online platforms, including social media, search engines, and online marketplaces.', 9, 37, '2025-05-09 17:38:54', 'N', 'N'),
(42, '2025-05-09 17:38:54', 'Electric Utilities', 'Companies engaged in the generation, transmission, and distribution of electricity.', 10, 37, '2025-05-09 17:38:54', 'N', 'N'),
(43, '2025-05-09 17:38:54', 'Gas Utilities', 'Companies involved in the distribution of natural gas to residential, commercial, and industrial customers.', 10, 37, '2025-05-09 17:38:54', 'N', 'N'),
(44, '2025-05-09 17:38:54', 'Water Utilities', 'Companies that provide water and wastewater services.', 10, 37, '2025-05-09 17:38:54', 'N', 'N'),
(45, '2025-05-09 17:38:54', 'Multi-Utilities', 'Utility companies that provide a combination of electricity, gas, and/or water services.', 10, 37, '2025-05-09 17:38:54', 'N', 'N'),
(46, '2025-05-09 17:38:54', 'Independent Power & Renewable Electricity Producers', 'Companies that generate and sell electricity from independent power projects or renewable energy sources (e.g., solar, wind).', 10, 37, '2025-05-09 17:38:54', 'N', 'N'),
(47, '2025-05-09 17:38:54', 'Equity Real Estate Investment Trusts (REITs)', 'Companies that own, operate, and finance income-producing real estate across various property types (e.g., office, retail, residential, industrial). Excludes Mortgage REITs.', 11, 37, '2025-05-09 17:38:54', 'N', 'N'),
(48, '2025-05-09 17:38:54', 'Real Estate Management & Development', 'Companies involved in real estate development, management, brokerage, and other real estate services.', 11, 37, '2025-05-09 17:38:54', 'N', 'N'),
(49, '2025-05-12 13:37:12', 'Professional Services', 'Companies providing specialized business support services, including human resources, employment services, research & consulting, and data processing & outsourced services.', 3, 37, '2025-05-12 13:37:12', 'N', 'N'),
(50, '2025-05-12 13:37:12', 'Research & Consulting Services', 'Companies offering specialized research and consulting services to businesses, including management consulting, strategy advisory, business process improvement, and market research. (e.g., Ernst & Young, Deloitte, McKinsey).', 3, 37, '2025-05-12 13:37:12', 'N', 'N'),
(51, '2025-05-12 13:37:12', 'Management and Business Consulting Services', 'Firms that provide advisory and implementation services to organizations to improve performance, efficiency, and strategy. Covers areas like management, operations, IT, HR, and financial advisory.', 3, 37, '2025-05-12 13:37:12', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_industry_sectors`
--

CREATE TABLE `tija_industry_sectors` (
  `sectorID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `sectorName` varchar(255) NOT NULL,
  `sectorDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL DEFAULT 37,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_industry_sectors`
--

INSERT INTO `tija_industry_sectors` (`sectorID`, `DateAdded`, `sectorName`, `sectorDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-09 17:06:47', 'Energy', 'Companies that are involved in the exploration and production of energy resources, or provide equipment and services to these companies. Focuses primarily on traditional fossil fuels.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(2, '2025-05-09 17:06:47', 'Materials', 'Companies engaged in the discovery, development, and processing of raw materials, including chemicals, construction materials, containers and packaging, metals and mining, and paper and forest products.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(3, '2025-05-09 17:06:47', 'Industrials', 'Companies that manufacture and distribute capital goods or provide commercial and professional services. Includes aerospace & defense, building products, construction & engineering, electrical equipment, machinery, and transportation.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(4, '2025-05-09 17:06:47', 'Consumer Discretionary', 'Businesses that tend to be most sensitive to economic cycles. Includes automotive, household durable goods, apparel, hotels, restaurants & leisure, media, and retail (non-essential goods).', '2025-05-09 17:06:47', 37, 'N', 'N'),
(5, '2025-05-09 17:06:47', 'Consumer Staples', 'Companies that provide goods and services that are considered necessities and are less sensitive to economic cycles. Includes food & staples retailing, food, beverage & tobacco, and household & personal products.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(6, '2025-05-09 17:06:47', 'Health Care', 'Companies involved in providing medical services, manufacturing medical equipment or drugs, or involved in biotechnology and life sciences research.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(7, '2025-05-09 17:06:47', 'Financials', 'Companies engaged in banking, insurance, diversified financial services, real estate finance (mortgage REITs), and capital markets.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(8, '2025-05-09 17:06:47', 'Information Technology', 'Companies that offer software and information technology services, manufacturers and distributors of technology hardware & equipment, and semiconductor & semiconductor equipment manufacturers.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(9, '2025-05-09 17:06:47', 'Communication Services', 'Companies that facilitate communication and offer related content and information through various mediums. Includes telecommunication services, media, and entertainment.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(10, '2025-05-09 17:06:47', 'Utilities', 'Companies considered as electric, gas, or water utilities, or those that operate as independent power and renewable electricity producers.', '2025-05-09 17:06:47', 37, 'N', 'N'),
(11, '2025-05-09 17:06:47', 'Real Estate', 'Companies engaged in real estate development and operation, and firms offering real estate related services. Includes Real Estate Investment Trusts (REITs) that own and operate income-producing real estate.', '2025-05-09 17:06:47', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_investment_mapped_accounts`
--

CREATE TABLE `tija_investment_mapped_accounts` (
  `investmentMappedAccountID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `investmentFinancialStatementID` int(11) NOT NULL,
  `InvestmentAllowanceID` int(11) NOT NULL,
  `investmentAllowanceAccountID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoices`
--

CREATE TABLE `tija_invoices` (
  `invoiceID` int(11) NOT NULL,
  `invoiceNumber` varchar(50) NOT NULL COMMENT 'Unique invoice number/identifier',
  `clientID` int(11) NOT NULL COMMENT 'Reference to client table',
  `salesCaseID` int(11) DEFAULT NULL COMMENT 'Reference to sales case (if applicable)',
  `projectID` int(11) DEFAULT NULL COMMENT 'Reference to project (if applicable)',
  `invoiceDate` date NOT NULL COMMENT 'Date when invoice was issued',
  `dueDate` date NOT NULL COMMENT 'Payment due date',
  `invoiceAmount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Base invoice amount before tax',
  `subtotal` decimal(15,2) DEFAULT 0.00 COMMENT 'Subtotal before tax and discount',
  `discountPercent` decimal(5,2) DEFAULT 0.00 COMMENT 'Overall discount percentage',
  `discountAmount` decimal(15,2) DEFAULT 0.00 COMMENT 'Overall discount amount',
  `taxAmount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tax amount',
  `totalAmount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total amount including tax',
  `notes` text DEFAULT NULL COMMENT 'Invoice notes',
  `terms` text DEFAULT NULL COMMENT 'Payment terms',
  `pdfURL` varchar(500) DEFAULT NULL COMMENT 'Generated PDF URL',
  `sentDate` datetime DEFAULT NULL COMMENT 'When invoice was sent',
  `paidDate` datetime DEFAULT NULL COMMENT 'When invoice was fully paid',
  `paidAmount` decimal(15,2) DEFAULT 0.00 COMMENT 'Total amount paid',
  `outstandingAmount` decimal(15,2) DEFAULT 0.00 COMMENT 'Outstanding amount',
  `currency` varchar(3) NOT NULL DEFAULT 'KES' COMMENT 'Currency code (KES, USD, EUR, etc.)',
  `invoiceStatusID` int(11) NOT NULL DEFAULT 1 COMMENT 'Reference to invoice status table',
  `templateID` int(11) DEFAULT NULL COMMENT 'FK to tija_invoice_templates',
  `orgDataID` int(11) NOT NULL COMMENT 'Reference to organization data',
  `entityID` int(11) NOT NULL COMMENT 'Reference to entity',
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Record creation timestamp',
  `LastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last update timestamp',
  `LastUpdatedByID` int(11) DEFAULT NULL COMMENT 'User who last updated the record',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether invoice has lapsed',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether invoice is suspended'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice management table for TIJA PMS system';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_expenses`
--

CREATE TABLE `tija_invoice_expenses` (
  `mappingID` int(10) UNSIGNED NOT NULL,
  `invoiceItemID` int(11) NOT NULL COMMENT 'FK to tija_invoice_items',
  `expenseID` int(11) DEFAULT NULL COMMENT 'FK to tija_project_expenses',
  `feeExpenseID` int(11) DEFAULT NULL COMMENT 'FK to tija_project_fee_expenses',
  `amount` decimal(15,2) NOT NULL COMMENT 'Amount billed for this expense',
  `markupPercent` decimal(5,2) DEFAULT 0.00 COMMENT 'Markup percentage applied',
  `DateAdded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps expenses to invoice items';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_items`
--

CREATE TABLE `tija_invoice_items` (
  `invoiceItemID` int(10) UNSIGNED NOT NULL,
  `invoiceID` int(11) NOT NULL COMMENT 'FK to tija_invoices',
  `itemType` enum('project','task','work_hours','expense','fee_expense','license','custom') NOT NULL COMMENT 'Type of invoice item',
  `itemReferenceID` int(11) DEFAULT NULL COMMENT 'ID of referenced item (projectID, taskID, expenseID, etc.)',
  `itemCode` varchar(100) DEFAULT NULL COMMENT 'Item code/reference',
  `itemDescription` text NOT NULL COMMENT 'Description of the item',
  `quantity` decimal(10,2) DEFAULT 1.00 COMMENT 'Quantity (hours, units, etc.)',
  `unitPrice` decimal(15,2) NOT NULL COMMENT 'Price per unit',
  `discountPercent` decimal(5,2) DEFAULT 0.00 COMMENT 'Discount percentage',
  `discountAmount` decimal(15,2) DEFAULT 0.00 COMMENT 'Discount amount',
  `taxPercent` decimal(5,2) DEFAULT 0.00 COMMENT 'Tax percentage',
  `taxAmount` decimal(15,2) DEFAULT 0.00 COMMENT 'Tax amount',
  `lineTotal` decimal(15,2) NOT NULL COMMENT 'Total for this line item',
  `sortOrder` int(11) DEFAULT 0 COMMENT 'Display order',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional item metadata (dates, employee info, etc.)' CHECK (json_valid(`metadata`)),
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice line items linking to projects, tasks, hours, expenses';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_licenses`
--

CREATE TABLE `tija_invoice_licenses` (
  `licenseID` int(10) UNSIGNED NOT NULL,
  `licenseName` varchar(255) NOT NULL COMMENT 'License/subscription name',
  `licenseCode` varchar(100) DEFAULT NULL COMMENT 'License code/reference',
  `licenseType` enum('software','subscription','service','maintenance','other') DEFAULT 'software',
  `clientID` int(11) DEFAULT NULL COMMENT 'FK to tija_clients if client-specific',
  `projectID` int(11) DEFAULT NULL COMMENT 'FK to tija_projects if project-specific',
  `monthlyCost` decimal(15,2) DEFAULT NULL COMMENT 'Monthly cost',
  `annualCost` decimal(15,2) DEFAULT NULL COMMENT 'Annual cost',
  `startDate` date DEFAULT NULL COMMENT 'License start date',
  `endDate` date DEFAULT NULL COMMENT 'License end date',
  `renewalDate` date DEFAULT NULL COMMENT 'Next renewal date',
  `autoRenew` enum('Y','N') DEFAULT 'N' COMMENT 'Auto-renew license',
  `billingFrequency` enum('monthly','quarterly','annually','one_time') DEFAULT 'monthly',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `description` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL DEFAULT 1,
  `entityID` int(11) NOT NULL DEFAULT 1,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Licenses and subscriptions that can be billed';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_payments`
--

CREATE TABLE `tija_invoice_payments` (
  `paymentID` int(10) UNSIGNED NOT NULL,
  `invoiceID` int(11) NOT NULL COMMENT 'FK to tija_invoices',
  `paymentNumber` varchar(100) NOT NULL COMMENT 'Payment reference number',
  `paymentDate` date NOT NULL COMMENT 'Date payment was received',
  `paymentAmount` decimal(15,2) NOT NULL COMMENT 'Amount paid',
  `paymentMethod` enum('cash','bank_transfer','cheque','credit_card','mobile_money','other') DEFAULT 'bank_transfer',
  `paymentReference` varchar(255) DEFAULT NULL COMMENT 'Payment reference (transaction ID, cheque number, etc.)',
  `bankAccountID` int(11) DEFAULT NULL COMMENT 'FK to bank account if applicable',
  `currency` varchar(3) DEFAULT 'KES' COMMENT 'Payment currency',
  `exchangeRate` decimal(10,4) DEFAULT 1.0000 COMMENT 'Exchange rate if different currency',
  `notes` text DEFAULT NULL COMMENT 'Payment notes',
  `receivedBy` int(11) DEFAULT NULL COMMENT 'User who recorded the payment',
  `verifiedBy` int(11) DEFAULT NULL COMMENT 'User who verified the payment',
  `verificationDate` datetime DEFAULT NULL COMMENT 'When payment was verified',
  `status` enum('pending','verified','reversed','cancelled') DEFAULT 'pending',
  `orgDataID` int(11) NOT NULL DEFAULT 1,
  `entityID` int(11) NOT NULL DEFAULT 1,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payments received against invoices';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_status`
--

CREATE TABLE `tija_invoice_status` (
  `statusID` int(11) NOT NULL,
  `statusName` varchar(50) NOT NULL COMMENT 'Status name',
  `statusDescription` text DEFAULT NULL COMMENT 'Detailed status description',
  `statusColor` varchar(7) DEFAULT '#007bff' COMMENT 'Hex color code for UI display',
  `isActive` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether status is active',
  `sortOrder` int(11) DEFAULT 0 COMMENT 'Sort order for display',
  `DateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice status lookup table';

--
-- Dumping data for table `tija_invoice_status`
--

INSERT INTO `tija_invoice_status` (`statusID`, `statusName`, `statusDescription`, `statusColor`, `isActive`, `sortOrder`, `DateAdded`, `LastUpdate`) VALUES
(1, 'Draft', 'Invoice is in draft status and not yet sent', '#6c757d', 'Y', 1, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(2, 'Sent', 'Invoice has been sent to client', '#007bff', 'Y', 2, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(3, 'Paid', 'Invoice has been fully paid', '#28a745', 'Y', 3, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(4, 'Partially Paid', 'Invoice has been partially paid', '#ffc107', 'Y', 4, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(5, 'Overdue', 'Invoice is past due date and not paid', '#dc3545', 'Y', 5, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(6, 'Cancelled', 'Invoice has been cancelled', '#6c757d', 'Y', 6, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(7, 'Disputed', 'Invoice is under dispute', '#fd7e14', 'Y', 7, '2025-09-16 14:13:44', '2025-09-16 14:13:44'),
(8, 'Refunded', 'Invoice has been refunded', '#20c997', 'Y', 8, '2025-09-16 14:13:44', '2025-09-16 14:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_templates`
--

CREATE TABLE `tija_invoice_templates` (
  `templateID` int(10) UNSIGNED NOT NULL,
  `templateName` varchar(255) NOT NULL COMMENT 'Template name',
  `templateCode` varchar(100) NOT NULL COMMENT 'Unique template code',
  `templateDescription` text DEFAULT NULL COMMENT 'Template description',
  `templateType` enum('standard','hourly','expense','milestone','recurring','custom') DEFAULT 'standard',
  `headerHTML` text DEFAULT NULL COMMENT 'Invoice header HTML',
  `footerHTML` text DEFAULT NULL COMMENT 'Invoice footer HTML',
  `bodyHTML` text DEFAULT NULL COMMENT 'Invoice body HTML template',
  `cssStyles` text DEFAULT NULL COMMENT 'Custom CSS styles',
  `logoURL` varchar(500) DEFAULT NULL COMMENT 'Company logo URL',
  `companyName` varchar(255) DEFAULT NULL COMMENT 'Company name',
  `companyAddress` text DEFAULT NULL COMMENT 'Company address',
  `companyPhone` varchar(50) DEFAULT NULL COMMENT 'Company phone',
  `companyEmail` varchar(255) DEFAULT NULL COMMENT 'Company email',
  `companyWebsite` varchar(255) DEFAULT NULL COMMENT 'Company website',
  `companyTaxID` varchar(100) DEFAULT NULL COMMENT 'Company tax ID/VAT number',
  `defaultTerms` text DEFAULT NULL COMMENT 'Default payment terms',
  `defaultNotes` text DEFAULT NULL COMMENT 'Default invoice notes',
  `currency` varchar(3) DEFAULT 'KES' COMMENT 'Default currency',
  `taxEnabled` enum('Y','N') DEFAULT 'Y' COMMENT 'Enable tax calculation',
  `defaultTaxPercent` decimal(5,2) DEFAULT 0.00 COMMENT 'Default tax percentage',
  `isDefault` enum('Y','N') DEFAULT 'N' COMMENT 'Is this the default template',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `orgDataID` int(11) NOT NULL DEFAULT 1,
  `entityID` int(11) NOT NULL DEFAULT 1,
  `createdBy` int(11) DEFAULT NULL COMMENT 'User who created the template',
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice templates for different invoice types';

--
-- Dumping data for table `tija_invoice_templates`
--

INSERT INTO `tija_invoice_templates` (`templateID`, `templateName`, `templateCode`, `templateDescription`, `templateType`, `headerHTML`, `footerHTML`, `bodyHTML`, `cssStyles`, `logoURL`, `companyName`, `companyAddress`, `companyPhone`, `companyEmail`, `companyWebsite`, `companyTaxID`, `defaultTerms`, `defaultNotes`, `currency`, `taxEnabled`, `defaultTaxPercent`, `isDefault`, `isActive`, `orgDataID`, `entityID`, `createdBy`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Suspended`) VALUES
(1, 'Standard Invoice Template', 'STANDARD', 'Default standard invoice template', 'standard', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'KES', 'Y', 0.00, 'Y', 'Y', 1, 1, NULL, '2025-11-14 15:40:28', '2025-11-14 12:40:28', NULL, 'N'),
(2, 'SBSL Template', 'SBSL_TEMP', 'SBSL template', 'standard', NULL, NULL, NULL, NULL, NULL, 'Strategic Business Solutions Limited', 'Rainbow Towers\r\nP. O. BOX 20212 00100', '0722540169', 'felixmauncho@gmail.com', 'http://sbsl.co.ke', 'Strategic Business Solutions Limited', 'Payment after 30 days maximum', 'Invoice due in 30 days.', 'KES', 'Y', 16.00, 'N', 'Y', 1, 1, 4, '2025-11-14 17:28:37', '2025-11-14 11:45:27', 4, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_work_hours`
--

CREATE TABLE `tija_invoice_work_hours` (
  `mappingID` int(10) UNSIGNED NOT NULL,
  `invoiceItemID` int(11) NOT NULL COMMENT 'FK to tija_invoice_items',
  `timelogID` int(11) NOT NULL COMMENT 'FK to tija_tasks_time_logs',
  `hoursBilled` decimal(10,2) NOT NULL COMMENT 'Hours billed for this time log',
  `billingRate` decimal(15,2) NOT NULL COMMENT 'Rate used for billing',
  `amount` decimal(15,2) NOT NULL COMMENT 'Amount billed for this time log',
  `DateAdded` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps work hours/time logs to invoice items';

-- --------------------------------------------------------

--
-- Table structure for table `tija_job_bands`
--

CREATE TABLE `tija_job_bands` (
  `jobBandID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `jobBandTitle` varchar(255) NOT NULL,
  `jobBandDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_job_bands`
--

INSERT INTO `tija_job_bands` (`jobBandID`, `DateAdded`, `jobBandTitle`, `jobBandDescription`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-28 20:54:55', 'Executive P4', 'Top level Management - Professional level 4', 1, '2024-06-28 20:54:55', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_job_categories`
--

CREATE TABLE `tija_job_categories` (
  `jobCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `jobCategoryTitle` varchar(255) NOT NULL,
  `jobCategoryDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_job_categories`
--

INSERT INTO `tija_job_categories` (`jobCategoryID`, `DateAdded`, `jobCategoryTitle`, `jobCategoryDescription`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-23 15:13:26', 'Officials & Managers', 'Officioals and managers category', 1, '2024-06-23 15:13:26', 'N', 'N'),
(2, '2024-06-23 15:16:31', 'Operatives', 'Operatives job category', 1, '2024-06-23 15:16:31', 'N', 'N'),
(3, '2024-06-23 15:37:39', 'Craft Workers', 'Craft specialist workers', 1, '2024-06-23 15:37:39', 'N', 'N'),
(4, '2024-06-23 15:38:06', 'Sales Workers', 'Employees specialized in the sales of  products', 1, '2024-06-23 15:38:06', 'N', 'N'),
(5, '2024-06-23 15:39:03', 'Professionals', 'High value professionals with top training and specialisatiuon.', 1, '2024-06-23 15:39:03', 'N', 'N'),
(6, '2024-06-23 15:39:53', 'Service workers', 'Employees supporting the business but not directly involved in the core business', 1, '2024-06-23 15:39:53', 'N', 'N'),
(7, '2024-06-23 15:40:24', 'Gig Nows', 'Temporary employees offering specific skill sets', 1, '2024-06-23 15:40:24', 'N', 'N'),
(8, '2024-06-23 15:41:27', 'Technicians', 'Technical specialty workers with specific technical hands-on experience', 1, '2024-06-23 15:41:27', 'N', 'N'),
(9, '2024-06-23 15:42:19', 'Laborers and Helpers', 'Labourers and Helpers of the business with no specific specialized skill just support staff', 1, '2024-06-23 15:42:19', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_job_titles`
--

CREATE TABLE `tija_job_titles` (
  `jobTitleID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `jobTitle` varchar(120) NOT NULL,
  `jobCategoryID` int(11) DEFAULT NULL,
  `jobDescription` text DEFAULT NULL,
  `jobSpesification` varchar(256) DEFAULT NULL,
  `jobTitleNote` text DEFAULT NULL,
  `jobGradeID` int(11) DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `jobDescriptionDoc` varchar(255) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_job_titles`
--

INSERT INTO `tija_job_titles` (`jobTitleID`, `DateAdded`, `jobTitle`, `jobCategoryID`, `jobDescription`, `jobSpesification`, `jobTitleNote`, `jobGradeID`, `orgDataID`, `jobDescriptionDoc`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(11, '2023-06-14 13:29:38', 'Art Director', 1, '<p>Art directors typically oversee the work of other designers and artists who produce images for television, film, live performances, advertisements, or video games. They determine the overall style in which a message is communicated visually to its audience.</p>', '1686BCA_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:24:47', 1, 'N', 'N'),
(12, '2023-06-14 13:31:09', 'Brand Manager', 1, '<p>Image result for Brand Manager Key elements of the job are researching the marketplace to determine where the product or client fits in (i.e., analyzing competitive positioning, products, brands and spending); developing marketing and advertising strategies and managing those budgets; helping create designs and layouts for print and digital marketing campaigns.</p>', '168CE7A_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:25:44', 1, 'N', 'N'),
(13, '2023-06-14 13:32:18', 'CCO (Chief Customer Officer)', 1, '<p>The Chief Customer Officer (CCO) is responsible for managing all aspects of customer support and service. &nbsp;The CCO develops the organization\'s customer service strategy and manages the overall performance of people and processes to achieve the highest levels of customer satisfaction.</p>', '168DD5C_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:25:54', 1, 'N', 'N'),
(14, '2023-06-14 13:32:54', 'CEO(Chief Executive Officer)', 1, 'Chief Executive Officer (CEO), the highest-ranking person in a company who is ultimately responsible for making managerial decisions.', '16866E3_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:34:41', 1, 'N', 'N'),
(15, '2023-06-14 13:33:27', 'Chief Financial Officer', 1, 'The term chief financial officer (CFO) refers to a senior executive responsible for managing the financial actions of a company. The CFO\'s duties include tracking cash flow and financial planning as well as analyzing the company\'s financial strengths and weaknesses and proposing corrective actions.', '168EB0A_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:34:49', 1, 'N', 'N'),
(16, '2023-06-14 13:34:08', 'Chief Human Resource Officer (CHRO)', 1, 'The Chief Human Resource Officer (CHRO) is responsible for developing and executing human resource strategy in support of the overall business plan and strategic direction of the organization, specifically in the areas of succession planning, talent management, change management, organizational and performance.', '16889BE_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:34:56', 1, 'N', 'N'),
(17, '2023-06-14 13:34:35', 'Chief Marketing Officer (CMO)', 1, 'Chief Marketing Officer (CMO) is a marketing expert who takes responsibility for overall marketing results of a company.  The CMO oversees marketing strategies and efforts in order to strengthen company\'s market position and achieve desired business goals.', NULL, NULL, NULL, 3, '', '2025-02-14 20:35:03', 1, 'N', 'N'),
(18, '2023-06-14 13:35:11', 'Controller', 2, 'The Controller oversees the accounting tasks and financial reporting procedures of the organization.', NULL, NULL, NULL, 3, '', '2025-02-14 20:35:12', 1, 'N', 'N'),
(19, '2023-06-14 13:36:25', 'Chief Operating Officer (COO)', 1, 'The Chief Operating Officer (COO) is a senior executive tasked with overseeing the day-to-day administrative and operational functions of a business.', '168A01E_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:35:21', 1, 'N', 'N'),
(20, '2023-06-14 13:37:24', 'Credit Analyst', 2, 'A credit analyst is responsible for assessing a loan applicant\'s ability to repay the loan and recommending that it be approved or denied. Credit analysts are employed by commercial and investment banks, credit card companies, credit rating agencies, and investment companies.', '16885AE_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:35:32', 1, 'N', 'N'),
(21, '2023-06-14 13:39:38', 'Chief Revenue Officer (CRO)', 1, 'Chief Revenue Officer (CRO) heads up the sales team or department is tasked with identifying sales targets and goals, and bringing together all efforts to achieve them. CRO candidates are experienced leaders responsible for directing their organization\'s sales team to meet and exceed goals.', '1689160_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:35:39', 1, 'N', 'N'),
(22, '2023-06-14 13:40:44', 'Chief Technology Officer (CTO)', 1, 'The Chief Technology Officer (CTO) is the individual within an organization who oversees the current technology and creates relevant policy.', '1689601_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:35:51', 1, 'N', 'N'),
(23, '2023-06-14 13:41:43', 'Customer Success Manager (CSM)', 1, 'Customer Success Managers (CSMs) are a unique hybrid role between customer service and sales. Their main goal is to provide support for customers as they transition from the sales pipeline (prospects) to the support pipeline (active users)', '1687B92_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:36:02', 1, 'N', 'N'),
(24, '2023-06-14 13:45:12', 'Customer Support', 5, 'When customers experience problems with products and services, these support representatives handle complaints. They listen to or read about customer problems and suggest solutions.', '168ACA9_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:36:12', 1, 'N', 'N'),
(25, '2023-06-14 13:46:25', 'customer support manager (CSM', 1, 'A customer support manager (CSM) works to create positive brand experiences for existing customers in order to support company expansion goals and reduce the possibility for churn and contraction.', NULL, NULL, NULL, 3, '', '2025-02-14 20:36:19', 1, 'N', 'N'),
(26, '2023-06-14 13:47:26', 'Digital Marketing Manager', 5, 'A digital marketing manager is responsible for developing, implementing and managing marketing campaigns that promote a company and its products and/or services. He or she plays a major role in enhancing brand awareness within the digital space as well as driving website traffic and acquiring leads/customers.', '168B657_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:36:32', 1, 'N', 'N'),
(27, '2023-06-14 13:49:24', 'Finance Manager', 1, 'Finance managers analyze every day financial activities and provide advice and guidance to upper management on future financial plans. Typical duties include reviewing financial reports, monitoring accounts, and preparing financial forecasts.', '16884E0_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:36:41', 1, 'N', 'N'),
(28, '2023-06-14 13:51:48', 'Front End Developer', 5, 'A front-end web developer is generally expected to: Develop functional and appealing web- and mobile-based applications based on usability', '168B4E2_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:36:58', 1, 'N', 'N'),
(29, '2023-06-14 13:59:29', 'Global Marketing Manager', 1, 'As a global marketing professional or global marketing manager, you are responsible for handling the promotion of your company and its products or services around the world. You may work with other marketing professionals to develop materials, conduct market research and develop strategies to improve company sales.', '168C109_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:37:05', 1, 'N', 'N'),
(30, '2023-06-14 14:00:01', 'HR Admin', 5, 'The HR Admin manages the administration of company policies, evaluating employee relations, data management, and human resources management, along with the HR manager. ... HR administrators help the HR manager in daily tasks, process new hires, manage HR data, and HR data systems such as an HRIS.', '1683B5C_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:37:20', 1, 'N', 'N'),
(31, '2023-06-14 14:00:56', 'HR Executive', 5, 'HR Executive responsibilities include creating referral programs, updating HR policies and overseeing our hiring processes. To be successful in this role, you should have an extensive background in Human Resources departments and thorough knowledge of labor legislation.', '1681360_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:37:30', 1, 'N', 'N'),
(32, '2023-06-14 14:01:33', 'IT Executive', 5, 'The job of an IT executive is to oversee the information technology needs of an organization including supervising subordinates, coordinating software implementation and upgrades, determining IT budget and equipment needs, and ensuring systems security', '168A562_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:01', 1, 'N', 'N'),
(33, '2023-06-14 14:02:19', 'IT Manager', 5, 'These individuals may have job titles such as CTO, CIO, IT director, information systems director, IT project manager, database manager, among others. IT managers help guide the technological direction of their organizations by constructing business plans, overseeing network security, and directing online operations.', '1685721_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:11', 1, 'N', 'N'),
(34, '2023-06-14 14:03:43', 'IT Technical Support', 3, 'IT Technical Support is an IT professional who monitors and maintains the computer systems and networks of an organization. IT Technical Support provide technical assistance and support to employees.', '168A0AC_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:21', 1, 'N', 'N'),
(35, '2023-06-14 14:04:58', 'Marketing Manager', 1, 'Marketing managers help generate sales for a product or service by creating and overseeing marketing plans. ... They often work closely with an organization\'s executives, providing them with an in-depth understanding of marketing trends, new marketing strategies, and on-going campaigns.', '168C299_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:29', 1, 'N', 'N'),
(36, '2023-06-14 14:05:59', 'Production Manager', 5, 'As a production manager, you\'ll oversee the production process, coordinating all production activities and operations. ... plan and draw up a production schedule. decide on and order the resources that are required and ensure stock levels remain adequate. select equipment and take responsibility for its maintenance.', '168B346_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:37', 1, 'N', 'N'),
(37, '2023-06-14 14:06:00', 'Production Manager', 5, 'As a production manager, you\'ll oversee the production process, coordinating all production activities and operations. ... plan and draw up a production schedule. decide on and order the resources that are required and ensure stock levels remain adequate. select equipment and take responsibility for its maintenance.', '168B82B_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:47', 1, 'N', 'N'),
(38, '2023-06-14 14:07:07', 'QA Engineer', 5, 'The main goal of QA engineers is to prevent defects. Quality Control specialists, in their turn, analyze the test results and find mistakes. They are responsible for identifying and eliminating defects in a product (or, in other words, these engineers make sure that developers get the results they expect).', '168BB27_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:38:56', 1, 'N', 'N'),
(39, '2023-06-14 14:08:01', 'Regional HR Manager', 5, 'The Human Resource Manager will lead and direct the routine functions of the Human Resources (HR) department including hiring and interviewing staff, administering pay, benefits, and leave, and enforcing company policies and practices.', '1688E39_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:39:22', 1, 'N', 'N'),
(40, '2023-06-14 14:08:07', 'Regional Sales Manager', 4, 'The Human Resource Manager will lead and direct the routine functions of the Human Resources (HR) department including hiring and interviewing staff, administering pay, benefits, and leave, and enforcing company policies and practices.', '168A510_sample-job-description.pdf', '			', NULL, 3, '', '2025-02-14 20:40:18', 1, 'N', 'N'),
(41, '2023-06-14 14:10:09', 'Sales Executive', 4, 'A sales executive is responsible for helping build up a business by identifying new business prospects and selling product to them. They must maintain relationships with current clients and build and maintain relationships with new clients.', '168850E_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:40:04', 1, 'N', 'N'),
(42, '2023-06-14 14:11:02', 'Senior Manager IT', 2, 'What Do Senior Information Technology (IT) Managers Do? Establish IT policies regarding operational procedures and ensure compliance. Work with outside vendors, upper management, and leadership of other departments to ensure smooth processes.', '1685253_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:40:33', 1, 'N', 'N'),
(43, '2023-06-14 14:11:51', 'Senior Technical Support Engineer', 5, 'A Technical Support Engineer is generally hired by a company to oversee and maintain their computer hardware and software systems. Their skills assist the company in resolving technical issues concerning customer\'s accounts or company software infrastructure.', '168125B_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:39:55', 1, 'N', 'N'),
(44, '2023-06-14 14:12:44', 'SEO Specialist', 5, 'As an SEO specialist you\'ll identify strategies, techniques and tactics to increase the number of visitors to a website and obtain a high-ranking placement in the results page of search engines. By generating more leads for the business you\'ll open up new opportunities for driving growth and profit.', NULL, NULL, NULL, 3, '', '2025-02-14 20:41:05', 1, 'N', 'N'),
(45, '2023-06-14 14:15:33', 'Software Development Manager', 3, 'Software development managers are leaders in the technology industry. They are responsible for developing software as well as leading and managing the team involved in development.Software Development Manager', '16876D6_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:40:47', 1, 'N', 'N'),
(46, '2023-06-14 14:16:57', 'Software Engineer', 5, '			', NULL, '			', NULL, 3, '', '2025-02-14 20:41:29', 1, 'N', 'N'),
(47, '2023-06-14 14:18:56', 'Talent Acquisition Manager', 2, 'A Talent Acquisition Manager is responsible for finding, recruiting, hiring – and retaining – talented candidates. They\'re in charge of planning, developing, and implementing an effective Talent Acquisition strategy for their organization. This includes (co) building a strong Employer Brand.', '1688DBE_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:41:40', 1, 'N', 'N'),
(48, '2023-06-14 14:19:56', 'Technical Support Engineer', 5, 'A Technical Support Engineer, also known as an IT support engineer, helps in resolving technical issues within different components of computer systems, such as software, hardware, and other network-related IT related problems. ... A technical support engineer should take responsibility for all technical issues.', '1687A53_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:41:15', 1, 'N', 'N'),
(49, '2023-06-14 18:27:31', 'Technical Support Manager', 5, 'Job duties include monitoring inventory, designing and implementing better processes or policies for operation, recommending product or service changes, and ensures all projects meet customer needs and budget requirements.', '16822DF_sample-job-description.pdf', NULL, NULL, 3, '', '2025-02-14 20:39:43', 1, 'N', 'N'),
(51, '2025-03-21 11:15:03', 'Segment Lead', 1, 'A Segment Lead is responsible for overseeing the implementation of projects within their specific segment, ensuring alignment with the company\'s strategic goals. They guide and support their team, fostering collaboration and clear communication to navigate challenges and drive progress. By monitoring performance and providing feedback, they help maintain high standards and efficiency. Their leadership ensures that projects are delivered on time, within budget, and meet client expectations, contributing to the overall success of their segment.', NULL, NULL, NULL, 0, 'MfAw0hEB39al3qLqOJyN.docx', '2025-03-21 11:15:03', 11, 'N', 'N'),
(52, '2025-03-21 11:17:09', 'Product Segment Manager', 5, 'A Product Segment Lead oversees the successful implementation of projects by guiding and supporting a team of project consultants. They ensure that each project aligns with the company\'s strategic goals and meets client expectations. By fostering collaboration and clear communication within the team, the Lead helps navigate challenges and drive project progress. They also monitor performance, provide feedback, and implement best practices to enhance efficiency and quality. Ultimately, their leadership ensures that projects are delivered on time, within budget, and to the highest standards, contributing to the overall success of the product segment.', NULL, NULL, NULL, 0, 'DEbFS8WyBRCHt8Yxpd6A.docx', '2025-03-21 11:17:09', 11, 'N', 'N'),
(53, '2025-03-21 11:17:55', 'Project Implementation Consultant', 5, 'A Project Implementation Consultant ensures successful project execution by developing plans, coordinating with clients, and collaborating with internal teams. They monitor progress, adjust as needed, and provide updates to stakeholders. Post-implementation, they review outcomes to improve future projects and ensure client satisfaction. Their expertise in project management and strong communication skills enable them to manage multiple projects effectively.', NULL, NULL, NULL, 0, 'NC3GiDO5HWBiXyGPH6Ow.docx', '2025-03-21 11:17:55', 11, 'N', 'N'),
(54, '2025-04-06 00:32:08', 'Manager', 1, 'A manager in a consulting firm is responsible for overseeing day-to-day operations and ensuring the successful execution of client projects. They manage project teams, coordinate tasks, and ensure that deliverables meet the firm\'s standards of quality. Managers also maintain client relationships, addressing their needs and concerns promptly. Their role includes identifying opportunities for process improvements, mentoring junior staff, and managing project budgets. Managers often represent the firm in client meetings and industry events, contributing to its professional reputation', NULL, NULL, NULL, 0, 'VhdtDPwRG6pAbalaUTk5.docx', '2025-04-06 00:32:08', 1, 'N', 'N'),
(55, '2025-04-06 00:48:03', 'Partner', 1, 'A partner in a consulting firm is a senior leader responsible for driving the firm\'s growth and success. They develop and execute business strategies, manage client relationships, and oversee the delivery of high-quality consulting projects. Partners are instrumental in generating new business opportunities and expanding existing client relationships. They lead and mentor consulting staff, manage project finances, and represent the firm at industry events, contributing to its thought leadership and reputation.', NULL, NULL, NULL, 0, 'voUBObcw9vkOT1Nweu5a.docx', '2025-04-06 00:48:03', 1, 'N', 'N'),
(56, '2025-04-06 00:49:05', 'Director', 1, 'A director in a consulting firm plays a pivotal role in shaping the firm\'s strategic direction and ensuring the successful delivery of client projects. They manage client relationships, oversee multiple projects, and ensure that consulting teams deliver high-quality solutions. Their responsibilities also include identifying new business opportunities, mentoring staff, and managing project budgets. Directors represent the firm at industry events, contributing to its reputation and thought leadership.', NULL, NULL, NULL, 0, 'wY4kPv5uDMzQE021QRbh.docx', '2025-04-06 00:49:05', 1, 'N', 'N'),
(57, '2025-04-06 00:51:00', 'Assistant Manager', 1, 'An assistant manager in a consulting firm supports the manager in overseeing daily operations and ensuring the smooth execution of client projects. They assist in coordinating project tasks, managing teams, and maintaining quality standards. Assistant managers help address client needs and concerns, contributing to strong client relationships. Their role includes supporting process improvements, mentoring junior staff, and assisting with project budgeting. They often participate in client meetings and industry events, representing the firm and contributing to its professional reputation', NULL, NULL, NULL, 0, 'inFuRMTfwYLbofNuaxma.docx', '2025-04-06 00:51:00', 1, 'N', 'N'),
(58, '2025-04-06 00:53:39', 'Senior Associate', 2, 'Art directors typically oversee the work of other designers and artists who produce images for television, film, live performances, advertisements, or video games. They determine the overall style in which a message is communicated visually to its audience.', NULL, NULL, NULL, 0, 'erESyG7Q7hyIPb2GgOYU.pdf', '2025-05-21 16:44:21', 2, 'N', 'N'),
(59, '2025-04-06 00:55:16', 'Associate', 5, 'An associate in a consulting firm is responsible for supporting project teams in delivering high-quality consulting services. They conduct research, analyze data, and assist in developing solutions tailored to client needs. Associates work closely with senior team members to ensure that project deliverables meet the firm\'s standards. They also help maintain client relationships by addressing client inquiries and providing timely updates. Associates contribute to business development efforts by identifying potential opportunities and supporting proposal development. Their role involves continuous learning and professional development to enhance their consulting skills.', NULL, NULL, NULL, 0, 'Gk8aNonU73Agc5D0hlQL.pdf', '2025-04-06 00:55:16', 1, 'N', 'N'),
(61, '2025-04-06 01:07:24', 'Intern', 6, 'An intern in a consulting firm supports project teams by conducting research, analyzing data, and assisting in the development of client solutions. They work closely with associates and senior team members to learn about consulting practices and contribute to project deliverables. Interns help with administrative tasks, prepare reports, and participate in client meetings. Their role involves gaining practical experience and developing skills that are essential for a career in consulting.', NULL, NULL, NULL, 0, '', '2025-04-06 01:07:24', 1, 'N', 'N'),
(64, '2025-04-06 22:09:34', 'Senior Manager', 1, 'A senior manager in a consulting firm plays a key role in overseeing complex projects and ensuring their successful delivery. They manage project teams, coordinate tasks, and maintain high-quality standards. Senior managers build and maintain strong client relationships, addressing their needs and concerns promptly. Their responsibilities include identifying opportunities for business growth, mentoring junior staff, and managing project budgets. Senior managers represent the firm in client meetings and industry events, contributing to its professional reputation and thought leadership.', NULL, NULL, NULL, 0, 'jDaLM3dTNyQrAEpxOUTA.pdf', '2025-04-06 22:09:34', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_job_title_pay_grade`
--

CREATE TABLE `tija_job_title_pay_grade` (
  `mappingID` int(10) UNSIGNED NOT NULL,
  `DateAdded` timestamp NULL DEFAULT current_timestamp(),
  `jobTitleID` int(10) UNSIGNED NOT NULL,
  `payGradeID` int(10) UNSIGNED NOT NULL,
  `effectiveDate` date NOT NULL COMMENT 'When this mapping became effective',
  `endDate` date DEFAULT NULL COMMENT 'When this mapping ended (NULL if current)',
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `notes` text DEFAULT NULL,
  `createdBy` int(10) UNSIGNED DEFAULT NULL,
  `updatedBy` int(10) UNSIGNED DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mapping between job titles and pay grades';

--
-- Dumping data for table `tija_job_title_pay_grade`
--

INSERT INTO `tija_job_title_pay_grade` (`mappingID`, `DateAdded`, `jobTitleID`, `payGradeID`, `effectiveDate`, `endDate`, `isCurrent`, `notes`, `createdBy`, `updatedBy`, `LastUpdate`, `Suspended`) VALUES
(1, '2025-10-16 14:59:42', 11, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(2, '2025-10-16 14:59:42', 12, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(3, '2025-10-16 14:59:42', 13, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(4, '2025-10-16 14:59:42', 14, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(5, '2025-10-16 14:59:42', 15, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(6, '2025-10-16 14:59:42', 16, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(7, '2025-10-16 14:59:42', 17, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(8, '2025-10-16 14:59:42', 18, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(9, '2025-10-16 14:59:42', 19, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(10, '2025-10-16 14:59:42', 20, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(11, '2025-10-16 14:59:42', 21, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(12, '2025-10-16 14:59:42', 22, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(13, '2025-10-16 14:59:42', 23, 5, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(14, '2025-10-16 14:59:42', 24, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(15, '2025-10-16 14:59:42', 25, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(16, '2025-10-16 14:59:42', 26, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(17, '2025-10-16 14:59:42', 27, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(18, '2025-10-16 14:59:42', 28, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(19, '2025-10-16 14:59:42', 29, 5, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(20, '2025-10-16 14:59:42', 30, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(21, '2025-10-16 14:59:42', 31, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(22, '2025-10-16 14:59:42', 32, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(23, '2025-10-16 14:59:42', 33, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(24, '2025-10-16 14:59:42', 34, 1, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(25, '2025-10-16 14:59:42', 35, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(26, '2025-10-16 14:59:42', 36, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(27, '2025-10-16 14:59:42', 37, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(28, '2025-10-16 14:59:42', 38, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(29, '2025-10-16 14:59:42', 39, 5, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(30, '2025-10-16 14:59:42', 40, 5, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(31, '2025-10-16 14:59:42', 41, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(32, '2025-10-16 14:59:42', 42, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(33, '2025-10-16 14:59:42', 43, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(34, '2025-10-16 14:59:42', 44, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(35, '2025-10-16 14:59:42', 45, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(36, '2025-10-16 14:59:42', 46, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(37, '2025-10-16 14:59:42', 47, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(38, '2025-10-16 14:59:42', 48, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(39, '2025-10-16 14:59:42', 49, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(40, '2025-10-16 14:59:42', 51, 5, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(41, '2025-10-16 14:59:42', 52, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(42, '2025-10-16 14:59:42', 53, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(43, '2025-10-16 14:59:42', 54, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(44, '2025-10-16 14:59:42', 55, 6, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(45, '2025-10-16 14:59:42', 56, 5, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(46, '2025-10-16 14:59:42', 57, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(47, '2025-10-16 14:59:42', 58, 3, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(48, '2025-10-16 14:59:42', 59, 2, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(49, '2025-10-16 14:59:42', 61, 1, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N'),
(50, '2025-10-16 14:59:42', 64, 4, '2025-10-16', NULL, 'Y', NULL, 4, 4, '2025-10-16 14:59:42', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_lead_sources`
--

CREATE TABLE `tija_lead_sources` (
  `leadSourceID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leadSourceName` varchar(200) NOT NULL,
  `leadSourceDescription` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_lead_sources`
--

INSERT INTO `tija_lead_sources` (`leadSourceID`, `DateAdded`, `leadSourceName`, `leadSourceDescription`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-21 18:10:03', 'Customer Recomendation', NULL, 1, 2, '2025-02-21 18:10:03', 2, 'N', 'N'),
(2, '2025-02-22 10:03:15', 'Request for Proposal', NULL, 1, 2, '2025-02-22 10:03:15', 2, 'N', 'N'),
(3, '2025-03-05 14:23:48', 'Tender', NULL, 1, 3, '2025-03-05 14:23:48', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_history`
--

CREATE TABLE `tija_leave_accumulation_history` (
  `historyID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL COMMENT 'Employee who received the accrual',
  `policyID` int(11) NOT NULL COMMENT 'Policy that generated this accrual',
  `ruleID` int(11) DEFAULT NULL COMMENT 'Rule that applied (if any)',
  `leaveTypeID` int(11) NOT NULL COMMENT 'Leave type accrued',
  `accrualPeriod` varchar(20) NOT NULL COMMENT 'Period this accrual covers (e.g., 2024-01, 2024-Q1)',
  `accrualDate` date NOT NULL COMMENT 'Date when accrual was calculated',
  `baseAccrualRate` decimal(5,2) NOT NULL COMMENT 'Base rate from policy',
  `appliedMultiplier` decimal(3,2) DEFAULT 1.00 COMMENT 'Multiplier applied from rules',
  `finalAccrualAmount` decimal(5,2) NOT NULL COMMENT 'Final amount accrued',
  `carryoverAmount` decimal(5,2) DEFAULT 0.00 COMMENT 'Amount carried over from previous period',
  `totalBalance` decimal(5,2) NOT NULL COMMENT 'Total balance after this accrual',
  `calculationNotes` text DEFAULT NULL COMMENT 'Notes about how this was calculated',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='History of leave accruals for employees';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_policies`
--

CREATE TABLE `tija_leave_accumulation_policies` (
  `policyID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL COMMENT 'Entity this policy applies to (NULL for global policies)',
  `parentEntityID` int(11) DEFAULT NULL COMMENT 'Parent entity ID for global policies (entityParentID = 0)',
  `policyScope` enum('Global','Entity','Cadre') DEFAULT 'Entity' COMMENT 'Policy scope: Global (parent entity), Entity (specific entity), Cadre (job category/band)',
  `policyName` varchar(255) NOT NULL COMMENT 'Name of the accumulation policy',
  `leaveTypeID` int(11) NOT NULL COMMENT 'Leave type this policy applies to',
  `jobCategoryID` int(11) DEFAULT NULL COMMENT 'Job category ID for cadre-level policies',
  `jobBandID` int(11) DEFAULT NULL COMMENT 'Job band ID for cadre-level policies',
  `accrualType` enum('Monthly','Quarterly','Annual','Continuous') NOT NULL DEFAULT 'Monthly',
  `accrualRate` decimal(5,2) NOT NULL COMMENT 'Days accrued per period',
  `maxCarryover` int(11) DEFAULT NULL COMMENT 'Maximum days that can be carried over (null = unlimited)',
  `carryoverExpiryMonths` int(11) DEFAULT NULL COMMENT 'Months after which carryover expires (null = never)',
  `accrualStartDate` date DEFAULT NULL COMMENT 'Date when accrual starts (null = immediate)',
  `accrualEndDate` date DEFAULT NULL COMMENT 'Date when accrual ends (null = indefinite)',
  `proRated` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether accrual is pro-rated for partial periods',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `priority` int(11) DEFAULT 1 COMMENT 'Priority order when multiple policies apply',
  `isActive` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether this policy is currently active',
  `policyDescription` text DEFAULT NULL COMMENT 'Detailed description of the policy'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Policies for leave accumulation and accrual';

--
-- Dumping data for table `tija_leave_accumulation_policies`
--

INSERT INTO `tija_leave_accumulation_policies` (`policyID`, `entityID`, `parentEntityID`, `policyScope`, `policyName`, `leaveTypeID`, `jobCategoryID`, `jobBandID`, `accrualType`, `accrualRate`, `maxCarryover`, `carryoverExpiryMonths`, `accrualStartDate`, `accrualEndDate`, `proRated`, `DateAdded`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `priority`, `isActive`, `policyDescription`) VALUES
(1, 1, NULL, 'Entity', 'Annual Leave Monthly Accrual', 1, NULL, NULL, 'Monthly', 1.75, 10, 12, NULL, NULL, 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(2, 1, NULL, 'Entity', 'Sick Leave Monthly Accrual', 2, NULL, NULL, 'Monthly', 1.50, 5, 6, NULL, NULL, 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(3, 1, NULL, 'Entity', 'Maternity Leave Annual', 3, NULL, NULL, 'Annual', 90.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 19:57:42', '2025-11-22 10:16:20', 4, 'Y', 'N', 1, 'Y', NULL),
(4, 1, NULL, 'Entity', 'Paternity Leave Annual', 4, NULL, NULL, 'Annual', 14.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(5, 1, NULL, 'Entity', 'Annual Leave Monthly Accrual', 1, NULL, NULL, 'Monthly', 2.00, 10, 12, NULL, NULL, 'Y', '2025-09-27 20:01:05', '2025-11-22 08:29:12', 4, 'Y', 'N', 1, 'N', NULL),
(6, 1, NULL, 'Entity', 'Sick Leave Monthly Accrual', 2, NULL, NULL, 'Monthly', 1.50, 5, 6, NULL, NULL, 'Y', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(7, 1, NULL, 'Entity', 'Maternity Leave Annual', 3, NULL, NULL, 'Annual', 90.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(8, 1, NULL, 'Entity', 'Paternity Leave Annual', 4, NULL, NULL, 'Annual', 14.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(9, 1, NULL, 'Entity', 'Annual Leave Annual Accrual', 1, NULL, NULL, 'Annual', 21.00, 10, 3, NULL, NULL, 'N', '2025-11-22 08:39:50', '2025-11-22 08:39:50', 4, 'N', 'N', 1, 'Y', 'Front load accrual of annual leave'),
(10, 1, NULL, 'Entity', 'Study Leave Annual ', 6, NULL, NULL, 'Annual', 15.00, 5, 3, NULL, NULL, 'N', '2025-11-22 10:17:16', '2025-11-22 10:17:16', 4, 'N', 'N', 1, 'Y', 'Front Load study leave accrual policy');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_rules`
--

CREATE TABLE `tija_leave_accumulation_rules` (
  `ruleID` int(11) NOT NULL,
  `policyID` int(11) NOT NULL COMMENT 'Parent policy this rule belongs to',
  `ruleName` varchar(255) NOT NULL COMMENT 'Name of the rule',
  `ruleType` enum('Tenure','Performance','Department','Role','Custom') NOT NULL DEFAULT 'Tenure',
  `conditionField` varchar(100) DEFAULT NULL COMMENT 'Field to evaluate (e.g., yearsOfService, performanceRating)',
  `conditionOperator` enum('=','>','>=','<','<=','<>','IN','NOT IN') DEFAULT '>=',
  `conditionValue` text DEFAULT NULL COMMENT 'Value to compare against',
  `accrualMultiplier` decimal(3,2) DEFAULT 1.00 COMMENT 'Multiplier for base accrual rate',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rules for complex accumulation policies';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_applications`
--

CREATE TABLE `tija_leave_applications` (
  `leaveApplicationID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leaveTypeID` int(11) NOT NULL,
  `leavePeriodID` int(11) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `leaveStatusID` int(11) NOT NULL DEFAULT 1,
  `employeeID` int(11) NOT NULL,
  `leaveFiles` text DEFAULT NULL,
  `leaveComments` text DEFAULT NULL,
  `leaveEntitlementID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `noOfDays` decimal(3,2) DEFAULT NULL,
  `emergencyContact` text DEFAULT NULL COMMENT 'Emergency contact information for the leave period',
  `handoverNotes` text DEFAULT NULL COMMENT 'Notes about work handover during leave',
  `createdBy` int(11) DEFAULT NULL COMMENT 'User ID who created the application',
  `createdDate` datetime DEFAULT NULL COMMENT 'Date and time when the application was created',
  `modifiedBy` int(11) DEFAULT NULL COMMENT 'User ID who last modified the application',
  `modifiedDate` datetime DEFAULT NULL COMMENT 'Date and time when the application was last modified',
  `halfDayLeave` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether this is a half day leave',
  `halfDayPeriod` varchar(20) DEFAULT NULL COMMENT 'Period for half day leave (AM/PM)',
  `dateApplied` datetime DEFAULT NULL COMMENT 'Date when the application was submitted',
  `appliedByID` int(11) DEFAULT NULL COMMENT 'ID of the person who applied for leave'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_leave_applications`
--

INSERT INTO `tija_leave_applications` (`leaveApplicationID`, `DateAdded`, `leaveTypeID`, `leavePeriodID`, `startDate`, `endDate`, `leaveStatusID`, `employeeID`, `leaveFiles`, `leaveComments`, `leaveEntitlementID`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `noOfDays`, `emergencyContact`, `handoverNotes`, `createdBy`, `createdDate`, `modifiedBy`, `modifiedDate`, `halfDayLeave`, `halfDayPeriod`, `dateApplied`, `appliedByID`) VALUES
(1, '2025-11-30 14:23:40', 1, 1, '2025-12-01', '2025-12-03', 3, 4, NULL, 'Personal leave', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 3.00, 'Brian Nyongesa', '', NULL, NULL, NULL, NULL, 'N', '', '2025-11-30 14:23:40', 4),
(2, '2025-12-01 08:19:15', 1, 1, '2025-12-02', '2025-12-05', 3, 24, NULL, 'sdf sfdg adfsg afdsgafsdga', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 4.00, 'Felix Mauncho', 'asd asdf asdfasdfasdf asd', NULL, NULL, NULL, NULL, 'N', '', '2025-12-01 08:19:15', 24),
(3, '2025-12-01 10:20:48', 5, 1, '2025-12-09', '2025-12-12', 3, 24, NULL, 'Sick leave demo', 5, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 3.00, 'sick leave demo', 'dsfgfdgsfdgsdfgsdfgsdfg  gdf gsdf gsdf gsdf', NULL, NULL, NULL, NULL, 'N', '', '2025-12-01 10:20:48', 24);

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approvals`
--

CREATE TABLE `tija_leave_approvals` (
  `leaveApprovalID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leaveApplicationID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `leaveTypeID` int(11) NOT NULL,
  `leavePeriodID` int(11) NOT NULL,
  `leaveApproverID` int(11) NOT NULL,
  `leaveDate` date NOT NULL,
  `leaveStatus` enum('approved','rejected') NOT NULL,
  `leaveStatusID` int(11) NOT NULL,
  `approversComments` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_actions`
--

CREATE TABLE `tija_leave_approval_actions` (
  `actionID` int(11) NOT NULL,
  `instanceID` int(11) NOT NULL,
  `stepID` int(11) NOT NULL,
  `stepOrder` int(11) NOT NULL,
  `approverID` int(11) NOT NULL COMMENT 'User who took action',
  `approverUserID` int(11) DEFAULT NULL,
  `action` enum('pending','approved','rejected','delegated','escalated','cancelled','info_requested') NOT NULL,
  `comments` text DEFAULT NULL,
  `delegatedTo` int(11) DEFAULT NULL,
  `actionDate` datetime NOT NULL,
  `responseTime` int(11) DEFAULT NULL COMMENT 'Minutes from notification to action',
  `ipAddress` varchar(45) DEFAULT NULL,
  `userAgent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of all approval actions taken';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_comments`
--

CREATE TABLE `tija_leave_approval_comments` (
  `commentID` int(11) NOT NULL,
  `leaveApplicationID` int(11) NOT NULL,
  `approverID` int(11) DEFAULT NULL,
  `approverUserID` int(11) DEFAULT NULL,
  `approvalLevel` varchar(50) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `commentType` varchar(30) DEFAULT NULL,
  `commentDate` datetime DEFAULT current_timestamp(),
  `DateAdded` datetime DEFAULT current_timestamp(),
  `Lapsed` char(1) NOT NULL DEFAULT 'N',
  `Suspended` char(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_instances`
--

CREATE TABLE `tija_leave_approval_instances` (
  `instanceID` int(11) NOT NULL,
  `leaveApplicationID` int(11) NOT NULL,
  `policyID` int(11) NOT NULL,
  `currentStepID` int(11) DEFAULT NULL,
  `currentStepOrder` int(11) DEFAULT 1,
  `workflowStatus` enum('pending','in_progress','approved','rejected','cancelled','escalated') DEFAULT 'pending',
  `startedAt` datetime NOT NULL,
  `completedAt` datetime DEFAULT NULL,
  `lastActionAt` datetime DEFAULT NULL,
  `lastActionBy` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workflow instances for leave applications';

--
-- Dumping data for table `tija_leave_approval_instances`
--

INSERT INTO `tija_leave_approval_instances` (`instanceID`, `leaveApplicationID`, `policyID`, `currentStepID`, `currentStepOrder`, `workflowStatus`, `startedAt`, `completedAt`, `lastActionAt`, `lastActionBy`, `createdAt`) VALUES
(1, 1, 1, 4, 1, 'pending', '2025-11-30 14:23:40', NULL, NULL, NULL, '2025-11-30 09:23:40'),
(2, 2, 1, 4, 1, 'pending', '2025-12-01 08:19:15', NULL, NULL, NULL, '2025-12-01 03:19:15'),
(3, 3, 1, 4, 1, 'pending', '2025-12-01 10:20:48', NULL, NULL, NULL, '2025-12-01 05:20:48');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_policies`
--

CREATE TABLE `tija_leave_approval_policies` (
  `policyID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `policyName` varchar(255) NOT NULL,
  `policyDescription` text DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `approvalType` varchar(20) NOT NULL DEFAULT 'parallel',
  `isDefault` enum('Y','N') DEFAULT 'N',
  `requireAllApprovals` enum('Y','N') DEFAULT 'N' COMMENT 'If Y, all approvers must approve. If N, sequential approval',
  `allowDelegation` enum('Y','N') DEFAULT 'Y',
  `autoApproveThreshold` int(11) DEFAULT NULL COMMENT 'Auto-approve if leave days <= this value',
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N',
  `Lapsed` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Leave approval workflow policies per entity';

--
-- Dumping data for table `tija_leave_approval_policies`
--

INSERT INTO `tija_leave_approval_policies` (`policyID`, `entityID`, `orgDataID`, `policyName`, `policyDescription`, `isActive`, `approvalType`, `isDefault`, `requireAllApprovals`, `allowDelegation`, `autoApproveThreshold`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Suspended`, `Lapsed`) VALUES
(1, 1, 1, 'Direct Line Manager approval', 'This template is used for employees who need approval from their direct supervisor, project manager and finally the HR Manager', 'Y', 'parallel', 'Y', 'N', 'Y', 4, 4, '2025-10-22 08:28:42', 4, '2025-11-19 17:00:11', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_steps`
--

CREATE TABLE `tija_leave_approval_steps` (
  `stepID` int(11) NOT NULL,
  `policyID` int(11) NOT NULL,
  `stepOrder` int(11) NOT NULL COMMENT 'Order of approval (1, 2, 3...)',
  `stepName` varchar(255) NOT NULL,
  `stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user') NOT NULL,
  `stepDescription` text DEFAULT NULL,
  `isRequired` enum('Y','N') DEFAULT 'Y',
  `approvalRequired` varchar(10) NOT NULL DEFAULT 'all',
  `isConditional` enum('Y','N') DEFAULT 'N',
  `conditionType` enum('days_threshold','leave_type','user_role','department','custom') DEFAULT NULL,
  `conditionValue` text DEFAULT NULL COMMENT 'JSON string for condition parameters',
  `escalationDays` int(11) DEFAULT NULL COMMENT 'Days before escalation if no action',
  `escalateToStepID` int(11) DEFAULT NULL COMMENT 'Which step to escalate to',
  `notifyOnPending` enum('Y','N') DEFAULT 'Y',
  `notifyOnApprove` enum('Y','N') DEFAULT 'Y',
  `notifyOnReject` enum('Y','N') DEFAULT 'Y',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual steps in approval workflow';

--
-- Dumping data for table `tija_leave_approval_steps`
--

INSERT INTO `tija_leave_approval_steps` (`stepID`, `policyID`, `stepOrder`, `stepName`, `stepType`, `stepDescription`, `isRequired`, `approvalRequired`, `isConditional`, `conditionType`, `conditionValue`, `escalationDays`, `escalateToStepID`, `notifyOnPending`, `notifyOnApprove`, `notifyOnReject`, `createdAt`, `updatedAt`, `Suspended`) VALUES
(4, 1, 1, 'Direct Supervisor', 'supervisor', 'Direct Supervisor Approval', 'Y', 'all', 'N', NULL, NULL, 3, NULL, 'Y', 'Y', 'Y', '2025-11-19 17:00:11', NULL, 'N'),
(5, 1, 2, 'HR Manager', 'hr_manager', 'HR Manager Approval', 'Y', 'all', 'N', NULL, NULL, 3, NULL, 'Y', 'Y', 'Y', '2025-11-19 17:00:11', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_step_approvers`
--

CREATE TABLE `tija_leave_approval_step_approvers` (
  `approverID` int(11) NOT NULL,
  `stepID` int(11) NOT NULL,
  `approverType` enum('user','role','department') NOT NULL,
  `approverUserID` int(11) DEFAULT NULL COMMENT 'If approverType = user',
  `approverRole` varchar(100) DEFAULT NULL COMMENT 'If approverType = role',
  `approverDepartment` int(11) DEFAULT NULL COMMENT 'If approverType = department',
  `isBackup` enum('Y','N') DEFAULT 'N',
  `notificationOrder` int(11) DEFAULT 1 COMMENT 'Order for parallel approvers',
  `createdAt` datetime NOT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Specific approvers for custom workflow steps';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_audit_log`
--

CREATE TABLE `tija_leave_audit_log` (
  `auditID` int(11) NOT NULL,
  `entityType` enum('application','approval','clearance','entitlement','policy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entityID` int(11) NOT NULL COMMENT 'ID of the entity being audited',
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Action performed (CREATE, UPDATE, DELETE, APPROVE, etc.)',
  `oldValues` longtext DEFAULT NULL COMMENT 'Previous values (JSON format)',
  `newValues` longtext DEFAULT NULL COMMENT 'New values (JSON format)',
  `performedByID` int(11) NOT NULL COMMENT 'User who performed the action',
  `performedDate` datetime NOT NULL DEFAULT current_timestamp(),
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP address of user',
  `userAgent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User agent string',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Reason for the action'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_blackout_periods`
--

CREATE TABLE `tija_leave_blackout_periods` (
  `blackoutID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL COMMENT 'Entity this blackout applies to',
  `blackoutName` varchar(255) NOT NULL COMMENT 'Name of the blackout period',
  `startDate` date NOT NULL COMMENT 'Start date of blackout period',
  `endDate` date NOT NULL COMMENT 'End date of blackout period',
  `reason` text DEFAULT NULL COMMENT 'Reason for blackout period',
  `applicableLeaveTypes` text DEFAULT NULL COMMENT 'JSON array of leave type IDs this applies to (null = all types)',
  `severity` enum('Warning','Restriction','Prohibition') NOT NULL DEFAULT 'Restriction',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blackout periods when leave applications are restricted';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_entitlement`
--

CREATE TABLE `tija_leave_entitlement` (
  `leaveEntitlementID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leaveTypeID` int(11) NOT NULL,
  `jobCategoryID` int(11) DEFAULT NULL COMMENT 'Job category ID for cadre-level entitlements',
  `jobBandID` int(11) DEFAULT NULL COMMENT 'Job band ID for cadre-level entitlements',
  `entitlement` decimal(4,0) NOT NULL,
  `maxDaysPerApplication` int(11) DEFAULT NULL COMMENT 'Maximum days that can be applied for in a single application (NULL = unlimited)',
  `minNoticeDays` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL COMMENT 'Entity this entitlement applies to (NULL for global entitlements)',
  `parentEntityID` int(11) DEFAULT NULL COMMENT 'Parent entity ID for global entitlements (entityParentID = 0)',
  `policyScope` enum('Global','Entity','Cadre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Entity' COMMENT 'Policy scope: Global (parent entity), Entity (specific entity), Cadre (job category/band)',
  `orgDataID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_leave_entitlement`
--

INSERT INTO `tija_leave_entitlement` (`leaveEntitlementID`, `DateAdded`, `leaveTypeID`, `jobCategoryID`, `jobBandID`, `entitlement`, `maxDaysPerApplication`, `minNoticeDays`, `entityID`, `parentEntityID`, `policyScope`, `orgDataID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-17 13:41:39', 1, NULL, NULL, 21, 10, 5, 1, NULL, 'Entity', 0, '2025-11-09 16:46:18', 4, 'N', 'N'),
(2, '2025-03-17 13:53:03', 2, NULL, NULL, 14, 14, 0, 1, NULL, 'Entity', 0, '2025-11-09 16:46:05', 4, 'N', 'N'),
(3, '2025-03-17 13:53:29', 3, NULL, NULL, 63, 63, 0, 1, NULL, 'Entity', 0, '2025-11-09 16:45:58', 4, 'N', 'N'),
(4, '2025-03-17 13:53:40', 4, NULL, NULL, 14, 14, 0, 1, NULL, 'Entity', 0, '2025-11-09 16:45:43', 4, 'N', 'N'),
(5, '2025-03-17 13:53:50', 5, NULL, NULL, 10, 5, 2, 1, NULL, 'Entity', 0, '2025-11-09 16:44:19', 4, 'N', 'N'),
(6, '2025-11-19 15:42:03', 6, NULL, NULL, 15, 10, 7, 1, NULL, 'Entity', 0, '2025-11-19 15:42:03', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_periods`
--

CREATE TABLE `tija_leave_periods` (
  `leavePeriodID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leavePeriodName` varchar(255) NOT NULL,
  `leavePeriodStartDate` date NOT NULL,
  `leavePeriodEndDate` date NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_leave_periods`
--

INSERT INTO `tija_leave_periods` (`leavePeriodID`, `DateAdded`, `leavePeriodName`, `leavePeriodStartDate`, `leavePeriodEndDate`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 15:15:55', '2025 leave period', '2025-01-01', '2025-12-31', 0, 1, '2025-11-21 15:15:55', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_project_clearances`
--

CREATE TABLE `tija_leave_project_clearances` (
  `clearanceID` int(11) NOT NULL,
  `leaveApplicationID` int(11) NOT NULL COMMENT 'Reference to leave application',
  `projectID` int(11) NOT NULL COMMENT 'Project requiring clearance',
  `projectManagerID` int(11) NOT NULL COMMENT 'Project manager who needs to approve',
  `clearanceStatus` enum('Pending','Approved','Rejected','Not Required') NOT NULL DEFAULT 'Pending',
  `clearanceDate` datetime DEFAULT NULL COMMENT 'Date when clearance was given',
  `remarks` text DEFAULT NULL COMMENT 'Comments from project manager',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project manager clearances for leave applications';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_status`
--

CREATE TABLE `tija_leave_status` (
  `leaveStatusID` int(11) NOT NULL,
  `leaveStatusCode` varchar(80) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leaveStatusName` varchar(255) NOT NULL,
  `leaveStatusDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_leave_status`
--

INSERT INTO `tija_leave_status` (`leaveStatusID`, `leaveStatusCode`, `DateAdded`, `leaveStatusName`, `leaveStatusDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, 'scheduled', '2025-03-16 19:14:46', 'Scheduled', 'Scheduled Leave', '2025-03-16 19:14:46', 0, 'N', 'N'),
(2, 'taken', '2025-03-16 19:27:21', 'Taken', 'Leave already taken by employee', '2025-03-16 19:27:21', 0, 'N', 'N'),
(3, 'pending', '2025-03-16 19:40:10', 'Pending Approval', 'Leave requests pending approval', '2025-03-16 19:40:10', 0, 'N', 'N'),
(4, 'rejected', '2025-03-16 19:43:21', 'Rejected', 'leave requests rejected by supervisor', '2025-03-16 19:43:21', 0, 'N', 'N'),
(5, 'cancelled', '2025-03-16 19:44:23', 'Cancelled', 'Leave applications cancelled by employee', '2025-03-16 19:44:23', 0, 'N', 'N'),
(6, 'approved', '2025-05-28 17:40:00', 'approved', 'Approved Leave', '2025-05-28 17:40:00', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_types`
--

CREATE TABLE `tija_leave_types` (
  `leaveTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `leaveTypeCode` varchar(255) NOT NULL,
  `leaveTypeName` varchar(255) NOT NULL,
  `leaveTypeDescription` text NOT NULL,
  `leaveSegment` enum('male','female','specialNeeds') DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_leave_types`
--

INSERT INTO `tija_leave_types` (`leaveTypeID`, `DateAdded`, `leaveTypeCode`, `leaveTypeName`, `leaveTypeDescription`, `leaveSegment`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-15 23:02:11', 'ANN_R2HR5', 'Annual Leave', 'Annual Leave/ normal Leave', NULL, '2025-11-21 15:33:21', 4, 'N', 'Y'),
(2, '2025-03-17 13:05:48', 'COM_78QN5', 'Compassionate Leave', 'Compassionate Leave', NULL, '2025-03-17 13:05:48', 11, 'N', 'N'),
(3, '2025-03-17 13:08:01', 'MAT_6QPM5', 'Maternity Leave', 'Maternity Leave', 'female', '2025-03-17 13:08:01', 11, 'N', 'N'),
(4, '2025-03-17 13:08:19', 'PAT_0LD6W', 'Paternity Leave', 'Paternity Leave', 'male', '2025-03-17 13:08:19', 11, 'N', 'N'),
(5, '2025-03-17 13:08:35', 'SIC_D9NRA', 'Sick Leave', 'Sick Leave', NULL, '2025-03-17 13:08:35', 11, 'N', 'N'),
(6, '2025-11-19 15:21:58', 'STUDY_LV', 'Study Leave', 'Study Leave', NULL, '2025-11-19 15:21:58', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_workflow_templates`
--

CREATE TABLE `tija_leave_workflow_templates` (
  `templateID` int(11) NOT NULL,
  `templateName` varchar(255) NOT NULL,
  `templateDescription` text DEFAULT NULL,
  `sourcePolicyID` int(11) DEFAULT NULL COMMENT 'Original policy this was created from',
  `isSystemTemplate` enum('Y','N') DEFAULT 'N',
  `isPublic` enum('Y','N') DEFAULT 'N' COMMENT 'If Y, visible to all entities',
  `createdBy` int(11) NOT NULL,
  `createdForEntityID` int(11) DEFAULT NULL,
  `usageCount` int(11) DEFAULT 0,
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reusable workflow templates';

--
-- Dumping data for table `tija_leave_workflow_templates`
--

INSERT INTO `tija_leave_workflow_templates` (`templateID`, `templateName`, `templateDescription`, `sourcePolicyID`, `isSystemTemplate`, `isPublic`, `createdBy`, `createdForEntityID`, `usageCount`, `createdAt`, `updatedAt`, `Suspended`) VALUES
(1, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(2, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(3, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(4, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(5, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:48:14', NULL, 'N'),
(6, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:48:14', NULL, 'N'),
(7, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:48:14', NULL, 'N'),
(8, 'Direct Line approval', 'Direct reporting line approval workflow', NULL, 'Y', 'Y', 1, NULL, 1, '2025-10-21 15:48:14', '2025-11-19 16:41:12', 'N'),
(9, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(10, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(11, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(12, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(13, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(14, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(15, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(16, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(17, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(18, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(19, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(20, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(21, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(22, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(23, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(24, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(25, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N'),
(26, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N'),
(27, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N'),
(28, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_workflow_template_steps`
--

CREATE TABLE `tija_leave_workflow_template_steps` (
  `templateStepID` int(11) NOT NULL,
  `templateID` int(11) NOT NULL,
  `stepOrder` int(11) NOT NULL,
  `stepName` varchar(255) NOT NULL,
  `stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user') NOT NULL,
  `stepDescription` text DEFAULT NULL,
  `isRequired` enum('Y','N') DEFAULT 'Y',
  `isConditional` enum('Y','N') DEFAULT 'N',
  `conditionType` enum('days_threshold','leave_type','user_role','department','custom') DEFAULT NULL,
  `conditionValue` text DEFAULT NULL,
  `escalationDays` int(11) DEFAULT NULL,
  `notifySettings` text DEFAULT NULL COMMENT 'JSON for notification settings'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Steps in workflow templates';

--
-- Dumping data for table `tija_leave_workflow_template_steps`
--

INSERT INTO `tija_leave_workflow_template_steps` (`templateStepID`, `templateID`, `stepOrder`, `stepName`, `stepType`, `stepDescription`, `isRequired`, `isConditional`, `conditionType`, `conditionValue`, `escalationDays`, `notifySettings`) VALUES
(1, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(2, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(3, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(4, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(5, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(6, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(7, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(8, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(9, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(10, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(11, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(12, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(13, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(14, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(15, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(16, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(17, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(18, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(19, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(20, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(21, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(22, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(23, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(24, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(25, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(26, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(27, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(28, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(29, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(30, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(31, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(32, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(33, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(34, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(35, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(36, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(37, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(38, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(39, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(40, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(41, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(42, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(43, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(44, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(45, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(46, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(47, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(48, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(49, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(50, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(51, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(52, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(53, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(54, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(55, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(56, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(57, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(58, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(59, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(60, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(61, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(62, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(63, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(71, 8, 1, 'Direct Supervisor', 'supervisor', 'Direct Supervisor Approval', 'Y', 'N', NULL, NULL, 3, NULL),
(72, 8, 2, 'HR Manager', 'hr_manager', 'HR Manager Approval', 'Y', 'N', NULL, NULL, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_licenses`
--

CREATE TABLE `tija_licenses` (
  `licenseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL COMMENT 'Foreign key to tija_organisation_data',
  `licenseType` enum('trial','basic','standard','premium','enterprise') NOT NULL DEFAULT 'trial',
  `licenseKey` varchar(50) NOT NULL COMMENT 'Unique license key',
  `userLimit` int(11) NOT NULL DEFAULT 50 COMMENT 'Maximum number of users allowed',
  `currentUsers` int(11) NOT NULL DEFAULT 0 COMMENT 'Current active users count',
  `licenseIssueDate` date NOT NULL COMMENT 'Date license was issued',
  `licenseExpiryDate` date NOT NULL COMMENT 'Date license expires',
  `licenseStatus` enum('active','suspended','expired','trial') NOT NULL DEFAULT 'trial',
  `features` text DEFAULT NULL COMMENT 'JSON array of enabled features',
  `licenseNotes` text DEFAULT NULL COMMENT 'Additional notes about the license',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL COMMENT 'User ID who last updated',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores license information for tenant organizations';

--
-- Dumping data for table `tija_licenses`
--

INSERT INTO `tija_licenses` (`licenseID`, `DateAdded`, `orgDataID`, `licenseType`, `licenseKey`, `userLimit`, `currentUsers`, `licenseIssueDate`, `licenseExpiryDate`, `licenseStatus`, `features`, `licenseNotes`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 06:58:17', 1, 'basic', 'TIJA-BAS-2025-439ADBC8', 50, 0, '2025-11-01', '2026-11-01', 'active', '[\"payroll\",\"leave\",\"attendance\",\"reports\",\"employee_management\"]', 'Internal Licence that will not be charged', '2025-11-21 09:58:17', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_license_types`
--

CREATE TABLE `tija_license_types` (
  `licenseTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `licenseTypeName` varchar(100) NOT NULL COMMENT 'Display name (e.g., Standard, Premium)',
  `licenseTypeCode` varchar(50) NOT NULL COMMENT 'System code (e.g., standard, premium)',
  `licenseTypeDescription` text DEFAULT NULL COMMENT 'Detailed description of the license type',
  `defaultUserLimit` int(11) NOT NULL DEFAULT 50 COMMENT 'Default maximum users allowed',
  `monthlyPrice` decimal(10,2) DEFAULT NULL COMMENT 'Monthly subscription price',
  `yearlyPrice` decimal(10,2) DEFAULT NULL COMMENT 'Yearly subscription price (discounted)',
  `defaultDuration` int(11) NOT NULL DEFAULT 365 COMMENT 'Default license duration in days',
  `features` text DEFAULT NULL COMMENT 'JSON array of included features',
  `isPopular` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Mark as popular/recommended',
  `displayOrder` int(11) NOT NULL DEFAULT 0 COMMENT 'Sort order for display',
  `colorCode` varchar(20) DEFAULT NULL COMMENT 'Color for UI display (e.g., #5b6fe3)',
  `iconClass` varchar(50) DEFAULT NULL COMMENT 'Font Awesome icon class',
  `restrictions` text DEFAULT NULL COMMENT 'JSON array of restrictions/limitations',
  `benefits` text DEFAULT NULL COMMENT 'JSON array of key benefits',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL COMMENT 'User ID who last updated',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores configurable license types for tenant organizations';

--
-- Dumping data for table `tija_license_types`
--

INSERT INTO `tija_license_types` (`licenseTypeID`, `DateAdded`, `licenseTypeName`, `licenseTypeCode`, `licenseTypeDescription`, `defaultUserLimit`, `monthlyPrice`, `yearlyPrice`, `defaultDuration`, `features`, `isPopular`, `displayOrder`, `colorCode`, `iconClass`, `restrictions`, `benefits`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-24 18:00:50', 'Trial', 'trial', 'Perfect for testing and evaluation. Get started with all basic features for 30 days at no cost. Great for exploring the system before committing to a paid plan.', 10, 0.00, 0.00, 30, '[\"payroll\",\"leave\",\"attendance\",\"reports\"]', 'N', 1, '#95a5a6', 'fa-flask', '[\"Limited to 10 users\",\"30 days duration\",\"Basic features only\",\"No API access\",\"Email support only\"]', '[\"Free for 30 days\",\"No credit card required\",\"Full basic features\",\"Quick setup\",\"Email support\"]', '2025-10-24 18:15:18', NULL, 'N', 'N'),
(2, '2025-10-24 18:00:50', 'Basic', 'basic', 'Ideal for small teams and startups. Includes essential HR management features to streamline your basic operations with support for up to 50 users.', 50, 49.99, 499.99, 365, '[\"payroll\",\"leave\",\"attendance\",\"reports\",\"employee_management\"]', 'N', 2, '#3498db', 'fa-building', '[\"Up to 50 users\",\"Standard features\",\"Email support\",\"Monthly reports\",\"Limited integrations\"]', '[\"All basic features\",\"Employee management\",\"Leave tracking\",\"Attendance monitoring\",\"Email support\"]', '2025-10-24 18:16:51', NULL, 'N', 'N'),
(3, '2025-10-24 18:00:50', 'Standard', 'standard', 'Most popular choice for growing businesses. Comprehensive features with advanced reporting and analytics. Supports up to 200 users with priority support.', 200, 149.99, 1499.99, 365, '[\"payroll\",\"leave\",\"attendance\",\"performance\",\"reports\",\"employee_management\",\"training\",\"advanced_reports\"]', 'Y', 3, '#2ecc71', 'fa-star', '[\"Up to 200 users\",\"All standard features\",\"Priority email support\",\"Weekly reports\",\"Standard integrations\"]', '[\"All basic features\",\"Performance management\",\"Training modules\",\"Advanced reporting\",\"Priority support\",\"Custom workflows\"]', '2025-10-24 18:16:51', NULL, 'N', 'N'),
(4, '2025-10-24 18:00:50', 'Premium', 'premium', 'Advanced solution for large organizations. Includes all features plus recruitment, advanced analytics, and dedicated support for up to 500 users.', 500, 299.99, 2999.99, 365, '[\"payroll\",\"leave\",\"attendance\",\"performance\",\"recruitment\",\"reports\",\"employee_management\",\"training\",\"advanced_reports\",\"analytics\",\"custom_reports\"]', 'N', 4, '#9b59b6', 'fa-gem', '[\"Up to 500 users\",\"All premium features\",\"Priority phone & email support\",\"Daily reports\",\"Premium integrations\",\"Dedicated account manager\"]', '[\"All standard features\",\"Recruitment module\",\"Advanced analytics\",\"Custom reports\",\"Phone support\",\"Dedicated manager\",\"SLA guarantee\"]', '2025-10-24 18:00:50', NULL, 'N', 'N'),
(5, '2025-10-24 18:00:50', 'Enterprise', 'enterprise', 'Complete solution for large enterprises with unlimited users. Includes API access, white-label options, custom development, and 24/7 dedicated support.', 999999, NULL, NULL, 365, '[\"payroll\",\"leave\",\"attendance\",\"performance\",\"recruitment\",\"reports\",\"employee_management\",\"training\",\"advanced_reports\",\"analytics\",\"custom_reports\",\"api\",\"whitelabel\",\"custom_development\",\"sso\"]', 'N', 5, '#e74c3c', 'fa-crown', '[\"Unlimited users\",\"All features included\",\"24/7 phone & email support\",\"Real-time reports\",\"All integrations\",\"Custom development available\",\"White-label options\"]', '[\"Unlimited users\",\"API access\",\"White-label branding\",\"Custom development\",\"SSO integration\",\"24/7 support\",\"Dedicated team\",\"Custom SLA\",\"Priority updates\"]', '2025-10-24 18:00:50', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_name_prefixes`
--

CREATE TABLE `tija_name_prefixes` (
  `prefixID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `prefixName` varchar(10) NOT NULL,
  `prefixDescription` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `lapsed` enum('Y','N') DEFAULT 'N',
  `suspended` enum('Y','N') DEFAULT 'N',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_name_prefixes`
--

INSERT INTO `tija_name_prefixes` (`prefixID`, `DateAdded`, `prefixName`, `prefixDescription`, `is_active`, `lapsed`, `suspended`, `created_at`, `LastUpdate`) VALUES
(1, '2025-02-15 19:13:17', 'Mr.', 'Mister - Traditional honorific for men', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(2, '2025-02-15 19:13:17', 'Mrs.', 'Missus - Traditional honorific for married women', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(3, '2025-02-15 19:13:17', 'Ms.', 'Traditional honorific for women regardless of marital status', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(4, '2025-02-15 19:13:17', 'Miss', 'Traditional honorific for unmarried women', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(5, '2025-02-15 19:13:17', 'Dr.', 'Doctor - Academic or medical title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(6, '2025-02-15 19:13:17', 'Prof.', 'Professor - Academic title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(7, '2025-02-15 19:13:17', 'Rev.', 'Reverend - Religious title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(8, '2025-02-15 19:13:17', 'Hon.', 'Honorable - Title for judges, politicians', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(9, '2025-02-15 19:13:17', 'Sir', 'Traditional honorific for knights or formal address', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(10, '2025-02-15 19:13:17', 'Lady', 'Traditional honorific for women of rank', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(11, '2025-02-15 19:13:17', 'Capt.', 'Captain - Military or maritime title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(12, '2025-02-15 19:13:17', 'Lt.', 'Lieutenant - Military title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(13, '2025-02-15 19:13:17', 'Col.', 'Colonel - Military title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(14, '2025-02-15 19:13:17', 'Maj.', 'Major - Military title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(15, '2025-02-15 19:13:17', 'Adm.', 'Admiral - Naval title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22'),
(16, '2025-02-15 19:13:17', 'Eng.', 'Engineer - Professional title', 1, 'N', 'N', '2025-02-15 14:49:22', '2025-02-15 14:49:22');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notifications`
--

CREATE TABLE `tija_notifications` (
  `notificationID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(11) NOT NULL,
  `approverID` int(11) NOT NULL,
  `originatorUserID` int(11) NOT NULL,
  `targetUserID` int(11) NOT NULL,
  `segmentType` varchar(256) NOT NULL DEFAULT 'general',
  `segmentID` int(11) DEFAULT NULL,
  `notificationNotes` text NOT NULL,
  `notificationType` varchar(120) NOT NULL,
  `emailed` enum('Y','N') NOT NULL DEFAULT 'N',
  `notificationText` text DEFAULT NULL,
  `notificationStatus` enum('read','unread') NOT NULL DEFAULT 'unread',
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_notifications`
--

INSERT INTO `tija_notifications` (`notificationID`, `DateAdded`, `employeeID`, `approverID`, `originatorUserID`, `targetUserID`, `segmentType`, `segmentID`, `notificationNotes`, `notificationType`, `emailed`, `notificationText`, `notificationStatus`, `timestamp`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-27 06:44:11', 22, 22, 22, 22, 'clients', 1, '<p>You have been added as an engagement partner for client Test Company by Ian  Simba (IS)</p>\r\n												<p><a href=\'https://pms.sbsl.co.ke/html/?s=user&ss=clients&p=client_details&client_id1\'>View Client</a></p>\r\n												<p> You have been assigned to this client as an engagement partner.</p>', 'clients_engagement_partner_add', 'N', NULL, 'unread', '2025-11-27 06:44:11', 'N', 'N'),
(2, '2025-11-27 07:12:32', 0, 22, 22, 0, 'activity', 1, '<p>New activity <strong>Call Test Company</strong> has been assigned to you by Ian  Simba (IS). The activity Owner is Ian  Simba (IS). Please contact Ian  Simba (IS) for guidance </p>\r\n                        <p> The activity Date is 2025-11-27 and Start Time is 14:52 </p> \r\n\r\n                                                <p><a href=\'../../../html/?s=user&ss=schedule&p=activity_details&activityID=1\'>View Activity</a></p>\r\n                                                <p> You have been assigned to this activity.</p>', 'single_activities_assigned_Call', 'N', 'New activity <strong>Call Test Company</strong> has been assigned to you by \r\n                                                <p> You have been assigned to this activity.</p>\r\n                                                <a href=\'https://pms.sbsl.co.ke/html/?s=user&ss=schedule&p=activity_details&activityID=1\'>View Activity</a>', 'unread', '2025-11-27 07:12:32', 'N', 'N'),
(3, '2025-11-29 03:11:40', 5, 15, 15, 5, 'projects', 1, '<p>You have been assigned as a <strong>Project Manager</strong> for project: <strong>Rejea Build</strong></p>\n                                                <p>Assigned by Bryson  Yida (BY)</p>\n                                                <p><a href=\'https://pms.sbsl.co.ke/html/?s=user&ss=projects&p=project&pid=1\'>View Project</a></p>', 'Rejea Build_project_manager_assigned', 'N', NULL, 'unread', '2025-11-29 03:11:40', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notifications_enhanced`
--

CREATE TABLE `tija_notifications_enhanced` (
  `notificationID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `eventID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `originatorUserID` int(11) DEFAULT NULL,
  `entityID` int(11) DEFAULT NULL,
  `orgDataID` int(11) DEFAULT NULL,
  `segmentType` varchar(50) DEFAULT NULL,
  `segmentID` int(11) DEFAULT NULL,
  `notificationTitle` varchar(250) NOT NULL,
  `notificationBody` text NOT NULL,
  `notificationData` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notificationData`)),
  `notificationLink` varchar(500) DEFAULT NULL,
  `notificationIcon` varchar(50) DEFAULT 'ri-notification-line',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('unread','read','archived','deleted') DEFAULT 'unread',
  `readAt` datetime DEFAULT NULL,
  `archivedAt` datetime DEFAULT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notifications_enhanced`
--

INSERT INTO `tija_notifications_enhanced` (`notificationID`, `DateAdded`, `eventID`, `userID`, `originatorUserID`, `entityID`, `orgDataID`, `segmentType`, `segmentID`, `notificationTitle`, `notificationBody`, `notificationData`, `notificationLink`, `notificationIcon`, `priority`, `status`, `readAt`, `archivedAt`, `expiresAt`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(66, '2025-11-30 14:23:40', 2, 2, 4, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Felix Mauncho', 'Felix Mauncho has submitted a leave application for Annual Leave from Dec 1, 2025 to Dec 3, 2025 (3 day(s)). Please review and approve.', '{\"employee_id\":\"4\",\"employee_name\":\"Felix Mauncho\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":3,\"application_id\":\"1\",\"approval_level\":1,\"step_name\":\"Direct Supervisor\",\"approver_name\":\"Brian Nyongesa\",\"is_final_step\":false,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=1\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 06:49:30', NULL, NULL, '2025-12-01 06:49:30', 'N', 'N'),
(67, '2025-11-30 14:23:40', 2, 2, 4, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Felix Mauncho', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Felix Mauncho has submitted a Annual Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> Felix Mauncho</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 1, 2025 – Dec 3, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>1</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"4\",\"employee_name\":\"Felix Mauncho\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":3,\"application_id\":\"1\",\"approval_level\":1,\"step_name\":\"Direct Supervisor\",\"approver_name\":\"Brian Nyongesa\",\"is_final_step\":false,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=1\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 06:49:30', NULL, NULL, '2025-12-01 06:49:30', 'N', 'N'),
(68, '2025-11-30 14:23:40', 2, 3, 4, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Felix Mauncho', 'Felix Mauncho has submitted a leave application for Annual Leave from Dec 1, 2025 to Dec 3, 2025 (3 day(s)). Please review and approve.', '{\"employee_id\":\"4\",\"employee_name\":\"Felix Mauncho\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":3,\"application_id\":\"1\",\"approval_level\":2,\"step_name\":\"HR Manager\",\"approver_name\":\"Dennis Wabukala\",\"is_final_step\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=1\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-11-30 14:23:40', 'N', 'N'),
(69, '2025-11-30 14:23:40', 2, 3, 4, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Felix Mauncho', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Felix Mauncho has submitted a Annual Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> Felix Mauncho</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 1, 2025 – Dec 3, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>2</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"4\",\"employee_name\":\"Felix Mauncho\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":3,\"application_id\":\"1\",\"approval_level\":2,\"step_name\":\"HR Manager\",\"approver_name\":\"Dennis Wabukala\",\"is_final_step\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=1\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-11-30 14:23:40', 'N', 'N'),
(70, '2025-11-30 14:23:40', 2, 4, 4, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Felix Mauncho', 'Felix Mauncho has submitted a leave application for Annual Leave from Dec 1, 2025 to Dec 3, 2025 (3 day(s)). Please review and approve.', '{\"employee_id\":\"4\",\"employee_name\":\"Felix Mauncho\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":3,\"application_id\":\"1\",\"approval_level\":3,\"step_name\":\"HR Manager Final Approval\",\"approver_name\":\"Felix Mauncho\",\"is_final_step\":true,\"is_hr_manager\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=1\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-11-30 14:23:40', 'N', 'N'),
(71, '2025-11-30 14:23:40', 2, 4, 4, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Felix Mauncho', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Felix Mauncho has submitted a Annual Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> Felix Mauncho</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 1, 2025 – Dec 3, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>3</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"4\",\"employee_name\":\"Felix Mauncho\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":3,\"application_id\":\"1\",\"approval_level\":3,\"step_name\":\"HR Manager Final Approval\",\"approver_name\":\"Felix Mauncho\",\"is_final_step\":true,\"is_hr_manager\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=1\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-11-30 14:23:40', 'N', 'N'),
(72, '2025-11-30 14:23:41', 1, 4, 4, 1, 1, 'leave_application', 1, 'Leave Application Submitted', 'Your leave application for Annual Leave from Dec 1, 2025 to Dec 3, 2025 (3.00 day(s)) has been submitted successfully and is pending approval.', '{\"employee_name\":\"Felix Mauncho\",\"employee_id\":\"4\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":\"3.00\",\"leave_reason\":\"Personal leave\",\"application_id\":\"1\",\"application_link\":\"?s=user&ss=leave&p=my_applications&id=1\",\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=1\"}', '?s=user&ss=leave&p=my_applications&id=1', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-30 14:23:41', 'N', 'N'),
(73, '2025-11-30 14:23:41', 1, 4, 4, 1, 1, 'leave_application', 1, 'Leave Application Submitted', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Submitted</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Submitted</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request has been received and routed to your approvers.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> Felix Mauncho</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 1, 2025 – Dec 3, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3.00</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Reason provided: Personal leave</p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=1\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=1\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=1</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_name\":\"Felix Mauncho\",\"employee_id\":\"4\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 1, 2025\",\"end_date\":\"Dec 3, 2025\",\"total_days\":\"3.00\",\"leave_reason\":\"Personal leave\",\"application_id\":\"1\",\"application_link\":\"?s=user&ss=leave&p=my_applications&id=1\",\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=1\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=1\"}', '?s=user&ss=leave&p=my_applications&id=1', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-30 14:23:41', 'N', 'N'),
(74, '2025-12-01 08:19:15', 2, 23, 24, 1, 1, 'leave_application', 2, 'Leave Application Pending Approval - John Doe', 'John Doe has submitted a leave application for Annual Leave from Dec 2, 2025 to Dec 5, 2025 (4 day(s)). Please review and approve.', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":4,\"application_id\":\"2\",\"approval_level\":1,\"step_name\":\"Direct Supervisor\",\"approver_name\":\"Test User\",\"is_final_step\":false,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=2\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\"}', '?s=user&ss=leave&p=pending_approvals&id=2', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 08:23:08', NULL, NULL, '2025-12-01 08:23:08', 'N', 'N'),
(75, '2025-12-01 08:19:15', 2, 23, 24, 1, 1, 'leave_application', 2, 'Leave Application Pending Approval - John Doe', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">John Doe has submitted a Annual Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 2, 2025 – Dec 5, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 4</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>1</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":4,\"application_id\":\"2\",\"approval_level\":1,\"step_name\":\"Direct Supervisor\",\"approver_name\":\"Test User\",\"is_final_step\":false,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=2\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\"}', '?s=user&ss=leave&p=pending_approvals&id=2', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 08:23:18', NULL, NULL, '2025-12-01 08:23:18', 'N', 'N'),
(76, '2025-12-01 08:19:15', 2, 3, 24, 1, 1, 'leave_application', 2, 'Leave Application Pending Approval - John Doe', 'John Doe has submitted a leave application for Annual Leave from Dec 2, 2025 to Dec 5, 2025 (4 day(s)). Please review and approve.', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":4,\"application_id\":\"2\",\"approval_level\":2,\"step_name\":\"HR Manager\",\"approver_name\":\"Dennis Wabukala\",\"is_final_step\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=2\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\"}', '?s=user&ss=leave&p=pending_approvals&id=2', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 08:19:15', 'N', 'N'),
(77, '2025-12-01 08:19:15', 2, 3, 24, 1, 1, 'leave_application', 2, 'Leave Application Pending Approval - John Doe', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">John Doe has submitted a Annual Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 2, 2025 – Dec 5, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 4</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>2</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":4,\"application_id\":\"2\",\"approval_level\":2,\"step_name\":\"HR Manager\",\"approver_name\":\"Dennis Wabukala\",\"is_final_step\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=2\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\"}', '?s=user&ss=leave&p=pending_approvals&id=2', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 08:19:15', 'N', 'N'),
(78, '2025-12-01 08:19:15', 2, 4, 24, 1, 1, 'leave_application', 2, 'Leave Application Pending Approval - John Doe', 'John Doe has submitted a leave application for Annual Leave from Dec 2, 2025 to Dec 5, 2025 (4 day(s)). Please review and approve.', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":4,\"application_id\":\"2\",\"approval_level\":3,\"step_name\":\"HR Manager Final Approval\",\"approver_name\":\"Felix Mauncho\",\"is_final_step\":true,\"is_hr_manager\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=2\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\"}', '?s=user&ss=leave&p=pending_approvals&id=2', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 10:17:33', NULL, NULL, '2025-12-01 10:17:33', 'N', 'N'),
(79, '2025-12-01 08:19:15', 2, 4, 24, 1, 1, 'leave_application', 2, 'Leave Application Pending Approval - John Doe', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">John Doe has submitted a Annual Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 2, 2025 – Dec 5, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 4</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>3</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=2</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":4,\"application_id\":\"2\",\"approval_level\":3,\"step_name\":\"HR Manager Final Approval\",\"approver_name\":\"Felix Mauncho\",\"is_final_step\":true,\"is_hr_manager\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=2\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=2\"}', '?s=user&ss=leave&p=pending_approvals&id=2', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 08:19:15', 'N', 'N'),
(80, '2025-12-01 08:19:15', 1, 24, 24, 1, 1, 'leave_application', 2, 'Leave Application Submitted', 'Your leave application for Annual Leave from Dec 2, 2025 to Dec 5, 2025 (4.00 day(s)) has been submitted successfully and is pending approval.', '{\"employee_name\":\"John Doe\",\"employee_id\":\"24\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":\"4.00\",\"leave_reason\":\"sdf sfdg adfsg afdsgafsdga\",\"application_id\":\"2\",\"application_link\":\"?s=user&ss=leave&p=my_applications&id=2\",\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=2\"}', '?s=user&ss=leave&p=my_applications&id=2', 'ri-calendar-event-line', 'medium', 'read', '2025-12-01 10:19:45', NULL, NULL, '2025-12-01 10:19:45', 'N', 'N'),
(81, '2025-12-01 08:19:15', 1, 24, 24, 1, 1, 'leave_application', 2, 'Leave Application Submitted', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Submitted</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Submitted</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request has been received and routed to your approvers.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Annual Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 2, 2025 – Dec 5, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 4.00</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Reason provided: sdf sfdg adfsg afdsgafsdga</p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=2\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=2\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=2</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_name\":\"John Doe\",\"employee_id\":\"24\",\"leave_type\":\"Annual Leave\",\"start_date\":\"Dec 2, 2025\",\"end_date\":\"Dec 5, 2025\",\"total_days\":\"4.00\",\"leave_reason\":\"sdf sfdg adfsg afdsgafsdga\",\"application_id\":\"2\",\"application_link\":\"?s=user&ss=leave&p=my_applications&id=2\",\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=2\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=2\"}', '?s=user&ss=leave&p=my_applications&id=2', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-12-01 08:19:15', 'N', 'N'),
(82, '2025-12-01 10:20:48', 2, 23, 24, 1, 1, 'leave_application', 3, 'Leave Application Pending Approval - John Doe', 'John Doe has submitted a leave application for Sick Leave from Dec 9, 2025 to Dec 12, 2025 (3 day(s)). Please review and approve.', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":3,\"application_id\":\"3\",\"approval_level\":1,\"step_name\":\"Direct Supervisor\",\"approver_name\":\"Test User\",\"is_final_step\":false,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=3\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\"}', '?s=user&ss=leave&p=pending_approvals&id=3', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 10:21:26', NULL, NULL, '2025-12-01 10:21:26', 'N', 'N'),
(83, '2025-12-01 10:20:48', 2, 23, 24, 1, 1, 'leave_application', 3, 'Leave Application Pending Approval - John Doe', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">John Doe has submitted a Sick Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Sick Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 9, 2025 – Dec 12, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>1</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":3,\"application_id\":\"3\",\"approval_level\":1,\"step_name\":\"Direct Supervisor\",\"approver_name\":\"Test User\",\"is_final_step\":false,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=3\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\"}', '?s=user&ss=leave&p=pending_approvals&id=3', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 10:20:48', 'N', 'N');
INSERT INTO `tija_notifications_enhanced` (`notificationID`, `DateAdded`, `eventID`, `userID`, `originatorUserID`, `entityID`, `orgDataID`, `segmentType`, `segmentID`, `notificationTitle`, `notificationBody`, `notificationData`, `notificationLink`, `notificationIcon`, `priority`, `status`, `readAt`, `archivedAt`, `expiresAt`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(84, '2025-12-01 10:20:48', 2, 3, 24, 1, 1, 'leave_application', 3, 'Leave Application Pending Approval - John Doe', 'John Doe has submitted a leave application for Sick Leave from Dec 9, 2025 to Dec 12, 2025 (3 day(s)). Please review and approve.', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":3,\"application_id\":\"3\",\"approval_level\":2,\"step_name\":\"HR Manager\",\"approver_name\":\"Dennis Wabukala\",\"is_final_step\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=3\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\"}', '?s=user&ss=leave&p=pending_approvals&id=3', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 10:20:48', 'N', 'N'),
(85, '2025-12-01 10:20:48', 2, 3, 24, 1, 1, 'leave_application', 3, 'Leave Application Pending Approval - John Doe', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">John Doe has submitted a Sick Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Sick Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 9, 2025 – Dec 12, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>2</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":3,\"application_id\":\"3\",\"approval_level\":2,\"step_name\":\"HR Manager\",\"approver_name\":\"Dennis Wabukala\",\"is_final_step\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=3\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\"}', '?s=user&ss=leave&p=pending_approvals&id=3', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 10:20:48', 'N', 'N'),
(86, '2025-12-01 10:20:48', 2, 4, 24, 1, 1, 'leave_application', 3, 'Leave Application Pending Approval - John Doe', 'John Doe has submitted a leave application for Sick Leave from Dec 9, 2025 to Dec 12, 2025 (3 day(s)). Please review and approve.', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":3,\"application_id\":\"3\",\"approval_level\":3,\"step_name\":\"HR Manager Final Approval\",\"approver_name\":\"Felix Mauncho\",\"is_final_step\":true,\"is_hr_manager\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=3\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\"}', '?s=user&ss=leave&p=pending_approvals&id=3', 'ri-calendar-event-line', 'high', 'read', '2025-12-01 10:23:49', NULL, NULL, '2025-12-01 10:23:49', 'N', 'N'),
(87, '2025-12-01 10:20:48', 2, 4, 24, 1, 1, 'leave_application', 3, 'Leave Application Pending Approval - John Doe', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">John Doe has submitted a Sick Leave request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Sick Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 9, 2025 – Dec 12, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>3</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=3</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_id\":\"24\",\"employee_name\":\"John Doe\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":3,\"application_id\":\"3\",\"approval_level\":3,\"step_name\":\"HR Manager Final Approval\",\"approver_name\":\"Felix Mauncho\",\"is_final_step\":true,\"is_hr_manager\":true,\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link\":\"?s=user&ss=leave&p=pending_approvals&id=3\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=pending_approvals&id=3\"}', '?s=user&ss=leave&p=pending_approvals&id=3', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-01 10:20:48', 'N', 'N'),
(88, '2025-12-01 10:20:48', 1, 24, 24, 1, 1, 'leave_application', 3, 'Leave Application Submitted', 'Your leave application for Sick Leave from Dec 9, 2025 to Dec 12, 2025 (3.00 day(s)) has been submitted successfully and is pending approval.', '{\"employee_name\":\"John Doe\",\"employee_id\":\"24\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":\"3.00\",\"leave_reason\":\"Sick leave demo\",\"application_id\":\"3\",\"application_link\":\"?s=user&ss=leave&p=my_applications&id=3\",\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=3\"}', '?s=user&ss=leave&p=my_applications&id=3', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-12-01 10:20:48', 'N', 'N'),
(89, '2025-12-01 10:20:48', 1, 24, 24, 1, 1, 'leave_application', 3, 'Leave Application Submitted', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Submitted</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Submitted</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request has been received and routed to your approvers.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> John Doe</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> Sick Leave</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> Dec 9, 2025 – Dec 12, 2025</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> 3.00</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Reason provided: Sick leave demo</p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=3\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=3\" style=\"color:#2563eb;text-decoration:none;\">https://pms.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=3</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from Tija Practice Management System · https://pms.sbsl.co.ke\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '{\"employee_name\":\"John Doe\",\"employee_id\":\"24\",\"leave_type\":\"Sick Leave\",\"start_date\":\"Dec 9, 2025\",\"end_date\":\"Dec 12, 2025\",\"total_days\":\"3.00\",\"leave_reason\":\"Sick leave demo\",\"application_id\":\"3\",\"application_link\":\"?s=user&ss=leave&p=my_applications&id=3\",\"site_url\":\"https:\\/\\/pms.sbsl.co.ke\",\"site_name\":\"Tija Practice Management System\",\"application_link_full\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=3\",\"cta_link\":\"https:\\/\\/pms.sbsl.co.ke\\/html\\/?s=user&ss=leave&p=my_applications&id=3\"}', '?s=user&ss=leave&p=my_applications&id=3', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-12-01 10:20:48', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_channels`
--

CREATE TABLE `tija_notification_channels` (
  `channelID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `channelName` varchar(50) NOT NULL,
  `channelSlug` varchar(50) NOT NULL,
  `channelDescription` text DEFAULT NULL,
  `channelIcon` varchar(50) DEFAULT 'ri-notification-line',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `requiresConfiguration` enum('Y','N') DEFAULT 'N',
  `configFields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configFields`)),
  `sortOrder` int(11) DEFAULT 0,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_channels`
--

INSERT INTO `tija_notification_channels` (`channelID`, `DateAdded`, `channelName`, `channelSlug`, `channelDescription`, `channelIcon`, `isActive`, `requiresConfiguration`, `configFields`, `sortOrder`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 'In-App Notification', 'in_app', 'Display notifications in the application interface', 'ri-notification-3-line', 'Y', 'N', NULL, 1, '2025-10-22 06:56:25', 'N', 'N'),
(2, '2025-10-22 09:56:25', 'Email', 'email', 'Send notifications via email', 'ri-mail-line', 'Y', 'Y', NULL, 2, '2025-10-22 06:56:25', 'N', 'N'),
(3, '2025-10-22 09:56:25', 'SMS', 'sms', 'Send notifications via SMS', 'ri-message-3-line', 'Y', 'Y', NULL, 3, '2025-10-22 06:56:25', 'N', 'N'),
(4, '2025-10-22 09:56:25', 'Push Notification', 'push', 'Browser push notifications', 'ri-notification-badge-line', 'Y', 'Y', NULL, 4, '2025-10-22 06:56:25', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_entity_preferences`
--

CREATE TABLE `tija_notification_entity_preferences` (
  `entityPreferenceID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `entityID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `channelID` int(11) NOT NULL,
  `isEnabled` enum('Y','N') NOT NULL DEFAULT 'Y',
  `enforceForAllUsers` enum('Y','N') NOT NULL DEFAULT 'N',
  `notifyImmediately` enum('Y','N') NOT NULL DEFAULT 'Y',
  `notifyDigest` enum('Y','N') NOT NULL DEFAULT 'N',
  `digestFrequency` enum('none','daily','weekly') NOT NULL DEFAULT 'none',
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('N','Y') NOT NULL DEFAULT 'N',
  `Suspended` enum('N','Y') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_entity_preferences`
--

INSERT INTO `tija_notification_entity_preferences` (`entityPreferenceID`, `DateAdded`, `entityID`, `eventID`, `channelID`, `isEnabled`, `enforceForAllUsers`, `notifyImmediately`, `notifyDigest`, `digestFrequency`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-24 15:46:35', 1, 1, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(2, '2025-11-24 15:46:35', 1, 1, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(3, '2025-11-24 15:46:35', 1, 1, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(4, '2025-11-24 15:46:35', 1, 1, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(5, '2025-11-24 15:46:35', 1, 2, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(6, '2025-11-24 15:46:35', 1, 2, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(7, '2025-11-24 15:46:35', 1, 2, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(8, '2025-11-24 15:46:35', 1, 2, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(9, '2025-11-24 15:46:35', 1, 3, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(10, '2025-11-24 15:46:35', 1, 3, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(11, '2025-11-24 15:46:35', 1, 3, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(12, '2025-11-24 15:46:35', 1, 3, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(13, '2025-11-24 15:46:35', 1, 4, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(14, '2025-11-24 15:46:35', 1, 4, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(15, '2025-11-24 15:46:35', 1, 4, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(16, '2025-11-24 15:46:35', 1, 4, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(17, '2025-11-24 15:46:35', 1, 5, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(18, '2025-11-24 15:46:35', 1, 5, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(19, '2025-11-24 15:46:35', 1, 5, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(20, '2025-11-24 15:46:35', 1, 5, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(21, '2025-11-24 15:46:35', 1, 6, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(22, '2025-11-24 15:46:35', 1, 6, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(23, '2025-11-24 15:46:35', 1, 6, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(24, '2025-11-24 15:46:35', 1, 6, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(25, '2025-11-24 15:46:35', 1, 7, 1, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(26, '2025-11-24 15:46:35', 1, 7, 2, 'Y', 'N', 'Y', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(27, '2025-11-24 15:46:35', 1, 7, 3, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N'),
(28, '2025-11-24 15:46:35', 1, 7, 4, 'N', 'N', 'N', 'N', 'none', '2025-11-24 13:00:06', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_events`
--

CREATE TABLE `tija_notification_events` (
  `eventID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `moduleID` int(11) NOT NULL,
  `eventName` varchar(100) NOT NULL,
  `eventSlug` varchar(100) NOT NULL,
  `eventDescription` text DEFAULT NULL,
  `eventCategory` varchar(50) DEFAULT 'general',
  `isUserConfigurable` enum('Y','N') DEFAULT 'Y',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `defaultEnabled` enum('Y','N') DEFAULT 'Y',
  `priorityLevel` enum('low','medium','high','critical') DEFAULT 'medium',
  `sortOrder` int(11) DEFAULT 0,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_events`
--

INSERT INTO `tija_notification_events` (`eventID`, `DateAdded`, `moduleID`, `eventName`, `eventSlug`, `eventDescription`, `eventCategory`, `isUserConfigurable`, `isActive`, `defaultEnabled`, `priorityLevel`, `sortOrder`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 'Leave Application Submitted', 'leave_application_submitted', 'When an employee submits a leave application', 'application', 'Y', 'Y', 'Y', 'medium', 1, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 'Leave Pending Approval', 'leave_pending_approval', 'Notify approver of pending leave request', 'approval', 'Y', 'Y', 'Y', 'high', 2, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 1, 'Leave Approved', 'leave_approved', 'When leave application is approved', 'approval', 'Y', 'Y', 'Y', 'high', 3, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 1, 'Leave Rejected', 'leave_rejected', 'When leave application is rejected', 'approval', 'Y', 'Y', 'Y', 'high', 4, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 1, 'Leave Cancelled', 'leave_cancelled', 'When leave application is cancelled', 'application', 'Y', 'Y', 'Y', 'medium', 5, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(6, '2025-10-22 09:56:25', 1, 'Leave Approval Reminder', 'leave_approval_reminder', 'Reminder for pending approval', 'reminder', 'Y', 'Y', 'Y', 'medium', 6, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(7, '2025-10-22 09:56:25', 1, 'Leave Starting Soon', 'leave_starting_soon', 'Reminder that leave is starting soon', 'reminder', 'Y', 'Y', 'Y', 'low', 7, '2025-10-22 03:56:25', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_logs`
--

CREATE TABLE `tija_notification_logs` (
  `logID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `notificationID` int(11) DEFAULT NULL,
  `queueID` int(11) DEFAULT NULL,
  `eventID` int(11) NOT NULL,
  `channelID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `actionDetails` text DEFAULT NULL,
  `ipAddress` varchar(45) DEFAULT NULL,
  `userAgent` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_logs`
--

INSERT INTO `tija_notification_logs` (`logID`, `DateAdded`, `notificationID`, `queueID`, `eventID`, `channelID`, `userID`, `action`, `actionDetails`, `ipAddress`, `userAgent`) VALUES
(47, '2025-11-25 10:06:32', NULL, NULL, 0, 0, 13, 'mark_all_read', 'All notifications marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(66, '2025-11-30 14:23:40', 66, NULL, 2, 1, 2, 'created', 'In-app notification created', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(67, '2025-11-30 14:23:40', 67, 17, 2, 2, 2, 'sent', 'Email sent immediately via PHPMailer to brian@sbsl.co.ke', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(68, '2025-11-30 14:23:40', 68, NULL, 2, 1, 3, 'created', 'In-app notification created', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(69, '2025-11-30 14:23:40', 69, 18, 2, 2, 3, 'sent', 'Email sent immediately via PHPMailer to dennis@sbsl.co.ke', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(70, '2025-11-30 14:23:40', 70, NULL, 2, 1, 4, 'created', 'In-app notification created', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(71, '2025-11-30 14:23:41', 71, 19, 2, 2, 4, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@sbsl.co.ke', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(72, '2025-11-30 14:23:41', 72, NULL, 1, 1, 4, 'created', 'In-app notification created', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(73, '2025-11-30 14:23:41', 73, 20, 1, 2, 4, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@sbsl.co.ke', '41.90.209.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(74, '2025-12-01 06:49:30', NULL, NULL, 0, 0, 2, 'mark_all_read', 'All notifications marked as read', '105.163.157.255', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(75, '2025-12-01 08:19:15', 74, NULL, 2, 1, 23, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(76, '2025-12-01 08:19:15', 75, 21, 2, 2, 23, 'sent', 'Email sent immediately via PHPMailer to felix.maucho@skm.co.ke', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(77, '2025-12-01 08:19:15', 76, NULL, 2, 1, 3, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(78, '2025-12-01 08:19:15', 77, 22, 2, 2, 3, 'sent', 'Email sent immediately via PHPMailer to dennis@sbsl.co.ke', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(79, '2025-12-01 08:19:15', 78, NULL, 2, 1, 4, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(80, '2025-12-01 08:19:15', 79, 23, 2, 2, 4, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@sbsl.co.ke', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(81, '2025-12-01 08:19:15', 80, NULL, 1, 1, 24, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(82, '2025-12-01 08:19:16', 81, 24, 1, 2, 24, 'sent', 'Email sent immediately via PHPMailer to felixmauncho@gmail.com', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(83, '2025-12-01 08:23:08', 74, NULL, 0, 0, 23, 'read', 'Notification marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(84, '2025-12-01 08:23:18', 75, NULL, 0, 0, 23, 'read', 'Notification marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(85, '2025-12-01 10:17:33', 78, NULL, 0, 0, 4, 'read', 'Notification marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(86, '2025-12-01 10:19:45', 80, NULL, 0, 0, 24, 'read', 'Notification marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(87, '2025-12-01 10:20:48', 82, NULL, 2, 1, 23, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(88, '2025-12-01 10:20:48', 83, 25, 2, 2, 23, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@skm.co.ke', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(89, '2025-12-01 10:20:48', 84, NULL, 2, 1, 3, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(90, '2025-12-01 10:20:48', 85, 26, 2, 2, 3, 'sent', 'Email sent immediately via PHPMailer to dennis@sbsl.co.ke', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(91, '2025-12-01 10:20:48', 86, NULL, 2, 1, 4, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(92, '2025-12-01 10:20:48', 87, 27, 2, 2, 4, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@sbsl.co.ke', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(93, '2025-12-01 10:20:48', 88, NULL, 1, 1, 24, 'created', 'In-app notification created', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(94, '2025-12-01 10:20:49', 89, 28, 1, 2, 24, 'sent', 'Email sent immediately via PHPMailer to felixmauncho@gmail.com', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(95, '2025-12-01 10:21:26', 82, NULL, 0, 0, 23, 'read', 'Notification marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(96, '2025-12-01 10:23:49', 86, NULL, 0, 0, 4, 'read', 'Notification marked as read', '41.90.161.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_modules`
--

CREATE TABLE `tija_notification_modules` (
  `moduleID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `moduleName` varchar(100) NOT NULL,
  `moduleSlug` varchar(50) NOT NULL,
  `moduleDescription` text DEFAULT NULL,
  `moduleIcon` varchar(50) DEFAULT 'ri-notification-line',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `sortOrder` int(11) DEFAULT 0,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_modules`
--

INSERT INTO `tija_notification_modules` (`moduleID`, `DateAdded`, `moduleName`, `moduleSlug`, `moduleDescription`, `moduleIcon`, `isActive`, `sortOrder`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 'Leave Management', 'leave', 'Leave applications, approvals, and status updates', 'ri-calendar-event-line', 'Y', 1, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 'Sales & CRM', 'sales', 'Lead assignments, opportunity updates, and sales activities', 'ri-money-dollar-circle-line', 'Y', 2, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 'Tasks & Projects', 'projects', 'Task assignments, project updates, and deadlines', 'ri-task-line', 'Y', 3, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 'Activities & Events', 'activities', 'Activity reminders and event notifications', 'ri-calendar-check-line', 'Y', 4, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 'System Alerts', 'system', 'System-wide announcements and important alerts', 'ri-error-warning-line', 'Y', 5, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(6, '2025-10-22 09:56:25', 'HR & Employee', 'hr', 'Employee profile updates, documentation, and HR announcements', 'ri-user-line', 'Y', 6, '2025-10-22 06:56:25', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_preferences`
--

CREATE TABLE `tija_notification_preferences` (
  `preferenceID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `eventID` int(11) NOT NULL,
  `channelID` int(11) NOT NULL,
  `isEnabled` enum('Y','N') DEFAULT 'Y',
  `notifyImmediately` enum('Y','N') DEFAULT 'Y',
  `notifyDigest` enum('Y','N') DEFAULT 'N',
  `digestFrequency` enum('none','daily','weekly') DEFAULT 'none',
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_queue`
--

CREATE TABLE `tija_notification_queue` (
  `queueID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `notificationID` int(11) NOT NULL,
  `channelID` int(11) NOT NULL,
  `recipientEmail` varchar(250) DEFAULT NULL,
  `recipientPhone` varchar(20) DEFAULT NULL,
  `scheduledFor` datetime DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `maxAttempts` int(11) DEFAULT 3,
  `lastAttemptAt` datetime DEFAULT NULL,
  `status` enum('pending','processing','sent','failed','cancelled') DEFAULT 'pending',
  `errorMessage` text DEFAULT NULL,
  `sentAt` datetime DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_queue`
--

INSERT INTO `tija_notification_queue` (`queueID`, `DateAdded`, `notificationID`, `channelID`, `recipientEmail`, `recipientPhone`, `scheduledFor`, `attempts`, `maxAttempts`, `lastAttemptAt`, `status`, `errorMessage`, `sentAt`, `LastUpdate`) VALUES
(17, '2025-11-30 14:23:40', 67, 2, 'brian@sbsl.co.ke', NULL, '2025-11-30 14:23:40', 0, 3, NULL, 'sent', NULL, '2025-11-30 14:23:40', '2025-11-30 14:23:40'),
(18, '2025-11-30 14:23:40', 69, 2, 'dennis@sbsl.co.ke', NULL, '2025-11-30 14:23:40', 0, 3, NULL, 'sent', NULL, '2025-11-30 14:23:40', '2025-11-30 14:23:40'),
(19, '2025-11-30 14:23:41', 71, 2, 'felix.mauncho@sbsl.co.ke', NULL, '2025-11-30 14:23:41', 0, 3, NULL, 'sent', NULL, '2025-11-30 14:23:41', '2025-11-30 14:23:41'),
(20, '2025-11-30 14:23:41', 73, 2, 'felix.mauncho@sbsl.co.ke', NULL, '2025-11-30 14:23:41', 0, 3, NULL, 'sent', NULL, '2025-11-30 14:23:41', '2025-11-30 14:23:41'),
(21, '2025-12-01 08:19:15', 75, 2, 'felix.maucho@skm.co.ke', NULL, '2025-12-01 08:19:15', 0, 3, NULL, 'sent', NULL, '2025-12-01 08:19:15', '2025-12-01 08:19:15'),
(22, '2025-12-01 08:19:15', 77, 2, 'dennis@sbsl.co.ke', NULL, '2025-12-01 08:19:15', 0, 3, NULL, 'sent', NULL, '2025-12-01 08:19:15', '2025-12-01 08:19:15'),
(23, '2025-12-01 08:19:15', 79, 2, 'felix.mauncho@sbsl.co.ke', NULL, '2025-12-01 08:19:15', 0, 3, NULL, 'sent', NULL, '2025-12-01 08:19:15', '2025-12-01 08:19:15'),
(24, '2025-12-01 08:19:16', 81, 2, 'felixmauncho@gmail.com', NULL, '2025-12-01 08:19:16', 0, 3, NULL, 'sent', NULL, '2025-12-01 08:19:16', '2025-12-01 08:19:16'),
(25, '2025-12-01 10:20:48', 83, 2, 'felix.mauncho@skm.co.ke', NULL, '2025-12-01 10:20:48', 0, 3, NULL, 'sent', NULL, '2025-12-01 10:20:48', '2025-12-01 10:20:48'),
(26, '2025-12-01 10:20:48', 85, 2, 'dennis@sbsl.co.ke', NULL, '2025-12-01 10:20:48', 0, 3, NULL, 'sent', NULL, '2025-12-01 10:20:48', '2025-12-01 10:20:48'),
(27, '2025-12-01 10:20:48', 87, 2, 'felix.mauncho@sbsl.co.ke', NULL, '2025-12-01 10:20:48', 0, 3, NULL, 'sent', NULL, '2025-12-01 10:20:48', '2025-12-01 10:20:48'),
(28, '2025-12-01 10:20:49', 89, 2, 'felixmauncho@gmail.com', NULL, '2025-12-01 10:20:49', 0, 3, NULL, 'sent', NULL, '2025-12-01 10:20:49', '2025-12-01 10:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_templates`
--

CREATE TABLE `tija_notification_templates` (
  `templateID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `eventID` int(11) NOT NULL,
  `channelID` int(11) NOT NULL,
  `orgDataID` int(11) DEFAULT NULL,
  `entityID` int(11) DEFAULT NULL,
  `templateName` varchar(150) NOT NULL,
  `templateSubject` varchar(250) DEFAULT NULL,
  `templateBody` text NOT NULL,
  `templateVariables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`templateVariables`)),
  `isDefault` enum('Y','N') DEFAULT 'N',
  `isSystem` enum('Y','N') DEFAULT 'N',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `createdBy` int(11) DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_templates`
--

INSERT INTO `tija_notification_templates` (`templateID`, `DateAdded`, `eventID`, `channelID`, `orgDataID`, `entityID`, `templateName`, `templateSubject`, `templateBody`, `templateVariables`, `isDefault`, `isSystem`, `isActive`, `createdBy`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 1, NULL, NULL, 'Leave Application Submitted - In-App', 'Leave Application Submitted', 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been submitted successfully and is pending approval.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 2, NULL, NULL, 'Leave Application Submitted - Email', 'Leave Application Submitted', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Submitted</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Submitted</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request has been received and routed to your approvers.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Reason provided: {{leave_reason}}</p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"leave_reason\", \"application_id\", \"application_link\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 2, 1, NULL, NULL, 'Leave Pending Approval - In-App', 'Leave Application Pending Approval - {{employee_name}}', '{{employee_name}} has submitted a leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)). Please review and approve.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approval_level\", \"approver_name\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 3, 1, NULL, NULL, 'Leave Approved - In-App', 'Leave Application Approved', 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been approved.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approver_name\", \"approver_comments\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 4, 1, NULL, NULL, 'Leave Rejected - In-App', 'Leave Application Rejected', 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been rejected.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approver_name\", \"rejection_reason\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(6, '2025-11-25 09:50:03', 2, 2, NULL, NULL, '', 'Leave Application Pending Approval - {{employee_name}}', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">{{employee_name}} has submitted a {{leave_type}} request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>{{approval_level}}</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(7, '2025-11-25 09:50:03', 3, 2, NULL, NULL, '', 'Leave Application Approved', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Approved</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Approved</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Great news — {{approver_name}} approved your leave request.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Approved Leave Details</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <div style=\"margin:24px 0;padding:16px;border-radius:12px;background-color:#ecfdf5;\"><p style=\"margin:0;font-size:14px;color:#065f46;\"><strong>Approver comments:</strong> {{approver_comments}}</p></div>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N'),
(8, '2025-11-25 09:50:03', 4, 2, NULL, NULL, '', 'Leave Application Rejected', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Update</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Update</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request was not approved by {{approver_name}}.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <div style=\"margin:24px 0;padding:16px;border-radius:12px;background-color:#fef2f2;\"><p style=\"margin:0;font-size:14px;color:#991b1b;\"><strong>Reason provided:</strong> {{approver_comments}}</p></div>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Details</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-25 14:50:03', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_template_variables`
--

CREATE TABLE `tija_notification_template_variables` (
  `variableID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `moduleID` int(11) NOT NULL,
  `variableName` varchar(100) NOT NULL,
  `variableSlug` varchar(100) NOT NULL,
  `variableDescription` text DEFAULT NULL,
  `dataSource` varchar(100) DEFAULT NULL,
  `dataField` varchar(100) DEFAULT NULL,
  `exampleValue` varchar(250) DEFAULT NULL,
  `sortOrder` int(11) DEFAULT 0,
  `Lapsed` enum('N','Y') DEFAULT 'N',
  `Suspended` enum('N','Y') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_template_variables`
--

INSERT INTO `tija_notification_template_variables` (`variableID`, `DateAdded`, `moduleID`, `variableName`, `variableSlug`, `variableDescription`, `dataSource`, `dataField`, `exampleValue`, `sortOrder`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 'Employee Name', 'employee_name', 'Full name of the employee', 'people', 'CONCAT(FirstName, \" \", Surname)', 'John Doe', 1, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 'Employee ID', 'employee_id', 'ID of the employee', 'people', 'ID', '123', 2, 'N', 'N'),
(3, '2025-10-22 09:56:25', 1, 'Leave Type', 'leave_type', 'Type of leave', 'tija_leave_types', 'leaveTypeName', 'Annual Leave', 3, 'N', 'N'),
(4, '2025-10-22 09:56:25', 1, 'Start Date', 'start_date', 'Leave start date', 'tija_leave_applications', 'startDate', '2025-10-25', 4, 'N', 'N'),
(5, '2025-10-22 09:56:25', 1, 'End Date', 'end_date', 'Leave end date', 'tija_leave_applications', 'endDate', '2025-10-27', 5, 'N', 'N'),
(6, '2025-10-22 09:56:25', 1, 'Total Days', 'total_days', 'Total number of leave days', 'tija_leave_applications', 'noOfDays', '3', 6, 'N', 'N'),
(7, '2025-10-22 09:56:25', 1, 'Leave Reason', 'leave_reason', 'Reason for leave', 'tija_leave_applications', 'leaveComments', 'Family vacation', 7, 'N', 'N'),
(8, '2025-10-22 09:56:25', 1, 'Application ID', 'application_id', 'Leave application ID', 'tija_leave_applications', 'leaveApplicationID', '456', 8, 'N', 'N'),
(9, '2025-10-22 09:56:25', 1, 'Approver Name', 'approver_name', 'Name of the approver', 'people', 'CONCAT(FirstName, \" \", Surname)', 'Jane Smith', 9, 'N', 'N'),
(10, '2025-10-22 09:56:25', 1, 'Approval Level', 'approval_level', 'Current approval level', 'tija_leave_approval_steps', 'stepOrder', '1', 10, 'N', 'N'),
(11, '2025-10-22 09:56:25', 1, 'Application Link', 'application_link', 'Direct link to the application', 'generated', 'url', 'https://example.com/leave/123', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_organisation_data`
--

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
(1, '2025-11-21 06:58:17', NULL, 'Strategic Business Solutions Limited', 80, 30, '98309', 'P051147271C', 'Y', 'Rainbow Towers\r\nP. O. BOX 2021', '00100', 'Nairobi', 25, '+254 721 358850', NULL, 'info@sbsl.co.ke', '2025-11-21 09:58:17', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_organisation_roles`
--

CREATE TABLE `tija_organisation_roles` (
  `orgRoleID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `jobTotleID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `jobTitleID` int(11) NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_charts`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_chart_position_assignments`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_role_types`
--

CREATE TABLE `tija_org_role_types` (
  `roleTypeID` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `roleTypeName` varchar(100) NOT NULL COMMENT 'Display name (e.g., Executive, Management)',
  `roleTypeCode` varchar(20) NOT NULL COMMENT 'Short code (e.g., EXEC, MGT)',
  `roleTypeDescription` text DEFAULT NULL COMMENT 'Description of the role type',
  `displayOrder` int(11) DEFAULT 0 COMMENT 'Order for display in dropdowns',
  `colorCode` varchar(7) DEFAULT '#667eea' COMMENT 'Hex color code for badges',
  `iconClass` varchar(50) DEFAULT 'fa-user-tie' COMMENT 'FontAwesome icon class',
  `isDefault` enum('Y','N') DEFAULT 'N' COMMENT 'Is this a default/system role type',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Is this role type active',
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role types for organizational roles';

--
-- Dumping data for table `tija_org_role_types`
--

INSERT INTO `tija_org_role_types` (`roleTypeID`, `DateAdded`, `roleTypeName`, `roleTypeCode`, `roleTypeDescription`, `displayOrder`, `colorCode`, `iconClass`, `isDefault`, `isActive`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-14 13:41:50', 'Executive', 'EXEC', 'C-Level, Top Leadership', 1, '#dc3545', 'fa-crown', 'Y', 'Y', '2025-11-14 10:41:50', NULL, 'N', 'N'),
(2, '2025-11-14 13:41:50', 'Management', 'MGT', 'Directors, Managers', 2, '#ffc107', 'fa-user-tie', 'Y', 'Y', '2025-11-14 10:41:50', NULL, 'N', 'N'),
(3, '2025-11-14 13:41:50', 'Supervisory', 'SUPV', 'Team Leads, Supervisors', 3, '#17a2b8', 'fa-user-shield', 'Y', 'Y', '2025-11-14 10:41:50', NULL, 'N', 'N'),
(4, '2025-11-14 13:41:50', 'Operational', 'OPR', 'Officers, Staff (Default)', 4, '#28a745', 'fa-user', 'Y', 'Y', '2025-11-14 10:41:50', NULL, 'N', 'N'),
(5, '2025-11-14 13:41:50', 'Support', 'SUPP', 'Administrative, Assistants', 5, '#6c757d', 'fa-user-cog', 'Y', 'Y', '2025-11-14 10:41:50', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_overtime_multiplier`
--

CREATE TABLE `tija_overtime_multiplier` (
  `overtimeMultiplierID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectID` int(11) NOT NULL,
  `overtimeMultiplierName` varchar(254) NOT NULL,
  `multiplierRate` decimal(4,2) NOT NULL,
  `workTypeID` varchar(256) NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `entityID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_overtime_multiplier`
--

INSERT INTO `tija_overtime_multiplier` (`overtimeMultiplierID`, `DateAdded`, `projectID`, `overtimeMultiplierName`, `multiplierRate`, `workTypeID`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`, `entityID`) VALUES
(1, '2025-09-02 15:31:26', 53, 'Weekend Overtime', 2.50, '1,2,3', 4, '2025-09-02 15:31:26', 'N', 'N', 1),
(2, '2025-09-29 15:06:40', 51, 'Weekend Overtime', 2.50, '1', 4, '2025-09-29 15:06:40', 'N', 'N', 1),
(3, '2025-11-15 18:08:08', 74, 'Standard Overtime', 1.50, '3', 4, '2025-11-15 18:08:08', 'N', 'N', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tija_payroll_computation_rules`
--

CREATE TABLE `tija_payroll_computation_rules` (
  `ruleID` int(10) UNSIGNED NOT NULL,
  `DateAdded` timestamp NULL DEFAULT current_timestamp(),
  `orgDataID` int(10) UNSIGNED NOT NULL,
  `entityID` int(10) UNSIGNED NOT NULL,
  `ruleName` varchar(100) NOT NULL COMMENT 'e.g., PAYE Tax Calculation',
  `ruleDescription` text DEFAULT NULL,
  `ruleType` enum('tax','statutory_deduction','benefit','allowance','overtime') NOT NULL,
  `computationFormula` text NOT NULL COMMENT 'Formula or algorithm for calculation',
  `parameters` text DEFAULT NULL COMMENT 'JSON: Parameters needed for calculation',
  `effectiveDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `priority` int(11) DEFAULT 0 COMMENT 'Execution order',
  `createdBy` int(10) UNSIGNED DEFAULT NULL,
  `updatedBy` int(10) UNSIGNED DEFAULT NULL,
  `LastUpdated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_pay_grades`
--

CREATE TABLE `tija_pay_grades` (
  `payGradeID` int(10) UNSIGNED NOT NULL,
  `DateAdded` timestamp NULL DEFAULT current_timestamp(),
  `orgDataID` int(10) UNSIGNED NOT NULL,
  `entityID` int(10) UNSIGNED NOT NULL,
  `payGradeCode` varchar(20) NOT NULL COMMENT 'e.g., PG-1, PG-2, PG-3',
  `payGradeName` varchar(100) NOT NULL COMMENT 'e.g., Junior Level, Mid Level, Senior Level',
  `payGradeDescription` text DEFAULT NULL,
  `minSalary` decimal(15,2) NOT NULL COMMENT 'Minimum salary for this grade',
  `midSalary` decimal(15,2) NOT NULL COMMENT 'Midpoint salary for this grade',
  `maxSalary` decimal(15,2) NOT NULL COMMENT 'Maximum salary for this grade',
  `currency` varchar(10) DEFAULT 'KES',
  `gradeLevel` int(11) DEFAULT NULL COMMENT 'Numeric level for sorting (1=lowest, higher=senior)',
  `allowsOvertime` enum('Y','N') DEFAULT 'Y' COMMENT 'Can employees in this grade get overtime?',
  `bonusEligible` enum('Y','N') DEFAULT 'N' COMMENT 'Are employees in this grade eligible for bonuses?',
  `commissionEligible` enum('Y','N') DEFAULT 'N' COMMENT 'Are employees eligible for commission?',
  `notes` text DEFAULT NULL,
  `createdBy` int(10) UNSIGNED DEFAULT NULL,
  `updatedBy` int(10) UNSIGNED DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pay grade structure with salary ranges for each entity';

--
-- Dumping data for table `tija_pay_grades`
--

INSERT INTO `tija_pay_grades` (`payGradeID`, `DateAdded`, `orgDataID`, `entityID`, `payGradeCode`, `payGradeName`, `payGradeDescription`, `minSalary`, `midSalary`, `maxSalary`, `currency`, `gradeLevel`, `allowsOvertime`, `bonusEligible`, `commissionEligible`, `notes`, `createdBy`, `updatedBy`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-16 14:41:47', 1, 1, 'PG-1', 'Entry Level', 'For interns and junior staff with 0-1 years experience', 20000.00, 35000.00, 50000.00, 'KES', 1, 'Y', 'Y', 'Y', NULL, 4, 1, '2025-11-21 06:44:06', 'N', 'N'),
(2, '2025-10-16 14:41:47', 1, 1, 'PG-2', 'Junior Level', 'For staff with 1-3 years experience', 45000.00, 65000.00, 85000.00, 'KES', 2, 'Y', 'Y', 'N', NULL, 4, 4, '2025-10-16 14:41:47', 'N', 'N'),
(3, '2025-10-16 14:41:47', 1, 1, 'PG-3', 'Mid Level', 'For experienced staff with 3-5 years experience', 80000.00, 110000.00, 140000.00, 'KES', 3, 'Y', 'Y', 'N', NULL, 4, 4, '2025-10-16 14:41:47', 'N', 'N'),
(4, '2025-10-16 14:41:48', 1, 1, 'PG-4', 'Senior Level', 'For senior staff with 6-10 years experience', 135000.00, 167000.00, 199000.00, 'KES', 4, 'N', 'Y', 'Y', NULL, 4, 1, '2025-11-21 06:45:42', 'N', 'N'),
(5, '2025-10-16 14:41:48', 1, 1, 'PG-5', 'Lead/Manager', 'For team leads and managers with 10+ years', 200000.00, 249500.00, 299000.00, 'KES', 5, 'N', 'Y', 'Y', NULL, 4, 1, '2025-11-21 06:45:21', 'N', 'N'),
(6, '2025-10-16 14:41:48', 1, 1, 'PG-6', 'Executive', 'For senior management and C-level executives', 300000.00, 435000.00, 570000.00, 'KES', 6, 'N', 'Y', 'Y', NULL, 4, 1, '2025-11-21 06:44:49', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_permission_levels`
--

CREATE TABLE `tija_permission_levels` (
  `permissionLevelID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `permissionLevelTitle` varchar(255) NOT NULL,
  `permissionLevelDescription` mediumtext NOT NULL,
  `iconClass` varchar(256) NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_permission_profiles`
--

CREATE TABLE `tija_permission_profiles` (
  `permissionProfileID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `permissionProfileTitle` varchar(255) NOT NULL,
  `permissionProfileDescription` mediumtext NOT NULL,
  `permissionProfileScopeID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdatedByID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_permission_profiles`
--

INSERT INTO `tija_permission_profiles` (`permissionProfileID`, `DateAdded`, `permissionProfileTitle`, `permissionProfileDescription`, `permissionProfileScopeID`, `LastUpdate`, `Lapsed`, `Suspended`, `LastUpdatedByID`) VALUES
(1, '2024-06-21 09:59:31', 'Super Administrator', 'Admin with global privileges to the entire application', 1, '2024-06-21 09:59:31', 'N', 'N', 1),
(2, '2024-06-21 10:44:03', 'Instance Admin', 'User With instance scope', 2, '2024-06-21 10:44:03', 'N', 'N', 1),
(3, '2024-06-21 10:45:12', 'Group Entity Admin', 'User with admin across several entities in a group set up', 3, '2024-06-21 10:45:12', 'N', 'N', 1),
(4, '2024-06-21 10:46:41', 'Entity Admin', 'User with admin over single entity and subsidiaries', 4, '2024-06-21 10:46:41', 'N', 'N', 1),
(5, '2024-06-21 10:49:12', 'Unit Admin', 'User with Privileges over one or more units', 5, '2024-06-21 10:49:12', 'N', 'N', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tija_permission_roles`
--

CREATE TABLE `tija_permission_roles` (
  `permissionRoleID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `permRoleTitle` varchar(256) DEFAULT NULL,
  `permRoleDescription` mediumtext NOT NULL,
  `permissionProfileID` int(11) NOT NULL,
  `permissionScopeID` int(11) NOT NULL,
  `roleTypeID` int(11) DEFAULT NULL,
  `importPermission` enum('Y','N') DEFAULT 'N',
  `exportPermission` enum('Y','N') DEFAULT 'N',
  `viewPermission` enum('Y','N') DEFAULT 'N',
  `editPermission` enum('Y','N') DEFAULT 'N',
  `addPermission` enum('Y','N') DEFAULT 'N',
  `deletePermission` enum('Y','N') DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_permission_roles`
--

INSERT INTO `tija_permission_roles` (`permissionRoleID`, `DateAdded`, `permRoleTitle`, `permRoleDescription`, `permissionProfileID`, `permissionScopeID`, `roleTypeID`, `importPermission`, `exportPermission`, `viewPermission`, `editPermission`, `addPermission`, `deletePermission`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-21 13:39:08', 'Super Administrator', 'The supper administrator is all the top-level; admin access', 1, 1, 1, 'N', 'N', NULL, NULL, NULL, NULL, '2024-06-21 13:39:08', 1, 'N', 'N'),
(2, '2024-06-21 16:59:12', 'Instance Admin', 'The instance admin is responsible for an instance or selected instances in the platform', 2, 2, 1, 'N', 'N', 'N', 'N', 'N', 'N', '2024-06-21 16:59:12', 1, 'N', 'N'),
(3, '2024-06-21 17:20:44', 'Entity Administrator', 'Entity  Administrator is responsible for one or more entities of an instance or a g group', 3, 3, 1, 'N', 'N', 'N', 'N', 'N', 'N', '2024-06-21 17:20:44', 1, 'N', 'N'),
(4, '2024-06-21 17:23:40', 'Group Admin', 'A group Admin is responsible for all the entities in a group for one or all services/ Products within thee group', 3, 3, 1, 'N', 'N', 'N', 'N', 'N', 'N', '2024-06-21 17:23:40', 1, 'N', 'N'),
(5, '2024-06-21 17:25:07', 'Unit Admin', 'Unit admin is responsible for a specific unit within an entity or group could be any unit from a department to a team.', 5, 5, 1, 'N', 'N', 'N', 'N', 'N', 'N', '2024-06-21 17:25:07', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_permission_scopes`
--

CREATE TABLE `tija_permission_scopes` (
  `permissionScopeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `permissionScopeTitle` varchar(255) NOT NULL,
  `permissionScopeDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_permission_scopes`
--

INSERT INTO `tija_permission_scopes` (`permissionScopeID`, `DateAdded`, `permissionScopeTitle`, `permissionScopeDescription`, `LastUpdatedByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-20 14:32:30', 'Global', 'Global scope depicts the entire platform.', 1, '2024-06-20 14:32:30', 'N', 'N'),
(2, '2024-06-20 14:37:10', 'Instance', 'Instance Admins have a scope of one instance where they must belong', 1, '2024-06-20 14:37:10', 'N', 'N'),
(3, '2024-06-20 14:39:26', 'Group', 'Group levels include group access and any entity within the group', 1, '2024-06-20 14:39:26', 'N', 'N'),
(4, '2024-06-20 14:41:29', 'Entity', 'Entity admin/manager has a scope of a specific entity and any sub-entities within the mother entity', 1, '2024-06-20 14:41:29', 'N', 'N'),
(5, '2024-06-20 14:53:17', 'Unit', 'Unit Manager/admin is responsible within a specific unit.', 1, '2024-06-20 14:53:17', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_permission_types`
--

CREATE TABLE `tija_permission_types` (
  `permissionTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `permissionTypeTitle` varchar(255) NOT NULL,
  `permissionTypeDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_permission_types`
--

INSERT INTO `tija_permission_types` (`permissionTypeID`, `DateAdded`, `permissionTypeTitle`, `permissionTypeDescription`, `LastUpdatedByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-19 14:44:53', 'View', 'View Permissions', 0, '2024-06-19 14:44:53', 'N', 'N'),
(2, '2024-06-19 14:59:31', 'Add/Create', 'Add/ Create permissions', 0, '2024-06-19 14:59:31', 'N', 'N'),
(3, '2024-06-19 21:18:57', 'Edit/Update', 'Edit and update permissions', 1, '2024-06-19 21:18:57', 'N', 'N'),
(4, '2024-06-19 21:19:19', 'Delete', 'Delete Permissions', 1, '2024-06-19 21:19:19', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_pms_work_segment`
--

CREATE TABLE `tija_pms_work_segment` (
  `workSegmentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `workSegmentCode` varchar(100) DEFAULT NULL,
  `workSegmentName` varchar(255) NOT NULL,
  `workSegmentDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_pms_work_segment`
--

INSERT INTO `tija_pms_work_segment` (`workSegmentID`, `DateAdded`, `workSegmentCode`, `workSegmentName`, `workSegmentDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-02 14:05:19', '231073_PRO_5', 'Project', '<p>Project Tasks and segment particulars s relates to projects</p>', '2025-05-02 14:05:19', 37, 'N', 'N'),
(2, '2025-05-02 14:06:42', '393531_SAL_5', 'Sales', '<p>Sales segment particulars</p>', '2025-05-02 14:06:42', 37, 'N', 'N'),
(3, '2025-05-02 14:11:53', '818396_ACT_5', 'Activity', '<p>Activities segment particulars(sales Activities/meetings/calls/tasks/Todo)</p>', '2025-05-02 14:11:53', 37, 'N', 'N'),
(4, '2025-05-02 14:12:24', '921626_LEA_5', 'Leave', '<p>Leave Segment Management</p>', '2025-05-02 14:12:24', 37, 'N', 'N'),
(5, '2025-05-02 14:13:29', '729763_ADM_5', 'Administration', '<p>Inhouse Administrative Segment&nbsp;</p>', '2025-05-02 14:13:29', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_products`
--

CREATE TABLE `tija_products` (
  `productID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `productName` varchar(256) NOT NULL,
  `productDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_products`
--

INSERT INTO `tija_products` (`productID`, `DateAdded`, `productName`, `productDescription`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2024-04-14 21:29:09', 'Tija Request Desk', 'Request Desk serves as a central point for all requisitions and support for the organization.', 1, '2024-06-22 15:05:57', 'N', 'N'),
(2, '2024-04-14 21:34:42', 'Attendance Management', 'Tija Attendance & Check-in Management system', 1, '2025-03-29 18:52:28', 'N', 'N'),
(3, '2024-04-14 21:35:08', 'Tija Time Tracking', 'Tija time Tracking system', 0, '2024-04-14 21:35:08', 'N', 'N'),
(4, '2024-06-19 11:23:39', 'Tija Work Management', 'Tija Work management System to measure productivity, capacity utilization and headcount requirements', 1, '2024-06-19 11:23:55', 'N', 'N'),
(5, '2024-06-19 11:27:09', 'Tija Performance Management', 'Tija performance Management system provides a platform to help organizations through performance monitoring, evaluation and compensation.', 0, '2024-06-19 11:27:09', 'N', 'N'),
(6, '2024-06-22 14:57:21', 'Tija Recruitment', 'Tija recruitment systems is a recruitment portal that helps organize and align the recruitment process from requisition to onboarding', 0, '2024-06-22 14:57:21', 'N', 'N'),
(7, '2024-06-22 15:02:04', 'Tija Payroll', 'Tija\'s Payroll management system helps an organization manage its payroll obligations by calculating all deductions and taxes and is flexible enough to accommodate a hybrid system of work.', 0, '2024-06-22 15:02:04', 'N', 'N'),
(8, '2024-06-22 15:04:37', 'Tija Leave management', 'Tija leave management goes beyond leave planning and administration but also facilitates deliverables accountability for employees by allowing then to verify the handover of their deliverables to other parties not to affect the timelines for the organization.', 0, '2024-06-22 15:04:37', 'N', 'N'),
(9, '2024-06-22 18:15:53', 'Tija Comparative Worth index(CWI)', 'Tija CWI is a job evaluation and analysis tool for the futuristic company.', 0, '2024-06-22 18:15:53', 'N', 'N'),
(10, '2025-01-17 11:53:22', 'Tija Tax Computation and Management System.', 'Tija Tax Management and computation system is designed to streamline the process of calculating, Managing and reporting taxes from multiple entities within a corporate group addressing complexities of consolidated tax reporting, intercompany transactions and jurisdiction-specific tax compliance for groups operating within multiple regions or countriess', 0, '2025-01-17 11:53:22', 'N', 'N'),
(11, '2025-04-05 15:23:28', 'Practice Management System', 'Incorporates Project Management, practice management, Performance Managenet, Leave and Payrol', 0, '2025-04-05 15:23:28', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_product_billing_period_levels`
--

CREATE TABLE `tija_product_billing_period_levels` (
  `productBillingPeriodLevelID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `productBillingPeriodLevelName` varchar(255) NOT NULL,
  `productBillingPeriodLevelDescription` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_product_billing_period_levels`
--

INSERT INTO `tija_product_billing_period_levels` (`productBillingPeriodLevelID`, `DateAdded`, `productBillingPeriodLevelName`, `productBillingPeriodLevelDescription`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-09 14:05:45', 'Billable Now', 'Invoices that are due now', 0, '2025-03-09 14:05:45', 'N', 'N'),
(2, '2025-03-09 14:06:36', 'Billable Later', 'Invoices billable in the near future', 0, '2025-03-09 14:06:36', 'N', 'N'),
(3, '2025-03-09 14:07:07', 'Billed', 'Value of invoices already Billed', 0, '2025-03-09 14:07:07', 'N', 'N'),
(4, '2025-03-09 14:07:41', 'Non-billable', 'Invoices for work that is not billable to the project', 0, '2025-03-09 14:07:41', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_product_rates`
--

CREATE TABLE `tija_product_rates` (
  `productRateID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `productRateName` varchar(256) NOT NULL,
  `productRateTypeID` int(11) NOT NULL,
  `priceRate` decimal(10,2) NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_product_rates`
--

INSERT INTO `tija_product_rates` (`productRateID`, `DateAdded`, `projectID`, `entityID`, `productRateName`, `productRateTypeID`, `priceRate`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-15 18:03:42', 74, 1, 'Consulting Day', 1, 150000.00, 0, '2025-11-15 18:03:42', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_product_rate_types`
--

CREATE TABLE `tija_product_rate_types` (
  `productRateTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `productRateTypeName` varchar(255) NOT NULL,
  `productRateTypeDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_product_rate_types`
--

INSERT INTO `tija_product_rate_types` (`productRateTypeID`, `DateAdded`, `productRateTypeName`, `productRateTypeDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-08 17:08:45', 'Consulting Day', 'An extra consulting day', '2025-03-08 17:08:45', 11, 'N', 'N'),
(2, '2025-03-08 17:26:54', 'Equipment Purchase', 'Equipment purchase&nbsp;', '2025-03-08 17:26:54', 11, 'N', 'N'),
(3, '2025-03-08 17:30:30', 'Product license', 'Product license purchase', '2025-03-08 17:30:30', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_product_types`
--

CREATE TABLE `tija_product_types` (
  `productTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `productTypeName` varchar(255) NOT NULL,
  `productTypeDescription` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_product_types`
--

INSERT INTO `tija_product_types` (`productTypeID`, `DateAdded`, `productTypeName`, `productTypeDescription`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-09 12:08:53', 'Own Work', 'Work done by employees', 0, '2025-03-09 12:08:53', 'N', 'N'),
(2, '2025-03-09 12:09:42', 'Products', 'Product Work', 0, '2025-03-09 12:09:42', 'N', 'N'),
(3, '2025-03-09 12:26:15', 'SubContracting', 'Subcontracting&nbsp;', 0, '2025-03-09 12:26:15', 'N', 'N'),
(4, '2025-03-09 12:39:00', 'Travel Expenses', 'Travel Expenses', 0, '2025-03-09 12:39:00', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_projects`
--

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
  `isRecurring` enum('Y','N') DEFAULT 'N',
  `recurrenceType` enum('weekly','monthly','quarterly','annually','custom') DEFAULT NULL,
  `recurrenceInterval` int(11) DEFAULT 1 COMMENT 'e.g., every 2 weeks',
  `recurrenceDayOfWeek` int(11) DEFAULT NULL COMMENT '1-7 for weekly, NULL for others',
  `recurrenceDayOfMonth` int(11) DEFAULT NULL COMMENT '1-31 for monthly/quarterly',
  `recurrenceMonthOfYear` int(11) DEFAULT NULL COMMENT '1-12 for annually',
  `recurrenceStartDate` date DEFAULT NULL,
  `recurrenceEndDate` date DEFAULT NULL COMMENT 'NULL for indefinite',
  `recurrenceCount` int(11) DEFAULT NULL COMMENT 'number of cycles, NULL for indefinite',
  `planReuseMode` enum('same','customizable') DEFAULT 'same',
  `teamAssignmentMode` enum('template','instance','both') DEFAULT 'template',
  `billingCycleAmount` decimal(15,2) DEFAULT NULL COMMENT 'amount per billing cycle',
  `autoGenerateInvoices` enum('Y','N') DEFAULT 'N',
  `invoiceDaysBeforeDue` int(11) DEFAULT 7 COMMENT 'days before cycle end to generate draft',
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

INSERT INTO `tija_projects` (`projectID`, `DateAdded`, `DateLastUpdated`, `projectCode`, `projectName`, `orgDataID`, `caseID`, `entityID`, `clientID`, `projectStart`, `projectClose`, `projectDeadline`, `projectOwnerID`, `projectManagersIDs`, `billable`, `billingRateID`, `billableRateValue`, `roundingoff`, `roundingInterval`, `businessUnitID`, `projectValue`, `approval`, `projectStatus`, `isRecurring`, `recurrenceType`, `recurrenceInterval`, `recurrenceDayOfWeek`, `recurrenceDayOfMonth`, `recurrenceMonthOfYear`, `recurrenceStartDate`, `recurrenceEndDate`, `recurrenceCount`, `planReuseMode`, `teamAssignmentMode`, `billingCycleAmount`, `autoGenerateInvoices`, `invoiceDaysBeforeDue`, `salesCaseID`, `projectTypeID`, `orderDate`, `projectType`, `allocatedWorkHours`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-29 11:11:40', '2025-11-29 03:11:40', 'REJ_O9BJ5', 'Rejea Build', 1, 0, 1, 1, '2025-11-29', '2026-01-02', '2026-01-02', 15, NULL, 'N', 1, 4000.00, 'no_rounding', NULL, 2, 99999999.99, 'N', '', 'N', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'same', 'template', NULL, 'N', 7, NULL, 2, NULL, 'client', NULL, '2025-11-29 11:11:40', 15, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_assignments`
--

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
-- Table structure for table `tija_project_expenses`
--

CREATE TABLE `tija_project_expenses` (
  `expenseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `expenseTypeID` int(11) NOT NULL,
  `expenseAmount` decimal(10,2) NOT NULL,
  `expenseDescription` text DEFAULT NULL,
  `expenseDate` date DEFAULT NULL,
  `expenseStatus` enum('pending','approved','rejected','disputed') NOT NULL DEFAULT 'pending',
  `expenseDocuments` text DEFAULT NULL,
  `timeLogID` int(11) DEFAULT NULL,
  `projectID` int(11) DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_fee_expenses`
--

CREATE TABLE `tija_project_fee_expenses` (
  `projectFeeExpenseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `productTypeID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `feeCostName` varchar(255) NOT NULL,
  `feeCostDescription` text NOT NULL,
  `productQuantity` int(11) NOT NULL,
  `productUnit` varchar(120) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL,
  `unitCost` decimal(10,2) NOT NULL,
  `vat` int(11) NOT NULL,
  `dateOfCost` date DEFAULT NULL,
  `billable` varchar(120) NOT NULL,
  `billingDate` date NOT NULL,
  `billingFrequency` int(11) NOT NULL,
  `billingFrequencyUnit` varchar(120) NOT NULL,
  `billingStartDate` date DEFAULT NULL,
  `recurrenceEnd` date DEFAULT NULL,
  `recurrencyTimes` int(11) DEFAULT NULL,
  `billingEndDate` date DEFAULT NULL,
  `billingPhaseID` int(11) DEFAULT NULL,
  `billingMilestone` int(11) DEFAULT NULL,
  `billed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastupdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_files`
--

CREATE TABLE `tija_project_files` (
  `fileID` int(10) UNSIGNED NOT NULL,
  `projectID` int(11) NOT NULL,
  `taskID` int(11) DEFAULT NULL COMMENT 'Optional task linkage',
  `fileName` varchar(255) NOT NULL,
  `fileOriginalName` varchar(255) NOT NULL,
  `fileURL` varchar(500) NOT NULL,
  `fileType` varchar(50) DEFAULT NULL COMMENT 'pdf, docx, xlsx, image, etc.',
  `fileSize` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `fileMimeType` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'contract, design, report, etc.',
  `version` varchar(20) DEFAULT '1.0',
  `uploadedBy` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `isPublic` enum('Y','N') DEFAULT 'N' COMMENT 'Accessible to client',
  `downloadCount` int(11) DEFAULT 0,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project file and document management';

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_memeber_categories`
--

CREATE TABLE `tija_project_memeber_categories` (
  `projectTeamMemeberCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `teamMemberCategoryName` varchar(255) NOT NULL,
  `teamMemberCategoryDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_phases`
--

CREATE TABLE `tija_project_phases` (
  `projectPhaseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectID` int(11) NOT NULL,
  `billingCycleID` int(11) DEFAULT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `projectPhaseName` varchar(180) NOT NULL,
  `phaseDescription` text NOT NULL,
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

INSERT INTO `tija_project_phases` (`projectPhaseID`, `DateAdded`, `projectID`, `billingCycleID`, `projectPhaseName`, `phaseDescription`, `phaseStartDate`, `phaseEndDate`, `phaseWorkHrs`, `phaseWeighting`, `billingMilestone`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-29 11:11:40', 1, NULL, 'Planning', 'Project planning and resource allocation', '2025-11-29', '2025-12-07', NULL, NULL, 'N', '2025-11-29 11:11:40', 15, 'N', 'N'),
(2, '2025-11-29 11:11:40', 1, NULL, 'Development', 'Core development and implementation', '2025-12-07', '2025-12-15', NULL, NULL, 'N', '2025-11-29 11:11:40', 15, 'N', 'N'),
(3, '2025-11-29 11:11:40', 1, NULL, 'Testing', 'Quality assurance and testing', '2025-12-15', '2025-12-23', NULL, NULL, 'N', '2025-11-29 11:11:40', 15, 'N', 'N'),
(4, '2025-11-29 11:11:40', 1, NULL, 'Deployment', 'Launch and deployment to production', '2025-12-23', '2026-01-02', NULL, NULL, 'N', '2025-11-29 11:11:40', 15, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_plan_templates`
--

CREATE TABLE `tija_project_plan_templates` (
  `templateID` int(11) NOT NULL,
  `templateName` varchar(200) NOT NULL,
  `templateDescription` text DEFAULT NULL,
  `templateCategory` varchar(100) DEFAULT NULL COMMENT 'e.g., software, construction, marketing',
  `isPublic` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Organization-wide, N=Personal',
  `isSystemTemplate` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Built-in, cannot be deleted',
  `createdByID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `usageCount` int(11) DEFAULT 0 COMMENT 'Track how many times template is used',
  `lastUsedDate` datetime DEFAULT NULL,
  `isActive` enum('Y','N') NOT NULL DEFAULT 'Y',
  `DateAdded` datetime NOT NULL,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores reusable project plan templates for organization-wide use';

--
-- Dumping data for table `tija_project_plan_templates`
--

INSERT INTO `tija_project_plan_templates` (`templateID`, `templateName`, `templateDescription`, `templateCategory`, `isPublic`, `isSystemTemplate`, `createdByID`, `orgDataID`, `entityID`, `usageCount`, `lastUsedDate`, `isActive`, `DateAdded`, `LastUpdate`, `LastUpdateByID`) VALUES
(1, 'Standard Software Project', 'A general-purpose template for software development projects', 'software', 'Y', 'Y', 1, 1, NULL, 6, '2025-11-29 03:10:54', 'Y', '2025-11-04 13:32:24', NULL, NULL),
(2, 'Agile Sprint', 'Template for agile/scrum sprint-based projects', 'software', 'Y', 'Y', 1, 1, NULL, 12, '2025-11-18 15:26:16', 'Y', '2025-11-04 13:32:24', NULL, NULL),
(3, 'Waterfall Project', 'Traditional waterfall methodology project template', 'software', 'Y', 'Y', 1, 1, NULL, 12, '2025-11-19 09:41:47', 'Y', '2025-11-04 13:32:24', NULL, NULL),
(4, 'Research Project', 'Academic or business research project template', 'research', 'Y', 'Y', 1, 1, NULL, 0, NULL, 'Y', '2025-11-04 13:32:24', NULL, NULL),
(5, 'Construction Project', 'Building and construction project template', 'construction', 'Y', 'Y', 1, 1, NULL, 0, NULL, 'Y', '2025-11-04 13:32:24', NULL, NULL),
(6, 'Marketing Campaign', 'Marketing campaign project template', 'marketing', 'Y', 'Y', 1, 1, NULL, 1, '2025-11-14 23:14:47', 'Y', '2025-11-04 13:32:24', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_plan_template_phases`
--

CREATE TABLE `tija_project_plan_template_phases` (
  `templatePhaseID` int(11) NOT NULL,
  `templateID` int(11) NOT NULL,
  `phaseName` varchar(200) NOT NULL,
  `phaseDescription` text DEFAULT NULL,
  `phaseOrder` int(11) NOT NULL DEFAULT 0,
  `phaseColor` varchar(20) DEFAULT NULL COMMENT 'Hex color code for visual representation',
  `estimatedDuration` int(11) DEFAULT NULL COMMENT 'Estimated duration in days',
  `durationPercent` decimal(5,2) DEFAULT NULL COMMENT 'Percentage of total project duration',
  `DateAdded` datetime NOT NULL,
  `LastUpdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores individual phases for each project plan template with duration percentages';

--
-- Dumping data for table `tija_project_plan_template_phases`
--

INSERT INTO `tija_project_plan_template_phases` (`templatePhaseID`, `templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `phaseColor`, `estimatedDuration`, `durationPercent`, `DateAdded`, `LastUpdate`) VALUES
(1, 1, 'Planning', 'Project planning and resource allocation', 1, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(2, 1, 'Development', 'Core development and implementation', 2, NULL, NULL, 50.00, '2025-11-04 13:32:24', NULL),
(3, 1, 'Testing', 'Quality assurance and testing', 3, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(4, 1, 'Deployment', 'Launch and deployment to production', 4, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL),
(5, 2, 'Sprint Planning', 'Sprint goal and backlog refinement', 1, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL),
(6, 2, 'Development', 'Sprint development and daily standups', 2, NULL, NULL, 70.00, '2025-11-04 13:32:24', NULL),
(7, 2, 'Review', 'Sprint review with stakeholders', 3, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL),
(8, 2, 'Retrospective', 'Team retrospective and improvements', 4, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL),
(9, 3, 'Requirements', 'Requirements gathering and analysis', 1, NULL, NULL, 15.00, '2025-11-04 13:32:24', NULL),
(10, 3, 'Design', 'System and UI/UX design', 2, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(11, 3, 'Implementation', 'Development and coding', 3, NULL, NULL, 40.00, '2025-11-04 13:32:24', NULL),
(12, 3, 'Testing', 'System testing and QA', 4, NULL, NULL, 15.00, '2025-11-04 13:32:24', NULL),
(13, 3, 'Maintenance', 'Deployment and ongoing maintenance', 5, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL),
(14, 4, 'Literature Review', 'Research existing materials and studies', 1, NULL, NULL, 25.00, '2025-11-04 13:32:24', NULL),
(15, 4, 'Data Collection', 'Gather data and conduct experiments', 2, NULL, NULL, 35.00, '2025-11-04 13:32:24', NULL),
(16, 4, 'Analysis', 'Analyze results and findings', 3, NULL, NULL, 25.00, '2025-11-04 13:32:24', NULL),
(17, 4, 'Reporting', 'Document and present findings', 4, NULL, NULL, 15.00, '2025-11-04 13:32:24', NULL),
(18, 5, 'Planning & Permits', 'Project planning and permit acquisition', 1, NULL, NULL, 15.00, '2025-11-04 13:32:24', NULL),
(19, 5, 'Foundation', 'Site preparation and foundation work', 2, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(20, 5, 'Structure', 'Main structure construction', 3, NULL, NULL, 35.00, '2025-11-04 13:32:24', NULL),
(21, 5, 'Finishing', 'Interior and exterior finishing', 4, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(22, 5, 'Handover', 'Final inspection and client handover', 5, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL),
(23, 6, 'Market Research', 'Research target audience and competitors', 1, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(24, 6, 'Strategy Development', 'Develop campaign strategy and messaging', 2, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(25, 6, 'Content Creation', 'Create campaign materials and content', 3, NULL, NULL, 30.00, '2025-11-04 13:32:24', NULL),
(26, 6, 'Campaign Launch', 'Execute and launch campaign', 4, NULL, NULL, 20.00, '2025-11-04 13:32:24', NULL),
(27, 6, 'Analysis & Reporting', 'Measure results and optimize', 5, NULL, NULL, 10.00, '2025-11-04 13:32:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_roles`
--

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
  `billingCycleID` int(11) DEFAULT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
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

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_task_types`
--

CREATE TABLE `tija_project_task_types` (
  `projectTaskTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectTaskTypeName` varchar(255) NOT NULL,
  `projectTaskTypeDescription` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `projectTaskTypeCode` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_project_task_types`
--

INSERT INTO `tija_project_task_types` (`projectTaskTypeID`, `DateAdded`, `projectTaskTypeName`, `projectTaskTypeDescription`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`, `projectTaskTypeCode`) VALUES
(1, '2025-03-24 06:46:29', 'One-off Task', 'One-off Task', 11, '2025-03-24 06:46:29', 'N', 'N', 'one_off_task'),
(2, '2025-03-24 06:46:29', 'Recurrent Task', 'Recurrent Task', 11, '2025-03-24 06:46:29', 'N', 'N', 'recurrent_task');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_team`
--

CREATE TABLE `tija_project_team` (
  `projectTeamMemberID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `userID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL,
  `projectTeamRoleID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdateByID` int(11) NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_project_team`
--

INSERT INTO `tija_project_team` (`projectTeamMemberID`, `DateAdded`, `userID`, `projectID`, `projectTeamRoleID`, `orgDataID`, `entityID`, `LastUpdate`, `Lapsed`, `LastUpdateByID`, `Suspended`) VALUES
(1, '2025-11-29 11:11:40', 5, 1, 2, 1, 1, '2025-11-29 11:11:40', 'N', 15, 'N'),
(2, '2025-11-29 11:11:40', 16, 1, 3, 1, 1, '2025-11-29 11:11:40', 'N', 15, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_team_roles`
--

CREATE TABLE `tija_project_team_roles` (
  `projectTeamRoleID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectTeamRoleName` varchar(255) NOT NULL,
  `projectTeamRoleDescription` text NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_project_team_roles`
--

INSERT INTO `tija_project_team_roles` (`projectTeamRoleID`, `DateAdded`, `projectTeamRoleName`, `projectTeamRoleDescription`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-11 14:51:10', 'Consultant', '&lt;p&gt;Product /implementation consultant&lt;/p&gt;', 0, '2025-03-11 14:51:10', 'N', 'N'),
(2, '2025-03-11 15:09:23', 'Project Manager', '<p>Project Manager</p>', 0, '2025-03-11 15:09:23', 'N', 'N'),
(3, '2025-03-11 15:10:58', 'Senior Specialist', '<p>Senior Specialist</p>', 0, '2025-03-11 15:10:58', 'N', 'N'),
(4, '2025-05-19 16:36:54', 'Quality Assurance', '<p>The role ensures the project quality is up to standard. To review the project and ensure it captures the spirit and vision of the client</p>', 0, '2025-05-19 16:36:54', 'N', 'N'),
(5, '2025-05-19 17:46:59', 'JuniorSpecialist', '<p>Junior specialist</p>', 0, '2025-05-19 17:46:59', 'N', 'N'),
(6, '2025-05-22 17:03:37', 'Liason Partner', '<p>Liason Partner</p>', 0, '2025-05-22 17:03:37', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_types`
--

CREATE TABLE `tija_project_types` (
  `projectTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectTypeName` varchar(255) NOT NULL,
  `projectTypeDescription` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_project_types`
--

INSERT INTO `tija_project_types` (`projectTypeID`, `DateAdded`, `projectTypeName`, `projectTypeDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-24 06:47:35', 'Client Project', 'Client Project', '2025-03-24 06:47:35', 0, 'N', 'N'),
(2, '2025-03-24 06:47:35', 'Internal Project', 'Internal Project', '2025-03-24 06:47:35', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposals`
--

CREATE TABLE `tija_proposals` (
  `proposalID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `proposalCode` varchar(120) DEFAULT NULL,
  `proposalTitle` varchar(255) NOT NULL,
  `proposalDescription` text NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `salesCaseID` int(11) NOT NULL,
  `proposalDeadline` date NOT NULL,
  `proposalStatusID` int(11) NOT NULL,
  `proposalComments` text NOT NULL,
  `proposalValue` decimal(16,2) NOT NULL,
  `proposalOwnerID` int(11) NOT NULL,
  `proposalFile` varchar(255) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `completionPercentage` decimal(5,2) DEFAULT 0.00 COMMENT 'Total completion percentage',
  `mandatoryCompletionPercentage` decimal(5,2) DEFAULT 0.00 COMMENT 'Mandatory items completion percentage',
  `statusStage` varchar(50) DEFAULT 'draft' COMMENT 'Current stage: draft, in_review, submitted, won, lost, archived',
  `statusStageOrder` int(11) DEFAULT 1 COMMENT 'Order of current stage',
  `lastStatusChangeDate` datetime DEFAULT NULL COMMENT 'Date of last status change',
  `lastStatusChangedBy` int(11) DEFAULT NULL COMMENT 'User who changed status last'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_activities`
--

CREATE TABLE `tija_proposal_activities` (
  `proposalActivityID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalID` int(11) NOT NULL,
  `activityTypeID` int(11) NOT NULL,
  `activityDate` date NOT NULL,
  `activityTime` time NOT NULL,
  `activityDescription` text NOT NULL,
  `activityOwnerID` int(11) NOT NULL,
  `activityStatusID` int(11) NOT NULL,
  `activityDeadline` datetime NOT NULL DEFAULT current_timestamp(),
  `activityNotes` text DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `ActivityName` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_attachments`
--

CREATE TABLE `tija_proposal_attachments` (
  `proposalAttachmentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalAttachmentName` varchar(255) NOT NULL,
  `proposalID` int(11) NOT NULL,
  `proposalAttachmentFile` text NOT NULL,
  `proposalAttachmentType` int(11) NOT NULL,
  `uploadByEmployeeID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklists`
--

CREATE TABLE `tija_proposal_checklists` (
  `proposalChecklistID` int(11) NOT NULL,
  `proposalChecklistName` varchar(256) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalID` int(11) NOT NULL,
  `proposalChecklistStatusID` int(11) NOT NULL,
  `proposalChecklistDeadlineDate` date NOT NULL,
  `proposalChecklistDescription` text NOT NULL,
  `assignedEmployeeID` int(11) NOT NULL,
  `assigneeID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_items`
--

CREATE TABLE `tija_proposal_checklist_items` (
  `proposalChecklistItemID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalChecklistItemName` varchar(255) NOT NULL,
  `proposalChecklistItemDescription` text NOT NULL,
  `proposalChecklistItemCategoryID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `isMandatory` enum('Y','N') DEFAULT 'N' COMMENT 'Is this a mandatory checklist item'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_proposal_checklist_items`
--

INSERT INTO `tija_proposal_checklist_items` (`proposalChecklistItemID`, `DateAdded`, `proposalChecklistItemName`, `proposalChecklistItemDescription`, `proposalChecklistItemCategoryID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `isMandatory`) VALUES
(1, '2025-05-17 17:05:37', 'Statutory Documentation', 'Statutory documentation, i.e. tax returns, KRA certificate', 1, '2025-05-17 17:05:37', 37, 'N', 'N', 'N'),
(2, '2025-05-17 17:32:18', 'Team Résumés ( Curriculum Vitae -CV)', 'Team R&eacute;sum&eacute;s&nbsp; documents', 1, '2025-05-17 17:32:18', 37, 'N', 'N', 'N'),
(3, '2025-05-17 17:32:47', 'Company Recommendations', 'Company Recommendations', 1, '2025-05-17 17:32:47', 37, 'N', 'N', 'N'),
(4, '2025-05-17 17:51:58', 'Financials', 'Bank Statements', 1, '2025-05-17 17:51:58', 37, 'N', 'N', 'N'),
(5, '2025-05-17 17:53:55', 'Technical Proposal', 'Technical proposal documents', 2, '2025-05-17 17:53:54', 37, 'N', 'N', 'N'),
(6, '2025-05-17 17:54:21', 'Commercial Proposal', 'Commercial Proposal Document', 2, '2025-05-17 17:54:21', 37, 'N', 'N', 'N'),
(7, '2025-05-17 18:00:27', 'Project Plan', 'Project Plan Document', 2, '2025-05-17 18:00:27', 37, 'N', 'N', 'N'),
(8, '2025-05-17 18:01:01', 'Disaster Recovery Plan', 'Disaster recovery Plan document', 2, '2025-05-17 18:01:01', 37, 'N', 'N', 'N'),
(9, '2025-06-03 18:56:37', 'Terms of Refference', 'This is the TOR the proposal will be based on', 1, '2025-06-03 18:56:37', 37, 'N', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_item_assignment`
--

CREATE TABLE `tija_proposal_checklist_item_assignment` (
  `proposalChecklistItemAssignmentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalID` int(11) NOT NULL,
  `proposalChecklistID` int(11) NOT NULL,
  `proposalChecklistItemCategoryID` int(11) NOT NULL,
  `proposalChecklistItemID` int(11) NOT NULL,
  `proposalChecklistItemAssignmentDueDate` date NOT NULL,
  `proposalChecklistItemAssignmentDescription` text NOT NULL,
  `proposalChecklistAssignmentDocument` varchar(256) DEFAULT NULL,
  `proposalChecklistTemplate` varchar(256) DEFAULT NULL,
  `proposalChecklistItemAssignmentStatusID` int(11) NOT NULL,
  `checklistItemAssignedEmployeeID` int(11) NOT NULL,
  `checklistTemplate` varchar(255) DEFAULT NULL,
  `proposalChecklistAssignorID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `isMandatory` enum('Y','N') DEFAULT 'N' COMMENT 'Is this assignment mandatory',
  `completionPercentage` decimal(5,2) DEFAULT 0.00 COMMENT 'Assignment completion percentage',
  `submittedDate` datetime DEFAULT NULL COMMENT 'When assignment was submitted',
  `approvedDate` datetime DEFAULT NULL COMMENT 'When assignment was approved'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_proposal_checklist_item_assignment`
--

INSERT INTO `tija_proposal_checklist_item_assignment` (`proposalChecklistItemAssignmentID`, `DateAdded`, `proposalID`, `proposalChecklistID`, `proposalChecklistItemCategoryID`, `proposalChecklistItemID`, `proposalChecklistItemAssignmentDueDate`, `proposalChecklistItemAssignmentDescription`, `proposalChecklistAssignmentDocument`, `proposalChecklistTemplate`, `proposalChecklistItemAssignmentStatusID`, `checklistItemAssignedEmployeeID`, `checklistTemplate`, `proposalChecklistAssignorID`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `isMandatory`, `completionPercentage`, `submittedDate`, `approvedDate`) VALUES
(1, '2025-07-19 19:06:33', 3, 2, 1, 2, '2025-07-22', 'Please provide 5 resumes for the team members', 'checklist/1752941193_file-sample_1MB__2_.docx', 'checklist/1752941193_file_example_XLSX_5000.xlsx', 1, 4, NULL, 4, 1, 1, '2025-07-19 19:06:33', 4, 'N', 'N', 'N', 0.00, NULL, NULL),
(2, '2025-07-19 20:37:57', 3, 2, 1, 3, '2025-07-22', 'Please provide 5 company recommendations', NULL, 'checklist/1752946677_file_example_XLSX_5000.xlsx', 1, 4, NULL, 4, 1, 1, '2025-07-19 20:42:00', 4, 'N', 'N', 'N', 0.00, NULL, NULL),
(3, '2025-07-23 04:09:35', 6, 5, 1, 2, '2025-07-24', 'resume', NULL, NULL, 1, 3, NULL, 3, 1, 1, '2025-07-23 11:09:35', 3, 'N', 'N', 'N', 0.00, NULL, NULL),
(4, '2025-07-23 04:15:16', 6, 5, 2, 5, '2025-07-23', 'First review of the proposal', NULL, NULL, 1, 5, NULL, 3, 1, 1, '2025-07-23 11:17:00', 3, 'N', 'N', 'N', 0.00, NULL, NULL),
(5, '2025-07-23 04:19:11', 6, 5, 2, 7, '2025-07-23', 'ccc', NULL, NULL, 1, 6, NULL, 3, 1, 1, '2025-07-23 11:19:11', 3, 'N', 'N', 'N', 0.00, NULL, NULL),
(6, '2025-08-26 03:49:55', 9, 6, 1, 3, '2025-09-01', 'DAGFDSFSTNHFFHJNH', NULL, 'checklist/1756194595_tax_com_requirements.docx', 1, 27, NULL, 4, 1, 1, '2025-08-26 10:49:55', 4, 'N', 'N', 'N', 0.00, NULL, NULL),
(7, '2025-09-02 15:25:16', 9, 6, 1, 3, '2025-09-01', 'fgfdagdf fsd gafdg', NULL, 'checklist/1756815916_Tija_Feature_List.xlsx', 1, 37, NULL, 4, 1, 1, '2025-09-02 15:25:16', 4, 'N', 'N', 'N', 0.00, NULL, NULL),
(8, '2025-09-23 14:51:30', 11, 8, 1, 2, '2025-09-25', 'wde4thjuij', NULL, NULL, 1, 37, NULL, 4, 1, 1, '2025-09-23 14:51:30', 4, 'N', 'N', 'N', 0.00, NULL, NULL),
(9, '2025-09-27 16:02:23', 11, 8, 1, 9, '2025-09-25', 'Please check that the terms of reffereces ref;ect the project requirements', NULL, 'checklist/1758978143_1758053731358-request-for-expression-of-interest.pdf', 1, 39, NULL, 4, 1, 1, '2025-09-27 16:02:22', 4, 'N', 'N', 'N', 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_item_assignment_submissions`
--

CREATE TABLE `tija_proposal_checklist_item_assignment_submissions` (
  `proposalChecklistItemAssignmentSubmissionID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalChecklistItemAssignmentID` int(11) NOT NULL,
  `proposalChecklistItemID` int(11) DEFAULT NULL,
  `checklistItemAssignedEmployeeID` int(11) NOT NULL,
  `proposalChecklistItemAssignmentStatusID` int(11) NOT NULL,
  `proposalChecklistItemUploadfiles` text NOT NULL,
  `proposalChecklistItemAssignmentSubmissionDescription` text NOT NULL,
  `proposalChecklist` varchar(56) DEFAULT NULL,
  `proposalChecklistItemAssignmentSubmissionDate` date NOT NULL,
  `proposalChecklistItemAssignmentSubmissionStatusID` int(11) NOT NULL,
  `createdByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_item_categories`
--

CREATE TABLE `tija_proposal_checklist_item_categories` (
  `proposalChecklistItemCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalChecklistItemCategoryName` varchar(255) NOT NULL,
  `proposalChecklistItemCategoryDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_proposal_checklist_item_categories`
--

INSERT INTO `tija_proposal_checklist_item_categories` (`proposalChecklistItemCategoryID`, `DateAdded`, `proposalChecklistItemCategoryName`, `proposalChecklistItemCategoryDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-16 19:15:44', 'Documents Requirement', 'Document required for  submission', '2025-05-16 19:15:44', 37, 'N', 'N'),
(2, '2025-05-16 20:04:53', 'Proposal Content', 'Proposal section contents', '2025-05-16 20:04:53', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_item_submissions`
--

CREATE TABLE `tija_proposal_checklist_item_submissions` (
  `submissionID` int(10) UNSIGNED NOT NULL,
  `proposalChecklistItemAssignmentID` int(11) NOT NULL COMMENT 'FK to tija_proposal_checklist_item_assignment',
  `submittedBy` int(11) NOT NULL COMMENT 'FK to people - who submitted',
  `submissionDate` datetime DEFAULT current_timestamp() COMMENT 'When submitted',
  `submissionStatus` enum('draft','submitted','approved','rejected','revision_requested') DEFAULT 'submitted',
  `submissionNotes` text DEFAULT NULL COMMENT 'Submission notes or comments',
  `reviewedBy` int(11) DEFAULT NULL COMMENT 'FK to people - who reviewed',
  `reviewedDate` datetime DEFAULT NULL COMMENT 'When reviewed',
  `reviewNotes` text DEFAULT NULL COMMENT 'Review comments',
  `submissionFiles` text DEFAULT NULL COMMENT 'JSON array of submitted file paths',
  `orgDataID` int(11) DEFAULT NULL,
  `entityID` int(11) DEFAULT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Submissions for proposal checklist item assignments';

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_status`
--

CREATE TABLE `tija_proposal_checklist_status` (
  `proposalChecklistStatusID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalChecklistStatusName` varchar(255) NOT NULL,
  `proposalChecklistStatusDescription` text NOT NULL,
  `proposalChecklistStatusType` varchar(120) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_proposal_checklist_status`
--

INSERT INTO `tija_proposal_checklist_status` (`proposalChecklistStatusID`, `DateAdded`, `proposalChecklistStatusName`, `proposalChecklistStatusDescription`, `proposalChecklistStatusType`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-16 12:46:25', 'Assigned', 'Assigned checklist item', 'checkListItem', 1, 1, '2025-05-16 12:46:25', 37, 'N', 'N'),
(2, '2025-05-16 17:40:41', 'Submited', 'Assignee Has submited the checklist item for approval&nbsp;', 'checkListItem', 1, 1, '2025-05-16 17:40:41', 37, 'N', 'N'),
(3, '2025-05-16 17:42:11', 'Completed', 'The proposal owner has approved and completed the checklist item', 'checkListItem', 1, 1, '2025-05-16 17:42:11', 37, 'N', 'N'),
(4, '2025-05-16 18:40:34', 'Draft', 'Checklist created but none of the items completed or submited', 'checkList', 1, 1, '2025-05-16 18:40:34', 37, 'N', 'N'),
(5, '2025-05-16 18:41:08', 'In Progress', 'Checklist with part of the items submited and closed', 'checkList', 1, 1, '2025-05-16 18:41:08', 37, 'N', 'N'),
(6, '2025-05-16 18:42:57', 'Completed', 'Checklist with all the items submitte,d but the checklist is not approved', 'checkList', 1, 1, '2025-05-16 18:42:57', 37, 'N', 'N'),
(7, '2025-05-16 18:44:15', 'Closed', 'A checklist with all items completed and approved/Closed', 'checkList', 1, 1, '2025-05-16 18:44:15', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_statuses`
--

CREATE TABLE `tija_proposal_statuses` (
  `proposalStatusID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalStatusName` varchar(255) NOT NULL,
  `proposalStatusDescription` text NOT NULL,
  `proposalStatusCategoryID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_proposal_statuses`
--

INSERT INTO `tija_proposal_statuses` (`proposalStatusID`, `DateAdded`, `proposalStatusName`, `proposalStatusDescription`, `proposalStatusCategoryID`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-14 10:30:45', 'Draft', 'Draft Status', 1, 1, 1, '2025-05-14 10:30:45', 37, 'N', 'N'),
(2, '2025-05-14 11:26:41', 'Review', 'Review Status', 1, 1, 1, '2025-05-14 11:26:41', 37, 'N', 'N'),
(3, '2025-05-14 11:27:40', 'Approved', 'Approved', 1, 1, 1, '2025-05-14 11:27:40', 37, 'N', 'N'),
(4, '2025-05-14 11:27:57', 'Sent', 'Sent Status', 1, 1, 1, '2025-05-14 11:27:57', 37, 'N', 'N'),
(5, '2025-05-14 11:28:19', 'Accepted', 'Proposal Accepted', 2, 1, 1, '2025-05-14 11:28:19', 37, 'N', 'N'),
(6, '2025-05-14 11:28:37', 'Rejected', 'Lost and rejected proposa;', 3, 1, 1, '2025-05-14 11:28:37', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_status_categories`
--

CREATE TABLE `tija_proposal_status_categories` (
  `proposalStatusCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `proposalStatusCategoryName` varchar(255) NOT NULL,
  `proposalStatusCategoryDescription` text NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `LastUpdateByID` int(11) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_proposal_status_categories`
--

INSERT INTO `tija_proposal_status_categories` (`proposalStatusCategoryID`, `DateAdded`, `proposalStatusCategoryName`, `proposalStatusCategoryDescription`, `orgDataID`, `entityID`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-13 18:54:35', 'Sales in progress', 'Sales in progress', 1, 1, 37, '2025-05-13 18:54:35', 'N', 'N'),
(2, '2025-05-13 20:39:24', 'Won statuses', 'Won statuses', 1, 1, 37, '2025-05-13 20:39:24', 'N', 'N'),
(3, '2025-05-13 20:40:03', 'Lost Statuses', 'Lost Statuses', 1, 1, 37, '2025-05-13 20:40:03', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_status_stages`
--

CREATE TABLE `tija_proposal_status_stages` (
  `stageID` int(10) UNSIGNED NOT NULL,
  `stageCode` varchar(50) NOT NULL COMMENT 'draft, in_review, submitted, won, lost, archived',
  `stageName` varchar(100) NOT NULL COMMENT 'Display name',
  `stageDescription` text DEFAULT NULL COMMENT 'Stage description',
  `stageOrder` int(11) NOT NULL COMMENT 'Order for display',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `requiresApproval` enum('Y','N') DEFAULT 'N' COMMENT 'Requires approval to move to this stage',
  `canEdit` enum('Y','N') DEFAULT 'Y' COMMENT 'Can edit proposal in this stage',
  `colorCode` varchar(20) DEFAULT '#007bff' COMMENT 'Color for UI display',
  `iconClass` varchar(50) DEFAULT 'ri-file-line' COMMENT 'Icon class',
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proposal status stages reference table';

--
-- Dumping data for table `tija_proposal_status_stages`
--

INSERT INTO `tija_proposal_status_stages` (`stageID`, `stageCode`, `stageName`, `stageDescription`, `stageOrder`, `isActive`, `requiresApproval`, `canEdit`, `colorCode`, `iconClass`, `DateAdded`, `LastUpdate`) VALUES
(1, 'draft', 'Draft', 'Proposal is being prepared and edited', 1, 'Y', 'N', 'Y', '#6c757d', 'ri-edit-box-line', '2025-11-16 19:58:53', '2025-11-16 16:58:53'),
(2, 'in_review', 'In Review', 'Proposal is under internal review', 2, 'Y', 'N', 'Y', '#0dcaf0', 'ri-eye-line', '2025-11-16 19:58:53', '2025-11-16 16:58:53'),
(3, 'submitted', 'Submitted', 'Proposal has been submitted to client', 3, 'Y', 'Y', 'N', '#0d6efd', 'ri-send-plane-line', '2025-11-16 19:58:53', '2025-11-16 16:58:53'),
(4, 'won', 'Won', 'Proposal was accepted by client', 4, 'Y', 'Y', 'N', '#198754', 'ri-checkbox-circle-line', '2025-11-16 19:58:53', '2025-11-16 16:58:53'),
(5, 'lost', 'Lost', 'Proposal was rejected or lost', 5, 'Y', 'Y', 'N', '#dc3545', 'ri-close-circle-line', '2025-11-16 19:58:53', '2025-11-16 16:58:53'),
(6, 'archived', 'Archived', 'Proposal has been archived', 6, 'Y', 'N', 'N', '#6c757d', 'ri-archive-line', '2025-11-16 19:58:53', '2025-11-16 16:58:53');

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_tasks`
--

CREATE TABLE `tija_proposal_tasks` (
  `proposalTaskID` int(10) UNSIGNED NOT NULL,
  `proposalID` int(11) NOT NULL COMMENT 'FK to tija_proposals',
  `taskName` varchar(255) NOT NULL COMMENT 'Task name',
  `taskDescription` text DEFAULT NULL COMMENT 'Task description',
  `assignedTo` int(11) NOT NULL COMMENT 'FK to people - assigned user',
  `assignedBy` int(11) NOT NULL COMMENT 'FK to people - who assigned',
  `dueDate` datetime NOT NULL COMMENT 'Task due date',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `completionPercentage` decimal(5,2) DEFAULT 0.00 COMMENT 'Task completion percentage',
  `isMandatory` enum('Y','N') DEFAULT 'N' COMMENT 'Is this a mandatory task',
  `completedDate` datetime DEFAULT NULL COMMENT 'Date when task was completed',
  `completedBy` int(11) DEFAULT NULL COMMENT 'FK to people - who completed',
  `notificationSent` enum('Y','N') DEFAULT 'N' COMMENT 'Notification sent flag',
  `notificationSentDate` datetime DEFAULT NULL COMMENT 'When notification was sent',
  `orgDataID` int(11) DEFAULT NULL,
  `entityID` int(11) DEFAULT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proposal tasks for tracking individual tasks within proposals';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_activity_instances`
--

CREATE TABLE `tija_recurring_activity_instances` (
  `recurringInstanceID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `activityID` int(11) NOT NULL,
  `activityInstanceDate` date NOT NULL,
  `activityinstanceStartTime` time NOT NULL,
  `activityInstanceDurationEndTime` time DEFAULT NULL,
  `instanceCount` int(11) DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `activityStatusID` int(11) NOT NULL DEFAULT 1,
  `activityInstanceOwnerID` int(11) NOT NULL,
  `completed` enum('Y','N') NOT NULL DEFAULT 'N',
  `dateCompleted` timestamp NULL DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_billing_cycles`
--

CREATE TABLE `tija_recurring_project_billing_cycles` (
  `billingCycleID` int(10) UNSIGNED NOT NULL,
  `projectID` int(11) NOT NULL,
  `cycleNumber` int(11) NOT NULL COMMENT '1, 2, 3...',
  `cycleStartDate` date NOT NULL,
  `cycleEndDate` date NOT NULL,
  `billingDate` date NOT NULL COMMENT 'when invoice should be generated',
  `dueDate` date NOT NULL COMMENT 'payment due date',
  `status` enum('upcoming','active','billing_due','invoiced','paid','overdue','cancelled') DEFAULT 'upcoming',
  `invoiceDraftID` int(11) DEFAULT NULL COMMENT 'FK to tija_invoices when draft created',
  `invoiceID` int(11) DEFAULT NULL COMMENT 'FK to tija_invoices when finalized',
  `amount` decimal(15,2) NOT NULL,
  `hoursLogged` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Billing cycles for recurring projects';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_cycle_config`
--

CREATE TABLE `tija_recurring_project_plan_cycle_config` (
  `configID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL COMMENT 'FK to tija_projects',
  `billingCycleID` int(11) NOT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `templatePhaseID` int(11) DEFAULT NULL COMMENT 'FK to tija_recurring_project_plan_templates (if phase-specific)',
  `templateTaskID` int(11) DEFAULT NULL COMMENT 'FK to tija_recurring_project_plan_task_templates (if task-specific)',
  `isEnabled` enum('Y','N') DEFAULT 'Y' COMMENT 'Enable/disable this phase/task for this cycle',
  `customStartDate` date DEFAULT NULL COMMENT 'Override start date for this cycle',
  `customEndDate` date DEFAULT NULL COMMENT 'Override end date for this cycle',
  `customDuration` int(11) DEFAULT NULL COMMENT 'Override duration in days',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuration for cycle-specific plan customization';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_instances`
--

CREATE TABLE `tija_recurring_project_plan_instances` (
  `planInstanceID` int(10) UNSIGNED NOT NULL,
  `billingCycleID` int(10) UNSIGNED NOT NULL,
  `projectID` int(11) NOT NULL,
  `phaseJSON` text DEFAULT NULL COMMENT 'customized phases/tasks for this cycle',
  `isCustomized` enum('Y','N') DEFAULT 'N',
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customized plan instances for recurring project billing cycles';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_task_templates`
--

CREATE TABLE `tija_recurring_project_plan_task_templates` (
  `templateTaskID` int(11) NOT NULL,
  `templatePhaseID` int(11) NOT NULL COMMENT 'FK to tija_recurring_project_plan_templates',
  `originalTaskID` int(11) DEFAULT NULL COMMENT 'FK to original task in tija_project_tasks',
  `taskName` varchar(256) NOT NULL,
  `taskCode` varchar(30) NOT NULL,
  `taskDescription` text DEFAULT NULL,
  `relativeStartDay` int(11) DEFAULT 0 COMMENT 'Days from phase start',
  `relativeEndDay` int(11) DEFAULT 0 COMMENT 'Days from phase start',
  `hoursAllocated` decimal(10,2) DEFAULT NULL,
  `taskWeighting` decimal(10,2) DEFAULT NULL,
  `assigneeID` int(11) DEFAULT NULL COMMENT 'FK to people table',
  `applyToAllCycles` enum('Y','N') DEFAULT 'Y' COMMENT 'Apply to all cycles or specific cycles',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores task templates for recurring project phases';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_templates`
--

CREATE TABLE `tija_recurring_project_plan_templates` (
  `templatePhaseID` int(11) NOT NULL,
  `projectID` int(11) NOT NULL COMMENT 'FK to tija_projects',
  `originalPhaseID` int(11) DEFAULT NULL COMMENT 'FK to original phase in tija_project_phases',
  `phaseName` varchar(200) NOT NULL,
  `phaseDescription` text DEFAULT NULL,
  `phaseOrder` int(11) NOT NULL DEFAULT 0 COMMENT 'Order of phase in template',
  `phaseDuration` int(11) DEFAULT NULL COMMENT 'Duration in days',
  `phaseWorkHrs` decimal(10,2) DEFAULT NULL,
  `phaseWeighting` decimal(10,2) DEFAULT NULL,
  `billingMilestone` enum('Y','N') DEFAULT 'N',
  `relativeStartDay` int(11) DEFAULT 0 COMMENT 'Days from cycle start (0 = start of cycle)',
  `relativeEndDay` int(11) DEFAULT 0 COMMENT 'Days from cycle start',
  `applyToAllCycles` enum('Y','N') DEFAULT 'Y' COMMENT 'Apply to all cycles or specific cycles',
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores phase templates for recurring projects';

--
-- Dumping data for table `tija_recurring_project_plan_templates`
--

INSERT INTO `tija_recurring_project_plan_templates` (`templatePhaseID`, `projectID`, `originalPhaseID`, `phaseName`, `phaseDescription`, `phaseOrder`, `phaseDuration`, `phaseWorkHrs`, `phaseWeighting`, `billingMilestone`, `relativeStartDay`, `relativeEndDay`, `applyToAllCycles`, `DateAdded`, `LastUpdate`, `Suspended`) VALUES
(1, 78, 93, 'Requirements', 'Requirements gathering and analysis', 0, 3, NULL, NULL, 'N', 0, 2, 'Y', '2025-11-18 13:04:15', NULL, 'N'),
(2, 78, 94, 'Design', 'System and UI/UX design', 0, 3, NULL, NULL, 'N', 0, 2, 'Y', '2025-11-18 13:04:15', NULL, 'N'),
(3, 78, 95, 'Implementation', 'Development and coding', 0, 3, NULL, NULL, 'N', 0, 2, 'Y', '2025-11-18 13:04:15', NULL, 'N'),
(4, 78, 96, 'Testing', 'System testing and QA', 0, 3, NULL, NULL, 'N', 0, 2, 'Y', '2025-11-18 13:04:15', NULL, 'N'),
(5, 78, 97, 'Maintenance', 'Deployment and ongoing maintenance', 0, 4, NULL, NULL, 'N', 0, 3, 'Y', '2025-11-18 13:04:15', NULL, 'N'),
(11, 80, 108, 'Requirements', 'Requirements gathering and analysis', 0, 73, NULL, NULL, 'N', 0, 72, 'Y', '2025-11-18 13:14:24', NULL, 'N'),
(12, 80, 109, 'Design', 'System and UI/UX design', 0, 73, NULL, NULL, 'N', 0, 72, 'Y', '2025-11-18 13:14:24', NULL, 'N'),
(13, 80, 110, 'Implementation', 'Development and coding', 0, 73, NULL, NULL, 'N', 0, 72, 'Y', '2025-11-18 13:14:24', NULL, 'N'),
(14, 80, 111, 'Testing', 'System testing and QA', 0, 73, NULL, NULL, 'N', 0, 72, 'Y', '2025-11-18 13:14:24', NULL, 'N'),
(15, 80, 112, 'Maintenance', 'Deployment and ongoing maintenance', 0, 77, NULL, NULL, 'N', 0, 76, 'Y', '2025-11-18 13:14:24', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_team_assignments`
--

CREATE TABLE `tija_recurring_project_team_assignments` (
  `teamAssignmentID` int(10) UNSIGNED NOT NULL,
  `billingCycleID` int(10) UNSIGNED NOT NULL,
  `projectID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL COMMENT 'owner, manager, member',
  `hoursAllocated` decimal(10,2) DEFAULT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Team assignments for recurring project billing cycles';

-- --------------------------------------------------------

--
-- Table structure for table `tija_reporting_hierarchy_cache`
--

CREATE TABLE `tija_reporting_hierarchy_cache` (
  `cacheID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `ancestorID` int(11) NOT NULL COMMENT 'All people in reporting chain',
  `pathLength` int(11) NOT NULL COMMENT 'Levels between employee and ancestor',
  `hierarchyPath` text DEFAULT NULL COMMENT 'Full path as JSON',
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `lastCalculated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cached reporting hierarchy for quick lookups';

-- --------------------------------------------------------

--
-- Table structure for table `tija_reporting_matrix`
--

CREATE TABLE `tija_reporting_matrix` (
  `matrixID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(11) NOT NULL,
  `functionalSupervisorID` int(11) DEFAULT NULL COMMENT 'Functional line manager',
  `projectSupervisorID` int(11) DEFAULT NULL COMMENT 'Project/program manager',
  `administrativeSupervisorID` int(11) DEFAULT NULL COMMENT 'Administrative manager',
  `primarySupervisorID` int(11) NOT NULL COMMENT 'Primary reporting line',
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matrix reporting structure support';

-- --------------------------------------------------------

--
-- Table structure for table `tija_reporting_relationships`
--

CREATE TABLE `tija_reporting_relationships` (
  `relationshipID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `employeeID` int(11) NOT NULL COMMENT 'Employee who reports',
  `supervisorID` int(11) NOT NULL COMMENT 'Employee being reported to',
  `roleID` int(11) DEFAULT NULL COMMENT 'Role context for this relationship',
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `relationshipType` enum('Direct','Dotted','Matrix','Functional','Administrative') DEFAULT 'Direct',
  `relationshipStrength` int(11) DEFAULT 100 COMMENT 'Percentage (100=primary, <100=secondary)',
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `reportingFrequency` enum('Daily','Weekly','Biweekly','Monthly','Quarterly','Adhoc') DEFAULT 'Weekly',
  `canDelegate` enum('Y','N') DEFAULT 'N',
  `canSubstitute` enum('Y','N') DEFAULT 'N',
  `notes` text DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `approvedBy` int(11) DEFAULT NULL,
  `approvedDate` datetime DEFAULT NULL,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee reporting relationships';

--
-- Dumping data for table `tija_reporting_relationships`
--

INSERT INTO `tija_reporting_relationships` (`relationshipID`, `DateAdded`, `employeeID`, `supervisorID`, `roleID`, `orgDataID`, `entityID`, `relationshipType`, `relationshipStrength`, `effectiveDate`, `endDate`, `isCurrent`, `reportingFrequency`, `canDelegate`, `canSubstitute`, `notes`, `createdBy`, `approvedBy`, `approvedDate`, `LastUpdate`, `Suspended`) VALUES
(1, '2025-11-24 13:32:56', 7, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:32:56', 'N'),
(2, '2025-11-24 13:32:56', 13, 4, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:32:56', 'N'),
(3, '2025-11-24 13:32:56', 5, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:32:56', 'N'),
(4, '2025-11-24 13:32:56', 11, 4, NULL, 1, 1, 'Direct', 0, '2025-04-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:32:56', 'N'),
(5, '2025-11-24 13:47:03', 15, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:03', 'N'),
(6, '2025-11-24 13:47:03', 3, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:03', 'N'),
(7, '2025-11-24 13:47:03', 10, 6, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:03', 'N'),
(8, '2025-11-24 13:47:16', 8, 5, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(9, '2025-11-24 13:47:16', 12, 4, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(10, '2025-11-24 13:47:16', 4, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(11, '2025-11-24 13:47:16', 9, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(12, '2025-11-24 13:47:16', 19, 4, NULL, 1, 1, 'Direct', 0, '2025-08-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(13, '2025-11-24 13:47:16', 14, 4, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(14, '2025-11-24 13:47:16', 18, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(15, '2025-11-24 13:47:16', 17, 4, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(16, '2025-11-24 13:47:16', 6, 2, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(17, '2025-11-24 13:47:16', 20, 3, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(18, '2025-11-24 13:47:16', 22, 4, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(19, '2025-11-24 13:47:16', 16, 4, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 10:47:16', 'N'),
(20, '2025-11-24 17:26:53', 24, 22, NULL, 1, 1, 'Direct', 100, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 14:27:56', 'N'),
(21, '2025-11-24 17:26:53', 23, 22, NULL, 1, 1, 'Direct', 0, '2025-01-01', NULL, 'Y', 'Weekly', 'N', 'N', NULL, NULL, NULL, NULL, '2025-11-24 14:26:53', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_roles`
--

CREATE TABLE `tija_roles` (
  `roleID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `roleName` varchar(255) NOT NULL,
  `roleCode` varchar(50) DEFAULT NULL,
  `roleDescription` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) DEFAULT NULL,
  `departmentID` int(11) DEFAULT NULL,
  `unitID` int(11) DEFAULT NULL,
  `parentRoleID` int(11) DEFAULT NULL COMMENT 'Reports to this role',
  `jobTitleID` int(11) DEFAULT NULL,
  `roleLevel` int(11) DEFAULT 0 COMMENT 'Hierarchy level (0=top)',
  `roleLevelID` int(11) NOT NULL,
  `roleType` enum('Executive','Management','Supervisory','Operational','Support') DEFAULT 'Operational',
  `roleTypeID` int(11) NOT NULL,
  `requiresApproval` enum('Y','N') DEFAULT 'N',
  `canApprove` enum('Y','N') DEFAULT 'N',
  `approvalLimit` decimal(15,2) DEFAULT NULL COMMENT 'Financial approval limit',
  `reportsCount` int(11) DEFAULT 0 COMMENT 'Number of direct reports',
  `iconClass` varchar(100) DEFAULT NULL,
  `colorCode` varchar(20) DEFAULT NULL,
  `isActive` enum('Y','N') NOT NULL DEFAULT 'Y',
  `LastUpdate` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organizational roles and positions hierarchy';

--
-- Dumping data for table `tija_roles`
--

INSERT INTO `tija_roles` (`roleID`, `DateAdded`, `roleName`, `roleCode`, `roleDescription`, `orgDataID`, `entityID`, `departmentID`, `unitID`, `parentRoleID`, `jobTitleID`, `roleLevel`, `roleLevelID`, `roleType`, `roleTypeID`, `requiresApproval`, `canApprove`, `approvalLimit`, `reportsCount`, `iconClass`, `colorCode`, `isActive`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(3, '2025-11-01 16:59:36', 'CEO(Chief Executive Officer)', 'CEO', 'Chief Executive Officer', 1, NULL, 1, 1, NULL, 14, 1, 2, '', 4, 'N', 'Y', NULL, NULL, 'fas fa-crown', '#0d6efd', 'Y', '2025-11-14 14:16:00', 4, 'N', 'N'),
(4, '2025-11-01 17:18:56', 'Partner', 'PAT', 'Service Line Leader, Department Head and has the financial responsibility for the Department in the budget, expenditure and Revenue for the business line', 1, NULL, 1, 1, 3, 55, 2, 3, 'Executive', 1, 'N', 'Y', NULL, 1, 'fas fa-crown', '#fd0d99', 'Y', '2025-11-14 14:16:00', 4, 'N', 'N'),
(5, '2025-11-01 17:20:30', 'Manager', 'MAN', 'Service Line Managers who are responsible for specific product lines and revenue streams. They report to the partnership and are responsible for their team', 1, 1, NULL, NULL, 4, 54, 4, 5, 'Management', 2, 'Y', 'Y', NULL, 2, 'fas fa-star', '#0d6efd', 'Y', '2025-11-14 14:16:00', 4, 'N', 'N'),
(6, '2025-11-01 17:25:21', 'Assistant Manager', 'As. MAN', 'Assistant product line managers who serve as team leads for specific revenue lines or product teams.', 1, 1, NULL, NULL, 5, 57, 5, 6, 'Supervisory', 3, 'Y', 'Y', NULL, 4, 'fas fa-users', '#0d6efd', 'Y', '2025-11-14 14:16:00', 4, 'N', 'N'),
(7, '2025-11-14 08:06:39', 'Senior Associate', 'SR_ASST', 'Operational staff and technical operatives with direct product line technical and Practical knowledge', 1, 1, NULL, NULL, 6, 58, 6, 7, 'Operational', 4, 'Y', 'N', NULL, 2, 'fas fa-user-friends', '#0d6efd', 'Y', '2025-11-14 14:16:00', 4, 'N', 'N'),
(8, '2025-11-14 08:07:55', 'Associate', 'ASST', 'Technical and Operational staff with administrative roles', 1, 1, NULL, NULL, 7, 59, 7, 8, 'Operational', 4, 'Y', 'N', NULL, 3, 'fas fa-user', '#0d6efd', 'Y', '2025-11-14 14:16:00', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_role_levels`
--

CREATE TABLE `tija_role_levels` (
  `roleLevelID` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `levelNumber` int(11) NOT NULL COMMENT 'Numeric level (0-8, lower = higher authority)',
  `levelName` varchar(100) NOT NULL COMMENT 'Display name (e.g., Board/External, CEO/Executive)',
  `levelCode` varchar(20) DEFAULT NULL COMMENT 'Short code (e.g., BOARD, CEO, CSUITE)',
  `levelDescription` text DEFAULT NULL COMMENT 'Description of the role level',
  `displayOrder` int(11) DEFAULT 0 COMMENT 'Order for display in dropdowns',
  `isDefault` enum('Y','N') DEFAULT 'N' COMMENT 'Is this a default/system role level',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Is this role level active',
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role levels for organizational hierarchy';

--
-- Dumping data for table `tija_role_levels`
--

INSERT INTO `tija_role_levels` (`roleLevelID`, `DateAdded`, `levelNumber`, `levelName`, `levelCode`, `levelDescription`, `displayOrder`, `isDefault`, `isActive`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-14 13:59:43', 0, 'Board/External', 'BOARD', 'Board Members, External Auditors', 0, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(2, '2025-11-14 13:59:43', 1, 'CEO/Executive', 'CEO', 'Chief Executive Officer', 1, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(3, '2025-11-14 13:59:43', 2, 'C-Suite', 'CSUITE', 'CFO, COO, CTO, CMO', 2, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(4, '2025-11-14 13:59:43', 3, 'Director', 'DIR', 'Director of Finance, IT Director', 3, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(5, '2025-11-14 13:59:43', 4, 'Manager', 'MGR', 'Department Manager, Project Manager', 4, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(6, '2025-11-14 13:59:43', 5, 'Supervisor', 'SUPV', 'Team Lead, Supervisor (Default)', 5, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(7, '2025-11-14 13:59:43', 6, 'Senior Staff', 'SRSTAFF', 'Senior Officer, Senior Consultant', 6, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(8, '2025-11-14 13:59:43', 7, 'Staff', 'STAFF', 'Officer, Consultant, Staff', 7, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N'),
(9, '2025-11-14 13:59:43', 8, 'Entry Level', 'ENTRY', 'Junior Officer, Trainee', 8, 'Y', 'Y', '2025-11-14 10:59:43', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_role_types`
--

CREATE TABLE `tija_role_types` (
  `roleTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `roleTypeTitle` varchar(255) NOT NULL,
  `roleTypeDescription` mediumtext NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_role_types`
--

INSERT INTO `tija_role_types` (`roleTypeID`, `DateAdded`, `roleTypeTitle`, `roleTypeDescription`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-21 13:39:08', 'Administrator', 'Administrator access allows individuals access to the backend of the application', '2024-06-21 13:39:08', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_salary_components`
--

CREATE TABLE `tija_salary_components` (
  `salaryComponentID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `entityID` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `componentCode` varchar(30) DEFAULT NULL,
  `salaryComponentTitle` varchar(255) NOT NULL,
  `salaryComponentDescription` mediumtext NOT NULL,
  `salaryComponentType` enum('earning','deduction','benefit') NOT NULL,
  `salaryComponentValueType` enum('fixed','percentage','formula') NOT NULL DEFAULT 'fixed',
  `defaultValue` decimal(15,2) DEFAULT 0.00,
  `calculationFormula` text DEFAULT NULL,
  `applyTo` enum('total_payable','cost_to_company') NOT NULL,
  `isStatutory` enum('Y','N') DEFAULT 'N',
  `isMandatory` enum('Y','N') DEFAULT 'N',
  `isVisible` enum('Y','N') DEFAULT 'Y',
  `isTaxable` enum('Y','N') DEFAULT 'Y',
  `isProrated` enum('Y','N') DEFAULT 'N',
  `affectsGross` enum('Y','N') DEFAULT 'Y',
  `affectsNet` enum('Y','N') DEFAULT 'Y',
  `minimumValue` decimal(15,2) DEFAULT NULL,
  `maximumValue` decimal(15,2) DEFAULT NULL,
  `effectiveDate` date DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `payrollFrequency` enum('all','monthly','bi-weekly','weekly') DEFAULT 'all',
  `eligibilityCriteria` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `sortOrder` int(11) DEFAULT 0,
  `salaryComponentCategoryID` int(11) NOT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_salary_components`
--

INSERT INTO `tija_salary_components` (`salaryComponentID`, `DateAdded`, `orgDataID`, `entityID`, `componentCode`, `salaryComponentTitle`, `salaryComponentDescription`, `salaryComponentType`, `salaryComponentValueType`, `defaultValue`, `calculationFormula`, `applyTo`, `isStatutory`, `isMandatory`, `isVisible`, `isTaxable`, `isProrated`, `affectsGross`, `affectsNet`, `minimumValue`, `maximumValue`, `effectiveDate`, `expiryDate`, `payrollFrequency`, `eligibilityCriteria`, `notes`, `sortOrder`, `salaryComponentCategoryID`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-28 18:18:59', 1, 1, 'BASICSALARY', 'Basic Salary', 'Remuneration or Salary before benefits and deductions', 'earning', 'fixed', NULL, NULL, '', 'N', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, NULL, 1, 4, '2024-06-28 18:18:59', 'N', 'N'),
(2, '2024-06-28 19:01:13', 1, 1, 'INCOMETAX', 'Income Tax', 'State-imposed revenue deduction as income tax pay as you earn tax', 'deduction', 'percentage', 10.00, NULL, '', 'Y', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, NULL, 2, 4, '2024-06-28 19:01:13', 'N', 'N'),
(3, '2024-06-28 19:03:43', 1, 1, 'CAR_ALL', 'Car Allowance', 'Car benefit/allowance due for car ownership', 'earning', 'percentage', NULL, NULL, '', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, NULL, 3, 4, '2024-06-28 19:03:43', 'N', 'N'),
(4, '2024-06-28 19:05:36', 1, 1, 'PENSION', 'Pension Fund', 'Pension/retirement savings for employees', 'deduction', 'percentage', NULL, NULL, '', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, NULL, 4, 4, '2024-06-28 19:05:36', 'N', 'N'),
(5, '2024-06-28 19:09:24', 1, 1, 'EPF_CONTRIBUTION', 'EPF (Employee Contribution)', 'Employee providence fund contribution (Contribution to providence fund by employee )', 'deduction', 'percentage', NULL, NULL, '', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, NULL, 5, 4, '2024-06-28 19:09:24', 'N', 'N'),
(6, '2025-10-18 17:13:22', 1, 1, 'HSEALL', 'Housing Allowance', 'House allowance', 'earning', 'percentage', 5.00, NULL, '', 'N', 'Y', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, 0, 3, 4, '2025-10-18 17:13:22', 'N', 'N'),
(8, '2025-10-18 17:35:02', 1, 1, 'TRAV_ALL', 'Travel Allowance', 'Travel allowance', 'earning', 'fixed', 50000.00, NULL, '', 'N', 'N', 'Y', 'Y', 'N', 'Y', 'Y', NULL, NULL, NULL, NULL, 'all', NULL, NULL, 0, 3, 4, '2025-10-18 17:35:02', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_salary_component_category`
--

CREATE TABLE `tija_salary_component_category` (
  `salaryComponentCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `entityID` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `categoryCode` varchar(20) DEFAULT NULL,
  `salaryComponentCategoryTitle` varchar(255) NOT NULL,
  `salaryComponentCategoryDescription` mediumtext NOT NULL,
  `categoryType` enum('earning','deduction','statutory','benefit','reimbursement') NOT NULL DEFAULT 'earning',
  `isSystemCategory` enum('Y','N') DEFAULT 'N',
  `sortOrder` int(11) DEFAULT 0,
  `LastUpdatedByID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_salary_component_category`
--

INSERT INTO `tija_salary_component_category` (`salaryComponentCategoryID`, `DateAdded`, `orgDataID`, `entityID`, `categoryCode`, `salaryComponentCategoryTitle`, `salaryComponentCategoryDescription`, `categoryType`, `isSystemCategory`, `sortOrder`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-28 18:18:59', 1, 1, 'SALARY', 'Salary', 'Remuneration to employee', 'earning', 'N', 0, 1, '2024-06-28 18:18:59', 'N', 'N'),
(2, '2024-06-28 19:01:13', 1, 1, 'TAX', 'Tax', 'State-imposed revenue deduction', 'earning', 'N', 0, 1, '2024-06-28 19:01:13', 'N', 'N'),
(3, '2024-06-28 19:03:43', 1, 1, 'ALLOWANCES', 'Allowances', 'Employee salary benefits in cash', 'earning', 'N', 0, 1, '2024-06-28 19:03:43', 'N', 'N'),
(4, '2024-06-28 19:05:36', 1, 1, 'PENSION', 'Pension', 'Retirement saving scheme', 'earning', 'N', 0, 1, '2024-06-28 19:05:36', 'N', 'N'),
(5, '2024-06-28 19:09:24', 1, 1, 'PROVIDENT_FUND', 'Provident Fund', 'Provident Fund', 'earning', 'N', 0, 1, '2024-06-28 19:09:24', 'N', 'N'),
(6, '2025-10-17 15:21:21', 1, 1, 'ALLOW', 'Allowances', 'Employee allowances (housing, transport, medical, etc)', 'earning', 'Y', 1, 0, '2025-10-17 15:21:21', 'N', 'Y'),
(7, '2025-10-17 15:21:21', 1, 1, 'BONUS', 'Bonuses', 'Performance bonuses and incentives', 'earning', 'Y', 2, 0, '2025-10-17 15:21:21', 'N', 'N'),
(8, '2025-10-17 15:21:21', 1, 1, 'COMMIS', 'Commissions', 'Sales commissions and variable pay', 'earning', 'Y', 3, 0, '2025-10-17 15:21:21', 'N', 'N'),
(9, '2025-10-17 15:21:21', 1, 1, 'OVERTM', 'Overtime', 'Overtime and extra hours pay', 'earning', 'Y', 4, 0, '2025-10-17 15:21:21', 'N', 'N'),
(10, '2025-10-17 15:21:21', 1, 1, 'REIMB', 'Reimbursements', 'Expense reimbursements', 'reimbursement', 'Y', 5, 0, '2025-10-17 15:21:21', 'N', 'N'),
(11, '2025-10-17 15:21:21', 1, 1, 'STATU', 'Statutory Deductions', 'PAYE, NHIF, NSSF, Housing Levy', 'statutory', 'Y', 6, 0, '2025-10-17 15:21:21', 'N', 'N'),
(12, '2025-10-17 15:21:21', 1, 1, 'LOAN', 'Loans', 'Loan deductions', 'deduction', 'Y', 7, 0, '2025-10-17 15:21:21', 'N', 'N'),
(13, '2025-10-17 15:21:21', 1, 1, 'DEDUC', 'Other Deductions', 'Miscellaneous deductions', 'deduction', 'Y', 8, 0, '2025-10-17 15:21:21', 'N', 'N'),
(14, '2025-10-17 15:21:21', 1, 1, 'BENEF', 'Benefits', 'Employee benefits and welfare', 'benefit', 'Y', 9, 0, '2025-10-17 15:21:21', 'N', 'N'),
(15, '2025-10-17 22:33:49', 1, 1, 'ALLOWANCE', 'Allowances & Benefits', 'Employee allowances and benefits', '', 'Y', 2, 0, '2025-10-17 22:33:49', 'N', 'N'),
(16, '2025-10-17 22:33:49', 1, 1, 'EARNING', 'Basic Earnings', 'Basic salary and earnings', 'earning', 'Y', 1, 0, '2025-10-17 22:33:49', 'N', 'N'),
(17, '2025-10-17 22:33:49', 1, 1, 'DEDUCTION', 'Deductions', 'Salary deductions', 'deduction', 'Y', 3, 0, '2025-10-17 22:33:49', 'N', 'N'),
(18, '2025-10-17 22:33:49', 1, 1, 'STATUTORY', 'Statutory Deductions', 'Government-mandated deductions (PAYE, NHIF, NSSF, etc.)', 'statutory', 'Y', 4, 0, '2025-10-17 22:33:49', 'N', 'N'),
(19, '2025-10-18 13:18:56', 1, 0, 'SALARY', 'Salary', 'Remuneration to employee', 'earning', 'N', 0, 4, '2025-10-18 13:18:56', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_salary_component_history`
--

CREATE TABLE `tija_salary_component_history` (
  `historyID` int(10) UNSIGNED NOT NULL,
  `DateAdded` timestamp NULL DEFAULT current_timestamp(),
  `salaryComponentID` int(10) UNSIGNED NOT NULL,
  `changeType` enum('created','updated','deleted','suspended','reactivated') NOT NULL,
  `fieldChanged` varchar(100) DEFAULT NULL COMMENT 'Which field was changed',
  `oldValue` text DEFAULT NULL COMMENT 'Previous value',
  `newValue` text DEFAULT NULL COMMENT 'New value',
  `changedBy` int(10) UNSIGNED NOT NULL,
  `changeReason` text DEFAULT NULL,
  `changeDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_activities`
--

CREATE TABLE `tija_sales_activities` (
  `salesActivityID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `activityTypeID` int(11) NOT NULL,
  `salesActivityDate` date NOT NULL,
  `activityTime` time NOT NULL,
  `activityDescription` text NOT NULL,
  `salesCaseID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `activityName` varchar(255) NOT NULL,
  `activityOwnerID` int(11) NOT NULL,
  `salesPersonID` int(11) NOT NULL,
  `activityCategory` enum('one_off','reccuring','duration') NOT NULL,
  `activityStatus` enum('open','inprogress','stalled','completed') NOT NULL DEFAULT 'open',
  `activityDeadline` date DEFAULT NULL,
  `activityStartDate` date DEFAULT NULL,
  `activityCloseDate` date DEFAULT NULL,
  `activityCloseStatus` enum('open','pending','stalled','closed') NOT NULL DEFAULT 'open',
  `ActivityNotes` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_cases`
--

CREATE TABLE `tija_sales_cases` (
  `salesCaseID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `salesCaseName` varchar(256) NOT NULL,
  `clientID` int(11) NOT NULL,
  `salesCaseContactID` int(11) NOT NULL,
  `orgDataID` int(11) DEFAULT NULL,
  `entityID` int(11) DEFAULT NULL,
  `businessUnitID` int(11) NOT NULL,
  `salesPersonID` int(11) NOT NULL,
  `saleStatusLevelID` int(11) NOT NULL,
  `saleStage` enum('business_development','opportunities','order','loss') NOT NULL DEFAULT 'business_development',
  `salesCaseEstimate` double(16,2) NOT NULL,
  `probability` int(11) NOT NULL DEFAULT 0,
  `expectedCloseDate` date NOT NULL,
  `leadSourceID` int(11) NOT NULL,
  `dateClosed` date DEFAULT NULL,
  `closeStatus` enum('open','won','lost') DEFAULT 'open',
  `projectID` int(11) DEFAULT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `closeDate` date DEFAULT NULL,
  `LastUpdatedByID` int(11) NOT NULL,
  `salesProgressID` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_documents`
--

CREATE TABLE `tija_sales_documents` (
  `documentID` int(10) UNSIGNED NOT NULL,
  `salesCaseID` int(11) NOT NULL COMMENT 'FK to tija_sales_cases',
  `proposalID` int(11) DEFAULT NULL COMMENT 'Optional FK to tija_proposals if document is proposal-related',
  `documentName` varchar(255) NOT NULL COMMENT 'Display name for the document',
  `fileName` varchar(255) NOT NULL COMMENT 'Stored filename',
  `fileOriginalName` varchar(255) NOT NULL COMMENT 'Original filename from upload',
  `fileURL` varchar(500) NOT NULL COMMENT 'Path to stored file',
  `fileType` varchar(50) DEFAULT NULL COMMENT 'File extension: pdf, docx, xlsx, etc.',
  `fileSize` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `fileMimeType` varchar(100) DEFAULT NULL COMMENT 'MIME type',
  `documentCategory` varchar(100) NOT NULL COMMENT 'Category: sales_agreement, tor, proposal, engagement_letter, confidentiality_agreement, expense_document, other',
  `documentType` varchar(100) DEFAULT NULL COMMENT 'Sub-type or specific document type',
  `version` varchar(20) DEFAULT '1.0' COMMENT 'Document version',
  `uploadedBy` int(11) NOT NULL COMMENT 'FK to tija_users',
  `description` text DEFAULT NULL COMMENT 'Document description or notes',
  `expenseID` int(11) DEFAULT NULL COMMENT 'Optional FK to expense if this is an expense document',
  `isConfidential` enum('Y','N') DEFAULT 'N' COMMENT 'Confidential document flag',
  `isPublic` enum('Y','N') DEFAULT 'N' COMMENT 'Accessible to client',
  `requiresApproval` enum('Y','N') DEFAULT 'N' COMMENT 'Requires management/finance approval',
  `approvalStatus` enum('pending','approved','rejected') DEFAULT NULL COMMENT 'Approval status if requiresApproval=Y',
  `approvedBy` int(11) DEFAULT NULL COMMENT 'FK to tija_users - who approved',
  `approvedDate` datetime DEFAULT NULL COMMENT 'Approval date',
  `downloadCount` int(11) DEFAULT 0 COMMENT 'Number of times downloaded',
  `DateAdded` datetime DEFAULT current_timestamp(),
  `LastUpdate` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `LastUpdatedByID` int(11) DEFAULT NULL COMMENT 'FK to tija_users',
  `Suspended` enum('Y','N') DEFAULT 'N' COMMENT 'Soft delete flag'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sales documents and files management for sales cases';

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_progress`
--

CREATE TABLE `tija_sales_progress` (
  `salesProgressID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `salesCaseID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `businessUnitID` int(11) DEFAULT NULL,
  `saleStatusLevelID` int(11) NOT NULL,
  `progressPercentage` decimal(3,2) NOT NULL,
  `progressNotes` text NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `salesPersonID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_prospects`
--

CREATE TABLE `tija_sales_prospects` (
  `salesProspectID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `salesProspectName` varchar(255) NOT NULL,
  `clientID` int(11) DEFAULT NULL,
  `isClient` enum('Y','N') NOT NULL DEFAULT 'N',
  `address` text DEFAULT NULL,
  `prospectEmail` varchar(254) DEFAULT NULL,
  `prospectCaseName` varchar(255) NOT NULL,
  `estimatedValue` int(11) DEFAULT NULL,
  `probability` int(11) DEFAULT NULL,
  `salesProspectStatus` enum('open','closed') NOT NULL DEFAULT 'open',
  `LeadSourceID` int(11) NOT NULL,
  `businessUnitID` int(11) NOT NULL,
  `productCategoryID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `ownerID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_status_levels`
--

CREATE TABLE `tija_sales_status_levels` (
  `saleStatusLevelID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `statusLevel` varchar(255) NOT NULL,
  `statusOrder` int(11) NOT NULL,
  `StatusLevelDescription` text DEFAULT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `levelPercentage` decimal(4,2) NOT NULL,
  `previousLevelID` int(11) NOT NULL,
  `closeLevel` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_sales_status_levels`
--

INSERT INTO `tija_sales_status_levels` (`saleStatusLevelID`, `DateAdded`, `statusLevel`, `statusOrder`, `StatusLevelDescription`, `orgDataID`, `entityID`, `levelPercentage`, `previousLevelID`, `closeLevel`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-20 20:32:59', 'Stalled', 1, 'Not lost per definition.\r\nDialogue was postponed for an indefinite period.\r\nclose date should not be for the current fiscal year.', 1, 1, 5.00, 0, 'N', '2025-03-04 09:48:42', 11, 'N', 'N'),
(3, '2025-02-21 14:21:29', 'Interest', 2, 'Expression of interest from Prospect/Client. \r\nProduct info Sent and/or meeting has been held. \r\nNo clear next step has been agreed.', 1, 1, 10.00, 1, 'N', '2025-03-04 09:49:38', 11, 'N', 'N'),
(4, '2025-02-21 14:22:39', 'Qualification', 3, 'Had a meeting. Identified need. The solution is presented. The Next Step/activity is agreed upon and booked.', 1, 1, 25.00, 3, 'N', '2025-03-04 09:51:17', 11, 'N', 'N'),
(5, '2025-02-21 14:30:52', 'Evaluation', 4, 'Dialogue about solution/evaluation internally. The price is proposed and well-calibrated. The next step/activity is agreed upon and booked.', 1, 1, 50.00, 4, 'N', '2025-03-04 09:52:17', 11, 'N', 'N'),
(6, '2025-02-21 14:35:57', 'Negotiation', 5, 'Solution is accepted and an offer/contract sent. Pricing and terms are discussed. The time plan for the project is being discussed', 1, 1, 75.00, 5, 'N', '2025-03-04 09:53:10', 11, 'N', 'N'),
(7, '2025-02-21 14:37:04', 'Verbal Acceptance', 6, 'Verbal acceptance/approval via email on quote/ agreement. The time plan for the project start is confirmed. The final draft sent for signing.', 1, 1, 90.00, 6, 'N', '2025-03-04 09:54:03', 11, 'N', 'N'),
(8, '2025-02-21 14:37:34', 'Close', 7, 'Contract signed (Product/implementation/service)\r\nApproval Via email for smaller deals i.e. Training, minor consultancy, more users etc', 1, 1, 99.99, 7, 'N', '2025-03-04 09:54:40', 11, 'N', 'N'),
(9, '2025-04-11 14:10:35', 'Lead', 1, 'A lead is a potential customer who has shown interest in your product or service. This could be through various means. A lead is considered when you have made initial contact with the client and gathered basic information like name, contacts and company details. The client interest level is defined but not qualified', 1, 2, 10.00, 0, 'N', '2025-04-11 14:10:35', 2, 'N', 'N'),
(10, '2025-04-11 14:33:09', 'Opportunity', 2, 'An opportunity is a lead that has been qualified and shows a higher likelihood of becoming a customer. This stage involves deeper engagement and understanding of the customer\'s needs. Characteristics include Lead has been qualified(i.e. budget, authority need and timelines). You have had detailed discussions about the product/Service and potential solutions are being explored.', 1, 2, 25.00, 9, 'N', '2025-04-11 14:33:09', 2, 'N', 'N'),
(11, '2025-04-11 14:36:14', 'Proposal', 3, 'At the proposal stage, a formal offer is made to the customer. This includes detailed pricing, terms, and conditions tailored to the customer\'s requirements. i.e proposal document has been created and shared. Negotiations may occur, and the customer may review the offer and provide feedback', 1, 2, 50.00, 10, 'N', '2025-04-11 14:36:14', 2, 'N', 'N'),
(12, '2025-04-11 14:38:30', 'Close (Order/Lost)', 4, 'The close stage is where the final decision is made. The deal is either won (order) or lost. i.e. Customer makes a final decision.\r\nIf won, the order is processed, and the sale is completed.\r\nIf lost, reasons for the loss are analyzed for future improvement.', 1, 2, 99.99, 11, 'Y', '2025-04-11 14:38:30', 2, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_statement_of_investment_allowance_accounts`
--

CREATE TABLE `tija_statement_of_investment_allowance_accounts` (
  `investmentAllowanceAccountID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `accountName` varchar(255) NOT NULL,
  `parentAccountID` int(11) NOT NULL,
  `accountCategory` varchar(255) NOT NULL,
  `financialStatementTypeID` int(11) NOT NULL,
  `statementTypeNode` varchar(256) NOT NULL,
  `accountCode` varchar(255) NOT NULL,
  `accountNode` varchar(255) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_statement_of_investment_allowance_data`
--

CREATE TABLE `tija_statement_of_investment_allowance_data` (
  `InvestmentAllowanceID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `financialStatementID` int(11) NOT NULL,
  `investmentName` varchar(256) NOT NULL,
  `rate` float(6,4) NOT NULL,
  `initialWriteDownValue` float(20,2) NOT NULL,
  `beginDate` date NOT NULL,
  `additions` float(18,2) NOT NULL,
  `disposals` float(18,2) NOT NULL,
  `wearAndTearAllowance` float(18,2) NOT NULL,
  `endWriteDownValue` float(18,2) NOT NULL,
  `endDate` date NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `allowInTotal` enum('Y','N') NOT NULL DEFAULT 'Y'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='sbsl_statement_of_investment_allowance_data';

-- --------------------------------------------------------

--
-- Table structure for table `tija_subtasks`
--

CREATE TABLE `tija_subtasks` (
  `subtaskID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectTaskID` int(11) NOT NULL,
  `subTaskName` varchar(256) NOT NULL,
  `subTaskStatus` enum('active','pending','completed','in progress','overdue') NOT NULL DEFAULT 'active',
  `subTaskStatusID` int(11) DEFAULT NULL,
  `assignee` varchar(256) DEFAULT NULL,
  `subtaskDueDate` date DEFAULT NULL,
  `dependencies` varchar(256) DEFAULT NULL,
  `subTaskDescription` text NOT NULL,
  `subTaskAllocatedWorkHours` decimal(10,2) DEFAULT NULL,
  `needsDocuments` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tasks_time_logs`
--

CREATE TABLE `tija_tasks_time_logs` (
  `timelogID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `taskDate` date NOT NULL,
  `employeeID` int(11) NOT NULL,
  `clientID` int(11) NOT NULL,
  `projectID` int(11) DEFAULT NULL,
  `projectPhaseID` int(11) DEFAULT NULL,
  `projectTaskID` int(11) DEFAULT NULL,
  `subtaskID` int(11) DEFAULT NULL,
  `workTypeID` int(11) NOT NULL,
  `taskNarrative` text DEFAULT NULL,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `taskDuration` varchar(30) NOT NULL,
  `taskDurationSeconds` int(11) NOT NULL,
  `billable` enum('Y','N') NOT NULL DEFAULT 'Y',
  `billableRateValue` decimal(10,2) NOT NULL,
  `workHours` varchar(20) DEFAULT NULL,
  `dailyComplete` enum('Y','N') NOT NULL DEFAULT 'N',
  `taskStatusID` int(11) DEFAULT NULL,
  `taskType` enum('adhoc','project','sales','activity','proposal') NOT NULL DEFAULT 'project',
  `taskActivityID` int(11) DEFAULT NULL,
  `workSegmentID` int(11) DEFAULT NULL,
  `recurringInstanceID` int(11) DEFAULT NULL,
  `billingCycleID` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_task_files`
--

CREATE TABLE `tija_task_files` (
  `taskFileID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `fileURL` varchar(256) NOT NULL,
  `timelogID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `fileSize` int(11) DEFAULT NULL,
  `fileType` varchar(256) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_task_status`
--

CREATE TABLE `tija_task_status` (
  `taskStatusID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `taskStatusName` varchar(256) NOT NULL,
  `taskStatusDescription` text NOT NULL,
  `colorVariableID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('N','Y') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_task_status`
--

INSERT INTO `tija_task_status` (`taskStatusID`, `DateAdded`, `taskStatusName`, `taskStatusDescription`, `colorVariableID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-05-29 20:27:33', 'Not Started', 'Task has not been initiated/ No activity on task yet', NULL, '2025-05-29 17:27:33', 37, 'N', 'N'),
(2, '2025-05-29 20:45:05', 'In Progress', 'Task initiated and work hour logs have been reported', NULL, '2025-05-29 17:45:05', 37, 'N', 'N'),
(3, '2025-05-29 20:47:56', 'In Review', 'Tasks under review by an approver', NULL, '2025-05-29 17:47:56', 37, 'N', 'N'),
(4, '2025-05-29 20:48:38', 'Completed', 'Tasks completed and approved by the approver', NULL, '2025-05-29 17:48:38', 37, 'N', 'N'),
(5, '2025-05-29 20:50:48', 'Needs Attention', 'Activity requires the attention of a superior authority. The maker requires help or is stuck', NULL, '2025-05-29 17:50:48', 37, 'N', 'N'),
(6, '2025-05-29 20:51:28', 'Documents Received', 'All necessary material and documents to perform the task have been provided', NULL, '2025-05-29 17:51:28', 37, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_task_status_change_log`
--

CREATE TABLE `tija_task_status_change_log` (
  `taskStatusChangeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `projectID` int(11) NOT NULL,
  `taskStatusID` int(11) NOT NULL,
  `projectTaskID` int(11) NOT NULL,
  `projectPhaseID` int(11) DEFAULT NULL,
  `subtaskID` int(11) DEFAULT NULL,
  `changeDateTime` datetime NOT NULL,
  `employeeID` int(11) NOT NULL,
  `taskChangeNotes` text DEFAULT NULL,
  `taskDate` date NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_taxable_profit`
--

CREATE TABLE `tija_taxable_profit` (
  `taxableProfitID` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `fiscalYear` int(11) NOT NULL,
  `taxableProfit` float(20,2) NOT NULL,
  `taxableProfitDescription` text DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tax_adjustments_accounts`
--

CREATE TABLE `tija_tax_adjustments_accounts` (
  `adjustmentAccountsID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `adjustmentTypeID` int(11) NOT NULL,
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `financialStatementAccountID` int(11) NOT NULL,
  `financialStatementTypeID` int(11) NOT NULL,
  `accountRate` float(3,2) NOT NULL DEFAULT 1.00,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tax_adjustment_categories`
--

CREATE TABLE `tija_tax_adjustment_categories` (
  `adjustmentCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `adjustmentCategoryName` varchar(256) NOT NULL,
  `adjustmentCategoryDescription` text NOT NULL,
  `adjustmentTypeID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tax_adjustment_types`
--

CREATE TABLE `tija_tax_adjustment_types` (
  `adjustmentTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `adjustmentType` varchar(255) NOT NULL,
  `adjustmentTypeDescription` text NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_travel_rate_types`
--

CREATE TABLE `tija_travel_rate_types` (
  `travelRateTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `travelRateTypeName` varchar(255) NOT NULL,
  `travelRateTypeDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_travel_rate_types`
--

INSERT INTO `tija_travel_rate_types` (`travelRateTypeID`, `DateAdded`, `travelRateTypeName`, `travelRateTypeDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-08 16:06:50', 'MIllege', 'Travel expenses based on distance', '2025-03-08 16:06:50', 11, 'N', 'N'),
(2, '2025-03-08 16:18:00', 'Per Diem', 'Perdiem for a night out', '2025-03-08 16:18:00', 11, 'N', 'N'),
(3, '2025-03-08 16:18:34', 'Flight Ticket', 'Flight Tickets', '2025-03-08 16:18:34', 11, 'N', 'N'),
(4, '2025-03-08 16:19:03', 'Visa Fees', 'Visa fee for country entry', '2025-03-08 16:19:03', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_trial_balance_mapped_accounts`
--

CREATE TABLE `tija_trial_balance_mapped_accounts` (
  `mappedAccountID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `financialStatementID` int(11) NOT NULL,
  `financialStatementTypeID` int(11) NOT NULL,
  `statementTypeNode` varchar(256) NOT NULL,
  `financialStatementAccountID` int(11) NOT NULL,
  `financialStatementDataID` int(11) NOT NULL,
  `accountName` varchar(256) NOT NULL,
  `accountType` varchar(255) NOT NULL,
  `accountCategory` varchar(256) NOT NULL,
  `debitValue` decimal(12,2) NOT NULL,
  `creditValue` decimal(12,2) NOT NULL,
  `accountCode` varchar(120) NOT NULL,
  `categoryAccountCode` varchar(120) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_units`
--

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
(1, '2025-11-21 12:25:40', 'T-543534', '1', 1, 'Technology', 1, 4, 0, 'Technology Revenue Unit', '2025-11-21 12:25:40', 'N', 'N'),
(2, '2025-11-21 12:26:20', 'A-409294', '1', 1, 'Administration', 1, 2, 0, 'Administration and Executive', '2025-11-21 12:26:20', 'N', 'N'),
(3, '2025-11-21 12:27:30', 'P-654535', '1', 1, 'Projects', 1, 3, 0, 'Projects Department Unit', '2025-11-21 12:27:30', 'N', 'N'),
(4, '2025-11-21 12:33:43', 'F-523426', '1', 1, 'Finance', 1, 21, 0, 'Finance and Revenue Department', '2025-11-21 12:33:43', 'N', 'N'),
(5, '2025-11-21 12:42:18', 'S&M-180847', '1', 1, 'Sales & Marketing', 1, 2, 0, 'Sales & Marketing Department', '2025-11-21 12:42:18', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_unit_types`
--

CREATE TABLE `tija_unit_types` (
  `unitTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `unitTypeName` varchar(256) NOT NULL,
  `unitOrder` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_unit_types`
--

INSERT INTO `tija_unit_types` (`unitTypeID`, `DateAdded`, `unitTypeName`, `unitOrder`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-15 16:28:01', 'Department', NULL, '2025-02-15 16:28:01', 'N', 'N'),
(2, '2025-02-15 16:50:22', 'Section', NULL, '2025-02-15 16:50:22', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_user_unit_assignments`
--

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
(1, '2025-11-25 08:43:53', 1, 1, 13, 5, 1, '2025-11-25', NULL, '2025-11-25 08:43:53', 13, 'N', 'N'),
(2, '2025-11-26 09:15:18', 1, 1, 6, 4, 1, '2025-11-26', NULL, '2025-11-26 09:15:18', 6, 'N', 'N'),
(3, '2025-11-26 09:15:29', 1, 1, 6, 5, 1, '2025-11-26', NULL, '2025-11-26 09:15:29', 6, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_withholding_tax`
--

CREATE TABLE `tija_withholding_tax` (
  `withholdingTaxID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `orgDataID` int(11) NOT NULL,
  `entityID` int(11) NOT NULL,
  `fiscalYear` int(11) NOT NULL,
  `withholdingTax` float(22,2) NOT NULL,
  `withholdingTaxDescription` text DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_work_categories`
--

CREATE TABLE `tija_work_categories` (
  `workCategoryID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `workCategoryName` varchar(255) NOT NULL,
  `workCategoryCode` varchar(120) NOT NULL,
  `workCategoryDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdatedByID` int(11) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_work_categories`
--

INSERT INTO `tija_work_categories` (`workCategoryID`, `DateAdded`, `workCategoryName`, `workCategoryCode`, `workCategoryDescription`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-30 19:53:29', 'Productive Work', '027151_PW_5', '<p>Productive Work</p>', '2025-03-30 19:53:29', 0, 'N', 'N'),
(2, '2025-04-11 12:14:29', 'None Productive Work', '201357_NPW_5', '<p>Work Types that are not directly billable to the client</p>', '2025-04-11 12:14:29', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_work_types`
--

CREATE TABLE `tija_work_types` (
  `workTypeID` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT current_timestamp(),
  `workTypeCode` varchar(120) DEFAULT NULL,
  `workTypeName` varchar(120) NOT NULL,
  `workTypeDescription` text DEFAULT NULL,
  `workCategoryID` int(11) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `LastUpdateByID` int(11) NOT NULL,
  `lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tija_work_types`
--

INSERT INTO `tija_work_types` (`workTypeID`, `DateAdded`, `workTypeCode`, `workTypeName`, `workTypeDescription`, `workCategoryID`, `LastUpdate`, `LastUpdateByID`, `lapsed`, `Suspended`) VALUES
(1, '2021-08-28 12:10:33', 'mEqjFg', 'Consulting', '<p>Consulting work</p>', 1, '2025-03-31 17:08:28', 0, 'N', 'N'),
(2, '2021-08-28 12:10:33', 'Zcv0V7', 'Internal Work', '<p>Internal Work</p>', 1, '2025-03-31 17:20:09', 11, 'N', 'N'),
(3, '2021-11-01 11:26:04', '6X9Eff', 'Project Management', '<p>Work that involves operational transactions ie number of withdrawals, swift transfers etc.</p>', 1, '2025-03-31 17:22:06', 11, 'N', 'N'),
(4, '2021-11-01 11:26:04', 'QsXPgx', 'Junior Specialist', '<p>Normal tasks that take time that produce a report for work Done</p>', 1, '2025-03-31 17:22:21', 11, 'N', 'N'),
(5, '2021-11-28 02:07:59', 'ZD6rBm', 'Senior Specialist', '<p>Project task continuous tasks</p>', 1, '2025-03-31 17:22:28', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

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
  `LastUpdate` datetime NOT NULL DEFAULT current_timestamp(),
  `isHRManager` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`ID`, `UID`, `DateAdded`, `orgDataID`, `entityID`, `prefixID`, `phoneNo`, `payrollNo`, `PIN`, `dateOfBirth`, `gender`, `businessUnitID`, `supervisorID`, `supervisingJobTitleID`, `workTypeID`, `jobTitleID`, `departmentID`, `costPerHour`, `jobCategoryID`, `salary`, `jobBandID`, `employmentStatusID`, `dailyHours`, `weekWorkDays`, `overtimeAllowed`, `workHourRoundingID`, `payGradeID`, `nationalID`, `nhifNumber`, `nssfNumber`, `basicSalary`, `bonusEligible`, `commissionEligible`, `commissionRate`, `SetUpProfile`, `profileImageFile`, `Lapsed`, `Suspended`, `contractStartDate`, `contractEndDate`, `employmentStartDate`, `employmentEndDate`, `LastUpdatedByID`, `LastUpdate`, `isHRManager`) VALUES
(2, 'ada2b28babe49a343e90ba0761e687bc896d7650e8976dcecfa64c7b9aa3f685', '2025-11-21 09:59:36', 1, 1, '1', '+254 721 358850', 'SBSL-001', NULL, NULL, '', NULL, 0, NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-25 11:34:39', 'N'),
(3, 'dfa49391168a5c5f4bc9f9857826485450fd0aff952db6acdd8586e3374c11cb', '2025-11-21 10:01:12', 1, 1, '1', '+254 720 668781', 'SBSL-002', NULL, NULL, '', NULL, 2, NULL, NULL, 19, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-12-01 10:23:28', 'N'),
(4, '44af8d7eee03d5305300e17c14b408401707baa65bb4d37cdcda5d753bcfe8b4', '2025-11-21 11:21:15', 1, 1, '1', '+254722540169', 'SBSL-003', NULL, NULL, '', NULL, 2, NULL, NULL, 22, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-12-01 10:23:28', 'Y'),
(5, '103997ec9a284212482cdff5bac5b8d7e4fa450ab46758930b0e57ed9f224fe8', '2025-11-21 11:23:46', 1, 1, '1', '+254 725 148487', 'SBSL-004', NULL, NULL, '', NULL, 2, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:23:46', 'N'),
(6, '4e6cf8f79eaa49d8c6036607e750e34b30322ec201e17b88e11d50975c18dcfb', '2025-11-21 11:25:20', 1, 1, '3', '+254 723 853601', 'SBSL-005', NULL, NULL, '', NULL, 2, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:25:20', 'N'),
(7, '33a01c6d40927d592553e8220a611af4f1f0f8feaf2df201fb6b37d907994753', '2025-11-21 11:30:05', 1, 1, '1', '+254', 'SBSL-006', NULL, NULL, '', NULL, 2, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:30:05', 'N'),
(8, 'd404d0cef55db4e0cc487d7aa8ca950f611a5a114a990837103caed8de490a42', '2025-11-21 11:31:54', 1, 1, '1', '+254', 'SBSL-007', NULL, NULL, '', NULL, 5, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:31:54', 'N'),
(9, '7713d6a14f491f26f89614a09d7acc78fa2805b90fc1d2ab5c88c43d9058a87d', '2025-11-21 11:42:45', 1, 1, '1', '+254', 'SBSL-008', NULL, NULL, '', NULL, 2, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:42:45', 'N'),
(10, '536c5409ab73e8ea8a4e35bbae3c272eedad40402e09b2bfeba279a82ea67841', '2025-11-21 11:45:25', 1, 1, '3', '+254', 'SBSL-009', NULL, NULL, '', NULL, 6, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:45:25', 'N'),
(11, '07a838d888e7c00132e6304625b31a972ec4662dc9489afa48a8cfb7cc22f2ba', '2025-11-21 11:49:53', 1, 1, NULL, '+254', 'SBSL-011', NULL, NULL, '', NULL, 4, NULL, NULL, 41, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', 3, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', '2025-04-01', NULL, '2025-04-01', NULL, 4, '2025-11-21 11:49:53', 'N'),
(12, '785179f2eb8775766fc916af14ef9d26803c5fb9dfc9d299c92a4c77ae00635d', '2025-11-21 11:51:24', 1, 1, '1', '+254', 'SBSL-012', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:51:24', 'N'),
(13, '368879280e79775136acf93dba0a25c0e0c7a9e36dcf75f9f10ec267e30e8d11', '2025-11-21 11:56:24', 1, 1, NULL, '+254115631643', 'SBSL-013', 'A022427709X', '2005-04-01', 'female', NULL, 4, NULL, NULL, 41, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, '424087542', '424087542', '20600581699', NULL, 'N', 'N', 0.00, 'n', 'employee_profile/1764008119_Screenshot_2025-07-01_232448.png', 'N', 'N', NULL, NULL, '2025-01-01', NULL, 13, '2025-11-21 11:56:24', 'N'),
(14, 'd5e47d3e44f798967eca55de66407f4cc3f8dfd82bd6e136ead46901ecafbf39', '2025-11-21 12:00:07', 1, 1, '1', '+254 758227376', 'SBSL-014', 'A020110171X', '2005-01-28', 'male', NULL, 4, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 5, 8, '5', 'N', NULL, NULL, '42443285', NULL, NULL, NULL, 'N', 'N', 0.00, 'n', 'employee_profile/1763968287_Germans_passport_photo.png', 'N', 'N', NULL, NULL, '2025-01-01', NULL, 14, '2025-11-21 12:00:07', 'N'),
(15, '0ca7eae04454e868289b9f2b43a9bea0b81fb2a7aca8bb373c7e5b5a04e6454c', '2025-11-21 12:05:10', 1, 1, '1', '+254', 'SBSL-015', NULL, NULL, '', NULL, 2, NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', 'employee_profile/1764404376_avatar.jpg', 'N', 'N', NULL, NULL, '2025-01-01', NULL, 15, '2025-11-21 12:05:10', 'N'),
(16, '0231f46e42ceaf0c4a0fe4639896d8efaf66e77c45c11cf4223c2270a983e10a', '2025-11-21 12:10:29', 1, 1, '1', '+254', 'SBSL-016', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:10:29', 'N'),
(17, 'ab6e05b9cce17f9c29a31d7499ce39e975d13a9e6b46885a29de7b89335986f2', '2025-11-21 12:16:15', 1, 1, '1', '+254', 'SBSL-017', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:16:15', 'N'),
(18, '20eba8a2696a02b21db8f4fd15254ccbd1b9e84b7fbd1820f857ebd7efac26de', '2025-11-21 12:18:51', 1, 1, '1', '+254', 'SBSL-018', NULL, NULL, '', NULL, 2, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:18:51', 'N'),
(19, '46f299208147f28489b9ef4da23ef583a57cf5e77f1ed60681ea6a0711d8ba95', '2025-11-21 12:23:27', 1, 1, '1', '+254', 'SBSL-019', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-08-01', NULL, 1, '2025-11-21 12:23:27', 'N'),
(20, 'ac5298c8157cd560ce568cc0688de7bdc478d0349325c008e5e0933c1ba8f1df', '2025-11-21 12:30:30', 1, 1, '3', '+254', 'SBSL-020', NULL, NULL, '', NULL, 3, NULL, NULL, 57, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:30:30', 'N'),
(21, '6001923fb6a5d0e11213929aed7527fab50f35928ef4a4534088e85013b91203', '2025-11-21 12:32:33', 1, 1, '1', '+254', 'SBSL-010', NULL, NULL, '', NULL, 0, NULL, NULL, 15, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:32:33', 'N'),
(22, '8222dc395fbb445d0354c5c1e909eac7b77cdf106ffca73710c5712c9caedf0e', '2025-11-22 10:54:36', 1, 1, '1', '+254', 'SBSL-021', NULL, NULL, '', NULL, 2, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, 4, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-11-01', NULL, 4, '2025-11-22 10:54:36', 'N'),
(23, 'b587d73e3f428dd4580694b801148bed433882fd7effbcc038ce97d76b180f55', '2025-11-24 01:45:13', 1, 1, '1', '+254785659652', 'SKMO99', 'I4522152339Y', '2007-01-01', '', NULL, 4, NULL, NULL, 46, NULL, NULL, NULL, 70000.00, NULL, 1, 8, NULL, 'Y', NULL, NULL, '1951510753', '235030423', '23436543', 70000.00, 'Y', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 01:45:13', 'N'),
(24, '6997dc4ceb38d1abf6cc938f995602e8ef05324808aafc3adfa04997541f2105', '2025-11-24 01:57:38', 1, 1, '1', '+254745734948', 'SKM100', 'I4522152339R', '2007-01-01', '', NULL, 23, NULL, NULL, 28, NULL, NULL, NULL, 70000.00, NULL, 1, NULL, NULL, 'Y', NULL, NULL, '245657698', '23503042k', '2343654y', 70000.00, 'Y', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 01:57:38', 'N');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leave_approval_policies`
-- (See below for the actual view)
--
CREATE TABLE `vw_leave_approval_policies` (
`policyID` int(11)
,`entityID` int(11)
,`orgDataID` int(11)
,`policyName` varchar(255)
,`policyDescription` text
,`isActive` enum('Y','N')
,`isDefault` enum('Y','N')
,`requireAllApprovals` enum('Y','N')
,`allowDelegation` enum('Y','N')
,`autoApproveThreshold` int(11)
,`totalSteps` bigint(21)
,`requiredSteps` bigint(21)
,`createdBy` int(11)
,`createdAt` datetime
,`createdByName` varchar(257)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leave_approval_workflow`
-- (See below for the actual view)
--
CREATE TABLE `vw_leave_approval_workflow` (
`policyID` int(11)
,`policyName` varchar(255)
,`entityID` int(11)
,`stepID` int(11)
,`stepOrder` int(11)
,`stepName` varchar(255)
,`stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user')
,`stepDescription` text
,`isRequired` enum('Y','N')
,`isConditional` enum('Y','N')
,`conditionType` enum('days_threshold','leave_type','user_role','department','custom')
,`escalationDays` int(11)
,`customApproversCount` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_notification_events_with_templates`
-- (See below for the actual view)
--
CREATE TABLE `vw_notification_events_with_templates` (
`eventID` int(11)
,`eventName` varchar(100)
,`eventSlug` varchar(100)
,`eventDescription` text
,`eventCategory` varchar(50)
,`priorityLevel` enum('low','medium','high','critical')
,`moduleID` int(11)
,`moduleName` varchar(100)
,`moduleSlug` varchar(50)
,`templateCount` bigint(21)
,`isActive` enum('Y','N')
,`isUserConfigurable` enum('Y','N')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_pending_leave_approvals`
-- (See below for the actual view)
--
CREATE TABLE `vw_pending_leave_approvals` (
`instanceID` int(11)
,`leaveApplicationID` int(11)
,`employeeID` int(11)
,`employeeName` varchar(257)
,`leaveTypeID` int(11)
,`leaveTypeName` varchar(255)
,`startDate` date
,`endDate` date
,`totalDays` decimal(3,2)
,`policyID` int(11)
,`policyName` varchar(255)
,`currentStepID` int(11)
,`currentStepName` varchar(255)
,`currentStepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user')
,`currentStepOrder` int(11)
,`workflowStatus` enum('pending','in_progress','approved','rejected','cancelled','escalated')
,`startedAt` datetime
,`lastActionAt` datetime
,`daysPending` int(8)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_user_notification_summary`
-- (See below for the actual view)
--
CREATE TABLE `vw_user_notification_summary` (
`userID` int(11)
,`totalNotifications` bigint(21)
,`unreadCount` decimal(22,0)
,`readCount` decimal(22,0)
,`criticalUnread` decimal(22,0)
,`lastNotificationDate` datetime
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `african_countries`
--
ALTER TABLE `african_countries`
  ADD PRIMARY KEY (`countryID`);

--
-- Indexes for table `client_relationship_assignments`
--
ALTER TABLE `client_relationship_assignments`
  ADD PRIMARY KEY (`clientRelationshipID`);

--
-- Indexes for table `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`currencyID`);

--
-- Indexes for table `industry_sectors`
--
ALTER TABLE `industry_sectors`
  ADD PRIMARY KEY (`industrySectorID`);

--
-- Indexes for table `login_sessions`
--
ALTER TABLE `login_sessions`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `SessIDStr` (`SessIDStr`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `registration_tokens`
--
ALTER TABLE `registration_tokens`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Token1` (`Token1`),
  ADD UNIQUE KEY `Token2` (`Token2`);

--
-- Indexes for table `tija_absence_data`
--
ALTER TABLE `tija_absence_data`
  ADD PRIMARY KEY (`absenceID`);

--
-- Indexes for table `tija_absence_type`
--
ALTER TABLE `tija_absence_type`
  ADD PRIMARY KEY (`absenceTypeID`);

--
-- Indexes for table `tija_activities`
--
ALTER TABLE `tija_activities`
  ADD PRIMARY KEY (`activityID`);

--
-- Indexes for table `tija_activity_categories`
--
ALTER TABLE `tija_activity_categories`
  ADD PRIMARY KEY (`activityCategoryID`);

--
-- Indexes for table `tija_activity_log`
--
ALTER TABLE `tija_activity_log`
  ADD PRIMARY KEY (`activityLogID`);

--
-- Indexes for table `tija_activity_participant_assignment`
--
ALTER TABLE `tija_activity_participant_assignment`
  ADD PRIMARY KEY (`activityParticipantID`);

--
-- Indexes for table `tija_activity_status`
--
ALTER TABLE `tija_activity_status`
  ADD PRIMARY KEY (`activityStatusID`);

--
-- Indexes for table `tija_activity_types`
--
ALTER TABLE `tija_activity_types`
  ADD PRIMARY KEY (`activityTypeID`);

--
-- Indexes for table `tija_administrators`
--
ALTER TABLE `tija_administrators`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `tija_admin_types`
--
ALTER TABLE `tija_admin_types`
  ADD PRIMARY KEY (`adminTypeID`),
  ADD UNIQUE KEY `adminCode` (`adminCode`);

--
-- Indexes for table `tija_advance_tax`
--
ALTER TABLE `tija_advance_tax`
  ADD PRIMARY KEY (`advanceTaxID`);

--
-- Indexes for table `tija_assigned_project_tasks`
--
ALTER TABLE `tija_assigned_project_tasks`
  ADD PRIMARY KEY (`assignmentTaskID`);

--
-- Indexes for table `tija_benefit_types`
--
ALTER TABLE `tija_benefit_types`
  ADD PRIMARY KEY (`benefitTypeID`),
  ADD UNIQUE KEY `benefitCode` (`benefitCode`),
  ADD KEY `benefitCategory` (`benefitCategory`),
  ADD KEY `Suspended` (`Suspended`);

--
-- Indexes for table `tija_billing_rate`
--
ALTER TABLE `tija_billing_rate`
  ADD PRIMARY KEY (`billingRateID`);

--
-- Indexes for table `tija_billing_rates`
--
ALTER TABLE `tija_billing_rates`
  ADD PRIMARY KEY (`billingRateID`);

--
-- Indexes for table `tija_billing_rate_types`
--
ALTER TABLE `tija_billing_rate_types`
  ADD PRIMARY KEY (`billingRateTypeID`);

--
-- Indexes for table `tija_bradford_factor`
--
ALTER TABLE `tija_bradford_factor`
  ADD PRIMARY KEY (`bradfordFactorID`);

--
-- Indexes for table `tija_business_units`
--
ALTER TABLE `tija_business_units`
  ADD PRIMARY KEY (`businessUnitID`),
  ADD KEY `idx_category` (`categoryID`);

--
-- Indexes for table `tija_business_unit_categories`
--
ALTER TABLE `tija_business_unit_categories`
  ADD PRIMARY KEY (`categoryID`),
  ADD UNIQUE KEY `idx_category_code` (`categoryCode`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_cases`
--
ALTER TABLE `tija_cases`
  ADD PRIMARY KEY (`caseID`);

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
-- Indexes for table `tija_client_levels`
--
ALTER TABLE `tija_client_levels`
  ADD PRIMARY KEY (`clientLevelID`);

--
-- Indexes for table `tija_client_relationship_types`
--
ALTER TABLE `tija_client_relationship_types`
  ADD PRIMARY KEY (`clientRelationshipTypeID`);

--
-- Indexes for table `tija_contact_relationships`
--
ALTER TABLE `tija_contact_relationships`
  ADD PRIMARY KEY (`relationshipID`),
  ADD UNIQUE KEY `relationshipCode` (`relationshipCode`);

--
-- Indexes for table `tija_contact_types`
--
ALTER TABLE `tija_contact_types`
  ADD PRIMARY KEY (`contactTypeID`);

--
-- Indexes for table `tija_delegation_assignments`
--
ALTER TABLE `tija_delegation_assignments`
  ADD PRIMARY KEY (`delegationID`),
  ADD KEY `idx_delegator` (`delegatorID`),
  ADD KEY `idx_delegate` (`delegateID`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_dates` (`startDate`,`endDate`);

--
-- Indexes for table `tija_document_types`
--
ALTER TABLE `tija_document_types`
  ADD PRIMARY KEY (`documentTypeID`);

--
-- Indexes for table `tija_employee_addresses`
--
ALTER TABLE `tija_employee_addresses`
  ADD PRIMARY KEY (`addressID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_address_type` (`addressType`),
  ADD KEY `idx_primary` (`isPrimary`);

--
-- Indexes for table `tija_employee_allowances`
--
ALTER TABLE `tija_employee_allowances`
  ADD PRIMARY KEY (`allowanceID`),
  ADD KEY `idx_employee` (`employeeID`);

--
-- Indexes for table `tija_employee_bank_accounts`
--
ALTER TABLE `tija_employee_bank_accounts`
  ADD PRIMARY KEY (`bankAccountID`),
  ADD KEY `employeeID` (`employeeID`),
  ADD KEY `isPrimary` (`isPrimary`),
  ADD KEY `isActive` (`isActive`),
  ADD KEY `Suspended` (`Suspended`);

--
-- Indexes for table `tija_employee_bank_details`
--
ALTER TABLE `tija_employee_bank_details`
  ADD PRIMARY KEY (`bankDetailID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_account_number` (`accountNumber`),
  ADD KEY `idx_primary` (`isPrimary`),
  ADD KEY `idx_active_salary` (`isActiveForSalary`);

--
-- Indexes for table `tija_employee_benefits`
--
ALTER TABLE `tija_employee_benefits`
  ADD PRIMARY KEY (`benefitID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_benefit_type` (`benefitType`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_policy_number` (`policyNumber`);

--
-- Indexes for table `tija_employee_certifications`
--
ALTER TABLE `tija_employee_certifications`
  ADD PRIMARY KEY (`certificationID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_certification_name` (`certificationName`),
  ADD KEY `idx_expiry_date` (`expiryDate`),
  ADD KEY `idx_active` (`isActive`);

--
-- Indexes for table `tija_employee_dependants`
--
ALTER TABLE `tija_employee_dependants`
  ADD PRIMARY KEY (`dependantID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_beneficiary` (`isBeneficiary`),
  ADD KEY `idx_relationship` (`relationship`);

--
-- Indexes for table `tija_employee_education`
--
ALTER TABLE `tija_employee_education`
  ADD PRIMARY KEY (`educationID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_education_level` (`educationLevel`),
  ADD KEY `idx_sort_order` (`sortOrder`);

--
-- Indexes for table `tija_employee_emergency_contacts`
--
ALTER TABLE `tija_employee_emergency_contacts`
  ADD PRIMARY KEY (`emergencyContactID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_primary` (`isPrimary`),
  ADD KEY `idx_sort_order` (`sortOrder`);

--
-- Indexes for table `tija_employee_extended_personal`
--
ALTER TABLE `tija_employee_extended_personal`
  ADD PRIMARY KEY (`extendedPersonalID`),
  ADD UNIQUE KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_employee_job_history`
--
ALTER TABLE `tija_employee_job_history`
  ADD PRIMARY KEY (`jobHistoryID`),
  ADD KEY `idx_employee` (`employeeID`);

--
-- Indexes for table `tija_employee_licenses`
--
ALTER TABLE `tija_employee_licenses`
  ADD PRIMARY KEY (`licenseID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_license_type` (`licenseType`),
  ADD KEY `idx_expiry_date` (`expiryDate`),
  ADD KEY `idx_active` (`isActive`);

--
-- Indexes for table `tija_employee_next_of_kin`
--
ALTER TABLE `tija_employee_next_of_kin`
  ADD PRIMARY KEY (`nextOfKinID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_primary` (`isPrimary`),
  ADD KEY `idx_sort_order` (`sortOrder`);

--
-- Indexes for table `tija_employee_salary_components`
--
ALTER TABLE `tija_employee_salary_components`
  ADD PRIMARY KEY (`employeeComponentID`),
  ADD UNIQUE KEY `idx_unique_assignment` (`employeeID`,`salaryComponentID`,`effectiveDate`,`Suspended`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_component` (`salaryComponentID`),
  ADD KEY `idx_current` (`isCurrent`);

--
-- Indexes for table `tija_employee_salary_history`
--
ALTER TABLE `tija_employee_salary_history`
  ADD PRIMARY KEY (`salaryHistoryID`),
  ADD KEY `idx_employee` (`employeeID`);

--
-- Indexes for table `tija_employee_skills`
--
ALTER TABLE `tija_employee_skills`
  ADD PRIMARY KEY (`skillID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_skill_category` (`skillCategory`),
  ADD KEY `idx_proficiency` (`proficiencyLevel`);

--
-- Indexes for table `tija_employee_subordinates`
--
ALTER TABLE `tija_employee_subordinates`
  ADD PRIMARY KEY (`subordinateMappingID`),
  ADD KEY `idx_supervisor` (`supervisorID`),
  ADD KEY `idx_subordinate` (`subordinateID`);

--
-- Indexes for table `tija_employee_supervisors`
--
ALTER TABLE `tija_employee_supervisors`
  ADD PRIMARY KEY (`supervisorMappingID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_supervisor` (`supervisorID`);

--
-- Indexes for table `tija_employee_supervisor_relationships`
--
ALTER TABLE `tija_employee_supervisor_relationships`
  ADD PRIMARY KEY (`relationshipID`),
  ADD KEY `employeeID` (`employeeID`),
  ADD KEY `supervisorID` (`supervisorID`),
  ADD KEY `relationshipType` (`relationshipType`),
  ADD KEY `isActive` (`isActive`),
  ADD KEY `Suspended` (`Suspended`),
  ADD KEY `idx_employee_active` (`employeeID`,`isActive`,`Suspended`),
  ADD KEY `idx_supervisor_active` (`supervisorID`,`isActive`,`Suspended`);

--
-- Indexes for table `tija_employee_work_experience`
--
ALTER TABLE `tija_employee_work_experience`
  ADD PRIMARY KEY (`workExperienceID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_company` (`companyName`),
  ADD KEY `idx_sort_order` (`sortOrder`);

--
-- Indexes for table `tija_employment_status`
--
ALTER TABLE `tija_employment_status`
  ADD PRIMARY KEY (`employmentStatusID`);

--
-- Indexes for table `tija_entities`
--
ALTER TABLE `tija_entities`
  ADD PRIMARY KEY (`entityID`);

--
-- Indexes for table `tija_entity_hr_assignments`
--
ALTER TABLE `tija_entity_hr_assignments`
  ADD PRIMARY KEY (`assignmentID`),
  ADD UNIQUE KEY `unique_entity_role` (`entityID`,`roleType`),
  ADD UNIQUE KEY `unique_entity_user` (`entityID`,`userID`),
  ADD KEY `idx_assignment_entity` (`entityID`),
  ADD KEY `idx_assignment_user` (`userID`);

--
-- Indexes for table `tija_entity_role_types`
--
ALTER TABLE `tija_entity_role_types`
  ADD PRIMARY KEY (`roleTypeID`),
  ADD UNIQUE KEY `unique_roleTypeCode` (`roleTypeCode`),
  ADD KEY `idx_isActive` (`isActive`),
  ADD KEY `idx_Suspended` (`Suspended`),
  ADD KEY `idx_displayOrder` (`displayOrder`);

--
-- Indexes for table `tija_entity_types`
--
ALTER TABLE `tija_entity_types`
  ADD PRIMARY KEY (`entityTypeID`);

--
-- Indexes for table `tija_expense`
--
ALTER TABLE `tija_expense`
  ADD PRIMARY KEY (`expenseID`),
  ADD UNIQUE KEY `unique_expense_number` (`expenseNumber`),
  ADD UNIQUE KEY `unique_expense_code` (`expenseCode`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_employee_code` (`employeeCode`),
  ADD KEY `idx_expense_type` (`expenseTypeID`),
  ADD KEY `idx_expense_category` (`expenseCategoryID`),
  ADD KEY `idx_expense_status` (`expenseStatusID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_client` (`clientID`),
  ADD KEY `idx_sales_case` (`salesCaseID`),
  ADD KEY `idx_department` (`departmentID`),
  ADD KEY `idx_expense_date` (`expenseDate`),
  ADD KEY `idx_submission_date` (`submissionDate`),
  ADD KEY `idx_amount` (`amount`),
  ADD KEY `idx_currency` (`currency`),
  ADD KEY `idx_approval_status` (`approvalRequired`,`approvedBy`),
  ADD KEY `idx_payment_status` (`paymentMethod`,`paymentDate`),
  ADD KEY `idx_reimbursement` (`reimbursementAmount`,`reimbursementDate`),
  ADD KEY `idx_budget` (`budgetCode`,`budgetYear`,`budgetMonth`),
  ADD KEY `idx_vendor` (`vendor`,`vendorCode`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`),
  ADD KEY `idx_created_by` (`createdBy`),
  ADD KEY `idx_created_date` (`createdDate`),
  ADD KEY `idx_suspended` (`Suspended`),
  ADD KEY `idx_deleted` (`isDeleted`),
  ADD KEY `idx_employee_status` (`employeeID`,`expenseStatusID`),
  ADD KEY `idx_date_status` (`expenseDate`,`expenseStatusID`),
  ADD KEY `idx_amount_status` (`amount`,`expenseStatusID`),
  ADD KEY `idx_approval_workflow` (`approvalRequired`,`approvalLevel`,`approvedBy`),
  ADD KEY `idx_payment_workflow` (`paymentMethod`,`paymentDate`,`paidBy`),
  ADD KEY `idx_budget_tracking` (`budgetCode`,`budgetYear`,`budgetMonth`,`amount`),
  ADD KEY `idx_vendor_tracking` (`vendor`,`vendorCode`,`expenseDate`);
ALTER TABLE `tija_expense` ADD FULLTEXT KEY `ft_description` (`description`,`shortDescription`);

--
-- Indexes for table `tija_expenses`
--
ALTER TABLE `tija_expenses`
  ADD PRIMARY KEY (`expenseID`),
  ADD UNIQUE KEY `expenseNumber` (`expenseNumber`),
  ADD KEY `employeeID` (`employeeID`),
  ADD KEY `expenseTypeID` (`expenseTypeID`),
  ADD KEY `expenseCategoryID` (`expenseCategoryID`),
  ADD KEY `expenseStatusID` (`expenseStatusID`),
  ADD KEY `projectID` (`projectID`),
  ADD KEY `clientID` (`clientID`),
  ADD KEY `salesCaseID` (`salesCaseID`),
  ADD KEY `orgDataID` (`orgDataID`),
  ADD KEY `entityID` (`entityID`),
  ADD KEY `expenseDate` (`expenseDate`),
  ADD KEY `submissionDate` (`submissionDate`);

--
-- Indexes for table `tija_expense_approvals`
--
ALTER TABLE `tija_expense_approvals`
  ADD PRIMARY KEY (`approvalID`),
  ADD KEY `expenseID` (`expenseID`),
  ADD KEY `approverID` (`approverID`),
  ADD KEY `orgDataID` (`orgDataID`),
  ADD KEY `entityID` (`entityID`);

--
-- Indexes for table `tija_expense_attachments`
--
ALTER TABLE `tija_expense_attachments`
  ADD PRIMARY KEY (`attachmentID`),
  ADD KEY `expenseID` (`expenseID`),
  ADD KEY `uploadedBy` (`uploadedBy`),
  ADD KEY `orgDataID` (`orgDataID`),
  ADD KEY `entityID` (`entityID`);

--
-- Indexes for table `tija_expense_categories`
--
ALTER TABLE `tija_expense_categories`
  ADD PRIMARY KEY (`expenseCategoryID`),
  ADD UNIQUE KEY `unique_category_code` (`categoryCode`,`orgDataID`,`entityID`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_suspended` (`Suspended`),
  ADD KEY `idx_parent_category` (`parentCategoryID`),
  ADD KEY `idx_sort_order` (`sortOrder`),
  ADD KEY `idx_created_by` (`createdBy`),
  ADD KEY `idx_created_date` (`createdDate`),
  ADD KEY `idx_category_name` (`categoryName`),
  ADD KEY `idx_category_code` (`categoryCode`),
  ADD KEY `idx_max_amount` (`maxAmount`),
  ADD KEY `idx_approval_level` (`approvalLevel`),
  ADD KEY `idx_budget_limit` (`hasBudgetLimit`);

--
-- Indexes for table `tija_expense_status`
--
ALTER TABLE `tija_expense_status`
  ADD PRIMARY KEY (`expenseStatusID`),
  ADD UNIQUE KEY `unique_status_code` (`statusCode`,`orgDataID`,`entityID`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_suspended` (`Suspended`),
  ADD KEY `idx_priority` (`statusPriority`),
  ADD KEY `idx_initial_status` (`isInitialStatus`),
  ADD KEY `idx_final_status` (`isFinalStatus`),
  ADD KEY `idx_approval_status` (`isApprovalStatus`),
  ADD KEY `idx_pending_status` (`isPendingStatus`),
  ADD KEY `idx_paid_status` (`isPaidStatus`),
  ADD KEY `idx_created_by` (`createdBy`),
  ADD KEY `idx_created_date` (`createdDate`),
  ADD KEY `idx_status_name` (`statusName`),
  ADD KEY `idx_status_code` (`statusCode`),
  ADD KEY `idx_status_color` (`statusColor`),
  ADD KEY `idx_workflow_status` (`isPendingStatus`,`isApprovalStatus`,`isPaidStatus`);

--
-- Indexes for table `tija_expense_types`
--
ALTER TABLE `tija_expense_types`
  ADD PRIMARY KEY (`expenseTypeID`),
  ADD UNIQUE KEY `typeCode` (`typeCode`),
  ADD KEY `idx_isActive` (`isActive`),
  ADD KEY `idx_requiresReceipt` (`requiresReceipt`),
  ADD KEY `idx_isReimbursable` (`isReimbursable`),
  ADD KEY `idx_isPettyCash` (`isPettyCash`),
  ADD KEY `idx_requiresApproval` (`requiresApproval`),
  ADD KEY `idx_approvalLevel` (`approvalLevel`),
  ADD KEY `idx_parentTypeID` (`parentTypeID`),
  ADD KEY `idx_typeLevel` (`typeLevel`),
  ADD KEY `idx_sortOrder` (`sortOrder`),
  ADD KEY `idx_isTaxable` (`isTaxable`),
  ADD KEY `idx_defaultCurrency` (`defaultCurrency`),
  ADD KEY `orgDataID` (`orgDataID`),
  ADD KEY `entityID` (`entityID`),
  ADD KEY `fk_expense_types_last_updated_by` (`lastUpdatedBy`);

--
-- Indexes for table `tija_financial_statements`
--
ALTER TABLE `tija_financial_statements`
  ADD PRIMARY KEY (`financialStatementID`);

--
-- Indexes for table `tija_financial_statements_types`
--
ALTER TABLE `tija_financial_statements_types`
  ADD PRIMARY KEY (`financialStatementTypeID`);

--
-- Indexes for table `tija_financial_statement_accounts`
--
ALTER TABLE `tija_financial_statement_accounts`
  ADD PRIMARY KEY (`financialStatementAccountID`);

--
-- Indexes for table `tija_financial_statement_data`
--
ALTER TABLE `tija_financial_statement_data`
  ADD PRIMARY KEY (`financialStatementDataID`);

--
-- Indexes for table `tija_global_holidays`
--
ALTER TABLE `tija_global_holidays`
  ADD PRIMARY KEY (`holidayID`),
  ADD KEY `idx_holiday_date` (`holidayDate`),
  ADD KEY `idx_jurisdiction` (`jurisdiction`),
  ADD KEY `idx_holiday_type` (`holidayType`);

--
-- Indexes for table `tija_holidays`
--
ALTER TABLE `tija_holidays`
  ADD PRIMARY KEY (`holidayID`),
  ADD KEY `idx_jurisdiction` (`jurisdictionLevel`),
  ADD KEY `idx_affects_balance` (`affectsLeaveBalance`),
  ADD KEY `idx_generated_from` (`generatedFrom`);

--
-- Indexes for table `tija_holiday_audit_log`
--
ALTER TABLE `tija_holiday_audit_log`
  ADD PRIMARY KEY (`auditID`),
  ADD KEY `idx_holiday` (`holidayID`),
  ADD KEY `idx_performed_by` (`performedByID`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `tija_industries`
--
ALTER TABLE `tija_industries`
  ADD PRIMARY KEY (`industryID`);

--
-- Indexes for table `tija_industry_sectors`
--
ALTER TABLE `tija_industry_sectors`
  ADD PRIMARY KEY (`sectorID`);

--
-- Indexes for table `tija_investment_mapped_accounts`
--
ALTER TABLE `tija_investment_mapped_accounts`
  ADD PRIMARY KEY (`investmentMappedAccountID`);

--
-- Indexes for table `tija_invoices`
--
ALTER TABLE `tija_invoices`
  ADD PRIMARY KEY (`invoiceID`),
  ADD KEY `idx_invoice_number` (`invoiceNumber`),
  ADD KEY `idx_client_id` (`clientID`),
  ADD KEY `idx_sales_case_id` (`salesCaseID`),
  ADD KEY `idx_project_id` (`projectID`),
  ADD KEY `idx_invoice_date` (`invoiceDate`),
  ADD KEY `idx_due_date` (`dueDate`),
  ADD KEY `idx_invoice_status` (`invoiceStatusID`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`),
  ADD KEY `idx_date_added` (`DateAdded`),
  ADD KEY `idx_suspended_lapsed` (`Suspended`,`Lapsed`),
  ADD KEY `idx_client_date` (`clientID`,`invoiceDate`),
  ADD KEY `idx_org_entity_date` (`orgDataID`,`entityID`,`invoiceDate`),
  ADD KEY `idx_status_date` (`invoiceStatusID`,`invoiceDate`),
  ADD KEY `idx_template` (`templateID`),
  ADD KEY `idx_sent_date` (`sentDate`),
  ADD KEY `idx_paid_date` (`paidDate`);

--
-- Indexes for table `tija_invoice_expenses`
--
ALTER TABLE `tija_invoice_expenses`
  ADD PRIMARY KEY (`mappingID`),
  ADD KEY `idx_invoice_item` (`invoiceItemID`),
  ADD KEY `idx_expense` (`expenseID`),
  ADD KEY `idx_fee_expense` (`feeExpenseID`);

--
-- Indexes for table `tija_invoice_items`
--
ALTER TABLE `tija_invoice_items`
  ADD PRIMARY KEY (`invoiceItemID`),
  ADD KEY `idx_invoice` (`invoiceID`),
  ADD KEY `idx_item_type` (`itemType`),
  ADD KEY `idx_reference` (`itemReferenceID`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_invoice_licenses`
--
ALTER TABLE `tija_invoice_licenses`
  ADD PRIMARY KEY (`licenseID`),
  ADD KEY `idx_client` (`clientID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_renewal` (`renewalDate`),
  ADD KEY `idx_active` (`isActive`);

--
-- Indexes for table `tija_invoice_payments`
--
ALTER TABLE `tija_invoice_payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD UNIQUE KEY `paymentNumber` (`paymentNumber`),
  ADD KEY `idx_invoice` (`invoiceID`),
  ADD KEY `idx_payment_number` (`paymentNumber`),
  ADD KEY `idx_payment_date` (`paymentDate`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`);

--
-- Indexes for table `tija_invoice_status`
--
ALTER TABLE `tija_invoice_status`
  ADD PRIMARY KEY (`statusID`),
  ADD UNIQUE KEY `unique_status_name` (`statusName`),
  ADD KEY `idx_active_sort` (`isActive`,`sortOrder`);

--
-- Indexes for table `tija_invoice_templates`
--
ALTER TABLE `tija_invoice_templates`
  ADD PRIMARY KEY (`templateID`),
  ADD UNIQUE KEY `templateCode` (`templateCode`),
  ADD KEY `idx_template_code` (`templateCode`),
  ADD KEY `idx_template_type` (`templateType`),
  ADD KEY `idx_is_default` (`isDefault`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_invoice_work_hours`
--
ALTER TABLE `tija_invoice_work_hours`
  ADD PRIMARY KEY (`mappingID`),
  ADD UNIQUE KEY `unique_item_timelog` (`invoiceItemID`,`timelogID`),
  ADD KEY `idx_invoice_item` (`invoiceItemID`),
  ADD KEY `idx_timelog` (`timelogID`);

--
-- Indexes for table `tija_job_bands`
--
ALTER TABLE `tija_job_bands`
  ADD PRIMARY KEY (`jobBandID`);

--
-- Indexes for table `tija_job_categories`
--
ALTER TABLE `tija_job_categories`
  ADD PRIMARY KEY (`jobCategoryID`);

--
-- Indexes for table `tija_job_titles`
--
ALTER TABLE `tija_job_titles`
  ADD PRIMARY KEY (`jobTitleID`);

--
-- Indexes for table `tija_job_title_pay_grade`
--
ALTER TABLE `tija_job_title_pay_grade`
  ADD PRIMARY KEY (`mappingID`),
  ADD UNIQUE KEY `idx_unique_current` (`jobTitleID`,`isCurrent`,`Suspended`),
  ADD KEY `idx_job_title` (`jobTitleID`),
  ADD KEY `idx_pay_grade` (`payGradeID`),
  ADD KEY `idx_current` (`isCurrent`);

--
-- Indexes for table `tija_lead_sources`
--
ALTER TABLE `tija_lead_sources`
  ADD PRIMARY KEY (`leadSourceID`);

--
-- Indexes for table `tija_leave_accumulation_history`
--
ALTER TABLE `tija_leave_accumulation_history`
  ADD PRIMARY KEY (`historyID`),
  ADD KEY `idx_employee_period` (`employeeID`,`accrualPeriod`),
  ADD KEY `idx_policy_history` (`policyID`,`accrualDate`),
  ADD KEY `idx_leave_type_date` (`leaveTypeID`,`accrualDate`),
  ADD KEY `idx_accrual_date` (`accrualDate`),
  ADD KEY `ruleID` (`ruleID`);

--
-- Indexes for table `tija_leave_accumulation_policies`
--
ALTER TABLE `tija_leave_accumulation_policies`
  ADD PRIMARY KEY (`policyID`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_leave_type` (`leaveTypeID`),
  ADD KEY `idx_accrual_type` (`accrualType`),
  ADD KEY `idx_policy_scope` (`policyScope`,`entityID`,`jobCategoryID`,`jobBandID`);

--
-- Indexes for table `tija_leave_accumulation_rules`
--
ALTER TABLE `tija_leave_accumulation_rules`
  ADD PRIMARY KEY (`ruleID`),
  ADD KEY `idx_policy_rules` (`policyID`,`Lapsed`),
  ADD KEY `idx_rule_type` (`ruleType`);

--
-- Indexes for table `tija_leave_applications`
--
ALTER TABLE `tija_leave_applications`
  ADD PRIMARY KEY (`leaveApplicationID`),
  ADD KEY `idx_employee_date` (`employeeID`,`startDate`),
  ADD KEY `idx_status_date` (`leaveStatusID`,`startDate`),
  ADD KEY `idx_leave_type` (`leaveTypeID`),
  ADD KEY `idx_created_by` (`createdBy`),
  ADD KEY `idx_created_date` (`createdDate`),
  ADD KEY `idx_modified_by` (`modifiedBy`),
  ADD KEY `idx_modified_date` (`modifiedDate`);

--
-- Indexes for table `tija_leave_approvals`
--
ALTER TABLE `tija_leave_approvals`
  ADD PRIMARY KEY (`leaveApprovalID`);

--
-- Indexes for table `tija_leave_approval_actions`
--
ALTER TABLE `tija_leave_approval_actions`
  ADD PRIMARY KEY (`actionID`),
  ADD KEY `idx_instance` (`instanceID`),
  ADD KEY `idx_approver` (`approverID`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_date` (`actionDate`),
  ADD KEY `idx_action_pending` (`instanceID`,`action`,`actionDate`),
  ADD KEY `idx_actions_instance_step_approver` (`instanceID`,`stepID`,`approverUserID`);

--
-- Indexes for table `tija_leave_approval_comments`
--
ALTER TABLE `tija_leave_approval_comments`
  ADD PRIMARY KEY (`commentID`),
  ADD KEY `idx_comments_application` (`leaveApplicationID`),
  ADD KEY `idx_comments_approver` (`approverUserID`);

--
-- Indexes for table `tija_leave_approval_instances`
--
ALTER TABLE `tija_leave_approval_instances`
  ADD PRIMARY KEY (`instanceID`),
  ADD KEY `idx_application` (`leaveApplicationID`),
  ADD KEY `idx_policy` (`policyID`),
  ADD KEY `idx_status` (`workflowStatus`),
  ADD KEY `idx_instance_status` (`workflowStatus`,`currentStepOrder`);

--
-- Indexes for table `tija_leave_approval_policies`
--
ALTER TABLE `tija_leave_approval_policies`
  ADD PRIMARY KEY (`policyID`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_orgdata` (`orgDataID`),
  ADD KEY `idx_active` (`isActive`,`Suspended`,`Lapsed`),
  ADD KEY `idx_policy_entity` (`entityID`,`isActive`),
  ADD KEY `idx_policy_default` (`entityID`,`isDefault`);

--
-- Indexes for table `tija_leave_approval_steps`
--
ALTER TABLE `tija_leave_approval_steps`
  ADD PRIMARY KEY (`stepID`),
  ADD KEY `idx_policy` (`policyID`),
  ADD KEY `idx_order` (`stepOrder`),
  ADD KEY `idx_step_policy_order` (`policyID`,`stepOrder`);

--
-- Indexes for table `tija_leave_approval_step_approvers`
--
ALTER TABLE `tija_leave_approval_step_approvers`
  ADD PRIMARY KEY (`approverID`),
  ADD KEY `idx_step` (`stepID`),
  ADD KEY `idx_user` (`approverUserID`);

--
-- Indexes for table `tija_leave_audit_log`
--
ALTER TABLE `tija_leave_audit_log`
  ADD PRIMARY KEY (`auditID`),
  ADD KEY `idx_entity` (`entityType`,`entityID`),
  ADD KEY `idx_performed_by` (`performedByID`),
  ADD KEY `idx_performed_date` (`performedDate`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `tija_leave_blackout_periods`
--
ALTER TABLE `tija_leave_blackout_periods`
  ADD PRIMARY KEY (`blackoutID`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_date_range` (`startDate`,`endDate`),
  ADD KEY `idx_severity` (`severity`);

--
-- Indexes for table `tija_leave_entitlement`
--
ALTER TABLE `tija_leave_entitlement`
  ADD PRIMARY KEY (`leaveEntitlementID`),
  ADD KEY `idx_entity_type` (`entityID`,`leaveTypeID`),
  ADD KEY `idx_policy_scope` (`policyScope`,`entityID`,`jobCategoryID`,`jobBandID`);

--
-- Indexes for table `tija_leave_periods`
--
ALTER TABLE `tija_leave_periods`
  ADD PRIMARY KEY (`leavePeriodID`),
  ADD KEY `idx_entity_period` (`entityID`,`leavePeriodStartDate`,`leavePeriodEndDate`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`);

--
-- Indexes for table `tija_leave_project_clearances`
--
ALTER TABLE `tija_leave_project_clearances`
  ADD PRIMARY KEY (`clearanceID`),
  ADD KEY `idx_leave_application` (`leaveApplicationID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_project_manager` (`projectManagerID`),
  ADD KEY `idx_clearance_status` (`clearanceStatus`);

--
-- Indexes for table `tija_leave_status`
--
ALTER TABLE `tija_leave_status`
  ADD PRIMARY KEY (`leaveStatusID`);

--
-- Indexes for table `tija_leave_types`
--
ALTER TABLE `tija_leave_types`
  ADD PRIMARY KEY (`leaveTypeID`);

--
-- Indexes for table `tija_leave_workflow_templates`
--
ALTER TABLE `tija_leave_workflow_templates`
  ADD PRIMARY KEY (`templateID`),
  ADD KEY `idx_public` (`isPublic`,`Suspended`);

--
-- Indexes for table `tija_leave_workflow_template_steps`
--
ALTER TABLE `tija_leave_workflow_template_steps`
  ADD PRIMARY KEY (`templateStepID`),
  ADD KEY `idx_template` (`templateID`);

--
-- Indexes for table `tija_licenses`
--
ALTER TABLE `tija_licenses`
  ADD PRIMARY KEY (`licenseID`),
  ADD UNIQUE KEY `licenseKey` (`licenseKey`),
  ADD KEY `idx_orgDataID` (`orgDataID`),
  ADD KEY `idx_licenseStatus` (`licenseStatus`),
  ADD KEY `idx_licenseExpiryDate` (`licenseExpiryDate`),
  ADD KEY `idx_licenseKey` (`licenseKey`);

--
-- Indexes for table `tija_license_types`
--
ALTER TABLE `tija_license_types`
  ADD PRIMARY KEY (`licenseTypeID`),
  ADD UNIQUE KEY `licenseTypeCode` (`licenseTypeCode`),
  ADD UNIQUE KEY `idx_licenseTypeCode` (`licenseTypeCode`),
  ADD KEY `idx_displayOrder` (`displayOrder`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_name_prefixes`
--
ALTER TABLE `tija_name_prefixes`
  ADD PRIMARY KEY (`prefixID`);

--
-- Indexes for table `tija_notifications`
--
ALTER TABLE `tija_notifications`
  ADD PRIMARY KEY (`notificationID`);

--
-- Indexes for table `tija_notifications_enhanced`
--
ALTER TABLE `tija_notifications_enhanced`
  ADD PRIMARY KEY (`notificationID`),
  ADD KEY `idx_user` (`userID`,`status`),
  ADD KEY `idx_event` (`eventID`),
  ADD KEY `idx_originator` (`originatorUserID`),
  ADD KEY `idx_segment` (`segmentType`,`segmentID`),
  ADD KEY `idx_date` (`DateAdded`),
  ADD KEY `idx_entity` (`entityID`,`orgDataID`);

--
-- Indexes for table `tija_notification_channels`
--
ALTER TABLE `tija_notification_channels`
  ADD PRIMARY KEY (`channelID`),
  ADD UNIQUE KEY `channelSlug` (`channelSlug`),
  ADD UNIQUE KEY `idx_channel_slug` (`channelSlug`);

--
-- Indexes for table `tija_notification_entity_preferences`
--
ALTER TABLE `tija_notification_entity_preferences`
  ADD PRIMARY KEY (`entityPreferenceID`),
  ADD UNIQUE KEY `unique_entity_event_channel` (`entityID`,`eventID`,`channelID`),
  ADD KEY `idx_entity_pref_entity` (`entityID`),
  ADD KEY `idx_entity_pref_event` (`eventID`),
  ADD KEY `idx_entity_pref_channel` (`channelID`);

--
-- Indexes for table `tija_notification_events`
--
ALTER TABLE `tija_notification_events`
  ADD PRIMARY KEY (`eventID`),
  ADD UNIQUE KEY `unique_event_slug` (`eventSlug`,`moduleID`),
  ADD KEY `idx_module` (`moduleID`),
  ADD KEY `idx_event_slug` (`eventSlug`);

--
-- Indexes for table `tija_notification_logs`
--
ALTER TABLE `tija_notification_logs`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `idx_notification` (`notificationID`),
  ADD KEY `idx_queue` (`queueID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `idx_date` (`DateAdded`);

--
-- Indexes for table `tija_notification_modules`
--
ALTER TABLE `tija_notification_modules`
  ADD PRIMARY KEY (`moduleID`),
  ADD UNIQUE KEY `moduleSlug` (`moduleSlug`),
  ADD KEY `idx_module_slug` (`moduleSlug`),
  ADD KEY `idx_active` (`isActive`,`Suspended`);

--
-- Indexes for table `tija_notification_preferences`
--
ALTER TABLE `tija_notification_preferences`
  ADD PRIMARY KEY (`preferenceID`),
  ADD UNIQUE KEY `unique_preference` (`userID`,`eventID`,`channelID`),
  ADD KEY `idx_user` (`userID`),
  ADD KEY `eventID` (`eventID`),
  ADD KEY `channelID` (`channelID`);

--
-- Indexes for table `tija_notification_queue`
--
ALTER TABLE `tija_notification_queue`
  ADD PRIMARY KEY (`queueID`),
  ADD KEY `idx_notification` (`notificationID`),
  ADD KEY `idx_status` (`status`,`scheduledFor`),
  ADD KEY `idx_channel` (`channelID`);

--
-- Indexes for table `tija_notification_templates`
--
ALTER TABLE `tija_notification_templates`
  ADD PRIMARY KEY (`templateID`),
  ADD KEY `idx_event` (`eventID`),
  ADD KEY `idx_channel` (`channelID`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_active` (`isActive`,`Suspended`);

--
-- Indexes for table `tija_notification_template_variables`
--
ALTER TABLE `tija_notification_template_variables`
  ADD PRIMARY KEY (`variableID`),
  ADD UNIQUE KEY `unique_variable` (`moduleID`,`variableSlug`);

--
-- Indexes for table `tija_organisation_data`
--
ALTER TABLE `tija_organisation_data`
  ADD PRIMARY KEY (`orgDataID`),
  ADD UNIQUE KEY `orgPIN` (`orgPIN`);

--
-- Indexes for table `tija_organisation_roles`
--
ALTER TABLE `tija_organisation_roles`
  ADD PRIMARY KEY (`orgRoleID`);

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
-- Indexes for table `tija_org_role_types`
--
ALTER TABLE `tija_org_role_types`
  ADD PRIMARY KEY (`roleTypeID`),
  ADD UNIQUE KEY `unique_roleTypeCode` (`roleTypeCode`),
  ADD KEY `idx_isActive` (`isActive`),
  ADD KEY `idx_Suspended` (`Suspended`),
  ADD KEY `idx_displayOrder` (`displayOrder`);

--
-- Indexes for table `tija_overtime_multiplier`
--
ALTER TABLE `tija_overtime_multiplier`
  ADD PRIMARY KEY (`overtimeMultiplierID`);

--
-- Indexes for table `tija_payroll_computation_rules`
--
ALTER TABLE `tija_payroll_computation_rules`
  ADD PRIMARY KEY (`ruleID`),
  ADD KEY `idx_entity` (`entityID`);

--
-- Indexes for table `tija_pay_grades`
--
ALTER TABLE `tija_pay_grades`
  ADD PRIMARY KEY (`payGradeID`),
  ADD UNIQUE KEY `idx_unique_grade` (`orgDataID`,`entityID`,`payGradeCode`,`Suspended`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_level` (`gradeLevel`),
  ADD KEY `idx_active` (`Suspended`);

--
-- Indexes for table `tija_permission_levels`
--
ALTER TABLE `tija_permission_levels`
  ADD PRIMARY KEY (`permissionLevelID`);

--
-- Indexes for table `tija_permission_profiles`
--
ALTER TABLE `tija_permission_profiles`
  ADD PRIMARY KEY (`permissionProfileID`);

--
-- Indexes for table `tija_permission_roles`
--
ALTER TABLE `tija_permission_roles`
  ADD PRIMARY KEY (`permissionRoleID`);

--
-- Indexes for table `tija_permission_scopes`
--
ALTER TABLE `tija_permission_scopes`
  ADD PRIMARY KEY (`permissionScopeID`);

--
-- Indexes for table `tija_permission_types`
--
ALTER TABLE `tija_permission_types`
  ADD PRIMARY KEY (`permissionTypeID`);

--
-- Indexes for table `tija_pms_work_segment`
--
ALTER TABLE `tija_pms_work_segment`
  ADD PRIMARY KEY (`workSegmentID`);

--
-- Indexes for table `tija_products`
--
ALTER TABLE `tija_products`
  ADD PRIMARY KEY (`productID`);

--
-- Indexes for table `tija_product_billing_period_levels`
--
ALTER TABLE `tija_product_billing_period_levels`
  ADD PRIMARY KEY (`productBillingPeriodLevelID`);

--
-- Indexes for table `tija_product_rates`
--
ALTER TABLE `tija_product_rates`
  ADD PRIMARY KEY (`productRateID`);

--
-- Indexes for table `tija_product_rate_types`
--
ALTER TABLE `tija_product_rate_types`
  ADD PRIMARY KEY (`productRateTypeID`);

--
-- Indexes for table `tija_product_types`
--
ALTER TABLE `tija_product_types`
  ADD PRIMARY KEY (`productTypeID`);

--
-- Indexes for table `tija_projects`
--
ALTER TABLE `tija_projects`
  ADD PRIMARY KEY (`projectID`),
  ADD KEY `idx_recurring` (`isRecurring`,`recurrenceType`);

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
-- Indexes for table `tija_project_expenses`
--
ALTER TABLE `tija_project_expenses`
  ADD PRIMARY KEY (`expenseID`);

--
-- Indexes for table `tija_project_fee_expenses`
--
ALTER TABLE `tija_project_fee_expenses`
  ADD PRIMARY KEY (`projectFeeExpenseID`);

--
-- Indexes for table `tija_project_files`
--
ALTER TABLE `tija_project_files`
  ADD PRIMARY KEY (`fileID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_uploader` (`uploadedBy`),
  ADD KEY `idx_task` (`taskID`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_project_memeber_categories`
--
ALTER TABLE `tija_project_memeber_categories`
  ADD PRIMARY KEY (`projectTeamMemeberCategoryID`);

--
-- Indexes for table `tija_project_phases`
--
ALTER TABLE `tija_project_phases`
  ADD PRIMARY KEY (`projectPhaseID`),
  ADD KEY `idx_billing_cycle` (`billingCycleID`);

--
-- Indexes for table `tija_project_plan_templates`
--
ALTER TABLE `tija_project_plan_templates`
  ADD PRIMARY KEY (`templateID`),
  ADD KEY `idx_org_entity` (`orgDataID`,`entityID`),
  ADD KEY `idx_creator` (`createdByID`),
  ADD KEY `idx_public` (`isPublic`,`isActive`),
  ADD KEY `idx_category` (`templateCategory`),
  ADD KEY `idx_template_search` (`templateName`,`orgDataID`,`isActive`),
  ADD KEY `idx_template_usage` (`usageCount` DESC,`lastUsedDate` DESC);

--
-- Indexes for table `tija_project_plan_template_phases`
--
ALTER TABLE `tija_project_plan_template_phases`
  ADD PRIMARY KEY (`templatePhaseID`),
  ADD KEY `idx_template` (`templateID`),
  ADD KEY `idx_order` (`templateID`,`phaseOrder`);

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
  ADD UNIQUE KEY `projectTaskCode` (`projectTaskCode`),
  ADD KEY `idx_billing_cycle` (`billingCycleID`);

--
-- Indexes for table `tija_project_task_types`
--
ALTER TABLE `tija_project_task_types`
  ADD PRIMARY KEY (`projectTaskTypeID`);

--
-- Indexes for table `tija_project_team`
--
ALTER TABLE `tija_project_team`
  ADD PRIMARY KEY (`projectTeamMemberID`);

--
-- Indexes for table `tija_project_team_roles`
--
ALTER TABLE `tija_project_team_roles`
  ADD PRIMARY KEY (`projectTeamRoleID`);

--
-- Indexes for table `tija_project_types`
--
ALTER TABLE `tija_project_types`
  ADD PRIMARY KEY (`projectTypeID`);

--
-- Indexes for table `tija_proposals`
--
ALTER TABLE `tija_proposals`
  ADD PRIMARY KEY (`proposalID`),
  ADD UNIQUE KEY `proposalCode` (`proposalCode`);

--
-- Indexes for table `tija_proposal_activities`
--
ALTER TABLE `tija_proposal_activities`
  ADD PRIMARY KEY (`proposalActivityID`);

--
-- Indexes for table `tija_proposal_attachments`
--
ALTER TABLE `tija_proposal_attachments`
  ADD PRIMARY KEY (`proposalAttachmentID`);

--
-- Indexes for table `tija_proposal_checklists`
--
ALTER TABLE `tija_proposal_checklists`
  ADD PRIMARY KEY (`proposalChecklistID`);

--
-- Indexes for table `tija_proposal_checklist_items`
--
ALTER TABLE `tija_proposal_checklist_items`
  ADD PRIMARY KEY (`proposalChecklistItemID`);

--
-- Indexes for table `tija_proposal_checklist_item_assignment`
--
ALTER TABLE `tija_proposal_checklist_item_assignment`
  ADD PRIMARY KEY (`proposalChecklistItemAssignmentID`);

--
-- Indexes for table `tija_proposal_checklist_item_assignment_submissions`
--
ALTER TABLE `tija_proposal_checklist_item_assignment_submissions`
  ADD PRIMARY KEY (`proposalChecklistItemAssignmentSubmissionID`);

--
-- Indexes for table `tija_proposal_checklist_item_categories`
--
ALTER TABLE `tija_proposal_checklist_item_categories`
  ADD PRIMARY KEY (`proposalChecklistItemCategoryID`);

--
-- Indexes for table `tija_proposal_checklist_item_submissions`
--
ALTER TABLE `tija_proposal_checklist_item_submissions`
  ADD PRIMARY KEY (`submissionID`),
  ADD KEY `idx_assignment` (`proposalChecklistItemAssignmentID`),
  ADD KEY `idx_submitted_by` (`submittedBy`),
  ADD KEY `idx_status` (`submissionStatus`),
  ADD KEY `idx_reviewed_by` (`reviewedBy`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_proposal_checklist_status`
--
ALTER TABLE `tija_proposal_checklist_status`
  ADD PRIMARY KEY (`proposalChecklistStatusID`);

--
-- Indexes for table `tija_proposal_statuses`
--
ALTER TABLE `tija_proposal_statuses`
  ADD PRIMARY KEY (`proposalStatusID`);

--
-- Indexes for table `tija_proposal_status_categories`
--
ALTER TABLE `tija_proposal_status_categories`
  ADD PRIMARY KEY (`proposalStatusCategoryID`);

--
-- Indexes for table `tija_proposal_status_stages`
--
ALTER TABLE `tija_proposal_status_stages`
  ADD PRIMARY KEY (`stageID`),
  ADD UNIQUE KEY `stageCode` (`stageCode`),
  ADD KEY `idx_stage_code` (`stageCode`),
  ADD KEY `idx_stage_order` (`stageOrder`),
  ADD KEY `idx_active` (`isActive`);

--
-- Indexes for table `tija_proposal_tasks`
--
ALTER TABLE `tija_proposal_tasks`
  ADD PRIMARY KEY (`proposalTaskID`),
  ADD KEY `idx_proposal` (`proposalID`),
  ADD KEY `idx_assigned_to` (`assignedTo`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_mandatory` (`isMandatory`),
  ADD KEY `idx_due_date` (`dueDate`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_recurring_activity_instances`
--
ALTER TABLE `tija_recurring_activity_instances`
  ADD PRIMARY KEY (`recurringInstanceID`);

--
-- Indexes for table `tija_recurring_project_billing_cycles`
--
ALTER TABLE `tija_recurring_project_billing_cycles`
  ADD PRIMARY KEY (`billingCycleID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_billing_date` (`billingDate`),
  ADD KEY `idx_due_date` (`dueDate`),
  ADD KEY `idx_cycle_dates` (`cycleStartDate`,`cycleEndDate`);

--
-- Indexes for table `tija_recurring_project_plan_cycle_config`
--
ALTER TABLE `tija_recurring_project_plan_cycle_config`
  ADD PRIMARY KEY (`configID`),
  ADD UNIQUE KEY `idx_unique_phase_cycle` (`templatePhaseID`,`billingCycleID`),
  ADD UNIQUE KEY `idx_unique_task_cycle` (`templateTaskID`,`billingCycleID`),
  ADD KEY `idx_project_cycle` (`projectID`,`billingCycleID`),
  ADD KEY `idx_template_phase` (`templatePhaseID`),
  ADD KEY `idx_template_task` (`templateTaskID`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_recurring_project_plan_instances`
--
ALTER TABLE `tija_recurring_project_plan_instances`
  ADD PRIMARY KEY (`planInstanceID`),
  ADD KEY `idx_cycle` (`billingCycleID`),
  ADD KEY `idx_project` (`projectID`);

--
-- Indexes for table `tija_recurring_project_plan_task_templates`
--
ALTER TABLE `tija_recurring_project_plan_task_templates`
  ADD PRIMARY KEY (`templateTaskID`),
  ADD KEY `idx_template_phase` (`templatePhaseID`),
  ADD KEY `idx_original_task` (`originalTaskID`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_recurring_project_plan_templates`
--
ALTER TABLE `tija_recurring_project_plan_templates`
  ADD PRIMARY KEY (`templatePhaseID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_original_phase` (`originalPhaseID`),
  ADD KEY `idx_order` (`projectID`,`phaseOrder`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_recurring_project_team_assignments`
--
ALTER TABLE `tija_recurring_project_team_assignments`
  ADD PRIMARY KEY (`teamAssignmentID`),
  ADD KEY `idx_cycle` (`billingCycleID`),
  ADD KEY `idx_project` (`projectID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `tija_reporting_hierarchy_cache`
--
ALTER TABLE `tija_reporting_hierarchy_cache`
  ADD PRIMARY KEY (`cacheID`),
  ADD UNIQUE KEY `idx_employee_ancestor` (`employeeID`,`ancestorID`),
  ADD KEY `idx_ancestor` (`ancestorID`),
  ADD KEY `idx_org` (`orgDataID`);

--
-- Indexes for table `tija_reporting_matrix`
--
ALTER TABLE `tija_reporting_matrix`
  ADD PRIMARY KEY (`matrixID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_functional` (`functionalSupervisorID`),
  ADD KEY `idx_project` (`projectSupervisorID`),
  ADD KEY `idx_current` (`isCurrent`);

--
-- Indexes for table `tija_reporting_relationships`
--
ALTER TABLE `tija_reporting_relationships`
  ADD PRIMARY KEY (`relationshipID`),
  ADD KEY `idx_employee` (`employeeID`),
  ADD KEY `idx_supervisor` (`supervisorID`),
  ADD KEY `idx_org` (`orgDataID`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_current` (`isCurrent`),
  ADD KEY `idx_dates` (`effectiveDate`,`endDate`);

--
-- Indexes for table `tija_roles`
--
ALTER TABLE `tija_roles`
  ADD PRIMARY KEY (`roleID`),
  ADD KEY `idx_org` (`orgDataID`),
  ADD KEY `idx_entity` (`entityID`),
  ADD KEY `idx_parent` (`parentRoleID`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_level` (`roleLevel`),
  ADD KEY `idx_roleTypeID` (`roleTypeID`),
  ADD KEY `idx_roleLevelID` (`roleLevelID`);

--
-- Indexes for table `tija_role_levels`
--
ALTER TABLE `tija_role_levels`
  ADD PRIMARY KEY (`roleLevelID`),
  ADD UNIQUE KEY `unique_levelNumber` (`levelNumber`),
  ADD UNIQUE KEY `unique_levelCode` (`levelCode`),
  ADD KEY `idx_isActive` (`isActive`),
  ADD KEY `idx_Suspended` (`Suspended`),
  ADD KEY `idx_displayOrder` (`displayOrder`),
  ADD KEY `idx_levelNumber` (`levelNumber`);

--
-- Indexes for table `tija_role_types`
--
ALTER TABLE `tija_role_types`
  ADD PRIMARY KEY (`roleTypeID`);

--
-- Indexes for table `tija_salary_components`
--
ALTER TABLE `tija_salary_components`
  ADD PRIMARY KEY (`salaryComponentID`);

--
-- Indexes for table `tija_salary_component_category`
--
ALTER TABLE `tija_salary_component_category`
  ADD PRIMARY KEY (`salaryComponentCategoryID`),
  ADD UNIQUE KEY `unique_category_code_entity` (`categoryCode`,`entityID`);

--
-- Indexes for table `tija_salary_component_history`
--
ALTER TABLE `tija_salary_component_history`
  ADD PRIMARY KEY (`historyID`),
  ADD KEY `idx_component` (`salaryComponentID`);

--
-- Indexes for table `tija_sales_activities`
--
ALTER TABLE `tija_sales_activities`
  ADD PRIMARY KEY (`salesActivityID`);

--
-- Indexes for table `tija_sales_cases`
--
ALTER TABLE `tija_sales_cases`
  ADD PRIMARY KEY (`salesCaseID`);

--
-- Indexes for table `tija_sales_documents`
--
ALTER TABLE `tija_sales_documents`
  ADD PRIMARY KEY (`documentID`),
  ADD KEY `idx_sales_case` (`salesCaseID`),
  ADD KEY `idx_proposal` (`proposalID`),
  ADD KEY `idx_category` (`documentCategory`),
  ADD KEY `idx_uploader` (`uploadedBy`),
  ADD KEY `idx_expense` (`expenseID`),
  ADD KEY `idx_approval` (`requiresApproval`,`approvalStatus`),
  ADD KEY `idx_confidential` (`isConfidential`),
  ADD KEY `idx_suspended` (`Suspended`);

--
-- Indexes for table `tija_sales_progress`
--
ALTER TABLE `tija_sales_progress`
  ADD PRIMARY KEY (`salesProgressID`);

--
-- Indexes for table `tija_sales_prospects`
--
ALTER TABLE `tija_sales_prospects`
  ADD PRIMARY KEY (`salesProspectID`);

--
-- Indexes for table `tija_sales_status_levels`
--
ALTER TABLE `tija_sales_status_levels`
  ADD PRIMARY KEY (`saleStatusLevelID`);

--
-- Indexes for table `tija_statement_of_investment_allowance_accounts`
--
ALTER TABLE `tija_statement_of_investment_allowance_accounts`
  ADD PRIMARY KEY (`investmentAllowanceAccountID`);

--
-- Indexes for table `tija_statement_of_investment_allowance_data`
--
ALTER TABLE `tija_statement_of_investment_allowance_data`
  ADD PRIMARY KEY (`InvestmentAllowanceID`);

--
-- Indexes for table `tija_subtasks`
--
ALTER TABLE `tija_subtasks`
  ADD PRIMARY KEY (`subtaskID`);

--
-- Indexes for table `tija_tasks_time_logs`
--
ALTER TABLE `tija_tasks_time_logs`
  ADD PRIMARY KEY (`timelogID`),
  ADD KEY `idx_billing_cycle` (`billingCycleID`);

--
-- Indexes for table `tija_task_files`
--
ALTER TABLE `tija_task_files`
  ADD PRIMARY KEY (`taskFileID`);

--
-- Indexes for table `tija_task_status`
--
ALTER TABLE `tija_task_status`
  ADD PRIMARY KEY (`taskStatusID`);

--
-- Indexes for table `tija_task_status_change_log`
--
ALTER TABLE `tija_task_status_change_log`
  ADD PRIMARY KEY (`taskStatusChangeID`);

--
-- Indexes for table `tija_taxable_profit`
--
ALTER TABLE `tija_taxable_profit`
  ADD PRIMARY KEY (`taxableProfitID`);

--
-- Indexes for table `tija_tax_adjustments_accounts`
--
ALTER TABLE `tija_tax_adjustments_accounts`
  ADD PRIMARY KEY (`adjustmentAccountsID`);

--
-- Indexes for table `tija_tax_adjustment_categories`
--
ALTER TABLE `tija_tax_adjustment_categories`
  ADD PRIMARY KEY (`adjustmentCategoryID`);

--
-- Indexes for table `tija_tax_adjustment_types`
--
ALTER TABLE `tija_tax_adjustment_types`
  ADD PRIMARY KEY (`adjustmentTypeID`);

--
-- Indexes for table `tija_travel_rate_types`
--
ALTER TABLE `tija_travel_rate_types`
  ADD PRIMARY KEY (`travelRateTypeID`);

--
-- Indexes for table `tija_trial_balance_mapped_accounts`
--
ALTER TABLE `tija_trial_balance_mapped_accounts`
  ADD PRIMARY KEY (`mappedAccountID`);

--
-- Indexes for table `tija_units`
--
ALTER TABLE `tija_units`
  ADD PRIMARY KEY (`unitID`),
  ADD UNIQUE KEY `UID` (`unitCode`);

--
-- Indexes for table `tija_unit_types`
--
ALTER TABLE `tija_unit_types`
  ADD PRIMARY KEY (`unitTypeID`);

--
-- Indexes for table `tija_user_unit_assignments`
--
ALTER TABLE `tija_user_unit_assignments`
  ADD PRIMARY KEY (`unitAssignmentID`);

--
-- Indexes for table `tija_withholding_tax`
--
ALTER TABLE `tija_withholding_tax`
  ADD PRIMARY KEY (`withholdingTaxID`);

--
-- Indexes for table `tija_work_categories`
--
ALTER TABLE `tija_work_categories`
  ADD PRIMARY KEY (`workCategoryID`);

--
-- Indexes for table `tija_work_types`
--
ALTER TABLE `tija_work_types`
  ADD PRIMARY KEY (`workTypeID`),
  ADD UNIQUE KEY `workTypeCode` (`workTypeCode`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD UNIQUE KEY `UID` (`UID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `african_countries`
--
ALTER TABLE `african_countries`
  MODIFY `countryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `client_relationship_assignments`
--
ALTER TABLE `client_relationship_assignments`
  MODIFY `clientRelationshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `currency`
--
ALTER TABLE `currency`
  MODIFY `currencyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `industry_sectors`
--
ALTER TABLE `industry_sectors`
  MODIFY `industrySectorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `login_sessions`
--
ALTER TABLE `login_sessions`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `registration_tokens`
--
ALTER TABLE `registration_tokens`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tija_absence_data`
--
ALTER TABLE `tija_absence_data`
  MODIFY `absenceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_absence_type`
--
ALTER TABLE `tija_absence_type`
  MODIFY `absenceTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_activities`
--
ALTER TABLE `tija_activities`
  MODIFY `activityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_activity_categories`
--
ALTER TABLE `tija_activity_categories`
  MODIFY `activityCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_activity_log`
--
ALTER TABLE `tija_activity_log`
  MODIFY `activityLogID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_activity_participant_assignment`
--
ALTER TABLE `tija_activity_participant_assignment`
  MODIFY `activityParticipantID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_activity_status`
--
ALTER TABLE `tija_activity_status`
  MODIFY `activityStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_activity_types`
--
ALTER TABLE `tija_activity_types`
  MODIFY `activityTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tija_administrators`
--
ALTER TABLE `tija_administrators`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_admin_types`
--
ALTER TABLE `tija_admin_types`
  MODIFY `adminTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_advance_tax`
--
ALTER TABLE `tija_advance_tax`
  MODIFY `advanceTaxID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_assigned_project_tasks`
--
ALTER TABLE `tija_assigned_project_tasks`
  MODIFY `assignmentTaskID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_benefit_types`
--
ALTER TABLE `tija_benefit_types`
  MODIFY `benefitTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_billing_rate`
--
ALTER TABLE `tija_billing_rate`
  MODIFY `billingRateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_billing_rates`
--
ALTER TABLE `tija_billing_rates`
  MODIFY `billingRateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tija_billing_rate_types`
--
ALTER TABLE `tija_billing_rate_types`
  MODIFY `billingRateTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_bradford_factor`
--
ALTER TABLE `tija_bradford_factor`
  MODIFY `bradfordFactorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_business_units`
--
ALTER TABLE `tija_business_units`
  MODIFY `businessUnitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_business_unit_categories`
--
ALTER TABLE `tija_business_unit_categories`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tija_cases`
--
ALTER TABLE `tija_cases`
  MODIFY `caseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_clients`
--
ALTER TABLE `tija_clients`
  MODIFY `clientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_client_addresses`
--
ALTER TABLE `tija_client_addresses`
  MODIFY `clientAddressID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_client_contacts`
--
ALTER TABLE `tija_client_contacts`
  MODIFY `clientContactID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_client_documents`
--
ALTER TABLE `tija_client_documents`
  MODIFY `clientDocumentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_client_levels`
--
ALTER TABLE `tija_client_levels`
  MODIFY `clientLevelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_client_relationship_types`
--
ALTER TABLE `tija_client_relationship_types`
  MODIFY `clientRelationshipTypeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_contact_relationships`
--
ALTER TABLE `tija_contact_relationships`
  MODIFY `relationshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_contact_types`
--
ALTER TABLE `tija_contact_types`
  MODIFY `contactTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_delegation_assignments`
--
ALTER TABLE `tija_delegation_assignments`
  MODIFY `delegationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_document_types`
--
ALTER TABLE `tija_document_types`
  MODIFY `documentTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_employee_addresses`
--
ALTER TABLE `tija_employee_addresses`
  MODIFY `addressID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_employee_allowances`
--
ALTER TABLE `tija_employee_allowances`
  MODIFY `allowanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_bank_accounts`
--
ALTER TABLE `tija_employee_bank_accounts`
  MODIFY `bankAccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_employee_bank_details`
--
ALTER TABLE `tija_employee_bank_details`
  MODIFY `bankDetailID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_benefits`
--
ALTER TABLE `tija_employee_benefits`
  MODIFY `benefitID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_certifications`
--
ALTER TABLE `tija_employee_certifications`
  MODIFY `certificationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_dependants`
--
ALTER TABLE `tija_employee_dependants`
  MODIFY `dependantID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_education`
--
ALTER TABLE `tija_employee_education`
  MODIFY `educationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_employee_emergency_contacts`
--
ALTER TABLE `tija_employee_emergency_contacts`
  MODIFY `emergencyContactID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_extended_personal`
--
ALTER TABLE `tija_employee_extended_personal`
  MODIFY `extendedPersonalID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_employee_job_history`
--
ALTER TABLE `tija_employee_job_history`
  MODIFY `jobHistoryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_licenses`
--
ALTER TABLE `tija_employee_licenses`
  MODIFY `licenseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_next_of_kin`
--
ALTER TABLE `tija_employee_next_of_kin`
  MODIFY `nextOfKinID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_employee_salary_components`
--
ALTER TABLE `tija_employee_salary_components`
  MODIFY `employeeComponentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_salary_history`
--
ALTER TABLE `tija_employee_salary_history`
  MODIFY `salaryHistoryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_skills`
--
ALTER TABLE `tija_employee_skills`
  MODIFY `skillID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_subordinates`
--
ALTER TABLE `tija_employee_subordinates`
  MODIFY `subordinateMappingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_supervisors`
--
ALTER TABLE `tija_employee_supervisors`
  MODIFY `supervisorMappingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employee_supervisor_relationships`
--
ALTER TABLE `tija_employee_supervisor_relationships`
  MODIFY `relationshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_employee_work_experience`
--
ALTER TABLE `tija_employee_work_experience`
  MODIFY `workExperienceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_employment_status`
--
ALTER TABLE `tija_employment_status`
  MODIFY `employmentStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tija_entities`
--
ALTER TABLE `tija_entities`
  MODIFY `entityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_entity_hr_assignments`
--
ALTER TABLE `tija_entity_hr_assignments`
  MODIFY `assignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_entity_role_types`
--
ALTER TABLE `tija_entity_role_types`
  MODIFY `roleTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_entity_types`
--
ALTER TABLE `tija_entity_types`
  MODIFY `entityTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_expense`
--
ALTER TABLE `tija_expense`
  MODIFY `expenseID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique expense identifier';

--
-- AUTO_INCREMENT for table `tija_expenses`
--
ALTER TABLE `tija_expenses`
  MODIFY `expenseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_expense_approvals`
--
ALTER TABLE `tija_expense_approvals`
  MODIFY `approvalID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_expense_attachments`
--
ALTER TABLE `tija_expense_attachments`
  MODIFY `attachmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_expense_categories`
--
ALTER TABLE `tija_expense_categories`
  MODIFY `expenseCategoryID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for expense category', AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tija_expense_status`
--
ALTER TABLE `tija_expense_status`
  MODIFY `expenseStatusID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for expense status', AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_expense_types`
--
ALTER TABLE `tija_expense_types`
  MODIFY `expenseTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_financial_statements`
--
ALTER TABLE `tija_financial_statements`
  MODIFY `financialStatementID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_financial_statements_types`
--
ALTER TABLE `tija_financial_statements_types`
  MODIFY `financialStatementTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tija_financial_statement_accounts`
--
ALTER TABLE `tija_financial_statement_accounts`
  MODIFY `financialStatementAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_financial_statement_data`
--
ALTER TABLE `tija_financial_statement_data`
  MODIFY `financialStatementDataID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_global_holidays`
--
ALTER TABLE `tija_global_holidays`
  MODIFY `holidayID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tija_holidays`
--
ALTER TABLE `tija_holidays`
  MODIFY `holidayID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_holiday_audit_log`
--
ALTER TABLE `tija_holiday_audit_log`
  MODIFY `auditID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_industries`
--
ALTER TABLE `tija_industries`
  MODIFY `industryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `tija_industry_sectors`
--
ALTER TABLE `tija_industry_sectors`
  MODIFY `sectorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tija_investment_mapped_accounts`
--
ALTER TABLE `tija_investment_mapped_accounts`
  MODIFY `investmentMappedAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_invoices`
--
ALTER TABLE `tija_invoices`
  MODIFY `invoiceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_invoice_expenses`
--
ALTER TABLE `tija_invoice_expenses`
  MODIFY `mappingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_invoice_items`
--
ALTER TABLE `tija_invoice_items`
  MODIFY `invoiceItemID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_invoice_licenses`
--
ALTER TABLE `tija_invoice_licenses`
  MODIFY `licenseID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_invoice_payments`
--
ALTER TABLE `tija_invoice_payments`
  MODIFY `paymentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_invoice_status`
--
ALTER TABLE `tija_invoice_status`
  MODIFY `statusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tija_invoice_templates`
--
ALTER TABLE `tija_invoice_templates`
  MODIFY `templateID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_invoice_work_hours`
--
ALTER TABLE `tija_invoice_work_hours`
  MODIFY `mappingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_job_bands`
--
ALTER TABLE `tija_job_bands`
  MODIFY `jobBandID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_job_categories`
--
ALTER TABLE `tija_job_categories`
  MODIFY `jobCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tija_job_titles`
--
ALTER TABLE `tija_job_titles`
  MODIFY `jobTitleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `tija_job_title_pay_grade`
--
ALTER TABLE `tija_job_title_pay_grade`
  MODIFY `mappingID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `tija_lead_sources`
--
ALTER TABLE `tija_lead_sources`
  MODIFY `leadSourceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_leave_accumulation_history`
--
ALTER TABLE `tija_leave_accumulation_history`
  MODIFY `historyID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_accumulation_policies`
--
ALTER TABLE `tija_leave_accumulation_policies`
  MODIFY `policyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tija_leave_accumulation_rules`
--
ALTER TABLE `tija_leave_accumulation_rules`
  MODIFY `ruleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_applications`
--
ALTER TABLE `tija_leave_applications`
  MODIFY `leaveApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_leave_approvals`
--
ALTER TABLE `tija_leave_approvals`
  MODIFY `leaveApprovalID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_approval_actions`
--
ALTER TABLE `tija_leave_approval_actions`
  MODIFY `actionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_approval_comments`
--
ALTER TABLE `tija_leave_approval_comments`
  MODIFY `commentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_approval_instances`
--
ALTER TABLE `tija_leave_approval_instances`
  MODIFY `instanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_leave_approval_policies`
--
ALTER TABLE `tija_leave_approval_policies`
  MODIFY `policyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_leave_approval_steps`
--
ALTER TABLE `tija_leave_approval_steps`
  MODIFY `stepID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_leave_approval_step_approvers`
--
ALTER TABLE `tija_leave_approval_step_approvers`
  MODIFY `approverID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_leave_audit_log`
--
ALTER TABLE `tija_leave_audit_log`
  MODIFY `auditID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_blackout_periods`
--
ALTER TABLE `tija_leave_blackout_periods`
  MODIFY `blackoutID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_entitlement`
--
ALTER TABLE `tija_leave_entitlement`
  MODIFY `leaveEntitlementID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_leave_periods`
--
ALTER TABLE `tija_leave_periods`
  MODIFY `leavePeriodID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_leave_project_clearances`
--
ALTER TABLE `tija_leave_project_clearances`
  MODIFY `clearanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_leave_status`
--
ALTER TABLE `tija_leave_status`
  MODIFY `leaveStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_leave_types`
--
ALTER TABLE `tija_leave_types`
  MODIFY `leaveTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_leave_workflow_templates`
--
ALTER TABLE `tija_leave_workflow_templates`
  MODIFY `templateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tija_leave_workflow_template_steps`
--
ALTER TABLE `tija_leave_workflow_template_steps`
  MODIFY `templateStepID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tija_licenses`
--
ALTER TABLE `tija_licenses`
  MODIFY `licenseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_license_types`
--
ALTER TABLE `tija_license_types`
  MODIFY `licenseTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_name_prefixes`
--
ALTER TABLE `tija_name_prefixes`
  MODIFY `prefixID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tija_notifications`
--
ALTER TABLE `tija_notifications`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_notifications_enhanced`
--
ALTER TABLE `tija_notifications_enhanced`
  MODIFY `notificationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `tija_notification_channels`
--
ALTER TABLE `tija_notification_channels`
  MODIFY `channelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_notification_entity_preferences`
--
ALTER TABLE `tija_notification_entity_preferences`
  MODIFY `entityPreferenceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `tija_notification_events`
--
ALTER TABLE `tija_notification_events`
  MODIFY `eventID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tija_notification_logs`
--
ALTER TABLE `tija_notification_logs`
  MODIFY `logID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `tija_notification_modules`
--
ALTER TABLE `tija_notification_modules`
  MODIFY `moduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_notification_preferences`
--
ALTER TABLE `tija_notification_preferences`
  MODIFY `preferenceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_notification_queue`
--
ALTER TABLE `tija_notification_queue`
  MODIFY `queueID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `tija_notification_templates`
--
ALTER TABLE `tija_notification_templates`
  MODIFY `templateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tija_notification_template_variables`
--
ALTER TABLE `tija_notification_template_variables`
  MODIFY `variableID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tija_organisation_data`
--
ALTER TABLE `tija_organisation_data`
  MODIFY `orgDataID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_organisation_roles`
--
ALTER TABLE `tija_organisation_roles`
  MODIFY `orgRoleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_org_charts`
--
ALTER TABLE `tija_org_charts`
  MODIFY `orgChartID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_org_chart_position_assignments`
--
ALTER TABLE `tija_org_chart_position_assignments`
  MODIFY `positionAssignmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_org_role_types`
--
ALTER TABLE `tija_org_role_types`
  MODIFY `roleTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_overtime_multiplier`
--
ALTER TABLE `tija_overtime_multiplier`
  MODIFY `overtimeMultiplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_payroll_computation_rules`
--
ALTER TABLE `tija_payroll_computation_rules`
  MODIFY `ruleID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_pay_grades`
--
ALTER TABLE `tija_pay_grades`
  MODIFY `payGradeID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_permission_levels`
--
ALTER TABLE `tija_permission_levels`
  MODIFY `permissionLevelID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_permission_profiles`
--
ALTER TABLE `tija_permission_profiles`
  MODIFY `permissionProfileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_permission_roles`
--
ALTER TABLE `tija_permission_roles`
  MODIFY `permissionRoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_permission_scopes`
--
ALTER TABLE `tija_permission_scopes`
  MODIFY `permissionScopeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_permission_types`
--
ALTER TABLE `tija_permission_types`
  MODIFY `permissionTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_pms_work_segment`
--
ALTER TABLE `tija_pms_work_segment`
  MODIFY `workSegmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_products`
--
ALTER TABLE `tija_products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tija_product_billing_period_levels`
--
ALTER TABLE `tija_product_billing_period_levels`
  MODIFY `productBillingPeriodLevelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_product_rates`
--
ALTER TABLE `tija_product_rates`
  MODIFY `productRateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_product_rate_types`
--
ALTER TABLE `tija_product_rate_types`
  MODIFY `productRateTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_product_types`
--
ALTER TABLE `tija_product_types`
  MODIFY `productTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_projects`
--
ALTER TABLE `tija_projects`
  MODIFY `projectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_project_assignments`
--
ALTER TABLE `tija_project_assignments`
  MODIFY `assignmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_expenses`
--
ALTER TABLE `tija_project_expenses`
  MODIFY `expenseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_fee_expenses`
--
ALTER TABLE `tija_project_fee_expenses`
  MODIFY `projectFeeExpenseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_files`
--
ALTER TABLE `tija_project_files`
  MODIFY `fileID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_memeber_categories`
--
ALTER TABLE `tija_project_memeber_categories`
  MODIFY `projectTeamMemeberCategoryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_phases`
--
ALTER TABLE `tija_project_phases`
  MODIFY `projectPhaseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_project_plan_templates`
--
ALTER TABLE `tija_project_plan_templates`
  MODIFY `templateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_project_plan_template_phases`
--
ALTER TABLE `tija_project_plan_template_phases`
  MODIFY `templatePhaseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `tija_project_roles`
--
ALTER TABLE `tija_project_roles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tija_project_tasks`
--
ALTER TABLE `tija_project_tasks`
  MODIFY `projectTaskID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_project_task_types`
--
ALTER TABLE `tija_project_task_types`
  MODIFY `projectTaskTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_project_team`
--
ALTER TABLE `tija_project_team`
  MODIFY `projectTeamMemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_project_team_roles`
--
ALTER TABLE `tija_project_team_roles`
  MODIFY `projectTeamRoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_project_types`
--
ALTER TABLE `tija_project_types`
  MODIFY `projectTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_proposals`
--
ALTER TABLE `tija_proposals`
  MODIFY `proposalID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_proposal_activities`
--
ALTER TABLE `tija_proposal_activities`
  MODIFY `proposalActivityID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_proposal_attachments`
--
ALTER TABLE `tija_proposal_attachments`
  MODIFY `proposalAttachmentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_proposal_checklists`
--
ALTER TABLE `tija_proposal_checklists`
  MODIFY `proposalChecklistID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_proposal_checklist_items`
--
ALTER TABLE `tija_proposal_checklist_items`
  MODIFY `proposalChecklistItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tija_proposal_checklist_item_assignment`
--
ALTER TABLE `tija_proposal_checklist_item_assignment`
  MODIFY `proposalChecklistItemAssignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tija_proposal_checklist_item_assignment_submissions`
--
ALTER TABLE `tija_proposal_checklist_item_assignment_submissions`
  MODIFY `proposalChecklistItemAssignmentSubmissionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_proposal_checklist_item_categories`
--
ALTER TABLE `tija_proposal_checklist_item_categories`
  MODIFY `proposalChecklistItemCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_proposal_checklist_item_submissions`
--
ALTER TABLE `tija_proposal_checklist_item_submissions`
  MODIFY `submissionID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_proposal_checklist_status`
--
ALTER TABLE `tija_proposal_checklist_status`
  MODIFY `proposalChecklistStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tija_proposal_statuses`
--
ALTER TABLE `tija_proposal_statuses`
  MODIFY `proposalStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_proposal_status_categories`
--
ALTER TABLE `tija_proposal_status_categories`
  MODIFY `proposalStatusCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_proposal_status_stages`
--
ALTER TABLE `tija_proposal_status_stages`
  MODIFY `stageID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_proposal_tasks`
--
ALTER TABLE `tija_proposal_tasks`
  MODIFY `proposalTaskID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_recurring_activity_instances`
--
ALTER TABLE `tija_recurring_activity_instances`
  MODIFY `recurringInstanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_recurring_project_billing_cycles`
--
ALTER TABLE `tija_recurring_project_billing_cycles`
  MODIFY `billingCycleID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_recurring_project_plan_cycle_config`
--
ALTER TABLE `tija_recurring_project_plan_cycle_config`
  MODIFY `configID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_recurring_project_plan_instances`
--
ALTER TABLE `tija_recurring_project_plan_instances`
  MODIFY `planInstanceID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_recurring_project_plan_task_templates`
--
ALTER TABLE `tija_recurring_project_plan_task_templates`
  MODIFY `templateTaskID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_recurring_project_plan_templates`
--
ALTER TABLE `tija_recurring_project_plan_templates`
  MODIFY `templatePhaseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tija_recurring_project_team_assignments`
--
ALTER TABLE `tija_recurring_project_team_assignments`
  MODIFY `teamAssignmentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_reporting_hierarchy_cache`
--
ALTER TABLE `tija_reporting_hierarchy_cache`
  MODIFY `cacheID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_reporting_matrix`
--
ALTER TABLE `tija_reporting_matrix`
  MODIFY `matrixID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_reporting_relationships`
--
ALTER TABLE `tija_reporting_relationships`
  MODIFY `relationshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tija_roles`
--
ALTER TABLE `tija_roles`
  MODIFY `roleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tija_role_levels`
--
ALTER TABLE `tija_role_levels`
  MODIFY `roleLevelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tija_role_types`
--
ALTER TABLE `tija_role_types`
  MODIFY `roleTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tija_salary_components`
--
ALTER TABLE `tija_salary_components`
  MODIFY `salaryComponentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tija_salary_component_category`
--
ALTER TABLE `tija_salary_component_category`
  MODIFY `salaryComponentCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tija_salary_component_history`
--
ALTER TABLE `tija_salary_component_history`
  MODIFY `historyID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_sales_activities`
--
ALTER TABLE `tija_sales_activities`
  MODIFY `salesActivityID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_sales_cases`
--
ALTER TABLE `tija_sales_cases`
  MODIFY `salesCaseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_sales_documents`
--
ALTER TABLE `tija_sales_documents`
  MODIFY `documentID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_sales_progress`
--
ALTER TABLE `tija_sales_progress`
  MODIFY `salesProgressID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_sales_prospects`
--
ALTER TABLE `tija_sales_prospects`
  MODIFY `salesProspectID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_sales_status_levels`
--
ALTER TABLE `tija_sales_status_levels`
  MODIFY `saleStatusLevelID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tija_statement_of_investment_allowance_accounts`
--
ALTER TABLE `tija_statement_of_investment_allowance_accounts`
  MODIFY `investmentAllowanceAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_statement_of_investment_allowance_data`
--
ALTER TABLE `tija_statement_of_investment_allowance_data`
  MODIFY `InvestmentAllowanceID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_subtasks`
--
ALTER TABLE `tija_subtasks`
  MODIFY `subtaskID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_tasks_time_logs`
--
ALTER TABLE `tija_tasks_time_logs`
  MODIFY `timelogID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_task_files`
--
ALTER TABLE `tija_task_files`
  MODIFY `taskFileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_task_status`
--
ALTER TABLE `tija_task_status`
  MODIFY `taskStatusID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tija_task_status_change_log`
--
ALTER TABLE `tija_task_status_change_log`
  MODIFY `taskStatusChangeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_taxable_profit`
--
ALTER TABLE `tija_taxable_profit`
  MODIFY `taxableProfitID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_tax_adjustments_accounts`
--
ALTER TABLE `tija_tax_adjustments_accounts`
  MODIFY `adjustmentAccountsID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_tax_adjustment_categories`
--
ALTER TABLE `tija_tax_adjustment_categories`
  MODIFY `adjustmentCategoryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_tax_adjustment_types`
--
ALTER TABLE `tija_tax_adjustment_types`
  MODIFY `adjustmentTypeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_travel_rate_types`
--
ALTER TABLE `tija_travel_rate_types`
  MODIFY `travelRateTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tija_trial_balance_mapped_accounts`
--
ALTER TABLE `tija_trial_balance_mapped_accounts`
  MODIFY `mappedAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_units`
--
ALTER TABLE `tija_units`
  MODIFY `unitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tija_unit_types`
--
ALTER TABLE `tija_unit_types`
  MODIFY `unitTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_user_unit_assignments`
--
ALTER TABLE `tija_user_unit_assignments`
  MODIFY `unitAssignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tija_withholding_tax`
--
ALTER TABLE `tija_withholding_tax`
  MODIFY `withholdingTaxID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tija_work_categories`
--
ALTER TABLE `tija_work_categories`
  MODIFY `workCategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tija_work_types`
--
ALTER TABLE `tija_work_types`
  MODIFY `workTypeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `vw_leave_approval_policies`
--
DROP TABLE IF EXISTS `vw_leave_approval_policies`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_leave_approval_policies`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`entityID` AS `entityID`, `p`.`orgDataID` AS `orgDataID`, `p`.`policyName` AS `policyName`, `p`.`policyDescription` AS `policyDescription`, `p`.`isActive` AS `isActive`, `p`.`isDefault` AS `isDefault`, `p`.`requireAllApprovals` AS `requireAllApprovals`, `p`.`allowDelegation` AS `allowDelegation`, `p`.`autoApproveThreshold` AS `autoApproveThreshold`, count(distinct `s`.`stepID`) AS `totalSteps`, count(distinct case when `s`.`isRequired` = 'Y' then `s`.`stepID` end) AS `requiredSteps`, `p`.`createdBy` AS `createdBy`, `p`.`createdAt` AS `createdAt`, concat(`creator`.`FirstName`,' ',`creator`.`Surname`) AS `createdByName` FROM ((`tija_leave_approval_policies` `p` left join `tija_leave_approval_steps` `s` on(`p`.`policyID` = `s`.`policyID` and `s`.`Suspended` = 'N')) left join `people` `creator` on(`p`.`createdBy` = `creator`.`ID`)) WHERE `p`.`Lapsed` = 'N' GROUP BY `p`.`policyID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_leave_approval_workflow`
--
DROP TABLE IF EXISTS `vw_leave_approval_workflow`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_leave_approval_workflow`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `p`.`entityID` AS `entityID`, `s`.`stepID` AS `stepID`, `s`.`stepOrder` AS `stepOrder`, `s`.`stepName` AS `stepName`, `s`.`stepType` AS `stepType`, `s`.`stepDescription` AS `stepDescription`, `s`.`isRequired` AS `isRequired`, `s`.`isConditional` AS `isConditional`, `s`.`conditionType` AS `conditionType`, `s`.`escalationDays` AS `escalationDays`, count(`a`.`approverID`) AS `customApproversCount` FROM ((`tija_leave_approval_policies` `p` join `tija_leave_approval_steps` `s` on(`p`.`policyID` = `s`.`policyID`)) left join `tija_leave_approval_step_approvers` `a` on(`s`.`stepID` = `a`.`stepID` and `a`.`Suspended` = 'N')) WHERE `p`.`Lapsed` = 'N' AND `p`.`Suspended` = 'N' AND `s`.`Suspended` = 'N' GROUP BY `s`.`stepID` ORDER BY `p`.`policyID` ASC, `s`.`stepOrder` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_notification_events_with_templates`
--
DROP TABLE IF EXISTS `vw_notification_events_with_templates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_notification_events_with_templates`  AS SELECT `e`.`eventID` AS `eventID`, `e`.`eventName` AS `eventName`, `e`.`eventSlug` AS `eventSlug`, `e`.`eventDescription` AS `eventDescription`, `e`.`eventCategory` AS `eventCategory`, `e`.`priorityLevel` AS `priorityLevel`, `m`.`moduleID` AS `moduleID`, `m`.`moduleName` AS `moduleName`, `m`.`moduleSlug` AS `moduleSlug`, count(distinct `t`.`templateID`) AS `templateCount`, `e`.`isActive` AS `isActive`, `e`.`isUserConfigurable` AS `isUserConfigurable` FROM ((`tija_notification_events` `e` join `tija_notification_modules` `m` on(`e`.`moduleID` = `m`.`moduleID`)) left join `tija_notification_templates` `t` on(`e`.`eventID` = `t`.`eventID` and `t`.`Suspended` = 'N')) WHERE `e`.`Suspended` = 'N' AND `m`.`Suspended` = 'N' GROUP BY `e`.`eventID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_pending_leave_approvals`
--
DROP TABLE IF EXISTS `vw_pending_leave_approvals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_pending_leave_approvals`  AS SELECT `i`.`instanceID` AS `instanceID`, `i`.`leaveApplicationID` AS `leaveApplicationID`, `la`.`employeeID` AS `employeeID`, concat(`emp`.`FirstName`,' ',`emp`.`Surname`) AS `employeeName`, `la`.`leaveTypeID` AS `leaveTypeID`, `lt`.`leaveTypeName` AS `leaveTypeName`, `la`.`startDate` AS `startDate`, `la`.`endDate` AS `endDate`, `la`.`noOfDays` AS `totalDays`, `i`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `i`.`currentStepID` AS `currentStepID`, `s`.`stepName` AS `currentStepName`, `s`.`stepType` AS `currentStepType`, `s`.`stepOrder` AS `currentStepOrder`, `i`.`workflowStatus` AS `workflowStatus`, `i`.`startedAt` AS `startedAt`, `i`.`lastActionAt` AS `lastActionAt`, to_days(current_timestamp()) - to_days(`i`.`lastActionAt`) AS `daysPending` FROM (((((`tija_leave_approval_instances` `i` join `tija_leave_applications` `la` on(`i`.`leaveApplicationID` = `la`.`leaveApplicationID`)) join `people` `emp` on(`la`.`employeeID` = `emp`.`ID`)) join `tija_leave_types` `lt` on(`la`.`leaveTypeID` = `lt`.`leaveTypeID`)) join `tija_leave_approval_policies` `p` on(`i`.`policyID` = `p`.`policyID`)) left join `tija_leave_approval_steps` `s` on(`i`.`currentStepID` = `s`.`stepID`)) WHERE `i`.`workflowStatus` in ('pending','in_progress') ORDER BY `i`.`lastActionAt` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_user_notification_summary`
--
DROP TABLE IF EXISTS `vw_user_notification_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_user_notification_summary`  AS SELECT `tija_notifications_enhanced`.`userID` AS `userID`, count(0) AS `totalNotifications`, sum(case when `tija_notifications_enhanced`.`status` = 'unread' then 1 else 0 end) AS `unreadCount`, sum(case when `tija_notifications_enhanced`.`status` = 'read' then 1 else 0 end) AS `readCount`, sum(case when `tija_notifications_enhanced`.`priority` = 'critical' and `tija_notifications_enhanced`.`status` = 'unread' then 1 else 0 end) AS `criticalUnread`, max(`tija_notifications_enhanced`.`DateAdded`) AS `lastNotificationDate` FROM `tija_notifications_enhanced` WHERE `tija_notifications_enhanced`.`Lapsed` = 'N' AND `tija_notifications_enhanced`.`Suspended` = 'N' GROUP BY `tija_notifications_enhanced`.`userID` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tija_employee_addresses`
--
ALTER TABLE `tija_employee_addresses`
  ADD CONSTRAINT `fk_address_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_allowances`
--
ALTER TABLE `tija_employee_allowances`
  ADD CONSTRAINT `fk_allowances_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_bank_details`
--
ALTER TABLE `tija_employee_bank_details`
  ADD CONSTRAINT `fk_bank_details_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_benefits`
--
ALTER TABLE `tija_employee_benefits`
  ADD CONSTRAINT `fk_benefit_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_certifications`
--
ALTER TABLE `tija_employee_certifications`
  ADD CONSTRAINT `fk_certification_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_dependants`
--
ALTER TABLE `tija_employee_dependants`
  ADD CONSTRAINT `fk_dependant_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_education`
--
ALTER TABLE `tija_employee_education`
  ADD CONSTRAINT `fk_education_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_emergency_contacts`
--
ALTER TABLE `tija_employee_emergency_contacts`
  ADD CONSTRAINT `fk_emergency_contact_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_extended_personal`
--
ALTER TABLE `tija_employee_extended_personal`
  ADD CONSTRAINT `fk_extended_personal_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_job_history`
--
ALTER TABLE `tija_employee_job_history`
  ADD CONSTRAINT `fk_job_history_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_licenses`
--
ALTER TABLE `tija_employee_licenses`
  ADD CONSTRAINT `fk_license_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_next_of_kin`
--
ALTER TABLE `tija_employee_next_of_kin`
  ADD CONSTRAINT `fk_next_of_kin_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_salary_history`
--
ALTER TABLE `tija_employee_salary_history`
  ADD CONSTRAINT `fk_salary_history_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_skills`
--
ALTER TABLE `tija_employee_skills`
  ADD CONSTRAINT `fk_skill_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_subordinates`
--
ALTER TABLE `tija_employee_subordinates`
  ADD CONSTRAINT `fk_subordinate_mapping_subordinate` FOREIGN KEY (`subordinateID`) REFERENCES `people` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subordinate_mapping_supervisor` FOREIGN KEY (`supervisorID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_supervisors`
--
ALTER TABLE `tija_employee_supervisors`
  ADD CONSTRAINT `fk_supervisor_mapping_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_supervisor_mapping_supervisor` FOREIGN KEY (`supervisorID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_employee_work_experience`
--
ALTER TABLE `tija_employee_work_experience`
  ADD CONSTRAINT `fk_work_experience_employee` FOREIGN KEY (`employeeID`) REFERENCES `people` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_expense`
--
ALTER TABLE `tija_expense`
  ADD CONSTRAINT `fk_expense_category` FOREIGN KEY (`expenseCategoryID`) REFERENCES `tija_expense_categories` (`expenseCategoryID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expense_status` FOREIGN KEY (`expenseStatusID`) REFERENCES `tija_expense_status` (`expenseStatusID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expense_type` FOREIGN KEY (`expenseTypeID`) REFERENCES `tija_expense_types` (`expenseTypeID`) ON UPDATE CASCADE;

--
-- Constraints for table `tija_expense_types`
--
ALTER TABLE `tija_expense_types`
  ADD CONSTRAINT `fk_expense_types_last_updated_by` FOREIGN KEY (`lastUpdatedBy`) REFERENCES `people` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expense_types_parent` FOREIGN KEY (`parentTypeID`) REFERENCES `tija_expense_types` (`expenseTypeID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tija_invoices`
--
ALTER TABLE `tija_invoices`
  ADD CONSTRAINT `fk_invoices_status` FOREIGN KEY (`invoiceStatusID`) REFERENCES `tija_invoice_status` (`statusID`) ON UPDATE CASCADE;

--
-- Constraints for table `tija_leave_accumulation_history`
--
ALTER TABLE `tija_leave_accumulation_history`
  ADD CONSTRAINT `tija_leave_accumulation_history_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_accumulation_policies` (`policyID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_leave_accumulation_history_ibfk_2` FOREIGN KEY (`ruleID`) REFERENCES `tija_leave_accumulation_rules` (`ruleID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_leave_accumulation_rules`
--
ALTER TABLE `tija_leave_accumulation_rules`
  ADD CONSTRAINT `tija_leave_accumulation_rules_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_accumulation_policies` (`policyID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_approval_actions`
--
ALTER TABLE `tija_leave_approval_actions`
  ADD CONSTRAINT `tija_leave_approval_actions_ibfk_1` FOREIGN KEY (`instanceID`) REFERENCES `tija_leave_approval_instances` (`instanceID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_approval_instances`
--
ALTER TABLE `tija_leave_approval_instances`
  ADD CONSTRAINT `tija_leave_approval_instances_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_approval_policies` (`policyID`);

--
-- Constraints for table `tija_leave_approval_steps`
--
ALTER TABLE `tija_leave_approval_steps`
  ADD CONSTRAINT `tija_leave_approval_steps_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_approval_policies` (`policyID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_approval_step_approvers`
--
ALTER TABLE `tija_leave_approval_step_approvers`
  ADD CONSTRAINT `tija_leave_approval_step_approvers_ibfk_1` FOREIGN KEY (`stepID`) REFERENCES `tija_leave_approval_steps` (`stepID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_workflow_template_steps`
--
ALTER TABLE `tija_leave_workflow_template_steps`
  ADD CONSTRAINT `tija_leave_workflow_template_steps_ibfk_1` FOREIGN KEY (`templateID`) REFERENCES `tija_leave_workflow_templates` (`templateID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notifications_enhanced`
--
ALTER TABLE `tija_notifications_enhanced`
  ADD CONSTRAINT `tija_notifications_enhanced_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_entity_preferences`
--
ALTER TABLE `tija_notification_entity_preferences`
  ADD CONSTRAINT `fk_entity_pref_channel` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_entity_pref_event` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_preferences`
--
ALTER TABLE `tija_notification_preferences`
  ADD CONSTRAINT `tija_notification_preferences_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_notification_preferences_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_queue`
--
ALTER TABLE `tija_notification_queue`
  ADD CONSTRAINT `tija_notification_queue_ibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `tija_notifications_enhanced` (`notificationID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_notification_queue_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_templates`
--
ALTER TABLE `tija_notification_templates`
  ADD CONSTRAINT `tija_notification_templates_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_notification_templates_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_template_variables`
--
ALTER TABLE `tija_notification_template_variables`
  ADD CONSTRAINT `tija_notification_template_variables_ibfk_1` FOREIGN KEY (`moduleID`) REFERENCES `tija_notification_modules` (`moduleID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_project_plan_template_phases`
--
ALTER TABLE `tija_project_plan_template_phases`
  ADD CONSTRAINT `fk_template_phases_template` FOREIGN KEY (`templateID`) REFERENCES `tija_project_plan_templates` (`templateID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tija_roles`
--
ALTER TABLE `tija_roles`
  ADD CONSTRAINT `fk_roles_roleLevel` FOREIGN KEY (`roleLevelID`) REFERENCES `tija_role_levels` (`roleLevelID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roles_roleType` FOREIGN KEY (`roleTypeID`) REFERENCES `tija_org_role_types` (`roleTypeID`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
