-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 02, 2025 at 06:54 AM
-- Server version: 8.3.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `pms_sbsl`
--

-- --------------------------------------------------------

--
-- Table structure for table `african_countries`
--

DROP TABLE IF EXISTS `african_countries`;
CREATE TABLE IF NOT EXISTS `african_countries` (
  `countryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `countryName` varchar(100) NOT NULL,
  `countryCode` char(2) NOT NULL,
  `countryISO3Code` char(3) NOT NULL,
  `phoneCode` varchar(5) DEFAULT NULL,
  `countryCapital` varchar(100) DEFAULT NULL,
  `region` varchar(50) DEFAULT 'Africa',
  `subregion` varchar(50) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`countryID`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;

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
-- Table structure for table `industry_sectors`
--

DROP TABLE IF EXISTS `industry_sectors`;
CREATE TABLE IF NOT EXISTS `industry_sectors` (
  `industrySectorID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `industryTitle` varchar(180) NOT NULL,
  `industryCategory` varchar(180) NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`industrySectorID`)
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=latin1;

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
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `ID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FirstName` varchar(128) COLLATE utf8mb4_bin NOT NULL,
  `Surname` varchar(128) COLLATE utf8mb4_bin DEFAULT NULL,
  `OtherNames` varchar(128) COLLATE utf8mb4_bin DEFAULT NULL,
  `Email` varchar(128) COLLATE utf8mb4_bin NOT NULL,
  `profile_image` varchar(256) COLLATE utf8mb4_bin DEFAULT NULL,
  `Password` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `NeedsToChangePassword` enum('y','n') COLLATE utf8mb4_bin DEFAULT 'n',
  `Valid` enum('y','n') COLLATE utf8mb4_bin DEFAULT 'n',
  `active` enum('Y','N') COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`ID`, `DateAdded`, `FirstName`, `Surname`, `OtherNames`, `Email`, `profile_image`, `Password`, `NeedsToChangePassword`, `Valid`, `active`) VALUES
