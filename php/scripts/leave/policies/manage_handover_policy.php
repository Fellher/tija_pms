<?php
/**
 * Manage Handover Policy
 *
 * Handles CRUD operations for leave handover policies via AJAX.
 */
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isHRManager) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

    if (!class_exists('LeaveHandoverPolicy')) {
        echo json_encode(['success' => false, 'message' => 'LeaveHandoverPolicy class not available']);
        exit;
    }

    switch ($action) {
        case 'create':
            $policyData = array(
                'entityID' => isset($_POST['entityID']) ? (int)$_POST['entityID'] : 0,
                'orgDataID' => isset($_POST['orgDataID']) ? (int)$_POST['orgDataID'] : null,
                'leaveTypeID' => isset($_POST['leaveTypeID']) && !empty($_POST['leaveTypeID']) ? (int)$_POST['leaveTypeID'] : null,
                'policyScope' => isset($_POST['policyScope']) ? Utility::clean_string($_POST['policyScope']) : 'entity_wide',
                'targetRoleID' => isset($_POST['targetRoleID']) && !empty($_POST['targetRoleID']) ? (int)$_POST['targetRoleID'] : null,
                'targetJobCategoryID' => isset($_POST['targetJobCategoryID']) && !empty($_POST['targetJobCategoryID']) ? (int)$_POST['targetJobCategoryID'] : null,
                'targetJobBandID' => isset($_POST['targetJobBandID']) && !empty($_POST['targetJobBandID']) ? (int)$_POST['targetJobBandID'] : null,
                'targetJobLevelID' => isset($_POST['targetJobLevelID']) && !empty($_POST['targetJobLevelID']) ? (int)$_POST['targetJobLevelID'] : null,
                'targetJobTitleID' => isset($_POST['targetJobTitleID']) && !empty($_POST['targetJobTitleID']) ? (int)$_POST['targetJobTitleID'] : null,
                'isMandatory' => isset($_POST['isMandatory']) ? Utility::clean_string($_POST['isMandatory']) : 'N',
                'minHandoverDays' => isset($_POST['minHandoverDays']) ? (int)$_POST['minHandoverDays'] : 0,
                'requireConfirmation' => isset($_POST['requireConfirmation']) ? Utility::clean_string($_POST['requireConfirmation']) : 'Y',
                'requireTraining' => isset($_POST['requireTraining']) ? Utility::clean_string($_POST['requireTraining']) : 'N',
                'requireCredentials' => isset($_POST['requireCredentials']) ? Utility::clean_string($_POST['requireCredentials']) : 'N',
                'requireTools' => isset($_POST['requireTools']) ? Utility::clean_string($_POST['requireTools']) : 'N',
                'requireDocuments' => isset($_POST['requireDocuments']) ? Utility::clean_string($_POST['requireDocuments']) : 'N',
                'allowProjectIntegration' => isset($_POST['allowProjectIntegration']) ? Utility::clean_string($_POST['allowProjectIntegration']) : 'N',
                'requireNomineeAcceptance' => isset($_POST['requireNomineeAcceptance']) ? Utility::clean_string($_POST['requireNomineeAcceptance']) : 'Y',
                'nomineeResponseDeadlineHours' => isset($_POST['nomineeResponseDeadlineHours']) ? (int)$_POST['nomineeResponseDeadlineHours'] : 48,
                'allowPeerRevision' => isset($_POST['allowPeerRevision']) ? Utility::clean_string($_POST['allowPeerRevision']) : 'Y',
                'maxRevisionAttempts' => isset($_POST['maxRevisionAttempts']) ? (int)$_POST['maxRevisionAttempts'] : 3,
                'effectiveDate' => isset($_POST['effectiveDate']) ? Utility::clean_string($_POST['effectiveDate']) : date('Y-m-d'),
                'expiryDate' => isset($_POST['expiryDate']) && !empty($_POST['expiryDate']) ? Utility::clean_string($_POST['expiryDate']) : null,
                'policyName' => isset($_POST['policyName']) ? Utility::clean_string($_POST['policyName']) : null,
                'policyDescription' => isset($_POST['policyDescription']) ? Utility::clean_string($_POST['policyDescription']) : null
            );

            if (!$policyData['entityID']) {
                echo json_encode(['success' => false, 'message' => 'Entity ID is required']);
                exit;
            }

            $policyID = LeaveHandoverPolicy::create_policy($policyData, $DBConn);
            if ($policyID) {
                echo json_encode(['success' => true, 'message' => 'Policy created successfully', 'policyID' => $policyID]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create policy']);
            }
            break;

        case 'update':
            $policyID = isset($_POST['policyID']) ? (int)$_POST['policyID'] : 0;
            if (!$policyID) {
                echo json_encode(['success' => false, 'message' => 'Policy ID is required']);
                exit;
            }

            $policyData = array();
            $allowedFields = array(
                'orgDataID', 'leaveTypeID', 'policyScope', 'targetRoleID', 'targetJobCategoryID',
                'targetJobBandID', 'targetJobLevelID', 'targetJobTitleID', 'isMandatory', 'minHandoverDays',
                'requireConfirmation', 'requireTraining', 'requireCredentials', 'requireTools', 'requireDocuments',
                'allowProjectIntegration', 'requireNomineeAcceptance', 'nomineeResponseDeadlineHours',
                'allowPeerRevision', 'maxRevisionAttempts', 'effectiveDate', 'expiryDate',
                'policyName', 'policyDescription', 'Lapsed', 'Suspended'
            );

            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $policyData[$field] = $_POST[$field];
                }
            }

            $result = LeaveHandoverPolicy::update_policy($policyID, $policyData, $DBConn);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Policy updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update policy']);
            }
            break;

        case 'delete':
            $policyID = isset($_POST['policyID']) ? (int)$_POST['policyID'] : 0;
            if (!$policyID) {
                echo json_encode(['success' => false, 'message' => 'Policy ID is required']);
                exit;
            }

            $result = LeaveHandoverPolicy::delete_policy($policyID, $DBConn);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Policy deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete policy']);
            }
            break;

        case 'get':
            $filters = array();
            if (isset($_POST['entityID'])) {
                $filters['entityID'] = (int)$_POST['entityID'];
            }
            if (isset($_POST['policyScope'])) {
                $filters['policyScope'] = Utility::clean_string($_POST['policyScope']);
            }
            if (isset($_POST['Lapsed'])) {
                $filters['Lapsed'] = Utility::clean_string($_POST['Lapsed']);
            }

            $policies = LeaveHandoverPolicy::get_policies($filters, $DBConn);
            echo json_encode(['success' => true, 'policies' => $policies]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Operation failed: ' . $e->getMessage()
    ]);
}
?>

