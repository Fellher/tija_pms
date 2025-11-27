<?php
/**
 * Toggle Business Unit Category Status Script
 * Activates or deactivates a category
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
$success = "";

if ($isValidAdmin || $isAdmin) {

    $categoryID = isset($_GET['categoryID']) ? Utility::clean_string($_GET['categoryID']) : "";
    $newStatus = isset($_GET['newStatus']) ? Utility::clean_string($_GET['newStatus']) : "";

    if (!$categoryID) {
        $errors[] = "Category ID is required";
    }

    if (!in_array($newStatus, ['Y', 'N'])) {
        $errors[] = "Invalid status value";
    }

    if (count($errors) === 0) {
        $category = Data::business_unit_categories(array("categoryID" => $categoryID), true, $DBConn);

        if (!$category) {
            $errors[] = "Category not found";
        } else {
            $changes = array(
                'isActive' => $newStatus,
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdatedByID' => $userDetails->ID
            );

            if (!$DBConn->update_table("tija_business_unit_categories", $changes, array("categoryID" => $categoryID))) {
                $errors[] = "ERROR updating category status";
            } else {
                $action = $newStatus === 'Y' ? 'activated' : 'deactivated';
                $success = "Category {$action} successfully";
            }
        }
    }

} else {
    $errors[] = 'You need to log in as a valid administrator.';
}

$returnURL = isset($_GET['returnURL']) ? $_GET['returnURL'] : $base . 'html/?s=core&ss=admin&p=business_unit_categories';

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
header("location:{$returnURL}");
?>

