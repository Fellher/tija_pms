<?php
/**
 * Manage Business Unit Category Script
 * Creates or updates business unit categories
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 * @version 1.0
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

$DBConn->begin();
$errors = array();
$details = array();
$changes = array();
$success = "";

if ($isValidAdmin || $isAdmin) {

    $categoryID = (isset($_POST['categoryID']) && !empty($_POST['categoryID'])) ? Utility::clean_string($_POST['categoryID']) : "";
    $categoryName = (isset($_POST['categoryName']) && !empty($_POST['categoryName'])) ? Utility::clean_string($_POST['categoryName']) : "";
    $categoryCode = (isset($_POST['categoryCode']) && !empty($_POST['categoryCode'])) ? Utility::clean_string($_POST['categoryCode']) : "";
    $categoryDescription = (isset($_POST['categoryDescription']) && !empty($_POST['categoryDescription'])) ? Utility::clean_string($_POST['categoryDescription']) : "";
    $categoryOrder = (isset($_POST['categoryOrder']) && !empty($_POST['categoryOrder'])) ? Utility::clean_string($_POST['categoryOrder']) : 1;
    $iconClass = (isset($_POST['iconClass']) && !empty($_POST['iconClass'])) ? Utility::clean_string($_POST['iconClass']) : "";
    $colorCode = (isset($_POST['colorCode']) && !empty($_POST['colorCode'])) ? Utility::clean_string($_POST['colorCode']) : "#007bff";
    $isActive = (isset($_POST['isActive']) && $_POST['isActive'] == 'Y') ? 'Y' : 'N';

    if (!$categoryID) {
        // CREATE NEW CATEGORY
        $details = [];

        $categoryName ? $details['categoryName'] = $categoryName : $errors[] = "Please provide a category name";
        $categoryCode ? $details['categoryCode'] = $categoryCode : $errors[] = "Please provide a category code";

        if ($categoryDescription) $details['categoryDescription'] = $categoryDescription;
        $details['categoryOrder'] = $categoryOrder;
        if ($iconClass) $details['iconClass'] = $iconClass;
        if ($colorCode) $details['colorCode'] = $colorCode;
        $details['isActive'] = $isActive;

        if (count($errors) === 0) {
            // Check for duplicate category code
            $existing = Data::business_unit_categories(array("categoryCode" => $categoryCode), true, $DBConn);
            if ($existing) {
                $errors[] = "A category with this code already exists";
            } else {
                if (!$DBConn->insert_data("tija_business_unit_categories", $details)) {
                    $errors[] = "ERROR inserting category in the database";
                } else {
                    $success = "Business unit category added successfully";
                }
            }
        }

    } else {
        // UPDATE EXISTING CATEGORY
        $categoryDetails = Data::business_unit_categories(array("categoryID" => $categoryID), true, $DBConn);

        if (!$categoryDetails) {
            $errors[] = "Category not found";
        } else {
            $changes = [];

            if ($categoryName && ($categoryName !== $categoryDetails->categoryName)) {
                $changes['categoryName'] = $categoryName;
            }

            if ($categoryCode && ($categoryCode !== $categoryDetails->categoryCode)) {
                // Check if new code is unique
                $existing = Data::business_unit_categories(array("categoryCode" => $categoryCode), true, $DBConn);
                if ($existing && $existing->categoryID != $categoryID) {
                    $errors[] = "A category with this code already exists";
                } else {
                    $changes['categoryCode'] = $categoryCode;
                }
            }

            if ($categoryDescription !== $categoryDetails->categoryDescription) {
                $changes['categoryDescription'] = $categoryDescription;
            }

            if ($categoryOrder && ($categoryOrder !== $categoryDetails->categoryOrder)) {
                $changes['categoryOrder'] = $categoryOrder;
            }

            if ($iconClass !== $categoryDetails->iconClass) {
                $changes['iconClass'] = $iconClass;
            }

            if ($colorCode !== $categoryDetails->colorCode) {
                $changes['colorCode'] = $colorCode;
            }

            if ($isActive !== $categoryDetails->isActive) {
                $changes['isActive'] = $isActive;
            }

            if (count($errors) === 0) {
                if (count($changes) > 0) {
                    $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                    $changes['LastUpdatedByID'] = $userDetails->ID;

                    if (!$DBConn->update_table("tija_business_unit_categories", $changes, array("categoryID" => $categoryID))) {
                        $errors[] = "ERROR updating category in the database";
                    } else {
                        $success = "Business unit category updated successfully";
                    }
                } else {
                    $success = "No changes detected";
                }
            }
        }
    }

} else {
    $errors[] = 'You need to log in as a valid administrator to manage categories.';
}

$returnURL = Utility::returnURL($_SESSION['returnURL'], 's=core&ss=admin&p=business_unit_categories');

if (count($errors) == 0) {
    $DBConn->commit();
    $messages = array(array('Text' => $success, 'Type' => 'success'));
} else {
    $DBConn->rollback();
    $messages = array_map(function($error) {
        return array('Text' => $error, 'Type' => 'danger');
    }, $errors);
}

$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
?>

