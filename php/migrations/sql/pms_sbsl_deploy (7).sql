-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 06, 2025 at 01:03 PM
-- Server version: 8.3.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS=0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pms_sbsl_deploy`
--

--
-- Procedures
--
DELIMITER $$
DROP PROCEDURE IF EXISTS `add_policy_column`$$
CREATE PROCEDURE `add_policy_column` (`colName` VARCHAR(64), `definition` TEXT, `afterColumn` VARCHAR(64))   BEGIN
    SELECT COUNT(*) INTO @missing_col
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'tija_leave_handover_policies' AND COLUMN_NAME = colName;

    IF @missing_col = 0 THEN
        SET @sql = CONCAT('ALTER TABLE `tija_leave_handover_policies` ADD COLUMN `', colName, '` ', definition, ' AFTER `', afterColumn, '`;');
        PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `sp_build_administrative_closure`$$
CREATE PROCEDURE `sp_build_administrative_closure` ()   BEGIN
    -- Clear existing administrative closure
    DELETE FROM tija_org_hierarchy_closure WHERE hierarchy_type = 'Administrative';

    -- Insert self-references (each node is its own ancestor at depth 0)
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT entityID, entityID, 0, 'Administrative', 'Entity', 'Entity'
    FROM tija_entities
    WHERE Lapsed = 'N' AND Suspended = 'N';

    -- Insert direct parent-child relationships (depth 1) from entity hierarchy
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT
        e.entityParentID,
        e.entityID,
        1,
        'Administrative',
        'Entity',
        'Entity'
    FROM tija_entities e
    WHERE e.entityParentID IS NOT NULL
    AND e.Lapsed = 'N'
    AND e.Suspended = 'N'
    AND NOT EXISTS (
        SELECT 1 FROM tija_org_hierarchy_closure c
        WHERE c.ancestor_id = e.entityParentID
        AND c.descendant_id = e.entityID
        AND c.hierarchy_type = 'Administrative'
    );

    -- Build transitive closure paths iteratively
    closure_loop: WHILE TRUE DO
        INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
        SELECT DISTINCT
            c1.ancestor_id,
            c2.descendant_id,
            c1.depth + c2.depth,
            'Administrative',
            c1.ancestor_type,
            c2.descendant_type
        FROM tija_org_hierarchy_closure c1
        INNER JOIN tija_org_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
        WHERE c1.hierarchy_type = 'Administrative'
        AND c2.hierarchy_type = 'Administrative'
        AND c1.ancestor_type = 'Entity'
        AND c1.descendant_type = 'Entity'
        AND c2.ancestor_type = 'Entity'
        AND c2.descendant_type = 'Entity'
        AND c1.depth > 0
        AND c2.depth = 1
        AND NOT EXISTS (
            SELECT 1 FROM tija_org_hierarchy_closure c3
            WHERE c3.ancestor_id = c1.ancestor_id
            AND c3.descendant_id = c2.descendant_id
            AND c3.hierarchy_type = 'Administrative'
        );

        IF ROW_COUNT() = 0 THEN
            LEAVE closure_loop;
        END IF;
    END WHILE closure_loop;

    -- Add user-to-entity relationships (individuals to their entity)
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT
        ud.entityID,
        ud.ID,
        0,
        'Administrative',
        'Entity',
        'Individual'
    FROM user_details ud
    WHERE ud.entityID IS NOT NULL
    AND ud.Lapsed = 'N'
    AND ud.Suspended = 'N'
    AND NOT EXISTS (
        SELECT 1 FROM tija_org_hierarchy_closure c
        WHERE c.ancestor_id = ud.entityID
        AND c.descendant_id = ud.ID
        AND c.hierarchy_type = 'Administrative'
    );

    -- Insert self-references for individuals (each individual is its own ancestor at depth 0)
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT ID, ID, 0, 'Administrative', 'Individual', 'Individual'
    FROM user_details
    WHERE Lapsed = 'N' AND Suspended = 'N';

    -- Add supervisor relationships (individual to individual) - direct relationships are depth 1
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT
        ud1.supervisorID,
        ud1.ID,
        1,
        'Administrative',
        'Individual',
        'Individual'
    FROM user_details ud1
    INNER JOIN user_details ud2 ON ud1.supervisorID = ud2.ID
    WHERE ud1.supervisorID IS NOT NULL
    AND ud1.Lapsed = 'N'
    AND ud1.Suspended = 'N'
    AND ud2.Lapsed = 'N'
    AND ud2.Suspended = 'N'
    AND NOT EXISTS (
        SELECT 1 FROM tija_org_hierarchy_closure c
        WHERE c.ancestor_id = ud1.supervisorID
        AND c.descendant_id = ud1.ID
        AND c.hierarchy_type = 'Administrative'
    );

    -- Build transitive closure for supervisor relationships
    supervisor_loop:WHILE TRUE DO
        INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
        SELECT DISTINCT
            c1.ancestor_id,
            c2.descendant_id,
            c1.depth + c2.depth,
            'Administrative',
            'Individual',
            'Individual'
        FROM tija_org_hierarchy_closure c1
        INNER JOIN tija_org_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
        WHERE c1.hierarchy_type = 'Administrative'
        AND c2.hierarchy_type = 'Administrative'
        AND c1.ancestor_type = 'Individual'
        AND c1.descendant_type = 'Individual'
        AND c2.ancestor_type = 'Individual'
        AND c2.descendant_type = 'Individual'
        AND c1.depth > 0
        AND c2.depth = 1
        AND NOT EXISTS (
            SELECT 1 FROM tija_org_hierarchy_closure c3
            WHERE c3.ancestor_id = c1.ancestor_id
            AND c3.descendant_id = c2.descendant_id
            AND c3.hierarchy_type = 'Administrative'
        );

        IF ROW_COUNT() = 0 THEN
            LEAVE supervisor_loop;
        END IF;
    END WHILE supervisor_loop;
END$$

DROP PROCEDURE IF EXISTS `sp_calculate_expense_totals`$$
CREATE PROCEDURE `sp_calculate_expense_totals` (IN `p_employee_id` INT, IN `p_date_from` DATE, IN `p_date_to` DATE, OUT `p_total_amount` DECIMAL(12,2), OUT `p_total_reimbursement` DECIMAL(12,2), OUT `p_total_tax` DECIMAL(10,2), OUT `p_expense_count` INT)   BEGIN
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
CREATE PROCEDURE `sp_generate_expense_number` (IN `p_expense_date` DATE, OUT `p_expense_number` VARCHAR(50))   BEGIN
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

DROP PROCEDURE IF EXISTS `sp_get_ancestors`$$
CREATE PROCEDURE `sp_get_ancestors` (IN `p_descendant_id` INT, IN `p_hierarchy_type` VARCHAR(20), IN `p_max_depth` INT)   BEGIN
    SELECT
        c.ancestor_id,
        c.depth,
        c.ancestor_type,
        CASE
            WHEN c.ancestor_type = 'Entity' THEN e.entityName
            WHEN c.ancestor_type = 'Individual' THEN CONCAT(p.FirstName, ' ', p.Surname)
        END AS ancestor_name
    FROM tija_org_hierarchy_closure c
    LEFT JOIN tija_entities e ON c.ancestor_type = 'Entity' AND c.ancestor_id = e.entityID
    LEFT JOIN people p ON c.ancestor_type = 'Individual' AND c.ancestor_id = p.ID
    WHERE c.descendant_id = p_descendant_id
    AND c.hierarchy_type = p_hierarchy_type
    AND (p_max_depth IS NULL OR c.depth <= p_max_depth)
    AND c.depth > 0 -- Exclude self
    ORDER BY c.depth, ancestor_name;
END$$

DROP PROCEDURE IF EXISTS `sp_get_descendants`$$
CREATE PROCEDURE `sp_get_descendants` (IN `p_ancestor_id` INT, IN `p_hierarchy_type` VARCHAR(20), IN `p_max_depth` INT)   BEGIN
    SELECT
        c.descendant_id,
        c.depth,
        c.descendant_type,
        CASE
            WHEN c.descendant_type = 'Entity' THEN e.entityName
            WHEN c.descendant_type = 'Individual' THEN CONCAT(p.FirstName, ' ', p.Surname)
        END AS descendant_name
    FROM tija_org_hierarchy_closure c
    LEFT JOIN tija_entities e ON c.descendant_type = 'Entity' AND c.descendant_id = e.entityID
    LEFT JOIN people p ON c.descendant_type = 'Individual' AND c.descendant_id = p.ID
    WHERE c.ancestor_id = p_ancestor_id
    AND c.hierarchy_type = p_hierarchy_type
    AND (p_max_depth IS NULL OR c.depth <= p_max_depth)
    AND c.depth > 0 -- Exclude self
    ORDER BY c.depth, descendant_name;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

DROP TABLE IF EXISTS `administrators`;
CREATE TABLE IF NOT EXISTS `administrators` (
  `ID` int UNSIGNED NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`ID`) VALUES
(1);

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
-- Table structure for table `client_relationship_assignments`
--

DROP TABLE IF EXISTS `client_relationship_assignments`;
CREATE TABLE IF NOT EXISTS `client_relationship_assignments` (
  `clientRelationshipID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientID` int NOT NULL,
  `employeeID` int NOT NULL,
  `clientRelationshipType` enum('clientLiaisonPartner','engagementPartner','manager','AssociateSeniorAssociate','associateIntern') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientRelationshipID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `client_relationship_assignments`
--

INSERT INTO `client_relationship_assignments` (`clientRelationshipID`, `DateAdded`, `clientID`, `employeeID`, `clientRelationshipType`, `startDate`, `endDate`, `notes`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(2, '2025-12-02 16:07:20', 1, 2, 'clientLiaisonPartner', '2025-12-02', NULL, NULL, '2025-12-02 13:07:20', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
CREATE TABLE IF NOT EXISTS `currency` (
  `currencyID` int NOT NULL AUTO_INCREMENT,
  `NAME` varchar(20) DEFAULT NULL,
  `CODE` varchar(3) DEFAULT NULL,
  `symbol` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`currencyID`)
) ENGINE=MyISAM AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb3;

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
-- Table structure for table `login_sessions`
--

DROP TABLE IF EXISTS `login_sessions`;
CREATE TABLE IF NOT EXISTS `login_sessions` (
  `ID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `SessIDStr` varchar(255) NOT NULL,
  `CheckStr` varchar(255) NOT NULL,
  `PersonID` int UNSIGNED DEFAULT NULL,
  `LoginTime` datetime DEFAULT NULL,
  `LastActionTime` datetime DEFAULT NULL,
  `LogoutTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `SessIDStr` (`SessIDStr`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `login_sessions`
--

INSERT INTO `login_sessions` (`ID`, `SessIDStr`, `CheckStr`, `PersonID`, `LoginTime`, `LastActionTime`, `LogoutTime`) VALUES
(1, '72c53756dd7098c067c7d7f4a467827f5a11f62acfd7dc07d1e3bed077ef4ec0', '0ca27ab01ebec234a96676d368f3396a', 1, '2025-11-21 06:52:15', '2025-11-21 12:58:05', '2025-11-29 15:40:52'),
(2, '61d681f88ae05fbc3a2ff8eb2f6653805290861c7b2fc85cf0608279e3ae5f54', '5054fabac77c345d64ad9fa55c1f0549', 4, '2025-11-21 11:26:45', '2025-11-21 12:58:05', '2025-11-21 13:06:25'),
(3, '23fe21eca55f5f83181a1d88c23e916cdf6055531b9a59d9002642f86db9d414', '228a853d81649a3d190b9a7a0ebf03a2', 4, '2025-11-21 13:06:25', '2025-11-22 07:16:01', '2025-11-22 07:28:31'),
(4, 'c44ee8cb1c88df52bace8d1563f3239fe1980464e40a8bba937a1a0b26b8a8c6', 'e3c833070f140dd4428b7e37ffef1197', 4, '2025-11-22 07:28:31', '2025-11-22 11:41:21', '2025-11-22 11:57:42'),
(5, '56f7a2a087d9e9c03c0c67d6060147b92b4dec9be3410a1b5e790d36ab0a2c64', '70583630627719b351df2f425f469ac7', 4, '2025-11-22 11:57:42', '2025-11-22 15:45:12', '2025-11-22 15:45:12'),
(6, 'f681b4040ae2f43dfda6e79180685305e4430992f2f99e3665f4484e279d370a', '9c248469df0e997cc6776161bcad439b', 4, '2025-11-22 15:45:30', '2025-11-24 11:11:21', '2025-11-24 11:22:27'),
(7, '5722ffcf481e1af5137252b12633a77603bcc4b239e77bea725842fd52d69a3b', '9484c6bb6909448070341786d9e449e9', 4, '2025-11-24 11:22:27', '2025-11-24 11:22:33', '2025-11-24 11:22:33'),
(8, 'bde8d8e72ae142164f7cd7ab0d45ea13893d8c5456d8e7f38c9df61d62b13bb8', '1ae9c58f7900187b211944cc8be5feae', 4, '2025-11-24 11:22:36', '2025-11-24 16:44:54', '2025-11-24 16:57:40'),
(9, '3bebd0d3329eb48044f451c2e8f2228ccfa6e4a55735261490592202370e160d', 'e61a0769f650ebc69d907ad644d68899', 24, '2025-11-24 14:43:21', '2025-11-24 16:43:53', '2025-11-25 09:11:11'),
(10, '24ef50ec29a0569e323be2333a4eca20bd99a209984165f1d89b1e2cc5bee730', '63528bc7728b2e2f8ac71396dce8ec99', 23, '2025-11-24 16:43:57', '2025-11-24 16:45:28', '2025-11-24 17:08:41'),
(11, '59f9cb80e85703bc7530ed985d58fe40ae1b994c9e077e7baea1aea303b5905d', '333d3067cfd69e62268d4ec154cb27fe', 4, '2025-11-24 16:57:40', '2025-11-25 06:15:08', '2025-11-25 08:15:46'),
(12, '62749a2c2bdd33317b5fe26271013d9b19776ea70dea69e82f2fc37668cd5766', 'ba8437e33f4525295a943ed5a3a42526', 23, '2025-11-24 17:08:41', '2025-11-25 06:51:57', '2025-11-24 18:46:23'),
(13, '058d79c39c48dbadac3690bb1817286f21bfdfdb034ea4653d889cd31f4076dd', '46e36ef46093a3b594c31aae69f72b22', 23, '2025-11-24 18:46:23', '2025-11-25 06:53:09', '2025-11-25 09:12:09'),
(14, '714dcaa927d1dd79e78f48dfa51fa8b048ae3bf1e5db47329e3cf4998f1fae67', '5d888c0fb19cb3957229ba0098876717', 4, '2025-11-25 08:15:46', '2025-11-25 13:42:46', '2025-11-25 10:02:50'),
(15, 'f1e60705826b2b1c41ca92ea9107519a9da2c27e15c2b41894a9dd1ac7ea206d', '392581ac6c05172b18d546b00196e00c', 22, '2025-11-25 09:09:41', '2025-11-25 10:41:32', '2025-11-25 10:41:32'),
(16, '9a2b5085c20ce3548113bdc21f5c7473eb1d6fa2aa93ea6f6c369b9e0f8a07a8', '7d9ae8a2c3c7456dcbc1300f0f22fb90', 24, '2025-11-25 09:11:11', '2025-11-25 11:32:20', '2025-12-03 06:55:43'),
(17, 'a022f49315953675d3850ad605c6855e54d87532268c04a7ab890fdf6822593a', '095d4cccb1355d20819e0383b55b9bae', 23, '2025-11-25 09:12:09', '2025-11-25 13:50:09', '2025-12-03 07:22:40'),
(18, 'c3f155fe9ccb70430a39eb4f1f681f58ffae34adcc2f7b4ea9f3fd50457b54ca', '8e5172405ffa2d659a4141c3f5f638aa', 4, '2025-11-25 10:02:50', '2025-11-25 10:05:40', '2025-11-25 10:05:40'),
(19, 'c8f7621e701a059273420f544177d344ec9bacaa92c94fa957487be506ab300f', '933f812b6cfa5f8243cd2b7f6421d8da', 4, '2025-11-25 10:05:57', '2025-11-25 10:06:08', '2025-11-25 10:06:08'),
(20, 'e0c41b92c124fb199f7adfcd166e7338b2b0bfe1e36f355b0707bb71a2ef05b1', 'cc49ac28b6faa95ccd8b6a0bddc0c2c4', 4, '2025-11-25 10:08:18', '2025-11-25 10:08:45', '2025-11-25 10:08:45'),
(21, '68cb837730bb1159cd919a19dc8f162e99aa5350d77fbe1e615779cc043e126b', 'e4433d25d18cbd339ea78b3710383cf4', 4, '2025-11-25 10:09:04', '2025-11-25 10:09:15', '2025-11-25 10:09:15'),
(22, '266c61b4ac712cf375dd71eb29b78ea0ad4c9cf0000c219023d4d01c6fd483ca', '7e9ec2ef82f7881a13b7728fefb99eea', 4, '2025-11-25 10:09:38', '2025-11-25 10:09:54', '2025-11-25 10:09:54'),
(23, '1a7ad035f8040e51f4bad45c4cd61cd22c6c56f3e56cd6149a1e2cf1927b0c99', '7ee9d5b13c8b357d05f60cbc9789d0a4', 4, '2025-11-25 10:10:10', '2025-11-25 10:10:57', '2025-11-25 10:10:57'),
(24, '4c5e73cb7e28b409e40510f0a1dc52ab1c39921146f06a6de57d91194728034e', 'e5526d0106d98ca85018cebf3fd37e33', 4, '2025-11-25 10:10:57', '2025-11-25 10:11:20', '2025-11-25 10:11:20'),
(25, 'fc07f51832afa84361489d9f28e6a6930cd38bf7ae6d0ed1eaa189c14d7c8366', 'eeeb850c8d15888ba94b948980b86fa8', 4, '2025-11-25 10:11:20', '2025-11-25 10:11:36', '2025-11-25 10:11:36'),
(26, 'bc60aeb9b8a7470dfd9c26c8b9cdd389d70b6204599181032c239a1ab24b2fa2', 'c548e1b97ce39186ee31b4fbf01453d9', 4, '2025-11-25 10:11:36', '2025-11-25 10:12:25', '2025-11-25 10:12:25'),
(27, '003df64466060247e6517c22d4fff2ae684c78c54a48dcc42cb4e8d8ff1c5edb', 'fd0d594491ecc2288e861566037016dc', 4, '2025-11-25 10:12:25', '2025-11-25 10:12:44', '2025-11-25 10:12:44'),
(28, '53229ace6ef57f00c7e09e4f576ac5a855789a1c15b28bb1c76c7f0ad0b40b49', '6c9bb13ffa37f3d9696f1bdc8dd9f373', 4, '2025-11-25 10:12:51', '2025-11-25 10:13:25', '2025-11-25 10:13:26'),
(29, '75da73c924cf7bdee5a3653d4ede9b2febc43d83ee501cdabab7df13f35e0eee', '9546315ee785a0d045eddee4fe162ce1', 4, '2025-11-25 10:13:26', '2025-11-25 10:13:33', '2025-11-25 10:13:33'),
(30, '0ce492673f068e6f61dbcfa1e391d4b8442e0636bffc819e68aa5183f4dba473', 'af4b2b5e171ae1df7750c3441053c6c2', 4, '2025-11-25 10:13:33', '2025-11-25 10:40:16', '2025-11-25 10:40:16'),
(31, 'c7193bd0e37c83c5cf33acbb608d6964f938dc3ca3853e6d20e86c8329886a45', 'c4418870ae983fa34c1f5a5f5a0b7285', 4, '2025-11-25 13:44:56', '2025-11-25 13:46:07', '2025-11-25 14:29:43'),
(32, 'd98e8174cae3e0394c45d446d2cc1cdf9f20d0806d644e2d440df42214b23e87', 'f363025506772bd4e6c896970d142d4b', 4, '2025-11-25 14:29:43', '2025-11-27 10:14:16', '2025-11-27 12:40:20'),
(33, '7d372394d78897c025691cd74794b4e77a0aa6d41ee528e6f64b02ded04f59c5', 'fa6f60e19ebc20db66e6f1d6e45863ce', 4, '2025-11-27 12:40:20', '2025-11-30 08:50:39', '2025-11-30 14:38:22'),
(34, 'a87ae1601da060ae994849abdf179891a19ca9da3a950f4b264354c4b27e3970', '1861ff60a49102439c16c1727423cf1f', 1, '2025-11-29 15:40:52', '2025-11-29 18:13:21', '2025-11-29 18:13:21'),
(35, 'c424cadb1191b71054929c2d6b72be38be595e25a011705b8a311238cb1e4310', 'fdfc80be205f19912c57d0a82ecc8e80', 4, '2025-11-30 14:38:22', '2025-12-01 06:54:36', '2025-12-01 08:05:48'),
(36, 'a62e05e62f00cf6e6412597664a7911041e24df63611bbec1c6468b98a167a6b', '91256e30ee94209539b292b8644a5a36', 4, '2025-12-01 08:05:48', '2025-12-01 10:03:10', '2025-12-01 12:16:00'),
(37, '631dfe688ac271a60778476881f0c773b887b0280fc01496c59b93e633c97a3c', '991b5a546daa1312a16f72d26bbbee43', 22, '2025-12-01 08:09:50', '2025-12-01 10:10:37', '2025-12-01 12:15:40'),
(38, 'a52ac97459da01139f39b7dbeb6a64ef69d8ee7e7becc5672364cd109eb373a6', 'ac864fc60e41c90831e692bf3ad84649', 22, '2025-12-01 12:15:40', '2025-12-01 12:15:52', '2025-12-01 12:15:52'),
(39, 'abe1d3999f4557dd6e53522ebb3c5994a6979681c76a1a07e91498b62fef9c3a', 'a77a646c83c4a59d4d8812784ba2168c', 4, '2025-12-01 12:16:00', '2025-12-02 10:28:17', '2025-12-02 10:35:20'),
(40, '351aa32e2c0bc4a047b11d1765ee1a9833b23ea6ce313af927fa1b384e7c2ef0', '45738ca7fde124ae55470309560ec0d6', 4, '2025-12-02 10:35:20', '2025-12-03 07:23:06', '2025-12-03 07:23:06'),
(41, 'c4a0935b44a65ef4c21b017f7b639ca239a4b51ccf7623840ff37cb647072fdc', 'f2a29a15feeac844f67afbae70a29c14', 24, '2025-12-03 06:55:43', '2025-12-03 11:11:50', '2025-12-06 12:40:57'),
(42, '7f1a600942e97a192cc16d103d009cf94379788cc7a64d82cf40af1257ff3fc3', 'a33282cb023237083c13e68f4962f0ed', 23, '2025-12-03 07:22:40', '2025-12-03 07:22:51', '2025-12-03 07:22:51'),
(43, '1b163b62aa733c8cab66d3afb2ea3f201bb3371bf4614a25b16f689cc5f8aacb', 'c9e8a0da1ec9697988bb4958999e37ed', 4, '2025-12-03 07:22:59', '2025-12-03 07:23:34', '2025-12-03 07:23:34'),
(44, 'f55b37d5b7b8ecba0ed88fe7692d4c47dc917e30b2210a93902aa8ecedd00331', '77932a822578800fcbe09ea47b9172c8', 23, '2025-12-03 07:23:20', '2025-12-03 11:12:24', '2025-12-03 11:15:36'),
(45, '1c73fc901418261a5ca9d145701f71c3202a1fb548890bfe4a1ada1386395e16', '88257400a3a8e04cb8a66a314b13316d', 22, '2025-12-03 07:23:46', '2025-12-03 10:37:50', '2025-12-06 12:38:58'),
(46, '9264b51efaaccdb4d52f083c6f295a9b3265b47c0521f00713d41f12bcb3f572', '08ade5e3b6201cdb993ae8cad2e57164', 4, '2025-12-03 07:24:35', '2025-12-03 11:11:50', '2025-12-03 11:15:51'),
(47, 'd66b9b00873c97672036d5ac451233e68e367cd11fc679299fb1256c84ff13f7', '8821a4e705d38e111ec7778681e4aa92', 23, '2025-12-03 11:15:36', '2025-12-03 11:15:41', '2025-12-03 11:15:41'),
(48, '1dbe3c9732abf0b84424257b7b5d5598306deb1c25ebd4436b717d1ef276f2c1', 'f45d0f77cd2c7d3bd39b014cb752faf4', 4, '2025-12-03 11:15:51', '2025-12-03 11:23:59', '2025-12-06 08:57:51'),
(49, 'db26235e5bca13385aacfa6be1fcd222859f7c96f3691c68aaa37b1e1bd4f18a', '7a6f5b29670aebf31cfc2a8b20dd6618', 4, '2025-12-06 08:57:51', '2025-12-06 08:59:19', '2025-12-06 08:59:19'),
(50, '6455c4db68719613301bc87e8d33b224f9d345f520d00fecbcc10f6fa3a40a17', '1439276a1df2adac06c49f8f4e93a919', 4, '2025-12-06 12:28:11', '2025-12-06 13:03:27', '2025-12-06 12:37:50'),
(51, 'dbce200bac7843f80113fe752198ba6617e1817ba38068daea750397589db836', 'caf79502b8429f8429e78ea7d78be304', 4, '2025-12-06 12:37:50', '2025-12-06 13:02:27', NULL),
(52, '63b01389ada4b8ad46b58d2edc70a6fc2e27a08f451cf0c6ebeb61fbe67192dc', '8d09d8b3b770af47a31010f7356c2200', 22, '2025-12-06 12:38:58', '2025-12-06 13:03:27', NULL),
(53, 'e5e09bad175eaf42ec7f1143967ffbd5256b7b7590194fc7ec3ba526f6bc590c', '4754299892ce6507dc1b31d554957cf9', 24, '2025-12-06 12:40:57', '2025-12-06 13:03:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `ID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FirstName` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `Surname` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `OtherNames` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `userInitials` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `Email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `profile_image` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `Password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `NeedsToChangePassword` enum('y','n') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'n',
  `Valid` enum('y','n') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'n',
  `active` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `LastUpdateByID` int DEFAULT NULL,
  `isEmployee` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`ID`, `DateAdded`, `FirstName`, `Surname`, `OtherNames`, `userInitials`, `Email`, `profile_image`, `Password`, `NeedsToChangePassword`, `Valid`, `active`, `LastUpdateByID`, `isEmployee`) VALUES
(1, '2023-03-12 16:56:54', 'System', 'Administrator', NULL, '', 'support@sbsl.co.ke', 'employee_profile/1756735549_4.jpg', '$6$rounds=1024$1063359921$mAbT9hkQ9Eazp16ULeuWdqSIxiyY5cR6zzo0.EwofatNwZybPCuODvERRpTuDowDH9DOOLDTb7/CZjkYCNAla.', 'n', 'y', 'N', NULL, 'N'),
(2, '2025-11-21 09:59:36', 'Brian', 'Nyongesa', 'Julius', '', 'brian@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(3, '2025-11-21 10:01:12', 'Dennis', 'Wabukala', '', 'DW', 'dennis@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(4, '2025-11-21 11:21:15', 'Felix', 'Mauncho', '', 'FM', 'felix.mauncho@sbsl.co.ke', NULL, '$6$rounds=1024$2022530352$Le1HBPlg1ihvIQ7/r3UDUygnDcgSDLm1u/XWDk3VegKTmrezm7tcsqaXV7lrcs.QpK0FFeobNlXvW0IDtqtlE/', 'n', 'y', 'N', NULL, 'Y'),
(5, '2025-11-21 11:23:46', 'Brown', 'Ndiewo', '', 'SBS', 'brown@sbsl.co.ke', NULL, NULL, 'y', 'n', 'N', NULL, 'Y'),
(6, '2025-11-21 11:25:20', 'Marleeen', 'Kwamboka', '', 'MK', 'marleen.kwamboka@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(7, '2025-11-21 11:30:05', 'Amos', 'Kiritu', '', 'AK', 'amos.kiritu@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(8, '2025-11-21 11:31:54', 'Edwin', 'Masai', '', 'EM', 'edwin.masai@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(9, '2025-11-21 11:42:45', 'Francis', 'Lelei', '', 'FL', 'francis.lelei@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(10, '2025-11-21 11:45:25', 'Eddah', 'Jelimo', '', 'EJ', 'eddah.jelimo@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(11, '2025-11-21 11:49:53', 'Brenda', 'Wambua', '', 'BW', 'brenda.wambua@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(12, '2025-11-21 11:51:24', 'Emmanuel', 'Kelechi', '', 'EK', 'irene.muthoni@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(13, '2025-11-21 11:56:24', 'Anita', 'Wanjiru', '', '', 'anita.wanjiru@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(14, '2025-11-21 12:00:07', 'Jerumani', 'Kibwana', '', 'JK', 'jerumani.kibwana@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(15, '2025-11-21 12:05:10', 'Bryson', 'Yida', '', 'BY', 'bryson@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(16, '2025-11-21 12:10:29', 'Timothy', 'Oduor', '', 'TO', 'timothy.oduor@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(17, '2025-11-21 12:16:15', 'Luther', 'Icami', '', 'LI', 'luther.Icami@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(18, '2025-11-21 12:18:51', 'Joseph', 'Nzeli', '', 'JN', 'joseph.nzeli@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(19, '2025-11-21 12:23:27', 'Hobson', 'Mokaya', 'Atuti', 'HM', 'hobson.mokaya@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(20, '2025-11-21 12:30:30', 'Mercy', 'Morema', '', '', 'Mercy.morema@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(21, '2025-11-21 12:32:33', 'Dan', 'Birenge', '', 'DB', 'dan.birenge@sbsl.co.ke', NULL, NULL, 'n', 'n', 'N', NULL, 'Y'),
(22, '2025-11-24 10:10:16', 'Test', 'Employee', '', 'TE', 'felix.mauncho@skm.co.ke', NULL, '$6$rounds=1024$898455597$kY4890VSmqtpeGCJginM3XcfjrRxXsz0BLct2B1102U7geTBqrYq8e0BszrrOje4YalNW7xCwl1Te4vHkzB/O/', 'n', 'y', 'N', NULL, 'Y'),
(23, '2025-11-24 17:19:54', 'John', 'Doe', '', 'JD', 'felixmauncho@gmail.com', NULL, '$6$rounds=1024$240923102$yBfvbyRMXmIguWwk5r6t2jHYESMRvyRnCTC2DEmwY7O9aTbOHJQUY9jRNMt0LEWPxtyjwn0bS1j0ERsb9sZu2.', 'n', 'y', 'N', NULL, 'Y'),
(24, '2025-11-24 17:21:00', 'Jane', 'Smith', 'S', 'JS', 'mauncho.home@gmail.com', NULL, '$6$rounds=1024$1512339618$G9HYxFv1Omu8AfC5wypF1FpSGZzj6v8sRhCSPiZNVuG.v6j/bcNzJy/5pGjYZYmDhJE88bj0tHpGD93XRo3zR/', 'n', 'y', 'N', NULL, 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `registration_tokens`
--

DROP TABLE IF EXISTS `registration_tokens`;
CREATE TABLE IF NOT EXISTS `registration_tokens` (
  `ID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `PersonID` int UNSIGNED DEFAULT NULL,
  `DateAdded` datetime NOT NULL,
  `Token1` varchar(128) NOT NULL,
  `Token2` varchar(128) NOT NULL,
  `PasswordSet` enum('y','n') NOT NULL DEFAULT 'n',
  `DatePasswordSet` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Token1` (`Token1`),
  UNIQUE KEY `Token2` (`Token2`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `registration_tokens`
--

INSERT INTO `registration_tokens` (`ID`, `PersonID`, `DateAdded`, `Token1`, `Token2`, `PasswordSet`, `DatePasswordSet`) VALUES
(1, 2, '2025-11-21 09:59:36', '6db4f28b4806bc4a7c051f88f09aadfbbc30175bc579b3b98afb2592d66f5bd3', '4b28f4cfa1b5c389740a5cd58d2d121b31218a53a9bc553ec8443ff6e9c45696', 'n', NULL),
(2, 3, '2025-11-21 10:01:12', 'd37c4988a785a3da6b11e1f8ed624164e3adc72e873eba09f8f250ba9d38be40', 'd1017378f5d9e2179d9c36bd65e62b473e6964f8bfe6c933f8d9ae6c07a515aa', 'n', NULL),
(3, 4, '2025-11-21 11:21:15', '28510d9efe961ff51999fb805b771e5ba9b3f131969cbde95352386e8807cddc', '95a68b8c9799dda5c1647e2d09ede038dc97a6d87bf2815f13c6b8c7f4d47c80', 'y', '2025-11-24 14:22:27'),
(4, 5, '2025-11-21 11:23:46', '21198b1a88d9c8e65ce319edf5c8c3d43575555d1995bc5177b86019d2c2f535', 'c97d6c4194807613377ff0d9890abde0a4709fbe66d4bbda3bc29760352c885e', 'n', NULL),
(5, 6, '2025-11-21 11:25:20', '1857e9e5f23c7dfe69f8f33b0d3e33825255527d389d8b04b1039c3f8b8c9d2f', 'e83abc7c6ab5a793d8b37832468c04d0914b7e5c249aacd8edb2d1f358eba1f9', 'n', NULL),
(6, 7, '2025-11-21 11:30:05', '9df9ae4d3ada30ef24424dd9354c08f600b49accb0fc29f1d59ae8643bd9ef12', '21176ca0021b32ccd77a6fac80128eb846182bda3c402f0b8cb12145947128a6', 'n', NULL),
(7, 8, '2025-11-21 11:31:54', 'b4b9f865799cd395c7f32553bd2d4797194ef10b61180254769c52e45a2fc178', '0c0da7535dd2f89433661aea33e89365f8f24a61da9d031e804899bb2f78fa57', 'n', NULL),
(8, 9, '2025-11-21 11:42:45', '66a11cbb9cacd28d13d37fb643a6f5e7317468f1ef36e200b72e0007d95ab9cc', '8e76f5d4951163611d38866999d4d2f575ed8969e403219bd7e5af1fd3085b81', 'n', NULL),
(9, 10, '2025-11-21 11:45:25', '0980df75f616d20a05aad123fd3585a43a9af81058773d542617cf4f7749c791', 'c3e19b1764cae39da8edd2e187e43d8ffa92ffc181d3774cb62e9d90da8cc79a', 'n', NULL),
(10, 11, '2025-11-21 11:49:53', 'b96843918ce3b155c093e2ec055cd7a0aa2ee5b6f0db57b822a9525e949ec6f0', 'c68f17f7a1b3268b7a6419081c2e7c61df9e3ac0b4c5a28042164f3550392062', 'n', NULL),
(11, 12, '2025-11-21 11:51:24', '9172b7414a7f9cd5644f22bf1db61b086ee70295728b83cba538b703f6bbbf69', 'f4059ee0504682594bf6065c2d7fc5dfc33efd258b31031dd8c584fbd6fe9d00', 'n', NULL),
(12, 13, '2025-11-21 11:56:24', '3c5b8191ced9e0613083b29af3844f20079b5715ffb26b569ac8f65cec2e074c', 'b3450b4b9adcd4800f1e86bbc6e33fdbbffacbeee3ad092263ead219ac5116ea', 'n', NULL),
(13, 14, '2025-11-21 12:00:07', '4b441b06f8b6ba498d8b0995dc08e8a3393ebb8f2f4e9dfda4aeada824209659', '885016eca53bb1e63cce4bcec62a30cc44bc3c62edb5676430c7595c68b7495b', 'n', NULL),
(14, 15, '2025-11-21 12:05:10', '34f941b58a2097eb34e247bf33927a94edec940abb9dc39a1e97f5b46597be72', 'fccacdb091f9c371c66c1ca26f91075b9cd2a9fb98c8cd123b9613c73f7b965b', 'n', NULL),
(15, 16, '2025-11-21 12:10:29', 'b9e58342ba50c1719f07e81402899af7598282eb3dce1bc515b1572bb5f245d3', '5fb006787eaa52bde44e73d6df60d512f2baee17cc39cd2d1a41bf6615b45299', 'n', NULL),
(16, 17, '2025-11-21 12:16:15', 'aacc280c203d2c140da40644df8d178d87443a9efb218681fc94ba3c8454bcf4', '7d8b46d8137b84a298fd683d044423cc9c0e4fa1a9a2b92e1260f8d6c3ddb8ac', 'n', NULL),
(17, 18, '2025-11-21 12:18:51', 'bcbeabb0ab5bbb41c919e24ea5c2c5fc30cbcf098a5dffaaf9a3e762dbf59a57', '0794013c9165a4b704c3ba32feb6d54516797a9d970a1a9279061174bc8bf081', 'n', NULL),
(18, 19, '2025-11-21 12:23:27', '29d45b5385a11b22e47422eb1d8a1509fae7f51716f68764ddbb2b5966a9eae5', 'f4738302f802ff5bfeec5be9b80ce98f7d22a99874d8d20e3bec9c9f1779128b', 'n', NULL),
(19, 20, '2025-11-21 12:30:30', 'ee88b17b5ace3a5927d66960f848f3b9d778106289548df5b6df6dce95935da4', '9523376425e22a67874930daf469781d36958fa8c7bd9dc0b10ef9131b1d4cd0', 'n', NULL),
(20, 21, '2025-11-21 12:32:33', '4e7bd732a3b300282876b453f44d21cad46baf7cb4df43364325b772371cfb4f', '769227dab48acee67796ce52e73e5cec439ad58678cc082ae22dc132d836f333', 'n', NULL),
(21, 22, '2025-11-24 10:10:16', '02040f0b58f291ed47d996946891a9fa5af15dc58741993ed4be4f40320b02a8', '57478593b1d4a10893c421f781e05aba9b18911b5f41189b119f8614e4e4bbb6', 'y', '2025-11-25 12:09:41'),
(22, 23, '2025-11-24 17:19:54', '1d4db7b3fb1a881cd3967e8226a10a368ee4f9aa60926de43c9e119e56f81bc9', 'fc6a50dab398ceac8872b941aafe8404613c08e43230eea64a6503b07e2fae4b', 'y', '2025-11-24 19:43:57'),
(23, 24, '2025-11-24 17:21:00', '4e9bbbf491e732b3ae476200637fd66a444bb4eaf63dcc2c1c432594bc3d0728', 'af793b0045ae5cca49d0fff00dfc65a317142593e9d8d2da67beefdf358924ce', 'y', '2025-11-24 17:43:21');

-- --------------------------------------------------------

--
-- Table structure for table `tija_absence_data`
--

DROP TABLE IF EXISTS `tija_absence_data`;
CREATE TABLE IF NOT EXISTS `tija_absence_data` (
  `absenceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `absenceName` varchar(256) NOT NULL,
  `absenceTypeID` int NOT NULL,
  `projectID` text COMMENT 'Affected Project',
  `absenceDate` date NOT NULL,
  `startTime` varchar(20) NOT NULL,
  `endTime` varchar(20) NOT NULL,
  `allday` enum('Y','N') NOT NULL DEFAULT 'N',
  `absenceHrs` time NOT NULL,
  `functionID` int DEFAULT NULL,
  `absenceDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`absenceID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_absence_type`
--

DROP TABLE IF EXISTS `tija_absence_type`;
CREATE TABLE IF NOT EXISTS `tija_absence_type` (
  `absenceTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `absenceTypeName` varchar(180) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`absenceTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_activities`;
CREATE TABLE IF NOT EXISTS `tija_activities` (
  `activityID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `clientID` int NOT NULL,
  `activityName` varchar(255) NOT NULL,
  `activityDescription` text,
  `activityCategoryID` int NOT NULL,
  `activityTypeID` int NOT NULL,
  `activitySegment` enum('sales','project','task','activity','businessDevelopment') DEFAULT NULL,
  `durationType` varchar(120) NOT NULL,
  `activityDate` date NOT NULL,
  `activityStartTime` time DEFAULT NULL,
  `activityDurationEndTime` time DEFAULT NULL,
  `activityDurationEndDate` date DEFAULT NULL,
  `recurring` varchar(120) DEFAULT NULL,
  `recurrenceType` varchar(254) DEFAULT NULL,
  `recurringInterval` int DEFAULT NULL,
  `recurringIntervalUnit` varchar(120) DEFAULT NULL,
  `weekRecurringDays` text,
  `monthRepeatOnDays` varchar(120) DEFAULT NULL,
  `monthlyRepeatingDay` int DEFAULT NULL,
  `customFrequencyOrdinal` varchar(120) DEFAULT NULL,
  `customFrequencyDayValue` varchar(120) NOT NULL,
  `recurrenceEndType` varchar(120) DEFAULT NULL,
  `numberOfOccurrencesToEnd` int DEFAULT NULL,
  `recurringEndDate` date DEFAULT NULL,
  `salesCaseID` int DEFAULT NULL,
  `projectID` int DEFAULT NULL,
  `projectPhaseID` int DEFAULT NULL,
  `projectTaskID` int DEFAULT NULL,
  `activityStatus` enum('notStarted','inProgress','inReview','completed','needsAttention','stalled') NOT NULL DEFAULT 'notStarted',
  `activityStatusID` int NOT NULL DEFAULT '1',
  `activityPriority` varchar(120) NOT NULL,
  `activityOwnerID` int NOT NULL,
  `activityParticipants` text,
  `activityNotesID` int DEFAULT NULL,
  `activityNotes` text,
  `activityOutcome` varchar(100) DEFAULT NULL,
  `activityResult` text,
  `activityCost` decimal(15,2) DEFAULT '0.00' COMMENT 'Deprecated: Use tija_activity_expenses table',
  `costCategory` varchar(100) DEFAULT NULL COMMENT 'Deprecated: Use tija_activity_expenses table',
  `costNotes` text COMMENT 'Deprecated: Use tija_activity_expenses table',
  `followUpNotes` text,
  `requiresFollowUp` enum('Y','N') NOT NULL DEFAULT 'N',
  `sendReminder` enum('Y','N') NOT NULL DEFAULT 'N',
  `reminderTime` int DEFAULT NULL COMMENT 'Minutes before activity to send reminder',
  `allDayEvent` enum('Y','N') NOT NULL DEFAULT 'N',
  `duration` int DEFAULT NULL COMMENT 'Duration in minutes',
  `activityLocation` text,
  `meetingLink` varchar(500) DEFAULT NULL,
  `assignedByID` int NOT NULL,
  `workSegmentID` int NOT NULL DEFAULT '3',
  `activityCompleted` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`activityID`),
  KEY `idx_activities_outcome` (`activityOutcome`),
  KEY `idx_activities_status` (`activityStatus`),
  KEY `idx_activities_date` (`activityDate`),
  KEY `idx_activities_owner` (`activityOwnerID`),
  KEY `idx_activities_sales` (`salesCaseID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_activities`
--

INSERT INTO `tija_activities` (`activityID`, `DateAdded`, `orgDataID`, `entityID`, `clientID`, `activityName`, `activityDescription`, `activityCategoryID`, `activityTypeID`, `activitySegment`, `durationType`, `activityDate`, `activityStartTime`, `activityDurationEndTime`, `activityDurationEndDate`, `recurring`, `recurrenceType`, `recurringInterval`, `recurringIntervalUnit`, `weekRecurringDays`, `monthRepeatOnDays`, `monthlyRepeatingDay`, `customFrequencyOrdinal`, `customFrequencyDayValue`, `recurrenceEndType`, `numberOfOccurrencesToEnd`, `recurringEndDate`, `salesCaseID`, `projectID`, `projectPhaseID`, `projectTaskID`, `activityStatus`, `activityStatusID`, `activityPriority`, `activityOwnerID`, `activityParticipants`, `activityNotesID`, `activityNotes`, `activityOutcome`, `activityResult`, `activityCost`, `costCategory`, `costNotes`, `followUpNotes`, `requiresFollowUp`, `sendReminder`, `reminderTime`, `allDayEvent`, `duration`, `activityLocation`, `meetingLink`, `assignedByID`, `workSegmentID`, `activityCompleted`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-02 12:14:53', 1, 1, 1, 'Catch up meetng', 'dsfs dfgsdfg sdfg sdfgsd fgdfg dsfg dfg dfg dsfg sdfgdf gsd', 2, 6, 'sales', 'oneOff', '2025-12-02', '12:00:00', '15:30:00', NULL, 'N', NULL, 1, 'day', NULL, NULL, NULL, NULL, '', 'never', NULL, NULL, 3, NULL, NULL, NULL, 'notStarted', 1, 'Medium', 4, '[\"7\",\"2\"]', NULL, 'ds asdf asdf sadf sadf sdaf sa', NULL, 'ds adsf sadf sadf sdf sf sdf', 0.00, NULL, NULL, NULL, 'N', 'N', 15, 'N', 210, 'Teams Meeting', 'https://teams.microsoft.com', 4, 3, 'N', '2025-12-02 12:14:53', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_attachments`
--

DROP TABLE IF EXISTS `tija_activity_attachments`;
CREATE TABLE IF NOT EXISTS `tija_activity_attachments` (
  `attachmentID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `fileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filePath` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fileSize` int DEFAULT NULL COMMENT 'Size in bytes',
  `uploadedBy` int NOT NULL,
  `uploadedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf8mb4_unicode_ci,
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`attachmentID`),
  KEY `idx_activity_attachments` (`activityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_categories`
--

DROP TABLE IF EXISTS `tija_activity_categories`;
CREATE TABLE IF NOT EXISTS `tija_activity_categories` (
  `activityCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityCategoryName` varchar(254) NOT NULL,
  `iconlink` varchar(255) DEFAULT NULL,
  `activityCategoryDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`activityCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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
-- Table structure for table `tija_activity_comments`
--

DROP TABLE IF EXISTS `tija_activity_comments`;
CREATE TABLE IF NOT EXISTS `tija_activity_comments` (
  `commentID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `commentText` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentBy` int NOT NULL,
  `commentOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parentCommentID` int DEFAULT NULL COMMENT 'For threaded comments',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`commentID`),
  KEY `idx_activity_comments` (`activityID`),
  KEY `idx_comment_parent` (`parentCommentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_expenses`
--

DROP TABLE IF EXISTS `tija_activity_expenses`;
CREATE TABLE IF NOT EXISTS `tija_activity_expenses` (
  `expenseID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `expenseDate` date NOT NULL,
  `expenseCategory` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expenseAmount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `expenseDescription` text COLLATE utf8mb4_unicode_ci,
  `expenseCurrency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES',
  `receiptNumber` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiptAttached` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `receiptPath` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymentMethod` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cash, Card, Mpesa, etc.',
  `reimbursable` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `reimbursementStatus` enum('pending','approved','rejected','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approvedBy` int DEFAULT NULL,
  `approvedOn` datetime DEFAULT NULL,
  `paidOn` datetime DEFAULT NULL,
  `addedBy` int NOT NULL,
  `addedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`expenseID`),
  KEY `idx_activity_expenses` (`activityID`),
  KEY `idx_expense_date` (`expenseDate`),
  KEY `idx_expense_category` (`expenseCategory`),
  KEY `idx_reimbursement_status` (`reimbursementStatus`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_history`
--

DROP TABLE IF EXISTS `tija_activity_history`;
CREATE TABLE IF NOT EXISTS `tija_activity_history` (
  `historyID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `fieldChanged` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `oldValue` text COLLATE utf8mb4_unicode_ci,
  `newValue` text COLLATE utf8mb4_unicode_ci,
  `changedBy` int NOT NULL,
  `changedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changeNote` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`historyID`),
  KEY `idx_activity_history` (`activityID`),
  KEY `idx_changed_on` (`changedOn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_log`
--

DROP TABLE IF EXISTS `tija_activity_log`;
CREATE TABLE IF NOT EXISTS `tija_activity_log` (
  `activityLogID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `objectType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `objectID` int NOT NULL,
  `objectName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`activityLogID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_participant_assignment`
--

DROP TABLE IF EXISTS `tija_activity_participant_assignment`;
CREATE TABLE IF NOT EXISTS `tija_activity_participant_assignment` (
  `activityParticipantID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityID` int NOT NULL,
  `participantUserID` int NOT NULL,
  `activityOwnerID` int NOT NULL,
  `recurring` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `recurringInterval` int DEFAULT NULL,
  `recurringIntervalUnit` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `activityStartDate` date NOT NULL,
  `activityEndDate` date DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `CreatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`activityParticipantID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_reminders`
--

DROP TABLE IF EXISTS `tija_activity_reminders`;
CREATE TABLE IF NOT EXISTS `tija_activity_reminders` (
  `reminderID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `reminderTime` datetime NOT NULL,
  `reminderType` enum('email','sms','notification','all') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'notification',
  `recipientID` int NOT NULL,
  `reminderSent` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `sentOn` datetime DEFAULT NULL,
  `reminderNote` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`reminderID`),
  KEY `idx_activity_reminders` (`activityID`),
  KEY `idx_reminder_time` (`reminderTime`),
  KEY `idx_reminder_sent` (`reminderSent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_activity_status`
--

DROP TABLE IF EXISTS `tija_activity_status`;
CREATE TABLE IF NOT EXISTS `tija_activity_status` (
  `activityStatusID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityStatusName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `activityStatusDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`activityStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_activity_types`;
CREATE TABLE IF NOT EXISTS `tija_activity_types` (
  `activityTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityTypeName` varchar(256) NOT NULL,
  `activityTypeDescription` text NOT NULL,
  `iconlink` varchar(256) NOT NULL,
  `activityCategoryID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`activityTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_adhoc_tasks`;
CREATE TABLE IF NOT EXISTS `tija_adhoc_tasks` (
  `adhocTaskID` int NOT NULL,
  `DateAdded` int NOT NULL,
  `adhoctaskTitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `adhocTaskDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `workSegmentID` int NOT NULL,
  `segmentActivityTaskID` int NOT NULL,
  `businessUnitID` int NOT NULL,
  `workTypeID` int NOT NULL,
  `approverUserID` int NOT NULL,
  `employeeID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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
  `isEmployee` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`adminID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_administrators`
--

INSERT INTO `tija_administrators` (`adminID`, `DateAdded`, `userID`, `adminTypeID`, `orgDataID`, `entityID`, `unitTypeID`, `unitID`, `isEmployee`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 14:19:31', 4, 1, 1, 0, 0, 0, 'Y', '2025-11-21 14:19:31', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_admin_types`
--

DROP TABLE IF EXISTS `tija_admin_types`;
CREATE TABLE IF NOT EXISTS `tija_admin_types` (
  `adminTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adminTypeName` varchar(256) NOT NULL,
  `adminCode` varchar(80) NOT NULL,
  `adminTypeDescription` text NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`adminTypeID`),
  UNIQUE KEY `adminCode` (`adminCode`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_advance_tax`;
CREATE TABLE IF NOT EXISTS `tija_advance_tax` (
  `advanceTaxID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `fiscalYear` int NOT NULL,
  `advanceTax` float(22,2) NOT NULL,
  `advanceTaxDescription` text,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`advanceTaxID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_assigned_project_tasks`
--

DROP TABLE IF EXISTS `tija_assigned_project_tasks`;
CREATE TABLE IF NOT EXISTS `tija_assigned_project_tasks` (
  `assignmentTaskID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `projectID` int DEFAULT NULL,
  `projectTaskID` int NOT NULL,
  `projectTeamMemberID` int NOT NULL,
  `assignmentStatus` enum('accepted','rejected','edit-request','assigned','pending','suspended') DEFAULT 'assigned',
  `notes` text,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`assignmentTaskID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_bau_activities`
--

DROP TABLE IF EXISTS `tija_bau_activities`;
CREATE TABLE IF NOT EXISTS `tija_bau_activities` (
  `activityID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
  `activityCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional activity code',
  `activityName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activityDescription` text COLLATE utf8mb4_unicode_ci,
  `estimatedDuration` decimal(10,2) DEFAULT NULL COMMENT 'Estimated hours',
  `displayOrder` int DEFAULT '0',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`activityID`),
  KEY `idx_process` (`processID`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Activities - Actionable units of work';

--
-- Dumping data for table `tija_bau_activities`
--

INSERT INTO `tija_bau_activities` (`activityID`, `processID`, `activityCode`, `activityName`, `activityDescription`, `estimatedDuration`, `displayOrder`, `isActive`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 1, '6.3.1.1', 'Collect Time and Attendance', 'Collect and validate employee time and attendance records', 2.00, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(2, 1, '6.3.1.2', 'Calculate Gross Pay', 'Calculate gross pay based on hours worked, rates, and overtime', 1.50, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(3, 1, '6.3.1.3', 'Calculate Deductions', 'Calculate payroll deductions including taxes, benefits, and other deductions', 2.00, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(4, 1, '6.3.1.4', 'Calculate Net Pay', 'Calculate net pay after all deductions', 0.50, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(5, 1, '6.3.1.5', 'Process Payroll Payments', 'Process and distribute payroll payments via direct deposit or checks', 1.00, 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(6, 1, '6.3.1.6', 'Generate Payroll Reports', 'Generate payroll reports and remit taxes and deductions', 1.00, 6, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(7, 6, '8.3.1.1', 'Receive and Verify Invoices', 'Receive vendor invoices and verify accuracy and authorization', 1.00, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(8, 6, '8.3.1.2', 'Match Invoices to Purchase Orders', 'Match invoices to purchase orders and receiving documents', 1.50, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(9, 6, '8.3.1.3', 'Obtain Approval', 'Obtain required approvals for invoice payment', 0.50, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(10, 6, '8.3.1.4', 'Process Payment', 'Process payment to vendor via check, ACH, or wire transfer', 1.00, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(11, 6, '8.3.1.5', 'Record in General Ledger', 'Record accounts payable transactions in general ledger', 0.50, 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(12, 10, '8.6.2.1', 'Retrieve Bank Statements', 'Retrieve bank statements and transaction records', 0.50, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(13, 10, '8.6.2.2', 'Compare Bank Records to General Ledger', 'Compare bank records to general ledger cash account', 2.00, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(14, 10, '8.6.2.3', 'Identify and Resolve Discrepancies', 'Identify discrepancies and resolve outstanding items', 1.50, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(15, 10, '8.6.2.4', 'Document Reconciliation', 'Document reconciliation results and file supporting documents', 0.50, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(16, 2, '6.3.2.1', 'Enroll Employees in Benefits', 'Process new employee benefit enrollments', 1.00, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(17, 2, '6.3.2.2', 'Process Benefit Changes', 'Process employee benefit changes and updates', 0.50, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(18, 2, '6.3.2.3', 'Reconcile Benefit Deductions', 'Reconcile benefit deductions with provider invoices', 1.00, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(19, 2, '6.3.2.4', 'Process Benefit Claims', 'Process and coordinate employee benefit claims', 1.50, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(20, 4, '6.4.1.1', 'Schedule Performance Reviews', 'Schedule performance review meetings with employees and managers', 0.50, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(21, 4, '6.4.1.2', 'Collect Performance Data', 'Collect performance data, goals, and feedback', 1.00, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(22, 4, '6.4.1.3', 'Conduct Review Meeting', 'Conduct performance review meeting with employee', 1.00, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(23, 4, '6.4.1.4', 'Document Review Results', 'Document performance review results and action items', 0.50, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(24, 4, '6.4.1.5', 'Set Development Goals', 'Set development goals and create improvement plans', 0.50, 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(25, 5, '6.4.2.1', 'Assess Training Needs', 'Assess organizational and individual training needs', 2.00, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(26, 5, '6.4.2.2', 'Develop Training Programs', 'Develop or select training programs to meet identified needs', 3.00, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(27, 5, '6.4.2.3', 'Schedule Training Sessions', 'Schedule training sessions and coordinate logistics', 1.00, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(28, 5, '6.4.2.4', 'Deliver Training', 'Deliver training sessions or coordinate external training', 4.00, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(29, 5, '6.4.2.5', 'Evaluate Training Effectiveness', 'Evaluate training effectiveness and gather feedback', 1.00, 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(30, 7, '8.3.2.1', 'Generate Customer Invoices', 'Generate invoices for products or services delivered', 1.00, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(31, 7, '8.3.2.2', 'Send Invoices to Customers', 'Send invoices to customers via email or mail', 0.50, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(32, 7, '8.3.2.3', 'Record Receivables', 'Record accounts receivable in general ledger', 0.50, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(33, 7, '8.3.2.4', 'Monitor Collections', 'Monitor accounts receivable aging and follow up on overdue accounts', 2.00, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(34, 7, '8.3.2.5', 'Process Customer Payments', 'Process customer payments and apply to invoices', 1.00, 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(35, 7, '8.3.2.6', 'Reconcile Receivables', 'Reconcile accounts receivable and resolve discrepancies', 1.00, 6, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(36, 8, '8.3.3.1', 'Post Journal Entries', 'Post journal entries to general ledger', 1.00, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(37, 8, '8.3.3.2', 'Reconcile General Ledger Accounts', 'Reconcile general ledger accounts monthly', 2.00, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(38, 8, '8.3.3.3', 'Maintain Chart of Accounts', 'Maintain and update chart of accounts structure', 1.00, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(39, 8, '8.3.3.4', 'Close Accounting Periods', 'Close accounting periods and prepare for next period', 2.00, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(40, 9, '8.6.1.1', 'Monitor Daily Cash Position', 'Monitor daily cash balances across all accounts', 0.50, 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(41, 9, '8.6.1.2', 'Forecast Cash Flow', 'Forecast short-term and long-term cash flow requirements', 2.00, 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(42, 9, '8.6.1.3', 'Optimize Cash Position', 'Optimize cash position through investments or borrowing', 1.00, 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(43, 9, '8.6.1.4', 'Manage Bank Relationships', 'Manage relationships with banks and financial institutions', 0.50, 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_bau_categories`
--

DROP TABLE IF EXISTS `tija_bau_categories`;
CREATE TABLE IF NOT EXISTS `tija_bau_categories` (
  `categoryID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APQC code (e.g., 7.0)',
  `categoryName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoryDescription` text COLLATE utf8mb4_unicode_ci,
  `displayOrder` int DEFAULT '0',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`categoryID`),
  UNIQUE KEY `unique_categoryCode` (`categoryCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Categories - Top-level domains';

--
-- Dumping data for table `tija_bau_categories`
--

INSERT INTO `tija_bau_categories` (`categoryID`, `categoryCode`, `categoryName`, `categoryDescription`, `displayOrder`, `isActive`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '1.0', 'Develop Vision and Strategy', 'Develop vision and strategy to guide the direction of the enterprise', 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(2, '2.0', 'Develop and Manage Products and Services', 'Develop and manage products and services to meet market needs', 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(3, '3.0', 'Market and Sell Products and Services', 'Market and sell products and services to customers', 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(4, '4.0', 'Deliver Products and Services', 'Deliver products and services to customers', 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(5, '5.0', 'Manage Customer Service', 'Manage customer service to ensure customer satisfaction', 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(6, '6.0', 'Develop and Manage Human Capital', 'Develop and manage human capital to enable individual and organizational success', 6, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(7, '7.0', 'Develop and Manage Human Capital', 'Develop and manage human capital to enable individual and organizational success', 7, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(8, '8.0', 'Manage Financial Resources', 'Manage financial resources to ensure financial viability', 8, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(9, '9.0', 'Acquire, Construct, and Manage Property', 'Acquire, construct, and manage property to support operations', 9, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(10, '10.0', 'Manage Information Technology', 'Manage information technology to support business processes', 10, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(11, '11.0', 'Manage Enterprise Risk, Compliance, Remediation, and Resiliency', 'Manage enterprise risk, compliance, remediation, and resiliency', 11, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(12, '12.0', 'Manage External Relationships', 'Manage external relationships to support business objectives', 12, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_bau_processes`
--

DROP TABLE IF EXISTS `tija_bau_processes`;
CREATE TABLE IF NOT EXISTS `tija_bau_processes` (
  `processID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processGroupID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_process_groups',
  `categoryID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_categories (denormalized from processGroup)',
  `processCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APQC code (e.g., 7.3.1)',
  `processName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processDescription` text COLLATE utf8mb4_unicode_ci,
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `isCustom` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Custom vs standard APQC process',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`processID`),
  UNIQUE KEY `unique_processCode` (`processCode`),
  KEY `idx_processGroup` (`processGroupID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_functionalAreaOwner` (`functionalAreaOwnerID`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`),
  KEY `idx_categoryID` (`categoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Processes - Specific workflows';

--
-- Dumping data for table `tija_bau_processes`
--

INSERT INTO `tija_bau_processes` (`processID`, `processGroupID`, `categoryID`, `processCode`, `processName`, `processDescription`, `functionalArea`, `functionalAreaID`, `functionalAreaOwnerID`, `isCustom`, `isActive`, `createdByID`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 3, 6, '6.3.1', 'Manage Payroll', 'Process payroll accurately and on time for all employees', 'HR', 2, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(2, 3, 6, '6.3.2', 'Manage Employee Benefits ', 'Administer employee benefits programs including health, retirement, and other benefits', 'HR', 2, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:10:06', NULL, 'N', 'N'),
(3, 3, 6, '6.3.3', 'Manage Employee Relations', 'Manage employee relations and workplace policies', 'HR', 2, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(4, 4, 6, '6.4.1', 'Conduct Performance Reviews', 'Conduct regular performance reviews and evaluations', 'HR', 2, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(5, 4, 6, '6.4.2', 'Manage Training and Development', 'Manage employee training and development programs', 'HR', 2, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(6, 7, 8, '8.3.1', 'Process Accounts Payable', 'Process accounts payable transactions including vendor invoices and payments', 'Finance', 1, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(7, 7, 8, '8.3.2', 'Process Accounts Receivable', 'Process accounts receivable transactions including customer invoices and collections', 'Finance', 1, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(8, 7, 8, '8.3.3', 'Process General Ledger', 'Process general ledger transactions and maintain chart of accounts', 'Finance', 1, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(9, 10, 8, '8.6.1', 'Manage Cash', 'Manage cash to ensure adequate liquidity and optimize cash position', 'Finance', 1, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N'),
(10, 10, 8, '8.6.2', 'Reconcile Bank Accounts', 'Reconcile bank accounts to ensure accuracy and identify discrepancies', 'Finance', 1, NULL, 'N', 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 13:07:37', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_bau_process_groups`
--

DROP TABLE IF EXISTS `tija_bau_process_groups`;
CREATE TABLE IF NOT EXISTS `tija_bau_process_groups` (
  `processGroupID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_categories',
  `processGroupCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APQC code (e.g., 7.3)',
  `processGroupName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processGroupDescription` text COLLATE utf8mb4_unicode_ci,
  `displayOrder` int DEFAULT '0',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`processGroupID`),
  UNIQUE KEY `unique_processGroupCode` (`processGroupCode`),
  KEY `idx_category` (`categoryID`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Process Groups - Functional areas within categories';

--
-- Dumping data for table `tija_bau_process_groups`
--

INSERT INTO `tija_bau_process_groups` (`processGroupID`, `categoryID`, `processGroupCode`, `processGroupName`, `processGroupDescription`, `displayOrder`, `isActive`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 6, '6.1', 'Develop Human Capital Strategy', 'Develop human capital strategy aligned with business objectives', 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(2, 6, '6.2', 'Attract, Source, and Select Talent', 'Attract, source, and select talent to meet workforce needs', 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(3, 6, '6.3', 'Reward and Retain Employees', 'Reward and retain employees to maintain workforce capability', 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(4, 6, '6.4', 'Develop and Deploy People', 'Develop and deploy people to build workforce capability', 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(5, 8, '8.1', 'Develop Financial Strategy and Plans', 'Develop financial strategy and plans to guide financial decisions', 1, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(6, 8, '8.2', 'Manage Financial Resources', 'Manage financial resources to ensure financial viability', 2, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(7, 8, '8.3', 'Process Financial Transactions', 'Process financial transactions accurately and efficiently', 3, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(8, 8, '8.4', 'Report Financial Information', 'Report financial information to stakeholders', 4, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(9, 8, '8.5', 'Manage Financial Risk', 'Manage financial risk to protect financial resources', 5, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N'),
(10, 8, '8.6', 'Manage Treasury Operations', 'Manage treasury operations to optimize cash and liquidity', 6, 'Y', '2025-11-29 15:09:38', '2025-11-29 12:09:38', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_benefit_types`
--

DROP TABLE IF EXISTS `tija_benefit_types`;
CREATE TABLE IF NOT EXISTS `tija_benefit_types` (
  `benefitTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `benefitName` varchar(255) NOT NULL,
  `benefitCode` varchar(50) NOT NULL,
  `benefitCategory` enum('insurance','pension','allowance','wellness','other') NOT NULL DEFAULT 'insurance',
  `description` text,
  `providerName` varchar(255) DEFAULT NULL,
  `providerContact` varchar(255) DEFAULT NULL,
  `employerContribution` decimal(10,2) DEFAULT '0.00',
  `employeeContribution` decimal(10,2) DEFAULT '0.00',
  `contributionType` enum('fixed','percentage') DEFAULT 'fixed',
  `isActive` enum('Y','N') DEFAULT 'Y',
  `sortOrder` int DEFAULT '0',
  `createdBy` int DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`benefitTypeID`),
  UNIQUE KEY `benefitCode` (`benefitCode`),
  KEY `benefitCategory` (`benefitCategory`),
  KEY `Suspended` (`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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

DROP TABLE IF EXISTS `tija_billing_rate`;
CREATE TABLE IF NOT EXISTS `tija_billing_rate` (
  `billingRateID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL,
  `billingRate` varchar(120) NOT NULL,
  `billingRateDescription` text NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`billingRateID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_billing_rates`;
CREATE TABLE IF NOT EXISTS `tija_billing_rates` (
  `billingRateID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `workTypeID` int NOT NULL,
  `billingRateName` varchar(256) DEFAULT NULL,
  `billingRateDescription` text,
  `workCategory` enum('sales','project','administartive') DEFAULT NULL,
  `doneByID` int NOT NULL,
  `hourlyRate` decimal(10,2) NOT NULL,
  `billingRateTypeID` int NOT NULL,
  `entityID` int NOT NULL,
  `projectID` int NOT NULL,
  `bill` enum('Y','N') NOT NULL DEFAULT 'Y',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`billingRateID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_billing_rate_types`;
CREATE TABLE IF NOT EXISTS `tija_billing_rate_types` (
  `billingRateTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `billingRateTypeName` varchar(255) NOT NULL,
  `billingRateTypeDescription` text,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`billingRateTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_bradford_factor`;
CREATE TABLE IF NOT EXISTS `tija_bradford_factor` (
  `bradfordFactorID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bradfordFactorName` varchar(255) NOT NULL,
  `bradfordFactorValue` decimal(4,2) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`bradfordFactorID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_business_units`;
CREATE TABLE IF NOT EXISTS `tija_business_units` (
  `businessUnitID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `businessUnitName` varchar(180) NOT NULL,
  `businessUnitDescription` text,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `unitTypeID` int DEFAULT NULL,
  `categoryID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`businessUnitID`),
  KEY `idx_category` (`categoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_business_units`
--

INSERT INTO `tija_business_units` (`businessUnitID`, `DateAdded`, `businessUnitName`, `businessUnitDescription`, `orgDataID`, `entityID`, `unitTypeID`, `categoryID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 12:35:59', 'Human Resource Advisory', 'HR Consultancy & Technology Enablement', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(2, '2025-11-21 12:36:44', 'Reconciliation Advisory', 'Reconciliation Product line advisory & Implementation', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(3, '2025-11-21 12:37:40', 'Risk & Compliance Advisory', 'Risk and Compliance Automation Advisory', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(4, '2025-11-21 12:39:14', 'Reporting Advisory', 'Group reporting & Quick console', 1, 1, 2, 5, '0000-00-00 00:00:00', 0, 'N', 'N'),
(5, '2025-12-01 16:57:27', 'Technology', NULL, 1, 1, NULL, NULL, '2025-12-01 16:57:27', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_business_unit_categories`
--

DROP TABLE IF EXISTS `tija_business_unit_categories`;
CREATE TABLE IF NOT EXISTS `tija_business_unit_categories` (
  `categoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `categoryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoryCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoryDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categoryOrder` int DEFAULT '1',
  `iconClass` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Font Awesome icon class',
  `colorCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color for UI display',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`categoryID`),
  UNIQUE KEY `idx_category_code` (`categoryCode`),
  KEY `idx_active` (`isActive`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Business unit categories for classification';

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

DROP TABLE IF EXISTS `tija_cases`;
CREATE TABLE IF NOT EXISTS `tija_cases` (
  `caseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `caseName` varchar(256) NOT NULL,
  `caseOwner` int NOT NULL,
  `caseType` varchar(80) NOT NULL,
  `clientID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `saleID` int DEFAULT NULL,
  `projectID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`caseID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_clients`
--

DROP TABLE IF EXISTS `tija_clients`;
CREATE TABLE IF NOT EXISTS `tija_clients` (
  `clientID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientCode` varchar(20) NOT NULL,
  `clientName` varchar(256) NOT NULL,
  `clientDescription` text,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `clientIndustryID` int DEFAULT NULL,
  `clientSectorID` int DEFAULT NULL,
  `clientLevelID` int NOT NULL DEFAULT '1',
  `clientPin` int DEFAULT NULL,
  `vatNumber` varchar(120) DEFAULT NULL,
  `accountOwnerID` int NOT NULL,
  `isClient` enum('Y','N') NOT NULL DEFAULT 'N',
  `inhouse` enum('Y','N') NOT NULL DEFAULT 'N',
  `countryID` int DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `clientStatus` enum('active','inactive') NOT NULL DEFAULT 'active',
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_clients`
--

INSERT INTO `tija_clients` (`clientID`, `DateAdded`, `clientCode`, `clientName`, `clientDescription`, `orgDataID`, `entityID`, `clientIndustryID`, `clientSectorID`, `clientLevelID`, `clientPin`, `vatNumber`, `accountOwnerID`, `isClient`, `inhouse`, `countryID`, `city`, `clientStatus`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-01 17:54:24', 'AEAL-128108', 'AARO East Africa Limited', NULL, 1, 1, 35, 8, 1, NULL, NULL, 4, 'N', 'N', 25, 'Nairobi', 'active', 4, '2025-12-01 17:54:24', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_addresses`
--

DROP TABLE IF EXISTS `tija_client_addresses`;
CREATE TABLE IF NOT EXISTS `tija_client_addresses` (
  `clientAddressID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `address` text NOT NULL,
  `postalCode` varchar(20) DEFAULT NULL,
  `clientEmail` int DEFAULT NULL,
  `City` varchar(120) NOT NULL,
  `countryID` int NOT NULL,
  `addressType` enum('officeAddress','postalAddress') NOT NULL,
  `billingAddress` enum('Y','N') NOT NULL DEFAULT 'N',
  `headquarters` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientAddressID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_client_addresses`
--

INSERT INTO `tija_client_addresses` (`clientAddressID`, `DateAdded`, `clientID`, `orgDataID`, `entityID`, `address`, `postalCode`, `clientEmail`, `City`, `countryID`, `addressType`, `billingAddress`, `headquarters`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-01 17:54:24', 1, 1, 1, 'International House, Mamangina Street', '00100', NULL, 'Nairobi', 25, 'officeAddress', '', 'N', 4, '2025-12-01 17:54:24', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_contacts`
--

DROP TABLE IF EXISTS `tija_client_contacts`;
CREATE TABLE IF NOT EXISTS `tija_client_contacts` (
  `clientContactID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `clientID` int NOT NULL,
  `contactTypeID` int DEFAULT NULL,
  `contactName` varchar(255) DEFAULT NULL,
  `title` varchar(80) DEFAULT NULL,
  `salutationID` int DEFAULT NULL,
  `contactEmail` varchar(256) DEFAULT NULL,
  `contactPhone` varchar(22) DEFAULT NULL,
  `clientAddressID` int DEFAULT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientContactID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_client_contacts`
--

INSERT INTO `tija_client_contacts` (`clientContactID`, `DateAdded`, `userID`, `clientID`, `contactTypeID`, `contactName`, `title`, `salutationID`, `contactEmail`, `contactPhone`, `clientAddressID`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-01 15:26:04', 0, 1, 1, 'Jason AAro', 'Procurement', NULL, 'jason@aaro.com', '+60722540168', 1, 4, '2025-12-02 10:06:52', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_documents`
--

DROP TABLE IF EXISTS `tija_client_documents`;
CREATE TABLE IF NOT EXISTS `tija_client_documents` (
  `clientDocumentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientDocumentName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `clientDocumentDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `documentTypeID` int NOT NULL,
  `clientID` int NOT NULL,
  `clientDocumentFile` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `documentFileName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `documentFileSize` int NOT NULL,
  `documentFileType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `documentFilePath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientDocumentID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_client_levels`
--

DROP TABLE IF EXISTS `tija_client_levels`;
CREATE TABLE IF NOT EXISTS `tija_client_levels` (
  `clientLevelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientLevelName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `clientLevelDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL DEFAULT '37',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientLevelID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_client_relationship_types`;
CREATE TABLE IF NOT EXISTS `tija_client_relationship_types` (
  `clientRelationshipTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clientRelationshipType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `clientRelationshipTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clientRelationshipTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_contact_relationships`
--

DROP TABLE IF EXISTS `tija_contact_relationships`;
CREATE TABLE IF NOT EXISTS `tija_contact_relationships` (
  `relationshipID` int NOT NULL AUTO_INCREMENT,
  `relationshipName` varchar(100) NOT NULL,
  `relationshipCode` varchar(50) NOT NULL,
  `description` text,
  `sortOrder` int DEFAULT '0',
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`relationshipID`),
  UNIQUE KEY `relationshipCode` (`relationshipCode`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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

DROP TABLE IF EXISTS `tija_contact_types`;
CREATE TABLE IF NOT EXISTS `tija_contact_types` (
  `contactTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contactType` varchar(120) NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`contactTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_delegation_assignments`;
CREATE TABLE IF NOT EXISTS `tija_delegation_assignments` (
  `delegationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delegatorID` int NOT NULL COMMENT 'Person delegating authority',
  `delegateID` int NOT NULL COMMENT 'Person receiving authority',
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `delegationType` enum('Full','Partial','Specific') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Partial',
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `reason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approvalScope` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'What can be approved',
  `financialLimit` decimal(15,2) DEFAULT NULL,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `approvedBy` int DEFAULT NULL,
  `approvedDate` datetime DEFAULT NULL,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`delegationID`),
  KEY `idx_delegator` (`delegatorID`),
  KEY `idx_delegate` (`delegateID`),
  KEY `idx_active` (`isActive`),
  KEY `idx_dates` (`startDate`,`endDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Temporary delegation of authority';

-- --------------------------------------------------------

--
-- Table structure for table `tija_document_types`
--

DROP TABLE IF EXISTS `tija_document_types`;
CREATE TABLE IF NOT EXISTS `tija_document_types` (
  `documentTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `documentTypeName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `DocumentTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`documentTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_document_types`
--

INSERT INTO `tija_document_types` (`documentTypeID`, `DateAdded`, `documentTypeName`, `DocumentTypeDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-06-19 16:32:38', 'Statutory Documents', 'These are the official records that a business is legally obligated to create, maintain, and in many cases, file with governmental authorities.', '2025-06-19 16:32:38', 37, 'N', 'N'),
(2, '2025-07-18 09:10:07', 'fdetey', 'gfhgfjh', '2025-07-18 09:10:07', 4, 'N', 'N'),
(3, '2025-07-23 03:40:32', 'KYC', 'Certificate of incorporation', '2025-07-23 10:40:32', 21, 'N', 'N'),
(4, '2025-08-26 02:05:19', 'Project Document', 'Documents that relate to spesific projects/assignments', '2025-08-26 09:05:19', 25, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_addresses`
--

DROP TABLE IF EXISTS `tija_employee_addresses`;
CREATE TABLE IF NOT EXISTS `tija_employee_addresses` (
  `addressID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `addressType` enum('home','work','postal','permanent','temporary') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `addressLine1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `addressLine2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `county` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'County/State/Province',
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postalCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Kenya',
  `landmark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nearby landmark for directions',
  `validFrom` date DEFAULT NULL COMMENT 'Address valid from date',
  `validTo` date DEFAULT NULL COMMENT 'Address valid until date',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes about this address',
  `isPrimary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`addressID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_address_type` (`addressType`),
  KEY `idx_primary` (`isPrimary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee addresses - current, permanent, postal';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_allowances`
--

DROP TABLE IF EXISTS `tija_employee_allowances`;
CREATE TABLE IF NOT EXISTS `tija_employee_allowances` (
  `allowanceID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL,
  `housingAllowance` decimal(15,2) DEFAULT '0.00',
  `transportAllowance` decimal(15,2) DEFAULT '0.00',
  `medicalAllowance` decimal(15,2) DEFAULT '0.00',
  `communicationAllowance` decimal(15,2) DEFAULT '0.00',
  `mealAllowance` decimal(15,2) DEFAULT '0.00',
  `otherAllowances` decimal(15,2) DEFAULT '0.00',
  `allowanceNotes` text,
  `bonusEligible` enum('Y','N') DEFAULT 'N',
  `overtimeEligible` enum('Y','N') DEFAULT 'N',
  `overtimeRate` decimal(5,2) DEFAULT '1.50',
  `commissionEligible` enum('Y','N') DEFAULT 'N',
  `commissionRate` decimal(5,2) DEFAULT '0.00',
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`allowanceID`),
  KEY `idx_employee` (`employeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_bank_accounts`
--

DROP TABLE IF EXISTS `tija_employee_bank_accounts`;
CREATE TABLE IF NOT EXISTS `tija_employee_bank_accounts` (
  `bankAccountID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int NOT NULL,
  `bankName` varchar(255) NOT NULL,
  `bankCode` varchar(50) DEFAULT NULL,
  `branchName` varchar(255) DEFAULT NULL,
  `branchCode` varchar(50) DEFAULT NULL,
  `accountNumber` varchar(100) NOT NULL,
  `accountName` varchar(255) NOT NULL,
  `accountType` enum('savings','checking','current','salary') DEFAULT 'salary',
  `currency` varchar(10) DEFAULT 'KES',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `allocationPercentage` decimal(5,2) DEFAULT '100.00',
  `swiftCode` varchar(50) DEFAULT NULL,
  `iban` varchar(100) DEFAULT NULL,
  `sortCode` varchar(50) DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `effectiveDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `isVerified` enum('Y','N') DEFAULT 'N',
  `verifiedDate` date DEFAULT NULL,
  `verifiedBy` int DEFAULT NULL,
  `notes` text,
  `createdBy` int DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`bankAccountID`),
  KEY `employeeID` (`employeeID`),
  KEY `isPrimary` (`isPrimary`),
  KEY `isActive` (`isActive`),
  KEY `Suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_bank_details`
--

DROP TABLE IF EXISTS `tija_employee_bank_details`;
CREATE TABLE IF NOT EXISTS `tija_employee_bank_details` (
  `bankDetailID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `bankName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bankCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branchName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branchCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accountName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `accountType` enum('savings','current','fixed_deposit','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'savings',
  `swiftCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iban` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'KES',
  `isPrimary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `isActiveForSalary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `salaryAllocationPercentage` decimal(5,2) DEFAULT '100.00',
  `sortOrder` int DEFAULT '0',
  `verificationStatus` enum('pending','verified','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verifiedBy` int DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`bankDetailID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_account_number` (`accountNumber`),
  KEY `idx_primary` (`isPrimary`),
  KEY `idx_active_salary` (`isActiveForSalary`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bank account details for salary deposits';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_benefits`
--

DROP TABLE IF EXISTS `tija_employee_benefits`;
CREATE TABLE IF NOT EXISTS `tija_employee_benefits` (
  `benefitID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `benefitTypeID` int NOT NULL,
  `benefitType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Medical, Life, Pension, etc.',
  `benefitName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `providerName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `policyNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `membershipNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coverageAmount` decimal(15,2) DEFAULT NULL,
  `employeeContribution` decimal(15,2) DEFAULT '0.00',
  `employerContribution` decimal(15,2) DEFAULT '0.00',
  `totalContribution` decimal(15,2) GENERATED ALWAYS AS ((`employeeContribution` + `employerContribution`)) STORED,
  `contributionFrequency` enum('monthly','quarterly','annually') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `coverageStartDate` date NOT NULL,
  `coverageEndDate` date DEFAULT NULL,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `beneficiaries` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of beneficiaries',
  `attachmentPath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `enrollmentDate` date NOT NULL,
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `coverageLevel` enum('individual','spouse','family','children') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'individual',
  `memberNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `totalPremium` decimal(10,2) DEFAULT '0.00',
  `dependentsCovered` int DEFAULT '0',
  `dependentIDs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `providerContact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `providerPolicyNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`benefitID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_benefit_type` (`benefitType`),
  KEY `idx_active` (`isActive`),
  KEY `idx_policy_number` (`policyNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee benefits enrollment and coverage';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_certifications`
--

DROP TABLE IF EXISTS `tija_employee_certifications`;
CREATE TABLE IF NOT EXISTS `tija_employee_certifications` (
  `certificationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `certificationName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `issuingOrganization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificationNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issueDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `doesNotExpire` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `verificationURL` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credentialID` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credentialURL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachmentPath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verificationStatus` enum('pending','verified','failed','not_required') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verifiedBy` int DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`certificationID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_certification_name` (`certificationName`),
  KEY `idx_expiry_date` (`expiryDate`),
  KEY `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Professional certifications';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_dependants`
--

DROP TABLE IF EXISTS `tija_employee_dependants`;
CREATE TABLE IF NOT EXISTS `tija_employee_dependants` (
  `dependantID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `fullName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Child, Spouse, Parent, etc.',
  `dateOfBirth` date NOT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthCertificateNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isStudent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isDisabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isDependentForTax` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `schoolName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `studentID` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bloodType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hasDisability` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `disabilityDetails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `medicalConditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `insuranceMemberNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isBeneficiary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Eligible for benefits',
  `benefitStartDate` date DEFAULT NULL,
  `benefitEndDate` date DEFAULT NULL,
  `allocationPercentage` decimal(5,2) DEFAULT '0.00',
  `phoneNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailAddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photoPath` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`dependantID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_beneficiary` (`isBeneficiary`),
  KEY `idx_relationship` (`relationship`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dependants for insurance and benefits';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_education`
--

DROP TABLE IF EXISTS `tija_employee_education`;
CREATE TABLE IF NOT EXISTS `tija_employee_education` (
  `educationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `institutionName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institutionType` enum('high_school','college','university','technical','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'university',
  `institutionCountry` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Kenya',
  `qualificationLevel` enum('high_school','diploma','degree','masters','phd','certificate','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `qualificationTitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `educationLevel` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primary, Secondary, Diploma, Degree, Masters, PhD, etc.',
  `fieldOfStudy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `degreeTitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `startDate` date DEFAULT NULL,
  `completionDate` date DEFAULT NULL,
  `isCompleted` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `certificateNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachmentPath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verificationStatus` enum('pending','verified','failed','not_required') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verifiedBy` int DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `sortOrder` int DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`educationID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_education_level` (`educationLevel`),
  KEY `idx_sort_order` (`sortOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Educational qualifications';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_emergency_contacts`
--

DROP TABLE IF EXISTS `tija_employee_emergency_contacts`;
CREATE TABLE IF NOT EXISTS `tija_employee_emergency_contacts` (
  `emergencyContactID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `contactName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Spouse, Parent, Sibling, Friend, etc.',
  `primaryPhoneNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secondaryPhoneNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `workPhoneNumber` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Work phone number',
  `emailAddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `county` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'County/State',
  `postalCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Postal code',
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Kenya',
  `isPrimary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `contactPriority` enum('primary','secondary','tertiary') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'secondary' COMMENT 'Priority level',
  `sortOrder` int DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `occupation` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Occupation of emergency contact',
  `employer` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Employer of emergency contact',
  `nationalID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'National ID/Passport',
  `bloodType` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Blood type',
  `medicalConditions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Medical conditions',
  `authorizedToCollectSalary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Can collect salary',
  `authorizedForMedicalDecisions` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Can make medical decisions',
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`emergencyContactID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_primary` (`isPrimary`),
  KEY `idx_sort_order` (`sortOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Emergency contact persons';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_extended_personal`
--

DROP TABLE IF EXISTS `tija_employee_extended_personal`;
CREATE TABLE IF NOT EXISTS `tija_employee_extended_personal` (
  `extendedPersonalID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `middleName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maidenName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maritalStatus` enum('single','married','divorced','widowed','separated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Kenyan',
  `passportNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passportIssueDate` date DEFAULT NULL,
  `passportExpiryDate` date DEFAULT NULL,
  `bloodGroup` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ethnicity` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `languagesSpoken` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of languages',
  `disabilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`extendedPersonalID`),
  UNIQUE KEY `idx_employee` (`employeeID`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_job_history`
--

DROP TABLE IF EXISTS `tija_employee_job_history`;
CREATE TABLE IF NOT EXISTS `tija_employee_job_history` (
  `jobHistoryID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL,
  `jobTitleID` int DEFAULT NULL,
  `departmentID` int DEFAULT NULL,
  `businessUnitID` int DEFAULT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'N',
  `responsibilities` text,
  `achievements` text,
  `changeReason` varchar(255) DEFAULT NULL,
  `salaryAtTime` decimal(15,2) DEFAULT NULL,
  `notes` text,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`jobHistoryID`),
  KEY `idx_employee` (`employeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_licenses`
--

DROP TABLE IF EXISTS `tija_employee_licenses`;
CREATE TABLE IF NOT EXISTS `tija_employee_licenses` (
  `licenseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `licenseType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Driving License, Professional License, etc.',
  `licenseName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `licenseNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `licenseCategory` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issuingAuthority` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issuingCountry` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Kenya',
  `issueDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `doesNotExpire` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `restrictions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachmentPath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verificationStatus` enum('pending','verified','failed','not_required') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `verifiedBy` int DEFAULT NULL,
  `verificationDate` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`licenseID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_license_type` (`licenseType`),
  KEY `idx_expiry_date` (`expiryDate`),
  KEY `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Professional licenses';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_next_of_kin`
--

DROP TABLE IF EXISTS `tija_employee_next_of_kin`;
CREATE TABLE IF NOT EXISTS `tija_employee_next_of_kin` (
  `nextOfKinID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `fullName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phoneNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternativePhone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emailAddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `county` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Kenya',
  `isPrimary` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `allocationPercentage` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage of benefits',
  `sortOrder` int DEFAULT '0',
  `occupation` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employer` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`nextOfKinID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_primary` (`isPrimary`),
  KEY `idx_sort_order` (`sortOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Next of kin for benefits and insurance';

--
-- Dumping data for table `tija_employee_next_of_kin`
--

INSERT INTO `tija_employee_next_of_kin` (`nextOfKinID`, `DateAdded`, `employeeID`, `fullName`, `relationship`, `dateOfBirth`, `gender`, `nationalID`, `phoneNumber`, `alternativePhone`, `emailAddress`, `address`, `city`, `county`, `country`, `isPrimary`, `allocationPercentage`, `sortOrder`, `occupation`, `employer`, `notes`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-13 17:22:26', 31, 'Felix Nyandega MAuncho', 'Parent', '2025-11-04', 'male', '2343456543', '0722540169', '0722540169', 'felixmauncho@gmail.com', 'Rainbow Towers\r\nP. O. BOX 20212 00100', 'Nairobi', 'Nairobi', 'Kenya', 'N', 20.00, 0, 'Communication Director', 'The University Of Nairobi', 'reer yer ert rtwy rw', 31, '2025-11-13 14:22:26', 31, '2025-11-13 14:22:31', 'N', 'Y'),
(2, '2025-11-13 17:56:37', 31, 'asfgsagasfdg', 'Parent', '2025-10-27', 'female', '23595758', '0722540169', '0722540169', 'johndoe@example.com', '2012002\r\nsuite 255, longhorn House', 'Nairobi', 'Nairobi', 'Kenya', 'N', 23.00, 0, 'Professor', 'The University Of Nairobi', 'sfadg fag asfdg a', 31, '2025-11-13 14:56:37', 31, '2025-11-13 14:56:37', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_salary_components`
--

DROP TABLE IF EXISTS `tija_employee_salary_components`;
CREATE TABLE IF NOT EXISTS `tija_employee_salary_components` (
  `employeeComponentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL,
  `salaryComponentID` int UNSIGNED NOT NULL,
  `componentValue` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Overrides default value',
  `valueType` enum('fixed','percentage','formula') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'How this value is applied',
  `applyTo` enum('basic_salary','gross_salary','taxable_income','net_salary') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'basic_salary',
  `effectiveDate` date NOT NULL COMMENT 'When this assignment starts',
  `endDate` date DEFAULT NULL COMMENT 'When this assignment ends',
  `isCurrent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Is this the current assignment?',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Is this component active for the employee?',
  `frequency` enum('every_payroll','monthly','bi-weekly','weekly','one-time') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'every_payroll',
  `oneTimePayrollDate` date DEFAULT NULL COMMENT 'For one-time components',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Reason for assignment or special notes',
  `assignedBy` int UNSIGNED DEFAULT NULL COMMENT 'Who assigned this component',
  `assignedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int UNSIGNED DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`employeeComponentID`),
  UNIQUE KEY `idx_unique_assignment` (`employeeID`,`salaryComponentID`,`effectiveDate`,`Suspended`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_component` (`salaryComponentID`),
  KEY `idx_current` (`isCurrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_salary_history`
--

DROP TABLE IF EXISTS `tija_employee_salary_history`;
CREATE TABLE IF NOT EXISTS `tija_employee_salary_history` (
  `salaryHistoryID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL,
  `oldBasicSalary` decimal(15,2) DEFAULT '0.00',
  `newBasicSalary` decimal(15,2) NOT NULL,
  `oldGrossSalary` decimal(15,2) DEFAULT '0.00',
  `newGrossSalary` decimal(15,2) NOT NULL,
  `changePercentage` decimal(5,2) DEFAULT '0.00',
  `changeReason` varchar(255) DEFAULT NULL,
  `effectiveDate` date NOT NULL,
  `approvedBy` int DEFAULT NULL,
  `approvalDate` date DEFAULT NULL,
  `notes` text,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`salaryHistoryID`),
  KEY `idx_employee` (`employeeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_skills`
--

DROP TABLE IF EXISTS `tija_employee_skills`;
CREATE TABLE IF NOT EXISTS `tija_employee_skills` (
  `skillID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `skillName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `skillCategory` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Technical, Soft, Language, etc.',
  `proficiencyLevel` enum('beginner','intermediate','advanced','expert') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'intermediate',
  `yearsOfExperience` decimal(4,1) DEFAULT NULL,
  `lastUsed` date DEFAULT NULL,
  `isCertified` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `certificationName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastUsedDate` date DEFAULT NULL,
  `certificationDate` date DEFAULT NULL,
  `certificationExpiry` date DEFAULT NULL,
  `endorsedBy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`skillID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_skill_category` (`skillCategory`),
  KEY `idx_proficiency` (`proficiencyLevel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Professional skills and competencies';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_subordinates`
--

DROP TABLE IF EXISTS `tija_employee_subordinates`;
CREATE TABLE IF NOT EXISTS `tija_employee_subordinates` (
  `subordinateMappingID` int NOT NULL AUTO_INCREMENT,
  `supervisorID` int UNSIGNED NOT NULL,
  `subordinateID` int UNSIGNED NOT NULL,
  `reportingType` enum('direct','functional','dotted_line') DEFAULT 'direct',
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `notes` text,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`subordinateMappingID`),
  KEY `idx_supervisor` (`supervisorID`),
  KEY `idx_subordinate` (`subordinateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_supervisors`
--

DROP TABLE IF EXISTS `tija_employee_supervisors`;
CREATE TABLE IF NOT EXISTS `tija_employee_supervisors` (
  `supervisorMappingID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL,
  `supervisorID` int UNSIGNED NOT NULL,
  `supervisorType` enum('direct','functional','dotted_line') DEFAULT 'direct',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') DEFAULT 'Y',
  `notes` text,
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`supervisorMappingID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_supervisor` (`supervisorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_supervisor_relationships`
--

DROP TABLE IF EXISTS `tija_employee_supervisor_relationships`;
CREATE TABLE IF NOT EXISTS `tija_employee_supervisor_relationships` (
  `relationshipID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int NOT NULL COMMENT 'Employee who reports to supervisor',
  `supervisorID` int NOT NULL COMMENT 'The supervisor',
  `relationshipType` enum('direct','indirect','dotted-line','functional','matrix') NOT NULL DEFAULT 'direct',
  `isPrimary` enum('Y','N') DEFAULT 'N',
  `percentage` decimal(5,2) DEFAULT '100.00',
  `effectiveDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `isActive` enum('Y','N') DEFAULT 'Y',
  `scope` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `projectID` int DEFAULT NULL,
  `notes` text,
  `createdBy` int DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') DEFAULT 'N',
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`relationshipID`),
  KEY `employeeID` (`employeeID`),
  KEY `supervisorID` (`supervisorID`),
  KEY `relationshipType` (`relationshipType`),
  KEY `isActive` (`isActive`),
  KEY `Suspended` (`Suspended`),
  KEY `idx_employee_active` (`employeeID`,`isActive`,`Suspended`),
  KEY `idx_supervisor_active` (`supervisorID`,`isActive`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_employee_supervisor_relationships`
--

INSERT INTO `tija_employee_supervisor_relationships` (`relationshipID`, `DateAdded`, `employeeID`, `supervisorID`, `relationshipType`, `isPrimary`, `percentage`, `effectiveDate`, `endDate`, `isActive`, `scope`, `department`, `projectID`, `notes`, `createdBy`, `updatedBy`, `updatedAt`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-24 17:25:07', 24, 4, 'direct', 'Y', 100.00, NULL, NULL, 'Y', 'Administrative', 'Administrative', NULL, '', 4, 4, '2025-11-24 17:25:07', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_employee_work_experience`
--

DROP TABLE IF EXISTS `tija_employee_work_experience`;
CREATE TABLE IF NOT EXISTS `tija_employee_work_experience` (
  `workExperienceID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID',
  `companyName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `companyIndustry` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyLocation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jobTitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employmentType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Full-time, Part-time, Contract, etc.',
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isCurrentEmployer` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `responsibilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `achievements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reasonForLeaving` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisorName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisorContact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `canContact` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthlyGrossSalary` decimal(15,2) DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'KES',
  `sortOrder` int DEFAULT '0',
  `createdBy` int DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`workExperienceID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_company` (`companyName`),
  KEY `idx_sort_order` (`sortOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Previous employment history';

-- --------------------------------------------------------

--
-- Table structure for table `tija_employment_status`
--

DROP TABLE IF EXISTS `tija_employment_status`;
CREATE TABLE IF NOT EXISTS `tija_employment_status` (
  `employmentStatusID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employmentStatusTitle` varchar(255) NOT NULL,
  `employmentStatusDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`employmentStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_entities`
--

INSERT INTO `tija_entities` (`entityID`, `DateAdded`, `entityName`, `entityDescription`, `entityTypeID`, `orgDataID`, `entityParentID`, `industrySectorID`, `registrationNumber`, `entityPIN`, `entityCity`, `entityCountry`, `entityPhoneNumber`, `entityEmail`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 06:58:17', 'SBSL Kenya', NULL, 1, 1, 0, 0, '98309', '', 'Nairobi', '25', 254, 'info@sbsl.co.ke', '2025-11-21 09:58:17', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entity_hr_assignments`
--

DROP TABLE IF EXISTS `tija_entity_hr_assignments`;
CREATE TABLE IF NOT EXISTS `tija_entity_hr_assignments` (
  `assignmentID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL,
  `userID` int NOT NULL,
  `roleType` enum('primary','substitute') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`assignmentID`),
  UNIQUE KEY `unique_entity_role` (`entityID`,`roleType`),
  UNIQUE KEY `unique_entity_user` (`entityID`,`userID`),
  KEY `idx_assignment_entity` (`entityID`),
  KEY `idx_assignment_user` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_entity_hr_assignments`
--

INSERT INTO `tija_entity_hr_assignments` (`assignmentID`, `entityID`, `userID`, `roleType`, `DateAdded`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, 1, 4, 'primary', '2025-11-24 14:18:25', '2025-11-24 14:18:25', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_entity_role_types`
--

DROP TABLE IF EXISTS `tija_entity_role_types`;
CREATE TABLE IF NOT EXISTS `tija_entity_role_types` (
  `roleTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `roleTypeName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name (e.g., Executive, Management)',
  `roleTypeCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Short code (e.g., EXEC, MGT)',
  `roleTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Description of the role type',
  `displayOrder` int DEFAULT '0' COMMENT 'Order for display in dropdowns',
  `colorCode` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#667eea' COMMENT 'Hex color code for badges',
  `iconClass` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-user-tie' COMMENT 'FontAwesome icon class',
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Is this a default/system role type',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Is this role type active',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`roleTypeID`),
  UNIQUE KEY `unique_roleTypeCode` (`roleTypeCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_Suspended` (`Suspended`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role types for organizational roles';

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
-- Table structure for table `tija_expense`
--

DROP TABLE IF EXISTS `tija_expense`;
CREATE TABLE IF NOT EXISTS `tija_expense` (
  `expenseID` int NOT NULL AUTO_INCREMENT COMMENT 'Unique expense identifier',
  `expenseNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique expense reference number (e.g., EXP-202412-0001)',
  `expenseCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Short expense code for quick reference',
  `employeeID` int NOT NULL COMMENT 'ID of employee who incurred the expense',
  `employeeCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Employee code for quick reference',
  `expenseTypeID` int NOT NULL COMMENT 'Reference to expense type (travel, meals, etc.)',
  `expenseCategoryID` int NOT NULL COMMENT 'Reference to expense category',
  `expenseStatusID` int NOT NULL DEFAULT '1' COMMENT 'Current status of the expense',
  `projectID` int DEFAULT NULL COMMENT 'Associated project ID if applicable',
  `clientID` int DEFAULT NULL COMMENT 'Associated client ID if applicable',
  `salesCaseID` int DEFAULT NULL COMMENT 'Associated sales case ID if applicable',
  `departmentID` int DEFAULT NULL COMMENT 'Department ID for expense allocation',
  `expenseDate` date NOT NULL COMMENT 'Date when expense was incurred',
  `submissionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date when expense was submitted',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Detailed description of the expense',
  `shortDescription` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Brief description for quick reference',
  `amount` decimal(12,2) NOT NULL COMMENT 'Expense amount (supports up to 999,999,999.99)',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES' COMMENT 'Currency code (ISO 4217)',
  `exchangeRate` decimal(10,6) DEFAULT '1.000000' COMMENT 'Exchange rate if different from base currency',
  `baseAmount` decimal(12,2) DEFAULT NULL COMMENT 'Amount converted to base currency',
  `taxAmount` decimal(10,2) DEFAULT '0.00' COMMENT 'Tax amount included in expense',
  `taxRate` decimal(5,2) DEFAULT '0.00' COMMENT 'Tax rate percentage',
  `netAmount` decimal(12,2) DEFAULT NULL COMMENT 'Net amount after tax deductions',
  `receiptRequired` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y' COMMENT 'Whether receipt is mandatory',
  `receiptAttached` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether receipt is attached',
  `receiptPath` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File path to receipt attachment',
  `receiptFileName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Original receipt filename',
  `receiptFileSize` int DEFAULT NULL COMMENT 'Receipt file size in bytes',
  `receiptMimeType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Receipt file MIME type',
  `approvalRequired` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y' COMMENT 'Whether approval is required',
  `approvalLevel` int DEFAULT '1' COMMENT 'Required approval level',
  `approvedBy` int DEFAULT NULL COMMENT 'ID of person who approved',
  `approvalDate` datetime DEFAULT NULL COMMENT 'Date of approval',
  `approvalNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Notes from approver',
  `approvalDeadline` datetime DEFAULT NULL COMMENT 'Approval deadline',
  `rejectedBy` int DEFAULT NULL COMMENT 'ID of person who rejected',
  `rejectionDate` datetime DEFAULT NULL COMMENT 'Date of rejection',
  `rejectionReason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Reason for rejection',
  `rejectionCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Standardized rejection code',
  `paymentMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD','MOBILE_MONEY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'BANK_TRANSFER' COMMENT 'Method of payment',
  `paymentDate` datetime DEFAULT NULL COMMENT 'Date of payment',
  `paymentReference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Payment reference number',
  `paidBy` int DEFAULT NULL COMMENT 'ID of person who processed payment',
  `paymentNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Payment processing notes',
  `paymentDeadline` datetime DEFAULT NULL COMMENT 'Payment deadline',
  `reimbursementAmount` decimal(12,2) DEFAULT NULL COMMENT 'Amount to be reimbursed',
  `reimbursementRate` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage of expense to reimburse',
  `reimbursementMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD','MOBILE_MONEY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'BANK_TRANSFER' COMMENT 'Method of reimbursement',
  `reimbursementDate` datetime DEFAULT NULL COMMENT 'Date of reimbursement',
  `reimbursementReference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Reimbursement reference',
  `budgetCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Budget code for expense allocation',
  `costCenter` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cost center for expense tracking',
  `budgetYear` year DEFAULT NULL COMMENT 'Budget year',
  `budgetMonth` tinyint DEFAULT NULL COMMENT 'Budget month (1-12)',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Location where expense was incurred',
  `vendor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vendor or merchant name',
  `vendorCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vendor code for tracking',
  `invoiceNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Invoice number if applicable',
  `invoiceDate` date DEFAULT NULL COMMENT 'Invoice date if applicable',
  `isRecurring` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this is a recurring expense',
  `recurringFrequency` enum('DAILY','WEEKLY','MONTHLY','QUARTERLY','YEARLY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Frequency of recurring expense',
  `isBillable` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether expense can be billed to client',
  `isTaxDeductible` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether expense is tax deductible',
  `requiresJustification` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether detailed justification is required',
  `isUrgent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether expense requires urgent processing',
  `orgDataID` int NOT NULL COMMENT 'Organization data ID',
  `entityID` int NOT NULL COMMENT 'Entity ID for multi-tenant support',
  `createdBy` int NOT NULL COMMENT 'ID of user who created the record',
  `createdDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `lastUpdatedBy` int DEFAULT NULL COMMENT 'ID of user who last updated the record',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether record is suspended',
  `isDeleted` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Soft delete flag',
  `deletedBy` int DEFAULT NULL COMMENT 'ID of user who deleted the record',
  `deletedDate` datetime DEFAULT NULL COMMENT 'Soft delete timestamp',
  PRIMARY KEY (`expenseID`),
  UNIQUE KEY `unique_expense_number` (`expenseNumber`),
  UNIQUE KEY `unique_expense_code` (`expenseCode`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_employee_code` (`employeeCode`),
  KEY `idx_expense_type` (`expenseTypeID`),
  KEY `idx_expense_category` (`expenseCategoryID`),
  KEY `idx_expense_status` (`expenseStatusID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_client` (`clientID`),
  KEY `idx_sales_case` (`salesCaseID`),
  KEY `idx_department` (`departmentID`),
  KEY `idx_expense_date` (`expenseDate`),
  KEY `idx_submission_date` (`submissionDate`),
  KEY `idx_amount` (`amount`),
  KEY `idx_currency` (`currency`),
  KEY `idx_approval_status` (`approvalRequired`,`approvedBy`),
  KEY `idx_payment_status` (`paymentMethod`,`paymentDate`),
  KEY `idx_reimbursement` (`reimbursementAmount`,`reimbursementDate`),
  KEY `idx_budget` (`budgetCode`,`budgetYear`,`budgetMonth`),
  KEY `idx_vendor` (`vendor`,`vendorCode`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`),
  KEY `idx_created_by` (`createdBy`),
  KEY `idx_created_date` (`createdDate`),
  KEY `idx_suspended` (`Suspended`),
  KEY `idx_deleted` (`isDeleted`),
  KEY `idx_employee_status` (`employeeID`,`expenseStatusID`),
  KEY `idx_date_status` (`expenseDate`,`expenseStatusID`),
  KEY `idx_amount_status` (`amount`,`expenseStatusID`),
  KEY `idx_approval_workflow` (`approvalRequired`,`approvalLevel`,`approvedBy`),
  KEY `idx_payment_workflow` (`paymentMethod`,`paymentDate`,`paidBy`),
  KEY `idx_budget_tracking` (`budgetCode`,`budgetYear`,`budgetMonth`,`amount`),
  KEY `idx_vendor_tracking` (`vendor`,`vendorCode`,`expenseDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comprehensive expense management table with full audit trail and workflow support';

--
-- Triggers `tija_expense`
--
DELIMITER $$
DROP TRIGGER IF EXISTS `tr_expense_calculate_net_amount`$$
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
END$$
DROP TRIGGER IF EXISTS `tr_expense_update_net_amount`$$
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
END$$
DROP TRIGGER IF EXISTS `tr_expense_validate_data`$$
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expenses`
--

DROP TABLE IF EXISTS `tija_expenses`;
CREATE TABLE IF NOT EXISTS `tija_expenses` (
  `expenseID` int NOT NULL AUTO_INCREMENT,
  `expenseNumber` varchar(50) NOT NULL,
  `employeeID` int NOT NULL,
  `expenseTypeID` int NOT NULL,
  `expenseCategoryID` int NOT NULL,
  `expenseStatusID` int NOT NULL DEFAULT '1',
  `projectID` int DEFAULT NULL,
  `clientID` int DEFAULT NULL,
  `salesCaseID` int DEFAULT NULL,
  `expenseDate` date NOT NULL,
  `submissionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'KES',
  `receiptRequired` enum('Y','N') DEFAULT 'Y',
  `receiptAttached` enum('Y','N') DEFAULT 'N',
  `receiptPath` varchar(255) DEFAULT NULL,
  `approvalRequired` enum('Y','N') DEFAULT 'Y',
  `approvedBy` int DEFAULT NULL,
  `approvalDate` datetime DEFAULT NULL,
  `approvalNotes` text,
  `rejectedBy` int DEFAULT NULL,
  `rejectionDate` datetime DEFAULT NULL,
  `rejectionReason` text,
  `paymentMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH') DEFAULT 'BANK_TRANSFER',
  `paymentDate` datetime DEFAULT NULL,
  `paymentReference` varchar(100) DEFAULT NULL,
  `paidBy` int DEFAULT NULL,
  `paymentNotes` text,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `createdBy` int NOT NULL,
  `createdDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdatedBy` int DEFAULT NULL,
  `lastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`expenseID`),
  UNIQUE KEY `expenseNumber` (`expenseNumber`),
  KEY `employeeID` (`employeeID`),
  KEY `expenseTypeID` (`expenseTypeID`),
  KEY `expenseCategoryID` (`expenseCategoryID`),
  KEY `expenseStatusID` (`expenseStatusID`),
  KEY `projectID` (`projectID`),
  KEY `clientID` (`clientID`),
  KEY `salesCaseID` (`salesCaseID`),
  KEY `orgDataID` (`orgDataID`),
  KEY `entityID` (`entityID`),
  KEY `expenseDate` (`expenseDate`),
  KEY `submissionDate` (`submissionDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_approvals`
--

DROP TABLE IF EXISTS `tija_expense_approvals`;
CREATE TABLE IF NOT EXISTS `tija_expense_approvals` (
  `approvalID` int NOT NULL AUTO_INCREMENT,
  `expenseID` int NOT NULL,
  `approverID` int NOT NULL,
  `approvalLevel` int NOT NULL DEFAULT '1',
  `approvalStatus` enum('PENDING','APPROVED','REJECTED','DELEGATED') DEFAULT 'PENDING',
  `approvalDate` datetime DEFAULT NULL,
  `approvalNotes` text,
  `delegatedTo` int DEFAULT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `createdBy` int NOT NULL,
  `createdDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`approvalID`),
  KEY `expenseID` (`expenseID`),
  KEY `approverID` (`approverID`),
  KEY `orgDataID` (`orgDataID`),
  KEY `entityID` (`entityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_attachments`
--

DROP TABLE IF EXISTS `tija_expense_attachments`;
CREATE TABLE IF NOT EXISTS `tija_expense_attachments` (
  `attachmentID` int NOT NULL AUTO_INCREMENT,
  `expenseID` int NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `filePath` varchar(500) NOT NULL,
  `fileSize` int DEFAULT NULL,
  `fileType` varchar(50) DEFAULT NULL,
  `uploadedBy` int NOT NULL,
  `uploadDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `Suspended` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`attachmentID`),
  KEY `expenseID` (`expenseID`),
  KEY `uploadedBy` (`uploadedBy`),
  KEY `orgDataID` (`orgDataID`),
  KEY `entityID` (`entityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_categories`
--

DROP TABLE IF EXISTS `tija_expense_categories`;
CREATE TABLE IF NOT EXISTS `tija_expense_categories` (
  `expenseCategoryID` int NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for expense category',
  `categoryName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name of the expense category',
  `categoryDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description of the category and its purpose',
  `categoryCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Short code for the category (e.g., TRAVEL, MEALS)',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether the category is currently active and available for use',
  `requiresReceipt` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether receipts are mandatory for expenses in this category',
  `maxAmount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum allowed amount for expenses in this category (NULL = no limit)',
  `minAmount` decimal(10,2) DEFAULT NULL COMMENT 'Minimum amount for expenses in this category (NULL = no minimum)',
  `requiresApproval` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether expenses in this category require approval',
  `approvalLevel` int DEFAULT '1' COMMENT 'Required approval level (1=Manager, 2=Director, etc.)',
  `autoApproveLimit` decimal(10,2) DEFAULT NULL COMMENT 'Amount below which expenses are auto-approved (NULL = manual approval always required)',
  `hasBudgetLimit` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this category has a budget limit',
  `monthlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Monthly budget limit for this category',
  `yearlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Yearly budget limit for this category',
  `budgetPeriod` enum('MONTHLY','QUARTERLY','YEARLY') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'MONTHLY' COMMENT 'Budget period for tracking',
  `parentCategoryID` int DEFAULT NULL COMMENT 'Parent category ID for hierarchical organization',
  `categoryLevel` int DEFAULT '1' COMMENT 'Level in category hierarchy (1=top level)',
  `sortOrder` int DEFAULT '0' COMMENT 'Display order for category listing',
  `isTaxable` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether expenses in this category are subject to tax',
  `taxRate` decimal(5,2) DEFAULT NULL COMMENT 'Tax rate percentage for this category (NULL = use default)',
  `taxInclusive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether amounts include tax (Y) or are tax-exclusive (N)',
  `reimbursementRate` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage of expense amount that can be reimbursed (100 = full reimbursement)',
  `reimbursementMethod` enum('CASH','BANK_TRANSFER','CHEQUE','PETTY_CASH','CREDIT_CARD') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'BANK_TRANSFER' COMMENT 'Default reimbursement method for this category',
  `requiresJustification` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether detailed justification is required for expenses in this category',
  `requiresProjectLink` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether expenses must be linked to a project',
  `requiresClientLink` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether expenses must be linked to a client',
  `requiresSalesCaseLink` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether expenses must be linked to a sales case',
  `notifyOnSubmission` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to notify approvers when expenses are submitted in this category',
  `notifyOnApproval` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to notify employee when expenses are approved',
  `notifyOnRejection` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to notify employee when expenses are rejected',
  `orgDataID` int NOT NULL COMMENT 'Organization data identifier',
  `entityID` int NOT NULL COMMENT 'Entity identifier within organization',
  `createdBy` int NOT NULL COMMENT 'User ID who created this category',
  `createdDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time when category was created',
  `lastUpdatedBy` int DEFAULT NULL COMMENT 'User ID who last updated this category',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date and time when category was last updated',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether the category is suspended/deleted',
  `categoryIcon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoryColor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`expenseCategoryID`),
  UNIQUE KEY `unique_category_code` (`categoryCode`,`orgDataID`,`entityID`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`),
  KEY `idx_active` (`isActive`),
  KEY `idx_suspended` (`Suspended`),
  KEY `idx_parent_category` (`parentCategoryID`),
  KEY `idx_sort_order` (`sortOrder`),
  KEY `idx_created_by` (`createdBy`),
  KEY `idx_created_date` (`createdDate`),
  KEY `idx_category_name` (`categoryName`),
  KEY `idx_category_code` (`categoryCode`),
  KEY `idx_max_amount` (`maxAmount`),
  KEY `idx_approval_level` (`approvalLevel`),
  KEY `idx_budget_limit` (`hasBudgetLimit`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Expense categories for organizing and managing different types of business expenses';

--
-- Dumping data for table `tija_expense_categories`
--

INSERT INTO `tija_expense_categories` (`expenseCategoryID`, `categoryName`, `categoryDescription`, `categoryCode`, `isActive`, `requiresReceipt`, `maxAmount`, `minAmount`, `requiresApproval`, `approvalLevel`, `autoApproveLimit`, `hasBudgetLimit`, `monthlyBudgetLimit`, `yearlyBudgetLimit`, `budgetPeriod`, `parentCategoryID`, `categoryLevel`, `sortOrder`, `isTaxable`, `taxRate`, `taxInclusive`, `reimbursementRate`, `reimbursementMethod`, `requiresJustification`, `requiresProjectLink`, `requiresClientLink`, `requiresSalesCaseLink`, `notifyOnSubmission`, `notifyOnApproval`, `notifyOnRejection`, `orgDataID`, `entityID`, `createdBy`, `createdDate`, `lastUpdatedBy`, `lastUpdated`, `Suspended`, `categoryIcon`, `categoryColor`) VALUES
(1, 'Travel', 'Business travel expenses including flights, accommodation, meals, and local transport', 'TRAVEL', 'Y', 'Y', 100000.00, 100.00, 'Y', 2, 5000.00, 'Y', 50000.00, 500000.00, 'MONTHLY', NULL, 1, 1, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(2, 'Meals & Entertainment', 'Client meetings, business meals, entertainment expenses, and hospitality', 'MEALS', 'Y', 'Y', 15000.00, 50.00, 'Y', 1, 2000.00, 'Y', 20000.00, 200000.00, 'MONTHLY', NULL, 1, 2, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(3, 'Office Supplies', 'Stationery, office equipment, supplies, and consumables', 'OFFICE', 'Y', 'Y', 10000.00, 10.00, 'Y', 1, 1000.00, 'Y', 15000.00, 150000.00, 'MONTHLY', NULL, 1, 3, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(4, 'Communication', 'Phone bills, internet, mobile data, and communication expenses', 'COMM', 'Y', 'Y', 5000.00, 50.00, 'Y', 1, 500.00, 'Y', 10000.00, 100000.00, 'MONTHLY', NULL, 1, 4, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(5, 'Transportation', 'Local transport, fuel, parking fees, and vehicle expenses', 'TRANS', 'Y', 'Y', 3000.00, 20.00, 'Y', 1, 500.00, 'Y', 8000.00, 80000.00, 'MONTHLY', NULL, 1, 5, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(6, 'Training & Development', 'Courses, conferences, professional development, and educational expenses', 'TRAINING', 'Y', 'Y', 50000.00, 100.00, 'Y', 2, 5000.00, 'Y', 30000.00, 300000.00, 'MONTHLY', NULL, 1, 6, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(7, 'Marketing', 'Marketing materials, advertising, promotional items, and brand expenses', 'MARKETING', 'Y', 'Y', 25000.00, 100.00, 'Y', 2, 2000.00, 'Y', 20000.00, 200000.00, 'MONTHLY', NULL, 1, 7, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(8, 'Equipment', 'IT equipment, tools, machinery, and capital expenses', 'EQUIPMENT', 'Y', 'Y', 200000.00, 500.00, 'Y', 3, 10000.00, 'Y', 50000.00, 500000.00, 'MONTHLY', NULL, 1, 8, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(9, 'Utilities', 'Electricity, water, office utilities, and facility expenses', 'UTILITIES', 'Y', 'Y', 30000.00, 100.00, 'Y', 1, 2000.00, 'Y', 25000.00, 250000.00, 'MONTHLY', NULL, 1, 9, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(10, 'Miscellaneous', 'Other business expenses not covered by specific categories', 'MISC', 'Y', 'Y', 5000.00, 10.00, 'Y', 1, 500.00, 'Y', 10000.00, 100000.00, 'MONTHLY', NULL, 1, 10, 'Y', 16.00, 'N', 100.00, 'BANK_TRANSFER', 'Y', 'N', 'N', 'N', 'Y', 'Y', 'Y', 1, 1, 1, '2025-09-17 18:08:11', NULL, NULL, 'N', NULL, NULL),
(12, 'Travel', 'Transportation and mileage', '', 'Y', 'Y', NULL, NULL, 'Y', 1, NULL, 'N', NULL, NULL, 'MONTHLY', NULL, 1, 0, 'Y', NULL, 'N', 100.00, 'BANK_TRANSFER', 'N', 'N', 'N', 'N', 'Y', 'Y', 'Y', 0, 0, 0, '2025-12-02 12:33:36', NULL, NULL, 'N', 'ri-taxi-line', '#007bff');

-- --------------------------------------------------------

--
-- Table structure for table `tija_expense_status`
--

DROP TABLE IF EXISTS `tija_expense_status`;
CREATE TABLE IF NOT EXISTS `tija_expense_status` (
  `expenseStatusID` int NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier for expense status',
  `statusName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name of the expense status',
  `statusDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description of the status and its meaning',
  `statusCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Short code for the status (e.g., DRAFT, SUBMITTED, APPROVED)',
  `statusColor` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#6c757d' COMMENT 'Hex color code for status display (e.g., #28a745 for green)',
  `statusIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icon class or name for status display',
  `statusPriority` int DEFAULT '0' COMMENT 'Priority level for status ordering (higher = more important)',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether the status is currently active and available for use',
  `isInitialStatus` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this is the initial status for new expenses',
  `isFinalStatus` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this is a final status (no further transitions allowed)',
  `requiresAction` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this status requires user action to proceed',
  `isApprovalStatus` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this status represents an approval state',
  `isRejectionStatus` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this status represents a rejection state',
  `isPendingStatus` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this status represents a pending state',
  `isPaidStatus` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether this status represents a paid state',
  `allowedTransitions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of status IDs that can transition from this status',
  `blockedTransitions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of status IDs that cannot transition from this status',
  `autoTransitionAfter` int DEFAULT NULL COMMENT 'Days after which status auto-transitions (NULL = no auto-transition)',
  `autoTransitionTo` int DEFAULT NULL COMMENT 'Status ID to auto-transition to after specified days',
  `notifyEmployee` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to notify employee when expense reaches this status',
  `notifyApprover` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether to notify approver when expense reaches this status',
  `notifyFinance` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether to notify finance team when expense reaches this status',
  `notifyManager` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether to notify manager when expense reaches this status',
  `emailTemplate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email template name for notifications',
  `smsTemplate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SMS template name for notifications',
  `notificationSubject` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Default notification subject line',
  `allowsEditing` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether expenses in this status can be edited',
  `allowsDeletion` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether expenses in this status can be deleted',
  `allowsAttachment` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether attachments can be added in this status',
  `requiresComment` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether a comment is required when transitioning to this status',
  `showInDashboard` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to show expenses with this status in dashboard',
  `showInReports` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to include expenses with this status in reports',
  `showInKanban` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Whether to show expenses with this status in kanban board',
  `kanbanColumnTitle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Custom title for kanban column (NULL = use statusName)',
  `orgDataID` int NOT NULL COMMENT 'Organization data identifier',
  `entityID` int NOT NULL COMMENT 'Entity identifier within organization',
  `createdBy` int NOT NULL COMMENT 'User ID who created this status',
  `createdDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time when status was created',
  `lastUpdatedBy` int DEFAULT NULL COMMENT 'User ID who last updated this status',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date and time when status was last updated',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Whether the status is suspended/deleted',
  PRIMARY KEY (`expenseStatusID`),
  UNIQUE KEY `unique_status_code` (`statusCode`,`orgDataID`,`entityID`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`),
  KEY `idx_active` (`isActive`),
  KEY `idx_suspended` (`Suspended`),
  KEY `idx_priority` (`statusPriority`),
  KEY `idx_initial_status` (`isInitialStatus`),
  KEY `idx_final_status` (`isFinalStatus`),
  KEY `idx_approval_status` (`isApprovalStatus`),
  KEY `idx_pending_status` (`isPendingStatus`),
  KEY `idx_paid_status` (`isPaidStatus`),
  KEY `idx_created_by` (`createdBy`),
  KEY `idx_created_date` (`createdDate`),
  KEY `idx_status_name` (`statusName`),
  KEY `idx_status_code` (`statusCode`),
  KEY `idx_status_color` (`statusColor`),
  KEY `idx_workflow_status` (`isPendingStatus`,`isApprovalStatus`,`isPaidStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Expense status definitions for managing expense workflow states';

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

DROP TABLE IF EXISTS `tija_expense_types`;
CREATE TABLE IF NOT EXISTS `tija_expense_types` (
  `expenseTypeID` int NOT NULL AUTO_INCREMENT,
  `typeName` varchar(100) NOT NULL COMMENT 'Display name of the expense type',
  `typeDescription` text COMMENT 'Detailed description of the expense type',
  `typeCode` varchar(20) NOT NULL COMMENT 'Short code for the expense type (e.g., REIMB)',
  `isActive` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether the expense type is currently active and available for use',
  `isReimbursable` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses of this type are reimbursable',
  `isPettyCash` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this is a petty cash expense type',
  `requiresReceipt` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether receipts are mandatory for expenses of this type',
  `maxAmount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum allowed amount for expenses of this type (NULL = no limit)',
  `minAmount` decimal(10,2) DEFAULT NULL COMMENT 'Minimum amount for expenses of this type (NULL = no minimum)',
  `requiresApproval` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses of this type require approval',
  `approvalLimit` decimal(10,2) DEFAULT NULL COMMENT 'Amount above which approval is required',
  `approvalLevel` int DEFAULT '1' COMMENT 'Required approval level (1=Manager, 2=Director, etc.)',
  `autoApproveLimit` decimal(10,2) DEFAULT NULL COMMENT 'Amount below which expenses are auto-approved (NULL = manual approval always required)',
  `hasBudgetLimit` enum('Y','N') DEFAULT 'N' COMMENT 'Whether this expense type has a budget limit',
  `monthlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Monthly budget limit for this expense type',
  `yearlyBudgetLimit` decimal(10,2) DEFAULT NULL COMMENT 'Yearly budget limit for this expense type',
  `budgetPeriod` enum('MONTHLY','QUARTERLY','YEARLY') DEFAULT 'MONTHLY' COMMENT 'Budget period for tracking',
  `parentTypeID` int DEFAULT NULL COMMENT 'Parent expense type ID for hierarchical organization',
  `typeLevel` int DEFAULT '1' COMMENT 'Level in expense type hierarchy (1=top level)',
  `sortOrder` int DEFAULT '0' COMMENT 'Display order for expense type listing',
  `isTaxable` enum('Y','N') DEFAULT 'Y' COMMENT 'Whether expenses of this type are subject to tax',
  `taxRate` decimal(5,2) DEFAULT NULL COMMENT 'Tax rate percentage for this expense type (NULL = use default)',
  `taxInclusive` enum('Y','N') DEFAULT 'N' COMMENT 'Whether amounts include tax (Y) or are tax-exclusive (N)',
  `reimbursementRate` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage of expense amount that can be reimbursed (100 = full reimbursement)',
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
  `expenseValidityDays` int DEFAULT '30' COMMENT 'Number of days after expense date that submission is valid',
  `submissionDeadlineDays` int DEFAULT '7' COMMENT 'Number of days after expense date to submit for reimbursement',
  `approvalDeadlineDays` int DEFAULT '3' COMMENT 'Number of days for approval deadline',
  `paymentDeadlineDays` int DEFAULT '7' COMMENT 'Number of days for payment after approval',
  `orgDataID` int NOT NULL COMMENT 'Organization data ID',
  `entityID` int NOT NULL COMMENT 'Entity ID',
  `createdBy` int UNSIGNED NOT NULL COMMENT 'ID of user who created this record',
  `createdDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when record was created',
  `lastUpdatedBy` int UNSIGNED DEFAULT NULL COMMENT 'ID of user who last updated this record',
  `lastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of last update',
  `Suspended` enum('Y','N') DEFAULT 'N' COMMENT 'Whether the expense type is suspended',
  PRIMARY KEY (`expenseTypeID`),
  UNIQUE KEY `typeCode` (`typeCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_requiresReceipt` (`requiresReceipt`),
  KEY `idx_isReimbursable` (`isReimbursable`),
  KEY `idx_isPettyCash` (`isPettyCash`),
  KEY `idx_requiresApproval` (`requiresApproval`),
  KEY `idx_approvalLevel` (`approvalLevel`),
  KEY `idx_parentTypeID` (`parentTypeID`),
  KEY `idx_typeLevel` (`typeLevel`),
  KEY `idx_sortOrder` (`sortOrder`),
  KEY `idx_isTaxable` (`isTaxable`),
  KEY `idx_defaultCurrency` (`defaultCurrency`),
  KEY `orgDataID` (`orgDataID`),
  KEY `entityID` (`entityID`),
  KEY `fk_expense_types_last_updated_by` (`lastUpdatedBy`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Comprehensive expense types table with advanced configuration options';

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

DROP TABLE IF EXISTS `tija_financial_statements`;
CREATE TABLE IF NOT EXISTS `tija_financial_statements` (
  `financialStatementID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `financialStatementTypeID` int NOT NULL,
  `fiscalYear` int NOT NULL,
  `fiscalPeriod` varchar(40) NOT NULL,
  `periodStartDate` date DEFAULT NULL,
  `periodEndDate` date DEFAULT NULL,
  `statementTypeNode` varchar(255) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL,
  `Suspended` enum('Y','N') NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `financialStatementTypeName` varchar(256) NOT NULL,
  PRIMARY KEY (`financialStatementID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_financial_statements_types`
--

DROP TABLE IF EXISTS `tija_financial_statements_types`;
CREATE TABLE IF NOT EXISTS `tija_financial_statements_types` (
  `financialStatementTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `financialStatementTypeName` varchar(255) NOT NULL,
  `financialStatementTypeDescription` text NOT NULL,
  `statementTypeNode` varchar(120) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`financialStatementTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_financial_statement_accounts`;
CREATE TABLE IF NOT EXISTS `tija_financial_statement_accounts` (
  `financialStatementAccountID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accountNode` varchar(256) NOT NULL,
  `accountName` varchar(256) NOT NULL,
  `parentAccountID` int NOT NULL,
  `accountCode` varchar(120) NOT NULL,
  `accountDescription` text,
  `accountType` enum('debit','credit') NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`financialStatementAccountID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_financial_statement_data`
--

DROP TABLE IF EXISTS `tija_financial_statement_data`;
CREATE TABLE IF NOT EXISTS `tija_financial_statement_data` (
  `financialStatementDataID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `financialStatementID` int NOT NULL,
  `financialStatementTypeID` int NOT NULL,
  `accountNode` varchar(255) NOT NULL,
  `accountName` varchar(255) NOT NULL,
  `accountCode` varchar(250) NOT NULL,
  `accountDescription` text,
  `accountType` varchar(120) NOT NULL,
  `accountCategory` varchar(256) NOT NULL,
  `debitValue` decimal(20,2) DEFAULT NULL,
  `creditValue` decimal(20,2) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL,
  PRIMARY KEY (`financialStatementDataID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_functional_areas`
--

DROP TABLE IF EXISTS `tija_functional_areas`;
CREATE TABLE IF NOT EXISTS `tija_functional_areas` (
  `functionalAreaID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `functionalAreaCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique code (e.g., FIN, HR, IT)',
  `functionalAreaName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name (e.g., Finance, Human Resources)',
  `functionalAreaDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of the functional area',
  `isShared` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Can be shared across organizations',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `displayOrder` int DEFAULT '0',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`functionalAreaID`),
  UNIQUE KEY `unique_functionalAreaCode` (`functionalAreaCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_isShared` (`isShared`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master functional areas that can be shared across organizations';

--
-- Dumping data for table `tija_functional_areas`
--

INSERT INTO `tija_functional_areas` (`functionalAreaID`, `functionalAreaCode`, `functionalAreaName`, `functionalAreaDescription`, `isShared`, `isActive`, `displayOrder`, `createdByID`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 'FIN', 'Finance', 'Financial management, accounting, treasury, and financial planning', 'Y', 'Y', 1, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(2, 'HR', 'Human Resources', 'Human capital management, recruitment, payroll, benefits, and employee relations', 'Y', 'Y', 2, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(3, 'IT', 'Information Technology', 'IT infrastructure, systems, applications, and technology support', 'Y', 'Y', 3, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(4, 'SALES', 'Sales', 'Sales operations, customer acquisition, and revenue generation', 'Y', 'Y', 4, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(5, 'MKTG', 'Marketing', 'Marketing strategy, campaigns, branding, and customer engagement', 'Y', 'Y', 5, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(6, 'LEGAL', 'Legal', 'Legal affairs, compliance, contracts, and risk management', 'Y', 'Y', 6, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(7, 'FAC', 'Facilities', 'Facilities management, property, maintenance, and workplace services', 'Y', 'Y', 7, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N'),
(8, 'CUSTOM', 'Custom', 'Custom functional area for organization-specific needs', 'Y', 'Y', 8, NULL, '2025-11-29 15:08:06', '2025-11-29 12:08:06', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_function_head_assignments`
--

DROP TABLE IF EXISTS `tija_function_head_assignments`;
CREATE TABLE IF NOT EXISTS `tija_function_head_assignments` (
  `assignmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL COMMENT 'FK to people - Function head',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `effectiveDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `permissions` json DEFAULT NULL COMMENT 'Specific permissions (define_processes, define_workflows, approve_sops, etc.)',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  PRIMARY KEY (`assignmentID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_effectiveDate` (`effectiveDate`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Function head assignments to functional areas';

-- --------------------------------------------------------

--
-- Table structure for table `tija_global_holidays`
--

DROP TABLE IF EXISTS `tija_global_holidays`;
CREATE TABLE IF NOT EXISTS `tija_global_holidays` (
  `holidayID` int NOT NULL AUTO_INCREMENT,
  `holidayName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the holiday',
  `holidayDate` date NOT NULL COMMENT 'Date of the holiday',
  `jurisdiction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Country, state, or "Global"',
  `holidayType` enum('Public','Religious','Cultural','Company','Regional') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Public',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Description of the holiday',
  `recurring` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether holiday recurs annually',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`holidayID`),
  KEY `idx_holiday_date` (`holidayDate`),
  KEY `idx_jurisdiction` (`jurisdiction`),
  KEY `idx_holiday_type` (`holidayType`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Global holidays for different jurisdictions';

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
(11, 'Jamhuri Day', '2025-12-12', 'Kenya', 'Public', 'Independence Day', 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_goals`
--

DROP TABLE IF EXISTS `tija_goals`;
CREATE TABLE IF NOT EXISTS `tija_goals` (
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID v4 for global uniqueness and sharding support',
  `parentGoalUUID` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Self-referencing FK for cascading goals',
  `ownerEntityID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID for entity-level goals',
  `ownerUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID for individual-level goals',
  `libraryRefID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_goal_library.libraryID if created from template',
  `goalType` enum('Strategic','OKR','KPI') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of goal',
  `goalTitle` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Goal title/name',
  `goalDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description',
  `propriety` enum('Low','Medium','High','Critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Medium' COMMENT 'Criticality level',
  `weight` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Weight percentage (0.0000-1.0000)',
  `progressMetric` json DEFAULT NULL COMMENT 'Progress tracking: {"current": 80, "target": 100, "unit": "USD", "currency": "USD"}',
  `evaluatorConfig` json DEFAULT NULL COMMENT 'Multi-rater configuration: {"manager_weight": 0.5, "peer_weight": 0.3, "self_weight": 0.2}',
  `jurisdictionID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID for L3 compliance rules',
  `visibility` enum('Global','Public','Private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Private' COMMENT 'Visibility scope',
  `cascadeMode` enum('Strict','Aligned','Hybrid','None') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'Cascade mode if this is a parent goal',
  `startDate` date NOT NULL COMMENT 'Goal start date',
  `endDate` date NOT NULL COMMENT 'Goal end date',
  `status` enum('Draft','Active','Completed','Cancelled','OnHold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft' COMMENT 'Goal status',
  `completionPercentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Calculated completion percentage',
  `sysStartTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Temporal versioning start',
  `sysEndTime` datetime DEFAULT NULL COMMENT 'Temporal versioning end (NULL = current version)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`goalUUID`),
  KEY `idx_parentGoal` (`parentGoalUUID`),
  KEY `idx_ownerEntity` (`ownerEntityID`),
  KEY `idx_ownerUser` (`ownerUserID`),
  KEY `idx_libraryRef` (`libraryRefID`),
  KEY `idx_goalType` (`goalType`),
  KEY `idx_status` (`status`),
  KEY `idx_propriety` (`propriety`),
  KEY `idx_dates` (`startDate`,`endDate`),
  KEY `idx_jurisdiction` (`jurisdictionID`),
  KEY `idx_temporal` (`sysStartTime`,`sysEndTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main goals table - supports Strategic Goals, OKRs, and KPIs';

--
-- Dumping data for table `tija_goals`
--

INSERT INTO `tija_goals` (`goalUUID`, `parentGoalUUID`, `ownerEntityID`, `ownerUserID`, `libraryRefID`, `goalType`, `goalTitle`, `goalDescription`, `propriety`, `weight`, `progressMetric`, `evaluatorConfig`, `jurisdictionID`, `visibility`, `cascadeMode`, `startDate`, `endDate`, `status`, `completionPercentage`, `sysStartTime`, `sysEndTime`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
('7fab7e49-ce00-11f0-ae0d-00ff532f7d82', NULL, 1, NULL, 1, 'Strategic', 'Achieve 25% Revenue Growth Over 5 Years', 'Strategic goal to drive significant revenue growth through market expansion, new product launches, and customer acquisition. This goal aligns with our long-term vision of becoming a market leader in our industry.', 'Critical', 0.3000, '{\"unit\": \"percentage\", \"target\": 25, \"current\": 0, \"baseline\": 0, \"currency\": \"USD\"}', '{\"peer_weight\": 0.20, \"self_weight\": 0.20, \"matrix_weight\": 0.00, \"manager_weight\": 0.50, \"subordinate_weight\": 0.10}', NULL, 'Global', 'Aligned', '2025-11-30', '2030-11-30', 'Active', 0.00, '2025-11-30 18:23:18', NULL, '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL, 'N', 'N'),
('7fb008eb-ce00-11f0-ae0d-00ff532f7d82', NULL, 1, NULL, 8, 'OKR', 'Improve Customer Satisfaction to 90%', 'Annual OKR to improve customer satisfaction scores through enhanced service quality, faster response times, and proactive customer engagement.', 'High', 0.2500, '{\"unit\": \"percentage\", \"target\": 90, \"current\": 75, \"baseline\": 70, \"currency\": null}', '{\"peer_weight\": 0.20, \"self_weight\": 0.30, \"matrix_weight\": 0.00, \"manager_weight\": 0.40, \"subordinate_weight\": 0.10}', NULL, 'Public', 'Strict', '2025-11-30', '2026-11-30', 'Active', 0.00, '2025-11-30 18:23:18', NULL, '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL, 'N', 'N'),
('7fb3d281-ce00-11f0-ae0d-00ff532f7d82', NULL, 1, NULL, 11, 'KPI', 'Maintain 99.9% Uptime for Core Systems', 'Monthly KPI to ensure high availability and reliability of critical IT systems and infrastructure.', 'Critical', 0.2000, '{\"unit\": \"percentage\", \"target\": 99.9, \"current\": 99.5, \"baseline\": 99.0, \"currency\": null}', '{\"peer_weight\": 0.10, \"self_weight\": 0.30, \"matrix_weight\": 0.00, \"manager_weight\": 0.60, \"subordinate_weight\": 0.00}', NULL, 'Private', 'None', '2025-11-30', '2025-12-30', 'Active', 0.00, '2025-11-30 18:23:18', NULL, '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL, 'N', 'N'),
('917b956f-6fc1-4629-a9c0-79395edd9d19', '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 1, NULL, NULL, 'Strategic', '', NULL, 'Medium', 0.0000, NULL, NULL, NULL, 'Private', 'Strict', '2025-12-01', '2026-12-01', 'Active', 0.00, '2025-12-01 12:43:23', NULL, '2025-12-01 15:43:23', '2025-12-01 12:43:23', 1, 'N', 'N'),
('ba94f304-ac7c-4efe-8003-2f5b4458ad61', '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 1, NULL, NULL, 'Strategic', '', NULL, 'Medium', 0.0000, NULL, NULL, NULL, 'Private', 'Strict', '2025-12-01', '2026-12-01', 'Active', 0.00, '2025-12-01 12:41:57', NULL, '2025-12-01 15:41:57', '2025-12-01 12:41:57', 1, 'N', 'N'),
('c9535352-0f85-471b-a0d5-7747c3cc3f86', '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 1, NULL, NULL, 'Strategic', '', NULL, 'Medium', 0.0000, NULL, NULL, NULL, 'Private', 'Strict', '2025-12-01', '2026-12-01', 'Active', 0.00, '2025-12-01 12:39:32', NULL, '2025-12-01 15:39:32', '2025-12-01 12:39:32', 1, 'N', 'N'),
('ecba4c13-e1ab-4acb-aef7-7d23e6cafa08', '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 1, NULL, NULL, 'Strategic', '', NULL, 'Medium', 0.0000, NULL, NULL, NULL, 'Private', 'Strict', '2025-12-01', '2026-12-01', 'Active', 0.00, '2025-12-01 12:40:51', NULL, '2025-12-01 15:40:51', '2025-12-01 12:40:51', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_cascade_log`
--

DROP TABLE IF EXISTS `tija_goal_cascade_log`;
CREATE TABLE IF NOT EXISTS `tija_goal_cascade_log` (
  `logID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentGoalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID - parent goal',
  `childGoalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID - child goal created',
  `cascadeMode` enum('Strict','Aligned','Hybrid') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mode used for cascade',
  `targetEntityID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID - where cascaded to',
  `targetUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - individual target if applicable',
  `cascadeDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When cascade was executed',
  `cascadedByUserID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - who executed cascade',
  `status` enum('Pending','Accepted','Rejected','Modified','AutoCreated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending' COMMENT 'Cascade status',
  `modificationNotes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes if status is Modified',
  `responseDate` datetime DEFAULT NULL COMMENT 'When target responded (accepted/rejected)',
  `respondedByUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - who responded',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`logID`),
  KEY `idx_parentGoal` (`parentGoalUUID`),
  KEY `idx_childGoal` (`childGoalUUID`),
  KEY `idx_cascadeMode` (`cascadeMode`),
  KEY `idx_targetEntity` (`targetEntityID`),
  KEY `idx_targetUser` (`targetUserID`),
  KEY `idx_status` (`status`),
  KEY `idx_cascadeDate` (`cascadeDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Cascade Log - Audit trail for goal cascading operations';

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_currency_rates`
--

DROP TABLE IF EXISTS `tija_goal_currency_rates`;
CREATE TABLE IF NOT EXISTS `tija_goal_currency_rates` (
  `rateID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `fromCurrency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO currency code (e.g., USD, EUR, JPY)',
  `toCurrency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO currency code (target currency)',
  `budgetRate` decimal(15,6) NOT NULL COMMENT 'Fixed budget rate (set at fiscal year start)',
  `spotRate` decimal(15,6) NOT NULL COMMENT 'Current spot rate',
  `effectiveDate` date NOT NULL COMMENT 'Date rate becomes effective',
  `expiryDate` date DEFAULT NULL COMMENT 'Date rate expires (NULL = current)',
  `fiscalYear` year NOT NULL COMMENT 'Fiscal year this rate applies to',
  `rateType` enum('Budget','Spot','Average') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Spot' COMMENT 'Type of rate',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`rateID`),
  UNIQUE KEY `unique_currency_date` (`fromCurrency`,`toCurrency`,`effectiveDate`,`rateType`),
  KEY `idx_fromCurrency` (`fromCurrency`),
  KEY `idx_toCurrency` (`toCurrency`),
  KEY `idx_effectiveDate` (`effectiveDate`),
  KEY `idx_fiscalYear` (`fiscalYear`),
  KEY `idx_rateType` (`rateType`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Currency Rates - Exchange rates for multi-currency performance normalization';

--
-- Dumping data for table `tija_goal_currency_rates`
--

INSERT INTO `tija_goal_currency_rates` (`rateID`, `fromCurrency`, `toCurrency`, `budgetRate`, `spotRate`, `effectiveDate`, `expiryDate`, `fiscalYear`, `rateType`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`) VALUES
(1, 'USD', 'USD', 1.000000, 1.000000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(2, 'USD', 'EUR', 0.920000, 0.930000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(3, 'USD', 'GBP', 0.790000, 0.800000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(4, 'USD', 'KES', 130.000000, 132.000000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(5, 'USD', 'ZAR', 18.500000, 18.700000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(6, 'USD', 'NGN', 750.000000, 760.000000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(7, 'USD', 'GHS', 12.000000, 12.200000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(8, 'USD', 'JPY', 150.000000, 151.000000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(9, 'USD', 'CNY', 7.200000, 7.250000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(10, 'USD', 'INR', 83.000000, 83.500000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(11, 'USD', 'USD', 1.000000, 1.000000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(12, 'USD', 'EUR', 0.920000, 0.920000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(13, 'USD', 'GBP', 0.790000, 0.790000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(14, 'USD', 'KES', 130.000000, 130.000000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(15, 'USD', 'ZAR', 18.500000, 18.500000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(16, 'USD', 'NGN', 750.000000, 750.000000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(17, 'USD', 'GHS', 12.000000, 12.000000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(18, 'USD', 'JPY', 150.000000, 150.000000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(19, 'USD', 'CNY', 7.200000, 7.200000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(20, 'USD', 'INR', 83.000000, 83.000000, '2025-11-30', '2026-11-30', '2025', 'Budget', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(21, 'EUR', 'USD', 1.086957, 1.075269, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(22, 'GBP', 'USD', 1.265823, 1.250000, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(23, 'KES', 'USD', 0.007692, 0.007576, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(24, 'ZAR', 'USD', 0.054054, 0.053476, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(25, 'NGN', 'USD', 0.001333, 0.001316, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(26, 'GHS', 'USD', 0.083333, 0.081967, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(27, 'JPY', 'USD', 0.006667, 0.006623, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(28, 'CNY', 'USD', 0.138889, 0.137931, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL),
(29, 'INR', 'USD', 0.012048, 0.011976, '2025-11-30', '2026-11-30', '2025', 'Spot', '2025-11-30 18:23:18', '2025-11-30 15:23:18', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_evaluations`
--

DROP TABLE IF EXISTS `tija_goal_evaluations`;
CREATE TABLE IF NOT EXISTS `tija_goal_evaluations` (
  `evaluationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `evaluatorUserID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - who is evaluating',
  `evaluatorRole` enum('Manager','Self','Peer','Subordinate','Matrix','External') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Role of evaluator',
  `score` decimal(5,2) NOT NULL COMMENT 'Score given (0.00-100.00)',
  `comments` text COLLATE utf8mb4_unicode_ci COMMENT 'Evaluation comments/feedback',
  `isAnonymous` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Is this evaluation anonymous',
  `evaluationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When evaluation was submitted',
  `status` enum('Draft','Submitted','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft' COMMENT 'Evaluation status',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`evaluationID`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_evaluator` (`evaluatorUserID`),
  KEY `idx_evaluatorRole` (`evaluatorRole`),
  KEY `idx_status` (`status`),
  KEY `idx_evaluationDate` (`evaluationDate`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_evaluation_weights`
--

DROP TABLE IF EXISTS `tija_goal_evaluation_weights`;
CREATE TABLE IF NOT EXISTS `tija_goal_evaluation_weights` (
  `weightID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `evaluatorRole` enum('Manager','Self','Peer','Subordinate','Matrix','External') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Role of evaluator',
  `weight` decimal(5,4) NOT NULL COMMENT 'Weight percentage (0.0000-1.0000)',
  `isDefault` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Is this a default weight (can be overridden)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`weightID`),
  UNIQUE KEY `unique_goal_role` (`goalUUID`,`evaluatorRole`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_evaluatorRole` (`evaluatorRole`)
) ;

--
-- Dumping data for table `tija_goal_evaluation_weights`
--

INSERT INTO `tija_goal_evaluation_weights` (`weightID`, `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`, `LastUpdate`) VALUES
(11, '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 'Manager', 0.5000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(12, '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 'Self', 0.2000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(13, '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 'Peer', 0.2000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(14, '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', 'Subordinate', 0.1000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(15, '7fb008eb-ce00-11f0-ae0d-00ff532f7d82', 'Manager', 0.4000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(16, '7fb008eb-ce00-11f0-ae0d-00ff532f7d82', 'Self', 0.3000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(17, '7fb008eb-ce00-11f0-ae0d-00ff532f7d82', 'Peer', 0.2000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(18, '7fb008eb-ce00-11f0-ae0d-00ff532f7d82', 'Subordinate', 0.1000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(19, '7fb3d281-ce00-11f0-ae0d-00ff532f7d82', 'Manager', 0.6000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(20, '7fb3d281-ce00-11f0-ae0d-00ff532f7d82', 'Self', 0.3000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47'),
(21, '7fb3d281-ce00-11f0-ae0d-00ff532f7d82', 'Peer', 0.1000, 'Y', '2025-11-30 18:26:47', '2025-11-30 15:26:47');

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_kpis`
--

DROP TABLE IF EXISTS `tija_goal_kpis`;
CREATE TABLE IF NOT EXISTS `tija_goal_kpis` (
  `kpiID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `kpiName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'KPI name',
  `kpiDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'KPI description',
  `measurementFrequency` enum('Daily','Weekly','Monthly','Quarterly','Annual','Continuous') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Monthly' COMMENT 'How often this KPI is measured',
  `baselineValue` decimal(15,2) DEFAULT NULL COMMENT 'Baseline value at start',
  `targetValue` decimal(15,2) NOT NULL COMMENT 'Target value to achieve',
  `currentValue` decimal(15,2) DEFAULT NULL COMMENT 'Current value',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unit of measurement (e.g., USD, %, hours)',
  `currencyCode` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISO currency code if monetary KPI',
  `reportingRate` decimal(15,6) DEFAULT NULL COMMENT 'Exchange rate for multi-currency normalization',
  `isPerpetual` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Is this a perpetual/continuous KPI',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`kpiID`),
  UNIQUE KEY `unique_goalUUID` (`goalUUID`),
  KEY `idx_currency` (`currencyCode`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='KPI-specific data - Key Performance Indicators';

--
-- Dumping data for table `tija_goal_kpis`
--

INSERT INTO `tija_goal_kpis` (`kpiID`, `goalUUID`, `kpiName`, `kpiDescription`, `measurementFrequency`, `baselineValue`, `targetValue`, `currentValue`, `unit`, `currencyCode`, `reportingRate`, `isPerpetual`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`) VALUES
(1, '7fb3d281-ce00-11f0-ae0d-00ff532f7d82', 'System Uptime Percentage', 'Percentage of time that core IT systems are operational and accessible to users', 'Monthly', 99.00, 99.90, 99.50, NULL, NULL, NULL, 'N', '2025-11-30 18:26:47', '2025-11-30 15:26:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_library`
--

DROP TABLE IF EXISTS `tija_goal_library`;
CREATE TABLE IF NOT EXISTS `tija_goal_library` (
  `libraryID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique template code (e.g., SALE-001)',
  `templateName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Template name',
  `templateDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Template description',
  `goalType` enum('Strategic','OKR','KPI') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of goal this template creates',
  `variables` json DEFAULT NULL COMMENT 'Parameterized fields: ["Product", "Target", "Timeframe"]',
  `defaultKPIs` json DEFAULT NULL COMMENT 'Suggested metrics: [{"name": "Revenue Growth", "target": 20}]',
  `jurisdictionDeny` json DEFAULT NULL COMMENT 'Array of jurisdiction codes where invalid: ["DE", "FR"]',
  `suggestedWeight` decimal(5,4) DEFAULT '0.2500' COMMENT 'Suggested weight (0.0000-1.0000)',
  `functionalDomain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Department/job family: Sales, IT, HR, Legal, Operations',
  `competencyLevel` enum('Junior','Senior','Principal','Executive','All') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All' COMMENT 'Required seniority level',
  `strategicPillar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'L0 objective it supports: Innovation, Revenue, ESG, Customer Intimacy',
  `timeHorizon` enum('5-Year','Annual','Quarterly','Sprint','Monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Annual' COMMENT 'Intended duration',
  `jurisdictionScope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Where valid: Global, EU-Only, Excludes-California',
  `broaderConceptID` int UNSIGNED DEFAULT NULL COMMENT 'SKOS: FK to parent concept in taxonomy',
  `narrowerConceptIDs` json DEFAULT NULL COMMENT 'SKOS: Array of child concept IDs',
  `relatedConceptIDs` json DEFAULT NULL COMMENT 'SKOS: Array of related concept IDs',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `usageCount` int UNSIGNED DEFAULT '0' COMMENT 'Number of times this template has been used',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`libraryID`),
  UNIQUE KEY `unique_templateCode` (`templateCode`),
  KEY `idx_goalType` (`goalType`),
  KEY `idx_functionalDomain` (`functionalDomain`),
  KEY `idx_competencyLevel` (`competencyLevel`),
  KEY `idx_strategicPillar` (`strategicPillar`),
  KEY `idx_timeHorizon` (`timeHorizon`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_broaderConcept` (`broaderConceptID`),
  KEY `LastUpdatedByID` (`LastUpdatedByID`)
) ;

--
-- Dumping data for table `tija_goal_library`
--

INSERT INTO `tija_goal_library` (`libraryID`, `templateCode`, `templateName`, `templateDescription`, `goalType`, `variables`, `defaultKPIs`, `jurisdictionDeny`, `suggestedWeight`, `functionalDomain`, `competencyLevel`, `strategicPillar`, `timeHorizon`, `jurisdictionScope`, `broaderConceptID`, `narrowerConceptIDs`, `relatedConceptIDs`, `isActive`, `usageCount`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 'STRAT-001', 'Achieve [Target]% Revenue Growth', 'Strategic goal to drive significant revenue growth over the strategic planning period. Focuses on expanding market share, entering new markets, or launching new products.', 'Strategic', '[\"Target\", \"Timeframe\", \"Market\"]', '[{\"name\": \"Revenue Growth Rate\", \"unit\": \"percentage\", \"target\": 25}, {\"name\": \"Market Share\", \"unit\": \"percentage\", \"target\": 15}, {\"name\": \"New Customer Acquisition\", \"unit\": \"count\", \"target\": 1000}]', NULL, 0.3000, 'Sales', 'Executive', 'Revenue', '5-Year', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(2, 'STRAT-002', 'Expand into [Region/Market]', 'Strategic expansion goal to enter new geographical markets or customer segments. Includes market research, regulatory compliance, and infrastructure setup.', 'Strategic', '[\"Region\", \"Market Segment\", \"Investment Budget\"]', '[{\"name\": \"Market Entry Success\", \"unit\": \"percentage\", \"target\": 100}, {\"name\": \"Revenue from New Market\", \"unit\": \"USD\", \"target\": 5000000}, {\"name\": \"Regulatory Approvals\", \"unit\": \"count\", \"target\": 5}]', NULL, 0.2500, 'Business Development', 'Executive', 'Revenue', '5-Year', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(3, 'STRAT-003', 'Launch [Number] Innovative Products/Services', 'Strategic innovation goal to develop and launch new products or services that create competitive advantage.', 'Strategic', '[\"Number\", \"Product Category\", \"Innovation Type\"]', '[{\"name\": \"Products Launched\", \"unit\": \"count\", \"target\": 5}, {\"name\": \"R&D Investment\", \"unit\": \"USD\", \"target\": 2000000}, {\"name\": \"Time to Market\", \"unit\": \"months\", \"target\": 18}]', NULL, 0.2000, 'Product Development', 'Executive', 'Innovation', '5-Year', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(4, 'STRAT-004', 'Achieve [Target]% Digital Transformation', 'Strategic goal to transform business operations through digital technologies, automation, and data-driven decision making.', 'Strategic', '[\"Target\", \"Transformation Area\", \"Technology Stack\"]', '[{\"name\": \"Digital Maturity Score\", \"unit\": \"percentage\", \"target\": 80}, {\"name\": \"Processes Automated\", \"unit\": \"count\", \"target\": 50}, {\"name\": \"Data Analytics Adoption\", \"unit\": \"percentage\", \"target\": 90}]', NULL, 0.2500, 'IT', 'Executive', 'Innovation', '5-Year', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(5, 'STRAT-005', 'Achieve Carbon Neutrality by [Year]', 'Strategic environmental goal to achieve carbon neutrality through renewable energy, efficiency improvements, and carbon offset programs.', 'Strategic', '[\"Year\", \"Baseline Emissions\", \"Reduction Strategy\"]', '[{\"name\": \"Carbon Emissions Reduction\", \"unit\": \"percentage\", \"target\": 100}, {\"name\": \"Renewable Energy Usage\", \"unit\": \"percentage\", \"target\": 100}, {\"name\": \"ESG Rating Score\", \"unit\": \"score\", \"target\": 85}]', NULL, 0.1500, 'Operations', 'Executive', 'ESG Impact', '5-Year', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(6, 'STRAT-006', 'Achieve [Target]% Employee Engagement Score', 'Strategic human capital goal to improve employee satisfaction, retention, and productivity through engagement initiatives.', 'Strategic', '[\"Target\", \"Engagement Driver\", \"Measurement Method\"]', '[{\"name\": \"Employee Engagement Score\", \"unit\": \"percentage\", \"target\": 85}, {\"name\": \"Employee Retention Rate\", \"unit\": \"percentage\", \"target\": 90}, {\"name\": \"Training Hours per Employee\", \"unit\": \"hours\", \"target\": 40}]', NULL, 0.2000, 'HR', 'Executive', 'Employee Engagement', '5-Year', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(7, 'OKR-001', 'Increase [Product] Sales by [Target]%', 'OKR template for sales teams to achieve specific product sales targets through focused execution.', 'OKR', '[\"Product\", \"Target\", \"Sales Channel\"]', '[{\"name\": \"Sales Revenue\", \"unit\": \"USD\", \"target\": 1000000}, {\"name\": \"Units Sold\", \"unit\": \"count\", \"target\": 5000}, {\"name\": \"Conversion Rate\", \"unit\": \"percentage\", \"target\": 25}]', NULL, 0.3000, 'Sales', 'All', 'Revenue', 'Annual', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(8, 'OKR-002', 'Improve Customer Satisfaction to [Target]%', 'OKR template focused on improving customer satisfaction scores through service quality improvements.', 'OKR', '[\"Target\", \"Customer Segment\", \"Satisfaction Metric\"]', '[{\"name\": \"NPS Score\", \"unit\": \"score\", \"target\": 70}, {\"name\": \"Customer Satisfaction\", \"unit\": \"percentage\", \"target\": 90}, {\"name\": \"Customer Complaints\", \"unit\": \"count\", \"target\": 5}]', NULL, 0.2500, 'Customer Service', 'All', 'Customer Intimacy', 'Quarterly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(9, 'OKR-003', 'Reduce [Process] Cycle Time by [Target]%', 'OKR template for operational efficiency improvements through process optimization.', 'OKR', '[\"Process\", \"Target\", \"Current Baseline\"]', '[{\"name\": \"Cycle Time Reduction\", \"unit\": \"percentage\", \"target\": 30}, {\"name\": \"Process Efficiency\", \"unit\": \"percentage\", \"target\": 85}, {\"name\": \"Cost Savings\", \"unit\": \"USD\", \"target\": 100000}]', NULL, 0.2000, 'Operations', 'All', 'Operational Excellence', 'Quarterly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(10, 'OKR-004', 'Launch [Product/Feature] by [Date]', 'OKR template for product development teams to deliver new products or features on schedule.', 'OKR', '[\"Product\", \"Date\", \"Quality Standard\"]', '[{\"name\": \"On-Time Delivery\", \"unit\": \"percentage\", \"target\": 100}, {\"name\": \"Quality Score\", \"unit\": \"percentage\", \"target\": 95}, {\"name\": \"User Adoption\", \"unit\": \"percentage\", \"target\": 80}]', NULL, 0.3000, 'Product Development', 'All', 'Innovation', 'Quarterly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(11, 'KPI-001', 'Maintain [Target]% Uptime for [System]', 'KPI template for IT operations to ensure system availability and reliability.', 'KPI', '[\"Target\", \"System\", \"Measurement Period\"]', '[{\"name\": \"System Uptime\", \"unit\": \"percentage\", \"target\": 99.9}, {\"name\": \"Mean Time to Recovery\", \"unit\": \"hours\", \"target\": 4}, {\"name\": \"Incident Count\", \"unit\": \"count\", \"target\": 2}]', NULL, 0.2000, 'IT', 'All', 'Operational Excellence', 'Monthly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(12, 'KPI-002', 'Achieve [Target]% Employee Retention Rate', 'KPI template for HR to track and improve employee retention.', 'KPI', '[\"Target\", \"Employee Segment\", \"Retention Period\"]', '[{\"name\": \"Retention Rate\", \"unit\": \"percentage\", \"target\": 90}, {\"name\": \"Voluntary Turnover\", \"unit\": \"percentage\", \"target\": 5}, {\"name\": \"Average Tenure\", \"unit\": \"months\", \"target\": 36}]', NULL, 0.1500, 'HR', 'All', 'Employee Engagement', 'Quarterly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(13, 'KPI-003', 'Process [Target] Invoices per Month', 'KPI template for finance/accounting teams to measure processing efficiency.', 'KPI', '[\"Target\", \"Invoice Type\", \"Processing Standard\"]', '[{\"name\": \"Invoices Processed\", \"unit\": \"count\", \"target\": 1000}, {\"name\": \"Processing Accuracy\", \"unit\": \"percentage\", \"target\": 99.5}, {\"name\": \"Average Processing Time\", \"unit\": \"days\", \"target\": 2}]', NULL, 0.1500, 'Finance', 'All', 'Operational Excellence', 'Monthly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(14, 'KPI-004', 'Generate [Target] Qualified Leads per Month', 'KPI template for marketing teams to track lead generation performance.', 'KPI', '[\"Target\", \"Lead Source\", \"Qualification Criteria\"]', '[{\"name\": \"Qualified Leads\", \"unit\": \"count\", \"target\": 500}, {\"name\": \"Lead Conversion Rate\", \"unit\": \"percentage\", \"target\": 20}, {\"name\": \"Cost per Lead\", \"unit\": \"USD\", \"target\": 50}]', NULL, 0.2000, 'Marketing', 'All', 'Revenue', 'Monthly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(15, 'KPI-005', 'Maintain [Target]% Gross Profit Margin', 'KPI template for finance/operations to track profitability.', 'KPI', '[\"Target\", \"Product Line\", \"Cost Category\"]', '[{\"name\": \"Gross Profit Margin\", \"unit\": \"percentage\", \"target\": 35}, {\"name\": \"Operating Margin\", \"unit\": \"percentage\", \"target\": 20}, {\"name\": \"Cost Reduction\", \"unit\": \"percentage\", \"target\": 10}]', NULL, 0.2500, 'Finance', 'All', 'Revenue', 'Quarterly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(16, 'KPI-006', 'Resolve [Target]% of Support Tickets within SLA', 'KPI template for customer support teams.', 'KPI', '[\"Target\", \"Ticket Priority\", \"SLA Standard\"]', '[{\"name\": \"SLA Compliance\", \"unit\": \"percentage\", \"target\": 95}, {\"name\": \"Average Resolution Time\", \"unit\": \"hours\", \"target\": 24}, {\"name\": \"First Contact Resolution\", \"unit\": \"percentage\", \"target\": 80}]', NULL, 0.2000, 'Customer Service', 'All', 'Customer Intimacy', 'Monthly', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N'),
(17, 'OKR-005', 'Complete [Number] Training Programs', 'OKR template for learning and development initiatives.', 'OKR', '[\"Number\", \"Training Type\", \"Target Audience\"]', '[{\"name\": \"Programs Completed\", \"unit\": \"count\", \"target\": 10}, {\"name\": \"Participant Satisfaction\", \"unit\": \"percentage\", \"target\": 85}, {\"name\": \"Skills Improvement\", \"unit\": \"percentage\", \"target\": 30}]', NULL, 0.2000, 'HR', 'All', 'Employee Engagement', 'Annual', 'Global', NULL, NULL, NULL, 'Y', 0, '2025-11-30 18:13:04', '2025-11-30 15:13:04', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_library_versions`
--

DROP TABLE IF EXISTS `tija_goal_library_versions`;
CREATE TABLE IF NOT EXISTS `tija_goal_library_versions` (
  `versionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `libraryID` int UNSIGNED NOT NULL COMMENT 'FK to tija_goal_library.libraryID',
  `versionNumber` int UNSIGNED NOT NULL COMMENT 'Version number (1, 2, 3, ...)',
  `templateData` json NOT NULL COMMENT 'Complete snapshot of template at this version',
  `changeDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of changes in this version',
  `effectiveDate` date NOT NULL COMMENT 'Date this version became effective',
  `deprecatedDate` date DEFAULT NULL COMMENT 'Date this version was deprecated (NULL = current)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`versionID`),
  UNIQUE KEY `unique_library_version` (`libraryID`,`versionNumber`),
  KEY `idx_libraryID` (`libraryID`),
  KEY `idx_effectiveDate` (`effectiveDate`),
  KEY `LastUpdatedByID` (`LastUpdatedByID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Library Versions - Template versioning and change tracking';

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_matrix_assignments`
--

DROP TABLE IF EXISTS `tija_goal_matrix_assignments`;
CREATE TABLE IF NOT EXISTS `tija_goal_matrix_assignments` (
  `assignmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `employeeUserID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - employee receiving goal',
  `matrixManagerID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - functional/matrix manager',
  `administrativeManagerID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - legal entity manager',
  `assignmentType` enum('Functional','Project','Matrix','Temporary') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Matrix' COMMENT 'Type of assignment',
  `allocationPercent` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage allocation if partial (0.00-100.00)',
  `projectID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_projects.projectID if project-based',
  `startDate` date NOT NULL COMMENT 'Assignment start date',
  `endDate` date DEFAULT NULL COMMENT 'Assignment end date (NULL = ongoing)',
  `status` enum('Active','Completed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`assignmentID`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_employee` (`employeeUserID`),
  KEY `idx_matrixManager` (`matrixManagerID`),
  KEY `idx_adminManager` (`administrativeManagerID`),
  KEY `idx_assignmentType` (`assignmentType`),
  KEY `idx_projectID` (`projectID`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`startDate`,`endDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Matrix Assignments - Cross-border and matrix goal assignments';

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_okrs`
--

DROP TABLE IF EXISTS `tija_goal_okrs`;
CREATE TABLE IF NOT EXISTS `tija_goal_okrs` (
  `okrID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `objective` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Qualitative Objective (the O in OKR)',
  `keyResults` json NOT NULL COMMENT 'Array of Key Results: [{"kr": "Reduce carbon by 20%", "target": 20, "current": 15, "unit": "percent"}, ...]',
  `alignmentDirection` enum('TopDown','BottomUp','Bidirectional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TopDown' COMMENT 'How this OKR aligns',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`okrID`),
  UNIQUE KEY `unique_goalUUID` (`goalUUID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OKR-specific data - Objectives and Key Results';

--
-- Dumping data for table `tija_goal_okrs`
--

INSERT INTO `tija_goal_okrs` (`okrID`, `goalUUID`, `objective`, `keyResults`, `alignmentDirection`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`) VALUES
(1, '7fb008eb-ce00-11f0-ae0d-00ff532f7d82', 'Deliver exceptional customer experiences that exceed expectations and drive loyalty', '[{\"unit\": \"percentage\", \"target\": 90, \"weight\": 0.40, \"current\": 75, \"keyResult\": \"Achieve 90% customer satisfaction score\"}, {\"unit\": \"score\", \"target\": 70, \"weight\": 0.35, \"current\": 60, \"keyResult\": \"Maintain NPS score above 70\"}, {\"unit\": \"count\", \"target\": 5, \"weight\": 0.25, \"current\": 15, \"keyResult\": \"Reduce customer complaints to less than 5 per month\"}]', 'TopDown', '2025-11-30 18:26:47', '2025-11-30 15:26:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_performance_snapshots`
--

DROP TABLE IF EXISTS `tija_goal_performance_snapshots`;
CREATE TABLE IF NOT EXISTS `tija_goal_performance_snapshots` (
  `snapshotID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `snapshotDate` date NOT NULL COMMENT 'Date of snapshot (typically weekly)',
  `currentScore` decimal(5,2) DEFAULT NULL COMMENT 'Current calculated score (0.00-100.00)',
  `targetValue` decimal(15,2) DEFAULT NULL COMMENT 'Target value at snapshot time',
  `actualValue` decimal(15,2) DEFAULT NULL COMMENT 'Actual value at snapshot time',
  `completionPercentage` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Completion percentage (0.00-100.00)',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active' COMMENT 'Status: OnTrack, AtRisk, Behind, Completed',
  `trend` enum('Improving','Stable','Declining') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Trend compared to previous snapshot',
  `ownerEntityID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID - for aggregation',
  `ownerUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - for individual goals',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`snapshotID`),
  UNIQUE KEY `unique_goal_snapshot` (`goalUUID`,`snapshotDate`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_snapshotDate` (`snapshotDate`),
  KEY `idx_ownerEntity` (`ownerEntityID`),
  KEY `idx_ownerUser` (`ownerUserID`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Performance Snapshots - Weekly snapshots for data warehouse and reporting';

--
-- Dumping data for table `tija_goal_performance_snapshots`
--

INSERT INTO `tija_goal_performance_snapshots` (`snapshotID`, `goalUUID`, `snapshotDate`, `currentScore`, `targetValue`, `actualValue`, `completionPercentage`, `status`, `trend`, `ownerEntityID`, `ownerUserID`, `DateAdded`) VALUES
(1, '7fab7e49-ce00-11f0-ae0d-00ff532f7d82', '2025-11-30', 0.00, 25.00, 0.00, 0.00, 'On Track', NULL, NULL, NULL, '2025-11-30 18:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `tija_goal_scores`
--

DROP TABLE IF EXISTS `tija_goal_scores`;
CREATE TABLE IF NOT EXISTS `tija_goal_scores` (
  `scoreID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `calculatedScore` decimal(5,2) NOT NULL COMMENT 'Weighted average score (0.00-100.00)',
  `weightedScore` decimal(5,2) NOT NULL COMMENT 'Score × weight (0.00-100.00)',
  `calculationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When score was calculated',
  `calculationMethod` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WeightedAverage' COMMENT 'Method used: WeightedAverage, AHP, etc.',
  `evaluatorCount` int UNSIGNED DEFAULT '0' COMMENT 'Number of evaluators included',
  `missingEvaluators` json DEFAULT NULL COMMENT 'Array of evaluator roles that did not submit',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scoreID`),
  UNIQUE KEY `unique_goal_latest` (`goalUUID`,`calculationDate`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_calculationDate` (`calculationDate`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `tija_holidays`
--

DROP TABLE IF EXISTS `tija_holidays`;
CREATE TABLE IF NOT EXISTS `tija_holidays` (
  `holidayID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `holidayName` varchar(256) NOT NULL,
  `holidayDate` date NOT NULL,
  `holidayType` enum('half_day','full_day') NOT NULL,
  `countryID` int NOT NULL,
  `repeatsAnnually` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `jurisdictionLevel` varchar(20) DEFAULT 'country' COMMENT 'global, country, region, city, entity',
  `regionID` varchar(100) DEFAULT NULL COMMENT 'Region/State identifier',
  `cityID` varchar(100) DEFAULT NULL COMMENT 'City identifier',
  `entitySpecific` text COMMENT 'Comma-separated entity IDs',
  `excludeBusinessUnits` text COMMENT 'Comma-separated business unit IDs to exclude',
  `affectsLeaveBalance` char(1) DEFAULT 'Y' COMMENT 'Whether holiday affects leave calculations',
  `holidayNotes` text COMMENT 'Additional notes or observance details',
  `CreatedByID` int DEFAULT NULL COMMENT 'User ID who created the holiday',
  `applyToEmploymentTypes` varchar(500) DEFAULT 'all' COMMENT 'Comma-separated employment types',
  `CreateDate` datetime DEFAULT NULL COMMENT 'Creation timestamp',
  `generatedFrom` int DEFAULT NULL COMMENT 'Source holiday ID if auto-generated',
  PRIMARY KEY (`holidayID`),
  KEY `idx_jurisdiction` (`jurisdictionLevel`),
  KEY `idx_affects_balance` (`affectsLeaveBalance`),
  KEY `idx_generated_from` (`generatedFrom`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_holiday_audit_log`;
CREATE TABLE IF NOT EXISTS `tija_holiday_audit_log` (
  `auditID` int NOT NULL AUTO_INCREMENT,
  `holidayID` int NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'created, updated, deleted, generated',
  `performedByID` int NOT NULL,
  `performedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changeDetails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON of what changed',
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`auditID`),
  KEY `idx_holiday` (`holidayID`),
  KEY `idx_performed_by` (`performedByID`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_industries`
--

DROP TABLE IF EXISTS `tija_industries`;
CREATE TABLE IF NOT EXISTS `tija_industries` (
  `industryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `industryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `industryDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `sectorID` int NOT NULL,
  `LastUpdateByID` int NOT NULL DEFAULT '37',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`industryID`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_industry_sectors`;
CREATE TABLE IF NOT EXISTS `tija_industry_sectors` (
  `sectorID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sectorName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `sectorDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL DEFAULT '37',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`sectorID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_investment_mapped_accounts`;
CREATE TABLE IF NOT EXISTS `tija_investment_mapped_accounts` (
  `investmentMappedAccountID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `investmentFinancialStatementID` int NOT NULL,
  `InvestmentAllowanceID` int NOT NULL,
  `investmentAllowanceAccountID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`investmentMappedAccountID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoices`
--

DROP TABLE IF EXISTS `tija_invoices`;
CREATE TABLE IF NOT EXISTS `tija_invoices` (
  `invoiceID` int NOT NULL AUTO_INCREMENT,
  `invoiceNumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique invoice number/identifier',
  `clientID` int NOT NULL COMMENT 'Reference to client table',
  `salesCaseID` int DEFAULT NULL COMMENT 'Reference to sales case (if applicable)',
  `projectID` int DEFAULT NULL COMMENT 'Reference to project (if applicable)',
  `invoiceDate` date NOT NULL COMMENT 'Date when invoice was issued',
  `dueDate` date NOT NULL COMMENT 'Payment due date',
  `invoiceAmount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Base invoice amount before tax',
  `subtotal` decimal(15,2) DEFAULT '0.00' COMMENT 'Subtotal before tax and discount',
  `discountPercent` decimal(5,2) DEFAULT '0.00' COMMENT 'Overall discount percentage',
  `discountAmount` decimal(15,2) DEFAULT '0.00' COMMENT 'Overall discount amount',
  `taxAmount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tax amount',
  `totalAmount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Total amount including tax',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Invoice notes',
  `terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Payment terms',
  `pdfURL` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Generated PDF URL',
  `sentDate` datetime DEFAULT NULL COMMENT 'When invoice was sent',
  `paidDate` datetime DEFAULT NULL COMMENT 'When invoice was fully paid',
  `paidAmount` decimal(15,2) DEFAULT '0.00' COMMENT 'Total amount paid',
  `outstandingAmount` decimal(15,2) DEFAULT '0.00' COMMENT 'Outstanding amount',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES' COMMENT 'Currency code (KES, USD, EUR, etc.)',
  `invoiceStatusID` int NOT NULL DEFAULT '1' COMMENT 'Reference to invoice status table',
  `templateID` int DEFAULT NULL COMMENT 'FK to tija_invoice_templates',
  `orgDataID` int NOT NULL COMMENT 'Reference to organization data',
  `entityID` int NOT NULL COMMENT 'Reference to entity',
  `DateAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
  `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  `LastUpdatedByID` int DEFAULT NULL COMMENT 'User who last updated the record',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether invoice has lapsed',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether invoice is suspended',
  PRIMARY KEY (`invoiceID`),
  KEY `idx_invoice_number` (`invoiceNumber`),
  KEY `idx_client_id` (`clientID`),
  KEY `idx_sales_case_id` (`salesCaseID`),
  KEY `idx_project_id` (`projectID`),
  KEY `idx_invoice_date` (`invoiceDate`),
  KEY `idx_due_date` (`dueDate`),
  KEY `idx_invoice_status` (`invoiceStatusID`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`),
  KEY `idx_date_added` (`DateAdded`),
  KEY `idx_suspended_lapsed` (`Suspended`,`Lapsed`),
  KEY `idx_client_date` (`clientID`,`invoiceDate`),
  KEY `idx_org_entity_date` (`orgDataID`,`entityID`,`invoiceDate`),
  KEY `idx_status_date` (`invoiceStatusID`,`invoiceDate`),
  KEY `idx_template` (`templateID`),
  KEY `idx_sent_date` (`sentDate`),
  KEY `idx_paid_date` (`paidDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice management table for TIJA PMS system';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_expenses`
--

DROP TABLE IF EXISTS `tija_invoice_expenses`;
CREATE TABLE IF NOT EXISTS `tija_invoice_expenses` (
  `mappingID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoiceItemID` int NOT NULL COMMENT 'FK to tija_invoice_items',
  `expenseID` int DEFAULT NULL COMMENT 'FK to tija_project_expenses',
  `feeExpenseID` int DEFAULT NULL COMMENT 'FK to tija_project_fee_expenses',
  `amount` decimal(15,2) NOT NULL COMMENT 'Amount billed for this expense',
  `markupPercent` decimal(5,2) DEFAULT '0.00' COMMENT 'Markup percentage applied',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mappingID`),
  KEY `idx_invoice_item` (`invoiceItemID`),
  KEY `idx_expense` (`expenseID`),
  KEY `idx_fee_expense` (`feeExpenseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps expenses to invoice items';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_items`
--

DROP TABLE IF EXISTS `tija_invoice_items`;
CREATE TABLE IF NOT EXISTS `tija_invoice_items` (
  `invoiceItemID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoiceID` int NOT NULL COMMENT 'FK to tija_invoices',
  `itemType` enum('project','task','work_hours','expense','fee_expense','license','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of invoice item',
  `itemReferenceID` int DEFAULT NULL COMMENT 'ID of referenced item (projectID, taskID, expenseID, etc.)',
  `itemCode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Item code/reference',
  `itemDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Description of the item',
  `quantity` decimal(10,2) DEFAULT '1.00' COMMENT 'Quantity (hours, units, etc.)',
  `unitPrice` decimal(15,2) NOT NULL COMMENT 'Price per unit',
  `discountPercent` decimal(5,2) DEFAULT '0.00' COMMENT 'Discount percentage',
  `discountAmount` decimal(15,2) DEFAULT '0.00' COMMENT 'Discount amount',
  `taxPercent` decimal(5,2) DEFAULT '0.00' COMMENT 'Tax percentage',
  `taxAmount` decimal(15,2) DEFAULT '0.00' COMMENT 'Tax amount',
  `lineTotal` decimal(15,2) NOT NULL COMMENT 'Total for this line item',
  `sortOrder` int DEFAULT '0' COMMENT 'Display order',
  `metadata` json DEFAULT NULL COMMENT 'Additional item metadata (dates, employee info, etc.)',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`invoiceItemID`),
  KEY `idx_invoice` (`invoiceID`),
  KEY `idx_item_type` (`itemType`),
  KEY `idx_reference` (`itemReferenceID`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice line items linking to projects, tasks, hours, expenses';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_licenses`
--

DROP TABLE IF EXISTS `tija_invoice_licenses`;
CREATE TABLE IF NOT EXISTS `tija_invoice_licenses` (
  `licenseID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `licenseName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'License/subscription name',
  `licenseCode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'License code/reference',
  `licenseType` enum('software','subscription','service','maintenance','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'software',
  `clientID` int DEFAULT NULL COMMENT 'FK to tija_clients if client-specific',
  `projectID` int DEFAULT NULL COMMENT 'FK to tija_projects if project-specific',
  `monthlyCost` decimal(15,2) DEFAULT NULL COMMENT 'Monthly cost',
  `annualCost` decimal(15,2) DEFAULT NULL COMMENT 'Annual cost',
  `startDate` date DEFAULT NULL COMMENT 'License start date',
  `endDate` date DEFAULT NULL COMMENT 'License end date',
  `renewalDate` date DEFAULT NULL COMMENT 'Next renewal date',
  `autoRenew` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Auto-renew license',
  `billingFrequency` enum('monthly','quarterly','annually','one_time') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `orgDataID` int NOT NULL DEFAULT '1',
  `entityID` int NOT NULL DEFAULT '1',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`licenseID`),
  KEY `idx_client` (`clientID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_renewal` (`renewalDate`),
  KEY `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Licenses and subscriptions that can be billed';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_payments`
--

DROP TABLE IF EXISTS `tija_invoice_payments`;
CREATE TABLE IF NOT EXISTS `tija_invoice_payments` (
  `paymentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoiceID` int NOT NULL COMMENT 'FK to tija_invoices',
  `paymentNumber` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Payment reference number',
  `paymentDate` date NOT NULL COMMENT 'Date payment was received',
  `paymentAmount` decimal(15,2) NOT NULL COMMENT 'Amount paid',
  `paymentMethod` enum('cash','bank_transfer','cheque','credit_card','mobile_money','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'bank_transfer',
  `paymentReference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Payment reference (transaction ID, cheque number, etc.)',
  `bankAccountID` int DEFAULT NULL COMMENT 'FK to bank account if applicable',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'KES' COMMENT 'Payment currency',
  `exchangeRate` decimal(10,4) DEFAULT '1.0000' COMMENT 'Exchange rate if different currency',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Payment notes',
  `receivedBy` int DEFAULT NULL COMMENT 'User who recorded the payment',
  `verifiedBy` int DEFAULT NULL COMMENT 'User who verified the payment',
  `verificationDate` datetime DEFAULT NULL COMMENT 'When payment was verified',
  `status` enum('pending','verified','reversed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `orgDataID` int NOT NULL DEFAULT '1',
  `entityID` int NOT NULL DEFAULT '1',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`paymentID`),
  UNIQUE KEY `paymentNumber` (`paymentNumber`),
  KEY `idx_invoice` (`invoiceID`),
  KEY `idx_payment_number` (`paymentNumber`),
  KEY `idx_payment_date` (`paymentDate`),
  KEY `idx_status` (`status`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Payments received against invoices';

-- --------------------------------------------------------

--
-- Table structure for table `tija_invoice_status`
--

DROP TABLE IF EXISTS `tija_invoice_status`;
CREATE TABLE IF NOT EXISTS `tija_invoice_status` (
  `statusID` int NOT NULL AUTO_INCREMENT,
  `statusName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Status name',
  `statusDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Detailed status description',
  `statusColor` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#007bff' COMMENT 'Hex color code for UI display',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y' COMMENT 'Whether status is active',
  `sortOrder` int DEFAULT '0' COMMENT 'Sort order for display',
  `DateAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`statusID`),
  UNIQUE KEY `unique_status_name` (`statusName`),
  KEY `idx_active_sort` (`isActive`,`sortOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice status lookup table';

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

DROP TABLE IF EXISTS `tija_invoice_templates`;
CREATE TABLE IF NOT EXISTS `tija_invoice_templates` (
  `templateID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Template name',
  `templateCode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique template code',
  `templateDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Template description',
  `templateType` enum('standard','hourly','expense','milestone','recurring','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'standard',
  `headerHTML` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Invoice header HTML',
  `footerHTML` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Invoice footer HTML',
  `bodyHTML` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Invoice body HTML template',
  `cssStyles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Custom CSS styles',
  `logoURL` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company logo URL',
  `companyName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company name',
  `companyAddress` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Company address',
  `companyPhone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company phone',
  `companyEmail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company email',
  `companyWebsite` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company website',
  `companyTaxID` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company tax ID/VAT number',
  `defaultTerms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Default payment terms',
  `defaultNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Default invoice notes',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'KES' COMMENT 'Default currency',
  `taxEnabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Enable tax calculation',
  `defaultTaxPercent` decimal(5,2) DEFAULT '0.00' COMMENT 'Default tax percentage',
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Is this the default template',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `orgDataID` int NOT NULL DEFAULT '1',
  `entityID` int NOT NULL DEFAULT '1',
  `createdBy` int DEFAULT NULL COMMENT 'User who created the template',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  UNIQUE KEY `templateCode` (`templateCode`),
  KEY `idx_template_code` (`templateCode`),
  KEY `idx_template_type` (`templateType`),
  KEY `idx_is_default` (`isDefault`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Invoice templates for different invoice types';

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

DROP TABLE IF EXISTS `tija_invoice_work_hours`;
CREATE TABLE IF NOT EXISTS `tija_invoice_work_hours` (
  `mappingID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoiceItemID` int NOT NULL COMMENT 'FK to tija_invoice_items',
  `timelogID` int NOT NULL COMMENT 'FK to tija_tasks_time_logs',
  `hoursBilled` decimal(10,2) NOT NULL COMMENT 'Hours billed for this time log',
  `billingRate` decimal(15,2) NOT NULL COMMENT 'Rate used for billing',
  `amount` decimal(15,2) NOT NULL COMMENT 'Amount billed for this time log',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mappingID`),
  UNIQUE KEY `unique_item_timelog` (`invoiceItemID`,`timelogID`),
  KEY `idx_invoice_item` (`invoiceItemID`),
  KEY `idx_timelog` (`timelogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Maps work hours/time logs to invoice items';

-- --------------------------------------------------------

--
-- Table structure for table `tija_job_bands`
--

DROP TABLE IF EXISTS `tija_job_bands`;
CREATE TABLE IF NOT EXISTS `tija_job_bands` (
  `jobBandID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jobBandTitle` varchar(255) NOT NULL,
  `jobBandDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`jobBandID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_job_bands`
--

INSERT INTO `tija_job_bands` (`jobBandID`, `DateAdded`, `jobBandTitle`, `jobBandDescription`, `LastUpdatedByID`, `LastUpdated`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-28 20:54:55', 'Executive P4', 'Top level Management - Professional level 4', 1, '2024-06-28 20:54:55', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_job_categories`
--

DROP TABLE IF EXISTS `tija_job_categories`;
CREATE TABLE IF NOT EXISTS `tija_job_categories` (
  `jobCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jobCategoryTitle` varchar(255) NOT NULL,
  `jobCategoryDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`jobCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_job_titles`;
CREATE TABLE IF NOT EXISTS `tija_job_titles` (
  `jobTitleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jobTitle` varchar(120) NOT NULL,
  `jobCategoryID` int DEFAULT NULL,
  `jobDescription` text,
  `jobSpesification` varchar(256) DEFAULT NULL,
  `jobTitleNote` text,
  `jobGradeID` int DEFAULT NULL,
  `orgDataID` int NOT NULL,
  `jobDescriptionDoc` varchar(255) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`jobTitleID`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_job_title_pay_grade`;
CREATE TABLE IF NOT EXISTS `tija_job_title_pay_grade` (
  `mappingID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `jobTitleID` int UNSIGNED NOT NULL,
  `payGradeID` int UNSIGNED NOT NULL,
  `effectiveDate` date NOT NULL COMMENT 'When this mapping became effective',
  `endDate` date DEFAULT NULL COMMENT 'When this mapping ended (NULL if current)',
  `isCurrent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int UNSIGNED DEFAULT NULL,
  `updatedBy` int UNSIGNED DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`mappingID`),
  UNIQUE KEY `idx_unique_current` (`jobTitleID`,`isCurrent`,`Suspended`),
  KEY `idx_job_title` (`jobTitleID`),
  KEY `idx_pay_grade` (`payGradeID`),
  KEY `idx_current` (`isCurrent`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mapping between job titles and pay grades';

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

DROP TABLE IF EXISTS `tija_lead_sources`;
CREATE TABLE IF NOT EXISTS `tija_lead_sources` (
  `leadSourceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leadSourceName` varchar(200) NOT NULL,
  `leadSourceDescription` text,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leadSourceID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_leave_accumulation_history`;
CREATE TABLE IF NOT EXISTS `tija_leave_accumulation_history` (
  `historyID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL COMMENT 'Employee who received the accrual',
  `policyID` int NOT NULL COMMENT 'Policy that generated this accrual',
  `ruleID` int DEFAULT NULL COMMENT 'Rule that applied (if any)',
  `leaveTypeID` int NOT NULL COMMENT 'Leave type accrued',
  `accrualPeriod` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Period this accrual covers (e.g., 2024-01, 2024-Q1)',
  `accrualDate` date NOT NULL COMMENT 'Date when accrual was calculated',
  `baseAccrualRate` decimal(5,2) NOT NULL COMMENT 'Base rate from policy',
  `appliedMultiplier` decimal(3,2) DEFAULT '1.00' COMMENT 'Multiplier applied from rules',
  `finalAccrualAmount` decimal(5,2) NOT NULL COMMENT 'Final amount accrued',
  `carryoverAmount` decimal(5,2) DEFAULT '0.00' COMMENT 'Amount carried over from previous period',
  `totalBalance` decimal(5,2) NOT NULL COMMENT 'Total balance after this accrual',
  `calculationNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Notes about how this was calculated',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  PRIMARY KEY (`historyID`),
  KEY `idx_employee_period` (`employeeID`,`accrualPeriod`),
  KEY `idx_policy_history` (`policyID`,`accrualDate`),
  KEY `idx_leave_type_date` (`leaveTypeID`,`accrualDate`),
  KEY `idx_accrual_date` (`accrualDate`),
  KEY `ruleID` (`ruleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='History of leave accruals for employees';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_policies`
--

DROP TABLE IF EXISTS `tija_leave_accumulation_policies`;
CREATE TABLE IF NOT EXISTS `tija_leave_accumulation_policies` (
  `policyID` int NOT NULL AUTO_INCREMENT,
  `entityID` int DEFAULT NULL COMMENT 'Entity this policy applies to (NULL for global policies)',
  `parentEntityID` int DEFAULT NULL COMMENT 'Parent entity ID for global policies (entityParentID = 0)',
  `policyScope` enum('Global','Entity','Cadre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Entity' COMMENT 'Policy scope: Global (parent entity), Entity (specific entity), Cadre (job category/band)',
  `policyName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the accumulation policy',
  `leaveTypeID` int NOT NULL COMMENT 'Leave type this policy applies to',
  `jobCategoryID` int DEFAULT NULL COMMENT 'Job category ID for cadre-level policies',
  `jobBandID` int DEFAULT NULL COMMENT 'Job band ID for cadre-level policies',
  `accrualType` enum('Monthly','Quarterly','Annual','Continuous') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Monthly',
  `accrualRate` decimal(5,2) NOT NULL COMMENT 'Days accrued per period',
  `maxCarryover` int DEFAULT NULL COMMENT 'Maximum days that can be carried over (null = unlimited)',
  `carryoverExpiryMonths` int DEFAULT NULL COMMENT 'Months after which carryover expires (null = never)',
  `accrualStartDate` date DEFAULT NULL COMMENT 'Date when accrual starts (null = immediate)',
  `accrualEndDate` date DEFAULT NULL COMMENT 'Date when accrual ends (null = indefinite)',
  `proRated` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether accrual is pro-rated for partial periods',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `priority` int DEFAULT '1' COMMENT 'Priority order when multiple policies apply',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y' COMMENT 'Whether this policy is currently active',
  `policyDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description of the policy',
  PRIMARY KEY (`policyID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_leave_type` (`leaveTypeID`),
  KEY `idx_accrual_type` (`accrualType`),
  KEY `idx_policy_scope` (`policyScope`,`entityID`,`jobCategoryID`,`jobBandID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Policies for leave accumulation and accrual';

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

DROP TABLE IF EXISTS `tija_leave_accumulation_rules`;
CREATE TABLE IF NOT EXISTS `tija_leave_accumulation_rules` (
  `ruleID` int NOT NULL AUTO_INCREMENT,
  `policyID` int NOT NULL COMMENT 'Parent policy this rule belongs to',
  `ruleName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the rule',
  `ruleType` enum('Tenure','Performance','Department','Role','Custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tenure',
  `conditionField` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Field to evaluate (e.g., yearsOfService, performanceRating)',
  `conditionOperator` enum('=','>','>=','<','<=','<>','IN','NOT IN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '>=',
  `conditionValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Value to compare against',
  `accrualMultiplier` decimal(3,2) DEFAULT '1.00' COMMENT 'Multiplier for base accrual rate',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ruleID`),
  KEY `idx_policy_rules` (`policyID`,`Lapsed`),
  KEY `idx_rule_type` (`ruleType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rules for complex accumulation policies';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_applications`
--

DROP TABLE IF EXISTS `tija_leave_applications`;
CREATE TABLE IF NOT EXISTS `tija_leave_applications` (
  `leaveApplicationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveTypeID` int NOT NULL,
  `leavePeriodID` int NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `leaveStatusID` int NOT NULL DEFAULT '1',
  `employeeID` int NOT NULL,
  `leaveFiles` text,
  `leaveComments` text,
  `leaveEntitlementID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `noOfDays` decimal(3,2) DEFAULT NULL,
  `emergencyContact` text COMMENT 'Emergency contact information for the leave period',
  `handoverNotes` text COMMENT 'Notes about work handover during leave',
  `handoverRequired` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether a structured handover is required',
  `handoverStatus` enum('not_required','pending','in_progress','completed','partial') NOT NULL DEFAULT 'not_required',
  `handoverCompletedDate` datetime DEFAULT NULL COMMENT 'When the handover was fully confirmed',
  `createdBy` int DEFAULT NULL COMMENT 'User ID who created the application',
  `createdDate` datetime DEFAULT NULL COMMENT 'Date and time when the application was created',
  `modifiedBy` int DEFAULT NULL COMMENT 'User ID who last modified the application',
  `modifiedDate` datetime DEFAULT NULL COMMENT 'Date and time when the application was last modified',
  `halfDayLeave` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether this is a half day leave',
  `halfDayPeriod` varchar(20) DEFAULT NULL COMMENT 'Period for half day leave (AM/PM)',
  `dateApplied` datetime DEFAULT NULL COMMENT 'Date when the application was submitted',
  `appliedByID` int DEFAULT NULL COMMENT 'ID of the person who applied for leave',
  PRIMARY KEY (`leaveApplicationID`),
  KEY `idx_employee_date` (`employeeID`,`startDate`),
  KEY `idx_status_date` (`leaveStatusID`,`startDate`),
  KEY `idx_leave_type` (`leaveTypeID`),
  KEY `idx_created_by` (`createdBy`),
  KEY `idx_created_date` (`createdDate`),
  KEY `idx_modified_by` (`modifiedBy`),
  KEY `idx_modified_date` (`modifiedDate`),
  KEY `idx_handover_status` (`handoverStatus`,`handoverRequired`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_applications`
--

INSERT INTO `tija_leave_applications` (`leaveApplicationID`, `DateAdded`, `leaveTypeID`, `leavePeriodID`, `startDate`, `endDate`, `leaveStatusID`, `employeeID`, `leaveFiles`, `leaveComments`, `leaveEntitlementID`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `noOfDays`, `emergencyContact`, `handoverNotes`, `handoverRequired`, `handoverStatus`, `handoverCompletedDate`, `createdBy`, `createdDate`, `modifiedBy`, `modifiedDate`, `halfDayLeave`, `halfDayPeriod`, `dateApplied`, `appliedByID`) VALUES
(1, '2025-12-06 12:41:15', 1, 1, '2025-12-07', '2025-12-09', 3, 24, NULL, 'eefwee e we ww', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 2.00, '', '', 'N', 'not_required', NULL, NULL, NULL, NULL, NULL, 'N', '', '2025-12-06 12:41:15', 24);

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approvals`
--

DROP TABLE IF EXISTS `tija_leave_approvals`;
CREATE TABLE IF NOT EXISTS `tija_leave_approvals` (
  `leaveApprovalID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveApplicationID` int NOT NULL,
  `employeeID` int NOT NULL,
  `leaveTypeID` int NOT NULL,
  `leavePeriodID` int NOT NULL,
  `leaveApproverID` int NOT NULL,
  `leaveDate` date NOT NULL,
  `leaveStatus` enum('approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `leaveStatusID` int NOT NULL,
  `approversComments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveApprovalID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_actions`
--

DROP TABLE IF EXISTS `tija_leave_approval_actions`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_actions` (
  `actionID` int NOT NULL AUTO_INCREMENT,
  `instanceID` int NOT NULL,
  `stepID` int NOT NULL,
  `stepOrder` int NOT NULL,
  `approverID` int NOT NULL COMMENT 'User who took action',
  `approverUserID` int DEFAULT NULL,
  `action` enum('pending','approved','rejected','delegated','escalated','cancelled','info_requested') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delegatedTo` int DEFAULT NULL,
  `actionDate` datetime NOT NULL,
  `responseTime` int DEFAULT NULL COMMENT 'Minutes from notification to action',
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`actionID`),
  KEY `idx_instance` (`instanceID`),
  KEY `idx_approver` (`approverID`),
  KEY `idx_action` (`action`),
  KEY `idx_date` (`actionDate`),
  KEY `idx_action_pending` (`instanceID`,`action`,`actionDate`),
  KEY `idx_actions_instance_step_approver` (`instanceID`,`stepID`,`approverUserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of all approval actions taken';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_comments`
--

DROP TABLE IF EXISTS `tija_leave_approval_comments`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_comments` (
  `commentID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `approverID` int DEFAULT NULL,
  `approverUserID` int DEFAULT NULL,
  `approvalLevel` varchar(50) DEFAULT NULL,
  `comment` text,
  `commentType` varchar(30) DEFAULT NULL,
  `commentDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` char(1) NOT NULL DEFAULT 'N',
  `Suspended` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`commentID`),
  KEY `idx_comments_application` (`leaveApplicationID`),
  KEY `idx_comments_approver` (`approverUserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_instances`
--

DROP TABLE IF EXISTS `tija_leave_approval_instances`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_instances` (
  `instanceID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `policyID` int NOT NULL,
  `currentStepID` int DEFAULT NULL,
  `currentStepOrder` int DEFAULT '1',
  `workflowStatus` enum('pending','in_progress','approved','rejected','cancelled','escalated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `startedAt` datetime NOT NULL,
  `completedAt` datetime DEFAULT NULL,
  `lastActionAt` datetime DEFAULT NULL,
  `lastActionBy` int DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`instanceID`),
  KEY `idx_application` (`leaveApplicationID`),
  KEY `idx_policy` (`policyID`),
  KEY `idx_status` (`workflowStatus`),
  KEY `idx_instance_status` (`workflowStatus`,`currentStepOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workflow instances for leave applications';

--
-- Dumping data for table `tija_leave_approval_instances`
--

INSERT INTO `tija_leave_approval_instances` (`instanceID`, `leaveApplicationID`, `policyID`, `currentStepID`, `currentStepOrder`, `workflowStatus`, `startedAt`, `completedAt`, `lastActionAt`, `lastActionBy`, `createdAt`) VALUES
(1, 1, 1, 4, 1, 'pending', '2025-12-06 12:41:15', NULL, NULL, NULL, '2025-12-06 15:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_policies`
--

DROP TABLE IF EXISTS `tija_leave_approval_policies`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_policies` (
  `policyID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `policyName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `policyDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `approvalType` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'parallel',
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `requireAllApprovals` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'If Y, all approvers must approve. If N, sequential approval',
  `allowDelegation` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `autoApproveThreshold` int DEFAULT NULL COMMENT 'Auto-approve if leave days <= this value',
  `createdBy` int NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`policyID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_orgdata` (`orgDataID`),
  KEY `idx_active` (`isActive`,`Suspended`,`Lapsed`),
  KEY `idx_policy_entity` (`entityID`,`isActive`),
  KEY `idx_policy_default` (`entityID`,`isDefault`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Leave approval workflow policies per entity';

--
-- Dumping data for table `tija_leave_approval_policies`
--

INSERT INTO `tija_leave_approval_policies` (`policyID`, `entityID`, `orgDataID`, `policyName`, `policyDescription`, `isActive`, `approvalType`, `isDefault`, `requireAllApprovals`, `allowDelegation`, `autoApproveThreshold`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Suspended`, `Lapsed`) VALUES
(1, 1, 1, 'Direct Line Manager approval', 'This template is used for employees who need approval from their direct supervisor, project manager and finally the HR Manager', 'Y', 'parallel', 'Y', 'N', 'Y', 4, 4, '2025-10-22 08:28:42', 4, '2025-11-19 17:00:11', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_steps`
--

DROP TABLE IF EXISTS `tija_leave_approval_steps`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_steps` (
  `stepID` int NOT NULL AUTO_INCREMENT,
  `policyID` int NOT NULL,
  `stepOrder` int NOT NULL COMMENT 'Order of approval (1, 2, 3...)',
  `stepName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isRequired` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `approvalRequired` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `isConditional` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `conditionType` enum('days_threshold','leave_type','user_role','department','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditionValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON string for condition parameters',
  `escalationDays` int DEFAULT NULL COMMENT 'Days before escalation if no action',
  `escalateToStepID` int DEFAULT NULL COMMENT 'Which step to escalate to',
  `notifyOnPending` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyOnApprove` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyOnReject` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`stepID`),
  KEY `idx_policy` (`policyID`),
  KEY `idx_order` (`stepOrder`),
  KEY `idx_step_policy_order` (`policyID`,`stepOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual steps in approval workflow';

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

DROP TABLE IF EXISTS `tija_leave_approval_step_approvers`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_step_approvers` (
  `approverID` int NOT NULL AUTO_INCREMENT,
  `stepID` int NOT NULL,
  `approverType` enum('user','role','department') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approverUserID` int DEFAULT NULL COMMENT 'If approverType = user',
  `approverRole` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'If approverType = role',
  `approverDepartment` int DEFAULT NULL COMMENT 'If approverType = department',
  `isBackup` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `notificationOrder` int DEFAULT '1' COMMENT 'Order for parallel approvers',
  `createdAt` datetime NOT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`approverID`),
  KEY `idx_step` (`stepID`),
  KEY `idx_user` (`approverUserID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Specific approvers for custom workflow steps';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_audit_log`
--

DROP TABLE IF EXISTS `tija_leave_audit_log`;
CREATE TABLE IF NOT EXISTS `tija_leave_audit_log` (
  `auditID` int NOT NULL AUTO_INCREMENT,
  `entityType` enum('application','approval','clearance','entitlement','policy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entityID` int NOT NULL COMMENT 'ID of the entity being audited',
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Action performed (CREATE, UPDATE, DELETE, APPROVE, etc.)',
  `oldValues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Previous values (JSON format)',
  `newValues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'New values (JSON format)',
  `performedByID` int NOT NULL COMMENT 'User who performed the action',
  `performedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP address of user',
  `userAgent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'User agent string',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Reason for the action',
  PRIMARY KEY (`auditID`),
  KEY `idx_entity` (`entityType`,`entityID`),
  KEY `idx_performed_by` (`performedByID`),
  KEY `idx_performed_date` (`performedDate`),
  KEY `idx_action` (`action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_blackout_periods`
--

DROP TABLE IF EXISTS `tija_leave_blackout_periods`;
CREATE TABLE IF NOT EXISTS `tija_leave_blackout_periods` (
  `blackoutID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL COMMENT 'Entity this blackout applies to',
  `blackoutName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the blackout period',
  `startDate` date NOT NULL COMMENT 'Start date of blackout period',
  `endDate` date NOT NULL COMMENT 'End date of blackout period',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Reason for blackout period',
  `applicableLeaveTypes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of leave type IDs this applies to (null = all types)',
  `severity` enum('Warning','Restriction','Prohibition') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Restriction',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`blackoutID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_date_range` (`startDate`,`endDate`),
  KEY `idx_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blackout periods when leave applications are restricted';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_entitlement`
--

DROP TABLE IF EXISTS `tija_leave_entitlement`;
CREATE TABLE IF NOT EXISTS `tija_leave_entitlement` (
  `leaveEntitlementID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveTypeID` int NOT NULL,
  `jobCategoryID` int DEFAULT NULL COMMENT 'Job category ID for cadre-level entitlements',
  `jobBandID` int DEFAULT NULL COMMENT 'Job band ID for cadre-level entitlements',
  `entitlement` decimal(4,0) NOT NULL,
  `maxDaysPerApplication` int DEFAULT NULL COMMENT 'Maximum days that can be applied for in a single application (NULL = unlimited)',
  `minNoticeDays` int NOT NULL,
  `entityID` int DEFAULT NULL COMMENT 'Entity this entitlement applies to (NULL for global entitlements)',
  `parentEntityID` int DEFAULT NULL COMMENT 'Parent entity ID for global entitlements (entityParentID = 0)',
  `policyScope` enum('Global','Entity','Cadre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Entity' COMMENT 'Policy scope: Global (parent entity), Entity (specific entity), Cadre (job category/band)',
  `orgDataID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveEntitlementID`),
  KEY `idx_entity_type` (`entityID`,`leaveTypeID`),
  KEY `idx_policy_scope` (`policyScope`,`entityID`,`jobCategoryID`,`jobBandID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

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
-- Table structure for table `tija_leave_handovers`
--

DROP TABLE IF EXISTS `tija_leave_handovers`;
CREATE TABLE IF NOT EXISTS `tija_leave_handovers` (
  `handoverID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `employeeID` int NOT NULL,
  `entityID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `policyID` int DEFAULT NULL,
  `nomineeID` int DEFAULT NULL COMMENT 'Peer/nominee assigned for handover',
  `fsmStateID` int DEFAULT NULL COMMENT 'FK to tija_leave_handover_fsm_states',
  `revisionCount` int NOT NULL DEFAULT '0' COMMENT 'Number of revision attempts',
  `handoverStatus` enum('pending','in_progress','partial','completed','rejected') NOT NULL DEFAULT 'pending',
  `packageStatus` enum('draft','submitted','approved','returned') NOT NULL DEFAULT 'draft',
  `managerReviewStatus` enum('pending','verified','returned','waived') NOT NULL DEFAULT 'pending',
  `managerReviewerID` int DEFAULT NULL,
  `managerReviewDate` datetime DEFAULT NULL,
  `managerComments` text,
  `hrOverrideStatus` enum('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  `hrOverrideByID` int DEFAULT NULL,
  `hrOverrideDate` datetime DEFAULT NULL,
  `hrOverrideComments` text,
  `handoverDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completionDate` datetime DEFAULT NULL,
  `notes` text,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`handoverID`),
  KEY `idx_handover_application` (`leaveApplicationID`),
  KEY `idx_handover_employee` (`employeeID`),
  KEY `idx_handover_status` (`handoverStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_leave_handovers`
--

INSERT INTO `tija_leave_handovers` (`handoverID`, `leaveApplicationID`, `employeeID`, `entityID`, `orgDataID`, `policyID`, `nomineeID`, `fsmStateID`, `revisionCount`, `handoverStatus`, `packageStatus`, `managerReviewStatus`, `managerReviewerID`, `managerReviewDate`, `managerComments`, `hrOverrideStatus`, `hrOverrideByID`, `hrOverrideDate`, `hrOverrideComments`, `handoverDate`, `completionDate`, `notes`, `DateAdded`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, 4, 4, 1, 1, NULL, NULL, NULL, 0, 'in_progress', 'draft', 'pending', NULL, NULL, NULL, 'none', NULL, NULL, NULL, '2025-11-26 10:25:28', NULL, NULL, '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(2, 5, 4, 1, 1, NULL, NULL, NULL, 0, 'in_progress', 'draft', 'pending', NULL, NULL, NULL, 'none', NULL, NULL, NULL, '2025-11-26 11:15:29', NULL, NULL, '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_artifacts`
--

DROP TABLE IF EXISTS `tija_leave_handover_artifacts`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_artifacts` (
  `artifactID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `handoverItemID` int DEFAULT NULL,
  `assignmentID` int DEFAULT NULL,
  `artifactType` enum('document','credential','training','other') NOT NULL DEFAULT 'document',
  `filePath` varchar(255) NOT NULL,
  `fileLabel` varchar(255) DEFAULT NULL,
  `description` text,
  `accessInstructions` text,
  `uploadedByID` int NOT NULL,
  `uploadedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`artifactID`),
  KEY `idx_artifact_handover` (`handoverID`),
  KEY `idx_artifact_item` (`handoverItemID`),
  KEY `idx_artifact_assignment` (`assignmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_assignments`
--

DROP TABLE IF EXISTS `tija_leave_handover_assignments`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_assignments` (
  `assignmentID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `handoverItemID` int DEFAULT NULL,
  `assignedToID` int NOT NULL,
  `assignedByID` int NOT NULL,
  `assignmentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmationStatus` enum('pending','acknowledged','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `confirmedDate` datetime DEFAULT NULL,
  `confirmationComments` text,
  `negotiationID` int DEFAULT NULL COMMENT 'FK to tija_leave_handover_peer_negotiations',
  `revisionRequested` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether revision was requested for this assignment',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`assignmentID`),
  KEY `idx_assignment_handover` (`handoverID`),
  KEY `idx_assignment_item` (`handoverItemID`),
  KEY `idx_assignment_assignee` (`assignedToID`),
  KEY `idx_assignment_status` (`confirmationStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_leave_handover_assignments`
--

INSERT INTO `tija_leave_handover_assignments` (`assignmentID`, `handoverID`, `handoverItemID`, `assignedToID`, `assignedByID`, `assignmentDate`, `confirmationStatus`, `confirmedDate`, `confirmationComments`, `negotiationID`, `revisionRequested`, `DateAdded`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, 1, 1, 13, 4, '2025-11-26 10:25:28', 'pending', NULL, NULL, NULL, 'N', '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(2, 1, 1, 5, 4, '2025-11-26 10:25:28', 'pending', NULL, NULL, NULL, 'N', '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(3, 1, 1, 3, 4, '2025-11-26 10:25:28', 'pending', NULL, NULL, NULL, 'N', '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(4, 2, 2, 13, 4, '2025-11-26 11:15:29', 'pending', NULL, NULL, NULL, 'N', '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N'),
(5, 2, 2, 5, 4, '2025-11-26 11:15:29', 'pending', NULL, NULL, NULL, 'N', '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N'),
(6, 2, 2, 15, 4, '2025-11-26 11:15:29', 'pending', NULL, NULL, NULL, 'N', '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_confirmations`
--

DROP TABLE IF EXISTS `tija_leave_handover_confirmations`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_confirmations` (
  `confirmationID` int NOT NULL AUTO_INCREMENT,
  `assignmentID` int NOT NULL,
  `handoverItemID` int DEFAULT NULL,
  `briefed` enum('Y','N','not_required') NOT NULL DEFAULT 'Y',
  `briefedDate` datetime DEFAULT NULL,
  `trained` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `trainedDate` datetime DEFAULT NULL,
  `hasCredentials` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `credentialsDetails` text,
  `hasTools` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `toolsDetails` text,
  `hasDocuments` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `documentsDetails` text,
  `readyToTakeOver` enum('Y','N') NOT NULL DEFAULT 'N',
  `additionalNotes` text,
  `confirmedByID` int NOT NULL,
  `confirmedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`confirmationID`),
  KEY `idx_confirmation_assignment` (`assignmentID`),
  KEY `idx_confirmation_item` (`handoverItemID`),
  KEY `idx_confirmation_ready` (`readyToTakeOver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_fsm_states`
--

DROP TABLE IF EXISTS `tija_leave_handover_fsm_states`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_fsm_states` (
  `stateID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `handoverID` int DEFAULT NULL,
  `currentState` enum('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') NOT NULL,
  `previousState` enum('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') DEFAULT NULL,
  `stateOwnerID` int DEFAULT NULL COMMENT 'Employee ID who owns current state',
  `nomineeID` int DEFAULT NULL COMMENT 'Peer/nominee assigned for handover',
  `stateEnteredAt` datetime NOT NULL,
  `stateCompletedAt` datetime DEFAULT NULL,
  `timerStartedAt` datetime DEFAULT NULL COMMENT 'For peer response deadlines',
  `timerExpiresAt` datetime DEFAULT NULL,
  `revisionCount` int NOT NULL DEFAULT '0',
  `chainOfCustodyLog` text COMMENT 'JSON log of state transitions',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stateID`),
  KEY `idx_application` (`leaveApplicationID`),
  KEY `idx_handover` (`handoverID`),
  KEY `idx_current_state` (`currentState`),
  KEY `idx_nominee` (`nomineeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_items`
--

DROP TABLE IF EXISTS `tija_leave_handover_items`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_items` (
  `handoverItemID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `itemType` enum('project_task','function','duty','other') NOT NULL DEFAULT 'other',
  `itemTitle` varchar(255) NOT NULL,
  `itemDescription` text,
  `projectID` int DEFAULT NULL,
  `taskID` int DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `dueDate` date DEFAULT NULL,
  `instructions` text,
  `isMandatory` enum('Y','N') NOT NULL DEFAULT 'Y',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`handoverItemID`),
  KEY `idx_item_handover` (`handoverID`),
  KEY `idx_item_type` (`itemType`),
  KEY `idx_item_priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_leave_handover_items`
--

INSERT INTO `tija_leave_handover_items` (`handoverItemID`, `handoverID`, `itemType`, `itemTitle`, `itemDescription`, `projectID`, `taskID`, `priority`, `dueDate`, `instructions`, `isMandatory`, `DateAdded`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, 1, 'project_task', 'Weekly sale  report', 'sda dsa dsf asdf sadf sdf sadfsdaf  sd afsdfs', NULL, NULL, 'low', '2025-12-05', NULL, 'Y', '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(2, 2, 'duty', 'fsad fsadf asf d', 'dsaf sdf sadf asdf', NULL, NULL, 'low', '2025-12-05', NULL, 'Y', '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_packages`
--

DROP TABLE IF EXISTS `tija_leave_handover_packages`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_packages` (
  `packageID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `preparedByID` int NOT NULL,
  `lineManagerID` int DEFAULT NULL,
  `packageStatus` enum('draft','submitted','approved','returned') NOT NULL DEFAULT 'draft',
  `handoverOverview` text,
  `taskChecklistJson` longtext,
  `knowledgeTransferPlan` text,
  `credentialStatus` enum('complete','partial','missing','not_required') NOT NULL DEFAULT 'not_required',
  `documentStatus` enum('complete','partial','missing','not_required') NOT NULL DEFAULT 'not_required',
  `trainingStatus` enum('complete','partial','missing','not_required') NOT NULL DEFAULT 'not_required',
  `riskAssessment` text,
  `submittedAt` datetime DEFAULT NULL,
  `returnedReason` text,
  `managerNotes` text,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`packageID`),
  UNIQUE KEY `uniq_package_handover` (`handoverID`),
  KEY `idx_package_status` (`packageStatus`),
  KEY `idx_package_manager` (`lineManagerID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_leave_handover_packages`
--

INSERT INTO `tija_leave_handover_packages` (`packageID`, `handoverID`, `preparedByID`, `lineManagerID`, `packageStatus`, `handoverOverview`, `taskChecklistJson`, `knowledgeTransferPlan`, `credentialStatus`, `documentStatus`, `trainingStatus`, `riskAssessment`, `submittedAt`, `returnedReason`, `managerNotes`, `DateAdded`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, 1, 4, NULL, 'draft', 'fdgdsfg sdfg dsfg dsfg sdfg s', '[{\"itemTitle\":\"Weekly sale  report\",\"itemDescription\":\"sda dsa dsf asdf sadf sdf sadfsdaf  sd afsdfs\",\"itemType\":\"project_task\",\"priority\":\"low\",\"dueDate\":\"2025-12-05\",\"assignees\":[\"13\",\"5\",\"3\"]}]', 'sd fgsdfgsdfgsdfgsdfg sdf', 'complete', 'complete', 'complete', 'dsfg sdfg sdfg sdfg', NULL, NULL, NULL, '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(2, 2, 4, NULL, 'draft', 'sdf sdf sdaf', '[{\"itemTitle\":\"fsad fsadf asf d\",\"itemDescription\":\"dsaf sdf sadf asdf\",\"itemType\":\"duty\",\"priority\":\"low\",\"dueDate\":\"2025-12-05\",\"assignees\":[\"13\",\"5\",\"15\"]}]', 'sdfasdf asdf asdf asd', 'not_required', 'not_required', 'complete', 'sf asdf asf sdaf sdfsdaf sda', NULL, NULL, NULL, '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_peer_negotiations`
--

DROP TABLE IF EXISTS `tija_leave_handover_peer_negotiations`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_peer_negotiations` (
  `negotiationID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `assignmentID` int DEFAULT NULL,
  `nomineeID` int NOT NULL,
  `requesterID` int NOT NULL,
  `negotiationType` enum('request_change','reject','accept') NOT NULL,
  `requestedChanges` text COMMENT 'Details of what needs to be changed',
  `negotiationStatus` enum('pending','resolved','escalated') NOT NULL DEFAULT 'pending',
  `responseDate` datetime DEFAULT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`negotiationID`),
  KEY `idx_handover` (`handoverID`),
  KEY `idx_nominee` (`nomineeID`),
  KEY `idx_assignment` (`assignmentID`),
  KEY `idx_status` (`negotiationStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_policies`
--

DROP TABLE IF EXISTS `tija_leave_handover_policies`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_policies` (
  `policyID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL,
  `orgDataID` int DEFAULT NULL,
  `leaveTypeID` int DEFAULT NULL,
  `policyScope` enum('entity_wide','role_based','job_group','job_level','job_title') NOT NULL DEFAULT 'entity_wide' COMMENT 'Scope of policy targeting',
  `targetRoleID` int DEFAULT NULL COMMENT 'Target role ID for role-based policies',
  `targetJobCategoryID` int DEFAULT NULL COMMENT 'Target job category ID for job group policies',
  `targetJobBandID` int DEFAULT NULL COMMENT 'Target job band ID for job group policies',
  `targetJobLevelID` int DEFAULT NULL COMMENT 'Target job level ID (FK to tija_role_levels)',
  `targetJobTitleID` int DEFAULT NULL COMMENT 'Target job title ID (FK to tija_job_titles)',
  `requireNomineeAcceptance` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether nominee acceptance is required',
  `nomineeResponseDeadlineHours` int NOT NULL DEFAULT '48' COMMENT 'Hours for nominee to respond',
  `allowPeerRevision` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether peer can request revisions',
  `maxRevisionAttempts` int NOT NULL DEFAULT '3' COMMENT 'Maximum number of revision attempts allowed',
  `isMandatory` enum('Y','N') NOT NULL DEFAULT 'N',
  `minHandoverDays` int NOT NULL DEFAULT '0',
  `requireConfirmation` enum('Y','N') NOT NULL DEFAULT 'Y',
  `requireTraining` enum('Y','N') NOT NULL DEFAULT 'N',
  `requireCredentials` enum('Y','N') NOT NULL DEFAULT 'N',
  `requireTools` enum('Y','N') NOT NULL DEFAULT 'N',
  `requireDocuments` enum('Y','N') NOT NULL DEFAULT 'N',
  `allowProjectIntegration` enum('Y','N') NOT NULL DEFAULT 'N',
  `effectiveDate` date NOT NULL DEFAULT '1970-01-01',
  `expiryDate` date DEFAULT NULL,
  `policyName` varchar(255) DEFAULT NULL,
  `policyDescription` text,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`policyID`),
  KEY `idx_policy_entity` (`entityID`),
  KEY `idx_policy_leave_type` (`leaveTypeID`),
  KEY `idx_policy_effective` (`effectiveDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_handover_signoffs`
--

DROP TABLE IF EXISTS `tija_leave_handover_signoffs`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_signoffs` (
  `signoffID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `relatedAssignmentID` int DEFAULT NULL,
  `signoffType` enum('delegate','manager','hr') NOT NULL DEFAULT 'delegate',
  `status` enum('pending','approved','returned','rejected') NOT NULL DEFAULT 'pending',
  `signedByID` int DEFAULT NULL,
  `requiresActionByID` int DEFAULT NULL,
  `comments` text,
  `signedAt` datetime DEFAULT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`signoffID`),
  KEY `idx_signoff_handover` (`handoverID`),
  KEY `idx_signoff_assignment` (`relatedAssignmentID`),
  KEY `idx_signoff_type` (`signoffType`),
  KEY `idx_signoff_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_leave_handover_signoffs`
--

INSERT INTO `tija_leave_handover_signoffs` (`signoffID`, `handoverID`, `relatedAssignmentID`, `signoffType`, `status`, `signedByID`, `requiresActionByID`, `comments`, `signedAt`, `DateAdded`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, 1, NULL, 'manager', 'pending', NULL, 2, NULL, NULL, '2025-11-26 10:25:28', '2025-11-26 10:25:28', 'N', 'N'),
(2, 2, NULL, 'manager', 'pending', NULL, 2, NULL, NULL, '2025-11-26 11:15:29', '2025-11-26 11:15:29', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_manual_balances`
--

DROP TABLE IF EXISTS `tija_leave_manual_balances`;
CREATE TABLE IF NOT EXISTS `tija_leave_manual_balances` (
  `manualBalanceID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL,
  `entityID` int NOT NULL,
  `leaveTypeID` int NOT NULL,
  `payrollNumber` varchar(120) DEFAULT NULL,
  `openingBalanceDays` decimal(8,2) NOT NULL DEFAULT '0.00',
  `asOfDate` date DEFAULT NULL,
  `uploadBatch` varchar(64) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `createdBy` int DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updatedDate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` char(1) NOT NULL DEFAULT 'N',
  `Suspended` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`manualBalanceID`),
  UNIQUE KEY `uniq_manual_balance_employee_leave` (`employeeID`,`leaveTypeID`),
  KEY `idx_manual_balance_entity` (`entityID`),
  KEY `idx_manual_balance_leave_type` (`leaveTypeID`),
  KEY `idx_manual_balance_payroll` (`payrollNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_periods`
--

DROP TABLE IF EXISTS `tija_leave_periods`;
CREATE TABLE IF NOT EXISTS `tija_leave_periods` (
  `leavePeriodID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leavePeriodName` varchar(255) NOT NULL,
  `leavePeriodStartDate` date NOT NULL,
  `leavePeriodEndDate` date NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leavePeriodID`),
  KEY `idx_entity_period` (`entityID`,`leavePeriodStartDate`,`leavePeriodEndDate`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_periods`
--

INSERT INTO `tija_leave_periods` (`leavePeriodID`, `DateAdded`, `leavePeriodName`, `leavePeriodStartDate`, `leavePeriodEndDate`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 15:15:55', '2025 leave period', '2025-01-01', '2025-12-31', 0, 1, '2025-11-21 15:15:55', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_project_clearances`
--

DROP TABLE IF EXISTS `tija_leave_project_clearances`;
CREATE TABLE IF NOT EXISTS `tija_leave_project_clearances` (
  `clearanceID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL COMMENT 'Reference to leave application',
  `projectID` int NOT NULL COMMENT 'Project requiring clearance',
  `projectManagerID` int NOT NULL COMMENT 'Project manager who needs to approve',
  `clearanceStatus` enum('Pending','Approved','Rejected','Not Required') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `clearanceDate` datetime DEFAULT NULL COMMENT 'Date when clearance was given',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Comments from project manager',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clearanceID`),
  KEY `idx_leave_application` (`leaveApplicationID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_project_manager` (`projectManagerID`),
  KEY `idx_clearance_status` (`clearanceStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project manager clearances for leave applications';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_status`
--

DROP TABLE IF EXISTS `tija_leave_status`;
CREATE TABLE IF NOT EXISTS `tija_leave_status` (
  `leaveStatusID` int NOT NULL AUTO_INCREMENT,
  `leaveStatusCode` varchar(80) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveStatusName` varchar(255) NOT NULL,
  `leaveStatusDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_leave_types`;
CREATE TABLE IF NOT EXISTS `tija_leave_types` (
  `leaveTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveTypeCode` varchar(255) NOT NULL,
  `leaveTypeName` varchar(255) NOT NULL,
  `leaveTypeDescription` text NOT NULL,
  `leaveSegment` enum('male','female','specialNeeds') DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_leave_workflow_templates`;
CREATE TABLE IF NOT EXISTS `tija_leave_workflow_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT,
  `templateName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sourcePolicyID` int DEFAULT NULL COMMENT 'Original policy this was created from',
  `isSystemTemplate` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isPublic` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'If Y, visible to all entities',
  `createdBy` int NOT NULL,
  `createdForEntityID` int DEFAULT NULL,
  `usageCount` int DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  KEY `idx_public` (`isPublic`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reusable workflow templates';

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

DROP TABLE IF EXISTS `tija_leave_workflow_template_steps`;
CREATE TABLE IF NOT EXISTS `tija_leave_workflow_template_steps` (
  `templateStepID` int NOT NULL AUTO_INCREMENT,
  `templateID` int NOT NULL,
  `stepOrder` int NOT NULL,
  `stepName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isRequired` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `isConditional` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `conditionType` enum('days_threshold','leave_type','user_role','department','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditionValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `escalationDays` int DEFAULT NULL,
  `notifySettings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON for notification settings',
  PRIMARY KEY (`templateStepID`),
  KEY `idx_template` (`templateID`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Steps in workflow templates';

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

DROP TABLE IF EXISTS `tija_licenses`;
CREATE TABLE IF NOT EXISTS `tija_licenses` (
  `licenseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL COMMENT 'Foreign key to tija_organisation_data',
  `licenseType` enum('trial','basic','standard','premium','enterprise') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `licenseKey` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique license key',
  `userLimit` int NOT NULL DEFAULT '50' COMMENT 'Maximum number of users allowed',
  `currentUsers` int NOT NULL DEFAULT '0' COMMENT 'Current active users count',
  `licenseIssueDate` date NOT NULL COMMENT 'Date license was issued',
  `licenseExpiryDate` date NOT NULL COMMENT 'Date license expires',
  `licenseStatus` enum('active','suspended','expired','trial') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'trial',
  `features` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of enabled features',
  `licenseNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Additional notes about the license',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL COMMENT 'User ID who last updated',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`licenseID`),
  UNIQUE KEY `licenseKey` (`licenseKey`),
  KEY `idx_orgDataID` (`orgDataID`),
  KEY `idx_licenseStatus` (`licenseStatus`),
  KEY `idx_licenseExpiryDate` (`licenseExpiryDate`),
  KEY `idx_licenseKey` (`licenseKey`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores license information for tenant organizations';

--
-- Dumping data for table `tija_licenses`
--

INSERT INTO `tija_licenses` (`licenseID`, `DateAdded`, `orgDataID`, `licenseType`, `licenseKey`, `userLimit`, `currentUsers`, `licenseIssueDate`, `licenseExpiryDate`, `licenseStatus`, `features`, `licenseNotes`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 06:58:17', 1, 'basic', 'TIJA-BAS-2025-439ADBC8', 50, 0, '2025-11-01', '2026-11-01', 'active', '[\"payroll\",\"leave\",\"attendance\",\"reports\",\"employee_management\"]', 'Internal Licence that will not be charged', '2025-11-21 09:58:17', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_license_types`
--

DROP TABLE IF EXISTS `tija_license_types`;
CREATE TABLE IF NOT EXISTS `tija_license_types` (
  `licenseTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `licenseTypeName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name (e.g., Standard, Premium)',
  `licenseTypeCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'System code (e.g., standard, premium)',
  `licenseTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description of the license type',
  `defaultUserLimit` int NOT NULL DEFAULT '50' COMMENT 'Default maximum users allowed',
  `monthlyPrice` decimal(10,2) DEFAULT NULL COMMENT 'Monthly subscription price',
  `yearlyPrice` decimal(10,2) DEFAULT NULL COMMENT 'Yearly subscription price (discounted)',
  `defaultDuration` int NOT NULL DEFAULT '365' COMMENT 'Default license duration in days',
  `features` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of included features',
  `isPopular` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Mark as popular/recommended',
  `displayOrder` int NOT NULL DEFAULT '0' COMMENT 'Sort order for display',
  `colorCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color for UI display (e.g., #5b6fe3)',
  `iconClass` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Font Awesome icon class',
  `restrictions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of restrictions/limitations',
  `benefits` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of key benefits',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL COMMENT 'User ID who last updated',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`licenseTypeID`),
  UNIQUE KEY `licenseTypeCode` (`licenseTypeCode`),
  UNIQUE KEY `idx_licenseTypeCode` (`licenseTypeCode`),
  KEY `idx_displayOrder` (`displayOrder`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores configurable license types for tenant organizations';

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

DROP TABLE IF EXISTS `tija_name_prefixes`;
CREATE TABLE IF NOT EXISTS `tija_name_prefixes` (
  `prefixID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prefixName` varchar(10) NOT NULL,
  `prefixDescription` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `lapsed` enum('Y','N') DEFAULT 'N',
  `suspended` enum('Y','N') DEFAULT 'N',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`prefixID`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_notifications`;
CREATE TABLE IF NOT EXISTS `tija_notifications` (
  `notificationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int NOT NULL,
  `approverID` int NOT NULL,
  `originatorUserID` int NOT NULL,
  `targetUserID` int NOT NULL,
  `segmentType` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'general',
  `segmentID` int DEFAULT NULL,
  `notificationNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `notificationType` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `emailed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `notificationText` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notificationStatus` enum('read','unread') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'unread',
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`notificationID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_notifications`
--

INSERT INTO `tija_notifications` (`notificationID`, `DateAdded`, `employeeID`, `approverID`, `originatorUserID`, `targetUserID`, `segmentType`, `segmentID`, `notificationNotes`, `notificationType`, `emailed`, `notificationText`, `notificationStatus`, `timestamp`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-01 17:27:25', 4, 4, 4, 4, 'sales', 1, '<p>You have been assigned to the sales case <strong>Annual Audit</strong> by Felix  Mauncho (FM)</p>\r\n                                          <p><a href=\'http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=sales&p=sale_details&saleid=1\'>View Sales Case</a></p>\r\n                                          <p> You have been assigned to this sales case.</p>', 'sales_case_add', 'N', NULL, 'unread', '2025-12-01 17:27:25', 'N', 'N'),
(2, '2025-12-01 17:54:24', 4, 4, 4, 4, 'sales', 2, '<p>You have been assigned to the sales case <strong>Employee on Record</strong> by Felix  Mauncho (FM)</p>\r\n                                          <p><a href=\'http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=sales&p=sale_details&saleid=2\'>View Sales Case</a></p>\r\n                                          <p> You have been assigned to this sales case.</p>', 'sales_case_add', 'N', NULL, 'unread', '2025-12-01 17:54:24', 'N', 'N'),
(3, '2025-12-02 16:07:20', 2, 4, 0, 0, 'client_relationships', 2, '<p>Client Relationship for  has been updated by Brian Julius Nyongesa ()</p>\r\n                                       <p><a href=\'../../../html/?s=user&ss=clients&p=client_details&clientID=\'>View Client Relationship</a></p>\r\n                                       <p> You have been assigned to this client relationship.</p>', 'client_relationships_add', 'N', NULL, 'unread', '2025-12-02 16:07:20', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notifications_enhanced`
--

DROP TABLE IF EXISTS `tija_notifications_enhanced`;
CREATE TABLE IF NOT EXISTS `tija_notifications_enhanced` (
  `notificationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eventID` int NOT NULL,
  `userID` int NOT NULL,
  `originatorUserID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `orgDataID` int DEFAULT NULL,
  `segmentType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `segmentID` int DEFAULT NULL,
  `notificationTitle` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notificationBody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notificationData` json DEFAULT NULL,
  `notificationLink` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notificationIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-notification-line',
  `priority` enum('low','medium','high','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('unread','read','archived','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  `readAt` datetime DEFAULT NULL,
  `archivedAt` datetime DEFAULT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`notificationID`),
  KEY `idx_user` (`userID`,`status`),
  KEY `idx_event` (`eventID`),
  KEY `idx_originator` (`originatorUserID`),
  KEY `idx_segment` (`segmentType`,`segmentID`),
  KEY `idx_date` (`DateAdded`),
  KEY `idx_entity` (`entityID`,`orgDataID`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notifications_enhanced`
--

INSERT INTO `tija_notifications_enhanced` (`notificationID`, `DateAdded`, `eventID`, `userID`, `originatorUserID`, `entityID`, `orgDataID`, `segmentType`, `segmentID`, `notificationTitle`, `notificationBody`, `notificationData`, `notificationLink`, `notificationIcon`, `priority`, `status`, `readAt`, `archivedAt`, `expiresAt`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(134, '2025-12-06 12:41:15', 2, 22, 24, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Jane Smith', 'Jane Smith has submitted a leave application for Annual Leave from Dec 7, 2025 to Dec 9, 2025 (2 day(s)). Please review and approve.', '{\"cta_link\": \"http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\", \"end_date\": \"Dec 9, 2025\", \"site_url\": \"http://localhost/sbsl.tija.sbsl.co.ke\", \"site_name\": \"Tija Practice Management System\", \"step_name\": \"Direct Supervisor\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Dec 7, 2025\", \"total_days\": 2, \"employee_id\": \"24\", \"approver_name\": \"Test Employee\", \"employee_name\": \"Jane Smith\", \"is_final_step\": false, \"application_id\": \"1\", \"approval_level\": 1, \"application_link\": \"?s=user&ss=leave&p=pending_approvals&id=1\", \"application_link_full\": \"http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-06 12:41:15', 'N', 'N'),
(135, '2025-12-06 12:41:21', 2, 4, 24, 1, 1, 'leave_application', 1, 'Leave Application Pending Approval - Jane Smith', 'Jane Smith has submitted a leave application for Annual Leave from Dec 7, 2025 to Dec 9, 2025 (2 day(s)). Please review and approve.', '{\"cta_link\": \"http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\", \"end_date\": \"Dec 9, 2025\", \"site_url\": \"http://localhost/sbsl.tija.sbsl.co.ke\", \"site_name\": \"Tija Practice Management System\", \"step_name\": \"HR Manager\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Dec 7, 2025\", \"total_days\": 2, \"employee_id\": \"24\", \"approver_name\": \"Felix Mauncho\", \"employee_name\": \"Jane Smith\", \"is_final_step\": true, \"application_id\": \"1\", \"approval_level\": 2, \"application_link\": \"?s=user&ss=leave&p=pending_approvals&id=1\", \"application_link_full\": \"http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=leave&p=pending_approvals&id=1\"}', '?s=user&ss=leave&p=pending_approvals&id=1', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-12-06 12:41:21', 'N', 'N'),
(136, '2025-12-06 12:41:27', 1, 24, 24, 1, 1, 'leave_application', 1, 'Leave Application Submitted', 'Your leave application for Annual Leave from Dec 7, 2025 to Dec 9, 2025 (2 day(s)) has been submitted successfully and is pending approval.', '{\"cta_link\": \"http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=1\", \"end_date\": \"Dec 9, 2025\", \"site_url\": \"http://localhost/sbsl.tija.sbsl.co.ke\", \"site_name\": \"Tija Practice Management System\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Dec 7, 2025\", \"total_days\": 2, \"employee_id\": \"24\", \"leave_reason\": \"eefwee e we ww\", \"employee_name\": \"Jane Smith\", \"application_id\": \"1\", \"application_link\": \"?s=user&ss=leave&p=my_applications&id=1\", \"application_link_full\": \"http://localhost/sbsl.tija.sbsl.co.ke/html/?s=user&ss=leave&p=my_applications&id=1\"}', '?s=user&ss=leave&p=my_applications&id=1', 'ri-calendar-event-line', 'medium', 'read', '2025-12-06 12:42:49', NULL, NULL, '2025-12-06 12:42:49', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_channels`
--

DROP TABLE IF EXISTS `tija_notification_channels`;
CREATE TABLE IF NOT EXISTS `tija_notification_channels` (
  `channelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channelName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `channelSlug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `channelDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `channelIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-notification-line',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `requiresConfiguration` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `configFields` json DEFAULT NULL,
  `sortOrder` int DEFAULT '0',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`channelID`),
  UNIQUE KEY `channelSlug` (`channelSlug`),
  UNIQUE KEY `idx_channel_slug` (`channelSlug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `tija_notification_entity_preferences`;
CREATE TABLE IF NOT EXISTS `tija_notification_entity_preferences` (
  `entityPreferenceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entityID` int NOT NULL,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `isEnabled` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `enforceForAllUsers` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `notifyImmediately` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `notifyDigest` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `digestFrequency` enum('none','daily','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`entityPreferenceID`),
  UNIQUE KEY `unique_entity_event_channel` (`entityID`,`eventID`,`channelID`),
  KEY `idx_entity_pref_entity` (`entityID`),
  KEY `idx_entity_pref_event` (`eventID`),
  KEY `idx_entity_pref_channel` (`channelID`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `tija_notification_events`;
CREATE TABLE IF NOT EXISTS `tija_notification_events` (
  `eventID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moduleID` int NOT NULL,
  `eventName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventSlug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `eventCategory` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `isUserConfigurable` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `defaultEnabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `priorityLevel` enum('low','medium','high','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `sortOrder` int DEFAULT '0',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`eventID`),
  UNIQUE KEY `unique_event_slug` (`eventSlug`,`moduleID`),
  KEY `idx_module` (`moduleID`),
  KEY `idx_event_slug` (`eventSlug`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(7, '2025-10-22 09:56:25', 1, 'Leave Starting Soon', 'leave_starting_soon', 'Reminder that leave is starting soon', 'reminder', 'Y', 'Y', 'Y', 'low', 7, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(8, '2025-11-27 07:24:03', 1, 'New Handover Assignment', 'leave_handover_assignment', 'Sent to nominees when they are allocated handover tasks', 'general', 'Y', 'Y', 'Y', 'high', 1, '2025-11-27 07:24:03', NULL, 'N', 'N'),
(9, '2025-11-27 07:24:03', 1, 'Handover Plan Submitted', 'leave_handover_submitted', 'Sent to applicants confirming their handover plan and nominee', 'general', 'Y', 'Y', 'Y', 'medium', 2, '2025-11-27 07:24:03', NULL, 'N', 'N'),
(10, '2025-11-27 07:24:03', 1, 'Handover Revision Requested', 'leave_handover_revision_requested', 'Sent to applicants when the nominee needs additional information', 'general', 'Y', 'Y', 'Y', 'high', 3, '2025-11-27 07:24:03', NULL, 'N', 'N'),
(11, '2025-11-27 07:24:04', 1, 'Handover Accepted', 'leave_handover_accepted', 'Sent to applicants once the nominee accepts the handover', 'general', 'Y', 'Y', 'Y', 'medium', 4, '2025-11-27 07:24:04', NULL, 'N', 'N'),
(12, '2025-11-27 07:24:04', 1, 'Handover Completed', 'leave_handover_completed', 'Sent when all handover tasks are confirmed', 'general', 'Y', 'Y', 'Y', 'medium', 5, '2025-11-27 07:24:04', NULL, 'N', 'N'),
(13, '2025-11-27 07:24:04', 1, 'Handover Response Overdue', 'leave_handover_timer_expired', 'Sent when the nominee has not acknowledged the handover in time', 'general', 'Y', 'Y', 'Y', 'high', 6, '2025-11-27 07:24:04', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_logs`
--

DROP TABLE IF EXISTS `tija_notification_logs`;
CREATE TABLE IF NOT EXISTS `tija_notification_logs` (
  `logID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notificationID` int DEFAULT NULL,
  `queueID` int DEFAULT NULL,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `userID` int NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actionDetails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`logID`),
  KEY `idx_notification` (`notificationID`),
  KEY `idx_queue` (`queueID`),
  KEY `idx_user` (`userID`),
  KEY `idx_date` (`DateAdded`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_logs`
--

INSERT INTO `tija_notification_logs` (`logID`, `DateAdded`, `notificationID`, `queueID`, `eventID`, `channelID`, `userID`, `action`, `actionDetails`, `ipAddress`, `userAgent`) VALUES
(158, '2025-12-06 12:41:15', 134, NULL, 2, 1, 22, 'created', 'Shared notification record created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(159, '2025-12-06 12:41:21', 134, 51, 2, 2, 22, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@skm.co.ke', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(160, '2025-12-06 12:41:21', 135, NULL, 2, 1, 4, 'created', 'Shared notification record created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(161, '2025-12-06 12:41:27', 135, 52, 2, 2, 4, 'sent', 'Email sent immediately via PHPMailer to felix.mauncho@sbsl.co.ke', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(162, '2025-12-06 12:41:27', 136, NULL, 1, 1, 24, 'created', 'Shared notification record created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(163, '2025-12-06 12:41:32', 136, 53, 1, 2, 24, 'sent', 'Email sent immediately via PHPMailer to mauncho.home@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(164, '2025-12-06 12:42:49', 136, NULL, 0, 0, 24, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_modules`
--

DROP TABLE IF EXISTS `tija_notification_modules`;
CREATE TABLE IF NOT EXISTS `tija_notification_modules` (
  `moduleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moduleName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moduleSlug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moduleDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `moduleIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-notification-line',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `sortOrder` int DEFAULT '0',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`moduleID`),
  UNIQUE KEY `moduleSlug` (`moduleSlug`),
  KEY `idx_module_slug` (`moduleSlug`),
  KEY `idx_active` (`isActive`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `tija_notification_preferences`;
CREATE TABLE IF NOT EXISTS `tija_notification_preferences` (
  `preferenceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `isEnabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyImmediately` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyDigest` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `digestFrequency` enum('none','daily','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`preferenceID`),
  UNIQUE KEY `unique_preference` (`userID`,`eventID`,`channelID`),
  KEY `idx_user` (`userID`),
  KEY `eventID` (`eventID`),
  KEY `channelID` (`channelID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_queue`
--

DROP TABLE IF EXISTS `tija_notification_queue`;
CREATE TABLE IF NOT EXISTS `tija_notification_queue` (
  `queueID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notificationID` int NOT NULL,
  `channelID` int NOT NULL,
  `recipientEmail` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipientPhone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduledFor` datetime DEFAULT NULL,
  `attempts` int DEFAULT '0',
  `maxAttempts` int DEFAULT '3',
  `lastAttemptAt` datetime DEFAULT NULL,
  `status` enum('pending','processing','sent','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `errorMessage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sentAt` datetime DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`queueID`),
  KEY `idx_notification` (`notificationID`),
  KEY `idx_status` (`status`,`scheduledFor`),
  KEY `idx_channel` (`channelID`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_queue`
--

INSERT INTO `tija_notification_queue` (`queueID`, `DateAdded`, `notificationID`, `channelID`, `recipientEmail`, `recipientPhone`, `scheduledFor`, `attempts`, `maxAttempts`, `lastAttemptAt`, `status`, `errorMessage`, `sentAt`, `LastUpdate`) VALUES
(51, '2025-12-06 12:41:21', 134, 2, 'felix.mauncho@skm.co.ke', NULL, '2025-12-06 12:41:21', 0, 3, NULL, 'sent', NULL, '2025-12-06 12:41:21', '2025-12-06 12:41:21'),
(52, '2025-12-06 12:41:27', 135, 2, 'felix.mauncho@sbsl.co.ke', NULL, '2025-12-06 12:41:27', 0, 3, NULL, 'sent', NULL, '2025-12-06 12:41:27', '2025-12-06 12:41:27'),
(53, '2025-12-06 12:41:32', 136, 2, 'mauncho.home@gmail.com', NULL, '2025-12-06 12:41:32', 0, 3, NULL, 'sent', NULL, '2025-12-06 12:41:32', '2025-12-06 12:41:32');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_templates`
--

DROP TABLE IF EXISTS `tija_notification_templates`;
CREATE TABLE IF NOT EXISTS `tija_notification_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `orgDataID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `templateName` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateSubject` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `templateBody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateVariables` json DEFAULT NULL,
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isSystem` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `createdBy` int DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  KEY `idx_event` (`eventID`),
  KEY `idx_channel` (`channelID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_active` (`isActive`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_templates`
--

INSERT INTO `tija_notification_templates` (`templateID`, `DateAdded`, `eventID`, `channelID`, `orgDataID`, `entityID`, `templateName`, `templateSubject`, `templateBody`, `templateVariables`, `isDefault`, `isSystem`, `isActive`, `createdBy`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 1, NULL, NULL, 'Leave Application Submitted - In-App', 'Leave Application Submitted', 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been submitted successfully and is pending approval.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 2, NULL, NULL, 'Leave Application Submitted - Email', 'Leave Application Submitted', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Submitted</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Submitted</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request has been received and routed to your approvers.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Reason provided: {{leave_reason}}</p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"leave_reason\", \"application_id\", \"application_link\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 2, 1, NULL, NULL, 'Leave Pending Approval - In-App', 'Leave Application Pending Approval - {{employee_name}}', '{{employee_name}} has submitted a leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)). Please review and approve.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approval_level\", \"approver_name\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 3, 1, NULL, NULL, 'Leave Approved - In-App', 'Leave Application Approved', 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been approved.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approver_name\", \"approver_comments\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 4, 1, NULL, NULL, 'Leave Rejected - In-App', 'Leave Application Rejected', 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been rejected.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approver_name\", \"rejection_reason\"]', 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(6, '2025-11-25 09:50:03', 2, 2, NULL, NULL, '', 'Leave Application Pending Approval - {{employee_name}}', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Request Pending Your Approval</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Request Pending Your Approval</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">{{employee_name}} has submitted a {{leave_type}} request that requires your review.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <p style=\"margin:20px 0 0;font-size:14px;color:#1f2937;\">Approval step: <strong>{{approval_level}}</strong></p>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Leave Request</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(7, '2025-11-25 09:50:03', 3, 2, NULL, NULL, '', 'Leave Application Approved', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Approved</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Approved</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Great news — {{approver_name}} approved your leave request.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Approved Leave Details</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <div style=\"margin:24px 0;padding:16px;border-radius:12px;background-color:#ecfdf5;\"><p style=\"margin:0;font-size:14px;color:#065f46;\"><strong>Approver comments:</strong> {{approver_comments}}</p></div>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">View Application</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(8, '2025-11-25 09:50:03', 4, 2, NULL, NULL, '', 'Leave Application Rejected', '<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Leave Application Update</title>\n</head>\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\n        <tr>\n            <td style=\"padding:32px;\">\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\n                    <tr>\n                        <td style=\"padding:40px 40px 32px;\">\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Leave Application Update</h2>\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your leave request was not approved by {{approver_name}}.</p>\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Request Summary</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Total days:</strong> {{total_days}}</p>\n                            </div>\n                            <div style=\"margin:24px 0;padding:16px;border-radius:12px;background-color:#fef2f2;\"><p style=\"margin:0;font-size:14px;color:#991b1b;\"><strong>Reason provided:</strong> {{approver_comments}}</p></div>\n                            <div style=\"text-align:center;margin:32px 0 16px;\">\n                                <a href=\"{{application_link_full}}\" style=\"display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;\">Review Details</a>\n                            </div>\n                            <p style=\"font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;\">\n                                Or copy and paste this link into your browser:<br>\n                                <a href=\"{{application_link_full}}\" style=\"color:#2563eb;text-decoration:none;\">{{application_link_full}}</a>\n                            </p>\n                            <p style=\"font-size:12px;color:#94a3b8;text-align:center;margin:0;\">\n                                Sent from {{site_name}} · {{site_url}}\n                            </p>\n                        </td>\n                    </tr>\n                </table>\n            </td>\n        </tr>\n    </table>\n</body>\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-25 09:50:03', NULL, 'N', 'N'),
(9, '2025-11-27 07:24:03', 8, 1, NULL, NULL, '', 'New handover assignment from {{employee_name}}', '{{employee_name}} assigned their {{leave_type}} handover to you for {{start_date}} – {{end_date}}. Review the tasks and acknowledge the handover.', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:03', NULL, 'N', 'N'),
(10, '2025-11-27 07:24:03', 8, 2, NULL, NULL, '', 'New handover assignment from {{employee_name}}', '<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>You were nominated to cover for {{employee_name}}</title>\r\n</head>\r\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n        <tr>\r\n            <td style=\"padding:32px;\">\r\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\r\n                    <tr>\r\n                        <td style=\"padding:40px 40px 32px;\">\r\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">You were nominated to cover for {{employee_name}}</h2>\r\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Please review the assigned responsibilities while {{employee_name}} is away.</p>\r\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\r\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Handover Summary</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Nominee:</strong> {{nominee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\r\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Next steps:</strong> Sign in to acknowledge the handover, confirm access to credentials, and raise any blockers.</p>\r\n                            </div>\r\n                            <div style=\"margin-top:32px;\">\r\n                                <a href=\"{{absolute_link}}\" style=\"display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;\">Review Handover</a>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td style=\"padding:24px 40px;border-top:1px solid #e2e8f0;\">\r\n                            <p style=\"margin:0;font-size:13px;color:#94a3b8;\">You’re receiving this message because you’re part of a leave handover workflow.</p>\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </td>\r\n        </tr>\r\n    </table>\r\n</body>\r\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:03', NULL, 'N', 'N'),
(11, '2025-11-27 07:24:03', 9, 1, NULL, NULL, '', 'Handover plan logged for {{leave_type}}', 'Your {{leave_type}} handover has been saved. {{nominee_name}} has been notified and can now acknowledge the plan.', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:03', NULL, 'N', 'N'),
(12, '2025-11-27 07:24:03', 9, 2, NULL, NULL, '', 'Handover plan logged for {{leave_type}}', '<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Handover plan recorded for {{leave_type}}</title>\r\n</head>\r\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n        <tr>\r\n            <td style=\"padding:32px;\">\r\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\r\n                    <tr>\r\n                        <td style=\"padding:40px 40px 32px;\">\r\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Handover plan recorded for {{leave_type}}</h2>\r\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">{{nominee_name}} has been notified. Track confirmations and update tasks as needed.</p>\r\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\r\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Plan Overview</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Nominee:</strong> {{nominee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\r\n                                \r\n                            </div>\r\n                            <div style=\"margin-top:32px;\">\r\n                                <a href=\"{{absolute_link}}\" style=\"display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;\">View Handover</a>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td style=\"padding:24px 40px;border-top:1px solid #e2e8f0;\">\r\n                            <p style=\"margin:0;font-size:13px;color:#94a3b8;\">You’re receiving this message because you’re part of a leave handover workflow.</p>\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </td>\r\n        </tr>\r\n    </table>\r\n</body>\r\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:03', NULL, 'N', 'N'),
(13, '2025-11-27 07:24:03', 10, 1, NULL, NULL, '', 'Revision requested by {{nominee_name}}', '{{nominee_name}} requested revisions to your {{leave_type}} handover. Details: {{requested_changes}}.', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:03', NULL, 'N', 'N'),
(14, '2025-11-27 07:24:03', 10, 2, NULL, NULL, '', 'Revision requested by {{nominee_name}}', '<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>{{nominee_name}} requested updates</title>\r\n</head>\r\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n        <tr>\r\n            <td style=\"padding:32px;\">\r\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\r\n                    <tr>\r\n                        <td style=\"padding:40px 40px 32px;\">\r\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">{{nominee_name}} requested updates</h2>\r\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Your nominee needs more information before accepting the handover.</p>\r\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\r\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Revision Details</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Nominee:</strong> {{nominee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\r\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\"><strong>Requested changes:</strong><br>{{requested_changes}}</p>\r\n                            </div>\r\n                            <div style=\"margin-top:32px;\">\r\n                                <a href=\"{{absolute_link}}\" style=\"display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;\">Update Handover</a>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td style=\"padding:24px 40px;border-top:1px solid #e2e8f0;\">\r\n                            <p style=\"margin:0;font-size:13px;color:#94a3b8;\">You’re receiving this message because you’re part of a leave handover workflow.</p>\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </td>\r\n        </tr>\r\n    </table>\r\n</body>\r\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:03', NULL, 'N', 'N'),
(15, '2025-11-27 07:24:04', 11, 1, NULL, NULL, '', '{{nominee_name}} accepted the handover', '{{nominee_name}} confirmed readiness to cover your {{leave_type}} handover.', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:04', NULL, 'N', 'N'),
(16, '2025-11-27 07:24:04', 11, 2, NULL, NULL, '', '{{nominee_name}} accepted the handover', '<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>{{nominee_name}} accepted your handover</title>\r\n</head>\r\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n        <tr>\r\n            <td style=\"padding:32px;\">\r\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\r\n                    <tr>\r\n                        <td style=\"padding:40px 40px 32px;\">\r\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">{{nominee_name}} accepted your handover</h2>\r\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">All required tasks have been acknowledged. Managers can now proceed with review.</p>\r\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\r\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Handover Snapshot</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Nominee:</strong> {{nominee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\r\n                                \r\n                            </div>\r\n                            <div style=\"margin-top:32px;\">\r\n                                <a href=\"{{absolute_link}}\" style=\"display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;\">Open Handover</a>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td style=\"padding:24px 40px;border-top:1px solid #e2e8f0;\">\r\n                            <p style=\"margin:0;font-size:13px;color:#94a3b8;\">You’re receiving this message because you’re part of a leave handover workflow.</p>\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </td>\r\n        </tr>\r\n    </table>\r\n</body>\r\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:04', NULL, 'N', 'N'),
(17, '2025-11-27 07:24:04', 12, 1, NULL, NULL, '', 'Handover confirmed for {{leave_type}}', 'All tasks for {{leave_type}} ({{start_date}} – {{end_date}}) are confirmed. Your handover is complete.', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:04', NULL, 'N', 'N'),
(18, '2025-11-27 07:24:04', 12, 2, NULL, NULL, '', 'Handover confirmed for {{leave_type}}', '<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Handover complete for {{leave_type}}</title>\r\n</head>\r\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n        <tr>\r\n            <td style=\"padding:32px;\">\r\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\r\n                    <tr>\r\n                        <td style=\"padding:40px 40px 32px;\">\r\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Handover complete for {{leave_type}}</h2>\r\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">Every task has been confirmed. The leave application is ready for final processing.</p>\r\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\r\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">What happens next</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Nominee:</strong> {{nominee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\r\n                                \r\n                            </div>\r\n                            <div style=\"margin-top:32px;\">\r\n                                <a href=\"{{absolute_link}}\" style=\"display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;\">View Application</a>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td style=\"padding:24px 40px;border-top:1px solid #e2e8f0;\">\r\n                            <p style=\"margin:0;font-size:13px;color:#94a3b8;\">You’re receiving this message because you’re part of a leave handover workflow.</p>\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </td>\r\n        </tr>\r\n    </table>\r\n</body>\r\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:04', NULL, 'N', 'N'),
(19, '2025-11-27 07:24:04', 13, 1, NULL, NULL, '', 'Handover response overdue', 'The nominee has not acknowledged the {{leave_type}} handover for {{start_date}} – {{end_date}}. Please follow up.', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:04', NULL, 'N', 'N'),
(20, '2025-11-27 07:24:04', 13, 2, NULL, NULL, '', 'Handover response overdue', '<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <meta charset=\"UTF-8\">\r\n    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Handover acknowledgement overdue</title>\r\n</head>\r\n<body style=\"margin:0;padding:0;background-color:#f4f6fb;font-family:\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;\">\r\n    <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n        <tr>\r\n            <td style=\"padding:32px;\">\r\n                <table role=\"presentation\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" style=\"max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);\">\r\n                    <tr>\r\n                        <td style=\"padding:40px 40px 32px;\">\r\n                            <h2 style=\"margin:0 0 12px;font-size:24px;color:#0f172a;\">Handover acknowledgement overdue</h2>\r\n                            <p style=\"margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;\">The nominee has not responded within the required timeframe.</p>\r\n                            <div style=\"padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;\">\r\n                                <p style=\"margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;\">Pending Handover</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Employee:</strong> {{employee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Nominee:</strong> {{nominee_name}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Leave type:</strong> {{leave_type}}</p>\r\n                                <p style=\"margin:0 0 8px;font-size:14px;color:#0f172a;\"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>\r\n                                <p style=\"margin:0;font-size:14px;color:#0f172a;\">Please reach out to {{nominee_name}} or reassign the handover so that coverage is confirmed.</p>\r\n                            </div>\r\n                            <div style=\"margin-top:32px;\">\r\n                                <a href=\"{{absolute_link}}\" style=\"display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;\">Follow Up Now</a>\r\n                            </div>\r\n                        </td>\r\n                    </tr>\r\n                    <tr>\r\n                        <td style=\"padding:24px 40px;border-top:1px solid #e2e8f0;\">\r\n                            <p style=\"margin:0;font-size:13px;color:#94a3b8;\">You’re receiving this message because you’re part of a leave handover workflow.</p>\r\n                        </td>\r\n                    </tr>\r\n                </table>\r\n            </td>\r\n        </tr>\r\n    </table>\r\n</body>\r\n</html>', NULL, 'Y', 'Y', 'Y', NULL, '2025-11-27 04:24:04', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_template_variables`
--

DROP TABLE IF EXISTS `tija_notification_template_variables`;
CREATE TABLE IF NOT EXISTS `tija_notification_template_variables` (
  `variableID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moduleID` int NOT NULL,
  `variableName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variableSlug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variableDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dataSource` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataField` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exampleValue` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sortOrder` int DEFAULT '0',
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`variableID`),
  UNIQUE KEY `unique_variable` (`moduleID`,`variableSlug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `tija_operational_projects`
--

DROP TABLE IF EXISTS `tija_operational_projects`;
CREATE TABLE IF NOT EXISTS `tija_operational_projects` (
  `operationalProjectID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `projectCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `projectName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., "FY25 HR Operations"',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `fiscalYear` int NOT NULL,
  `projectID` int DEFAULT NULL COMMENT 'FK to tija_projects - Soft booking link',
  `allocatedHours` decimal(10,2) DEFAULT '0.00' COMMENT 'Planned BAU hours',
  `actualHours` decimal(10,2) DEFAULT '0.00' COMMENT 'Logged hours',
  `fteRequirement` decimal(5,2) DEFAULT '0.00' COMMENT 'Calculated FTE',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head responsible',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`operationalProjectID`),
  UNIQUE KEY `unique_projectCode` (`projectCode`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_fiscalYear` (`fiscalYear`),
  KEY `idx_project` (`projectID`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Operational projects (BAU buckets) for capacity planning';

-- --------------------------------------------------------

--
-- Table structure for table `tija_operational_tasks`
--

DROP TABLE IF EXISTS `tija_operational_tasks`;
CREATE TABLE IF NOT EXISTS `tija_operational_tasks` (
  `operationalTaskID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates',
  `workflowInstanceID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflow_instances - If workflow-enabled',
  `instanceNumber` int DEFAULT '1' COMMENT 'Cycle number',
  `dueDate` date NOT NULL,
  `startDate` date DEFAULT NULL,
  `completedDate` datetime DEFAULT NULL,
  `status` enum('pending','in_progress','completed','overdue','cancelled','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `assigneeID` int NOT NULL COMMENT 'FK to people',
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `actualDuration` decimal(10,2) DEFAULT NULL COMMENT 'Actual hours spent',
  `nextInstanceDueDate` date DEFAULT NULL COMMENT 'For regeneration',
  `parentInstanceID` int UNSIGNED DEFAULT NULL COMMENT 'Links to previous cycle',
  `blockedByTaskID` int UNSIGNED DEFAULT NULL COMMENT 'Dependency blocker',
  `sopReviewed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'SOP review status',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`operationalTaskID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_workflowInstance` (`workflowInstanceID`),
  KEY `idx_assignee` (`assigneeID`),
  KEY `idx_process` (`processID`),
  KEY `idx_status` (`status`),
  KEY `idx_dueDate` (`dueDate`),
  KEY `idx_parentInstance` (`parentInstanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Operational task instances';

-- --------------------------------------------------------

--
-- Table structure for table `tija_operational_task_checklists`
--

DROP TABLE IF EXISTS `tija_operational_task_checklists`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_checklists` (
  `checklistItemID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates - Template-level',
  `operationalTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks - Instance-level',
  `itemOrder` int NOT NULL,
  `itemDescription` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `isMandatory` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isCompleted` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `completedByID` int DEFAULT NULL COMMENT 'FK to people',
  `completedDate` datetime DEFAULT NULL,
  `validationRule` json DEFAULT NULL COMMENT 'Optional validation logic',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`checklistItemID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_operationalTask` (`operationalTaskID`),
  KEY `idx_itemOrder` (`itemOrder`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `tija_operational_task_dependencies`
--

DROP TABLE IF EXISTS `tija_operational_task_dependencies`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_dependencies` (
  `dependencyID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `predecessorTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks or templateID',
  `predecessorTemplateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates',
  `successorTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks or templateID',
  `successorTemplateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates',
  `dependencyType` enum('finish_to_start','start_to_start','finish_to_finish') COLLATE utf8mb4_unicode_ci DEFAULT 'finish_to_start',
  `lagDays` int DEFAULT '0' COMMENT 'Delay in days',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dependencyID`),
  KEY `idx_predecessorTask` (`predecessorTaskID`),
  KEY `idx_predecessorTemplate` (`predecessorTemplateID`),
  KEY `idx_successorTask` (`successorTaskID`),
  KEY `idx_successorTemplate` (`successorTemplateID`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `tija_operational_task_notifications`
--

DROP TABLE IF EXISTS `tija_operational_task_notifications`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_notifications` (
  `notificationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateID` int UNSIGNED NOT NULL COMMENT 'FK to tija_operational_task_templates',
  `employeeID` int NOT NULL COMMENT 'FK to people - User to notify',
  `dueDate` date NOT NULL COMMENT 'Task due date',
  `notificationType` enum('scheduled_task_ready','task_overdue','task_due_soon') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled_task_ready',
  `status` enum('pending','sent','acknowledged','processed','dismissed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sentDate` datetime DEFAULT NULL,
  `acknowledgedDate` datetime DEFAULT NULL,
  `processedDate` datetime DEFAULT NULL,
  `taskInstanceID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks - Created when processed',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`notificationID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_status` (`status`),
  KEY `idx_dueDate` (`dueDate`),
  KEY `taskInstanceID` (`taskInstanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications for manual task processing';

-- --------------------------------------------------------

--
-- Table structure for table `tija_operational_task_templates`
--

DROP TABLE IF EXISTS `tija_operational_task_templates`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_templates` (
  `templateID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `workflowID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflows - Optional workflow',
  `sopID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_sops - Linked SOP',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `frequencyType` enum('daily','weekly','monthly','quarterly','annually','custom','event_driven') COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequencyInterval` int DEFAULT '1' COMMENT 'e.g., every 2 weeks',
  `frequencyDayOfWeek` int DEFAULT NULL COMMENT '1-7 for weekly',
  `frequencyDayOfMonth` int DEFAULT NULL COMMENT '1-31 for monthly/quarterly',
  `frequencyMonthOfYear` int DEFAULT NULL COMMENT '1-12 for annually',
  `triggerEvent` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Event name for event-driven tasks',
  `estimatedDuration` decimal(10,2) DEFAULT NULL COMMENT 'Estimated hours',
  `assignmentRule` json DEFAULT NULL COMMENT 'Auto-assignment logic (role-based, employee-specific, round-robin, etc.)',
  `requiresApproval` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `approverRoleID` int DEFAULT NULL COMMENT 'FK to permission roles',
  `requiresSOPReview` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Must review SOP before starting',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `processingMode` enum('cron','manual','both') COLLATE utf8mb4_unicode_ci DEFAULT 'cron' COMMENT 'cron=automatic via cron, manual=user notification on login, both=both methods',
  `lastNotificationSent` datetime DEFAULT NULL COMMENT 'Last time notification was sent for manual processing',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  UNIQUE KEY `unique_templateCode` (`templateCode`),
  KEY `idx_process` (`processID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_sop` (`sopID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_frequencyType` (`frequencyType`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Operational task templates for recurring tasks';

--
-- Dumping data for table `tija_operational_task_templates`
--

INSERT INTO `tija_operational_task_templates` (`templateID`, `templateCode`, `templateName`, `templateDescription`, `processID`, `workflowID`, `sopID`, `functionalArea`, `functionalAreaID`, `frequencyType`, `frequencyInterval`, `frequencyDayOfWeek`, `frequencyDayOfMonth`, `frequencyMonthOfYear`, `triggerEvent`, `estimatedDuration`, `assignmentRule`, `requiresApproval`, `approverRoleID`, `requiresSOPReview`, `isActive`, `processingMode`, `lastNotificationSent`, `createdByID`, `functionalAreaOwnerID`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 'TMPL-PAYROLL-MONTHLY', 'Monthly Payroll Processing', 'Recurring monthly task to process payroll for all employees', 1, 1, 1, 'HR', 2, 'monthly', 1, NULL, 25, NULL, NULL, 8.00, NULL, 'Y', NULL, 'Y', 'Y', 'cron', NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'N', 'N'),
(2, 'TMPL-AP-WEEKLY', 'Weekly Accounts Payable Processing', 'Recurring weekly task to process vendor invoices and payments', 6, 2, 2, 'Finance', 1, 'weekly', 1, 5, NULL, NULL, NULL, 4.00, NULL, 'Y', NULL, 'N', 'Y', 'cron', NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'N', 'N'),
(3, 'TMPL-BANK-RECON-MONTHLY', 'Monthly Bank Reconciliation', 'Recurring monthly task to reconcile all bank accounts', 10, NULL, 3, 'Finance', 1, 'monthly', 1, NULL, 5, NULL, NULL, 3.00, NULL, 'Y', NULL, 'Y', 'Y', 'cron', NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'N', 'N'),
(4, 'TMPL-CASH-DAILY', 'Daily Cash Management', 'Recurring daily task to monitor cash position and liquidity', 9, NULL, NULL, 'Finance', 1, 'daily', 1, NULL, NULL, NULL, NULL, 1.00, NULL, 'N', NULL, 'N', 'Y', 'cron', NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'N', 'N'),
(5, 'TMPL-PERF-REVIEW-QTR', 'Quarterly Performance Reviews', 'Recurring quarterly task to conduct employee performance reviews', 4, NULL, NULL, 'HR', 2, 'quarterly', 1, NULL, NULL, 3, NULL, 2.00, NULL, 'Y', NULL, 'N', 'Y', 'manual', NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'N', 'N');

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
  `countryID` int NOT NULL,
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

INSERT INTO `tija_organisation_data` (`orgDataID`, `DateAdded`, `orgLogo`, `orgName`, `industrySectorID`, `numberOfEmployees`, `registrationNumber`, `orgPIN`, `costCenterEnabled`, `orgAddress`, `orgPostalCode`, `orgCity`, `countryID`, `orgPhoneNumber1`, `orgPhoneNUmber2`, `orgEmail`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-21 06:58:17', NULL, 'Strategic Business Solutions Limited', 80, 30, '98309', 'P051147271C', 'Y', 'Rainbow Towers\r\nP. O. BOX 2021', '00100', 'Nairobi', 25, '+254 721 358850', NULL, 'info@sbsl.co.ke', '2025-11-21 09:58:17', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_organisation_roles`
--

DROP TABLE IF EXISTS `tija_organisation_roles`;
CREATE TABLE IF NOT EXISTS `tija_organisation_roles` (
  `orgRoleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `jobTotleID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `jobTitleID` int NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`orgRoleID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_organization_functional_areas`
--

DROP TABLE IF EXISTS `tija_organization_functional_areas`;
CREATE TABLE IF NOT EXISTS `tija_organization_functional_areas` (
  `linkID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `orgDataID` int NOT NULL COMMENT 'FK to tija_organisation_data',
  `functionalAreaID` int UNSIGNED NOT NULL COMMENT 'FK to tija_functional_areas',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  UNIQUE KEY `unique_org_functional_area` (`orgDataID`,`functionalAreaID`),
  KEY `idx_organization` (`orgDataID`),
  KEY `idx_functionalArea` (`functionalAreaID`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Junction table linking organizations to functional areas';

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_charts`
--

DROP TABLE IF EXISTS `tija_org_charts`;
CREATE TABLE IF NOT EXISTS `tija_org_charts` (
  `orgChartID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgChartName` varchar(256) NOT NULL,
  `orgChartDescription` int NOT NULL COMMENT 'Description of the organizational chart',
  `chartType` varchar(50) DEFAULT 'hierarchical' COMMENT 'Type: hierarchical, matrix, flat, divisional',
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `effectiveDate` date DEFAULT NULL COMMENT 'Date when this org chart becomes effective',
  `isCurrent` enum('Y','N') DEFAULT 'N' COMMENT 'Is this the current active organizational chart',
  PRIMARY KEY (`orgChartID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_hierarchy_closure`
--

DROP TABLE IF EXISTS `tija_org_hierarchy_closure`;
CREATE TABLE IF NOT EXISTS `tija_org_hierarchy_closure` (
  `ancestor_id` int UNSIGNED NOT NULL COMMENT 'FK to tija_entities.entityID or people.ID',
  `descendant_id` int UNSIGNED NOT NULL COMMENT 'FK to tija_entities.entityID or people.ID',
  `depth` int NOT NULL DEFAULT '0' COMMENT 'Number of levels between ancestor and descendant',
  `hierarchy_type` enum('Administrative','Functional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Administrative' COMMENT 'Type of hierarchy relationship',
  `ancestor_type` enum('Entity','Individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Entity' COMMENT 'Type of ancestor node',
  `descendant_type` enum('Entity','Individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Entity' COMMENT 'Type of descendant node',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ancestor_id`,`descendant_id`,`hierarchy_type`),
  KEY `idx_ancestor` (`ancestor_id`,`hierarchy_type`),
  KEY `idx_descendant` (`descendant_id`,`hierarchy_type`),
  KEY `idx_depth` (`depth`,`hierarchy_type`),
  KEY `idx_hierarchy_type` (`hierarchy_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Closure Table for organizational hierarchy - stores all ancestor-descendant paths';

-- --------------------------------------------------------

--
-- Table structure for table `tija_org_role_types`
--

DROP TABLE IF EXISTS `tija_org_role_types`;
CREATE TABLE IF NOT EXISTS `tija_org_role_types` (
  `roleTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `roleTypeName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name (e.g., Executive, Management)',
  `roleTypeCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Short code (e.g., EXEC, MGT)',
  `roleTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Description of the role type',
  `displayOrder` int DEFAULT '0' COMMENT 'Order for display in dropdowns',
  `colorCode` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#667eea' COMMENT 'Hex color code for badges',
  `iconClass` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-user-tie' COMMENT 'FontAwesome icon class',
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Is this a default/system role type',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Is this role type active',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`roleTypeID`),
  UNIQUE KEY `unique_roleTypeCode` (`roleTypeCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_Suspended` (`Suspended`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role types for organizational roles';

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

DROP TABLE IF EXISTS `tija_overtime_multiplier`;
CREATE TABLE IF NOT EXISTS `tija_overtime_multiplier` (
  `overtimeMultiplierID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectID` int NOT NULL,
  `overtimeMultiplierName` varchar(254) NOT NULL,
  `multiplierRate` decimal(4,2) NOT NULL,
  `workTypeID` varchar(256) NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `entityID` int NOT NULL,
  PRIMARY KEY (`overtimeMultiplierID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_payroll_computation_rules`;
CREATE TABLE IF NOT EXISTS `tija_payroll_computation_rules` (
  `ruleID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int UNSIGNED NOT NULL,
  `entityID` int UNSIGNED NOT NULL,
  `ruleName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., PAYE Tax Calculation',
  `ruleDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ruleType` enum('tax','statutory_deduction','benefit','allowance','overtime') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `computationFormula` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Formula or algorithm for calculation',
  `parameters` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON: Parameters needed for calculation',
  `effectiveDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `priority` int DEFAULT '0' COMMENT 'Execution order',
  `createdBy` int UNSIGNED DEFAULT NULL,
  `updatedBy` int UNSIGNED DEFAULT NULL,
  `LastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`ruleID`),
  KEY `idx_entity` (`entityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_pay_grades`
--

DROP TABLE IF EXISTS `tija_pay_grades`;
CREATE TABLE IF NOT EXISTS `tija_pay_grades` (
  `payGradeID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int UNSIGNED NOT NULL,
  `entityID` int UNSIGNED NOT NULL,
  `payGradeCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., PG-1, PG-2, PG-3',
  `payGradeName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., Junior Level, Mid Level, Senior Level',
  `payGradeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `minSalary` decimal(15,2) NOT NULL COMMENT 'Minimum salary for this grade',
  `midSalary` decimal(15,2) NOT NULL COMMENT 'Midpoint salary for this grade',
  `maxSalary` decimal(15,2) NOT NULL COMMENT 'Maximum salary for this grade',
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'KES',
  `gradeLevel` int DEFAULT NULL COMMENT 'Numeric level for sorting (1=lowest, higher=senior)',
  `allowsOvertime` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Can employees in this grade get overtime?',
  `bonusEligible` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Are employees in this grade eligible for bonuses?',
  `commissionEligible` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Are employees eligible for commission?',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int UNSIGNED DEFAULT NULL,
  `updatedBy` int UNSIGNED DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`payGradeID`),
  UNIQUE KEY `idx_unique_grade` (`orgDataID`,`entityID`,`payGradeCode`,`Suspended`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_level` (`gradeLevel`),
  KEY `idx_active` (`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pay grade structure with salary ranges for each entity';

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

DROP TABLE IF EXISTS `tija_permission_levels`;
CREATE TABLE IF NOT EXISTS `tija_permission_levels` (
  `permissionLevelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permissionLevelTitle` varchar(255) NOT NULL,
  `permissionLevelDescription` mediumtext NOT NULL,
  `iconClass` varchar(256) NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`permissionLevelID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_permission_profiles`
--

DROP TABLE IF EXISTS `tija_permission_profiles`;
CREATE TABLE IF NOT EXISTS `tija_permission_profiles` (
  `permissionProfileID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permissionProfileTitle` varchar(255) NOT NULL,
  `permissionProfileDescription` mediumtext NOT NULL,
  `permissionProfileScopeID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdatedByID` int NOT NULL,
  PRIMARY KEY (`permissionProfileID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_permission_roles`;
CREATE TABLE IF NOT EXISTS `tija_permission_roles` (
  `permissionRoleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permRoleTitle` varchar(256) DEFAULT NULL,
  `permRoleDescription` mediumtext NOT NULL,
  `permissionProfileID` int NOT NULL,
  `permissionScopeID` int NOT NULL,
  `roleTypeID` int DEFAULT NULL,
  `importPermission` enum('Y','N') DEFAULT 'N',
  `exportPermission` enum('Y','N') DEFAULT 'N',
  `viewPermission` enum('Y','N') DEFAULT 'N',
  `editPermission` enum('Y','N') DEFAULT 'N',
  `addPermission` enum('Y','N') DEFAULT 'N',
  `deletePermission` enum('Y','N') DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`permissionRoleID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_permission_scopes`;
CREATE TABLE IF NOT EXISTS `tija_permission_scopes` (
  `permissionScopeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permissionScopeTitle` varchar(255) NOT NULL,
  `permissionScopeDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`permissionScopeID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_permission_types`;
CREATE TABLE IF NOT EXISTS `tija_permission_types` (
  `permissionTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permissionTypeTitle` varchar(255) NOT NULL,
  `permissionTypeDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`permissionTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_pms_work_segment`;
CREATE TABLE IF NOT EXISTS `tija_pms_work_segment` (
  `workSegmentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `workSegmentCode` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `workSegmentName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `workSegmentDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`workSegmentID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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
-- Table structure for table `tija_process_metrics`
--

DROP TABLE IF EXISTS `tija_process_metrics`;
CREATE TABLE IF NOT EXISTS `tija_process_metrics` (
  `metricID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
  `metricName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., cycle_time, cost_per_unit, error_rate',
  `metricValue` decimal(15,4) NOT NULL,
  `metricUnit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., hours, dollars, percentage',
  `measurementDate` date NOT NULL,
  `source` enum('actual','simulated','target') COLLATE utf8mb4_unicode_ci DEFAULT 'actual',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`metricID`),
  KEY `idx_process` (`processID`),
  KEY `idx_metricName` (`metricName`),
  KEY `idx_measurementDate` (`measurementDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process performance metrics';

-- --------------------------------------------------------

--
-- Table structure for table `tija_process_models`
--

DROP TABLE IF EXISTS `tija_process_models`;
CREATE TABLE IF NOT EXISTS `tija_process_models` (
  `modelID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `modelName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `modelType` enum('as_is','to_be','simulation','optimized') COLLATE utf8mb4_unicode_ci DEFAULT 'as_is',
  `modelDefinition` json DEFAULT NULL COMMENT 'Process model (BPMN-like structure)',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isBaseline` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Baseline for comparison',
  PRIMARY KEY (`modelID`),
  KEY `idx_process` (`processID`),
  KEY `idx_modelType` (`modelType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process model definitions';

-- --------------------------------------------------------

--
-- Table structure for table `tija_process_optimization_recommendations`
--

DROP TABLE IF EXISTS `tija_process_optimization_recommendations`;
CREATE TABLE IF NOT EXISTS `tija_process_optimization_recommendations` (
  `recommendationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
  `recommendationType` enum('automation','reengineering','resource_allocation','elimination') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendationTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendationDescription` text COLLATE utf8mb4_unicode_ci,
  `estimatedImpact` json DEFAULT NULL COMMENT 'Expected improvements',
  `implementationEffort` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('pending','approved','implemented','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `createdByID` int DEFAULT NULL COMMENT 'FK to people (system or user)',
  `approvedByID` int DEFAULT NULL COMMENT 'FK to people',
  `approvedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`recommendationID`),
  KEY `idx_process` (`processID`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process optimization recommendations';

-- --------------------------------------------------------

--
-- Table structure for table `tija_process_simulations`
--

DROP TABLE IF EXISTS `tija_process_simulations`;
CREATE TABLE IF NOT EXISTS `tija_process_simulations` (
  `simulationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `modelID` int UNSIGNED NOT NULL COMMENT 'FK to tija_process_models',
  `simulationName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `simulationDescription` text COLLATE utf8mb4_unicode_ci,
  `simulationParameters` json DEFAULT NULL COMMENT 'Input parameters',
  `simulationResults` json DEFAULT NULL COMMENT 'Output metrics',
  `runDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `runByID` int DEFAULT NULL COMMENT 'FK to people',
  `status` enum('pending','running','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  PRIMARY KEY (`simulationID`),
  KEY `idx_model` (`modelID`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process simulation runs';

-- --------------------------------------------------------

--
-- Table structure for table `tija_products`
--

DROP TABLE IF EXISTS `tija_products`;
CREATE TABLE IF NOT EXISTS `tija_products` (
  `productID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `productName` varchar(256) NOT NULL,
  `productDescription` mediumtext NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`productID`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_product_billing_period_levels`;
CREATE TABLE IF NOT EXISTS `tija_product_billing_period_levels` (
  `productBillingPeriodLevelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `productBillingPeriodLevelName` varchar(255) NOT NULL,
  `productBillingPeriodLevelDescription` text NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`productBillingPeriodLevelID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_product_rates`;
CREATE TABLE IF NOT EXISTS `tija_product_rates` (
  `productRateID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectID` int NOT NULL,
  `entityID` int NOT NULL,
  `productRateName` varchar(256) NOT NULL,
  `productRateTypeID` int NOT NULL,
  `priceRate` decimal(10,2) NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`productRateID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_product_rates`
--

INSERT INTO `tija_product_rates` (`productRateID`, `DateAdded`, `projectID`, `entityID`, `productRateName`, `productRateTypeID`, `priceRate`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-15 18:03:42', 74, 1, 'Consulting Day', 1, 150000.00, 0, '2025-11-15 18:03:42', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_product_rate_types`
--

DROP TABLE IF EXISTS `tija_product_rate_types`;
CREATE TABLE IF NOT EXISTS `tija_product_rate_types` (
  `productRateTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `productRateTypeName` varchar(255) NOT NULL,
  `productRateTypeDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`productRateTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_product_types`;
CREATE TABLE IF NOT EXISTS `tija_product_types` (
  `productTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `productTypeName` varchar(255) NOT NULL,
  `productTypeDescription` text NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`productTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_projects`;
CREATE TABLE IF NOT EXISTS `tija_projects` (
  `projectID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateLastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectCode` varchar(30) NOT NULL,
  `projectName` varchar(255) NOT NULL,
  `orgDataID` int NOT NULL,
  `caseID` int NOT NULL,
  `entityID` int NOT NULL,
  `clientID` int NOT NULL,
  `projectStart` date NOT NULL,
  `projectClose` date DEFAULT NULL,
  `projectDeadline` date DEFAULT NULL,
  `projectOwnerID` int DEFAULT NULL,
  `projectManagersIDs` varchar(256) DEFAULT NULL,
  `billable` enum('Y','N') DEFAULT 'N',
  `billingRateID` int DEFAULT NULL,
  `billableRateValue` decimal(10,2) NOT NULL DEFAULT '4000.00',
  `roundingoff` varchar(255) DEFAULT NULL,
  `roundingInterval` int DEFAULT NULL,
  `businessUnitID` int NOT NULL,
  `projectValue` decimal(10,2) DEFAULT NULL,
  `approval` enum('Y','N') NOT NULL DEFAULT 'N',
  `projectStatus` enum('open','closed','inactive') NOT NULL DEFAULT 'open',
  `isRecurring` enum('Y','N') DEFAULT 'N',
  `recurrenceType` enum('weekly','monthly','quarterly','annually','custom') DEFAULT NULL,
  `recurrenceInterval` int DEFAULT '1' COMMENT 'e.g., every 2 weeks',
  `recurrenceDayOfWeek` int DEFAULT NULL COMMENT '1-7 for weekly, NULL for others',
  `recurrenceDayOfMonth` int DEFAULT NULL COMMENT '1-31 for monthly/quarterly',
  `recurrenceMonthOfYear` int DEFAULT NULL COMMENT '1-12 for annually',
  `recurrenceStartDate` date DEFAULT NULL,
  `recurrenceEndDate` date DEFAULT NULL COMMENT 'NULL for indefinite',
  `recurrenceCount` int DEFAULT NULL COMMENT 'number of cycles, NULL for indefinite',
  `planReuseMode` enum('same','customizable') DEFAULT 'same',
  `teamAssignmentMode` enum('template','instance','both') DEFAULT 'template',
  `billingCycleAmount` decimal(15,2) DEFAULT NULL COMMENT 'amount per billing cycle',
  `autoGenerateInvoices` enum('Y','N') DEFAULT 'N',
  `invoiceDaysBeforeDue` int DEFAULT '7' COMMENT 'days before cycle end to generate draft',
  `salesCaseID` int DEFAULT NULL,
  `projectTypeID` int NOT NULL DEFAULT '1',
  `orderDate` date DEFAULT NULL,
  `projectType` enum('inhouse','recurrent','client') NOT NULL DEFAULT 'client',
  `allocatedWorkHours` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectID`),
  KEY `idx_recurring` (`isRecurring`,`recurrenceType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_assignments`
--

DROP TABLE IF EXISTS `tija_project_assignments`;
CREATE TABLE IF NOT EXISTS `tija_project_assignments` (
  `assignmentID` int NOT NULL AUTO_INCREMENT,
  `projectID` int NOT NULL COMMENT 'Project ID',
  `employeeID` int NOT NULL COMMENT 'Employee ID',
  `roleID` int DEFAULT NULL COMMENT 'Role in the project',
  `startDate` date DEFAULT NULL COMMENT 'Assignment start date',
  `endDate` date DEFAULT NULL COMMENT 'Assignment end date',
  `allocationPercentage` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage allocation to project',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`assignmentID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_role` (`roleID`),
  KEY `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee assignments to projects';

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_expenses`
--

DROP TABLE IF EXISTS `tija_project_expenses`;
CREATE TABLE IF NOT EXISTS `tija_project_expenses` (
  `expenseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expenseTypeID` int NOT NULL,
  `expenseAmount` decimal(10,2) NOT NULL,
  `expenseDescription` text,
  `expenseDate` date DEFAULT NULL,
  `expenseStatus` enum('pending','approved','rejected','disputed') NOT NULL DEFAULT 'pending',
  `expenseDocuments` text,
  `timeLogID` int DEFAULT NULL,
  `projectID` int DEFAULT NULL,
  `userID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`expenseID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_fee_expenses`
--

DROP TABLE IF EXISTS `tija_project_fee_expenses`;
CREATE TABLE IF NOT EXISTS `tija_project_fee_expenses` (
  `projectFeeExpenseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `productTypeID` int NOT NULL,
  `projectID` int NOT NULL,
  `feeCostName` varchar(255) NOT NULL,
  `feeCostDescription` text NOT NULL,
  `productQuantity` int NOT NULL,
  `productUnit` varchar(120) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL,
  `unitCost` decimal(10,2) NOT NULL,
  `vat` int NOT NULL,
  `dateOfCost` date DEFAULT NULL,
  `billable` varchar(120) NOT NULL,
  `billingDate` date NOT NULL,
  `billingFrequency` int NOT NULL,
  `billingFrequencyUnit` varchar(120) NOT NULL,
  `billingStartDate` date DEFAULT NULL,
  `recurrenceEnd` date DEFAULT NULL,
  `recurrencyTimes` int DEFAULT NULL,
  `billingEndDate` date DEFAULT NULL,
  `billingPhaseID` int DEFAULT NULL,
  `billingMilestone` int DEFAULT NULL,
  `billed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastupdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectFeeExpenseID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_files`
--

DROP TABLE IF EXISTS `tija_project_files`;
CREATE TABLE IF NOT EXISTS `tija_project_files` (
  `fileID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `projectID` int NOT NULL,
  `taskID` int DEFAULT NULL COMMENT 'Optional task linkage',
  `fileName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileOriginalName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileURL` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'pdf, docx, xlsx, image, etc.',
  `fileSize` bigint DEFAULT NULL COMMENT 'File size in bytes',
  `fileMimeType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'contract, design, report, etc.',
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `uploadedBy` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isPublic` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Accessible to client',
  `downloadCount` int DEFAULT '0',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`fileID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_category` (`category`),
  KEY `idx_uploader` (`uploadedBy`),
  KEY `idx_task` (`taskID`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project file and document management';

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_memeber_categories`
--

DROP TABLE IF EXISTS `tija_project_memeber_categories`;
CREATE TABLE IF NOT EXISTS `tija_project_memeber_categories` (
  `projectTeamMemeberCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `teamMemberCategoryName` varchar(255) NOT NULL,
  `teamMemberCategoryDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectTeamMemeberCategoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_phases`
--

DROP TABLE IF EXISTS `tija_project_phases`;
CREATE TABLE IF NOT EXISTS `tija_project_phases` (
  `projectPhaseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectID` int NOT NULL,
  `billingCycleID` int DEFAULT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `projectPhaseName` varchar(180) NOT NULL,
  `phaseDescription` text NOT NULL,
  `phaseStartDate` date DEFAULT NULL,
  `phaseEndDate` date DEFAULT NULL,
  `phaseWorkHrs` decimal(10,2) DEFAULT NULL,
  `phaseWeighting` decimal(10,2) DEFAULT NULL,
  `billingMilestone` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectPhaseID`),
  KEY `idx_billing_cycle` (`billingCycleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_plan_templates`
--

DROP TABLE IF EXISTS `tija_project_plan_templates`;
CREATE TABLE IF NOT EXISTS `tija_project_plan_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT,
  `templateName` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `templateCategory` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., software, construction, marketing',
  `isPublic` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Y=Organization-wide, N=Personal',
  `isSystemTemplate` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Y=Built-in, cannot be deleted',
  `createdByID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `usageCount` int DEFAULT '0' COMMENT 'Track how many times template is used',
  `lastUsedDate` datetime DEFAULT NULL,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `DateAdded` datetime NOT NULL,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  PRIMARY KEY (`templateID`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`),
  KEY `idx_creator` (`createdByID`),
  KEY `idx_public` (`isPublic`,`isActive`),
  KEY `idx_category` (`templateCategory`),
  KEY `idx_template_search` (`templateName`,`orgDataID`,`isActive`),
  KEY `idx_template_usage` (`usageCount` DESC,`lastUsedDate` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores reusable project plan templates for organization-wide use';

--
-- Dumping data for table `tija_project_plan_templates`
--

INSERT INTO `tija_project_plan_templates` (`templateID`, `templateName`, `templateDescription`, `templateCategory`, `isPublic`, `isSystemTemplate`, `createdByID`, `orgDataID`, `entityID`, `usageCount`, `lastUsedDate`, `isActive`, `DateAdded`, `LastUpdate`, `LastUpdateByID`) VALUES
(1, 'Standard Software Project', 'A general-purpose template for software development projects', 'software', 'Y', 'Y', 1, 1, NULL, 5, '2025-11-18 15:45:42', 'Y', '2025-11-04 13:32:24', NULL, NULL),
(2, 'Agile Sprint', 'Template for agile/scrum sprint-based projects', 'software', 'Y', 'Y', 1, 1, NULL, 12, '2025-11-18 15:26:16', 'Y', '2025-11-04 13:32:24', NULL, NULL),
(3, 'Waterfall Project', 'Traditional waterfall methodology project template', 'software', 'Y', 'Y', 1, 1, NULL, 12, '2025-11-19 09:41:47', 'Y', '2025-11-04 13:32:24', NULL, NULL),
(4, 'Research Project', 'Academic or business research project template', 'research', 'Y', 'Y', 1, 1, NULL, 0, NULL, 'Y', '2025-11-04 13:32:24', NULL, NULL),
(5, 'Construction Project', 'Building and construction project template', 'construction', 'Y', 'Y', 1, 1, NULL, 0, NULL, 'Y', '2025-11-04 13:32:24', NULL, NULL),
(6, 'Marketing Campaign', 'Marketing campaign project template', 'marketing', 'Y', 'Y', 1, 1, NULL, 1, '2025-11-14 23:14:47', 'Y', '2025-11-04 13:32:24', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_plan_template_phases`
--

DROP TABLE IF EXISTS `tija_project_plan_template_phases`;
CREATE TABLE IF NOT EXISTS `tija_project_plan_template_phases` (
  `templatePhaseID` int NOT NULL AUTO_INCREMENT,
  `templateID` int NOT NULL,
  `phaseName` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phaseDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phaseOrder` int NOT NULL DEFAULT '0',
  `phaseColor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hex color code for visual representation',
  `estimatedDuration` int DEFAULT NULL COMMENT 'Estimated duration in days',
  `durationPercent` decimal(5,2) DEFAULT NULL COMMENT 'Percentage of total project duration',
  `DateAdded` datetime NOT NULL,
  `LastUpdate` datetime DEFAULT NULL,
  PRIMARY KEY (`templatePhaseID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_order` (`templateID`,`phaseOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores individual phases for each project plan template with duration percentages';

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

DROP TABLE IF EXISTS `tija_project_roles`;
CREATE TABLE IF NOT EXISTS `tija_project_roles` (
  `roleID` int NOT NULL AUTO_INCREMENT,
  `roleName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the role',
  `roleDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Description of the role',
  `roleCategory` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Category of role (Technical, Management, etc.)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`roleID`),
  KEY `idx_role_name` (`roleName`),
  KEY `idx_role_category` (`roleCategory`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Available roles for project assignments';

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
CREATE TABLE IF NOT EXISTS `tija_project_tasks` (
  `projectTaskID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateLastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectTaskCode` varchar(30) NOT NULL,
  `projectTaskName` varchar(256) NOT NULL,
  `taskStart` date NOT NULL,
  `taskDeadline` date DEFAULT NULL,
  `projectID` int DEFAULT NULL,
  `projectPhaseID` int DEFAULT NULL,
  `billingCycleID` int DEFAULT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `billableTaskrate` varchar(20) DEFAULT NULL,
  `taskStatusID` int DEFAULT NULL,
  `projectTaskTypeID` int NOT NULL DEFAULT '1',
  `status` varchar(120) NOT NULL DEFAULT 'active',
  `progress` int DEFAULT NULL,
  `taskDescription` text,
  `hoursAllocated` decimal(10,2) DEFAULT NULL,
  `assigneeID` int NOT NULL,
  `taskWeighting` decimal(10,2) DEFAULT NULL,
  `needsDocuments` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectTaskID`),
  UNIQUE KEY `projectTaskCode` (`projectTaskCode`),
  KEY `idx_billing_cycle` (`billingCycleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_task_types`
--

DROP TABLE IF EXISTS `tija_project_task_types`;
CREATE TABLE IF NOT EXISTS `tija_project_task_types` (
  `projectTaskTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectTaskTypeName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `projectTaskTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `projectTaskTypeCode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`projectTaskTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_project_team`;
CREATE TABLE IF NOT EXISTS `tija_project_team` (
  `projectTeamMemberID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `projectID` int NOT NULL,
  `projectTeamRoleID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdateByID` int NOT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectTeamMemberID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_project_team_roles`
--

DROP TABLE IF EXISTS `tija_project_team_roles`;
CREATE TABLE IF NOT EXISTS `tija_project_team_roles` (
  `projectTeamRoleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectTeamRoleName` varchar(255) NOT NULL,
  `projectTeamRoleDescription` text NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectTeamRoleID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_project_types`;
CREATE TABLE IF NOT EXISTS `tija_project_types` (
  `projectTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectTypeName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `projectTypeDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`projectTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_proposals`;
CREATE TABLE IF NOT EXISTS `tija_proposals` (
  `proposalID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL,
  `proposalCode` varchar(120) DEFAULT NULL,
  `proposalTitle` varchar(255) NOT NULL,
  `proposalDescription` text NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `employeeID` int NOT NULL,
  `clientID` int NOT NULL,
  `salesCaseID` int NOT NULL,
  `proposalDeadline` date NOT NULL,
  `proposalStatusID` int NOT NULL,
  `proposalComments` text NOT NULL,
  `proposalValue` decimal(16,2) NOT NULL,
  `proposalOwnerID` int NOT NULL,
  `proposalFile` varchar(255) NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `completionPercentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Total completion percentage',
  `mandatoryCompletionPercentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Mandatory items completion percentage',
  `statusStage` varchar(50) DEFAULT 'draft' COMMENT 'Current stage: draft, in_review, submitted, won, lost, archived',
  `statusStageOrder` int DEFAULT '1' COMMENT 'Order of current stage',
  `lastStatusChangeDate` datetime DEFAULT NULL COMMENT 'Date of last status change',
  `lastStatusChangedBy` int DEFAULT NULL COMMENT 'User who changed status last',
  PRIMARY KEY (`proposalID`),
  UNIQUE KEY `proposalCode` (`proposalCode`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_proposals`
--

INSERT INTO `tija_proposals` (`proposalID`, `DateAdded`, `proposalCode`, `proposalTitle`, `proposalDescription`, `orgDataID`, `entityID`, `employeeID`, `clientID`, `salesCaseID`, `proposalDeadline`, `proposalStatusID`, `proposalComments`, `proposalValue`, `proposalOwnerID`, `proposalFile`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `completionPercentage`, `mandatoryCompletionPercentage`, `statusStage`, `statusStageOrder`, `lastStatusChangeDate`, `lastStatusChangedBy`) VALUES
(1, '2025-12-03 09:22:08', '3925_2025', 'Proposal for Tija PMS to I&M', 'ds fasd fasdf asdf asdf', 1, 1, 0, 1, 1, '2025-12-31', 2, '', 200000.00, 0, '', '2025-12-03 09:22:08', 4, 'N', 'N', 0.00, 0.00, 'draft', 1, NULL, NULL),
(2, '2025-12-03 09:24:11', '7261_2025', 'Proposal for Tija PMS to I&M', 'ds fasd fasdf asdf asdf', 0, 0, 0, 1, 1, '2025-12-31', 2, '', 200000.00, 0, '', '2025-12-03 09:24:11', 4, 'N', 'N', 0.00, 0.00, 'draft', 1, NULL, NULL),
(3, '2025-12-03 09:30:04', '8096_2025', 'Proposal for Tija PMS to I&M', 'f sgdfag sdf fg asdfg asdf', 0, 0, 0, 1, 3, '2025-12-31', 2, '', 250000.00, 0, '', '2025-12-03 09:30:04', 4, 'N', 'N', 0.00, 0.00, 'draft', 1, NULL, NULL),
(4, '2025-12-03 09:37:22', '3438_2025', 'Proposal for Tija PMS to Equity Bank', 'The draft proposal for the application is ready for review', 0, 0, 0, 1, 3, '2025-12-15', 1, '', 3500000.00, 0, '', '2025-12-03 09:37:22', 4, 'N', 'N', 0.00, 0.00, 'draft', 1, NULL, NULL),
(5, '2025-12-03 09:40:55', '0615_2025', 'Proposal for Tija PMS to I&M', '&lt;p&gt;dasd asd asdf asdf&lt;/p&gt;', 1, 1, 4, 1, 1, '2025-12-30', 1, '&lt;p&gt;as dfasdf asdfasdf&lt;/p&gt;', 200000.00, 0, '', '2025-12-03 09:41:27', 4, 'N', 'N', NULL, NULL, 'draft', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_activities`
--

DROP TABLE IF EXISTS `tija_proposal_activities`;
CREATE TABLE IF NOT EXISTS `tija_proposal_activities` (
  `proposalActivityID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalID` int NOT NULL,
  `activityTypeID` int NOT NULL,
  `activityDate` date NOT NULL,
  `activityTime` time NOT NULL,
  `activityDescription` text NOT NULL,
  `activityOwnerID` int NOT NULL,
  `activityStatusID` int NOT NULL,
  `activityDeadline` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityNotes` text,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `ActivityName` varchar(255) NOT NULL,
  PRIMARY KEY (`proposalActivityID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_attachments`
--

DROP TABLE IF EXISTS `tija_proposal_attachments`;
CREATE TABLE IF NOT EXISTS `tija_proposal_attachments` (
  `proposalAttachmentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalAttachmentName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalID` int NOT NULL,
  `proposalAttachmentFile` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalAttachmentType` int NOT NULL,
  `uploadByEmployeeID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalAttachmentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklists`
--

DROP TABLE IF EXISTS `tija_proposal_checklists`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklists` (
  `proposalChecklistID` int NOT NULL AUTO_INCREMENT,
  `proposalChecklistName` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalID` int NOT NULL,
  `proposalChecklistStatusID` int NOT NULL,
  `proposalChecklistDeadlineDate` date NOT NULL,
  `proposalChecklistDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `assignedEmployeeID` int NOT NULL,
  `assigneeID` int NOT NULL,
  `entityID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalChecklistID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_items`
--

DROP TABLE IF EXISTS `tija_proposal_checklist_items`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_items` (
  `proposalChecklistItemID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalChecklistItemName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistItemDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistItemCategoryID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `isMandatory` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'N' COMMENT 'Is this a mandatory checklist item',
  PRIMARY KEY (`proposalChecklistItemID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_proposal_checklist_item_assignment`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_item_assignment` (
  `proposalChecklistItemAssignmentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalID` int NOT NULL,
  `proposalChecklistID` int NOT NULL,
  `proposalChecklistItemCategoryID` int NOT NULL,
  `proposalChecklistItemID` int NOT NULL,
  `proposalChecklistItemAssignmentDueDate` date NOT NULL,
  `proposalChecklistItemAssignmentDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistAssignmentDocument` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `proposalChecklistTemplate` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `proposalChecklistItemAssignmentStatusID` int NOT NULL,
  `checklistItemAssignedEmployeeID` int NOT NULL,
  `checklistTemplate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `proposalChecklistAssignorID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `isMandatory` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'N' COMMENT 'Is this assignment mandatory',
  `completionPercentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Assignment completion percentage',
  `submittedDate` datetime DEFAULT NULL COMMENT 'When assignment was submitted',
  `approvedDate` datetime DEFAULT NULL COMMENT 'When assignment was approved',
  PRIMARY KEY (`proposalChecklistItemAssignmentID`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_proposal_checklist_item_assignment_submissions`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_item_assignment_submissions` (
  `proposalChecklistItemAssignmentSubmissionID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalChecklistItemAssignmentID` int NOT NULL,
  `proposalChecklistItemID` int DEFAULT NULL,
  `checklistItemAssignedEmployeeID` int NOT NULL,
  `proposalChecklistItemAssignmentStatusID` int NOT NULL,
  `proposalChecklistItemUploadfiles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistItemAssignmentSubmissionDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklist` varchar(56) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `proposalChecklistItemAssignmentSubmissionDate` date NOT NULL,
  `proposalChecklistItemAssignmentSubmissionStatusID` int NOT NULL,
  `createdByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalChecklistItemAssignmentSubmissionID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_item_categories`
--

DROP TABLE IF EXISTS `tija_proposal_checklist_item_categories`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_item_categories` (
  `proposalChecklistItemCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalChecklistItemCategoryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistItemCategoryDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalChecklistItemCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_proposal_checklist_item_submissions`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_item_submissions` (
  `submissionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `proposalChecklistItemAssignmentID` int NOT NULL COMMENT 'FK to tija_proposal_checklist_item_assignment',
  `submittedBy` int NOT NULL COMMENT 'FK to people - who submitted',
  `submissionDate` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'When submitted',
  `submissionStatus` enum('draft','submitted','approved','rejected','revision_requested') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'submitted',
  `submissionNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Submission notes or comments',
  `reviewedBy` int DEFAULT NULL COMMENT 'FK to people - who reviewed',
  `reviewedDate` datetime DEFAULT NULL COMMENT 'When reviewed',
  `reviewNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Review comments',
  `submissionFiles` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of submitted file paths',
  `orgDataID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`submissionID`),
  KEY `idx_assignment` (`proposalChecklistItemAssignmentID`),
  KEY `idx_submitted_by` (`submittedBy`),
  KEY `idx_status` (`submissionStatus`),
  KEY `idx_reviewed_by` (`reviewedBy`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Submissions for proposal checklist item assignments';

-- --------------------------------------------------------

--
-- Table structure for table `tija_proposal_checklist_status`
--

DROP TABLE IF EXISTS `tija_proposal_checklist_status`;
CREATE TABLE IF NOT EXISTS `tija_proposal_checklist_status` (
  `proposalChecklistStatusID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalChecklistStatusName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistStatusDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalChecklistStatusType` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalChecklistStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_proposal_statuses`;
CREATE TABLE IF NOT EXISTS `tija_proposal_statuses` (
  `proposalStatusID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalStatusName` varchar(255) NOT NULL,
  `proposalStatusDescription` text NOT NULL,
  `proposalStatusCategoryID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_proposal_status_categories`;
CREATE TABLE IF NOT EXISTS `tija_proposal_status_categories` (
  `proposalStatusCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proposalStatusCategoryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `proposalStatusCategoryDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`proposalStatusCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_proposal_status_stages`;
CREATE TABLE IF NOT EXISTS `tija_proposal_status_stages` (
  `stageID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `stageCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'draft, in_review, submitted, won, lost, archived',
  `stageName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name',
  `stageDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Stage description',
  `stageOrder` int NOT NULL COMMENT 'Order for display',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `requiresApproval` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Requires approval to move to this stage',
  `canEdit` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Can edit proposal in this stage',
  `colorCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#007bff' COMMENT 'Color for UI display',
  `iconClass` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-file-line' COMMENT 'Icon class',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stageID`),
  UNIQUE KEY `stageCode` (`stageCode`),
  KEY `idx_stage_code` (`stageCode`),
  KEY `idx_stage_order` (`stageOrder`),
  KEY `idx_active` (`isActive`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proposal status stages reference table';

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

DROP TABLE IF EXISTS `tija_proposal_tasks`;
CREATE TABLE IF NOT EXISTS `tija_proposal_tasks` (
  `proposalTaskID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `proposalID` int NOT NULL COMMENT 'FK to tija_proposals',
  `taskName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Task name',
  `taskDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Task description',
  `assignedTo` int NOT NULL COMMENT 'FK to people - assigned user',
  `assignedBy` int NOT NULL COMMENT 'FK to people - who assigned',
  `dueDate` datetime NOT NULL COMMENT 'Task due date',
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `completionPercentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Task completion percentage',
  `isMandatory` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Is this a mandatory task',
  `completedDate` datetime DEFAULT NULL COMMENT 'Date when task was completed',
  `completedBy` int DEFAULT NULL COMMENT 'FK to people - who completed',
  `notificationSent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Notification sent flag',
  `notificationSentDate` datetime DEFAULT NULL COMMENT 'When notification was sent',
  `orgDataID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`proposalTaskID`),
  KEY `idx_proposal` (`proposalID`),
  KEY `idx_assigned_to` (`assignedTo`),
  KEY `idx_status` (`status`),
  KEY `idx_mandatory` (`isMandatory`),
  KEY `idx_due_date` (`dueDate`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Proposal tasks for tracking individual tasks within proposals';

--
-- Dumping data for table `tija_proposal_tasks`
--

INSERT INTO `tija_proposal_tasks` (`proposalTaskID`, `proposalID`, `taskName`, `taskDescription`, `assignedTo`, `assignedBy`, `dueDate`, `priority`, `status`, `completionPercentage`, `isMandatory`, `completedDate`, `completedBy`, `notificationSent`, `notificationSentDate`, `orgDataID`, `entityID`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Suspended`) VALUES
(1, 5, 'Create Inception Report for project', 'dsf adsf asdf', 14, 4, '2025-12-09 12:00:00', 'medium', 'pending', 0.00, 'Y', NULL, NULL, 'Y', '2025-12-03 09:41:27', 1, 1, '2025-12-03 09:41:27', '2025-12-03 06:41:27', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_activity_instances`
--

DROP TABLE IF EXISTS `tija_recurring_activity_instances`;
CREATE TABLE IF NOT EXISTS `tija_recurring_activity_instances` (
  `recurringInstanceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityID` int NOT NULL,
  `activityInstanceDate` date NOT NULL,
  `activityinstanceStartTime` time NOT NULL,
  `activityInstanceDurationEndTime` time DEFAULT NULL,
  `instanceCount` int DEFAULT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `activityStatusID` int NOT NULL DEFAULT '1',
  `activityInstanceOwnerID` int NOT NULL,
  `completed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `dateCompleted` timestamp NULL DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`recurringInstanceID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_billing_cycles`
--

DROP TABLE IF EXISTS `tija_recurring_project_billing_cycles`;
CREATE TABLE IF NOT EXISTS `tija_recurring_project_billing_cycles` (
  `billingCycleID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `projectID` int NOT NULL,
  `cycleNumber` int NOT NULL COMMENT '1, 2, 3...',
  `cycleStartDate` date NOT NULL,
  `cycleEndDate` date NOT NULL,
  `billingDate` date NOT NULL COMMENT 'when invoice should be generated',
  `dueDate` date NOT NULL COMMENT 'payment due date',
  `status` enum('upcoming','active','billing_due','invoiced','paid','overdue','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'upcoming',
  `invoiceDraftID` int DEFAULT NULL COMMENT 'FK to tija_invoices when draft created',
  `invoiceID` int DEFAULT NULL COMMENT 'FK to tija_invoices when finalized',
  `amount` decimal(15,2) NOT NULL,
  `hoursLogged` decimal(10,2) DEFAULT '0.00',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`billingCycleID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_status` (`status`),
  KEY `idx_billing_date` (`billingDate`),
  KEY `idx_due_date` (`dueDate`),
  KEY `idx_cycle_dates` (`cycleStartDate`,`cycleEndDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Billing cycles for recurring projects';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_cycle_config`
--

DROP TABLE IF EXISTS `tija_recurring_project_plan_cycle_config`;
CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_cycle_config` (
  `configID` int NOT NULL AUTO_INCREMENT,
  `projectID` int NOT NULL COMMENT 'FK to tija_projects',
  `billingCycleID` int NOT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `templatePhaseID` int DEFAULT NULL COMMENT 'FK to tija_recurring_project_plan_templates (if phase-specific)',
  `templateTaskID` int DEFAULT NULL COMMENT 'FK to tija_recurring_project_plan_task_templates (if task-specific)',
  `isEnabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Enable/disable this phase/task for this cycle',
  `customStartDate` date DEFAULT NULL COMMENT 'Override start date for this cycle',
  `customEndDate` date DEFAULT NULL COMMENT 'Override end date for this cycle',
  `customDuration` int DEFAULT NULL COMMENT 'Override duration in days',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`configID`),
  UNIQUE KEY `idx_unique_phase_cycle` (`templatePhaseID`,`billingCycleID`),
  UNIQUE KEY `idx_unique_task_cycle` (`templateTaskID`,`billingCycleID`),
  KEY `idx_project_cycle` (`projectID`,`billingCycleID`),
  KEY `idx_template_phase` (`templatePhaseID`),
  KEY `idx_template_task` (`templateTaskID`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configuration for cycle-specific plan customization';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_instances`
--

DROP TABLE IF EXISTS `tija_recurring_project_plan_instances`;
CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_instances` (
  `planInstanceID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `billingCycleID` int UNSIGNED NOT NULL,
  `projectID` int NOT NULL,
  `phaseJSON` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'customized phases/tasks for this cycle',
  `isCustomized` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`planInstanceID`),
  KEY `idx_cycle` (`billingCycleID`),
  KEY `idx_project` (`projectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customized plan instances for recurring project billing cycles';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_task_templates`
--

DROP TABLE IF EXISTS `tija_recurring_project_plan_task_templates`;
CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_task_templates` (
  `templateTaskID` int NOT NULL AUTO_INCREMENT,
  `templatePhaseID` int NOT NULL COMMENT 'FK to tija_recurring_project_plan_templates',
  `originalTaskID` int DEFAULT NULL COMMENT 'FK to original task in tija_project_tasks',
  `taskName` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskCode` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `relativeStartDay` int DEFAULT '0' COMMENT 'Days from phase start',
  `relativeEndDay` int DEFAULT '0' COMMENT 'Days from phase start',
  `hoursAllocated` decimal(10,2) DEFAULT NULL,
  `taskWeighting` decimal(10,2) DEFAULT NULL,
  `assigneeID` int DEFAULT NULL COMMENT 'FK to people table',
  `applyToAllCycles` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Apply to all cycles or specific cycles',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateTaskID`),
  KEY `idx_template_phase` (`templatePhaseID`),
  KEY `idx_original_task` (`originalTaskID`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores task templates for recurring project phases';

-- --------------------------------------------------------

--
-- Table structure for table `tija_recurring_project_plan_templates`
--

DROP TABLE IF EXISTS `tija_recurring_project_plan_templates`;
CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_templates` (
  `templatePhaseID` int NOT NULL AUTO_INCREMENT,
  `projectID` int NOT NULL COMMENT 'FK to tija_projects',
  `originalPhaseID` int DEFAULT NULL COMMENT 'FK to original phase in tija_project_phases',
  `phaseName` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phaseDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phaseOrder` int NOT NULL DEFAULT '0' COMMENT 'Order of phase in template',
  `phaseDuration` int DEFAULT NULL COMMENT 'Duration in days',
  `phaseWorkHrs` decimal(10,2) DEFAULT NULL,
  `phaseWeighting` decimal(10,2) DEFAULT NULL,
  `billingMilestone` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `relativeStartDay` int DEFAULT '0' COMMENT 'Days from cycle start (0 = start of cycle)',
  `relativeEndDay` int DEFAULT '0' COMMENT 'Days from cycle start',
  `applyToAllCycles` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Apply to all cycles or specific cycles',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templatePhaseID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_original_phase` (`originalPhaseID`),
  KEY `idx_order` (`projectID`,`phaseOrder`),
  KEY `idx_suspended` (`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores phase templates for recurring projects';

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

DROP TABLE IF EXISTS `tija_recurring_project_team_assignments`;
CREATE TABLE IF NOT EXISTS `tija_recurring_project_team_assignments` (
  `teamAssignmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `billingCycleID` int UNSIGNED NOT NULL,
  `projectID` int NOT NULL,
  `employeeID` int NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'owner, manager, member',
  `hoursAllocated` decimal(10,2) DEFAULT NULL,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`teamAssignmentID`),
  KEY `idx_cycle` (`billingCycleID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Team assignments for recurring project billing cycles';

-- --------------------------------------------------------

--
-- Table structure for table `tija_reporting_hierarchy_cache`
--

DROP TABLE IF EXISTS `tija_reporting_hierarchy_cache`;
CREATE TABLE IF NOT EXISTS `tija_reporting_hierarchy_cache` (
  `cacheID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL,
  `ancestorID` int NOT NULL COMMENT 'All people in reporting chain',
  `pathLength` int NOT NULL COMMENT 'Levels between employee and ancestor',
  `hierarchyPath` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Full path as JSON',
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `lastCalculated` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cacheID`),
  UNIQUE KEY `idx_employee_ancestor` (`employeeID`,`ancestorID`),
  KEY `idx_ancestor` (`ancestorID`),
  KEY `idx_org` (`orgDataID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cached reporting hierarchy for quick lookups';

-- --------------------------------------------------------

--
-- Table structure for table `tija_reporting_matrix`
--

DROP TABLE IF EXISTS `tija_reporting_matrix`;
CREATE TABLE IF NOT EXISTS `tija_reporting_matrix` (
  `matrixID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int NOT NULL,
  `functionalSupervisorID` int DEFAULT NULL COMMENT 'Functional line manager',
  `projectSupervisorID` int DEFAULT NULL COMMENT 'Project/program manager',
  `administrativeSupervisorID` int DEFAULT NULL COMMENT 'Administrative manager',
  `primarySupervisorID` int NOT NULL COMMENT 'Primary reporting line',
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`matrixID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_functional` (`functionalSupervisorID`),
  KEY `idx_project` (`projectSupervisorID`),
  KEY `idx_current` (`isCurrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matrix reporting structure support';

-- --------------------------------------------------------

--
-- Table structure for table `tija_reporting_relationships`
--

DROP TABLE IF EXISTS `tija_reporting_relationships`;
CREATE TABLE IF NOT EXISTS `tija_reporting_relationships` (
  `relationshipID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int NOT NULL COMMENT 'Employee who reports',
  `supervisorID` int NOT NULL COMMENT 'Employee being reported to',
  `roleID` int DEFAULT NULL COMMENT 'Role context for this relationship',
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `relationshipType` enum('Direct','Dotted','Matrix','Functional','Administrative') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Direct',
  `relationshipStrength` int DEFAULT '100' COMMENT 'Percentage (100=primary, <100=secondary)',
  `effectiveDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `isCurrent` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `reportingFrequency` enum('Daily','Weekly','Biweekly','Monthly','Quarterly','Adhoc') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Weekly',
  `canDelegate` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `canSubstitute` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `createdBy` int DEFAULT NULL,
  `approvedBy` int DEFAULT NULL,
  `approvedDate` datetime DEFAULT NULL,
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`relationshipID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_supervisor` (`supervisorID`),
  KEY `idx_org` (`orgDataID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_current` (`isCurrent`),
  KEY `idx_dates` (`effectiveDate`,`endDate`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Employee reporting relationships';

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

DROP TABLE IF EXISTS `tija_roles`;
CREATE TABLE IF NOT EXISTS `tija_roles` (
  `roleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `roleName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roleCode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roleDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `orgDataID` int NOT NULL,
  `entityID` int DEFAULT NULL,
  `departmentID` int DEFAULT NULL,
  `unitID` int DEFAULT NULL,
  `parentRoleID` int DEFAULT NULL COMMENT 'Reports to this role',
  `jobTitleID` int DEFAULT NULL,
  `roleLevel` int DEFAULT '0' COMMENT 'Hierarchy level (0=top)',
  `roleLevelID` int NOT NULL,
  `roleType` enum('Executive','Management','Supervisory','Operational','Support') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Operational',
  `roleTypeID` int NOT NULL,
  `requiresApproval` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `canApprove` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `approvalLimit` decimal(15,2) DEFAULT NULL COMMENT 'Financial approval limit',
  `reportsCount` int DEFAULT '0' COMMENT 'Number of direct reports',
  `iconClass` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colorCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `LastUpdate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`roleID`),
  KEY `idx_org` (`orgDataID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_parent` (`parentRoleID`),
  KEY `idx_active` (`isActive`),
  KEY `idx_level` (`roleLevel`),
  KEY `idx_roleTypeID` (`roleTypeID`),
  KEY `idx_roleLevelID` (`roleLevelID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organizational roles and positions hierarchy';

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

DROP TABLE IF EXISTS `tija_role_levels`;
CREATE TABLE IF NOT EXISTS `tija_role_levels` (
  `roleLevelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `levelNumber` int NOT NULL COMMENT 'Numeric level (0-8, lower = higher authority)',
  `levelName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name (e.g., Board/External, CEO/Executive)',
  `levelCode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Short code (e.g., BOARD, CEO, CSUITE)',
  `levelDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Description of the role level',
  `displayOrder` int DEFAULT '0' COMMENT 'Order for display in dropdowns',
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Is this a default/system role level',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Is this role level active',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`roleLevelID`),
  UNIQUE KEY `unique_levelNumber` (`levelNumber`),
  UNIQUE KEY `unique_levelCode` (`levelCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_Suspended` (`Suspended`),
  KEY `idx_displayOrder` (`displayOrder`),
  KEY `idx_levelNumber` (`levelNumber`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role levels for organizational hierarchy';

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

DROP TABLE IF EXISTS `tija_role_types`;
CREATE TABLE IF NOT EXISTS `tija_role_types` (
  `roleTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `roleTypeTitle` varchar(255) NOT NULL,
  `roleTypeDescription` mediumtext NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`roleTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_role_types`
--

INSERT INTO `tija_role_types` (`roleTypeID`, `DateAdded`, `roleTypeTitle`, `roleTypeDescription`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2024-06-21 13:39:08', 'Administrator', 'Administrator access allows individuals access to the backend of the application', '2024-06-21 13:39:08', 1, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_salary_components`
--

DROP TABLE IF EXISTS `tija_salary_components`;
CREATE TABLE IF NOT EXISTS `tija_salary_components` (
  `salaryComponentID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int UNSIGNED NOT NULL DEFAULT '1',
  `entityID` int UNSIGNED NOT NULL DEFAULT '1',
  `componentCode` varchar(30) DEFAULT NULL,
  `salaryComponentTitle` varchar(255) NOT NULL,
  `salaryComponentDescription` mediumtext NOT NULL,
  `salaryComponentType` enum('earning','deduction','benefit') NOT NULL,
  `salaryComponentValueType` enum('fixed','percentage','formula') NOT NULL DEFAULT 'fixed',
  `defaultValue` decimal(15,2) DEFAULT '0.00',
  `calculationFormula` text,
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
  `eligibilityCriteria` text,
  `notes` text,
  `sortOrder` int DEFAULT '0',
  `salaryComponentCategoryID` int NOT NULL,
  `LastUpdatedByID` int NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`salaryComponentID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_salary_component_category`;
CREATE TABLE IF NOT EXISTS `tija_salary_component_category` (
  `salaryComponentCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int UNSIGNED NOT NULL DEFAULT '1',
  `entityID` int UNSIGNED NOT NULL DEFAULT '1',
  `categoryCode` varchar(20) DEFAULT NULL,
  `salaryComponentCategoryTitle` varchar(255) NOT NULL,
  `salaryComponentCategoryDescription` mediumtext NOT NULL,
  `categoryType` enum('earning','deduction','statutory','benefit','reimbursement') NOT NULL DEFAULT 'earning',
  `isSystemCategory` enum('Y','N') DEFAULT 'N',
  `sortOrder` int DEFAULT '0',
  `LastUpdatedByID` int NOT NULL,
  `LastUpdated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`salaryComponentCategoryID`),
  UNIQUE KEY `unique_category_code_entity` (`categoryCode`,`entityID`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_salary_component_history`;
CREATE TABLE IF NOT EXISTS `tija_salary_component_history` (
  `historyID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `DateAdded` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `salaryComponentID` int UNSIGNED NOT NULL,
  `changeType` enum('created','updated','deleted','suspended','reactivated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fieldChanged` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Which field was changed',
  `oldValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Previous value',
  `newValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'New value',
  `changedBy` int UNSIGNED NOT NULL,
  `changeReason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `changeDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`historyID`),
  KEY `idx_component` (`salaryComponentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_activities`
--

DROP TABLE IF EXISTS `tija_sales_activities`;
CREATE TABLE IF NOT EXISTS `tija_sales_activities` (
  `salesActivityID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activityTypeID` int NOT NULL,
  `salesActivityDate` date NOT NULL,
  `activityTime` time NOT NULL,
  `activityDescription` text NOT NULL,
  `salesCaseID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `clientID` int NOT NULL,
  `activityName` varchar(255) NOT NULL,
  `activityOwnerID` int NOT NULL,
  `salesPersonID` int NOT NULL,
  `activityCategory` enum('one_off','reccuring','duration') NOT NULL,
  `activityStatus` enum('open','inprogress','stalled','completed') NOT NULL DEFAULT 'open',
  `activityDeadline` date DEFAULT NULL,
  `activityStartDate` date DEFAULT NULL,
  `activityCloseDate` date DEFAULT NULL,
  `activityCloseStatus` enum('open','pending','stalled','closed') NOT NULL DEFAULT 'open',
  `ActivityNotes` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`salesActivityID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_sales_activities`
--

INSERT INTO `tija_sales_activities` (`salesActivityID`, `DateAdded`, `activityTypeID`, `salesActivityDate`, `activityTime`, `activityDescription`, `salesCaseID`, `orgDataID`, `entityID`, `clientID`, `activityName`, `activityOwnerID`, `salesPersonID`, `activityCategory`, `activityStatus`, `activityDeadline`, `activityStartDate`, `activityCloseDate`, `activityCloseStatus`, `ActivityNotes`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-01 20:21:51', 3, '2025-12-09', '00:00:00', 'ghhkc lv hljhv', 5, 1, 1, 1, 'Carchup call', 4, 4, 'one_off', 'open', NULL, NULL, NULL, 'open', '', '2025-12-01 20:21:51', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_cases`
--

DROP TABLE IF EXISTS `tija_sales_cases`;
CREATE TABLE IF NOT EXISTS `tija_sales_cases` (
  `salesCaseID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `salesCaseName` varchar(256) NOT NULL,
  `clientID` int NOT NULL,
  `salesCaseContactID` int NOT NULL,
  `orgDataID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `businessUnitID` int NOT NULL,
  `salesPersonID` int NOT NULL,
  `saleStatusLevelID` int NOT NULL,
  `saleStage` enum('business_development','opportunities','order','loss') NOT NULL DEFAULT 'business_development',
  `salesCaseEstimate` double(16,2) NOT NULL,
  `probability` int NOT NULL DEFAULT '0',
  `expectedCloseDate` date NOT NULL,
  `leadSourceID` int NOT NULL,
  `dateClosed` date DEFAULT NULL,
  `closeStatus` enum('open','won','lost') DEFAULT 'open',
  `projectID` int DEFAULT NULL,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closeDate` date DEFAULT NULL,
  `LastUpdatedByID` int NOT NULL,
  `salesProgressID` int DEFAULT NULL,
  `salesCaseNotes` text COMMENT 'General notes and description about the sales opportunity',
  PRIMARY KEY (`salesCaseID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_sales_cases`
--

INSERT INTO `tija_sales_cases` (`salesCaseID`, `DateAdded`, `salesCaseName`, `clientID`, `salesCaseContactID`, `orgDataID`, `entityID`, `businessUnitID`, `salesPersonID`, `saleStatusLevelID`, `saleStage`, `salesCaseEstimate`, `probability`, `expectedCloseDate`, `leadSourceID`, `dateClosed`, `closeStatus`, `projectID`, `Suspended`, `Lapsed`, `LastUpdate`, `closeDate`, `LastUpdatedByID`, `salesProgressID`, `salesCaseNotes`) VALUES
(1, '2025-12-01 17:27:25', 'Annual Audit', 1, 1, 1, 1, 2, 4, 4, 'opportunities', 1000000.00, 25, '2025-12-18', 1, NULL, 'open', NULL, 'N', 'N', '2025-12-02 15:16:08', NULL, 4, NULL, 'e wer qer qer tert re ter er ter tqrt qr re qre tqrtqretqwre rt ret qrt qawretaqrew'),
(2, '2025-12-01 17:54:24', 'Employee on Record', 1, 0, 1, 1, 1, 4, 5, '', 1500000.00, 50, '2025-12-31', 2, NULL, 'open', NULL, 'N', 'N', '2025-12-01 19:59:04', NULL, 4, NULL, NULL),
(3, '2025-12-01 15:26:04', 'Tija PMS Automation', 1, 1, 1, 1, 1, 4, 4, 'opportunities', 1000000.00, 25, '2025-12-24', 1, NULL, 'open', NULL, 'N', 'N', '2025-12-01 17:09:25', NULL, 4, NULL, NULL),
(4, '2025-12-01 15:38:13', 'Employee on Record', 1, 1, 1, 1, 1, 4, 5, 'opportunities', 1000000.00, 50, '2025-12-31', 1, NULL, 'open', NULL, 'N', 'N', '2025-12-01 17:13:14', NULL, 4, NULL, NULL),
(5, '2025-12-01 16:57:27', 'Custom Application', 1, 1, 1, 1, 1, 4, 4, 'opportunities', 1000000.00, 25, '2026-01-08', 1, NULL, 'open', NULL, 'N', 'N', '2025-12-01 17:20:56', NULL, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_documents`
--

DROP TABLE IF EXISTS `tija_sales_documents`;
CREATE TABLE IF NOT EXISTS `tija_sales_documents` (
  `documentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `salesCaseID` int NOT NULL COMMENT 'FK to tija_sales_cases',
  `proposalID` int DEFAULT NULL COMMENT 'Optional FK to tija_proposals if document is proposal-related',
  `documentName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name for the document',
  `fileName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Stored filename',
  `fileOriginalName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Original filename from upload',
  `fileURL` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path to stored file',
  `fileType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File extension: pdf, docx, xlsx, etc.',
  `fileSize` bigint DEFAULT NULL COMMENT 'File size in bytes',
  `fileMimeType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'MIME type',
  `documentCategory` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Category: sales_agreement, tor, proposal, engagement_letter, confidentiality_agreement, expense_document, other',
  `documentType` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sub-type or specific document type',
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '1.0' COMMENT 'Document version',
  `uploadedBy` int NOT NULL COMMENT 'FK to tija_users',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Document description or notes',
  `expenseID` int DEFAULT NULL COMMENT 'Optional FK to expense if this is an expense document',
  `isConfidential` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Confidential document flag',
  `isPublic` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Accessible to client',
  `requiresApproval` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Requires management/finance approval',
  `approvalStatus` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Approval status if requiresApproval=Y',
  `approvedBy` int DEFAULT NULL COMMENT 'FK to tija_users - who approved',
  `approvedDate` datetime DEFAULT NULL COMMENT 'Approval date',
  `downloadCount` int DEFAULT '0' COMMENT 'Number of times downloaded',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL COMMENT 'FK to tija_users',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Soft delete flag',
  `salesStage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lead, Opportunity, Proposal, Closed-Won, Closed-Lost',
  `saleStatusLevelID` int DEFAULT NULL COMMENT 'FK to tija_sales_status_levels',
  `documentStage` enum('draft','final','revision','approved','signed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sharedWithClient` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `sharedDate` datetime DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci,
  `expiryDate` date DEFAULT NULL,
  `linkedActivityID` int DEFAULT NULL,
  `viewCount` int NOT NULL DEFAULT '0',
  `lastAccessedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`documentID`),
  KEY `idx_sales_case` (`salesCaseID`),
  KEY `idx_proposal` (`proposalID`),
  KEY `idx_category` (`documentCategory`),
  KEY `idx_uploader` (`uploadedBy`),
  KEY `idx_expense` (`expenseID`),
  KEY `idx_approval` (`requiresApproval`,`approvalStatus`),
  KEY `idx_confidential` (`isConfidential`),
  KEY `idx_suspended` (`Suspended`),
  KEY `idx_sales_stage` (`salesStage`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sales documents and files management for sales cases';

--
-- Dumping data for table `tija_sales_documents`
--

INSERT INTO `tija_sales_documents` (`documentID`, `salesCaseID`, `proposalID`, `documentName`, `fileName`, `fileOriginalName`, `fileURL`, `fileType`, `fileSize`, `fileMimeType`, `documentCategory`, `documentType`, `version`, `uploadedBy`, `description`, `expenseID`, `isConfidential`, `isPublic`, `requiresApproval`, `approvalStatus`, `approvedBy`, `approvedDate`, `downloadCount`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Suspended`, `salesStage`, `saleStatusLevelID`, `documentStage`, `sharedWithClient`, `sharedDate`, `tags`, `expiryDate`, `linkedActivityID`, `viewCount`, `lastAccessedDate`) VALUES
(1, 2, NULL, 'con', '1764601080_CITAM_Schools_Woodley_JSS_Fee_Structure_2026.pdf', 'CITAM Schools Woodley JSS Fee Structure 2026.pdf', 'sales_documents/1764601080_CITAM_Schools_Woodley_JSS_Fee_Structure_2026.pdf', 'pdf', 330137, 'application/pdf', 'confidentiality_agreement', '', '1.0', 4, 'hfghfvhgkjghkg hjkhjkh', NULL, 'Y', 'N', 'N', NULL, NULL, NULL, 0, '2025-12-01 17:58:00', '2025-12-02 10:21:24', NULL, 'N', '', 5, NULL, 'N', NULL, NULL, NULL, NULL, 0, NULL),
(2, 2, NULL, 'con', '1764601855_CITAM_Schools_Woodley_JSS_Fee_Structure_2026.pdf', 'CITAM Schools Woodley JSS Fee Structure 2026.pdf', 'sales_documents/1764601855_CITAM_Schools_Woodley_JSS_Fee_Structure_2026.pdf', 'pdf', 330137, 'application/pdf', 'confidentiality_agreement', '', '1.0', 4, 'hfghfvhgkjghkg hjkhjkh', NULL, 'Y', 'N', 'N', NULL, NULL, NULL, 0, '2025-12-01 18:10:55', '2025-12-02 10:21:24', NULL, 'N', '', 5, NULL, 'N', NULL, NULL, NULL, NULL, 0, NULL),
(3, 3, NULL, 'Proposal Document', '1764672202_Email_Configuration_Guide.pdf', 'Email Configuration Guide.pdf', 'sales_documents/1764672202_Email_Configuration_Guide.pdf', 'pdf', 1421411, 'application/pdf', 'proposal', 'Draft proposal', '1.0', 4, 'dsf asd asdf asd', NULL, 'N', 'Y', 'Y', 'pending', NULL, NULL, 0, '2025-12-02 13:43:22', '2025-12-02 10:43:22', NULL, 'N', 'opportunities', 4, 'revision', 'Y', '2025-12-02 00:00:00', 'Digital Innovation, Customer Engagement, Technology Adoption', '2025-12-31', 1, 0, NULL),
(4, 3, NULL, 'Proposal Document', '1764672608_Email_Configuration_Guide.pdf', 'Email Configuration Guide.pdf', 'sales_documents/1764672608_Email_Configuration_Guide.pdf', 'pdf', 1421411, 'application/pdf', 'proposal', 'Draft proposal', '1.0', 4, 'dsf asd asdf asd', NULL, 'N', 'Y', 'Y', 'pending', NULL, NULL, 0, '2025-12-02 13:50:08', '2025-12-02 10:50:08', NULL, 'N', 'opportunities', 4, 'revision', 'Y', '2025-12-02 00:00:00', 'Digital Innovation, Customer Engagement, Technology Adoption', '2025-12-31', 1, 0, NULL),
(5, 3, NULL, 'Non Discloser Agreement', '1764673112_Email_Configuration_Guide.pdf', 'Email Configuration Guide.pdf', 'sales_documents/1764673112_Email_Configuration_Guide.pdf', 'pdf', 1421411, 'application/pdf', 'confidentiality_agreement', 'Final Agreement', '1.0', 4, 'Fional NDA so we can share information with the client', NULL, 'Y', 'N', 'N', NULL, NULL, NULL, 0, '2025-12-02 13:58:32', '2025-12-02 10:58:32', NULL, 'N', 'opportunities', 4, 'final', 'Y', NULL, 'NDA,', '2025-12-31', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_document_access_log`
--

DROP TABLE IF EXISTS `tija_sales_document_access_log`;
CREATE TABLE IF NOT EXISTS `tija_sales_document_access_log` (
  `accessID` int NOT NULL AUTO_INCREMENT,
  `documentID` int NOT NULL,
  `accessedBy` int NOT NULL,
  `accessType` enum('view','download','share','edit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'view',
  `accessDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ipAddress` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`accessID`),
  KEY `idx_document_access` (`documentID`),
  KEY `idx_accessed_by` (`accessedBy`),
  KEY `idx_access_date` (`accessDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_document_shares`
--

DROP TABLE IF EXISTS `tija_sales_document_shares`;
CREATE TABLE IF NOT EXISTS `tija_sales_document_shares` (
  `shareID` int NOT NULL AUTO_INCREMENT,
  `documentID` int NOT NULL,
  `sharedWith` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sharedBy` int NOT NULL,
  `sharedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shareMethod` enum('email','link','portal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `accessLink` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessExpiry` datetime DEFAULT NULL,
  `accessCount` int NOT NULL DEFAULT '0',
  `lastAccessedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`shareID`),
  KEY `idx_document_shares` (`documentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_document_versions`
--

DROP TABLE IF EXISTS `tija_sales_document_versions`;
CREATE TABLE IF NOT EXISTS `tija_sales_document_versions` (
  `versionID` int NOT NULL AUTO_INCREMENT,
  `documentID` int NOT NULL,
  `versionNumber` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileURL` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileSize` bigint DEFAULT NULL,
  `versionNotes` text COLLATE utf8mb4_unicode_ci,
  `uploadedBy` int NOT NULL,
  `uploadedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isCurrent` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`versionID`),
  KEY `idx_document_versions` (`documentID`),
  KEY `idx_version_current` (`isCurrent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_progress`
--

DROP TABLE IF EXISTS `tija_sales_progress`;
CREATE TABLE IF NOT EXISTS `tija_sales_progress` (
  `salesProgressID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `salesCaseID` int NOT NULL,
  `clientID` int NOT NULL,
  `businessUnitID` int DEFAULT NULL,
  `saleStatusLevelID` int NOT NULL,
  `progressPercentage` decimal(3,2) NOT NULL,
  `progressNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `salesPersonID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`salesProgressID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_sales_progress`
--

INSERT INTO `tija_sales_progress` (`salesProgressID`, `DateAdded`, `salesCaseID`, `clientID`, `businessUnitID`, `saleStatusLevelID`, `progressPercentage`, `progressNotes`, `orgDataID`, `entityID`, `salesPersonID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-12-01 17:20:56', 5, 1, 1, 4, 0.00, 'the client accepted proposal and is in the evaluation stage', 1, 1, 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_prospects`
--

DROP TABLE IF EXISTS `tija_sales_prospects`;
CREATE TABLE IF NOT EXISTS `tija_sales_prospects` (
  `salesProspectID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `salesProspectName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `clientID` int DEFAULT NULL,
  `isClient` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `prospectEmail` varchar(254) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `prospectCaseName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `estimatedValue` int DEFAULT NULL,
  `probability` int DEFAULT NULL,
  `salesProspectStatus` enum('open','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'open',
  `LeadSourceID` int NOT NULL,
  `businessUnitID` int NOT NULL,
  `productCategoryID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `ownerID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  PRIMARY KEY (`salesProspectID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_sales_status_levels`
--

DROP TABLE IF EXISTS `tija_sales_status_levels`;
CREATE TABLE IF NOT EXISTS `tija_sales_status_levels` (
  `saleStatusLevelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statusLevel` varchar(255) NOT NULL,
  `statusOrder` int NOT NULL,
  `StatusLevelDescription` text,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `levelPercentage` decimal(4,2) NOT NULL,
  `previousLevelID` int NOT NULL,
  `closeLevel` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`saleStatusLevelID`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

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
(12, '2025-04-11 14:38:30', 'Close (Order/Lost)', 4, 'The close stage is where the final decision is made. The deal is either won (order) or lost. i.e. Customer makes a final decision.\r\nIf won, the order is processed, and the sale is completed.\r\nIf lost, reasons for the loss are analyzed for future improvement.', 1, 2, 99.99, 11, 'Y', '2025-04-11 14:38:30', 2, 'N', 'N'),
(13, '2025-12-01 16:32:34', 'Interest', 1, 'Expression of interest from Prospect/Client. \r\nProduct info Sent and/or meeting has been held. \r\nNo clear next step has been agreed.', 1, 2, 10.00, 0, 'N', '2025-12-01 16:32:34', 4, 'N', 'N'),
(14, '2025-12-01 16:33:02', 'Qualification', 2, 'Had a meeting. Identified need. The solution is presented. The Next Step/activity is agreed upon and booked.', 1, 2, 25.00, 13, 'N', '2025-12-01 16:33:02', 4, 'N', 'N'),
(15, '2025-12-01 16:33:27', 'Evaluation', 3, 'Dialogue about solution/evaluation internally. The price is proposed and well-calibrated. The next step/activity is agreed upon and booked.', 1, 2, 50.00, 14, 'N', '2025-12-01 16:33:27', 4, 'N', 'N'),
(16, '2025-12-01 16:33:49', 'Negotiation', 4, 'Solution is accepted and an offer/contract sent. Pricing and terms are discussed. The time plan for the project is being discussed', 1, 2, 75.00, 15, 'N', '2025-12-01 16:33:49', 4, 'N', 'N'),
(17, '2025-12-01 16:34:14', 'Verbal Acceptance', 5, 'Verbal acceptance/approval via email on quote/ agreement. The time plan for the project start is confirmed. The final draft sent for signing.', 1, 2, 90.00, 16, 'N', '2025-12-01 16:34:14', 4, 'N', 'N'),
(18, '2025-12-01 16:34:46', 'Close', 6, 'Contract signed (Product/implementation/service)\r\nApproval Via email for smaller deals i.e. Training, minor consultancy, more users etc', 1, 2, 99.99, 17, 'N', '2025-12-01 16:34:46', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_sops`
--

DROP TABLE IF EXISTS `tija_sops`;
CREATE TABLE IF NOT EXISTS `tija_sops` (
  `sopID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sopTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sopDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `sopVersion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0' COMMENT 'Version number',
  `sopDocumentURL` text COLLATE utf8mb4_unicode_ci COMMENT 'Link to document/knowledge base',
  `sopContent` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Rich text content (HTML/Markdown)',
  `effectiveDate` date DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `approvalStatus` enum('draft','pending_approval','approved','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `approvedByID` int DEFAULT NULL COMMENT 'FK to people',
  `approvedDate` datetime DEFAULT NULL,
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`sopID`),
  UNIQUE KEY `unique_sopCode_version` (`sopCode`,`sopVersion`),
  KEY `idx_process` (`processID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_approvalStatus` (`approvalStatus`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SOP master records';

--
-- Dumping data for table `tija_sops`
--

INSERT INTO `tija_sops` (`sopID`, `sopCode`, `sopTitle`, `sopDescription`, `processID`, `functionalArea`, `functionalAreaID`, `sopVersion`, `sopDocumentURL`, `sopContent`, `effectiveDate`, `expiryDate`, `approvalStatus`, `approvedByID`, `approvedDate`, `createdByID`, `functionalAreaOwnerID`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `isActive`, `Lapsed`, `Suspended`) VALUES
(1, 'SOP-PAYROLL-001', 'Monthly Payroll Processing Procedure', 'Standard operating procedure for processing monthly payroll including time collection, calculation, approval, and payment distribution.', 1, 'HR', 2, '1.0', NULL, '<h2>Monthly Payroll Processing Procedure</h2>\r\n<h3>1. Overview</h3>\r\n<p>This procedure outlines the steps for processing monthly payroll accurately and on time.</p>\r\n<h3>2. Procedure</h3>\r\n<ol>\r\n<li>Collect time and attendance records from all employees</li>\r\n<li>Verify and validate time records for accuracy</li>\r\n<li>Calculate gross pay based on hours worked and rates</li>\r\n<li>Calculate all deductions (taxes, benefits, etc.)</li>\r\n<li>Calculate net pay</li>\r\n<li>Obtain approval from HR Manager</li>\r\n<li>Process payments via direct deposit or checks</li>\r\n<li>Generate payroll reports</li>\r\n<li>Remit taxes and deductions to appropriate agencies</li>\r\n</ol>\r\n<h3>3. Responsibilities</h3>\r\n<ul>\r\n<li>Payroll Administrator: Collect time, calculate payroll</li>\r\n<li>HR Manager: Review and approve payroll</li>\r\n<li>Finance: Process payments and remit taxes</li>\r\n</ul>', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'Y', 'N', 'N'),
(2, 'SOP-AP-001', 'Accounts Payable Processing Procedure', 'Standard operating procedure for processing vendor invoices and payments including invoice verification, matching, approval, and payment.', 6, 'Finance', 1, '1.0', NULL, '<h2>Accounts Payable Processing Procedure</h2>\r\n<h3>1. Overview</h3>\r\n<p>This procedure outlines the steps for processing vendor invoices and payments accurately and efficiently.</p>\r\n<h3>2. Procedure</h3>\r\n<ol>\r\n<li>Receive vendor invoice</li>\r\n<li>Verify invoice details (amount, date, vendor)</li>\r\n<li>Match invoice to purchase order and receiving documents</li>\r\n<li>Obtain required approval based on amount</li>\r\n<li>Process payment via check, ACH, or wire transfer</li>\r\n<li>Record transaction in general ledger</li>\r\n<li>File supporting documents</li>\r\n</ol>\r\n<h3>3. Approval Limits</h3>\r\n<ul>\r\n<li>Under $1,000: Department Manager</li>\r\n<li>$1,000 - $10,000: Finance Manager</li>\r\n<li>Over $10,000: CFO</li>\r\n</ul>', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'Y', 'N', 'N'),
(3, 'SOP-BANK-RECON-001', 'Monthly Bank Reconciliation Procedure', 'Standard operating procedure for reconciling bank accounts monthly to ensure accuracy and identify discrepancies.', 10, 'Finance', 1, '1.0', NULL, '<h2>Monthly Bank Reconciliation Procedure</h2>\r\n<h3>1. Overview</h3>\r\n<p>This procedure outlines the steps for reconciling bank accounts monthly.</p>\r\n<h3>2. Procedure</h3>\r\n<ol>\r\n<li>Retrieve bank statements</li>\r\n<li>Compare bank records to general ledger cash account</li>\r\n<li>Identify outstanding checks and deposits</li>\r\n<li>Identify and resolve discrepancies</li>\r\n<li>Document reconciliation results</li>\r\n<li>Obtain approval from Finance Manager</li>\r\n<li>File reconciliation documents</li>\r\n</ol>', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, '2025-11-29 15:09:39', '2025-11-29 12:10:07', NULL, 'Y', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_sop_attachments`
--

DROP TABLE IF EXISTS `tija_sop_attachments`;
CREATE TABLE IF NOT EXISTS `tija_sop_attachments` (
  `attachmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopID` int UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
  `fileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileURL` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fileSize` bigint DEFAULT NULL COMMENT 'File size in bytes',
  `uploadedByID` int DEFAULT NULL COMMENT 'FK to people',
  `uploadedDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attachmentID`),
  KEY `idx_sop` (`sopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SOP file attachments';

-- --------------------------------------------------------

--
-- Table structure for table `tija_sop_links`
--

DROP TABLE IF EXISTS `tija_sop_links`;
CREATE TABLE IF NOT EXISTS `tija_sop_links` (
  `linkID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopID` int UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
  `linkType` enum('template','task','workflow_step','process') COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkedEntityID` int UNSIGNED NOT NULL COMMENT 'ID of linked entity',
  `isRequired` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Must review before completion',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`linkID`),
  KEY `idx_sop` (`sopID`),
  KEY `idx_linkType_entity` (`linkType`,`linkedEntityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Links SOPs to tasks/templates/workflows';

-- --------------------------------------------------------

--
-- Table structure for table `tija_sop_sections`
--

DROP TABLE IF EXISTS `tija_sop_sections`;
CREATE TABLE IF NOT EXISTS `tija_sop_sections` (
  `sectionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopID` int UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
  `sectionOrder` int NOT NULL,
  `sectionTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sectionContent` text COLLATE utf8mb4_unicode_ci,
  `sectionType` enum('overview','procedure','checklist','troubleshooting','references') COLLATE utf8mb4_unicode_ci DEFAULT 'procedure',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sectionID`),
  KEY `idx_sop` (`sopID`),
  KEY `idx_sectionOrder` (`sectionOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SOP structured sections';

--
-- Dumping data for table `tija_sop_sections`
--

INSERT INTO `tija_sop_sections` (`sectionID`, `sopID`, `sectionOrder`, `sectionTitle`, `sectionContent`, `sectionType`, `DateAdded`, `LastUpdate`) VALUES
(1, 1, 1, 'Overview', 'This procedure outlines the steps for processing monthly payroll accurately and on time.', 'overview', '2025-11-29 15:09:39', '2025-11-29 12:09:39'),
(2, 1, 2, 'Procedure Steps', '1. Collect time and attendance records\n2. Verify and validate time records\n3. Calculate gross pay\n4. Calculate deductions\n5. Calculate net pay\n6. Obtain approval\n7. Process payments\n8. Generate reports\n9. Remit taxes', 'procedure', '2025-11-29 15:09:39', '2025-11-29 12:09:39'),
(3, 1, 3, 'Checklist', '□ Time records collected\n□ Time records verified\n□ Payroll calculated\n□ Approval obtained\n□ Payments processed\n□ Reports generated\n□ Taxes remitted', 'checklist', '2025-11-29 15:09:39', '2025-11-29 12:09:39'),
(4, 2, 1, 'Overview', 'This procedure outlines the steps for processing vendor invoices and payments.', 'overview', '2025-11-29 15:09:39', '2025-11-29 12:09:39'),
(5, 2, 2, 'Procedure Steps', '1. Receive and verify invoice\n2. Match to purchase order\n3. Obtain approval\n4. Process payment\n5. Record in general ledger', 'procedure', '2025-11-29 15:09:39', '2025-11-29 12:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `tija_statement_of_investment_allowance_accounts`
--

DROP TABLE IF EXISTS `tija_statement_of_investment_allowance_accounts`;
CREATE TABLE IF NOT EXISTS `tija_statement_of_investment_allowance_accounts` (
  `investmentAllowanceAccountID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `accountName` varchar(255) NOT NULL,
  `parentAccountID` int NOT NULL,
  `accountCategory` varchar(255) NOT NULL,
  `financialStatementTypeID` int NOT NULL,
  `statementTypeNode` varchar(256) NOT NULL,
  `accountCode` varchar(255) NOT NULL,
  `accountNode` varchar(255) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`investmentAllowanceAccountID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_statement_of_investment_allowance_data`
--

DROP TABLE IF EXISTS `tija_statement_of_investment_allowance_data`;
CREATE TABLE IF NOT EXISTS `tija_statement_of_investment_allowance_data` (
  `InvestmentAllowanceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `financialStatementID` int NOT NULL,
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
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `allowInTotal` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`InvestmentAllowanceID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='sbsl_statement_of_investment_allowance_data';

-- --------------------------------------------------------

--
-- Table structure for table `tija_subtasks`
--

DROP TABLE IF EXISTS `tija_subtasks`;
CREATE TABLE IF NOT EXISTS `tija_subtasks` (
  `subtaskID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectTaskID` int NOT NULL,
  `subTaskName` varchar(256) NOT NULL,
  `subTaskStatus` enum('active','pending','completed','in progress','overdue') NOT NULL DEFAULT 'active',
  `subTaskStatusID` int DEFAULT NULL,
  `assignee` varchar(256) DEFAULT NULL,
  `subtaskDueDate` date DEFAULT NULL,
  `dependencies` varchar(256) DEFAULT NULL,
  `subTaskDescription` text NOT NULL,
  `subTaskAllocatedWorkHours` decimal(10,2) DEFAULT NULL,
  `needsDocuments` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`subtaskID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tasks_time_logs`
--

DROP TABLE IF EXISTS `tija_tasks_time_logs`;
CREATE TABLE IF NOT EXISTS `tija_tasks_time_logs` (
  `timelogID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `taskDate` date NOT NULL,
  `employeeID` int NOT NULL,
  `clientID` int NOT NULL,
  `projectID` int DEFAULT NULL,
  `projectPhaseID` int DEFAULT NULL,
  `projectTaskID` int DEFAULT NULL,
  `subtaskID` int DEFAULT NULL,
  `workTypeID` int NOT NULL,
  `taskNarrative` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `startTime` time NOT NULL,
  `endTime` time NOT NULL,
  `taskDuration` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `taskDurationSeconds` int NOT NULL,
  `billable` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Y',
  `billableRateValue` decimal(10,2) NOT NULL,
  `workHours` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `dailyComplete` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `taskStatusID` int DEFAULT NULL,
  `taskType` enum('adhoc','project','sales','activity','proposal','operational') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'project',
  `taskActivityID` int DEFAULT NULL,
  `workSegmentID` int DEFAULT NULL,
  `recurringInstanceID` int DEFAULT NULL,
  `billingCycleID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_recurring_project_billing_cycles',
  `operationalTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks',
  `operationalProjectID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_projects',
  `processID` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'APQC process identifier',
  `workflowStepID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflow_steps - If part of workflow',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`timelogID`),
  KEY `idx_billing_cycle` (`billingCycleID`),
  KEY `idx_operationalTask` (`operationalTaskID`),
  KEY `idx_operationalProject` (`operationalProjectID`),
  KEY `idx_processID` (`processID`),
  KEY `idx_workflowStep` (`workflowStepID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_tasks_time_logs`
--

INSERT INTO `tija_tasks_time_logs` (`timelogID`, `DateAdded`, `taskDate`, `employeeID`, `clientID`, `projectID`, `projectPhaseID`, `projectTaskID`, `subtaskID`, `workTypeID`, `taskNarrative`, `startTime`, `endTime`, `taskDuration`, `taskDurationSeconds`, `billable`, `billableRateValue`, `workHours`, `dailyComplete`, `taskStatusID`, `taskType`, `taskActivityID`, `workSegmentID`, `recurringInstanceID`, `billingCycleID`, `operationalTaskID`, `operationalProjectID`, `processID`, `workflowStepID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-29 12:08:08', '2025-11-29', 4, 4, 3, 2, 3, NULL, 1, 'fsd fg sdfg safdg', '00:00:00', '00:00:00', '02:18', 0, 'Y', 0.00, NULL, 'N', 2, 'project', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-29 12:08:08', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_task_files`
--

DROP TABLE IF EXISTS `tija_task_files`;
CREATE TABLE IF NOT EXISTS `tija_task_files` (
  `taskFileID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fileURL` varchar(256) NOT NULL,
  `timelogID` int NOT NULL,
  `userID` int NOT NULL,
  `fileSize` int DEFAULT NULL,
  `fileType` varchar(256) DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`taskFileID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_task_status`
--

DROP TABLE IF EXISTS `tija_task_status`;
CREATE TABLE IF NOT EXISTS `tija_task_status` (
  `taskStatusID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `taskStatusName` varchar(256) NOT NULL,
  `taskStatusDescription` text NOT NULL,
  `colorVariableID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('N','Y') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`taskStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_task_status_change_log`;
CREATE TABLE IF NOT EXISTS `tija_task_status_change_log` (
  `taskStatusChangeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectID` int NOT NULL,
  `taskStatusID` int NOT NULL,
  `projectTaskID` int NOT NULL,
  `projectPhaseID` int DEFAULT NULL,
  `subtaskID` int DEFAULT NULL,
  `changeDateTime` datetime NOT NULL,
  `employeeID` int NOT NULL,
  `taskChangeNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `taskDate` date NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`taskStatusChangeID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_taxable_profit`
--

DROP TABLE IF EXISTS `tija_taxable_profit`;
CREATE TABLE IF NOT EXISTS `tija_taxable_profit` (
  `taxableProfitID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `fiscalYear` int NOT NULL,
  `taxableProfit` float(20,2) NOT NULL,
  `taxableProfitDescription` text,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`taxableProfitID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tax_adjustments_accounts`
--

DROP TABLE IF EXISTS `tija_tax_adjustments_accounts`;
CREATE TABLE IF NOT EXISTS `tija_tax_adjustments_accounts` (
  `adjustmentAccountsID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adjustmentTypeID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `financialStatementAccountID` int NOT NULL,
  `financialStatementTypeID` int NOT NULL,
  `accountRate` float(3,2) NOT NULL DEFAULT '1.00',
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`adjustmentAccountsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tax_adjustment_categories`
--

DROP TABLE IF EXISTS `tija_tax_adjustment_categories`;
CREATE TABLE IF NOT EXISTS `tija_tax_adjustment_categories` (
  `adjustmentCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adjustmentCategoryName` varchar(256) NOT NULL,
  `adjustmentCategoryDescription` text NOT NULL,
  `adjustmentTypeID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`adjustmentCategoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_tax_adjustment_types`
--

DROP TABLE IF EXISTS `tija_tax_adjustment_types`;
CREATE TABLE IF NOT EXISTS `tija_tax_adjustment_types` (
  `adjustmentTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `adjustmentType` varchar(255) NOT NULL,
  `adjustmentTypeDescription` text NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`adjustmentTypeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_travel_rate_types`
--

DROP TABLE IF EXISTS `tija_travel_rate_types`;
CREATE TABLE IF NOT EXISTS `tija_travel_rate_types` (
  `travelRateTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `travelRateTypeName` varchar(255) NOT NULL,
  `travelRateTypeDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`travelRateTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `tija_trial_balance_mapped_accounts`;
CREATE TABLE IF NOT EXISTS `tija_trial_balance_mapped_accounts` (
  `mappedAccountID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `financialStatementID` int NOT NULL,
  `financialStatementTypeID` int NOT NULL,
  `statementTypeNode` varchar(256) NOT NULL,
  `financialStatementAccountID` int NOT NULL,
  `financialStatementDataID` int NOT NULL,
  `accountName` varchar(256) NOT NULL,
  `accountType` varchar(255) NOT NULL,
  `accountCategory` varchar(256) NOT NULL,
  `debitValue` decimal(12,2) NOT NULL,
  `creditValue` decimal(12,2) NOT NULL,
  `accountCode` varchar(120) NOT NULL,
  `categoryAccountCode` varchar(120) NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mappedAccountID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_units`
--

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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
-- Table structure for table `tija_withholding_tax`
--

DROP TABLE IF EXISTS `tija_withholding_tax`;
CREATE TABLE IF NOT EXISTS `tija_withholding_tax` (
  `withholdingTaxID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `fiscalYear` int NOT NULL,
  `withholdingTax` float(22,2) NOT NULL,
  `withholdingTaxDescription` text,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`withholdingTaxID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tija_workflows`
--

DROP TABLE IF EXISTS `tija_workflows`;
CREATE TABLE IF NOT EXISTS `tija_workflows` (
  `workflowID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workflowName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workflowDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `workflowType` enum('sequential','parallel','conditional','state_machine') COLLATE utf8mb4_unicode_ci DEFAULT 'sequential',
  `version` int DEFAULT '1' COMMENT 'Version control',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `workflowDefinition` json DEFAULT NULL COMMENT 'Workflow structure (nodes, edges, conditions)',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`workflowID`),
  UNIQUE KEY `unique_workflowCode` (`workflowCode`),
  KEY `idx_process` (`processID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master workflow definitions';

--
-- Dumping data for table `tija_workflows`
--

INSERT INTO `tija_workflows` (`workflowID`, `workflowCode`, `workflowName`, `workflowDescription`, `processID`, `functionalArea`, `functionalAreaID`, `workflowType`, `version`, `isActive`, `workflowDefinition`, `createdByID`, `functionalAreaOwnerID`, `DateAdded`, `LastUpdate`, `LastUpdatedByID`, `Lapsed`, `Suspended`) VALUES
(1, 'WF-PAYROLL-001', 'Monthly Payroll Processing', 'Sequential workflow for processing monthly payroll', 1, 'HR', 2, 'sequential', 1, 'Y', '{\"type\": \"sequential\", \"steps\": [{\"name\": \"Collect Time Records\", \"step\": 1, \"type\": \"task\"}, {\"name\": \"Calculate Payroll\", \"step\": 2, \"type\": \"task\"}, {\"name\": \"Review and Approve\", \"step\": 3, \"type\": \"approval\"}, {\"name\": \"Process Payments\", \"step\": 4, \"type\": \"task\"}, {\"name\": \"Generate Reports\", \"step\": 5, \"type\": \"task\"}]}', NULL, NULL, '2025-11-29 15:09:38', '2025-11-29 12:10:07', NULL, 'N', 'N'),
(2, 'WF-AP-001', 'Accounts Payable Processing', 'Workflow for processing vendor invoices and payments', 6, 'Finance', 1, 'sequential', 1, 'Y', '{\"type\": \"sequential\", \"steps\": [{\"name\": \"Receive Invoice\", \"step\": 1, \"type\": \"task\"}, {\"name\": \"Match to PO\", \"step\": 2, \"type\": \"task\"}, {\"name\": \"Obtain Approval\", \"step\": 3, \"type\": \"approval\"}, {\"name\": \"Process Payment\", \"step\": 4, \"type\": \"task\"}, {\"name\": \"Record in GL\", \"step\": 5, \"type\": \"task\"}]}', NULL, NULL, '2025-11-29 15:09:38', '2025-11-29 12:10:07', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_workflow_instances`
--

DROP TABLE IF EXISTS `tija_workflow_instances`;
CREATE TABLE IF NOT EXISTS `tija_workflow_instances` (
  `instanceID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
  `operationalTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks',
  `currentStepID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflow_steps',
  `status` enum('pending','in_progress','completed','cancelled','error') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `startedDate` datetime DEFAULT NULL,
  `completedDate` datetime DEFAULT NULL,
  `instanceData` json DEFAULT NULL COMMENT 'Runtime data',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`instanceID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_operationalTask` (`operationalTaskID`),
  KEY `idx_currentStep` (`currentStepID`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Active workflow executions';

-- --------------------------------------------------------

--
-- Table structure for table `tija_workflow_steps`
--

DROP TABLE IF EXISTS `tija_workflow_steps`;
CREATE TABLE IF NOT EXISTS `tija_workflow_steps` (
  `workflowStepID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
  `stepOrder` int NOT NULL,
  `stepName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepDescription` text COLLATE utf8mb4_unicode_ci,
  `stepType` enum('task','approval','decision','notification','automation','subprocess') COLLATE utf8mb4_unicode_ci DEFAULT 'task',
  `assigneeType` enum('role','employee','function_head','auto') COLLATE utf8mb4_unicode_ci DEFAULT 'auto',
  `assigneeRoleID` int DEFAULT NULL COMMENT 'FK to permission roles',
  `assigneeEmployeeID` int DEFAULT NULL COMMENT 'FK to people',
  `estimatedDuration` decimal(10,2) DEFAULT NULL COMMENT 'Estimated hours',
  `isMandatory` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `stepConfig` json DEFAULT NULL COMMENT 'Step-specific configuration',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`workflowStepID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_stepOrder` (`stepOrder`),
  KEY `idx_assigneeRole` (`assigneeRoleID`),
  KEY `idx_assigneeEmployee` (`assigneeEmployeeID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual steps in workflow';

--
-- Dumping data for table `tija_workflow_steps`
--

INSERT INTO `tija_workflow_steps` (`workflowStepID`, `workflowID`, `stepOrder`, `stepName`, `stepDescription`, `stepType`, `assigneeType`, `assigneeRoleID`, `assigneeEmployeeID`, `estimatedDuration`, `isMandatory`, `stepConfig`, `DateAdded`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, 1, 1, 'Collect Time Records', 'Collect and validate employee time and attendance records', 'task', 'role', NULL, NULL, 2.00, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(2, 1, 2, 'Calculate Payroll', 'Calculate gross pay, deductions, and net pay', 'task', 'role', NULL, NULL, 3.00, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(3, 1, 3, 'Review and Approve', 'Review payroll calculations and obtain approval', 'approval', 'function_head', NULL, NULL, 1.00, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(4, 1, 4, 'Process Payments', 'Process and distribute payroll payments', 'task', 'role', NULL, NULL, 1.00, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(5, 1, 5, 'Generate Reports', 'Generate payroll reports and remit taxes', 'task', 'role', NULL, NULL, 1.00, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(6, 2, 1, 'Receive Invoice', 'Receive and verify vendor invoice', 'task', 'role', NULL, NULL, 1.00, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(7, 2, 2, 'Match to PO', 'Match invoice to purchase order and receiving documents', 'task', 'role', NULL, NULL, 1.50, 'Y', NULL, '2025-11-29 15:09:38', '2025-11-29 12:09:38', 'N', 'N'),
(8, 2, 3, 'Obtain Approval', 'Obtain required approval for payment', 'approval', 'function_head', NULL, NULL, 0.50, 'Y', NULL, '2025-11-29 15:09:39', '2025-11-29 12:09:39', 'N', 'N'),
(9, 2, 4, 'Process Payment', 'Process payment to vendor', 'task', 'role', NULL, NULL, 1.00, 'Y', NULL, '2025-11-29 15:09:39', '2025-11-29 12:09:39', 'N', 'N'),
(10, 2, 5, 'Record in GL', 'Record transaction in general ledger', 'task', 'role', NULL, NULL, 0.50, 'Y', NULL, '2025-11-29 15:09:39', '2025-11-29 12:09:39', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_workflow_transitions`
--

DROP TABLE IF EXISTS `tija_workflow_transitions`;
CREATE TABLE IF NOT EXISTS `tija_workflow_transitions` (
  `transitionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
  `fromStepID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflow_steps',
  `toStepID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflow_steps',
  `conditionType` enum('always','conditional','time_based','event_based') COLLATE utf8mb4_unicode_ci DEFAULT 'always',
  `conditionExpression` json DEFAULT NULL COMMENT 'Condition logic',
  `transitionLabel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transitionID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_fromStep` (`fromStepID`),
  KEY `idx_toStep` (`toStepID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Transitions between workflow steps';

--
-- Dumping data for table `tija_workflow_transitions`
--

INSERT INTO `tija_workflow_transitions` (`transitionID`, `workflowID`, `fromStepID`, `toStepID`, `conditionType`, `conditionExpression`, `transitionLabel`, `DateAdded`) VALUES
(1, 1, 1, 2, 'always', NULL, 'Next', '2025-11-29 15:09:38'),
(2, 1, 2, 3, 'always', NULL, 'Next', '2025-11-29 15:09:38'),
(3, 1, 3, 4, 'always', NULL, 'Approved', '2025-11-29 15:09:38'),
(4, 1, 4, 5, 'always', NULL, 'Next', '2025-11-29 15:09:38'),
(5, 2, 6, 7, 'always', NULL, 'Next', '2025-11-29 15:09:39'),
(6, 2, 7, 8, 'always', NULL, 'Next', '2025-11-29 15:09:39'),
(7, 2, 8, 9, 'always', NULL, 'Approved', '2025-11-29 15:09:39'),
(8, 2, 9, 10, 'always', NULL, 'Next', '2025-11-29 15:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `tija_work_categories`
--

DROP TABLE IF EXISTS `tija_work_categories`;
CREATE TABLE IF NOT EXISTS `tija_work_categories` (
  `workCategoryID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `workCategoryName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `workCategoryCode` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `workCategoryDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`workCategoryID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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

DROP TABLE IF EXISTS `tija_work_types`;
CREATE TABLE IF NOT EXISTS `tija_work_types` (
  `workTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `workTypeCode` varchar(120) DEFAULT NULL,
  `workTypeName` varchar(120) NOT NULL,
  `workTypeDescription` text,
  `workCategoryID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`workTypeID`),
  UNIQUE KEY `workTypeCode` (`workTypeCode`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

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
-- Table structure for table `time_entry_templates`
--

DROP TABLE IF EXISTS `time_entry_templates`;
CREATE TABLE IF NOT EXISTS `time_entry_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT COMMENT 'Primary key for template',
  `userID` int NOT NULL COMMENT 'User who created the template',
  `templateName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name/description of the template',
  `templateData` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON data containing template fields',
  `createdDate` datetime NOT NULL COMMENT 'When the template was created',
  `modifiedDate` datetime DEFAULT NULL COMMENT 'Last modification date',
  `Suspended` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Y/N - Is template active?',
  PRIMARY KEY (`templateID`),
  KEY `idx_userID` (`userID`),
  KEY `idx_suspended` (`Suspended`),
  KEY `idx_user_suspended` (`userID`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores reusable time entry templates for quick data entry';

--
-- Dumping data for table `time_entry_templates`
--

INSERT INTO `time_entry_templates` (`templateID`, `userID`, `templateName`, `templateData`, `createdDate`, `modifiedDate`, `Suspended`) VALUES
(1, 1, 'Daily Standup', '{\"projectID\":\"5\",\"workTypeID\":\"1\",\"taskDuration\":\"00:15\",\"taskStatusID\":\"2\",\"taskNarrative\":\"Daily standup meeting\"}', '2025-11-28 20:30:56', NULL, 'N'),
(2, 1, 'Code Review', '{\"projectID\":\"5\",\"workTypeID\":\"2\",\"taskDuration\":\"01:00\",\"taskStatusID\":\"2\",\"taskNarrative\":\"Code review session\"}', '2025-11-28 20:30:56', NULL, 'N'),
(3, 1, 'Documentation', '{\"projectID\":\"5\",\"workTypeID\":\"3\",\"taskDuration\":\"02:00\",\"taskStatusID\":\"2\",\"taskNarrative\":\"Writing technical documentation\"}', '2025-11-28 20:30:56', NULL, 'N'),
(4, 1, 'Daily Standup', '{\"projectID\":\"5\",\"workTypeID\":\"1\",\"taskDuration\":\"00:15\",\"taskStatusID\":\"2\",\"taskNarrative\":\"Daily standup meeting\"}', '2025-11-28 20:31:08', NULL, 'N'),
(5, 1, 'Code Review', '{\"projectID\":\"5\",\"workTypeID\":\"2\",\"taskDuration\":\"01:00\",\"taskStatusID\":\"2\",\"taskNarrative\":\"Code review session\"}', '2025-11-28 20:31:08', NULL, 'N'),
(6, 1, 'Documentation', '{\"projectID\":\"5\",\"workTypeID\":\"3\",\"taskDuration\":\"02:00\",\"taskStatusID\":\"2\",\"taskNarrative\":\"Writing technical documentation\"}', '2025-11-28 20:31:08', NULL, 'N');

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
  `bonusEligible` enum('Y','N') DEFAULT 'N' COMMENT 'Eligible for performance bonuses',
  `commissionEligible` enum('Y','N') DEFAULT 'N' COMMENT 'Eligible for sales commission',
  `commissionRate` decimal(5,2) DEFAULT '0.00' COMMENT 'Commission percentage (0-100)',
  `SetUpProfile` enum('y','n') NOT NULL DEFAULT 'n',
  `profileImageFile` varchar(256) DEFAULT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `contractStartDate` varchar(234) DEFAULT NULL,
  `contractEndDate` varchar(234) DEFAULT NULL,
  `employmentStartDate` date DEFAULT NULL,
  `employmentEndDate` date DEFAULT NULL,
  `LastUpdatedByID` int DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isHRManager` enum('Y','N') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'N',
  UNIQUE KEY `UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`ID`, `UID`, `DateAdded`, `orgDataID`, `entityID`, `prefixID`, `phoneNo`, `payrollNo`, `PIN`, `dateOfBirth`, `gender`, `businessUnitID`, `supervisorID`, `supervisingJobTitleID`, `workTypeID`, `jobTitleID`, `departmentID`, `costPerHour`, `jobCategoryID`, `salary`, `jobBandID`, `employmentStatusID`, `dailyHours`, `weekWorkDays`, `overtimeAllowed`, `workHourRoundingID`, `payGradeID`, `nationalID`, `nhifNumber`, `nssfNumber`, `basicSalary`, `bonusEligible`, `commissionEligible`, `commissionRate`, `SetUpProfile`, `profileImageFile`, `Lapsed`, `Suspended`, `contractStartDate`, `contractEndDate`, `employmentStartDate`, `employmentEndDate`, `LastUpdatedByID`, `LastUpdate`, `isHRManager`) VALUES
(2, 'ada2b28babe49a343e90ba0761e687bc896d7650e8976dcecfa64c7b9aa3f685', '2025-11-21 09:59:36', 1, 1, '1', '+254 721 358850', 'SBSL-001', NULL, NULL, '', NULL, 0, NULL, NULL, 14, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 09:59:36', 'N'),
(3, 'dfa49391168a5c5f4bc9f9857826485450fd0aff952db6acdd8586e3374c11cb', '2025-11-21 10:01:12', 1, 1, '1', '+254 720 668781', 'SBSL-002', NULL, NULL, '', NULL, 2, NULL, NULL, 19, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 13:54:26', 'N'),
(4, '44af8d7eee03d5305300e17c14b408401707baa65bb4d37cdcda5d753bcfe8b4', '2025-11-21 11:21:15', 1, 1, '1', '+254722540169', 'SBSL-003', NULL, NULL, '', NULL, 2, NULL, NULL, 22, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 14:18:25', 'Y'),
(5, '103997ec9a284212482cdff5bac5b8d7e4fa450ab46758930b0e57ed9f224fe8', '2025-11-21 11:23:46', 1, 1, '1', '+254 725 148487', 'SBSL-004', NULL, NULL, '', NULL, 2, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:23:46', 'N'),
(6, '4e6cf8f79eaa49d8c6036607e750e34b30322ec201e17b88e11d50975c18dcfb', '2025-11-21 11:25:20', 1, 1, '3', '+254 723 853601', 'SBSL-005', NULL, NULL, '', NULL, 2, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:25:20', 'N'),
(7, '33a01c6d40927d592553e8220a611af4f1f0f8feaf2df201fb6b37d907994753', '2025-11-21 11:30:05', 1, 1, '1', '+254', 'SBSL-006', NULL, NULL, '', NULL, 2, NULL, NULL, 51, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:30:05', 'N'),
(8, 'd404d0cef55db4e0cc487d7aa8ca950f611a5a114a990837103caed8de490a42', '2025-11-21 11:31:54', 1, 1, '1', '+254', 'SBSL-007', NULL, NULL, '', NULL, 5, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:31:54', 'N'),
(9, '7713d6a14f491f26f89614a09d7acc78fa2805b90fc1d2ab5c88c43d9058a87d', '2025-11-21 11:42:45', 1, 1, '1', '+254', 'SBSL-008', NULL, NULL, '', NULL, 2, NULL, NULL, 27, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:42:45', 'N'),
(10, '536c5409ab73e8ea8a4e35bbae3c272eedad40402e09b2bfeba279a82ea67841', '2025-11-21 11:45:25', 1, 1, '3', '+254', 'SBSL-009', NULL, NULL, '', NULL, 6, NULL, NULL, 53, NULL, NULL, NULL, NULL, NULL, 1, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:45:25', 'N'),
(11, '07a838d888e7c00132e6304625b31a972ec4662dc9489afa48a8cfb7cc22f2ba', '2025-11-21 11:49:53', 1, 1, NULL, '+254', 'SBSL-011', NULL, NULL, '', NULL, 4, NULL, NULL, 41, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-04-01', NULL, 1, '2025-11-21 11:49:53', 'N'),
(12, '785179f2eb8775766fc916af14ef9d26803c5fb9dfc9d299c92a4c77ae00635d', '2025-11-21 11:51:24', 1, 1, '1', '+254', 'SBSL-012', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:51:24', 'N'),
(13, '368879280e79775136acf93dba0a25c0e0c7a9e36dcf75f9f10ec267e30e8d11', '2025-11-21 11:56:24', 1, 1, NULL, '+254', 'SBSL-013', NULL, NULL, '', NULL, 4, NULL, NULL, 41, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 11:56:24', 'N'),
(14, 'd5e47d3e44f798967eca55de66407f4cc3f8dfd82bd6e136ead46901ecafbf39', '2025-11-21 12:00:07', 1, 1, '1', '+254', 'SBSL-014', NULL, NULL, '', NULL, 4, NULL, NULL, 59, NULL, NULL, NULL, NULL, NULL, 5, 8, '5', 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:00:07', 'N'),
(15, '0ca7eae04454e868289b9f2b43a9bea0b81fb2a7aca8bb373c7e5b5a04e6454c', '2025-11-21 12:05:10', 1, 1, '1', '+254', 'SBSL-015', NULL, NULL, '', NULL, 2, NULL, NULL, 43, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:05:10', 'N'),
(16, '0231f46e42ceaf0c4a0fe4639896d8efaf66e77c45c11cf4223c2270a983e10a', '2025-11-21 12:10:29', 1, 1, '1', '+254', 'SBSL-016', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:10:29', 'N'),
(17, 'ab6e05b9cce17f9c29a31d7499ce39e975d13a9e6b46885a29de7b89335986f2', '2025-11-21 12:16:15', 1, 1, '1', '+254', 'SBSL-017', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:16:15', 'N'),
(18, '20eba8a2696a02b21db8f4fd15254ccbd1b9e84b7fbd1820f857ebd7efac26de', '2025-11-21 12:18:51', 1, 1, '1', '+254', 'SBSL-018', NULL, NULL, '', NULL, 2, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:18:51', 'N'),
(19, '46f299208147f28489b9ef4da23ef583a57cf5e77f1ed60681ea6a0711d8ba95', '2025-11-21 12:23:27', 1, 1, '1', '+254', 'SBSL-019', NULL, NULL, '', NULL, 4, NULL, NULL, 48, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-08-01', NULL, 1, '2025-11-21 12:23:27', 'N'),
(20, 'ac5298c8157cd560ce568cc0688de7bdc478d0349325c008e5e0933c1ba8f1df', '2025-11-21 12:30:30', 1, 1, '3', '+254', 'SBSL-020', NULL, NULL, '', NULL, 3, NULL, NULL, 57, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:30:30', 'N'),
(21, '6001923fb6a5d0e11213929aed7527fab50f35928ef4a4534088e85013b91203', '2025-11-21 12:32:33', 1, 1, '1', '+254', 'SBSL-010', NULL, NULL, '', NULL, 0, NULL, NULL, 15, NULL, NULL, NULL, NULL, NULL, 5, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 1, '2025-11-21 12:32:33', 'N'),
(22, '5a5d380f4faf074fdfa2f3583750d60e52a1e410f40fb77fe41bd790eced4906', '2025-11-24 10:10:16', 1, 1, '1', '+254722540169', 'SBSL-099', 'A004654098IK', '2007-01-01', '', NULL, 4, NULL, NULL, 28, NULL, NULL, NULL, 90000.00, NULL, 1, NULL, NULL, 'Y', NULL, NULL, '2343456543', 'Y78u9474T', '2343654G', 90000.00, 'Y', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 10:10:16', 'N'),
(23, '28632e264eb531b164bf8ea856bde6f22dd60e06f996deaaf393c529c4233fe3', '2025-11-24 17:19:54', 1, 1, '1', '+254722540169', 'SBSL-102', NULL, NULL, '', NULL, 22, NULL, NULL, 20, NULL, NULL, NULL, 90000.00, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, 90000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 17:19:54', 'N'),
(24, 'd6716dfc5af3ee0a8f9d7ef817375775da8b174bba4e1f17a360b261406c8235', '2025-11-24 17:21:00', 1, 1, '3', '+254700039147', 'SBSL-101', NULL, NULL, '', NULL, 22, NULL, NULL, 24, NULL, NULL, NULL, 500000.00, NULL, 1, NULL, NULL, 'N', NULL, NULL, NULL, NULL, NULL, 500000.00, 'N', 'N', 0.00, 'n', NULL, 'N', 'N', NULL, NULL, '2025-01-01', NULL, 4, '2025-11-24 14:27:56', 'N');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_activity_expense_totals`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `view_activity_expense_totals`;
CREATE TABLE IF NOT EXISTS `view_activity_expense_totals` (
`activityID` int
,`approvedReimbursement` decimal(37,2)
,`expenseCount` bigint
,`paidReimbursement` decimal(37,2)
,`pendingReimbursement` decimal(37,2)
,`totalExpenses` decimal(37,2)
,`totalNonReimbursable` decimal(37,2)
,`totalReimbursable` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leave_approval_policies`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_leave_approval_policies`;
CREATE TABLE IF NOT EXISTS `vw_leave_approval_policies` (
`allowDelegation` enum('Y','N')
,`autoApproveThreshold` int
,`createdAt` datetime
,`createdBy` int
,`createdByName` varchar(257)
,`entityID` int
,`isActive` enum('Y','N')
,`isDefault` enum('Y','N')
,`orgDataID` int
,`policyDescription` text
,`policyID` int
,`policyName` varchar(255)
,`requireAllApprovals` enum('Y','N')
,`requiredSteps` bigint
,`totalSteps` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leave_approval_workflow`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_leave_approval_workflow`;
CREATE TABLE IF NOT EXISTS `vw_leave_approval_workflow` (
`conditionType` enum('days_threshold','leave_type','user_role','department','custom')
,`customApproversCount` bigint
,`entityID` int
,`escalationDays` int
,`isConditional` enum('Y','N')
,`isRequired` enum('Y','N')
,`policyID` int
,`policyName` varchar(255)
,`stepDescription` text
,`stepID` int
,`stepName` varchar(255)
,`stepOrder` int
,`stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_notification_events_with_templates`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_notification_events_with_templates`;
CREATE TABLE IF NOT EXISTS `vw_notification_events_with_templates` (
`eventCategory` varchar(50)
,`eventDescription` text
,`eventID` int
,`eventName` varchar(100)
,`eventSlug` varchar(100)
,`isActive` enum('Y','N')
,`isUserConfigurable` enum('Y','N')
,`moduleID` int
,`moduleName` varchar(100)
,`moduleSlug` varchar(50)
,`priorityLevel` enum('low','medium','high','critical')
,`templateCount` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_pending_leave_approvals`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_pending_leave_approvals`;
CREATE TABLE IF NOT EXISTS `vw_pending_leave_approvals` (
`currentStepID` int
,`currentStepName` varchar(255)
,`currentStepOrder` int
,`currentStepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user')
,`daysPending` int
,`employeeID` int
,`employeeName` varchar(257)
,`endDate` date
,`instanceID` int
,`lastActionAt` datetime
,`leaveApplicationID` int
,`leaveTypeID` int
,`leaveTypeName` varchar(255)
,`policyID` int
,`policyName` varchar(255)
,`startDate` date
,`startedAt` datetime
,`totalDays` decimal(3,2)
,`workflowStatus` enum('pending','in_progress','approved','rejected','cancelled','escalated')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_user_notification_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_user_notification_summary`;
CREATE TABLE IF NOT EXISTS `vw_user_notification_summary` (
`criticalUnread` decimal(23,0)
,`lastNotificationDate` datetime
,`readCount` decimal(23,0)
,`totalNotifications` bigint
,`unreadCount` decimal(23,0)
,`userID` int
);

-- --------------------------------------------------------

--
-- Structure for view `view_activity_expense_totals`
--
DROP TABLE IF EXISTS `view_activity_expense_totals`;

DROP VIEW IF EXISTS `view_activity_expense_totals`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `view_activity_expense_totals`  AS SELECT `ae`.`activityID` AS `activityID`, count(`ae`.`expenseID`) AS `expenseCount`, sum(`ae`.`expenseAmount`) AS `totalExpenses`, sum((case when (`ae`.`reimbursable` = 'Y') then `ae`.`expenseAmount` else 0 end)) AS `totalReimbursable`, sum((case when (`ae`.`reimbursable` = 'N') then `ae`.`expenseAmount` else 0 end)) AS `totalNonReimbursable`, sum((case when (`ae`.`reimbursementStatus` = 'pending') then `ae`.`expenseAmount` else 0 end)) AS `pendingReimbursement`, sum((case when (`ae`.`reimbursementStatus` = 'approved') then `ae`.`expenseAmount` else 0 end)) AS `approvedReimbursement`, sum((case when (`ae`.`reimbursementStatus` = 'paid') then `ae`.`expenseAmount` else 0 end)) AS `paidReimbursement` FROM `tija_activity_expenses` AS `ae` WHERE (`ae`.`Suspended` = 'N') GROUP BY `ae`.`activityID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_leave_approval_policies`
--
DROP TABLE IF EXISTS `vw_leave_approval_policies`;

DROP VIEW IF EXISTS `vw_leave_approval_policies`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_leave_approval_policies`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`entityID` AS `entityID`, `p`.`orgDataID` AS `orgDataID`, `p`.`policyName` AS `policyName`, `p`.`policyDescription` AS `policyDescription`, `p`.`isActive` AS `isActive`, `p`.`isDefault` AS `isDefault`, `p`.`requireAllApprovals` AS `requireAllApprovals`, `p`.`allowDelegation` AS `allowDelegation`, `p`.`autoApproveThreshold` AS `autoApproveThreshold`, count(distinct `s`.`stepID`) AS `totalSteps`, count(distinct (case when (`s`.`isRequired` = 'Y') then `s`.`stepID` end)) AS `requiredSteps`, `p`.`createdBy` AS `createdBy`, `p`.`createdAt` AS `createdAt`, concat(`creator`.`FirstName`,' ',`creator`.`Surname`) AS `createdByName` FROM ((`tija_leave_approval_policies` `p` left join `tija_leave_approval_steps` `s` on(((`p`.`policyID` = `s`.`policyID`) and (`s`.`Suspended` = 'N')))) left join `people` `creator` on((`p`.`createdBy` = `creator`.`ID`))) WHERE (`p`.`Lapsed` = 'N') GROUP BY `p`.`policyID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_leave_approval_workflow`
--
DROP TABLE IF EXISTS `vw_leave_approval_workflow`;

DROP VIEW IF EXISTS `vw_leave_approval_workflow`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_leave_approval_workflow`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `p`.`entityID` AS `entityID`, `s`.`stepID` AS `stepID`, `s`.`stepOrder` AS `stepOrder`, `s`.`stepName` AS `stepName`, `s`.`stepType` AS `stepType`, `s`.`stepDescription` AS `stepDescription`, `s`.`isRequired` AS `isRequired`, `s`.`isConditional` AS `isConditional`, `s`.`conditionType` AS `conditionType`, `s`.`escalationDays` AS `escalationDays`, count(`a`.`approverID`) AS `customApproversCount` FROM ((`tija_leave_approval_policies` `p` join `tija_leave_approval_steps` `s` on((`p`.`policyID` = `s`.`policyID`))) left join `tija_leave_approval_step_approvers` `a` on(((`s`.`stepID` = `a`.`stepID`) and (`a`.`Suspended` = 'N')))) WHERE ((`p`.`Lapsed` = 'N') AND (`p`.`Suspended` = 'N') AND (`s`.`Suspended` = 'N')) GROUP BY `s`.`stepID` ORDER BY `p`.`policyID` ASC, `s`.`stepOrder` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_notification_events_with_templates`
--
DROP TABLE IF EXISTS `vw_notification_events_with_templates`;

DROP VIEW IF EXISTS `vw_notification_events_with_templates`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_notification_events_with_templates`  AS SELECT `e`.`eventID` AS `eventID`, `e`.`eventName` AS `eventName`, `e`.`eventSlug` AS `eventSlug`, `e`.`eventDescription` AS `eventDescription`, `e`.`eventCategory` AS `eventCategory`, `e`.`priorityLevel` AS `priorityLevel`, `m`.`moduleID` AS `moduleID`, `m`.`moduleName` AS `moduleName`, `m`.`moduleSlug` AS `moduleSlug`, count(distinct `t`.`templateID`) AS `templateCount`, `e`.`isActive` AS `isActive`, `e`.`isUserConfigurable` AS `isUserConfigurable` FROM ((`tija_notification_events` `e` join `tija_notification_modules` `m` on((`e`.`moduleID` = `m`.`moduleID`))) left join `tija_notification_templates` `t` on(((`e`.`eventID` = `t`.`eventID`) and (`t`.`Suspended` = 'N')))) WHERE ((`e`.`Suspended` = 'N') AND (`m`.`Suspended` = 'N')) GROUP BY `e`.`eventID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_pending_leave_approvals`
--
DROP TABLE IF EXISTS `vw_pending_leave_approvals`;

DROP VIEW IF EXISTS `vw_pending_leave_approvals`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_pending_leave_approvals`  AS SELECT `i`.`instanceID` AS `instanceID`, `i`.`leaveApplicationID` AS `leaveApplicationID`, `la`.`employeeID` AS `employeeID`, concat(`emp`.`FirstName`,' ',`emp`.`Surname`) AS `employeeName`, `la`.`leaveTypeID` AS `leaveTypeID`, `lt`.`leaveTypeName` AS `leaveTypeName`, `la`.`startDate` AS `startDate`, `la`.`endDate` AS `endDate`, `la`.`noOfDays` AS `totalDays`, `i`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `i`.`currentStepID` AS `currentStepID`, `s`.`stepName` AS `currentStepName`, `s`.`stepType` AS `currentStepType`, `s`.`stepOrder` AS `currentStepOrder`, `i`.`workflowStatus` AS `workflowStatus`, `i`.`startedAt` AS `startedAt`, `i`.`lastActionAt` AS `lastActionAt`, (to_days(now()) - to_days(`i`.`lastActionAt`)) AS `daysPending` FROM (((((`tija_leave_approval_instances` `i` join `tija_leave_applications` `la` on((`i`.`leaveApplicationID` = `la`.`leaveApplicationID`))) join `people` `emp` on((`la`.`employeeID` = `emp`.`ID`))) join `tija_leave_types` `lt` on((`la`.`leaveTypeID` = `lt`.`leaveTypeID`))) join `tija_leave_approval_policies` `p` on((`i`.`policyID` = `p`.`policyID`))) left join `tija_leave_approval_steps` `s` on((`i`.`currentStepID` = `s`.`stepID`))) WHERE (`i`.`workflowStatus` in ('pending','in_progress')) ORDER BY `i`.`lastActionAt` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_user_notification_summary`
--
DROP TABLE IF EXISTS `vw_user_notification_summary`;

DROP VIEW IF EXISTS `vw_user_notification_summary`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_user_notification_summary`  AS SELECT `tija_notifications_enhanced`.`userID` AS `userID`, count(0) AS `totalNotifications`, sum((case when (`tija_notifications_enhanced`.`status` = 'unread') then 1 else 0 end)) AS `unreadCount`, sum((case when (`tija_notifications_enhanced`.`status` = 'read') then 1 else 0 end)) AS `readCount`, sum((case when ((`tija_notifications_enhanced`.`priority` = 'critical') and (`tija_notifications_enhanced`.`status` = 'unread')) then 1 else 0 end)) AS `criticalUnread`, max(`tija_notifications_enhanced`.`DateAdded`) AS `lastNotificationDate` FROM `tija_notifications_enhanced` WHERE ((`tija_notifications_enhanced`.`Lapsed` = 'N') AND (`tija_notifications_enhanced`.`Suspended` = 'N')) GROUP BY `tija_notifications_enhanced`.`userID` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tija_expense`
--
ALTER TABLE `tija_expense` ADD FULLTEXT KEY `ft_description` (`description`,`shortDescription`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tija_bau_activities`
--
ALTER TABLE `tija_bau_activities`
  ADD CONSTRAINT `tija_bau_activities_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_bau_processes`
--
ALTER TABLE `tija_bau_processes`
  ADD CONSTRAINT `fk_processes_category` FOREIGN KEY (`categoryID`) REFERENCES `tija_bau_categories` (`categoryID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `tija_bau_processes_ibfk_1` FOREIGN KEY (`processGroupID`) REFERENCES `tija_bau_process_groups` (`processGroupID`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tija_bau_processes_ibfk_2` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_bau_process_groups`
--
ALTER TABLE `tija_bau_process_groups`
  ADD CONSTRAINT `tija_bau_process_groups_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `tija_bau_categories` (`categoryID`) ON DELETE RESTRICT;

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
-- Constraints for table `tija_function_head_assignments`
--
ALTER TABLE `tija_function_head_assignments`
  ADD CONSTRAINT `tija_function_head_assignments_ibfk_1` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_goal_evaluations`
--
ALTER TABLE `tija_goal_evaluations`
  ADD CONSTRAINT `tija_goal_evaluations_ibfk_1` FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals` (`goalUUID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_goal_evaluations_ibfk_2` FOREIGN KEY (`evaluatorUserID`) REFERENCES `people` (`ID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_goal_evaluation_weights`
--
ALTER TABLE `tija_goal_evaluation_weights`
  ADD CONSTRAINT `tija_goal_evaluation_weights_ibfk_1` FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals` (`goalUUID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_goal_kpis`
--
ALTER TABLE `tija_goal_kpis`
  ADD CONSTRAINT `tija_goal_kpis_ibfk_1` FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals` (`goalUUID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_goal_library`
--
ALTER TABLE `tija_goal_library`
  ADD CONSTRAINT `tija_goal_library_ibfk_1` FOREIGN KEY (`broaderConceptID`) REFERENCES `tija_goal_library` (`libraryID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_goal_library_ibfk_2` FOREIGN KEY (`LastUpdatedByID`) REFERENCES `people` (`ID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_goal_library_versions`
--
ALTER TABLE `tija_goal_library_versions`
  ADD CONSTRAINT `tija_goal_library_versions_ibfk_1` FOREIGN KEY (`libraryID`) REFERENCES `tija_goal_library` (`libraryID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_goal_library_versions_ibfk_2` FOREIGN KEY (`LastUpdatedByID`) REFERENCES `people` (`ID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_goal_okrs`
--
ALTER TABLE `tija_goal_okrs`
  ADD CONSTRAINT `tija_goal_okrs_ibfk_1` FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals` (`goalUUID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_goal_scores`
--
ALTER TABLE `tija_goal_scores`
  ADD CONSTRAINT `tija_goal_scores_ibfk_1` FOREIGN KEY (`goalUUID`) REFERENCES `tija_goals` (`goalUUID`) ON DELETE CASCADE;

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
-- Constraints for table `tija_operational_projects`
--
ALTER TABLE `tija_operational_projects`
  ADD CONSTRAINT `tija_operational_projects_ibfk_1` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_operational_tasks`
--
ALTER TABLE `tija_operational_tasks`
  ADD CONSTRAINT `tija_operational_tasks_ibfk_1` FOREIGN KEY (`templateID`) REFERENCES `tija_operational_task_templates` (`templateID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_operational_tasks_ibfk_2` FOREIGN KEY (`workflowInstanceID`) REFERENCES `tija_workflow_instances` (`instanceID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_operational_tasks_ibfk_3` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_operational_tasks_ibfk_4` FOREIGN KEY (`parentInstanceID`) REFERENCES `tija_operational_tasks` (`operationalTaskID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_operational_task_checklists`
--
ALTER TABLE `tija_operational_task_checklists`
  ADD CONSTRAINT `tija_operational_task_checklists_ibfk_1` FOREIGN KEY (`templateID`) REFERENCES `tija_operational_task_templates` (`templateID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_operational_task_checklists_ibfk_2` FOREIGN KEY (`operationalTaskID`) REFERENCES `tija_operational_tasks` (`operationalTaskID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_operational_task_dependencies`
--
ALTER TABLE `tija_operational_task_dependencies`
  ADD CONSTRAINT `tija_operational_task_dependencies_ibfk_1` FOREIGN KEY (`predecessorTaskID`) REFERENCES `tija_operational_tasks` (`operationalTaskID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_operational_task_dependencies_ibfk_2` FOREIGN KEY (`predecessorTemplateID`) REFERENCES `tija_operational_task_templates` (`templateID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_operational_task_dependencies_ibfk_3` FOREIGN KEY (`successorTaskID`) REFERENCES `tija_operational_tasks` (`operationalTaskID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_operational_task_dependencies_ibfk_4` FOREIGN KEY (`successorTemplateID`) REFERENCES `tija_operational_task_templates` (`templateID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_operational_task_notifications`
--
ALTER TABLE `tija_operational_task_notifications`
  ADD CONSTRAINT `tija_operational_task_notifications_ibfk_1` FOREIGN KEY (`templateID`) REFERENCES `tija_operational_task_templates` (`templateID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_operational_task_notifications_ibfk_2` FOREIGN KEY (`taskInstanceID`) REFERENCES `tija_operational_tasks` (`operationalTaskID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_operational_task_templates`
--
ALTER TABLE `tija_operational_task_templates`
  ADD CONSTRAINT `tija_operational_task_templates_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_operational_task_templates_ibfk_2` FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows` (`workflowID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_operational_task_templates_ibfk_3` FOREIGN KEY (`sopID`) REFERENCES `tija_sops` (`sopID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_operational_task_templates_ibfk_4` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_organization_functional_areas`
--
ALTER TABLE `tija_organization_functional_areas`
  ADD CONSTRAINT `tija_organization_functional_areas_ibfk_1` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_process_metrics`
--
ALTER TABLE `tija_process_metrics`
  ADD CONSTRAINT `tija_process_metrics_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_process_models`
--
ALTER TABLE `tija_process_models`
  ADD CONSTRAINT `tija_process_models_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_process_optimization_recommendations`
--
ALTER TABLE `tija_process_optimization_recommendations`
  ADD CONSTRAINT `tija_process_optimization_recommendations_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_process_simulations`
--
ALTER TABLE `tija_process_simulations`
  ADD CONSTRAINT `tija_process_simulations_ibfk_1` FOREIGN KEY (`modelID`) REFERENCES `tija_process_models` (`modelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_project_plan_template_phases`
--
ALTER TABLE `tija_project_plan_template_phases`
  ADD CONSTRAINT `fk_template_phases_template` FOREIGN KEY (`templateID`) REFERENCES `tija_project_plan_templates` (`templateID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tija_roles`
--
ALTER TABLE `tija_roles`
  ADD CONSTRAINT `fk_roles_roleLevel` FOREIGN KEY (`roleLevelID`) REFERENCES `tija_role_levels` (`roleLevelID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roles_roleType` FOREIGN KEY (`roleTypeID`) REFERENCES `tija_org_role_types` (`roleTypeID`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `tija_sops`
--
ALTER TABLE `tija_sops`
  ADD CONSTRAINT `tija_sops_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_sops_ibfk_2` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_sop_attachments`
--
ALTER TABLE `tija_sop_attachments`
  ADD CONSTRAINT `tija_sop_attachments_ibfk_1` FOREIGN KEY (`sopID`) REFERENCES `tija_sops` (`sopID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_sop_links`
--
ALTER TABLE `tija_sop_links`
  ADD CONSTRAINT `tija_sop_links_ibfk_1` FOREIGN KEY (`sopID`) REFERENCES `tija_sops` (`sopID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_sop_sections`
--
ALTER TABLE `tija_sop_sections`
  ADD CONSTRAINT `tija_sop_sections_ibfk_1` FOREIGN KEY (`sopID`) REFERENCES `tija_sops` (`sopID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_workflows`
--
ALTER TABLE `tija_workflows`
  ADD CONSTRAINT `tija_workflows_ibfk_1` FOREIGN KEY (`processID`) REFERENCES `tija_bau_processes` (`processID`) ON DELETE SET NULL,
  ADD CONSTRAINT `tija_workflows_ibfk_2` FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas` (`functionalAreaID`) ON DELETE RESTRICT;

--
-- Constraints for table `tija_workflow_instances`
--
ALTER TABLE `tija_workflow_instances`
  ADD CONSTRAINT `tija_workflow_instances_ibfk_1` FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows` (`workflowID`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tija_workflow_instances_ibfk_2` FOREIGN KEY (`currentStepID`) REFERENCES `tija_workflow_steps` (`workflowStepID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_workflow_steps`
--
ALTER TABLE `tija_workflow_steps`
  ADD CONSTRAINT `tija_workflow_steps_ibfk_1` FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows` (`workflowID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_workflow_transitions`
--
ALTER TABLE `tija_workflow_transitions`
  ADD CONSTRAINT `tija_workflow_transitions_ibfk_1` FOREIGN KEY (`workflowID`) REFERENCES `tija_workflows` (`workflowID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_workflow_transitions_ibfk_2` FOREIGN KEY (`fromStepID`) REFERENCES `tija_workflow_steps` (`workflowStepID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_workflow_transitions_ibfk_3` FOREIGN KEY (`toStepID`) REFERENCES `tija_workflow_steps` (`workflowStepID`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