(1, '2023-03-12 16:56:54', 'System', 'Administrator', NULL, 'support@sbsl.co.ke', NULL, '$6$rounds=1024$1063359921$mAbT9hkQ9Eazp16ULeuWdqSIxiyY5cR6zzo0.EwofatNwZybPCuODvERRpTuDowDH9DOOLDTb7/CZjkYCNAla.', 'n', 'y', 'N'),
(4, '2025-02-26 11:04:48', 'Brian ', 'Nyongesa', '', 'brian@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N'),
(5, '2025-02-26 11:04:48', 'Dan', 'Birenge ', '', 'dan.birenge@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N'),
(6, '2025-02-26 11:04:48', 'Dennis ', 'Wabukala', '', 'dennis@sbsl.co.ke', NULL, '$6$rounds=1024$1127087966$CZ4/xziN4psb0IFTaQ/2Y11OumWaD10UFcfU90UeWWjqvOV9r4Uapsrx7ym62L8bsZEG.kSRRzL5/DW6s5PCR1', 'n', 'y', 'N'),
(7, '2025-02-26 11:04:48', 'Marleeen ', 'Kwamboka ', '', 'marleen.kwamboka@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N'),
(8, '2025-02-26 11:04:48', 'Brown ', 'Ndiewo', '', 'brown@sbsl.co.ke', NULL, '$6$rounds=1024$1343662581$j.cUfliP/6iQLtNIw7Mjem4ExcNthkcVADtq7ky4SZikg20EHiUXvWJIfyoj1HjKmPuAO33fVk5QHf/Apy6LU.', 'n', 'y', 'Y'),
(9, '2025-02-26 11:04:48', 'Eddah ', 'Choge ', '', 'eddah.jelimo@sbsl.co.ke', NULL, '$6$rounds=1024$713774922$SE53OE7o4S28Da1uj3WMO6cbwxFbUQrewibIbDucJTe3tgptglcF8SVVPTq3nElHx8glD0ZGPg5Y5pJ4bGZdc0', 'n', 'y', 'N'),
(10, '2025-02-26 11:04:48', 'Edwin ', 'Masai', '', 'edwin.masai@sbsl.co.ke', NULL, '$6$rounds=1024$1343662581$j.cUfliP/6iQLtNIw7Mjem4ExcNthkcVADtq7ky4SZikg20EHiUXvWJIfyoj1HjKmPuAO33fVk5QHf/Apy6LU.', 'n', 'y', 'N'),
(11, '2025-02-26 11:04:48', 'Felix', 'Mauncho', '', 'felix.mauncho@sbsl.co.ke', NULL, '$6$rounds=1024$1982023237$EP5utXqddGBMvNSLj3ounllA3iqEQid9AJOQ45YivIBA5KqoHkeWcvFjhnnH4fb5nvUzk0iNaHgglI7iyGkId0', 'n', 'y', 'Y'),
(12, '2025-02-26 11:04:48', 'Francis ', 'Lelei', '', 'francis.lelei@sbsl.co.ke', NULL, '$6$rounds=1024$1733684590$P6KylsgKM.Fyr1q8NbKj9G8y2hn5oGmGxE5JlXt.urX3vXLMxHsSHhcH.wbURvHil5V4H.RRpkn7Fh07HvZx3/', 'n', 'y', 'N'),
(13, '2025-02-26 11:04:48', 'Joel', 'Muli', '', 'joel.muli@sbsl.co.ke', NULL, '$6$rounds=1024$567723878$FwpbGhy/PVUMEUEHhox6YaeF8Q4LmB9RrC7ynJy7wQ8DymLI.M0hJhwaENLj4OJEAoFP0uHsfGwr8SX1paAu/0', 'n', 'y', 'N'),
(14, '2025-02-26 11:04:48', 'Priscilla ', 'Makena', '', 'priscilla.makena@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N'),
(15, '2025-02-26 11:04:48', 'Kendi ', 'Njeru', '', 'kendi.njeru@sbsl.co.ke', NULL, '$6$rounds=1024$1765883151$xURzsu1z5X1rxW2igmjnKhCnIkusXtxCbSfSHgWXkVlPxrJWYMpoEkwTHO1YQWLjaUTmLBoyt85EjiSV3ATjH0', 'n', 'y', 'N'),
(16, '2025-02-26 11:04:48', 'Emmanuel ', 'Kelechi ', '', 'emmanuel.kelechi@sbsl.co.ke', NULL, '$6$rounds=1024$849078739$uYNKoIk8w4nH.sKUzis/wdg29XchtUK.fvVFpvmUyGA5RD3kErpsNjo/TFE1wJAp2C1CXbS1UCru4J3HCpZG20', 'n', 'y', 'N'),
(17, '2025-02-26 11:04:48', 'Irene ', 'Muthoni ', '', 'irene.muthoni@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N'),
(18, '2025-02-26 11:04:48', 'Jesse ', 'Wambua ', '', 'jesse.wanjau@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N'),
(19, '2025-02-26 11:04:48', 'Timothy ', 'Oduor', '', 'timothy.oduor@sbsl.co.ke', NULL, '$6$rounds=1024$1566836044$2uvB62RCX6o4fJME97NwvIvi/r6wjebP4B/6SqmEciJ1Q0mKeB9iZiqeeOLTXZjYNzhKLLvLUgkyS1wRUNpcD.', 'y', 'n', 'N'),
(20, '2025-02-26 11:04:48', 'Brenda ', 'Wambua ', '', 'brenda.wambua@sbsl.co.ke', NULL, '$6$rounds=1024$171698763$sD6H0ptZ8y7fX8b/cDcLh6vLgygF9dCt6St86l6gE3cMmf38t3Tfzyn0xE5jbnupAvYlNflgtWAk67v5i36UL/', 'n', 'y', 'N'),
(21, '2025-02-26 11:04:48', 'Luther ', 'Icami', '', 'Luther.icami@sbsl.co.ke', NULL, '$6$rounds=1024$117061473$Y4PkdHbu9rtIK/znQH2irza47J9dCSjXFwaA.XkMPgBXX.MW3zdFj/Es6uS2.P9nFhOq8zd52WqMwpFRPYLhp1', 'n', 'y', 'N'),
(22, '2025-02-26 11:04:48', 'Agatha ', 'Wakaba ', '', 'Agatha.wakaba@sbsl.co.ke ', NULL, NULL, 'n', 'n', 'N'),
(23, '2025-02-26 11:04:48', 'Mercy ', 'Morema ', '', 'Mercy.morema@sbsl.co.ke', NULL, '$6$rounds=1024$522458767$HyrsTgvsTlxh4qBXZpgAW19v2TbcP/nptFbkv.OIo/i500AXyTU5DZ8w6BxD6Ugnm6X6MINhd0CegnF55cZYx/', 'n', 'y', 'N'),
(24, '2025-02-26 11:04:48', 'Anita ', 'Wanjiru', '', 'anita.wanjiru@sbsl.co.ke', NULL, '$6$rounds=1024$75847822$JK.6JJuVv/xPnX27aL3/36iiUi.6h80tm3CNjzo5Ql.cXsGoCM359izh461O.OOyrpvbz8OwMlWY7gMOODbQ01', 'n', 'y', 'N'),
(25, '2025-02-26 11:04:48', 'Elias', 'Mokaya ', '', 'Eliaship@gmail.com', NULL, '$6$rounds=1024$78889264$8linA/.hKxYvzqMiGDaRzQC6PqY.rXiVGun1uTrmXcrKJOjMgWTT7QJ2tqwdDYnwBv61SByUDaPCkZe52JIo91', 'n', 'y', 'N'),
(29, '2025-03-03 12:56:23', 'John', 'Doe', NULL, 'johnDoe@example.com', NULL, '$6$rounds=1024$1786586611$CGCH0cbj9Bwb2pUgtpX2FfqOM00ioXmL0YZRzp9ZZDnJLDRQvbbdebw5cRV9HPpM2v9v4UeCQPFjOZnGfbz5K0', 'n', 'y', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_administrators`
--

DROP TABLE IF EXISTS `tija_administrators`;
CREATE TABLE IF NOT EXISTS `tija_administrators` (
  `adminID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `adminTypeID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `unitTypeID` int DEFAULT NULL,
  `unitID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`adminID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_administrators`
--

INSERT INTO `tija_administrators` (`adminID`, `DateAdded`, `userID`, `adminTypeID`, `orgDataID`, `entityID`, `unitTypeID`, `unitID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2023-04-10 14:06:45', 11, 1, 1, NULL, NULL, NULL, '2025-02-02 14:03:27', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_admin_types`
--

DROP TABLE IF EXISTS `tija_admin_types`;
CREATE TABLE IF NOT EXISTS `tija_admin_types` (
  `adminTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adminTypeName` varchar(256) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`adminTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_admin_types`
--

INSERT INTO `tija_admin_types` (`adminTypeID`, `DateAdded`, `adminTypeName`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2023-03-10 21:42:49', 'Super Admin', '2023-03-10 21:42:49', 'N', 'N'),
(2, '2023-03-10 21:42:49', 'System Admin', '2023-03-10 21:42:49', 'N', 'N'),
(3, '2023-03-10 21:57:37', 'Entity Admin', '2023-03-10 21:57:37', 'N', 'N'),
(4, '2023-03-10 21:57:37', 'Unit Admin', '2023-03-10 21:57:37', 'N', 'N'),
(5, '2023-03-10 21:57:37', 'Team Admin', '2023-03-10 21:57:37', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entities`
--

DROP TABLE IF EXISTS `tija_entities`;
CREATE TABLE IF NOT EXISTS `tija_entities` (
  `entityID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entityName` varchar(255) NOT NULL,
  `entityDescription` text,
  `entityTypeID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityParentID` int NOT NULL,
  `industrySectorID` int NOT NULL,
  `registrationNumber` varchar(60) NOT NULL,
  `entityPIN` varchar(60) NOT NULL,
  `entityCity` varchar(120) NOT NULL,
  `entityCountry` varchar(180) NOT NULL,
  `entityPhoneNumber` int NOT NULL,
  `entityEmail` varchar(256) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`entityID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_entities`
--

INSERT INTO `tija_entities` (`entityID`, `DateAdded`, `entityName`, `entityDescription`, `entityTypeID`, `orgDataID`, `entityParentID`, `industrySectorID`, `registrationNumber`, `entityPIN`, `entityCity`, `entityCountry`, `entityPhoneNumber`, `entityEmail`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-26 09:21:10', 'SKM Kenya', NULL, 1, 1, 0, 80, '234R65723', 'P051661058D', 'Nairobi', '25', 722540169, 'info@sbsl.co.ke', '2025-02-26 09:21:10', 1, 'N', 'N'),
(2, '2025-02-26 09:25:52', 'SBSL Group', NULL, 1, 1, 0, 80, '234R65723', 'P051661058D', 'Nairobi', '25', 722540169, 'info@sbsl.co.ke', '2025-02-26 09:25:52', 1, 'N', 'N'),
(3, '2025-02-26 09:27:09', 'SBSL Kenya', NULL, 1, 1, 2, 80, '234R65745', 'P051661058D', 'Nairobi', '25', 722540169, 'info@sbsl.co.ke', '2025-02-26 09:27:09', 1, 'N', 'N'),
(4, '2025-03-07 11:12:35', 'SBSL Uganda', 'Uganda branch', 1, 1, 2, 80, '234R65723', 'P051661058D', 'Kampala', '52', 2147483647, 'uganda@sbsl.co.ke', '2025-03-07 11:12:35', 11, 'N', 'N'),
(5, '2025-03-07 11:23:07', 'SBSL Tanzania', 'Tanzania Entity', 1, 1, 2, 80, '234R65745', 'P051661043D', 'Dar es Salaam', '49', 2147483647, 'info@tz.sbsl.co.ke', '2025-03-07 11:23:07', 11, 'N', 'N'),
(6, '2025-03-29 19:47:49', 'SBSL Advisory Consultants', 'SBSL Advisory Consultants', 1, 1, 3, 115, '234R65723', 'P051661058D', 'Nairobi', '25', 722540168, 'advisory@sbsl.co.ke', '2025-03-29 19:47:49', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entity_types`
--

DROP TABLE IF EXISTS `tija_entity_types`;
CREATE TABLE IF NOT EXISTS `tija_entity_types` (
  `entityTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entityTypeTitle` varchar(255) NOT NULL,
  `entityTypeDescription` text,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`entityTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_entity_types`
--

INSERT INTO `tija_entity_types` (`entityTypeID`, `DateAdded`, `entityTypeTitle`, `entityTypeDescription`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-01 22:43:32', 'company', 'company', '2025-02-01 22:43:32', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_organisation_data`
--

DROP TABLE IF EXISTS `tija_organisation_data`;
CREATE TABLE IF NOT EXISTS `tija_organisation_data` (
  `orgDataID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgLogo` varchar(256) DEFAULT NULL,
  `orgName` varchar(255) NOT NULL,
  `industrySectorID` int DEFAULT NULL,
  `numberOfEmployees` int NOT NULL,
  `registrationNumber` varchar(30) NOT NULL,
  `orgPIN` varchar(80) NOT NULL,
  `costCenterEnabled` enum('Y','N') NOT NULL DEFAULT 'N',
  `orgAddress` varchar(30) NOT NULL,
  `orgPostalCode` varchar(30) DEFAULT NULL,
  `orgCity` varchar(128) NOT NULL,
  `orgCountry` varchar(128) NOT NULL,
  `orgPhoneNumber1` varchar(30) NOT NULL,
  `orgPhoneNUmber2` varchar(30) DEFAULT NULL,
  `orgEmail` varchar(255) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`orgDataID`),
  UNIQUE KEY `orgPIN` (`orgPIN`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_organisation_data`
--

INSERT INTO `tija_organisation_data` (`orgDataID`, `DateAdded`, `orgLogo`, `orgName`, `industrySectorID`, `numberOfEmployees`, `registrationNumber`, `orgPIN`, `costCenterEnabled`, `orgAddress`, `orgPostalCode`, `orgCity`, `orgCountry`, `orgPhoneNumber1`, `orgPhoneNUmber2`, `orgEmail`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-02-26 09:00:01', '', 'Strategic Business Solutions Limited', 80, 23, '234R65723', 'P051661058D', 'Y', 'Rainbow Towers', '00100', 'Nairobi', 'Kenya', '0722540169', NULL, 'info@sbsl.co.ke', '2025-02-26 09:00:01', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_charts`
--

DROP TABLE IF EXISTS `tija_org_charts`;
CREATE TABLE IF NOT EXISTS `tija_org_charts` (
  `orgChartID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgChartName` varchar(256) NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`orgChartID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_org_charts`
--


--
-- Table structure for table `tija_org_chart_position_assignments`
--

DROP TABLE IF EXISTS `tija_org_chart_position_assignments`;
CREATE TABLE IF NOT EXISTS `tija_org_chart_position_assignments` (
  `positionAssignmentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `orgChartID` int NOT NULL,
  `entityID` int NOT NULL,
  `positionID` int NOT NULL,
  `positionTypeID` int DEFAULT NULL,
  `positionTitle` varchar(255) NOT NULL,
  `positionDescription` text,
  `positionParentID` int NOT NULL,
  `positionOrder` int DEFAULT NULL,
  `positionLevel` varchar(120) DEFAULT NULL,
  `positionCode` varchar(120) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`positionAssignmentID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;



DROP TABLE IF EXISTS `tija_units`;
CREATE TABLE IF NOT EXISTS `tija_units` (
  `unitID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unitCode` varchar(256) DEFAULT NULL,
  `orgDataID` varchar(120) NOT NULL,
  `entityID` int NOT NULL,
  `unitName` varchar(256) NOT NULL,
  `unitTypeID` int NOT NULL,
  `headOfUnitID` int NOT NULL,
  `parentUnitID` int NOT NULL,
  `unitDescription` text,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`unitID`),
  UNIQUE KEY `UID` (`unitCode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_unit_types`
--

DROP TABLE IF EXISTS `tija_unit_types`;
CREATE TABLE IF NOT EXISTS `tija_unit_types` (
  `unitTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unitTypeName` varchar(256) NOT NULL,
  `unitOrder` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`unitTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Table structure for table `tija_user_unit_assignments`
--

DROP TABLE IF EXISTS `tija_user_unit_assignments`;
CREATE TABLE IF NOT EXISTS `tija_user_unit_assignments` (
  `unitAssignmentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `userID` int NOT NULL,
  `unitID` int NOT NULL,
  `unitTypeID` int NOT NULL,
  `assignmentStartDate` date NOT NULL,
  `assignmentEndDate` date DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`unitAssignmentID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

DROP TABLE IF EXISTS `user_details`;
CREATE TABLE IF NOT EXISTS `user_details` (
  `ID` int NOT NULL,
  `UID` varchar(256) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL DEFAULT '1',
  `entityID` int NOT NULL,
  `prefixID` varchar(10) DEFAULT NULL,
  `phoneNo` varchar(40) DEFAULT NULL,
  `payrollNo` varchar(20) DEFAULT NULL,
  `PIN` varchar(30) DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `businessUnitID` int DEFAULT NULL,
  `supervisorID` int DEFAULT NULL,
  `supervisingJobTitleID` int DEFAULT NULL,
  `workTypeID` int DEFAULT NULL,
  `jobTitleID` int DEFAULT NULL,
  `departmentID` int DEFAULT NULL,
  `costPerHour` int DEFAULT NULL,
  `jobCategoryID` int DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `jobBandID` int DEFAULT NULL,
  `employmentStatusID` int DEFAULT NULL,
  `dailyHours` int DEFAULT NULL,
  `weekWorkDays` varchar(256) DEFAULT NULL,
  `overtimeAllowed` enum('Y','N') DEFAULT NULL,
  `workHourRoundingID` int DEFAULT NULL,
  `payGradeID` int DEFAULT NULL,
  `nationalID` varchar(23) DEFAULT NULL,
  `nhifNumber` varchar(22) DEFAULT NULL,
  `nssfNumber` varchar(22) DEFAULT NULL,
  `basicSalary` float(16,2) DEFAULT NULL,
  `SetUpProfile` enum('y','n') NOT NULL DEFAULT 'n',
  `profileImageFile` varchar(256) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `contractStartDate` varchar(234) DEFAULT NULL,
  `contractEndDate` varchar(234) DEFAULT NULL,
  `employmentStartDate` date DEFAULT NULL,
  `employmentEndDate` date DEFAULT NULL,
  `LastUpdatedByID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`ID`, `UID`, `DateAdded`, `orgDataID`, `entityID`, `prefixID`, `phoneNo`, `payrollNo`, `PIN`, `dateOfBirth`, `gender`, `businessUnitID`, `supervisorID`, `supervisingJobTitleID`, `workTypeID`, `jobTitleID`, `departmentID`, `costPerHour`, `jobCategoryID`, `salary`, `jobBandID`, `employmentStatusID`, `dailyHours`, `weekWorkDays`, `overtimeAllowed`, `workHourRoundingID`, `payGradeID`, `nationalID`, `nhifNumber`, `nssfNumber`, `basicSalary`, `SetUpProfile`, `profileImageFile`, `Lapsed`, `Suspended`, `contractStartDate`, `contractEndDate`, `employmentStartDate`, `employmentEndDate`, `LastUpdatedByID`, `LastUpdate`) VALUES
(28, '7d231cc3d158e9793017098f5ae98ab7f006c31e9c1971b8daffc22f4b892108', '2025-03-03 12:49:06', 1, 3, '1', '', NULL, '', '1999-07-14', 'male', NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL, NULL, NULL, 2, 8, NULL, NULL, 60, NULL, '2343456543', 'Y78u9474', '2343654', 80000.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-03-03', '0000-00-00', 11, '2025-03-03 12:49:06'),
(4, '6edca01dc25e2db45e1047bb4706a9d14db2154dc3f3fbfd52ebce3d05ceea4c', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A004712285z', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(5, 'd1c041537b3c9ae1dfd478a68bc55d256ca98287138a8de8b695e4ac3ab0e5d3', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A001129656V', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(6, 'db7bfc6c1a40df0fb1b25d22ef228edda5bbf216665c0b9dbc51b6296f7a5d42', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A005058358A', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(7, 'caa131f986897b32f2247eb90acfef4cf9341b6dd8eaad8fb3fa2f0360659efc', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A005808066K', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(8, '2249611787e74f848db270acdd71c2cab0b3d90941d3ca20d2e49042c86415f2', '2025-02-26 11:04:48', 1, 3, '1', '', '', 'A005507804U', '0000-00-00', 'male', NULL, NULL, NULL, NULL, 52, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(9, '24c7b23affe8dfbe6b9b98f52c1f8dbd0aa3e679ba31c5314f6b5af6dd7a256f', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A019214498C', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(10, 'dc95d8d4880b644bd76f6cc855ccd4340fe5d63fb7973dde882a6171b8187c90', '2025-02-26 11:04:48', 1, 3, '1', '', '', 'A004577290K', '0000-00-00', '', NULL, NULL, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(11, '3c807500a992b9cf529ef3802fffc4ced5f2c751a6d11595e222e85d75ead6f1', '2025-02-26 11:04:48', 1, 3, '1', '', '', 'A004654098I', '0000-00-00', '', NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, NULL, NULL, 2, 8, NULL, NULL, 15, NULL, '23595758', 'Y78u9474', '453654645', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(12, '3aabbe41792f7d073d8a5158afbda3a075e97a9078e17fe059adb157fd80a898', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A016372044J', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(13, '3f31808cf083dc468eec31bf73191d1cda71979cfa8096e5edd1781f5086309c', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(14, 'e3b3912f8951806361cebbb0449eab163922926cfbbc65d11ff8014c64a83742', '2025-02-26 11:04:48', 1, 3, '3', '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(15, 'd76e5e8a4dab9bca9cb313052b3c8ab4f8b2ee63aba9ef882423170f440018f7', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(16, 'c44470ffc34fc0798bdb6a01014bbb39a79db7d0cdac1eca26eaf387c7d8360b', '2025-02-26 11:04:48', 1, 3, '11', '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-03-25', '2025-03-31', 16, '2025-02-26 11:04:48'),
(17, 'e01940c5e471c9ccc5f65365e01d9e4f14c50867cec61628deb1df26deb01e97', '2025-02-26 11:04:48', 1, 3, '3', '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(18, 'd047266a0fd5fc910ee957dd13189803064ab10122fc89ccc27dee6e938b7f19', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(19, 'f59fa3577879937404f3b7c69fbae8076e15ef5fee5f7b04229e9cf5f6bc46cf', '2025-02-26 11:04:48', 1, 3, NULL, '', '', 'A010876995V', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(20, '6b6f83c5435417b2a5ecd4470addb1f9a8ecd52a7463d0e9de0c54d3f573a333', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(21, 'b061d19023cc660081f4c271c1c6e05953404165af0ebb0c4da8e83751f25896', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(22, '754fe531f95abc2379c747e17eecc637876398aa42c74daa3037c54c59bfb632', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, 22, NULL, NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, '23595758', '23503042', NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, 11, '2025-02-26 11:04:48'),
(23, '935a2c572566a9ae6cfdaac2e532cc268c5f2094f46e75753c50a5ada41ea947', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(24, 'd600fda53ffb00373c01520e2eaec0236af8185bf83e78f692ae7529bd0ee72c', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(25, 'd95496442276bf1d0f7505ca70de78a7d295f72ba10a57d5256469e16571e642', '2025-02-26 11:04:48', 1, 3, NULL, '', '', '', '0000-00-00', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 'n', NULL, 'N', 'N', NULL, NULL, NULL, NULL, NULL, '2025-02-26 11:04:48'),
(29, '8397189318986a45b5cb9f54f0c3549715a1a3d16f0c1eda549421b6eef362f5', '2025-03-03 12:56:23', 1, 3, '1', '0722540169', 'sbsl-234', '', '1992-02-06', 'male', NULL, NULL, NULL, NULL, 16, NULL, NULL, NULL, NULL, NULL, 2, 8, NULL, NULL, 60, NULL, '2343456543', 'Y78u9474', '2343654', 2343654.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-03-03', NULL, 11, '2025-03-03 12:56:23');
COMMIT;
