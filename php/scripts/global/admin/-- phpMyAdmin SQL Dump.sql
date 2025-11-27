-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 13, 2025 at 01:27 PM
-- Server version: 8.3.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pms_skm_tija`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `sp_calculate_expense_totals`$$
CREATE DEFINER=`skmcibhb_sbslUser`@`localhost` PROCEDURE `sp_calculate_expense_totals` (IN `p_employee_id` INT, IN `p_date_from` DATE, IN `p_date_to` DATE, OUT `p_total_amount` DECIMAL(12,2), OUT `p_total_reimbursement` DECIMAL(12,2), OUT `p_total_tax` DECIMAL(10,2), OUT `p_expense_count` INT)   BEGIN
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
CREATE DEFINER=`skmcibhb_sbslUser`@`localhost` PROCEDURE `sp_generate_expense_number` (IN `p_expense_date` DATE, OUT `p_expense_number` VARCHAR(50))   BEGIN
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
