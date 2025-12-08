<?php
/**
 * Goal Library Management Script
 * Handles CRUD operations for goal templates
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

require_once 'php/classes/goallibrary.php';
require_once 'php/classes/goalpermissions.php';

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : 'create';
$response = array('success' => false, 'message' => 'Invalid action');

// Check permissions
if (!GoalPermissions::canManageLibrary($userDetails->ID, $DBConn)) {
    echo json_encode(array('success' => false, 'message' => 'Permission denied: Cannot manage goal library'));
    exit;
}

try {
    if ($action === 'get') {
        $libraryID = isset($_POST['libraryID']) ? intval($_POST['libraryID']) : 0;
        if (!$libraryID) {
            $response = array('success' => false, 'message' => 'Library ID is required');
        } else {
            $template = GoalLibrary::getTemplate($libraryID, $DBConn);
            if ($template) {
                $response = array('success' => true, 'template' => $template);
            } else {
                $response = array('success' => false, 'message' => 'Template not found');
            }
        }
    } elseif ($action === 'create') {
        $templateData = array(
            'templateCode' => isset($_POST['templateCode']) ? Utility::clean_string($_POST['templateCode']) : '',
            'templateName' => isset($_POST['templateName']) ? Utility::clean_string($_POST['templateName']) : '',
            'templateDescription' => isset($_POST['templateDescription']) ? Utility::clean_string($_POST['templateDescription']) : null,
            'goalType' => isset($_POST['goalType']) ? Utility::clean_string($_POST['goalType']) : 'Strategic',
            'functionalDomain' => isset($_POST['functionalDomain']) ? Utility::clean_string($_POST['functionalDomain']) : null,
            'competencyLevel' => isset($_POST['competencyLevel']) ? Utility::clean_string($_POST['competencyLevel']) : 'All',
            'timeHorizon' => isset($_POST['timeHorizon']) ? Utility::clean_string($_POST['timeHorizon']) : 'Annual',
            'suggestedWeight' => isset($_POST['suggestedWeight']) ? floatval($_POST['suggestedWeight']) : 0.25,
            'LastUpdatedByID' => $userDetails->ID
        );

        // Parse variables JSON
        if (isset($_POST['variables']) && !empty($_POST['variables'])) {
            $variables = json_decode($_POST['variables'], true);
            if ($variables) {
                $templateData['variables'] = $variables;
            }
        }

        $libraryID = GoalLibrary::createTemplate($templateData, $DBConn);

        if ($libraryID) {
            $response = array('success' => true, 'libraryID' => $libraryID, 'message' => 'Template created successfully');
        } else {
            $response = array('success' => false, 'message' => 'Failed to create template');
        }
    } elseif ($action === 'update') {
        $libraryID = isset($_POST['libraryID']) ? intval($_POST['libraryID']) : 0;
        if (!$libraryID) {
            $response = array('success' => false, 'message' => 'Library ID is required');
        } else {
            $templateData = array(
                'templateCode' => isset($_POST['templateCode']) ? Utility::clean_string($_POST['templateCode']) : '',
                'templateName' => isset($_POST['templateName']) ? Utility::clean_string($_POST['templateName']) : '',
                'templateDescription' => isset($_POST['templateDescription']) ? Utility::clean_string($_POST['templateDescription']) : null,
                'goalType' => isset($_POST['goalType']) ? Utility::clean_string($_POST['goalType']) : 'Strategic',
                'functionalDomain' => isset($_POST['functionalDomain']) ? Utility::clean_string($_POST['functionalDomain']) : null,
                'competencyLevel' => isset($_POST['competencyLevel']) ? Utility::clean_string($_POST['competencyLevel']) : 'All',
                'timeHorizon' => isset($_POST['timeHorizon']) ? Utility::clean_string($_POST['timeHorizon']) : 'Annual',
                'suggestedWeight' => isset($_POST['suggestedWeight']) ? floatval($_POST['suggestedWeight']) : 0.25,
                'LastUpdatedByID' => $userDetails->ID
            );

            // Parse variables JSON
            if (isset($_POST['variables'])) {
                $rawVars = trim($_POST['variables']);
                if ($rawVars === '') {
                    $templateData['variables'] = array();
                } else {
                    $variables = json_decode($rawVars, true);
                    if (is_array($variables)) {
                        $templateData['variables'] = $variables;
                    }
                }
            }

            $updated = GoalLibrary::updateTemplate($libraryID, $templateData, $DBConn);
            if ($updated) {
                $response = array('success' => true, 'libraryID' => $libraryID, 'message' => 'Template updated successfully');
            } else {
                $response = array('success' => false, 'message' => 'Failed to update template');
            }
        }
    } elseif ($action === 'delete') {
        $libraryID = isset($_POST['libraryID']) ? intval($_POST['libraryID']) : 0;
        if (!$libraryID) {
            $response = array('success' => false, 'message' => 'Library ID is required');
        } else {
            // Soft delete
            $updateData = array('Lapsed' => 'Y', 'LastUpdatedByID' => $userDetails->ID);
            $result = $DBConn->update_table('tija_goal_library', $updateData, array('libraryID' => $libraryID));
            $response = array('success' => $result, 'message' => $result ? 'Template deleted successfully' : 'Failed to delete template');
        }
    }
} catch (Exception $e) {
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

