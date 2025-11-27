<?php
/**
 * PROJECT PLAN TEMPLATE MANAGEMENT
 * =================================
 *
 * Handles CRUD operations for project plan templates
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 * @version    1.0.0
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

$DBConn->begin();
$errors = array();
$success = "";

if ($isValidUser) {
    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : 'create';
    $templateID = isset($_POST['templateID']) ? Utility::clean_string($_POST['templateID']) : null;
    $templateName = isset($_POST['templateName']) ? Utility::clean_string($_POST['templateName']) : null;
    $templateDescription = isset($_POST['templateDescription']) ? Utility::clean_string($_POST['templateDescription']) : '';
    $templateCategory = isset($_POST['templateCategory']) ? Utility::clean_string($_POST['templateCategory']) : null;
    $isPublic = isset($_POST['isPublic']) ? 'Y' : 'N';

    $orgDataID = isset($_POST['orgDataID']) ? Utility::clean_string($_POST['orgDataID']) : $employeeDetails->orgDataID;
    $entityID = isset($_POST['entityID']) ? Utility::clean_string($_POST['entityID']) : $employeeDetails->entityID;

    $returnURL = Utility::returnURL($_SESSION['returnURL'], "?s=user&ss=projects&p=templates");

    // ============================================================================
    // CREATE NEW TEMPLATE
    // ============================================================================
    if ($action === 'create' && !$templateID) {
        // Validate inputs
        if (!$templateName) {
            $errors[] = 'Template name is required';
        }

        if (!$orgDataID || !$entityID) {
            $errors[] = 'Organization and Entity context required';
        }

        // Get phases data
        $phaseNames = isset($_POST['phaseName']) && is_array($_POST['phaseName'])
            ? array_map('Utility::clean_string', $_POST['phaseName'])
            : array();
        $phaseDescriptions = isset($_POST['phaseDescription']) && is_array($_POST['phaseDescription'])
            ? array_map('Utility::clean_string', $_POST['phaseDescription'])
            : array();
        $phasePercentages = isset($_POST['phasePercent']) && is_array($_POST['phasePercent'])
            ? array_map('floatval', $_POST['phasePercent'])
            : array();

        if (empty($phaseNames) || count(array_filter($phaseNames)) === 0) {
            $errors[] = 'At least one phase is required';
        }

        // Validate total percentage (should be 100%)
        $totalPercent = array_sum($phasePercentages);
        if (!empty($phasePercentages) && abs($totalPercent - 100) > 0.01) {
            $errors[] = "Phase percentages must sum to 100% (currently: {$totalPercent}%)";
        }

        if (!$errors) {
            // Insert template
            $templateData = array(
                'templateName' => $templateName,
                'templateDescription' => $templateDescription,
                'templateCategory' => $templateCategory,
                'isPublic' => $isPublic,
                'isSystemTemplate' => 'N',
                'createdByID' => $userDetails->ID,
                'orgDataID' => $orgDataID,
                'entityID' => $entityID,
                'usageCount' => 0,
                'isActive' => 'Y',
                'DateAdded' => $config['currentDateTimeFormated'],
                'LastUpdate' => $config['currentDateTimeFormated'],
                'LastUpdateByID' => $userDetails->ID
            );

            if ($DBConn->insert_data('tija_project_plan_templates', $templateData)) {
                $templateID = $DBConn->lastInsertId();

                // Insert phases
                $phasesAdded = 0;
                foreach ($phaseNames as $index => $phaseName) {
                    if (empty($phaseName)) continue;

                    $phaseData = array(
                        'templateID' => $templateID,
                        'phaseName' => $phaseName,
                        'phaseDescription' => isset($phaseDescriptions[$index]) ? $phaseDescriptions[$index] : '',
                        'phaseOrder' => $index + 1,
                        'durationPercent' => isset($phasePercentages[$index]) ? $phasePercentages[$index] : null,
                        'DateAdded' => $config['currentDateTimeFormated']
                    );

                    if ($DBConn->insert_data('tija_project_plan_template_phases', $phaseData)) {
                        $phasesAdded++;
                    }
                }

                $success = "Template '{$templateName}' created successfully with {$phasesAdded} phase(s)";
            } else {
                $errors[] = 'Failed to create template';
            }
        }
    }

    // ============================================================================
    // UPDATE EXISTING TEMPLATE
    // ============================================================================
    elseif ($action === 'update' && $templateID) {
        // Check if template exists and user has permission
        $templateDetails = $DBConn->retrieve_db_table_rows('tija_project_plan_templates',
            ['*'],
            array('templateID' => $templateID)
        );

        if (!$templateDetails || count($templateDetails) === 0) {
            $errors[] = 'Template not found';
        } else {
            $template = $templateDetails[0];

            // Check permissions (can only edit own templates or if admin)
            if ($template->createdByID != $userDetails->ID && !$isAdmin) {
                $errors[] = 'You do not have permission to edit this template';
            }

            // Cannot edit system templates
            if ($template->isSystemTemplate === 'Y') {
                $errors[] = 'System templates cannot be modified';
            }

            if (!$errors) {
                $changes = array();

                if ($templateName && $templateName !== $template->templateName) {
                    $changes['templateName'] = $templateName;
                }
                if ($templateDescription !== $template->templateDescription) {
                    $changes['templateDescription'] = $templateDescription;
                }
                if ($templateCategory && $templateCategory !== $template->templateCategory) {
                    $changes['templateCategory'] = $templateCategory;
                }
                if ($isPublic !== $template->isPublic) {
                    $changes['isPublic'] = $isPublic;
                }

                if (!empty($changes)) {
                    $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                    $changes['LastUpdateByID'] = $userDetails->ID;

                    if ($DBConn->update_table('tija_project_plan_templates', $changes, array('templateID' => $templateID))) {
                        $success = "Template updated successfully";

                        // Update phases if provided
                        $phaseNames = isset($_POST['phaseName']) && is_array($_POST['phaseName'])
                            ? array_map('Utility::clean_string', $_POST['phaseName'])
                            : array();

                        if (!empty($phaseNames)) {
                            // Delete existing phases
                            $DBConn->query("DELETE FROM tija_project_plan_template_phases WHERE templateID = ?");
                            $DBConn->bind(1, $templateID);
                            $DBConn->execute();

                            // Re-insert phases
                            $phaseDescriptions = isset($_POST['phaseDescription']) && is_array($_POST['phaseDescription'])
                                ? array_map('Utility::clean_string', $_POST['phaseDescription'])
                                : array();
                            $phasePercentages = isset($_POST['phasePercent']) && is_array($_POST['phasePercent'])
                                ? array_map('floatval', $_POST['phasePercent'])
                                : array();

                            foreach ($phaseNames as $index => $phaseName) {
                                if (empty($phaseName)) continue;

                                $phaseData = array(
                                    'templateID' => $templateID,
                                    'phaseName' => $phaseName,
                                    'phaseDescription' => isset($phaseDescriptions[$index]) ? $phaseDescriptions[$index] : '',
                                    'phaseOrder' => $index + 1,
                                    'durationPercent' => isset($phasePercentages[$index]) ? $phasePercentages[$index] : null,
                                    'DateAdded' => $config['currentDateTimeFormated']
                                );

                                $DBConn->insert_data('tija_project_plan_template_phases', $phaseData);
                            }
                        }
                    } else {
                        $errors[] = 'No changes detected or update failed';
                    }
                } else {
                    $success = "No changes were made to the template";
                }
            }
        }
    }

    // ============================================================================
    // DELETE TEMPLATE
    // ============================================================================
    elseif ($action === 'delete' && $templateID) {
        $templateDetails = $DBConn->retrieve_db_table_rows('tija_project_plan_templates',
            ['*'],
            array('templateID' => $templateID)
        );

        if (!$templateDetails || count($templateDetails) === 0) {
            $errors[] = 'Template not found';
        } else {
            $template = $templateDetails[0];

            // Check permissions
            if ($template->createdByID != $userDetails->ID && !$isAdmin) {
                $errors[] = 'You do not have permission to delete this template';
            }

            // Cannot delete system templates
            if ($template->isSystemTemplate === 'Y') {
                $errors[] = 'System templates cannot be deleted';
            }

            if (!$errors) {
                // Soft delete - set isActive to N
                $changes = array(
                    'isActive' => 'N',
                    'LastUpdate' => $config['currentDateTimeFormated'],
                    'LastUpdateByID' => $userDetails->ID
                );

                if ($DBConn->update_table('tija_project_plan_templates', $changes, array('templateID' => $templateID))) {
                    $success = "Template deleted successfully";
                } else {
                    $errors[] = 'Failed to delete template';
                }
            }
        }
    }

    // ============================================================================
    // TOGGLE TEMPLATE VISIBILITY
    // ============================================================================
    elseif ($action === 'toggleVisibility' && $templateID) {
        $templateDetails = $DBConn->retrieve_db_table_rows('tija_project_plan_templates',
            ['*'],
            array('templateID' => $templateID)
        );

        if ($templateDetails && count($templateDetails) > 0) {
            $template = $templateDetails[0];

            // Check permissions
            if ($template->createdByID != $userDetails->ID && !$isAdmin) {
                $errors[] = 'You do not have permission to modify this template';
            } else {
                $newVisibility = $template->isPublic === 'Y' ? 'N' : 'Y';
                $changes = array(
                    'isPublic' => $newVisibility,
                    'LastUpdate' => $config['currentDateTimeFormated'],
                    'LastUpdateByID' => $userDetails->ID
                );

                if ($DBConn->update_table('tija_project_plan_templates', $changes, array('templateID' => $templateID))) {
                    $visibilityText = $newVisibility === 'Y' ? 'public' : 'private';
                    $success = "Template is now {$visibilityText}";
                } else {
                    $errors[] = 'Failed to update template visibility';
                }
            }
        } else {
            $errors[] = 'Template not found';
        }
    }

} else {
    $errors[] = 'You need to be logged in to manage templates';
}

// ============================================================================
// RESPONSE
// ============================================================================

if (count($errors) == 0) {
    $DBConn->commit();
    Alert::success($success, true, array('text-center'));
} else {
    $DBConn->rollBack();
    $errorMessage = implode(', ', $errors);
    Alert::danger($errorMessage, true, array('text-center'));
}

header("Location: {$config['siteURL']}html/{$returnURL}");
exit();
?>

