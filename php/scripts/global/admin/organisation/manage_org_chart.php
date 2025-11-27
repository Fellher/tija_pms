
<?php
/**
 * Manage Organization Chart Script
 * Handles creation and updating of organizational charts
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 */

session_start();
$base = '../../../../../';
set_include_path($base);

include 'php/includes.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
error_log("=== Manage Org Chart Script Started ===");
error_log("POST Data: " . print_r($_POST, true));

$DBConn->begin();
$errors = array();
$success = "";

try {
    // Check admin access
    if (!$isValidAdmin && !$isAdmin) {
        throw new Exception("You are not authorized to perform this action.");
    }

    // Get form data
    $orgChartName = isset($_POST['orgChartName']) && !empty($_POST['orgChartName'])
        ? Utility::clean_string($_POST['orgChartName']) : null;
    $orgChartDescription = isset($_POST['orgChartDescription']) && !empty($_POST['orgChartDescription'])
        ? Utility::clean_string($_POST['orgChartDescription']) : null;
    $chartType = isset($_POST['chartType']) && !empty($_POST['chartType'])
        ? Utility::clean_string($_POST['chartType']) : 'hierarchical';
    $effectiveDate = isset($_POST['effectiveDate']) && !empty($_POST['effectiveDate'])
        ? Utility::clean_string($_POST['effectiveDate']) : date('Y-m-d');
    $isCurrent = isset($_POST['isCurrent']) && $_POST['isCurrent'] == 'Y' ? 'Y' : 'N';

    $entityID = isset($_POST['entityID']) && !empty($_POST['entityID'])
        ? Utility::clean_string($_POST['entityID']) : null;
    $orgDataID = isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])
        ? Utility::clean_string($_POST['orgDataID']) : null;
    $orgChartID = isset($_POST['orgChartID']) && !empty($_POST['orgChartID'])
        ? Utility::clean_string($_POST['orgChartID']) : null;

    // Validate required fields
    if (!$orgChartName) {
        $errors[] = "Organization Chart Name is required";
    }
    if (!$entityID) {
        $errors[] = "Organization Entity is required";
    }

    if (count($errors) === 0) {
        // Get entity details for orgDataID if not provided
        if (!$orgDataID) {
            $entityDetails = Data::entities(array('entityID' => $entityID), true, $DBConn);
            if ($entityDetails) {
                $orgDataID = $entityDetails->orgDataID;
            }
        }

        if (!$orgChartID) {
            // Create new org chart
            error_log("Creating new org chart: " . $orgChartName);

            // Core fields (always exist in table)
            $orgChartDetails = array(
                'orgChartName' => $orgChartName,
                'entityID' => $entityID,
                'orgDataID' => $orgDataID,
                'DateAdded' => date('Y-m-d H:i:s'),
                'LastUpdate' => date('Y-m-d H:i:s'),
                'LastUpdatedByID' => $userDetails->ID ?? null,
                'Lapsed' => 'N',
                'Suspended' => 'N'
            );

            // Add optional fields only if they have values (for backward compatibility)
            if ($orgChartDescription) {
                $orgChartDetails['orgChartDescription'] = $orgChartDescription;
            }
            if ($chartType) {
                $orgChartDetails['chartType'] = $chartType;
            }
            if ($effectiveDate) {
                $orgChartDetails['effectiveDate'] = $effectiveDate;
            }
            if ($isCurrent) {
                $orgChartDetails['isCurrent'] = $isCurrent;
            }

            error_log("Org chart data: " . print_r($orgChartDetails, true));

            try {
                if ($DBConn->insert_data('tija_org_charts', $orgChartDetails)) {
                    $orgChartID = $DBConn->lastInsertId();
                    error_log("Org chart created with ID: " . $orgChartID);
                    $success = "Organization chart created successfully";

                    // If this is set as current, update other charts (only if isCurrent column exists)
                    if ($isCurrent == 'Y' && isset($orgChartDetails['isCurrent'])) {
                        try {
                            $DBConn->update_table(
                                'tija_org_charts',
                                array('isCurrent' => 'N'),
                                array('entityID' => $entityID, 'orgChartID!=' => $orgChartID)
                            );
                        } catch (Exception $e) {
                            // Ignore if isCurrent column doesn't exist
                            error_log("Could not update isCurrent flag (column may not exist): " . $e->getMessage());
                        }
                    }
                } else {
                    $errors[] = "Error creating organization chart";
                    error_log("Failed to insert org chart");
                }
            } catch (Exception $dbError) {
                error_log("Database error: " . $dbError->getMessage());
                $errors[] = "Database error: " . $dbError->getMessage();
            }
        } else {
            // Update existing org chart
            error_log("Updating org chart ID: " . $orgChartID);

            $orgChartDetails = Data::org_charts(array('orgChartID' => $orgChartID), true, $DBConn);

            if (!$orgChartDetails) {
                $errors[] = "Organization chart not found";
            } else {
                $changes = array();

                // Core fields
                if ($orgChartName && $orgChartDetails->orgChartName != $orgChartName) {
                    $changes['orgChartName'] = $orgChartName;
                }
                if ($entityID && $orgChartDetails->entityID != $entityID) {
                    $changes['entityID'] = $entityID;
                    // Update orgDataID if entity changed
                    $entityDetails = Data::entities(array('entityID' => $entityID), true, $DBConn);
                    if ($entityDetails && $entityDetails->orgDataID !== $orgChartDetails->orgDataID) {
                        $changes['orgDataID'] = $entityDetails->orgDataID;
                    }
                }

                // Optional fields (only update if column exists in table)
                if ($orgChartDescription !== null && property_exists($orgChartDetails, 'orgChartDescription')) {
                    if ($orgChartDetails->orgChartDescription != $orgChartDescription) {
                        $changes['orgChartDescription'] = $orgChartDescription;
                    }
                }
                if ($chartType && property_exists($orgChartDetails, 'chartType')) {
                    if ($orgChartDetails->chartType != $chartType) {
                        $changes['chartType'] = $chartType;
                    }
                }
                if ($effectiveDate && property_exists($orgChartDetails, 'effectiveDate')) {
                    if ($orgChartDetails->effectiveDate != $effectiveDate) {
                        $changes['effectiveDate'] = $effectiveDate;
                    }
                }
                if (property_exists($orgChartDetails, 'isCurrent')) {
                    if ($orgChartDetails->isCurrent != $isCurrent) {
                        $changes['isCurrent'] = $isCurrent;
                    }
                }

                $changes['LastUpdate'] = date('Y-m-d H:i:s');
                $changes['LastUpdatedByID'] = $userDetails->ID ?? null;

                if (count($changes) > 0) {
                    error_log("Changes: " . print_r($changes, true));

                    try {
                        if ($DBConn->update_table('tija_org_charts', $changes, array('orgChartID' => $orgChartID))) {
                            $success = "Organization chart updated successfully";

                            // If this is set as current, update other charts
                            if (isset($changes['isCurrent']) && $changes['isCurrent'] == 'Y') {
                                try {
                                    $DBConn->update_table(
                                        'tija_org_charts',
                                        array('isCurrent' => 'N'),
                                        array('entityID' => $entityID, 'orgChartID!=' => $orgChartID)
                                    );
                                } catch (Exception $e) {
                                    error_log("Could not update other charts isCurrent flag: " . $e->getMessage());
                                }
                            }
                        } else {
                            $errors[] = "Error updating organization chart";
                            error_log("Failed to update org chart");
                        }
                    } catch (Exception $dbError) {
                        error_log("Database update error: " . $dbError->getMessage());
                        $errors[] = "Database error: " . $dbError->getMessage();
                    }
                } else {
                    $success = "No changes detected";
                }
            }
        }
    }

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $errors[] = $e->getMessage();
}

// Prepare return URL
$returnURL = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] :
    $base . 'html/?s=core&ss=admin&p=entity_details&entityID=' . $entityID . '&tab=reporting';

if (count($errors) == 0) {
    $DBConn->commit();
    error_log("Transaction committed successfully");

    $_SESSION['flash_message'] = $success ? $success : "Organization chart saved successfully";
    $_SESSION['flash_type'] = 'success';

    // Add orgChartID to URL if created
    if ($orgChartID && strpos($returnURL, 'orgChartID=') === false) {
        $returnURL .= (strpos($returnURL, '?') !== false ? '&' : '?') . "orgChartID={$orgChartID}";
    }
} else {
    $DBConn->rollback();
    error_log("Transaction rolled back. Errors: " . print_r($errors, true));

    $_SESSION['flash_message'] = implode('; ', $errors);
    $_SESSION['flash_type'] = 'danger';
}

error_log("Redirecting to: " . $returnURL);
header("Location: {$returnURL}");
exit;